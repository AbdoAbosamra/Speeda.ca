<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ErrorHelper;
use App\Http\Controllers\Controller;
use App\Services\Admin\WhatsappAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsappAnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * WhatsApp Click Intelligence dashboard.
     */
    public function index(Request $request, WhatsappAnalyticsService $service)
    {
        try {
            $filters = $this->filters($request);
            [$from, $to] = $service->dateRange($filters);

            $summary = $service->summary($filters);

            $charts = [
                'over_time' => $service->clicksOverTime($filters),
                'by_hour' => $service->clicksByHour($filters),
                'by_dow' => $service->clicksByDayOfWeek($filters),
                'by_source' => $service->clicksByDimension($filters, 'source_page'),
                'by_device' => $service->clicksByDimension($filters, 'device_type'),
                'by_locale' => $service->clicksByDimension($filters, 'locale'),
            ];

            $tables = [
                'top_providers' => $service->topProviders($filters),
                'low_conversion' => $service->lowConversionProviders($filters),
                'category_performance' => $service->categoryPerformance($filters),
                'location_performance' => $service->locationPerformance($filters),
                'suspicious' => $service->suspiciousSessions($filters),
            ];

            $recentActivity = $service->recentActivity($filters);
            $options = $service->filterOptions();

            return view('admin.analytics.whatsapp', compact(
                'filters',
                'from',
                'to',
                'summary',
                'charts',
                'tables',
                'recentActivity',
                'options'
            ));
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            Log::error('WhatsApp analytics error: '.$e->getMessage());
            ErrorHelper::flashNotification($error['message'], $error['type']);

            return redirect()->route('admin.dashboard');
        }
    }

    /**
     * Collect and sanitise the filter inputs (persisted in the query string).
     */
    private function filters(Request $request): array
    {
        return [
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'provider_id' => $request->input('provider_id') ?: null,
            'category_id' => $request->input('category_id') ?: null,
            'location_id' => $request->input('location_id') ?: null,
            'phone_search' => trim((string) $request->input('phone_search')) ?: null,
            'device_type' => $request->input('device_type') ?: null,
        ];
    }
}
