# 🚀 PRODUCTION DEPLOYMENT GUIDE
## Speeda Translation Migration - Safe Deployment Steps

---

## ✅ PRE-DEPLOYMENT CHECKLIST

Before running the migration on production:

- ✅ Database backup is configured and running
- ✅ You have ssh/terminal access to production server
- ✅ Laravel migrations are version controlled (they are)
- ✅ No ongoing database maintenance windows
- ✅ Cache is cleared after deployment (plan included)

---

## 📋 DEPLOYMENT STEPS

### Step 1: Connect to Production Server
```bash
ssh your-production-user@your-production-server
cd /path/to/speeda
```

### Step 2: Create Manual Database Backup (EXTRA SAFETY)
```bash
# Before running ANY migration
php artisan db:backup
# Or manually:
# mysqldump -u root -p speeda_db > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Step 3: Run the Secondary Migration
```bash
# This migration is PRODUCTION-SAFE:
# - Creates backup automatically
# - Uses database transaction (atomic operation)
# - Only updates if conditions are met
# - Logs all operations

php artisan migrate --path=database/migrations/2026_02_15_000001_populate_new_sections_translations.php

# Expected output:
# Migrating: 2026_02_15_000001_populate_new_sections_translations
# Migrated: 2026_02_15_000001_populate_new_sections_translations (XXXms)
```

### Step 4: Clear All Caches
```bash
# Critical after migrations!
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear
```

### Step 5: Verify Migration Success
```bash
# Check if categories were updated
php artisan tinker

# Then run these commands in tinker:
>>> DB::table('categories')->whereIn('id', [90,91,92,93,94,95,96,97])->select('id', 'name', 'name_ar', 'name_fr')->get();

# You should see name_ar and name_fr populated for all 8 categories
```

### Step 6: Test in Browser

#### Test Arabic (العربية):
1. Open https://your-domain/ar
2. Navigate to shop/categories (or wherever categories are displayed)
3. Look for categories 90-97 (Food Services, Construction Services, etc.)
4. **Verify**: You should see PURE ARABIC text, NO English mixed in
5. Example:
   - ✅ Correct: "خدمات المطاعم في لافال، مونتريال، أوتاوا، غاتينو"
   - ❌ Wrong: "Food خدمات في Laval"

#### Test French (Français):
1. Open https://your-domain/fr
2. Navigate to shop/categories
3. Look for categories 90-97
4. **Verify**: You should see PURE FRENCH text, NO Arabic or English mixed in
5. Example:
   - ✅ Correct: "Services de Restaurants à Laval, Montréal, Ottawa, Gatineau"
   - ❌ Wrong: "Restaurants Services à Laval"

#### Test English:
1. Open https://your-domain/en
2. Navigate to shop/categories
3. Look for categories 90-97
4. **Verify**: You should see PURE ENGLISH text
5. Example:
   - ✅ Correct: "Restaurants services in Laval, Montreal, Ottawa, Gatineau"
   - ❌ Wrong: "Restaurants Services in Laval"

---

## 📊 EXPECTED RESULTS AFTER DEPLOYMENT

### Database Changes:
Categories **90-97** will have translations populated:

| ID | Name (English) | اسم (Arabic) | Nom (French) |
|----|---|---|---|
| 90 | Food Services | خدمات الطعام | Services Alimentaires |
| 91 | Construction Services | خدمات الإنشاءات والمقاولات | Services de Construction |
| 92 | Restaurants | المطاعم | Restaurants |
| 93 | Home Kitchen | أكل بيتي (مطبخ منزلي) | Cuisine Maison |
| 94 | Catering | خدمات الضيافة والبوفيه | Services de Restauration |
| 95 | General Construction | المقاولات والإنشاءات العامة | Construction Générale |
| 96 | Photographers | المصورون والمصورون المتخصصون في الفيديو | Photographes & Vidéographes |
| 97 | Driving Lessons | تعليم القيادة ومدارس السياقة | Cours de Conduite |

---

## ⚠️ ROLLBACK PROCEDURE (If Issues Occur)

The migration creates a backup automatically. To rollback:

```bash
# 1. Check migration history
php artisan migrate:status

# 2. If you need to rollback:
php artisan migrate:rollback --step=1

# 3. Manually restore if needed:
# Check the backup in:
# storage/logs/laravel.log (contains backup data)
# Or use your database backup:
# mysql -u root -p speeda_db < backup_file.sql

# 4. Clear caches again
php artisan cache:clear
php artisan view:clear
```

---

## 📊 MONITORING AFTER DEPLOYMENT

### Check Logs:
```bash
tail -f storage/logs/laravel.log
```

Look for entries like:
```
[TIMESTAMP] production.INFO: [Translation Migration] Backup created
[TIMESTAMP] production.INFO: [Translation Migration] Starting transaction
[TIMESTAMP] production.INFO: [Translation Migration] Category 90 updated with translations
...
[TIMESTAMP] production.INFO: [Translation Migration] Success! All 8 categories updated.
```

### Verify Database Directly:
```bash
php artisan tinker

# Check that translations are there:
>>> DB::table('categories')->whereIn('id', [90,91,92,93,94,95,96,97])->where('name_ar', '!=', null)->count();
8  # Should return 8

>>> DB::table('categories')->whereIn('id', [90,91,92,93,94,95,96,97])->where('name_fr', '!=', null)->count();
8  # Should return 8
```

---

## 🎯 SUCCESS CRITERIA

Migration is successful when:

✅ Migration runs without errors  
✅ All 8 categories have `name_ar` populated  
✅ All 8 categories have `name_fr` populated  
✅ Arabic mode displays pure Arabic (no English)  
✅ French mode displays pure French (no English or Arabic)  
✅ English mode displays pure English (no Arabic or French)  
✅ No entries in error logs related to the migration  
✅ Page loads normally in all three languages  

---

## 🆘 TROUBLESHOOTING

### Issue: Migration doesn't run
```
Error: Migration not found
```
**Solution:**
- Ensure file `2026_02_15_000001_populate_new_sections_translations.php` exists in `database/migrations/`
- Try running: `php artisan migrate` (without --path) to run all pending migrations

### Issue: Mixed language still showing
```
Arabic showing: "Food خدمات في Laval"
```
**Solution:**
- Clear cache: `php artisan cache:clear`
- Check if migration actually ran: Check logs
- Verify in database directly with tinker that translations were populated

### Issue: Database transaction failed
```
Error: Savepoint transaction not found
```
**Solution:**
- This is very unlikely due to transaction wrapper
- Check MySQL error logs: `tail /var/log/mysql/error.log`
- The migration has automatic rollback on failure
- Manual restore from backup if needed

---

## 📞 SUPPORT

If you encounter issues:

1. **Check logs first:**
   ```bash
   tail -100 storage/logs/laravel.log
   ```

2. **Verify database state:**
   ```bash
   php artisan tinker
   >>> App\Models\Category::whereIn('id', [90,91,92,93,94,95,96,97])->select('id', 'name', 'name_ar', 'name_fr')->get();
   ```

3. **If still having issues:**
   - The migration has automatic rollback capability
   - Use the backup procedure above
   - Contact the development team with error logs

---

## ✅ FINAL VERIFICATION SCRIPT

After deployment, you can run this verification:

```bash
# In root directory:
php artisan tinker

# Copy and paste:
>>> include 'verify_production_safe.php';
```

This will run comprehensive checks including:
- Database integrity
- Translation population
- Accessor functionality
- Description template generation
- Language-specific rendering

---

**Last Updated:** 2026-02-15  
**Migration File:** `database/migrations/2026_02_15_000001_populate_new_sections_translations.php`  
**Categories Affected:** 90, 91, 92, 93, 94, 95, 96, 97 (8 categories)  
**Est. Deployment Time:** < 1 minute  
**Downtime Required:** None (background transaction)  
**Data Loss Risk:** Zero (automatic backup + transaction)
