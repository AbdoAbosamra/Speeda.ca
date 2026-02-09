@extends('layouts.app')

@section('content')
    <!-- Admin Reviews Management -->
    <div class="admin-content-wrapper" style="margin-left: 0 !important;">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 fw-bold mb-1">{{ __('admin.manage_reviews') }}</h1>
                    <p class="text-muted mb-0">{{ __('admin.manage_all_reviews') }}</p>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="fas fa-arrow-left me-2"></i>{{ __('admin.back_to_dashboard') }}
                </a>
            </div>

            <!-- Filter Tabs -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-body py-3">
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.reviews') }}"
                            class="btn {{ !request('status') ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">
                            <i class="fas fa-list me-1"></i>{{ __('admin.all') }}
                        </a>
                        <a href="{{ route('admin.reviews', ['status' => 'pending']) }}"
                            class="btn {{ request('status') === 'pending' ? 'btn-warning' : 'btn-outline-secondary' }} rounded-pill px-3">
                            <i class="fas fa-clock me-1"></i>{{ __('admin.pending') }}
                        </a>
                        <a href="{{ route('admin.reviews', ['status' => 'active']) }}"
                            class="btn {{ request('status') === 'active' ? 'btn-success' : 'btn-outline-secondary' }} rounded-pill px-3">
                            <i class="fas fa-check me-1"></i>{{ __('admin.approved') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Reviews List -->
            <div class="card border-0 shadow-lg" style="border-radius: 16px; background: white;">
                <div class="card-header bg-white" style="border-bottom: 2px solid #f1f5f9; border-radius: 16px 16px 0 0;">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-star me-2 text-warning"></i>{{ __('admin.reviews_list') }}
                        <span class="badge bg-secondary ms-2">{{ $reviews->total() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background: #f8fafc;">
                                <tr>
                                    <th class="fw-bold px-4 py-3">{{ __('admin.client') }}</th>
                                    <th class="fw-bold py-3">{{ __('admin.provider') }}</th>
                                    <th class="fw-bold py-3 text-center">{{ __('admin.rating') }}</th>
                                    <th class="fw-bold py-3">{{ __('admin.review_text') }}</th>
                                    <th class="fw-bold py-3">{{ __('admin.status') }}</th>
                                    <th class="fw-bold py-3">{{ __('admin.date') }}</th>
                                    <th class="fw-bold py-3 text-center">{{ __('admin.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reviews as $review)
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td class="px-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                                                    style="width: 36px; height: 36px; font-size: 0.875rem;">
                                                    {{ strtoupper(substr($review->client->name ?? 'U', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <strong>{{ $review->client->name ?? __('admin.unknown') }}</strong>
                                                    <div class="text-muted small">{{ $review->client->email ?? '' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            @if($review->serviceProviderProfile)
                                                <strong>{{ $review->serviceProviderProfile->user->name ?? __('admin.unknown') }}</strong>
                                            @else
                                                <span class="text-muted">{{ __('admin.not_available') }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-center">
                                            <div class="d-flex align-items-center justify-content-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"
                                                        style="font-size: 0.875rem;"></i>
                                                @endfor
                                                <span class="ms-2 fw-bold">{{ $review->rating }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3" style="max-width: 250px;">
                                            <div class="text-truncate" title="{{ $review->review_text }}">
                                                {{ Str::limit($review->review_text, 80) ?: '-' }}
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            @if($review->is_active)
                                                <span class="badge rounded-pill px-3 py-2 bg-success">
                                                    <i class="fas fa-check-circle me-1"></i>{{ __('admin.approved') }}
                                                </span>
                                            @elseif($review->admin_approved_at)
                                                <span class="badge rounded-pill px-3 py-2 bg-danger">
                                                    <i class="fas fa-times-circle me-1"></i>{{ __('admin.rejected') }}
                                                </span>
                                            @else
                                                <span class="badge rounded-pill px-3 py-2 bg-warning text-dark">
                                                    <i class="fas fa-clock me-1"></i>{{ __('admin.pending') }}
                                                </span>
                                            @endif
                                            @if($review->is_featured)
                                                <span class="badge rounded-pill px-3 py-2 bg-info ms-1">
                                                    <i class="fas fa-star me-1"></i>{{ __('admin.featured') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3">
                                            <small class="text-muted">{{ $review->created_at->format('M d, Y') }}</small>
                                        </td>
                                        <td class="py-3 text-center">
                                            <div class="btn-group">
                                                @if(!$review->is_active && !$review->admin_approved_at)
                                                    <!-- Pending Review Actions -->
                                                    <form action="{{ route('admin.reviews.approve', $review) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 me-1"
                                                            title="{{ __('admin.approve') }}">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.reviews.reject', $review) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3 me-1"
                                                            title="{{ __('admin.reject') }}"
                                                            onclick="return confirm('{{ __('admin.confirm_reject_review') }}');">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($review->is_active && !$review->is_featured)
                                                    <form action="{{ route('admin.reviews.feature', $review) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-info rounded-pill px-3 me-1"
                                                            title="{{ __('admin.feature') }}">
                                                            <i class="fas fa-star"></i>
                                                        </button>
                                                    </form>
                                                @elseif($review->is_featured)
                                                    <form action="{{ route('admin.reviews.unfeature', $review) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit"
                                                            class="btn btn-sm btn-outline-info rounded-pill px-3 me-1"
                                                            title="{{ __('admin.unfeature') }}">
                                                            <i class="far fa-star"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                <form action="{{ route('admin.reviews.delete', $review) }}" method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('{{ __('admin.confirm_delete_review') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                                        title="{{ __('admin.delete') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                            <p class="text-muted">{{ __('admin.no_reviews') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($reviews->hasPages())
                    <div class="card-footer bg-white" style="border-top: 2px solid #f1f5f9; border-radius: 0 0 16px 16px;">
                        {{ $reviews->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection