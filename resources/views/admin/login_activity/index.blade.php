@extends('layouts.app')

@section('content')
    <div class="admin-cms-page" dir="ltr">
        <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
            <x-admin.header
                eyebrow="Providers"
                title="Login Activity & Presence"
                subtitle="See who is online now, who has logged in, and who never signed in."
            />

            {{-- Stats --}}
            <section class="admin-stats-row">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-total"><i class="fas fa-users"></i></div>
                    <div class="admin-stat-content">
                        <span class="admin-stat-value">{{ $stats['total'] }}</span>
                        <span class="admin-stat-label">Total Providers</span>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-active"><i class="fas fa-circle"></i></div>
                    <div class="admin-stat-content">
                        <span class="admin-stat-value">{{ $stats['online'] }}</span>
                        <span class="admin-stat-label">Online Now</span>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-targeted"><i class="fas fa-right-to-bracket"></i></div>
                    <div class="admin-stat-content">
                        <span class="admin-stat-value">{{ $stats['today'] }}</span>
                        <span class="admin-stat-label">Logged In Today</span>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-expired"><i class="fas fa-user-slash"></i></div>
                    <div class="admin-stat-content">
                        <span class="admin-stat-value">{{ $stats['never'] }}</span>
                        <span class="admin-stat-label">Never Logged In</span>
                    </div>
                </div>
            </section>

            {{-- Filters --}}
            <section class="admin-filters-bar">
                <form method="GET" action="{{ route('admin.login_activity.index') }}" class="admin-filters-form">
                    <div class="admin-search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Search by name or email..." value="{{ request('search') }}">
                    </div>
                    <div class="admin-filter-select">
                        <select name="status">
                            <option value="">All Providers</option>
                            <option value="online" {{ request('status') === 'online' ? 'selected' : '' }}>Online Now</option>
                            <option value="never" {{ request('status') === 'never' ? 'selected' : '' }}>Never Logged In</option>
                        </select>
                    </div>
                    <x-ui.button type="submit" variant="secondary" icon="fas fa-filter" class="admin-btn admin-btn-secondary">
                        Filter
                    </x-ui.button>
                    @if(request()->hasAny(['search', 'status']))
                        <x-ui.button :href="route('admin.login_activity.index')" variant="ghost" icon="fas fa-times" class="admin-btn admin-btn-ghost">
                            Clear
                        </x-ui.button>
                    @endif
                </form>
            </section>

            <x-admin.table-card>
                <table class="admin-data-table">
                    <thead>
                        <tr>
                            <th>Provider</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Logins</th>
                            <th>Last IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($providers as $provider)
                            <tr>
                                <td>
                                    <div class="admin-table-title">
                                        {{ $provider->serviceProvider->company_name ?? $provider->name }}
                                    </div>
                                    <div class="admin-table-subtitle">{{ $provider->email }}</div>
                                </td>
                                <td>
                                    @if($provider->isOnline())
                                        <span class="presence-badge presence-online">
                                            <span class="presence-dot"></span> Online
                                        </span>
                                    @elseif($provider->last_seen_at)
                                        <span class="presence-badge presence-offline">
                                            <span class="presence-dot"></span> Seen {{ $provider->last_seen_at->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="presence-badge presence-never">
                                            <span class="presence-dot"></span> Never seen
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($provider->last_login_at)
                                        <span title="{{ $provider->last_login_at->format('M d, Y H:i') }}">
                                            {{ $provider->last_login_at->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $provider->login_count ?? 0 }}</td>
                                <td><span class="text-muted">{{ $provider->last_login_ip ?? '—' }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-ui.empty-state
                                        icon="fas fa-right-to-bracket"
                                        title="No providers found"
                                        description="No service providers match the current filters."
                                        class="admin-empty-state"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-admin.table-card>

            @if($providers->hasPages())
                <div class="admin-pagination-wrap">{{ $providers->links('components.global-pagination') }}</div>
            @endif
        </div>
    </div>

    <style>
    .admin-stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .admin-stat-card {
        display: flex; align-items: center; gap: 1rem; padding: 1.25rem;
        background: var(--sp-color-surface); border: 1px solid var(--sp-color-border-strong);
        border-radius: var(--sp-radius-xl);
    }
    .admin-stat-icon {
        display: flex; align-items: center; justify-content: center;
        width: 48px; height: 48px; border-radius: var(--sp-radius-lg); font-size: 1.25rem;
    }
    .admin-stat-icon-total { background: linear-gradient(135deg, rgba(99,102,241,.1), rgba(139,92,246,.15)); color: var(--sp-color-primary); }
    .admin-stat-icon-active { background: linear-gradient(135deg, rgba(16,185,129,.1), rgba(5,150,105,.15)); color: var(--sp-color-success); }
    .admin-stat-icon-expired { background: linear-gradient(135deg, rgba(245,158,11,.1), rgba(217,119,6,.15)); color: var(--sp-color-warning); }
    .admin-stat-icon-targeted { background: linear-gradient(135deg, rgba(14,165,233,.1), rgba(37,99,235,.15)); color: var(--sp-color-info); }
    .admin-stat-content { display: flex; flex-direction: column; }
    .admin-stat-value { font-size: 1.5rem; font-weight: 700; color: var(--sp-color-text); }
    .admin-stat-label { font-size: .875rem; color: var(--sp-color-text-subtle); }

    .admin-filters-bar { margin-bottom: 1.5rem; }
    .admin-filters-form { display: flex; gap: .75rem; flex-wrap: wrap; }
    .admin-search-box {
        display: flex; align-items: center; gap: .5rem; padding: .5rem 1rem;
        background: var(--sp-color-surface); border: 1px solid var(--sp-color-border-strong);
        border-radius: var(--sp-radius-lg); min-width: 280px;
    }
    .admin-search-box i { color: var(--sp-color-text-subtle); }
    .admin-search-box input { border: none; outline: none; flex: 1; font-size: .9375rem; color: var(--sp-color-text); background: transparent; }
    .admin-filter-select select {
        padding: .625rem 2rem .625rem 1rem; background: var(--sp-color-surface);
        border: 1px solid var(--sp-color-border-strong); border-radius: var(--sp-radius-lg);
        font-size: .9375rem; color: var(--sp-color-text); cursor: pointer;
    }

    .presence-badge {
        display: inline-flex; align-items: center; gap: .4rem;
        padding: .25rem .6rem; border-radius: var(--sp-radius-pill);
        font-size: .8125rem; font-weight: 600;
    }
    .presence-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
    .presence-online { background: rgba(16,185,129,.12); color: #059669; }
    .presence-online .presence-dot { background: #10B981; box-shadow: 0 0 0 3px rgba(16,185,129,.2); }
    .presence-offline { background: var(--sp-color-surface-muted); color: var(--sp-color-text-muted); }
    .presence-offline .presence-dot { background: #94A3B8; }
    .presence-never { background: rgba(148,163,184,.12); color: #64748B; }
    .presence-never .presence-dot { background: #CBD5E1; }

    @media (max-width: 768px) {
        .admin-stats-row { grid-template-columns: 1fr; }
        .admin-filters-form { flex-direction: column; }
        .admin-search-box { min-width: 100%; }
    }
    </style>
@endsection
