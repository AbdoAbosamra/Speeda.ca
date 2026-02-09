<!-- Benefits Section -->
<section class="benefits-section">
    <div class="container">
        <h2 class="text-center section-heading">{{ __('home.benefits_title') }}</h2>
        <div class="row g-4">
            <!-- Client Card -->
            <div class="col-lg-6">
                <x-benefit-card type="client" />
            </div>

            <!-- Provider Card -->
            <div class="col-lg-6">
                <x-benefit-card type="provider" />
            </div>
        </div>
    </div>
</section>
