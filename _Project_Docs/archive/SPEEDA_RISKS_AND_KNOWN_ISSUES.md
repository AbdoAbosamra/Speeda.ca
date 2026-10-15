# Speeda.ca — Risks & Known Issues

Generated 2026-06-17. Documentation only — nothing in this file was fixed or modified. Cross-reference with the full detail in `SPEEDA_OFFICIAL_MASTER_DOCUMENTATION.md` §19, §21, §22 (dated 2026-05-25); items below note what has changed since.

## Critical

1. **Analytics `analytics.user_id` schema/code mismatch.**
   As of the 2026-05-25 audit, code in `ProviderDashboardAnalyticsService`, `AdminProviderActivityMonitorService`, and `Admin/AdminController@dashboard` queries `analytics.user_id`, but the migration adding that column (`2026_05_19_092100_add_user_id_to_analytics_table.php`) was pending on the live DB. The migration file now exists as an **untracked** file in the working tree per current git status — confirm whether it has actually been run against the live database before trusting any admin/provider analytics dashboard output.

2. **Uncommitted/new migrations not yet reflected in the master documentation.**
   Several new migrations exist beyond the 2026-05-25 snapshot: `2026_05_19_092100_add_user_id_to_analytics_table.php`, `2026_06_07_000001_add_ontario_cities_to_locations_table.php`, `2026_06_07_000002_normalize_signup_city_provinces.php`, `2026_06_07_000003_replace_standalone_toronto_signup_location.php`. Many tracked files (controllers, services, lang files, views, migrations) also show as modified in git status. Treat the master documentation as **slightly stale** for locations/cities and registration flow — verify against current code before acting on it.

## High

3. **Last-admin deactivation guard is logically inverted.**
   Current check (as of last audit) tests `$user->isAdmin() && !$user->is_active`, which guards against deactivating an *already-inactive* admin rather than preventing the deactivation of the *last active* admin. Risk: full admin lockout is possible. Verify current logic in `Admin/AdminController` before relying on this safety net.

4. **Admin access can be granted purely via config.**
   `User::isAdmin()` returns true if `role === 'admin'` **or** the user's email is in `config('auth.admins')`. A misconfigured `.env`/config can silently grant admin access outside the DB role system. This is an intentional recovery mechanism per code comments, but should be tightly controlled and audited in production config.

5. **No DB-level uniqueness for one-review-per-client-per-provider.**
   Duplicate prevention is application-only; race conditions could create duplicate reviews. No unique index exists on `(client_id, service_provider_id)` in `service_provider_reviews`.

6. **Production sitemap risk.**
   The most recently generated `public/sitemap.xml` (per last audit) contained `http://localhost` URLs — a leftover from local generation. If this file is deployed as-is, it actively harms SEO. Must be regenerated with `APP_URL=https://speeda.ca` before/during any production deploy.

## Medium

7. **`service_providers.whatsapp_number` uniqueness is app-level only** until the now-present `2026_05_12_000001_add_unique_phone_identity_constraints.php` migration is confirmed run; duplicates could exist if data was inserted outside normal app validation.

8. **Orphan/non-section root categories exist in live data** (e.g., `cleaner-598`, `plumber-731` style entries per last audit) — these should be audited, reattached under proper sections, or deactivated. Don't assume the category tree is clean.

9. **Legacy/dormant database tables and models create confusion risk** for any AI agent reasoning about the schema: `service_provider_profiles`, `service_provider_categories`, `saved_providers`, `availability_schedules`, `service_areas`, `service_packages`, `portfolios`, `bookings`, `blog_posts`, `blog_categories`, `location_category` all exist but have no active end-to-end UI/route flow. An agent must be told explicitly which schema is "live" (e.g., `posts` not `blog_posts`; `service_providers` not `service_provider_profiles`) — see `SPEEDA_AI_MASTER_INSTRUCTIONS.md`.

10. **`ServiceProvider::scopeVerified()` is a no-op** — calling it does not actually filter by verification status. Any feature relying on "verified providers only" is currently broken at the model layer.

11. **Category slug uniqueness is `(slug, parent_id)`, not globally unique** — route-model binding by slug alone can be ambiguous if two categories under different parents share a slug.

12. **Endorsement counter cache can drift** — `service_providers.endorsement_count` is incremented/decremented outside a DB transaction paired with the toggle write.

13. **Provider search is plain SQL `LIKE`**, not full-text — will degrade as provider volume grows; matches against `company_name`, `bio`, `services_offered`, and owner `users.name` only.

14. **Legacy dead code: `App\Filters\ServiceProviderFilter`** references scopes/columns (`availableWeekends()`, `average_rating`) that don't exist on the current `ServiceProvider` model. It is not used by the active listing controller — don't "fix" it as if it were live code without first confirming intent with the user.

15. **Mixed CSS/UI systems** — Tailwind, Bootstrap utility classes, inline `<style>` blocks, and Font Awesome are all present simultaneously across Blade views. No single design system is enforced; styling regressions are easy to introduce inadvertently.

16. **Oversized controllers** — `Admin/AdminController` (~1135 lines) bundles dashboard + users + categories + locations; `ServiceProviderController` (~779 lines) bundles listing + show + supporting logic. Both are higher-risk surfaces for regressions; large diffs in either deserve extra scrutiny.

## Low

17. **Diagnostic/debug routes** (`DebugController`, `/debug-translations`, `/diagnostic`, `/translation-test`, `/test-language` views) exist and should be confirmed disabled or access-restricted in production.

18. **Backup/stale files in the working tree**: `app/Http/Controllers/Admin/AdminController.php.backup`, `routes/web.php.bak`, `resources/views/home.blade.php.backup`. These are not loaded by the app but could confuse a search/grep-based agent into editing the wrong file. Flag to the user whether these should be deleted — do not delete unilaterally.

19. **`robots.txt` lacks a `Sitemap:` directive.**

20. **Public CSRF token route** may be unnecessary exposure (not inherently dangerous, but worth auditing).

## Out of Scope for This Inventory

- No payment/billing/subscription module exists (Phase 3 roadmap item in the master doc) — not a bug, just an unbuilt feature area.
- No first-class API layer — by design, per project rules ("NO API architecture").
