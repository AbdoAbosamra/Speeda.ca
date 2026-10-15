<?php

namespace App\Traits;

use App\Services\CategoryCacheService;
use App\Services\LocationCacheService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Cache invalidation shared by the admin controllers that write categories,
 * locations and users.
 */
trait ClearsAdminCaches
{
    /**
     * Derived dashboard/analytics caches that must be dropped whenever an admin
     * changes underlying data. Keys mirror AdminDashboardService + helpers.
     */
    private const ADMIN_DASHBOARD_CACHE_KEYS = [
        'admin_dashboard_summary',
        'admin_dash_action_center',
        'admin_dash_kpis',
        'admin_dash_offers',
        'admin_dash_funnel',
        'admin_dash_visitor_trend_14',
        'admin_dash_profile_health',
        'admin_user_ids',
        'visitor_stats',
        'live_visitors_count',
    ];

    /**
     * Invalidate only the caches an admin write can actually invalidate.
     *
     * This deliberately does NOT call view:clear / route:clear / config:clear:
     * those wipe the production optimisation caches (making every subsequent
     * request slow until `optimize` runs again) and `cache:clear` would also
     * flush unrelated stores — including sessions when the session driver
     * shares the cache backend.
     */
    protected function clearApplicationCaches(): void
    {
        try {
            app(CategoryCacheService::class)->invalidateCache();
            app(LocationCacheService::class)->invalidateCache();

            foreach (self::ADMIN_DASHBOARD_CACHE_KEYS as $key) {
                Cache::forget($key);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to clear application caches', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
