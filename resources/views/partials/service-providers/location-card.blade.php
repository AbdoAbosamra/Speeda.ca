@php
    $mapAddress = trim((string) $serviceProvider->address);
    $mapLocale = in_array(app()->getLocale(), ['ar', 'en', 'fr'], true) ? app()->getLocale() : 'en';
    $mapEmbedUrl = 'https://maps.google.com/maps?' . http_build_query([
        'q' => $mapAddress,
        'z' => 15,
        'hl' => $mapLocale,
        'output' => 'embed',
    ], '', '&', PHP_QUERY_RFC3986);
    $mapDirectionsUrl = 'https://www.google.com/maps/dir/?' . http_build_query([
        'api' => 1,
        'destination' => $mapAddress,
    ], '', '&', PHP_QUERY_RFC3986);
    $mapSearchUrl = 'https://www.google.com/maps/search/?' . http_build_query([
        'api' => 1,
        'query' => $mapAddress,
    ], '', '&', PHP_QUERY_RFC3986);
@endphp

<details class="provider-location-disclosure" data-provider-location>
    <summary class="provider-location-toggle">
        <i class="fas fa-map-location-dot" aria-hidden="true"></i>
        <span>{{ __('service_provider.map_show_details') }}</span>
        <i class="fas fa-chevron-down provider-location-toggle__chevron" aria-hidden="true"></i>
    </summary>
<section class="provider-place" id="provider-location" aria-labelledby="provider-location-title">
    <div class="provider-place__header">
        <div class="provider-place__heading">
            <span class="provider-place__icon" aria-hidden="true"><i class="fas fa-map-location-dot"></i></span>
            <div>
                <p class="provider-place__eyebrow">{{ __('service_provider.map_eyebrow') }}</p>
                <h2 id="provider-location-title">{{ __('service_provider.map_title') }}</h2>
            </div>
        </div>
        @if($serviceProvider->location)
            <span class="provider-place__city">
                <span aria-hidden="true"></span>{{ $serviceProvider->location->localized_name }}
            </span>
        @endif
    </div>

    <div class="provider-place__details">
        <address id="provider-public-address" dir="auto">{{ $mapAddress }}</address>
        <div class="provider-place__actions">
            <a class="provider-place__directions" href="{{ $mapDirectionsUrl }}" target="_blank" rel="noopener noreferrer">
                <i class="fas fa-location-arrow" aria-hidden="true"></i>
                {{ __('service_provider.map_directions') }}
            </a>
            <button type="button" class="provider-place__copy" data-copy-provider-address
                data-copied="{{ __('service_provider.map_address_copied') }}"
                data-copy-fallback="{{ __('service_provider.map_copy_fallback') }}"
                aria-controls="provider-address-fallback" hidden>
                <i class="far fa-copy" aria-hidden="true"></i>
                <span data-copy-label>{{ __('service_provider.map_copy_address') }}</span>
            </button>
        </div>
        <p class="provider-place__status" data-address-copy-status role="status" aria-live="polite"></p>
        <input type="text" id="provider-address-fallback" class="provider-place__fallback"
            value="{{ $mapAddress }}" aria-label="{{ __('general.address') }}" readonly dir="auto" hidden>
    </div>

    <div class="provider-place__map">
        <iframe data-map-src="{{ $mapEmbedUrl }}"
            title="{{ __('service_provider.map_frame_title', ['name' => $serviceProvider->company_name ?: $serviceProvider->user->name]) }}"
            width="640" height="320" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
    </div>
    <div class="provider-place__footer">
        <span><i class="fas fa-map-pin" aria-hidden="true"></i> Google Maps</span>
        <a href="{{ $mapSearchUrl }}" target="_blank" rel="noopener noreferrer">
            {{ __('service_provider.map_larger') }}<i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
        </a>
    </div>
</section>
</details>
