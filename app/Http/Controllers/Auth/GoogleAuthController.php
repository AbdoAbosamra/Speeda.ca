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
use Laravel\Socialite\Two\InvalidStateException;
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
        // Google always returns to the one host registered as the redirect URI.
        // Starting the round trip from a different host that resolves to the
        // same server — 127.0.0.1 instead of localhost, or the bare domain
        // instead of www — means the session cookie is written for one host and
        // read back on another, so the CSRF state Socialite stored is simply
        // not there on return. The failure surfaces as a generic "sign-in
        // failed" with nothing pointing at the cause, so move to the canonical
        // host first and keep the whole flow on one origin.
        if ($canonical = $this->canonicalRedirect($request)) {
            return $canonical;
        }

        $intent = $request->query('as') === 'service_provider' ? 'service_provider' : 'client';

        $request->session()->put(GoogleAuthService::SESSION_INTENT, $intent);

        return Socialite::driver('google')->redirect();
    }

    /**
     * A redirect to this same route on the host Google will return to, or null
     * when the request is already there (the normal case).
     */
    private function canonicalRedirect(Request $request): ?RedirectResponse
    {
        $parts = parse_url((string) config('services.google.redirect'));

        if (empty($parts['host'])) {
            return null;
        }

        // Host only, deliberately. Cookies are not scoped by port or scheme, so
        // localhost:8000 and localhost:80 share a session and need no redirect —
        // only a genuinely different host name does.
        if (strcasecmp($parts['host'], $request->getHost()) === 0) {
            return null;
        }

        $target = ($parts['scheme'] ?? $request->getScheme()).'://'.$parts['host']
            .(isset($parts['port']) ? ':'.$parts['port'] : '');

        Log::info('[GoogleAuth] Restarting sign-in on the canonical host', [
            'from' => $request->getHost(),
            'to' => $parts['host'],
        ]);

        return redirect()->away(
            $target.$request->getPathInfo()
            .($request->getQueryString() ? '?'.$request->getQueryString() : '')
        );
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
        } catch (InvalidStateException $e) {
            // The state Socialite stored before the round trip is missing on
            // return. Almost always the session went away in between — it timed
            // out, cookies were cleared, or an old callback URL was reopened —
            // rather than anything wrong with the Google account, so "start
            // again" is the useful instruction, not "try again".
            // Logged at error level on purpose. A sign-in nobody can complete
            // is not a curiosity, and at the usual production LOG_LEVEL=error a
            // warning here is discarded — leaving the one line that explains
            // the failure unwritten exactly when it is needed.
            Log::error('[GoogleAuth] State missing on return', [
                'host' => $request->getHost(),
                'has_session_cookie' => $request->hasCookie(config('session.cookie')),
                'expected_host' => parse_url((string) config('services.google.redirect'), PHP_URL_HOST),
            ]);

            return redirect()->route('register')->with('error', __('auth.google_session_expired'));
        } catch (Throwable $e) {
            // An authorization code is single use: loading the callback twice —
            // a refresh, a back button, a link opened from history — spends it
            // on the first load and Google rejects the second with
            // invalid_grant. Telling that person to "try again" sends them
            // round the same loop, so it gets the "start again" message.
            $reused = str_contains($e->getMessage(), 'invalid_grant');

            Log::error('[GoogleAuth] Callback failed', [
                'exception' => $e::class,
                'error' => $e->getMessage(),
                'host' => $request->getHost(),
                'likely_reused_code' => $reused,
            ]);

            return redirect()->route('register')->with(
                'error',
                $reused ? __('auth.google_session_expired') : __('auth.google_failed')
            );
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
