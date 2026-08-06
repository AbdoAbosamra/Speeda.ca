@extends('layouts.app')

@section('title', __('admin.comment_details'))

@section('content')
    <div class="admin-content-wrapper" style="margin-inline-start: 0 !important;">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <div>
                    <h1 class="h3 fw-bold mb-1">{{ __('admin.comment_details') }}</h1>
                    <p class="text-muted mb-0">ID: {{ $comment->id }}</p>
                </div>
                <a href="{{ route('admin.comments') }}" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="fas fa-arrow-left me-2"></i>{{ __('admin.back_to_list') }}
                </a>
            </div>

            <div class="card border-0 shadow-lg" style="border-radius: 16px; background: #fff;">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="text-muted mb-2">{{ __('admin.user') }}</div>
                            <div class="fw-semibold">{{ $comment->user->name ?? __('admin.unknown') }}</div>
                            <div class="text-muted small">{{ $comment->user->email ?? '' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted mb-2">{{ __('admin.type') }}</div>
                            <div class="fw-semibold">{{ class_basename($comment->commentable_type ?? '') ?: '-' }}</div>
                            @if($comment->commentable)
                                <div class="text-muted small">
                                    #{{ $comment->commentable_id }}
                                    @if(isset($comment->commentable->title))
                                        · {{ Str::limit($comment->commentable->title, 60) }}
                                    @elseif(isset($comment->commentable->company_name))
                                        · {{ Str::limit($comment->commentable->company_name, 60) }}
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted mb-2">{{ __('admin.status') }}</div>
                            @if($comment->is_active)
                                <span class="badge rounded-pill px-3 py-2 bg-success">
                                    <i class="fas fa-check-circle me-1"></i>{{ __('admin.approved') }}
                                </span>
                            @elseif($comment->is_flagged)
                                <span class="badge rounded-pill px-3 py-2 bg-danger">
                                    <i class="fas fa-flag me-1"></i>{{ __('admin.flagged') }}
                                </span>
                            @elseif($comment->isRejected())
                                <span class="badge rounded-pill px-3 py-2 bg-dark">
                                    <i class="fas fa-ban me-1"></i>{{ __('admin.rejected') }}
                                </span>
                            @else
                                <span class="badge rounded-pill px-3 py-2 bg-warning text-dark">
                                    <i class="fas fa-clock me-1"></i>{{ __('admin.pending') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="mb-3">
                        <div class="text-muted mb-2">{{ __('admin.content') }}</div>
                        <div class="bg-light p-3 rounded-3" style="white-space: pre-wrap; line-height: 1.8;">{{ $comment->content }}</div>
                    </div>

                    @if($comment->rejection_reason)
                        <div class="alert alert-danger rounded-3">
                            <strong>{{ __('admin.rejection_reason') }}:</strong> {{ $comment->rejection_reason }}
                        </div>
                    @endif

                    <div class="row g-3 text-muted small mb-4">
                        <div class="col-md-4">
                            <i class="fas fa-calendar me-1"></i>
                            {{ __('admin.date') }}: {{ optional($comment->created_at)->format('M d, Y H:i') ?: '-' }}
                        </div>
                        @if($comment->approved_at)
                            <div class="col-md-4">
                                <i class="fas fa-gavel me-1"></i>
                                {{ optional($comment->approved_at)->format('M d, Y H:i') }}
                                @if($comment->approvedBy)
                                    · {{ $comment->approvedBy->name }}
                                @endif
                            </div>
                        @endif
                        @if($comment->deleted_at)
                            <div class="col-md-4 text-danger">
                                <i class="fas fa-trash me-1"></i>
                                {{ optional($comment->deleted_at)->format('M d, Y H:i') }}
                            </div>
                        @endif
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        @if(!$comment->is_active)
                            <form action="{{ route('admin.comments.approve', $comment) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success rounded-pill px-4">
                                    <i class="fas fa-check me-2"></i>{{ __('admin.approve') }}
                                </button>
                            </form>
                        @endif

                        @if(!$comment->isRejected())
                            <button type="button" class="btn btn-danger rounded-pill px-4"
                                    data-bs-toggle="modal" data-bs-target="#rejectModalShow">
                                <i class="fas fa-times me-2"></i>{{ __('admin.reject') }}
                            </button>
                        @endif

                        @if($comment->is_flagged)
                            <form action="{{ route('admin.comments.unflag', $comment) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-warning rounded-pill px-4">
                                    <i class="fas fa-flag me-2"></i>{{ __('admin.unflag') }}
                                </button>
                            </form>
                        @elseif($comment->is_active)
                            <form action="{{ route('admin.comments.flag', $comment) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-warning rounded-pill px-4">
                                    <i class="fas fa-flag me-2"></i>{{ __('admin.flag') }}
                                </button>
                            </form>
                        @endif

                        @if($comment->trashed())
                            <form action="{{ route('admin.comments.restore', $comment->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-success rounded-pill px-4">
                                    <i class="fas fa-undo me-2"></i>{{ __('admin.restore') }}
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.comments.delete', $comment) }}" method="POST"
                                  onsubmit="return confirm('{{ __('admin.confirm_delete_comment') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger rounded-pill px-4">
                                    <i class="fas fa-trash me-2"></i>{{ __('admin.delete') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Reject modal --}}
    <div class="modal fade" id="rejectModalShow" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px; border: none;">
                <div class="modal-header" style="border-bottom: 2px solid #f1f5f9;">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-times-circle me-2 text-danger"></i>{{ __('admin.reject_comment') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.comments.reject', $comment) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <label class="form-label fw-semibold">
                            {{ __('admin.rejection_reason') }} ({{ __('admin.optional') }})
                        </label>
                        <textarea name="reason" class="form-control" rows="3"
                                  placeholder="{{ __('admin.enter_rejection_reason') }}"
                                  style="border-radius: 12px; border: 2px solid #e2e8f0;"></textarea>
                    </div>
                    <div class="modal-footer" style="border-top: 2px solid #f1f5f9;">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
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
@endsection
