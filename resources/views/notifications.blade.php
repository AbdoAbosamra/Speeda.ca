@extends('layouts.app')

@section('content')
<div class="notifications-page py-4 py-lg-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                {{-- Header Section --}}
                <div class="notifications-header mb-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <h1 class="notifications-title h3 mb-1">
                                <i class="fas fa-bell me-2"></i>
                                {{ __('admin.notifications') }}
                            </h1>
                            <p class="notifications-subtitle text-muted mb-0">
                                {{ __('general.stay_updated_with_latest_alerts') }}
                            </p>
                        </div>
                        
                        @if($unreadCount > 0)
                            <button type="button" class="btn btn-mark-all-read" id="markAllReadBtn">
                                <i class="fas fa-check-double me-2"></i>
                                {{ __('general.mark_all_read') }}
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Filter Tabs --}}
                <div class="notifications-filters mb-4">
                    <div class="filter-tabs">
                        <a href="{{ route('notifications.index', ['filter' => 'all']) }}" 
                           class="filter-tab {{ $filter === 'all' ? 'active' : '' }}">
                            <i class="fas fa-layer-group me-1"></i>
                            {{ __('general.all') }}
                            <span class="filter-count">{{ $totalCount }}</span>
                        </a>
                        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
                           class="filter-tab {{ $filter === 'unread' ? 'active' : '' }}">
                            <i class="fas fa-envelope me-1"></i>
                            {{ __('general.unread') }}
                            <span class="filter-count">{{ $unreadCount }}</span>
                        </a>
                        <a href="{{ route('notifications.index', ['filter' => 'read']) }}" 
                           class="filter-tab {{ $filter === 'read' ? 'active' : '' }}">
                            <i class="fas fa-envelope-open me-1"></i>
                            {{ __('general.read') }}
                            <span class="filter-count">{{ $readCount }}</span>
                        </a>
                    </div>
                </div>

                {{-- Notifications List --}}
                <div class="notifications-list" id="notificationsList">
                    @forelse($notifications as $notif)
                        @php $isRead = in_array($notif->id, $readNotificationIds); @endphp
                        <article class="notification-card {{ $isRead ? 'is-read' : 'is-unread' }}" 
                                 data-notification-id="{{ $notif->id }}"
                                 data-title="{{ $notif->title }}"
                                 data-message="{{ $notif->message }}"
                                 data-time="{{ $notif->created_at->diffForHumans() }}">
                            <div class="notification-indicator"></div>
                            <div class="notification-icon">
                                <i class="fas {{ $isRead ? 'fa-envelope-open' : 'fa-envelope' }}"></i>
                            </div>
                            <div class="notification-content">
                                <div class="notification-header">
                                    <h3 class="notification-title">{{ $notif->title }}</h3>
                                    <span class="notification-time">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $notif->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                <p class="notification-message">{{ $notif->message }}</p>
                            </div>
                        </article>
                    @empty
                        <div class="notifications-empty">
                            <div class="empty-icon">
                                <i class="fas fa-bell-slash"></i>
                            </div>
                            <h2 class="empty-title">{{ __('admin.no_notifications') }}</h2>
                            <p class="empty-subtitle">{{ __('general.all_caught_up') }}</p>
                            <a href="{{ route('home') }}" class="btn btn-primary mt-3">
                                <i class="fas fa-home me-2"></i>
                                {{ __('general.back_to_home') }}
                            </a>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if($notifications->hasPages())
                    <div class="notifications-pagination mt-5">
                        {{ $notifications->links('components.global-pagination') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Notification Detail Modal --}}
<div class="modal fade" id="notificationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNotificationTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('general.close') }}"></button>
            </div>
            <div class="modal-body">
                <div class="modal-meta mb-3">
                    <i class="fas fa-clock me-1"></i>
                    <span id="modalNotificationTime"></span>
                </div>
                <div class="modal-message" id="modalNotificationMessage"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    {{ __('general.close') }}
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.notifications-page {
    min-height: 60vh;
}

.notifications-header {
    padding: 1.5rem;
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(139, 92, 246, 0.08));
    border-radius: 16px;
    border: 1px solid rgba(99, 102, 241, 0.1);
}

.notifications-title {
    font-weight: 700;
    color: var(--text-primary, #0f172a);
}

.notifications-title i {
    color: var(--primary-500, #3b82f6);
}

.btn-mark-all-read {
    background: white;
    border: 2px solid var(--primary-200, #bfdbfe);
    color: var(--primary-600, #2563eb);
    padding: 0.5rem 1.25rem;
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.2s ease;
}

.btn-mark-all-read:hover {
    background: var(--primary-50, #eff6ff);
    border-color: var(--primary-500, #3b82f6);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

.btn-mark-all-read.loading {
    pointer-events: none;
    opacity: 0.7;
}

.filter-tabs {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.filter-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    background: white;
    border: 2px solid var(--border-default, #e2e8f0);
    border-radius: 12px;
    color: var(--text-secondary, #475569);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9375rem;
    transition: all 0.2s ease;
}

.filter-tab:hover {
    border-color: var(--primary-300, #93c5fd);
    background: var(--primary-50, #eff6ff);
}

.filter-tab.active {
    background: linear-gradient(135deg, var(--primary-500, #3b82f6), var(--primary-600, #2563eb));
    border-color: transparent;
    color: white;
}

.filter-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 24px;
    height: 24px;
    padding: 0 0.5rem;
    background: rgba(0, 0, 0, 0.1);
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 700;
}

.filter-tab.active .filter-count {
    background: rgba(255, 255, 255, 0.25);
}

.notification-card {
    display: flex;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
    margin-bottom: 0.75rem;
    background: white;
    border: 1px solid var(--border-default, #e2e8f0);
    border-radius: 16px;
    cursor: pointer;
    transition: all 0.25s ease;
    position: relative;
    overflow: hidden;
}

.notification-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    border-color: var(--primary-200, #bfdbfe);
}

.notification-card.is-unread {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.03), rgba(139, 92, 246, 0.05));
    border-color: var(--primary-200, #bfdbfe);
}

.notification-indicator {
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(180deg, var(--primary-500, #3b82f6), var(--primary-600, #2563eb));
    opacity: 0;
    transition: opacity 0.2s ease;
}

.notification-card.is-unread .notification-indicator {
    opacity: 1;
}

[dir="rtl"] .notification-indicator {
    left: auto;
    right: 0;
}

.notification-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: var(--surface-subtle, #f8fafc);
    color: var(--text-muted, #94a3b8);
    flex-shrink: 0;
    transition: all 0.2s ease;
}

.notification-card.is-unread .notification-icon {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.15));
    color: var(--primary-500, #3b82f6);
}

.notification-card:hover .notification-icon {
    transform: scale(1.05);
}

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 0.5rem;
    flex-wrap: wrap;
}

.notification-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary, #0f172a);
    margin: 0;
}

.notification-card.is-read .notification-title {
    color: var(--text-secondary, #475569);
}

.notification-time {
    font-size: 0.8125rem;
    color: var(--text-muted, #94a3b8);
    white-space: nowrap;
}

.notification-message {
    font-size: 0.9375rem;
    line-height: 1.6;
    color: var(--text-secondary, #475569);
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.notifications-empty {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-icon {
    font-size: 4rem;
    color: var(--text-muted, #94a3b8);
    opacity: 0.3;
    margin-bottom: 1.5rem;
}

.empty-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-primary, #0f172a);
    margin-bottom: 0.5rem;
}

.empty-subtitle {
    color: var(--text-muted, #94a3b8);
}

.notifications-pagination .pagination {
    justify-content: center;
}

.notifications-pagination .page-link {
    border-radius: 8px;
    margin: 0 0.25rem;
    border: 1px solid var(--border-default, #e2e8f0);
    color: var(--text-secondary, #475569);
}

.notifications-pagination .page-link:hover {
    background: var(--primary-50, #eff6ff);
    border-color: var(--primary-300, #93c5fd);
    color: var(--primary-600, #2563eb);
}

.notifications-pagination .page-item.active .page-link {
    background: var(--primary-500, #3b82f6);
    border-color: var(--primary-500, #3b82f6);
}

/* Modal Styles */
#notificationModal .modal-content {
    border-radius: 20px;
    border: none;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

#notificationModal .modal-header {
    background: linear-gradient(135deg, var(--primary-500, #3b82f6), var(--primary-600, #2563eb));
    color: white;
    border-radius: 20px 20px 0 0;
    padding: 1.5rem 2rem;
}

#notificationModal .modal-title {
    font-weight: 700;
}

#notificationModal .btn-close {
    filter: brightness(0) invert(1);
}

#notificationModal .modal-body {
    padding: 2rem;
}

#notificationModal .modal-meta {
    color: var(--text-muted, #94a3b8);
    font-size: 0.875rem;
}

#notificationModal .modal-message {
    font-size: 1.0625rem;
    line-height: 1.8;
    color: var(--text-primary, #0f172a);
    white-space: pre-wrap;
}

#notificationModal .modal-footer {
    border-top: 1px solid var(--border-default, #e2e8f0);
    padding: 1rem 2rem;
}

/* Responsive */
@media (max-width: 768px) {
    .notifications-header {
        padding: 1rem;
    }
    
    .filter-tabs {
        width: 100%;
    }
    
    .filter-tab {
        flex: 1;
        justify-content: center;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }
    
    .notification-card {
        padding: 1rem;
    }
    
    .notification-icon {
        width: 40px;
        height: 40px;
    }
    
    .notification-header {
        flex-direction: column;
        gap: 0.25rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const markAllBtn = document.getElementById('markAllReadBtn');
    const notificationsList = document.getElementById('notificationsList');
    const notificationModal = new bootstrap.Modal(document.getElementById('notificationModal'));
    
    // Mark all as read
    if (markAllBtn) {
        markAllBtn.addEventListener('click', async function() {
            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>{{ __('general.loading') }}';
            this.classList.add('loading');
            
            try {
                const response = await fetch('{{ route("notifications.mark-as-read") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Update all notification cards
                    document.querySelectorAll('.notification-card.is-unread').forEach(card => {
                        card.classList.remove('is-unread');
                        card.classList.add('is-read');
                        const icon = card.querySelector('.notification-icon i');
                        if (icon) icon.className = 'fas fa-envelope-open';
                    });
                    
                    // Hide button
                    markAllBtn.style.display = 'none';
                    
                    // Update filter counts via page reload
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error:', error);
                this.innerHTML = originalHTML;
                this.classList.remove('loading');
            }
        });
    }
    
    // Click on notification card to view details
    notificationsList?.querySelectorAll('.notification-card').forEach(card => {
        card.addEventListener('click', function() {
            const title = this.dataset.title;
            const message = this.dataset.message;
            const time = this.dataset.time;
            
            document.getElementById('modalNotificationTitle').textContent = title;
            document.getElementById('modalNotificationMessage').textContent = message;
            document.getElementById('modalNotificationTime').textContent = time;
            
            notificationModal.show();
        });
    });
});
</script>
@endsection
