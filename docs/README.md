# Speeda: local setup and running

Speeda (speeda.ca) is a directory of service providers in Canada. Clients browse
providers by category and city, reveal their contact details, and leave
reviews. Providers manage their own profile and gallery. Admins moderate
everything from `/admin`. The public site is in English, Arabic (RTL) and
French.

| Doc | What it covers |
| --- | --- |
| [README.md](README.md) | Local setup, running, tests (this page) |
| [ARCHITECTURE.md](ARCHITECTURE.md) | Domains, request pipeline, key flows, where code lives |
| [DEPLOYMENT.md](DEPLOYMENT.md) | Production server, the pull-based deploy, rollback |
| [OPERATIONS.md](OPERATIONS.md) | Queue, scheduler, backups, trusted proxies, logs, routine commands |
| [CHANGELOG.md](CHANGELOG.md) | What changed and when, from git history and the dated audits |

Older reports live in `_Project_Docs/archive/`. They are kept for history only
and are not accurate for the current code.

## Requirements

- **PHP 8.4.** `composer.json` says `^8.2`, but the locked Symfony 8 packages
  need 8.4, and CI runs 8.4. Extensions: bcmath, ctype, dom, fileinfo, gd,
  intl, mbstring, pdo_mysql, pdo_sqlite, tokenizer, xml, zip.
- **Composer 2.**
- **Node 20 or newer** for Vite 7. CI uses Node 22.
- **MySQL** (utf8mb4). The tests use in-memory SQLite and need no database.

## First-time setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`. Create the
database:

```sql
CREATE DATABASE speeda CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then:

```bash
php artisan migrate
php artisan db:seed
php artisan storage:link
```

What `db:seed` does (`database/seeders/DatabaseSeeder.php`):

- Creates or updates the admin account (`AdminUserSeeder`).
- Seeds the category tree (sections, categories, and the "Others" section)
  only when the `categories` table is empty.
- Seeds locations (cities).

Optional test data: `php artisan db:seed --class=TestDataSeeder` adds about 60
fake users on `@test-speeda.ca`. `php artisan test-data:cleanup` removes them.

`database/sql/categories_seed.sql` is another way to load categories. Older
docs say it is required. It is not, because `db:seed` already does this. The
SQL file also **deletes every existing category** before it inserts, so use
it only on an empty database.

`storage:link` is required. Without it, uploaded images (profile photos,
gallery, offers) return 404. Uploads are not in git, so a fresh clone has no
images.

### Admin login

Type `admin` in the login field. `LoginRequest` maps it to
`config('auth.admin_email')` (`ADMIN_EMAIL`, default `admin@speeda.com`). The
local password is set in `database/seeders/AdminUserSeeder.php`. Change it
anywhere other than your own machine.

### Google sign-in (optional)

Set `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET`. If either one is empty,
the Google buttons are hidden and the `/auth/google/*` routes return 404
(`EnsureGoogleAuthEnabled`). `php artisan speeda:google-check` prints the
exact redirect URI the app sends, so you can match it in the Google console.

## Running

Everything in one terminal (web server, queue listener, Vite):

```bash
composer dev
```

Or run each one separately:

```bash
php artisan serve          # http://127.0.0.1:8000
php artisan queue:listen   # emails are queued; without this they wait in the jobs table
npm run dev                # Vite on port 5174 (strictPort)
```

The Vite dev server is pinned to port **5174**, not 5173 (see
`vite.config.js`). If `npm run dev` crashes, delete `public/hot`. Otherwise
the app keeps loading CSS from a dev server that is not running.

To build assets once without the dev server: `npm run build`.

### "419 Page Expired" on every form

The session is not being saved. Sessions are stored in the database
(`SESSION_DRIVER=database`) and encrypted. Check that migrations ran (they
create the `sessions` table). As a local workaround, set `SESSION_DRIVER=file`,
then run `php artisan config:clear`.

## Tests

```bash
php artisan test      # or: composer test (clears config first)
```

- Suites: Unit, Feature, Security (`phpunit.xml`). They run on in-memory
  SQLite with array cache, session and mail, and `LOG_CHANNEL=null`.
- `PageHealthTest` opens every page in all three languages as a guest, a
  client, a provider and an admin. It fails on a 500 error, a raw translation
  key in the output, or a PHP file that starts with a BOM.
- `tests/Browser/*` are Dusk tests. `laravel/dusk` is not installed, so they do
  not run.
- CI (`.github/workflows/ci-cd.yml`) also runs:
  - `php check_translations.php`, which fails if a translation key used in
    `app/`, `resources/views` or `routes/` is missing in en, ar or fr.
  - `config:cache`, `route:list` and `view:cache`.
  - `npm run build`.
  - A check that at least 250 tests actually ran.

## Useful commands

| Command | Purpose |
| --- | --- |
| `php artisan seo:generate-sitemap` | Write `public/sitemap.xml` (always uses https URLs) |
| `php artisan providers:recalculate-ratings` | Re-sync each provider's stored rating from its approved reviews |
| `php artisan providers:backfill-profile-completion` | Recalculate profile completion % for every provider |
| `php artisan providers:send-journey-emails --dry-run` | Preview the provider emails that would go out today |
| `php artisan categories:populate-translations` | Fill missing ar/fr category names and descriptions |
| `php artisan categories:verify-translations` | Report categories that are missing translations |
| `php artisan deploy:verify` | Production config check (fails on purpose on a local `.env`) |

## Where to edit translations

The `lang/{en,ar,fr}/*.php` files hold the UI text. Category, post and offer
text is stored per language in database columns (`*_ar`, `*_fr`). City names
come from `lang/*/cities.php`. Admins can also
edit the lang files in the browser at `/translations` (admin login required,
rate-limited, see `config/translations.php`).
