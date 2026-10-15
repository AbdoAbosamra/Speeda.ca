# Database Analysis

### 1. Critical Table Structures
- **`service_providers`**: The central entity. Stores business info, cached rating, and profile completion status.
- **`categories`**: Implements a self-referencing hierarchy (`parent_id`) with section-level grouping.
- **`service_provider_reviews`**: The reputation log. Requires admin approval before becoming "active".
- **`analytics`**: High-frequency tracking table. Stores `action_type` and `session_hash` for privacy-compliant deduplication.

### 2. Relationship Mapping (ERD Explanation)
- `User` **1:1** `ServiceProvider` (One account per business).
- `Category` **1:N** `ServiceProvider` (Hierarchical professional classification).
- `Location` **1:N** `ServiceProvider` (Primary service area).
- `ServiceProvider` **1:N** `Review` (Reputation history).
- `ServiceProvider` **N:M** `Location` (via `service_areas` table for expanded reach).

### 3. Weak Schema Areas
- **Mixed Media Storage**: The system uses a manual `profile_image` column in `service_providers` while using `Spatie\MediaLibrary` for the gallery. This creates inconsistent backup and migration paths for media.
- **Review System Instability**: Migration history shows multiple "fix/recreate" cycles for `service_provider_reviews`, suggesting potential legacy data corruption or schema mismatch during evolution.
- **Hardcoded Clusters**: Location clustering is handled in code (`LocationClusterService`) rather than the database, making it difficult for admins to manage clusters via UI.

### 4. Missing / Recommended Indexes
- **Missing Index**: `analytics(provider_id, action_type, created_at)` — Critical for dashboard performance.
- **Missing Index**: `categories(is_section, is_active, parent_id)` — High frequency for menu and filter generation.
- **Redundancy**: `service_providers.rating` is often out of sync with actual reviews; the system frequently uses subqueries to calculate "live_rating," making the column redundant or misleading.
