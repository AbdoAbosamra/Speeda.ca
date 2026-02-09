@extends('layouts.app')

@section('title', __('reviews.edit_review'))

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-6">{{ __('reviews.edit_review') }}</h1>

    @if ($review->is_active)
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <p class="text-yellow-800">{{ __('reviews.cannot_edit_approved_reviews') }}</p>
        </div>
        <a href="{{ url()->previous() }}" class="btn btn-secondary">{{ __('general.back') }}</a>
    @else
        <form method="POST" action="{{ route('reviews.update', $review) }}" class="bg-white rounded-lg shadow p-6">
            @csrf
            @method('PUT')

            <div class="form-group mb-4">
                <label for="rating" class="block font-semibold mb-2">{{ __('reviews.rating') }} *</label>
                <div class="flex gap-2" id="rating-container">
                    @for ($i = 1; $i <= 5; $i++)
                        <input type="radio" id="rating-{{ $i }}" name="rating" value="{{ $i }}" @if(old('rating', $review->rating) == $i) checked @endif required>
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
                >{{ old('review_text', $review->review_text) }}</textarea>
                @error('review_text')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-4">
                <button type="submit" class="btn btn-primary px-6 py-2">{{ __('general.save') }}</button>
                <a href="{{ url()->previous() }}" class="btn btn-secondary px-6 py-2">{{ __('general.cancel') }}</a>
            </div>
        </form>
    @endif
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
