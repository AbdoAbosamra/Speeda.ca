@extends('layouts.app')

@push('styles')
<style>
/* ===== Provider Analytics Details – Premium Design ===== */
:root {
    --pad-accent: #4f46e5;
    --pad-accent-soft: #eef2ff;
    --pad-border: #eef2f6;
    --pad-text: #0f172a;
    --pad-text-secondary: #475569;
    --pad-text-muted: #94a3b8;
    --pad-success: #10b981;
    --pad-success-soft: #ecfdf5;
    --pad-warning: #f59e0b;
    --pad-warning-soft: #fefce8;
    --pad-danger: #ef4444;
    --pad-danger-soft: #fef2f2;
    --pad-info: #3b82f6;
    --pad-info-soft: #eff6ff;
}

/* ===== Header ===== */
.pad-header {
    padding: 1.5rem 2rem;
    background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(249,250,252,0.9));
    border-radius: 1.25rem;
    border: 1px solid var(--pad-border);
    margin-bottom: 1.5rem;
}
.pad-header-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--pad-text);
    letter-spacing: -0.02em;
    margin-bottom: 0.25rem;
}
.pad-header-sub {
    font-size: 0.9rem;
    color: var(--pad-text-secondary);
}
.pad-header-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.pad-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 1rem;
    border-radius: 0.65rem;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}
.pad-btn:hover { transform: translateY(-1px); }
.pad-btn-ghost {
    background: #f8fafc;
    color: var(--pad-text-secondary);
    border: 1px solid var(--pad-border);
}
.pad-btn-ghost:hover {
    background: var(--pad-accent-soft);
    color: var(--pad-accent);
    border-color: rgba(79,70,229,0.2);
}
.pad-btn-primary {
    background: var(--pad-accent);
    color: #fff;
    border: 1px solid var(--pad-accent);
}
.pad-btn-primary:hover {
    background: #4338ca;
    color: #fff;
}
.pad-header-actions form { display: inline; }
.pad-header-actions { display: flex; flex-wrap: wrap; gap: 0.5rem; }

/* ===== Summary Cards ===== */
.pad-summary {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.pad-summary-card {
    background: white;
    border-radius: 1.25rem;
    border: 1px solid var(--pad-border);
    padding: 1.25rem 1.5rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.pad-summary-card:hover {
    box-shadow: 0 12px 24px -8px rgba(0,0,0,0.06);
    transform: translateY(-2px);
}
.pad-summary-icon {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 1.1rem;
    margin-bottom: 0.75rem;
}
.pad-summary-icon-views   { background: #eef2ff; color: #4f46e5; }
.pad-summary-icon-whatsapp { background: #ecfdf5; color: #059669; }
.pad-summary-icon-clock   { background: #fefce8; color: #b45309; }
.pad-summary-label {
    font-size: 0.8rem;
    color: var(--pad-text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.03em;
    font-weight: 600;
    margin-bottom: 0.25rem;
}
.pad-summary-value {
    font-size: 2rem;
    font-weight: 800;
    color: var(--pad-text);
    line-height: 1.1;
    letter-spacing: -0.02em;
}

/* ===== Timeline Card ===== */
.pad-timeline {
    background: white;
    border-radius: 1.25rem;
    border: 1px solid var(--pad-border);
    overflow: hidden;
}
.pad-timeline-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--pad-border);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.pad-timeline-header h5 {
    font-size: 1rem;
    font-weight: 700;
    color: var(--pad-text);
    margin: 0;
}
.pad-timeline-header i {
    color: var(--pad-accent);
}

/* ===== Timeline Rows ===== */
.pad-timeline-body {
    padding: 0.5rem 0;
}
.pad-event {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.85rem 1.5rem;
    transition: background 0.15s;
    border-bottom: 1px solid #f8fafc;
}
.pad-event:last-child {
    border-bottom: none;
}
.pad-event:hover {
    background: #fafbfc;
}
.pad-event-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}
.pad-event-dot-view      { background: var(--pad-info); }
.pad-event-dot-whatsapp  { background: var(--pad-success); }
.pad-event-dot-default   { background: var(--pad-text-muted); }
.pad-event-type {
    min-width: 120px;
}
.pad-event-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3rem 0.75rem;
    border-radius: 2rem;
    font-size: 0.78rem;
    font-weight: 600;
}
.pad-event-badge-view     { background: var(--pad-info-soft); color: #1e40af; }
.pad-event-badge-whatsapp { background: var(--pad-success-soft); color: #065f46; }
.pad-event-badge-default  { background: #f1f5f9; color: var(--pad-text-secondary); }
.pad-event-info {
    flex: 1;
    font-size: 0.85rem;
    color: var(--pad-text-secondary);
}
.pad-event-time {
    text-align: right;
    flex-shrink: 0;
}
.pad-event-date {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--pad-text);
    display: block;
}
.pad-event-timeonly {
    font-size: 0.75rem;
    color: var(--pad-text-muted);
    display: block;
}

/* Empty State */
.pad-empty {
    text-align: center;
    padding: 3rem 1.5rem;
    color: var(--pad-text-secondary);
}
.pad-empty i {
    font-size: 2.5rem;
    color: var(--pad-text-muted);
    margin-bottom: 0.75rem;
    opacity: 0.4;
}

/* ===== Responsive ===== */
@media (max-width: 768px) {
    .pad-header { padding: 1.25rem; }
    .pad-header-title { font-size: 1.2rem; }
    .pad-summary { grid-template-columns: 1fr; }
    .pad-event { flex-wrap: wrap; gap: 0.5rem; }
    .pad-event-type { min-width: auto; }
    .pad-event-time { text-align: left; }
    .pad-summary-value { font-size: 1.5rem; }
}

.admin-content-wrapper { padding-top: 20px; }
</style>
@endpush

@section('content')
<div class="admin-content-wrapper">
    <div class="container py-4">

        {{-- Header --}}
        <div class="pad-header">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <h1 class="pad-header-title">Provider Analytics Details</h1>
                    <p class="pad-header-sub">
                        Provider: <strong>{{ $provider->company_name ?: ('Provider #' . $provider->id) }}</strong>
                        &middot; ID: {{ $provider->id }}
                    </p>
                </div>
                <div class="pad-header-actions">
                    {{-- Management actions (this page used to be read-only). --}}
                    <a href="{{ route('admin.providers.edit', $provider->id) }}" class="pad-btn pad-btn-primary">
                        <i class="fas fa-pen"></i> Edit Provider
                    </a>

                    <form action="{{ route('admin.providers.toggle_active', $provider->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="pad-btn pad-btn-ghost">
                            <i class="fas {{ $provider->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                            {{ $provider->is_active ? 'Hide Listing' : 'Show Listing' }}
                        </button>
                    </form>

                    <form action="{{ route('admin.providers.toggle_verified', $provider->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="pad-btn pad-btn-ghost">
                            <i class="fas {{ $provider->is_verified ? 'fa-circle-xmark' : 'fa-circle-check' }}"></i>
                            {{ $provider->is_verified ? 'Unverify' : 'Verify' }}
                        </button>
                    </form>

                    <form action="{{ route('admin.providers.toggle_featured', $provider->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="pad-btn pad-btn-ghost">
                            <i class="{{ $provider->is_featured ? 'far' : 'fas' }} fa-star"></i>
                            {{ $provider->is_featured ? 'Unfeature' : 'Feature' }}
                        </button>
                    </form>

                    <a href="{{ route('service-providers.show', $provider->id) }}" class="pad-btn pad-btn-ghost" target="_blank" rel="noopener">
                        <i class="fas fa-arrow-up-right-from-square"></i> Public Profile
                    </a>
                    <a href="{{ route('admin.provider_activity_monitor.index') }}" class="pad-btn pad-btn-ghost">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="pad-summary">
            <div class="pad-summary-card">
                <div class="pad-summary-icon pad-summary-icon-views">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="pad-summary-label">Profile Views</div>
                <div class="pad-summary-value">{{ (int) ($summary->profile_views ?? 0) }}</div>
            </div>
            <div class="pad-summary-card">
                <div class="pad-summary-icon pad-summary-icon-whatsapp">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <div class="pad-summary-label">WhatsApp Clicks</div>
                <div class="pad-summary-value">{{ (int) ($summary->whatsapp_clicks ?? 0) }}</div>
            </div>
            <div class="pad-summary-card">
                <div class="pad-summary-icon pad-summary-icon-clock">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="pad-summary-label">Last Activity</div>
                <div class="pad-summary-value" style="font-size:1.25rem;">
                    @if(!empty($summary->last_activity_at))
                        {{ \Carbon\Carbon::parse($summary->last_activity_at)->diffForHumans() }}
                    @else
                        N/A
                    @endif
                </div>
            </div>
        </div>

        {{-- Timeline --}}
        <div class="pad-timeline">
            <div class="pad-timeline-header">
                <i class="fas fa-clock-rotate-left"></i>
                <h5>Event Timeline</h5>
                <span class="badge bg-soft-indigo text-indigo px-3 py-1 rounded-pill ms-auto fw-semibold">
                    {{ $events->total() }} events
                </span>
            </div>
            <div class="pad-timeline-body">
                @forelse($events as $e)
                    @php
                        $dotClass = match($e->action_type) {
                            'view' => 'pad-event-dot-view',
                            'click_whatsapp' => 'pad-event-dot-whatsapp',
                            default => 'pad-event-dot-default'
                        };
                        $badgeClass = match($e->action_type) {
                            'view' => 'pad-event-badge-view',
                            'click_whatsapp' => 'pad-event-badge-whatsapp',
                            default => 'pad-event-badge-default'
                        };
                        $badgeIcon = match($e->action_type) {
                            'view' => 'fa-eye',
                            'click_whatsapp' => 'fa-whatsapp',
                            default => 'fa-circle'
                        };
                    @endphp
                    <div class="pad-event">
                        <span class="pad-event-dot {{ $dotClass }}"></span>
                        <div class="pad-event-type">
                            <span class="pad-event-badge {{ $badgeClass }}">
                                <i class="fab {{ $badgeIcon }}"></i>
                                {{ $e->action_type === 'click_whatsapp' ? 'WhatsApp' : ucfirst($e->action_type) }}
                            </span>
                        </div>
                        <div class="pad-event-info">
                            <span class="text-muted">Privacy-safe event metadata</span>
                        </div>
                        <div class="pad-event-time">
                            <span class="pad-event-date">{{ \Carbon\Carbon::parse($e->created_at)->format('M d, Y') }}</span>
                            <span class="pad-event-timeonly">{{ \Carbon\Carbon::parse($e->created_at)->format('H:i:s') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="pad-empty">
                        <i class="fas fa-inbox"></i>
                        <p class="mb-0">No analytics events found for this provider.</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($events->hasPages())
                <div class="p-3 border-top" style="border-color: var(--pad-border) !important;">
                    {{ $events->links('components.global-pagination') }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.bg-soft-indigo { background: #eef2ff; }
.text-indigo { color: #4f46e5; }
</style>
@endsection
