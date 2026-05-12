<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
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
        // PERFORMANCE: Prevent lazy loading in development/staging to catch N+1 queries
        // CRITICAL: This is NOT enabled in production to avoid breaking the live site
        Model::preventLazyLoading(app()->environment(['local', 'staging']));

        View::share('supportedLocales', config('app.supported_locales'));

        // SEO & Business Logic Observers
        \App\Models\Category::observe(\App\Observers\CategoryObserver::class);
        ServiceProviderModel::observe(ServiceProviderObserver::class);
        
        // PERFORMANCE: Recalculate provider rating when reviews change
        // This keeps the calculated_rating column in sync without subqueries
        \App\Models\Review::observe(\App\Observers\ReviewObserver::class);

        // Auto-create admin user if configured via environment (only when explicitly enabled)
        try {
            $autoCreateConfig = config('auth.auto_create_admin');
            if ($autoCreateConfig['enabled'] ?? false) {
                $adminEmail = $autoCreateConfig['email'] ?? null;
                $adminPassword = $autoCreateConfig['password'] ?? null;
                if ($adminEmail && $adminPassword) {
                    $exists = User::where('email', $adminEmail)->first();
                    if (!$exists) {
                        $user = User::create([
                            'name' => $autoCreateConfig['name'] ?? 'Administrator',
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
        // PERFORMANCE: Cache notifications for 5 minutes to reduce DB queries
        View::composer('components.main-nav', function ($view) {
            if (auth()->check() && auth()->user()->isServiceProvider()) {
                $user = auth()->user();
                $cacheKey = "nav_notifications_{$user->id}";
                
                $notificationData = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($user) {
                    // Get active notifications with limit for dropdown (max 10 for preview)
                    $notifications = \App\Models\AdminNotification::active()
                        ->orderBy('created_at', 'desc')
                        ->take(10)
                        ->get();
                    
                    $readNotificationIds = $user->readAdminNotifications()
                        ->whereIn('admin_notification_id', $notifications->pluck('id'))
                        ->pluck('admin_notification_id')
                        ->toArray();

                    $unreadCount = $notifications->whereNotIn('id', $readNotificationIds)->count();

                    return [
                        'notifications' => $notifications,
                        'unreadCount' => $unreadCount,
                        'readNotificationIds' => $readNotificationIds
                    ];
                });

                $view->with([
                    'activeNotifications' => $notificationData['notifications'],
                    'unreadCount' => $notificationData['unreadCount'],
                    'readNotificationIds' => $notificationData['readNotificationIds']
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

