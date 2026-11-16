<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\EnrollProviderRequest;
use App\Services\AuthService;
use App\Services\CategoryCacheService;
use App\Services\LocationCacheService;
use App\Support\SignupLocations;
use Illuminate\Http\Request;

class ProviderEnrollmentController extends Controller
{
    public function create(Request $request)
    {
        if ($request->user()->serviceProvider || $request->user()->isAdmin()) {
            return redirect()->route('provider.offers.create');
        }

        return view('offers.enroll', [
            'professions' => app(CategoryCacheService::class)->getTerminalCategories(),
            'locationGroups' => SignupLocations::groupActiveLocations(app(LocationCacheService::class)->getActiveLocations()),
        ]);
    }

    public function store(EnrollProviderRequest $request, AuthService $authService)
    {
        $authService->enrollProvider($request->user(), $request->validated());

        return redirect()->route('provider.offers.create');
    }
}
