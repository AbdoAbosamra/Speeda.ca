@extends('layouts.app')

@section('title', 'Offer statistics')

@section('content')
@php
    $c = $summary['current'];
    $chg = $summary['change'];
    $sourceLabels = [
        'home' => 'Home page', 'offers' => 'Offers page', 'profile' => 'Provider profile', 'listing' => 'Provider search',
        'similar' => 'Related offers', 'share' => 'Shared link', 'direct' => 'Direct / other', 'unknown' => 'Unknown',
    ];
    $localeLabels = ['en' => 'English', 'ar' => 'Arabic', 'fr' => 'French', 'unknown' => 'Unknown'];
    $statusLabels = ['live' => 'Live', 'scheduled' => 'Scheduled', 'expired' => 'Expired', 'draft' => 'Draft', 'deleted' => 'Deleted'];
    $n = fn ($v) => number_format((float) $v);
    $baseQuery = array_filter([
        'range' => $f['range'],
        'from' => $f['range'] === 'custom' ? $f['from']->toDateString() : null,
        'to' => $f['range'] === 'custom' ? $f['to']->toDateString() : null,
        'offer_id' => $f['offer_id'], 'provider_id' => $f['provider_id'], 'source' => $f['source'], 'device' => $f['device'],
    ]);

    // Area chart for one daily series, drawn to a single scale.
    $chart = function (array $values, array $labels, string $color) {
        $w = 1000; $h = 220; $padL = 44; $padB = 26; $padT = 10;
        $count = count($values);
        $max = max(1, max($values ?: [0]));
        $step = $max <= 4 ? 1 : (int) ceil($max / 4);
        $mag = 10 ** max(0, strlen((string) $step) - 1);
        $step = (int) (ceil($step / $mag) * $mag);
        $top = $step * 4;
        $innerW = $w - $padL - 8;
        $innerH = $h - $padB - $padT;
        $x = fn ($i) => $padL + ($count > 1 ? $i * $innerW / ($count - 1) : $innerW / 2);
        $y = fn ($v) => $padT + $innerH - ($v / $top * $innerH);
        $pts = [];
        foreach ($values as $i => $v) { $pts[] = round($x($i), 1) . ',' . round($y($v), 1); }
        $line = implode(' ', $pts);
        $area = $count ? round($x(0), 1) . ',' . ($padT + $innerH) . ' ' . $line . ' ' . round($x($count - 1), 1) . ',' . ($padT + $innerH) : '';
        $every = max(1, (int) ceil($count / 8));
        return compact('w', 'h', 'padL', 'padB', 'padT', 'innerW', 'innerH', 'top', 'step', 'line', 'area', 'every', 'x', 'y', 'values', 'labels', 'color', 'count');
    };
    $series = [
        'views' => ['Offer page views', '#2563eb'],
        'clicks' => ['WhatsApp clicks', '#16a34a'],
        'impressions' => ['Card impressions', '#7c3aed'],
    ];
    $delta = function ($value) {
        if ($value === null) return '<span class="oa-delta is-up">new</span>';
        if ($value == 0) return '<span class="oa-delta">0%</span>';
        $cls = $value > 0 ? 'is-up' : 'is-down';
        return '<span class="oa-delta ' . $cls . '"><i class="fas fa-arrow-' . ($value > 0 ? 'up' : 'down') . '"></i> ' . abs($value) . '%</span>';
    };
@endphp

<div class="admin-cms-page ao-page oa-page" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
        <x-admin.header
            eyebrow="Local Offers"
            title="Offer statistics"
            :subtitle="'From ' . $f['from']->format('M j, Y') . ' to ' . $f['to']->format('M j, Y') . '. Admin visits and bots are never counted.'"
        >
            <x-slot:actions>
                <a href="{{ route('admin.offers.index') }}" class="admin-btn admin-btn-secondary"><i class="fas fa-tags"></i><span>Manage offers</span></a>
            </x-slot:actions>
        </x-admin.header>

        {{-- ============ FILTERS ============ --}}
        <form method="GET" class="ao-card oa-filters" x-data="{ range: @js($f['range']) }">
            <div class="ao-field">
                <label for="range">Period</label>
                <select name="range" id="range" class="form-select" x-model="range">
                    @foreach($ranges as $key => $label)
                        <option value="{{ $key }}" @selected($f['range'] === (string) $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ao-field" x-show="range === 'custom'" x-cloak>
                <label for="from">From</label>
                <input type="date" name="from" id="from" class="form-control" value="{{ $f['from']->toDateString() }}">
            </div>
            <div class="ao-field" x-show="range === 'custom'" x-cloak>
                <label for="to">To</label>
                <input type="date" name="to" id="to" class="form-control" value="{{ $f['to']->toDateString() }}">
            </div>
            <div class="ao-field">
                <label for="offer_id">Offer</label>
                <select name="offer_id" id="offer_id" class="form-select">
                    <option value="">All offers</option>
                    @foreach($offerOptions as $o)
                        <option value="{{ $o->id }}" @selected($f['offer_id'] === $o->id)>{{ $o->title_en }} — {{ $o->serviceProvider?->company_name }}{{ $o->deleted_at ? ' (deleted)' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ao-field">
                <label for="provider_id">Provider</label>
                <select name="provider_id" id="provider_id" class="form-select">
                    <option value="">All providers</option>
                    @foreach($providerOptions as $p)
                        <option value="{{ $p->id }}" @selected($f['provider_id'] === $p->id)>{{ $p->company_name ?: 'Provider #' . $p->id }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ao-field">
                <label for="source">Where visitors came from</label>
                <select name="source" id="source" class="form-select">
                    <option value="">All sources</option>
                    @foreach($sources as $s)
                        <option value="{{ $s }}" @selected($f['source'] === $s)>{{ $sourceLabels[$s] ?? $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ao-field">
                <label for="device">Device</label>
                <select name="device" id="device" class="form-select">
                    <option value="">All devices</option>
                    @foreach(['mobile', 'desktop', 'tablet'] as $d)
                        <option value="{{ $d }}" @selected($f['device'] === $d)>{{ ucfirst($d) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="oa-filter-actions">
                <button type="submit" class="admin-btn admin-btn-primary text-white"><i class="fas fa-filter"></i><span>Apply</span></button>
                <a href="{{ route('admin.offers.analytics') }}" class="admin-btn admin-btn-ghost"><span>Reset</span></a>
            </div>
        </form>

        @if($selectedOffer)
            <div class="ao-status ao-status--off">
                <i class="fas fa-filter"></i>
                <span>Showing one offer: <strong>{{ $selectedOffer->title_en }}</strong> by {{ $selectedOffer->providerName() }} ·
                    <a href="{{ route('offers.show', $selectedOffer->slug) }}" target="_blank" rel="noopener">public page</a>
                    @unless($selectedOffer->trashed()) · <a href="{{ route('admin.offers.edit', $selectedOffer) }}">edit</a>@endunless
                </span>
            </div>
        @endif

        {{-- ============ KPIs ============ --}}
        <div class="oa-kpis">
            @php
                $kpis = [
                    ['Card impressions', $n($c['impressions']), 'impressions', 'fa-eye', 'Times a card was seen on screen'],
                    ['Offer page views', $n($c['views']), 'views', 'fa-arrow-pointer', $n($c['unique_viewers']) . ' unique visitors'],
                    ['Click-through', $c['ctr'] . '%', 'ctr', 'fa-percent', 'Views ÷ impressions'],
                    ['WhatsApp clicks', $n($c['clicks']), 'clicks', 'fab fa-whatsapp', $n($summary['all_time_clicks']) . ' all time'],
                    ['Click rate', $c['click_rate'] . '%', 'click_rate', 'fa-bullseye', 'WhatsApp clicks ÷ page views'],
                    ['Likes', $n($c['likes']), 'likes', 'fa-heart', $n($c['favorites']) . ' saved to favorites'],
                    ['Live offers now', $n($summary['live_offers']), null, 'fa-bolt', $n($c['comments']) . ' comments written'],
                ];
            @endphp
            @foreach($kpis as [$label, $value, $key, $icon, $foot])
                <article class="oa-kpi">
                    <span class="oa-kpi-label"><i class="{{ str_starts_with($icon, 'fab') ? $icon : 'fas ' . $icon }}"></i> {{ $label }}</span>
                    <strong>{{ $value }}</strong>
                    <span class="oa-kpi-foot">
                        @if($key){!! $delta($chg[$key] ?? 0) !!} <span class="text-muted">vs previous period</span><br>@endif
                        {{ $foot }}
                    </span>
                </article>
            @endforeach
        </div>

        {{-- ============ FUNNEL ============ --}}
        <section class="ao-card oa-sec">
            <div class="oa-sec-head"><h2>Funnel</h2><span class="ao-hint">From seeing a card to messaging the provider</span></div>
            @php
                $funnel = [
                    ['Saw a card', $c['impressions'], '#7c3aed'],
                    ['Opened the offer', $c['views'], '#2563eb'],
                    ['Pressed “Get this offer on WhatsApp”', $c['clicks'], '#16a34a'],
                ];
                $fmax = max(1, max(array_column($funnel, 1)));
            @endphp
            <div class="oa-funnel">
                @foreach($funnel as $i => [$label, $value, $color])
                    <div class="oa-funnel-row">
                        <span class="oa-funnel-label">{{ $label }}</span>
                        <div class="oa-funnel-track"><div class="oa-funnel-bar" style="width: {{ max(1.5, $value / $fmax * 100) }}%; background: {{ $color }};"></div></div>
                        <span class="oa-funnel-val">{{ $n($value) }}</span>
                        <span class="oa-funnel-step">
                            @if($i > 0)
                                @php $prev = $funnel[$i - 1][1]; @endphp
                                {{ $prev > 0 ? round($value / $prev * 100, 1) : 0 }}% of previous
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>
            <div class="oa-engage">
                <div><b>{{ $n($c['shares']) }}</b><span>Offer links shared</span></div>
                <div><b>{{ $n($c['likes']) }}</b><span>Likes</span></div>
                <div><b>{{ $n($c['favorites']) }}</b><span>Saved to favorites (clients)</span></div>
                <div><b>{{ $n($c['comments']) }}</b><span>Comments written (approved or not)</span></div>
            </div>
        </section>

        {{-- ============ TREND ============ --}}
        <section class="ao-card oa-sec" x-data="{ metric: 'views' }">
            <div class="oa-sec-head">
                <h2>Day by day</h2>
                <div class="ao-seg" role="group" aria-label="Metric">
                    @foreach($series as $key => [$label])
                        <button type="button" :class="metric === '{{ $key }}' && 'is-on'" @click="metric = '{{ $key }}'">{{ $label }}</button>
                    @endforeach
                </div>
            </div>
            @foreach($series as $key => [$label, $color])
                @php $g = $chart($trend[$key], $trend['labels'], $color); $total = array_sum($trend[$key]); @endphp
                <div x-show="metric === '{{ $key }}'" @if($key !== 'views') x-cloak @endif>
                    <p class="ao-hint mb-2"><strong class="text-dark">{{ $n($total) }}</strong> {{ strtolower($label) }} in this period · best day {{ $n(max($trend[$key] ?: [0])) }}</p>
                    <div class="oa-chart">
                        <svg viewBox="0 0 {{ $g['w'] }} {{ $g['h'] }}" role="img" aria-label="{{ $label }} per day">
                            <defs>
                                <linearGradient id="grad-{{ $key }}" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0" stop-color="{{ $color }}" stop-opacity=".22"/>
                                    <stop offset="1" stop-color="{{ $color }}" stop-opacity="0"/>
                                </linearGradient>
                            </defs>
                            @for($gi = 0; $gi <= 4; $gi++)
                                @php $gy = $g['padT'] + $g['innerH'] - ($gi / 4 * $g['innerH']); @endphp
                                <line x1="{{ $g['padL'] }}" x2="{{ $g['w'] - 8 }}" y1="{{ $gy }}" y2="{{ $gy }}" stroke="#e2e8f0" stroke-width="1" vector-effect="non-scaling-stroke"/>
                                <text x="{{ $g['padL'] - 8 }}" y="{{ $gy + 4 }}" text-anchor="end" class="oa-axis">{{ $n($g['step'] * $gi) }}</text>
                            @endfor
                            @if($g['count'])
                                <polygon points="{{ $g['area'] }}" fill="url(#grad-{{ $key }})"/>
                                <polyline points="{{ $g['line'] }}" fill="none" stroke="{{ $color }}" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke"/>
                                @foreach($g['values'] as $i => $v)
                                    @php $isLast = $i === $g['count'] - 1; @endphp
                                    @if($isLast || ($i % $g['every'] === 0 && $g['count'] - 1 - $i >= max(1, intdiv($g['every'], 2))))
                                        <text x="{{ ($g['x'])($i) }}" y="{{ $g['h'] - 6 }}" text-anchor="{{ $isLast && $g['count'] > 1 ? 'end' : ($i === 0 && $g['count'] > 1 ? 'start' : 'middle') }}" class="oa-axis">{{ $g['labels'][$i] }}</text>
                                    @endif
                                    <circle cx="{{ ($g['x'])($i) }}" cy="{{ ($g['y'])($v) }}" r="{{ $g['count'] > 60 ? 0 : 3 }}" fill="#fff" stroke="{{ $color }}" stroke-width="2" vector-effect="non-scaling-stroke"><title>{{ $g['labels'][$i] }}: {{ $v }}</title></circle>
                                    <rect x="{{ ($g['x'])($i) - ($g['innerW'] / max(1, $g['count']) / 2) }}" y="{{ $g['padT'] }}" width="{{ $g['innerW'] / max(1, $g['count']) }}" height="{{ $g['innerH'] }}" fill="transparent"><title>{{ $g['labels'][$i] }}: {{ $v }} {{ strtolower($label) }}</title></rect>
                                @endforeach
                            @endif
                        </svg>
                    </div>
                </div>
            @endforeach
        </section>

        {{-- ============ BREAKDOWNS ============ --}}
        <div class="oa-three">
            @foreach([['Where visitors came from', 'fa-location-arrow', $bySource, $sourceLabels], ['Device', 'fa-mobile-screen', $byDevice, ['mobile' => 'Mobile', 'desktop' => 'Desktop', 'tablet' => 'Tablet', 'unknown' => 'Unknown']], ['Language', 'fa-language', $byLocale, $localeLabels]] as [$title, $icon, $rows, $labels])
                <section class="ao-card oa-sec">
                    <div class="oa-sec-head"><h2><i class="fas {{ $icon }}"></i> {{ $title }}</h2></div>
                    @php $bmax = max(1, collect($rows)->max('views') ?? 1, collect($rows)->max('clicks') ?? 1); @endphp
                    <div class="oa-hbars">
                        @forelse($rows as $row)
                            <div class="oa-hbar">
                                <div class="oa-hbar-top"><span>{{ $labels[$row['label']] ?? ucfirst($row['label']) }}</span><span class="ao-hint">{{ $row['rate'] }}% clicked</span></div>
                                <div class="oa-hbar-track"><div style="width: {{ max(1, $row['views'] / $bmax * 100) }}%; background: #93c5fd;"></div></div>
                                <div class="oa-hbar-track"><div style="width: {{ max(1, $row['clicks'] / $bmax * 100) }}%; background: #16a34a;"></div></div>
                                <div class="oa-hbar-nums ao-hint">{{ $n($row['views']) }} views · {{ $n($row['clicks']) }} WhatsApp clicks</div>
                            </div>
                        @empty
                            <p class="ao-hint mb-0">No data in this period.</p>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>

        {{-- ============ TIMING ============ --}}
        <div class="oa-two">
            <section class="ao-card oa-sec">
                <div class="oa-sec-head"><h2><i class="fas fa-clock"></i> WhatsApp clicks by hour of day</h2></div>
                @php $hmax = max(1, max($timing['hours'])); @endphp
                <div class="oa-cols">
                    @foreach($timing['hours'] as $hour => $v)
                        <div class="oa-col" title="{{ sprintf('%02d:00', $hour) }} — {{ $v }} clicks">
                            <div class="oa-col-bar" style="height: {{ max(2, $v / $hmax * 100) }}%;"></div>
                            <span>{{ $hour % 3 === 0 ? $hour : '' }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
            <section class="ao-card oa-sec">
                <div class="oa-sec-head"><h2><i class="fas fa-calendar-week"></i> WhatsApp clicks by weekday</h2></div>
                @php $wmax = max(1, max($timing['weekdays'])); @endphp
                <div class="oa-cols oa-cols-wide">
                    @foreach($timing['weekdays'] as $i => $v)
                        <div class="oa-col" title="{{ $timing['weekday_labels'][$i] }} — {{ $v }} clicks">
                            <b>{{ $v }}</b>
                            <div class="oa-col-bar" style="height: {{ max(2, $v / $wmax * 100) }}%;"></div>
                            <span>{{ $timing['weekday_labels'][$i] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        {{-- ============ PER OFFER ============ --}}
        <section class="ao-card oa-sec">
            <div class="oa-sec-head"><h2>Every offer</h2><span class="ao-hint">Sorted by WhatsApp clicks in this period</span></div>
            <div class="table-responsive">
                <table class="admin-data-table oa-table">
                    <thead>
                        <tr>
                            <th>Offer</th><th>Status</th><th class="text-end">Impressions</th><th class="text-end">Views</th><th class="text-end">Unique</th>
                            <th class="text-end">CTR</th><th class="text-end">WhatsApp clicks</th><th class="text-end">Click rate</th>
                            <th class="text-end">Likes</th><th class="text-end">Shares</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($offerRows as $row)
                            @php $o = $row['offer']; @endphp
                            <tr>
                                <td>
                                    <div class="admin-table-title">{{ $o->title_en }}</div>
                                    <div class="admin-table-subtitle">{{ $o->providerName() }} · <span class="ao-deal ao-deal--{{ $o->deal_type }}" style="font-size:.68rem;padding:.1rem .4rem">{{ $o->badgeLabel('en') }}</span></div>
                                </td>
                                <td><span class="ao-pill ao-pill--{{ $row['status'] }}">{{ $statusLabels[$row['status']] }}</span></td>
                                <td class="text-end">{{ $n($row['impressions']) }}</td>
                                <td class="text-end">{{ $n($row['views']) }}</td>
                                <td class="text-end">{{ $n($row['unique_viewers']) }}</td>
                                <td class="text-end">{{ $row['ctr'] }}%</td>
                                <td class="text-end fw-bold">{{ $n($row['clicks']) }}</td>
                                <td class="text-end"><span class="oa-rate" style="--r: {{ min(100, $row['click_rate']) }}%">{{ $row['click_rate'] }}%</span></td>
                                <td class="text-end">{{ $n($row['likes']) }}</td>
                                <td class="text-end">{{ $n($row['shares']) }}</td>
                                <td class="text-end"><a href="{{ route('admin.offers.analytics', array_merge($baseQuery, ['offer_id' => $o->id])) }}" class="admin-icon-action" title="Only this offer"><i class="fas fa-magnifying-glass-chart"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="text-center text-muted py-4">No offers yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- ============ ROLL-UPS ============ --}}
        <section class="ao-card oa-sec" x-data="{ tab: 'provider' }">
            <div class="oa-sec-head">
                <h2>Grouped</h2>
                <div class="ao-seg" role="group" aria-label="Group by">
                    <button type="button" :class="tab === 'provider' && 'is-on'" @click="tab = 'provider'">By provider</button>
                    <button type="button" :class="tab === 'category' && 'is-on'" @click="tab = 'category'">By category</button>
                    <button type="button" :class="tab === 'city' && 'is-on'" @click="tab = 'city'">By city</button>
                </div>
            </div>
            @foreach(['provider' => $providerRows, 'category' => $categoryRows, 'city' => $cityRows] as $key => $rows)
                <div class="table-responsive" x-show="tab === '{{ $key }}'" @if($key !== 'provider') x-cloak @endif>
                    <table class="admin-data-table oa-table">
                        <thead><tr><th>{{ ucfirst($key) }}</th><th class="text-end">Offers</th><th class="text-end">Impressions</th><th class="text-end">Views</th><th class="text-end">WhatsApp clicks</th><th class="text-end">Click rate</th><th class="text-end">Likes</th></tr></thead>
                        <tbody>
                            @forelse($rows as $row)
                                <tr>
                                    <td class="fw-semibold">
                                        @if($row['provider_id'])
                                            <a href="{{ route('admin.offers.analytics', array_merge($baseQuery, ['provider_id' => $row['provider_id'], 'offer_id' => null])) }}">{{ $row['label'] }}</a>
                                        @else
                                            {{ $row['label'] }}
                                        @endif
                                    </td>
                                    <td class="text-end">{{ $row['offers'] }}</td>
                                    <td class="text-end">{{ $n($row['impressions']) }}</td>
                                    <td class="text-end">{{ $n($row['views']) }}</td>
                                    <td class="text-end fw-bold">{{ $n($row['clicks']) }}</td>
                                    <td class="text-end">{{ $row['click_rate'] }}%</td>
                                    <td class="text-end">{{ $n($row['likes']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">No data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endforeach
        </section>

    </div>
</div>

@include('admin.offers.partials.styles')
@push('styles')
<style>
    [x-cloak] { display: none !important; }
    .oa-page .ao-card { margin-bottom: 1.1rem; }
    .oa-filters { padding: 1rem 1.1rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: .8rem; align-items: end; }
    .oa-filter-actions { display: flex; gap: .5rem; }
    .oa-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .9rem; margin-bottom: 1.1rem; }
    @media (max-width: 1199.98px) { .oa-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 575.98px) { .oa-kpis { grid-template-columns: 1fr; } }
    .oa-kpi { background: #fff; border: 1px solid rgba(148, 163, 184, .2); border-radius: 18px; padding: 1rem 1.1rem; display: grid; gap: .3rem; box-shadow: 0 16px 28px -26px rgba(15, 23, 42, .3); }
    .oa-kpi-label { font-size: .74rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: #64748b; }
    .oa-kpi-label i { color: #2563eb; }
    .oa-kpi strong { font-size: 1.9rem; font-weight: 800; color: #0f172a; line-height: 1.1; font-variant-numeric: tabular-nums; }
    .oa-kpi-foot { font-size: .78rem; color: #64748b; }
    .oa-delta { font-weight: 800; color: #64748b; }
    .oa-delta.is-up { color: #059669; }
    .oa-delta.is-down { color: #e11d48; }
    .oa-sec { padding: 1.1rem 1.25rem; }
    .oa-sec-head { display: flex; justify-content: space-between; align-items: center; gap: .75rem; flex-wrap: wrap; margin-bottom: .9rem; }
    .oa-sec-head h2 { font-size: 1.05rem; font-weight: 800; margin: 0; color: #0f172a; }
    .oa-sec-head h2 i { color: #2563eb; margin-inline-end: .3rem; }
    .oa-funnel { display: grid; gap: .6rem; }
    .oa-funnel-row { display: grid; grid-template-columns: 180px 1fr 80px 120px; gap: .8rem; align-items: center; }
    @media (max-width: 767.98px) { .oa-funnel-row { grid-template-columns: 1fr 70px; } .oa-funnel-track { grid-column: 1 / -1; order: 3; } .oa-funnel-step { grid-column: 1 / -1; order: 4; } }
    .oa-funnel-label { font-weight: 700; font-size: .88rem; color: #334155; }
    .oa-funnel-track { height: 16px; background: #f1f5f9; border-radius: 999px; overflow: hidden; }
    .oa-funnel-bar { height: 100%; border-radius: 999px; }
    .oa-funnel-val { font-weight: 800; color: #0f172a; text-align: end; font-variant-numeric: tabular-nums; }
    .oa-funnel-step { font-size: .78rem; color: #64748b; }
    .oa-engage { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: .7rem; margin-top: 1rem; }
    .oa-engage div { background: #f8fafc; border-radius: 12px; padding: .7rem .85rem; }
    .oa-engage b { display: block; font-size: 1.25rem; color: #0f172a; font-variant-numeric: tabular-nums; }
    .oa-engage span { font-size: .78rem; color: #64748b; }
    .oa-chart { width: 100%; overflow: hidden; }
    .oa-chart svg { width: 100%; height: auto; display: block; }
    .oa-axis { font-size: 11px; fill: #94a3b8; }
    .oa-three { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1.1rem; }
    .oa-two { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.1rem; }
    @media (max-width: 991.98px) { .oa-three, .oa-two { grid-template-columns: 1fr; } }
    .oa-hbars { display: grid; gap: .8rem; }
    .oa-hbar { display: grid; gap: .25rem; }
    .oa-hbar-top { display: flex; justify-content: space-between; font-weight: 700; font-size: .85rem; color: #334155; }
    .oa-hbar-track { height: 7px; background: #f1f5f9; border-radius: 999px; overflow: hidden; }
    .oa-hbar-track div { height: 100%; border-radius: 999px; }
    .oa-cols { display: flex; align-items: flex-end; gap: 3px; height: 170px; }
    .oa-col { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; gap: 4px; min-width: 0; }
    .oa-col-bar { width: 100%; background: linear-gradient(180deg, #4ade80, #16a34a); border-radius: 5px 5px 2px 2px; }
    .oa-col span { font-size: .68rem; color: #94a3b8; height: 14px; }
    .oa-col b { font-size: .75rem; color: #334155; }
    .oa-cols-wide { gap: 10px; }
    .oa-table th { white-space: nowrap; }
    .oa-table td { font-variant-numeric: tabular-nums; }
    .oa-rate { font-weight: 700; background: linear-gradient(90deg, #dcfce7 var(--r), transparent var(--r)); border-radius: 6px; padding: .1rem .4rem; }
</style>
@endpush
@endsection
