@extends('layouts.app')

@section('content')
    <div class="admin-cms-page" dir="ltr">
        <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
            @php
                $totalRecipients = $readRecipients->count() + $unreadRecipients->count();
                $readCount = $readRecipients->count();
                $unreadCount = $unreadRecipients->count();
                $readPct = $totalRecipients > 0 ? round(($readCount / $totalRecipients) * 100) : 0;
            @endphp

            <x-admin.header
                eyebrow="Notifications"
                :title="'Read Receipts'"
                :subtitle="$notification->title_en"
            >
                <x-slot:actions>
                    <x-ui.button
                        :href="route('admin.notifications.index')"
                        icon="fas fa-arrow-left"
                        variant="secondary"
                        class="admin-btn admin-btn-secondary"
                    >
                        Back to Notifications
                    </x-ui.button>
                </x-slot:actions>
            </x-admin.header>

            {{-- Summary --}}
            <section class="admin-stats-row">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-total">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="admin-stat-content">
                        <span class="admin-stat-value">{{ $totalRecipients }}</span>
                        <span class="admin-stat-label">
                            Recipients ({{ $isBroadcast ? 'All active providers' : 'Targeted' }})
                        </span>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-active">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div class="admin-stat-content">
                        <span class="admin-stat-value">{{ $readCount }}</span>
                        <span class="admin-stat-label">Read ({{ $readPct }}%)</span>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon admin-stat-icon-expired">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="admin-stat-content">
                        <span class="admin-stat-value">{{ $unreadCount }}</span>
                        <span class="admin-stat-label">Not read</span>
                    </div>
                </div>
            </section>

            @if($isBroadcast)
                <div class="admin-receipt-note">
                    <i class="fas fa-bullhorn"></i>
                    This is a broadcast notification — recipients are all currently active service providers.
                </div>
            @endif

            <div class="admin-receipt-grid">
                {{-- Read --}}
                <x-admin.table-card>
                    <div class="admin-receipt-list-head admin-receipt-list-head-read">
                        <i class="fas fa-check-double"></i>
                        <span>Read — {{ $readCount }}</span>
                    </div>
                    <table class="admin-data-table">
                        <thead>
                            <tr>
                                <th>Provider</th>
                                <th class="text-end">Read at</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($readRecipients as $user)
                                <tr>
                                    <td>
                                        <div class="admin-table-title">
                                            {{ $user->serviceProvider->company_name ?? $user->name }}
                                        </div>
                                        <div class="admin-table-subtitle">{{ $user->email }}</div>
                                    </td>
                                    <td class="text-end">
                                        <span class="admin-receipt-time">
                                            {{ optional($readMap[$user->id])->format('M d, Y H:i') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2">
                                        <x-ui.empty-state
                                            icon="fas fa-check-double"
                                            title="No one has read this yet"
                                            description="Read receipts will appear here as recipients open the notification."
                                            class="admin-empty-state"
                                        />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </x-admin.table-card>

                {{-- Not read --}}
                <x-admin.table-card>
                    <div class="admin-receipt-list-head admin-receipt-list-head-unread">
                        <i class="fas fa-envelope"></i>
                        <span>Not read — {{ $unreadCount }}</span>
                    </div>
                    <table class="admin-data-table">
                        <thead>
                            <tr>
                                <th>Provider</th>
                                <th class="text-end">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($unreadRecipients as $user)
                                <tr>
                                    <td>
                                        <div class="admin-table-title">
                                            {{ $user->serviceProvider->company_name ?? $user->name }}
                                        </div>
                                        <div class="admin-table-subtitle">{{ $user->email }}</div>
                                    </td>
                                    <td class="text-end">
                                        <x-ui.badge variant="warning" class="admin-badge admin-badge-draft">
                                            Pending
                                        </x-ui.badge>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2">
                                        <x-ui.empty-state
                                            icon="fas fa-check-circle"
                                            title="Everyone has read it"
                                            description="All recipients have opened this notification."
                                            class="admin-empty-state"
                                        />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </x-admin.table-card>
            </div>
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

    .admin-receipt-note {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        margin-bottom: 1.5rem;
        background: var(--sp-color-surface-muted);
        border: 1px solid var(--sp-color-border);
        border-radius: var(--sp-radius-lg);
        font-size: 0.875rem;
        color: var(--sp-color-text-muted);
    }

    .admin-receipt-note i {
        color: var(--sp-color-info);
    }

    .admin-receipt-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
        gap: 1.5rem;
    }

    .admin-receipt-list-head {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.25rem 1rem;
        font-weight: 700;
        font-size: 1rem;
        color: var(--sp-color-text);
    }

    .admin-receipt-list-head-read i {
        color: var(--sp-color-success);
    }

    .admin-receipt-list-head-unread i {
        color: var(--sp-color-warning);
    }

    .admin-receipt-time {
        font-size: 0.875rem;
        color: var(--sp-color-text-muted);
    }

    @media (max-width: 768px) {
        .admin-stats-row,
        .admin-receipt-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>
@endsection
