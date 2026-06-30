<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Post;
use App\Models\ServiceProvider;
use App\Models\Visitor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class VisitorTrackingService
{
    /**
     * Cache duration for visitor stats (in minutes).
     */
    protected const CACHE_DURATION = 5;

    /**
     * Apply admin exclusion to any visitor query.
     * Admins are already excluded at recording level (TrackVisitor middleware),
     * this provides a safety net for any existing admin entries in the DB.
     */
    private function excludeAdmins($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('user_id')
              ->orWhereDoesntHave('user', function ($q) {
                  $q->where('role', 'admin');
              });
        });
    }

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
        return $this->countUnique($this->excludeAdmins(Visitor::query()));
    }

    /**
     * Count unique visitors (distinct ip_hash + user_agent_hash).
     *
     * NOTE: Laravel's ->count() discards a custom ->selectRaw('DISTINCT ...'),
     * so it would silently count ALL rows (page-visits) instead of unique
     * visitors. We must aggregate with COUNT(DISTINCT ...) explicitly.
     */
    private function countUnique($query): int
    {
        return (int) $query
            ->selectRaw($this->uniqueVisitorExpression() . ' as aggregate')
            ->value('aggregate');
    }

    /**
     * Driver-portable "distinct (ip_hash, user_agent_hash)" count expression.
     * MySQL supports multi-column COUNT(DISTINCT ...); SQLite (tests) does not,
     * so fall back to concatenation there.
     */
    private function uniqueVisitorExpression(): string
    {
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();

        return $driver === 'mysql'
            ? 'COUNT(DISTINCT ip_hash, user_agent_hash)'
            : "COUNT(DISTINCT ip_hash || '|' || user_agent_hash)";
    }

    /**
     * Get unique visitors from the last 7 days.
     */
    public function getVisitorsLast7Days(): int
    {
        return $this->countUnique($this->excludeAdmins(Visitor::last7Days()));
    }

    /**
     * Get unique visitors from the last 30 days.
     */
    public function getVisitorsLast30Days(): int
    {
        return $this->countUnique($this->excludeAdmins(Visitor::last30Days()));
    }

    /**
     * Get unique visitors from the last 12 months.
     */
    public function getVisitorsLast12Months(): int
    {
        return $this->countUnique($this->excludeAdmins(Visitor::last12Months()));
    }

    /**
     * Get live visitors (last 15 minutes).
     */
    public function getLiveVisitors(): int
    {
        return Cache::remember('live_visitors_count', now()->addMinutes(1), function () {
            return $this->countUnique($this->excludeAdmins(Visitor::live()));
        });
    }

    /**
     * Get detailed visitor analytics for a specific time period.
     */
    public function getDetailedAnalytics(string $period = 'last_30_days'): array
    {
        $query = $this->excludeAdmins(Visitor::query());

        switch ($period) {
            case 'last_7_days':
                $query->last7Days();
                break;
            case 'last_12_months':
                $query->last12Months();
                break;
            case 'last_30_days':
            default:
                $query->last30Days();
                break;
        }

        $baseQuery = $query;

        // Get visitors grouped by date
        $visitorsByDate = (clone $baseQuery)
            ->selectRaw('DATE(visited_at) as date, COUNT(DISTINCT ip_hash, user_agent_hash) as count')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        // Get top pages visited
        $topPages = (clone $baseQuery)
            ->selectRaw('path, COUNT(*) as visits, COUNT(DISTINCT ip_hash, user_agent_hash) as unique_visitors')
            ->groupBy('path')
            ->orderBy('visits', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($page) {
                $page->page_name = $this->getPageDisplayName($page->path);
                return $page;
            });

        return [
            'visitors_by_date' => $visitorsByDate,
            'top_pages' => $topPages,
            'total_visits' => (clone $baseQuery)->count(),
            'unique_visitors' => $this->countUnique(clone $baseQuery),
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

    /**
     * Convert tracked URL paths into admin-friendly page names.
     */
    public function getPageDisplayName(?string $path): string
    {
        $path = trim((string) $path);
        $path = $path === '' ? '/' : trim($path, '/');
        $path = $path === '' ? '/' : $path;

        return match (true) {
            $path === '/' => 'Homepage',
            $path === 'service-providers' => 'Service Providers Directory',
            $path === 'categories' => 'Categories Directory',
            $path === 'blogs' => 'Blog',
            $path === 'register' => 'Sign Up / Login Page',
            $path === 'login' => 'Login Page',
            $path === 'csrf-token' => 'Form Security Check',
            $path === 'admin/visitors/live-count' => 'Admin Live Visitors Counter',
            Str::startsWith($path, 'service-providers/') => $this->serviceProviderPageName($path),
            Str::startsWith($path, 'blogs/') => $this->blogPostPageName($path),
            Str::startsWith($path, 'categories/') => $this->categoryPageName($path),
            default => $this->humanizePath($path),
        };
    }

    private function serviceProviderPageName(string $path): string
    {
        $id = (int) Str::after($path, 'service-providers/');
        $provider = $id > 0
            ? ServiceProvider::with('user')->find($id)
            : null;

        if ($provider) {
            $name = $provider->localized_company_name ?: $provider->user?->name;

            if ($name) {
                return 'Provider Profile: ' . $name;
            }
        }

        return 'Service Provider Profile';
    }

    private function blogPostPageName(string $path): string
    {
        $slug = trim(Str::after($path, 'blogs/'), '/');
        $post = $slug !== ''
            ? Post::where('slug', $slug)->first()
            : null;

        return $post
            ? 'Blog Post: ' . $post->localized_title
            : 'Blog Post: ' . Str::title(str_replace('-', ' ', $slug));
    }

    private function categoryPageName(string $path): string
    {
        $slug = trim(Str::after($path, 'categories/'), '/');
        $category = $slug !== ''
            ? Category::where('slug', $slug)->first()
            : null;

        return $category
            ? 'Category: ' . $category->localized_name
            : 'Category: ' . Str::title(str_replace('-', ' ', $slug));
    }

    private function humanizePath(string $path): string
    {
        return Str::title(str_replace(['-', '_', '/'], [' ', ' ', ' / '], $path));
    }
}
