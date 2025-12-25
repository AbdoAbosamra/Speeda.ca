<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    /**
     * Handle GET request for locale switch
     */
    public function switch(Request $request, string $locale)
    {
        return $this->handleLocaleChange($locale, $request);
    }

    /**
     * Handle POST request for locale update
     */
    public function update(Request $request)
    {
        $locale = $request->input('locale');
        return $this->handleLocaleChange($locale, $request);
    }

    /**
     * Common method to handle locale changing
     */
    protected function handleLocaleChange(string $locale, Request $request)
    {
        $supportedLocales = array_keys(config('app.supported_locales', ['en', 'ar', 'fr']));

        // Validate the locale
        if (!in_array($locale, $supportedLocales)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Invalid locale'], 400);
            }
            // Return 400 status for invalid locale in tests
            return response()->json(['error' => 'Invalid locale'], 400);
        }

        // CRITICAL: Store in session and force save immediately
        $request->session()->put('locale', $locale);
        $request->session()->save();

        // Set for current request
        App::setLocale($locale);

        // Get safe redirect URL (path only, not full URL)
        $redirectUrl = $this->getSafeRedirectUrl($request);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'locale' => $locale,
                'redirect_to' => $redirectUrl,
                'message' => __('language.language_changed_successfully'),
            ]);
        }

        // Redirect back to same page with new locale
        return redirect($redirectUrl)
            ->with('success', __('language.language_changed_successfully'));
    }

    /**
     * Get safe redirect URL with validation
     */
    protected function getSafeRedirectUrl(Request $request): string
    {
        // Try multiple sources for redirect URL
        $redirectUrl = $request->input('redirect_to')
                    ?? $request->input('redirect')
                    ?? $request->query('redirect')
                    ?? $request->header('Referer')
                    ?? url('/');

        // Basic validation
        if (!$redirectUrl || $redirectUrl === 'null') {
            return '/';
        }

        // Parse URL to extract path only (avoid full URL redirects that might break session)
        $parsedUrl = parse_url($redirectUrl);

        if (!$parsedUrl) {
            return '/';
        }

        // If it's a full URL with host
        if (isset($parsedUrl['host'])) {
            // Ensure same domain for security
            $appHost = parse_url(config('app.url'), PHP_URL_HOST);
            $targetHost = $parsedUrl['host'];

            if ($targetHost !== $appHost && $targetHost !== request()->getHost()) {
                return '/';
            }

            // Return path only (with query string if present) to maintain session
            $path = $parsedUrl['path'] ?? '/';
            if (isset($parsedUrl['query'])) {
                $path .= '?' . $parsedUrl['query'];
            }
            return $path;
        }

        // It's already a relative path
        return $redirectUrl;
    }

    /**
     * Get current locale info
     */
    public function getCurrentLocale()
    {
        $locale = app()->getLocale();
        $supportedLocales = config('app.supported_locales', []);
        $direction = in_array($locale, ['ar', 'he', 'ur', 'fa']) ? 'rtl' : 'ltr';

        return response()->json([
            'locale' => $locale,
            'direction' => $direction,
            'supported_locales' => $supportedLocales,
            'current_locale_info' => $supportedLocales[$locale] ?? null,
        ]);
    }
}
