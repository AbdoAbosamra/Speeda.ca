# Translation System

### 1. Implementation
- Uses `outhebox/laravel-translations` for keys and model-level columns for data (`name_ar`, `name_en`, etc.).
- **Locale Management**: Handled by `LocaleController` using session and cookie persistence.

### 2. Mixed Content Problem
- **Root Cause**: `Category::getLocalizedDescriptionAttribute` has logic to prevent English fallback, but if `description_ar` is empty, it returns an empty string rather than a graceful fallback, causing UI holes in Arabic view.
- **Translation Gaps**: Several professionnel names in the migrations are only populated for English and Arabic, leaving French (`name_fr`) empty or falling back incorrectly.
