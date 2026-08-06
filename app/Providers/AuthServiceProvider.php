<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Location;
use App\Models\Review;
use App\Models\ServiceProvider as ServiceProviderModel;
use App\Policies\CategoryPolicy;
use App\Policies\CommentPolicy;
use App\Policies\LocationPolicy;
use App\Policies\ReviewPolicy;
use App\Policies\ServiceProviderPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Registers authorization policies and admin gates.
 *
 * NOTE: this file previously aliased the framework's legacy AuthServiceProvider
 * to `ServiceProvider`, which collided with the `App\Models\ServiceProvider`
 * import above it and made the file impossible to parse. It was also missing
 * from bootstrap/providers.php, so none of these gates existed at runtime.
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected array $policies = [
        Category::class => CategoryPolicy::class,
        Comment::class => CommentPolicy::class,
        Location::class => LocationPolicy::class,
        Review::class => ReviewPolicy::class,
        ServiceProviderModel::class => ServiceProviderPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        // Admin access gates.
        Gate::define('admin-only', fn ($user) => (bool) ($user && $user->isAdmin()));
        Gate::define('manage-categories', fn ($user) => (bool) ($user && $user->isAdmin()));
        Gate::define('manage-locations', fn ($user) => (bool) ($user && $user->isAdmin()));
        Gate::define('manage-providers', fn ($user) => (bool) ($user && $user->isAdmin()));
        Gate::define('view-visitor-analytics', fn ($user) => (bool) ($user && $user->isAdmin()));
    }
}
