@extends('layouts.app')

@section('content')
    <!-- Admin Comments Management -->
    <div class="admin-content-wrapper" style="margin-inline-start: 0 !important;">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 fw-bold mb-1">{{ __('admin.manage_comments') }}</h1>
                    <p class="text-muted mb-0">{{ __('admin.manage_all_comments') }}</p>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="fas fa-arrow-left me-2"></i>{{ __('admin.back_to_dashboard') }}
                </a>
            </div>

            <!-- Filter Tabs -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-body py-3">
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.comments') }}"
                            class="btn {{ !request('status') ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">
                            <i class="fas fa-list me-1"></i>{{ __('admin.all') }}
                        </a>
                        <a href="{{ route('admin.comments', ['status' => 'pending']) }}"
                            class="btn {{ request('status') === 'pending' ? 'btn-warning' : 'btn-outline-secondary' }} rounded-pill px-3">
                            <i class="fas fa-clock me-1"></i>{{ __('admin.pending') }}
                        </a>
                        <a href="{{ route('admin.comments', ['status' => 'active']) }}"
                            class="btn {{ request('status') === 'active' ? 'btn-success' : 'btn-outline-secondary' }} rounded-pill px-3">
                            <i class="fas fa-check me-1"></i>{{ __('admin.approved') }}
                        </a>
                        <a href="{{ route('admin.comments', ['status' => 'flagged']) }}"
                            class="btn {{ request('status') === 'flagged' ? 'btn-danger' : 'btn-outline-secondary' }} rounded-pill px-3">
                            <i class="fas fa-flag me-1"></i>{{ __('admin.flagged') }}
                        </a>
                        <a href="{{ route('admin.comments', ['status' => 'rejected']) }}"
                            class="btn {{ request('status') === 'rejected' ? 'btn-dark' : 'btn-outline-secondary' }} rounded-pill px-3">
                            <i class="fas fa-ban me-1"></i>{{ __('admin.rejected') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Comments List -->
            <div class="card border-0 shadow-lg" style="border-radius: 16px; background: white;">
                <div class="card-header bg-white" style="border-bottom: 2px solid #f1f5f9; border-radius: 16px 16px 0 0;">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-comments me-2 text-info"></i>{{ __('admin.comments_list') }}
                        <span class="badge bg-secondary ms-2">{{ $comments->total() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background: #f8fafc;">
                                <tr>
                                    <th class="fw-bold px-4 py-3">{{ __('admin.user') }}</th>
                                    <th class="fw-bold py-3">{{ __('admin.content') }}</th>
                                    <th class="fw-bold py-3">{{ __('admin.type') }}</th>
                                    <th class="fw-bold py-3">{{ __('admin.status') }}</th>
                                    <th class="fw-bold py-3">{{ __('admin.date') }}</th>
                                    <th class="fw-bold py-3 text-center">{{ __('admin.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($comments as $comment)
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td class="px-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center me-2"
                                                    style="width: 36px; height: 36px; font-size: 0.875rem;">
                                                    {{ strtoupper(substr($comment->user->name ?? 'U', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <strong>{{ $comment->user->name ?? __('admin.unknown') }}</strong>
                                                    <div class="text-muted small">{{ $comment->user->email ?? '' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3" style="max-width: 300px;">
                                            <div class="text-truncate" title="{{ $comment->content }}">
                                                {{ Str::limit($comment->content, 100) }}
                                            </div>
                                            @if($comment->rejection_reason)
                                                <div class="text-danger small mt-1">
                                                    <i class="fas fa-info-circle me-1"></i>{{ __('admin.rejection_reason') }}:
                                                    {{ $comment->rejection_reason }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-3">
                                            @php
                                                $typeLabel = class_basename($comment->commentable_type ?? 'Unknown');
                                            @endphp
                                            <span class="badge rounded-pill px-3 py-2 bg-light text-dark">
                                                <i class="fas fa-link me-1"></i>{{ $typeLabel }}
                                            </span>
                                        </td>
                                        <td class="py-3">
                                            @if($comment->is_active)
                                                <span class="badge rounded-pill px-3 py-2 bg-success">
                                                    <i class="fas fa-check-circle me-1"></i>{{ __('admin.approved') }}
                                                </span>
                                            @elseif($comment->is_flagged)
                                                <span class="badge rounded-pill px-3 py-2 bg-danger">
                                                    <i class="fas fa-flag me-1"></i>{{ __('admin.flagged') }}
                                                </span>
                                            @elseif($comment->rejection_reason)
                                                <span class="badge rounded-pill px-3 py-2 bg-dark">
                                                    <i class="fas fa-ban me-1"></i>{{ __('admin.rejected') }}
                                                </span>
                                            @else
                                                <span class="badge rounded-pill px-3 py-2 bg-warning text-dark">
                                                    <i class="fas fa-clock me-1"></i>{{ __('admin.pending') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3">
                                            <small class="text-muted">{{ $comment->created_at->format('M d, Y') }}</small>
                                            <div class="text-muted small">{{ $comment->created_at->format('H:i') }}</div>
                                        </td>
                                        <td class="py-3 text-center">
                                            <div class="btn-group">
                                                @if(!$comment->is_active && !$comment->rejection_reason)
                                                    <!-- Pending Comment Actions -->
                                                    <form action="{{ route('admin.comments.approve', $comment) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 me-1"
                                                            title="{{ __('admin.approve') }}">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 me-1"
                                                        title="{{ __('admin.reject') }}" data-bs-toggle="modal"
                                                        data-bs-target="#rejectModal{{ $comment->id }}">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif

                                                @if(!$comment->is_flagged && $comment->is_active)
                                                    <form action="{{ route('admin.comments.flag', $comment) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit"
                                                            class="btn btn-sm btn-outline-warning rounded-pill px-3 me-1"
                                                            title="{{ __('admin.flag') }}">
                                                            <i class="fas fa-flag"></i>
                                                        </button>
                                                    </form>
                                                @elseif($comment->is_flagged)
                                                    <form action="{{ route('admin.comments.unflag', $comment) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 me-1"
                                                            title="{{ __('admin.unflag') }}">
                                                            <i class="fas fa-check"></i> {{ __('admin.approve') }}
                                                        </button>
                                                    </form>
                                                @endif

                                                <form action="{{ route('admin.comments.delete', $comment) }}" method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('{{ __('admin.confirm_delete_comment') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                                        title="{{ __('admin.delete') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>

                                            <!-- Reject Modal -->
                                            <div class="modal fade" id="rejectModal{{ $comment->id }}" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content" style="border-radius: 16px; border: none;">
                                                        <div class="modal-header" style="border-bottom: 2px solid #f1f5f9;">
                                                            <h5 class="modal-title fw-bold">
                                                                <i
                                                                    class="fas fa-times-circle me-2 text-danger"></i>{{ __('admin.reject_comment') }}
                                                            </h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form action="{{ route('admin.comments.reject', $comment) }}"
                                                            method="POST">
                                                            @csrf
                                                            <div class="modal-body">
                                                                <label
                                                                    class="form-label fw-semibold">{{ __('admin.rejection_reason') }}
                                                                    ({{ __('admin.optional') }})</label>
                                                                <textarea name="reason" class="form-control" rows="3"
                                                                    placeholder="{{ __('admin.enter_rejection_reason') }}"
                                                                    style="border-radius: 12px; border: 2px solid #e2e8f0;"></textarea>
                                                            </div>
                                                            <div class="modal-footer" style="border-top: 2px solid #f1f5f9;">
                                                                <button type="button"
                                                                    class="btn btn-secondary rounded-pill px-4"
                                                                    data-bs-dismiss="modal">
                                                                    {{ __('admin.cancel') }}
                                                                </button>
                                                                <button type="submit" class="btn btn-danger rounded-pill px-4">
                                                                    <i class="fas fa-times me-1"></i>{{ __('admin.reject') }}
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                            <p class="text-muted">{{ __('admin.no_comments') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($comments->hasPages())
                    <div class="card-footer bg-white" style="border-top: 2px solid #f1f5f9; border-radius: 0 0 16px 16px;">
                        {{ $comments->links('components.global-pagination') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
