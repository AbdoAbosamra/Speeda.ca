@extends('layouts.app')

@section('content')
    <div class="admin-cms-page" dir="ltr">
        <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
            <section class="admin-page-header">
                <div>
                    <p class="admin-section-eyebrow">Notifications</p>
                    <h1>Manage Notifications</h1>
                    <p>Broadcast multilingual messages to active service providers.</p>
                </div>
                <a href="{{ route('admin.notifications.create') }}" class="admin-btn admin-btn-primary">
                    <i class="fas fa-plus"></i>
                    <span>Create Notification</span>
                </a>
            </section>

            <section class="admin-table-card">
                <div class="table-responsive">
                    <table class="admin-data-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
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
                                        <span class="admin-badge {{ $isActive ? 'admin-badge-published' : 'admin-badge-draft' }}">
                                            {{ $isActive ? 'Active' : 'Expired' }}
                                        </span>
                                    </td>
                                    <td>{{ $notification->admin->name ?? 'System' }}</td>
                                    <td>{{ optional($notification->created_at)->format('M d, Y H:i') }}</td>
                                    <td>{{ optional($notification->expires_at)->format('M d, Y H:i') }}</td>
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
                                                        <div class="admin-language-preview" dir="rtl">
                                                            <strong>Arabic</strong>
                                                            <h3>{{ $notification->title_ar }}</h3>
                                                            <p>{{ $notification->message_ar }}</p>
                                                        </div>
                                                        <div class="admin-language-preview">
                                                            <strong>English</strong>
                                                            <h3>{{ $notification->title_en }}</h3>
                                                            <p>{{ $notification->message_en }}</p>
                                                        </div>
                                                        <div class="admin-language-preview">
                                                            <strong>French</strong>
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
                                    <td colspan="6">
                                        <div class="admin-empty-state">
                                            <i class="fas fa-bell-slash"></i>
                                            <h2>No notifications found</h2>
                                            <p>Create a notification to reach active service providers.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            @if($notifications->hasPages())
                <div class="admin-pagination-wrap">{{ $notifications->links() }}</div>
            @endif
        </div>
    </div>
@endsection
