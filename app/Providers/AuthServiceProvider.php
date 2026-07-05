<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Location;
use App\Models\ServiceProvider;
use App\Policies\CategoryPolicy;
use App\Policies\LocationPolicy;
use App\Policies\ServiceProviderPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Category::class => CategoryPolicy::class,
        Location::class => LocationPolicy::class,
        ServiceProvider::class => ServiceProviderPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Define a gate for admin access (strict scope)
        Gate::define('admin-only', function ($user) {
            return $user && $user->isAdmin();
        });

        // Define gates for specific admin resources
        Gate::define('manage-categories', function ($user) {
            return $user && $user->isAdmin();
        });

        Gate::define('manage-locations', function ($user) {
            return $user && $user->isAdmin();
        });

        Gate::define('view-visitor-analytics', function ($user) {
            return $user && $user->isAdmin();
        });
    }
}
