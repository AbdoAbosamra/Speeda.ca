<?php

namespace App\Services;

use App\Models\Visitor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;

class VisitorTrackingService
{
    /**
     * Cache duration for visitor stats (in minutes).
     */
    protected const CACHE_DURATION = 5;

    /**
     * Get visitor statistics for various time periods.
     */
    public function getStatistics(): array
    {
        return Cache::remember('visitor_stats', now()->addMinutes(self::CACHE_DURATION), function () {
            return [
                'total_visitors' => $this->getTotalVisitors(),
                'last_7_days' => $this->getVisitorsLast7Days(),
                'last_30_days' => $this->getVisitorsLast30Days(),
                'last_12_months' => $this->getVisitorsLast12Months(),
                'live_visitors' => $this->getLiveVisitors(),
            ];
        });
    }

    /**
     * Get total visitors count (all time, unique).
     */
    public function getTotalVisitors(): int
    {
        return Visitor::selectRaw('DISTINCT ip_hash, user_agent_hash')
            ->count();
    }

    /**
     * Get unique visitors from the last 7 days.
     */
    public function getVisitorsLast7Days(): int
    {
        return Visitor::last7Days()
            ->selectRaw('DISTINCT ip_hash, user_agent_hash')
            ->count();
    }

    /**
     * Get unique visitors from the last 30 days.
     */
    public function getVisitorsLast30Days(): int
    {
        return Visitor::last30Days()
            ->selectRaw('DISTINCT ip_hash, user_agent_hash')
            ->count();
    }

    /**
     * Get unique visitors from the last 12 months.
     */
    public function getVisitorsLast12Months(): int
    {
        return Visitor::last12Months()
            ->selectRaw('DISTINCT ip_hash, user_agent_hash')
            ->count();
    }

    /**
     * Get live visitors (last 15 minutes).
     */
    public function getLiveVisitors(): int
    {
        return Cache::remember('live_visitors_count', now()->addMinutes(1), function () {
            return Visitor::live()
                ->selectRaw('DISTINCT ip_hash, user_agent_hash')
                ->count();
        });
    }

    /**
     * Get detailed visitor analytics for a specific time period.
     */
    public function getDetailedAnalytics(string $period = 'last_30_days'): array
    {
        $query = Visitor::query();

        switch ($period) {
            case 'last_7_days':
                $query = $query->last7Days();
                break;
            case 'last_12_months':
                $query = $query->last12Months();
                break;
            case 'last_30_days':
            default:
                $query = $query->last30Days();
                break;
        }

        // Get visitors grouped by date
        $visitorsByDate = $query
            ->selectRaw('DATE(visited_at) as date, COUNT(DISTINCT ip_hash, user_agent_hash) as count')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        // Get top pages visited
        $topPages = $query
            ->selectRaw('path, COUNT(*) as visits, COUNT(DISTINCT ip_hash, user_agent_hash) as unique_visitors')
            ->groupBy('path')
            ->orderBy('visits', 'desc')
            ->limit(10)
            ->get();

        return [
            'visitors_by_date' => $visitorsByDate,
            'top_pages' => $topPages,
            'total_visits' => $query->count(),
            'unique_visitors' => $query->selectRaw('DISTINCT ip_hash, user_agent_hash')->count(),
        ];
    }

    /**
     * Clear visitor stats cache.
     */
    public function clearCache(): void
    {
        Cache::forget('visitor_stats');
        Cache::forget('live_visitors_count');
    }
}
