<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Translations</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; background: #f5f5f5; }
        .debug-section { background: white; margin: 20px 0; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        .debug { background: #f8f9fa; padding: 10px; margin: 10px 0; font-family: monospace; border-left: 4px solid #007bff; }
        .translation-test { background: #e8f5e8; padding: 10px; margin: 10px 0; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>🔍 Translation System Debug</h1>
    
    <div class="debug-section">
        <h2>📍 Current State</h2>
        <div class="debug">
            <p><strong>Session Locale:</strong> {{ session('locale', 'not set') }}</p>
            <p><strong>App Locale:</strong> {{ app()->getLocale() }}</p>
            <p><strong>Config Locale:</strong> {{ config('app.locale') }}</p>
            <p><strong>Text Direction:</strong> {{ app()->getLocale() === 'ar' ? 'RTL' : 'LTR' }}</p>
            <p><strong>Fallback Locale:</strong> {{ config('app.fallback_locale') }}</p>
        </div>
    </div>
    
    <div class="debug-section">
        <h2>🌍 Translation Tests</h2>
        
        <div class="translation-test">
            <h3>Language Switcher Translations:</h3>
            <p><strong>__('language.english'):</strong> "{{ __('language.english') }}"</p>
            <p><strong>__('language.arabic'):</strong> "{{ __('language.arabic') }}"</p>
            <p><strong>__('language.french'):</strong> "{{ __('language.french') }}"</p>
            <p><strong>__('language.current'):</strong> "{{ __('language.current') }}"</p>
        </div>
        
        <div class="translation-test">
            <h3>General Translations:</h3>
            <p><strong>__('general.home'):</strong> "{{ __('general.home') }}"</p>
            <p><strong>__('general.login'):</strong> "{{ __('general.login') }}"</p>
            <p><strong>__('general.register'):</strong> "{{ __('general.register') }}"</p>
        </div>
        
        <div class="translation-test">
            <h3>Auth Translations:</h3>
            <p><strong>__('auth.failed'):</strong> "{{ __('auth.failed') }}"</p>
            <p><strong>__('auth.password'):</strong> "{{ __('auth.password') }}"</p>
        </div>
    </div>
    
    <div class="debug-section">
        <h2>📁 File System Check</h2>
        <div class="debug">
            @php
                $langPath = lang_path();
                $enLangFile = $langPath . '/en/language.php';
                $arLangFile = $langPath . '/ar/language.php';
                $frLangFile = $langPath . '/fr/language.php';
            @endphp
            <p><strong>Language Directory:</strong> {{ $langPath }}</p>
            <p><strong>EN language.php exists:</strong> {{ file_exists($enLangFile) ? '✅ YES' : '❌ NO' }}</p>
            <p><strong>AR language.php exists:</strong> {{ file_exists($arLangFile) ? '✅ YES' : '❌ NO' }}</p>
            <p><strong>FR language.php exists:</strong> {{ file_exists($frLangFile) ? '✅ YES' : '❌ NO' }}</p>
        </div>
    </div>
    
    <div class="debug-section">
        <h2>🔄 Language Switcher Test</h2>
        @include('components.language-switcher')
    </div>
    
    <div class="debug-section">
        <h2>📝 Manual Translation Test</h2>
        <div class="debug">
            @php
                // Test direct translation
                $translated = trans('language.english');
                $translatedGeneral = trans('general.home');
            @endphp
            <p><strong>trans('language.english'):</strong> "{{ $translated }}"</p>
            <p><strong>trans('general.home'):</strong> "{{ $translatedGeneral }}"</p>
        </div>
    </div>
    
    <div class="debug-section">
        <h2>🔄 Test Language Switching</h2>
        <p>Click the language switcher above to test if translations change properly.</p>
        <p><strong>Expected behavior:</strong> When you switch languages, the page should reload and all translations should show in the selected language.</p>
    </div>
</body>
</html>