@extends('layouts.app')

@section('title', __('reviews.view_reviews'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">{{ __('reviews.view_reviews') }}</h1>

    @if ($reviews->count() > 0)
        <div class="space-y-4">
            @foreach ($reviews as $review)
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="font-semibold text-gray-800">{{ $review->client->name ?? __('reviews.anonymous_user') }}</p>
                            <p class="text-sm text-gray-500">{{ $review->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex text-yellow-400">
                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= $review->rating)
                                        <i class="fas fa-star"></i>
                                    @else
                                        <i class="far fa-star"></i>
                                    @endif
                                @endfor
                            </div>
                            <span class="text-lg font-bold text-gray-800">{{ $review->rating }}/5</span>
                        </div>
                    </div>

                    <p class="text-gray-700 mb-4">{{ $review->review_text }}</p>

                    @if (Auth::check() && (Auth::user()->id === $review->client_id || Auth::user()->isAdmin()))
                        <div class="flex gap-4">
                            @if (Auth::user()->id === $review->client_id && !$review->is_active)
                                <a href="{{ route('reviews.edit', $review) }}" class="text-blue-500 hover:text-blue-700">
                                    <i class="fas fa-edit"></i> {{ __('general.edit') }}
                                </a>
                            @endif
                            <form method="POST" action="{{ route('reviews.destroy', $review) }}" class="inline" onsubmit="return confirm('{{ __('reviews.delete_review') }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700">
                                    <i class="fas fa-trash"></i> {{ __('general.delete') }}
                                </button>
                            </form>
                        </div>
                    @endif

                    @if (!$review->is_active)
                        <p class="text-yellow-600 text-sm mt-2"><i class="fas fa-clock"></i> {{ __('reviews.pending_approval') }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        {{ $reviews->links() }}
    @else
        <p class="text-center text-gray-500">{{ __('reviews.no_reviews') }}</p>
    @endif

    @auth
        <div class="mt-8">
            <a href="{{ route('reviews.create', ['provider' => $provider->id]) }}" class="btn btn-primary">
                {{ __('reviews.add_review') }}
            </a>
        </div>
    @else
        <div class="mt-8 text-center">
            <p class="mb-4">{{ __('reviews.must_login_to_review') }}</p>
            <a href="{{ route('login') }}" class="btn btn-primary">{{ __('auth.login') }}</a>
        </div>
    @endauth
</div>
@endsection
