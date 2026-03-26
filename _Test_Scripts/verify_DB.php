<?php
// Check if the category has been updated
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$category = \DB::table('categories')->where('id', 92)->select('id', 'name', 'name_en', 'name_ar', 'name_fr')->first();

echo "\n";
echo "══════════════════════════════════════════\n";
echo "✅ DATABASE CHECK - Category ID 92\n";
echo "══════════════════════════════════════════\n\n";

echo "English (name_en): " . $category->name_en . "\n";
echo "Arabic (name_ar):  " . $category->name_ar . "\n";
echo "French (name_fr):  " . $category->name_fr . "\n";

echo "\n══════════════════════════════════════════\n";

// Verify all are correct
$is_correct =
    $category->name_en === 'Restaurants and Cafe' &&
    $category->name_ar === 'المطاعم والكافيهات' &&
    $category->name_fr === 'Restaurants et Cafés';

if ($is_correct) {
    echo "✅ ALL TRANSLATIONS UPDATED CORRECTLY!\n";
} else {
    echo "❌ SOME TRANSLATIONS ARE INCORRECT\n";
}

echo "══════════════════════════════════════════\n\n";
