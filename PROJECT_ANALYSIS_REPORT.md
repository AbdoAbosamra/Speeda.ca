# PROJECT ANALYSIS REPORT — Speeda

**Generated:** 2026-04-14  
**Laravel Version:** 12.56.0  
**PHP Version:** 8.4.13  
**Environment:** Production (Deployed)  
**Locales:** ar / en / fr  
**Architecture:** Fullstack (Blade + Controllers + Models)

---

## 1. SYSTEM OVERVIEW

### What the Project Does
Speeda is a **service provider discovery and review platform** operating in Canada (primarily Montreal/Laval and Ottawa/Gatineau regions). It allows:
- **Clients** to browse, filter, and review service providers
- **Service Providers** to manage profiles, upload galleries, view analytics
- **Admins** to moderate reviews/comments, manage categories/locations, monitor visitors

### Core Architecture
```
Request → SetLocale Middleware → TrackVisitor Middleware → CheckUserStatus Middleware
    → Route → Controller → Model → Blade View
```

- **Routing:** All routes in `routes/web.php` (no API routes)
- **Rendering:** Server-side Blade templates with Alpine.js for interactivity
- **State:** Session-based (locale, revealed contacts, auth)
- **Queue:** Not used (all operations synchronous)
- **Cache:** Used for location clusters (6h TTL), provider dashboard stats (10m TTL)
- **Media:** Spatie MediaLibrary for gallery images (WebP conversions)

---

## 2. MODULE BREAKDOWN

### 2.1 Service Providers Module
**Purpose:** Core business — listing, filtering, and displaying service providers  
**Main Files:**
- `ServiceProviderController.php` (859 lines)
- `ServiceProvider.php` (Model, ~400 lines)
- `ServiceProviderFilter.php` (Filter pipeline)
- `resources/views/service-providers/index.blade.php` (2379 lines)
- `resources/views/service-providers/show.blade.php` (3130 lines)

**Data Flow:**
1. Request → `ServiceProviderController::index()` or `show()`
2. Query builder with eager loading (`user`, `category`, `location`, `media`)
3. Raw subquery for `live_rating` from reviews table
4. Paginated results → Blade view

**Dependencies:** Category, Location, Review, User, MediaLibrary, Analytics

---

### 2.2 Reviews System
**Purpose:** Client reviews with admin approval workflow  
**Main Files:**
- `ReviewController.php` (270 lines)
- `Review.php` (Model)
- `StoreReviewRequest.php`, `UpdateReviewRequest.php`
- Admin: `AdminReviewController.php`

**Data Flow:**
1. Client submits review → `ReviewController::store()`
2. Review created with `is_active = false` (pending approval)
3. Admin approves → `Review::approve()` → recalculates provider rating
4. Only `is_active = true` reviews appear publicly

**Dependencies:** ServiceProvider, User (client + admin), Booking (optional), Comment

---

### 2.3 Recommend/Endorsement System
**Purpose:** Users can endorse providers (separate from reviews)  
**Main Files:**
- `EndorsementController.php`
- `Endorsement.php` (Model)
- `components/endorsement-button.blade.php`

**Data Flow:**
1. Authenticated user clicks endorse → `EndorsementController::toggle()`
2. Creates/deletes endorsement record
3. `endorsement_count` incremented on ServiceProvider

**Dependencies:** ServiceProvider, User

---

### 2.4 Filters System (CRITICAL)
**Purpose:** Filter service providers by category, location, search  
**Main Files:**
- `ServiceProviderFilter.php` (filter pipeline — NOT used in current index!)
- `ServiceProviderController::index()` (inline filter logic)
- `LocationClusterService.php` (city clustering)
- `Category::resolveFilterValue()` / `providerCategoryIds()`

**Data Flow:**
1. Request with query params (`category`, `location`, `search`)
2. Controller builds query with conditional WHERE clauses
3. Category: resolves slug/ID → gets all descendant IDs → WHERE IN
4. Location: cluster mapping → WHERE IN location_id
5. Order by `live_rating DESC`, `views DESC`
6. Paginate(12)

**⚠️ CRITICAL ISSUE:** `ServiceProviderFilter.php` exists but is **NOT USED**. The filter logic is duplicated inline in `ServiceProviderController::index()`. This is dead code.

---

### 2.5 Categories System
**Purpose:** Hierarchical category tree (sections → subcategories → leaf)  
**Main Files:**
- `Category.php` (Model, ~300 lines)
- `CategoryController.php`
- Admin: `AdminController::categories()` + CRUD

**Hierarchy:** 3 levels deep
- Level 0: Sections (is_section=true, parent_id=null)
- Level 1: Subcategories (is_section=false, parent_id=section)
- Level 2: Leaf categories (is_section=false, no children)

**Data Flow:**
1. `Category::filterGroups()` returns all leaf categories whose parent chain ends at a section
2. `providerCategoryIds()` traverses descendants and excludes non-leaf parents
3. Multilingual names via `name_ar`, `name_en`, `name_fr` columns

---

### 2.6 Profile System
**Purpose:** Service provider profile management with completion tracking  
**Main Files:**
- `ServiceProviderController::update()` (profile update)
- `ProfileController.php` (user profile)
- `CalculateProfileCompletionAction.php`
- `ServiceProviderObserver.php`
- `UpdateServiceProviderProfileRequest.php`

**Completion Scoring:**
- Profile photo: 30%
- Experience years: 30%
- Address: 20%
- Bio: 10%
- Gallery (≥4 images): 5%
- Services offered: 5%

**Data Flow:**
1. Provider submits profile update → `UpdateServiceProviderProfileRequest` validation
2. DB transaction → handle image uploads → update fields
3. Observer triggers `CalculateProfileCompletionAction`
4. `updateQuietly()` saves completion % without re-triggering observer

---

### 2.7 Analytics System
**Purpose:** Track provider views, clicks, and engagement  
**Main Files:**
- `ProviderDashboardAnalyticsService.php`
- `ServiceProviderAnalyticsController.php`
- `TrackProviderViewAction.php`
- `TrackProviderClickAction.php`
- `AdminProviderActivityMonitorService.php`
- `VisitorTrackingService.php`
- `TrackVisitor.php` (middleware)

**Data Flow:**
1. Page visit → `TrackProviderViewAction` → writes to `analytics` table (session fingerprint, 24h dedup)
2. WhatsApp click → `TrackProviderClickAction` → writes to `analytics` table
3. Dashboard reads → `ProviderDashboardAnalyticsService` (cached 10m for past data, live for today)
4. Admin monitors → `AdminProviderActivityMonitorService` (paginated query)

---

### 2.8 Translation System
**Purpose:** Multi-language support (ar/en/fr)  
**Main Files:**
- `TranslationService.php` (Google Translate API fallback — NOT USED)
- `SetLocale.php` (middleware)
- `LocaleController.php`
- `lang/ar/`, `lang/en/`, `lang/fr/` (27 files each = 81 total)

**How it works:**
1. `SetLocale` middleware runs on every request
2. Checks session → browser Accept-Language → fallback to 'en'
3. `App::setLocale()` sets the locale
4. Blade uses `__('key')` to translate
5. Model attributes use `getLocalizedNameAttribute()` for dynamic content

---

### 2.9 Navigation System
**Purpose:** Main navbar with notifications, user info, language switcher  
**Main Files:**
- `components/main-nav.blade.php` (refactored)
- `components/language-switcher.blade.php`
- `components/notification-card.blade.php`

**Data Flow:**
1. `AppServiceProvider::boot()` → View composer shares notifications to `main-nav`
2. For service providers: loads `AdminNotification` records + read status
3. JavaScript handles dropdown toggle, mark-as-read fetch, mobile menu

---

## 3. DATA FLOW EXPLANATION

### Typical Request Flow: Browse Service Providers
```
GET /service-providers?category=plumbing&location=cluster_montreal
    ↓
SetLocale middleware (sets locale from session)
    ↓
TrackVisitor middleware (records visitor)
    ↓
CheckUserStatus middleware (verifies user is_active)
    ↓
ServiceProviderController::index()
    ↓
QueryBuilder: ServiceProvider::with(['user','category','location','media'])
    ↓
Filter: WHERE category_id IN (descendant IDs)
    ↓
Filter: WHERE location_id IN (cluster IDs)
    ↓
Subquery: SELECT AVG(rating) FROM reviews WHERE is_active=true
    ↓
ORDER BY live_rating DESC, views DESC
    ↓
paginate(12)
    ↓
View: service-providers/index.blade.php (2379 lines of inline CSS/JS)
    ↓
HTML response to browser
```

---

## 4. CURRENT STRENGTHS

| # | Strength | Details |
|---|----------|---------|
| S1 | **Robust review approval workflow** | Reviews require admin approval before appearing publicly. Rating recalculation is automatic on approve/reject. |
| S2 | **Good use of Form Requests** | Validation is properly separated into dedicated Form Request classes. |
| S3 | **Profile completion observer pattern** | Uses `ServiceProviderObserver` + `CalculateProfileCompletionAction` — clean separation, avoids infinite loops with `updateQuietly()`. |
| S4 | **Location clustering** | `LocationClusterService` with 6-hour cache is a smart approach for Montreal/Laval and Ottawa/Gatineau. |
| S5 | **Session-based privacy** | `revealed_contacts` session key ensures only the user who clicked sees contact info. |
| S6 | **Throttle protection** | Rate limiting on contact reveal (5/min), gallery uploads (20-30/min), profile updates (10/min). |
| S7 | **Comprehensive exception handling** | `bootstrap/app.php` handles ValidationException, AuthenticationException, AuthorizationException, TokenMismatchException, PostTooLargeException, ModelNotFoundException. |
| S8 | **Admin activity logging** | `LogsAdminActions` trait + `AdminLog` model provides audit trail with undo capability. |
| S9 | **Spatie MediaLibrary integration** | Proper WebP conversions, file type validation, size limits. |
| S10 | **SEO service exists** | `SEOService` class generates meta tags and JSON-LD structured data. |
| S11 | **Facebook Conversion API** | `FacebookConversionService` tracks ViewContent and Lead events server-side. |
| S12 | **Reduced motion support** | `@media (prefers-reduced-motion: reduce)` CSS in main-nav. |
| S13 | **Comprehensive test coverage** | 40+ test files across Feature, Unit, Browser, Performance, Integration. |

---

## 5. WEAKNESSES & ISSUES

### W1: **NO SEO META TAGS RENDERED IN VIEWS**
**Severity: HIGH**

`SEOService` exists and generates meta tags, but **it is never called** in any Blade view. The home page, service provider index, and provider show pages all have hardcoded `<title>` tags with no Open Graph, description, or canonical URLs.

**Why it's wrong:** Search engines cannot properly index pages. Social media shares will not display rich previews.

**Best solution:** Create a Blade component `<x-seo-meta>` that calls `SEOService` and renders all meta tags. Inject into `<head>` of every page.

**Impact:** HIGH

---

### W2: **DEAD CODE: ServiceProviderFilter.php**
**Severity: MEDIUM**

The entire `ServiceProviderFilter` class is never invoked. Filter logic is duplicated inline in `ServiceProviderController::index()`. This creates maintenance burden and inconsistency risk.

**Why it's wrong:** Two filter implementations exist. If one is updated, the other becomes stale.

**Best solution:** Either remove `ServiceProviderFilter.php` OR refactor the controller to use it. Since the inline version is more current, delete the dead class.

**Impact:** MEDIUM

---

### W3: **MONOLITHIC BLADE VIEWS**
**Severity: MEDIUM**

- `service-providers/show.blade.php`: **3130 lines** (all inline CSS + JS + HTML)
- `service-providers/index.blade.php`: **2379 lines** (all inline CSS + JS + HTML)
- `home.blade.php`: **1456 lines** (all inline CSS + JS + HTML)

**Why it's wrong:** Impossible to maintain. No CSS/JS bundling. No caching of static assets. Every page reload re-downloads thousands of lines of CSS.

**Best solution:** Extract CSS into `public/css/` files, JS into `public/js/` files. Use `<link>` and `<script src>` tags. Keep Blade views under 500 lines.

**Impact:** MEDIUM (performance), HIGH (maintainability)

---

### W4: **N+1 QUERY RISK IN `similarProviders`**
**Severity: MEDIUM**

In `ServiceProviderController::show()`:
```php
$similarProviders = ServiceProvider::with(['category', 'location', 'user', 'media'])
    ->where('category_id', $serviceProvider->category_id)
    ->orderBy('rating', 'desc') // ← Uses STALE stored rating, not live_rating
    ->limit(4)
    ->get();
```

Orders by `rating` (stored column) instead of `live_rating` (subquery). This means similar providers are sorted by potentially stale data.

**Best solution:** Add the same `live_rating` subquery or use `activeReviews()->avg('rating')`.

**Impact:** MEDIUM

---

### W5: **Category Description Accessor Returns Empty String**
**Severity: LOW**

```php
// Category.php
public function getLocalizedDescriptionAttribute(): string
{
    return '';
}
```

The description accessor always returns `''`. The docstring mentions template generation, but the implementation was removed.

**Best solution:** Either implement description generation or remove the accessor + its `appends` entry.

**Impact:** LOW

---

### W6: **TranslationService is Effectively Dead Code**
**Severity: LOW**

`TranslationService::translate()` returns `null` unless Google Translate API key is configured (which it isn't). The dictionary fallback was intentionally removed.

**Why it's wrong:** This service is imported but non-functional. If any code path calls it, it returns null silently.

**Best solution:** Either configure Google Translate API or remove the service. Add admin panel for manual translation management.

**Impact:** LOW

---

### W7: **Missing `is_active` Index on ServiceProvider**
**Severity: MEDIUM**

The `is_active` column exists on `service_providers` but is **not indexed**. The query `whereHas('user', fn($q) => $q->where('is_active', true))` performs a full table scan on every listing page load.

**Best solution:** Add index on `service_providers.is_active` and `users.is_active`.

**Impact:** MEDIUM (performance at scale)

---

### W8: **`review_text` Can Be NULL But Not Validated**
**Severity: LOW**

`StoreReviewRequest` allows `review_text` to be nullable, but a review with only a rating and no text has limited value.

**Best solution:** Require `review_text` with minimum length (e.g., 10 chars) for quality control.

**Impact:** LOW

---

### W9: **No Pagination on Provider Dashboard Analytics**
**Severity: LOW**

`AdminProviderActivityMonitorService::getProviderDetails()` paginates events at 30 per page, but `ProviderDashboardAnalyticsService` has no pagination — it loads all analytics for a provider.

**Best solution:** Add date range parameters with default "last 30 days".

**Impact:** LOW

---

### W10: **Hard-coded Admin Email Check**
**Severity: MEDIUM**

```php
// User.php
$admins = array_filter(array_map('trim', explode(',', env('ADMINS', env('ADMIN_EMAIL', '')))));
```

Calling `env()` at runtime is **strongly discouraged** in Laravel. In production with config caching, `env()` returns `null`.

**Best solution:** Move to `config('app.admin_emails')` and use the config system.

**Impact:** MEDIUM (production config caching breaks this)

---

## 6. BUGS FOUND

| # | Bug | Location | Severity |
|---|-----|----------|----------|
| B1 | **`env()` called at runtime** — returns `null` when config is cached | `User::isAdmin()` | HIGH |
| B2 | **`similarProviders` orders by stale `rating`** instead of live `live_rating` | `ServiceProviderController::show()` | MEDIUM |
| B3 | **Category description accessor always returns `''`** — broken feature | `Category::getLocalizedDescriptionAttribute()` | LOW |
| B4 | **`ServiceProviderFilter.php` is dead code** — not used anywhere | `app/Filters/ServiceProviderFilter.php` | MEDIUM |
| B5 | **Review `rating_breakdown` column** exists but is never populated or used | `Review.php` | LOW |
| B6 | **Home page uses `home.css` + `home.js`** but provider pages have 2000+ lines of inline CSS/JS — inconsistent asset loading | Views | LOW |

---

## 7. PERFORMANCE ISSUES

| # | Issue | Location | Impact |
|---|-------|----------|--------|
| P1 | **No meta tags rendered** — SEOService exists but never called | All Blade views | HIGH (SEO) |
| P2 | **Inline CSS/JS in every page** — no browser caching of static assets | All views | HIGH (load time) |
| P3 | **`live_rating` subquery runs per provider** in index — efficient but not cached | `ServiceProviderController::index()` | MEDIUM |
| P4 | **`activeReviews()->count()` called per provider** in cards without eager loading | Provider card components | MEDIUM (N+1 risk) |
| P5 | **Location cluster resolution loads ALL locations** in `resolveClusterCities()` | `LocationClusterService` | LOW (cached, but inefficient) |
| P6 | **No Redis/OpCache mention** — cache driver defaults to file | `config/cache.php` | MEDIUM |
| P7 | **`ServiceProviderController::show()` does 6+ queries** before rendering | Single page load | MEDIUM |
| P8 | **`similarProviders` query uses `rating` (stale) + missing `is_active` filter** | `ServiceProviderController::show()` | LOW |
| P9 | **`getMedia('gallery')` called twice** in show() — once in controller, once in view | `ServiceProviderController` + view | LOW |
| P10 | **No HTTP compression** for large HTML responses (2000+ line views) | Server config | LOW |

---

## 8. UX PROBLEMS

| # | Issue | Description | Impact |
|---|-------|-------------|--------|
| U1 | **No visual feedback on review submission** — review goes to "pending approval" but user may not understand | Review flow | MEDIUM |
| U2 | **Provider show page has NO navigation component** — uses its own navbar instead of `main-nav` component | `show.blade.php` | MEDIUM (inconsistency) |
| U3 | **Home page has no link to `main-nav` component** — uses its own navbar | `home.blade.php` | LOW |
| U4 | **Category filter shows ALL 55+ categories** in dropdown — no grouping by section | Provider index | MEDIUM |
| U5 | **No loading states** on AJAX calls (gallery upload, endorse, reveal contact) | Various | LOW |
| U6 | **Mobile menu on provider show page is different** from main-nav mobile menu | UX inconsistency | LOW |

---

## 9. TRANSLATION ISSUES

| # | Issue | Description | Impact |
|---|-------|-------------|--------|
| T1 | **`dir` attribute inconsistent** — home.blade.php checks `['ar','he','ur','fa']` but show.blade.php checks only `app()->getLocale() === 'ar'` | RTL support | MEDIUM |
| T2 | **Category descriptions always empty** — `getLocalizedDescriptionAttribute()` returns `''` | Category pages | MEDIUM |
| T3 | **TranslationService is non-functional** — returns null without API key | Dynamic content | LOW |
| T4 | **Some blade views use hardcoded English text** instead of translation keys | Various views | LOW |
| T5 | **81 language files to maintain** — no automated sync between ar/en/fr | Maintenance | MEDIUM |

---

## 10. SEO ISSUES

| # | Issue | Description | Impact |
|---|-------|-------------|--------|
| SE1 | **No Open Graph meta tags** — `og:title`, `og:description`, `og:image` not rendered | Social sharing | HIGH |
| SE2 | **No canonical URLs** — duplicate content risk | Google indexing | HIGH |
| SE3 | **No meta description** on provider show pages | Search snippets | HIGH |
| SE4 | **No JSON-LD structured data** rendered (service exists but not called) | Rich results | MEDIUM |
| SE5 | **No sitemap.xml route** (exists as artisan command only) | Search engine crawling | MEDIUM |
| SE6 | **No robots.txt dynamic rules** — static file only | Crawl control | LOW |

---

## 11. IMPROVEMENT PLAN

### Phase 1: Critical Fixes (Production-Safe, Low Risk)

| Step | Action | Files | Risk |
|------|--------|-------|------|
| 1.1 | Replace `env()` calls with config | `User::isAdmin()` | LOW |
| 1.2 | Add `is_active` indexes | New migration | LOW |
| 1.3 | Render SEO meta tags in views | All Blade views | LOW |
| 1.4 | Fix `similarProviders` ordering | `ServiceProviderController::show()` | LOW |
| 1.5 | Fix RTL dir attribute consistency | `show.blade.php` | LOW |

### Phase 2: Performance (Medium Risk)

| Step | Action | Files | Risk |
|------|--------|-------|------|
| 2.1 | Extract inline CSS to external files | All views | MEDIUM |
| 2.2 | Extract inline JS to external files | All views | MEDIUM |
| 2.3 | Add Redis cache driver | `config/cache.php` | MEDIUM |
| 2.4 | Cache `similarProviders` query | `ServiceProviderController` | LOW |
| 2.5 | Optimize `resolveClusterCities()` | `LocationClusterService` | LOW |

### Phase 3: Architecture (Higher Risk, Plan Carefully)

| Step | Action | Files | Risk |
|------|--------|-------|------|
| 3.1 | Remove dead `ServiceProviderFilter.php` | `app/Filters/` | LOW |
| 3.2 | Implement Service layer for provider queries | New service class | MEDIUM |
| 3.3 | Extract Blade components for reusable UI | Various views | MEDIUM |
| 3.4 | Add queued jobs for media conversions | Spatie config | MEDIUM |
| 3.5 | Add API resource endpoints (optional) | New controllers | HIGH |

---

## 12. PRIORITY LIST

### 🔴 CRITICAL
1. Fix `env()` runtime call in `User::isAdmin()` (B1)
2. Add SEO meta tags to all pages (SE1, SE2, SE3)
3. Add `is_active` database indexes (W7)

### 🟡 IMPORTANT
4. Fix `similarProviders` stale rating sort (B2, W4)
5. Fix RTL dir attribute inconsistency (T1)
6. Extract CSS/JS from monolithic Blade views (W3, P2)
7. Remove dead `ServiceProviderFilter.php` (W2, B4)
8. Fix Category description accessor (W5, B3)

### 🟢 NICE-TO-HAVE
9. Configure Redis cache driver (P6)
10. Add loading states to AJAX interactions (U5)
11. Require minimum review text length (W8)
12. Add date range filter to analytics dashboard (W9)
13. Configure Google Translate API or remove service (W6)
14. Add sitemap.xml route (SE5)

---

## 13. FINAL RECOMMENDATIONS

### Immediate (This Week)
1. **Fix the `env()` bug** — This is silently broken in production if config is cached. Replace with `config('app.admin_emails')`.
2. **Add SEO meta rendering** — The `SEOService` is ready; just needs to be called from views. Create a `<x-seo-meta>` component.
3. **Add missing indexes** — `service_providers.is_active`, `users.is_active`, `service_provider_reviews.is_active`.

### Short-term (Next Sprint)
4. **CSS/JS extraction** — Move inline styles/scripts to external files. This alone will reduce page size by 30-50%.
5. **Fix `similarProviders`** — Use `live_rating` subquery instead of stale `rating` column.
6. **RTL consistency** — Ensure `dir` attribute logic is consistent across all views.

### Medium-term (Next Quarter)
7. **Component-based Blade architecture** — Break 3000-line views into composable components.
8. **Redis cache driver** — Replace file cache with Redis for shared caching across workers.
9. **Service layer abstraction** — Move complex query logic from controllers into dedicated services.
10. **Translation management** — Build admin UI for managing translation keys across 3 languages.

### Long-term (6+ Months)
11. **API layer** — If mobile app or third-party integration is planned, build JSON API endpoints using the existing `ServiceProviderResource`.
12. **Queue system** — Move Spatie media conversions and email notifications to queued jobs.
13. **Database read replicas** — If traffic scales, consider read replicas for the heavy listing queries.

---

## DETAILED CHANGELOG (SUGGESTED FIXES)

| File | Change Type | Risk Level | Why Safe |
|------|-------------|------------|----------|
| `app/Models/User.php` | Replace `env()` with `config()` | LOW | Config is source of truth; env() is unreliable after config:cache |
| `database/migrations/*` | Add `is_active` indexes on `service_providers` and `users` | LOW | Additive-only migration, no data changes |
| `resources/views/layouts/app.blade.php` | Add `<x-seo-meta>` component call | LOW | Adds meta tags; no existing functionality affected |
| `app/Http/Controllers/ServiceProviderController.php` | Fix `similarProviders` to use `live_rating` | LOW | Changes sort order to match index page; no data loss |
| `resources/views/service-providers/show.blade.php` | Fix `dir` attribute logic | LOW | Only affects RTL rendering; makes consistent with home |
| `app/Models/Category.php` | Fix or remove `getLocalizedDescriptionAttribute` | LOW | Currently returns empty string; any change is an improvement |
| `app/Filters/ServiceProviderFilter.php` | Delete file | LOW | Dead code — never referenced anywhere |
| `resources/views/**/*.blade.php` | Extract CSS/JS to external files | MEDIUM | No logic changes; only asset location changes |

---

*End of Report — Generated by Principal Frontend Architect & System Design Analysis*
