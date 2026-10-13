{{--
    Home page "Local Offers" carousel. Only included when the admin has the
    section switched on AND at least one featured offer is live, so the whole
    block is absent otherwise.
--}}
@include('partials.offers.styles')
@include('partials.offers.scripts')

<section class="home-section so-scope" id="local-offers" aria-labelledby="local-offers-title">
    <div class="container">
        <div class="home-shell home-section-panel fade-in-up">
            <div class="so-home-head">
                <div class="so-home-icon" aria-hidden="true"><i class="fas fa-tag"></i></div>
                <div class="so-home-title">
                    <h2 class="section-title mb-1" id="local-offers-title">
                        {{ $offerSection->displayTitle() }}
                        <span class="so-live">{{ trans_choice('offers.live_count', $offerLiveCount, ['count' => $offerLiveCount]) }}</span>
                    </h2>
                    <p class="section-subtitle mb-0">{{ $offerSection->displaySubtitle() }}</p>
                </div>
                <a href="{{ route('offers.index') }}" class="premium-btn-outline home-inline-action">
                    {{ __('offers.view_all') }}
                    <i class="fas fa-arrow-right home-inline-arrow"></i>
                </a>
            </div>

            <div class="so-carousel" data-so-carousel>
                <button type="button" class="so-arrow so-arrow-prev" aria-label="{{ __('offers.previous') }}">
                    <i class="fas fa-chevron-left" aria-hidden="true"></i>
                </button>
                <div class="so-track" data-so-impressions="home">
                    @foreach($homeOffers as $offer)
                        @include('partials.offers.card', ['offer' => $offer, 'source' => 'home'])
                    @endforeach
                </div>
                <button type="button" class="so-arrow so-arrow-next" aria-label="{{ __('offers.next') }}">
                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                </button>
            </div>
            <div class="so-pager" data-label="{{ __('offers.go_to_page', ['page' => ':page']) }}"></div>
        </div>
    </div>
</section>
