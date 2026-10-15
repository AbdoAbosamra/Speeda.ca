# Speeda.ca UI/UX Refactor - Phase 3 Token Stabilization

Date: 2026-06-30  
Branch: `design-improvement-experiment`  
Scope: Design token stabilization across Vite CSS entrypoints.

## Objective

Phase 3 creates one canonical `sp-*` token layer that can be used across public, provider, profile, and admin UI work without forcing a visual rewrite.

This is a production-safe stabilization step:

- No controllers changed.
- No models changed.
- No routes changed.
- No migrations or database commands added.
- No `.env` or secret values touched.

## Files Changed

| File | Change |
| --- | --- |
| `resources/css/foundation/design-tokens.css` | Expanded canonical `--sp-*` token aliases to understand existing legacy token names. |
| `resources/css/providers.css` | Imports the shared token layer. |
| `resources/css/provider-profile.css` | Imports the shared token layer. |
| `resources/css/app.css` | Already imports shared tokens and UI primitive CSS. |
| `resources/views/admin/notifications/index.blade.php` | Converts selected page-local visual values to `--sp-*` tokens as the first Phase 3 adoption slice. |

## Token Compatibility Map

The canonical token layer now supports the three existing naming styles found during audit:

| Canonical Token | Main App Legacy | Provider Listing Legacy | Provider Profile Legacy |
| --- | --- | --- | --- |
| `--sp-color-primary` | `--speeda-blue` | `--primary` | `--primary-color` |
| `--sp-color-primary-hover` | `--speeda-blue-dark` | `--primary-dark` | `--secondary-color` |
| `--sp-color-accent` | `--speeda-gold` | `--accent` | `--accent-color` |
| `--sp-color-success` | `--speeda-green` | `--success` | fallback |
| `--sp-color-danger` | fallback | `--danger` | fallback |
| `--sp-color-border` | `--border-light` | `--gray-200` | fallback |
| `--sp-color-text` | `--text-primary` | `--gray-900` | `--dark-text` |
| `--sp-color-text-muted` | `--speeda-text-muted` | `--gray-500` | fallback |
| `--sp-shadow-md` | `--shadow-md` | fallback | `--card-shadow` |
| `--sp-shadow-lg` | `--shadow-lg` | fallback | `--card-hover-shadow` |

## Why This Is Safe

The token layer is mostly additive. Existing legacy variables remain in place and existing styles continue to resolve. Future components can now use `--sp-*` variables consistently even when rendered on pages that compile `providers.css` or `provider-profile.css` instead of only `app.css`.

## First Adoption Slice

`resources/views/admin/notifications/index.blade.php` now uses `--sp-*` tokens for selected page-local styles:

- Card surfaces.
- Borders.
- Radius values.
- Text colors.
- Muted text.
- Status colors.
- Soft surfaces.
- Hover shadow timing.

Routes, forms, modals, filters, pagination, delete actions, and data rendering were not changed.

## Verification

Run:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts\ui-safety-smoke.ps1
```

Expected:

- Environment guard passes.
- `git diff --check` passes.
- `composer validate` passes with only known non-blocking dependency warnings.
- `php artisan route:list` passes.
- `php artisan view:cache` passes.
- `php artisan view:clear` runs.
- `npm run build` passes.
- Laravel tests remain skipped unless a non-production test DB is confirmed.

