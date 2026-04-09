# Improvement Roadmap

### 1. Phase 1: Stability & Performance (Short Term)
- **Database Optimization**: Migrate `live_rating` subquery to a cached `rating` column on the `service_providers` table.
- **RTL Sweep**: Perform a full audit of CSS and Blade files to replace all `left/right` absolute positioning with logical alternatives (`inset-inline-start`, etc.).
- **SEO Foundation**: Add `hreflang` tags to the layout and implement dynamic meta descriptions for all public routes.

### 2. Phase 2: User Experience & Architecture (Medium Term)
- **Unified Media Management**: Transition all `profile_image` handling to `Spatie\MediaLibrary` to benefit from unified optimization and backup paths.
- **Search Enhancement**: Integrate Meilisearch or implement a full-text search index to handle misspelled profession names and multi-language matches more gracefully.
- **Global Caching**: Implement a global cache for the category tree and location lists to reduce DB queries on landing pages.

### 3. Phase 3: Scaling & Analytics (Long Term)
- **Distance-Based Filtering**: Upgrade the filters engine to support actual coordinate-based (Lat/Long) distance sorting for more accurate "local" results.
- **Advanced Admin Audit**: Implement a detailed audit log UI that allows admins to view the history of provider profile changes and review approvals.
- **Social Proof Expansion**: Allow providers to respond to reviews and showcase "Verified Hire" badges to further increase platform trust.
