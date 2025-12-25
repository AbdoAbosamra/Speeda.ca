<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>Translation Test</title>
</head>
<body>
    <h1>Translation Test Page</h1>
    
    <div style="background: #f0f0f0; padding: 20px; margin: 10px;">
        <h3>System Info:</h3>
        <p>App Locale: {{ app()->getLocale() }}</p>
        <p>Session Locale: {{ session('locale') }}</p>
    </div>

    <div style="background: #e0f0e0; padding: 20px; margin: 10px;">
        <h3>Translation Tests:</h3>
        <p>Welcome: {{ __('messages.welcome') }}</p>
        <p>Home: {{ __('messages.home') }}</p>
        <p>Login: {{ __('messages.login') }}</p>
        <p>Register: {{ __('messages.register') }}</p>
    </div>

    <div style="background: #f0e0e0; padding: 20px; margin: 10px;">
        <h3>Change Language:</h3>
        <a href="/locale/en?redirect={{ url()->current() }}">English</a> | 
        <a href="/locale/ar?redirect={{ url()->current() }}">العربية</a> | 
        <a href="/locale/fr?redirect={{ url()->current() }}">Français</a>
    </div>
</body>
</html>