<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ in_array(app()->getLocale(), ['ar', 'he', 'ur', 'fa']) ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Speeda') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/main-logo.png') }}"> <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap + Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="font-sans antialiased bg-light">

    <div class="min-h-screen">
        @if(!request()->routeIs('admin.*'))
            @include('layouts.navigation')
        @endif
        {{-- Quick admin button for authenticated admins on public pages --}}
        @auth
            @if(auth()->user()->isAdmin() && !request()->routeIs('admin.*'))
                <a href="{{ route('admin.dashboard') }}" class="admin-quick-link" title="{{ __('admin.dashboard') }}"
                    style="position: fixed; right: 1rem; bottom: 1rem; z-index: 2000; background: linear-gradient(135deg,#6366f1,#8b5cf6); color: white; padding: 0.65rem 0.85rem; border-radius: 12px; box-shadow: 0 8px 24px rgba(99,102,241,0.25); display: flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                    <i class="fas fa-shield-alt"></i>
                    <span style="font-weight:700">{{ __('admin.dashboard') }}</span>
                </a>
            @endif
        @endauth

        @if(request()->routeIs('admin.*'))
            <style>
                .sp-nav,
                nav:not(.admin-sidebar),
                header[class*="nav"] {
                    display: none !important;
                }
            </style>

            {{-- Admin top quick-bar: links between admin pages and back to public site --}}
            <div
                style="position:fixed; left:280px; right:0; top:0; z-index:1100; background: linear-gradient(90deg,#ffffff, #f8fafc); border-bottom:1px solid #e6edf7; padding:0.6rem 1rem; display:flex; gap:0.5rem; align-items:center;">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-primary"
                    style="border-radius:10px; padding:0.45rem 0.75rem; font-weight:600;">
                    <i class="fas fa-tachometer-alt me-1"></i> {{ __('admin.dashboard') }}
                </a>
                <a href="{{ route('admin.locations') }}" class="btn btn-sm btn-outline-secondary"
                    style="border-radius:10px; padding:0.45rem 0.75rem;">
                    <i class="fas fa-map-marker-alt me-1"></i> {{ __('admin.manage_locations') }}
                </a>
                <a href="{{ route('admin.categories') }}" class="btn btn-sm btn-outline-secondary"
                    style="border-radius:10px; padding:0.45rem 0.75rem;">
                    <i class="fas fa-folder me-1"></i> {{ __('admin.manage_categories') }}
                </a>
                <a href="{{ route('admin.visitors') }}" class="btn btn-sm btn-outline-secondary"
                    style="border-radius:10px; padding:0.45rem 0.75rem;">
                    <i class="fas fa-chart-line me-1"></i> {{ __('admin.analytics_label') }}
                </a>
                <a href="{{ route('admin.activity_logs') }}" class="btn btn-sm btn-outline-secondary"
                    style="border-radius:10px; padding:0.45rem 0.75rem;">
                    <i class="fas fa-history me-1"></i> {{ __('admin.activity_logs') }}
                </a>
                <div style="flex:1"></div>
                <a href="{{ route('home') }}" class="btn btn-sm btn-light"
                    style="border-radius:10px; padding:0.45rem 0.75rem; border:1px solid #e6edf7;">
                    <i class="fas fa-external-link-alt me-1"></i> {{ __('admin.view_site') ?? 'View Site' }}
                </a>
            </div>
        @endif

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white shadow-sm border-bottom">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                    <h2 class="h4 fw-bold text-gray-800">{{ $header }}</h2>
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main class="py-4">
            {{-- Unified Error Handler --}}
            <div class="container">
                <x-error-handler />
            </div>

            @yield('content')
        </main>

        {{-- Global Toast Notifications --}}
        <x-toast-notification />
    </div>

    @if(!request()->routeIs('admin.*'))
        @include('layouts.footer')
    @endif

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>

</html>