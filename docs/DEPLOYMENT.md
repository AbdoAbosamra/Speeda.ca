# Deployment

Production is **speeda.ca** on a CloudPanel server. Each deploy is an
immutable release directory, and the site switches to it with an atomic
symlink swap, so there is no downtime. The deploy is **pull-based**: the server
downloads the code and the prebuilt assets from GitHub over HTTPS. CI never
connects to the server.

For the reasoning behind each step (why pull instead of push, why compiled
views are per release, why there is a 404 probe), see
[`deploy/GITHUB_ACTIONS_DEPLOYMENT.md`](../deploy/GITHUB_ACTIONS_DEPLOYMENT.md),
which sits next to the scripts it describes. This page is the runbook.

## Pieces

| Piece | Where | Role |
| --- | --- | --- |
| `.github/workflows/ci-cd.yml` | GitHub | On every push: tests, translation check, Vite build. On pushes to `main` (or `vars.DEPLOY_BRANCH`), it also publishes `public/build` as the GitHub Release asset `build-<sha>` with a `.sha256` file. |
| `deploy/pull-deploy.sh` | server copy at `/home/speeda/deploy/pull-deploy.sh` | Runs on the server as the `speeda` user. Fetches, builds, verifies, migrates (optional), cuts over, health-checks, prunes. |
| `/home/speeda/deploy/HOLD_SWITCH` | server | While this file exists, releases are built and verified, but production is not switched. |

The server runs Node 18, and Vite 7 needs Node 20 or newer. That is why the
assets are built in CI. `pull-deploy.sh` refuses to deploy a commit that has
no published `build-<sha>` asset.

## Server layout

```text
/home/speeda/deploy/
  pull-deploy.sh                   the copy that actually runs (update it by hand)
  releases/<sha12>-<timestamp>/    immutable; no .git, node_modules, tests
  shared/.env                      production env, never in git
  shared/storage/                  uploads and logs, survives every deploy
  backups/                         pre-migration DB dumps
  HOLD_SWITCH                      optional gate
  queue.lock                       flock file for the queue cron

/home/speeda/htdocs/speeda_live -> deploy/releases/<active>   (CloudPanel document root: speeda_live/public)
/home/speeda/htdocs/speeda.ca                                (old flat directory, kept as a last-resort fallback)
```

Nothing in a deploy touches nginx or CloudPanel, and no step needs `sudo`.
The script keeps the last 5 releases (`KEEP_RELEASES`).

## One-time server setup

1. **Shared env.** Create `/home/speeda/deploy/shared/.env`. `deploy:verify`
   (`app/Console/Commands/VerifyProduction.php`) refuses to cut over unless
   all of these hold:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://speeda.ca`. It must be a public https URL.
   - `SESSION_ENCRYPT=true`
   - Secure session cookie. `SESSION_SECURE_COOKIE` defaults to true when
     `APP_URL` is https.
   - `ANALYTICS_SALT` set, or a valid `APP_KEY` (used as the fallback).
   - Compiled views inside the release. The script sets `VIEW_COMPILED_PATH`
     itself.
   - PHP `upload_max_filesize` ≥ 10 MB, and `post_max_size` ≥ upload size +
     8 MB.
   - `storage/app/public` is writable.
   - The database is reachable.

   Also set: DB credentials (`DB_DATABASE=speedadb` is what the backup step
   assumes; override `DB_NAME` if it differs), `MAIL_*`,
   `QUEUE_CONNECTION=database`, `LOG_LEVEL=warning`, and optionally
   `GOOGLE_CLIENT_ID/SECRET` and `TRUSTED_PROXIES`.
2. **PHP limits.** Gallery images may be up to 10 MB. In CloudPanel's PHP
   settings (or `php.ini`), set at least `upload_max_filesize = 16M` and
   `post_max_size = 64M`. Set nginx `client_max_body_size` to the same value
   as `post_max_size`. Otherwise nginx answers 413 before PHP sees the
   request. CI runs with these same values.
3. **Opcache.** Keep `opcache.validate_timestamps=1` and `revalidate_freq=2`,
   so PHP-FPM workers pick up the new release path within seconds of the swap.
4. **Cron** for the scheduler and the queue: see
   [OPERATIONS.md](OPERATIONS.md#queue-and-scheduler). The deploy does not
   install it.
5. **Copy `deploy/pull-deploy.sh`** to `/home/speeda/deploy/` and make it
   executable. The server always runs its own copy, not the one in the
   fetched release. **Re-copy it whenever it changes in the repo.**

## Deploying a commit

1. Push to `main`. Wait for both CI jobs: "CI checks" and "Publish build
   artifact". The run summary prints the exact command.
2. On the server, as `speeda`:

   ```bash
   /home/speeda/deploy/pull-deploy.sh <commit-sha>

   # if the commit adds migrations:
   RUN_MIGRATIONS=true /home/speeda/deploy/pull-deploy.sh <commit-sha>
   ```

What the script does, in order:

1. Downloads the source tarball and `build-<sha>/build.tar.gz` and checks the
   asset's sha256.
2. Stages the release (rsync without `.git`, `tests`, `node_modules`,
   `storage`, `.env*`) and runs `composer install --no-dev`.
3. Links `shared/storage` and `shared/.env` into the release and runs
   `storage:link`, `config:cache`, `event:cache`, `route:cache` and
   `view:cache`, all inside the new release.
4. Runs `php artisan deploy:verify` and stops if it fails.
5. With `RUN_MIGRATIONS=true` and pending migrations: dumps the database with
   `clpctl db:export` to `backups/`, rejects an empty or corrupt dump
   (`gzip -t`), then runs `migrate --force --isolated`.
6. **Refuses to cut over while any migration is still pending**, including
   when the status check itself fails. Without `RUN_MIGRATIONS=true` on a
   commit with new migrations, the deploy stops here and the live site is
   left alone.
7. Runs `seo:generate-sitemap` against production data.
8. If `HOLD_SWITCH` exists, stops with the release prepared.
9. Swaps `speeda_live` with `ln -sfn` + `mv -Tf` and runs `queue:restart`.
10. Health checks: `https://speeda.ca/up` must return 200 (5 tries), and an
    unknown path must return a real 404. **If either fails, the script rolls
    back to the previous release automatically.**
11. Prunes old releases, never the active one. A pruning failure cannot undo
    a healthy deploy.

### Migration rule

Migrations run while the **old** release is still serving traffic, and a
rollback restores code only, not the schema. So every migration must be
backward-compatible: new tables, new nullable columns, looser constraints.
Dropping or renaming a column the old code still reads needs a separate
planned window.

## Verifying a deploy

```bash
readlink -f /home/speeda/htdocs/speeda_live      # the release prefix is the commit
curl -o /dev/null -w '%{http_code}\n' https://speeda.ca/up                         # 200
curl -o /dev/null -w '%{http_code}\n' https://speeda.ca/this-page-does-not-exist   # 404
```

Releases have no `.git`, so read the symlink to see what is deployed. After
the first deploy, also run the real-visitor-IP check in
[OPERATIONS.md](OPERATIONS.md#trusted-proxies).

## Rollback

```bash
ln -sfn /home/speeda/deploy/releases/<previous> /home/speeda/htdocs/speeda_live.tmp
mv -Tf /home/speeda/htdocs/speeda_live.tmp /home/speeda/htdocs/speeda_live
php /home/speeda/htdocs/speeda_live/artisan queue:restart
```

Last resort: point CloudPanel's document root back at `speeda.ca/public`.
Rollback does not reverse migrations. To undo data changes, restore the dump
from `backups/`.

## Other server layouts (not production)

`deploy/nginx.example.conf`, `deploy/supervisor.example.conf` and
`deploy/prepare_deploy.sh` are for a generic VPS (`/var/www/<project>/current`,
Supervisor workers, tarball upload). Production does not use them. Their
useful parts are already in this page:

- The PHP and nginx upload limits.
- `fastcgi_param SCRIPT_FILENAME $realpath_root...`, so that a symlink swap
  is picked up.

The old Arabic walkthrough for that layout is archived at
`_Project_Docs/archive/DEPLOY_README.md`.
