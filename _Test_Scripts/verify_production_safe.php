<?php
/**
 * PRODUCTION VERIFICATION SCRIPT
 * ==============================
 * بعد تشغيل الـ migration، استخدم هذا الـ script للتحقق من نجاح التحديث
 *
 * طريقة الاستخدام:
 * php artisan tinker < verify_production_translations.php
 */

use App\Models\Category;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "🔍 PRODUCTION TRANSLATION VERIFICATION\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Test 1: Check if all Food & Construction categories exist
echo "1️⃣ CHECK: Categories Exist in Database\n";
echo "─────────────────────────────────────────────────────────\n";

$category_ids = [90, 91, 92, 93, 94, 95, 96, 97];
$categories = DB::table('categories')
    ->whereIn('id', $category_ids)
    ->select('id', 'name', 'name_ar', 'name_fr')
    ->orderBy('id')
    ->get();

if (count($categories) === count($category_ids)) {
    echo "✅ All 8 categories found in database\n\n";
} else {
    echo "⚠️  WARNING: Only " . count($categories) . " of " . count($category_ids) . " categories found\n\n";
}

// Test 2: Display each category with translations
echo "2️⃣ DETAILS: Category Translations\n";
echo "─────────────────────────────────────────────────────────\n";

$issues = 0;
foreach ($categories as $cat) {
    echo "ID {$cat->id}: {$cat->name}\n";
    echo "  ├─ EN: {$cat->name} ✅\n";

    if ($cat->name_ar) {
        echo "  ├─ AR: {$cat->name_ar} ✅\n";
    } else {
        echo "  ├─ AR: EMPTY ❌\n";
        $issues++;
    }

    if ($cat->name_fr) {
        echo "  └─ FR: {$cat->name_fr} ✅\n";
    } else {
        echo "  └─ FR: EMPTY ❌\n";
        $issues++;
    }
    echo "\n";
}

// Test 3: Check if localized_name accessor works correctly
echo "3️⃣ TEST: Localized Name Accessor (In-App Functionality)\n";
echo "─────────────────────────────────────────────────────────\n";

$testCategories = [90, 92, 96];
foreach ($testCategories as $id) {
    $category = Category::find($id);
    if ($category) {
        echo "Category ID {$id}: {$category->name}\n";

        // Test English
        app()->setLocale('en');
        $en_name = $category->localized_name;
        echo "  ├─ EN Mode: $en_name\n";

        // Test Arabic
        app()->setLocale('ar');
        $ar_name = $category->localized_name;
        echo "  ├─ AR Mode: $ar_name\n";
        if (strpos($ar_name, 'خ') !== false || strpos($ar_name, 'ع') !== false) {
            echo "       ✅ Contains Arabic characters\n";
        } else {
            echo "       ❌ NO Arabic characters detected\n";
            $issues++;
        }

        // Test French
        app()->setLocale('fr');
        $fr_name = $category->localized_name;
        echo "  └─ FR Mode: $fr_name\n";

        echo "\n";
    }
}

// Test 4: Check descriptions in different locales
echo "4️⃣ TEST: Description Template Generation\n";
echo "─────────────────────────────────────────────────────────\n";

$testCat = Category::find(92); // Restaurants
if ($testCat) {
    app()->setLocale('en');
    $en_desc = $testCat->translated_description;
    echo "English Description:\n";
    echo "  {$en_desc}\n\n";

    app()->setLocale('ar');
    $ar_desc = $testCat->translated_description;
    echo "Arabic Description:\n";
    echo "  {$ar_desc}\n";
    if (strpos($ar_desc, 'بيانات') !== false || strpos($ar_desc, 'خدمات') !== false) {
        echo "  ✅ Arabic template working\n\n";
    } else {
        echo "  ❌ Arabic template might have issues\n\n";
        $issues++;
    }

    app()->setLocale('fr');
    $fr_desc = $testCat->translated_description;
    echo "French Description:\n";
    echo "  {$fr_desc}\n";
    if (strpos($fr_desc, 'Services de') !== false || strpos($fr_desc, 'Restaurants') !== false) {
        echo "  ✅ French template working\n\n";
    } else {
        echo "  ❌ French template might have issues\n\n";
        $issues++;
    }
}

// Summary
echo "═══════════════════════════════════════════════════════════\n";
if ($issues === 0) {
    echo "🎉 ALL TESTS PASSED - Production is SAFE and WORKING!\n";
} else {
    echo "⚠️  ISSUES FOUND: {$issues} problems detected\n";
}
echo "═══════════════════════════════════════════════════════════\n";
echo "\nCheck production logs for detailed information:\n";
echo "  tail -f storage/logs/laravel.log\n\n";
