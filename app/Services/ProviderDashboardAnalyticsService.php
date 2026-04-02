<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProviderDashboardAnalyticsService
{
    /**
     * Get summary stats for the provider dashboard.
     */
    public function getStatsForProvider(int $providerId): array
    {
        $todayStart = Carbon::today()->startOfDay();
        $pastStart = (clone $todayStart)->subDays(6);

        $pastCacheKey = sprintf(
            'provider_dashboard_past_%d_%s',
            $providerId,
            $pastStart->toDateString()
        );

        $past = Cache::remember($pastCacheKey, now()->addMinutes(10), function () use ($providerId, $pastStart, $todayStart) {
            return DB::table('analytics')
                ->where('provider_id', $providerId)
                ->where('created_at', '>=', $pastStart)
                ->where('created_at', '<', $todayStart)
                ->selectRaw('
                    SUM(CASE WHEN action_type = "view" THEN 1 ELSE 0 END) as views_past,
                    SUM(CASE WHEN action_type = "click_whatsapp" THEN 1 ELSE 0 END) as clicks_whatsapp_past
                ')
                ->first();
        });

        // Live query for today only.
        $today = DB::table('analytics')
            ->where('provider_id', $providerId)
            ->where('created_at', '>=', $todayStart)
            ->selectRaw('
                SUM(CASE WHEN action_type = "view" THEN 1 ELSE 0 END) as views_today,
                SUM(CASE WHEN action_type = "click_whatsapp" THEN 1 ELSE 0 END) as clicks_whatsapp_today
            ')
            ->first();

        $viewsToday = (int) ($today->views_today ?? 0);
        $viewsPast = (int) ($past->views_past ?? 0);

        $clicksToday = (int) ($today->clicks_whatsapp_today ?? 0);
        $clicksPast = (int) ($past->clicks_whatsapp_past ?? 0);

        $viewsThisWeek = $viewsPast + $viewsToday;
        $totalClicks = $clicksPast + $clicksToday;

        $engagementRate = 0.0;
        if ($viewsThisWeek > 0) {
            $engagementRate = round(($totalClicks / $viewsThisWeek) * 100, 2);
        }

        return [
            'views_today' => $viewsToday,
            'views_this_week' => $viewsThisWeek,
            'total_clicks' => $totalClicks,
            'engagement_rate' => $engagementRate,
        ];
    }

    /**
     * Get daily trend data for charts (last N days).
     *
     * Returns arrays of date labels and corresponding view/click counts
     * suitable for rendering with Chart.js.
     */
    public function getDailyTrends(int $providerId, int $days = 7): array
    {
        $startDate = Carbon::today()->subDays($days - 1)->startOfDay();

        $rawData = DB::table('analytics')
            ->where('provider_id', $providerId)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('
                DATE(created_at) as date,
                SUM(CASE WHEN action_type = "view" THEN 1 ELSE 0 END) as views,
                SUM(CASE WHEN action_type = "click_whatsapp" THEN 1 ELSE 0 END) as clicks
            ')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $views = [];
        $clicks = [];

        for ($i = 0; $i < $days; $i++) {
            $date = (clone $startDate)->addDays($i)->toDateString();
            $labels[] = Carbon::parse($date)->format('M d');
            $views[] = (int) ($rawData[$date]->views ?? 0);
            $clicks[] = (int) ($rawData[$date]->clicks ?? 0);
        }

        return [
            'labels' => $labels,
            'views' => $views,
            'clicks' => $clicks,
        ];
    }

    /**
     * Get 30-day totals for the PDF export.
     */
    public function getMonthlyStats(int $providerId): array
    {
        $start = Carbon::today()->subDays(29)->startOfDay();

        $data = DB::table('analytics')
            ->where('provider_id', $providerId)
            ->where('created_at', '>=', $start)
            ->selectRaw('
                SUM(CASE WHEN action_type = "view" THEN 1 ELSE 0 END) as total_views,
                SUM(CASE WHEN action_type = "click_whatsapp" THEN 1 ELSE 0 END) as total_clicks
            ')
            ->first();

        $totalViews = (int) ($data->total_views ?? 0);
        $totalClicks = (int) ($data->total_clicks ?? 0);

        return [
            'total_views' => $totalViews,
            'total_clicks' => $totalClicks,
            'engagement_rate' => $totalViews > 0
                ? round(($totalClicks / $totalViews) * 100, 2)
                : 0.0,
            'period_start' => $start->toDateString(),
            'period_end' => Carbon::today()->toDateString(),
        ];
    }
}
