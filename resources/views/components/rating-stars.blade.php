{{--
    Star Rating Component
    
    Usage:
    @include('components.rating-stars', [
        'providerId' => $serviceProvider->id,
        'currentRating' => $serviceProvider->rating,
        'interactive' => true,  // Allow clicking to rate
        'size' => 'lg',         // sm, md, lg
    ])
--}}

@props([
    'providerId' => null,
    'currentRating' => 0,
    'interactive' => false,
    'size' => 'md',
    'userRating' => null,
])

@php
    $star_sizes = [
        'sm' => 'font-size: 0.875rem;',
        'md' => 'font-size: 1.25rem;',
        'lg' => 'font-size: 1.5rem;',
    ];
    $starStyle = $star_sizes[$size] ?? $star_sizes['md'];
    $uniqueId = 'rating-' . ($providerId ?? uniqid());
@endphp

<div class="rating-stars-wrapper" id="{{ $uniqueId }}" data-provider-id="{{ $providerId }}">
    {{-- Display Stars --}}
    <div class="rating-display d-inline-flex align-items-center gap-1">
        @for($i = 1; $i <= 5; $i++)
            @if($interactive && Auth::check())
                <i class="rating-star {{ $i <= ($userRating ?? 0) ? 'fas text-warning' : ($i <= round($currentRating ?? 0) ? 'fas text-warning opacity-50' : 'far text-muted') }} fa-star"
                   data-rating="{{ $i }}"
                   style="{{ $starStyle }} cursor: pointer; transition: all 0.2s;"
                   title="{{ __('ratings.click_to_rate') }} {{ $i }} {{ __('ratings.stars') }}"
                   role="button"></i>
            @else
                <i class="{{ $i <= round($currentRating ?? 0) ? 'fas text-warning' : 'far text-muted' }} fa-star"
                   style="{{ $starStyle }}"></i>
            @endif
        @endfor
        
        @if($currentRating)
            <span class="ms-2 text-muted small fw-semibold">
                {{ number_format($currentRating, 1) }}
            </span>
        @endif
    </div>
    
    {{-- Interactive Rating Form --}}
    @if($interactive && Auth::check() && $providerId)
        <form id="{{ $uniqueId }}-form" action="{{ route('ratings.store', $providerId) }}" method="POST" class="d-none">
            @csrf
            <input type="hidden" name="rating" id="{{ $uniqueId }}-input" value="{{ $userRating ?? 0 }}">
        </form>
        
        <script>
            (function() {
                const wrapper = document.getElementById('{{ $uniqueId }}');
                const form = document.getElementById('{{ $uniqueId }}-form');
                const input = document.getElementById('{{ $uniqueId }}-input');
                const stars = wrapper.querySelectorAll('.rating-star');
                
                stars.forEach(star => {
                    star.addEventListener('mouseenter', function() {
                        const rating = parseInt(this.dataset.rating);
                        stars.forEach((s, i) => {
                            if (i < rating) {
                                s.classList.remove('far', 'opacity-50');
                                s.classList.add('fas', 'text-warning');
                            } else {
                                s.classList.remove('fas');
                                s.classList.add('far', 'text-muted');
                            }
                        });
                    });
                    
                    star.addEventListener('click', function() {
                        const rating = parseInt(this.dataset.rating);
                        input.value = rating;
                        
                        // Submit via AJAX
                        fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ rating: rating })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Update current user rating display
                                stars.forEach((s, i) => {
                                    s.classList.remove('far', 'fas', 'opacity-50');
                                    if (i < rating) {
                                        s.classList.add('fas', 'text-warning');
                                    } else {
                                        s.classList.add('far', 'text-muted');
                                    }
                                });
                                
                                // Show success message
                                if (typeof toastr !== 'undefined') {
                                    toastr.success(data.message || '{{ __('ratings.submitted_successfully') }}');
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Rating error:', error);
                            if (typeof toastr !== 'undefined') {
                                toastr.error('{{ __('ratings.submission_failed') }}');
                            }
                        });
                    });
                });
                
                // Reset on mouse leave
                wrapper.querySelector('.rating-display').addEventListener('mouseleave', function() {
                    const userRating = parseInt(input.value) || 0;
                    const avgRating = {{ $currentRating ?? 0 }};
                    
                    stars.forEach((s, i) => {
                        s.classList.remove('far', 'fas', 'opacity-50');
                        if (i < userRating) {
                            s.classList.add('fas', 'text-warning');
                        } else if (i < Math.round(avgRating)) {
                            s.classList.add('fas', 'text-warning', 'opacity-50');
                        } else {
                            s.classList.add('far', 'text-muted');
                        }
                    });
                });
            })();
        </script>
    @endif
</div>
