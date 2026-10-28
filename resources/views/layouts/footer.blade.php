<!-- Footer -->
<footer class="site-footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 mb-4">
                <a href="{{ route('home') }}" class="d-inline-block mb-3" aria-label="Speeda home">
                    <img src="{{ asset('images/speeda-logo-128.webp') }}" alt="Speeda" style="height: 48px; width: auto;" loading="lazy">
                </a>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <h6>{{ __('general.company') }}</h6>
                <ul class="footer-links">
                    <li><a href="{{ route('about-us') }}">{{ __('home.about_us') }}</a></li>
                    <li><a href="{{ route('service-providers.index') }}">{{ __('service_provider.browse_providers') }}</a></li>
                    <li><a href="{{ route('blogs.index') }}">{{ __('blog.blog') }}</a></li>
                    <li><a href="{{ route('help-center') }}">{{ __('home.help_center') }}</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <h6>{{ __('home.legal') }}</h6>
                <ul class="footer-links">
                    <li><a href="{{ route('legal-affairs') }}">{{ __('home.legal') }}</a></li>
                    <li><a href="{{ route('terms-of-service')}}">{{ __('home.terms_service') }}</a></li>
                    <li><a href="{{ route('privacy-policy')}}">{{ __('home.privacy_policy') }}</a></li>
                </ul>
            </div>
            <div class="col-lg-3 mb-4">
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
    </div>
</footer>
{{-- Footer styles are in resources/css/app.css --}}
