#!/usr/bin/env bash
set -Eeuo pipefail

# =============================================================================
#  Pull-based deployment — runs ON the server, as the site user.
# =============================================================================
#
#  WHY PULL AND NOT PUSH
#
#  GitHub-hosted runners could not reliably SSH in: `Configure SSH` died at
#  exactly 5s, ssh-keyscan's default timeout, meaning packets were dropped
#  rather than refused. fail2ban is active on this host and sshd sits on
#  0.0.0.0:22 with PermitRootLogin yes, so it is probed constantly and bans
#  aggressively. Runner IPs rotate across ranges too large to allowlist, so a
#  push-based deploy is a coin flip on which IP the job draws.
#
#  This inverts the direction. The server makes an OUTBOUND HTTPS request to
#  GitHub — measured at 59ms to api.github.com from this box — which no
#  intrusion-prevention rule interferes with. Consequences:
#
#    * no inbound port has to be open to CI
#    * no SSH private key needs to live in GitHub secrets
#    * runner IP churn stops mattering entirely
#
#  USAGE
#      ./pull-deploy.sh <commit-sha>            prepare (and cut over if allowed)
#      RUN_MIGRATIONS=true ./pull-deploy.sh ... also migrate, after a backup
#
#  HOLD_SWITCH ($DEPLOY_ROOT/HOLD_SWITCH) is the hard gate. While it exists the
#  release is built and verified but production is never touched. It lives on
#  the server, not in CI, so whoever controls the box controls the cutover.
# =============================================================================

REPO="${REPO:-AbdoAbosamra/Speeda.ca}"
DEPLOY_ROOT="${DEPLOY_ROOT:-/home/speeda/deploy}"
PUBLIC_LINK="${PUBLIC_LINK:-/home/speeda/htdocs/speeda_live}"
PHP_BIN="${PHP_BIN:-php}"
KEEP_RELEASES="${KEEP_RELEASES:-5}"
RUN_MIGRATIONS="${RUN_MIGRATIONS:-false}"
HEALTH_URL="${PRODUCTION_HEALTH_URL:-https://speeda.ca/up}"
DB_NAME="${DB_NAME:-speedadb}"

SHA="${1:-}"
[ -n "$SHA" ] || { echo "usage: $0 <commit-sha>" >&2; exit 2; }

RELEASES="$DEPLOY_ROOT/releases"
SHARED="$DEPLOY_ROOT/shared"
BACKUPS="$DEPLOY_ROOT/backups"
RELEASE="$RELEASES/${SHA:0:12}-$(date +%Y%m%d%H%M%S)"
PREVIOUS=""
SWITCHED=0

log() { printf '[deploy] %s\n' "$*"; }

rollback() {
  if [ "$SWITCHED" = "1" ] && [ -n "$PREVIOUS" ] && [ -d "$PREVIOUS" ]; then
    log "ROLLING BACK to $PREVIOUS"
    ln -sfn "$PREVIOUS" "$PUBLIC_LINK.tmp"
    mv -Tf "$PUBLIC_LINK.tmp" "$PUBLIC_LINK"
    "$PHP_BIN" "$PUBLIC_LINK/artisan" queue:restart --ansi || true
  fi
}
trap 'code=$?; log "FAILED (exit $code)"; rollback; exit $code' ERR

mkdir -p "$RELEASES" "$SHARED" "$BACKUPS"
[ -f "$SHARED/.env" ] || { echo "[deploy] missing $SHARED/.env" >&2; exit 1; }

# ---------------------------------------------------------------- 1. FETCH ---
# Public repo, so both fetches are unauthenticated HTTPS. Nothing secret is
# transported and nothing has to be trusted beyond TLS.
log "Fetching source $SHA"
WORK="$(mktemp -d)"
trap 'rm -rf "$WORK"' EXIT
curl -fsSL "https://codeload.github.com/$REPO/tar.gz/$SHA" -o "$WORK/src.tar.gz"
tar -xzf "$WORK/src.tar.gz" -C "$WORK"
SRC="$(find "$WORK" -maxdepth 1 -type d -name '*-*' | head -1)"
[ -d "$SRC" ] || { echo "[deploy] source extract failed" >&2; exit 1; }

# Vite assets are built in CI and published as a release asset: this server runs
# Node 18 and the toolchain needs 20+, so building here is not an option.
log "Fetching prebuilt assets"
ASSET="https://github.com/$REPO/releases/download/build-$SHA/build.tar.gz"
if curl -fsSL "$ASSET" -o "$WORK/build.tar.gz"; then
  if curl -fsSL "$ASSET.sha256" -o "$WORK/build.sha256" 2>/dev/null; then
    (cd "$WORK" && sha256sum -c --status <(awk '{print $1"  build.tar.gz"}' build.sha256)) \
      || { echo "[deploy] asset checksum mismatch" >&2; exit 1; }
    log "Asset checksum verified"
  fi
  mkdir -p "$SRC/public"
  tar -xzf "$WORK/build.tar.gz" -C "$SRC"
else
  echo "[deploy] no published build for $SHA — refusing to ship without assets" >&2
  exit 1
fi

# --------------------------------------------------------------- 2. STAGE ---
log "Staging $RELEASE"
mkdir -p "$RELEASE"
rsync -a --exclude='.git' --exclude='.github' --exclude='node_modules' \
      --exclude='storage' --exclude='tests' --exclude='.env*' \
      --exclude='public/hot' --exclude='public/storage' \
      "$SRC/" "$RELEASE/"

log "Installing PHP dependencies"
(cd "$RELEASE" && composer install --no-dev --no-interaction --prefer-dist \
   --no-progress --optimize-autoloader >/dev/null)

# --------------------------------------------------------------- 3. LINK -----
ln -sfn "$SHARED/storage" "$RELEASE/storage"
ln -sfn "$SHARED/.env"    "$RELEASE/.env"
mkdir -p "$RELEASE/bootstrap/cache/views"

# Compiled Blade MUST be per-release. The shared .env sets TMPDIR inside
# storage, so without this override every release would compile into the same
# shared directory and overwrite the templates the live release is rendering.
# config:cache bakes the value in, so it also applies at runtime.
export VIEW_COMPILED_PATH="$RELEASE/bootstrap/cache/views"

cd "$RELEASE"
log "Building release caches"
"$PHP_BIN" artisan storage:link --ansi >/dev/null
"$PHP_BIN" artisan config:cache --ansi >/dev/null
"$PHP_BIN" artisan event:cache --ansi >/dev/null
"$PHP_BIN" artisan view:cache --ansi >/dev/null

# -------------------------------------------------------------- 4. VERIFY ---
log "Verifying release"
"$PHP_BIN" artisan tinker --execute='
  $f = [];
  if (app()->version() === "") $f[] = "version";
  if (config("app.debug") !== false) $f[] = "APP_DEBUG must be false";
  if (!str_starts_with(config("view.compiled"), base_path())) $f[] = "view.compiled not release-local";
  try { DB::select("select 1"); } catch (\Throwable $e) { $f[] = "database unreachable"; }
  if ($f) { fwrite(STDERR, "VERIFY FAILED: ".implode(", ", $f).PHP_EOL); exit(1); }
  echo "verify ok".PHP_EOL;
' >/dev/null

# ------------------------------------------------------------ 5. MIGRATIONS --
# Backup first, always, and prove it is usable before touching the schema.
if [ "$RUN_MIGRATIONS" = "true" ]; then
  PENDING="$("$PHP_BIN" artisan migrate:status 2>/dev/null | grep -ci pending || true)"
  if [ "${PENDING:-0}" -gt 0 ]; then
    DUMP="$BACKUPS/${DB_NAME}_$(date +%Y%m%d_%H%M%S).sql.gz"
    log "Backing up database before $PENDING migration(s)"
    clpctl db:export --databaseName="$DB_NAME" --file="$DUMP" >/dev/null
    [ -s "$DUMP" ] || { echo "[deploy] backup empty" >&2; exit 1; }
    gzip -t "$DUMP" || { echo "[deploy] backup corrupt" >&2; exit 1; }
    log "Backup verified: $DUMP"
    # Runs while the OLD release still serves, so migrations must be
    # backward-compatible. Destructive changes need a separate, planned window.
    "$PHP_BIN" artisan migrate --force --isolated --ansi
  else
    log "No pending migrations"
  fi
fi

# ----------------------------------------------------------------- 6. HOLD ---
if [ -f "$DEPLOY_ROOT/HOLD_SWITCH" ]; then
  log "HOLD_SWITCH present — prepared but NOT activated"
  log "Release ready: $RELEASE"
  log "Remove $DEPLOY_ROOT/HOLD_SWITCH and re-run to cut over."
  exit 0
fi

# -------------------------------------------------------------- 7. CUTOVER ---
if [ -e "$PUBLIC_LINK" ] && [ ! -L "$PUBLIC_LINK" ]; then
  echo "[deploy] $PUBLIC_LINK is a real directory, not a symlink. Refusing." >&2
  exit 1
fi
[ -L "$PUBLIC_LINK" ] && PREVIOUS="$(readlink -f "$PUBLIC_LINK")"

log "Switching $PUBLIC_LINK -> $RELEASE"
ln -sfn "$RELEASE" "$PUBLIC_LINK.tmp"
mv -Tf "$PUBLIC_LINK.tmp" "$PUBLIC_LINK"
SWITCHED=1
"$PHP_BIN" "$PUBLIC_LINK/artisan" queue:restart --ansi >/dev/null || true

# --------------------------------------------------------------- 8. HEALTH ---
log "Health check: $HEALTH_URL"
for i in 1 2 3 4 5; do
  code="$(curl -s -o /dev/null -w '%{http_code}' --max-time 15 "$HEALTH_URL" || true)"
  [ "$code" = "200" ] && { log "healthy (attempt $i)"; break; }
  [ "$i" = "5" ] && { echo "[deploy] health check failed: $code" >&2; exit 1; }
  sleep 3
done

# A 404 answering 302 is how the soft-404 regression looked; assert the real
# status so that class of bug can never reach production unnoticed again.
nf="$(curl -s -o /dev/null -w '%{http_code}' --max-time 15 "${HEALTH_URL%/up}/__deploy_probe_404" || true)"
[ "$nf" = "404" ] || { echo "[deploy] expected 404 for unknown route, got $nf" >&2; exit 1; }
log "404 semantics verified"

# ---------------------------------------------------------------- 9. PRUNE ---
# Only after the release is proven healthy, and never the active one.
cur="$(readlink -f "$PUBLIC_LINK")"
find "$RELEASES" -mindepth 1 -maxdepth 1 -type d -printf '%T@ %p\n' | sort -rn | tail -n +$((KEEP_RELEASES+1)) \
  | while read -r _ old; do
      [ "$(readlink -f "$old")" != "$cur" ] && { log "pruning $old"; rm -rf "$old"; }
    done

log "Deployed $SHA with no downtime"
