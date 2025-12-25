<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Facade::setFacadeApplication($app);

use Illuminate\Support\Facades\DB;

$rows = DB::table('service_providers')->select('id','is_verified','company_name','phone','created_at')->orderBy('id','asc')->limit(50)->get();
if ($rows->isEmpty()) {
    echo "NO ROWS\n";
    exit(0);
}
foreach ($rows as $r) {
    echo sprintf("id=%d verified=%d company=%s phone=%s created_at=%s\n", $r->id, (int)$r->is_verified, $r->company_name ?? '(null)', $r->phone ?? '(null)', $r->created_at ?? '(null)');
}
