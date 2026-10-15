# Speeda Setup Guide

## Initial Setup Steps

### 1. Clone the Repository
```bash
git clone <repository-url>
cd speeda
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` with your database credentials:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=speeda
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Database Setup
```bash
php artisan migrate
```

**IMPORTANT: Seed Categories**
```bash
# Method 1: Using SQL dump (RECOMMENDED - ensures exact structure)
mysql -u root -p speeda < database/sql/categories_seed.sql

# Method 2: Using seeders (if available)
php artisan db:seed
```

**Why SQL dump?**
The `categories_seed.sql` file contains the complete category structure including the critical "Others" section and category. This ensures:
- ✅ All 7 sections are created correctly
- ✅ "Others" section (ID: 62) exists
- ✅ "Others" category (ID: 63) exists under Others section
- ✅ Service providers with "other" profession work properly

See `database/sql/README.md` for detailed instructions.

### 5. **IMPORTANT: Storage Link**
**This step is REQUIRED for images to work:**
```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public`.
Without this, uploaded images (profile pictures, certifications, etc.) will NOT display.

### 6. Build Assets
```bash
npm run build
# Or for development:
npm run dev
```

### 7. Start the Server
```bash
php artisan serve
```

## Important Notes

### ⚠️ Uploaded Images Are NOT in Git
**IMPORTANT:** User-uploaded images (profile pictures, certifications) are stored in `storage/app/public/` but are **NOT tracked by Git** (they're in `.gitignore`).

**What this means:**
- When you clone the repo, you won't have any user-uploaded images
- The directory structure will be created, but will be empty
- This is intentional - user uploads should not be in version control

**For Production/Staging:**
1. Copy `storage/app/public/` from production server
2. Or use a shared storage service (S3, etc.)
3. Or accept that user uploads start fresh

### Storage
- All uploaded files go to `storage/app/public/`
- They are accessible via `public/storage/` after running `php artisan storage:link`
- **The symlink `public/storage` must be created on every new environment**
- If images don't show, run the storage:link command again

### Categories & Others Section
- The "Others" section is a regular database entry, not dynamic
- If you reset the database, you need to manually add the "Others" section and category
- Or import from the production database

### Cache
After major changes, clear all cache:
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

## Troubleshooting

### Images Not Showing
1. Check if `public/storage` symlink exists
2. Run `php artisan storage:link`
3. Check file permissions on `storage/app/public`
4. Verify `APP_URL` in `.env` matches your local URL

### Others Section Missing
The "Others" section should exist in the database with:
- Section: `Others` (parent category, is_section = 1)
- Category: `Others` (child category under Others section)

If missing, add manually in the database or contact admin for SQL dump.
