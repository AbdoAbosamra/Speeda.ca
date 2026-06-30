# Claude Project Upload Checklist — Speeda.ca

Generated 2026-06-17.

## Files to Upload First

1. `_Project_Docs/SPEEDA_AI_MASTER_INSTRUCTIONS.md` — paste into Project Instructions, not as a regular file, if the tool supports a separate instructions field.
2. `_Project_Docs/SPEEDA_ARCHITECTURE_SUMMARY.md`
3. `_Project_Docs/SPEEDA_FEATURE_INVENTORY.md`
4. `_Project_Docs/SPEEDA_RISKS_AND_KNOWN_ISSUES.md`
5. `_Project_Docs/SPEEDA_OFFICIAL_MASTER_DOCUMENTATION.md` (the full ~1900-line audit — upload if the tool's context window can hold it; otherwise keep as a reference you paste excerpts from)
6. `routes/web.php`, `routes/auth.php`
7. `app/Models/*.php` (all 17)
8. `composer.json`, `package.json`

## Files to Upload if Claude Needs More Context

- `app/Http/Controllers/**/*.php` (all controllers) — upload incrementally per feature area being discussed rather than all at once, since `AdminController.php` and `ServiceProviderController.php` are large
- `app/Services/*.php`, `app/Actions/*.php`
- `app/Http/Middleware/*.php`
- `app/Http/Requests/**/*.php`
- `resources/views/admin/**/*.blade.php` (when working on admin features)
- `resources/views/service-providers/*.blade.php` (when working on the marketplace UI)
- `lang/ar/*.php`, `lang/en/*.php`, `lang/fr/*.php` (when working on translations or copy)
- `database/migrations/*` (when working on schema changes — upload the relevant subset by keyword, e.g. `*locations*`, `*categories*`, `*reviews*`)
- `resources/views/components/*.blade.php` (when working on shared UI)
- `tests/Feature/**/*.php` (when asking Claude to write or extend tests)

## Files Never to Upload

```text
.env
.env.*
storage/logs/*
bootstrap/cache/*
vendor/*
node_modules/*
public/storage/private/*
database/*.sqlite
*.key
*.pem
*.crt
*.sql
*.dump
*.zip
```

Also avoid uploading:
- `app/Http/Controllers/Admin/AdminController.php.backup`, `routes/web.php.bak`, `resources/views/home.blade.php.backup` — stale, not loaded by the app; uploading them risks Claude treating them as current.
- Any file under `storage/app/public/` containing real uploaded user/provider images or documents.

## Recommended Project Instructions

Use the full content of `SPEEDA_AI_MASTER_INSTRUCTIONS.md` as the Claude Project's custom instructions. It covers project identity, absolute rules (no API, no destructive migrations, no category/Others-category deletion, ar/en/fr public site, English-only admin, changelog requirement), technical standards, current core systems, and the required 10-part response format.

## Suggested First Message to Claude

```text
You're helping with Speeda.ca, a live Laravel 12 / Blade / MySQL service
marketplace for Arabic-speaking users in Canada (ar/en/fr public site,
English-only admin). I've uploaded the project instructions and context
docs. Before suggesting any change:

1. Confirm you've read SPEEDA_AI_MASTER_INSTRUCTIONS.md and will follow
   its absolute rules and response format.
2. Ask me to confirm current migration/DB state before assuming any
   schema column exists — the docs flag a known analytics.user_id
   mismatch and other schema risks in SPEEDA_RISKS_AND_KNOWN_ISSUES.md.
3. Tell me which additional files (if any) you need before starting.

Here's what I want to work on: [describe task]
```

## How to Ask Claude for Changes Safely

- Always state which environment you mean (local/staging/production) before asking for migrations, seeders, or cache-clearing commands.
- Ask Claude to show the diff/plan before applying changes to controllers/services that touch `service_providers`, `categories`, `users`, or `analytics` — these are the highest-risk tables.
- For anything touching the category tree, explicitly remind Claude that the "Others" category must never be deleted and that taxonomy resets are forbidden.
- For anything touching translations, require all three locale files (`ar`, `en`, `fr`) to be updated together, never just one.
- For anything touching `AdminController.php` or `ServiceProviderController.php`, ask Claude to flag whether new logic should go into a Service/Action class instead of growing the controller further.
- Require a changelog-style summary (what changed, why) at the end of every response that proposes code changes, per the required response format.
- Never let Claude run destructive commands (`migrate:fresh`, `db:wipe`, force deletes) without explicit, scoped confirmation of target environment.
