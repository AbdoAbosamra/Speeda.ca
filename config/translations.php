<?php

/*
 * outhebox/laravel-translations (the /translations editor).
 *
 * The package ships with its own login, password-reset and invitation pages
 * reachable by anyone, plus a "publish" action that rewrites the lang files.
 * Speeda does not use those contributor accounts, so the whole UI sits behind
 * the site's own admin login and a rate limit: visitors get the normal login
 * redirect instead of a second, unthrottled login form.
 */
return [
    'domain' => env('TRANSLATIONS_DOMAIN', null),

    'path' => env('TRANSLATIONS_PATH', 'translations'),

    'middleware' => [
        'web',
        'auth',
        'admin',
        'throttle:60,1',
    ],

    'database_connection' => env('TRANSLATIONS_DB_CONNECTION', null),

    'include_file_in_key' => env('TRANSLATIONS_INCLUDE_FILE_IN_KEY', false),

    'source_language' => env('TRANSLATIONS_SOURCE_LANGUAGE', 'en'),

    'exclude_files' => [
        //
    ],
];
