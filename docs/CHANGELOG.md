# Changelog

This changelog is built from git history and the two dated audits that were
checked against the code (`_Project_Docs/archive/AUDIT_2026-10-09.md`,
`_Project_Docs/archive/VERIFIED_AUDIT_2026-09-08.md`). Entries before July
2026 are summarized from archived reports and from commit subjects, which
were not descriptive at the time. They are less precise.

## Unreleased

In progress in the working tree, not yet committed:

- `config/trustedproxy.php`: trust CloudPanel's loopback proxy hop so the real
  visitor IP is used for rate limits, analytics and HSTS (`TRUSTED_PROXIES`).
- Branded error pages for 403, 419, 429 and 500.
- A provider gallery holds at most 4 images
  (`ServiceProvider::MAX_GALLERY_IMAGES`), enforced on every upload path.
- `AdminController` is being split into focused admin controllers.
- Docs consolidated into `docs/`. Old reports moved to `_Project_Docs/archive/`.

## 2026-10-13: Local Offers, security and SEO pass

Deploy with `RUN_MIGRATIONS=true` (five new migrations).

**Added**
- **Local Offers.** Admin-managed deals tied to real providers:
  - Public `/offers` list and offer pages, with a WhatsApp hand-off whose
    message names the offer.
  - Likes, moderated comments (honeypot plus per-minute and per-day limits)
    and favorites (`/my-favorites`).
  - Admin side: a 7-step editor, a comment queue, analytics and dashboard
    KPIs.
- **My account page** (`/profile`) in en/ar/fr. It used to return 500 for
  every client. Users can edit their name and email, change or set a password
  (Google-only accounts can create one), and delete the account. Admin
  accounts cannot be deleted here.
- `php artisan providers:recalculate-ratings`.

**Changed**
- Provider emails: only the welcome email and one "build your reputation"
  email (plus up to 3 reminders) remain. The step-by-step onboarding emails
  were retired.
- The admin panel is always English (`ForceAdminLocale`).
- Visitor tracking counts only real page views. Bots, JSON/AJAX requests and
  error pages are not counted.
- Provider profile views count once per visitor per 24 h. Bots are excluded.
- `pull-deploy.sh` refuses to cut over while any migration is pending.

**Security**
- `/translations` (lang-file editor) now requires an admin login and is
  rate-limited. Before this it was open to anyone.
- Search XSS fixed: page titles were printed unescaped inside JSON-LD, and one
  cached title served the payload to every search visitor.
- Blog content is sanitized with `AdminHtml::clean` on save and on render.
- Rate limits added to ratings, reviews, recommendations, comments, profile
  image upload, password confirm/change, account delete and password reset.
- New `Permissions-Policy` and `Cross-Origin-Opener-Policy` headers.
- The session cookie is `secure` by default when `APP_URL` is https, and
  `deploy:verify` enforces it.
- Offer analytics: the SQL column name is checked against an allow-list.
- Dependency advisories fixed: `laravel/framework`, `league/commonmark`,
  `league/flysystem`, `phpseclib`. `composer audit` and `npm audit` are clean.

**Fixed**
- Raw translation keys (`general.company`, etc.) showing in the footer, on
  404/410 pages and on provider pages. The cause was `__()` calls with a
  `default` option, which `__()` does not support.
- A BOM in `lang/{en,ar}/profile.php` was printed before `<!DOCTYPE>`.
- Provider rating N+1 (search page went from 21 queries to 13).
  `preventLazyLoading` is now also on in tests.
- Admin edits to cities did not clear the city cache.
- Page titles: "| Speeda - Speeda" duplication. Pages without SEO data were
  titled just "Speeda".
- SEO meta for pages without a model was cached under one shared key and
  leaked between pages.
- Login no longer returns 500 for a provider account with no profile row.

**Removed**
- Dead `ServiceProviderProfileController`, `ServiceProviderController::profile()`
  and `edit()`, and an unused gallery partial.
- Scratch scripts and diagnostic views, moved to `_Dev_Tools/archive/`.

## 2026-09-09: hardening release (verified audit of 2026-09-08)

**Security / deploy**
- `SecurityHeaders` middleware: nosniff, frame options, referrer policy, a
  minimal CSP, and HSTS on HTTPS in production.
- `php artisan deploy:verify` checks production config before every cutover.
  Sessions are encrypted by default.
- Analytics: no hard-coded public salt. `ANALYTICS_SALT` is used, falling back
  to `APP_KEY`.
- Routes are cached on deploy. nginx example uses `$realpath_root`.
- The build fails if an email ships without an opt-out link.

**Fixed**
- Gallery:
  - Real WebP conversions (`Fit::Crop`).
  - A failed replace keeps the old image.
  - Correct image counts after upload and delete.
  - Missing files fall back to the default image.
  - Another provider's image returns 404.
- Upload errors now say what went wrong (`ExplainUploadFailures`).
- Recommendations are counted inside a transaction, so the count can no
  longer go negative.
- The `admin` login shortcut uses the same rate-limit key as the email login.
- The provider route ID only matches digits, so `/service-providers/dashboard`
  is no longer swallowed.
- Sitemap uses `https://speeda.ca`. URL generator state is restored after the
  sitemap is generated.
- Google sign-in stays on one host, and its failures are logged.
- Provider profile layout:
  - Unclosed tags fixed.
  - Duplicate WhatsApp buttons on phones removed.
  - Overflow at 320 px fixed.
- 17 missing translation keys added per language. The translation checker
  was rewritten and added to CI.

**Removed**
- Livewire and Volt (they were unused). Tinker moved to dev dependencies.
  dompdf pinned to `^3.1`.
- Diagnostic pages and routes, public test files, and backup copies of
  routes and controllers.

## 2026-08: zero-downtime, pull-based deploys

- 08-06: admin email journey screens, testimonials, provider management, bulk
  actions.
- 08-08: admin broadcast email to all providers. Zero-downtime immutable
  releases. CI fails if fewer than the minimum number of tests ran.
- 08-09: switched from push-over-SSH to **pull-based** deploys
  (`deploy/pull-deploy.sh`, `HOLD_SWITCH` gate). HTTP exceptions keep their
  status, so a 404 is no longer turned into a 302.
- 08-16: the sitemap always uses the production domain and is regenerated
  daily. A pruning failure can no longer roll back a healthy release.

## 2026-07: test suite, CI, admin features

- 07-01:
  - Admin targeted notifications with read receipts.
  - CMS-managed legal pages.
  - UI/UX refactor, done in phases.
  - CI moved to PHP 8.4. Test suites rewritten to match current behavior.
- 07-02: provider ranking by photo + stars, then photo + experience, views,
  seniority. Merged "Renovation and Construction" filter.
- 07-04: permanent admin account seeder (login `admin`). WhatsApp analytics
  filters reworked.
- 07-05: 19 dependency advisories fixed. Laravel Pint applied. Orphaned
  providers repaired automatically when categories are rebuilt.
- 07-08: owner/developer profile with a private location and phone whitelist
  (`config/owner.php`).

## 2026-02 to 2026-06: summarized from archived reports

- **June:**
  - Provider service areas (extra cities per provider, `service_areas`).
  - Ontario cities added. Signup city/province data normalized.
- **May:** many iterative "modifications" commits (v5–v16), together with:
  - Unique phone identity constraints.
  - Admin dashboard work.
  - Authentication review.
  - SEO title configuration.
- **April:**
  - Home page changes and the blog (`posts`).
  - Admin notifications tables.
  - Category images.
  - Meta/Facebook Pixel removed.
  - SEO audit and meta-tag work.
  - Gallery and category pre-production pass.
- **February:**
  - Dynamic multilingual categories (en/ar/fr columns).
  - "Restaurants" renamed to "Restaurants and Cafe".
  - "Others" category and logos.
  - Review, rating and recommend workflow fixes.
  - Admin reviews screen improvements.
  - Old provider profile model removed.
- **2025-12-25:** first deployment.
