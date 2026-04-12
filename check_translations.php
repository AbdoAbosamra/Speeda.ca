<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$viewsDir = __DIR__ . '/resources/views';
$langDirs = ['en', 'ar', 'fr'];
$baseLangDir = __DIR__ . '/lang';

$missingKeys = [];
$foundKeys = [];

function checkDirectory($dir) {
    global $foundKeys;
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            checkDirectory($path);
        } elseif (preg_match('/\.blade\.php$/', $file)) {
            $content = file_get_contents($path);
            // Match __('group.key') or trans('group.key') or @lang('group.key')
            // Using a basic regex, could be improved but covers most cases
            preg_match_all("/(?:__|trans|@lang)\s*\(\s*['\"]([^'\"]+)['\"]/", $content, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $key) {
                    // Skip keys that don't have a dot (might be JSON keys which are handled differently)
                    if (str_contains($key, '.')) {
                        $foundKeys[$key][] = $path;
                    }
                }
            }
        }
    }
}

checkDirectory($viewsDir);

echo "Found " . count($foundKeys) . " unique translation keys in blade files.\n";

$allMissing = [];

foreach ($langDirs as $lang) {
    foreach (array_keys($foundKeys) as $fullKey) {
        $parts = explode('.', $fullKey, 2);
        $group = $parts[0];
        $key = $parts[1];
        
        $langFile = $baseLangDir . '/' . $lang . '/' . $group . '.php';
        if (file_exists($langFile)) {
            $translations = require $langFile;
            if (!isset($translations[$key])) {
                $allMissing[$lang][$fullKey] = true;
            }
        } else {
            // Group file missing
            $allMissing[$lang][$fullKey] = true;
        }
    }
}

echo "\n--- Missing Translations ---\n";
foreach ($allMissing as $lang => $keys) {
    echo "\nLanguage: $lang\n";
    foreach ($keys as $k => $v) {
        echo "- $k\n";
    }
}

file_put_contents(__DIR__ . '/missing_translations.json', json_encode($allMissing, JSON_PRETTY_PRINT));
echo "\nSaved missing list to missing_translations.json\n";
