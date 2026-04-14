@extends('layouts.app')

@section('content')
<div class="admin-content-wrapper">
    <div class="container py-4">
        {{-- Header --}}
        <div class="d-flex flex-wrap align-items-end justify-content-between mb-5">
            <div>
                <span class="badge bg-soft-indigo text-indigo px-3 py-2 rounded-pill mb-2 fw-semibold">
                    <i class="fas fa-bell me-1"></i> {{ __('admin.notifications') }}
                </span>
                <h1 class="display-6 fw-bold mb-1" style="color: var(--text-primary);">{{ __('admin.manage_notifications') ?? 'Manage Notifications' }}</h1>
                <p class="text-secondary fs-5 mb-0">{{ __('admin.broadcast_messages_to_providers') ?? 'Broadcast messages to all service providers' }}</p>
            </div>
            <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary d-flex align-items-center gap-2 px-4 py-2 rounded-3 shadow-sm">
                <i class="fas fa-plus"></i> {{ __('admin.add_notification') ?? 'Add Notification' }}
            </a>
        </div>

        {{-- Notifications Table --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3">{{ __('admin.title') ?? 'Title' }} (EN)</th>
                            <th class="py-3">{{ __('admin.status') ?? 'Status' }}</th>
                            <th class="py-3">{{ __('admin.created_by') ?? 'Created By' }}</th>
                            <th class="py-3">{{ __('admin.created_at') ?? 'Created At' }}</th>
                            <th class="py-3">{{ __('admin.expires_at') ?? 'Expires At' }}</th>
                            <th class="pe-4 py-3 text-end">{{ __('admin.actions') ?? 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- @change 2026-04-14 TASK-5 | Replaced harsh single-line truncation with a cleaner preview plus full-content modal access | Long admin notifications must stay readable without layout overflow | risk:LOW --}}
                        @forelse($notifications as $notification)
                            <tr>
                                <td class="ps-4">
                                    <h6 class="fw-bold mb-0">{{ $notification->title_en }}</h6>
                                    <div class="notification-preview text-muted mt-2">{{ $notification->message_en }}</div>
                                    <button type="button"
                                            class="btn btn-link notification-preview-link p-0 mt-2"
                                            data-bs-toggle="modal"
                                            data-bs-target="#viewNotification{{ $notification->id }}">
                                        {{ __('admin.view') ?? 'View' }} {{ __('admin.notification_details') ?? 'Notification Details' }}
                                    </button>
                                </td>
                                <td>
                                    @if($notification->expires_at > now())
                                        <span class="badge bg-success-soft text-success">
                                            <i class="fas fa-check-circle me-1"></i> {{ __('admin.active') ?? 'Active' }}
                                        </span>
                                    @else
                                        <span class="badge bg-danger-soft text-danger">
                                            <i class="fas fa-clock me-1"></i> {{ __('admin.expired') ?? 'Expired' }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="small fw-semibold">{{ $notification->admin->name ?? 'System' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-secondary small">{{ $notification->created_at->format('Y-m-d H:i') }}</span>
                                </td>
                                <td>
                                    <span class="text-secondary small">{{ $notification->expires_at->format('Y-m-d H:i') }}</span>
                                </td>
                                <td class="pe-4 text-end">
                                    <button type="button" class="btn btn-icon btn-soft-indigo me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewNotification{{ $notification->id }}" 
                                            title="{{ __('admin.view') ?? 'View' }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <form action="{{ route('admin.notifications.destroy', $notification) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('admin.confirm_delete_notification') ?? 'Are you sure you want to delete this notification?' }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-soft-danger" title="{{ __('admin.delete') ?? 'Delete' }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>

                                    {{-- Full Message Modal --}}
                                    <div class="modal fade" id="viewNotification{{ $notification->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content border-0 shadow rounded-4 text-start">
                                                <div class="modal-header border-bottom-0 pb-0">
                                                    <h5 class="modal-title fw-bold">{{ __('admin.notification_details') ?? 'Notification Details' }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="mb-4">
                                                        <label class="text-secondary small fw-bold text-uppercase mb-1 d-block">{{ __('admin.title') ?? 'Title' }} (AR)</label>
                                                        <h6 class="fw-bold mb-3" dir="rtl">{{ $notification->title_ar }}</h6>
                                                        <label class="text-secondary small fw-bold text-uppercase mb-1 d-block">{{ __('admin.message') ?? 'Message' }} (AR)</label>
                                                        <div class="p-3 bg-light rounded-3 mb-3 border" dir="rtl" style="white-space: pre-wrap;">{{ $notification->message_ar }}</div>
                                                    </div>
                                                    <hr class="my-4 opacity-10">
                                                    <div class="mb-4">
                                                        <label class="text-secondary small fw-bold text-uppercase mb-1 d-block">{{ __('admin.title') ?? 'Title' }} (EN)</label>
                                                        <h6 class="fw-bold mb-3">{{ $notification->title_en }}</h6>
                                                        <label class="text-secondary small fw-bold text-uppercase mb-1 d-block">{{ __('admin.message') ?? 'Message' }} (EN)</label>
                                                        <div class="p-3 bg-light rounded-3 mb-3 border" style="white-space: pre-wrap;">{{ $notification->message_en }}</div>
                                                    </div>
                                                    <hr class="my-4 opacity-10">
                                                    <div>
                                                        <label class="text-secondary small fw-bold text-uppercase mb-1 d-block">{{ __('admin.title') ?? 'Title' }} (FR)</label>
                                                        <h6 class="fw-bold mb-3">{{ $notification->title_fr }}</h6>
                                                        <label class="text-secondary small fw-bold text-uppercase mb-1 d-block">{{ __('admin.message') ?? 'Message' }} (FR)</label>
                                                        <div class="p-3 bg-light rounded-3 border" style="white-space: pre-wrap;">{{ $notification->message_fr }}</div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top-0 pt-0">
                                                    <button type="button" class="btn btn-outline-secondary px-4 rounded-3" data-bs-dismiss="modal">{{ __('admin.close') ?? 'Close' }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="fas fa-bell-slash display-4 text-light mb-3"></i>
                                        <h5 class="text-secondary">{{ __('admin.no_notifications_found') ?? 'No notifications found' }}</h5>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($notifications->hasPages())
                <div class="card-footer bg-white py-3">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .bg-soft-indigo { background: #eef2ff; }
    .text-indigo { color: #4f46e5; }
    .bg-success-soft { background: #ecfdf5; color: #059669; }
    .bg-danger-soft { background: #fef2f2; color: #dc2626; }
    .btn-soft-indigo {
        color: #4f46e5;
        background-color: #eef2ff;
        border: none;
    }
    .btn-soft-indigo:hover {
        background-color: #e0e7ff;
        color: #4338ca;
    }
    .btn-soft-danger {
        color: #dc2626;
        background-color: #fef2f2;
        border: none;
    }
    .btn-soft-danger:hover {
        background-color: #fee2e2;
        color: #b91c1c;
    }
    .btn-icon {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        transition: all 0.2s;
    }
    .notification-preview {
        max-inline-size: 300px;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 3;
        overflow: hidden;
        line-height: 1.5;
        white-space: normal;
        word-break: break-word;
    }
    .notification-preview-link {
        font-size: 0.825rem;
        font-weight: 600;
        text-decoration: none;
    }
    .notification-preview-link:hover {
        text-decoration: underline;
    }
</style>
@endsection
