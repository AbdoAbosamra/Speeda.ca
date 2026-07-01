@extends('layouts.app')

@section('content')
    <div class="admin-cms-page" dir="ltr">
        <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
            <x-admin.header
                eyebrow="Notifications"
                title="Manage Notifications"
                subtitle="Broadcast multilingual messages or target selected service providers."
            >
                <x-slot:actions>
                    <x-ui.button
                        :href="route('admin.notifications.create')"
                        icon="fas fa-plus"
                        class="admin-btn admin-btn-primary text-white"
                    >
                        Create Notification
                    </x-ui.button>
                </x-slot:actions>
            </x-admin.header>

            {{-- Stats Cards --}}
            <section class="admin-stats-row">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-total">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="admin-stat-content">
                        <span class="admin-stat-value">{{ $stats['total'] }}</span>
                        <span class="admin-stat-label">Total Notifications</span>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-active">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="admin-stat-content">
                        <span class="admin-stat-value">{{ $stats['active'] }}</span>
                        <span class="admin-stat-label">Active</span>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-expired">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="admin-stat-content">
                        <span class="admin-stat-value">{{ $stats['expired'] }}</span>
                        <span class="admin-stat-label">Expired</span>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-targeted">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="admin-stat-content">
                        <span class="admin-stat-value">{{ $stats['targeted'] }}</span>
                        <span class="admin-stat-label">Targeted</span>
                    </div>
                </div>
            </section>

            {{-- Search & Filters --}}
            <section class="admin-filters-bar">
                <form method="GET" action="{{ route('admin.notifications.index') }}" class="admin-filters-form">
                    <div class="admin-search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Search notifications..." value="{{ request('search') }}">
                    </div>
                    <div class="admin-filter-select">
                        <select name="status">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                    <x-ui.button type="submit" variant="secondary" icon="fas fa-filter" class="admin-btn admin-btn-secondary">
                        Filter
                    </x-ui.button>
                    @if(request()->hasAny(['search', 'status']))
                        <x-ui.button
                            :href="route('admin.notifications.index')"
                            variant="ghost"
                            icon="fas fa-times"
                            class="admin-btn admin-btn-ghost"
                        >
                            Clear
                        </x-ui.button>
                    @endif
                </form>
            </section>

            <x-admin.table-card>
                    <table class="admin-data-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Target</th>
                                <th>Created By</th>
                                <th>Created</th>
                                <th>Expires</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($notifications as $notification)
                                @php($isActive = $notification->expires_at && $notification->expires_at->isFuture())
                                <tr>
                                    <td>
                                        <div class="admin-table-title">{{ $notification->title_en }}</div>
                                        <div class="admin-table-subtitle">{{ Str::limit($notification->message_en, 110) }}</div>
                                    </td>
                                    <td>
                                        <x-ui.badge
                                            :variant="$isActive ? 'success' : 'warning'"
                                            class="admin-badge {{ $isActive ? 'admin-badge-published' : 'admin-badge-draft' }}"
                                        >
                                            {{ $isActive ? 'Active' : 'Expired' }}
                                        </x-ui.badge>
                                    </td>
                                    <td>
                                        @if($notification->target_service_providers_count > 0)
                                            <x-ui.badge variant="primary" icon="fas fa-user-check">
                                                {{ $notification->target_service_providers_count }} selected
                                            </x-ui.badge>
                                        @else
                                            <x-ui.badge variant="neutral" icon="fas fa-bullhorn">
                                                All providers
                                            </x-ui.badge>
                                        @endif
                                    </td>
                                    <td>{{ $notification->admin->name ?? 'System' }}</td>
                                    <td>{{ optional($notification->created_at)->format('M d, Y H:i') }}</td>
                                    <td>
                                        <span class="{{ $isActive ? '' : 'text-muted' }}">
                                            {{ optional($notification->expires_at)->format('M d, Y H:i') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="admin-row-actions">
                                            <button type="button" class="admin-icon-action" data-bs-toggle="modal" data-bs-target="#viewNotification{{ $notification->id }}">
                                                <i class="fas fa-eye"></i>
                                                <span>View</span>
                                            </button>
                                            <form action="{{ route('admin.notifications.destroy', $notification) }}" method="POST" onsubmit="return confirm('Delete this notification?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="admin-icon-action admin-icon-danger">
                                                    <i class="fas fa-trash"></i>
                                                    <span>Delete</span>
                                                </button>
                                            </form>
                                        </div>

                                        <div class="modal fade" id="viewNotification{{ $notification->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content admin-modal">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Notification Details</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="admin-notification-meta mb-4">
                                                            <div class="meta-item">
                                                                <i class="fas fa-calendar"></i>
                                                                <span>Created: {{ $notification->created_at->format('M d, Y H:i') }}</span>
                                                            </div>
                                                            <div class="meta-item">
                                                                <i class="fas fa-hourglass-half"></i>
                                                                <span>Expires: {{ $notification->expires_at->format('M d, Y H:i') }}</span>
                                                            </div>
                                                            <div class="meta-item">
                                                                <i class="fas fa-user"></i>
                                                                <span>By: {{ $notification->admin->name ?? 'System' }}</span>
                                                            </div>
                                                            <div class="meta-item">
                                                                <i class="fas fa-bullseye"></i>
                                                                <span>
                                                                    Target:
                                                                    @if($notification->target_service_providers_count > 0)
                                                                        {{ $notification->target_service_providers_count }} selected provider(s)
                                                                    @else
                                                                        All service providers
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        @if($notification->target_service_providers_count > 0)
                                                            <div class="admin-target-preview mb-4">
                                                                <strong>Selected Providers</strong>
                                                                <div class="admin-target-list">
                                                                    @foreach($notification->targetServiceProviders as $provider)
                                                                        <span class="admin-target-pill">
                                                                            {{ $provider->company_name ?? $provider->user?->name ?? 'Provider #' . $provider->id }}
                                                                        </span>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                        <div class="admin-language-preview" dir="rtl">
                                                            <div class="language-label">
                                                                <span class="flag-icon">🇸🇦</span>
                                                                <strong>Arabic</strong>
                                                            </div>
                                                            <h3>{{ $notification->title_ar }}</h3>
                                                            <p>{{ $notification->message_ar }}</p>
                                                        </div>
                                                        <div class="admin-language-preview">
                                                            <div class="language-label">
                                                                <span class="flag-icon">🇺🇸</span>
                                                                <strong>English</strong>
                                                            </div>
                                                            <h3>{{ $notification->title_en }}</h3>
                                                            <p>{{ $notification->message_en }}</p>
                                                        </div>
                                                        <div class="admin-language-preview">
                                                            <div class="language-label">
                                                                <span class="flag-icon">🇫🇷</span>
                                                                <strong>French</strong>
                                                            </div>
                                                            <h3>{{ $notification->title_fr }}</h3>
                                                            <p>{{ $notification->message_fr }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <x-ui.empty-state
                                            icon="fas fa-bell-slash"
                                            title="No notifications found"
                                            description="Create a notification to reach active service providers."
                                            class="admin-empty-state"
                                        />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
            </x-admin.table-card>

            @if($notifications->hasPages())
                <div class="admin-pagination-wrap">{{ $notifications->links('components.global-pagination') }}</div>
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
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem;
        background: var(--sp-color-surface);
        border: 1px solid var(--sp-color-border-strong);
        border-radius: var(--sp-radius-xl);
        transition: all var(--sp-duration-base) var(--sp-ease-standard);
    }

    .admin-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--sp-shadow-sm);
    }

    .admin-stat-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        border-radius: var(--sp-radius-lg);
        font-size: 1.25rem;
    }

    .admin-stat-icon-total {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.15));
        color: var(--sp-color-primary);
    }

    .admin-stat-icon-active {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.15));
        color: var(--sp-color-success);
    }

    .admin-stat-icon-expired {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(217, 119, 6, 0.15));
        color: var(--sp-color-warning);
    }

    .admin-stat-icon-targeted {
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.1), rgba(37, 99, 235, 0.15));
        color: var(--sp-color-info);
    }

    .admin-stat-content {
        display: flex;
        flex-direction: column;
    }

    .admin-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--sp-color-text);
    }

    .admin-stat-label {
        font-size: 0.875rem;
        color: var(--sp-color-text-subtle);
    }

    .admin-filters-bar {
        margin-bottom: 1.5rem;
    }

    .admin-filters-form {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .admin-search-box {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: var(--sp-color-surface);
        border: 1px solid var(--sp-color-border-strong);
        border-radius: var(--sp-radius-lg);
        min-width: 280px;
    }

    .admin-search-box i {
        color: var(--sp-color-text-subtle);
    }

    .admin-search-box input {
        border: none;
        outline: none;
        flex: 1;
        font-size: 0.9375rem;
        color: var(--sp-color-text);
    }

    .admin-search-box input::placeholder {
        color: var(--sp-color-text-subtle);
    }

    .admin-filter-select select {
        padding: 0.625rem 2rem 0.625rem 1rem;
        background: var(--sp-color-surface);
        border: 1px solid var(--sp-color-border-strong);
        border-radius: var(--sp-radius-lg);
        font-size: 0.9375rem;
        color: var(--sp-color-text);
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
    }

    .admin-btn-ghost {
        background: transparent;
        border: 1px solid var(--sp-color-border-strong);
        color: var(--sp-color-text-muted);
    }

    .admin-btn-ghost:hover {
        background: var(--sp-color-surface-muted);
    }

    .admin-notification-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        padding: 1rem;
        background: var(--sp-color-surface-muted);
        border-radius: var(--sp-radius-lg);
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: var(--sp-color-text-muted);
    }

    .meta-item i {
        color: var(--sp-color-primary);
    }

    .language-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .flag-icon {
        font-size: 1.25rem;
    }

    .admin-language-preview {
        padding: 1.25rem;
        margin-bottom: 1rem;
        background: var(--sp-color-surface);
        border: 1px solid var(--sp-color-border-strong);
        border-radius: var(--sp-radius-lg);
    }

    .admin-language-preview h3 {
        font-size: 1.125rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--sp-color-text);
    }

    .admin-language-preview p {
        color: var(--sp-color-text-muted);
        line-height: 1.6;
        margin: 0;
    }

    .admin-target-preview {
        padding: 1rem;
        background: var(--sp-color-surface-muted);
        border: 1px solid var(--sp-color-border);
        border-radius: var(--sp-radius-lg);
    }

    .admin-target-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.75rem;
    }

    .admin-target-pill {
        display: inline-flex;
        align-items: center;
        max-width: 100%;
        padding: 0.35rem 0.65rem;
        border: 1px solid var(--sp-color-primary-border);
        border-radius: var(--sp-radius-pill);
        background: var(--sp-color-surface);
        color: var(--sp-color-text-body);
        font-size: 0.8125rem;
        font-weight: 700;
    }

    @media (max-width: 768px) {
        .admin-stats-row {
            grid-template-columns: 1fr;
        }

        .admin-filters-form {
            flex-direction: column;
        }

        .admin-search-box {
            min-width: 100%;
        }
    }
    </style>
@endsection
