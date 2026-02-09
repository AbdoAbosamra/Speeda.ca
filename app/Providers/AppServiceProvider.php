<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
        //
    ];

    /**
     * All of the container singletons that should be registered.
     *
     * @var array
     */
    public $singletons = [
        //
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::share('supportedLocales', config('app.supported_locales'));

        // Auto-create admin user if configured via environment (only when explicitly enabled)
        try {
            if (env('AUTO_CREATE_ADMIN', false)) {
                $adminEmail = env('ADMIN_EMAIL');
                $adminPassword = env('ADMIN_PASSWORD');
                if ($adminEmail && $adminPassword) {
                    $exists = User::where('email', $adminEmail)->first();
                    if (!$exists) {
                        $user = User::create([
                            'name' => env('ADMIN_NAME', 'Administrator'),
                            'email' => $adminEmail,
                            'password' => Hash::make($adminPassword),
                            'role' => 'admin',
                        ]);
                        Log::warning('Auto-created admin user: '.$adminEmail);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Auto-create admin failed: '.$e->getMessage());
        }
    }
}

