<?php

namespace App\Services;

use App\Models\ServiceProvider;
use App\Support\AdminAnalyticsExclusion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminProviderActivityMonitorService
{
    public function paginateProviders(int $perPage = 15, ?Request $request = null): LengthAwarePaginator
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

        $query = DB::table('service_providers as sp')
            ->leftJoinSub($analyticsAgg, 'a', 'sp.id', '=', 'a.provider_id')
            ->leftJoinSub($galleryAgg, 'g', 'sp.id', '=', 'g.provider_id')
            ->selectRaw('
                sp.id,
                sp.company_name,
                sp.profile_completion_percent,
                sp.profile_image,
                sp.created_at,
                COALESCE(a.profile_views, 0) as profile_views,
                COALESCE(a.whatsapp_clicks, 0) as whatsapp_clicks,
                a.last_activity_at,
                a.last_action_type,
                COALESCE(g.gallery_count, 0) as gallery_count,
                CASE WHEN sp.profile_image IS NOT NULL AND sp.profile_image <> "" THEN 1 ELSE 0 END as has_profile_photo
            ');

        // Apply search filter
        if ($request && $search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('sp.id', 'LIKE', "%{$search}%")
                  ->orWhere('sp.company_name', 'LIKE', "%{$search}%");
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

        $query->orderByRaw('COALESCE(a.last_activity_at, sp.created_at) DESC');

        return $query->paginate($perPage)->withQueryString();
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
}
