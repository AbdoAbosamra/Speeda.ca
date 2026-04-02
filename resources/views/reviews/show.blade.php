@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="text-3xl font-bold mb-1">{{ __('reviews.view_reviews') }}</h1>
                <p class="text-muted mb-0">{{ $review->client->name ?? __('reviews.anonymous') }}</p>
            </div>

            <a href="{{ route('service-providers.show', $review->serviceProvider->id) }}" class="btn btn-outline-secondary rounded-pill">
                <i class="fas fa-arrow-left me-2"></i>{{ __('general.back') }}
            </a>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <div class="text-muted small mb-1">
                        <i class="fas fa-briefcase me-1"></i>{{ $review->serviceProvider->company_name ?? $review->serviceProvider->user->name }}
                    </div>
                    <div class="text-muted small">
                        <i class="fas fa-clock me-1"></i>{{ $review->created_at->format('Y-m-d H:i') }}
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <div class="flex text-yellow-400">
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star {{ $i <= $review->rating ? '' : 'far' }}"></i>
                        @endfor
                    </div>
                    <span class="fw-bold">{{ (int) $review->rating }}/5</span>
                </div>
            </div>

            <div class="mb-3">
                @if(!$review->is_active)
                    <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                        <i class="fas fa-clock me-1"></i>{{ __('reviews.pending_approval') }}
                    </span>
                @else
                    <span class="badge bg-success rounded-pill px-3 py-2">
                        <i class="fas fa-check-circle me-1"></i>{{ __('reviews.approved') }}
                    </span>
                @endif
            </div>

            <p class="mb-0" style="white-space: pre-wrap; line-height: 1.8;">
                {{ $review->review_text }}
            </p>
        </div>

        @if(auth()->check() && (auth()->id() === $review->client_id || auth()->user()->isAdmin()))
            <div class="d-flex gap-2">
                @if(auth()->check() && auth()->id() === $review->client_id && !$review->is_active)
                    <a href="{{ route('reviews.edit', $review) }}" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-edit me-2"></i>{{ __('reviews.edit_review') }}
                    </a>
                @endif
            </div>
        @endif

        <div class="mt-4">
            <a href="{{ route('reviews.index', $review->serviceProvider) }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fas fa-comments me-2"></i>{{ __('service_provider.view_all_reviews') }}
            </a>
        </div>
    </div>
@endsection

