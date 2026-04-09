<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Category;
use App\Models\User;
use App\Services\AuthService;
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
        // Load terminal categories (actual professions — no children) with parent chain.
        $professions = Category::with('parent.parent')
            ->terminal()
            ->orderBy('name')
            ->get();

        // Group professions by immediate parent's localized name for optgroup rendering
        $professionGroups = $professions
            ->groupBy(function ($category) {
                return $category->parent
                    ? $category->parent->localized_name
                    : __('general.other');
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
