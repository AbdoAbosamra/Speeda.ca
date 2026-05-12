<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Category;
use App\Models\User;
use App\Services\AuthService;
use App\Services\CategoryCacheService;
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

        // Eager load ALL active categories into a lookup map to resolve roots without lazy loading
        $allCategories = Category::where('is_active', true)->get()->keyBy('id');

        // Group terminal professions by their top-level root section (Level 1) for hierarchical optgroup rendering
        $professionGroups = $professions
            ->groupBy(function ($category) use ($allCategories) {
                // Find the root section (top ancestor where parent_id is null) using the lookup map
                $root = $category;
                while ($root->parent_id && isset($allCategories[$root->parent_id])) {
                    $root = $allCategories[$root->parent_id];
                }
                return $root->localized_name;
            })
            ->sortKeys();

        return view('auth.register', compact('professions', 'professionGroups'));
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
