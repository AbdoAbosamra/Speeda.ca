<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\VisitorTrackingService;
use Illuminate\Http\Request;

class VisitorAnalyticsController extends Controller
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
     * Display visitor analytics dashboard.
     */
    public function index(Request $request)
    {
        try {
            // Get basic statistics
            $stats = $this->visitorService->getStatistics();

            // Get detailed analytics for a specific period
            $period = $request->input('period', 'last_30_days');
            $analytics = $this->visitorService->getDetailedAnalytics($period);

            return view('admin.visitors.index', compact('stats', 'analytics', 'period'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error displaying visitor analytics', [
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('admin.dashboard')
                ->with('error', 'Unable to load visitor analytics.');
        }
    }

    /**
     * Get live visitor count (for AJAX).
     */
    public function getLiveCount()
    {
        try {
            $count = $this->visitorService->getLiveVisitors();
            return response()->json([
                'success' => true,
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Unable to load live visitors.',
            ], 500);
        }
    }

    /**
     * Export visitor statistics (CSV).
     */
    public function export(Request $request)
    {
        try {
            $period = $request->input('period', 'last_30_days');
            $analytics = $this->visitorService->getDetailedAnalytics($period);

            $filename = 'visitor_analytics_' . now()->format('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => "attachment; filename=$filename",
            ];

            $callback = function () use ($analytics) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Date', 'Unique Visitors']);

                foreach ($analytics['visitors_by_date'] as $data) {
                    fputcsv($file, [
                        $data->date,
                        $data->count,
                    ]);
                }

                fputcsv($file, []); // Empty line
                fputcsv($file, ['Page', 'Total Visits', 'Unique Visitors']);

                foreach ($analytics['top_pages'] as $page) {
                    fputcsv($file, [
                        $page->page_name ?? $page->path,
                        $page->visits,
                        $page->unique_visitors,
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error exporting visitor analytics', [
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()
                ->with('error', 'Unable to export visitor analytics.');
        }
    }
}
