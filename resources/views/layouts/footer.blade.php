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
                    <a href="https://www.tiktok.com/@speeda.ca" class="text-decoration-none">
                        <i class="fab fa-tiktok fa-lg"></i>
                    </a>
                    <a href="https://www.instagram.com/speeda.ca/" class="text-decoration-none">
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

<style>
    /* Footer */
    footer {
        background: linear-gradient(135deg, #212529 0%, #343a40 100%);
        color: #ccc;
        padding: 3rem 0 1.5rem;
    }

    footer h5, footer h6 {
        color: white;
        margin-bottom: 1rem;
    }

    footer a {
        color: #ccc;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    footer a:hover {
        color: #03a9f4;
    }

    .footer-links {
        list-style: none;
        padding: 0;
    }

    .footer-links li {
        margin-bottom: 0.5rem;
    }

    .copyright {
        border-top: 1px solid #444;
        padding-top: 1.5rem;
        margin-top: 2rem;
        text-align: center;
        font-size: 0.9rem;
        color: #999;
    }
</style>
