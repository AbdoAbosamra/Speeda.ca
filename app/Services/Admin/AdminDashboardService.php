<?php

namespace App\Services\Admin;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Review;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Support\AdminAnalyticsExclusion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Builds the admin operations dashboard.
 *
 * Design goals:
 *  - Every analytics number is admin-excluded in a NULL-safe way (guests kept)
 *    and uses driver-portable DISTINCT counting.
 *  - Trends compare equal-length rolling windows (30d vs the previous 30d).
 *  - Heavy aggregations are cached; "needs attention" counters are short-cached
 *    so the moderation queue stays fresh.
 */
class AdminDashboardService
{
    private const HEAVY_TTL = 300;   // 5 min — charts, top lists, funnel
    private const ACTION_TTL = 60;   // 1 min — moderation queue counters

    public function __construct(
        private WhatsappAnalyticsService $whatsapp
    ) {
    }

    /**
     * Everything the dashboard view needs, in one structured payload.
     */
    public function build(): array
    {
        return [
            'action_center' => $this->actionCenter(),
            'kpis' => $this->kpis(),
            'funnel' => $this->leadFunnel(),
            'visitor_trend' => $this->visitorTrend(14),
            'profile_health' => $this->profileHealth(),
            'top_providers' => $this->whatsapp->topProviders($this->noFilters(), 6),
            'top_categories' => $this->whatsapp->categoryPerformance($this->noFilters(), 6),
            'recent_signups' => $this->recentSignups(),
            'recent_reviews' => $this->recentReviews(),
            'recent_admin_actions' => $this->recentAdminActions(),
        ];
    }

    /* ===================== ACTION CENTER ===================== */

    /**
     * Items that need an admin decision now, with deep links.
     */
    public function actionCenter(): array
    {
        return Cache::remember('admin_dash_action_center', self::ACTION_TTL, function () {
            $pendingReviews = Review::where('is_active', false)->whereNull('admin_approved_at')->count();
            $pendingComments = Comment::pending()->count();
            $flaggedComments = Comment::flagged()->count();

            $incompleteProfiles = ServiceProvider::where('profile_completion_percent', '<', 100)->count();

            $items = [
                [
                    'key' => 'pending_reviews', 'label' => 'Reviews awaiting approval',
                    'count' => $pendingReviews, 'route' => route('admin.reviews', ['status' => 'pending']),
                    'icon' => 'fa-star', 'tone' => 'amber',
                ],
                [
                    'key' => 'pending_comments', 'label' => 'Comments awaiting approval',
                    'count' => $pendingComments, 'route' => route('admin.comments', ['status' => 'pending']),
                    'icon' => 'fa-comments', 'tone' => 'sky',
                ],
                [
                    'key' => 'flagged_comments', 'label' => 'Flagged comments',
                    'count' => $flaggedComments, 'route' => route('admin.comments', ['status' => 'flagged']),
                    'icon' => 'fa-flag', 'tone' => 'rose',
                ],
                [
                    'key' => 'incomplete_profiles', 'label' => 'Incomplete provider profiles',
                    'count' => $incompleteProfiles, 'route' => route('admin.provider_activity_monitor.index', ['completion_status' => 'partial']),
                    'icon' => 'fa-id-card', 'tone' => 'slate',
                ],
            ];

            return [
                'items' => $items,
                'total' => array_sum(array_column($items, 'count')),
            ];
        });
    }

    /* ===================== KPIs (with real trends) ===================== */

    public function kpis(): array
    {
        return Cache::remember('admin_dash_kpis', self::HEAVY_TTL, function () {
            $now = Carbon::now();
            $d30 = $now->copy()->subDays(30);
            $d60 = $now->copy()->subDays(60);

            // Providers
            $providersNew30 = DB::table('service_providers')->where('created_at', '>=', $d30)->count();
            $providersPrev30 = DB::table('service_providers')->whereBetween('created_at', [$d60, $d30])->count();

            // Clients
            $clientsNew30 = User::where('role', 'client')->where('created_at', '>=', $d30)->count();
            $clientsPrev30 = User::where('role', 'client')->whereBetween('created_at', [$d60, $d30])->count();

            // WhatsApp leads (NULL-safe)
            $leads30 = $this->clicks($d30, $now);
            $leadsPrev30 = $this->clicks($d60, $d30);

            $views30 = $this->views($d30, $now);

            return [
                'providers' => [
                    'total' => DB::table('service_providers')->count(),
                    'new_30' => $providersNew30,
                    'trend' => $this->growth($providersNew30, $providersPrev30),
                ],
                'clients' => [
                    'total' => User::where('role', 'client')->count(),
                    'new_30' => $clientsNew30,
                    'trend' => $this->growth($clientsNew30, $clientsPrev30),
                ],
                'leads' => [
                    'total' => $leads30,
                    'trend' => $this->growth($leads30, $leadsPrev30),
                ],
                'conversion' => [
                    'rate' => $this->rate($leads30, $views30),
                    'views' => $views30,
                ],
                'reviews_total' => Review::count(),
                'blogs_total' => Post::count(),
            ];
        });
    }

    /* ===================== LEAD FUNNEL (30d) ===================== */

    public function leadFunnel(): array
    {
        return Cache::remember('admin_dash_funnel', self::HEAVY_TTL, function () {
            $now = Carbon::now();
            $d30 = $now->copy()->subDays(30);

            $views = $this->views($d30, $now);
            $clicks = $this->clicks($d30, $now);

            $sessQ = DB::table('analytics')
                ->where('action_type', 'click_whatsapp')
                ->where('created_at', '>=', $d30)
                ->whereNotNull('session_hash');
            AdminAnalyticsExclusion::apply($sessQ);
            $uniqueLeadSessions = (clone $sessQ)->distinct()->count('session_hash');

            return [
                'views' => $views,
                'unique_lead_sessions' => $uniqueLeadSessions,
                'clicks' => $clicks,
                'conversion_rate' => $this->rate($clicks, $views),
            ];
        });
    }

    /* ===================== VISITOR TREND ===================== */

    /**
     * Unique visitors per day for the last N days (zero-filled, admin-excluded).
     */
    public function visitorTrend(int $days = 14): array
    {
        return Cache::remember("admin_dash_visitor_trend_{$days}", self::HEAVY_TTL, function () use ($days) {
            $start = Carbon::today()->subDays($days - 1)->startOfDay();

            $q = DB::table('visitors')
                ->where('visited_at', '>=', $start)
                ->where(function ($q) {
                    $q->whereNull('user_id')
                      ->orWhereNotIn('user_id', AdminAnalyticsExclusion::adminIds() ?: [0]);
                });

            $rows = $q->selectRaw('DATE(visited_at) as d, ' . $this->uniqueVisitorExpr() . ' as c')
                ->groupByRaw('DATE(visited_at)')
                ->pluck('c', 'd');

            $labels = [];
            $values = [];
            for ($i = 0; $i < $days; $i++) {
                $date = $start->copy()->addDays($i);
                $labels[] = $date->format('M d');
                $values[] = (int) ($rows[$date->toDateString()] ?? 0);
            }

            return ['labels' => $labels, 'values' => $values];
        });
    }

    /* ===================== PROFILE HEALTH ===================== */

    public function profileHealth(): array
    {
        return Cache::remember('admin_dash_profile_health', self::HEAVY_TTL, function () {
            $total = DB::table('service_providers')->count();
            $complete = DB::table('service_providers')->where('profile_completion_percent', '>=', 100)->count();
            $partial = DB::table('service_providers')->whereBetween('profile_completion_percent', [1, 99])->count();
            $empty = DB::table('service_providers')->where('profile_completion_percent', '<=', 0)->count();

            return [
                'total' => $total,
                'complete' => $complete,
                'partial' => $partial,
                'incomplete' => $empty,
                'complete_pct' => $this->rate($complete, $total),
            ];
        });
    }

    /* ===================== RECENT ACTIVITY FEEDS ===================== */

    public function recentSignups(int $limit = 6): array
    {
        return User::query()
            ->select('id', 'name', 'email', 'role', 'created_at')
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->all();
    }

    public function recentReviews(int $limit = 6): array
    {
        return Review::query()
            ->with(['serviceProvider:id,company_name', 'client:id,name'])
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->all();
    }

    public function recentAdminActions(int $limit = 6): array
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('admin_logs')) {
            return [];
        }

        return DB::table('admin_logs')
            ->leftJoin('users', 'admin_logs.admin_id', '=', 'users.id')
            ->select('admin_logs.action', 'admin_logs.model_type', 'admin_logs.model_name', 'admin_logs.created_at', 'users.name as admin_name')
            ->orderByDesc('admin_logs.created_at')
            ->limit($limit)
            ->get()
            ->all();
    }

    /* ===================== INTERNALS ===================== */

    private function clicks(Carbon $from, Carbon $to): int
    {
        return $this->actionCount('click_whatsapp', $from, $to);
    }

    private function views(Carbon $from, Carbon $to): int
    {
        return $this->actionCount('view', $from, $to);
    }

    private function actionCount(string $type, Carbon $from, Carbon $to): int
    {
        $q = DB::table('analytics')
            ->where('action_type', $type)
            ->whereBetween('created_at', [$from, $to]);
        AdminAnalyticsExclusion::apply($q);

        return $q->count();
    }

    private function uniqueVisitorExpr(): string
    {
        return DB::connection()->getDriverName() === 'mysql'
            ? 'COUNT(DISTINCT ip_hash, user_agent_hash)'
            : "COUNT(DISTINCT ip_hash || '|' || user_agent_hash)";
    }

    private function rate(int $numerator, int $denominator): float
    {
        return $denominator > 0 ? round(($numerator / $denominator) * 100, 1) : 0.0;
    }

    private function growth(int $current, int $previous): float
    {
        if ($previous > 0) {
            return round((($current - $previous) / $previous) * 100, 1);
        }

        return $current > 0 ? 100.0 : 0.0;
    }

    private function noFilters(): array
    {
        return [
            'date_from' => null, 'date_to' => null, 'provider_id' => null,
            'category_id' => null, 'location_id' => null, 'source_page' => null,
            'locale' => null, 'device_type' => null,
        ];
    }
}
