<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        try {
            $request->authenticate();
            $request->session()->regenerate();

            // Determine redirect based on user role
            $user = Auth::user();

            // ✅ ADMIN: Redirect to admin dashboard
            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', __('auth.login_success'));
            }

            if ($user->role === 'service_provider') {
                return redirect()->intended(route('service-providers.show', $user->serviceProvider->id))
                    ->with('success', __('auth.login_success'));
            }

            // Client users go to the provider discovery page
            return redirect()->intended(route('service-providers.index'))
                ->with('success', __('auth.login_success'));

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exceptions to show proper error messages
            throw $e;
        } catch (\Exception $e) {
            // Log unexpected errors
            \Log::error('Login error: ' . $e->getMessage());

            return back()
                ->withInput($request->only('login', 'remember'))
                ->withErrors([
                    'login' => __('auth.login_error'),
                ]);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
