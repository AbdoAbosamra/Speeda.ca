# Admin Targeted Notifications

Date: 2026-06-30  
Branch: `design-improvement-experiment`  
Scope: Admin-created notifications for all service providers or selected service providers only.

## Production Safety Boundary

- No production data is required to enable this feature.
- No `.env` values or secrets are stored in code.
- The migration is additive and creates only a new pivot table.
- Existing notifications remain broadcasts unless selected provider rows are attached.
- If the pivot table has not been migrated yet, provider-facing notification queries fall back to the previous broadcast behavior.

## What Changed

Admins can now choose recipients when creating an admin notification:

| Mode | Behavior |
| --- | --- |
| All Service Providers | Notification is visible to every active service provider. No pivot rows are stored. |
| Selected Service Providers | Notification is visible only to the selected active service providers. |

Provider-facing notification pages, navbar dropdown counts, unread counts, and mark-as-read behavior all use the same visibility scope.

## Database

New migration:

```text
database/migrations/2026_06_30_000002_create_admin_notification_service_provider_table.php
```

New table:

```text
admin_notification_service_provider
```

Columns:

| Column | Purpose |
| --- | --- |
| `admin_notification_id` | Notification being targeted. |
| `service_provider_id` | Service provider allowed to see it. |
| `created_at`, `updated_at` | Pivot timestamps. |

Indexes and foreign keys use short explicit names for MySQL compatibility.

## Deployment Notes

Run the migration during normal deployment:

```bash
php artisan migrate --force
```

After migration:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Do not run seeders on production for this feature.

## Admin Workflow

1. Open Admin Dashboard -> Notifications -> Create Notification.
2. Choose `All Service Providers` or `Selected Service Providers`.
3. If selected mode is used, choose one or more active providers.
4. Fill Arabic, English, and French title/message fields.
5. Preview and send.

The index screen shows whether a notification is broadcast or targeted, including the selected provider names in the details modal.

## Provider Visibility Rules

- Broadcast notifications have no selected provider pivot rows and are visible to every service provider.
- Targeted notifications have one or more selected provider pivot rows and are visible only to those providers.
- Users without a service provider profile do not receive provider notifications.
- Inactive provider accounts cannot be selected as targeted recipients.
- A provider cannot mark a targeted notification as read unless it is visible to that provider.

## Cache Behavior

Navbar notification cache keys keep the existing format:

```text
nav_notifications_{user_id}
```

Cache invalidation is scoped:

- Broadcast create/delete clears all provider notification caches.
- Targeted create/delete clears only the selected providers' notification caches.

## Verification

Targeted feature tests:

```bash
php artisan test tests/Feature/AdminNotificationTargetingTest.php --no-coverage --no-ansi
```

Current local result:

```text
8 passed, 35 assertions
```

Safe supporting checks:

```bash
php artisan route:list --except-vendor --no-ansi
php artisan view:cache --no-ansi
php artisan view:clear --no-ansi
npm run build
```

