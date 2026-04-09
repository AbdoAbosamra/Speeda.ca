<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <h2>{{ __('home.cta_title') }}</h2>
        <p>{{ __('home.cta_description') }}</p>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="{{ route('service-providers.index') }}" class="btn btn-primary btn-lg" aria-label="{{ __('home.find_service') }}">
                <i class="fas fa-search me-2"></i> {{ __('home.find_service') }}
            </a>
            <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg" aria-label="{{ __('home.register_pro') }}">
                <i class="fas fa-user-tie me-2"></i> {{ __('home.register_pro') }}
            </a>
        </div>
    </div>
</section>
