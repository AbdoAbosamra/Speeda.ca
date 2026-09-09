<?php

// Check literal keys without booting the app or using fallback translations.
require __DIR__.'/vendor/autoload.php';

$keys = [];
foreach (['app', 'resources/views', 'routes'] as $directory) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/'.$directory));
    foreach ($files as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }
        preg_match_all('/(?:__|trans|trans_choice|@lang)\s*\(\s*([\'"])([a-zA-Z0-9_]+\.[a-zA-Z0-9_.-]+)\1\s*(?=[,)])/u', file_get_contents($file->getPathname()), $matches);
        foreach ($matches[2] as $key) {
            $keys[$key] = true;
        }
    }
}
ksort($keys);
$missing = [];
foreach (['en', 'ar', 'fr'] as $locale) {
    $groups = [];
    foreach (array_keys($keys) as $fullKey) {
        [$group, $key] = explode('.', $fullKey, 2);
        if (! isset($groups[$group])) {
            $path = __DIR__."/lang/{$locale}/{$group}.php";
            $groups[$group] = is_file($path) ? require $path : [];
        }
        if (! Illuminate\Support\Arr::has($groups[$group], $key)) {
            $missing[$locale][$fullKey] = true;
        }
    }
}
file_put_contents(__DIR__.'/missing_translations.json', json_encode($missing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL);
$unique = [];
foreach ($missing as $locale => $localeKeys) {
    foreach (array_keys($localeKeys) as $key) {
        echo "{$locale}: {$key}\n";
        $unique[$key] = true;
    }
}
file_put_contents(__DIR__.'/unique_missing.txt', implode(PHP_EOL, array_keys($unique)).($unique ? PHP_EOL : ''));
echo count($keys).' literal translation keys checked; '.count($unique)." missing keys. Dynamic expressions require runtime coverage.\n";
exit($missing ? 1 : 0);
