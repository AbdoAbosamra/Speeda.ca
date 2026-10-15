# Admin Legal Pages CMS

Date: 2026-07-01  
Branch: `design-improvement-experiment`  
Scope: Admin-managed privacy, terms, policies, and custom legal pages.

## Production Safety Boundary

- No `.env` values or secrets are used.
- The migration only creates a new `legal_pages` table.
- Existing Blade legal pages remain in place as fallback content.
- Publishing a CMS page with slug `privacy-policy` or `terms-of-service` overrides the public page.
- Deleting a CMS override restores the static fallback for core legal links.

## Public URLs

| Page type | URL |
| --- | --- |
| Privacy Policy override | `/privacy-policy` |
| Terms of Service override | `/terms-of-service` |
| Custom legal page | `/legal/{slug}` |

Draft pages are hidden from public CMS routes.

## Admin Workflow

1. Open Admin Dashboard -> Policies & Privacy.
2. Choose `Customize` for existing Privacy Policy or Terms of Service, or create a new legal page.
3. Fill English, Arabic, and French title/content fields.
4. Choose `Draft` or `Published`.
5. Save.

Basic HTML is allowed in legal content: headings, paragraphs, lists, links, tables, blockquotes, and emphasis. Script tags, event handlers, forms, and unsafe embedded elements are stripped before saving.

## Deployment

Run migrations during deployment:

```bash
php artisan migrate --force
```

Then refresh Laravel caches normally:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

No seeders are required.

## Verification

Feature test:

```bash
php artisan test tests/Feature/AdminLegalPagesTest.php --no-coverage --no-ansi
```

Current local result:

```text
5 passed, 27 assertions
```

Supporting checks:

```bash
php artisan route:list --except-vendor --no-ansi
php artisan view:cache --no-ansi
php artisan view:clear --no-ansi
npm run build
```

