<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option defines the default authentication "guard" and password
    | reset "broker" for your application. You may change these values
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | which utilizes session storage plus the Eloquent user provider.
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | If you have multiple user tables or models you may configure multiple
    | providers to represent the model / table. These providers may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | These configuration options specify the behavior of Laravel's password
    | reset functionality, including the table utilized for token storage
    | and the user provider that is invoked to actually retrieve users.
    |
    | The expiry time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    | The throttle setting is the number of seconds a user must wait before
    | generating more password reset tokens. This prevents the user from
    | quickly generating a very large amount of password reset tokens.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_reset' => [
        'support_email' => env('SUPPORT_EMAIL', env('MAIL_FROM_ADDRESS', 'support@speeda.ca')),
        'rate_limit_per_minute' => env('PASSWORD_RESET_RATE_LIMIT_PER_MINUTE', 5),
        'rate_limit_per_hour' => env('PASSWORD_RESET_RATE_LIMIT_PER_HOUR', 20),
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the number of seconds before a password confirmation
    | window expires and users are asked to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

    /*
    |--------------------------------------------------------------------------
    | Admin Users Configuration
    |--------------------------------------------------------------------------
    |
    | This option defines the admin users who have full access to the admin
    | panel. These email addresses are loaded from the ADMINS and ADMIN_EMAIL
    | environment variables and cached when config:cache is run.
    |
    | IMPORTANT: Always use config('auth.admins') instead of env() directly
    | to prevent admin access issues when configuration is cached.
    |
    */

    'admins' => array_filter(
        array_map('trim', explode(',', env('ADMINS', env('ADMIN_EMAIL', ''))))
    ),

    /*
    |--------------------------------------------------------------------------
    | Admin Login Email
    |--------------------------------------------------------------------------
    |
    | The email used when an admin types 'admin' as the login shortcut.
    | Override via ADMIN_EMAIL in .env for security.
    |
    */

    'admin_email' => env('ADMIN_EMAIL', 'admin@speeda.com'),

    /*
    |--------------------------------------------------------------------------
    | Auto-Create Admin Configuration
    |--------------------------------------------------------------------------
    |
    | These options control the automatic creation of an admin user during
    | application boot. This is typically used during initial deployment.
    |
    */

    'auto_create_admin' => [
        'enabled' => env('AUTO_CREATE_ADMIN', false),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
        'name' => env('ADMIN_NAME', 'Administrator'),
    ],

];
