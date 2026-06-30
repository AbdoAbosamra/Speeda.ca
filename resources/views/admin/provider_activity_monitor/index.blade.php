@extends('layouts.app')

@push('styles')
<style>
/* ===== Provider Activity Monitor – Premium Design ===== */
:root {
    --pam-bg: #ffffff;
    --pam-border: #eef2f6;
    --pam-radius: 1.25rem;
    --pam-shadow: 0 1px 3px rgba(0,0,0,0.02);
    --pam-hover-shadow: 0 20px 40px -12px rgba(0,0,0,0.08);
    --pam-accent: #4f46e5;
    --pam-accent-soft: #eef2ff;
    --pam-text: #0f172a;
    --pam-text-secondary: #475569;
    --pam-text-muted: #94a3b8;
    --pam-success: #10b981;
    --pam-success-soft: #ecfdf5;
    --pam-warning: #f59e0b;
    --pam-warning-soft: #fefce8;
    --pam-danger: #ef4444;
    --pam-danger-soft: #fef2f2;
    --pam-info: #3b82f6;
    --pam-info-soft: #eff6ff;
}

/* ===== Header ===== */
.pam-header {
    padding: 1.5rem 2rem;
    background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(249,250,252,0.9));
    border-radius: var(--pam-radius);
    border: 1px solid var(--pam-border);
    margin-bottom: 1.5rem;
}
.pam-header-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--pam-text);
    letter-spacing: -0.02em;
    margin-bottom: 0.25rem;
}
.pam-header-sub {
    font-size: 0.95rem;
    color: var(--pam-text-secondary);
    margin-bottom: 0;
}

/* ===== Filter Bar ===== */
.pam-filters {
    background: white;
    border-radius: var(--pam-radius);
    border: 1px solid var(--pam-border);
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.75rem;
}
.pam-search-wrap {
    position: relative;
    flex: 1;
    min-width: 200px;
}
.pam-search-wrap i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--pam-text-muted);
    font-size: 0.9rem;
}
.pam-search-wrap input {
    width: 100%;
    padding: 0.6rem 1rem 0.6rem 2.5rem;
    border: 1px solid var(--pam-border);
    border-radius: 0.75rem;
    font-size: 0.9rem;
    background: #f8fafc;
    transition: all 0.2s;
}
.pam-search-wrap input:focus {
    outline: none;
    border-color: var(--pam-accent);
    background: white;
    box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
}
.pam-filter-select {
    padding: 0.6rem 2rem 0.6rem 1rem;
    border: 1px solid var(--pam-border);
    border-radius: 0.75rem;
    font-size: 0.85rem;
    background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23475569' d='M6 8L1 3h10z'/%3E%3C/svg%3E") no-repeat right 0.75rem center;
    appearance: none;
    cursor: pointer;
    transition: all 0.2s;
    min-width: 140px;
}
.pam-filter-select:focus {
    outline: none;
    border-color: var(--pam-accent);
    box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
}
.pam-clear-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.6rem 1.25rem;
    background: white;
    color: var(--pam-text-secondary);
    border: 1px solid var(--pam-border);
    border-radius: 0.75rem;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}
.pam-clear-btn:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: var(--pam-text);
}

/* ===== Provider Card ===== */
.pam-card {
    background: white;
    border-radius: var(--pam-radius);
    border: 1px solid var(--pam-border);
    box-shadow: var(--pam-shadow);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    margin-bottom: 1rem;
    position: relative;
}
.pam-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: var(--pam-accent);
    transform: scaleY(0);
    transition: transform 0.3s ease;
}
.pam-card:hover {
    box-shadow: var(--pam-hover-shadow);
    border-color: rgba(79,70,229,0.2);
}
.pam-card:hover::before {
    transform: scaleY(1);
}

/* Card Top – Provider Identity */
.pam-card-top {
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    border-bottom: 1px solid var(--pam-border);
}
.pam-avatar {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    font-weight: 700;
    color: white;
    flex-shrink: 0;
    background: linear-gradient(135deg, var(--pam-accent), #818cf8);
}
.pam-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 14px;
}
.pam-identity {
    flex: 1;
    min-width: 0;
}
.pam-name {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--pam-text);
    text-decoration: none;
    display: block;
    margin-bottom: 0.15rem;
}
.pam-name:hover {
    color: var(--pam-accent);
}
.pam-id {
    font-size: 0.8rem;
    color: var(--pam-text-muted);
}
.pam-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 1rem;
    border-radius: 2rem;
    font-size: 0.8rem;
    font-weight: 700;
    white-space: nowrap;
}
.pam-badge-success {
    background: var(--pam-success-soft);
    color: #065f46;
}
.pam-badge-warning {
    background: var(--pam-warning-soft);
    color: #92400e;
}
.pam-badge-danger {
    background: var(--pam-danger-soft);
    color: #991b1b;
}

/* Card Body */
.pam-card-body {
    padding: 1.25rem 1.5rem;
}

/* Metrics Row */
.pam-metrics {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 0.75rem;
    margin-bottom: 1.25rem;
}
.pam-metric {
    background: #f8fafc;
    border-radius: 0.85rem;
    padding: 0.85rem;
    text-align: center;
    transition: background 0.2s;
}
.pam-metric:hover {
    background: #f1f5f9;
}
.pam-metric-value {
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--pam-text);
    line-height: 1.2;
}
.pam-metric-value-sm {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--pam-text-secondary);
}
.pam-metric-label {
    font-size: 0.7rem;
    color: var(--pam-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.03em;
    margin-top: 0.2rem;
}

/* Progress Bar */
.pam-progress-wrap {
    margin-bottom: 1rem;
}
.pam-progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}
.pam-progress-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--pam-text-secondary);
}
.pam-progress-pct {
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--pam-text);
}
.pam-progress-track {
    height: 8px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
}
.pam-progress-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}
.pam-progress-fill-success { background: linear-gradient(90deg, #10b981, #34d399); }
.pam-progress-fill-warning { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.pam-progress-fill-danger  { background: linear-gradient(90deg, #ef4444, #f87171); }

/* Completion Breakdown */
.pam-breakdown {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
    margin-bottom: 1rem;
}
.pam-breakdown-item {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.5rem 0.75rem;
    border-radius: 0.65rem;
    font-size: 0.82rem;
    transition: background 0.2s;
}
.pam-breakdown-item:hover {
    background: #f8fafc;
}
.pam-breakdown-icon {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 0.7rem;
    flex-shrink: 0;
}
.pam-breakdown-icon-success { background: var(--pam-success-soft); color: #065f46; }
.pam-breakdown-icon-danger  { background: var(--pam-danger-soft); color: #991b1b; }
.pam-breakdown-icon-warning { background: var(--pam-warning-soft); color: #92400e; }
.pam-breakdown-text {
    flex: 1;
    color: var(--pam-text-secondary);
}
.pam-breakdown-cta {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--pam-accent);
    text-decoration: none;
    padding: 0.2rem 0.6rem;
    border-radius: 0.5rem;
    transition: all 0.2s;
}
.pam-breakdown-cta:hover {
    background: var(--pam-accent-soft);
    color: var(--pam-accent);
}

/* Card Footer */
.pam-card-footer {
    padding: 0.85rem 1.5rem;
    border-top: 1px solid var(--pam-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem;
}
.pam-last-activity {
    font-size: 0.82rem;
    color: var(--pam-text-secondary);
    display: flex;
    align-items: center;
    gap: 0.35rem;
}
.pam-last-activity strong {
    color: var(--pam-text);
    font-weight: 600;
}
.pam-actions {
    display: flex;
    gap: 0.5rem;
}
.pam-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 0.9rem;
    border-radius: 0.65rem;
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}
.pam-btn:hover {
    transform: translateY(-1px);
}
.pam-btn-ghost {
    background: #f8fafc;
    color: var(--pam-text-secondary);
    border: 1px solid var(--pam-border);
}
.pam-btn-ghost:hover {
    background: var(--pam-accent-soft);
    color: var(--pam-accent);
    border-color: rgba(79,70,229,0.2);
}
.pam-btn-primary {
    background: linear-gradient(135deg, var(--pam-accent), #6366f1);
    color: white;
    box-shadow: 0 4px 12px rgba(79,70,229,0.2);
}
.pam-btn-primary:hover {
    box-shadow: 0 6px 16px rgba(79,70,229,0.3);
}

/* Empty State */
.pam-empty {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: var(--pam-radius);
    border: 1px solid var(--pam-border);
}
.pam-empty-icon {
    font-size: 3.5rem;
    color: var(--pam-text-muted);
    margin-bottom: 1rem;
    opacity: 0.4;
}
.pam-empty h4 {
    font-weight: 700;
    color: var(--pam-text);
    margin-bottom: 0.5rem;
}
.pam-empty p {
    color: var(--pam-text-secondary);
    margin-bottom: 0;
}

/* ===== Responsive ===== */
@media (max-width: 768px) {
    .pam-header { padding: 1.25rem; }
    .pam-header-title { font-size: 1.35rem; }
    .pam-filters { padding: 0.85rem 1rem; }
    .pam-metrics { grid-template-columns: repeat(2, 1fr); }
    .pam-breakdown { grid-template-columns: 1fr; }
    .pam-card-footer { flex-direction: column; align-items: flex-start; }
    .pam-card-top { flex-wrap: wrap; }
    .pam-badge { font-size: 0.75rem; padding: 0.3rem 0.75rem; }
}

/* Admin content wrapper */
.admin-content-wrapper { padding-top: 20px; }

/* Chevron on hover fix for action cards */
[dir="rtl"] .pam-card::before { left: auto; right: 0; }
</style>
@endpush

@section('content')
<div class="admin-content-wrapper">
    <div class="container py-4">

        {{-- Header --}}
        <div class="pam-header">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <h1 class="pam-header-title">Provider Activity Monitor</h1>
                    <p class="pam-header-sub">Aggregated provider analytics, gallery status, and profile completion</p>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="pam-btn pam-btn-ghost">
                    <i class="fas fa-arrow-left"></i> Dashboard
                </a>
            </div>
        </div>

        {{-- Filter Bar --}}
        <form method="GET" class="pam-filters">
            <div class="pam-search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Search by name or ID..." value="{{ request('search') }}">
            </div>
            <select name="completion_status" class="pam-filter-select">
                <option value="">All Status</option>
                <option value="complete" {{ request('completion_status') === 'complete' ? 'selected' : '' }}>Complete (100%)</option>
                <option value="partial" {{ request('completion_status') === 'partial' ? 'selected' : '' }}>Partial (1-99%)</option>
                <option value="incomplete" {{ request('completion_status') === 'incomplete' ? 'selected' : '' }}>Incomplete (0%)</option>
            </select>
            <select name="activity" class="pam-filter-select">
                <option value="">All Activity</option>
                <option value="today" {{ request('activity') === 'today' ? 'selected' : '' }}>Active Today</option>
                <option value="week" {{ request('activity') === 'week' ? 'selected' : '' }}>Active This Week</option>
                <option value="month" {{ request('activity') === 'month' ? 'selected' : '' }}>Active This Month</option>
                <option value="never" {{ request('activity') === 'never' ? 'selected' : '' }}>No Activity</option>
            </select>
            <button type="submit" class="pam-btn pam-btn-primary">
                <i class="fas fa-filter"></i> Filter
            </button>
            @if(request('search') || request('completion_status') || request('activity'))
                <a href="{{ route('admin.provider_activity_monitor.index') }}" class="pam-clear-btn">
                    <i class="fas fa-times"></i> Clear
                </a>
            @endif
        </form>

        {{-- Provider Cards --}}
        @forelse($providers as $p)
            @php
                $completionPercent = (int) $p->profile_completion_percent;
                $completionClass = $completionPercent >= 100 ? 'success' : ($completionPercent >= 50 ? 'warning' : 'danger');
                $missingPhoto = !$p->has_profile_photo;
                $missingGallery = (int) $p->gallery_count < 4;
                $initial = strtoupper(substr($p->company_name ?: ('Provider #' . $p->id), 0, 1));
                $lastActivityType = match($p->last_action_type ?? null) {
                    'view' => 'Profile View',
                    'click_whatsapp' => 'WhatsApp Click',
                    null => '-',
                    default => \Illuminate\Support\Str::headline($p->last_action_type),
                };
            @endphp

            <div class="pam-card">
                <div class="pam-card-top">
                    <div class="pam-avatar">
                        @if($p->profile_image)
                            <img src="{{ asset('storage/' . $p->profile_image) }}" alt="{{ $p->company_name }}">
                        @else
                            {{ $initial }}
                        @endif
                    </div>
                    <div class="pam-identity">
                        <a href="{{ route('service-providers.show', $p->id) }}" class="pam-name">
                            {{ $p->company_name ?: ('Provider #' . $p->id) }}
                        </a>
                        <div class="pam-id">ID: {{ $p->id }}</div>
                    </div>
                    <span class="pam-badge pam-badge-{{ $completionClass }}">
                        <i class="fas fa-chart-pie"></i>
                        {{ $completionPercent }}% Complete
                    </span>
                </div>

                <div class="pam-card-body">
                    {{-- Metrics --}}
                    <div class="pam-metrics">
                        <div class="pam-metric">
                            <div class="pam-metric-value">{{ (int) $p->profile_views }}</div>
                            <div class="pam-metric-label">Profile Views</div>
                        </div>
                        <div class="pam-metric">
                            <div class="pam-metric-value">{{ (int) $p->whatsapp_clicks }}</div>
                            <div class="pam-metric-label">WhatsApp Clicks</div>
                        </div>
                        <div class="pam-metric">
                            <div class="pam-metric-value" style="font-size:0.95rem;">{{ $lastActivityType }}</div>
                            <div class="pam-metric-label">Last Activity Type</div>
                        </div>
                        <div class="pam-metric">
                            <div class="pam-metric-value">{{ (int) $p->gallery_count }}<span class="pam-metric-value-sm">/4</span></div>
                            <div class="pam-metric-label">Gallery Images</div>
                        </div>
                        <div class="pam-metric">
                            <div class="pam-metric-value" style="font-size:0.95rem;">
                                {{ $p->created_at ? \Carbon\Carbon::parse($p->created_at)->format('M d, Y') : '-' }}
                            </div>
                            <div class="pam-metric-label">Created</div>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="pam-progress-wrap">
                        <div class="pam-progress-header">
                            <span class="pam-progress-label">Profile Completion</span>
                            <span class="pam-progress-pct">{{ $completionPercent }}%</span>
                        </div>
                        <div class="pam-progress-track">
                            <div class="pam-progress-fill pam-progress-fill-{{ $completionClass }}" style="width: {{ $completionPercent }}%;"></div>
                        </div>
                    </div>

                    {{-- Completion Breakdown --}}
                    <div class="pam-breakdown">
                        <div class="pam-breakdown-item">
                            <div class="pam-breakdown-icon pam-breakdown-icon-{{ $p->has_profile_photo ? 'success' : 'danger' }}">
                                <i class="fas fa-{{ $p->has_profile_photo ? 'check' : 'times' }}"></i>
                            </div>
                            <span class="pam-breakdown-text">
                                <strong>Profile Photo</strong> &middot; {{ $p->has_profile_photo ? 'Uploaded' : 'Missing' }}
                            </span>

                        </div>
                        <div class="pam-breakdown-item">
                            <div class="pam-breakdown-icon pam-breakdown-icon-{{ $p->gallery_count >= 4 ? 'success' : 'warning' }}">
                                <i class="fas fa-{{ $p->gallery_count >= 4 ? 'check' : 'exclamation' }}"></i>
                            </div>
                            <span class="pam-breakdown-text">
                                <strong>Gallery</strong> &middot; {{ (int) $p->gallery_count }}/4 images
                            </span>

                        </div>
                        <div class="pam-breakdown-item">
                            <div class="pam-breakdown-icon pam-breakdown-icon-success">
                                <i class="fas fa-info"></i>
                            </div>
                            <span class="pam-breakdown-text">
                                <strong>Services</strong> &middot; Listed
                            </span>
                            </a>
                        </div>
                        <div class="pam-breakdown-item">
                            <div class="pam-breakdown-icon pam-breakdown-icon-success">
                                <i class="fas fa-info"></i>
                            </div>
                            <span class="pam-breakdown-text">
                                <strong>Description</strong> &middot; Set
                            </span>
                        </div>
                    </div>
                </div>

                <div class="pam-card-footer">
                    <div class="pam-last-activity">
                        <i class="fas fa-clock"></i>
                        Last activity:
                        <strong>{{ $p->last_activity_at ? \Carbon\Carbon::parse($p->last_activity_at)->diffForHumans() : 'Never' }}</strong>
                    </div>
                    <div class="pam-actions">
                        <a href="{{ route('service-providers.show', $p->id) }}" class="pam-btn pam-btn-ghost">
                            <i class="fas fa-user"></i> Profile
                        </a>
                        <a href="{{ route('admin.provider_activity_monitor.show', $p->id) }}" class="pam-btn pam-btn-primary">
                            <i class="fas fa-chart-line"></i> Analytics
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="pam-empty">
                <div class="pam-empty-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h4>No Provider Activity Yet</h4>
                <p>There are no provider activity records matching your criteria.</p>
            </div>
        @endforelse

        {{-- Pagination --}}
        @if($providers->hasPages())
            <div class="mt-4">
                {!! $providers->links('components.global-pagination') !!}
            </div>
        @endif
        </div>
        </div>
        </div>
        </div>
        </div>
        </div>
        </div>
        </div>
        </div>
        </div>
        </div>
        </div>
        </div>
        </div>
        </div>
        </div>
        </div>
        </div>
        </div>
        </div>
        </div>
        </div>
        </div>
        </div>
        </div>
    </div>
</div>
@endsection
