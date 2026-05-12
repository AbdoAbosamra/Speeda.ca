<?php

use App\Models\ServiceProvider;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$providers = ServiceProvider::with('user')->where('profile_completion_percent', '>=', 80)->get();

echo "Total providers in DB: " . ServiceProvider::count() . "\n";
echo "Providers with >= 80% completion: " . $providers->count() . "\n";
foreach ($providers as $p) {
    echo "ID: {$p->id}, Name: {$p->user->name}, Percent: {$p->profile_completion_percent}%, Category: " . ($p->category_id ?? 'NULL') . "\n";
}
