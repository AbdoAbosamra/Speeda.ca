# Deployment

Speeda.ca deploys with **zero downtime** using immutable releases and an atomic
symlink swap. The transport is **pull-based**: the server fetches from GitHub
over HTTPS. CI never connects to the server.

## Why pull and not push

The original workflow SSH'd from a GitHub-hosted runner. It failed at
`Configure SSH` after exactly 5s — `ssh-keyscan`'s default timeout — meaning
packets were dropped rather than refused. `fail2ban` is active on the host and
`sshd` listens on `0.0.0.0:22` with `PermitRootLogin yes`, so it is probed
constantly and bans aggressively. Runner IPs rotate across ranges far too wide
to allowlist, which is why one run succeeded and the next two failed unchanged.

Inverting the direction removed the dependency instead of working around it:

- no inbound port has to be open to CI
- **no SSH private key needs to exist in GitHub secrets**
- runner IP churn stops mattering

## Pieces

| Piece | Role |
| --- | --- |
| `.github/workflows/ci-cd.yml` | Tests, then builds Vite assets and publishes them as a GitHub Release asset (`build-<sha>`) with a sha256 |
| `deploy/pull-deploy.sh` | Runs **on the server** as `speeda`. Fetches, builds, verifies, and (if allowed) cuts over |
| `$DEPLOY_ROOT/HOLD_SWITCH` | Server-side gate. While present, releases are prepared but production is never touched |

The server runs Node 18 and the Vite toolchain needs 20+, which is why assets
are built in CI rather than on the box.

## Server layout

```text
/home/speeda/deploy/
  releases/<sha12>-<timestamp>/    immutable; no .git, no node_modules, no tests
  shared/.env                      never in git
  shared/storage/                  uploads survive every deploy
  backups/
  HOLD_SWITCH

/home/speeda/htdocs/speeda_live -> releases/<active>     ← the atomic swap target
/home/speeda/htdocs/speeda.ca                            ← legacy flat dir, kept as fallback
```

CloudPanel's Document Root is `speeda_live/public`. Nothing in a deploy touches
CloudPanel or nginx, and no step needs `sudo`.

## Deploying

```bash
# on the server, as speeda
/home/speeda/deploy/pull-deploy.sh <commit-sha>

# with schema changes (takes and verifies a backup first)
RUN_MIGRATIONS=true /home/speeda/deploy/pull-deploy.sh <commit-sha>
```

`pull-deploy.sh` is executed from the **server's copy**, not from the fetched
source. Update it on the server when it changes in the repo.

### What makes it zero-downtime

Everything expensive happens inside the new release directory, which nothing is
serving: dependency install, config/event/view caches, sitemap generation.
Only then does the symlink move, via `ln -sfn` + `mv -Tf` — atomic at the
filesystem level, so no request can observe a missing path.

Three details carry the guarantee:

1. **Compiled views are per-release.** The shared `.env` sets `TMPDIR` inside
   `storage`, so without `VIEW_COMPILED_PATH` every release would compile Blade
   into the same shared directory and overwrite the templates the *live*
   release is rendering. `config:cache` bakes the override in so it also applies
   at runtime.
2. **Nothing shared is cleared.** `optimize:clear` is never run: it would flush
   the application cache the live site is using.
3. **Opcache follows the swap.** `opcache.validate_timestamps=1`,
   `revalidate_freq=2` and `pm=ondemand` with a 10s idle timeout mean workers
   turn over within seconds and pick up the new path.

### Migrations

Migrations run **before** the swap, while the old code still serves. That is
only safe for backward-compatible changes: new tables, new nullable columns,
relaxed constraints. Dropping or renaming a column the old code still reads, or
rewriting rows it depends on, needs a planned window instead.

A database dump is taken first and rejected unless it is non-empty and passes
`gzip -t`.

## Verifying a deploy

```bash
readlink -f /home/speeda/htdocs/speeda_live        # active release (prefix = commit)
curl -o /dev/null -w '%{http_code}\n' https://speeda.ca/up                        # 200
curl -o /dev/null -w '%{http_code}\n' https://speeda.ca/this-page-does-not-exist  # 404
```

There is no `.git` in a release, so `git rev-parse` cannot tell you what is
deployed — read the symlink instead.

The 404 probe is not cosmetic. A catch-all exception handler once turned every
404 into a 302 to the homepage, and `pull-deploy.sh` asserts real 404 semantics
after cutover specifically so that class of regression rolls itself back.

## Rollback

```bash
ln -sfn /home/speeda/deploy/releases/<previous> /home/speeda/htdocs/speeda_live.tmp
mv -Tf /home/speeda/htdocs/speeda_live.tmp /home/speeda/htdocs/speeda_live
php /home/speeda/htdocs/speeda_live/artisan queue:restart
```

Seconds, and automatic if the post-cutover health check fails. As a last resort,
point CloudPanel's Document Root back at `speeda.ca/public`.

Rollback reverts **code only** — migrations are not reversed, which is why the
backward-compatibility rule matters: the previous release has to run against the
newer schema.

## Queue and scheduler

Both run from the `speeda` user's crontab — no root, no Supervisor:

```cron
* * * * * cd /home/speeda/htdocs/speeda_live && php artisan schedule:run
* * * * * flock -n /home/speeda/deploy/queue.lock -c "cd /home/speeda/htdocs/speeda_live && php artisan queue:work --stop-when-empty --max-time=55 --tries=3"
```

`flock` prevents overlap; `--max-time=55` guarantees the worker exits before the
next minute. Paths go through the `speeda_live` symlink, so cron never needs
updating when a release changes.

Queued work includes provider journey emails and admin broadcasts. **Without a
worker they accumulate silently in the `jobs` table with no visible error.**
