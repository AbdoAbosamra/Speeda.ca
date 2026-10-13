<!-- Footer -->
<footer>
    <div class="container">
        <div class="row">
            <div class="col-lg-3 mb-4">
                <a href="{{ route('home') }}" class="d-inline-block mb-3" aria-label="Speeda home">
                    <img src="{{ asset('images/speeda-logo-128.webp') }}" alt="Speeda" style="height: 48px; width: auto;" loading="lazy">
                </a>
                <h5>Speeda</h5>
                <p style="font-size: 13px;">{{ __('readiness.platform_disclaimer') }}</p>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <h6>{{ __('categories.popular_categories') }}</h6>
                <ul class="footer-links">
                    <li><a href="{{ route('service-providers.index', ['category' => 'automotive-services']) }}">{{ __('categories.automotive_services') }}</a></li>
                    <li><a href="{{ route('service-providers.index', ['category' => 'car-mechanics']) }}">{{ __('categories.car_mechanics') }}</a></li>
                    <li><a href="{{ route('service-providers.index', ['category' => 'restaurants-cafe']) }}">{{ __('categories.restaurants_cafe') }}</a></li>
                    <li><a href="{{ route('service-providers.index', ['category' => 'marketing-web-services']) }}">{{ __('categories.marketing_web_services') }}</a></li>
                    <li><a href="{{ route('service-providers.index', ['category' => 'home-property-services']) }}">{{ __('categories.home_property_services') }}</a></li>
                    <li><a href="{{ route('categories') }}" class="text-primary fw-semibold">{{ __('general.view_all') }} &rarr;</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-6 mb-4">
                <h6>{{ __('general.company') }}</h6>
                <ul class="footer-links">
                    <li><a href="{{ route('about-us') }}">{{ __('home.about_us') }}</a></li>
                    <li><a href="{{ route('service-providers.index') }}">{{ __('service_provider.browse_providers') }}</a></li>
                    <li><a href="{{ route('blogs.index') }}">{{ __('blog.blog') }}</a></li>
                    <li><a href="{{ route('help-center') }}">{{ __('home.help_center') }}</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-6 mb-4">
                <h6>{{ __('home.legal') }}</h6>
                <ul class="footer-links">
                    <li><a href="{{ route('legal-affairs') }}">{{ __('home.legal') }}</a></li>
                    <li><a href="{{ route('terms-of-service')}}">{{ __('home.terms_service') }}</a></li>
                    <li><a href="{{ route('privacy-policy')}}">{{ __('home.privacy_policy') }}</a></li>
                </ul>
            </div>
            <div class="col-lg-2 mb-4">
                <h6>{{ __('home.connect_us') }}</h6>
                <div class="d-flex gap-3 mt-3">
                    <a href="https://www.facebook.com/profile.php?id=61583422931690" target="_blank" rel="noopener noreferrer" aria-label="Facebook" class="text-decoration-none">
                        <i class="fab fa-facebook-f fa-lg"></i>
                    </a>
                    <a href="https://www.tiktok.com/@speeda.ca?lang=en" target="_blank" rel="noopener noreferrer" aria-label="TikTok" class="text-decoration-none">
                        <i class="fab fa-tiktok fa-lg"></i>
                    </a>
                    <a href="https://www.instagram.com/speeda.ca/" target="_blank" rel="noopener noreferrer" aria-label="Instagram" class="text-decoration-none">
                        <i class="fab fa-instagram fa-lg"></i>
                    </a>
                    <a href="https://www.linkedin.com/company/110736471/" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn" class="text-decoration-none">
                        <i class="fab fa-linkedin-in fa-lg"></i>
                    </a>

                </div>
                <p style="margin-top:25px"><a href="mailto:support@speeda.ca">support@speeda.ca</a></p>
            </div>
        </div>
        <div class="copyright">
            <p class="mb-2" style="font-size: 13px;">&copy; {{ date('Y') }} Speeda. {{ __('home.all_rights_reserved') }}</p>
            <p class="small text-muted" style="font-size: 11px;">{{ __('readiness.platform_disclaimer') }}</p>
            <p class="mb-0 mt-2" style="font-size: 11px; opacity: 0.85;">
                Designed &amp; developed by
                <a href="https://www.linkedin.com/in/abdelrahman-abo-samra/" target="_blank" rel="noopener noreferrer author" style="text-decoration: none; font-weight: 600;">Eng. Abdelrahman Abosamra</a>
            </p>
        </div>
    </div>
</footer>
{{-- Footer styles are in resources/css/app.css --}}
