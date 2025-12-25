<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Facade::setFacadeApplication($app);

use App\Models\ServiceProvider;

$id = $argv[1] ?? 16;
$sp = ServiceProvider::withTrashed()->find($id);
if (!$sp) {
    echo "NOT FOUND\n";
    exit(0);
}

echo "FOUND id={$sp->id} verified=" . ((int)$sp->is_verified) . " business_name=" . ($sp->business_name ?? '(null)') . "\n";
echo json_encode($sp->toArray(), JSON_PRETTY_PRINT) . "\n";
