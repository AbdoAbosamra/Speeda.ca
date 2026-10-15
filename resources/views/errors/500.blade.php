{{--
    Self-contained on purpose: no layout, no database, no Vite, no CDN.
    A 500 often means the database or a view the layout needs is what broke,
    and an error page that itself throws leaves the visitor with a blank screen.
    Translations come from lang files on disk, which are always readable.
--}}
@php
    $locale = in_array(app()->getLocale(), ['en', 'ar', 'fr'], true) ? app()->getLocale() : 'en';
    $dir = $locale === 'ar' ? 'rtl' : 'ltr';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>500 - {{ __('errors.page_500_title') }} | Speeda</title>
    <link rel="icon" type="image/png" href="/images/New_logo.png">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            padding: 24px 16px; background: #f0f7ff; color: #1e293b;
            font-family: system-ui, -apple-system, "Segoe UI", Tahoma, Arial, sans-serif; line-height: 1.6;
        }
        .card {
            width: 100%; max-width: 520px; background: #fff; border-radius: 20px; text-align: center;
            padding: 40px 28px; box-shadow: 0 10px 30px rgba(15, 23, 42, .08);
        }
        .logo { width: 140px; height: auto; margin-bottom: 20px; }
        .code { font-size: 64px; font-weight: 800; color: #0056b3; margin: 0; line-height: 1; }
        h1 { font-size: 22px; margin: 12px 0 8px; }
        p { color: #64748b; margin: 0 0 28px; }
        .actions { display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; }
        .btn {
            display: inline-block; padding: 10px 22px; border-radius: 10px; font-weight: 600;
            text-decoration: none; border: 1px solid #0056b3; cursor: pointer; font-size: 15px;
        }
        .btn-primary { background: #0056b3; color: #fff; }
        .btn-outline { background: #fff; color: #0056b3; }
    </style>
</head>
<body>
    <main class="card">
        <img class="logo" src="/images/New_logo.png" alt="Speeda">
        <p class="code">500</p>
        <h1>{{ __('errors.page_500_title') }}</h1>
        <p>{{ __('errors.page_500_desc') }}</p>
        <div class="actions">
            <a class="btn btn-primary" href="{{ request()->fullUrl() }}">{{ __('errors.try_again') }}</a>
            <a class="btn btn-outline" href="/">{{ __('general.home') }}</a>
        </div>
    </main>
</body>
</html>
