@extends('layouts.app')

@php
    use Illuminate\Support\Carbon;

    $fmtPct = function ($v) {
        $v = (float) $v;
        $sign = $v > 0 ? '+' : '';
        return $sign . number_format($v, 1) . '%';
    };

    // Helper to render a simple CSS bar chart from a labels/values array.
    $maxOf = function (array $values) {
        $values = array_map('intval', $values);
        return max(1, count($values) ? max($values) : 1);
    };
@endphp

@section('content')
<div class="admin-dashboard-page wa-analytics" dir="ltr">
    <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">

        {{-- Hero --}}
        <section class="admin-dashboard-hero">
            <div>
                <div class="admin-dashboard-kicker">
                    <i class="fab fa-whatsapp"></i>
                    <span>Lead Intelligence</span>
                </div>
                <h1 class="admin-dashboard-title">WhatsApp Analytics</h1>
                <p class="admin-dashboard-subtitle">
                    Everything about WhatsApp lead clicks across Speeda — privacy-safe, admin activity excluded.
                    Showing range <strong>{{ Carbon::parse($from)->format('M d, Y') }}</strong>
                    – <strong>{{ Carbon::parse($to)->format('M d, Y') }}</strong>.
                </p>
            </div>
            <div class="admin-hero-actions">
                <a href="{{ route('admin.provider_activity_monitor.index') }}" class="admin-btn admin-btn-secondary">
                    <i class="fas fa-briefcase"></i><span>Provider Monitor</span>
                </a>
            </div>
        </section>

        {{-- Filters --}}
        <section class="admin-section-block">
            <form method="GET" action="{{ route('admin.whatsapp_analytics.index') }}" class="wa-filter-card">
                <div class="wa-filter-grid">
                    <label>Date from
                        <input type="date" name="date_from" value="{{ $filters['date_from'] }}">
                    </label>
                    <label>Date to
                        <input type="date" name="date_to" value="{{ $filters['date_to'] }}">
                    </label>
                    <label>Category
                        <select name="category_id">
                            <option value="">All categories</option>
                            @foreach($options['categories'] as $cat)
                                <option value="{{ $cat->id }}" @selected($filters['category_id'] == $cat->id)>{{ $cat->name_en ?? ('#' . $cat->id) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Location
                        <select name="location_id">
                            <option value="">All locations</option>
                            @foreach($options['locations'] as $loc)
                                <option value="{{ $loc->id }}" @selected($filters['location_id'] == $loc->id)>{{ $loc->city ?? ('#' . $loc->id) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Source page
                        <select name="source_page">
                            <option value="">All sources</option>
                            @foreach($options['source_pages'] as $sp)
                                <option value="{{ $sp }}" @selected($filters['source_page'] === $sp)>{{ ucfirst(str_replace('_', ' ', $sp)) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Locale
                        <select name="locale">
                            <option value="">All locales</option>
                            @foreach($options['locales'] as $lc)
                                <option value="{{ $lc }}" @selected($filters['locale'] === $lc)>{{ strtoupper($lc) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Device
                        <select name="device_type">
                            <option value="">All devices</option>
                            @foreach($options['devices'] as $dv)
                                <option value="{{ $dv }}" @selected($filters['device_type'] === $dv)>{{ ucfirst($dv) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Provider ID
                        <input type="number" name="provider_id" min="1" value="{{ $filters['provider_id'] }}" placeholder="e.g. 42">
                    </label>
                </div>
                <div class="wa-filter-actions">
                    <button type="submit" class="admin-btn admin-btn-primary text-white">
                        <i class="fas fa-filter"></i><span>Apply Filters</span>
                    </button>
                    <a href="{{ route('admin.whatsapp_analytics.index') }}" class="admin-btn admin-btn-ghost">
                        <i class="fas fa-rotate-left"></i><span>Reset</span>
                    </a>
                </div>
            </form>
        </section>

        {{-- Summary Cards --}}
        <section class="admin-section-block">
            <div class="admin-section-heading">
                <div>
                    <p class="admin-section-eyebrow">Overview</p>
                    <h2 class="admin-section-title">Click Summary</h2>
                </div>
            </div>

            <div class="admin-stat-grid">
                <article class="admin-stat-card">
                    <div class="admin-stat-head">
                        <span class="admin-stat-icon admin-stat-icon-emerald"><i class="fab fa-whatsapp"></i></span>
                        <span class="admin-stat-label">Total Clicks (all time)</span>
                    </div>
                    <strong class="admin-stat-value">{{ number_format($summary['total_all_time']) }}</strong>
                    <span class="admin-stat-foot">Lifetime WhatsApp leads</span>
                </article>

                <article class="admin-stat-card">
                    <div class="admin-stat-head">
                        <span class="admin-stat-icon admin-stat-icon-sky"><i class="fas fa-calendar-day"></i></span>
                        <span class="admin-stat-label">Today</span>
                    </div>
                    <strong class="admin-stat-value">{{ number_format($summary['today']) }}</strong>
                    <span class="admin-stat-foot">Clicks so far today</span>
                </article>

                <article class="admin-stat-card">
                    <div class="admin-stat-head">
                        <span class="admin-stat-icon admin-stat-icon-teal"><i class="fas fa-calendar-week"></i></span>
                        <span class="admin-stat-label">Last 7 Days</span>
                    </div>
                    <strong class="admin-stat-value">{{ number_format($summary['last_7_days']) }}</strong>
                    <span class="admin-stat-foot">Rolling weekly clicks</span>
                </article>

                <article class="admin-stat-card">
                    <div class="admin-stat-head">
                        <span class="admin-stat-icon admin-stat-icon-rose"><i class="fas fa-calendar-days"></i></span>
                        <span class="admin-stat-label">Last 30 Days</span>
                    </div>
                    <strong class="admin-stat-value">{{ number_format($summary['last_30_days']) }}</strong>
                    <span class="admin-stat-foot">
                        @if($summary['growth_pct'] > 0)
                            <span style="color:#10b981;"><i class="fas fa-arrow-up"></i> {{ $fmtPct($summary['growth_pct']) }}</span> vs previous 30d
                        @elseif($summary['growth_pct'] < 0)
                            <span style="color:#ef4444;"><i class="fas fa-arrow-down"></i> {{ $fmtPct($summary['growth_pct']) }}</span> vs previous 30d
                        @else
                            <i class="fas fa-minus"></i> 0% vs previous 30d
                        @endif
                    </span>
                </article>

                <article class="admin-stat-card">
                    <div class="admin-stat-head">
                        <span class="admin-stat-icon admin-stat-icon-amber"><i class="fas fa-bullseye"></i></span>
                        <span class="admin-stat-label">Click-Through Rate (30d)</span>
                    </div>
                    <strong class="admin-stat-value">{{ $summary['overall_ctr_30'] }}%</strong>
                    <span class="admin-stat-foot">{{ number_format($summary['last_30_days']) }} clicks / {{ number_format($summary['views_30']) }} views</span>
                </article>

                <article class="admin-stat-card">
                    <div class="admin-stat-head">
                        <span class="admin-stat-icon admin-stat-icon-sky"><i class="fas fa-fingerprint"></i></span>
                        <span class="admin-stat-label">Unique Sessions (30d)</span>
                    </div>
                    <strong class="admin-stat-value">{{ number_format($summary['unique_sessions_30']) }}</strong>
                    <span class="admin-stat-foot">Distinct anonymous clickers</span>
                </article>

                <article class="admin-stat-card">
                    <div class="admin-stat-head">
                        <span class="admin-stat-icon admin-stat-icon-teal"><i class="fas fa-people-group"></i></span>
                        <span class="admin-stat-label">Avg Clicks / Provider (30d)</span>
                    </div>
                    <strong class="admin-stat-value">{{ $summary['avg_clicks_per_provider_30'] }}</strong>
                    <span class="admin-stat-foot">Among providers with clicks</span>
                </article>
            </div>
        </section>

        {{-- Clicks over time --}}
        <section class="admin-section-block">
            <div class="admin-section-heading">
                <div>
                    <p class="admin-section-eyebrow">Trend</p>
                    <h2 class="admin-section-title">Clicks Over Time</h2>
                </div>
            </div>
            <div class="wa-panel">
                @php $maxT = $maxOf($charts['over_time']['values']); @endphp
                <div class="wa-bars wa-bars-scroll">
                    @forelse($charts['over_time']['values'] as $i => $val)
                        <div class="wa-bar-col" title="{{ $charts['over_time']['labels'][$i] }}: {{ $val }} clicks">
                            <div class="wa-bar" style="height: {{ max(2, round(($val / $maxT) * 100)) }}%;"></div>
                            <span class="wa-bar-val">{{ $val }}</span>
                            <span class="wa-bar-lbl">{{ $charts['over_time']['labels'][$i] }}</span>
                        </div>
                    @empty
                        <p class="wa-empty">No data in this range.</p>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- Hour + Day-of-week --}}
        <section class="admin-section-block">
            <div class="wa-two-col">
                <div class="wa-panel">
                    <h3 class="wa-panel-title"><i class="fas fa-clock"></i> Best Hours of Day</h3>
                    @php $maxH = $maxOf($charts['by_hour']['values']); @endphp
                    <div class="wa-bars">
                        @foreach($charts['by_hour']['values'] as $i => $val)
                            <div class="wa-bar-col" title="{{ $charts['by_hour']['labels'][$i] }}: {{ $val }}">
                                <div class="wa-bar wa-bar-thin" style="height: {{ max(2, round(($val / $maxH) * 100)) }}%;"></div>
                                <span class="wa-bar-lbl wa-bar-lbl-sm">{{ $i }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="wa-panel">
                    <h3 class="wa-panel-title"><i class="fas fa-calendar"></i> Best Days of Week</h3>
                    @php $maxD = $maxOf($charts['by_dow']['values']); @endphp
                    <div class="wa-bars">
                        @foreach($charts['by_dow']['values'] as $i => $val)
                            <div class="wa-bar-col" title="{{ $charts['by_dow']['labels'][$i] }}: {{ $val }}">
                                <div class="wa-bar" style="height: {{ max(2, round(($val / $maxD) * 100)) }}%;"></div>
                                <span class="wa-bar-val">{{ $val }}</span>
                                <span class="wa-bar-lbl">{{ $charts['by_dow']['labels'][$i] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- Source / Device / Locale --}}
        <section class="admin-section-block">
            <div class="wa-three-col">
                @php
                    $dimBlocks = [
                        ['title' => 'By Source Page', 'icon' => 'fa-location-arrow', 'data' => $charts['by_source']],
                        ['title' => 'By Device', 'icon' => 'fa-mobile-screen', 'data' => $charts['by_device']],
                        ['title' => 'By Locale', 'icon' => 'fa-language', 'data' => $charts['by_locale']],
                    ];
                @endphp
                @foreach($dimBlocks as $block)
                    <div class="wa-panel">
                        <h3 class="wa-panel-title"><i class="fas {{ $block['icon'] }}"></i> {{ $block['title'] }}</h3>
                        @php $maxB = max(1, collect($block['data'])->max('value') ?? 1); @endphp
                        <div class="wa-hbars">
                            @forelse($block['data'] as $row)
                                <div class="wa-hbar-row">
                                    <span class="wa-hbar-label">{{ ucfirst(str_replace('_', ' ', $row['label'])) }}</span>
                                    <div class="wa-hbar-track">
                                        <div class="wa-hbar-fill" style="width: {{ max(3, round(($row['value'] / $maxB) * 100)) }}%;"></div>
                                    </div>
                                    <span class="wa-hbar-val">{{ number_format($row['value']) }}</span>
                                </div>
                            @empty
                                <p class="wa-empty">No data.</p>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Top Providers --}}
        @include('admin.analytics.partials.provider-table', [
            'eyebrow' => 'Performance',
            'title' => 'Top Providers by WhatsApp Clicks',
            'rows' => $tables['top_providers'],
            'showConversion' => true,
            'showLastClick' => true,
            'emptyText' => 'No WhatsApp clicks in this range.',
        ])

        {{-- Low Conversion Providers --}}
        @include('admin.analytics.partials.provider-table', [
            'eyebrow' => 'Needs Attention',
            'title' => 'High Views, Low WhatsApp Conversion',
            'rows' => $tables['low_conversion'],
            'showConversion' => true,
            'showLastClick' => false,
            'emptyText' => 'No providers meet the minimum-views threshold yet.',
            'suggestAction' => true,
        ])

        {{-- Category + Location performance --}}
        <section class="admin-section-block">
            <div class="wa-two-col">
                <div class="wa-panel">
                    <h3 class="wa-panel-title"><i class="fas fa-folder-tree"></i> Category Performance</h3>
                    <div class="wa-table-wrap">
                        <table class="wa-table">
                            <thead><tr><th>Category</th><th>Views</th><th>Clicks</th><th>Conv.</th></tr></thead>
                            <tbody>
                                @forelse($tables['category_performance'] as $row)
                                    <tr>
                                        <td>{{ $row['category'] }}</td>
                                        <td>{{ number_format($row['views']) }}</td>
                                        <td>{{ number_format($row['clicks']) }}</td>
                                        <td><span class="wa-pill">{{ $row['conversion_rate'] }}%</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="wa-empty">No data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="wa-panel">
                    <h3 class="wa-panel-title"><i class="fas fa-location-dot"></i> Location Performance</h3>
                    <div class="wa-table-wrap">
                        <table class="wa-table">
                            <thead><tr><th>Location</th><th>Views</th><th>Clicks</th><th>Conv.</th></tr></thead>
                            <tbody>
                                @forelse($tables['location_performance'] as $row)
                                    <tr>
                                        <td>{{ $row['location'] }}</td>
                                        <td>{{ number_format($row['views']) }}</td>
                                        <td>{{ number_format($row['clicks']) }}</td>
                                        <td><span class="wa-pill">{{ $row['conversion_rate'] }}%</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="wa-empty">No data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        {{-- Suspicious sessions (privacy-safe) --}}
        @if(!empty($tables['suspicious']))
        <section class="admin-section-block">
            <div class="wa-panel wa-panel-warn">
                <h3 class="wa-panel-title"><i class="fas fa-triangle-exclamation"></i> Quality Signal — High-Frequency Anonymous Sessions</h3>
                <p class="wa-note">Privacy-safe heuristic (salted session fingerprint, never an IP). May indicate self-clicking or bots; review before trusting affected clicks.</p>
                <div class="wa-table-wrap">
                    <table class="wa-table">
                        <thead><tr><th>Session (truncated)</th><th>Clicks</th><th>Providers</th><th>Last seen</th></tr></thead>
                        <tbody>
                            @foreach($tables['suspicious'] as $row)
                                <tr>
                                    <td><code>{{ $row['session'] }}</code></td>
                                    <td>{{ $row['clicks'] }}</td>
                                    <td>{{ $row['providers'] }}</td>
                                    <td>{{ $row['last_seen'] ? Carbon::parse($row['last_seen'])->diffForHumans() : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        @endif

        {{-- Recent activity --}}
        <section class="admin-section-block">
            <div class="admin-section-heading">
                <div>
                    <p class="admin-section-eyebrow">Live Feed</p>
                    <h2 class="admin-section-title">Recent WhatsApp Clicks</h2>
                </div>
            </div>
            <div class="wa-panel">
                <div class="wa-table-wrap">
                    <table class="wa-table">
                        <thead>
                            <tr><th>Time</th><th>Provider</th><th>Source</th><th>Locale</th><th>Device</th></tr>
                        </thead>
                        <tbody>
                            @forelse($recentActivity as $event)
                                <tr>
                                    <td title="{{ $event->created_at }}">{{ Carbon::parse($event->created_at)->diffForHumans() }}</td>
                                    <td>
                                        <a href="{{ route('admin.provider_activity_monitor.show', $event->provider_id) }}" class="wa-link">
                                            {{ $event->company_name }}
                                        </a>
                                    </td>
                                    <td>{{ $event->source_page ? ucfirst(str_replace('_', ' ', $event->source_page)) : '—' }}</td>
                                    <td>{{ $event->locale ? strtoupper($event->locale) : '—' }}</td>
                                    <td>{{ $event->device_type ? ucfirst($event->device_type) : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="wa-empty">No clicks recorded in this range.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="wa-pagination">
                    {{ $recentActivity->links() }}
                </div>
            </div>
        </section>

    </div>
</div>

@once
<style>
    .wa-filter-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:1.25rem 1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.05);}
    .wa-filter-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:1rem;}
    .wa-filter-grid label{display:flex;flex-direction:column;gap:.35rem;font-size:.78rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;}
    .wa-filter-grid input,.wa-filter-grid select{padding:.5rem .65rem;border:1px solid #cbd5e1;border-radius:9px;font-size:.9rem;font-weight:500;text-transform:none;letter-spacing:0;color:#0f172a;background:#fff;}
    .wa-filter-actions{display:flex;gap:.75rem;margin-top:1.1rem;flex-wrap:wrap;}
    .wa-panel{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:1.4rem 1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.05);}
    .wa-panel-warn{border-color:#fcd34d;background:#fffbeb;}
    .wa-panel-title{font-size:1rem;font-weight:800;color:#1e293b;margin:0 0 1rem;display:flex;align-items:center;gap:.5rem;}
    .wa-panel-title i{color:#10b981;}
    .wa-two-col{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:1.25rem;}
    .wa-three-col{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1.25rem;}
    .wa-bars{display:flex;align-items:flex-end;gap:.4rem;height:200px;padding-top:1rem;}
    .wa-bars-scroll{overflow-x:auto;}
    .wa-bar-col{flex:1;min-width:26px;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%;gap:.25rem;}
    .wa-bar{width:60%;min-height:2px;background:linear-gradient(180deg,#34d399,#059669);border-radius:6px 6px 0 0;transition:height .3s ease;}
    .wa-bar-thin{width:75%;}
    .wa-bar-val{font-size:.7rem;font-weight:700;color:#334155;}
    .wa-bar-lbl{font-size:.65rem;color:#94a3b8;white-space:nowrap;transform:rotate(0);}
    .wa-bar-lbl-sm{font-size:.6rem;}
    .wa-hbars{display:flex;flex-direction:column;gap:.6rem;}
    .wa-hbar-row{display:grid;grid-template-columns:90px 1fr 48px;align-items:center;gap:.6rem;}
    .wa-hbar-label{font-size:.8rem;font-weight:600;color:#475569;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .wa-hbar-track{height:10px;background:#eef2f7;border-radius:99px;overflow:hidden;}
    .wa-hbar-fill{height:100%;background:linear-gradient(90deg,#10b981,#059669);border-radius:99px;}
    .wa-hbar-val{font-size:.8rem;font-weight:700;color:#334155;text-align:right;}
    .wa-table-wrap{overflow-x:auto;}
    .wa-table{width:100%;border-collapse:collapse;font-size:.9rem;}
    .wa-table thead th{text-align:start;padding:.7rem 1rem;background:#f8fafc;color:#475569;font-weight:700;border-bottom:1px solid #e2e8f0;white-space:nowrap;}
    .wa-table tbody td{padding:.7rem 1rem;border-bottom:1px solid #f1f5f9;color:#334155;}
    .wa-table tbody tr:hover{background:#f8fafc;}
    .wa-pill{display:inline-block;padding:.15rem .6rem;border-radius:99px;background:#ecfdf5;color:#047857;font-weight:700;font-size:.8rem;}
    .wa-link{color:#2563eb;font-weight:600;text-decoration:none;}
    .wa-link:hover{text-decoration:underline;}
    .wa-empty{text-align:center;color:#94a3b8;font-style:italic;padding:1.5rem;}
    .wa-note{font-size:.82rem;color:#92400e;margin:-.4rem 0 1rem;}
    .wa-pagination{margin-top:1rem;}
    .wa-conv-good{color:#047857;}.wa-conv-mid{color:#b45309;}.wa-conv-bad{color:#b91c1c;}
</style>
@endonce
@endsection
