{{-- resources/views/service-providers/dashboard.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('service_provider.analytics_dashboard') }} - Speeda</title>

    <link rel="icon" type="image/png" href="{{ asset('images/New_logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">

    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3f37c9;
            --accent: #f72585;
            --success: #10b981;
            --warning: #f59e0b;
        }
        body {
            font-family: 'Figtree', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #f5f7ff 0%, #e9ecef 100%);
            color: #1a1a2e;
            min-height: 100vh;
        }
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            border-radius: 20px 20px 0 0;
        }
        .stat-card.views::before { background: linear-gradient(90deg, var(--primary), var(--primary-dark)); }
        .stat-card.clicks::before { background: linear-gradient(90deg, var(--accent), #b5179e); }
        .stat-card.engagement::before { background: linear-gradient(90deg, var(--success), #059669); }
        .stat-card.weekly::before { background: linear-gradient(90deg, var(--warning), #d97706); }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }
        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: white;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.2;
        }
        .stat-label {
            font-size: 0.85rem;
            color: #6b7280;
            font-weight: 500;
        }
        .chart-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }
        .chart-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1a1a2e;
            margin-bottom: 1rem;
        }
        .breadcrumb-custom {
            background: rgba(255,255,255,0.8);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 0.75rem 1.25rem;
        }
        .completion-bar-container {
            background: white;
            border-radius: 20px;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }
        .completion-bar {
            height: 12px;
            border-radius: 6px;
            background: #e9ecef;
            overflow: hidden;
        }
        .completion-bar .fill {
            height: 100%;
            border-radius: 6px;
            transition: width 1s ease;
        }
    </style>
</head>
<body>
    @include('components.main-nav')
    @include('components.notification-card')

    <div class="container py-4">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb breadcrumb-custom mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="fas fa-home me-1"></i>{{ __('general.home') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('service-providers.show', $serviceProvider->id) }}"><i class="fas fa-user me-1"></i>{{ __('service_provider.my_profile') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('service_provider.analytics_dashboard') }}</li>
            </ol>
        </nav>

        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
            <div>
                <h1 class="fw-bold mb-1" style="color: var(--primary);">
                    <i class="fas fa-chart-line me-2"></i>{{ __('service_provider.analytics_dashboard') }}
                </h1>
                <div class="text-muted">
                    {{ $serviceProvider->company_name ?? $serviceProvider->user->name }}
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('service-providers.analytics.export-pdf') }}" class="btn btn-outline-danger rounded-pill px-3">
                    <i class="fas fa-file-pdf me-2"></i>{{ __('service_provider.export_pdf') }}
                </a>
                <a href="{{ route('service-providers.show', $serviceProvider->id) }}" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="fas fa-arrow-left me-2"></i>{{ __('service_provider.back_to_profile') }}
                </a>
            </div>
        </div>

        {{-- Profile Completion Bar --}}
        @php $pct = $serviceProvider->profile_completion_percent ?? 0; @endphp
        <div class="completion-bar-container mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-semibold"><i class="fas fa-tasks text-primary me-2"></i>{{ __('service_provider.profile_completion_title') }}</span>
                <span class="badge rounded-pill @if($pct >= 80) bg-success @elseif($pct >= 50) bg-warning text-dark @else bg-danger @endif px-3">
                    {{ $pct }}%
                </span>
            </div>
            <div class="completion-bar">
                <div class="fill" style="width: {{ $pct }}%; background: linear-gradient(90deg, @if($pct >= 80)#10b981,#059669 @elseif($pct >= 50) #f59e0b,#d97706 @else #ef4444,#dc2626 @endif);"></div>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stat-card views">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="stat-icon" style="background: linear-gradient(135deg, var(--primary), var(--primary-dark));">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div class="stat-label">{{ __('service_provider.dashboard_views_today') }}</div>
                    </div>
                    <div class="stat-value" style="color: var(--primary);">{{ number_format($stats['views_today']) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card weekly">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="stat-icon" style="background: linear-gradient(135deg, var(--warning), #d97706);">
                            <i class="fas fa-calendar-week"></i>
                        </div>
                        <div class="stat-label">{{ __('service_provider.dashboard_views_this_week') }}</div>
                    </div>
                    <div class="stat-value" style="color: var(--warning);">{{ number_format($stats['views_this_week']) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card clicks">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="stat-icon" style="background: linear-gradient(135deg, var(--accent), #b5179e);">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <div class="stat-label">{{ __('service_provider.dashboard_total_clicks') }}</div>
                    </div>
                    <div class="stat-value" style="color: var(--accent);">{{ number_format($stats['total_clicks']) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card engagement">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="stat-icon" style="background: linear-gradient(135deg, var(--success), #059669);">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="stat-label">{{ __('service_provider.dashboard_engagement_rate') }}</div>
                    </div>
                    <div class="stat-value" style="color: var(--success);">{{ number_format($stats['engagement_rate'], 1) }}%</div>
                </div>
            </div>
        </div>

        {{-- Charts --}}
        <div class="row g-3 mb-4">
            <div class="col-lg-7">
                <div class="chart-card">
                    <div class="chart-title"><i class="fas fa-chart-area text-primary me-2"></i>{{ __('service_provider.views_trend') }}</div>
                    <canvas id="viewsChart" height="260"></canvas>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="chart-card">
                    <div class="chart-title"><i class="fas fa-chart-bar text-danger me-2"></i>{{ __('service_provider.clicks_distribution') }}</div>
                    <canvas id="clicksChart" height="260"></canvas>
                </div>
            </div>
        </div>

        {{-- Back Button --}}
        <div class="text-center mt-3 mb-5">
            <a href="{{ route('service-providers.show', $serviceProvider->id) }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fas fa-arrow-left me-2"></i>{{ __('service_provider.back_to_profile') }}
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const labels = @json($trends['labels']);
            const viewsData = @json($trends['views']);
            const clicksData = @json($trends['clicks']);

            // Views Line Chart
            new Chart(document.getElementById('viewsChart'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '{{ __("service_provider.views_label") }}',
                        data: viewsData,
                        borderColor: '#4361ee',
                        backgroundColor: 'rgba(67, 97, 238, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#4361ee',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1a1a2e',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            padding: 12,
                            cornerRadius: 10,
                            displayColors: false,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, color: '#9ca3af' },
                            grid: { color: 'rgba(0,0,0,0.04)' }
                        },
                        x: {
                            ticks: { color: '#9ca3af' },
                            grid: { display: false }
                        }
                    }
                }
            });

            // Clicks Bar Chart
            new Chart(document.getElementById('clicksChart'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '{{ __("service_provider.dashboard_total_clicks") }}',
                        data: clicksData,
                        backgroundColor: 'rgba(247, 37, 133, 0.7)',
                        borderColor: '#f72585',
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1a1a2e',
                            padding: 12,
                            cornerRadius: 10,
                            displayColors: false,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, color: '#9ca3af' },
                            grid: { color: 'rgba(0,0,0,0.04)' }
                        },
                        x: {
                            ticks: { color: '#9ca3af' },
                            grid: { display: false }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
