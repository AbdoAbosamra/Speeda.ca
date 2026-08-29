<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\GoogleDetailsRequest;
use App\Services\AuthService;
use App\Services\CategoryCacheService;
use App\Services\GoogleAuthService;
use App\Services\LocationCacheService;
use App\Support\SignupLocations;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

/**
 * "Continue with Google".
 *
 * Two outcomes on return from Google:
 *   - known account -> signed in
 *   - new account   -> sent to a short form, whichever role they are signing
 *                      up as, because Google settles only the email address
 *                      and a display name. A client still has to say that a
 *                      client is what they are and accept the terms; a
 *                      provider additionally needs the trade, city and phone
 *                      their public profile cannot exist without.
 *
 * That second step is why the Google profile is parked in the session between
 * the two. Only the id, email, name and the role the button implied are
 * stored, never the access token, and the stash expires so an abandoned
 * signup cannot be picked up later on a shared machine.
 */
class GoogleAuthController extends Controller
{
    /**
     * The "no credentials configured" case is handled by
     * EnsureGoogleAuthEnabled on the routes, not here: Laravel instantiates
     * controllers while gathering route middleware, so aborting from a
     * constructor would break `route:list` as well as the request.
     */
    public function __construct(private readonly GoogleAuthService $google) {}

    /**
     * Hand off to Google, remembering whether they pressed the button on the
     * client tab or the provider tab.
     */
    public function redirect(Request $request): RedirectResponse
    {
        $intent = $request->query('as') === 'service_provider' ? 'service_provider' : 'client';

        $request->session()->put(GoogleAuthService::SESSION_INTENT, $intent);

        return Socialite::driver('google')->redirect();
    }

    /**
     * Back from Google.
     */
    public function callback(Request $request): RedirectResponse
    {
        // The user pressed "Cancel" on Google's screen. Not an error.
        if ($request->query('error')) {
            return redirect()->route('register')
                ->with('error', __('auth.google_cancelled'));
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            Log::warning('[GoogleAuth] Callback failed', ['error' => $e->getMessage()]);

            return redirect()->route('register')->with('error', __('auth.google_failed'));
        }

        if (blank($googleUser->getEmail())) {
            return redirect()->route('register')->with('error', __('auth.google_no_email'));
        }

        // Google will hand back an unverified address for some workspace
        // configurations. Trusting it would let someone claim an email they do
        // not own, and from there an existing Speeda account.
        if (($googleUser->user['email_verified'] ?? false) !== true) {
            return redirect()->route('register')->with('error', __('auth.google_email_unverified'));
        }

        $user = $this->google->findOrLinkExisting($googleUser);

        if ($user) {
            if (!$user->isActive()) {
                return redirect()->route('register')->with('error', __('auth.account_disabled'));
            }

            return $this->signIn($user, __('auth.login_success'));
        }

        // Nothing is written yet. Both roles finish on the details form, so a
        // half-finished signup leaves no account behind.
        $intent = $request->session()->pull(GoogleAuthService::SESSION_INTENT, 'client');

        $request->session()->put(
            GoogleAuthService::SESSION_PENDING,
            $this->google->pendingPayload($googleUser, $intent),
        );

        return redirect()->route('google.complete');
    }

    /**
     * Step two: the details Google cannot give us. The provider fields are
     * rendered but hidden until the provider role is picked, so switching
     * role never costs a page load.
     */
    public function showComplete(Request $request): View|RedirectResponse
    {
        $pending = $request->session()->get(GoogleAuthService::SESSION_PENDING);

        if (!$this->google->pendingIsFresh($pending)) {
            $request->session()->forget(GoogleAuthService::SESSION_PENDING);

            return redirect()->route('register')->with('error', __('auth.google_session_expired'));
        }

        $locations = app(LocationCacheService::class)->getActiveLocations();

        return view('auth.google-complete', [
            'pending' => $pending,
            'professions' => app(CategoryCacheService::class)->getTerminalCategories(),
            'professionGroups' => app(CategoryCacheService::class)->getGroupedTerminalCategories(),
            'locations' => $locations,
            'locationGroups' => SignupLocations::groupActiveLocations($locations),
        ]);
    }

    public function storeComplete(GoogleDetailsRequest $request, AuthService $authService): RedirectResponse
    {
        $pending = $request->session()->get(GoogleAuthService::SESSION_PENDING);

        if (!$this->google->pendingIsFresh($pending)) {
            $request->session()->forget(GoogleAuthService::SESSION_PENDING);

            return redirect()->route('register')->with('error', __('auth.google_session_expired'));
        }

        $user = $this->google->createAccount($pending, $request->validated());

        $request->session()->forget(GoogleAuthService::SESSION_PENDING);

        event(new Registered($user));

        return $this->signIn($user, __('auth.registration_success'));
    }

    /**
     * Log in and send them where their role belongs, regenerating the session
     * id so a session fixed before the round-trip to Google is useless.
     */
    private function signIn($user, string $message): RedirectResponse
    {
        Auth::login($user, remember: true);

        session()->regenerate();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard')->with('success', $message);
        }

        return redirect(app(AuthService::class)->getRedirectPath($user))->with('success', $message);
    }

}
