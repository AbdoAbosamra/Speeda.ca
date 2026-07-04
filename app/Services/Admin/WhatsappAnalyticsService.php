<?php

namespace App\Services\Admin;

use App\Support\AdminAnalyticsExclusion;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * WhatsApp Click Intelligence aggregation service.
 *
 * Every query here:
 *   - excludes admin activity in a NULL-safe way (guests are kept),
 *   - applies the admin's dimension filters (provider/category/location/
 *     mobile-or-whatsapp/device),
 *   - handles division-by-zero in conversion rates (returns 0.0),
 *   - reads only privacy-safe columns (no IP, no raw User-Agent).
 *
 * Summary cards use fixed time windows (today / 7d / 30d / all-time); charts and
 * tables use the admin-selected date range.
 */
class WhatsappAnalyticsService
{
    private const CLICK = 'click_whatsapp';
    private const VIEW = 'view';

    private const SUMMARY_TTL = 300;   // 5 minutes
    private const BREAKDOWN_TTL = 600; // 10 minutes

    /* =====================================================================
     |  SUMMARY CARDS  (fixed windows, dimension filters applied)
     * ===================================================================== */

    public function summary(array $filters): array
    {
        return Cache::remember(
            'wa_summary_' . $this->filtersKey($filters, false),
            self::SUMMARY_TTL,
            function () use ($filters) {
                $now = Carbon::now();

                $totalAllTime = $this->clickQuery($filters, false)->count();
                $today = $this->clickQuery($filters, false)
                    ->whereDate('created_at', $now->toDateString())->count();
                $last7 = $this->clickQuery($filters, false)
                    ->where('created_at', '>=', $now->copy()->subDays(7))->count();
                $last30 = $this->clickQuery($filters, false)
                    ->where('created_at', '>=', $now->copy()->subDays(30))->count();
                $prev30 = $this->clickQuery($filters, false)
                    ->whereBetween('created_at', [$now->copy()->subDays(60), $now->copy()->subDays(30)])->count();

                $views30 = $this->viewQuery($filters, false)
                    ->where('created_at', '>=', $now->copy()->subDays(30))->count();

                // Unique anonymous sessions that produced a click in last 30 days.
                $uniqueSessions = $this->clickQuery($filters, false)
                    ->where('created_at', '>=', $now->copy()->subDays(30))
                    ->whereNotNull('session_hash')
                    ->distinct()
                    ->count('session_hash');

                $providersWithClicks = $this->clickQuery($filters, false)
                    ->where('created_at', '>=', $now->copy()->subDays(30))
                    ->distinct()
                    ->count('provider_id');

                return [
                    'total_all_time' => $totalAllTime,
                    'today' => $today,
                    'last_7_days' => $last7,
                    'last_30_days' => $last30,
                    'prev_30_days' => $prev30,
                    'growth_pct' => $this->growth($last30, $prev30),
                    'unique_sessions_30' => $uniqueSessions,
                    'avg_clicks_per_provider_30' => $providersWithClicks > 0
                        ? round($last30 / $providersWithClicks, 1) : 0.0,
                    'overall_ctr_30' => $this->rate($last30, $views30),
                    'views_30' => $views30,
                ];
            }
        );
    }

    /* =====================================================================
     |  CHARTS  (selected date range)
     * ===================================================================== */

    /**
     * Clicks per day across the selected range (zero-filled).
     */
    public function clicksOverTime(array $filters): array
    {
        [$from, $to] = $this->dateRange($filters);

        $rows = $this->clickQuery($filters)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupByRaw('DATE(created_at)')
            ->pluck('c', 'd');

        $labels = [];
        $values = [];
        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();
        $guard = 0;

        while ($cursor->lte($end) && $guard < 366) {
            $key = $cursor->toDateString();
            $labels[] = $cursor->format('M d');
            $values[] = (int) ($rows[$key] ?? 0);
            $cursor->addDay();
            $guard++;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Clicks by hour of day (0–23).
     */
    public function clicksByHour(array $filters): array
    {
        $rows = $this->clickQuery($filters)
            ->selectRaw('HOUR(created_at) as h, COUNT(*) as c')
            ->groupByRaw('HOUR(created_at)')
            ->pluck('c', 'h');

        $labels = [];
        $values = [];
        for ($h = 0; $h < 24; $h++) {
            $labels[] = sprintf('%02d:00', $h);
            $values[] = (int) ($rows[$h] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Clicks by day of week (Mon–Sun). MySQL DAYOFWEEK: 1=Sun..7=Sat.
     */
    public function clicksByDayOfWeek(array $filters): array
    {
        $rows = $this->clickQuery($filters)
            ->selectRaw('DAYOFWEEK(created_at) as dow, COUNT(*) as c')
            ->groupByRaw('DAYOFWEEK(created_at)')
            ->pluck('c', 'dow');

        $order = [2 => 'Mon', 3 => 'Tue', 4 => 'Wed', 5 => 'Thu', 6 => 'Fri', 7 => 'Sat', 1 => 'Sun'];
        $labels = [];
        $values = [];
        foreach ($order as $mysqlDow => $label) {
            $labels[] = $label;
            $values[] = (int) ($rows[$mysqlDow] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Clicks grouped by a simple column (source_page / device_type / locale).
     */
    public function clicksByDimension(array $filters, string $column): array
    {
        $allowed = ['source_page', 'device_type', 'locale'];
        if (! in_array($column, $allowed, true)) {
            return [];
        }

        return $this->clickQuery($filters)
            ->selectRaw("COALESCE($column, 'unknown') as label, COUNT(*) as c")
            ->groupByRaw("COALESCE($column, 'unknown')")
            ->orderByDesc('c')
            ->get()
            ->map(fn ($r) => ['label' => $r->label, 'value' => (int) $r->c])
            ->all();
    }

    /* =====================================================================
     |  BREAKDOWN TABLES with conversion (selected date range)
     * ===================================================================== */

    public function categoryPerformance(array $filters, int $limit = 15): array
    {
        return Cache::remember(
            'wa_cat_' . $this->filtersKey($filters),
            self::BREAKDOWN_TTL,
            fn () => $this->dimensionPerformance($filters, 'analytics.category_id', 'categories', 'id', 'name_en', 'category', $limit)
        );
    }

    public function locationPerformance(array $filters, int $limit = 15): array
    {
        return Cache::remember(
            'wa_loc_' . $this->filtersKey($filters),
            self::BREAKDOWN_TTL,
            fn () => $this->dimensionPerformance($filters, 'analytics.location_id', 'locations', 'id', 'city', 'location', $limit)
        );
    }

    /**
     * Generic "views + clicks + conversion" breakdown for a dimension column.
     */
    private function dimensionPerformance(array $filters, string $dimColumn, string $table, string $joinKey, string $nameColumn, string $labelKey, int $limit): array
    {
        [$from, $to] = $this->dateRange($filters);

        $q = DB::table('analytics')
            ->leftJoin($table, "analytics.{$this->bare($dimColumn)}", '=', "{$table}.{$joinKey}")
            ->whereBetween('analytics.created_at', [$from, $to])
            ->whereNotNull($dimColumn);

        AdminAnalyticsExclusion::apply($q, 'analytics.user_id');
        $this->applyDimensionFilters($q, $filters, 'analytics.');

        $rows = $q->selectRaw("
                {$dimColumn} as dim_id,
                COALESCE({$table}.{$nameColumn}, 'Unknown') as name,
                SUM(CASE WHEN analytics.action_type = ? THEN 1 ELSE 0 END) as views,
                SUM(CASE WHEN analytics.action_type = ? THEN 1 ELSE 0 END) as clicks
            ", [self::VIEW, self::CLICK])
            ->groupBy('dim_id', 'name')
            ->havingRaw('clicks > 0 OR views > 0')
            ->orderByDesc('clicks')
            ->limit($limit)
            ->get();

        return $rows->map(fn ($r) => [
            $labelKey => $r->name,
            'views' => (int) $r->views,
            'clicks' => (int) $r->clicks,
            'conversion_rate' => $this->rate((int) $r->clicks, (int) $r->views),
        ])->all();
    }

    /**
     * Top providers by WhatsApp clicks (with conversion + last click).
     */
    public function topProviders(array $filters, int $limit = 15): array
    {
        [$from, $to] = $this->dateRange($filters);

        $q = DB::table('analytics')
            ->join('service_providers as sp', 'analytics.provider_id', '=', 'sp.id')
            ->leftJoin('categories as c', 'sp.category_id', '=', 'c.id')
            ->leftJoin('locations as l', 'sp.location_id', '=', 'l.id')
            ->whereBetween('analytics.created_at', [$from, $to]);

        AdminAnalyticsExclusion::apply($q, 'analytics.user_id');
        $this->applyDimensionFilters($q, $filters, 'analytics.');

        $rows = $q->selectRaw("
                sp.id,
                sp.company_name,
                COALESCE(c.name_en, '—') as category,
                COALESCE(l.city, '—') as location,
                SUM(CASE WHEN analytics.action_type = ? THEN 1 ELSE 0 END) as views,
                SUM(CASE WHEN analytics.action_type = ? THEN 1 ELSE 0 END) as clicks,
                MAX(CASE WHEN analytics.action_type = ? THEN analytics.created_at END) as last_click_at
            ", [self::VIEW, self::CLICK, self::CLICK])
            ->groupBy('sp.id', 'sp.company_name', 'category', 'location')
            ->havingRaw('clicks > 0')
            ->orderByDesc('clicks')
            ->limit($limit)
            ->get();

        return $rows->map(fn ($r) => [
            'id' => $r->id,
            'company_name' => $r->company_name,
            'category' => $r->category,
            'location' => $r->location,
            'views' => (int) $r->views,
            'clicks' => (int) $r->clicks,
            'conversion_rate' => $this->rate((int) $r->clicks, (int) $r->views),
            'last_click_at' => $r->last_click_at,
        ])->all();
    }

    /**
     * Providers with healthy visibility (views >= threshold) but weak WhatsApp
     * conversion — the actionable "needs attention" list.
     */
    public function lowConversionProviders(array $filters, int $minViews = 20, int $limit = 15): array
    {
        [$from, $to] = $this->dateRange($filters);

        $q = DB::table('analytics')
            ->join('service_providers as sp', 'analytics.provider_id', '=', 'sp.id')
            ->leftJoin('categories as c', 'sp.category_id', '=', 'c.id')
            ->leftJoin('locations as l', 'sp.location_id', '=', 'l.id')
            ->whereBetween('analytics.created_at', [$from, $to]);

        AdminAnalyticsExclusion::apply($q, 'analytics.user_id');
        $this->applyDimensionFilters($q, $filters, 'analytics.');

        $rows = $q->selectRaw("
                sp.id,
                sp.company_name,
                COALESCE(c.name_en, '—') as category,
                COALESCE(l.city, '—') as location,
                SUM(CASE WHEN analytics.action_type = ? THEN 1 ELSE 0 END) as views,
                SUM(CASE WHEN analytics.action_type = ? THEN 1 ELSE 0 END) as clicks
            ", [self::VIEW, self::CLICK])
            ->groupBy('sp.id', 'sp.company_name', 'category', 'location')
            ->havingRaw('views >= ?', [$minViews])
            ->orderByRaw('(clicks / views) ASC, views DESC')
            ->limit($limit)
            ->get();

        return $rows->map(fn ($r) => [
            'id' => $r->id,
            'company_name' => $r->company_name,
            'category' => $r->category,
            'location' => $r->location,
            'views' => (int) $r->views,
            'clicks' => (int) $r->clicks,
            'conversion_rate' => $this->rate((int) $r->clicks, (int) $r->views),
        ])->all();
    }

    /**
     * Recent WhatsApp click activity (paginated, not cached).
     */
    public function recentActivity(array $filters, int $perPage = 25): LengthAwarePaginator
    {
        [$from, $to] = $this->dateRange($filters);

        $q = DB::table('analytics')
            ->join('service_providers as sp', 'analytics.provider_id', '=', 'sp.id')
            ->where('analytics.action_type', self::CLICK)
            ->whereBetween('analytics.created_at', [$from, $to]);

        AdminAnalyticsExclusion::apply($q, 'analytics.user_id');
        $this->applyDimensionFilters($q, $filters, 'analytics.');

        return $q->selectRaw('
                analytics.created_at,
                analytics.source_page,
                analytics.locale,
                analytics.device_type,
                sp.id as provider_id,
                sp.company_name
            ')
            ->orderByDesc('analytics.created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Privacy-safe quality signal: anonymous sessions that produced an
     * abnormally high number of clicks in the range (possible self-clicking /
     * bot). No IP is used — only the salted session fingerprint.
     */
    public function suspiciousSessions(array $filters, int $threshold = 8, int $limit = 10): array
    {
        [$from, $to] = $this->dateRange($filters);

        $q = DB::table('analytics')
            ->where('action_type', self::CLICK)
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('session_hash');

        AdminAnalyticsExclusion::apply($q);
        $this->applyDimensionFilters($q, $filters);

        return $q->selectRaw('
                session_hash,
                COUNT(*) as clicks,
                COUNT(DISTINCT provider_id) as providers,
                MAX(created_at) as last_seen
            ')
            ->groupBy('session_hash')
            ->havingRaw('clicks >= ?', [$threshold])
            ->orderByDesc('clicks')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'session' => substr($r->session_hash, 0, 10) . '…', // truncated, non-reversible
                'clicks' => (int) $r->clicks,
                'providers' => (int) $r->providers,
                'last_seen' => $r->last_seen,
            ])->all();
    }

    /* =====================================================================
     |  FILTER OPTION LISTS  (for the filter dropdowns)
     * ===================================================================== */

    public function filterOptions(): array
    {
        return Cache::remember('wa_filter_options', self::BREAKDOWN_TTL, function () {
            return [
                'categories' => DB::table('categories')->select('id', 'name_en')->orderBy('name_en')->get(),
                'locations' => DB::table('locations')->select('id', 'city')->orderBy('city')->get(),
                'devices' => DB::table('analytics')->whereNotNull('device_type')->distinct()->pluck('device_type'),
            ];
        });
    }

    /* =====================================================================
     |  INTERNALS
     * ===================================================================== */

    /** Build a click-only query (admin-excluded + dimension filters). */
    private function clickQuery(array $filters, bool $withDateRange = true)
    {
        return $this->actionQuery(self::CLICK, $filters, $withDateRange);
    }

    /** Build a view-only query (admin-excluded + dimension filters). */
    private function viewQuery(array $filters, bool $withDateRange = true)
    {
        return $this->actionQuery(self::VIEW, $filters, $withDateRange);
    }

    private function actionQuery(string $actionType, array $filters, bool $withDateRange)
    {
        $q = DB::table('analytics')->where('action_type', $actionType);
        AdminAnalyticsExclusion::apply($q);
        $this->applyDimensionFilters($q, $filters);

        if ($withDateRange) {
            [$from, $to] = $this->dateRange($filters);
            $q->whereBetween('created_at', [$from, $to]);
        }

        return $q;
    }

    private function applyDimensionFilters($q, array $filters, string $prefix = ''): void
    {
        foreach (['provider_id', 'category_id', 'location_id', 'device_type'] as $field) {
            if (! empty($filters[$field])) {
                $q->where($prefix . $field, $filters[$field]);
            }
        }

        // Single-field search matching either the provider's mobile (phone) or
        // WhatsApp number. Resolved to provider IDs so it applies uniformly to
        // every query, including those that don't join service_providers.
        if (! empty($filters['phone_search'])) {
            $ids = $this->providerIdsMatchingPhone($filters['phone_search']);
            // Empty result set → force a no-match rather than ignoring the filter.
            $q->whereIn($prefix . 'provider_id', $ids ?: [0]);
        }
    }

    /**
     * Provider IDs whose mobile (phone) or WhatsApp number contains the term.
     */
    private function providerIdsMatchingPhone(string $term): array
    {
        $like = '%' . str_replace(['%', '_'], ['\%', '\_'], trim($term)) . '%';

        return DB::table('service_providers')
            ->where(function ($w) use ($like) {
                $w->where('phone', 'like', $like)
                    ->orWhere('whatsapp_number', 'like', $like);
            })
            ->pluck('id')
            ->all();
    }

    /**
     * @return array{0:Carbon,1:Carbon}
     */
    public function dateRange(array $filters): array
    {
        $to = ! empty($filters['date_to'])
            ? Carbon::parse($filters['date_to'])->endOfDay()
            : Carbon::now();

        $from = ! empty($filters['date_from'])
            ? Carbon::parse($filters['date_from'])->startOfDay()
            : Carbon::now()->subDays(29)->startOfDay();

        // Guard against inverted ranges.
        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    /** Safe percentage rate (clicks / views * 100). */
    private function rate(int $numerator, int $denominator): float
    {
        return $denominator > 0 ? round(($numerator / $denominator) * 100, 1) : 0.0;
    }

    /** Period-over-period growth %. */
    private function growth(int $current, int $previous): float
    {
        if ($previous > 0) {
            return round((($current - $previous) / $previous) * 100, 1);
        }

        return $current > 0 ? 100.0 : 0.0;
    }

    /** Strip a table prefix from a column reference (analytics.category_id → category_id). */
    private function bare(string $column): string
    {
        return str_contains($column, '.') ? explode('.', $column)[1] : $column;
    }

    /** Stable cache key for a filter set. */
    private function filtersKey(array $filters, bool $withDate = true): string
    {
        $keys = ['provider_id', 'category_id', 'location_id', 'phone_search', 'device_type'];
        if ($withDate) {
            $keys[] = 'date_from';
            $keys[] = 'date_to';
        }

        $parts = [];
        foreach ($keys as $k) {
            $parts[$k] = $filters[$k] ?? '';
        }

        return md5(json_encode($parts));
    }
}
