@extends('layouts.app')

@section('title', 'Visitor Analytics')

@section('content')
    @php
        $maxVisitors = max(1, (int) collect($analytics['visitors_by_date'])->max('count'));
        $periodLabels = [
            'last_7_days' => 'Last 7 Days',
            'last_30_days' => 'Last 30 Days',
            'last_12_months' => 'Last 12 Months',
        ];
    @endphp

    <div class="admin-cms-page" dir="ltr">
        <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
            <section class="admin-page-header">
                <div>
                    <p class="admin-section-eyebrow">Analytics</p>
                    <h1>Visitor Analytics</h1>
                    <p>Review privacy-safe traffic trends and top public pages.</p>
                </div>
                <a href="{{ route('admin.visitors.export', ['period' => $period]) }}" class="admin-btn admin-btn-secondary">
                    <i class="fas fa-download"></i>
                    <span>Export CSV</span>
                </a>
            </section>

            <div class="admin-stat-grid">
                <article class="admin-stat-card">
                    <div class="admin-stat-head">
                        <span class="admin-stat-icon"><i class="fas fa-users"></i></span>
                        <span class="admin-stat-label">Total Visitors</span>
                    </div>
                    <strong class="admin-stat-value">{{ number_format($stats['total_visitors']) }}</strong>
                    <span class="admin-stat-foot">All time unique visitors</span>
                </article>

                <article class="admin-stat-card">
                    <div class="admin-stat-head">
                        <span class="admin-stat-icon admin-stat-icon-sky"><i class="fas fa-calendar-week"></i></span>
                        <span class="admin-stat-label">Last 7 Days</span>
                    </div>
                    <strong class="admin-stat-value">{{ number_format($stats['last_7_days']) }}</strong>
                    <span class="admin-stat-foot">Unique visitors</span>
                </article>

                <article class="admin-stat-card">
                    <div class="admin-stat-head">
                        <span class="admin-stat-icon admin-stat-icon-rose"><i class="fas fa-calendar-days"></i></span>
                        <span class="admin-stat-label">Last 30 Days</span>
                    </div>
                    <strong class="admin-stat-value">{{ number_format($stats['last_30_days']) }}</strong>
                    <span class="admin-stat-foot">Unique visitors</span>
                </article>

                <article class="admin-stat-card admin-stat-card-live">
                    <div class="admin-stat-head">
                        <span class="admin-stat-icon admin-stat-icon-teal"><i class="fas fa-signal"></i></span>
                        <span class="admin-stat-label">Live Visitors</span>
                    </div>
                    <strong class="admin-stat-value" id="liveCount">{{ number_format($stats['live_visitors']) }}</strong>
                    <span class="admin-stat-foot admin-stat-pill"><span class="admin-live-dot"></span>Active now</span>
                </article>
            </div>

            <section class="admin-section-block mt-4">
                <form method="GET" action="{{ route('admin.visitors') }}" class="admin-filter-bar">
                    <label class="admin-filter-field">
                        <span>Reporting Period</span>
                        <select name="period" onchange="this.form.submit()">
                            @foreach($periodLabels as $value => $label)
                                <option value="{{ $value }}" @selected($period === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="admin-filter-actions">
                        <span class="admin-muted-note">Showing {{ $periodLabels[$period] ?? 'Last 30 Days' }}</span>
                    </div>
                </form>
            </section>

            <div class="admin-analytics-grid">
                <section class="admin-table-card admin-chart-card">
                    <div class="admin-card-header">
                        <div>
                            <h2>Visitors By Date</h2>
                            <p>Unique visitor trend for the selected period.</p>
                        </div>
                    </div>

                    @forelse($analytics['visitors_by_date'] as $data)
                        <div class="admin-chart-row">
                            <span>{{ $data->date }}</span>
                            <div class="admin-chart-bar-track">
                                <div class="admin-chart-bar" style="width: {{ max(4, ((int) $data->count / $maxVisitors) * 100) }}%"></div>
                            </div>
                            <strong>{{ number_format($data->count) }}</strong>
                        </div>
                    @empty
                        <div class="admin-empty-state">
                            <i class="fas fa-chart-simple"></i>
                            <h2>No visitor data</h2>
                            <p>Traffic will appear here after visits are tracked.</p>
                        </div>
                    @endforelse
                </section>

                <section class="admin-table-card">
                    <div class="admin-card-header">
                        <div>
                            <h2>Top Pages</h2>
                            <p>Most visited public paths with privacy-safe reporting.</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="admin-data-table">
                            <thead>
                                <tr>
                                    <th>Page</th>
                                    <th>Visits</th>
                                    <th>Unique</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analytics['top_pages'] as $page)
                                    <tr>
                                        <td><span class="admin-table-title">{{ $page->path }}</span></td>
                                        <td>{{ number_format($page->visits) }}</td>
                                        <td>{{ number_format($page->unique_visitors) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">
                                            <div class="admin-empty-state">
                                                <i class="fas fa-file-lines"></i>
                                                <h2>No page data</h2>
                                                <p>Top pages will appear after traffic is recorded.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script>
        (() => {
            async function updateLiveCount() {
                try {
                    const response = await fetch('{{ route('admin.visitors.live-count') }}', {
                        headers: {'X-Requested-With': 'XMLHttpRequest'},
                    });
                    if (!response.ok) return;
                    const data = await response.json();
                    if (data.success) {
                        document.getElementById('liveCount').textContent = data.count;
                    }
                } catch (error) {
                    console.warn('Live visitor refresh failed.', error);
                }
            }

            window.setInterval(updateLiveCount, 30000);
        })();
    </script>
@endsection
