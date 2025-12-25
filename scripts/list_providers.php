<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Facade::setFacadeApplication($app);

use App\Models\ServiceProvider;

$providers = ServiceProvider::orderBy('id', 'asc')->limit(50)->get(['id','business_name','business_slug','is_verified']);
if ($providers->isEmpty()) {
    echo "NO PROVIDERS\n";
    exit(0);
}
foreach ($providers as $p) {
    echo sprintf("id=%d verified=%d name=%s slug=%s\n", $p->id, (int)$p->is_verified, $p->business_name ?? '(null)', $p->business_slug ?? '(null)');
}
