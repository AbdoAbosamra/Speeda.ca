# Architecture

Speeda is a single Laravel 12 application. Pages are rendered on the server
with Blade, and there is no separate frontend app. There is no `routes/api.php`
either: the few JSON endpoints (tracking, likes, notification counts) are
ordinary web routes with a session and CSRF. MySQL in production.

## Stack

| Layer | What is used |
| --- | --- |
| Backend | Laravel 12 on PHP 8.4 |
| Views | Blade. Bootstrap 5.3.3 and Font Awesome are loaded from a CDN in `resources/views/layouts/*`. Custom CSS (`resources/css/*`) and Alpine.js (`resources/js/app.js`) are built by Vite. There is no Tailwind and no Livewire. |
| Images | `spatie/laravel-medialibrary`: provider gallery with 600×600 WebP thumbnails generated synchronously (`nonQueued`, no worker needed) |
| SEO | `artesaos/seotools` plus the page builders in `app/Domain/SEO`. `spatie/laravel-sitemap` writes the sitemap. |
| PDF | `barryvdh/laravel-dompdf`: provider analytics export |
| Auth | Breeze-style controllers in `app/Http/Controllers/Auth`. Optional Google sign-in through Socialite. |
| Translations UI | `outhebox/laravel-translations` at `/translations`, admin only |
| Installed but not used by routes | `laravel/sanctum`, `laravel/mcp` |

## Users and roles

`users.role` is `client`, `service_provider` or `admin` (`User::assignRole`).
`User::isAdmin()` is also true for any email in `config('auth.admins')`, which
comes from `ADMINS` and falls back to `ADMIN_EMAIL`. That is a recovery path,
so keep the list short.

A service provider is a `User` plus a `ServiceProvider` row (`hasOne`). A
provider's listing is public only when both the provider row and the user are
active (`ServiceProvider::scopePubliclyVisible`). This lets an admin hide a
listing without blocking the owner's login.

## Domains

| Domain | Main code | Notes |
| --- | --- | --- |
| **Directory** | `CategoryController`, `LocationController`, `ServiceProviderController@index/show`, `CategoryCacheService`, `LocationCacheService`, `LocationClusterService` | Categories form a tree (`parent_id`): sections → categories, with "Others" last. Listing is 12 per page and filters by category, city and city cluster (Montreal+Laval, Ottawa+Gatineau). Providers also list extra cities through `service_areas`. |
| **Provider profile & self-service** | `ServiceProviderController` (profile update, profile image), `GalleryController`, `ProviderDashboardController`, `ProviderAnalyticsExportController`, `CalculateProfileCompletionAction` | Everything sits under `/service-providers/...` behind `auth`, with per-route throttles. |
| **Contact & tracking** | `ServiceProviderController@revealContact`, `ServiceProviderAnalyticsController`, `TrackProviderViewAction`, `TrackProviderClickAction`, `app/Support/WhatsApp.php` | Revealed contacts are remembered in the session. A profile view counts once per visitor session per 24 h. Admins and bots are not counted. |
| **Reputation** | `ReviewController`, `RatingController`, `EndorsementController`, `CommentController`, `ReviewObserver` | New reviews are saved with `is_active=false` and appear only after an admin approves them. `ReviewObserver` recalculates the provider's stored rating. Comments are polymorphic and moderated. |
| **Local Offers** | `OfferController`, `OfferEngagementController`, `OfferTrackingService`, `Admin/AdminOffer*Controller`, `Offer*` models | Deals that admins create for real providers. `Offer::live()` means active, inside its start/end dates, and the provider is publicly visible. Users can like, comment (moderated, honeypot, `offer-comments` rate limiter) and favorite (`/my-favorites`). The WhatsApp hand-off is `/offers/{slug}/whatsapp`. |
| **Content** | `BlogController` + `Admin/BlogPostController` (`Post`), `LegalPageController` (CMS legal pages), `AdminHomeVideoController`, `AdminTestimonialController`, static views | Blog posts and offers store text per language (`*_en/_ar/_fr` or base + `_ar/_fr`). |
| **Email** | `app/Mail/*` (all extend `TemplatedEmail`, which is `ShouldQueue`), `UserEngagementEmailService`, `ProviderEmailJourneyService`, `ProviderBroadcastService` + `SendProviderBroadcastEmail` job, `EmailUnsubscribeController` | Admins can edit the email copy at `/admin/email-templates`. Unsubscribe links are signed URLs, with one-click POST excluded from CSRF. `email_opt_out_at` and `broadcast_opt_out_at` are checked before each send. |
| **In-app notifications** | `AdminNotificationController`, `NotificationController`, `AdminNotification` | Admin-targeted announcements with read receipts. The scheduler deletes expired ones daily. |
| **Admin** | `app/Http/Controllers/Admin/*`, `app/Services/Admin/*`, `LogsAdminActions`, `UndoController` | `/admin/*` sits behind `auth`, `admin`, `admin.locale` (always English) and `throttle:120,1`. Admin actions are logged in `admin_logs`, and many can be undone. Also: visitor analytics, login activity, provider activity monitor, WhatsApp analytics, offer analytics, bulk actions. |
| **SEO** | `app/Domain/SEO/Builders/*` (home, category, provider, search, blog index/post), `SeoMetaService`, `SitemapService` | The sitemap lists home, static pages, categories, publicly visible providers, indexable published posts and live offers. Alternate languages use `?lang=xx`. |
| **Visitor analytics** | `TrackVisitor` middleware, `VisitorTrackingService`, `Visitor` | Counts only real page views: `GET` requests that are not AJAX, JSON, prefetch or bots, and that did not return an error. The visitor's IP and user agent are stored hashed (salt `ANALYTICS_SALT`, falling back to `APP_KEY`), and repeat visits within 5 minutes are dropped. |

## Request pipeline

`bootstrap/app.php` is the single place where middleware, exception rendering
and the closure schedule are configured.

1. Global: Laravel's `TrustProxies` (reads `config/trustedproxy.php`; see
   [OPERATIONS.md](OPERATIONS.md#trusted-proxies)) and `SecurityHeaders`:
   `nosniff`, `X-Frame-Options: SAMEORIGIN`, a referrer policy, a minimal CSP
   (`base-uri`, `object-src`, `frame-ancestors`), `Permissions-Policy`, COOP,
   and HSTS on HTTPS in production.
2. `web` group, appended in this order: `SetLocale`, `TrackVisitor`,
   `CheckUserStatus` (logs out deactivated users), `TrackUserPresence`
   (`last_seen_at`), `ExplainUploadFailures` (turns PHP upload errors into a
   message for each field).
3. Route middleware aliases: `role`, `admin`, `admin.locale`,
   `check.user.status`, `handle.large.uploads`, plus `throttle:*` on most
   write routes.

### Locale

`SetLocale` picks the language in this order: `?lang=` query, then the session,
then the browser's `Accept-Language`, then `APP_FALLBACK_LOCALE` (`en`). The
choice is saved in the session. The layout sets `dir="rtl"` for Arabic. Text
lives in `lang/{en,ar,fr}/*.php`. `php check_translations.php` (run in CI)
fails if a literal key used in the code is missing in any of the three
languages.

### Errors

- HTTP exceptions keep their real status. A 404 stays a 404 and is never
  redirected. `pull-deploy.sh` checks this after each cutover.
- A 419 (expired CSRF token) sends the user back to the form with what they
  typed, minus passwords.
- Any other exception on a `GET` request renders the 500 page. On a form
  submission it redirects back with the input and a flash message. With
  `APP_DEBUG=true`, Laravel's own debug page is shown instead.
- Validation errors return 422 JSON for AJAX requests, and redirect back
  with errors otherwise.

## Key flows

**Registration.** `RegisteredUserController` → `AuthService::registerUser()`
creates the user, plus the provider row when the role is `service_provider`,
in one transaction. The user is then redirected according to their role.
Clients get a welcome email on their first successful login
(`RecordSuccessfulLogin` → `UserEngagementEmailService`).

**Review lifecycle.** A client submits a review (`throttle:20,1`). It is saved
inactive. An admin approves it at `/admin/reviews`. The observer recalculates
the provider's rating. `Review` then calls
`UserEngagementEmailService::handleReviewApproved`, which queues a one-time
email to the client on their 1st and 5th approved review, and a milestone
email to the provider. If the stored ratings ever drift,
`php artisan providers:recalculate-ratings` re-syncs them.

**Provider emails.** A provider gets a welcome email at registration. Later,
`providers:send-journey-emails` (daily) sends one "build your reputation"
email, with up to 3 reminders, to providers whose profile is complete but who
have no reviews yet. The older step-by-step onboarding emails (photo,
services, bio, experience, gallery) were retired in October 2026. Their log
rows are kept for history.

**Admin broadcast.** `/admin/broadcasts`: the admin writes the email, previews
it and sends a test. On send, `ProviderBroadcastService::queue()` creates one
recipient row per provider and dispatches one `SendProviderBroadcastEmail` job
for each. `/admin/broadcasts/{id}/progress` shows the sent and failed counts.
**This needs a queue worker.**

**Offer → WhatsApp.** `/offers/{slug}` shows the offer. "Contact" goes through
`/offers/{slug}/whatsapp` (`throttle:30,1`), which records an `OfferEvent` and
redirects to a WhatsApp message that names the Speeda offer and links to it.

## Where things live

```text
app/Http/Controllers/        public controllers; Admin/ and Auth/ subfolders
app/Services/                business logic (caches, analytics, email, auth)
app/Domain/SEO/              SEO builders, DTO, sitemap
app/Actions/                 single-purpose actions (profile completion, view/click tracking)
app/Support/                 small helpers (AdminHtml::clean, UploadLimits, WhatsApp, SafeJsonLd, ...)
app/Console/Commands/        artisan commands (see README "Useful commands")
routes/web.php, routes/auth.php, routes/console.php (scheduled commands)
config/owner.php             owner phone whitelist, developer badge email, offers WhatsApp number
lang/{en,ar,fr}/             UI strings
resources/views/             Blade; admin/, service-providers/, offers/, seo/, errors/, components/
```

## Things that look live but are not

- `App\Filters\ServiceProviderFilter` is not referenced anywhere. The listing
  filters live in `ServiceProviderController`.
- `Booking`, `ServicePackage`, `Portfolio` and the `saved_providers` table
  have models or relations but no routes or UI.
- `tests/Browser/*` (Dusk) does not run, because Dusk is not installed.
