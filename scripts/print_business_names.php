<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Facade::setFacadeApplication($app);

use App\Models\ServiceProvider;

$providers = ServiceProvider::all();
if ($providers->isEmpty()) {
    echo "NO PROVIDERS\n";
    exit(0);
}
foreach ($providers as $p) {
    echo sprintf("id=%d name=%s verified=%d views=%s\n", $p->id, $p->business_name ?? '(null)', (int)($p->is_verified ?? 0), $p->views);
}
