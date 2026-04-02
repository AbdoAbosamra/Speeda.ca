<?php

namespace App\Services;

use App\Models\ServiceProvider;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AdminProviderActivityMonitorService
{
    public function paginateProviders(int $perPage = 15): LengthAwarePaginator
    {
        $serviceProviderMorphType = (new ServiceProvider())->getMorphClass();

        // Aggregate analytics per provider (views + WhatsApp clicks + last activity).
        $analyticsAgg = DB::table('analytics')
            ->selectRaw('
                provider_id,
                SUM(CASE WHEN action_type = "view" THEN 1 ELSE 0 END) as profile_views,
                SUM(CASE WHEN action_type = "click_whatsapp" THEN 1 ELSE 0 END) as whatsapp_clicks,
                MAX(created_at) as last_activity_at
            ')
            ->groupBy('provider_id');

        // Aggregate gallery count per provider.
        $galleryAgg = DB::table('media')
            ->selectRaw('
                model_id as provider_id,
                COUNT(*) as gallery_count
            ')
            ->where('collection_name', 'provider_gallery')
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
                COALESCE(g.gallery_count, 0) as gallery_count,
                CASE WHEN sp.profile_image IS NOT NULL AND sp.profile_image <> "" THEN 1 ELSE 0 END as has_profile_photo
            ')
            ->orderByRaw('COALESCE(a.last_activity_at, sp.created_at) DESC');

        return $query->paginate($perPage)->withQueryString();
    }

    public function getProviderDetails(ServiceProvider $serviceProvider, int $perPage = 30): array
    {
        $provider = $serviceProvider->loadMissing(['user', 'category']);

        $eventsQuery = DB::table('analytics')
            ->where('provider_id', $provider->id)
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

