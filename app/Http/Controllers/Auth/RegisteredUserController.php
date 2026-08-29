<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Location;
use App\Models\User;
use App\Services\AuthService;
use App\Services\CategoryCacheService;
use App\Services\LocationCacheService;
use App\Support\SignupLocations;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Services\FacebookConversionService;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        // PERFORMANCE: Use cached terminal categories from Redis (24h TTL)
        $professions = app(CategoryCacheService::class)->getTerminalCategories();

        $professionGroups = app(CategoryCacheService::class)->getGroupedTerminalCategories();

        // @change 2026-06-07 | Pass active locations to view for dynamic grouped city dropdown
        // Locations are ordered by region then city name for readable grouping.
        $locations = app(LocationCacheService::class)->getActiveLocations();
        $locationGroups = SignupLocations::groupActiveLocations($locations);

        return view('auth.register', compact('professions', 'professionGroups', 'locations', 'locationGroups'));

    }

    /**
     * Handle an incoming registration request.
     */
    public function store(RegisterRequest $request, AuthService $authService): RedirectResponse
    {
        // Validation is handled by RegisterRequest
        $validatedData = $request->validated();

        // Use AuthService to handle registration logic
        $user = $authService->registerUser($validatedData);

        // Fire registered event
        event(new Registered($user));

        // Log the user in
        Auth::login($user);

        // Get role-based redirect path
        $redirectPath = $authService->getRedirectPath($user);

        // Flash Meta Pixel CompleteRegistration event data for client-side tracking
        $registrationEventId = 'reg_' . $user->id . '_' . time();
        session()->flash('meta_pixel_complete_registration', true);
        session()->flash('meta_pixel_registration_event_id', $registrationEventId);

        // CAPI: Send CompleteRegistration event (server-side, non-blocking)
        try {
            app(FacebookConversionService::class)->trackCompleteRegistration($registrationEventId, [
                'status' => true,
            ], [
                'email' => $user->email,
                'external_id' => $user->id,
            ]);
        } catch (\Throwable $e) {
            // Silently ignore CAPI errors
        }

        return redirect($redirectPath)->with('success', __('auth.registration_success'));
    }
}
