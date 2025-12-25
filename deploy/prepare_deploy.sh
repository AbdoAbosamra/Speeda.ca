#!/usr/bin/env bash
set -euo pipefail

# prepare_deploy.sh
# Usage: ./prepare_deploy.sh <project_name>
# Runs locally (or on build machine) to build assets, install prod deps,
# and create a tar.gz ready to upload to the VPS.

PROJECT_NAME=${1:-your-project}
DIST_DIR="deploy_package"
ARCHIVE_NAME="${PROJECT_NAME}_$(date +%F_%H%M%S).tar.gz"

echo "[1/6] Checking environment..."
if ! command -v composer >/dev/null 2>&1; then
  echo "Composer not found in PATH. Install composer or run this on a machine with composer." >&2
  exit 1
fi

echo "[2/6] Installing PHP dependencies (production)..."
composer install --no-dev --optimize-autoloader

if [ -f package.json ]; then
  echo "[3/6] Installing node modules and building assets..."
  if ! command -v npm >/dev/null 2>&1; then
    echo "npm not found. Skipping frontend build. You can build assets locally and re-run."
  else
    npm ci
    # prefer common build scripts
    if npm run build --silent; then
      echo "npm build finished"
    elif npm run production --silent; then
      echo "npm production finished"
    else
      echo "No known build script succeeded — check package.json scripts." >&2
    fi
  fi
fi

echo "[4/6] Preparing package directory: $DIST_DIR"
rm -rf "$DIST_DIR"
mkdir -p "$DIST_DIR"

echo "[5/6] Copying files (excluding vendor, node_modules, .git, storage, .env, deploy)"
rsync -a --exclude 'vendor' --exclude 'node_modules' --exclude '.git' --exclude 'storage' --exclude '.env' --exclude 'deploy' ./ "$DIST_DIR/"

echo "[6/6] Creating archive $ARCHIVE_NAME"
tar -czf "$ARCHIVE_NAME" -C "$DIST_DIR" .

echo "Package created: $ARCHIVE_NAME"
echo "Next: upload the archive to your VPS and extract under /var/www/<your-project>"

exit 0
