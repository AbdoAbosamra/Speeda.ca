{{-- "Current offers" box on a provider profile. Renders nothing without a live offer. --}}
@if($offers->isNotEmpty())
    @include('partials.offers.styles')
    @include('partials.offers.scripts')
    <section class="so-scope so-profile-offers" id="offers" aria-labelledby="so-profile-offers-title" data-so-impressions="profile">
        <h2 class="so-profile-offers-head m-0" id="so-profile-offers-title">
            <i class="fas fa-tag" aria-hidden="true"></i>
            {{ __('offers.profile_offers_title') }}
            <small>· {{ __('offers.profile_offers_subtitle') }}</small>
        </h2>
        @foreach($offers as $offer)
            <div class="so-profile-offer" data-offer-id="{{ $offer->id }}">
                <div class="so-profile-offer-pct {{ $offer->deal_type === \App\Models\Offer::TYPE_FREE ? 'is-free' : '' }}">{{ $offer->badgeLabel() }}</div>
                <div class="so-profile-offer-text">
                    <b>{{ $offer->localized_title }}</b>
                    <p>
                        {{ $offer->localized_summary }}
                        @if($offer->isEndingSoon())
                            <span class="text-danger fw-semibold">· {{ trans_choice('offers.ends_in', $offer->daysLeft(), ['count' => $offer->daysLeft()]) }}</span>
                        @elseif($offer->ends_at)
                            · {{ __('offers.profile_offer_ends', ['date' => \App\Models\Offer::localDate($offer->ends_at, false)]) }}
                        @endif
                    </p>
                </div>
                <a href="{{ route('offers.show', ['slug' => $offer->slug, 'src' => 'profile']) }}" class="so-btn so-btn-primary">
                    {{ __('offers.view_offer') }} <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
        @endforeach
    </section>
@endif
