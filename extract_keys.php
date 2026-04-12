<?php
$d = json_decode(file_get_contents('missing_translations.json'), true);
$keys = [];
foreach($d as $lang => $group){
    foreach($group as $k => $v){
        $keys[$k] = true;
    }
}
file_put_contents('unique_missing.txt', implode("\n", array_keys($keys)));
echo "Extracted " . count($keys) . " unique keys.\n";
