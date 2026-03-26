<?php

require __DIR__ . '/bootstrap/app.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n=== SERVICE PROVIDER DATABASE CHECK ===\n\n";

$total = \App\Models\ServiceProvider::count();
$withImages = \App\Models\ServiceProvider::whereNotNull('profile_image')->where('profile_image', '!=', '')->count();

echo "Total Service Providers: $total\n";
echo "With profile_image: $withImages\n";

echo "\n=== FIRST 5 SERVICE PROVIDERS ===\n";

$sps = \App\Models\ServiceProvider::limit(5)->get();

foreach ($sps as $sp) {
    echo "\nID: {$sp->id}\n";
    echo "Name: {$sp->company_name}\n";
    echo "profile_image (DB): " . ($sp->profile_image ?? 'NULL') . "\n";
    echo "profile_image_url: " . $sp->profile_image_url . "\n";
}
