<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceProvider;
use App\Services\AdminProviderActivityMonitorService;
use Illuminate\Http\Request;

class ProviderActivityMonitorController extends Controller
{
    public function __construct(
        protected AdminProviderActivityMonitorService $activityService
    ) {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Admin: Provider Activity Monitor (paginated).
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);
        $perPage = max(5, min(50, $perPage));

        $providers = $this->activityService->paginateProviders($perPage);

        return view('admin.provider_activity_monitor.index', compact('providers'));
    }

    /**
     * Admin: provider analytics details (events list).
     */
    public function show(ServiceProvider $serviceProvider, Request $request)
    {
        $perPage = (int) $request->input('per_page', 30);
        $perPage = max(10, min(100, $perPage));

        $details = $this->activityService->getProviderDetails($serviceProvider, $perPage);

        return view('admin.provider_activity_monitor.show', $details);
    }
}

