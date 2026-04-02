@extends('layouts.app')

@section('title', __('reviews.add_review'))

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="text-3xl font-bold mb-1">{{ __('reviews.add_review') }}</h1>
            <p class="text-muted mb-0">
                {{ $provider->company_name ?? $provider->user->name }}
            </p>
        </div>
        <a href="{{ route('service-providers.show', $provider->id) }}" class="btn btn-outline-secondary rounded-pill">
            <i class="fas fa-arrow-left me-2"></i>{{ __('general.back') ?? 'Back' }}
        </a>
    </div>

    <form method="POST" action="{{ route('reviews.store') }}" class="bg-white rounded-lg shadow p-6">
        @csrf

        <div class="form-group mb-4">
            <input type="hidden" name="service_provider_id" value="{{ $provider->id }}">
            <label for="rating" class="block font-semibold mb-2">{{ __('reviews.rating') }} *</label>
            <div class="flex gap-2" id="rating-container">
                @for ($i = 1; $i <= 5; $i++)
                    <input type="radio" id="rating-{{ $i }}" name="rating" value="{{ $i }}" @if(old('rating') == $i) checked @endif required>
                    <label for="rating-{{ $i }}" class="cursor-pointer text-2xl">
                        <i class="fas fa-star text-yellow-400"></i>
                    </label>
                @endfor
            </div>
            @error('rating')
                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group mb-4">
            <label for="review_text" class="block font-semibold mb-2">{{ __('reviews.review_text') }} *</label>
            <textarea
                id="review_text"
                name="review_text"
                class="form-control w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('review_text') border-red-500 @enderror"
                rows="6"
                placeholder="{{ __('reviews.review_placeholder') }}"
                required
                minlength="10"
                maxlength="1000"
            >{{ old('review_text') }}</textarea>
            @error('review_text')
                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <input type="hidden" name="booking_id" value="{{ request('booking_id') }}">

        <div class="flex gap-4">
            <button type="submit" class="btn btn-primary px-6 py-2">{{ __('reviews.submit_review') }}</button>
            <a href="{{ route('service-providers.show', $provider->id) }}" class="btn btn-secondary px-6 py-2">
                {{ __('general.cancel') ?? 'Cancel' }}
            </a>
        </div>
    </form>
</div>

<style>
    #rating-container input[type="radio"] {
        display: none;
    }

    #rating-container label {
        opacity: 0.3;
        transition: opacity 0.2s;
    }

    #rating-container input[type="radio"]:checked ~ label,
    #rating-container label:hover {
        opacity: 1;
    }
</style>

<script>
    document.querySelectorAll('#rating-container label').forEach(label => {
        label.addEventListener('mouseover', function() {
            document.querySelectorAll('#rating-container label').forEach(l => l.style.opacity = '0.3');
            this.style.opacity = '1';
        });
    });

    document.getElementById('rating-container').addEventListener('mouseout', function() {
        document.querySelectorAll('#rating-container input[type="radio"]:checked').forEach(input => {
            document.querySelectorAll('#rating-container label').forEach(l => l.style.opacity = '0.3');
            document.querySelector(`label[for="${input.id}"]`).style.opacity = '1';
        });
    });
</script>
@endsection
