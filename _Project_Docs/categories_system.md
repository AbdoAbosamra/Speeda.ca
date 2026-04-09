# Categories System

### 1. Hierarchy Explanation
The system uses a **Section → Group → Profession** hierarchy:
- **Sections** (Level 0): `is_section = true`, `parent_id = null`. (e.g., Construction).
- **Filter Groups** (Level 1): Direct children of sections.
- **Terminal Professions** (Level 2): The actual profession selected by the provider.

### 2. Logic Implementation
- **Filter Resolution**: `Category::resolveFilterValue()` allows filtering by either ID or Slug, providing SEO-friendly URLs.
- **Descendant Resolution**: `providerCategoryIds()` uses a recursive-style frontier search to find all leaf-node categories under a parent, ensuring that a search for "Construction" returns providers from all sub-professions.

### 3. Potential Connection Risks
- **Slug Collisions**: Slugs are generated from English names. Multi-language names that map to the same English slug create collisions.
- **Hardcoded "Others" logic**: The logic for locking categories (`UpdateServiceProviderProfileRequest`) relies on checking strings like `'Others'`. If an admin renames the "Others" category, the security lock on category changes will break.
