#!/usr/bin/env bash
set -Eeuo pipefail

# =============================================================================
#  Zero-downtime release activation
# =============================================================================
#
#  The site keeps serving traffic for the entire deployment. There is no
#  `artisan down`. That is only safe because of three specific decisions:
#
#  1. PER-RELEASE COMPILED VIEWS.
#     config/view.php defaults the compiled path to a shared temp directory.
#     With releases sharing it, `view:cache` for the new code would overwrite
#     the templates the OLD release is still rendering — mismatched views
#     against old controllers, i.e. live errors. VIEW_COMPILED_PATH below
#     pins each release to its own directory, and `config:cache` bakes that
#     value into the release's own config cache, so it also applies at runtime.
#
#  2. NOTHING SHARED IS CLEARED.
#     The old release stays fully warm while the new one is prepared. We never
#     run `optimize:clear`, because it also flushes the shared application
#     cache that the live site is using.
#
#  3. ADDITIVE MIGRATIONS ONLY.
#     Migrations run BEFORE the symlink swap, while the old code is still
#     serving. That is safe only for backward-compatible changes: new tables,
#     new nullable columns, relaxed constraints. For a destructive or
#     rewriting migration, set MAINTENANCE_MODE=true for that one deploy —
#     it accepts a short outage in exchange for a consistent cutover.
#
#  Any failure rolls the `current` symlink back to the previous release.
# =============================================================================

log() {
  printf '[deploy] %s\n' "$*"
}

require_env() {
  local name="$1"
  if [ -z "${!name:-}" ]; then
    printf '[deploy] Missing required environment variable: %s\n' "$name" >&2
    exit 1
  fi
}

require_env PROJECT_PATH
require_env RELEASE_NAME

PHP_BIN="${PHP_BIN:-php}"
KEEP_RELEASES="${KEEP_RELEASES:-5}"
RUN_MIGRATIONS="${RUN_MIGRATIONS:-false}"
MAINTENANCE_MODE="${MAINTENANCE_MODE:-false}"
DEPLOY_ROOT="${PROJECT_PATH%/}"
RELEASES_DIR="$DEPLOY_ROOT/releases"
SHARED_DIR="$DEPLOY_ROOT/shared"
# The symlink that is swapped atomically. On CloudPanel this is the site's own
# document path (/home/<user>/htdocs/<domain>), so the vhost keeps pointing
# where CloudPanel put it and NO nginx change — and no root — is ever needed.
# Defaults to the classic <root>/current layout when PUBLIC_LINK is unset.
CURRENT_LINK="${PUBLIC_LINK:-$DEPLOY_ROOT/current}"
RELEASE_PATH="$RELEASES_DIR/$RELEASE_NAME"
SHARED_ENV="$SHARED_DIR/.env"
SHARED_STORAGE="$SHARED_DIR/storage"
MAINTENANCE_ENTERED=0
SWITCH_COMPLETED=0
PREVIOUS_RELEASE=""

# Compiled Blade for THIS release only. bootstrap/cache is per-release (it is
# not one of the shared symlinks), which is exactly the isolation we need.
export VIEW_COMPILED_PATH="$RELEASE_PATH/bootstrap/cache/views"

if [ ! -d "$RELEASE_PATH" ]; then
  printf '[deploy] Release path does not exist: %s\n' "$RELEASE_PATH" >&2
  exit 1
fi

case "$KEEP_RELEASES" in
  ''|*[!0-9]*)
    log "Invalid KEEP_RELEASES value '$KEEP_RELEASES'; using 5"
    KEEP_RELEASES=5
    ;;
esac

rollback_current() {
  if [ "$SWITCH_COMPLETED" = "1" ] && [ -n "$PREVIOUS_RELEASE" ] && [ -d "$PREVIOUS_RELEASE" ]; then
    log "Rolling current symlink back to $PREVIOUS_RELEASE"
    ln -sfn "$PREVIOUS_RELEASE" "$CURRENT_LINK.tmp"
    mv -Tf "$CURRENT_LINK.tmp" "$CURRENT_LINK"
    refresh_php_workers
    if [ -f "$CURRENT_LINK/artisan" ]; then
      "$PHP_BIN" "$CURRENT_LINK/artisan" queue:restart --ansi || true
    fi
  fi
}

bring_app_up() {
  if [ "$MAINTENANCE_ENTERED" = "1" ] && [ -f "$CURRENT_LINK/artisan" ]; then
    log "Leaving maintenance mode after failure"
    "$PHP_BIN" "$CURRENT_LINK/artisan" up --ansi || true
  fi
}

on_error() {
  local code=$?
  log "Deployment failed with exit code $code"
  rollback_current
  bring_app_up
  exit "$code"
}

trap on_error ERR

# -----------------------------------------------------------------------------
# Make PHP notice the new code behind the `current` symlink.
#
# PHP caches compiled opcode and resolved realpaths. If nginx passes
# SCRIPT_FILENAME as $document_root (the symlink path) rather than
# $realpath_root, FPM can keep executing the PREVIOUS release after the swap —
# the deployment reports success while users get old code. Reloading FPM is a
# graceful operation: it finishes in-flight requests, so it costs no downtime.
#
# Every step is best-effort: a deploy user without sudo must not fail the
# release. See deploy/nginx.example.conf for the $realpath_root setting that
# makes this unnecessary in the first place.
# -----------------------------------------------------------------------------
refresh_php_workers() {
  if command -v systemctl >/dev/null 2>&1; then
    local unit
    unit="$(systemctl list-units --type=service --state=running --no-legend 2>/dev/null \
      | awk '{print $1}' | grep -E '^php.*fpm\.service$' | head -n1 || true)"

    if [ -n "$unit" ] && sudo -n systemctl reload "$unit" >/dev/null 2>&1; then
      log "Reloaded $unit gracefully"
      return
    fi
  fi

  log "Could not reload PHP-FPM (no sudo?). Relying on \$realpath_root in nginx."
}

log "Starting zero-downtime release activation"
log "Release: $RELEASE_NAME"
log "Commit: ${GITHUB_SHA:-unknown}"
log "Run: ${GITHUB_RUN_ID:-manual}"

mkdir -p "$RELEASES_DIR" "$SHARED_DIR"
mkdir -p \
  "$SHARED_STORAGE/app/public" \
  "$SHARED_STORAGE/framework/cache/data" \
  "$SHARED_STORAGE/framework/sessions" \
  "$SHARED_STORAGE/framework/testing" \
  "$SHARED_STORAGE/framework/views" \
  "$SHARED_STORAGE/logs"

if [ ! -f "$SHARED_ENV" ]; then
  if [ -f "$CURRENT_LINK/.env" ]; then
    log "Bootstrapping shared .env from current release"
    cp -p "$CURRENT_LINK/.env" "$SHARED_ENV"
  elif [ -f "$DEPLOY_ROOT/.env" ]; then
    log "Bootstrapping shared .env from legacy project root"
    cp -p "$DEPLOY_ROOT/.env" "$SHARED_ENV"
  else
    printf '[deploy] Missing %s. Create it on the server before deploying.\n' "$SHARED_ENV" >&2
    exit 1
  fi
fi

if [ -d "$CURRENT_LINK/storage" ] && [ ! -L "$CURRENT_LINK/storage" ]; then
  log "Copying existing current storage into shared storage without deleting files"
  rsync -a --ignore-existing "$CURRENT_LINK/storage/" "$SHARED_STORAGE/"
fi

# =============================================================================
#  PHASE 1 — Prepare the new release. The live site is untouched throughout.
# =============================================================================

rm -rf "$RELEASE_PATH/storage"
ln -sfn "$SHARED_STORAGE" "$RELEASE_PATH/storage"
ln -sfn "$SHARED_ENV" "$RELEASE_PATH/.env"
mkdir -p "$RELEASE_PATH/bootstrap/cache" "$VIEW_COMPILED_PATH"
chmod -R ug+rwX "$RELEASE_PATH/bootstrap/cache" "$SHARED_STORAGE" || true

cd "$RELEASE_PATH"

# Drop any compiled artefacts that rode along in the upload. Deliberately NOT
# `optimize:clear`: that would also flush the shared application cache, which
# the currently live release is serving from.
log "Removing stale compiled artefacts from the new release"
rm -f bootstrap/cache/config.php bootstrap/cache/routes-v7.php bootstrap/cache/events.php

log "Ensuring public storage link"
"$PHP_BIN" artisan storage:link --ansi

log "Caching configuration (this bakes VIEW_COMPILED_PATH into the release)"
"$PHP_BIN" artisan config:cache --ansi

log "Caching events"
"$PHP_BIN" artisan event:cache --ansi

log "Compiling Blade views into $VIEW_COMPILED_PATH"
"$PHP_BIN" artisan view:cache --ansi

log "Regenerating sitemap with production URLs"
"$PHP_BIN" artisan seo:generate-sitemap --ansi

# =============================================================================
#  HOLD GATE — prepare-only mode.
#
#  When $DEPLOY_ROOT/HOLD_SWITCH exists, the release is built and verified but
#  NOTHING that touches production happens: no migrations, no symlink swap. The
#  deploy exits successfully, leaving a ready release that a later run (after
#  the file is removed) can cut over to in seconds.
#
#  It is a file on the server rather than a workflow input on purpose: whoever
#  controls production controls the gate, and a push cannot bypass it.
# =============================================================================

if [ -f "$DEPLOY_ROOT/HOLD_SWITCH" ]; then
  log "HOLD_SWITCH present — prepare-only mode"
  log "Release is built and cached at: $RELEASE_PATH"
  log "Migrations SKIPPED. Symlink NOT switched. Production untouched."
  log "Live target still points at: $(readlink -f "$CURRENT_LINK" 2>/dev/null || echo "$CURRENT_LINK")"
  log "To complete: remove $DEPLOY_ROOT/HOLD_SWITCH and re-run the deployment."
  exit 0
fi

# =============================================================================
#  PHASE 2 — Schema. Runs against the live database while old code serves.
# =============================================================================

if [ "$MAINTENANCE_MODE" = "true" ]; then
  if [ -f "$CURRENT_LINK/artisan" ]; then
    log "MAINTENANCE_MODE=true — taking the site down for a consistent cutover"
    "$PHP_BIN" "$CURRENT_LINK/artisan" down --retry=60 --ansi
    MAINTENANCE_ENTERED=1
  fi
else
  log "Zero-downtime mode: the site stays up for the whole deployment"
fi

if [ "$RUN_MIGRATIONS" = "true" ]; then
  # --isolated takes a lock so two concurrent deploys cannot both migrate.
  log "Running migrations with --force --isolated"
  "$PHP_BIN" artisan migrate --force --isolated --ansi
else
  log "Skipping migrations because RUN_MIGRATIONS is not true"
fi

# =============================================================================
#  PHASE 3 — Atomic cutover.
# =============================================================================

# A real directory cannot be replaced by `mv -Tf` (mv refuses to overwrite a
# non-empty directory), and treating it as PREVIOUS_RELEASE would later point
# the symlink at itself. Migrating a legacy flat deployment is a deliberate
# one-time human step, not something a push should perform.
if [ -e "$CURRENT_LINK" ] && [ ! -L "$CURRENT_LINK" ]; then
  printf '[deploy] %s is a real directory, not a symlink.\n' "$CURRENT_LINK" >&2
  printf '[deploy] Move it aside and create the symlink once, by hand, before deploying.\n' >&2
  exit 1
fi

if [ -L "$CURRENT_LINK" ] && [ -f "$CURRENT_LINK/artisan" ]; then
  PREVIOUS_RELEASE="$(readlink -f "$CURRENT_LINK" || true)"
else
  log "No previous release linked; this is the first cutover"
fi

log "Switching current symlink"
# `ln` then `mv -Tf` because mv of a symlink onto a symlink is atomic at the
# filesystem level: no request can ever observe a missing `current`.
ln -sfn "$RELEASE_PATH" "$CURRENT_LINK.tmp"
mv -Tf "$CURRENT_LINK.tmp" "$CURRENT_LINK"
SWITCH_COMPLETED=1

refresh_php_workers

log "Restarting queue workers gracefully"
"$PHP_BIN" "$CURRENT_LINK/artisan" queue:restart --ansi || true

if [ "$MAINTENANCE_ENTERED" = "1" ]; then
  log "Leaving maintenance mode"
  "$PHP_BIN" "$CURRENT_LINK/artisan" up --ansi
  MAINTENANCE_ENTERED=0
fi

# =============================================================================
#  PHASE 4 — Verify, and roll back automatically if the new release is unwell.
# =============================================================================

if [ -n "${PRODUCTION_HEALTH_URL:-}" ]; then
  log "Checking health URL"
  health_ok=0
  for attempt in 1 2 3 4 5; do
    if curl --fail --silent --show-error --max-time 15 "$PRODUCTION_HEALTH_URL" >/dev/null; then
      health_ok=1
      log "Health check passed on attempt $attempt"
      break
    fi
    log "Health check attempt $attempt failed; retrying in 3s"
    sleep 3
  done

  if [ "$health_ok" != "1" ]; then
    printf '[deploy] Health check failed after the switch; rolling back.\n' >&2
    exit 1
  fi
else
  log "PRODUCTION_HEALTH_URL not set — skipping verification (rollback cannot trigger automatically)"
fi

# =============================================================================
#  PHASE 5 — Prune. Only after the new release is proven healthy.
# =============================================================================

log "Cleaning old releases, keeping $KEEP_RELEASES"
mapfile -t releases < <(find "$RELEASES_DIR" -mindepth 1 -maxdepth 1 -type d -printf '%T@ %p\n' | sort -rn | awk '{ $1=""; sub(/^ /, ""); print }')

if [ "${#releases[@]}" -gt "$KEEP_RELEASES" ]; then
  current_realpath="$(readlink -f "$CURRENT_LINK")"
  for old_release in "${releases[@]:$KEEP_RELEASES}"; do
    old_realpath="$(readlink -f "$old_release")"
    case "$old_realpath" in
      "$RELEASES_DIR"/*)
        if [ "$old_realpath" != "$current_realpath" ]; then
          log "Removing old release $old_release"
          rm -rf "$old_release"
        fi
        ;;
      *)
        log "Skipping unexpected release path $old_release"
        ;;
    esac
  done
fi

log "Deployment complete with no downtime"
log "Active release: $(readlink -f "$CURRENT_LINK")"
