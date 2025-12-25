# Database SQL Seeds

This directory contains SQL dumps for seeding the database with essential data.

## 📁 Files

### `categories_seed.sql`
**Complete categories structure - CLEANED VERSION**

**Total: 57 active categories**
- **7 Sections** (parent categories):
  1. Automotive Services (8 subcategories)
  2. Home & Property Services (14 subcategories)
  3. Professional & Business Services (6 subcategories)
  4. Personal & Lifestyle Services (9 subcategories)
  5. Technical & Repair Services (5 subcategories)
  6. Event & Entertainment Services (7 subcategories)
  7. **Others** section (ID: 62) with 1 subcategory

- **50 Subcategories** including:
  - **Others** category (ID: 63) under Others section

## ⚠️ CRITICAL WARNING

**This SQL file will DELETE all existing categories!**

The file contains:
```sql
DELETE FROM `categories`;
ALTER TABLE `categories` AUTO_INCREMENT = 1;
```

**Before running:**
1. ✅ **Backup your database** first!
2. ✅ Check for service_providers linked to categories
3. ✅ Export any custom data you need

**Use this file for:**
- ✅ Fresh installations (recommended)
- ✅ Fixing "multiple Others" duplicate issues
- ✅ Ensuring consistent structure across environments
- ✅ Removing soft-deleted categories

## 🚀 Usage

### Method 1: MySQL Command Line
```bash
mysql -u root -p speeda < database/sql/categories_seed.sql
```

### Method 2: MySQL Workbench
1. Open MySQL Workbench
2. Connect to your database
3. File → Open SQL Script
4. Select `categories_seed.sql`
5. Execute

### Method 3: phpMyAdmin
1. Login to phpMyAdmin
2. Select `speeda` database
3. Click "Import" tab
4. Choose `categories_seed.sql`
5. Click "Go"

### Method 4: Laravel Tinker (from project root)
```bash
php artisan tinker
```
```php
DB::unprepared(file_get_contents('database/sql/categories_seed.sql'));
```

### Method 5: PowerShell (Windows)
```powershell
Get-Content database\sql\categories_seed.sql | mysql -u root -p speeda
```

## ⚠️ Important Notes

### Fresh Installation vs. Existing Data

**This SQL file will:**
- ✅ **DELETE ALL existing categories** (including soft-deleted ones)
- ✅ Reset auto-increment to start from 1
- ✅ Insert fresh, clean data
- ⚠️ **This is a DESTRUCTIVE operation!**

**Why this approach?**
The old database may have:
- Soft-deleted "Others" categories (with `deleted_at` set)
- Duplicate entries from running seeders multiple times
- Inconsistent IDs

This SQL file ensures a **completely clean slate** with the exact structure needed.

### Before Running

**If you have important data:**
```bash
# Backup first!
mysqldump -u root -p speeda > backup_before_categories_seed.sql
```

**If you have service providers:**
```bash
# They will lose their category links!
# You may want to migrate them or reassign after import
```
```bash
# 1. Run migrations
php artisan migrate

# 2. Import categories
mysql -u root -p speeda < database/sql/categories_seed.sql

# 3. Continue with setup
php artisan storage:link
```

### Fresh Installation (Recommended Flow)
If you're setting up from scratch:
```bash
# 1. Run migrations
php artisan migrate

# 2. Import categories (will clean and insert fresh data)
mysql -u root -p speeda < database/sql/categories_seed.sql

# 3. Continue with setup
php artisan storage:link
```

### Existing Database (⚠️ DANGER ZONE)
**WARNING:** This SQL file **DELETES ALL CATEGORIES**

Before running:
1. **Backup your database:**
   ```bash
   mysqldump -u root -p speeda > backup.sql
   ```

2. **Check for service providers:**
   ```sql
   SELECT COUNT(*) FROM service_providers WHERE category_id IS NOT NULL;
   ```
   
3. If you have service providers linked to categories, they will be orphaned!

4. **Only run if you understand the consequences:**
   ```bash
   mysql -u root -p speeda < database/sql/categories_seed.sql
   ```

## 🔍 Verification

After importing, verify the data:

```sql
-- Check total sections (should be 7)
SELECT COUNT(*) as total_sections 
FROM categories 
WHERE is_section = 1 AND deleted_at IS NULL;

-- Check total categories (should be 50)
SELECT COUNT(*) as total_categories 
FROM categories 
WHERE is_section = 0 AND deleted_at IS NULL;

-- Check total (should be 57)
SELECT COUNT(*) as total_all 
FROM categories 
WHERE deleted_at IS NULL;

-- Check Others section and category
SELECT id, name, slug, parent_id, is_section 
FROM categories 
WHERE name = 'Others' 
ORDER BY is_section DESC, id;

-- Check for soft-deleted categories (should be 0)
SELECT COUNT(*) as soft_deleted 
FROM categories 
WHERE deleted_at IS NOT NULL;
```

Expected output:
```
total_sections: 7
total_categories: 50
total_all: 57
soft_deleted: 0
```

Others structure:
```
+----+--------+--------------------+-----------+------------+
| id | name   | slug               | parent_id | is_section |
+----+--------+--------------------+-----------+------------+
| 62 | Others | others-1           | NULL      | 1          |
| 63 | Others | others-subcategory | 62        | 0          |
+----+--------+--------------------+-----------+------------+
```

## 🎯 Why This File Exists

### The "Others" Problem
Before this SQL file, the "Others" section was missing when:
- Cloning the repository from GitHub
- Fresh database installation
- Running migrations without seeders

### Solution
This SQL file ensures:
✅ Complete category structure
✅ Proper "Others" section (ID: 62)
✅ Proper "Others" category (ID: 63)
✅ Service providers with "other" profession work correctly
✅ Consistent data across all environments

## 📝 When to Use

### Use this SQL file when:
- Setting up the project for the first time
- Moving to a new server/environment
- Database got corrupted or categories are missing
- You want the exact same structure as production

### Don't use this file if:
- You have custom categories added in production
- You've modified the category structure
- You want to preserve existing category data

In those cases, export your own database instead:
```bash
mysqldump -u root -p speeda categories > my_custom_categories.sql
```

## 🔄 Updating This File

If you add/modify categories in production, regenerate this file:

```bash
# Export only active categories (without soft-deleted)
mysqldump -u root -p speeda categories --where="deleted_at IS NULL" --complete-insert > database/sql/categories_seed.sql
```

**Note:** You'll need to manually add the DELETE and ALTER TABLE statements at the beginning.

Or use this complete export with cleanup:
```sql
-- Add these lines at the beginning of your export:
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM `categories`;
ALTER TABLE `categories` AUTO_INCREMENT = 1;
SET FOREIGN_KEY_CHECKS = 1;
```

## 🐛 Troubleshooting

### Issue: "Multiple Others" categories appear
**Cause:** Soft-deleted categories still visible in some queries

**Solution:** Use this SQL file - it deletes ALL categories including soft-deleted ones

### Error: Duplicate entry for key 'PRIMARY'
**Cause:** Categories with same IDs already exist

**Solution:** The SQL file already handles this with `DELETE FROM categories`

### Error: Foreign key constraint fails
**Cause:** There are service_providers linked to categories

**Solution:** Disable foreign key checks temporarily:
```sql
SET FOREIGN_KEY_CHECKS = 0;
-- Run your SQL
SET FOREIGN_KEY_CHECKS = 1;
```

### Error: Unknown column 'is_section'
**Cause:** Migration not run yet

**Solution:**
```bash
php artisan migrate
```

## 📚 Related Documentation

- [SETUP_GUIDE.md](../../SETUP_GUIDE.md) - Complete setup instructions
- [GIT_IMAGES_EXPLAINED.md](../../GIT_IMAGES_EXPLAINED.md) - Why images aren't in Git
- [README.md](../../README.md) - Project overview

## 🎓 For Developers

### Structure
```
categories
├── Sections (is_section = 1, parent_id = NULL)
│   ├── Automotive Services (ID: 1)
│   ├── Home & Property Services (ID: 2)
│   ├── Professional & Business Services (ID: 3)
│   ├── Personal & Lifestyle Services (ID: 4)
│   ├── Technical & Repair Services (ID: 5)
│   ├── Event & Entertainment Services (ID: 6)
│   └── Others (ID: 62) ← IMPORTANT for "other" profession
│
└── Categories (is_section = 0, parent_id = section_id)
    ├── Under Automotive (parent_id = 1)
    ├── Under Home & Property (parent_id = 2)
    ├── ...
    └── Others (ID: 63, parent_id = 62) ← Used when profession = "other"
```

### Code Reference
When a service provider registers with `profession = "other"`:
```php
// app/Services/AuthService.php
$othersSection = Category::where('name', 'Others')
    ->where('is_section', true)
    ->first(); // ID: 62

$othersCategory = Category::where('name', 'Others')
    ->where('parent_id', $othersSection->id)
    ->first(); // ID: 63

$serviceProvider->category_id = $othersCategory->id; // 63
```

---

**Last Updated:** December 9, 2025  
**Categories Count:** 63 (7 sections + 56 categories)  
**Database:** MySQL 8.0+  
**Laravel Version:** 11.x
