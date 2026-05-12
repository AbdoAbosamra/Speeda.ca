<?php

use App\Models\ServiceProvider;
use App\Actions\CalculateProfileCompletionAction;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$action = new CalculateProfileCompletionAction();
$providers = ServiceProvider::all();

echo "Recalculating profile completion for " . $providers->count() . " providers...\n";

foreach ($providers as $provider) {
    $percent = $action->execute($provider);
    echo "Provider ID {$provider->id}: {$percent}%\n";
}

echo "Done!\n";
