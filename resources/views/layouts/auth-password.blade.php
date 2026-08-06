<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ar', 'he', 'ur', 'fa']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="referrer" content="same-origin">

    <link rel="icon" type="image/png" href="{{ asset('images/New_logo.png') }}">
    <title>{{ $pageTitle ?? __('auth.authentication') }} | {{ config('app.name', 'Speeda') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --auth-blue: #2563eb;
            --auth-blue-dark: #1d4ed8;
            --auth-cyan: #0891b2;
            --auth-green: #059669;
            --auth-ink: #0f172a;
            --auth-muted: #64748b;
            --auth-line: #dbe4f0;
            --auth-soft: #f6f9fc;
            --auth-panel: #ffffff;
            --auth-danger: #dc2626;
            --auth-success: #047857;
        }

        * {
            box-sizing: border-box;
        }

        body.password-auth-page {
            margin: 0;
            min-height: 100vh;
            background:
                linear-gradient(120deg, rgba(37, 99, 235, 0.08), rgba(8, 145, 178, 0.05) 38%, rgba(5, 150, 105, 0.08)),
                #f8fafc;
            color: var(--auth-ink);
            font-family: "Figtree", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .password-auth-nav {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(255, 255, 255, 0.92);
            border-bottom: 1px solid rgba(219, 228, 240, 0.9);
            backdrop-filter: blur(18px);
        }

        .password-auth-nav__inner {
            width: min(1180px, calc(100% - 32px));
            min-height: 76px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: 22px;
        }

        .password-auth-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--auth-ink);
            text-decoration: none;
            font-weight: 800;
        }

        .password-auth-brand img {
            width: 62px;
            height: 52px;
            object-fit: contain;
            filter: drop-shadow(0 8px 18px rgba(37, 99, 235, 0.18));
        }

        .password-auth-brand span {
            font-size: 1rem;
            letter-spacing: 0;
        }

        .password-auth-menu {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-inline-start: auto;
        }

        .password-auth-link,
        .password-auth-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            padding: 0 14px;
            border-radius: 8px;
            color: #334155;
            text-decoration: none;
            font-size: 0.92rem;
            font-weight: 700;
            white-space: nowrap;
            transition: transform 0.18s ease, background 0.18s ease, color 0.18s ease, box-shadow 0.18s ease;
        }

        .password-auth-link:hover {
            background: #eff6ff;
            color: var(--auth-blue-dark);
        }

        .password-auth-action {
            background: var(--auth-blue);
            color: #ffffff;
            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.22);
        }

        .password-auth-action:hover {
            background: var(--auth-blue-dark);
            transform: translateY(-1px);
        }

        .password-auth-nav .language-btn {
            min-width: 132px;
            min-height: 42px;
            padding: 0 12px;
            border-radius: 8px;
            font-size: 0.88rem;
            box-shadow: none;
        }

        .password-auth-main {
            width: min(1080px, calc(100% - 32px));
            min-height: calc(100vh - 76px);
            margin: 0 auto;
            padding: 48px 0;
            display: grid;
            align-items: center;
        }

        .password-auth-shell {
            display: grid;
            grid-template-columns: 0.9fr 1.1fr;
            overflow: hidden;
            background: var(--auth-panel);
            border: 1px solid rgba(219, 228, 240, 0.95);
            border-radius: 8px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.12);
        }

        .password-auth-visual {
            min-height: 520px;
            padding: 42px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background:
                linear-gradient(160deg, rgba(15, 23, 42, 0.84), rgba(30, 64, 175, 0.78)),
                url("{{ asset('images/hero-banner.jpeg') }}") center / cover;
            color: #ffffff;
        }

        .password-auth-mark {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            width: fit-content;
            padding: 10px 14px;
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.11);
        }

        .password-auth-mark img {
            width: 58px;
            height: 48px;
            object-fit: contain;
        }

        .password-auth-mark span {
            font-weight: 800;
            font-size: 1.05rem;
        }

        .password-auth-visual h1 {
            max-width: 430px;
            margin: 0 0 12px;
            font-size: clamp(2rem, 4vw, 3.2rem);
            line-height: 1.05;
            letter-spacing: 0;
        }

        .password-auth-visual p {
            max-width: 420px;
            margin: 0;
            color: rgba(255, 255, 255, 0.82);
            font-size: 1rem;
            line-height: 1.7;
        }

        .password-auth-card {
            padding: clamp(28px, 5vw, 56px);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .password-auth-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: fit-content;
            margin-bottom: 18px;
            padding: 8px 12px;
            border-radius: 8px;
            background: #ecfeff;
            color: #0e7490;
            font-size: 0.84rem;
            font-weight: 800;
        }

        .password-auth-card h2 {
            margin: 0;
            color: var(--auth-ink);
            font-size: clamp(1.8rem, 4vw, 2.55rem);
            line-height: 1.12;
            letter-spacing: 0;
        }

        .password-auth-card__intro {
            margin: 14px 0 28px;
            color: var(--auth-muted);
            font-size: 1rem;
            line-height: 1.7;
        }

        .password-form {
            display: grid;
            gap: 18px;
        }

        .password-field {
            display: grid;
            gap: 8px;
        }

        .password-field label {
            color: #1e293b;
            font-size: 0.92rem;
            font-weight: 800;
        }

        .password-input-wrap {
            position: relative;
        }

        .password-input-wrap i {
            position: absolute;
            inset-inline-start: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
        }

        .password-input-wrap input {
            width: 100%;
            min-height: 52px;
            padding: 0 48px;
            border: 1px solid var(--auth-line);
            border-radius: 8px;
            background: #ffffff;
            color: var(--auth-ink);
            font: inherit;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .password-input-wrap input:focus {
            border-color: var(--auth-blue);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .password-input-wrap input.is-invalid {
            border-color: var(--auth-danger);
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.08);
        }

        .password-toggle {
            position: absolute;
            inset-inline-end: 8px;
            top: 50%;
            width: 38px;
            height: 38px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            transform: translateY(-50%);
        }

        .password-toggle:hover,
        .password-toggle:focus {
            background: #f1f5f9;
            color: var(--auth-blue-dark);
            outline: none;
        }

        .password-toggle i {
            position: static;
            transform: none;
            pointer-events: auto;
        }

        .password-help {
            margin: 2px 0 0;
            color: var(--auth-muted);
            font-size: 0.86rem;
            line-height: 1.5;
        }

        .password-error {
            margin: 0;
            color: var(--auth-danger);
            font-size: 0.86rem;
            font-weight: 700;
            line-height: 1.45;
        }

        .password-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 20px;
            padding: 13px 14px;
            border-radius: 8px;
            font-weight: 700;
            line-height: 1.5;
        }

        .password-alert--success {
            background: #ecfdf5;
            color: var(--auth-success);
            border: 1px solid #bbf7d0;
        }

        .password-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            min-height: 52px;
            margin-top: 4px;
            border: 0;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--auth-blue), var(--auth-cyan));
            color: #ffffff;
            cursor: pointer;
            font: inherit;
            font-weight: 800;
            box-shadow: 0 16px 30px rgba(37, 99, 235, 0.22);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .password-submit:hover,
        .password-submit:focus {
            transform: translateY(-1px);
            box-shadow: 0 20px 36px rgba(37, 99, 235, 0.28);
            outline: none;
        }

        .password-auth-footer {
            margin-top: 24px;
            color: var(--auth-muted);
            font-size: 0.95rem;
            text-align: center;
        }

        .password-auth-footer a {
            color: var(--auth-blue-dark);
            font-weight: 800;
            text-decoration: none;
        }

        .password-auth-footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 880px) {
            .password-auth-nav__inner {
                min-height: auto;
                padding: 14px 0;
                flex-wrap: wrap;
            }

            .password-auth-brand img {
                width: 54px;
                height: 44px;
            }

            .password-auth-menu {
                width: 100%;
                justify-content: flex-start;
                overflow-x: auto;
                padding-bottom: 2px;
            }

            .password-auth-main {
                min-height: auto;
                padding: 24px 0;
            }

            .password-auth-shell {
                grid-template-columns: 1fr;
            }

            .password-auth-visual {
                min-height: 240px;
                padding: 28px;
            }

            .password-auth-visual h1 {
                font-size: 2rem;
            }
        }

        @media (max-width: 560px) {
            .password-auth-nav__inner,
            .password-auth-main {
                width: min(100% - 20px, 1080px);
            }

            .password-auth-link {
                display: none;
            }

            .password-auth-action,
            .password-auth-nav .language-btn {
                min-width: auto;
                padding-inline: 12px;
            }

            .password-auth-card {
                padding: 24px 18px;
            }

            .password-auth-visual {
                padding: 24px 18px;
            }

            .password-input-wrap input {
                padding-inline-start: 44px;
                padding-inline-end: 44px;
            }
        }
    </style>
</head>
<body class="password-auth-page">
    <header class="password-auth-nav">
        <div class="password-auth-nav__inner">
            <a class="password-auth-brand" href="{{ route('home') }}" aria-label="{{ config('app.name', 'Speeda') }}">
                <img src="{{ asset('images/New_logo.png') }}" alt="{{ __('general.logo_alt') }}">
                <span>{{ config('app.name', 'Speeda') }}</span>
            </a>

            <nav class="password-auth-menu" aria-label="{{ __('general.main_navigation') }}">
                <a class="password-auth-link" href="{{ route('home') }}">
                    <i class="fas fa-home" aria-hidden="true"></i>
                    {{ __('general.home') }}
                </a>
                <a class="password-auth-link" href="{{ route('categories') }}">
                    <i class="fas fa-th-large" aria-hidden="true"></i>
                    {{ __('general.categories') }}
                </a>
                @include('components.language-switcher')
                <a class="password-auth-action" href="{{ route('login') }}?tab=login">
                    <i class="fas fa-right-to-bracket" aria-hidden="true"></i>
                    {{ __('general.login') }}
                </a>
                <a class="password-auth-action" href="{{ route('register') }}?form=register">
                    <i class="fas fa-user-plus" aria-hidden="true"></i>
                    {{ __('general.register') }}
                </a>
            </nav>
        </div>
    </header>

    <main class="password-auth-main">
        <div class="password-auth-shell">
            <aside class="password-auth-visual" aria-hidden="true">
                <div class="password-auth-mark">
                    <img src="{{ asset('images/New_logo.png') }}" alt="">
                    <span>{{ config('app.name', 'Speeda') }}</span>
                </div>
                <div>
                    <h1>{{ $visualTitle ?? ($pageTitle ?? __('auth.authentication')) }}</h1>
                    <p>{{ $visualText ?? __('auth.password_reset_visual_text') }}</p>
                </div>
            </aside>

            <section class="password-auth-card" aria-labelledby="password-auth-title">
                <div class="password-auth-kicker">
                    <i class="fas fa-shield-halved" aria-hidden="true"></i>
                    {{ __('auth.secure_account_access') }}
                </div>
                <h2 id="password-auth-title">{{ $pageTitle ?? __('auth.authentication') }}</h2>
                <p class="password-auth-card__intro">{{ $pageIntro ?? '' }}</p>

                @if (session('status'))
                    <div class="password-alert password-alert--success" role="status">
                        <i class="fas fa-circle-check" aria-hidden="true"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @yield('content')
            </section>
        </div>
    </main>

    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.getAttribute('data-password-toggle'));
                if (!input) return;

                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                button.setAttribute('aria-pressed', String(isPassword));
                button.querySelector('i')?.classList.toggle('fa-eye', !isPassword);
                button.querySelector('i')?.classList.toggle('fa-eye-slash', isPassword);
            });
        });
    </script>
</body>
</html>
