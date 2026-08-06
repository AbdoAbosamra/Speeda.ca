<?php

namespace App\Services;

use App\Models\ServiceProvider;
use App\Support\AdminAnalyticsExclusion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminProviderActivityMonitorService
{
    private const SUMMARY_CACHE_KEY = 'admin_provider_monitor_summary';

    public function paginateProviders(int $perPage = 15, ?Request $request = null): LengthAwarePaginator
    {
        $query = $this->baseProviderActivityQuery();

        // Apply search filter
        if ($request && $search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('sp.id', 'LIKE', "%{$search}%")
                    ->orWhere('sp.company_name', 'LIKE', "%{$search}%")
                    ->orWhere('u.name', 'LIKE', "%{$search}%")
                    ->orWhere('u.email', 'LIKE', "%{$search}%");
            });
        }

        // Apply completion status filter
        if ($request && $status = $request->input('completion_status')) {
            switch ($status) {
                case 'complete':
                    $query->where('sp.profile_completion_percent', '>=', 100);
                    break;
                case 'partial':
                    $query->whereBetween('sp.profile_completion_percent', [1, 99]);
                    break;
                case 'incomplete':
                    $query->where('sp.profile_completion_percent', '<=', 0);
                    break;
            }
        }

        // Apply issue filter
        if ($request && $issue = $request->input('issue')) {
            switch ($issue) {
                case 'missing_photo':
                    $query->where(function ($q) {
                        $q->whereNull('sp.profile_image')->orWhere('sp.profile_image', '');
                    });
                    break;
                case 'missing_gallery':
                    $query->whereRaw('COALESCE(g.gallery_count, 0) < 4');
                    break;
                case 'missing_services':
                    $query->where(function ($q) {
                        $q->whereNull('sp.services_offered')
                            ->orWhere('sp.services_offered', '')
                            ->orWhere('sp.services_offered', '[]');
                    });
                    break;
                case 'missing_address':
                    $query->where(function ($q) {
                        $q->whereNull('sp.address')->orWhere('sp.address', '');
                    });
                    break;
            }
        }

        // Listing status filter (admin management, not analytics).
        if ($request && $listing = $request->input('listing_status')) {
            match ($listing) {
                'active' => $query->where('sp.is_active', true)->where('u.is_active', true),
                'hidden' => $query->where(function ($q) {
                    $q->where('sp.is_active', false)->orWhere('u.is_active', false);
                }),
                'verified' => $query->where('sp.is_verified', true),
                'unverified' => $query->where('sp.is_verified', false),
                'featured' => $query->where('sp.is_featured', true),
                default => null,
            };
        }

        // Apply activity date filter
        if ($request && $activity = $request->input('activity')) {
            switch ($activity) {
                case 'today':
                    $query->whereDate('a.last_activity_at', today());
                    break;
                case 'week':
                    $query->where('a.last_activity_at', '>=', now()->subWeek());
                    break;
                case 'month':
                    $query->where('a.last_activity_at', '>=', now()->subMonth());
                    break;
                case 'never':
                    $query->whereNull('a.last_activity_at');
                    break;
            }
        }

        $sort = $request?->input('sort', 'attention') ?: 'attention';
        match ($sort) {
            'views' => $query->orderByDesc('profile_views')->orderByDesc('whatsapp_clicks'),
            'clicks' => $query->orderByDesc('whatsapp_clicks')->orderByDesc('profile_views'),
            'completion_low' => $query->orderBy('sp.profile_completion_percent')->orderByDesc('sp.created_at'),
            'newest' => $query->orderByDesc('sp.created_at'),
            default => $query
                ->orderByRaw('CASE WHEN sp.profile_completion_percent < 100 THEN 0 ELSE 1 END')
                ->orderByRaw('CASE WHEN a.last_activity_at IS NULL THEN 0 ELSE 1 END')
                ->orderBy('sp.profile_completion_percent')
                ->orderByRaw('COALESCE(a.last_activity_at, sp.created_at) DESC'),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Headline counters for the provider monitor.
     *
     * Cached for a minute: the underlying query joins analytics + media
     * aggregates for EVERY provider with no limit, so recomputing it on each
     * page view (including every filter change) is expensive and the numbers do
     * not need to be second-accurate.
     */
    public function getIndexSummary(): array
    {
        return Cache::remember(
            self::SUMMARY_CACHE_KEY,
            now()->addMinute(),
            fn () => $this->buildIndexSummary()
        );
    }

    /**
     * Drop the cached summary after an admin changes a provider.
     */
    public static function forgetSummaryCache(): void
    {
        Cache::forget(self::SUMMARY_CACHE_KEY);
    }

    private function buildIndexSummary(): array
    {
        $rows = $this->baseProviderActivityQuery()->get();

        $total = $rows->count();
        $totalViews = (int) $rows->sum('profile_views');
        $totalClicks = (int) $rows->sum('whatsapp_clicks');

        return [
            'total' => $total,
            'complete' => $rows->where('profile_completion_percent', '>=', 100)->count(),
            'needs_work' => $rows->where('profile_completion_percent', '<', 100)->count(),
            'no_activity' => $rows->whereNull('last_activity_at')->count(),
            'missing_photo' => $rows->where('has_profile_photo', 0)->count(),
            'missing_gallery' => $rows->filter(fn ($row) => (int) $row->gallery_count < 4)->count(),
            'total_views' => $totalViews,
            'total_clicks' => $totalClicks,
            'conversion_rate' => $totalViews > 0 ? round(($totalClicks / $totalViews) * 100, 1) : 0,
            // Listing management counters.
            'hidden' => $rows->filter(fn ($row) => !$row->is_active || !$row->owner_is_active)->count(),
            'verified' => $rows->where('is_verified', 1)->count(),
            'featured' => $rows->where('is_featured', 1)->count(),
        ];
    }

    public function getProviderDetails(ServiceProvider $serviceProvider, int $perPage = 30): array
    {
        $provider = $serviceProvider->loadMissing(['user', 'category']);

        $eventsQuery = DB::table('analytics')
            ->where('provider_id', $provider->id)
            ->tap(fn($q) => AdminAnalyticsExclusion::apply($q))
            ->orderByDesc('created_at');

        $events = $eventsQuery->paginate($perPage)->withQueryString();

        $summary = (clone $eventsQuery)->selectRaw('
                SUM(CASE WHEN action_type = "view" THEN 1 ELSE 0 END) as profile_views,
                SUM(CASE WHEN action_type = "click_whatsapp" THEN 1 ELSE 0 END) as whatsapp_clicks,
                MAX(created_at) as last_activity_at
            ')->first();

        return [
            'provider' => $provider,
            'events' => $events,
            'summary' => $summary,
        ];
    }

    private function baseProviderActivityQuery()
    {
        $serviceProviderMorphType = (new ServiceProvider())->getMorphClass();

        $analyticsAgg = DB::table('analytics')
            ->selectRaw('
                provider_id,
                SUM(CASE WHEN action_type = "view" THEN 1 ELSE 0 END) as profile_views,
                SUM(CASE WHEN action_type = "click_whatsapp" THEN 1 ELSE 0 END) as whatsapp_clicks,
                MAX(created_at) as last_activity_at,
                SUBSTRING_INDEX(GROUP_CONCAT(action_type ORDER BY created_at DESC, id DESC), ",", 1) as last_action_type
            ')
            ->tap(fn($q) => AdminAnalyticsExclusion::apply($q))
            ->groupBy('provider_id');

        $galleryAgg = DB::table('media')
            ->selectRaw('
                model_id as provider_id,
                COUNT(*) as gallery_count
            ')
            ->where('collection_name', 'gallery')
            ->where('model_type', $serviceProviderMorphType)
            ->groupBy('model_id');

        return DB::table('service_providers as sp')
            ->leftJoin('users as u', 'sp.user_id', '=', 'u.id')
            ->leftJoin('categories as c', 'sp.category_id', '=', 'c.id')
            ->leftJoin('locations as l', 'sp.location_id', '=', 'l.id')
            ->leftJoinSub($analyticsAgg, 'a', 'sp.id', '=', 'a.provider_id')
            ->leftJoinSub($galleryAgg, 'g', 'sp.id', '=', 'g.provider_id')
            ->selectRaw('
                sp.id,
                sp.user_id,
                sp.company_name,
                sp.profile_completion_percent,
                sp.profile_image,
                sp.created_at,
                sp.bio,
                sp.address,
                sp.experience_years,
                sp.phone,
                sp.whatsapp_number,
                sp.services_offered,
                sp.is_verified,
                sp.is_featured,
                sp.is_active,
                sp.is_certified,
                u.name as owner_name,
                u.email as owner_email,
                u.is_active as owner_is_active,
                c.name as category_name,
                c.name_en as category_name_en,
                l.city as location_city,
                COALESCE(a.profile_views, 0) as profile_views,
                COALESCE(a.whatsapp_clicks, 0) as whatsapp_clicks,
                a.last_activity_at,
                a.last_action_type,
                COALESCE(g.gallery_count, 0) as gallery_count,
                CASE WHEN sp.profile_image IS NOT NULL AND sp.profile_image <> "" THEN 1 ELSE 0 END as has_profile_photo,
                CASE WHEN sp.bio IS NOT NULL AND sp.bio <> "" THEN 1 ELSE 0 END as has_description,
                CASE WHEN sp.address IS NOT NULL AND sp.address <> "" THEN 1 ELSE 0 END as has_address,
                CASE WHEN sp.experience_years IS NOT NULL AND sp.experience_years > 0 THEN 1 ELSE 0 END as has_experience,
                CASE WHEN sp.services_offered IS NOT NULL AND sp.services_offered <> "" AND sp.services_offered <> "[]" THEN 1 ELSE 0 END as has_services
            ');
    }
}
