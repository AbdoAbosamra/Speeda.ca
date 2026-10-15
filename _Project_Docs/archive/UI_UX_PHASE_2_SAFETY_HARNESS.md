# Speeda.ca UI/UX Refactor - Phase 2 Safety Harness

Date: 2026-06-30  
Branch: `design-improvement-experiment`  
Scope: Safe verification workflow for UI-only refactoring.

This phase adds a repeatable safety harness before expanding the UI refactor beyond the first admin page slice.

## What Phase 2 Adds

- PowerShell smoke script for the current Windows workspace:
  - `scripts/ui-safety-smoke.ps1`
- Bash smoke script for Linux/macOS/server-like environments:
  - `scripts/ui-safety-smoke.sh`
- Manual visual QA checklist for the most important public, auth, provider, and admin screens.
- Explicit database safety rule: Laravel tests are skipped by default because tests may touch a database depending on local configuration.

## Safe Smoke Command

Windows / PowerShell:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts\ui-safety-smoke.ps1
```

Linux/macOS/Git Bash:

```bash
bash scripts/ui-safety-smoke.sh
```

Optional Laravel tests:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts\ui-safety-smoke.ps1 -RunTests
```

```bash
bash scripts/ui-safety-smoke.sh --run-tests
```

Use test mode only when the local environment is confirmed to use a non-production test database.

## Smoke Script Guardrails

The scripts:

- Refuse to run when `.env` has git-visible changes.
- Refuse to run when `APP_ENV=production`.
- Run `git diff --check`.
- Run `composer validate --no-check-publish --no-interaction`.
- Run `php artisan route:list --except-vendor --no-ansi`.
- Run `php artisan view:cache --no-ansi`.
- Always clear compiled Blade views after the Blade check.
- Run `npm run build` unless explicitly skipped.
- Skip Laravel tests by default.

The scripts do not:

- Run migrations.
- Run seeders.
- Run queue workers.
- Run storage links.
- Modify production data.
- Print `.env` values or secrets.

## Manual Visual QA Checklist

Run the smoke script first, then verify the changed route group manually.

### Public Marketplace

| Screen | Route/Path | Checks |
| --- | --- | --- |
| Home | `/` | Header, hero, provider cards, footer, mobile nav. |
| Categories | `/categories` | Category cards, filters if present, RTL/LTR spacing. |
| Category detail | `/categories/{category}` | Listing content, pagination, empty state. |
| Provider listing | `/service-providers` | Cards, filters, pagination, mobile layout. |
| Provider profile | `/service-providers/{serviceProvider}` | Gallery, ratings, contact actions, review section. |

### Auth

| Screen | Route/Path | Checks |
| --- | --- | --- |
| Login | `/login` | Form layout, validation messages, submit state. |
| Register | `/register` | Role/provider flow, validation messages, mobile layout. |

### Provider Dashboard

| Screen | Route/Path | Checks |
| --- | --- | --- |
| Dashboard | `/service-providers/dashboard` | Charts/cards, navigation, responsive layout. |
| Profile edit | `/service-providers/profile` | Forms, upload controls, save states. |

### Admin

Admin is English-only. Verify copy does not become mixed-language unless the content itself is multilingual.

| Screen | Route/Path | Checks |
| --- | --- | --- |
| Dashboard | `/admin/dashboard` | Stats, cards, empty states, action buttons. |
| Notifications | `/admin/notifications` | Header action, filters, status badges, empty state, modals, pagination. |
| Categories | `/admin/categories` | Filters, tables, actions, modals. |
| Reviews | `/admin/reviews` | Tables, moderation actions, pagination. |
| Comments | `/admin/comments` | Tables, actions, empty state. |
| Locations | `/admin/locations` | Form, table, image previews, actions. |

## Acceptance Criteria For Each UI Refactor Slice

- Smoke script passes.
- Only UI files changed unless explicitly approved.
- No `.env` changes.
- No migrations or data commands required.
- Public pages still support `ar`, `en`, and `fr`.
- Admin pages remain English-only.
- Forms preserve names, methods, routes, CSRF, validation display, and old input behavior.
- Action links and destructive buttons preserve confirmation/authorization behavior.
- New UI uses shared `x-ui.*` primitives where practical.

## Current Phase 2 Status

- Safety scripts added.
- Checklist added.
- PowerShell script verified successfully in the current Windows workspace.
- Bash script added for non-Windows environments, but local syntax execution could not be verified because `bash` is not available in this workspace.
- Ready to run before each larger page migration.

## Non-Blocking Warnings Observed

The first successful PowerShell smoke run reported existing dependency-maintenance warnings:

- Composer warns that `barryvdh/laravel-dompdf` uses an unbound `*` constraint.
- Composer warns that `livewire/volt` uses an exact version constraint.
- Vite/Browserslist warns that browser baseline data is stale.

These warnings do not block the UI safety harness and were not changed in Phase 2 because dependency policy changes are outside this UI refactor slice.
