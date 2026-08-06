@extends('layouts.app')

@section('title', 'Email Journey – Admin')

@push('styles')
<style>
    /* ============ EMAIL JOURNEY ADMIN PANEL ============ */
    .ej-page { padding: 32px 0; }

    .ej-hero {
        background: linear-gradient(135deg, #0F1F3D 0%, #1E3A8A 100%);
        border-radius: 20px;
        padding: 36px 40px;
        margin-bottom: 32px;
        color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 24px;
        flex-wrap: wrap;
    }

    .ej-hero-title { font-size: 26px; font-weight: 800; margin: 0 0 6px; letter-spacing: -0.5px; }
    .ej-hero-sub   { font-size: 14px; color: rgba(255,255,255,0.65); margin: 0; }

    .ej-hero-actions { display: flex; gap: 12px; flex-wrap: wrap; }

    .ej-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 20px; border-radius: 100px; font-size: 14px; font-weight: 600;
        text-decoration: none; border: none; cursor: pointer; transition: all 0.2s;
    }
    .ej-btn-white  { background: #fff; color: #0F1F3D; }
    .ej-btn-white:hover { background: #F0F4FA; color: #0F1F3D; }
    .ej-btn-gold   { background: #F59E0B; color: #fff; }
    .ej-btn-gold:hover { background: #D97706; color: #fff; }
    .ej-btn-outline { background: transparent; color: #fff; border: 1.5px solid rgba(255,255,255,0.3); }
    .ej-btn-outline:hover { background: rgba(255,255,255,0.1); color: #fff; }

    /* Stats Grid */
    .ej-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        margin-bottom: 32px;
    }

    .ej-stat-card {
        background: #fff;
        border-radius: 16px;
        padding: 24px;
        border: 1px solid #E2E8F0;
        text-align: center;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }

    .ej-stat-icon {
        font-size: 28px;
        margin-bottom: 8px;
        display: block;
    }

    .ej-stat-number {
        font-size: 32px;
        font-weight: 800;
        color: #0F1F3D;
        display: block;
        line-height: 1;
        margin-bottom: 4px;
    }

    .ej-stat-label {
        font-size: 12px;
        color: #64748B;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Email Type Breakdown */
    .ej-breakdown {
        background: #fff;
        border-radius: 16px;
        padding: 28px;
        border: 1px solid #E2E8F0;
        margin-bottom: 28px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }

    .ej-breakdown-title {
        font-size: 16px;
        font-weight: 700;
        color: #0F1F3D;
        margin: 0 0 20px;
    }

    .ej-type-row {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 10px 0;
        border-bottom: 1px solid #F1F5F9;
    }
    .ej-type-row:last-child { border-bottom: none; }

    .ej-type-label { font-size: 14px; font-weight: 500; color: #374151; width: 180px; flex-shrink: 0; }
    .ej-type-bar-bg { flex: 1; background: #F1F5F9; border-radius: 100px; height: 8px; }
    .ej-type-bar-fill { height: 8px; border-radius: 100px; background: linear-gradient(90deg, #1D4ED8, #3B82F6); }
    .ej-type-count { font-size: 14px; font-weight: 700; color: #0F1F3D; width: 50px; text-align: right; flex-shrink: 0; }

    /* Preview Tiles */
    .ej-preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 32px;
    }

    .ej-preview-tile {
        background: #fff;
        border: 1.5px solid #E2E8F0;
        border-radius: 14px;
        padding: 20px;
        text-align: center;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s;
    }
    .ej-preview-tile:hover {
        border-color: #3B82F6;
        box-shadow: 0 4px 20px rgba(59,130,246,0.15);
        transform: translateY(-2px);
        color: inherit;
    }
    .ej-preview-emoji { font-size: 32px; display: block; margin-bottom: 10px; }
    .ej-preview-name  { font-size: 13px; font-weight: 600; color: #0F1F3D; }
    .ej-preview-type  { font-size: 11px; color: #94A3B8; margin-top: 4px; }

    /* Provider Table */
    .ej-table-wrap {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #E2E8F0;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }

    .ej-table-head {
        padding: 20px 24px;
        border-bottom: 1px solid #E2E8F0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .ej-table-head-title { font-size: 16px; font-weight: 700; color: #0F1F3D; margin: 0; }

    .ej-table { width: 100%; border-collapse: collapse; }
    .ej-table th {
        background: #F8FAFC;
        padding: 12px 20px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        color: #64748B;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #E2E8F0;
    }
    .ej-table td {
        padding: 16px 20px;
        border-bottom: 1px solid #F1F5F9;
        font-size: 14px;
        color: #374151;
        vertical-align: middle;
    }
    .ej-table tr:last-child td { border-bottom: none; }
    .ej-table tr:hover td { background: #F8FAFC; }

    .ej-provider-name { font-weight: 600; color: #0F1F3D; }
    .ej-provider-email { font-size: 12px; color: #94A3B8; }

    .ej-badge {
        display: inline-flex; align-items: center;
        padding: 4px 10px; border-radius: 100px;
        font-size: 12px; font-weight: 600;
    }
    .ej-badge-blue   { background: #DBEAFE; color: #1D4ED8; }
    .ej-badge-green  { background: #DCFCE7; color: #15803D; }
    .ej-badge-amber  { background: #FEF3C7; color: #B45309; }
    .ej-badge-gray   { background: #F1F5F9; color: #475569; }

    .ej-action-link {
        font-size: 13px; font-weight: 600; color: #1D4ED8;
        text-decoration: none;
    }
    .ej-action-link:hover { text-decoration: underline; }

    /* Section headings */
    .ej-section-title {
        font-size: 18px; font-weight: 700; color: #0F1F3D;
        margin: 0 0 16px;
    }
</style>
@endpush

@section('content')
<div class="ej-page">
    <div class="container-fluid px-4">

        {{-- Hero --}}
        <div class="ej-hero">
            <div>
                <h1 class="ej-hero-title">📧 Email Journey Dashboard</h1>
                <p class="ej-hero-sub">
                    Automated onboarding emails · {{ number_format($stats['total_sent']) }} emails sent ·
                    {{ number_format($stats['total_providers']) }} providers reached
                </p>
            </div>
            <div class="ej-hero-actions">
                <a href="{{ route('admin.email_journey.index') }}" class="ej-btn ej-btn-outline">
                    <i class="fas fa-sync-alt"></i> Refresh
                </a>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="ej-stats-grid">
            <div class="ej-stat-card">
                <span class="ej-stat-icon">📨</span>
                <span class="ej-stat-number">{{ number_format($stats['total_sent']) }}</span>
                <span class="ej-stat-label">Total Emails Sent</span>
            </div>
            <div class="ej-stat-card">
                <span class="ej-stat-icon">👥</span>
                <span class="ej-stat-number">{{ number_format($stats['total_providers']) }}</span>
                <span class="ej-stat-label">Providers Reached</span>
            </div>
            <div class="ej-stat-card">
                <span class="ej-stat-icon">⏰</span>
                <span class="ej-stat-number">{{ number_format($stats['pending_resends']) }}</span>
                <span class="ej-stat-label">Pending Resends (24h)</span>
            </div>
            @php
                $totalByType = array_sum($stats['by_type']);
                $welcomeCount = $stats['by_type']['welcome'] ?? 0;
            @endphp
            <div class="ej-stat-card">
                <span class="ej-stat-icon">📊</span>
                <span class="ej-stat-number">{{ $totalByType > 0 ? number_format(($stats['by_type']['complete'] ?? 0) / max($welcomeCount,1) * 100, 0) : 0 }}%</span>
                <span class="ej-stat-label">Journey Completion Rate</span>
            </div>
        </div>

        {{-- Email Type Breakdown --}}
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="ej-breakdown">
                    <h3 class="ej-breakdown-title">📊 Emails Sent by Type</h3>
                    @php $maxCount = max(1, max(array_values($stats['by_type']) ?: [1])); @endphp
                    @foreach($emailTypeLabels as $type => $label)
                        @php $count = $stats['by_type'][$type] ?? 0; @endphp
                        <div class="ej-type-row">
                            <span class="ej-type-label">{{ $label }}</span>
                            <div class="ej-type-bar-bg">
                                <div class="ej-type-bar-fill" style="width: {{ $maxCount > 0 ? round(($count/$maxCount)*100) : 0 }}%"></div>
                            </div>
                            <span class="ej-type-count">{{ number_format($count) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-md-6">
                {{-- Email Preview Tiles --}}
                <h3 class="ej-section-title">👁️ Preview Email Templates</h3>
                <div class="ej-preview-grid">
                    @foreach([
                        ['type'=>'welcome','icon'=>'🎉','name'=>'Welcome'],
                        ['type'=>'photo','icon'=>'📸','name'=>'Add Photo'],
                        ['type'=>'services','icon'=>'🛠️','name'=>'Add Services'],
                        ['type'=>'bio','icon'=>'📝','name'=>'Write Bio'],
                        ['type'=>'experience','icon'=>'📅','name'=>'Experience'],
                        ['type'=>'gallery','icon'=>'🖼️','name'=>'Gallery'],
                        ['type'=>'service_areas','icon'=>'🌍','name'=>'Service Areas'],
                        ['type'=>'complete','icon'=>'🏆','name'=>'Complete!'],
                        ['type'=>'reviews','icon'=>'⭐','name'=>'Reviews'],
                    ] as $email)
                        <a href="{{ route('admin.email_journey.preview', $email['type']) }}"
                           target="_blank" class="ej-preview-tile">
                            <span class="ej-preview-emoji">{{ $email['icon'] }}</span>
                            <div class="ej-preview-name">{{ $email['name'] }}</div>
                            <div class="ej-preview-type">{{ $email['type'] }}</div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Provider Table --}}
        <div class="ej-table-wrap">
            <div class="ej-table-head">
                <h3 class="ej-table-head-title">📋 Provider Email Journey Status</h3>
                <span class="ej-badge ej-badge-blue">{{ $providers->total() }} providers</span>
            </div>

            <table class="ej-table">
                <thead>
                    <tr>
                        <th>Provider</th>
                        <th>Emails Sent</th>
                        <th>Last Email</th>
                        <th>Last Sent At</th>
                        <th>Next Scheduled</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($providers as $provider)
                        @php
                            $latestLog = $provider->providerEmailLogs->first();
                            $emailCount = $provider->provider_email_logs_count;
                        @endphp
                        <tr>
                            <td>
                                <div class="ej-provider-name">
                                    {{ $provider->company_name ?? $provider->user?->name ?? 'Unknown' }}
                                </div>
                                <div class="ej-provider-email">{{ $provider->user?->email }}</div>
                            </td>
                            <td>
                                <span class="ej-badge {{ $emailCount > 0 ? 'ej-badge-blue' : 'ej-badge-gray' }}">
                                    {{ $emailCount }} email{{ $emailCount !== 1 ? 's' : '' }}
                                </span>
                            </td>
                            <td>
                                @if($latestLog)
                                    <span class="ej-badge ej-badge-amber">
                                        {{ $emailTypeLabels[$latestLog->email_type] ?? $latestLog->email_type }}
                                    </span>
                                @else
                                    <span class="ej-badge ej-badge-gray">None yet</span>
                                @endif
                            </td>
                            <td>
                                @if($latestLog)
                                    <span title="{{ $latestLog->sent_at->format('Y-m-d H:i') }}">
                                        {{ $latestLog->sent_at->diffForHumans() }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($latestLog?->next_send_at)
                                    <span class="ej-badge {{ $latestLog->next_send_at->isPast() ? 'ej-badge-green' : 'ej-badge-gray' }}"
                                          title="{{ $latestLog->next_send_at->format('Y-m-d H:i') }}">
                                        {{ $latestLog->next_send_at->isPast() ? '🔔 Due Now' : $latestLog->next_send_at->diffForHumans() }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.email_journey.show', $provider) }}" class="ej-action-link">
                                    View Timeline →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding: 40px; color:#94A3B8;">
                                No providers found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            @if($providers->hasPages())
                <div style="padding: 20px 24px; border-top: 1px solid #E2E8F0;">
                    {{ $providers->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
