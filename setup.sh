#!/bin/bash

# Speeda Post-Clone Setup Script
# Run this after cloning the repository

echo "🚀 Starting Speeda Setup..."

# Install PHP dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-interaction

# Install Node dependencies
echo "📦 Installing NPM dependencies..."
npm install

# Copy environment file
if [ ! -f .env ]; then
    echo "📄 Creating .env file..."
    cp .env.example .env
    php artisan key:generate
else
    echo "⚠️  .env file already exists, skipping..."
fi

# Create storage link (CRITICAL for images)
echo "🔗 Creating storage symlink..."
php artisan storage:link

# Clear all cache
echo "🧹 Clearing cache..."
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

echo ""
echo "✅ Setup complete!"
echo ""
echo "⚠️  IMPORTANT: Next steps:"
echo "1. Update .env with your database credentials"
echo "2. Create database: CREATE DATABASE speeda;"
echo "3. Run: php artisan migrate"
echo "4. 🎯 CRITICAL - Import categories:"
echo "   mysql -u root -p speeda < database/sql/categories_seed.sql"
echo "   (This includes the 'Others' section and all categories)"
echo "5. Run: npm run build (or npm run dev for development)"
echo "6. Run: php artisan serve"
echo ""
echo "📸 NOTE: Uploaded images (profile pictures, etc.) are NOT in Git."
echo "   The storage directories are empty. This is normal."
echo "   Users will need to re-upload, or copy files from production."
echo ""
echo "📖 See database/sql/README.md for database setup details"
echo "📖 See SETUP_GUIDE.md for complete setup instructions"
