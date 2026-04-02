<?php

namespace App\Http\Controllers;

use App\Actions\CalculateProfileCompletionAction;
use App\Services\ProviderDashboardAnalyticsService;
use Illuminate\Http\Request;

class ProviderDashboardController extends Controller
{
    public function index(Request $request, ProviderDashboardAnalyticsService $analyticsService)
    {
        $user = $request->user();

        if (!$user || $user->isAdmin()) {
            abort(403);
        }

        $provider = $user->serviceProvider;

        if (!$provider) {
            return redirect()->route('service-providers.index');
        }

        // Ensure completion percent is fresh
        app(CalculateProfileCompletionAction::class)->execute($provider);
        $provider->refresh();

        $stats = $analyticsService->getStatsForProvider($provider->id);
        $trends = $analyticsService->getDailyTrends($provider->id, 7);

        return view('service-providers.dashboard', [
            'serviceProvider' => $provider,
            'stats' => $stats,
            'trends' => $trends,
        ]);
    }
}
