@extends('layouts.app')

@include('partials.offers.styles')
@include('partials.offers.scripts')

@section('content')
<div class="container so-scope pb-5">
    <header class="so-hero">
        <i class="fas fa-tag so-hero-deco" aria-hidden="true"></i>
        <nav class="so-crumb" aria-label="breadcrumb">
            <a href="{{ route('home') }}">{{ __('offers.breadcrumb_home') }}</a> / {{ __('offers.page_title') }}
        </nav>
        <h1>{{ __('offers.page_title') }}</h1>
        <p>{{ __('offers.hero_subtitle') }}</p>
        @if($speedaWa = \App\Support\WhatsApp::contactSpeedaUrl())
            <div class="so-hero-actions">
                <a class="so-hero-contact" href="{{ $speedaWa }}" target="_blank" rel="noopener">
                    <span class="so-hero-contact-icon" aria-hidden="true"><i class="fab fa-whatsapp"></i></span>
                    <span class="so-hero-contact-text">
                        <b>{{ __('offers.contact_to_publish') }}</b>
                        <small>{{ __('offers.contact_to_publish_hint') }}</small>
                    </span>
                    <i class="fas fa-arrow-right so-hero-contact-arrow" aria-hidden="true"></i>
                </a>
            </div>
        @endif
    </header>

    <div class="d-grid gap-4 mt-4">
        @if($endingSoon->isNotEmpty())
            <section aria-labelledby="so-ending-title">
                <h2 class="so-strip-title" id="so-ending-title">
                    <i class="fas fa-hourglass-half text-danger" aria-hidden="true"></i>
                    {{ __('offers.ending_soon_title') }} <small>{{ __('offers.ending_soon_subtitle') }}</small>
                </h2>
                <div class="so-soon">
                    @foreach($endingSoon as $offer)
                        @php $d = $offer->daysLeft(); @endphp
                        <a class="so-soon-item" href="{{ route('offers.show', ['slug' => $offer->slug, 'src' => 'offers']) }}">
                            <div class="so-soon-thumb">@include('partials.offers.cover', ['offer' => $offer])</div>
                            <div>
                                <b>{{ $offer->localized_title }}</b>
                                <span>{{ $offer->providerName() }} · {{ $offer->badgeLabel() }}</span>
                            </div>
                            <div class="so-clock">{{ $d }}<small>{{ trans_choice('offers.days_left', $d) }}</small></div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <section aria-labelledby="so-all-title">
            <h2 class="so-strip-title" id="so-all-title">
                {{ __('offers.all_offers_title') }}
                <small>{{ trans_choice('offers.results', $offers->total(), ['count' => $offers->total()]) }}</small>
            </h2>

            @if($offers->isEmpty())
                <div class="so-empty">
                    <i class="fas fa-tags" aria-hidden="true"></i>
                    <p class="mb-3">{{ __('offers.no_offers') }}</p>
                    <a href="{{ route('service-providers.index') }}" class="so-btn so-btn-ghost">{{ __('offers.browse_providers') }}</a>
                </div>
            @else
                <div class="so-grid" data-so-impressions="offers">
                    @foreach($offers as $offer)
                        @include('partials.offers.card', ['offer' => $offer, 'source' => 'offers'])
                    @endforeach
                </div>
                @if($offers->hasPages())
                    <div class="mt-4 d-flex justify-content-center">{{ $offers->links() }}</div>
                @endif
            @endif
        </section>
    </div>
</div>
@endsection
