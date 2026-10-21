{{--
    One offer card, used on the home carousel, /offers and the offer page.
    $source records where the visitor met the offer (home, offers, similar…).
--}}
@php
    $source = $source ?? 'offers';
    $url = route('offers.show', ['slug' => $offer->slug, 'src' => $source]);
    $daysLeft = $offer->daysLeft();
    $provider = $offer->serviceProvider;
    $badgeClass = match ($offer->deal_type) {
        \App\Models\Offer::TYPE_FREE => 'so-badge--free',
        \App\Models\Offer::TYPE_CUSTOM => 'so-badge--custom',
        default => '',
    };
@endphp
<article class="so-card" data-offer-id="{{ $offer->id }}">
    <a href="{{ $url }}" class="so-card-media" tabindex="-1" aria-hidden="true">
        @include('partials.offers.cover', ['offer' => $offer])
        <span class="so-badge {{ $badgeClass }}">{{ $offer->badgeLabel() }}</span>
        @if($offer->isEndingSoon())
            <span class="so-urgent">{{ trans_choice('offers.ends_in', $daysLeft, ['count' => $daysLeft]) }}</span>
        @endif
    </a>
    <div class="so-card-body">
        <div class="so-card-provider">
            <div class="so-logo">
                @if($provider?->profile_image)
                    <img src="{{ $provider->profile_image_url }}" alt="" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/user.png') }}';">
                @else
                    <span style="background: {{ $offer->fallbackGradient() }};">{{ $offer->providerInitials() }}</span>
                @endif
            </div>
            <div class="so-card-provider-text">
                <strong>{{ $offer->providerName() }}</strong>
                @if($offer->providerCategory())
                    <small>{{ $offer->providerCategory() }}</small>
                @endif
            </div>
        </div>
        <span class="so-tag">{{ __('offers.exclusive_tag') }}</span>
        <h3 class="so-card-title"><a href="{{ $url }}">{{ $offer->localized_title }}</a></h3>
        <p class="so-card-text">{{ $offer->localized_summary }}</p>
        <div class="so-meta">
            {{-- Temporarily disabled: offer like count.
            @if($offer->likes_count > 0)
                <span class="so-meta-likes"><i class="fas fa-heart" aria-hidden="true"></i>{{ $offer->likes_count }}</span>
                <span class="so-sep" aria-hidden="true"></span>
            @endif
            --}}
            @if($offer->providerCity())
                <span><i class="fas fa-location-dot" aria-hidden="true"></i>{{ $offer->providerCity() }}</span>
                <span class="so-sep" aria-hidden="true"></span>
            @endif
            <span>
                <i class="far fa-calendar" aria-hidden="true"></i>
                @if($offer->ends_at)
                    {{ __('offers.valid_until', ['date' => \App\Models\Offer::localDate($offer->ends_at, false)]) }}
                @else
                    {{ __('offers.no_end_date') }}
                @endif
            </span>
        </div>
        <a href="{{ $url }}" class="so-btn so-btn-primary">
            {{ __('offers.view_offer') }} <i class="fas fa-arrow-right" aria-hidden="true"></i>
        </a>
    </div>
</article>
