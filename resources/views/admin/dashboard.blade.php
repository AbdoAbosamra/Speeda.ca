@extends('layouts.app')

@section('content')

    <!-- Main Content Area -->
    <div class="admin-content-wrapper" style="margin-left: 0 !important;">
        <div
            style="background-color: #f8fafc; background-image: radial-gradient(#e2e8f0 1px, transparent 1px); background-size: 24px 24px; min-height: 100vh;">

            <div class="container py-5">
                <!-- Header Section -->
                <div class="mb-5 animate-fade-in">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <span
                                class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill mb-2 fw-semibold">
                                <i class="fas fa-home me-1"></i> Dashboard
                            </span>
                            <h1 class="h2 fw-bold mb-1 text-dark">{{ __('admin.dashboard') }}</h1>
                            <p class="text-muted mb-0 fs-5">{{ __('admin.welcome_back') }},
                                <span class="fw-bold text-dark">{{ auth()->user()->name }}</span> 👋
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Visitor Statistics Cards -->
                <div class="row g-4 mb-5">
                    <!-- Live Visitors Card -->
                    <div class="col-md-3 col-sm-6">
                        <div class="card h-100 border-0 shadow-sm hover-lift animate-slide-up"
                            style="transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);">
                            <div class="card-body p-4 position-relative overflow-hidden">
                                <div
                                    style="position: absolute; right: -20px; top: -20px; width: 100px; height: 100px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); opacity: 0.1; border-radius: 50%;">
                                </div>

                                <div class="d-flex align-items-center mb-3">
                                    <div class="icon-box rounded-3 me-3 d-flex align-items-center justify-content-center"
                                        style="width: 56px; height: 56px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);">
                                        <i class="fas fa-users text-white fa-lg"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted mb-0 fw-bold text-uppercase"
                                            style="font-size: 0.75rem; letter-spacing: 1px;">
                                            {{ __('admin.live_visitors_label') }}</h6>
                                    </div>
                                </div>
                                <h2 class="fw-bold mb-0 display-6 live-count" style="color: #4c51bf;">
                                    {{ $stats['liveVisitors'] ?? 0 }}</h2>
                                <div class="mt-2 text-success small fw-semibold">
                                    <span class="badge bg-success bg-opacity-20 text-success">
                                        <i class="fas fa-circle fa-xxs me-1" style="animation: pulse 2s infinite;"></i>
                                        {{ __('admin.active_now') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Visitors Today Card -->
                    <div class="col-md-3 col-sm-6">
                        <div class="card h-100 border-0 shadow-sm hover-lift animate-slide-up"
                            style="transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); animation-delay: 0.1s;">
                            <div class="card-body p-4 position-relative overflow-hidden">
                                <div
                                    style="position: absolute; right: -20px; top: -20px; width: 100px; height: 100px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); opacity: 0.1; border-radius: 50%;">
                                </div>

                                <div class="d-flex align-items-center mb-3">
                                    <div class="icon-box rounded-3 me-3 d-flex align-items-center justify-content-center"
                                        style="width: 56px; height: 56px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); box-shadow: 0 8px 16px rgba(240, 147, 251, 0.3);">
                                        <i class="fas fa-calendar-day text-white fa-lg"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted mb-0 fw-bold text-uppercase"
                                            style="font-size: 0.75rem; letter-spacing: 1px;">
                                            {{ __('admin.time_period_today') }}</h6>
                                    </div>
                                </div>
                                <h2 class="fw-bold mb-0 display-6" style="color: #be185d;">
                                    {{ $stats['visitorsToday'] ?? 0 }}</h2>
                                <div class="mt-2 text-muted small fw-semibold">
                                    {{ __('admin.unique_visitors_label') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Last 7 Days Card -->
                    <div class="col-md-3 col-sm-6">
                        <div class="card h-100 border-0 shadow-sm hover-lift animate-slide-up"
                            style="transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); animation-delay: 0.2s;">
                            <div class="card-body p-4 position-relative overflow-hidden">
                                <div
                                    style="position: absolute; right: -20px; top: -20px; width: 100px; height: 100px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); opacity: 0.1; border-radius: 50%;">
                                </div>

                                <div class="d-flex align-items-center mb-3">
                                    <div class="icon-box rounded-3 me-3 d-flex align-items-center justify-content-center"
                                        style="width: 56px; height: 56px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); box-shadow: 0 8px 16px rgba(79, 172, 254, 0.3);">
                                        <i class="fas fa-calendar-week text-white fa-lg"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted mb-0 fw-bold text-uppercase"
                                            style="font-size: 0.75rem; letter-spacing: 1px;">
                                            {{ __('admin.time_period_last_7_days') }}</h6>
                                    </div>
                                </div>
                                <h2 class="fw-bold mb-0 display-6" style="color: #0369a1;">{{ $stats['last7Days'] ?? 0 }}
                                </h2>
                                <div class="mt-2 text-success small fw-semibold">
                                    <i class="fas fa-check-circle me-1"></i> {{ __('admin.unique_visitors_label') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Last 30 Days Card -->
                    <div class="col-md-3 col-sm-6">
                        <div class="card h-100 border-0 shadow-sm hover-lift animate-slide-up"
                            style="transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); animation-delay: 0.3s;">
                            <div class="card-body p-4 position-relative overflow-hidden">
                                <div
                                    style="position: absolute; right: -20px; top: -20px; width: 100px; height: 100px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); opacity: 0.1; border-radius: 50%;">
                                </div>

                                <div class="d-flex align-items-center mb-3">
                                    <div class="icon-box rounded-3 me-3 d-flex align-items-center justify-content-center"
                                        style="width: 56px; height: 56px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); box-shadow: 0 8px 16px rgba(67, 233, 123, 0.3);">
                                        <i class="fas fa-calendar-alt text-white fa-lg"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted mb-0 fw-bold text-uppercase"
                                            style="font-size: 0.75rem; letter-spacing: 1px;">
                                            {{ __('admin.time_period_last_30_days') }}</h6>
                                    </div>
                                </div>
                                <h2 class="fw-bold mb-0 display-6" style="color: #059669;">{{ $stats['last30Days'] ?? 0 }}
                                </h2>
                                <div class="mt-2 text-muted small fw-semibold">
                                    {{ __('admin.unique_visitors_label') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Last 12 Months Card -->
                    <div class="col-md-3 col-sm-6">
                        <div class="card h-100 border-0 shadow-sm hover-lift animate-slide-up"
                            style="transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); animation-delay: 0.4s;">
                            <div class="card-body p-4 position-relative overflow-hidden">
                                <div
                                    style="position: absolute; right: -20px; top: -20px; width: 100px; height: 100px; background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); opacity: 0.1; border-radius: 50%;">
                                </div>

                                <div class="d-flex align-items-center mb-3">
                                    <div class="icon-box rounded-3 me-3 d-flex align-items-center justify-content-center"
                                        style="width: 56px; height: 56px; background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); box-shadow: 0 8px 16px rgba(250, 112, 154, 0.3);">
                                        <i class="fas fa-chart-line text-white fa-lg"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted mb-0 fw-bold text-uppercase"
                                            style="font-size: 0.75rem; letter-spacing: 1px;">
                                            {{ __('admin.time_period_last_12_months') }}</h6>
                                    </div>
                                </div>
                                <h2 class="fw-bold mb-0 display-6" style="color: #d97706;">{{ $stats['last12Months'] ?? 0 }}
                                </h2>
                                <div class="mt-2 text-muted small fw-semibold">
                                    {{ __('admin.unique_visitors_label') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Visitors (All-Time) Card -->
                    <div class="col-md-3 col-sm-6">
                        <div class="card h-100 border-0 shadow-sm hover-lift animate-slide-up"
                            style="transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); animation-delay: 0.5s;">
                            <div class="card-body p-4 position-relative overflow-hidden">
                                <div
                                    style="position: absolute; right: -20px; top: -20px; width: 100px; height: 100px; background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); opacity: 0.1; border-radius: 50%;">
                                </div>

                                <div class="d-flex align-items-center mb-3">
                                    <div class="icon-box rounded-3 me-3 d-flex align-items-center justify-content-center"
                                        style="width: 56px; height: 56px; background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); box-shadow: 0 8px 16px rgba(168, 237, 234, 0.3);">
                                        <i class="fas fa-infinity text-white fa-lg"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted mb-0 fw-bold text-uppercase"
                                            style="font-size: 0.75rem; letter-spacing: 1px;">
                                            {{ __('admin.time_period_all_time') }}</h6>
                                    </div>
                                </div>
                                <h2 class="fw-bold mb-0 display-6" style="color: #0891b2;">
                                    {{ $stats['totalVisitors'] ?? 0 }}</h2>
                                <div class="mt-2 text-muted small fw-semibold">
                                    {{ __('admin.total_unique_visitors_label') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions - Interactive Command Cards -->
                <div class="row g-4 mb-5">
                    <div class="col-12">
                        <h5 class="fw-bold text-dark mb-3">Quick Actions</h5>
                    </div>

                    <div class="col-md-4">
                        <a href="{{ route('admin.locations') }}"
                            class="action-card text-decoration-none h-100 d-block p-4 rounded-4 border-0 shadow-sm position-relative overflow-hidden group"
                            style="background: white; transition: all 0.3s ease;">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="width: 50px; height: 50px; background: rgba(79, 172, 254, 0.1); color: #4facfe; transition: all 0.3s;">
                                    <i class="fas fa-map-marker-alt fa-lg"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1 text-dark">{{ __('admin.manage_locations') }}</h6>
                                    <small class="text-muted">{{ $stats['activeLocations'] ?? 0 }} Active /
                                        {{ $stats['totalLocations'] ?? 0 }} Total</small>
                                </div>
                                <i class="fas fa-chevron-right text-muted opacity-0 group-hover:opacity-100 transition-opacity"
                                    style="transform: translateX(-10px); transition: all 0.3s;"></i>
                            </div>
                            <div class="position-absolute bottom-0 start-0 h-1 bg-info"
                                style="width: 0%; transition: width 0.3s ease; background: linear-gradient(90deg, #4facfe, #00f2fe);">
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4">
                        <a href="{{ route('admin.categories') }}"
                            class="action-card text-decoration-none h-100 d-block p-4 rounded-4 border-0 shadow-sm position-relative overflow-hidden group"
                            style="background: white; transition: all 0.3s ease;">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="width: 50px; height: 50px; background: rgba(67, 233, 123, 0.1); color: #43e97b; transition: all 0.3s;">
                                    <i class="fas fa-folder fa-lg"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1 text-dark">{{ __('admin.manage_categories') }}</h6>
                                    <small class="text-muted">{{ $stats['activeCategories'] ?? 0 }} Active /
                                        {{ $stats['totalCategories'] ?? 0 }} Total</small>
                                </div>
                                <i class="fas fa-chevron-right text-muted opacity-0 group-hover:opacity-100 transition-opacity"
                                    style="transform: translateX(-10px); transition: all 0.3s;"></i>
                            </div>
                            <div class="position-absolute bottom-0 start-0 h-1 bg-success"
                                style="width: 0%; transition: width 0.3s ease; background: linear-gradient(90deg, #43e97b, #38f9d7);">
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4">
                        <a href="{{ route('admin.visitors') }}"
                            class="action-card text-decoration-none h-100 d-block p-4 rounded-4 border-0 shadow-sm position-relative overflow-hidden group"
                            style="background: white; transition: all 0.3s ease;">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="width: 50px; height: 50px; background: rgba(139, 92, 246, 0.1); color: #8b5cf6; transition: all 0.3s;">
                                    <i class="fas fa-analytics fa-lg"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1 text-dark">{{ __('admin.visitor_analytics_label') }}</h6>
                                    <small class="text-muted">{{ __('admin.visitor_analytics_description_short') }}</small>
                                </div>
                                <i class="fas fa-chevron-right text-muted opacity-0 group-hover:opacity-100 transition-opacity"
                                    style="transform: translateX(-10px); transition: all 0.3s;"></i>
                            </div>
                            <div class="position-absolute bottom-0 start-0 h-1"
                                style="width: 0%; transition: width 0.3s ease; background: linear-gradient(90deg, #8b5cf6, #6366f1);">
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Moderation Queue Section -->
                <div class="row g-4 mb-5">
                    <div class="col-12">
                        <h5 class="fw-bold text-dark mb-3">{{ __('admin.moderation_queue') }}</h5>
                    </div>

                    <!-- Pending Reviews Card -->
                    <div class="col-md-4">
                        <a href="{{ route('admin.reviews', ['status' => 'pending']) }}"
                            class="action-card text-decoration-none h-100 d-block p-4 rounded-4 border-0 shadow-sm position-relative overflow-hidden group"
                            style="background: white; transition: all 0.3s ease;">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="width: 50px; height: 50px; background: rgba(251, 191, 36, 0.1); color: #f59e0b; transition: all 0.3s;">
                                    <i class="fas fa-star fa-lg"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1 text-dark">{{ __('admin.review_moderation') }}</h6>
                                    <small class="text-muted">
                                        @if(($stats['pendingReviews'] ?? 0) > 0)
                                            <span class="badge bg-warning text-dark me-1">{{ $stats['pendingReviews'] }}</span>
                                            {{ __('admin.awaiting_approval') }}
                                        @else
                                            <i class="fas fa-check-circle text-success me-1"></i> No pending reviews
                                        @endif
                                    </small>
                                </div>
                                <i class="fas fa-chevron-right text-muted" style="transition: all 0.3s;"></i>
                            </div>
                            <div class="position-absolute bottom-0 start-0 h-1"
                                style="width: 0%; transition: width 0.3s ease; background: linear-gradient(90deg, #fbbf24, #f59e0b);">
                            </div>
                        </a>
                    </div>

                    <!-- Pending Comments Card -->
                    <div class="col-md-4">
                        <a href="{{ route('admin.comments', ['status' => 'pending']) }}"
                            class="action-card text-decoration-none h-100 d-block p-4 rounded-4 border-0 shadow-sm position-relative overflow-hidden group"
                            style="background: white; transition: all 0.3s ease;">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="width: 50px; height: 50px; background: rgba(59, 130, 246, 0.1); color: #3b82f6; transition: all 0.3s;">
                                    <i class="fas fa-comments fa-lg"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1 text-dark">{{ __('admin.comment_moderation') }}</h6>
                                    <small class="text-muted">
                                        @if(($stats['pendingComments'] ?? 0) > 0)
                                            <span class="badge bg-warning text-dark me-1">{{ $stats['pendingComments'] }}</span>
                                            {{ __('admin.awaiting_approval') }}
                                        @else
                                            <i class="fas fa-check-circle text-success me-1"></i> No pending comments
                                        @endif
                                    </small>
                                </div>
                                <i class="fas fa-chevron-right text-muted" style="transition: all 0.3s;"></i>
                            </div>
                            <div class="position-absolute bottom-0 start-0 h-1"
                                style="width: 0%; transition: width 0.3s ease; background: linear-gradient(90deg, #3b82f6, #6366f1);">
                            </div>
                        </a>
                    </div>

                    <!-- New Users Today Card -->
                    <div class="col-md-4">
                        <div class="action-card text-decoration-none h-100 d-block p-4 rounded-4 border-0 shadow-sm position-relative overflow-hidden"
                            style="background: white;">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="width: 50px; height: 50px; background: rgba(16, 185, 129, 0.1); color: #10b981;">
                                    <i class="fas fa-user-plus fa-lg"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1 text-dark">{{ __('admin.new_users_today') }}</h6>
                                    <small class="text-muted">
                                        <span class="badge bg-success me-1">{{ $stats['newUsersToday'] ?? 0 }}</span>
                                        registered today
                                    </small>
                                </div>
                            </div>
                            <div class="position-absolute bottom-0 start-0 h-1"
                                style="width: 0%; background: linear-gradient(90deg, #10b981, #34d399);"></div>
                        </div>
                    </div>
                </div>

                <!-- Cache Clear Button -->
                <div class="row mb-5">
                    <div class="col-md-4">
                        <div class="card border-0 bg-transparent">
                            <div class="card-body p-0">
                                <form action="{{ route('admin.clear-cache') }}" method="POST" class="d-flex">
                                    @csrf
                                    <button type="submit"
                                        class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2 py-3 rounded-3 fw-bold"
                                        style="border: 2px dashed #cbd5e1; color: #64748b; transition: all 0.3s;">
                                        <i class="fas fa-broom"></i> {{ __('admin.clear_caches') }}
                                    </button>
                                </form>
                                <small class="text-muted d-block text-center mt-2 fw-medium" style="font-size: 0.8rem;">
                                    {{ __('admin.clear_cache_help_text') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Modern CSS Styles -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

        :root {
            --primary: #6366f1;
            --secondary: #64748b;
            --success: #10b981;
            --bg-body: #f8fafc;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            --card-hover-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: #1e293b;
        }

        /* Card Hover Effects */
        .hover-lift {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-hover-shadow) !important;
        }

        .hover-lift:hover .icon-box {
            transform: scale(1.1);
        }

        .icon-box {
            transition: transform 0.3s ease;
        }

        /* Action Cards Styling */
        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--card-hover-shadow);
        }

        .action-card:hover div[class*="bg-opacity"] {
            background: var(--primary) !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .action-card:hover .h-1 {
            width: 100% !important;
        }

        .action-card:hover .fa-chevron-right {
            opacity: 1 !important;
            transform: translateX(0) !important;
            color: var(--secondary) !important;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.6s ease-out forwards;
        }

        .animate-slide-up {
            opacity: 0;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* Cache Button Hover */
        button[type="submit"]:hover {
            background-color: #f1f5f9;
            border-color: #94a3b8 !important;
            color: #334155;
        }

        /* Live count refresh */
        .live-count {
            animation: fadeIn 0.3s ease-in-out;
        }
    </style>

    <script>
        // Update live count every 30 seconds
        (function () {
            function updateLiveCount() {
                fetch('{{ route("admin.visitors.live-count") }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const elem = document.querySelector('.live-count');
                            if (elem) {
                                elem.textContent = data.count;
                            }
                        }
                    })
                    .catch(error => console.error('Error fetching live count:', error));
            }

            // Update immediately and then every 30 seconds
            updateLiveCount();
            setInterval(updateLiveCount, 30000);
        })();
    </script>

@endsection