# Filament v5 + SEO Blog Migration Blueprint

Date: 2026-04-25
Project: Speeda
Prepared from live repository audit, not from assumptions.

## Executive Summary

This repository is not ready for a same-day big-bang replacement of the current Blade admin with Filament v5.

The safe path is a phased migration with parallel operation:

1. Keep public routes and provider flows stable.
2. Introduce Filament as a new admin shell behind the existing `auth` + `admin` gate.
3. Rebuild modules one-by-one using the current domain models and services.
4. Switch old `/admin/*` routes to Filament pages only after validation parity is complete.
5. Add the public blog as a new isolated capability that reuses the existing SEO stack and multilingual conventions.

## Current-State Findings

### Verified from codebase

- Framework is currently Laravel 12, not Laravel 11. Source: `composer.json`.
- Filament is not currently installed. No Filament packages were found in `composer.lock`.
- The current admin is Blade-based under `resources/views/admin/*` with route/controller logic in:
  - `routes/web.php`
  - `app/Http/Controllers/Admin/AdminController.php`
  - `app/Http/Controllers/Admin/AdminReviewController.php`
  - `app/Http/Controllers/Admin/AdminNotificationController.php`
  - `app/Http/Controllers/Admin/VisitorAnalyticsController.php`
- SEO infrastructure already exists:
  - `app/Domain/SEO/DTOs/SeoData.php`
  - `app/Domain/SEO/Services/SeoMetaService.php`
  - `app/Domain/SEO/Services/SitemapService.php`
  - `artesaos/seotools`
  - `spatie/laravel-sitemap`
- Public site locale handling currently uses `?lang=ar|en|fr` via `app/Http/Middleware/SetLocale.php`.
- A draft blog start already exists but is not production-ready:
  - `app/Models/Post.php`
  - `database/migrations/2026_04_23_100728_create_posts_table.php`
- Homepage already attempts to show latest posts in `app/Http/Controllers/HomeController.php`.

### Critical safety observations

- There are already uncommitted in-flight changes in the worktree, including SEO, routes, admin, and blog-related files.
- Current admin user deletion paths are destructive and must not be mirrored directly into Filament without policy and workflow hardening.
- Current SEO cache invalidation is type-based and does not yet include a blog builder.
- Current sitemap service does not include blog URLs.
- Current locale strategy is query-parameter-based, so blog hreflang and canonical logic must respect that to avoid SEO regressions.

## Existing Module Inventory

### Public system already present

- Homepage
- Categories and grouped filters
- Service provider listing
- Service provider detail pages
- Reviews
- Ratings
- Endorsements / recommendations
- Comments
- Notifications for providers
- Locale switching
- SEO meta generation
- Sitemap generation
- Provider analytics
- Gallery management
- Profile completion logic

### Current admin capabilities already present

- Dashboard statistics
- Categories management
- Users management
- Soft-deleted user recovery
- Locations management
- Visitor analytics
- Reviews moderation
- Comments moderation
- Notifications management
- Cache clearing
- Activity logs
- Undo actions

### Missing or incomplete versus target brief

- Filament v5 admin shell
- Blog public listing/detail routes
- Production-grade multilingual blog schema
- Blog admin workflows
- Blog SEO builder
- Blog sitemap inclusion
- Blog permissions and approval workflow
- Full admin parity map for every legacy action
- Enterprise dashboard widgets and monitoring pages

## Target Architecture

## 1. Domain Direction

Do not rewrite business logic into Filament resources.

Use this layering:

- Eloquent models remain source of truth.
- Extract admin write behavior into Actions/Services where needed.
- Filament Resources and Pages act as UI orchestration only.
- Public website continues using controllers + Blade/Livewire as appropriate.
- Shared SEO uses `SeoMetaService`.

This avoids duplicate logic between old admin, new admin, and public site.

## 2. Filament Panel Strategy

Introduce a dedicated admin panel:

- Panel path: `/admin`
- Auth guard: existing web guard
- Access gate: existing `User::isAdmin()`
- Language: English only
- Translation keys in admin UI: do not use for panel labels, headings, and actions

Recommended structure:

- `app/Providers/Filament/AdminPanelProvider.php`
- `app/Filament/Resources/*`
- `app/Filament/Pages/*`
- `app/Filament/Widgets/*`
- `app/Filament/Actions/*` when resource-specific actions become complex

### Panel groups

- Dashboard
- People
- Providers
- Moderation
- Taxonomy
- Content
- SEO
- Analytics
- Notifications
- Reports
- System

## 3. Blog System Architecture

Use a dedicated content model, not the current minimal `Post` shape as-is.

Recommended models:

- `BlogPost`
- `BlogCategory`
- `BlogPostTranslation`
- `BlogCategoryTranslation`

Alternative acceptable schema:

- one `blog_posts` table with explicit per-locale columns
- one `blog_categories` table with explicit per-locale columns

Because the existing project already uses per-locale columns on `categories`, the lowest-risk fit is explicit columns rather than introducing a new translation package.

Recommended tables:

### `blog_categories`

- `id`
- `slug`
- `name_en`
- `name_ar`
- `name_fr`
- `description_en`
- `description_ar`
- `description_fr`
- `seo_title_en`
- `seo_title_ar`
- `seo_title_fr`
- `seo_description_en`
- `seo_description_ar`
- `seo_description_fr`
- `seo_keywords_en`
- `seo_keywords_ar`
- `seo_keywords_fr`
- `canonical_url`
- `is_active`
- `is_featured`
- `sort_order`
- timestamps
- soft deletes

### `blog_posts`

- `id`
- `author_id`
- `blog_category_id` nullable
- `slug`
- `title_en`
- `title_ar`
- `title_fr`
- `excerpt_en`
- `excerpt_ar`
- `excerpt_fr`
- `content_en`
- `content_ar`
- `content_fr`
- `featured_image`
- `featured_image_alt_en`
- `featured_image_alt_ar`
- `featured_image_alt_fr`
- `seo_title_en`
- `seo_title_ar`
- `seo_title_fr`
- `seo_description_en`
- `seo_description_ar`
- `seo_description_fr`
- `seo_keywords_en`
- `seo_keywords_ar`
- `seo_keywords_fr`
- `og_title_en`
- `og_title_ar`
- `og_title_fr`
- `og_description_en`
- `og_description_ar`
- `og_description_fr`
- `og_image`
- `twitter_title_en`
- `twitter_title_ar`
- `twitter_title_fr`
- `twitter_description_en`
- `twitter_description_ar`
- `twitter_description_fr`
- `twitter_image`
- `schema_type` default `Article`
- `status` enum: `draft`, `scheduled`, `published`, `archived`
- `published_at` nullable
- `is_featured`
- `reading_time_minutes`
- `allow_indexing`
- `canonical_url` nullable
- `meta_robots` default `index,follow`
- `created_by`
- `updated_by`
- timestamps
- soft deletes

### `blog_post_related`

- `blog_post_id`
- `related_blog_post_id`

This supports featured, related, multilingual SEO, scheduled publishing, and admin editorial control without touching provider/user tables.

## 4. Blog SEO Structure

Add a blog-specific SEO builder family inside the existing SEO domain:

- `BlogIndexSeoBuilder`
- `BlogPostSeoBuilder`
- `BlogCategorySeoBuilder`

Extend `SeoMetaService` builder registry with:

- `blog_index`
- `blog_post`
- `blog_category`

Each blog detail page must generate:

- dynamic `<title>`
- dynamic meta description
- canonical URL
- Open Graph title, description, image
- Twitter Card title, description, image
- Article schema JSON-LD
- hreflang alternates using the current locale pattern

Canonical strategy:

- Canonical should point to the published blog URL for the same locale representation strategy used site-wide.
- Since locale is currently query-based, canonical must be consistent with `?lang=xx` handling until the entire public routing strategy is changed site-wide.

Slug strategy:

- One shared canonical slug per post is safest for the current system.
- Localized titles should not produce separate physical records.
- Enforce unique slugs across all blog posts.

Validation rules:

- slug unique
- SEO title required for each locale
- SEO description required for each locale
- featured image required before publishing
- content required for each locale before publishing
- `published_at` required when status = `scheduled`

## 5. Public Route Strategy

Recommended new public routes:

- `GET /blogs` -> blog index
- `GET /blogs/{post:slug}` -> blog detail
- `GET /blog-categories/{category:slug}` optional only if category landing pages are desired

Do not change existing provider/category URLs.

Use explicit controller classes:

- `BlogController@index`
- `BlogController@show`
- `BlogCategoryController@show` if category pages are enabled

### Homepage integration

Homepage can safely add a latest blogs section using published posts only:

- limit 3 to 6 items
- cached
- hide section if there are no published posts

## 6. Filament Resource Map

Recommended first-class Filament resources/pages:

### Dashboard

- `Dashboard` page
- widgets:
  - User stats
  - Provider stats
  - Review moderation queue
  - Blog publishing queue
  - Visitor trends
  - Notification health
  - Profile completion distribution

### People

- `UserResource`

### Providers

- `ServiceProviderResource`
- relation managers:
  - Reviews
  - Gallery Media
  - Endorsements
  - Analytics snapshots

### Moderation

- `ReviewResource`
- `CommentResource`
- `RecommendationResource` if recommendations gain direct admin management beyond endorsements

### Taxonomy

- `CategoryResource`
- `LocationResource`
- grouped filters page or relation-aware category management page

### Content

- `BlogPostResource`
- `BlogCategoryResource`

### Notifications

- `AdminNotificationResource`

### Analytics and reports

- custom pages instead of pure resources:
  - `VisitorAnalyticsPage`
  - `ProviderAnalyticsPage`
  - `ReportsExportPage`

### SEO and system

- `SeoAuditPage`
- `SitemapPage`
- `SystemHealthPage`
- `ActivityLogResource`

## 7. Legacy-to-Filament Migration Map

### Keep first

- models
- policies
- services
- route names used by public website
- moderation logic in review model
- provider analytics services
- SEO services

### Replace gradually

- Blade admin views
- admin controllers that only coordinate CRUD
- manual table UIs
- dashboard statistics rendering

### Preserve carefully

- Undo behavior for admin changes
- review approval side effects
- provider rating recalculation
- cache invalidation after taxonomy changes
- soft delete recovery flows
- provider gallery media handling
- active/inactive visibility rules

## 8. Database Strategy

### Hard rules

- No destructive schema edits to existing production tables in phase 1.
- No foreign key changes on `users`, `service_providers`, `service_provider_reviews`, categories, notifications, or analytics tables during panel bootstrap.
- New blog tables only in phase 1.
- Additive columns only when truly needed.

### Sequence

1. Create new blog tables.
2. Add indexes on blog slugs, status, `published_at`, and category foreign keys.
3. Seed optional default blog categories.
4. Deploy public reads before admin writes if needed.
5. Enable Filament panel after admin auth validation.

### Rollback-friendly migration rules

- Every migration in this program should be additive.
- Never rename existing production columns in the same release as a panel swap.
- Never delete old admin code until parity is signed off.

## 9. Permissions and Security

Use policies plus Filament authorization hooks.

Roles:

- Admin: full blog access
- Service provider: no Filament admin access
- Client: no Filament admin access

Recommended permissions even if role-based only:

- `admin.access`
- `blog.view`
- `blog.create`
- `blog.update`
- `blog.publish`
- `blog.schedule`
- `blog.delete`
- `seo.manage`
- `reports.export`
- `reviews.approve`
- `notifications.manage`

If a permission package is not already present, keep the first release role-gated through policies and add granular permissions in phase 2.

## 10. Validation Matrix

### Blog

- create draft
- save translations
- upload featured image
- publish immediately
- schedule future publication
- related posts render
- latest posts render on homepage
- search and pagination work
- sitemap includes published posts only
- canonical and hreflang output valid

### Existing modules

- admin login
- dashboard widgets
- users list/edit/soft delete/restore
- provider visibility and edit flows
- review approve/reject/feature
- notification create/delete
- location create/update/activate/deactivate
- category create/update/toggle
- visitor analytics page and export
- provider public profile still loads
- provider gallery still works
- provider ratings still recalculate

## 11. Safe Deployment Strategy

### Pre-deployment

1. Create a staging snapshot from production data.
2. Run all new migrations only on staging first.
3. Validate blog publishing and Filament admin login on staging.
4. Diff route list before and after deployment.
5. Validate generated SEO markup on representative pages:
   - home
   - category
   - provider
   - blog index
   - blog post

### Deployment order

1. Deploy code with feature flags off.
2. Run additive blog migrations.
3. Warm config, route, and view caches.
4. Smoke-test public site.
5. Enable Filament for internal admins only.
6. Run parity checklist.
7. Switch `/admin` entrypoint to Filament only after sign-off.

### Fallback plan

- Keep legacy admin routes available behind a temporary fallback prefix such as `/legacy-admin`.
- Do not delete legacy Blade views during the first Filament release.
- Use a config flag:
  - `admin.driver=legacy|filament`

### Rollback strategy

- If panel issue only: switch `admin.driver` back to `legacy`.
- If blog public issue only: disable blog routes by feature flag.
- If migration issue occurs: stop rollout, restore code, do not roll back non-destructive tables unless absolutely necessary.

## 12. Production Rollout Plan

### Phase 0: audit and stabilization

- freeze unrelated admin changes
- resolve existing uncommitted blog/SEO work
- finalize source-of-truth business rules
- install Filament in a branch only after dependency approval

### Phase 1: blog foundation

- create production-safe blog schema
- add blog public routes and views
- add blog SEO builders and sitemap entries
- add homepage latest blogs section
- ship with no admin cutover yet

### Phase 2: Filament bootstrap

- install Filament
- create admin panel provider
- implement dashboard + auth + navigation skeleton
- ship behind feature flag

### Phase 3: parity modules

- users
- providers
- reviews
- comments
- categories
- locations
- notifications
- analytics
- reports
- SEO tools

### Phase 4: admin cutover

- redirect `/admin` to Filament
- keep legacy fallback route for one release cycle
- remove legacy only after parity confirmation

## 13. Key Risks and Mitigations

### Risk: breaking current admin workflows

Mitigation:

- run legacy and Filament in parallel
- map every old route/action to a Filament destination before cutover

### Risk: SEO regression

Mitigation:

- reuse existing SEO service
- add dedicated blog builders
- validate canonical, hreflang, sitemap, and schema outputs on staging

### Risk: provider/user relationship damage

Mitigation:

- no schema rewrites on core tables in initial releases
- no foreign key churn
- no bulk data migrations affecting providers/users

### Risk: duplicate content

Mitigation:

- unique blog slug enforcement
- canonical URL generation
- draft/scheduled content excluded from sitemap and indexing

### Risk: destructive admin actions in new panel

Mitigation:

- soft-delete-first policies
- confirmation modals
- restricted force-delete actions
- audit logging

### Risk: multilingual inconsistency

Mitigation:

- require all three locales before publish
- locale-specific validation and preview
- locale-aware SEO field completeness checks

## 14. Required Code Changes for Implementation Phase

This section is the target implementation map, not a claim that these files already exist.

### New files expected

- `app/Providers/Filament/AdminPanelProvider.php`
- `app/Filament/Resources/BlogPostResource.php`
- `app/Filament/Resources/BlogCategoryResource.php`
- `app/Filament/Resources/UserResource.php`
- `app/Filament/Resources/ServiceProviderResource.php`
- `app/Filament/Resources/ReviewResource.php`
- `app/Filament/Resources/CategoryResource.php`
- `app/Filament/Resources/LocationResource.php`
- `app/Filament/Resources/AdminNotificationResource.php`
- `app/Filament/Pages/*`
- `app/Filament/Widgets/*`
- `app/Http/Controllers/BlogController.php`
- `app/Http/Controllers/BlogCategoryController.php`
- `app/Models/BlogPost.php`
- `app/Models/BlogCategory.php`
- `app/Policies/BlogPostPolicy.php`
- `app/Policies/BlogCategoryPolicy.php`
- `app/Domain/SEO/Builders/BlogIndexSeoBuilder.php`
- `app/Domain/SEO/Builders/BlogPostSeoBuilder.php`
- `app/Domain/SEO/Builders/BlogCategorySeoBuilder.php`
- `resources/views/blog/index.blade.php`
- `resources/views/blog/show.blade.php`
- `resources/views/components/blog/*`
- `database/migrations/*create_blog_categories_table.php`
- `database/migrations/*create_blog_posts_table.php`
- `database/migrations/*create_blog_post_related_table.php`

### Existing files expected to change

- `composer.json`
- `composer.lock`
- `routes/web.php`
- `app/Domain/SEO/Services/SeoMetaService.php`
- `app/Domain/SEO/Services/SitemapService.php`
- `app/Http/Controllers/HomeController.php`
- `resources/views/home.blade.php`
- `bootstrap/providers.php` or provider registration path used by this app

## 15. Detailed Changelog For This Turn

### File modified

- `_Project_Docs/FILAMENT_V5_BLOG_MIGRATION_BLUEPRINT_2026-04-25.md`

### New resources created

- none in application runtime

### New models created

- none

### New migrations created

- none

### New Filament resources created

- none

### New SEO logic added

- none in runtime code
- blueprint defines the target blog SEO extension strategy

### Why changed

- to provide a production-safe, repo-specific migration architecture before any risky dependency install or admin cutover

### Why safe for production

- documentation-only change
- no runtime behavior altered
- no schema changes
- no route changes
- no cache changes

### Backend impact

- none in this turn

### Frontend impact

- none in this turn

### Admin impact

- none in this turn

## Recommended Next Implementation Slice

The safest next code slice is:

1. install Filament v5 in a feature branch
2. add the admin panel provider behind a feature flag
3. build only the new blog domain + public pages first
4. integrate blog SEO and sitemap
5. then begin admin parity module migration

This keeps the first functional release additive and reversible.
