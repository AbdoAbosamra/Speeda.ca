<?php

$en = include('lang/en/categories.php');
$fr = include('lang/fr/categories.php');
$ar = include('lang/ar/categories.php');

$missing_fr = array_diff_key($en, $fr);
$missing_ar = array_diff_key($en, $ar);

echo "Missing in French: " . count($missing_fr) . PHP_EOL;
echo "Missing in Arabic: " . count($missing_ar) . PHP_EOL;

if (count($missing_fr) > 0) {
    echo "\nMissing keys in French:\n";
    foreach(array_keys($missing_fr) as $key) {
        echo "  - $key\n";
    }
}

if (count($missing_ar) > 0) {
    echo "\nMissing keys in Arabic:\n";
    foreach(array_keys($missing_ar) as $key) {
        echo "  - $key\n";
    }
}

if (count($missing_fr) === 0 && count($missing_ar) === 0) {
    echo "\n✅ All translations are complete!\n";
}
