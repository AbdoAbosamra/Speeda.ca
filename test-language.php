<?php
// Test script to check language switching functionality

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Test locale switching
echo "Testing Language Switching System\n";
echo "=================================\n\n";

// Test 1: Check current locale
echo "1. Current Locale: " . app()->getLocale() . "\n";

// Test 2: Check if translation files exist
$languages = ['en', 'ar', 'fr'];
foreach ($languages as $lang) {
    $file = __DIR__."/lang/{$lang}/language.php";
    if (file_exists($file)) {
        echo "2. ✅ Translation file exists: {$lang}/language.php\n";
    } else {
        echo "2. ❌ Translation file missing: {$lang}/language.php\n";
    }
}

// Test 3: Check translation keys
echo "\n3. Testing Translation Keys:\n";
try {
    app()->setLocale('en');
    echo "   English: " . __('language.english') . "\n";
    
    app()->setLocale('ar');
    echo "   Arabic: " . __('language.arabic') . "\n";
    
    app()->setLocale('fr');
    echo "   French: " . __('language.french') . "\n";
} catch (Exception $e) {
    echo "   ❌ Translation error: " . $e->getMessage() . "\n";
}

// Reset to default
app()->setLocale('en');

echo "\n✅ Language system test completed!\n";