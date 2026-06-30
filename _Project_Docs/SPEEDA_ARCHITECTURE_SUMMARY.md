# Speeda.ca — Architecture Summary

Generated 2026-06-17 from local codebase inspection. Companion to `SPEEDA_OFFICIAL_MASTER_DOCUMENTATION.md` (dated 2026-05-25), which contains the full encyclopedic detail. This file is the condensed map for quickly orienting a new AI agent.

## 1. System Overview

Speeda is a **Laravel 12 monolith**, Blade-only (no SPA, no dedicated JSON/REST API layer). MySQL database. Tailwind/Vite for assets, with Alpine.js and some Bootstrap/Font Awesome usage in older views. Livewire is installed but not used for any first-class feature component yet (`app/Livewire` is empty; one Blade view exists at `resources/views/livewire/admin/user-management.blade.php`).

Multilingual public site: Arabic / English / French, driven by `lang/{ar,en,fr}/*.php` files and locale-aware Eloquent accessors. Admin dashboard is English-only by convention (not enforced by middleware, just by implementation).

## 2. Request Lifecycle

```text
HTTP request
  -> web middleware group (SetLocale, TrackVisitor, CheckUserStatus appended in bootstrap/app.php)
  -> route matching (routes/web.php, routes/auth.php)
  -> route-specific middleware (auth, admin, role, throttle, handle.large.uploads)
  -> Controller method (or FormRequest validation first)
  -> Service / Action class or direct Eloquent query
  -> MySQL
  -> Blade view, or small JSON response for AJAX-style endpoints
  -> TrackVisitor records eligible GET visit after response (deduped 5 min by hashed IP+UA)
```

Middleware aliases (`bootstrap/app.php`): `role`, `handle.large.uploads`, `admin`, `check.user.status`.

A daily scheduled closure in `bootstrap/app.php` deletes expired `admin_notifications`.

## 3. Route Structure

`routes/web.php` (~328 lines) + `routes/auth.php`. No `routes/api.php` in active use — Sanctum is installed but unused for API auth.

Major groups:
- Public: `/`, `/categories`, `/categories/{category:slug}`, `/locations`, `/blogs`, `/blogs/{post:slug}`, static pages (`/about-us`, `/Static/*`).
- Auth: register/login/password-reset/email-verification/logout (Breeze-style, in `routes/auth.php`).
- Providers (public): `/service-providers`, `/service-providers/{serviceProvider}`.
- Provider actions (auth): contact reveal, analytics click tracking, dashboard, profile update, image/gallery upload.
- Reviews, ratings, endorsements, comments — mostly auth + client-only.
- Admin (`auth` + `admin` middleware): dashboard, users, categories, locations, reviews, comments, notifications, visitors, activity logs, provider activity monitor, blog CMS, undo, clear-cache.
- `outhebox/laravel-translations` package routes under `/translations`.
- Livewire package asset/upload routes.

## 4. Controller Map

`app/Http/Controllers/`:
- `HomeController` — landing page.
- `CategoryController` — category pages.
- `LocationController` — location pages.
- `ServiceProviderController` (**~779 lines, largest public controller**) — listing, show, dashboard glue.
- `ServiceProviderProfileController` — profile edit, image/gallery uploads.
- `ServiceProviderAnalyticsController` — click/view tracking endpoints.
- `ProviderAnalyticsExportController` — PDF export (dompdf).
- `ProviderDashboardController` — provider-facing analytics dashboard.
- `ReviewController`, `RatingController`, `EndorsementController`, `CommentController` — engagement features.
- `BlogController` — public blog.
- `NotificationController` — provider notification center.
- `GalleryController` — gallery diagnostics/helpers.
- `LocaleController` — language switching.
- `ProfileController` — Breeze account profile.
- `DebugController` — diagnostics (should be locked down/removed in production).
- `Auth/*` — Breeze auth controllers, customized `RegisteredUserController` (provider+client dual registration) and `AuthenticatedSessionController`.
- `Admin/AdminController` (**~1135 lines, largest controller in the app**) — dashboard, users, categories, locations all bundled together; candidate for splitting.
- `Admin/ActivityLogController`, `AdminCommentController`, `AdminNotificationController`, `AdminReviewController`, `BlogPostController`, `ProviderActivityMonitorController`, `UndoController`, `VisitorAnalyticsController`.

Note: `AdminController.php.backup` and `routes/web.php.bak` exist in the tree — stale backup files, not part of the active app; do not treat as current.

## 5. Model Map

`app/Models/` (line counts indicate complexity):
- `User` (154 lines) — clients/providers/admins, role enum, `isAdmin()` checks DB role OR `config('auth.admins')` email list.
- `ServiceProvider` (537 lines, **most complex model**) — provider profile, ranking, completion %, media (Spatie), relationships to category/location/reviews/endorsements.
- `Category` (426 lines) — self-referential hierarchy (sections → groups → terminal/leaf professions), localized accessors, `resolveFilterValue()`, `providerCategoryIds()`.
- `Post` (305 lines) — multilingual blog/CMS entity (the *active* blog schema).
- `Review` (171 lines) — moderated reviews, `approve()`/`reject()`, triggers provider rating recalculation.
- `Location` (129 lines) — city directory, localized accessors.
- `Comment` (139 lines) — polymorphic, moderated.
- `AdminNotification` (95), `AdminLog` (91) — admin broadcast + audit/undo.
- `Visitor` (74), `Rating` (70), `ProviderAnalytics` (48), `Booking` (49, dormant), `Endorsement` (44), `ServiceArea` (39, dormant), `ServicePackage` (37, dormant), `Portfolio` (37, dormant).

Dormant models/tables (schema exists, no active route/UI flow): `Booking`, `ServiceArea`, `ServicePackage`, `Portfolio`, plus tables `service_provider_profiles`, `service_provider_categories`, `saved_providers`, `availability_schedules`, `blog_posts`, `blog_categories`, `location_category`. Treat these as legacy/future scaffolding, not current behavior.

## 6. Service & Action Map

`app/Services/`:
- `AuthService` — registration orchestration (client vs. provider, transactional).
- `CategoryCacheService` — locale-aware category tree/filter/terminal cache.
- `LocationCacheService` — locale-aware location cache.
- `LocationClusterService` — resolves `cluster_montreal` (Laval+Montreal) and `cluster_ottawa` (Ottawa+Gatineau) filter clusters.
- `ProviderDashboardAnalyticsService` — provider-facing metrics.
- `AdminProviderActivityMonitorService` — admin activity table/events.
- `VisitorTrackingService` — hashed visitor stats.
- `SEOService`, `TranslationService`.

`app/Actions/`: `CalculateProfileCompletionAction`, `TrackProviderViewAction`, `TrackProviderClickAction`.

**No repository layer** — controllers/services query Eloquent directly. There is a legacy `App\Filters\ServiceProviderFilter` class that is **not used** by the active listing controller (dead code referencing nonexistent columns/scopes).

## 7. Middleware Map

`app/Http/Middleware/`: `AdminMiddleware`, `CheckUserStatus`, `HandleLargeUploads`, `RoleMiddleware`, `SetLocale`, `TrackVisitor`.

## 8. Blade Layout Map

- `resources/views/layouts/{app,guest,navigation,footer}.blade.php`
- `resources/views/components/` — 30+ shared components: nav, language switcher, admin sidebar/top-bar, buttons, modal, pagination set (`components/pagination/*`), rating-stars, toast, profile-completion popup/banner/notification-center, endorsement-button, notification-card.
- View groups: `admin/*` (dashboard, categories, locations, users, reviews, comments, notifications, visitors, activity_logs, blog/posts, provider_activity_monitor), `auth/*`, `service-providers/*`, `provider/gallery/*`, `blog/*`, `reviews/*`, `comments/*`, `seo/*` (meta + structured-data partials), `Static/*` (legal/help pages), `partials/*` (homepage sections).

Pagination is centralized via `AppServiceProvider::boot()` setting `Paginator::defaultView('components.pagination.default')`.

## 9. Database Relationship Summary

Core entity graph: `User` 1—1 `ServiceProvider` → belongs to `Category` (hierarchical) and `Location`; has many `Review`/`Endorsement`/`Rating`/Spatie `Media` (gallery). `Review` belongs to `User` (client) and optionally `Booking`. `Comment` is polymorphic. `AdminNotification` belongs-to-many `User` through `admin_notification_user` (read-status pivot). `Post` (blog) belongs to author `User`, optional `Category`/`Location`. `Visitor` and `analytics` tables log hashed/anonymized engagement events keyed to `ServiceProvider`/`User`.

Full column-by-column detail for all 34+ tables is in `SPEEDA_OFFICIAL_MASTER_DOCUMENTATION.md` §3.

## 10. Admin Module Map

Single `auth`+`admin` gated area under `/admin/*`. `AdminController` currently owns dashboard + users + categories + locations (architectural smell — should eventually split into dedicated controllers per resource). Separate controllers already exist for reviews, comments, notifications, visitors, blog, activity logs, undo, provider activity monitor.

## 11. Public Module Map

Home → category/location/search → provider listing (filtered, paginated 12/page) → provider profile (reveal contact, WhatsApp click, reviews, gallery, similar providers) → optional auth → review/rate/recommend. Plus blog and static legal/help pages.

## 12. Multilingual System Summary

- Locale middleware: `SetLocale`.
- `lang/{ar,en,fr}/*.php` — ~28 files per locale (auth, admin, blog, categories, cities, comments, endorsements, errors, footer, general, help, home, language, legal, location, maintenance, messages, pagination, passwords, profile, ratings, reviews, seo, services, service_provider, sp_validation, terms, validation, about).
- Model-level localized accessors (Category, Location, Post, ServiceProvider) follow fallback chain: current locale column → translation file → base column → other locale columns.
- Admin UI is English-only by convention; public UI supports RTL (Arabic) and LTR.
- Language switcher component + `LocaleController`.

## 13. SEO System Summary

`SeoMetaService` dispatches to per-page builders (home, category, provider, search, blog_index, blog_post) producing a `SeoData` object rendered via `artesaos/seotools` in `resources/views/seo/meta.blade.php`. Sitemap generated via `php artisan seo:generate-sitemap` (`spatie/laravel-sitemap`) to `public/sitemap.xml`. Known issue: last-generated sitemap contains `localhost` URLs — must regenerate with production `APP_URL` before relying on it. Hreflang uses query-parameter locale switching (`?lang=`), not localized path segments.

## 14. Risk Areas (see full detail in SPEEDA_RISKS_AND_KNOWN_ISSUES.md)

1. Analytics queries reference `analytics.user_id`, added by a migration (`2026_05_19_092100_add_user_id_to_analytics_table.php`) that was pending as of the last full audit — verify it has since been applied (it now appears tracked in git status as a new untracked migration file, so confirm migration state before trusting dashboards).
2. `AdminController` (1135 lines) and `ServiceProviderController` (779 lines) are oversized — review carefully before extending.
3. Legacy/dormant tables and the unused `App\Filters\ServiceProviderFilter` class can mislead an agent into "fixing" dead code.
4. Backup files (`AdminController.php.backup`, `routes/web.php.bak`) exist in the tree — never treat them as current source of truth.
5. Several new, uncommitted migrations exist for Ontario cities / signup-city normalization (`2026_06_07_*`) — these are newer than the master documentation snapshot (2026-05-25) and not yet reflected there.
