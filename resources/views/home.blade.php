@extends('layouts.app')

@section('content')

    @push('styles')
        @vite('resources/css/app.css')
    @endpush

    <!-- ========== HERO SECTION ========== -->
    <section class="hero-wrapper pt-5 pb-5 mb-5">
        <div class="container hero-content">
            <div class="row align-items-center g-5">
                <!-- Left Side -->
                <div class="col-lg-6 mb-5 mb-lg-0 fade-in-up">
                    <h1 class="display-4 fw-bold mb-4 home-text-dark hero-title">
                        {{ __('home.hero_title') }}
                        <span class="hero-title-highlight">{{ __('home.hero_tagline') }}</span>
                    </h1>
                    <p class="lead home-text-muted mb-5 hero-subtitle">
                        {{ __('home.hero_subtitle') }}
                    </p>

                    <!-- Advanced Search Bar -->
                    <form id="homeSearchForm" class="premium-search-bar mb-4"
                        action="{{ route('service-providers.index') }}" method="GET">
                        <div class="premium-search-input">
                            <i class="fas fa-search icon-left"></i>
                            <input type="text" name="search" placeholder="{{ __('home.search_placeholder') }}"
                                aria-label="Search">
                        </div>

                        <div class="premium-search-input">
                            <i class="fas fa-map-marker-alt icon-left"></i>
                            <select name="location" aria-label="Location">
                                <option value="">{{ __('home.all_locations') }}</option>
                                @foreach($locationClusters as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <i class="fas fa-chevron-down icon-right"></i>
                        </div>

                        <div class="premium-search-input">
                            <i class="fas fa-th-large icon-left"></i>
                            <select name="category" aria-label="Category">
                                <option value="">{{ __('home.all_categories') }}</option>
                                @forelse($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->translated_name ?? $category->name }}
                                    </option>
                                @empty
                                    <option disabled>No categories available</option>
                                @endforelse
                            </select>
                            <i class="fas fa-chevron-down icon-right"></i>
                        </div>

                        <button type="submit" class="premium-search-btn">
                            <i class="fas fa-search"></i>
                            {{ __('home.search') }}
                        </button>
                    </form>

                    <div class="d-flex align-items-center gap-3 flex-wrap popular-tags">
                        <span class="fw-semibold home-text-muted">{{ __('home.popular') }}</span>
                        <a href="{{ route('service-providers.index', ['search' => __('home.plumbing')]) }}"
                            class="premium-lang-badge text-decoration-none">
                            <i class="fas fa-wrench me-1"></i> {{ __('home.plumbing') }}
                        </a>
                        <a href="{{ route('service-providers.index', ['search' => __('home.electrical')]) }}"
                            class="premium-lang-badge text-decoration-none">
                            <i class="fas fa-lightbulb me-1"></i> {{ __('home.electrical') }}
                        </a>
                        <a href="{{ route('service-providers.index', ['search' => __('home.renovation')]) }}"
                            class="premium-lang-badge text-decoration-none">
                            <i class="fas fa-hammer me-1"></i> {{ __('home.renovation') }}
                        </a>
                    </div>
                </div>

                <!-- Right Side (Visual Banner) -->
                <div class="col-lg-6 d-none d-lg-block fade-in-up" style="animation-delay: 0.2s;">
                    <div class="hero-visual-banner text-center">
                        <img src="{{ asset('images/New Banner.jpeg') }}" alt="Speeda Banner"
                            class="img-fluid rounded-4 shadow-lg hero-banner-img"
                            style="max-height: 500px; object-fit: contain;">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== CATEGORIES SECTION ========== -->
    @php
        $staticCategories = [
            ['name' => __('categories.plumbing'), 'icon' => 'faucet'],
            ['name' => __('categories.real_estate_agents'), 'icon' => 'home'],
            ['name' => __('categories.hvac_services'), 'icon' => 'wind'],
            ['name' => __('categories.electrical_services'), 'icon' => 'bolt'],
            ['name' => __('categories.appliance_repair'), 'icon' => 'tools'],
            ['name' => __('categories.insurance_brokers'), 'icon' => 'file-contract'],
            ['name' => __('categories.driving_lessons_schools'), 'icon' => 'car'],
            ['name' => __('categories.car_mechanics'), 'icon' => 'wrench'],
            ['name' => __('categories.moving_services'), 'icon' => 'truck-moving'],
            ['name' => __('categories.roadside_assistance'), 'icon' => 'car-battery'],
            ['name' => __('categories.handyman_services'), 'icon' => 'hammer'],
            ['name' => __('categories.landscaping_gardening'), 'icon' => 'leaf'],
            ['name' => __('categories.auto_body_repair'), 'icon' => 'car-side'],
            ['name' => __('categories.tire_balancing_wheel_alignment'), 'icon' => 'compact-disc'],
            ['name' => __('categories.car_dealers'), 'icon' => 'car-rear'],
            ['name' => __('categories.general_construction'), 'icon' => 'building'],
        ];
    @endphp

    <section class="home-bg-light py-5 mb-5">
        <div class="container">
            <div class="text-center mb-4 fade-in-up">
                <h2 class="fw-bold home-text-dark section-title">{{ __('home.explore_services') }}</h2>
                <p class="home-text-muted section-subtitle">{{ __('home.explore_services_desc') }}</p>
            </div>

            <div class="premium-subcategory-wrapper fade-in-up" style="animation-delay: 0.1s;">
                @foreach($staticCategories as $subcat)
                    <div class="premium-subcat-card no-click">
                        <div class="premium-subcat-icon">
                            <i class="fas fa-{{ $subcat['icon'] }}"></i>
                        </div>
                        <h6 class="premium-subcat-title">{{ $subcat['name'] }}</h6>
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-5 fade-in-up">
                <a href="{{ route('service-providers.index') }}" class="premium-btn-primary px-5">
                    {{ __('home.view_all_categories') }}
                </a>
            </div>
        </div>
    </section>

    <style>
        .premium-subcat-card.no-click {
            cursor: default !important;
            pointer-events: none;
            transition: none !important;
        }
        .premium-subcat-card.no-click:hover {
            transform: none !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05) !important;
            border-color: var(--border-light) !important;
        }
    </style>

    <!-- ========== TOP PROVIDERS SECTION ========== -->
    @if($topProviders && $topProviders->count() > 0)
        <section class="container py-5 mb-5">
            <div class="text-center mb-5 fade-in-up">
                <h2 class="fw-bold home-text-dark section-title">{{ __('home.top_providers_title') }}</h2>
                <p class="home-text-muted section-subtitle">{{ __('home.top_providers_desc') }}</p>
            </div>

            <div class="premium-marquee-wrapper fade-in-up" style="animation-delay: 0.2s;">
                <div class="premium-marquee-track">
                    @for ($i = 0; $i < 2; $i++)
                        @foreach($topProviders as $provider)
                            <a href="{{ route('service-providers.show', $provider) }}" class="premium-provider-card-link" {{ $i == 1 ? 'aria-hidden="true"' : '' }}>
                                <div class="premium-provider-card">
                                    <div class="premium-provider-img">
                                        @if($provider->media && $provider->media->first())
                                            <img src="{{ $provider->media->first()->getUrl() }}"
                                                alt="{{ $provider->user->name ?? 'Provider' }}" loading="lazy"
                                                class="w-100 h-100 object-fit-cover provider-img">
                                        @else
                                            <div
                                                class="w-100 h-100 d-flex align-items-center justify-content-center bg-light text-secondary">
                                                <i class="fas fa-user-circle fa-4x"></i>
                                            </div>
                                        @endif


                                    </div>

                                    <div class="premium-provider-body">
                                        <h5 class="fw-bold mb-1 home-text-dark text-truncate provider-name">
                                            {{ $provider->user->name ?? 'Provider' }}</h5>
                                        <p class="small home-text-muted mb-2 text-truncate provider-category">
                                            {{ $provider->category->translated_name ?? $provider->category->name ?? 'Service Provider' }}
                                        </p>

                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <div class="premium-stars">
                                                @if($provider->live_rating || $provider->rating)
                                                    @php $rating = $provider->live_rating ?? $provider->rating; @endphp
                                                    @for($j = 0; $j < floor($rating); $j++)
                                                        <i class="fas fa-star"></i>
                                                    @endfor
                                                    @if(($rating - floor($rating)) > 0)
                                                        <i class="fas fa-star-half-alt"></i>
                                                    @endif
                                                    <span class="text-dark fw-bold ms-1">{{ round($rating, 1) }}</span>
                                                @else
                                                    <span class="text-muted small">No rating</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="premium-provider-stats">
                                            <div>
                                                <span class="val">{{ $provider->reviews_count ?? 0 }}</span>
                                                <span class="lbl">{{ __('home.reviews') }}</span>
                                            </div>
                                            <div>
                                                <span class="val">{{ $provider->endorsements_count ?? 0 }}</span>
                                                <span class="lbl">{{ __('home.recommendations') }}</span>
                                            </div>
                                        </div>

                                        @if(!empty($provider->languages))
                                            <div class="d-flex flex-wrap gap-1 mb-3">
                                                @php
                                                    $languages = is_array($provider->languages) ? $provider->languages : explode(',', $provider->languages);
                                                @endphp
                                                @foreach($languages as $lang)
                                                    <span class="premium-lang-badge">{{ trim($lang) }}</span>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if($provider->location)
                                            <p class="small home-text-muted mb-0 mt-auto provider-location">
                                                <i class="fas fa-map-marker-alt text-primary me-1"></i>
                                                {{ $provider->location->translated_name ?? $provider->location->city ?? 'Location' }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    @endfor
                </div>
            </div>
        </section>
    @endif

    <!-- ========== BENEFITS SECTION ========== -->
    <section class="home-bg-light py-5">
        <div class="container">
            <div class="row g-5">
                <!-- For Clients -->
                <div class="col-lg-6 fade-in-up" style="animation-delay: 0.1s;">
                    <div class="benefits-split-left">
                        <h3 class="fw-bold mb-4 benefit-title" style="color: var(--speeda-gold);">
                            {{ __('home.for_clients') }}</h3>

                        <div class="benefit-item">
                            <div class="benefit-icon gold"><i class="fas fa-search"></i></div>
                            <div>
                                <strong class="d-block home-text-dark">{{ __('home.client_benefit1_title') }}</strong>
                                <span class="home-text-muted">{{ __('home.client_benefit1_desc') }}</span>
                            </div>
                        </div>
                        <div class="benefit-item">
                            <div class="benefit-icon gold"><i class="fas fa-comments"></i></div>
                            <div>
                                <strong class="d-block home-text-dark">{{ __('home.client_benefit2_title') }}</strong>
                                <span class="home-text-muted">{{ __('home.client_benefit2_desc') }}</span>
                            </div>
                        </div>
                        <div class="benefit-item">
                            <div class="benefit-icon gold"><i class="fas fa-shield-alt"></i></div>
                            <div>
                                <strong class="d-block home-text-dark">{{ __('home.client_benefit3_title') }}</strong>
                                <span class="home-text-muted">{{ __('home.client_benefit3_desc') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- For Providers -->
                <div class="col-lg-6 fade-in-up" style="animation-delay: 0.2s;">
                    <div class="benefits-split-right">
                        <h3 class="fw-bold mb-4 benefit-title" style="color: var(--speeda-green);">
                            {{ __('home.for_providers') }}</h3>

                        <div class="benefit-item">
                            <div class="benefit-icon green"><i class="fas fa-briefcase"></i></div>
                            <div>
                                <strong class="d-block home-text-dark">{{ __('home.provider_benefit1_title') }}</strong>
                                <span class="home-text-muted">{{ __('home.provider_benefit1_desc') }}</span>
                            </div>
                        </div>
                        <div class="benefit-item">
                            <div class="benefit-icon green"><i class="fas fa-users"></i></div>
                            <div>
                                <strong class="d-block home-text-dark">{{ __('home.provider_benefit2_title') }}</strong>
                                <span class="home-text-muted">{{ __('home.provider_benefit2_desc') }}</span>
                            </div>
                        </div>
                        <div class="benefit-item">
                            <div class="benefit-icon green"><i class="fas fa-chart-line"></i></div>
                            <div>
                                <strong class="d-block home-text-dark">{{ __('home.provider_benefit5_title') }}</strong>
                                <span class="home-text-muted">{{ __('home.provider_benefit5_desc') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== BLOGS SECTION ========== -->
    @if(isset($latestPosts) && $latestPosts->count() > 0)
    <section class="container py-5 mb-5">
        <div class="text-center mb-5 fade-in-up">
            <h2 class="fw-bold home-text-dark section-title">{{ __('home.latest_blogs_title') ?? 'Latest Blogs & Insights' }}</h2>
            <p class="home-text-muted section-subtitle">{{ __('home.latest_blogs_desc') ?? 'Expert tips and guides for your home services' }}</p>
        </div>

        <div class="row g-4 mb-5">
            @foreach($latestPosts as $post)
            <div class="col-md-6 col-lg-4 fade-in-up" style="animation-delay: {{ $loop->index * 0.1 }}s;">
                <a href="{{ route('blogs.show', $post) }}" class="premium-blog-card-link">
                    <div class="premium-blog-card">
                        <div class="premium-blog-img">
                            <img src="{{ $post->image_url }}" alt="{{ $post->localized_featured_image_alt }}" class="w-100 h-100 object-fit-cover">
                            @if($post->category)
                            <div class="premium-blog-category">
                                {{ $post->category->translated_name ?? $post->category->name }}
                            </div>
                            @endif
                        </div>
                        <div class="premium-blog-body">
                            <h5 class="fw-bold mb-2 home-text-dark text-truncate">{{ $post->localized_title }}</h5>
                            <p class="small home-text-muted mb-0 excerpt-text">{{ $post->localized_excerpt }}</p>
                            <div class="mt-3 d-flex align-items-center justify-content-between">
                                <span class="small text-primary fw-bold">{{ __('home.read_more') }} <i class="fas fa-arrow-right ms-1"></i></span>
                                <span class="small text-muted">{{ $post->published_date }}</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>

        <div class="text-center fade-in-up">
            <a href="{{ route('blogs.index') }}" class="premium-btn-outline px-5">
                {{ __('home.view_all_blogs') }}
            </a>
        </div>
    </section>
    @endif

    <!-- ========== CTA SECTION ========== -->
    <section class="container py-5 my-5">
        <div class="premium-cta-banner fade-in-up">
            <h2 class="display-5 fw-bold mb-3">{{ __('home.cta_title') }}</h2>
            <p class="lead mb-4 opacity-75 mx-auto" style="max-width: 600px;">{{ __('home.cta_description') }}</p>

            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="{{ route('service-providers.index') }}" class="premium-cta-btn-white">
                    <i class="fas fa-search"></i> {{ __('home.find_service_now') }}
                </a>
                <a href="{{ route('register') }}" class="premium-cta-btn-outline">
                    <i class="fas fa-user-plus"></i> {{ __('home.register_as_provider') }}
                </a>
            </div>
        </div>
    </section>

@endsection
