@extends('layouts.app')

@section('content')
    <!-- Admin Reviews Management -->
    <div class="admin-content-wrapper" style="margin-inline-start: 0 !important;">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 fw-bold mb-1">Manage Reviews</h1>
                    <p class="text-muted mb-0">View and manage all service provider reviews</p>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>

            <!-- Filter Tabs and Per Page Selector -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.reviews') }}"
                                class="btn {{ !request('status') ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">
                                <i class="fas fa-list me-1"></i>All
                            </a>
                            <a href="{{ route('admin.reviews', ['status' => 'pending']) }}"
                                class="btn {{ request('status') === 'pending' ? 'btn-warning' : 'btn-outline-secondary' }} rounded-pill px-3">
                                <i class="fas fa-clock me-1"></i>Pending
                            </a>
                            <a href="{{ route('admin.reviews', ['status' => 'active']) }}"
                                class="btn {{ request('status') === 'active' ? 'btn-success' : 'btn-outline-secondary' }} rounded-pill px-3">
                                <i class="fas fa-check me-1"></i>Approved
                            </a>
                        </div>
                        <form method="GET" action="{{ route('admin.reviews') }}" class="d-flex align-items-center gap-2">
                            @if(request('status'))
                                <input type="hidden" name="status" value="{{ request('status') }}">
                            @endif
                            <label for="per_page" class="text-muted small mb-0">Show:</label>
                            <select name="per_page" id="per_page" class="form-select form-select-sm rounded-pill" 
                                    style="width: auto; min-width: 80px;" onchange="this.form.submit()">
                                <option value="10" {{ request('per_page', 20) == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page', 20) == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100</option>
                            </select>
                            <span class="text-muted small">per page</span>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Reviews List -->
            <div class="card border-0 shadow-lg" style="border-radius: 16px; background: white;">
                <div class="card-header bg-white" style="border-bottom: 2px solid #f1f5f9; border-radius: 16px 16px 0 0;">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-star me-2 text-warning"></i>Reviews List
                        <span class="badge bg-secondary ms-2">{{ $reviews->total() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background: #f8fafc;">
                                <tr>
                                    <th class="fw-bold px-4 py-3">Client</th>
                                    <th class="fw-bold py-3">Provider</th>
                                    <th class="fw-bold py-3 text-center">Rating</th>
                                    <th class="fw-bold py-3">Review</th>
                                    <th class="fw-bold py-3">Status</th>
                                    <th class="fw-bold py-3">Date</th>
                                    <th class="fw-bold py-3 text-center">Actions</th>
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
                                                    <strong>{{ $review->client->name ?? 'Unknown' }}</strong>
                                                    <div class="text-muted small">{{ $review->client->email ?? '' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            @if(isset($review->serviceProvider) && $review->serviceProvider)
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-2"
                                                        style="width: 36px; height: 36px; font-size: 0.875rem;">
                                                        {{ strtoupper(substr($review->serviceProvider->user->name ?? 'P', 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <strong>{{ $review->serviceProvider->user->name ?? 'Unknown' }}</strong>
                                                        <div class="text-muted small">ID: {{ $review->serviceProvider->id ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted"><i class="fas fa-exclamation-circle me-1"></i>Not Available</span>
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
                                        <td class="py-3" style="max-width: 200px;">
                                            @if(isset($review->review_text) && $review->review_text)
                                                <a href="#" class="text-decoration-none"
                                                   data-bs-toggle="modal"
                                                   data-bs-target="#reviewModal{{ $review->id }}"
                                                   style="color: #4361ee;">
                                                    <i class="fas fa-comment-dots me-1"></i>
                                                    {{ Str::limit($review->review_text, 60) ?: 'View Review' }}
                                                    <i class="fas fa-expand-alt ms-1 small"></i>
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="py-3">
                                            @if($review->is_active)
                                                <span class="badge rounded-pill px-3 py-2 bg-success">
                                                    <i class="fas fa-check-circle me-1"></i>Approved
                                                </span>
                                            @elseif($review->admin_approved_at)
                                                <span class="badge rounded-pill px-3 py-2 bg-danger">
                                                    <i class="fas fa-times-circle me-1"></i>Rejected
                                                </span>
                                            @else
                                                <span class="badge rounded-pill px-3 py-2 bg-warning text-dark">
                                                    <i class="fas fa-clock me-1"></i>Pending
                                                </span>
                                            @endif
                                            @if($review->is_featured)
                                                <span class="badge rounded-pill px-3 py-2 bg-info ms-1">
                                                    <i class="fas fa-star me-1"></i>Featured
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3">
                                            <small class="text-muted">{{ isset($review->created_at) && $review->created_at ? $review->created_at->format('M d, Y') : '-' }}</small>
                                        </td>
                                        <td class="py-3 text-center">
                                            <div class="btn-group">
                                                <a href="{{ route('admin.reviews.show', $review) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 me-1" title="View details">
                                                    <i class="fas fa-eye me-1"></i>View
                                                </a>
                                                @if(!$review->is_active && !$review->admin_approved_at)
                                                    <!-- Pending Review Actions -->
                                                    <form action="{{ route('admin.reviews.approve', $review) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 me-1"
                                                            title="Approve">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.reviews.reject', $review) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3 me-1"
                                                            title="Reject"
                                                            onclick="return confirm('Are you sure you want to reject this review?');">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($review->is_active && !$review->is_featured)
                                                    <form action="{{ route('admin.reviews.feature', $review) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-info rounded-pill px-3 me-1"
                                                            title="Feature">
                                                            <i class="fas fa-star"></i>
                                                        </button>
                                                    </form>
                                                @elseif($review->is_featured)
                                                    <form action="{{ route('admin.reviews.unfeature', $review) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit"
                                                            class="btn btn-sm btn-outline-info rounded-pill px-3 me-1"
                                                            title="Unfeature">
                                                            <i class="far fa-star"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                <form action="{{ route('admin.reviews.delete', $review) }}" method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this review?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                                        title="Delete">
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
                                            <p class="text-muted">No reviews found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($reviews->hasPages())
                    <div class="card-footer bg-white" style="border-top: 2px solid #f1f5f9; border-radius: 0 0 16px 16px;">
                        {{ $reviews->appends(request()->query())->links('components.pagination.default') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Review Modals -->
    @foreach($reviews as $review)
        @if($review->review_text)
            <div class="modal fade" id="reviewModal{{ $review->id }}" tabindex="-1" aria-labelledby="reviewModalLabel{{ $review->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="border-radius: 16px; border: none;">
                        <div class="modal-header" style="background: linear-gradient(135deg, #4361ee, #3f37c9); border-radius: 16px 16px 0 0;">
                            <h5 class="modal-title text-white" id="reviewModalLabel{{ $review->id }}">
                                <i class="fas fa-comment-dots me-2"></i>Review Details
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <!-- Review Meta -->
                            <div class="d-flex align-items-center mb-3 pb-3" style="border-bottom: 1px solid #e2e8f0;">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                                     style="width: 40px; height: 40px; font-size: 1rem;">
                                    {{ strtoupper(substr($review->client->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <strong>{{ $review->client->name ?? 'Unknown' }}</strong>
                                    <div class="text-muted small">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}" style="font-size: 0.75rem;"></i>
                                        @endfor
                                        <span class="ms-1">{{ $review->rating }}/5</span>
                                    </div>
                                </div>
                                <div class="ms-auto text-muted small">
                                    {{ $review->created_at->format('M d, Y') }}
                                </div>
                            </div>

                            <!-- Full Review Text -->
                            <div class="bg-light p-3 rounded-3" style="max-height: 400px; overflow-y: auto;">
                                <p class="mb-0" style="white-space: pre-wrap; line-height: 1.8;">{{ $review->review_text }}</p>
                            </div>

                            <!-- Provider Info -->
                            <div class="mt-3 pt-3" style="border-top: 1px solid #e2e8f0;">
                                <small class="text-muted">
                                    <i class="fas fa-briefcase me-1"></i>
                                    Provider:
                                    <strong>{{ $review->serviceProvider->user->name ?? 'Not Available' }}</strong>
                                    @if($review->serviceProvider)
                                        <span class="text-muted">(ID: {{ $review->serviceProvider->id }})</span>
                                    @endif
                                </small>
                            </div>
                        </div>
                        <div class="modal-footer" style="border-top: 1px solid #e2e8f0;">
                            <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endsection
