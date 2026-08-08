# GitHub Actions CI/CD Deployment

This workflow deploys Speeda.ca with a release directory structure and **zero
downtime**. It does not package `.env`, does not commit secrets, and does not
run data seeders or destructive database commands.

## GitHub Configuration

Create these repository secrets:

- `SSH_HOST`
- `SSH_USER`
- `SSH_PORT`
- `SSH_PRIVATE_KEY`
- `PROJECT_PATH`

Create these repository variables:

| Variable | Default | Purpose |
| --- | --- | --- |
| `DEPLOY_BRANCH` | `main` | Branch that triggers a production deploy. |
| `KEEP_RELEASES` | `5` | Previous releases retained for rollback. |
| `RUN_MIGRATIONS` | `false` | Set to `true` only for a reviewed schema deployment. |
| `MAINTENANCE_MODE` | `false` | Set to `true` only for a destructive migration (see below). |
| `PHP_BIN` | `php` | PHP binary on the server. |
| `PRODUCTION_HEALTH_URL` | — | e.g. `https://speeda.ca/up`. **Set this** — automatic rollback depends on it. |

Use a repository variable, not a secret, for `DEPLOY_BRANCH` because GitHub
evaluates it before the deployment job starts. Branch names are not sensitive.

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

Nginx must point at **`PROJECT_PATH/current/public`** — the symlink, never a
specific release. Before the first deployment, create `PROJECT_PATH/shared/.env`
directly on the server. Do not store production `.env` values in GitHub or in
the repository.

Copy `deploy/nginx.example.conf`. Two settings in it are load-bearing for
zero-downtime; read the comments there before changing them.

## What CI Runs

- Composer validation.
- PHP dependency install.
- Node dependency install.
- Laravel config cache validation.
- Laravel route listing validation.
- Blade view cache validation.
- Vite production asset build.
- The full Laravel test suite (Unit, Feature, Security).

The workflow intentionally does not run `route:cache` because the current app
has closure routes in `routes/web.php`.

## Zero-Downtime Deployment

The site serves traffic for the entire deployment. There is no `artisan down`.
Three things make that safe — all three are in
`deploy/remote-release-activate.sh`:

**1. Per-release compiled views.** `config/view.php` defaults the compiled Blade
path to a shared temp directory. If releases shared it, caching the new
release's views would overwrite the templates the *old* release is still
rendering, producing live errors. The script exports `VIEW_COMPILED_PATH` into
each release's own `bootstrap/cache/views`, and `config:cache` bakes that value
into the release's config cache so it also applies at runtime.

**2. Nothing shared is cleared.** The old release stays warm the whole time. The
script never runs `optimize:clear`, which would flush the shared application
cache the live site is using; it deletes only that release's own compiled
config, events, and routes.

**3. Opcache sees the new code immediately.** With
`fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name`, each release
resolves to a distinct real path, so PHP's opcache keys differ per release and
the swap takes effect instantly. The script additionally attempts a graceful
`systemctl reload php*-fpm` as a fallback; it is best-effort and never fails the
deploy.

### Order of operations

1. Link shared `.env` and `storage` into the new release.
2. Build config, event, and view caches inside the new release.
3. Run `storage:link`.
4. Run migrations when `RUN_MIGRATIONS=true` — **while the old code still
   serves**.
5. Swap the `current` symlink atomically (`mv -Tf`).
6. Refresh PHP workers, restart queue workers gracefully.
7. Health-check the live URL, retrying 5 times.
8. Roll back automatically if the health check fails.
9. Prune old releases only after the new one is proven healthy.

### When to break zero-downtime

Step 4 runs migrations against the live database while the previous release is
still executing. That is safe **only for backward-compatible migrations**:

- Creating tables
- Adding nullable columns
- Relaxing a constraint
- Adding indexes

It is **not** safe for dropping or renaming a column still referenced by the old
code, changing a column's type, or backfilling that rewrites rows the old code
reads. For those, set the `MAINTENANCE_MODE` repository variable to `true` for
that one deploy. It takes the site down before migrating and brings it back up
after the cutover, trading a short outage for a consistent one. Set it back to
`false` afterwards.

## Rollback

Rollback is automatic when `PRODUCTION_HEALTH_URL` is set and the new release
fails its health check. To roll back manually:

```bash
cd "$PROJECT_PATH"
ls -1dt releases/*
ln -sfn "$PROJECT_PATH/releases/<previous-release>" current.tmp
mv -Tf current.tmp current
sudo systemctl reload php8.4-fpm
php current/artisan queue:restart
```

Do not run `optimize:clear` during a rollback — the previous release still has
its own valid caches, and clearing shared state would slow the whole site down
for no benefit. Do not run seeders or destructive database commands.

Note that a rollback reverts **code only**. Migrations are not reversed; this is
why the backward-compatibility rule above matters — the previous release must be
able to run against the newer schema.

## Queue Workers

Several features (including admin broadcast emails) depend on a running queue
worker. Configure Supervisor from `deploy/supervisor.example.conf`. Without a
worker, queued jobs accumulate silently in the `jobs` table with no visible
error.
