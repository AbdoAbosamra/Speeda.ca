#!/usr/bin/env php
<?php

use Illuminate\Support\Facades\DB;

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cat = DB::table('categories')->where('id', 92)->first();

echo "\n✅ RESTAURANTS CATEGORY UPDATE CHECK\n";
echo "═════════════════════════════════════════════\n\n";
echo "English (name_en): {$cat->name_en}\n";
echo "Arabic (name_ar):  {$cat->name_ar}\n";
echo "French (name_fr):  {$cat->name_fr}\n\n";

$check = ($cat->name_en === 'Restaurants and Cafe' &&
          $cat->name_ar === 'المطاعم والكافيهات' &&
          $cat->name_fr === 'Restaurants et Cafés');

if ($check) {
    echo "✅ ALL TRANSLATIONS ARE CORRECT!\n";
} else {
    echo "❌ SOME TRANSLATIONS ARE INCORRECT\n";
    echo "   Expected EN: Restaurants and Cafe\n";
    echo "   Expected AR: المطاعم والكافيهات\n";
    echo "   Expected FR: Restaurants et Cafés\n";
}

echo "═════════════════════════════════════════════\n\n";
