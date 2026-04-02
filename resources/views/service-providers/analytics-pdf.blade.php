<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('service_provider.analytics_report') }} - Speeda</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #1a1a2e;
            font-size: 12px;
            line-height: 1.6;
            padding: 40px;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #4361ee;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 24px;
            color: #4361ee;
            margin-bottom: 5px;
        }
        .header .subtitle {
            color: #666;
            font-size: 14px;
        }
        .provider-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .provider-info h2 {
            font-size: 18px;
            color: #1a1a2e;
            margin-bottom: 10px;
        }
        .provider-info p {
            color: #555;
            margin-bottom: 5px;
        }
        .stats-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .stats-grid th {
            background: linear-gradient(135deg, #4361ee, #3f37c9);
            color: white;
            padding: 12px 20px;
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
            font-size: 13px;
        }
        .stats-grid td {
            padding: 12px 20px;
            border-bottom: 1px solid #e9ecef;
            font-size: 13px;
        }
        .stats-grid tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .stats-grid .value {
            font-weight: bold;
            font-size: 16px;
            color: #4361ee;
        }
        .section-title {
            font-size: 16px;
            color: #4361ee;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e9ecef;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #999;
            font-size: 10px;
            border-top: 1px solid #e9ecef;
            padding-top: 15px;
        }
        .period-badge {
            display: inline-block;
            background: #e8ecff;
            color: #4361ee;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>SPEEDA</h1>
        <div class="subtitle">{{ __('service_provider.analytics_report') }}</div>
    </div>

    <div class="provider-info">
        <h2>{{ $serviceProvider->company_name ?? $serviceProvider->user->name }}</h2>
        <p><strong>{{ __('service_provider.category_label') }}</strong> {{ $serviceProvider->category->translated_name ?? '-' }}</p>
        <p><strong>{{ __('service_provider.report_generated') }}:</strong> {{ now()->format('Y-m-d H:i') }}</p>
        <p>
            <span class="period-badge">
                {{ $monthly['period_start'] }} → {{ $monthly['period_end'] }}
            </span>
        </p>
    </div>

    {{-- Weekly Stats --}}
    <h3 class="section-title">{{ __('service_provider.weekly_performance') }}</h3>
    <table class="stats-grid">
        <thead>
            <tr>
                <th>{{ __('service_provider.metric') }}</th>
                <th>{{ __('service_provider.value') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ __('service_provider.dashboard_views_today') }}</td>
                <td class="value">{{ number_format($stats['views_today']) }}</td>
            </tr>
            <tr>
                <td>{{ __('service_provider.dashboard_views_this_week') }}</td>
                <td class="value">{{ number_format($stats['views_this_week']) }}</td>
            </tr>
            <tr>
                <td>{{ __('service_provider.dashboard_total_clicks') }}</td>
                <td class="value">{{ number_format($stats['total_clicks']) }}</td>
            </tr>
            <tr>
                <td>{{ __('service_provider.dashboard_engagement_rate') }}</td>
                <td class="value">{{ number_format($stats['engagement_rate'], 2) }}%</td>
            </tr>
        </tbody>
    </table>

    {{-- Monthly Stats --}}
    <h3 class="section-title">{{ __('service_provider.monthly_performance') }}</h3>
    <table class="stats-grid">
        <thead>
            <tr>
                <th>{{ __('service_provider.metric') }}</th>
                <th>{{ __('service_provider.value') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ __('service_provider.total_views_30_days') }}</td>
                <td class="value">{{ number_format($monthly['total_views']) }}</td>
            </tr>
            <tr>
                <td>{{ __('service_provider.total_clicks_30_days') }}</td>
                <td class="value">{{ number_format($monthly['total_clicks']) }}</td>
            </tr>
            <tr>
                <td>{{ __('service_provider.conversion_rate_30_days') }}</td>
                <td class="value">{{ number_format($monthly['engagement_rate'], 2) }}%</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Speeda &copy; {{ date('Y') }} &mdash; {{ __('service_provider.analytics_report_footer') }}
    </div>
</body>
</html>
