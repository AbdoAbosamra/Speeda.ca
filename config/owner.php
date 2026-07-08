<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Owner / whitelisted phone numbers
    |--------------------------------------------------------------------------
    |
    | Phone numbers listed here bypass the Canadian phone-number validation rule.
    | This is intentionally restricted to the site owner's numbers so that the
    | ONLY non-Canadian (e.g. Egyptian) number accepted anywhere in the app is
    | the owner's. Any other foreign number is still rejected.
    |
    | Comparison is digits-only (country code included, symbols ignored), e.g.
    | "+20 128 912 1218" and "00201289121218" both match "201289121218".
    |
    */
    'exempt_phones' => array_values(array_filter(array_map(
        fn ($p) => preg_replace('/\D/', '', (string) $p),
        explode(',', (string) env('OWNER_EXEMPT_PHONES', '+201289121218'))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Site developer account
    |--------------------------------------------------------------------------
    |
    | The email of the service-provider account that belongs to the site's
    | developer. This account gets a special "Site Developer" badge on its card
    | and profile. Keep in sync with database/seeders/OwnerProviderSeeder.php.
    |
    */
    'email' => env('OWNER_EMAIL', 'abdo.abosamra80@gmail.com'),
];
