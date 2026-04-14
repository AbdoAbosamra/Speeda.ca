<?php
// lang_fix.php - fill missing translation keys in target langs with English placeholders
$root = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'lang');
$source = $root . DIRECTORY_SEPARATOR . 'en';
$targets = ['ar', 'fr'];

function mergeMissing(array $source, array &$target): bool {
    $changed = false;
    foreach ($source as $k => $v) {
        if (!array_key_exists($k, $target)) {
            $target[$k] = $v;
            $changed = true;
            continue;
        }
        if (is_array($v) && is_array($target[$k])) {
            if (mergeMissing($v, $target[$k])) $changed = true;
        }
    }
    return $changed;
}

function arrayToShortPhp(array $arr): string {
    // Use var_export then convert array() to [] style
    $export = var_export($arr, true);
    // convert array ( ) to [ ]
    $export = preg_replace(['/array \(/', '/\)(,?)/'], ['[', ']$1'], $export);
    // fix => formatting spacing
    $export = str_replace("=>\n        [", '=> [', $export);
    return $export;
}

$files = array_filter(scandir($source), function($f) use ($source) { return substr($f, -4) === '.php'; });
$report = [];
foreach ($files as $f) {
    $enPath = $source . DIRECTORY_SEPARATOR . $f;
    $en = include $enPath;
    foreach ($targets as $t) {
        $tDir = $root . DIRECTORY_SEPARATOR . $t;
        if (!is_dir($tDir)) continue;
        $tPath = $tDir . DIRECTORY_SEPARATOR . $f;
        $t = [];
        if (file_exists($tPath)) {
            $t = include $tPath;
        }
        $orig = $t;
        $changed = mergeMissing($en, $t);
        if ($changed) {
            $php = "<?php\n\nreturn " . arrayToShortPhp($t) . ";\n";
            file_put_contents($tPath, $php);
            $report[$f][] = $tPath;
        }
    }
}

echo json_encode(['fixed' => $report], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
