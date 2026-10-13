@extends('layouts.app')

@section('title', __('offers.my_favorites'))

@include('partials.offers.styles')
@include('partials.offers.scripts')

@section('content')
<div class="container so-scope pb-5">
    <header class="d-flex align-items-center gap-3 flex-wrap mb-4">
        <div class="so-home-icon" aria-hidden="true"><i class="fas fa-bookmark"></i></div>
        <div class="flex-grow-1">
            <h1 class="h3 fw-bold mb-1 text-dark">{{ __('offers.my_favorites') }}</h1>
            <p class="so-fine mb-0">{{ __('offers.my_favorites_subtitle') }}</p>
        </div>
        <a href="{{ route('offers.index') }}" class="so-btn so-btn-ghost">{{ __('offers.browse_offers') }}</a>
    </header>

    @if($offers->isEmpty())
        <div class="so-empty">
            <i class="far fa-bookmark" aria-hidden="true"></i>
            <p class="mb-3">{{ __('offers.no_favorites') }}</p>
            <a href="{{ route('offers.index') }}" class="so-btn so-btn-primary">{{ __('offers.browse_offers') }}</a>
        </div>
    @else
        <div class="so-grid">
            @foreach($offers as $offer)
                <div class="d-grid gap-2">
                    @include('partials.offers.card', ['offer' => $offer, 'source' => 'direct'])
                    <div class="d-flex justify-content-between align-items-center">
                        @if(! $offer->isLive())
                            <span class="so-tag" style="background:#f1f5f9;color:#64748b">{{ __('offers.favorite_ended') }}</span>
                        @else
                            <span></span>
                        @endif
                        <form method="POST" action="{{ route('offers.favorite', $offer->slug) }}" class="m-0">
                            @csrf
                            <button type="submit" class="so-pill"><i class="fas fa-trash-can" aria-hidden="true"></i> {{ __('offers.remove_favorite') }}</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $offers->links() }}</div>
    @endif
</div>
@endsection
