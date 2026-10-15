# AI Context Files for Speeda.ca — File Selection Report

Generated 2026-06-17. This report tells you which files/folders to upload to a Claude Project (or any AI tool) to give it working context on Speeda.ca, grouped by priority.

## Priority 1 — Must Upload

These are required for any AI agent to understand the system at all.

| File/Folder | Why it matters | System | What an AI agent learns |
| --- | --- | --- | --- |
| `routes/web.php` | Defines the entire public + admin route surface (~328 lines) | Routing | Every URL, controller binding, middleware applied per route |
| `routes/auth.php` | Auth-specific routes (Breeze-style) | Auth | Login/register/password-reset/verification route wiring |
| `composer.json` | PHP dependencies, autoload map, scripts | Project setup | Laravel version (12), key packages (Sanctum, Livewire, Spatie media/sitemap, SEO tools, dompdf) |
| `package.json` | Frontend build tooling | Project setup | Vite, Tailwind/Alpine usage, no React/Vue |
| `app/Models/*.php` | All 17 Eloquent models | Data layer | Relationships, business logic (rating calc, completion %, localized accessors) |
| `app/Http/Controllers/**/*.php` | All controllers (public + Admin/) | Request handling | Where business logic actually executes; identifies oversized controllers |
| `app/Services/*.php` | 9 service classes | Business logic | Caching strategy, analytics, location clustering, auth orchestration |
| `app/Actions/*.php` | 3 action classes | Business logic | Single-purpose operations (profile completion, view/click tracking) |
| `app/Http/Middleware/*.php` | 6 middleware classes | Request pipeline | Locale switching, visitor tracking, admin gating, large upload handling |
| `app/Http/Requests/**/*.php` | FormRequest validation classes | Validation | Validation + authorization rules per action |
| `resources/views/layouts/*.blade.php` | App/guest/navigation/footer layouts | Frontend | Page shell structure, RTL/LTR handling |
| `resources/views/components/*.blade.php` | 30+ shared Blade components | Frontend | Reusable UI building blocks (pagination, modals, nav, notifications) |
| `resources/views/admin/**/*.blade.php` | All admin views | Admin UI | What the English-only admin back office actually renders |
| `resources/views/auth/*.blade.php` | Auth views | Auth UI | Login/register/password reset forms |
| `resources/views/service-providers/*.blade.php` | Listing, show, dashboard, gallery diagnostic views | Core marketplace UI | The primary client-facing marketplace experience |
| `lang/ar/*.php`, `lang/en/*.php`, `lang/fr/*.php` | ~28 translation files per locale | Multilingual | Full vocabulary of the public site in all 3 languages |
| `database/migrations/*locations*` | Location table evolution | Schema | Current `locations` schema, recent Ontario city additions |
| `database/migrations/*users*` (incl. `0001_01_01_000000_create_users_table.php`, admin-role/soft-deletes migrations) | Users table evolution | Schema | Auth/role schema |
| `database/migrations/*categories*` | Categories table evolution incl. hierarchy refactor | Schema | Taxonomy schema history |
| `database/migrations/*service_provider_reviews*`, `*ratings*` | Review/rating schema | Schema | How reviews/ratings are structured and moderated |
| `database/migrations/*admin_notifications*` | Notification schema | Schema | Admin → provider broadcast schema |
| `_Project_Docs/SPEEDA_OFFICIAL_MASTER_DOCUMENTATION.md` | Existing full encyclopedic audit (2026-05-25) | Everything | Deepest available single source of truth — read this first |
| `_Project_Docs/SPEEDA_ARCHITECTURE_SUMMARY.md` | This bundle's condensed architecture map | Everything | Fast orientation without reading 1900 lines |
| `_Project_Docs/SPEEDA_AI_MASTER_INSTRUCTIONS.md` | This bundle's instruction set | Everything | Rules, conventions, required response format |

## Priority 2 — Important Upload

Useful for deeper development work once Priority 1 context is loaded.

| File/Folder | Why it matters | System | What an AI agent learns |
| --- | --- | --- | --- |
| `app/Http/Controllers/Admin/*` (individually, if controller list grows) | Granular admin feature controllers | Admin | Reviews/comments/notifications/visitors/blog/activity-log/undo logic |
| `database/migrations/*` (full folder) | Complete schema history | Schema | Every column/table change ever made, including dormant tables |
| `database/seeders/*.php` | Seed data definitions | Schema/data | What "default" categories/locations/test data look like |
| `config/auth.php`, `config/services.php`, `config/seo.php`, `config/seotools.php` | Non-secret config | Config | Admin email/admins list mechanism, SEO defaults — **review for secrets before upload, though these files typically only contain env() references, not values** |
| `resources/views/seo/*.blade.php` | SEO meta/structured-data partials | SEO | How meta tags and JSON-LD are rendered |
| `resources/views/blog/*.blade.php`, `admin/blog/posts/*.blade.php` | Blog CMS views | Blog | Public + admin blog UI |
| `resources/views/reviews/*.blade.php`, `comments/*.blade.php` | Review/comment UI | Engagement | Client-facing review/comment forms |
| `resources/views/partials/*.blade.php` | Homepage sections (hero, benefits, CTA, how-it-works, client-search) | Frontend | Homepage composition |
| `resources/css/app.css` | Main stylesheet | Styling | Tailwind config usage, custom CSS overrides |
| `tailwind.config.*`, `vite.config.*` | Build config | Frontend tooling | Tailwind content paths, Vite entry points |
| `tests/Feature/**/*.php` | Feature test suite | QA | What's already covered by tests (e.g., `Auth/RegistrationTest.php`, `Auth/ProviderRegistrationTest.php`) |
| `_Project_Docs/SPEEDA_FEATURE_INVENTORY.md` | This bundle's feature-by-feature breakdown | Everything | Routes/controllers/views/models/risks per feature |
| `_Project_Docs/SPEEDA_RISKS_AND_KNOWN_ISSUES.md` | This bundle's risk register | Everything | What's broken or fragile today |

## Priority 3 — Optional Upload

Useful only for UI polish, edge cases, or rare diagnostic work.

| File/Folder | Why it matters |
| --- | --- |
| `resources/views/Static/*.blade.php` | Legal/help/terms pages — rarely need changes |
| `resources/views/debug/*.blade.php`, `DebugController.php`, `debug-translations.blade.php`, `diagnostic.blade.php`, `translation-test.blade.php`, `test-language.blade.php` | Diagnostic-only views, not part of core product flows |
| `resources/views/livewire/admin/user-management.blade.php` | Single Livewire view, not a fully wired feature yet |
| `phpunit.xml` | Test runner config |
| `app/Http/Controllers/Admin/AdminController.php.backup`, `routes/web.php.bak`, `resources/views/home.blade.php.backup` | **Stale backup files — do not upload as if current; if uploaded, label clearly as historical reference only** |
| `public/images/*` | Static assets — only relevant for design/asset discussions |
| `database/migrations/0001_01_01_000001_create_cache_table.php`, `*_create_jobs_table.php`, `*_create_personal_access_tokens_table.php` | Laravel framework boilerplate tables, not business-specific |

## Files Inspected to Build This Report

`app/Models`, `app/Http/Controllers` (incl. `Admin/`, `Auth/`), `app/Services`, `app/Actions`, `app/Http/Middleware`, `routes/`, `resources/views/**` (admin, auth, components, service-providers, provider, blog, reviews, comments, seo, Static, partials, layouts), `lang/{ar,en,fr}/*.php`, `database/migrations/*`, `database/seeders/*`, `config/*`, `composer.json`, `package.json`, and the pre-existing `_Project_Docs/SPEEDA_OFFICIAL_MASTER_DOCUMENTATION.md`.
