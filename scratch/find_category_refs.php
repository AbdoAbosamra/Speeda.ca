<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tables = DB::select('SHOW TABLES');
foreach ($tables as $table) {
    $tableName = array_values((array)$table)[0];
    try {
        $cols = Schema::getColumnListing($tableName);
        if (in_array('category_id', $cols) || in_array('parent_id', $cols)) {
            echo "Table: $tableName\n";
            if (in_array('category_id', $cols)) echo " - category_id\n";
            if (in_array('parent_id', $cols)) echo " - parent_id\n";
        }
    } catch (\Exception $e) {
        // Skip tables we can't access
    }
}
