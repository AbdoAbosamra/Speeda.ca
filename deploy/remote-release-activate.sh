#!/usr/bin/env bash
set -Eeuo pipefail

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
DEPLOY_ROOT="${PROJECT_PATH%/}"
RELEASES_DIR="$DEPLOY_ROOT/releases"
SHARED_DIR="$DEPLOY_ROOT/shared"
CURRENT_LINK="$DEPLOY_ROOT/current"
RELEASE_PATH="$RELEASES_DIR/$RELEASE_NAME"
SHARED_ENV="$SHARED_DIR/.env"
SHARED_STORAGE="$SHARED_DIR/storage"
MAINTENANCE_ENTERED=0
SWITCH_COMPLETED=0
PREVIOUS_RELEASE=""

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
    if [ -f "$CURRENT_LINK/artisan" ]; then
      "$PHP_BIN" "$CURRENT_LINK/artisan" queue:restart --ansi || true
    fi
  fi
}

bring_app_up() {
  if [ "$MAINTENANCE_ENTERED" = "1" ]; then
    local target="$CURRENT_LINK"
    if [ -f "$target/artisan" ]; then
      log "Leaving maintenance mode after failure"
      "$PHP_BIN" "$target/artisan" up --ansi || true
    fi
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

log "Starting release activation"
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

rm -rf "$RELEASE_PATH/storage"
ln -sfn "$SHARED_STORAGE" "$RELEASE_PATH/storage"
ln -sfn "$SHARED_ENV" "$RELEASE_PATH/.env"
mkdir -p "$RELEASE_PATH/bootstrap/cache"
chmod -R ug+rwX "$RELEASE_PATH/bootstrap/cache" "$SHARED_STORAGE" || true

if [ -f "$CURRENT_LINK/artisan" ]; then
  PREVIOUS_RELEASE="$(readlink -f "$CURRENT_LINK" || true)"
  log "Entering maintenance mode"
  "$PHP_BIN" "$CURRENT_LINK/artisan" down --retry=60 --ansi
  MAINTENANCE_ENTERED=1
else
  log "No current release found; skipping maintenance mode for first deploy"
fi

cd "$RELEASE_PATH"

log "Clearing stale framework caches"
"$PHP_BIN" artisan optimize:clear --ansi

log "Ensuring public storage link"
"$PHP_BIN" artisan storage:link --ansi

if [ "$RUN_MIGRATIONS" = "true" ]; then
  log "Running migrations with --force --isolated"
  "$PHP_BIN" artisan migrate --force --isolated --ansi
else
  log "Skipping migrations because RUN_MIGRATIONS is not true"
fi

log "Caching configuration"
"$PHP_BIN" artisan config:cache --ansi

log "Caching events"
"$PHP_BIN" artisan event:cache --ansi

log "Caching Blade views"
"$PHP_BIN" artisan view:cache --ansi

log "Switching current symlink"
ln -sfn "$RELEASE_PATH" "$CURRENT_LINK.tmp"
mv -Tf "$CURRENT_LINK.tmp" "$CURRENT_LINK"
SWITCH_COMPLETED=1

log "Restarting queue workers gracefully"
"$PHP_BIN" "$CURRENT_LINK/artisan" queue:restart --ansi || true

if [ "$MAINTENANCE_ENTERED" = "1" ]; then
  log "Leaving maintenance mode"
  "$PHP_BIN" "$CURRENT_LINK/artisan" up --ansi
  MAINTENANCE_ENTERED=0
fi

if [ -n "${PRODUCTION_HEALTH_URL:-}" ]; then
  log "Checking health URL"
  curl --fail --silent --show-error --max-time 15 "$PRODUCTION_HEALTH_URL" >/dev/null
fi

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

log "Deployment complete"
log "Active release: $(readlink -f "$CURRENT_LINK")"
