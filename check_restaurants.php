<?php
$category = DB::table('categories')->where('id', 92)->select('id', 'name', 'name_en', 'name_ar', 'name_fr')->first();
echo 'ID: ' . $category->id . "\n";
echo 'Name (English): ' . $category->name_en . "\n";
echo 'Name (Arabic): ' . $category->name_ar . "\n";
echo 'Name (French): ' . $category->name_fr . "\n";
