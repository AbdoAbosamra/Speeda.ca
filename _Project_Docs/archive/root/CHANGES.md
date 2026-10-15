## [2026-04-12] — TASK-1: Public provider gallery display

**Files modified:**
- `app/Http/Controllers/ServiceProviderController.php` — eager loaded gallery media for the public show page and passed a read-only `galleryImages` payload to the view
- `resources/views/service-providers/show.blade.php` — added a public gallery grid with an Alpine lightbox between the bio and reviews sections

**Reason:** Visitors need a read-only version of provider gallery images on the public profile page.
**Risk:** LOW
**Safe because:** The change only reads existing media records and renders them without edit or delete controls.
**Verified by:** Open a public provider profile with gallery images and confirm the section appears after the bio, opens a lightbox, and disappears entirely when no gallery images exist.

## [2026-04-12] — TASK-2: Client registration without phone

**Files modified:**
- `app/Http/Requests/Auth/RegisterRequest.php` — removed client phone validation and allowed client registrations without a submitted name
- `app/Services/AuthService.php` — generated a safe fallback client display name when the registration form omits it
- `resources/views/auth/register.blade.php` — hid the client phone flow and updated register-role toggling so the client path only shows essential credential inputs

**Reason:** Client signup should stop collecting phone numbers at registration time.
**Risk:** LOW
**Safe because:** No schema or existing phone data was changed; extra client phone input is now ignored instead of validated or saved.
**Verified by:** Submit the client registration flow with email, password, and password confirmation only, then confirm registration succeeds and provider registration still shows its extra fields.

## [2026-04-12] — TASK-3: Two-cluster provider location filter

**Files modified:**
- `app/Http/Controllers/ServiceProviderController.php` — replaced the public listing dropdown data with the two allowed cluster keys and resolved cluster filters before querying providers
- `app/Services/LocationClusterService.php` — added named cluster key resolution for `cluster_montreal` and `cluster_ottawa` while preserving the legacy city-based cluster logic
- `resources/views/service-providers/index.blade.php` — updated the location dropdown to render the two fixed cluster options

**Reason:** The provider listing location filter must use broader metropolitan clusters instead of raw city records.
**Risk:** LOW
**Safe because:** Existing city-cluster logic remains intact and the new filter only narrows the public dropdown choices and query inputs.
**Verified by:** Select each location option on `/service-providers`, confirm only matching cluster providers are returned, and verify empty selection still shows all providers.

## [2026-04-12] — TASK-4: Inline change annotations and repository change log

**Files modified:**
- `app/Http/Controllers/ServiceProviderController.php` — annotated changed controller blocks with structured `@change` comments
- `app/Http/Requests/Auth/RegisterRequest.php` — annotated the registration rule update
- `app/Services/AuthService.php` — annotated the fallback client-name logic
- `app/Services/LocationClusterService.php` — annotated the named cluster entry point
- `resources/views/auth/register.blade.php` — annotated the client registration UI and role-toggle updates
- `resources/views/service-providers/index.blade.php` — annotated the restricted location dropdown block
- `resources/views/service-providers/show.blade.php` — annotated the public gallery styles and markup
- `CHANGES.md` — created the persistent repository-level change ledger

**Reason:** The project now needs durable in-code change annotations and a root change history file for future traceability.
**Risk:** LOW
**Safe because:** The annotations are comments only and the markdown log is additive documentation.
**Verified by:** Review each modified file for a nearby `@change` marker and confirm this `CHANGES.md` file lists all tasks completed in this session.

## [2026-04-14] — TASK-5: Notification UX cleanup and read-state sync

**Files modified:**
- `app/Http/Controllers/NotificationController.php` — tightened provider read-state updates, ignored invalid notification ids, and returned unread-count metadata for the dropdown UI
- `resources/views/admin/dashboard.blade.php` — converted the dashboard notification quick action into a dedicated management card with the requested CTA
- `resources/views/admin/notifications/index.blade.php` — replaced the harsh one-line preview with a cleaner multi-line preview and reinforced the full-content modal path
- `resources/views/components/admin-top-bar.blade.php` — removed unused admin notification dropdown scaffolding so the admin navbar no longer duplicates notification management
- `resources/views/components/main-nav.blade.php` — improved provider unread highlighting, fixed the bell pulse animation, and synchronized badge removal when notifications are opened or clicked

**Reason:** Notification management should live on the dedicated admin page while providers get a clearer and more reliable read/unread experience.
**Risk:** LOW
**Safe because:** The changes are additive UI/controller refinements on top of the existing notification system and do not alter or delete stored notification data.
**Verified by:** Open `/admin/dashboard` to confirm the bell is gone and the notification card routes to `/admin/notifications`, review a long notification preview and modal on `/admin/notifications`, and open the provider dropdown to confirm unread styling and the badge clear immediately and stay cleared after reload.
