<?php
/**
 * RESTAURANTS RENAME VERIFICATION SCRIPT
 * =======================================
 * تحقق من نجاح التحديث
 *
 * Usage:
 * php artisan tinker < verify_restaurants_rename.php
 */

use App\Models\Category;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "🔍 RESTAURANTS RENAME VERIFICATION\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Test 1: Check if category exists
echo "1️⃣ CHECK: Restaurant Category Exists\n";
echo "─────────────────────────────────────────────────────────\n";

$category = DB::table('categories')
    ->where('id', 92)
    ->select('id', 'name', 'name_en', 'name_ar', 'name_fr')
    ->first();

if ($category) {
    echo "✅ Category ID 92 found!\n\n";
} else {
    echo "❌ Category ID 92 not found!\n\n";
    exit;
}

// Test 2: Check if all names are updated
echo "2️⃣ CHECK: All Language Names Updated\n";
echo "─────────────────────────────────────────────────────────\n";

$issues = [];

// Check English
if ($category->name_en === 'Restaurants and Cafe') {
    echo "✅ English Name: {$category->name_en}\n";
} else {
    echo "❌ English Name: {$category->name_en} (Expected: 'Restaurants and Cafe')\n";
    $issues[] = "English name not updated";
}

// Check Arabic
if ($category->name_ar === 'المطاعم والكافيهات') {
    echo "✅ Arabic Name: {$category->name_ar}\n";
} else {
    echo "❌ Arabic Name: {$category->name_ar} (Expected: 'المطاعم والكافيهات')\n";
    $issues[] = "Arabic name not updated";
}

// Check French
if ($category->name_fr === 'Restaurants et Cafés') {
    echo "✅ French Name: {$category->name_fr}\n";
} else {
    echo "❌ French Name: {$category->name_fr} (Expected: 'Restaurants et Cafés')\n";
    $issues[] = "French name not updated";
}

echo "\n";

// Test 3: Check if localized_name accessor works
echo "3️⃣ TEST: Localized Name Accessor\n";
echo "─────────────────────────────────────────────────────────\n";

$categoryModel = Category::find(92);
if ($categoryModel) {
    // Test English
    app()->setLocale('en');
    $en_name = $categoryModel->localized_name;
    echo "EN Mode: $en_name";
    if ($en_name === 'Restaurants and Cafe') {
        echo " ✅\n";
    } else {
        echo " ❌\n";
        $issues[] = "English accessor not working correctly";
    }

    // Test Arabic
    app()->setLocale('ar');
    $ar_name = $categoryModel->localized_name;
    echo "AR Mode: $ar_name";
    if ($ar_name === 'المطاعم والكافيهات') {
        echo " ✅\n";
    } else {
        echo " ❌\n";
        $issues[] = "Arabic accessor not working correctly";
    }

    // Test French
    app()->setLocale('fr');
    $fr_name = $categoryModel->localized_name;
    echo "FR Mode: $fr_name";
    if ($fr_name === 'Restaurants et Cafés') {
        echo " ✅\n";
    } else {
        echo " ❌\n";
        $issues[] = "French accessor not working correctly";
    }
}

echo "\n";

// Test 4: Check description templates
echo "4️⃣ TEST: Description Templates\n";
echo "─────────────────────────────────────────────────────────\n";

app()->setLocale('en');
$en_desc = $categoryModel->translated_description ?? 'N/A';
echo "EN Description: {$en_desc}\n";

app()->setLocale('ar');
$ar_desc = $categoryModel->translated_description ?? 'N/A';
echo "AR Description: {$ar_desc}\n";

app()->setLocale('fr');
$fr_desc = $categoryModel->translated_description ?? 'N/A';
echo "FR Description: {$fr_desc}\n";

echo "\n";

// Summary
echo "═══════════════════════════════════════════════════════════\n";
if (empty($issues)) {
    echo "🎉 ALL TESTS PASSED - Rename Successful!\n";
} else {
    echo "⚠️  ISSUES FOUND:\n";
    foreach ($issues as $issue) {
        echo "  - $issue\n";
    }
}
echo "═══════════════════════════════════════════════════════════\n\n";

echo "Database Content:\n";
var_dump($category);
echo "\n";
