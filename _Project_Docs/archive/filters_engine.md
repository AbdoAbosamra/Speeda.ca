# Filters Engine

### 1. Current Workflow
Filters are processed in `ServiceProviderController@index` using a builder pattern:
1.  **Search**: Partial matches on name/bio.
2.  **Category**: Recursive ID expansion (Terminal nodes).
3.  **Location**: Cluster expansion (e.g., searching for "Laval" also pulls "Montreal" results).

### 2. Performance Issues
- **Live Aggregations**: The `selectRaw` for `live_rating` runs an `AVG` subquery for *every* provider in the search result. As the provider count grows, this will cause massive CPU spikes on the DB.
- **Eager Loading Overheads**: Eager loading `category.parent.parent` and `media` on the index page significantly increases the memory footprint per request.

### 3. Accuracy Problems
- **Location Clusters**: The cluster logic is binary. If a city is in a cluster, all results are shown equally. There is no "Distance-based" sorting or "Primary City first" logic.

### 4. Proposed Optimizations
- **Indexing**: Add composite indexes for `(is_active, category_id, location_id)`.
- **Pre-calculation**: Store the `live_rating` in a `calculated_rating` column and update it only when a review is approved, removing the subquery from the search logic.
