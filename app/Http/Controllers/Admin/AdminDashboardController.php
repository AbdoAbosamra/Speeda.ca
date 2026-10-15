<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\ErrorHelper;
use App\Models\Visitor;
use App\Models\Review;
use App\Models\Post;
use App\Models\ServiceProvider;
use App\Models\AdminNotification;
use App\Models\User;
use App\Services\VisitorTrackingService;
use App\Support\AdminAnalyticsExclusion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    protected VisitorTrackingService $visitorService;

    /**
     * Create a new controller instance.
     */
    public function __construct(VisitorTrackingService $visitorService)
    {
        $this->middleware('auth');
        $this->middleware('admin');
        $this->visitorService = $visitorService;
    }

    /**
     * Display admin dashboard with visitor statistics
     */
    public function dashboard()
    {
        try {
            $summary = Cache::remember('admin_dashboard_summary', 60, function () {
                // Get visitor statistics
                $visitorStats = $this->visitorService->getStatistics();

                // WhatsApp Analytics Calculations.
                // Every count below goes through whatsappClicksBetween() so admin
                // activity is excluded consistently — the category/provider break-
                // downs further down already did this, the headline numbers did not.
                $now = Carbon::now();

                $dailyWhatsappClicks = $this->whatsappClicksBetween($now->copy()->startOfDay(), $now);
                $yesterdayWhatsappClicks = $this->whatsappClicksBetween(
                    $now->copy()->subDay()->startOfDay(),
                    $now->copy()->subDay()->endOfDay()
                );

                $dailyWhatsappTrend = $this->percentageChange($dailyWhatsappClicks, $yesterdayWhatsappClicks);

                // Rolling 7-day window vs the previous 7-day window (equal length =
                // a fair comparison; avoids the "partial current week vs full last
                // week" distortion that always reads negative early in the period).
                $weeklyWhatsappClicks = $this->whatsappClicksBetween($now->copy()->subDays(7), $now);
                $lastWeekWhatsappClicks = $this->whatsappClicksBetween($now->copy()->subDays(14), $now->copy()->subDays(7));
                $weeklyWhatsappTrend = $this->percentageChange($weeklyWhatsappClicks, $lastWeekWhatsappClicks);

                // Rolling 30-day window vs the previous 30-day window.
                $monthlyWhatsappClicks = $this->whatsappClicksBetween($now->copy()->subDays(30), $now);
                $lastMonthWhatsappClicks = $this->whatsappClicksBetween($now->copy()->subDays(60), $now->copy()->subDays(30));
                $monthlyWhatsappTrend = $this->percentageChange($monthlyWhatsappClicks, $lastMonthWhatsappClicks);

                $totalWhatsappClicks = $this->whatsappClicksBetween(null, null);

                $mostClickedCategoryData = DB::table('analytics')
                    ->join('service_providers', 'analytics.provider_id', '=', 'service_providers.id')
                    ->join('categories', 'service_providers.category_id', '=', 'categories.id')
                    ->where('analytics.action_type', 'click_whatsapp')
                    ->tap(fn($q) => AdminAnalyticsExclusion::apply($q, 'analytics.user_id'))
                    ->select('categories.name_en as name', DB::raw('count(analytics.id) as total_clicks'))
                    ->groupBy('categories.id', 'categories.name_en')
                    ->orderByDesc('total_clicks')
                    ->first();

                $mostClickedCategory = null;
                if ($mostClickedCategoryData) {
                    $mostClickedCategory = [
                        'name' => $mostClickedCategoryData->name ?: 'Unknown',
                        'clicks' => $mostClickedCategoryData->total_clicks,
                        'percentage' => $totalWhatsappClicks > 0
                            ? round(($mostClickedCategoryData->total_clicks / $totalWhatsappClicks) * 100, 1)
                            : 0
                    ];
                }

                // NOTE: the "Top Providers" table on the dashboard is rendered from
                // $dashboard['top_providers'] (AdminDashboardService, cached). The
                // duplicate raw-SQL ranking that used to live here was never read by
                // the view and has been removed.

                // Only the keys the dashboard view actually reads. The remaining
                // counters (active/total categories & locations, total users,
                // active notifications, new users today, 7/30/365-day visitors) were
                // computed on every page load and never rendered.
                $stats = [
                    'liveVisitors' => $visitorStats['live_visitors'] ?? 0,
                    'visitorsToday' => $this->getVisitorsToday(),
                    'visitorsThisMonth' => $this->getVisitorsThisMonth(),
                    'totalVisitors' => $visitorStats['total_visitors'] ?? 0,
                    'totalProviders' => ServiceProvider::count(),
                    'totalClients' => User::where('role', 'client')->count(),
                    'totalBlogs' => Post::count(),
                    'notificationsSent' => AdminNotification::count(),
                    'pendingReviews' => Review::where('is_active', false)->whereNull('admin_approved_at')->count(),
                    'totalReviews' => Review::count(),
                ];

                return compact(
                    'stats',
                    'dailyWhatsappClicks',
                    'dailyWhatsappTrend',
                    'weeklyWhatsappClicks',
                    'weeklyWhatsappTrend',
                    'monthlyWhatsappClicks',
                    'monthlyWhatsappTrend',
                    'mostClickedCategory'
                );
            });

            $dashboard = app(\App\Services\Admin\AdminDashboardService::class)->build();

            return view('admin.dashboard', array_merge($summary, ['dashboard' => $dashboard]));
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            Log::error('Admin dashboard error: ' . $e->getMessage());
            ErrorHelper::flashNotification($error['message'], $error['type']);

            // Return view with empty stats to avoid redirect loop
            $stats = [
                'liveVisitors' => 0,
                'visitorsToday' => 0,
                'visitorsThisMonth' => 0,
                'totalVisitors' => 0,
                'totalProviders' => 0,
                'totalClients' => 0,
                'totalBlogs' => 0,
                'notificationsSent' => 0,
                'pendingReviews' => 0,
                'totalReviews' => 0,
            ];
            $dashboard = [
                'action_center' => ['items' => [], 'total' => 0],
                'kpis' => [], 'funnel' => [], 'visitor_trend' => ['labels' => [], 'values' => []],
                'profile_health' => [], 'top_providers' => [], 'top_categories' => [],
                'recent_signups' => [], 'recent_reviews' => [], 'recent_admin_actions' => [],
            ];
            return view('admin.dashboard', compact('stats', 'dashboard'));
        }
    }

    /**
     * Count WhatsApp clicks in a window, excluding admin activity.
     * Pass nulls for an all-time count.
     */
    private function whatsappClicksBetween(?Carbon $from, ?Carbon $to): int
    {
        $query = DB::table('analytics')->where('action_type', 'click_whatsapp');

        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        AdminAnalyticsExclusion::apply($query);

        return (int) $query->count();
    }

    /**
     * Percentage change between two equal-length windows.
     */
    private function percentageChange(int $current, int $previous): float
    {
        if ($previous > 0) {
            return round((($current - $previous) / $previous) * 100, 1);
        }

        return $current > 0 ? 100.0 : 0.0;
    }

    /**
     * Get visitors for today
     */
    private function getVisitorsToday(): int
    {
        $canadaNow = Carbon::now(config('app.timezone', 'America/Toronto'));

        return (int) Visitor::whereBetween('visited_at', [
                $canadaNow->copy()->startOfDay(),
                $canadaNow->copy()->endOfDay(),
            ])
            ->where(function ($q) {
                $q->whereNull('user_id')
                  ->orWhereDoesntHave('user', fn ($q) => $q->where('role', 'admin'));
            })
            ->selectRaw($this->uniqueVisitorExpression() . ' as aggregate')
            ->value('aggregate');
    }

    /**
     * Get visitors for the current Canadian calendar month.
     */
    private function getVisitorsThisMonth(): int
    {
        $canadaNow = Carbon::now(config('app.timezone', 'America/Toronto'));

        return (int) Visitor::whereBetween('visited_at', [
                $canadaNow->copy()->startOfMonth(),
                $canadaNow->copy()->endOfMonth(),
            ])
            ->where(function ($q) {
                $q->whereNull('user_id')
                  ->orWhereDoesntHave('user', fn ($q) => $q->where('role', 'admin'));
            })
            ->selectRaw($this->uniqueVisitorExpression() . ' as aggregate')
            ->value('aggregate');
    }

    /**
     * Driver-portable "distinct (ip_hash, user_agent_hash)" count expression.
     * MySQL supports multi-column COUNT(DISTINCT ...); SQLite does not.
     */
    private function uniqueVisitorExpression(): string
    {
        return DB::connection()->getDriverName() === 'mysql'
            ? 'COUNT(DISTINCT ip_hash, user_agent_hash)'
            : "COUNT(DISTINCT ip_hash || '|' || user_agent_hash)";
    }
}
