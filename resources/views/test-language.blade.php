<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Language Switcher Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ccc; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        .debug { background: #f0f0f0; padding: 10px; margin: 10px 0; font-family: monospace; }
    </style>
</head>
<body>
    <h1>Language Switcher Test</h1>
    
    <div class="test-section">
        <h2>Debug Information</h2>
        <div class="debug">
            <p><strong>Session Locale:</strong> {{ session('locale', 'not set') }}</p>
            <p><strong>App Locale:</strong> {{ app()->getLocale() }}</p>
            <p><strong>Config Locale:</strong> {{ config('app.locale') }}</p>
            <p><strong>Text Direction:</strong> {{ app()->getLocale() === 'ar' ? 'RTL' : 'LTR' }}</p>
        </div>
    </div>
    
    <div class="test-section">
        <h2>Translation Test</h2>
        <p><strong>Testing language.english:</strong> "{{ __('language.english') }}"</p>
        <p><strong>Testing language.arabic:</strong> "{{ __('language.arabic') }}"</p>
        <p><strong>Testing language.french:</strong> "{{ __('language.french') }}"</p>
        <p><strong>Testing general.home:</strong> "{{ __('general.home') }}"</p>
    </div>
    
    <div class="test-section">
        <h2>Language Switcher</h2>
        @include('components.language-switcher')
    </div>
    
    <div class="test-section">
        <h2>File Paths Check</h2>
        <div class="debug">
            <p><strong>EN Language file exists:</strong> {{ file_exists(resource_path('lang/en/language.php')) ? 'YES' : 'NO' }}</p>
            <p><strong>AR Language file exists:</strong> {{ file_exists(resource_path('lang/ar/language.php')) ? 'YES' : 'NO' }}</p>
            <p><strong>FR Language file exists:</strong> {{ file_exists(resource_path('lang/fr/language.php')) ? 'YES' : 'NO' }}</p>
        </div>
    </div>
    
    <div class="test-section">
        <h2>Instructions</h2>
        <p>1. Click on the language switcher above</p>
        <p>2. Select a different language</p>
        <p>3. The page should reload with the new language</p>
        <p>4. Check if the translations show correctly</p>
    </div>
</body>
</html>