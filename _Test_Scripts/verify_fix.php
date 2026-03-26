<?php

use App\Models\Category;

// Get automotive section as example
$automotive = Category::find(1);
$carMechanics = Category::find(7);

echo "\n=== VERIFICATION TEST ===\n";

// Test 1: Check if name_ar is populated
echo "\n1️⃣ Category Names Check:\n";
echo "   Car Mechanics ID 7:\n";
echo '   - EN: '.$carMechanics->name_en."\n";
echo '   - AR: '.($carMechanics->name_ar ?? 'EMPTY')."\n";
echo '   - FR: '.($carMechanics->name_fr ?? 'EMPTY')."\n";

// Test 2: Check localized names
echo "\n2️⃣ Localized Name Accessor Test:\n";
app()->setLocale('en');
echo "   English locale:\n";
echo '   - localized_name: '.$carMechanics->localized_name."\n";

app()->setLocale('ar');
echo "   Arabic locale:\n";
echo '   - localized_name: '.$carMechanics->localized_name."\n";

app()->setLocale('fr');
echo "   French locale:\n";
echo '   - localized_name: '.$carMechanics->localized_name."\n";

// Test 3: Check descriptions with template generation
echo "\n3️⃣ Description Template Generation Test:\n";

app()->setLocale('en');
echo "   English locale (en):\n";
echo '   - translated_description: '.$carMechanics->translated_description."\n";

app()->setLocale('ar');
echo "   Arabic locale (ar):\n";
echo '   - translated_description: '.$carMechanics->translated_description."\n";
echo '   - Contains Arabic? '.(strpos($carMechanics->translated_description, 'خدمات') !== false ? '✅ YES' : '❌ NO')."\n";
echo "   - Does NOT contain 'Professional'? ".(strpos($carMechanics->translated_description, 'Professional') === false ? '✅ YES' : '❌ NO')."\n";

app()->setLocale('fr');
echo "   French locale (fr):\n";
echo '   - translated_description: '.$carMechanics->translated_description."\n";
echo "   - Contains French 'Services'? ".(strpos($carMechanics->translated_description, 'Services de') !== false ? '✅ YES' : '❌ NO')."\n";

echo "\n✅ VERIFICATION COMPLETE\n\n";
