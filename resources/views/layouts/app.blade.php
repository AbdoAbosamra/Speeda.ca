<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ in_array(app()->getLocale(), ['ar', 'he', 'ur', 'fa']) ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('seo.meta')
    <link rel="icon" type="image/png" href="{{ asset('images/New_logo.png') }}">

    <!-- Google Font: Inter (clean, modern) -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap"
        rel="stylesheet">

    <!-- Bootstrap + Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    @stack('styles')
    @livewireStyles

    {{-- All styles now in resources/css/app.css --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="antialiased bg-light @if(request()->routeIs('admin.*')) admin-route-active @endif">

    <div class="min-vh-100 d-flex flex-column">
        @if(!request()->routeIs('admin.*'))
            @include('layouts.navigation')
        @endif

        {{-- Floating admin button – refined pill --}}
        @auth
            @if(auth()->user()->isAdmin() && !request()->routeIs('admin.*'))
                <a href="{{ route('admin.dashboard') }}" class="admin-quick-link text-white"
                    title="{{ __('admin.dashboard') }}">
                    <i class="fas fa-shield-alt fa-fw"></i>
                    <span>{{ __('admin.dashboard') }}</span>
                </a>
            @endif
        @endauth

        @if(request()->routeIs('admin.*'))
            {{-- Admin Top Bar – standalone component --}}
            <x-admin-top-bar :unreadNotifications="0" />
        @endif

        {{-- Page Header (optional) --}}
        @isset($header)
            <header class="bg-white border-bottom py-3">
                <div class="container">
                    <h2 class="h4 fw-semibold mb-0">{{ $header }}</h2>
                </div>
            </header>
        @endisset

        {{-- Main Content --}}
        <main class="py-4 flex-grow-1">
            <div class="container">
                <x-error-handler />
            </div>

            {{-- Provider gamification (popup once, then compact reminder) - never show on admin routes --}}
            @auth
                @if(!request()->routeIs('admin.*') && auth()->user()->serviceProvider)
                    <x-profile-completion-notification-center :provider="auth()->user()->serviceProvider" />
                @endif
            @endauth

            @yield('content')
        </main>

        {{-- Toast Notifications --}}
        <x-toast-notification />
    </div>

    @if(!request()->routeIs('admin.*'))
        @include('layouts.footer')
    @endif

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
    @livewireScripts
</body>

</html>