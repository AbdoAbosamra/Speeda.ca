{{-- Offer cover: the admin's image, or the category icon on a stable gradient. --}}
@if($offer->image_url)
    <img src="{{ $offer->image_url }}" alt="{{ $offer->localized_title }}" loading="{{ $eager ?? false ? 'eager' : 'lazy' }}" decoding="async" onerror="this.onerror=null;this.src='{{ asset('images/default-provider.jpg') }}';">
@else
    <div class="so-cover-fallback" style="background: {{ $offer->fallbackGradient() }};" aria-hidden="true">
        <i class="{{ $offer->fallbackIcon() }}"></i>
    </div>
@endif
