@extends('layouts.app')

@php
    use Illuminate\Support\Carbon;

    $ac = $dashboard['action_center'] ?? ['items' => [], 'total' => 0];
    $kpis = $dashboard['kpis'] ?? [];
    $funnel = $dashboard['funnel'] ?? [];
    $vtrend = $dashboard['visitor_trend'] ?? ['labels' => [], 'values' => []];
    $health = $dashboard['profile_health'] ?? [];
    $topProviders = $dashboard['top_providers'] ?? [];
    $topCategories = $dashboard['top_categories'] ?? [];
    $recentSignups = $dashboard['recent_signups'] ?? [];
    $recentReviews = $dashboard['recent_reviews'] ?? [];
    $recentAdminActions = $dashboard['recent_admin_actions'] ?? [];

    // Trend badge renderer (returns HTML string).
    $trend = function ($pct, $suffix = 'vs prev 30d') {
        $pct = (float) ($pct ?? 0);
        if ($pct > 0) {
            return '<span style="color:#10b981;"><i class="fas fa-arrow-up"></i> +' . number_format($pct, 1) . '%</span> ' . $suffix;
        }
        if ($pct < 0) {
            return '<span style="color:#ef4444;"><i class="fas fa-arrow-down"></i> ' . number_format($pct, 1) . '%</span> ' . $suffix;
        }
        return '<span style="color:#94a3b8;"><i class="fas fa-minus"></i> 0%</span> ' . $suffix;
    };

    $maxVisitor = max(1, count($vtrend['values']) ? max($vtrend['values']) : 1);
@endphp

@section('content')
    <div class="admin-dashboard-page" dir="ltr">
        <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">

            {{-- Hero --}}
            <section class="admin-dashboard-hero">
                <div>
                    <div class="admin-dashboard-kicker">
                        <i class="fas fa-chart-line"></i>
                        <span>Operations overview</span>
                    </div>
                    <h1 class="admin-dashboard-title">Admin Dashboard</h1>
                    <p class="admin-dashboard-subtitle">
                        Welcome back, <strong>{{ auth()->user()->name }}</strong>. Everything that needs your attention,
                        plus live traffic, leads and growth — all in one place.
                    </p>
                </div>
                <div class="admin-hero-actions">
                    <a href="{{ route('admin.whatsapp_analytics.index') }}" class="admin-btn admin-btn-primary text-white">
                        <i class="fab fa-whatsapp"></i><span>WhatsApp Analytics</span>
                    </a>
                    <a href="{{ route('admin.email_journey.index') }}" class="admin-btn admin-btn-secondary">
                        <i class="fas fa-envelope"></i><span>Email Journey</span>
                    </a>
                    <a href="{{ route('admin.notifications.index') }}" class="admin-btn admin-btn-secondary">
                        <i class="fas fa-bell"></i><span>Notifications</span>
                    </a>
                </div>
            </section>

            {{-- ===================== ACTION CENTER ===================== --}}
            <section class="admin-section-block">
                <div class="admin-section-heading">
                    <div>
                        <p class="admin-section-eyebrow">Needs Your Attention</p>
                        <h2 class="admin-section-title">Action Center</h2>
                    </div>
                    @if(($ac['total'] ?? 0) === 0)
                        <span class="dash-allclear"><i class="fas fa-circle-check"></i> All clear</span>
                    @else
                        <span class="dash-pending-total">{{ number_format($ac['total']) }} items</span>
                    @endif
                </div>

                <div class="dash-action-grid">
                    @forelse($ac['items'] as $item)
                        <a href="{{ $item['route'] }}" class="dash-action-card dash-tone-{{ $item['tone'] }} {{ $item['count'] > 0 ? 'is-active' : 'is-empty' }}">
                            <span class="dash-action-icon"><i class="fas {{ $item['icon'] }}"></i></span>
                            <div class="dash-action-body">
                                <strong class="dash-action-count">{{ number_format($item['count']) }}</strong>
                                <span class="dash-action-label">{{ $item['label'] }}</span>
                            </div>
                            <i class="fas fa-arrow-right dash-action-arrow"></i>
                        </a>
                    @empty
                        <p class="dash-empty">Moderation queue unavailable.</p>
                    @endforelse
                </div>
            </section>

            {{-- ===================== KEY METRICS (with trends) ===================== --}}
            <section class="admin-section-block">
                <div class="admin-section-heading">
                    <div>
                        <p class="admin-section-eyebrow">Core Metrics</p>
                        <h2 class="admin-section-title">Growth Snapshot</h2>
                    </div>
                </div>

                <div class="admin-stat-grid">
                    <article class="admin-stat-card">
                        <div class="admin-stat-head">
                            <span class="admin-stat-icon admin-stat-icon-sky"><i class="fas fa-briefcase"></i></span>
                            <span class="admin-stat-label">Providers</span>
                        </div>
                        <strong class="admin-stat-value">{{ number_format($kpis['providers']['total'] ?? ($stats['totalProviders'] ?? 0)) }}</strong>
                        <span class="admin-stat-foot">+{{ $kpis['providers']['new_30'] ?? 0 }} in 30d · {!! $trend($kpis['providers']['trend'] ?? 0) !!}</span>
                    </article>

                    <article class="admin-stat-card">
                        <div class="admin-stat-head">
                            <span class="admin-stat-icon admin-stat-icon-teal"><i class="fas fa-user-group"></i></span>
                            <span class="admin-stat-label">Clients</span>
                        </div>
                        <strong class="admin-stat-value">{{ number_format($kpis['clients']['total'] ?? ($stats['totalClients'] ?? 0)) }}</strong>
                        <span class="admin-stat-foot">+{{ $kpis['clients']['new_30'] ?? 0 }} in 30d · {!! $trend($kpis['clients']['trend'] ?? 0) !!}</span>
                    </article>

                    <article class="admin-stat-card">
                        <div class="admin-stat-head">
                            <span class="admin-stat-icon admin-stat-icon-emerald"><i class="fab fa-whatsapp"></i></span>
                            <span class="admin-stat-label">WhatsApp Leads (30d)</span>
                        </div>
                        <strong class="admin-stat-value">{{ number_format($kpis['leads']['total'] ?? 0) }}</strong>
                        <span class="admin-stat-foot">{!! $trend($kpis['leads']['trend'] ?? 0) !!}</span>
                    </article>

                    <article class="admin-stat-card">
                        <div class="admin-stat-head">
                            <span class="admin-stat-icon admin-stat-icon-amber"><i class="fas fa-bullseye"></i></span>
                            <span class="admin-stat-label">Conversion (30d)</span>
                        </div>
                        <strong class="admin-stat-value">{{ $kpis['conversion']['rate'] ?? 0 }}%</strong>
                        <span class="admin-stat-foot">leads ÷ {{ number_format($kpis['conversion']['views'] ?? 0) }} profile views</span>
                    </article>

                    <article class="admin-stat-card">
                        <div class="admin-stat-head">
                            <span class="admin-stat-icon admin-stat-icon-rose"><i class="fas fa-star"></i></span>
                            <span class="admin-stat-label">Total Reviews</span>
                        </div>
                        <strong class="admin-stat-value">{{ number_format($kpis['reviews_total'] ?? ($stats['totalReviews'] ?? 0)) }}</strong>
                        <span class="admin-stat-foot">{{ $ac['items'][0]['count'] ?? ($stats['pendingReviews'] ?? 0) }} pending moderation</span>
                    </article>

                    <article class="admin-stat-card">
                        <div class="admin-stat-head">
                            <span class="admin-stat-icon"><i class="fas fa-newspaper"></i></span>
                            <span class="admin-stat-label">Blog Posts</span>
                        </div>
                        <strong class="admin-stat-value">{{ number_format($kpis['blogs_total'] ?? ($stats['totalBlogs'] ?? 0)) }}</strong>
                        <span class="admin-stat-foot">{{ $stats['notificationsSent'] ?? 0 }} notifications sent</span>
                    </article>
                </div>
            </section>

            {{-- ===================== LEAD FUNNEL ===================== --}}
            @php
                $fViews = $funnel['views'] ?? 0;
                $fSess = $funnel['unique_lead_sessions'] ?? 0;
                $fClicks = $funnel['clicks'] ?? 0;
                $fMax = max(1, $fViews, $fSess, $fClicks);
            @endphp
            <section class="admin-section-block">
                <div class="admin-section-heading">
                    <div>
                        <p class="admin-section-eyebrow">Conversion · last 30 days</p>
                        <h2 class="admin-section-title">Lead Funnel</h2>
                    </div>
                    <a href="{{ route('admin.whatsapp_analytics.index') }}" class="dash-link">Full report <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="dash-panel">
                    <div class="dash-funnel">
                        <div class="dash-funnel-stage">
                            <div class="dash-funnel-bar dash-funnel-1" style="width: {{ round(($fViews / $fMax) * 100) }}%;">
                                <span>{{ number_format($fViews) }}</span>
                            </div>
                            <span class="dash-funnel-label"><i class="fas fa-eye"></i> Profile Views</span>
                        </div>
                        <div class="dash-funnel-stage">
                            <div class="dash-funnel-bar dash-funnel-2" style="width: {{ round(($fSess / $fMax) * 100) }}%;">
                                <span>{{ number_format($fSess) }}</span>
                            </div>
                            <span class="dash-funnel-label"><i class="fas fa-fingerprint"></i> Unique Lead Sessions</span>
                        </div>
                        <div class="dash-funnel-stage">
                            <div class="dash-funnel-bar dash-funnel-3" style="width: {{ round(($fClicks / $fMax) * 100) }}%;">
                                <span>{{ number_format($fClicks) }}</span>
                            </div>
                            <span class="dash-funnel-label"><i class="fab fa-whatsapp"></i> WhatsApp Clicks</span>
                        </div>
                    </div>
                    <div class="dash-funnel-conv">
                        <span class="dash-funnel-conv-val">{{ $funnel['conversion_rate'] ?? 0 }}%</span>
                        <span class="dash-funnel-conv-lbl">View → WhatsApp conversion</span>
                    </div>
                </div>
            </section>

            {{-- ===================== TRAFFIC ===================== --}}
            <section class="admin-section-block">
                <div class="admin-section-heading">
                    <div>
                        <p class="admin-section-eyebrow">Traffic</p>
                        <h2 class="admin-section-title">Visitor Health</h2>
                    </div>
                    <a href="{{ route('admin.visitors') }}" class="dash-link">Visitor analytics <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="dash-traffic-grid">
                    <div class="dash-panel dash-trend-panel">
                        <h3 class="dash-panel-title"><i class="fas fa-chart-column"></i> Unique Visitors — last 14 days</h3>
                        <div class="dash-bars">
                            @forelse($vtrend['values'] as $i => $val)
                                <div class="dash-bar-col" title="{{ $vtrend['labels'][$i] ?? '' }}: {{ $val }}">
                                    <div class="dash-bar" style="height: {{ max(2, round(($val / $maxVisitor) * 100)) }}%;"></div>
                                    <span class="dash-bar-val">{{ $val }}</span>
                                    <span class="dash-bar-lbl">{{ \Illuminate\Support\Str::of($vtrend['labels'][$i] ?? '')->explode(' ')->last() }}</span>
                                </div>
                            @empty
                                <p class="dash-empty">No visitor data yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="dash-mini-stats">
                        <div class="dash-mini-card dash-mini-live">
                            <span class="dash-mini-label"><span class="admin-live-dot"></span> Live now</span>
                            <strong class="dash-mini-val live-count">{{ $stats['liveVisitors'] ?? 0 }}</strong>
                        </div>
                        <div class="dash-mini-card">
                            <span class="dash-mini-label">Today</span>
                            <strong class="dash-mini-val">{{ number_format($stats['visitorsToday'] ?? 0) }}</strong>
                        </div>
                        <div class="dash-mini-card">
                            <span class="dash-mini-label">This month</span>
                            <strong class="dash-mini-val">{{ number_format($stats['visitorsThisMonth'] ?? 0) }}</strong>
                        </div>
                        <div class="dash-mini-card">
                            <span class="dash-mini-label">All time</span>
                            <strong class="dash-mini-val">{{ number_format($stats['totalVisitors'] ?? 0) }}</strong>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ===================== TOP PROVIDERS + CATEGORIES ===================== --}}
            <section class="admin-section-block">
                <div class="dash-two-col">
                    <div class="dash-panel">
                        <h3 class="dash-panel-title"><i class="fas fa-trophy"></i> Top Providers by WhatsApp Clicks (30d)</h3>
                        <div class="dash-table-wrap">
                            <table class="dash-table">
                                <thead><tr><th>Provider</th><th>Views</th><th>Clicks</th><th>Conv.</th></tr></thead>
                                <tbody>
                                    @forelse($topProviders as $p)
                                        <tr>
                                            <td><a href="{{ route('admin.provider_activity_monitor.show', $p['id']) }}" class="dash-tlink">{{ $p['company_name'] }}</a></td>
                                            <td>{{ number_format($p['views']) }}</td>
                                            <td>{{ number_format($p['clicks']) }}</td>
                                            <td><span class="dash-pill">{{ $p['conversion_rate'] }}%</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="dash-empty">No WhatsApp clicks in the last 30 days.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="dash-panel">
                        <h3 class="dash-panel-title"><i class="fas fa-folder-tree"></i> Top Categories by Clicks (30d)</h3>
                        <div class="dash-table-wrap">
                            <table class="dash-table">
                                <thead><tr><th>Category</th><th>Views</th><th>Clicks</th><th>Conv.</th></tr></thead>
                                <tbody>
                                    @forelse($topCategories as $c)
                                        <tr>
                                            <td>{{ $c['category'] }}</td>
                                            <td>{{ number_format($c['views']) }}</td>
                                            <td>{{ number_format($c['clicks']) }}</td>
                                            <td><span class="dash-pill">{{ $c['conversion_rate'] }}%</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="dash-empty">No category click data yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ===================== PROFILE HEALTH ===================== --}}
            @php
                $hTotal = max(1, $health['total'] ?? 1);
            @endphp
            <section class="admin-section-block">
                <div class="dash-two-col">
                    <div class="dash-panel">
                        <h3 class="dash-panel-title"><i class="fas fa-heart-pulse"></i> Provider Profile Health</h3>
                        <div class="dash-health">
                            <div class="dash-health-row">
                                <span class="dash-health-lbl">Complete</span>
                                <div class="dash-health-track"><div class="dash-health-fill dash-h-good" style="width: {{ round((($health['complete'] ?? 0) / $hTotal) * 100) }}%;"></div></div>
                                <span class="dash-health-val">{{ number_format($health['complete'] ?? 0) }}</span>
                            </div>
                            <div class="dash-health-row">
                                <span class="dash-health-lbl">Partial</span>
                                <div class="dash-health-track"><div class="dash-health-fill dash-h-mid" style="width: {{ round((($health['partial'] ?? 0) / $hTotal) * 100) }}%;"></div></div>
                                <span class="dash-health-val">{{ number_format($health['partial'] ?? 0) }}</span>
                            </div>
                            <div class="dash-health-row">
                                <span class="dash-health-lbl">Incomplete</span>
                                <div class="dash-health-track"><div class="dash-health-fill dash-h-bad" style="width: {{ round((($health['incomplete'] ?? 0) / $hTotal) * 100) }}%;"></div></div>
                                <span class="dash-health-val">{{ number_format($health['incomplete'] ?? 0) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- WhatsApp quick snapshot --}}
                    <div class="dash-panel">
                        <h3 class="dash-panel-title"><i class="fab fa-whatsapp"></i> WhatsApp Clicks Snapshot</h3>
                        <div class="dash-wa-grid">
                            <div class="dash-wa-cell">
                                <span class="dash-wa-num">{{ number_format($dailyWhatsappClicks ?? 0) }}</span>
                                <span class="dash-wa-lbl">Today</span>
                                <span class="dash-wa-trend">{!! $trend($dailyWhatsappTrend ?? 0, 'vs yesterday') !!}</span>
                            </div>
                            <div class="dash-wa-cell">
                                <span class="dash-wa-num">{{ number_format($weeklyWhatsappClicks ?? 0) }}</span>
                                <span class="dash-wa-lbl">Last 7 days</span>
                                <span class="dash-wa-trend">{!! $trend($weeklyWhatsappTrend ?? 0, 'vs prev 7d') !!}</span>
                            </div>
                            <div class="dash-wa-cell">
                                <span class="dash-wa-num">{{ number_format($monthlyWhatsappClicks ?? 0) }}</span>
                                <span class="dash-wa-lbl">Last 30 days</span>
                                <span class="dash-wa-trend">{!! $trend($monthlyWhatsappTrend ?? 0) !!}</span>
                            </div>
                            <div class="dash-wa-cell">
                                <span class="dash-wa-num" style="font-size:1.05rem;">{{ ($mostClickedCategory ?? false) ? $mostClickedCategory['name'] : 'N/A' }}</span>
                                <span class="dash-wa-lbl">Top category</span>
                                <span class="dash-wa-trend">{{ ($mostClickedCategory ?? false) ? $mostClickedCategory['clicks'].' clicks' : 'No data' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ===================== RECENT ACTIVITY FEEDS ===================== --}}
            <section class="admin-section-block">
                <div class="admin-section-heading">
                    <div>
                        <p class="admin-section-eyebrow">Live Feed</p>
                        <h2 class="admin-section-title">Recent Activity</h2>
                    </div>
                </div>

                <div class="dash-three-col">
                    {{-- Signups --}}
                    <div class="dash-panel">
                        <h3 class="dash-panel-title"><i class="fas fa-user-plus"></i> New Sign-ups</h3>
                        <ul class="dash-feed">
                            @forelse($recentSignups as $u)
                                <li class="dash-feed-item">
                                    <span class="dash-feed-avatar">{{ strtoupper(mb_substr($u->name ?? '?', 0, 1)) }}</span>
                                    <div class="dash-feed-body">
                                        <strong>{{ $u->name }}</strong>
                                        <span class="dash-feed-meta">{{ ucfirst(str_replace('_', ' ', $u->role)) }} · {{ Carbon::parse($u->created_at)->diffForHumans() }}</span>
                                    </div>
                                </li>
                            @empty
                                <li class="dash-empty">No recent sign-ups.</li>
                            @endforelse
                        </ul>
                        <a href="{{ route('admin.users') }}" class="dash-feed-link">Manage users <i class="fas fa-arrow-right"></i></a>
                    </div>

                    {{-- Reviews --}}
                    <div class="dash-panel">
                        <h3 class="dash-panel-title"><i class="fas fa-star"></i> Latest Reviews</h3>
                        <ul class="dash-feed">
                            @forelse($recentReviews as $r)
                                <li class="dash-feed-item">
                                    <span class="dash-feed-avatar dash-feed-avatar-amber"><i class="fas fa-star"></i></span>
                                    <div class="dash-feed-body">
                                        <strong>{{ $r->serviceProvider->company_name ?? 'Provider' }}</strong>
                                        <span class="dash-feed-meta">
                                            {{ $r->rating }}★ · {{ $r->is_active ? 'approved' : 'pending' }} · {{ Carbon::parse($r->created_at)->diffForHumans() }}
                                        </span>
                                    </div>
                                </li>
                            @empty
                                <li class="dash-empty">No reviews yet.</li>
                            @endforelse
                        </ul>
                        <a href="{{ route('admin.reviews') }}" class="dash-feed-link">Manage reviews <i class="fas fa-arrow-right"></i></a>
                    </div>

                    {{-- Admin actions --}}
                    <div class="dash-panel">
                        <h3 class="dash-panel-title"><i class="fas fa-clipboard-list"></i> Admin Actions</h3>
                        <ul class="dash-feed">
                            @forelse($recentAdminActions as $log)
                                <li class="dash-feed-item">
                                    <span class="dash-feed-avatar dash-feed-avatar-slate"><i class="fas fa-bolt"></i></span>
                                    <div class="dash-feed-body">
                                        <strong>{{ ucfirst(str_replace('_', ' ', $log->action)) }}{{ $log->model_name ? ': '.$log->model_name : '' }}</strong>
                                        <span class="dash-feed-meta">{{ $log->admin_name ?? 'Admin' }} · {{ Carbon::parse($log->created_at)->diffForHumans() }}</span>
                                    </div>
                                </li>
                            @empty
                                <li class="dash-empty">No admin actions logged.</li>
                            @endforelse
                        </ul>
                        <a href="{{ route('admin.activity_logs') }}" class="dash-feed-link">View activity logs <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </section>

            {{-- ===================== QUICK ACCESS ===================== --}}
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
                        <div class="admin-command-content"><h3>Manage Users</h3><p>Accounts, roles, status, access.</p></div>
                        <i class="fas fa-arrow-right admin-command-arrow"></i>
                    </a>
                    <a href="{{ route('admin.provider_activity_monitor.index') }}" class="admin-command-card">
                        <span class="admin-command-icon admin-command-emerald"><i class="fas fa-briefcase"></i></span>
                        <div class="admin-command-content"><h3>Manage Providers</h3><p>Visibility, views, WhatsApp engagement.</p></div>
                        <i class="fas fa-arrow-right admin-command-arrow"></i>
                    </a>
                    <a href="{{ route('admin.whatsapp_analytics.index') }}" class="admin-command-card">
                        <span class="admin-command-icon admin-command-emerald"><i class="fab fa-whatsapp"></i></span>
                        <div class="admin-command-content"><h3>WhatsApp Analytics</h3><p>Deep lead intelligence & conversion.</p></div>
                        <i class="fas fa-arrow-right admin-command-arrow"></i>
                    </a>
                    <a href="{{ route('admin.reviews') }}" class="admin-command-card">
                        <span class="admin-command-icon admin-command-amber"><i class="fas fa-shield-alt"></i></span>
                        <div class="admin-command-content"><h3>Manage Reviews</h3><p>Approve, reject, feature, remove.</p></div>
                        <i class="fas fa-arrow-right admin-command-arrow"></i>
                    </a>
                    <a href="{{ route('admin.blog.posts.index') }}" class="admin-command-card">
                        <span class="admin-command-icon admin-command-violet"><i class="fas fa-newspaper"></i></span>
                        <div class="admin-command-content"><h3>Manage Blogs</h3><p>Drafts, publishing, images, SEO.</p></div>
                        <i class="fas fa-arrow-right admin-command-arrow"></i>
                    </a>
                    <a href="{{ route('admin.legal-pages.index') }}" class="admin-command-card">
                        <span class="admin-command-icon admin-command-indigo"><i class="fas fa-scale-balanced"></i></span>
                        <div class="admin-command-content"><h3>Policies & Privacy</h3><p>Edit legal pages, terms, and privacy content.</p></div>
                        <i class="fas fa-arrow-right admin-command-arrow"></i>
                    </a>
                    <a href="{{ route('admin.visitors') }}" class="admin-command-card">
                        <span class="admin-command-icon admin-command-indigo"><i class="fas fa-chart-line"></i></span>
                        <div class="admin-command-content"><h3>Visitor Analytics</h3><p>Privacy-safe traffic & top pages.</p></div>
                        <i class="fas fa-arrow-right admin-command-arrow"></i>
                    </a>
                </div>
            </section>

            {{-- ===================== MAINTENANCE ===================== --}}
            <section class="admin-section-block">
                <div class="admin-panel-card">
                    <div class="admin-panel-card-head">
                        <span class="admin-command-icon admin-command-slate"><i class="fas fa-broom"></i></span>
                        <div><h3>Maintenance</h3><p>Refresh cached application data after admin changes.</p></div>
                    </div>
                    <form action="{{ route('admin.clear-cache') }}" method="POST">
                        @csrf
                        <button type="submit" class="admin-btn admin-btn-ghost admin-btn-full">
                            <i class="fas fa-rotate"></i><span>Clear Caches</span>
                        </button>
                    </form>
                </div>
            </section>

        </div>
    </div>

    @once
    <style>
        .dash-pending-total{background:#fef2f2;color:#b91c1c;font-weight:800;padding:.3rem .9rem;border-radius:99px;font-size:.85rem;}
        .dash-allclear{background:#ecfdf5;color:#047857;font-weight:800;padding:.3rem .9rem;border-radius:99px;font-size:.85rem;}
        .dash-action-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:1rem;}
        .dash-action-card{display:flex;align-items:center;gap:.9rem;background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:1rem 1.1rem;text-decoration:none;box-shadow:0 1px 3px rgba(0,0,0,.05);transition:transform .15s ease,box-shadow .15s ease;}
        .dash-action-card:hover{transform:translateY(-2px);box-shadow:0 8px 24px -12px rgba(15,23,42,.35);}
        .dash-action-card.is-empty{opacity:.55;}
        .dash-action-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;}
        .dash-tone-amber .dash-action-icon{background:#fef3c7;color:#b45309;}
        .dash-tone-sky .dash-action-icon{background:#e0f2fe;color:#0369a1;}
        .dash-tone-rose .dash-action-icon{background:#ffe4e6;color:#be123c;}
        .dash-tone-slate .dash-action-icon{background:#e2e8f0;color:#334155;}
        .dash-action-card.is-active.dash-tone-rose{border-color:#fecaca;}
        .dash-action-body{display:flex;flex-direction:column;flex:1;min-width:0;}
        .dash-action-count{font-size:1.5rem;font-weight:800;color:#0f172a;line-height:1;}
        .dash-action-label{font-size:.78rem;color:#64748b;font-weight:600;margin-top:.2rem;}
        .dash-action-arrow{color:#cbd5e1;font-size:.8rem;}
        .dash-panel{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:1.4rem 1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.05);}
        .dash-panel-title{font-size:1rem;font-weight:800;color:#1e293b;margin:0 0 1.1rem;display:flex;align-items:center;gap:.5rem;}
        .dash-panel-title i{color:#10b981;}
        .dash-link{color:#2563eb;font-weight:700;font-size:.85rem;text-decoration:none;}
        .dash-two-col{display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:1.25rem;}
        .dash-three-col{display:grid;grid-template-columns:repeat(auto-fit,minmax(290px,1fr));gap:1.25rem;}
        .dash-traffic-grid{display:grid;grid-template-columns:2fr 1fr;gap:1.25rem;}
        @media (max-width:900px){.dash-traffic-grid{grid-template-columns:1fr;}}
        /* Funnel */
        .dash-funnel{display:flex;flex-direction:column;gap:1rem;}
        .dash-funnel-stage{display:flex;flex-direction:column;gap:.35rem;}
        .dash-funnel-bar{height:38px;border-radius:10px;display:flex;align-items:center;padding:0 .9rem;color:#fff;font-weight:800;min-width:60px;transition:width .4s ease;}
        .dash-funnel-1{background:linear-gradient(90deg,#60a5fa,#2563eb);}
        .dash-funnel-2{background:linear-gradient(90deg,#34d399,#059669);}
        .dash-funnel-3{background:linear-gradient(90deg,#10b981,#047857);}
        .dash-funnel-label{font-size:.8rem;color:#475569;font-weight:600;}
        .dash-funnel-conv{margin-top:1.2rem;padding-top:1.1rem;border-top:1px dashed #e2e8f0;display:flex;align-items:baseline;gap:.6rem;}
        .dash-funnel-conv-val{font-size:1.8rem;font-weight:800;color:#047857;}
        .dash-funnel-conv-lbl{font-size:.85rem;color:#64748b;font-weight:600;}
        /* Visitor bars */
        .dash-bars{display:flex;align-items:flex-end;gap:.4rem;height:180px;padding-top:.5rem;}
        .dash-bar-col{flex:1;min-width:18px;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%;gap:.2rem;}
        .dash-bar{width:62%;min-height:2px;background:linear-gradient(180deg,#93c5fd,#2563eb);border-radius:6px 6px 0 0;transition:height .3s ease;}
        .dash-bar-val{font-size:.66rem;font-weight:700;color:#334155;}
        .dash-bar-lbl{font-size:.6rem;color:#94a3b8;}
        .dash-mini-stats{display:grid;grid-template-columns:1fr 1fr;gap:.9rem;align-content:start;}
        .dash-mini-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:1rem;box-shadow:0 1px 3px rgba(0,0,0,.05);}
        .dash-mini-label{font-size:.74rem;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:.04em;display:flex;align-items:center;gap:.4rem;}
        .dash-mini-val{display:block;font-size:1.5rem;font-weight:800;color:#0f172a;margin-top:.35rem;}
        .dash-mini-live{border-color:#bbf7d0;background:#f0fdf4;}
        /* Tables */
        .dash-table-wrap{overflow-x:auto;}
        .dash-table{width:100%;border-collapse:collapse;font-size:.88rem;}
        .dash-table thead th{text-align:start;padding:.55rem .8rem;background:#f8fafc;color:#475569;font-weight:700;border-bottom:1px solid #e2e8f0;white-space:nowrap;}
        .dash-table tbody td{padding:.55rem .8rem;border-bottom:1px solid #f1f5f9;color:#334155;}
        .dash-table tbody tr:hover{background:#f8fafc;}
        .dash-pill{display:inline-block;padding:.1rem .55rem;border-radius:99px;background:#ecfdf5;color:#047857;font-weight:700;font-size:.78rem;}
        .dash-tlink{color:#2563eb;font-weight:600;text-decoration:none;}
        .dash-tlink:hover{text-decoration:underline;}
        /* Health */
        .dash-health{display:flex;flex-direction:column;gap:.7rem;}
        .dash-health-row{display:grid;grid-template-columns:90px 1fr 50px;align-items:center;gap:.6rem;}
        .dash-health-lbl{font-size:.8rem;font-weight:600;color:#475569;}
        .dash-health-track{height:12px;background:#eef2f7;border-radius:99px;overflow:hidden;}
        .dash-health-fill{height:100%;border-radius:99px;}
        .dash-h-good{background:linear-gradient(90deg,#34d399,#059669);}
        .dash-h-mid{background:linear-gradient(90deg,#fbbf24,#d97706);}
        .dash-h-bad{background:linear-gradient(90deg,#f87171,#dc2626);}
        .dash-health-val{font-size:.85rem;font-weight:700;color:#334155;text-align:right;}
        .dash-health-foot{margin-top:1rem;padding-top:.9rem;border-top:1px dashed #e2e8f0;font-size:.82rem;color:#92400e;}
        .dash-health-foot i{color:#d97706;margin-inline-end:.3rem;}
        /* WhatsApp snapshot */
        .dash-wa-grid{display:grid;grid-template-columns:1fr 1fr;gap:.9rem;}
        .dash-wa-cell{background:#f8fafc;border:1px solid #eef2f7;border-radius:12px;padding:.9rem;}
        .dash-wa-num{display:block;font-size:1.5rem;font-weight:800;color:#0f172a;line-height:1.1;}
        .dash-wa-lbl{font-size:.74rem;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:.03em;}
        .dash-wa-trend{display:block;font-size:.72rem;color:#64748b;margin-top:.3rem;}
        /* Feeds */
        .dash-feed{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:.85rem;}
        .dash-feed-item{display:flex;align-items:center;gap:.7rem;}
        .dash-feed-avatar{width:38px;height:38px;border-radius:50%;background:#e0e7ff;color:#4338ca;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
        .dash-feed-avatar-amber{background:#fef3c7;color:#b45309;}
        .dash-feed-avatar-slate{background:#e2e8f0;color:#334155;}
        .dash-feed-body{display:flex;flex-direction:column;min-width:0;}
        .dash-feed-body strong{font-size:.88rem;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
        .dash-feed-meta{font-size:.74rem;color:#94a3b8;}
        .dash-feed-link{display:inline-block;margin-top:1rem;color:#2563eb;font-weight:700;font-size:.82rem;text-decoration:none;}
        .dash-empty{text-align:center;color:#94a3b8;font-style:italic;padding:1rem;font-size:.85rem;}
    </style>
    @endonce

    <script>
        (() => {
            let refreshTimer = null;
            async function updateLiveCount() {
                if (document.hidden) return;
                try {
                    const response = await fetch('{{ route('admin.visitors.live-count') }}', { headers: {'X-Requested-With': 'XMLHttpRequest'} });
                    if (!response.ok) return;
                    const data = await response.json();
                    const elem = document.querySelector('.live-count');
                    if (data.success && elem) elem.textContent = data.count;
                } catch (error) { console.warn('Live visitor refresh failed.', error); }
            }
            function startPolling() { if (refreshTimer !== null) return; refreshTimer = window.setInterval(updateLiveCount, 30000); }
            document.addEventListener('visibilitychange', () => { if (!document.hidden) updateLiveCount(); });
            updateLiveCount();
            startPolling();
        })();
    </script>
@endsection
