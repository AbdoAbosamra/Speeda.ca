# GitHub Actions CI/CD Deployment

This workflow deploys Speeda.ca with a release directory structure. It does not package `.env`, does not commit secrets, and does not run data seeders or destructive database commands.

## GitHub Configuration

Create these repository secrets:

- `SSH_HOST`
- `SSH_USER`
- `SSH_PORT`
- `SSH_PRIVATE_KEY`
- `PROJECT_PATH`

Create these repository variables:

- `DEPLOY_BRANCH`: production branch name. Current default in the workflow is `Full-VersionV3`.
- `KEEP_RELEASES`: optional, defaults to `5`.
- `RUN_MIGRATIONS`: optional, defaults to `false`. Set to `true` only for a reviewed schema deployment.
- `PHP_BIN`: optional, defaults to `php`.
- `PRODUCTION_HEALTH_URL`: optional, for example `https://speeda.ca/up`.

Use a repository variable, not a secret, for `DEPLOY_BRANCH` because GitHub evaluates it before the deployment job starts. Branch names are not sensitive.

## Server Layout

`PROJECT_PATH` should point to the deployment root:

```text
PROJECT_PATH/
  current -> releases/<active-release>
  releases/
  shared/
    .env
    storage/
```

Nginx should point to:

```text
PROJECT_PATH/current/public
```

Before the first deployment, create `PROJECT_PATH/shared/.env` directly on the server. Do not store production `.env` values in GitHub or in the repository.

## What CI Runs

- Composer validation.
- PHP dependency install.
- Node dependency install.
- Laravel config cache validation.
- Laravel route listing validation.
- Blade view cache validation.
- Vite production asset build.
- Laravel tests when `*Test.php` files exist.

The workflow intentionally does not run `route:cache` because the current app has closure routes in `routes/web.php`.

## What Production Deploy Runs

- Uploads a built release over SSH.
- Links shared `.env` and shared `storage`.
- Enters maintenance mode for existing releases.
- Clears stale Laravel caches.
- Runs `storage:link`.
- Runs migrations only when `RUN_MIGRATIONS=true`.
- Caches config, events, and views.
- Atomically switches the `current` symlink.
- Restarts queue workers gracefully.
- Exits maintenance mode.
- Keeps the newest releases for rollback.

## Rollback

SSH to the server and switch `current` back to a previous release:

```bash
cd "$PROJECT_PATH"
ls -1dt releases/*
ln -sfn "$PROJECT_PATH/releases/<previous-release>" current.tmp
mv -Tf current.tmp current
php current/artisan optimize:clear
php current/artisan config:cache
php current/artisan event:cache
php current/artisan view:cache
php current/artisan up
php current/artisan queue:restart
```

Do not run seeders or destructive database commands during rollback.
