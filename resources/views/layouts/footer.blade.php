<!-- Footer -->
<footer>
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <h5>Speeda</h5>
                <p style="font-size: 13px;">Speeda is a connection-only platform; all agreements and payments occur directly between clients and providers. We do not verify qualifications or guarantee service performance.</p>
            </div>
            <div class="col-lg-2 col-md-6 mb-4">
                <ul class="footer-links">
                    <li><a href="{{ route('about-us') }}">{{ __('home.about_us') }}</a></li>
                    <li><a href="#how-it-works">{{ __('home.how_it_works') }}</a></li>
                    <li><a href="{{ route('help-center') }}">{{ __('home.help_center') }}</a></li>
                    <li><a href="#">{{ __('home.careers') }}</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-6 mb-4">
                <ul class="footer-links">
                    <li><a href="{{ route('legal-affairs') }}">{{ __('home.legal') }}</a></li>
                    <li><a href="{{ route('terms-of-service')}}">{{ __('home.terms_service') }}</a></li>
                    <li><a href="{{ route('privacy-policy')}}">{{ __('home.privacy_policy') }}</a></li>
                </ul>
            </div>
            <div class="col-lg-4 mb-4">
                <h6>{{ __('home.connect_us') }}</h6>
                <div class="d-flex gap-3 mt-3">
                    <a href="https://www.facebook.com/profile.php?id=61583422931690" class="text-decoration-none">
                        <i class="fab fa-facebook-f fa-lg"></i>
                    </a>
                    <a href="#" class="text-decoration-none">
                        <i class="fab fa-tiktok fa-lg"></i>
                    </a>
                    <a href="#" class="text-decoration-none">
                        <i class="fab fa-instagram fa-lg"></i>
                    </a>
                    <a href="#" class="text-decoration-none">
                        <i class="fab fa-linkedin-in fa-lg"></i>
                    </a>

                </div>
                <p style="margin-top:25px"><a href="mailto:support@speeda.ca">support@speeda.ca</a></p>
            </div>
        </div>
        <div class="copyright">
            <p class="mb-2" style="font-size: 13px;">&copy; 2025 Speeda. {{ __('home.all_rights_reserved') }}</p>
            <p class="small text-muted" style="font-size: 11px;">Speeda is a connection-only platform; all agreements and payments occur directly between clients and providers. We do not verify qualifications or guarantee service performance.</p>
            <p class="mb-0 mt-2" style="font-size: 11px; opacity: 0.85;">
                Designed &amp; developed by
                <a href="https://www.linkedin.com/in/abdelrahman-abo-samra/" target="_blank" rel="noopener noreferrer author" style="text-decoration: none; font-weight: 600;">Eng. Abdelrahman Abosamra</a>
            </p>
        </div>
    </div>
</footer>
{{-- Footer styles are in resources/css/app.css --}}
