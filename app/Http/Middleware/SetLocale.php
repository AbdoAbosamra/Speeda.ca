<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * CRITICAL: This middleware runs BEFORE every request and sets the application locale
     * based on the user's session preference.
     */
    public function handle(Request $request, Closure $next)
    {
        // Get supported locales from config
        $supportedLocales = array_keys(config('app.supported_locales', ['en', 'ar', 'fr']));

        // STEP 1: Try to get locale from session (user's saved preference), guard when no session
        $locale = null;
        if ($request->hasSession()) {
            $locale = $request->session()->get('locale');
        }

        // STEP 2: If not in session, detect from browser Accept-Language header
        if (!$locale) {
            $locale = $this->detectLocaleFromBrowser($request, $supportedLocales);
            if ($request->hasSession()) {
                $request->session()->put('locale', $locale);
            }
        }

        // STEP 3: Validate locale is supported, fallback to configured fallback if invalid
        if (!in_array($locale, $supportedLocales)) {
            $locale = config('app.fallback_locale', 'en');
            if ($request->hasSession()) {
                $request->session()->put('locale', $locale);
            }
        }

        // STEP 4: Set application locale for this request
        App::setLocale($locale);

        return $next($request);
    }

    /**
     * Detect locale from browser accept-language header
     */
    protected function detectLocaleFromBrowser(Request $request, array $supportedLocales): string
    {
        $browserLocale = $request->getPreferredLanguage($supportedLocales);

        if ($browserLocale && in_array($browserLocale, $supportedLocales)) {
            return $browserLocale;
        }

        return config('app.fallback_locale', 'en');
    }
}
