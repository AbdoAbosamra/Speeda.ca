# Performance Analysis

### 1. The N+1 Wall
- **Reviews**: Accessing `calculated_rating` in a loop triggers a sub-query for every row if `activeReviews` isn't eager loaded.
- **Similar Providers**: The "Similar Providers" section in `show()` re-loads many of the same models already in memory.

### 2. Caching Gaps
- **Category Hierarchy**: The category tree is re-calculated from the DB on every page load. This should be cached globally as it rarely changes.
- **Location Clusters**: These are cached for 6 hours, which is excellent.

### 3. Query Efficiency
- The use of `selectRaw` for `live_rating` in `ServiceProviderController@index` is a "smart" stop-gap, but it will scale poorly compared to a triggers/events-based redundant column.
