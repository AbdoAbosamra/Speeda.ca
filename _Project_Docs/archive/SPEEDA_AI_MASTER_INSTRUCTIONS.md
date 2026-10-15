# Speeda.ca — Master Instructions for Future AI Agents

Paste this file's content into a new Claude Project's "Project Instructions" (or equivalent system prompt) when working on Speeda.ca outside of this Claude Code session.

---

## Project Identity

You are assisting with **Speeda.ca**, a live production service marketplace / provider directory / lead-generation platform for **Arabic-speaking users in Canada**.

- Backend: **Laravel 12**, PHP 8.2+.
- Frontend: **Blade templates only** — no SPA framework, no separate frontend app.
- **No API architecture.** JSON responses exist only for small AJAX-style endpoints (notifications, analytics tracking, contact reveal, gallery operations, locale helpers). Do not introduce a REST/GraphQL API layer unless explicitly asked.
- Database: **MySQL**.
- Styling: **Tailwind-first** direction, though older views mix in Bootstrap utility classes and Font Awesome. Prefer Tailwind for new/changed UI; don't fight existing Bootstrap usage in unrelated views.
- Public site is **multilingual: Arabic, English, French** (RTL support required for Arabic). Admin dashboard is **English-only** by convention.
- This is a **live production project** with real user and provider data.

## Absolute Rules

1. **No API architecture.** Don't add REST/GraphQL endpoints unless the user explicitly requests it.
2. **No destructive migrations.** Never write a migration that drops columns/tables or force-deletes data without explicit user instruction and confirmation.
3. **No data deletion.** Never delete rows, never truncate tables, never run destructive seeders against production data.
4. **No category taxonomy reset.** The category hierarchy (`categories` table, sections → groups → terminal professions) is business-critical. Never bulk-modify or reset it.
5. **Never delete the "Others" category** (section or its child). Registration's `profession = other` path depends on it existing; if missing, providers get a null category.
6. **Public site stays ar/en/fr.** Any new public-facing text needs entries in `lang/ar/*.php`, `lang/en/*.php`, and `lang/fr/*.php`.
7. **Admin dashboard stays English-only.** Don't add multilingual admin UI unless asked.
8. **Frontend and backend changes travel together.** A Blade view change that needs new data must come with the controller/service change that provides it, and vice versa.
9. **Document every change.** Summarize what changed and why (a changelog-style note) at the end of every response that modifies code.
10. **Never run migrations, seeders, or destructive artisan commands against production without explicit user confirmation of which environment you're targeting.**
11. **Never commit secrets.** Don't echo `.env` values, API keys, DB credentials, or tokens into responses or files.

## Technical Standards

- Prefer **Service classes** (`app/Services/`) for business logic that spans multiple models or needs caching (see `CategoryCacheService`, `LocationCacheService` as patterns).
- Prefer **Action classes** (`app/Actions/`) for single-purpose, reusable operations (see `TrackProviderViewAction`, `CalculateProfileCompletionAction` as patterns).
- There is **no Repository layer currently** — don't introduce one unless the user asks; Eloquent models/query builders are used directly today, and that's the established convention.
- **Avoid fat controllers.** `Admin/AdminController` (~1135 lines) and `ServiceProviderController` (~779 lines) are already oversized — when adding to these, consider whether the new logic belongs in a Service/Action instead, and flag the option to the user rather than silently growing the controller further.
- **Production-safe validation**: use Laravel FormRequest classes for validation + authorization, following the existing `RegisterRequest`, `StoreReviewRequest` pattern.
- **Tailwind-first, responsive-first** for any new UI.
- **RTL/LTR support is mandatory** for any public-facing UI change — test how it reads in Arabic (`dir="rtl"`), not just English/French.
- Localized model accessors follow this fallback chain: current locale column → translation file → base column → other locale columns. Follow this same pattern for any new localized field (see `Category`, `Location`, `Post` models).

## Current Core Systems (for orientation — see SPEEDA_FEATURE_INVENTORY.md for full detail)

- **Authentication**: dual registration (client vs. provider), email or provider-phone login, admin shortcut via `config('auth.admin_email')`.
- **Providers**: `service_providers` table is the *active* profile schema. `service_provider_profiles` is legacy/dormant — don't build on it.
- **Clients**: `users` with `role = client`. No phone field for clients.
- **Categories**: hierarchical (`categories` table, self-referential via `parent_id`). Sections (`is_section=true`, root) → groups → terminal/leaf professions.
- **Locations**: `locations` table, Canadian cities, with named filter clusters (`cluster_montreal` = Laval+Montreal, `cluster_ottawa` = Ottawa+Gatineau).
- **Filters**: category + location + search query string on `/service-providers`, paginated 12/page with `withQueryString()`.
- **Pagination**: centralized custom paginator views (`components/pagination/*`); multiple paginators on one page need distinct page names (see `reviews_page` on provider profile).
- **Notifications**: one-way admin → provider broadcast system (`admin_notifications` + `admin_notification_user` pivot), not a general notification system.
- **Reviews**: client-authored, admin-moderated (`is_active` flag), drives `service_providers.rating`/`calculated_rating`.
- **Recommendations**: `endorsements` table — independent client "recommend" signal, separate from reviews/ratings.
- **Analytics**: `analytics` table tracks provider profile views and WhatsApp clicks. **Verify `analytics.user_id` column exists on the live DB before trusting any dashboard built on it** — this was a known schema/code mismatch as of the last full audit (see SPEEDA_RISKS_AND_KNOWN_ISSUES.md).
- **Blogs**: `posts` table is the *active* multilingual CMS schema. `blog_posts`/`blog_categories` are dormant — don't build on them.
- **SEO**: `SeoMetaService` + per-page builders, `artesaos/seotools`, sitemap via `php artisan seo:generate-sitemap`.
- **Admin dashboard**: `/admin/*`, gated by `auth` + `admin` middleware, English-only.
- **Media/gallery**: Spatie Media Library on `ServiceProvider`, WebP conversions (`gallery_thumb`, `gallery_large`), non-queued.

## Required Response Format for Future Agents

Any future agent working on a non-trivial change to Speeda.ca should structure its response as:

1. **Understanding** — restate the task/problem in your own words.
2. **Files inspected** — list the actual files you read.
3. **Root cause analysis** — for bugs, identify *why* it happens, not just where.
4. **Implementation plan** — steps you intend to take, in order.
5. **Code changes** — the actual diffs/edits.
6. **Frontend impact** — what Blade/Tailwind/JS changed and how it behaves in ar/en/fr and RTL/LTR.
7. **Backend impact** — what controller/service/model/migration changed and why.
8. **Production safety** — explicit call-out of any migration, data, or config risk, and what was done to mitigate it.
9. **Testing checklist** — what was verified (and how) and what the user should still verify manually.
10. **Changelog** — a 1–3 sentence summary of what changed and why, suitable for a commit message or release note.

## Known Gaps This Documentation Cannot Fill

- Current production DB state (actual row counts, whether pending migrations have been run) cannot be confirmed from static code alone — always ask the user to confirm migration status before assuming schema columns exist.
- Business priorities/roadmap sequencing beyond what's inferable from the code (e.g., whether Phase 3 monetization work has started) should be confirmed with the user, not assumed from the codebase snapshot.
