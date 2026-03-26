<?php

use App\Models\Category;

$cat = Category::where('is_section', true)->first();

if ($cat) {
    echo "\n=== CATEGORY TEST ===\n";
    echo 'Category ID: '.$cat->id."\n";
    echo 'Name: '.$cat->name."\n";
    echo 'Name EN: '.$cat->name_en."\n";
    echo 'Name AR: '.($cat->name_ar ?? 'NULL')."\n";

    echo "\n=== TESTING DESCRIPTIONS ===\n";

    // Test English
    echo "\n--- ENGLISH ---\n";
    app()->setLocale('en');
    echo 'Locale: '.app()->getLocale()."\n";
    echo 'Description AR (DB): '.($cat->description_ar ?? 'NULL/EMPTY')."\n";
    echo 'Description EN (DB): '.($cat->description_en ?? 'NULL/EMPTY')."\n";
    echo 'Generated Description: '.$cat->translated_description."\n";

    // Test Arabic
    echo "\n--- ARABIC ---\n";
    app()->setLocale('ar');
    echo 'Locale: '.app()->getLocale()."\n";
    echo 'Description AR (DB): '.($cat->description_ar ?? 'NULL/EMPTY')."\n";
    echo 'Description EN (DB): '.($cat->description_en ?? 'NULL/EMPTY')."\n";
    echo 'Generated Description: '.$cat->translated_description."\n";
    echo "(Should contain Arabic, not English!)\n";

    // Test French
    echo "\n--- FRENCH ---\n";
    app()->setLocale('fr');
    echo 'Locale: '.app()->getLocale()."\n";
    echo 'Description AR (DB): '.($cat->description_ar ?? 'NULL/EMPTY')."\n";
    echo 'Description EN (DB): '.($cat->description_en ?? 'NULL/EMPTY')."\n";
    echo 'Generated Description: '.$cat->translated_description."\n";

    echo "\n✅ TEST COMPLETE\n";
} else {
    echo "No section found\n";
}
