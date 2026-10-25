# Operations

How to run production day to day. Paths assume the server layout in
[DEPLOYMENT.md](DEPLOYMENT.md#server-layout). Commands run as the `speeda`
user from `/home/speeda/htdocs/speeda_live`. The app timezone is
`America/Toronto` (`config/app.php`).

## Queue and scheduler

Both run from the `speeda` user's crontab. There is no Supervisor and no root
access is needed:

```cron
* * * * * cd /home/speeda/htdocs/speeda_live && php artisan schedule:run
* * * * * flock -n /home/speeda/deploy/queue.lock -c "cd /home/speeda/htdocs/speeda_live && php artisan queue:work --stop-when-empty --max-time=55 --tries=3"
```

- The paths go through the `speeda_live` symlink, so cron keeps working after
  every release swap.
- `flock` stops two workers from overlapping. `--max-time=55` makes each
  worker exit before the next minute starts.
- The queue uses the `database` driver (`jobs` table). Failed jobs go to
  `failed_jobs`.

**What is queued.** Every email extends `App\Mail\TemplatedEmail`, which is
`ShouldQueue`: client welcome and review-milestone emails, provider welcome,
"build your reputation" and review-received emails. Admin broadcasts are also
queued, as one `SendProviderBroadcastEmail` job per recipient. **If no worker
runs, these pile up silently in `jobs` and nothing reports an error.**

```bash
php artisan queue:failed          # list failures
php artisan queue:retry all       # retry them
php artisan queue:restart         # done automatically on each deploy
```

**Scheduled tasks** (`routes/console.php` and `bootstrap/app.php`):

| When | Task |
| --- | --- |
| daily 00:00 | delete expired `admin_notifications` |
| daily 03:00 | `seo:generate-sitemap` writes `public/sitemap.xml` inside the live release |
| daily 10:00 | `providers:send-journey-emails` queues the provider "build your reputation" email and its reminders |

To check: `php artisan schedule:list`. The journey run can be previewed with
`php artisan providers:send-journey-emails --dry-run`.

## Trusted proxies

CloudPanel puts one nginx in front of another, so PHP sees every request
coming from `127.0.0.1`. `config/trustedproxy.php` trusts that loopback hop
(`TRUSTED_PROXIES`, default `127.0.0.1,::1`). Laravel then reads the visitor's
real IP and scheme from `X-Forwarded-*`. If this is wrong:

- every guest shares **one** rate-limit bucket (login, register, contact
  reveal and offer comments get throttled site-wide);
- unique-visitor analytics count one visitor;
- `isSecure()` is false, so HSTS is never sent.

Trusting loopback only is safe, because nothing outside the machine can
connect from `127.0.0.1`. If the site goes behind Cloudflare or a load
balancer, add its ranges to `TRUSTED_PROXIES`, or restore the IP in nginx with
`set_real_ip_from` / `real_ip_header`. Do not use `*`.

**Check after a deploy.** Tinker is a dev dependency and is not installed on
the server. `ops:preflight` is read-only: it prints the last 24h of page views
and distinct IPs, and warns when every visitor shares one address.

```bash
php artisan ops:preflight
```

## Backups

What the repo does today:

- `pull-deploy.sh` dumps the database (`clpctl db:export`) to
  `/home/speeda/deploy/backups/<db>_<timestamp>.sql.gz` **only** when it runs
  migrations. It also checks that the dump is not empty and passes `gzip -t`.
  These dumps are never deleted, so prune them by hand now and then.

What the repo does **not** do:

- No scheduled database backup.
- No backup of uploads (`/home/speeda/deploy/shared/storage/app/public`).
- No copy of `shared/.env` anywhere.

Set these up in CloudPanel's backup settings, or with an off-server job, and
test a restore. Uploaded images are not in git, so losing `shared/storage`
loses them for good.

Restoring a dump:

```bash
gunzip -c /home/speeda/deploy/backups/<file>.sql.gz | mysql -u <user> -p speedadb
```

## Logs

- App log: `storage/logs/laravel.log`, which is
  `/home/speeda/deploy/shared/storage/logs/laravel.log`. The default
  `LOG_STACK=single` writes one file that is never rotated. Switch to
  `LOG_STACK=daily`, or rotate it with logrotate.
- Use `LOG_LEVEL=warning` on the server, not `error`. Rejected uploads,
  failed broadcast sends and failed Google sign-ins are logged at warning
  level.
- nginx logs are in CloudPanel's site log view.

## Caches

- The application cache uses the `database` store. Admins can flush it from
  `/admin` ("Clear cache").
- On the server, **never run `php artisan optimize:clear`**. It clears the
  shared application cache the live site is using, and config/route/view
  caches are rebuilt per release anyway.
- Category, location and dashboard caches are cleared automatically by the
  admin actions that change them.

## Routine commands

| Situation | Command |
| --- | --- |
| Provider ratings look wrong | `php artisan providers:recalculate-ratings` |
| Profile completion % looks wrong | `php artisan providers:backfill-profile-completion` |
| Sitemap is stale | `php artisan seo:generate-sitemap` |
| Google sign-in "redirect_uri_mismatch" | `php artisan speeda:google-check` |
| Is the config still production-safe? | `php artisan deploy:verify` |
| What emails are waiting, failed or half-sent? (read-only) | `php artisan ops:preflight` |
| Stop the next deploy from going live | `touch /home/speeda/deploy/HOLD_SWITCH` |

## Security notes for operators

- `/translations` (the lang-file editor) needs an admin login and is
  rate-limited. Its "publish" button rewrites the `lang/` files of the
  **current release only**, and the next deploy replaces them with the files
  from git. Commit translation changes to the repo instead.
- `AdminUserSeeder` writes a fixed admin password. Do not run `db:seed` in
  production unless you then change that password at once.
- `config('auth.admins')` grants admin to every email it lists, whatever the
  user's DB role is. It comes from `ADMINS` (comma-separated), and falls back
  to `ADMIN_EMAIL` when that is unset. Keep it minimal.
