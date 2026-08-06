@extends('layouts.app')

@section('content')
    <div class="admin-content-wrapper" style="margin-inline-start: 0 !important;">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 fw-bold mb-1">Review Details</h1>
                    <p class="text-muted mb-0">Review ID: {{ $review->id }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.reviews') }}" class="btn btn-outline-secondary rounded-pill px-3">
                        <i class="fas fa-arrow-left me-2"></i>Back to Reviews
                    </a>
                    {{-- Guarded: an orphaned review (provider removed) used to 500 here. --}}
                    @if($review->serviceProvider)
                        <a href="{{ route('service-providers.show', $review->serviceProvider->id) }}"
                            class="btn btn-outline-primary rounded-pill px-3" target="_blank" rel="noopener">
                            <i class="fas fa-user me-2"></i>View Provider
                        </a>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-lg" style="border-radius: 16px; background: #fff;">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="text-muted mb-2">Client</div>
                            <div class="fw-semibold">
                                {{ $review->client->name ?? __('reviews.anonymous') }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted mb-2">Provider</div>
                            <div class="fw-semibold">
                                {{ $review->serviceProvider?->company_name
                                    ?? $review->serviceProvider?->user?->name
                                    ?? ('Provider #' . $review->service_provider_id) }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted mb-2">Rating</div>
                            <div class="fw-semibold">
                                {{ (int) $review->rating }}/5
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="mb-3">
                        <div class="text-muted mb-2">Review Text</div>
                        <div class="bg-light p-3 rounded-3" style="white-space: pre-wrap; line-height: 1.8;">
                            {{ $review->review_text }}
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        @if(!$review->is_active)
                            <form action="{{ route('admin.reviews.approve', $review) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success rounded-pill px-4">
                                    <i class="fas fa-check me-2"></i>{{ $review->admin_approved_at ? 'Re-approve' : 'Approve' }}
                                </button>
                            </form>
                        @endif

                        @if($review->is_active || !$review->admin_approved_at)
                            <form action="{{ route('admin.reviews.reject', $review) }}" method="POST"
                                  onsubmit="return confirm('Reject this review?');">
                                @csrf
                                <button type="submit" class="btn btn-danger rounded-pill px-4">
                                    <i class="fas fa-times me-2"></i>Reject
                                </button>
                            </form>
                        @endif

                        @if($review->is_active)
                            @if(!$review->is_featured)
                                <form action="{{ route('admin.reviews.feature', $review) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-info rounded-pill px-4">
                                        <i class="fas fa-star me-2"></i>Feature
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.reviews.unfeature', $review) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-info rounded-pill px-4">
                                        <i class="far fa-star me-2"></i>Unfeature
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>

                </div>
            </div>

        </div>
    </div>
@endsection

