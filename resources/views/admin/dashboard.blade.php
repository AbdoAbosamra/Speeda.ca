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
                    <a href="{{ route('admin.blog.posts.create') }}" class="admin-btn admin-btn-primary text-white">
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

            <!-- WhatsApp Analytics Overview Section -->
            <section class="admin-section-block">
                <div class="admin-section-heading">
                    <div>
                        <p class="admin-section-eyebrow">Engagement</p>
                        <h2 class="admin-section-title">WhatsApp Analytics</h2>
                    </div>
                </div>

                <div class="admin-stat-grid">
                    <article class="admin-stat-card">
                        <div class="admin-stat-head">
                            <span class="admin-stat-icon admin-stat-icon-emerald"><i class="fab fa-whatsapp"></i></span>
                            <span class="admin-stat-label">Daily Clicks</span>
                        </div>
                        <strong class="admin-stat-value">{{ number_format($dailyWhatsappClicks) }}</strong>
                        <span class="admin-stat-foot">
                            @if($dailyWhatsappTrend > 0)
                                <span style="color: #10b981;"><i class="fas fa-arrow-up"></i> {{ round($dailyWhatsappTrend, 1) }}%</span> from yesterday
                            @elseif($dailyWhatsappTrend < 0)
                                <span style="color: #ef4444;"><i class="fas fa-arrow-down"></i> {{ abs(round($dailyWhatsappTrend, 1)) }}%</span> from yesterday
                            @else
                                <span><i class="fas fa-minus"></i> 0%</span> from yesterday
                            @endif
                        </span>
                    </article>

                    <article class="admin-stat-card">
                        <div class="admin-stat-head">
                            <span class="admin-stat-icon admin-stat-icon-emerald"><i class="fab fa-whatsapp"></i></span>
                            <span class="admin-stat-label">Weekly Clicks</span>
                        </div>
                        <strong class="admin-stat-value">{{ number_format($weeklyWhatsappClicks) }}</strong>
                        <span class="admin-stat-foot">
                            @if($weeklyWhatsappTrend > 0)
                                <span style="color: #10b981;"><i class="fas fa-arrow-up"></i> {{ round($weeklyWhatsappTrend, 1) }}%</span> from last week
                            @elseif($weeklyWhatsappTrend < 0)
                                <span style="color: #ef4444;"><i class="fas fa-arrow-down"></i> {{ abs(round($weeklyWhatsappTrend, 1)) }}%</span> from last week
                            @else
                                <span><i class="fas fa-minus"></i> 0%</span> from last week
                            @endif
                        </span>
                    </article>

                    <article class="admin-stat-card">
                        <div class="admin-stat-head">
                            <span class="admin-stat-icon admin-stat-icon-emerald"><i class="fab fa-whatsapp"></i></span>
                            <span class="admin-stat-label">Monthly Clicks</span>
                        </div>
                        <strong class="admin-stat-value">{{ number_format($monthlyWhatsappClicks) }}</strong>
                        <span class="admin-stat-foot">
                            @if($monthlyWhatsappTrend > 0)
                                <span style="color: #10b981;"><i class="fas fa-arrow-up"></i> {{ round($monthlyWhatsappTrend, 1) }}%</span> from last month
                            @elseif($monthlyWhatsappTrend < 0)
                                <span style="color: #ef4444;"><i class="fas fa-arrow-down"></i> {{ abs(round($monthlyWhatsappTrend, 1)) }}%</span> from last month
                            @else
                                <span><i class="fas fa-minus"></i> 0%</span> from last month
                            @endif
                        </span>
                    </article>

                    <article class="admin-stat-card">
                        <div class="admin-stat-head">
                            <span class="admin-stat-icon admin-stat-icon-amber"><i class="fas fa-fire"></i></span>
                            <span class="admin-stat-label">Most Clicked Category</span>
                        </div>
                        <strong class="admin-stat-value">{{ $mostClickedCategory ? $mostClickedCategory['name'] : 'N/A' }}</strong>
                        <span class="admin-stat-foot">
                            {{ $mostClickedCategory ? $mostClickedCategory['clicks'] . ' clicks (' . $mostClickedCategory['percentage'] . '% of total)' : 'No data yet' }}
                        </span>
                    </article>
                </div>
            </section>

            <!-- Top Providers Performance Table -->
            <section class="admin-section-block">
                <div class="admin-section-heading">
                    <div>
                        <p class="admin-section-eyebrow">Performance</p>
                        <h2 class="admin-section-title">Top Providers (30 Days)</h2>
                    </div>
                </div>

                <div style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: start; font-size: 0.95rem;">
                            <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <tr>
                                    <th style="padding: 1rem 1.5rem; font-weight: 600; color: #475569;">Provider Name</th>
                                    <th style="padding: 1rem 1.5rem; font-weight: 600; color: #475569;">Views</th>
                                    <th style="padding: 1rem 1.5rem; font-weight: 600; color: #475569;">WhatsApp Clicks</th>
                                    <th style="padding: 1rem 1.5rem; font-weight: 600; color: #475569;">Performance Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topProvidersPerformance as $provider)
                                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                        <td style="padding: 1rem 1.5rem;">
                                            <a href="{{ route('admin.provider_activity_monitor.show', $provider->id) }}" style="color: #3b82f6; font-weight: 500; text-decoration: none;">
                                                {{ $provider->company_name }}
                                            </a>
                                        </td>
                                        <td style="padding: 1rem 1.5rem; color: #64748b;">{{ number_format($provider->total_views) }}</td>
                                        <td style="padding: 1rem 1.5rem; color: #64748b;">{{ number_format($provider->total_whatsapp_clicks) }}</td>
                                        <td style="padding: 1rem 1.5rem;">
                                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                <span style="font-weight: 500; min-width: 3rem;">{{ $provider->performance_rate }}%</span>
                                                <div style="flex: 1; max-width: 120px; height: 6px; background: #e2e8f0; border-radius: 99px; overflow: hidden;">
                                                    @php
                                                        $barColor = '#ef4444'; // Red for 0-20%
                                                        if ($provider->performance_rate >= 50) $barColor = '#10b981'; // Green for 50-100%
                                                        elseif ($provider->performance_rate >= 20) $barColor = '#f59e0b'; // Yellow for 20-50%
                                                    @endphp
                                                    <div style="height: 100%; width: {{ min(100, $provider->performance_rate) }}%; background: {{ $barColor }}; border-radius: 99px;"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="padding: 2rem; text-align: center; color: #94a3b8; font-style: italic;">
                                            No performance data available for the last 30 days.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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
