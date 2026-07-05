<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tables = [];
foreach (DB::select('SHOW TABLES') as $t) {
    $n = array_values((array)$t)[0];
    try {
        if (in_array('category_id', Schema::getColumnListing($n))) {
            $tables[] = $n;
        }
    } catch (\Exception $e) {}
}
print_r($tables);
