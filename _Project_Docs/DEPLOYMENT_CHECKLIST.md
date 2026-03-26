# Translation System Fix - Deployment Checklist

## Pre-Deployment

### 1. Backup Database
```bash
php artisan backup:run
# OR
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql
```

### 2. Backup Code
```bash
git add .
git commit -m "Backup before translation system fixes"
git push
```

### 3. Verify Google Translate API (Optional)
```bash
# Check if API key is set
php artisan tinker --execute="echo config('services.google_translate.api_key') ? 'Configured' : 'Not configured';"
```

## Deployment Steps

### Step 1: Update TranslationService
```bash
# Replace app/Services/TranslationService.php with fixed version
# File: IMPLEMENTATION_FILES/01_FIXED_TRANSLATION_SERVICE.php
```

### Step 2: Update Validation Rules
```bash
# Replace app/Http/Requests/StoreCategoryRequest.php
# File: IMPLEMENTATION_FILES/02_ENHANCED_VALIDATION_RULES.php

# Replace app/Http/Requests/UpdateCategoryRequest.php
# File: IMPLEMENTATION_FILES/03_UPDATE_CATEGORY_REQUEST.php
```

### Step 3: Update PopulateCategoryTranslations Command
```bash
# Replace app/Console/Commands/PopulateCategoryTranslations.php
# File: IMPLEMENTATION_FILES/05_UPDATED_POPULATE_COMMAND.php
```

### Step 4: Add Admin UI Enhancements
```bash
# Update resources/views/admin/categories/index.blade.php
# Update resources/views/admin/categories/edit.blade.php
# See: IMPLEMENTATION_FILES/04_ADMIN_UI_ENHANCEMENTS.md
```

### Step 5: Add Translation Keys
```bash
# Add missing keys to lang/en/admin.php, lang/ar/admin.php, lang/fr/admin.php
# See: IMPLEMENTATION_FILES/04_ADMIN_UI_ENHANCEMENTS.md (bottom section)
```

### Step 6: Add Auto-Translate Route
```bash
# Add route to routes/web.php or routes/admin.php
# See: IMPLEMENTATION_FILES/04_ADMIN_UI_ENHANCEMENTS.md
```

### Step 7: Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### Step 8: Run Pint
```bash
vendor/bin/pint app/Services/TranslationService.php
vendor/bin/pint app/Http/Requests/
vendor/bin/pint app/Console/Commands/PopulateCategoryTranslations.php
```

## Post-Deployment Validation

### 1. Test Admin Category Creation
- [ ] Create new category with all 3 languages → Should save successfully
- [ ] Try creating with missing English → Should show validation error
- [ ] Try creating with missing French → Should show validation error
- [ ] Test auto-translate button (if API available) → Should fill English/French

### 2. Test Admin Category Edit
- [ ] Edit existing category → All 3 languages editable
- [ ] Change only Arabic → Others unchanged
- [ ] Save → Changes persisted

### 3. Test Public Site
- [ ] Switch to English → All categories show English
- [ ] Switch to Arabic → All categories show Arabic + RTL layout
- [ ] Switch to French → All categories show French
- [ ] Verify: No mixed languages
- [ ] Verify: No console errors

### 4. Test PopulateCategoryTranslations Command
```bash
# Dry run first
php artisan categories:populate-translations --dry-run

# If looks good, run for real
php artisan categories:populate-translations
```

### 5. Check Logs
```bash
tail -f storage/logs/laravel.log
# Look for any errors
```

## Rollback Plan

If issues occur:

### 1. Revert TranslationService
```bash
git checkout HEAD -- app/Services/TranslationService.php
```

### 2. Revert Validation Rules
```bash
git checkout HEAD -- app/Http/Requests/StoreCategoryRequest.php
git checkout HEAD -- app/Http/Requests/UpdateCategoryRequest.php
```

### 3. Revert PopulateCategoryTranslations
```bash
git checkout HEAD -- app/Console/Commands/PopulateCategoryTranslations.php
```

### 4. Revert Admin UI (if needed)
```bash
git checkout HEAD -- resources/views/admin/categories/
```

### 5. Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## Emergency Contacts

- **Database Issues:** [DBA Contact]
- **API Issues:** [API Admin Contact]
- **Code Issues:** [Dev Lead Contact]

## Notes

- **No database migrations required** - All changes are code-only
- **Zero downtime expected** - Changes are additive
- **Fully reversible** - All changes can be reverted via git
