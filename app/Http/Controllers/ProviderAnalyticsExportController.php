<?php

namespace App\Http\Controllers;

use App\Services\ProviderDashboardAnalyticsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ProviderAnalyticsExportController extends Controller
{
    /**
     * Export the provider's analytics report as a PDF.
     */
    public function exportPdf(Request $request, ProviderDashboardAnalyticsService $analyticsService)
    {
        $user = $request->user();

        if (!$user || $user->isAdmin()) {
            abort(403);
        }

        $provider = $user->serviceProvider;

        if (!$provider) {
            return redirect()->route('service-providers.index');
        }

        $provider->loadMissing(['user', 'category']);

        $stats = $analyticsService->getStatsForProvider($provider->id);
        $monthly = $analyticsService->getMonthlyStats($provider->id);

        $pdf = Pdf::loadView('service-providers.analytics-pdf', [
            'serviceProvider' => $provider,
            'stats' => $stats,
            'monthly' => $monthly,
        ]);

        $filename = 'speeda-analytics-' . $provider->id . '-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }
}
