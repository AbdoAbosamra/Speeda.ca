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

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        // Pass all child categories (all 55 professions) to the registration view
        $professions = Category::whereNotNull('parent_id')->where('is_active', 1)->orderBy('name')->get();

        return view('auth.register', compact('professions'));
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

        return redirect($redirectPath)->with('success', __('auth.registration_success'));
    }
}
