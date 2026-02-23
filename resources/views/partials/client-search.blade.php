<!-- Client Search Section - Clean Version -->
<section id="client-search-section">
    <div class="container">
        <!-- Live Badge -->
        <div class="text-center mb-5">
            <div class="live-badge d-inline-flex align-items-center px-4 py-2 rounded-pill">
                <div class="pulse-dot me-2"></div>
                <span class="fw-semibold text-primary">🔍 Clients Searching Now</span>
            </div>
        </div>

        <!-- Main Heading -->
        <div class="text-center mb-4">
            <h1 class="client-search-heading">
                <span class="d-block">Clients are actively</span>
                <span class="highlighted-text d-block mt-2">
                    searching for services like yours
                    <span class="highlight-underline"></span>
                </span>
            </h1>
        </div>

        <!-- Subheading -->
        <div class="text-center mb-5">
            <p class="client-search-subheading px-3">
                Create your professional profile and be visible where real clients are already looking.
                Start getting matched with clients who need your expertise today.
            </p>
        </div>

        <!-- CTA Button -->
        <div class="text-center mb-5">
            <a href="{{ route('register', ['type' => 'service-provider']) }}" class="client-search-cta">
                <span class="position-relative z-2">
                    <i class="fas fa-user-plus me-2"></i> Create Your Free Profile Now
                </span>
                <span class="btn-hover-effect"></span>
            </a>
            <p class="text-muted small mt-3">No credit card required • Setup in 3 minutes</p>
        </div>

        <!-- Benefits Grid -->
        <div class="row g-4 mt-5">
            <div class="col-md-4">
                <div class="client-search-benefit-card">
                    <div class="client-search-benefit-icon contract">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <h4 class="fw-bold mb-3">No Contracts</h4>
                    <p class="text-muted mb-0">
                        Start and stop anytime. No long-term commitments or hidden fees.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="client-search-benefit-card">
                    <div class="client-search-benefit-icon commission">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <h4 class="fw-bold mb-3">No Commissions</h4>
                    <p class="text-muted mb-0">
                        Keep 100% of what you earn. We don't take a cut from your hard work.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="client-search-benefit-card">
                    <div class="client-search-benefit-icon control">
                        <i class="fas fa-user-cog"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Full Control</h4>
                    <p class="text-muted mb-0">
                        You decide your rates, schedule, and which clients to work with.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>