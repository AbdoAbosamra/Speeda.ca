<?php

use App\Models\ServiceProvider;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

ServiceProvider::where('is_verified', 0)->update(['is_verified' => 1]);
echo "All providers set to verified.\n";
