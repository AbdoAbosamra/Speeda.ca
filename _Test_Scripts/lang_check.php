<?php
// lang_check.php - compare translation keys across lang/{en,ar,fr}
$root = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'lang';
$root = realpath($root);
$langs = array_filter(scandir($root), function($d) use ($root) {
    return $d !== '.' && $d !== '..' && is_dir($root . DIRECTORY_SEPARATOR . $d);
});
$key_re = '/["\']([a-zA-Z0-9_\.]+)["\']\s*=>/';
$file_keys = [];
foreach ($langs as $lang) {
    $dir = $root . DIRECTORY_SEPARATOR . $lang;
    $files = array_filter(scandir($dir), function($f){ return substr($f, -4) === '.php'; });
    foreach ($files as $f) {
        $path = $dir . DIRECTORY_SEPARATOR . $f;
        $txt = file_get_contents($path);
        preg_match_all($key_re, $txt, $matches);
        $keys = array_values(array_unique($matches[1]));
        sort($keys);
        $file_keys[$lang][$f] = $keys;
    }
}
$all_files = [];
foreach ($file_keys as $lang => $files) {
    foreach ($files as $f => $_) $all_files[$f] = true;
}
ksort($all_files);
$result = [];
foreach (array_keys($all_files) as $f) {
    $union = [];
    foreach ($file_keys as $lang => $files) {
        if (isset($files[$f])) $union = array_unique(array_merge($union, $files[$f]));
    }
    sort($union);
    foreach ($file_keys as $lang => $files) {
        $have = isset($files[$f]) ? $files[$f] : [];
        $missing = array_values(array_diff($union, $have));
        if (!empty($missing)) {
            $result[$f][$lang] = array_values($missing);
        }
    }
}
echo json_encode(['langs' => array_values($langs), 'issues' => $result], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
