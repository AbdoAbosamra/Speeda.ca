<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== W3: Others Category Check ===\n";
$others = App\Models\Category::where('name_ar', 'أخرى')
    ->orWhere('name_en', 'Others')
    ->orWhere('name_en', 'others')
    ->get(['id', 'name_ar', 'name_en', 'name_fr', 'slug']);

echo "Found: " . $others->count() . "\n";
foreach ($others as $c) {
    echo "  id={$c->id} name_ar={$c->name_ar} name_en=" . ($c->name_en ?? 'NULL') . " name_fr=" . ($c->name_fr ?? 'NULL') . " slug={$c->slug}\n";
}

echo "\n=== W5: All is_section categories check ===\n";
$sections = App\Models\Category::where('is_section', true)
    ->get(['id', 'name_ar', 'name_en', 'name_fr', 'slug', 'is_section']);
echo "Total sections: " . $sections->count() . "\n";
foreach ($sections as $s) {
    $missing = empty($s->name_en) ? ' MISSING name_en' : '';
    echo "  id={$s->id} name_ar={$s->name_ar} name_en=" . ($s->name_en ?? 'NULL') . "$missing\n";
}

echo "\n=== All categories with missing name_en ===\n";
$missingCount = App\Models\Category::whereNull('name_en')
    ->orWhere('name_en', '')
    ->count();
echo "Categories with missing name_en: $missingCount\n";
$missing = App\Models\Category::whereNull('name_en')
    ->orWhere('name_en', '')
    ->get(['id', 'name_ar', 'name_en', 'slug', 'is_section']);
foreach ($missing as $m) {
    echo "  id={$m->id} name_ar={$m->name_ar} slug={$m->slug} section=" . ($m->is_section ? 'YES' : 'NO') . "\n";
}

echo "\n=== DONE ===\n";
