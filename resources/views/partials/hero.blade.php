<!-- Hero Section -->
<section id="hero">
    <div class="hero-container">
        <div class="hero-content">
            <h1 class="hero-title">
                Your World of <span>Service in One Place</span>
            </h1>

            <p class="hero-subtitle">
                Connect with professionals in your area.
                Get quality services at competitive prices, or grow your business
                by connecting with clients who need your expertise.
            </p>

            <div class="hero-buttons">
                <a href="{{ route('service-providers.index') }}" class="hero-btn hero-btn-primary">
                    <i class="fas fa-search me-2"></i> Find a Provider
                </a>
                <a href="{{ route('register', ['type' => 'service-provider']) }}" class="hero-btn hero-btn-outline">
                    <i class="fas fa-user-tie me-2"></i> Join as Provider
                </a>
            </div>
        </div>
    </div>
</section>
