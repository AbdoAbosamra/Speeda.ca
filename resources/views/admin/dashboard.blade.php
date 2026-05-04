@extends('layouts.app')

@section('content')
    <div class="admin-dashboard-page" dir="ltr">
        <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
            <section class="admin-dashboard-hero">
                <div>
                    <div class="admin-dashboard-kicker">
                        <i class="fas fa-chart-line"></i>
                        <span>Operations overview</span>
                    </div>
                    <h1 class="admin-dashboard-title">Admin Dashboard</h1>
                    <p class="admin-dashboard-subtitle">
                        Welcome back, <strong>{{ auth()->user()->name }}</strong>. Manage users, providers, reviews,
                        and blog content from one clear workspace.
                    </p>
                </div>

                <div class="admin-hero-actions">
                    <a href="{{ route('admin.blog.posts.create') }}" class="admin-btn admin-btn-primary">
                        <i class="fas fa-plus"></i>
                        <span>New Blog Post</span>
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
                        <p class="admin-section-eyebrow">Core Metrics</p>
                        <h2 class="admin-section-title">Management Snapshot</h2>
                    </div>
                </div>

                <div class="admin-stat-grid">
                    <article class="admin-stat-card">
                        <div class="admin-stat-head">
                            <span class="admin-stat-icon admin-stat-icon-sky"><i class="fas fa-briefcase"></i></span>
                            <span class="admin-stat-label">Total Providers</span>
                        </div>
                        <strong class="admin-stat-value">{{ $stats['totalProviders'] ?? 0 }}</strong>
                        <span class="admin-stat-foot">Registered provider profiles</span>
                    </article>

                    <article class="admin-stat-card">
                        <div class="admin-stat-head">
                            <span class="admin-stat-icon admin-stat-icon-teal"><i class="fas fa-user-group"></i></span>
                            <span class="admin-stat-label">Total Clients</span>
                        </div>
                        <strong class="admin-stat-value">{{ $stats['totalClients'] ?? 0 }}</strong>
                        <span class="admin-stat-foot">{{ $stats['newUsersToday'] ?? 0 }} new users today</span>
                    </article>

                    <article class="admin-stat-card">
                        <div class="admin-stat-head">
                            <span class="admin-stat-icon admin-stat-icon-rose"><i class="fas fa-star"></i></span>
                            <span class="admin-stat-label">Total Reviews</span>
                        </div>
                        <strong class="admin-stat-value">{{ $stats['totalReviews'] ?? 0 }}</strong>
                        <span class="admin-stat-foot">{{ $stats['pendingReviews'] ?? 0 }} pending moderation</span>
                    </article>

                    <article class="admin-stat-card">
                        <div class="admin-stat-head">
                            <span class="admin-stat-icon"><i class="fas fa-newspaper"></i></span>
                            <span class="admin-stat-label">Total Blogs</span>
                        </div>
                        <strong class="admin-stat-value">{{ $stats['totalBlogs'] ?? 0 }}</strong>
                        <span class="admin-stat-foot">Admin-managed articles</span>
                    </article>

                    <article class="admin-stat-card">
                        <div class="admin-stat-head">
                            <span class="admin-stat-icon admin-stat-icon-teal"><i class="fas fa-bell"></i></span>
                            <span class="admin-stat-label">Notifications Sent</span>
                        </div>
                        <strong class="admin-stat-value">{{ $stats['notificationsSent'] ?? 0 }}</strong>
                        <span class="admin-stat-foot">{{ $stats['activeNotifications'] ?? 0 }} active broadcasts</span>
                    </article>
                </div>
            </section>

            <section class="admin-section-block">
                <div class="admin-section-heading">
                    <div>
                        <p class="admin-section-eyebrow">Quick Access</p>
                        <h2 class="admin-section-title">Manage The Platform</h2>
                    </div>
                </div>

                <div class="admin-command-grid">
                    <a href="{{ route('admin.users') }}" class="admin-command-card">
                        <span class="admin-command-icon admin-command-blue"><i class="fas fa-users-cog"></i></span>
                        <div class="admin-command-content">
                            <h3>Manage Users</h3>
                            <p>Review accounts, roles, status, and user access.</p>
                        </div>
                        <i class="fas fa-arrow-right admin-command-arrow"></i>
                    </a>

                    <a href="{{ route('admin.provider_activity_monitor.index') }}" class="admin-command-card">
                        <span class="admin-command-icon admin-command-emerald"><i class="fas fa-briefcase"></i></span>
                        <div class="admin-command-content">
                            <h3>Manage Providers</h3>
                            <p>Track provider visibility, views, and WhatsApp engagement.</p>
                        </div>
                        <i class="fas fa-arrow-right admin-command-arrow"></i>
                    </a>

                    <a href="{{ route('admin.reviews') }}" class="admin-command-card">
                        <span class="admin-command-icon admin-command-amber"><i class="fas fa-shield-alt"></i></span>
                        <div class="admin-command-content">
                            <h3>Manage Reviews</h3>
                            <p>Approve, reject, feature, and remove reviews safely.</p>
                        </div>
                        <i class="fas fa-arrow-right admin-command-arrow"></i>
                    </a>

                    <a href="{{ route('admin.blog.posts.index') }}" class="admin-command-card">
                        <span class="admin-command-icon admin-command-violet"><i class="fas fa-newspaper"></i></span>
                        <div class="admin-command-content">
                            <h3>Manage Blogs</h3>
                            <p>Create drafts, publish articles, upload images, and edit SEO-friendly slugs.</p>
                        </div>
                        <i class="fas fa-arrow-right admin-command-arrow"></i>
                    </a>

                    <a href="{{ route('admin.visitors') }}" class="admin-command-card">
                        <span class="admin-command-icon admin-command-indigo"><i class="fas fa-chart-line"></i></span>
                        <div class="admin-command-content">
                            <h3>View Analytics</h3>
                            <p>Review privacy-safe visitor trends and top public pages.</p>
                        </div>
                        <i class="fas fa-arrow-right admin-command-arrow"></i>
                    </a>

                    <a href="{{ route('admin.notifications.index') }}" class="admin-command-card">
                        <span class="admin-command-icon admin-command-slate"><i class="fas fa-bell"></i></span>
                        <div class="admin-command-content">
                            <h3>Manage Notifications</h3>
                            <p>Create and review active provider broadcasts.</p>
                        </div>
                        <i class="fas fa-arrow-right admin-command-arrow"></i>
                    </a>
                </div>
            </section>

            <section class="admin-section-block">
                <div class="admin-section-heading">
                    <div>
                        <p class="admin-section-eyebrow">Traffic</p>
                        <h2 class="admin-section-title">Visitor Health</h2>
                    </div>
                </div>

                <div class="admin-stat-grid">
                    <article class="admin-stat-card admin-stat-card-live">
                        <div class="admin-stat-head">
                            <span class="admin-stat-icon admin-stat-icon-teal"><i class="fas fa-signal"></i></span>
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
                            <span class="admin-stat-icon admin-stat-icon-sky"><i class="fas fa-calendar-day"></i></span>
                            <span class="admin-stat-label">Daily Visitors</span>
                        </div>
                        <strong class="admin-stat-value">{{ $stats['visitorsToday'] ?? 0 }}</strong>
                        <span class="admin-stat-foot">Today by Canada time</span>
                    </article>

                    <article class="admin-stat-card">
                        <div class="admin-stat-head">
                            <span class="admin-stat-icon admin-stat-icon-rose"><i class="fas fa-calendar-days"></i></span>
                            <span class="admin-stat-label">Monthly Visitors</span>
                        </div>
                        <strong class="admin-stat-value">{{ $stats['visitorsThisMonth'] ?? 0 }}</strong>
                        <span class="admin-stat-foot">Current month by Canada time</span>
                    </article>

                    <article class="admin-stat-card">
                        <div class="admin-stat-head">
                            <span class="admin-stat-icon"><i class="fas fa-infinity"></i></span>
                            <span class="admin-stat-label">All Time Visitors</span>
                        </div>
                        <strong class="admin-stat-value">{{ $stats['totalVisitors'] ?? 0 }}</strong>
                        <span class="admin-stat-foot">Total unique visitors</span>
                    </article>
                </div>
            </section>

            <section class="admin-support-grid">
                <div class="admin-sidebar-stack">
                    <section class="admin-section-block">
                        <div class="admin-panel-card">
                            <div class="admin-panel-card-head">
                                <span class="admin-command-icon admin-command-slate"><i class="fas fa-broom"></i></span>
                                <div>
                                    <h3>Maintenance</h3>
                                    <p>Refresh cached application data after admin changes.</p>
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
                if (document.hidden) return;

                try {
                    const response = await fetch('{{ route('admin.visitors.live-count') }}', {
                        headers: {'X-Requested-With': 'XMLHttpRequest'},
                    });

                    if (!response.ok) return;

                    const data = await response.json();
                    const elem = document.querySelector('.live-count');

                    if (data.success && elem) elem.textContent = data.count;
                } catch (error) {
                    console.warn('Live visitor refresh failed.', error);
                }
            }

            function startPolling() {
                if (refreshTimer !== null) return;
                refreshTimer = window.setInterval(updateLiveCount, 30000);
            }

            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) updateLiveCount();
            });

            updateLiveCount();
            startPolling();
        })();
    </script>
@endsection
