# Speeda.ca Official Master Documentation

Generated from the local Laravel codebase and live configured database on 2026-05-25.

This document describes the current Speeda.ca platform as implemented. It is written as an operating knowledge base for owners, CTOs, developers, security reviewers, SEO operators, and future AI agents.

Important current-state notes:

- Platform: Laravel 12 monolith, Blade-first, MySQL, Tailwind/Vite assets, Alpine/Bootstrap usage in views, Livewire installed.
- Architecture: no dedicated public API layer; JSON exists only for AJAX-style endpoints such as notifications, analytics tracking, contact reveal, gallery operations, and locale helpers.
- Current DB connection: MySQL database `speeda`.
- Current live schema has 34 tables.
- Current migrations: 74 ran, 2 pending.
- Pending migrations: `2026_05_12_000001_add_unique_phone_identity_constraints.php` and `2026_05_19_092100_add_user_id_to_analytics_table.php`.
- Dirty worktree existed during analysis; this document does not interpret uncommitted changes as deployed production truth unless they are also reflected in the running schema.

## 1. Executive Overview

### What Speeda Is

Speeda.ca is a multilingual service marketplace and provider directory for Arabic-speaking users in Canada. It helps clients discover local service providers, compare provider profiles, reveal contact details, contact providers through WhatsApp, rate/review providers, and read localized service content.

The product currently operates as a lead-generation marketplace rather than a transactional booking marketplace. The platform does contain booking-related database tables, but the active public journey is directory discovery and contact generation.

### Business Goals

The business goal is to aggregate service providers serving Arabic-speaking communities in Canadian cities, especially the current core clusters:

- Laval and Montreal.
- Ottawa and Gatineau.
- Other Canadian cities currently seeded: Toronto, Vancouver, Calgary, Edmonton, Quebec City, Longueuil, Mississauga, Brampton.

Speeda's commercial value comes from:

- Building supply: provider registrations, provider profiles, profile media, categories, locations.
- Building demand: Arabic/English/French localized discovery pages, SEO pages, blog content, and category/location filters.
- Creating measurable lead events: contact reveal, WhatsApp click, profile view, visitor tracking.
- Giving providers visibility into performance: provider dashboard analytics.
- Giving admins operational control: categories, locations, users, reviews, comments, notifications, visitor analytics, provider activity monitor.

### Business Model

The codebase supports a lead-generation/directory business model:

- Providers register and publish service profiles.
- Clients search and filter providers.
- Client intent is captured through profile visits, contact reveals, and WhatsApp clicks.
- Admin can monitor provider activity and user growth.

Not currently implemented as a complete paid SaaS marketplace:

- No subscription billing module exists.
- No provider plan/pricing module is active.
- No payment gateway is wired.
- Booking tables exist, but no active end-to-end booking UI or controller flow is present in the current routes.

### Target Users

Clients:

- Mostly Arabic-speaking consumers in Canada.
- Use Arabic, English, or French public UI.
- Register with email/password; clients do not need phone at registration.
- Can review, rate, and recommend providers after login.

Providers:

- Arabic/English/French-facing profile, English admin/dashboard shell.
- Register with name, email, phone, profession/category, city, optional WhatsApp.
- Maintain profile details, image, gallery, services, location, address, and language fields.
- Receive leads outside the platform through revealed phone/WhatsApp contact.

Admins:

- English-only operational back office.
- Access controlled by `role = admin` or configured admin email.
- Manage users, categories, locations, blog posts, moderation, notifications, visitor analytics, and provider activity.

### Competitive Positioning

Speeda is positioned between:

- A local directory such as Yellow Pages.
- A service marketplace such as TaskRabbit/Thumbtack.
- A community-specific lead-generation engine for Arabic-speaking immigrants and residents in Canada.

The differentiation is not technical complexity; it is audience focus, localized category taxonomy, multilingual experience, provider trust signals, and WhatsApp-native contact behavior.

### Marketplace Strategy

The implemented marketplace strategy is supply-first directory aggregation:

1. Seed high-value local service categories.
2. Let providers register directly.
3. Lock category changes except for "Others" providers to preserve taxonomy quality.
4. Encourage profile completion through completion scoring and UI prompts.
5. Rank visible providers by rating, views, reviews, and completion/profile image presence.
6. Let admins moderate reviews and keep category/location data clean.

### Lead Generation Strategy

Lead generation is measured through:

- `service_providers.views`: simple profile view counter.
- `analytics.action_type = view`: deduplicated provider profile view analytics.
- `analytics.action_type = click_whatsapp`: WhatsApp/contact click analytics.
- `revealed_contacts` session value: per-session contact visibility.
- Visitor tracking in `visitors`: hashed IP/user-agent path analytics.

Current business risk: the analytics system has code expecting `analytics.user_id`, but the migration that adds `user_id` is pending in the live schema. This can break admin/provider analytics queries that call `whereNotIn('user_id', ...)`.

## 2. System Architecture

### High-Level Architecture

Speeda is a Laravel monolith:

```text
Browser
  -> Laravel web route
  -> Middleware stack
  -> Controller or route closure
  -> FormRequest validation where used
  -> Service / Action / Eloquent query
  -> MySQL
  -> Blade view
  -> HTML response or small JSON response
```

### Installed Backend Packages

- `laravel/framework ^12.0`
- `laravel/sanctum ^4.2`
- `livewire/livewire ^4.2`
- `livewire/volt 1.10.5`
- `artesaos/seotools ^1.4`
- `spatie/laravel-medialibrary ^11.21`
- `spatie/laravel-sitemap ^8.1`
- `barryvdh/laravel-dompdf`
- `intervention/image`
- `outhebox/laravel-translations`

### Frontend Tooling

- Vite 7.
- Tailwind-first project CSS in `resources/css/app.css`, plus provider-specific CSS files.
- Alpine.js dependency.
- Bootstrap classes/modals appear in Blade templates.
- Font Awesome icons appear in many views.

### Request Lifecycle

Laravel 12 config is in `bootstrap/app.php`.

Web middleware appended globally:

- `SetLocale`
- `TrackVisitor`
- `CheckUserStatus`

Middleware aliases:

- `role`
- `handle.large.uploads`
- `admin`
- `check.user.status`

Main lifecycle:

```text
HTTP request
  -> Laravel web middleware
  -> SetLocale chooses app locale
  -> route matching
  -> auth/admin/throttle middleware if route requires it
  -> controller method
  -> validation/FormRequest if present
  -> Eloquent/DB/cache/action/service
  -> Blade or JSON response
  -> TrackVisitor records eligible GET visit after response
```

### Route Surface

`php artisan route:list` shows 178 routes. Major route groups:

- Public: `/`, `/locations`, `/categories`, `/categories/{category}`, `/blogs`, `/blogs/{post}`, static pages.
- Auth: register/login/password reset/email verification/logout.
- Providers public: `/service-providers`, `/service-providers/{serviceProvider}`.
- Provider actions: contact reveal, analytics click, dashboard, profile update, image upload, gallery operations.
- Reviews, ratings, endorsements, comments.
- Admin: dashboard, users, categories, locations, reviews, comments, notifications, visitors, activity logs, provider activity monitor, blog CMS, undo, clear-cache.
- Translations UI package routes under `/translations`.
- Livewire package asset/update/upload routes.

### Service Architecture

Actual service classes:

- `AuthService`: registration and role-specific provider creation.
- `CategoryCacheService`: locale-aware category tree/filter/terminal cache.
- `LocationCacheService`: locale-aware active/all location cache.
- `LocationClusterService`: resolves Laval/Montreal and Ottawa/Gatineau filter clusters.
- `ProviderDashboardAnalyticsService`: provider dashboard metrics.
- `AdminProviderActivityMonitorService`: admin activity table and provider event details.
- `VisitorTrackingService`: hashed visitor stats, top pages, live visitors.
- `SEOService`: legacy/general SEO helper.
- `TranslationService`: translation support.

Action classes:

- `CalculateProfileCompletionAction`
- `TrackProviderViewAction`
- `TrackProviderClickAction`

Repositories:

- No repository layer exists in the codebase.
- Eloquent models and query builders are used directly in controllers/services.

Livewire:

- Livewire is installed and package routes are present.
- `resources/views/livewire/admin/user-management.blade.php` exists.
- No first-class Livewire component class was found under `app/Livewire`; current business flows are Blade/controller-driven.

### Major Feature Request Flows

Homepage:

```text
User
  -> GET /
  -> HomeController@index
  -> SeoMetaService::apply('home')
  -> CategoryCacheService::getFilterGroups()
  -> ServiceProvider stats cache
  -> Featured providers query
  -> Latest published posts cache
  -> resources/views/home.blade.php
```

Provider listing:

```text
User
  -> GET /service-providers?category=&location=&search=
  -> ServiceProviderController@index
  -> SEO based on search/category/default
  -> ServiceProvider query with user/category/location/media/review/endorsement counts
  -> active-user constraint
  -> search/category/location cluster filters
  -> order by calculated_rating, views
  -> paginate(12)->withQueryString()
  -> resources/views/service-providers/index.blade.php
```

Provider profile:

```text
User
  -> GET /service-providers/{id}
  -> ServiceProviderController@show
  -> SeoMetaService::apply('provider')
  -> reject inactive provider owner
  -> if non-admin and non-owner: increment service_providers.views
  -> TrackProviderViewAction inserts analytics view once per session fingerprint per 24h
  -> aggregate active review statistics
  -> eager load user/category/location/reviews/endorsements/media
  -> paginate active reviews by reviews_page
  -> build gallery image URLs
  -> build similar providers
  -> resources/views/service-providers/show.blade.php
```

Registration:

```text
User
  -> GET /register
  -> RegisteredUserController@create
  -> CategoryCacheService::getTerminalCategories()
  -> grouped professions for provider registration
  -> auth/register.blade.php

User
  -> POST /register
  -> RegisterRequest validation
  -> AuthService::registerUser()
  -> users row
  -> if provider: service_providers row and location lookup/create
  -> Auth::login()
  -> redirect provider to profile page, client to home
```

Admin dashboard:

```text
Admin
  -> GET /admin/dashboard
  -> auth + admin middleware
  -> AdminController@dashboard
  -> VisitorTrackingService stats
  -> ProviderAnalytics WhatsApp counts/trends
  -> DB aggregates for category and provider performance
  -> counts for users/providers/categories/locations/blog/reviews/notifications
  -> admin/dashboard.blade.php
```

Review moderation:

```text
Client
  -> POST /reviews
  -> StoreReviewRequest authorizes client
  -> ReviewController@store
  -> duplicate/self-review/booking checks
  -> service_provider_reviews row with is_active=false

Admin
  -> POST /admin/reviews/{review}/approve
  -> AdminReviewController@approve
  -> Review::approve()
  -> is_active=true, admin fields set
  -> ServiceProvider::recalculateRating()
```

Notification delivery:

```text
Admin
  -> POST /admin/notifications
  -> AdminNotificationController@store
  -> admin_notifications row, expires_at +30 days
  -> provider nav caches cleared

Provider
  -> any page rendering components.main-nav
  -> View composer loads active notifications
  -> admin_notification_user pivot decides read/unread
```

## 3. Database Encyclopedia

### Current Database Inventory

Current table row counts at analysis time:

| Table | Rows | Purpose |
| --- | ---: | --- |
| admin_logs | 1 | Audit log for admin create/update/delete/toggle actions and undo support. |
| admin_notification_user | 0 | Pivot for provider read status on admin notifications. |
| admin_notifications | 10 | Multilingual provider-only admin broadcasts. |
| analytics | 0 | Provider profile view and WhatsApp click event log. |
| availability_schedules | 0 | Provider weekly availability; schema exists, no active route flow found. |
| blog_categories | 0 | Newer blog category schema exists but active CMS uses `categories` + `posts`. |
| blog_posts | 0 | Newer blog post schema exists but active CMS uses `posts`. |
| bookings | 0 | Booking schema exists; active booking UI/routes are not implemented. |
| cache | 7 | Laravel database cache store. |
| cache_locks | 0 | Laravel cache locks. |
| categories | 63 | Marketplace taxonomy: sections, groups, terminal service categories. |
| comments | 0 | Polymorphic comments with moderation. |
| endorsements | 59 | Client recommendations for providers. |
| failed_jobs | 0 | Failed queue jobs. |
| job_batches | 0 | Laravel batch jobs. |
| jobs | 0 | Queue jobs. |
| location_category | 0 | Location-category pivot; not central in active filtering. |
| locations | 12 | Canadian city/location records. |
| media | 0 | Spatie media library records for provider gallery. |
| migrations | 74 | Migration ledger. |
| password_reset_tokens | 0 | Password reset tokens. |
| personal_access_tokens | 0 | Sanctum tokens; no API architecture currently uses them. |
| portfolios | 0 | Provider portfolio schema; not active in routes. |
| posts | 12 | Active multilingual blog/content table. |
| ratings | 0 | Lightweight one-user-one-provider star ratings, separate from reviews. |
| saved_providers | 0 | Client bookmarks schema; relationship exists, no public UI route found. |
| service_areas | 0 | Provider multi-location service areas schema; primary filtering uses `service_providers.location_id`. |
| service_packages | 0 | Provider packages/pricing schema; no active UI route found. |
| service_provider_categories | 0 | Legacy profile-category pivot; current provider model has `category_id`. |
| service_provider_profiles | 0 | Legacy profile table; current model comments say profile is integrated into `service_providers`. |
| service_provider_reviews | 120 | Active review/moderation table. |
| service_providers | 53 | Main provider profile, business, contact, ranking, completion, and media owner entity. |
| sessions | 0 | Laravel database sessions if configured. |
| users | 81 | Clients, providers, admins. |
| visitors | 155 | Hashed visitor analytics by path/time. |

### Table Details

#### users

Purpose: core authenticated identity for clients, providers, and admins.

Columns: `id`, `name`, `email`, `role`, `is_active`, `profession`, `avatar`, `date_of_birth`, `gender`, `is_service_provider`, `is_profile_complete`, `provider_status`, `company_name`, `bio`, `website`, `location_id`, `address`, `latitude`, `longitude`, `preferences`, `notification_settings`, `timezone`, `login_count`, `last_login_at`, `last_login_ip`, `email_verified_at`, `phone_verified_at`, `verification_token`, `social_media_links`, `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at`.

Relationships: has one `service_providers`; has many bookings as client; belongs to many saved providers; has many endorsements/reviews/comments; belongs to many read notifications.

Used by: auth, admin users, reviews, comments, endorsements, notifications, visitors, admin logs.

Criticality: critical.

Risks: admin identity can be granted by configured email even if `role` is not admin; last-admin deactivation guard has a logic bug; clients have no phone identity; users soft-delete while some admin delete paths are described as permanent but call soft delete.

Future improvements: enforce MFA for admins, add explicit admin permission model, clean legacy provider fields, add audit for role changes.

#### service_providers

Purpose: active provider profile and marketplace listing record.

Columns: `id`, `user_id`, `category_id`, `location_id`, `is_certified`, `certification`, `bio`, `services_offered`, `hourly_rate`, `experience_years`, `phone`, `whatsapp_number`, `contact_email`, `address`, `facebook`, `instagram`, `linkedin`, `service_area`, `available_weekends`, `available_evenings`, `availability_schedule`, `languages`, `specializations`, `profile_image`, `profile_completion_percent`, `profile_completion_popup_shown_at`, `portfolio_images`, `portfolio_videos`, `is_verified`, `is_featured`, `business_type`, `company_name`, `business_license`, `rating`, `calculated_rating`, `total_reviews`, `completed_jobs`, `views`, `endorsement_count`, `emergency_available`, `response_time_hours`, `created_at`, `updated_at`, `deleted_at`.

Relationships: belongs to user/category/location; has many reviews, active reviews, endorsements, service areas; belongs to many locations through `service_areas`; owns Spatie `media`.

Used by: provider listing/profile/dashboard, admin dashboard, activity monitor, SEO, sitemap, review/rating/endorsement, gallery, analytics.

Criticality: critical.

Risks: `scopeVerified()` returns the query unchanged, so verified filtering is not enforced by the model scope; `calculated_rating` accessor overrides/can obscure the persisted column and can query active reviews dynamically; profile completion observer runs on every update; some localized accessors check columns that do not exist yet.

Future improvements: separate contact lead tracking from profile entity, fix verified scope, move ranking to indexed materialized fields, add provider status workflow.

#### categories

Purpose: marketplace taxonomy for sections, filter groups, and terminal provider professions.

Columns: `id`, `name`, `name_ar`, `name_en`, `name_fr`, `slug`, `description`, `description_ar`, `description_en`, `description_fr`, `icon`, `color`, `is_active`, `sort_order`, `parent_id`, `is_section`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `deleted_at`.

Relationships: self parent/children/allChildren/grandchildren; has many service providers.

Used by: registration profession selector, provider filtering, provider SEO, category pages, blog filtering, admin category manager, sitemap.

Criticality: critical.

Risks: active database contains non-section root categories like `plumber-731`, `cleaner-598`, etc.; slugs are unique by `(slug,parent_id)` but route model binding by slug can be ambiguous; cache invalidation does not run from `CategoryObserver`, only admin controller clear paths.

Future improvements: enforce taxonomy levels, add unique global slugs or custom route resolver, remove test/root orphan categories, add admin validation preventing invalid parent/section combinations.

#### locations

Purpose: city/location directory and filter source.

Columns: `id`, `is_active`, `city`, `country`, `area`, `latitude`, `longitude`, `image`, `meta_title`, `meta_description`, `created_at`, `updated_at`.

Relationships: has many service providers; belongs to many categories through `location_category`.

Used by: provider registration, provider filtering, profile edit, admin location manager, sitemap, blog filtering.

Criticality: high.

Risks: `city` is globally unique, limiting duplicate city names across provinces/countries; active filters use location IDs and hard-coded clusters; no city slug URL pages exist.

Future improvements: add province, country code, slug, geospatial indexing, and SEO landing pages for category-location combinations.

#### service_provider_reviews

Purpose: moderated client reviews.

Columns: `id`, `service_provider_id`, `client_id`, `rating`, `review_text`, `rating_breakdown`, `is_verified`, `title`, `is_active`, `is_featured`, `admin_approved_at`, `admin_approved_by`, `created_at`, `updated_at`, `booking_id`.

Relationships: belongs to provider, client, admin approver, booking; morph many comments.

Used by: public reviews, provider profile rating breakdown, admin moderation, rating recalculation, homepage stats.

Criticality: high.

Risks: no unique DB index for one review per client/provider; duplicate prevention is application-only; rejected reviews reuse `admin_approved_at` as a moderation timestamp; `title` exists but fillable/request flow does not currently use it.

Future improvements: add unique `(client_id, service_provider_id)`, separate `status` enum, add moderation notes/reasons, add spam checks.

#### ratings

Purpose: lightweight client star rating separate from text reviews.

Columns: `id`, `service_provider_id`, `user_id`, `rating`, `created_at`, `updated_at`.

Relationships: belongs to provider and user.

Used by: AJAX rating endpoints.

Criticality: medium.

Risks: ratings are not included in provider `calculated_rating`; user sees rating stored but provider average returned from `service_providers.rating`, which is recalculated only from approved reviews.

Future improvements: decide whether ratings are independent signal or merge into review score.

#### endorsements

Purpose: client recommendations for providers.

Columns: `id`, `service_provider_id`, `user_id`, `created_at`, `updated_at`.

Relationships: belongs to provider and user.

Used by: provider cards/profile, homepage stats, recommendation button.

Criticality: medium.

Risks: counter cache `service_providers.endorsement_count` can drift; no transaction wraps toggle and counter update.

Future improvements: recalculate counter periodically or use DB trigger/job; return JSON for smoother UI.

#### analytics

Purpose: provider event tracking for profile views and WhatsApp clicks.

Columns: `id`, `provider_id`, `action_type`, `ip_address`, `session_hash`, `created_at`, `updated_at`.

Relationships: belongs to service provider.

Used by: provider dashboard analytics, admin dashboard WhatsApp metrics, provider activity monitor.

Criticality: high.

Risks: code in `ProviderDashboardAnalyticsService`, `AdminProviderActivityMonitorService`, and `AdminController@dashboard` references `analytics.user_id`, but live schema does not have it because migration is pending. `ProviderAnalytics` model includes `user_id` fillable and a global scope conditioned on a cache key, but raw query services do not guard column existence.

Future improvements: run pending migration or guard all queries with `Schema::hasColumn`; add event type enum; add retention/rollup tables.

#### visitors

Purpose: hashed site visitor analytics.

Columns: `id`, `ip_hash`, `user_agent_hash`, `path`, `referer`, `user_id`, `visited_at`.

Relationships: belongs to user.

Used by: admin visitor dashboard, live count, top pages.

Criticality: medium-high.

Risks: hash based on app key; if key leaks, IP guesses can be brute-forced; dedupe is 5 minutes globally per IP/user-agent so rapid page browsing is undercounted.

Future improvements: rotate salt strategy, aggregate daily counts, exclude bot traffic, add path normalization.

#### admin_notifications

Purpose: admin-created multilingual broadcast notifications to providers.

Columns: `id`, `title_ar`, `title_en`, `title_fr`, `message_ar`, `message_en`, `message_fr`, `target_type`, `created_by`, `expires_at`, `created_at`, `updated_at`.

Relationships: belongs to admin; belongs to many users through `admin_notification_user`.

Used by: provider navbar, notification center, admin notifications.

Criticality: medium.

Risks: `target_type` enum only supports `provider_only`; no segmentation; deletion erases read history by cascade.

Future improvements: target categories/locations/provider segments, add publish scheduling.

#### admin_notification_user

Purpose: read/unread state pivot.

Columns: `id`, `user_id`, `admin_notification_id`, `read_at`, `created_at`, `updated_at`.

Relationships: user and admin notification.

Criticality: medium.

Risks: unread counts query all active notifications; large notification volume will need indexed active windows and possibly denormalized counts.

#### admin_logs

Purpose: admin action audit and undo source.

Columns: `id`, `admin_id`, `action`, `model_type`, `model_id`, `model_name`, `changes`, `is_undone`, `ip_address`, `user_agent`, `created_at`, `updated_at`.

Relationships: belongs to admin user.

Criticality: high for operations.

Risks: undo reliability depends on model-specific data completeness; logs can contain sensitive values in `changes`.

#### posts

Purpose: active multilingual blog/content CMS table.

Columns: `id`, `author_id`, `title`, `title_ar`, `title_en`, `title_fr`, `slug`, `content`, `content_ar`, `content_en`, `content_fr`, `excerpt`, `excerpt_ar`, `excerpt_en`, `excerpt_fr`, `category_id`, `location_id`, `image`, `featured_image`, `featured_image_alt_ar`, `featured_image_alt_en`, `featured_image_alt_fr`, `is_published`, `seo_title_ar`, `seo_title_en`, `seo_title_fr`, `seo_description_ar`, `seo_description_en`, `seo_description_fr`, `seo_keywords_ar`, `seo_keywords_en`, `seo_keywords_fr`, `og_title_ar`, `og_title_en`, `og_title_fr`, `og_description_ar`, `og_description_en`, `og_description_fr`, `og_image`, `twitter_title_ar`, `twitter_title_en`, `twitter_title_fr`, `twitter_description_ar`, `twitter_description_en`, `twitter_description_fr`, `twitter_image`, `status`, `published_at`, `is_featured`, `allow_indexing`, `canonical_url`, `meta_robots`, `reading_time_minutes`, `created_at`, `updated_at`, `deleted_at`.

Used by: public blog, admin blog CMS, homepage latest posts, SEO sitemap.

Criticality: medium-high.

Risks: two blog schemas exist (`posts` active, `blog_posts`/`blog_categories` dormant); featured cache key is locale-specific but category/location cache keys are not always locale-aware.

#### blog_posts and blog_categories

Purpose: alternate/newer blog schema, currently empty and not active in application routes.

Criticality: low unless migration plan reactivates it.

Risk: schema duplication confuses future teams.

#### comments

Purpose: polymorphic user comments with moderation status.

Columns: `id`, `commentable_type`, `commentable_id`, `user_id`, `content`, `is_active`, `is_flagged`, `approved_by`, `approved_at`, `rejection_reason`, `created_at`, `updated_at`, `deleted_at`.

Used by: public comments, admin comment moderation, review comments.

Criticality: medium.

Risks: moderation states are booleans, not an explicit status enum.

#### bookings

Purpose: planned booking/request workflow.

Columns include provider/profile/client references, reference, status, service details, costs, preferred/confirmed/completed dates, address, client phone, payment status/method, notes.

Criticality: dormant medium.

Risk: table still references both `service_provider_id` and legacy `service_provider_profile_id`; no active route implements booking workflow.

#### service_provider_profiles

Purpose: legacy provider profile table.

Criticality: legacy low.

Risk: current comments say profile is integrated into `service_providers`; table remaining can mislead developers.

#### service_provider_categories

Purpose: legacy many-to-many between service provider profiles and categories.

Criticality: legacy low.

Risk: current providers use `service_providers.category_id`; this pivot is empty.

#### saved_providers

Purpose: bookmarks/favorites.

Columns: `id`, `user_id`, `service_provider_id`, timestamps.

Criticality: dormant low.

Risk: model relationship exists but no complete active UI route was found.

#### service_areas

Purpose: planned provider multi-location service coverage.

Columns: provider, location, radius, extra charge, travel time, active flag.

Criticality: dormant medium.

Risk: public filtering ignores this table and uses provider primary `location_id`.

#### service_packages

Purpose: planned provider packages/pricing.

Criticality: dormant low.

Risk: no active UI/controller route.

#### availability_schedules

Purpose: planned weekly availability.

Criticality: dormant low.

Risk: no active UI/controller route.

#### portfolios

Purpose: planned provider project portfolio.

Criticality: dormant low.

Risk: active gallery uses Spatie `media`, not `portfolios`.

#### media

Purpose: Spatie media library table for provider gallery images and conversions.

Criticality: medium.

Risks: files must exist on `public` disk; conversion paths can break if storage symlink or permissions fail.

#### location_category

Purpose: many-to-many category/location relationship.

Criticality: low.

Risk: empty; active filtering does not use it.

#### cache, cache_locks, jobs, failed_jobs, job_batches, sessions, password_reset_tokens, personal_access_tokens, migrations

Purpose: Laravel framework infrastructure.

Criticality: varies by environment.

Risks: `sessions` is empty in current DB; if database sessions are enabled in production, cleanup scheduling matters. Sanctum tokens exist but no API architecture currently depends on them.

### Foreign Key Highlights

Critical cascades:

- Deleting a user cascades to provider, reviews, bookings, comments, endorsements, saved providers, read-notification pivots, admin logs if admin.
- Deleting a provider cascades to analytics, endorsements, ratings, reviews, service areas, packages, portfolios.
- Deleting a category cascades to children and providers through FK definitions, but admin delete blocks active/in-use categories before deletion.

### Index Highlights

Important indexes:

- `service_providers.user_id` unique.
- `service_providers.phone` unique.
- `categories.slug,parent_id` unique.
- `ratings.user_id,service_provider_id` unique.
- `endorsements.service_provider_id,user_id` unique.
- `analytics.provider_id,action_type,created_at`.
- `analytics.provider_id,session_hash,created_at`.
- `visitors.ip_hash,user_agent_hash`.

Pending uniqueness:

- `service_providers.whatsapp_number` will become unique only after pending migration.

## 4. Authentication System

### Login

Login route:

- `GET /login` redirects to `register`.
- `POST /login` uses `AuthenticatedSessionController@store` and `LoginRequest`.

`LoginRequest` accepts:

- `login`: email or phone.
- `password`.
- `role`: `client` or `service_provider`.
- `remember`.

Behavior:

- `admin` login value maps to `config('auth.admin_email', 'admin@speeda.com')`.
- Email login authenticates against `users.email`.
- Phone login is allowed only when selected role is `service_provider`.
- Phone login looks up `service_providers.phone`, then authenticates by the provider owner's email.
- After auth, selected role must match authenticated user role, except admins bypass this role check.
- Rate limit: 5 attempts per login/IP throttle key.

Security implications:

- Role selection prevents a provider from logging in through the client tab and vice versa.
- Admin shortcut `admin` depends on config and should be disabled/hidden in production if not intentional.
- Phone login depends on provider phone uniqueness.

### Registration

Registration route:

- `GET /register`: combined login/registration view.
- `POST /register`: `RegisterRequest`, then `AuthService::registerUser`.

Client registration:

- Requires email, password, role.
- Name is nullable; `AuthService` derives fallback from email prefix.
- No phone is required for clients.

Provider registration:

- Requires name, email, password, role, mobile, profession, city, terms.
- Optional WhatsApp number.
- `profession` must be terminal category ID or `other`.
- `city` is normalized and looked up/created in `locations`.
- Creates `users` and `service_providers` in one DB transaction.

### Roles

Roles are stored in `users.role` enum:

- `client`
- `service_provider`
- `admin`

Admin detection:

- `User::isAdmin()` returns true when `role === admin` or email is in `config('auth.admins', [])`.

Security implication:

- A user with a configured admin email can be treated as admin even if DB role is not admin. This is useful as a backdoor recovery path but should be carefully controlled.

### Permissions

No granular permission package is installed. Authorization uses:

- Admin middleware.
- Role middleware.
- FormRequest authorization.
- Controller checks.
- Policies for categories, comments, locations, reviews, service providers.
- Gates for admin-only, manage-categories, manage-locations, view-visitor-analytics.

### Sessions

Laravel session auth is used. Contact reveal uses session key `revealed_contacts`. Profile completion popup uses session key `profile_completion_popup_dismissed`.

### Password Reset

Laravel Breeze-style password reset routes exist:

- `forgot-password`
- `reset-password/{token}`
- `password.update`

Tokens use `password_reset_tokens`.

### Phone Validation

`CanadianPhoneNumber` validates:

- Empty values are left to nullable/required rules.
- Removes extensions and non-digits.
- Removes leading country code `1`.
- Requires exactly 10 digits.
- First digit cannot be 0 or 1.

### Can Duplicate Phones Exist?

Provider primary phone:

- Current live DB has unique index on `service_providers.phone`.
- Registration and profile update also validate uniqueness.
- Duplicate provider phone should not exist unless DB index is manually removed or data was imported with constraints disabled.

Provider WhatsApp number:

- Current live DB only has a non-unique index on `service_providers.whatsapp_number`.
- Registration/update validate uniqueness at application level.
- Duplicate WhatsApp numbers can exist if inserted outside app validation.
- Pending migration will add unique WhatsApp index after checking duplicates.

Client phone:

- Clients do not have a phone field in current registration flow.
- `users.mobile` does not exist in the live schema.

## 5. User Types

### Clients

Capabilities:

- Register/login with email/password.
- Browse providers.
- Reveal contact details.
- Review providers.
- Rate providers.
- Recommend providers.
- Comment where comments are exposed.

Restrictions:

- Cannot use provider phone login.
- Cannot rate/review themselves.
- Cannot create provider profile unless registered as provider.

Client journey:

```text
Home
  -> search/filter providers
  -> provider listing
  -> provider profile
  -> reveal contact / WhatsApp
  -> optional login
  -> review/rate/recommend
```

### Providers

Capabilities:

- Register with phone/category/city.
- Own exactly one `service_providers` row.
- Edit own profile.
- Upload profile image.
- Upload/replace/delete gallery images.
- View dashboard analytics.
- Read admin notifications.

Restrictions:

- Cannot rate/review/recommend own profile.
- Category cannot be changed after registration unless current category is "Others".
- Profile update authorization requires route provider to match authenticated user's provider.

Provider lifecycle:

```text
Register as provider
  -> users row
  -> service_providers row
  -> profile completion starts low
  -> provider profile page
  -> complete image/address/services/experience
  -> appears in listing
  -> receives views and WhatsApp clicks
  -> checks dashboard analytics
```

### Admins

Capabilities:

- Dashboard metrics.
- User activation/deletion/restore/role update.
- Category CRUD/toggle.
- Location CRUD/toggle.
- Review moderation.
- Comment moderation.
- Blog CMS.
- Notifications.
- Visitor analytics.
- Provider activity monitor.
- Admin logs and undo.
- Clear application caches.

Restrictions:

- Must pass auth and admin middleware.
- Admin UI is English-only by implementation style.

Admin journey:

```text
Login
  -> /admin/dashboard
  -> inspect marketplace health
  -> moderate reviews/comments
  -> manage taxonomy/locations/users/content
  -> monitor provider activity and visitor trends
```

## 6. Category System

### Hierarchy

`categories` is self-referential through `parent_id`.

Definitions:

- Section: `is_section = true` and `parent_id = null`.
- Filter group: non-section child of a root section.
- Terminal category/profession: active non-section category with no children.

Core methods:

- `isSection()`
- `isSubcategory()`
- `isLeaf()`
- `isFilterGroup()`
- `descendantAndSelfIds()`
- `providerCategoryIds()`
- `resolveFilterValue()`

### Current Root Sections

Active root sections:

- Automotive Services.
- Home & Property Services.
- Professional & Business Services.
- Personal & Lifestyle Services.
- Technical & Repair Services.
- Food Services.
- Grocery & Supermarkets.
- Others.

Data quality note: the live DB also has active root categories that are not sections (`cleaner-598`, `plumber-731`, etc.). These should be audited and either attached under sections, deactivated, or removed.

### Registration Usage

Provider registration uses terminal categories only:

- `RegisteredUserController@create` loads `CategoryCacheService::getTerminalCategories()`.
- It groups terminal professions under root sections for optgroup rendering.
- `RegisterRequest` validates `profession` as terminal category ID or `other`.
- `AuthService` stores chosen category in `service_providers.category_id`.

### Filtering Usage

Provider listing:

- `category` query parameter can be numeric ID or slug.
- `Category::resolveFilterValue()` resolves active category.
- `providerCategoryIds()` expands selected category to leaf categories.
- Query filters `service_providers.category_id IN (...)`.

SEO:

- Category filter applies category SEO builder.
- Sitemap creates category URLs as `/service-providers?category={slug}&lang={locale}`.
- Public `/categories/{category:slug}` route exists and is handled by `CategoryController@show`.

### Others Category

The "Others" category is special:

- Registration can use `profession = other`.
- `AuthService` finds root section `Others`, then child `Others`, and assigns that child category.
- Profile update permits category changes only when current category is "Others" or Arabic "أخرى".

Production dependency:

- If the "Others" section/child is renamed or missing, `other` registrations can create providers with null category.

## 7. Provider System

### Profiles

Provider data is stored in `service_providers`. The legacy `service_provider_profiles` table remains but current code comments indicate it is no longer active.

Profile fields include:

- Company/business name.
- Bio.
- Category.
- Location.
- Phone.
- WhatsApp.
- Address.
- Experience.
- Services offered.
- Languages.
- Profile image.
- Gallery images via Spatie media.
- Completion percent.
- Ratings/reviews/endorsements/views.

### Dashboard

Route:

- `GET /service-providers/dashboard`

Controller/service:

- `ProviderDashboardController@index`
- `ProviderDashboardAnalyticsService`

Metrics:

- Views today.
- Views this week.
- Total WhatsApp clicks.
- Engagement rate.
- Daily trends.
- Monthly stats.

Production risk:

- Analytics service queries `analytics.user_id` but live schema lacks it until pending migration is run.

### Media

Profile image:

- Stored manually under `storage/app/public/profile-images`.
- Path stored in `service_providers.profile_image`.

Gallery:

- Spatie media collection `gallery` on `ServiceProvider`.
- Allowed mime types: JPEG, JPG, PNG, WebP.
- Max backend collection file size: 10 MB.
- Conversions: `gallery_thumb` 600x600 WebP and `gallery_large` 1200x1200 WebP, non-queued.

Risks:

- Non-queued conversions can slow uploads.
- Storage symlink/permissions are critical.
- Old location images are intentionally not deleted on update to support undo; cleanup job is needed.

### Recommendations

Endorsements:

- One endorsement per user/provider enforced by unique DB index.
- Only clients can endorse.
- Self-endorsement blocked.
- Toggle increments/decrements `endorsement_count`.

Risk:

- Counter can drift without transaction/reconciliation.

## 8. Client Experience

Full journey:

```text
Landing page
  -> category/location/search entry
  -> service provider listing
  -> filters preserve query string
  -> provider card inspection
  -> provider profile
  -> contact reveal
  -> WhatsApp/contact click
  -> review/rating/recommendation after login
```

Landing page:

- `HomeController@index`.
- Shows cached filter categories, location shortcuts, provider stats, featured providers, latest blogs.

Search:

- Listing search checks provider `company_name`, `bio`, `services_offered`, and owner user `name`.

Provider discovery:

- Listing eager loads user/category/location/media.
- Sorts by `calculated_rating` and `views`.

Profile visit:

- Increments simple view counter.
- Tracks deduplicated analytics view.
- Shows review stats, active reviews, gallery, similar providers, contact reveal UI.

Contact:

- `POST /service-providers/{serviceProvider}/reveal-contact` stores provider ID in session.
- WhatsApp click tracking uses `ServiceProviderAnalyticsController@trackClick`.

Review:

- Clients can submit one review per provider.
- Review remains hidden until admin approval.

## 9. Filter System

### Category Filter

Input:

- `category` on public listing.

Logic:

```php
$selectedCategory = Category::resolveFilterValue($request->input('category'));
$query->whereIn('category_id', $selectedCategory->providerCategoryIds());
```

Behavior:

- Selecting a section or group expands to descendant leaf category IDs.
- Selecting a leaf category filters only that leaf.
- Invalid category produces an empty result through `whereNull('id')`.

### Location Filter

Input:

- `location`.
- optional `exact_location`.

Named clusters:

- `cluster_montreal`: Laval + Montreal.
- `cluster_ottawa`: Ottawa + Gatineau.

Legacy numeric location behavior:

- Numeric location uses cluster expansion unless `exact_location` is present.
- If exact, only selected ID is used.

Query:

```php
$query->whereIn('location_id', $clusterIds);
```

### Search Filter

Search uses SQL `LIKE`:

- `service_providers.company_name`
- `service_providers.bio`
- `service_providers.services_offered`
- `users.name`

Risk:

- No full-text provider search exists; large provider volume will need full-text or external search.

### Multilingual Behavior

Categories and locations expose localized accessors with fallback chain:

- Current locale.
- Translation files.
- Base column.
- Other locale columns.

Provider search itself is not multilingual full-text; it searches stored provider fields directly.

### Legacy Filter Class

`App\Filters\ServiceProviderFilter` exists but is not used by the main listing controller. It references scopes like `availableWeekends()` and columns like `average_rating` that are not defined on current `ServiceProvider`; treat it as legacy/dead code unless repaired.

## 10. Pagination System

Global setup:

- `AppServiceProvider` sets:
  - `Paginator::defaultView('components.pagination.default')`
  - `Paginator::defaultSimpleView('components.pagination.default')`

Custom pagination views:

- `components.pagination.default`
- `components.global-pagination`
- `components.pagination.buttons`
- `components.pagination.mobile`
- `components.pagination.progress`
- `components.pagination.summary`
- vendor pagination views: `admin`, `premium`

Main paginated pages:

- Provider listing: 12 per page, query string preserved.
- Provider reviews on profile: 5 per page using page name `reviews_page`.
- Reviews index: 10 per page.
- Admin users: 20 per page.
- Admin locations: 20 per page.
- Admin notifications: 15 per page.
- Blog index: 9 per page.
- Admin blog posts: 15 per page.
- Provider activity monitor: 15 per page.
- Provider activity detail events: 30 per page.

Why failures happen:

- Multiple paginators on one page must use distinct page names; provider profile handles reviews with `reviews_page`.
- Custom pagination views must accept Laravel paginator variables correctly.
- Query filters disappear if `withQueryString()` is omitted.
- Livewire pagination requires Livewire pagination trait and separate view rules; current active pages are standard Blade pagination.

Debugging checklist:

1. Confirm route query string contains expected `page` or custom page parameter.
2. Confirm controller calls `paginate()` not `get()`.
3. Confirm `withQueryString()` is used on filtered pages.
4. Confirm custom view handles disabled/active links.
5. For multiple paginators, use unique page names.

Future best practice:

- Standardize on one pagination component for public and one for admin.
- Add tests for filtered pagination preserving category/location/search/status.

## 11. Notification System

Architecture:

- Admin creates `admin_notifications` rows.
- Provider read status is stored in `admin_notification_user`.
- Provider navbar gets active notifications via view composer on `components.main-nav`.
- Full notification center supports all/unread/read filters.
- Expired notifications are deleted daily by a scheduled closure in `bootstrap/app.php`.

Creation:

- Route: `POST /admin/notifications`.
- Required fields: title/message in Arabic, English, French.
- `target_type` is hard-coded to `provider_only`.
- `expires_at` is set to 30 days.
- Caches `nav_notifications_{userId}` are cleared for provider users.

Delivery:

- Active scope: `expires_at > now()`.
- View composer caches per provider for 5 minutes.
- Notification page paginates active notifications.

Read/unread:

- Mark individual if `notification_id` is supplied.
- Mark all active if no ID supplied.
- Uses `syncWithoutDetaching`.

Security:

- Notification routes require auth.
- Admin notification CRUD requires auth + admin.
- Current provider notification center does not explicitly restrict to providers; any authenticated user can access it, though content is provider-targeted.

Future improvements:

- Add target segmentation.
- Add scheduled publishing.
- Add provider-only middleware for notification center.

## 12. Review System

Client review creation:

- Must be authenticated client.
- Cannot review own provider.
- Application checks one review per client/provider.
- Review starts `is_active = false`.
- Optional booking association can set `is_verified = true`.

Admin approval:

- `Review::approve(User $admin)` sets:
  - `is_active = true`
  - `admin_approved_by`
  - `admin_approved_at`
  - recalculates provider rating.

Admin rejection:

- `Review::reject(User $admin)` sets:
  - `is_active = false`
  - `admin_approved_by`
  - `admin_approved_at`
  - recalculates provider rating.

Rating formulas:

Provider profile breakdown:

```text
total_count = count(active reviews)
average_rating = avg(active review rating), rounded to 1 decimal
star_percentage[n] = count(star n) / total_count * 100
```

Cached provider rating:

```text
calculated_rating = round(avg(active review rating), 2)
rating = round(avg(active review rating), 1)
0 if no active reviews
```

Recommendation logic:

- `endorsements` is independent from reviews and ratings.
- It is a client "recommend" signal.

Risks:

- No DB unique index for reviews one-per-client/provider.
- Rejected status is inferred rather than explicit.
- Ratings table is separate from reviews and not included in provider score.

## 13. Analytics System

Provider profile views:

- `service_providers.views` increments for non-admin, non-owner visits.
- `TrackProviderViewAction` inserts one `analytics` row per provider/session hash per 24 hours.
- Session hash = SHA-256 of analytics salt + session ID + user agent.
- Admins are excluded.

WhatsApp/contact clicks:

- `TrackProviderClickAction` inserts `analytics` row with an action type such as `click_whatsapp`.
- Admins are excluded.

Provider dashboard:

- Computes today, weekly, monthly views/clicks.
- Engagement rate = clicks / views * 100.

Admin dashboard:

- Computes daily, weekly, monthly WhatsApp clicks and trend percentages.
- Computes most clicked category and top provider performance.

Visitor tracking:

- `TrackVisitor` records GET requests after response.
- Skips admin users.
- Deduplicates same IP hash/user-agent hash within 5 minutes.
- Stores path, referer, optional user ID, visited_at.

Critical current limitation:

- Live `analytics` table has no `user_id`; code uses `user_id` in raw analytics queries. Run or adapt pending migration before relying on dashboards.

## 14. Blog System

Active table:

- `posts`.

Public:

- `/blogs`: list published posts with search/category/location filters.
- `/blogs/{post:slug}`: show published post.

Admin:

- `/admin/blog/posts`: CRUD except show.

Translation:

- Required admin fields: `title_en`, `title_ar`, `title_fr`, `content_en`, `content_ar`, `content_fr`.
- Excerpts and SEO fields per language.
- Fallback logic in model uses current locale, base column, then other locales.

SEO:

- Blog index SEO builder.
- Blog post SEO builder uses localized SEO title/description/keywords, canonical URL, robots, OG image.
- Article structured data is built in `BlogController@show`.

Risks:

- Two inactive blog schemas exist (`blog_posts`, `blog_categories`).
- Image fallback references `images/banner.png`; ensure file exists.

## 15. SEO System

Architecture:

- `SeoMetaService` dispatches to builders:
  - home
  - category
  - provider
  - search
  - blog_index
  - blog_post
- Data object: `SeoData`.
- Rendering: `resources/views/seo/meta.blade.php` uses `{!! SEO::generate() !!}`.

Canonical:

- Defaults to current request URL without query string from `Request::url()`.
- Blog post can use `canonical_url`.

Hreflang:

- Built as current full URL with `?lang={locale}`.
- Sitemap also uses query-parameter language URLs.

Sitemap:

- Command: `php artisan seo:generate-sitemap`.
- Writes `public/sitemap.xml`.
- Includes home, categories as provider search URLs, provider pages, location search URLs, blog index, blog posts.

Current strengths:

- Central SEO builder system.
- Multilingual metadata fields for blog.
- Provider/category dynamic metadata.
- Sitemap generation exists.

Current weaknesses:

- Current `public/sitemap.xml` contains `http://localhost` URLs, indicating generation occurred in local environment.
- `robots.txt` has no sitemap directive.
- Hreflang uses query params rather than localized path structure.
- Category and location SEO pages are mostly query-driven, which is weaker than clean landing pages.
- Search pages correctly use `noindex, follow`.

Roadmap:

1. Regenerate sitemap in production with correct `APP_URL=https://speeda.ca`.
2. Add `Sitemap: https://speeda.ca/sitemap.xml` to robots.
3. Create clean landing pages for `/services/{category}/{city}`.
4. Add schema for LocalBusiness provider pages.
5. Add canonical handling that removes tracking/filter noise.

## 16. Admin Dashboard

Admin pages:

| Page | Purpose | Actions | DB impact | Risks |
| --- | --- | --- | --- | --- |
| `/admin/dashboard` | Marketplace health overview | View metrics | Reads users/providers/categories/locations/posts/reviews/analytics/visitors | May fail analytics aggregates if `analytics.user_id` missing. |
| `/admin/users` | User management | Search/filter/sort, toggle active, delete | Updates/deletes users/providers/related data | Last-admin deactivation guard bug; deletion semantics mix soft and force behavior. |
| `/admin/users/trash` | Soft-deleted users | Restore/force delete | users restore/forceDelete | Related data may already have been deleted. |
| `/admin/categories` | Taxonomy manager | Create/edit/delete/toggle | categories | Slug ambiguity, hierarchy quality. |
| `/admin/locations` | Location manager | Create/edit/delete/activate/deactivate | locations, image files | City uniqueness too broad; old images retained. |
| `/admin/reviews` | Review moderation | Approve/reject/feature/delete | service_provider_reviews, provider ratings | No explicit status enum. |
| `/admin/comments` | Comment moderation | Approve/reject/flag/unflag/delete/restore | comments | Similar status ambiguity. |
| `/admin/notifications` | Provider broadcasts | Create/delete/view | admin_notifications, pivot reads | No segmentation. |
| `/admin/visitors` | Visitor analytics | View/export/live count | visitors read/export | Hashed analytics undercounts rapid browsing. |
| `/admin/provider-activity-monitor` | Provider activity | Search/filter providers, inspect events | analytics/media/provider reads | Broken until `analytics.user_id` handled. |
| `/admin/blog/posts` | Blog CMS | Create/edit/delete posts | posts, uploaded images | Duplicate dormant blog schema. |
| `/admin/activity-logs` | Audit log | View actions | admin_logs read | Sensitive changes may be exposed. |
| `/admin/undo/{log}` | Undo admin change | Undo supported logs | model-dependent | Undo cannot restore deleted files if removed. |
| `/admin/clear-cache` | Operational utility | Clear cache/config/routes/views | framework cache | `config:clear` on production can have side effects if env is misconfigured. |

## 17. Frontend Design System

Layouts:

- `layouts.app`
- `layouts.guest`
- `layouts.navigation`
- `layouts.footer`

Core components:

- Main nav and language switcher.
- Admin sidebar/top bar.
- Buttons, dropdowns, modals, form inputs/errors.
- Rating stars.
- Pagination components.
- Toast notifications.
- Provider completion popup/banner/notification center.
- Notification cards.
- Endorsement button.

Design approach:

- Blade components and view-specific CSS blocks are both used.
- Tailwind-first project exists, but many templates also use Bootstrap utility classes and Font Awesome.
- Admin pages are LTR/English-only and use admin-specific classes.
- Public pages support RTL/LTR through locale and `dir` behavior in layouts/views.

Risks:

- Mixing Tailwind, Bootstrap, inline style blocks, and custom CSS can cause drift.
- Admin CSS is repeated in views instead of one fully centralized design system.
- Font Awesome dependency must load consistently.

Future improvements:

- Consolidate tokens for colors, spacing, typography.
- Build a provider card component shared by home/listing/similar providers.
- Centralize admin table/filter/card styles.

## 18. Performance

Current performance strengths:

- Eager loading on provider listing/profile.
- Counts use `withCount`.
- Category and location caches.
- SEO metadata cache.
- Visitor stats cache.
- Provider dashboard past stats cache.
- Indexed analytics, service provider category/location, visitor timestamps, post status/published date.

N+1 risks:

- Localized category parent traversal in views if parent chains are not eager-loaded.
- Notification read status can query per navbar user unless cache is warm.
- Similar providers include relationships but profile cards must avoid nested lazy access.
- Dynamic `calculatedRating` accessor can query active reviews when relation not loaded.

Image performance:

- Gallery conversions to WebP exist.
- Profile images are not automatically optimized through media library.

Optimization roadmap:

1. Run pending analytics migration or guard analytics queries.
2. Replace dynamic calculated rating accessor with pure cached column accessor.
3. Add full-text indexes/search for provider search.
4. Queue image conversions instead of non-queued for large galleries.
5. Create daily analytics rollups for admin dashboards.
6. Add cache invalidation in observers for category/location changes, not only admin controller paths.

## 19. Security Report

### High Risks

1. Analytics queries reference missing `analytics.user_id`.
   - Impact: provider/admin dashboards can break.
   - Fix: run pending migration or guard queries.

2. Admin last-active-admin deactivation guard is wrong.
   - Current check tests `$user->isAdmin() && !$user->is_active`, which protects inactive admins rather than active admins being deactivated.
   - Impact: possible lockout if last admin is deactivated.
   - Fix: check when target admin is currently active and new status would become inactive.

3. Admin authorization includes configured email list.
   - Impact: config mistake can grant admin access.
   - Fix: document, monitor, and preferably require DB role plus MFA.

### Medium Risks

- Review duplicate prevention is application-only.
- WhatsApp uniqueness is pending migration only.
- File upload validation is present but profile image path handling is manual.
- Admin logs may store sensitive changed values.
- Route `/translations` package exposes translation UI routes; access controls should be audited in package config.
- CSRF token endpoint is public; not inherently dangerous but unnecessary exposure can aid automation.

### Low Risks

- Diagnostic/debug routes are admin/auth protected, but should be disabled in production if not needed.
- Robots file lacks sitemap directive.

Upload controls:

- Profile image: image, jpg/jpeg/png/gif/webp, max 5 MB.
- Gallery: image, jpg/jpeg/png/webp, max 10 MB.
- Certification validation exists in request but controller does not process it in current update flow.
- Blog featured image: jpg/jpeg/png/webp/gif, max 5 MB.

Authorization coverage:

- Admin routes: `auth` + `admin`.
- Provider update: FormRequest owner authorization.
- Gallery delete/replace: owner checks.
- Reviews: client-only FormRequest and controller checks.
- Ratings/endorsements: client-only controller checks.

## 20. Production Deployment

Expected deployment process:

```text
Put app in maintenance mode
Pull/release code
Install PHP dependencies with optimized autoloader
Install/build frontend assets
Run migrations
Clear and rebuild caches
Link storage
Generate sitemap with production APP_URL
Restart queue/PHP-FPM/web server
Smoke test public/admin/provider flows
Disable maintenance mode
```

Checklist:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://speeda.ca`
- Correct DB credentials.
- `APP_KEY` set and stable.
- Mail configured for password reset.
- Storage symlink exists: `php artisan storage:link`.
- Scheduler active for expired notification cleanup.
- Queue worker active if queues are introduced.
- Run pending migrations after duplicate checks.
- Regenerate sitemap in production.

Rollback strategy:

- Keep previous release directory/artifact.
- Backup DB before migrations.
- For risky migrations, test down path or prepare manual rollback SQL.
- Never roll back file storage blindly; media paths may have changed.

## 21. Code Quality

Module scores:

| Module | Score | Notes |
| --- | ---: | --- |
| Routing | 7/10 | Clear monolith route groups; route file is large. |
| Auth | 7/10 | Strong role flow; admin shortcut/config admin needs care. |
| Provider profile | 7/10 | Core business logic works; controller is very large. |
| Category system | 7/10 | Useful hierarchy helpers; data integrity needs enforcement. |
| Filtering | 7/10 | Practical and readable; search is basic SQL LIKE. |
| Reviews | 7/10 | Good moderation flow; needs DB uniqueness/status enum. |
| Analytics | 5/10 | Good privacy intent; current schema/code mismatch is serious. |
| Notifications | 7/10 | Simple and functional; no segmentation. |
| Blog/SEO | 7/10 | Solid localized fields/builders; sitemap currently local URL. |
| Admin | 6/10 | Broad functionality; too much in `AdminController`. |
| Frontend | 6/10 | Feature-rich Blade UI; styling approach is mixed. |
| Tests | 6/10 | Many tests exist; critical current migration mismatch should have regression tests. |

Technical debt:

- Large controllers, especially `AdminController` and `ServiceProviderController`.
- Legacy/dormant tables and classes remain.
- Duplicate blog schemas.
- Mixed CSS/UI systems.
- Analytics migration mismatch.
- Some services import or reference missing/nonexistent services like `FacebookConversionService`, though calls are caught.

## 22. Known Issues

### Critical

1. Analytics `user_id` mismatch.
   - Root cause: pending migration not applied while code references column.
   - Impact: dashboard/activity analytics SQL errors.
   - Affected systems: admin dashboard, provider dashboard, provider activity monitor.
   - Solution: run migration or schema-guard all raw queries.

### High

2. Last admin can be deactivated.
   - Root cause: guard condition checks inactive admin rather than active admin about to be deactivated.
   - Impact: admin lockout.
   - Affected: admin users.
   - Solution: fix condition and add test.

3. Review one-per-provider not enforced by DB.
   - Root cause: no unique index.
   - Impact: duplicates under race conditions.
   - Solution: unique index `(client_id, service_provider_id)`.

4. Sitemap generated with localhost.
   - Root cause: generated in local environment.
   - Impact: SEO harm if deployed.
   - Solution: regenerate in production and add robots sitemap line.

### Medium

5. WhatsApp uniqueness only app-enforced until pending migration.
6. Orphan/root non-section categories exist in live data.
7. Legacy/dormant tables confuse architecture.
8. Provider verified scope does nothing.
9. Category slug route binding can be ambiguous because DB uniqueness is `(slug,parent_id)`.
10. Endorsement counter can drift.
11. Non-queued image conversions can slow requests.
12. Search is not full-text and will degrade at scale.

### Low

13. Diagnostics routes remain available to admins.
14. Public CSRF token route may be unnecessary.
15. Admin UI mixes CSS patterns.
16. `blog_posts`/`blog_categories` empty schema should be documented or removed.

## 23. Future Roadmap

### Phase 1: Stabilize Production

Priorities:

- Run/repair pending migrations.
- Fix analytics `user_id` mismatch.
- Fix last-admin deactivation guard.
- Regenerate production sitemap.
- Add DB unique constraints for review uniqueness and WhatsApp.
- Remove or protect diagnostics in production.

Business impact: high.
Technical impact: high.
SEO impact: medium-high.
Scalability impact: medium.

### Phase 2: Improve Marketplace Quality

Priorities:

- Clean category hierarchy and orphan categories.
- Add provider verification/status workflow.
- Add provider profile quality scoring beyond current four fields.
- Add category/location landing pages.
- Improve provider search.

Business impact: high.
Technical impact: medium.
SEO impact: high.
Scalability impact: medium.

### Phase 3: Monetization and Provider SaaS

Priorities:

- Provider plans/subscriptions.
- Featured placements.
- Lead credit model or monthly subscription.
- Admin revenue dashboard.
- Provider onboarding funnel.

Business impact: very high.
Technical impact: high.
SEO impact: low-medium.
Scalability impact: medium.

### Phase 4: Scale and Intelligence

Priorities:

- Analytics rollups.
- Queue-based media processing.
- Full-text/external search.
- Recommendation ranking.
- Fraud/spam detection.
- API architecture if mobile app or partner integrations become necessary.

Business impact: high.
Technical impact: very high.
SEO impact: medium.
Scalability impact: very high.

## 24. Final CTO Report

### Platform Maturity Score: 6.8/10

Speeda is a real operating Laravel marketplace monolith with provider discovery, multilingual content, admin operations, SEO infrastructure, moderation, and analytics. It is past prototype stage but not yet a fully mature SaaS marketplace.

### Architecture Score: 6.5/10

The monolith is appropriate for current scale. The main weakness is large controllers and legacy schema/code remnants.

### Security Score: 6.5/10

Core auth and authorization are present. Main concerns are admin lockout guard, config-based admin access, route/package exposure audit, and upload/storage hygiene.

### SEO Score: 7/10

SEO architecture is thoughtful and multilingual. Production sitemap/canonical/hreflang execution needs hardening.

### Scalability Score: 5.8/10

Good caching and indexes exist, but analytics, search, image processing, and large controller queries need evolution before major scale.

### Maintainability Score: 6/10

Readable Laravel code, but too many responsibilities sit in controllers and dormant systems create confusion.

### UX Score: 7/10

The client and provider journeys are coherent. The public UI is multilingual; admin is operationally capable. UI consistency should be improved.

### Production Readiness Score: 6.2/10

Production-ready for a small marketplace only after resolving the pending migration mismatch and admin deactivation bug. Not yet ready for aggressive scale or paid SaaS monetization without Phase 1 hardening.

## Appendix A: Current Public Route Map

Key routes:

- `/`
- `/service-providers`
- `/service-providers/{serviceProvider}`
- `/service-providers/{serviceProvider}/reveal-contact`
- `/service-providers/{serviceProvider}/analytics/click`
- `/categories`
- `/categories/{category:slug}`
- `/locations`
- `/blogs`
- `/blogs/{post:slug}`
- `/register`
- `/login` redirects to register
- `/reviews/create/{serviceProvider}`
- `/reviews`
- `/service-providers/{serviceProvider}/rate`
- `/service-providers/{serviceProvider}/endorse`
- `/notifications`

## Appendix B: Current Admin Route Map

- `/admin/dashboard`
- `/admin/users`
- `/admin/users/trash`
- `/admin/categories`
- `/admin/locations`
- `/admin/reviews`
- `/admin/comments`
- `/admin/notifications`
- `/admin/visitors`
- `/admin/provider-activity-monitor`
- `/admin/blog/posts`
- `/admin/activity-logs`
- `/admin/undo/{log}`
- `/admin/clear-cache`

## Appendix C: Current Category Roots and Clusters

Provider listing clusters:

- `cluster_montreal`: Laval and Montreal.
- `cluster_ottawa`: Ottawa and Gatineau.

Homepage location shortcuts:

- `2`: Montreal.
- `1`: Laval.
- `4`: Gatineau.
- `3`: Ottawa.

Current active locations:

- Laval
- Montreal
- Ottawa
- Gatineau
- Toronto
- Vancouver
- Calgary
- Edmonton
- Quebec City
- Longueuil
- Mississauga
- Brampton

## Appendix D: Rebuild-Oriented Mental Model

If rebuilding Speeda from this document, implement in this order:

1. Users/roles/auth/session/password reset.
2. Locations and category hierarchy.
3. Provider profile model with category/location/contact/media.
4. Public provider listing filters.
5. Provider profile pages and contact reveal.
6. Review moderation, ratings, endorsements.
7. Admin CRUD for users/categories/locations/reviews/comments.
8. Notifications.
9. Visitor and provider analytics.
10. Blog CMS and SEO builders.
11. Sitemap and localized metadata.
12. Provider dashboard.
13. Hardening: indexes, queues, cache invalidation, tests, deployment.
