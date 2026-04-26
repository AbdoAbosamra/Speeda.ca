<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\ServiceProvider as ServiceProviderModel;
use Illuminate\Support\ServiceProvider;
use App\Observers\ServiceProviderObserver;

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

        // SEO & Business Logic Observers
        \App\Models\Category::observe(\App\Observers\CategoryObserver::class);
        ServiceProviderModel::observe(ServiceProviderObserver::class);

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

        // Share active notifications for service providers in the navbar
        View::composer('components.main-nav', function ($view) {
            if (auth()->check() && auth()->user()->isServiceProvider()) {
                $user = auth()->user();
                $notifications = \App\Models\AdminNotification::active()
                    ->orderBy('created_at', 'desc')
                    ->get();
                
                $readNotificationIds = $user->readAdminNotifications()
                    ->whereIn('admin_notification_id', $notifications->pluck('id'))
                    ->pluck('admin_notification_id')
                    ->toArray();

                $unreadCount = $notifications->whereNotIn('id', $readNotificationIds)->count();

                $view->with([
                    'activeNotifications' => $notifications,
                    'unreadCount' => $unreadCount,
                    'readNotificationIds' => $readNotificationIds
                ]);
            } else {
                $view->with([
                    'activeNotifications' => collect(),
                    'unreadCount' => 0,
                    'readNotificationIds' => []
                ]);
            }
        });
    }
}

