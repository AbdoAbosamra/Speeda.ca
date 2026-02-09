<div id="cookie-overlay"></div>

<!-- Cookie Consent Banner -->
<div id="cookie-banner" role="dialog" aria-labelledby="cookie-title" aria-describedby="cookie-description">
    <div id="cookie-title" class="visually-hidden">{{ __('home.cookie_title') }}</div>
    <div id="cookie-description">
        🍪 {{ __('home.cookie_message') }}
        {{ __('home.cookie_agree') }}
        <a href="/terms-of-service" target="_blank" rel="noopener">{{ __('home.terms_service') }}</a> {{ __('home.and') }}
        <a href="/privacy-policy" target="_blank" rel="noopener">{{ __('home.privacy_policy') }}</a>.
    </div>
    <br>
    <button id="accept-cookies" aria-label="{{ __('home.accept_cookies_aria') }}">{{ __('home.accept') }}</button>
</div>
