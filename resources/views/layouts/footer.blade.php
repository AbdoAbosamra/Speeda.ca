<!-- Footer -->
<footer>
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <h5>Speeda</h5>
                <p>{{ __('home.disclaimer') }}</p>
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
            <p class="mb-2">&copy; 2025 Speeda. {{ __('home.all_rights_reserved') }}</p>
            <p class="small text-muted">{{ __('home.disclaimer') }}</p>
        </div>
    </div>
</footer>
{{-- Footer styles are in resources/css/app.css --}}
