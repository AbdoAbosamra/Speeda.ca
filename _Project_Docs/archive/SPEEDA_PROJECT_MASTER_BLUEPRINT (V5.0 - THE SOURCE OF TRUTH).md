### SPEEDA_PROJECT_MASTER_BLUEPRINT (V5.0 - THE SOURCE OF TRUTH)

🏗️ 1. Architecture Strategy

- **Framework**: Laravel (v12+ assumed based on syntax), with Blade for server-side rendering (no APIs, no Inertia.js or Vue – fully Blade-based).
- **Pattern**: MVC with Service-Repository-Controller (thin controllers delegating to Services like VisitorTrackingService), Form Requests for validation, Policies for authorization.
- **Multi-Tenancy**: Differentiates between Clients (review/comment), Service Providers (public profiles), and Admins (restricted access). Roles: 'client', 'service_provider', 'admin' (with env('ADMINS') email bypass).
- **Legacy Cleansing**: Bookings table exists but ignored (discovery-based interactions). Legacy models like ServiceProviderProfile should be consolidated into ServiceProvider to reduce duplication.
- **Frontend Stack**: Vite for bundling, Tailwind CSS for styling, Alpine.js for interactivity (e.g., live updates, dialogs). Responsive design with gradients, glassmorphism, animations.
- **Localization**: Session-based (App::setLocale), with safe redirects to prevent open redirect vulnerabilities.
- **Caching**: Automatic clearing on CRUD (e.g., categories/locations changes reflect instantly). TTL: 5 minutes for visitor stats, 1 minute for live counts.
- **Privacy Compliance**: GDPR-friendly (hashed IPs/User Agents via SHA256, no personal data stored).

🧩 2. Models & Data Relations (The Deep Logic)

A. **Identity & Access (User.php)**

- Roles: 'client', 'service_provider', 'admin'.
- Admin Check: role === 'admin' OR email in env('ADMINS').
- Relationships: hasOne(ServiceProviderProfile) – legacy bridge; belongsToMany(roles or similar implied).
- Business Rules: isClient() for reviews, isAdmin() for approvals.

B. **Directory Core (ServiceProvider.php & ServiceProviderProfile.php)**

- **Dual System**: ServiceProvider (main: rating, verification, views) + Profile (content: bio, phone, social, portfolio) – recommend consolidation (merge Profile into Provider).
- JSON Fields: languages, specializations, availability_schedule, portfolio_images for flexibility.
- Relationships: hasMany(Reviews), belongsToMany(Categories via pivot), belongsTo(Location).
- Business Rules: incrementViews() on profile visit; is_verified boolean for UI badge; hasUserReviewed($userId) check.

C. **Hierarchies (Category.php & Location.php)**

- **Category**: Self-referencing (parent_id for hierarchy); boot() auto-generates unique slugs (e.g., 'plumbing-1').
- **Location**: Hierarchical (country → city → area); getNameAttribute() returns city; added fields: latitude, longitude, meta_title, meta_description, image.
- Pivots: location_category (city-specific categories); service_provider_categories.
- Business Rules: SoftDeletes; no manual slugs; delete checks activeServiceProviders().

D. **Social Engine (Review.php & Comment.php)**

- **Review**: Unique composite key [service_provider_profile_id, client_id] (one review per provider); scopes: active(), verified(), featured(), pending(), orderByRating().
- **Comment**: Polymorphic (morphTo for reviews or future entities); is_active false by default; approve() by Admin.
- Relationships: Review belongsTo(ServiceProvider), belongsTo(Client); Comment morphTo(commentable), belongsTo(User).
- Business Rules: Only clients store reviews; admin/author destroy comments; flag() for reporting.

E. **Analytics (Visitor.php)**

- Fields: hashed_ip, hashed_user_agent, path, timestamps.
- Scopes: byPeriod (e.g., today, last7Days); deduplication (5-min window).
- Business Rules: Hashed for privacy (SHA256, irreversible); aggregated stats only.

F. **Other Models**:

- Booking: Ignored logic; legacy service_provider_profile_id → migrate to service_provider_id.

- ERD Diagram (ASCII):
  
  text
  
  ```
  User --hasOne--> ServiceProvider
  ServiceProvider --hasMany--> Review
  Review --morphMany--> Comment
  ServiceProvider --belongsToMany--> Category
  ServiceProvider --belongsTo--> Location
  Visitor (standalone for analytics)
  ```

⚙️ 3. Execution Logic (Controller Layer)

A. **Discovery Engine (ServiceProviderController.php)**

- Index: withCount('serviceProviders') for categories; filtering by city_id/name, minRating, verifiedReviews; sorting by rating/reviewCount.
- Show: incrementViews(); displays reviews/comments with cards.

B. **Admin Panel (AdminController.php & VisitorAnalyticsController.php)**

- Dashboard: 6 cards (live visitors, today, last7/30/12, all-time); AJAX live updates (30s).
- Locations/Categories: Full CRUD with Form Requests, Policies, transactions; instant cache clear.
- Visitors: Read-only; export CSV; top pages.

C. **Social (ReviewController.php & CommentController.php)**

- Store: Clients only; pending approval; unique check.
- Approve: Admin only; sets is_active=true.
- Flag/Destroy: User-specific; soft delete.

D. **Global (LocaleController.php)**

- Set locale in session; safe redirect.

E. **Middleware (TrackVisitor.php)**

- Runs on every GET; hashes IP/UA; deduplicates; clears stats cache.

F. **Routes (web.php)**:

- Admin: /admin/dashboard, /admin/locations (CRUD), /admin/categories (CRUD), /admin/visitors (read/export/live-count).
- Public: /service-providers, /reviews (index/create/store), /comments (store/flag/destroy).
- Removed: /admin/users (404 safe).

🛑 4. Development Guardrails (Rules for AI/Devs)

1. **Strict Deletion**: Use SoftDeletes; check activeServiceProviders() before delete Category/Location.
2. **No Manual Slugs**: Model handles generation.
3. **Auth Logic**: isClient() for StoreReview; isAdmin()/author for DestroyComment/Approve.
4. **Media**: Public disk; Storage::exists() before delete.
5. **Caching**: Always clear on CRUD (php artisan cache:clear).
6. **Migrations**: Non-destructive; rollback tested.
7. **Testing**: PHPUnit for features/units; 80%+ coverage.
8. **Security**: No hardcoded creds; rate limiting on views; OWASP checks.

🛠️ 5. Technical Inventory

- **Primary Tables**: users, service_providers, service_provider_profiles (legacy), categories, locations, reviews (service_provider_reviews), comments, visitors, bookings (ignored).
- **Pivot Tables**: location_category, service_provider_categories.
- **Migrations**: create_visitors_table, add_hierarchical_support_to_locations, consolidate_service_provider_models (recommended).
- **Controllers**: 26+ (e.g., AdminController refactored, VisitorAnalyticsController new).
- **Views**: 62+ Blade (e.g., dashboard.blade.php with cards, review-card component).
- **Middleware**: TrackVisitor, Admin.
- **Services**: VisitorTrackingService.
- **Policies**: CategoryPolicy, LocationPolicy.
- **Form Requests**: Store/Update for Category/Location.
- **Deployment**: php artisan migrate --force; clear caches; verify dashboard/CRUD.
- **Testing**: Feature/Unit tests in tests/ (e.g., ReviewFilteringTest).
- **Vision**: Scalable to 100k+ users; future: payments, advanced analytics.

🖼️ 6. UI/UX Flows (New Section)

- **Client Reviews Flow**: Login → Provider Profile → Write Review (stars, text) → Pending → Admin Approve → Display with card (stars, verified badge).
- **Admin Dashboard Flow**: Login → Dashboard (6 cards, live AJAX) → CRUD Locations/Categories → Instant Update (cache clear) → Export Visitors CSV.
- **Visitor Tracking**: Any page visit → Middleware hash/save → Stats update in dashboard.

🚨 7. Error Handling & Security Guide (New Section)

- **Errors**: Custom Handler; meaningful messages (e.g., "Cannot delete category with children").
- **Security**: CSRF enabled; input validation; no SQL injection (ORM); hashing privacy; policies/gates; no user management in admin.
- **Performance**: Eager loading (with()); caching; indexes on JSON fields.

📊 8. API Reference (New Section)

- No APIs (fully Blade/web routes). If needed, add RESTful (e.g., GET /api/providers) with Swagger docs.
