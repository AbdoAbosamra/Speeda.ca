@extends('layouts.app')

@push('styles')
    @vite('resources/css/app.css')
@endpush

@section('content')
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

        $featuredProviders = collect($topProviders ?? [])->take(8);
    @endphp

    <div class="home-page">
        <section class="hero-wrapper home-section home-hero-section">
            <div class="container">
                <div class="home-shell home-hero-panel">
                    <div class="row align-items-center g-5">
                        <div class="col-xl-6">
                            <div class="fade-in-up">


                                <h1 class="display-4 fw-bold mb-4 home-text-dark hero-title">
                                    {{ __('home.hero_title') }}
                                    <span class="hero-title-highlight">{{ __('home.hero_tagline') }}</span>
                                </h1>

                                <p class="lead home-text-muted mb-4 hero-subtitle">
                                    {{ __('home.hero_subtitle') }}
                                </p>
                            </div>
                            <form id="homeSearchForm" class="premium-search-bar home-search-panel fade-in-up"
                                action="{{ route('service-providers.index') }}" method="GET">
                                <div class="premium-search-input">
                                    <i class="fas fa-map-marker-alt icon-left"></i>
                                    <select name="location" aria-label="{{ __('home.location') }}">
                                        <option value="">{{ __('home.all_locations') }}</option>
                                        @foreach($locationClusters as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <i class="fas fa-chevron-down icon-right"></i>
                                </div>

                                <div class="premium-search-input">
                                    <i class="fas fa-th-large icon-left"></i>
                                    <select name="category" aria-label="{{ __('home.category') }}">
                                        <option value="">{{ __('home.all_categories') }}</option>
                                        @forelse($categories as $category)
                                            <option value="{{ $category->id }}">
                                                {{ $category->translated_name ?? $category->name }}
                                            </option>
                                        @empty
                                            <option disabled>{{ __('home.all_categories') }}</option>
                                        @endforelse
                                    </select>
                                    <i class="fas fa-chevron-down icon-right"></i>
                                </div>

                                <button type="submit" class="premium-search-btn">
                                    <i class="fas fa-search"></i>
                                    {{ __('home.search') }}
                                </button>
                            </form>

                            <div class="d-flex align-items-center gap-3 flex-wrap popular-tags mt-4 fade-in-up">
                                <span class="fw-semibold home-text-muted">{{ __('home.popular') }}</span>
                                <a href="{{ route('service-providers.index', ['search' => __('home.plumbing')]) }}"
                                    class="premium-lang-badge text-decoration-none">
                                    <i class="fas fa-wrench"></i>
                                    {{ __('home.plumbing') }}
                                </a>
                                <a href="{{ route('service-providers.index', ['search' => __('home.electrical')]) }}"
                                    class="premium-lang-badge text-decoration-none">
                                    <i class="fas fa-lightbulb"></i>
                                    {{ __('home.electrical') }}
                                </a>
                                <a href="{{ route('service-providers.index', ['search' => __('home.renovation')]) }}"
                                    class="premium-lang-badge text-decoration-none">
                                    <i class="fas fa-hammer"></i>
                                    {{ __('home.renovation') }}
                                </a>
                            </div>


                        </div>

                        <div class="col-xl-6 mt-5 mt-xl-0">
                            <div class="home-hero-visual fade-in-up" style="position: relative; z-index: 1;">
                                <div class="home-hero-orb home-hero-orb-primary"></div>
                                <div class="home-hero-orb home-hero-orb-secondary"></div>
                                <img src="{{ asset('images/hero-banner.jpeg') }}" alt="{{ __('home.banner_alt') }}"
                                    class="img-fluid home-hero-image hero-banner-custom" loading="eager"
                                    fetchpriority="high">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="home-section">
            <div class="container">
                <div class="home-shell home-section-panel fade-in-up">
                    <div class="home-section-head">
                        <div>
                            <h2 class="section-title mb-2">{{ __('home.explore_services') }}</h2>
                            <p class="section-subtitle mb-0">{{ __('home.explore_services_desc') }}</p>
                        </div>
                        <a href="{{ route('service-providers.index') }}" class="premium-btn-primary home-inline-action">
                            {{ __('home.view_all_categories') }}
                            <i class="fas fa-arrow-right home-inline-arrow"></i>
                        </a>
                    </div>

                    <div class="premium-category-slider-wrapper" id="categorySlider">
                        <button class="slider-nav-btn prev" aria-label="Previous" onclick="document.getElementById('categorySliderTrack').scrollBy({left: -300, behavior: 'smooth'})">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        
                        <div class="premium-category-slider-track" id="categorySliderTrack">
                            @foreach($staticCategories as $subcat)
                                <article class="home-category-card">
                                    <div class="premium-subcat-icon">
                                        <i class="fas fa-{{ $subcat['icon'] }}"></i>
                                    </div>
                                    <h3 class="premium-subcat-title">{{ $subcat['name'] }}</h3>
                                </article>
                            @endforeach
                        </div>

                        <button class="slider-nav-btn next" aria-label="Next" onclick="document.getElementById('categorySliderTrack').scrollBy({left: 300, behavior: 'smooth'})">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        @if($featuredProviders->isNotEmpty())
            <section class="home-section">
                <div class="container">
                    <div class="home-shell home-section-panel fade-in-up">
                        <div class="home-section-head">
                            <div>
                                <h2 class="section-title mb-2">{{ __('home.top_providers_title') }}</h2>
                                <p class="section-subtitle mb-0">{{ __('home.top_providers_desc') }}</p>
                            </div>
                            <a href="{{ route('service-providers.index') }}" class="premium-btn-outline home-inline-action">
                                {{ __('home.find_service_now') }}
                                <i class="fas fa-arrow-right home-inline-arrow"></i>
                            </a>
                        </div>

                        <div class="premium-marquee-wrapper">
                            <div class="premium-marquee-track">
                                @foreach($featuredProviders->concat($featuredProviders) as $provider)
                                    <a href="{{ route('service-providers.show', $provider) }}" class="premium-provider-card-link">
                                        <article class="premium-provider-card home-provider-card">
                                            <div class="premium-provider-img">
                                                @if($provider->media && $provider->media->first())
                                                    <img src="{{ $provider->media->first()->getUrl() }}"
                                                        alt="{{ $provider->user->name ?? __('home.view_profile') }}" loading="lazy"
                                                        class="w-100 h-100 object-fit-cover provider-img">
                                                @else
                                                    <div
                                                        class="w-100 h-100 d-flex align-items-center justify-content-center bg-light text-secondary">
                                                        <i class="fas fa-user-circle fa-4x"></i>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="premium-provider-body">
                                                <div class="home-provider-heading">
                                                    <h3 class="fw-bold mb-1 home-text-dark text-truncate provider-name">
                                                        {{ $provider->user->name ?? __('home.view_profile') }}
                                                    </h3>

                                                </div>

                                                <p class="small home-text-muted mb-3 text-truncate provider-category">
                                                    {{ $provider->category->translated_name ?? $provider->category->name ?? __('home.providers_available') }}
                                                </p>

                                                <div class="d-flex align-items-center gap-2 mb-3">
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
                                                            <span class="text-muted small">0.0</span>
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

                                                @if($provider->location)
                                                    <p class="small home-text-muted mb-0 provider-location home-provider-location">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                        {{ $provider->location->translated_name ?? $provider->location->city ?? __('home.location') }}
                                                    </p>
                                                @endif
                                            </div>
                                        </article>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <section class="home-section home-section-muted">
            <div class="container">
                <div class="row g-4 g-xl-5">
                    <div class="col-lg-6 fade-in-up">
                        <div class="home-shell home-benefit-panel benefits-split-left">
                            <h2 class="benefit-title home-benefit-heading home-benefit-heading-gold">
                                {{ __('home.for_clients') }}
                            </h2>

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

                    <div class="col-lg-6 fade-in-up">
                        <div class="home-shell home-benefit-panel benefits-split-right">
                            <h2 class="benefit-title home-benefit-heading home-benefit-heading-green">
                                {{ __('home.for_providers') }}
                            </h2>

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

        @if(isset($latestBlogPosts) && $latestBlogPosts->isNotEmpty())
            <section class="home-section">
                <div class="container">
                    <div class="home-shell home-section-panel fade-in-up">
                        <div class="home-section-head">
                            <div>
                                <h2 class="section-title mb-2">{{ __('home.latest_blogs_title') }}</h2>
                                <p class="section-subtitle mb-0">{{ __('home.latest_blogs_desc') }}</p>
                            </div>
                            <a href="{{ route('blogs.index') }}" class="premium-btn-outline home-inline-action">
                                {{ __('home.view_all_blogs') }}
                                <i class="fas fa-arrow-right home-inline-arrow"></i>
                            </a>
                        </div>

                        <div class="row g-4 home-blog-grid">
                            @foreach($latestBlogPosts as $post)
                                <div class="col-md-6 col-xl-4">
                                    <a href="{{ route('blogs.show', $post) }}" class="premium-blog-card-link">
                                        <article class="premium-blog-card home-blog-card">
                                            <div class="premium-blog-img">
                                                <img src="{{ $post->image_url }}" alt="{{ $post->localized_featured_image_alt }}"
                                                    class="w-100 h-100 object-fit-cover" loading="lazy">
                                                @if($post->category)
                                                    <div class="premium-blog-category">
                                                        {{ $post->category->translated_name ?? $post->category->name }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="premium-blog-body">
                                                <div class="home-blog-meta">
                                                    <span>{{ $post->published_date }}</span>
                                                    <span class="home-blog-dot"></span>
                                                    <span>
                                                        {{ max(1, (int) ($post->reading_time_minutes ?: ceil(str_word_count(strip_tags($post->localized_content)) / 200))) }}
                                                        {{ __('home.min_read') }}
                                                    </span>
                                                </div>
                                                <h3 class="fw-bold mb-2 home-text-dark home-blog-title">
                                                    {{ $post->localized_title }}
                                                </h3>
                                                <p class="small home-text-muted mb-0 excerpt-text">
                                                    {{ $post->localized_excerpt }}
                                                </p>
                                                <div class="home-blog-link">
                                                    <span class="small text-primary fw-bold">
                                                        {{ __('home.read_more') }}
                                                    </span>
                                                    <i class="fas fa-arrow-right home-inline-arrow"></i>
                                                </div>
                                            </div>
                                        </article>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <section class="home-section">
            <div class="container">
                <div class="premium-cta-banner home-cta-panel fade-in-up">
                    <h2 class="display-5 fw-bold mb-3">{{ __('home.cta_title') }}</h2>
                    <p class="lead mb-4 opacity-75 mx-auto home-cta-copy">{{ __('home.cta_description') }}</p>

                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="{{ route('service-providers.index') }}" class="premium-cta-btn-white">
                            <i class="fas fa-search"></i>
                            {{ __('home.find_service_now') }}
                        </a>
                        <a href="{{ route('register') }}" class="premium-cta-btn-outline">
                            <i class="fas fa-user-plus"></i>
                            {{ __('home.register_as_provider') }}
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection