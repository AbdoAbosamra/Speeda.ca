@extends('layouts.app')

@push('styles')
    @vite('resources/css/app.css')
@endpush

@section('content')
    @php
        $blogRouteExists = Route::has('admin.blog.posts.index');
        $blogDestination = $blogRouteExists ? route('admin.blog.posts.index') : route('blogs.index');
        $blogCardTitle = $blogRouteExists ? 'Blog Management' : 'Blog Library';
        $blogCardMeta = $blogRouteExists
            ? 'Create and manage articles in English, Arabic, and French.'
            : 'Review the live multilingual blog experience until the admin editor is wired.';
    @endphp

    <div class="admin-dashboard-page" dir="ltr">
        <div class="container py-4 py-lg-5">
            <section class="admin-dashboard-hero">
                <div>
                    <div class="admin-dashboard-kicker">
                        <i class="fas fa-chart-line"></i>
                        <span>Operations overview</span>
                    </div>
                    <h1 class="admin-dashboard-title">Admin Dashboard</h1>
                    <p class="admin-dashboard-subtitle">
                        Welcome back, <strong>{{ auth()->user()->name }}</strong>. This workspace keeps analytics,
                        moderation, content, and operations close at hand.
                    </p>
                </div>

                <div class="admin-hero-actions">
                    <a href="{{ $blogDestination }}" class="admin-btn admin-btn-primary">
                        <i class="fas fa-pen-nib"></i>
                        <span>{{ $blogCardTitle }}</span>
                    </a>
                    <a href="{{ route('admin.notifications.index') }}" class="admin-btn admin-btn-secondary">
                        <i class="fas fa-bell"></i>
                        <span>Notifications</span>
                    </a>
                </div>
            </section>

            <section class="admin-section-block">
                <div class="admin-section-heading">
                    <div>
                        <p class="admin-section-eyebrow">Traffic</p>
                        <h2 class="admin-section-title">Visitor snapshot</h2>
                    </div>
                </div>

                <div class="admin-stat-grid">
                    <article class="admin-stat-card admin-stat-card-live">
                        <div class="admin-stat-head">
                            <span class="admin-stat-icon"><i class="fas fa-users"></i></span>
                            <span class="admin-stat-label">Live Visitors</span>
                        </div>
                        <strong class="admin-stat-value live-count">{{ $stats['liveVisitors'] ?? 0 }}</strong>
                        <span class="admin-stat-foot admin-stat-pill">
                            <span class="admin-live-dot"></span>
                            Active now
                        </span>
                    </article>

                    <article class="admin-stat-card">
                        <div class="admin-stat-head">
                            <span class="admin-stat-icon admin-stat-icon-rose"><i class="fas fa-calendar-day"></i></span>
                            <span class="admin-stat-label">Today</span>
                        </div>
                        <strong class="admin-stat-value">{{ $stats['visitorsToday'] ?? 0 }}</strong>
                        <span class="admin-stat-foot">Unique visitors</span>
                    </article>

                    <article class="admin-stat-card">
                        <div class="admin-stat-head">
                            <span class="admin-stat-icon admin-stat-icon-sky"><i class="fas fa-calendar-week"></i></span>
                            <span class="admin-stat-label">Last 7 Days</span>
                        </div>
                        <strong class="admin-stat-value">{{ $stats['last7Days'] ?? 0 }}</strong>
                        <span class="admin-stat-foot">Unique visitors</span>
                    </article>

                    <article class="admin-stat-card">
                        <div class="admin-stat-head">
                            <span class="admin-stat-icon admin-stat-icon-teal"><i class="fas fa-infinity"></i></span>
                            <span class="admin-stat-label">All Time</span>
                        </div>
                        <strong class="admin-stat-value">{{ $stats['totalVisitors'] ?? 0 }}</strong>
                        <span class="admin-stat-foot">Total unique visitors</span>
                    </article>
                </div>
            </section>

            <section class="admin-section-block">
                <div class="admin-section-heading">
                    <div>
                        <p class="admin-section-eyebrow">Operations</p>
                        <h2 class="admin-section-title">Core management</h2>
                    </div>
                </div>

                <div class="admin-command-grid">
                    <a href="{{ route('admin.locations') }}" class="admin-command-card">
                        <span class="admin-command-icon admin-command-indigo"><i class="fas fa-map-marker-alt"></i></span>
                        <div class="admin-command-content">
                            <h3>Manage Locations</h3>
                            <p>{{ $stats['activeLocations'] ?? 0 }} active of {{ $stats['totalLocations'] ?? 0 }} total.</p>
                        </div>
                        <i class="fas fa-arrow-right admin-command-arrow"></i>
                    </a>

                    <a href="{{ route('admin.categories') }}" class="admin-command-card">
                        <span class="admin-command-icon admin-command-emerald"><i class="fas fa-folder"></i></span>
                        <div class="admin-command-content">
                            <h3>Manage Categories</h3>
                            <p>{{ $stats['activeCategories'] ?? 0 }} active of {{ $stats['totalCategories'] ?? 0 }} total.</p>
                        </div>
                        <i class="fas fa-arrow-right admin-command-arrow"></i>
                    </a>

                    <a href="{{ route('admin.users') }}" class="admin-command-card">
                        <span class="admin-command-icon admin-command-blue"><i class="fas fa-users-cog"></i></span>
                        <div class="admin-command-content">
                            <h3>Users Management</h3>
                            <p>{{ $stats['totalUsers'] ?? 0 }} total user accounts.</p>
                        </div>
                        <i class="fas fa-arrow-right admin-command-arrow"></i>
                    </a>

                    <a href="{{ route('admin.reviews') }}" class="admin-command-card">
                        <span class="admin-command-icon admin-command-amber"><i class="fas fa-star"></i></span>
                        <div class="admin-command-content">
                            <h3>Reviews Management</h3>
                            <p>{{ $stats['totalReviews'] ?? 0 }} reviews across all providers.</p>
                        </div>
                        <i class="fas fa-arrow-right admin-command-arrow"></i>
                    </a>

                    <a href="{{ route('admin.provider_activity_monitor.index') }}" class="admin-command-card">
                        <span class="admin-command-icon admin-command-lime"><i class="fab fa-whatsapp"></i></span>
                        <div class="admin-command-content">
                            <h3>WhatsApp Click Tracking</h3>
                            <p>Monitor provider interest and outbound engagement activity.</p>
                        </div>
                        <i class="fas fa-arrow-right admin-command-arrow"></i>
                    </a>

                    <a href="{{ $blogDestination }}" class="admin-command-card">
                        <span class="admin-command-icon admin-command-violet"><i class="fas fa-language"></i></span>
                        <div class="admin-command-content">
                            <h3>{{ $blogCardTitle }}</h3>
                            <p>{{ $blogCardMeta }}</p>
                        </div>
                        <i class="fas fa-arrow-right admin-command-arrow"></i>
                    </a>
                </div>
            </section>

            <section class="admin-support-grid">
                <div class="admin-section-block">
                    <div class="admin-section-heading">
                        <div>
                            <p class="admin-section-eyebrow">Review queue</p>
                            <h2 class="admin-section-title">Moderation and approvals</h2>
                        </div>
                    </div>

                    <div class="admin-split-cards">
                        <a href="{{ route('admin.reviews', ['status' => 'pending']) }}" class="admin-panel-card admin-panel-card-action">
                            <div class="admin-panel-card-head">
                                <span class="admin-command-icon admin-command-amber"><i class="fas fa-shield-alt"></i></span>
                                <div>
                                    <h3>Review Moderation</h3>
                                    <p>Handle pending review approvals and keep quality high.</p>
                                </div>
                            </div>
                            <div class="admin-panel-card-foot">
                                @if(($stats['pendingReviews'] ?? 0) > 0)
                                    <span class="admin-status-badge">{{ $stats['pendingReviews'] }} pending</span>
                                @else
                                    <span class="admin-status-ok"><i class="fas fa-check-circle"></i> All clear</span>
                                @endif
                                <span class="admin-link-inline">Open queue <i class="fas fa-arrow-right"></i></span>
                            </div>
                        </a>

                        <article class="admin-panel-card">
                            <div class="admin-panel-card-head">
                                <span class="admin-command-icon admin-command-emerald"><i class="fas fa-user-plus"></i></span>
                                <div>
                                    <h3>New Users Today</h3>
                                    <p>Fresh registrations tracked for today.</p>
                                </div>
                            </div>
                            <strong class="admin-panel-value">{{ $stats['newUsersToday'] ?? 0 }}</strong>
                        </article>
                    </div>
                </div>

                <div class="admin-sidebar-stack">
                    <section class="admin-section-block">
                        <div class="admin-panel-card">
                            <div class="admin-panel-card-head">
                                <span class="admin-command-icon admin-command-violet"><i class="fas fa-paper-plane"></i></span>
                                <div>
                                    <h3>Push Notifications</h3>
                                    <p>Send and manage provider communication from one place.</p>
                                </div>
                            </div>
                            <a href="{{ route('admin.notifications.index') }}" class="admin-btn admin-btn-secondary admin-btn-full">
                                <i class="fas fa-cog"></i>
                                <span>Manage Notifications</span>
                            </a>
                        </div>
                    </section>

                    <section class="admin-section-block">
                        <div class="admin-panel-card">
                            <div class="admin-panel-card-head">
                                <span class="admin-command-icon admin-command-slate"><i class="fas fa-broom"></i></span>
                                <div>
                                    <h3>Maintenance</h3>
                                    <p>Refresh cached application data after structural admin changes.</p>
                                </div>
                            </div>
                            <form action="{{ route('admin.clear-cache') }}" method="POST">
                                @csrf
                                <button type="submit" class="admin-btn admin-btn-ghost admin-btn-full">
                                    <i class="fas fa-rotate"></i>
                                    <span>Clear Caches</span>
                                </button>
                            </form>
                        </div>
                    </section>
                </div>
            </section>
        </div>
    </div>

    <script>
        (() => {
            let refreshTimer = null;

            async function updateLiveCount() {
                if (document.hidden) {
                    return;
                }

                try {
                    const response = await fetch('{{ route('admin.visitors.live-count') }}', {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        return;
                    }

                    const data = await response.json();
                    const elem = document.querySelector('.live-count');

                    if (data.success && elem) {
                        elem.textContent = data.count;
                    }
                } catch (error) {
                    console.warn('Live visitor refresh failed.', error);
                }
            }

            function startPolling() {
                if (refreshTimer !== null) {
                    return;
                }

                refreshTimer = window.setInterval(updateLiveCount, 30000);
            }

            function stopPolling() {
                if (refreshTimer === null) {
                    return;
                }

                window.clearInterval(refreshTimer);
                refreshTimer = null;
            }

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    stopPolling();
                    return;
                }

                updateLiveCount();
                startPolling();
            });

            updateLiveCount();
            startPolling();
        })();
    </script>
@endsection
