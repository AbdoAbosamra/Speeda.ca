<section class="so-trust" aria-labelledby="so-trust-title">
    <h2 id="so-trust-title" class="h6 fw-bold"><i class="fas fa-circle-check" aria-hidden="true"></i> {{ __('readiness.trust_title') }}</h2>
    <ol class="mb-2 ps-3">
        <li>{{ __('readiness.trust_link') }} <bdi dir="ltr">{{ parse_url(config('app.url'), PHP_URL_HOST) }}/offers/</bdi></li>
        <li>{{ __('readiness.trust_badge') }}
            @if($offer->ends_at)
                <time datetime="{{ $offer->ends_at->toDateString() }}">{{ __('offers.valid_until', ['date' => \App\Models\Offer::localDate($offer->ends_at)]) }}</time>
            @else
                {{ __('offers.no_end_date') }}
            @endif
        </li>
        <li>{{ $cta === \App\Models\Offer::CTA_WHATSAPP ? __('readiness.trust_whatsapp') : __('readiness.trust_profile') }}</li>
    </ol>
    @if($offer->verified_at)
        <p class="fw-semibold mb-0">{{ __('readiness.reviewed_at', ['date' => \App\Models\Offer::localDate($offer->verified_at)]) }}</p>
    @endif
</section>
