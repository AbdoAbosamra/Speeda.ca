# Speeda.ca — Feature Inventory

Generated 2026-06-17 from local codebase inspection. Lists every detectable feature with its surface area.

## 1. Registration / Login

- **Purpose**: dual-path signup (client vs. service provider) and authentication with email or provider-phone login, plus an admin login shortcut.
- **Users affected**: clients, providers, admins.
- **Routes**: `GET/POST /register`, `GET/POST /login` (redirects to register), `routes/auth.php` (password reset, email verification, logout).
- **Controllers**: `Auth/RegisteredUserController`, `Auth/AuthenticatedSessionController`, `Auth/PasswordResetLinkController`, `Auth/NewPasswordController`, `Auth/EmailVerification*`.
- **Views**: `resources/views/auth/*.blade.php`.
- **Models**: `User`, `ServiceProvider`.
- **DB tables**: `users`, `service_providers`, `password_reset_tokens`.
- **Current risks**: admin shortcut depends on `config('auth.admin_email')`; phone login only for providers; no MFA.
- **Future improvements**: MFA for admins, granular permission model.

## 2. Provider Profiles

- **Purpose**: provider's public-facing business profile (bio, contact, services, media, ratings).
- **Users affected**: providers (owners), clients (viewers), admins (moderation).
- **Routes**: `/service-providers/{serviceProvider}`, profile update/image/gallery endpoints.
- **Controllers**: `ServiceProviderController`, `ServiceProviderProfileController`, `GalleryController`.
- **Views**: `service-providers/show.blade.php`, `provider/gallery/_grid.blade.php`.
- **Models**: `ServiceProvider` (+ Spatie `Media`).
- **DB tables**: `service_providers`, `media`.
- **Current risks**: `scopeVerified()` is a no-op; profile completion observer fires on every update; non-queued image conversions.
- **Future improvements**: provider verification workflow, queued media conversions.

## 3. Service Provider Listing & Filters

- **Purpose**: searchable/filterable public directory of providers.
- **Users affected**: clients.
- **Routes**: `GET /service-providers?category=&location=&search=`.
- **Controllers**: `ServiceProviderController@index`.
- **Views**: `service-providers/index.blade.php`.
- **Models**: `ServiceProvider`, `Category`, `Location`.
- **DB tables**: `service_providers`, `categories`, `locations`.
- **Current risks**: search is SQL `LIKE`, not full-text; legacy `App\Filters\ServiceProviderFilter` is dead code.
- **Future improvements**: full-text/external search, category/location landing pages.

## 4. Category System

- **Purpose**: hierarchical taxonomy (sections → groups → terminal professions) driving registration, filtering, SEO.
- **Users affected**: providers (registration), clients (filtering), admins (taxonomy management).
- **Routes**: `/categories`, `/categories/{category:slug}`, `/admin/categories`.
- **Controllers**: `CategoryController`, `Admin/AdminController` (categories section).
- **Views**: `categories.blade.php`, `admin/categories/*`.
- **Models**: `Category` (self-referential).
- **Services**: `CategoryCacheService`.
- **DB tables**: `categories`.
- **Current risks**: live data has orphan non-section root categories; slug uniqueness is `(slug, parent_id)`, which can make route-model binding ambiguous; cache isn't invalidated by an observer, only by admin controller paths.
- **Future improvements**: enforce taxonomy levels, dedupe orphan categories.

## 5. Location System

- **Purpose**: Canadian city directory and filter source, with named clusters (Laval+Montreal, Ottawa+Gatineau).
- **Users affected**: providers (registration/profile), clients (filtering), admins.
- **Routes**: `/locations`, `/admin/locations`.
- **Controllers**: `LocationController`, `Admin/AdminController` (locations section).
- **Services**: `LocationCacheService`, `LocationClusterService`.
- **Views**: `location.blade.php`, `admin/locations/index.blade.php`.
- **Models**: `Location`.
- **DB tables**: `locations`.
- **Current risks**: `city` is globally unique (blocks same-name cities in different provinces); recent uncommitted migrations (`2026_06_07_*`) add Ontario cities and normalize signup-city provinces — verify these have been run and reflected in any cached location data before relying on them.
- **Future improvements**: add province/country code, slug, geospatial indexing, SEO landing pages.

## 6. Reviews

- **Purpose**: moderated client text reviews with star ratings, driving provider's average rating.
- **Users affected**: clients (authors), providers (subjects), admins (moderators).
- **Routes**: `/reviews/create/{serviceProvider}`, `POST /reviews`, `/admin/reviews`.
- **Controllers**: `ReviewController`, `Admin/AdminReviewController`.
- **Views**: `reviews/*.blade.php`, `admin/reviews/*.blade.php`.
- **Models**: `Review`.
- **DB tables**: `service_provider_reviews`.
- **Current risks**: no DB-level unique constraint for one-review-per-client-per-provider (app-only check); rejected status inferred rather than explicit enum.
- **Future improvements**: unique `(client_id, service_provider_id)` index, explicit status enum.

## 7. Ratings (lightweight)

- **Purpose**: standalone star rating separate from text reviews.
- **Users affected**: clients, providers.
- **Routes**: `POST /service-providers/{serviceProvider}/rate`.
- **Controllers**: `RatingController`.
- **Models**: `Rating`.
- **DB tables**: `ratings`.
- **Current risks**: not folded into provider's `calculated_rating`; unclear product purpose vs. reviews.
- **Future improvements**: decide whether to merge into review score or keep independent.

## 8. Endorsements (Recommendations)

- **Purpose**: client "recommend this provider" toggle.
- **Users affected**: clients, providers.
- **Routes**: `POST /service-providers/{serviceProvider}/endorse`.
- **Controllers**: `EndorsementController`.
- **Models**: `Endorsement`.
- **DB tables**: `endorsements`.
- **Current risks**: counter cache (`service_providers.endorsement_count`) can drift without a transaction.
- **Future improvements**: periodic reconciliation job.

## 9. Comments

- **Purpose**: polymorphic moderated comments (used on reviews/other commentable models).
- **Users affected**: clients, admins.
- **Routes**: `/comments/*`, `/admin/comments`.
- **Controllers**: `CommentController`, `Admin/AdminCommentController`.
- **Models**: `Comment`.
- **DB tables**: `comments`.
- **Current risks**: moderation states are booleans, not an explicit status enum.

## 10. Notifications (Admin → Provider broadcast)

- **Purpose**: admin-authored multilingual broadcast messages targeted at providers, with read/unread tracking.
- **Users affected**: admins (authors), providers (readers).
- **Routes**: `/notifications`, `/admin/notifications`.
- **Controllers**: `NotificationController`, `Admin/AdminNotificationController`.
- **Views**: `notifications.blade.php`, `admin/notifications/*.blade.php`, `components/notification-card.blade.php`.
- **Models**: `AdminNotification`.
- **DB tables**: `admin_notifications`, `admin_notification_user`.
- **Current risks**: `target_type` only supports `provider_only` (no segmentation); notification center isn't middleware-restricted to providers; expired notifications hard-deleted daily by scheduler.
- **Future improvements**: segmentation, scheduled publishing.

## 11. Blog / Content CMS

- **Purpose**: multilingual blog with SEO metadata, public listing + admin CMS.
- **Users affected**: clients (readers), admins (authors).
- **Routes**: `/blogs`, `/blogs/{post:slug}`, `/admin/blog/posts`.
- **Controllers**: `BlogController`, `Admin/BlogPostController`.
- **Views**: `blog/*.blade.php`, `admin/blog/posts/*.blade.php`.
- **Models**: `Post`.
- **DB tables**: `posts` (active); `blog_posts`/`blog_categories` exist but are dormant/empty — do not confuse with the active schema.
- **Current risks**: duplicate dormant blog schema can mislead future development.

## 12. Admin Dashboard

- **Purpose**: operational overview (users/providers/categories/locations/content counts, analytics trends).
- **Users affected**: admins.
- **Routes**: `/admin/dashboard`.
- **Controllers**: `Admin/AdminController@dashboard`.
- **Views**: `admin/dashboard.blade.php`.
- **Current risks**: depends on `analytics.user_id` column — verify migration state before trusting dashboard numbers.

## 13. Admin User Management

- **Purpose**: CRUD/activate/deactivate/soft-delete/restore users.
- **Routes**: `/admin/users`, `/admin/users/trash`.
- **Controllers**: `Admin/AdminController` (users section).
- **Views**: `admin/users/{index,edit,trash}.blade.php`.
- **DB tables**: `users`.
- **Current risks**: last-admin deactivation guard has an inverted condition (protects inactive admins instead of preventing the last active admin from being deactivated) — verify before relying on it as a safety net.

## 14. Provider Activity Monitor (Admin)

- **Purpose**: admin visibility into individual provider engagement/events.
- **Routes**: `/admin/provider-activity-monitor`.
- **Controllers**: `Admin/ProviderActivityMonitorController`.
- **Services**: `AdminProviderActivityMonitorService`.
- **Views**: `admin/provider_activity_monitor/{index,show}.blade.php`.
- **Current risks**: also depends on `analytics.user_id`.

## 15. Visitor Analytics

- **Purpose**: hashed site-wide visitor tracking and admin dashboard.
- **Routes**: `/admin/visitors`.
- **Controllers**: `Admin/VisitorAnalyticsController`.
- **Services**: `VisitorTrackingService`.
- **Middleware**: `TrackVisitor`.
- **Models**: `Visitor`.
- **DB tables**: `visitors`.
- **Current risks**: hash salt derives from app key — key rotation/leak implications; 5-minute dedup window undercounts rapid browsing.

## 16. Provider Analytics & Dashboard

- **Purpose**: provider-facing view/click metrics (today/week/month, engagement rate).
- **Routes**: `/service-providers/dashboard`, analytics click endpoint, PDF export.
- **Controllers**: `ProviderDashboardController`, `ServiceProviderAnalyticsController`, `ProviderAnalyticsExportController`.
- **Services**: `ProviderDashboardAnalyticsService`.
- **Actions**: `TrackProviderViewAction`, `TrackProviderClickAction`.
- **Models**: `ProviderAnalytics`.
- **DB tables**: `analytics`.
- **Current risks**: same `user_id` column dependency as items 12/14.

## 17. Media / Gallery

- **Purpose**: provider profile image + gallery (Spatie Media Library), with WebP conversions.
- **Routes**: image/gallery upload/replace/delete endpoints under provider profile routes.
- **Controllers**: `ServiceProviderProfileController`, `GalleryController`.
- **Views**: `provider/gallery/_grid.blade.php`, `service-providers/gallery-diagnostic.blade.php`.
- **DB tables**: `media`.
- **Current risks**: non-queued conversions; old images intentionally retained on update (to support undo) without a cleanup job.

## 18. SEO System

- **Purpose**: per-page meta tags, structured data, sitemap generation.
- **Routes**: applies to all public pages; `php artisan seo:generate-sitemap` console command.
- **Services**: `SEOService`, `SeoMetaService` (via `artesaos/seotools`).
- **Views**: `seo/meta.blade.php`, `seo/structured-data.blade.php`.
- **Current risks**: last sitemap generation used `localhost` URLs; `robots.txt` lacks sitemap directive; hreflang via query params, not localized paths.

## 19. Pagination System

- **Purpose**: consistent paginated UI across public/admin pages.
- **Components**: `components/pagination/{default,buttons,mobile,progress,summary}.blade.php`, `components/global-pagination.blade.php`.
- **Setup**: `AppServiceProvider::boot()` registers default paginator views.
- **Current risks**: multiple paginators on one page require distinct page names (e.g., `reviews_page` on provider profile); filters disappear without `withQueryString()`.

## 20. Admin Audit Log / Undo

- **Purpose**: tracks admin create/update/delete/toggle actions and supports reverting them.
- **Routes**: `/admin/activity-logs`, `/admin/undo/{log}`.
- **Controllers**: `Admin/ActivityLogController`, `Admin/UndoController`.
- **Models**: `AdminLog`.
- **DB tables**: `admin_logs`.
- **Current risks**: undo reliability depends on model-specific completeness; logs can contain sensitive changed values.
