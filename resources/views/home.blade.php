@extends('layouts.app')

@push('styles')
    @vite('resources/css/app.css')
    <style>
        .hero-subtitle-badge {
            display: inline-flex !important;
            align-items: center !important;
            padding: 0.6rem 1.4rem !important;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
            color: #ffffff !important;
            border-radius: 50px !important;
            font-weight: 700 !important;
            font-size: 0.95rem !important;
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.3) !important;
            margin-bottom: 2rem !important;
            letter-spacing: 0.01em !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        /* Toronto Expansion Card Styles */
        .expansion-banner-card {
            background: linear-gradient(135deg, #ffffff 0%, #f4f7ff 100%) !important;
            border: 1.5px solid rgba(37, 99, 235, 0.18) !important;
            border-radius: 24px !important;
            padding: 1.6rem 2.2rem !important;
            position: relative !important;
            overflow: hidden !important;
            box-shadow: 0 15px 35px -10px rgba(37, 99, 235, 0.08), 0 0 0 1px rgba(37, 99, 235, 0.02) !important;
            margin: 1.5rem 0 2.5rem 0 !important;
            display: flex !important;
            align-items: center !important;
            gap: 1.8rem !important;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        .expansion-banner-card:hover {
            transform: translateY(-4px) !important;
            border-color: rgba(37, 99, 235, 0.35) !important;
            box-shadow: 0 25px 50px -12px rgba(37, 99, 235, 0.16), 0 0 25px rgba(37, 99, 235, 0.06) !important;
        }
        .expansion-banner-icon-container {
            width: 76px !important;
            height: 76px !important;
            border-radius: 50% !important;
            background: #ffffff !important;
            border: 1.5px solid rgba(37, 99, 235, 0.12) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.15) !important;
            flex-shrink: 0 !important;
            position: relative !important;
            z-index: 2 !important;
        }
        .expansion-banner-icon-container::before {
            content: "" !important;
            position: absolute !important;
            top: -5px !important;
            left: -5px !important;
            right: -5px !important;
            bottom: -5px !important;
            border: 2px solid rgba(37, 99, 235, 0.3) !important;
            border-radius: 50% !important;
            animation: pulse-ring 2s cubic-bezier(0.215, 0.610, 0.355, 1) infinite !important;
            pointer-events: none !important;
        }
        @keyframes pulse-ring {
            0% { transform: scale(0.95); opacity: 1; }
            100% { transform: scale(1.2); opacity: 0; }
        }
        @keyframes float-rocket {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-5px) rotate(3deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        .expansion-banner-icon-container i {
            font-size: 2.4rem !important;
            color: #2563eb !important;
            filter: drop-shadow(0 4px 8px rgba(37, 99, 235, 0.2)) !important;
            animation: float-rocket 3s ease-in-out infinite !important;
        }
        .expansion-banner-content {
            flex-grow: 1 !important;
            position: relative !important;
            z-index: 2 !important;
        }
        .expansion-banner-header {
            display: flex !important;
            align-items: center !important;
            gap: 1rem !important;
            flex-wrap: wrap !important;
            margin-bottom: 0.6rem !important;
        }
        .expansion-banner-title {
            font-size: 1.35rem !important;
            font-weight: 850 !important;
            color: #0f172a !important;
            margin: 0 !important;
            line-height: 1.4 !important;
            letter-spacing: -0.01em !important;
        }
        .expansion-banner-title span.blue-highlight {
            background: linear-gradient(135deg, #2563eb 0%, #6366f1 100%) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
            display: inline-block !important;
            font-weight: 900 !important;
        }
        .expansion-banner-badge {
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            background: rgba(37, 99, 235, 0.08) !important;
            color: #2563eb !important;
            padding: 0.35rem 0.85rem !important;
            border-radius: 50px !important;
            font-weight: 700 !important;
            font-size: 0.75rem !important;
            letter-spacing: 0.02em !important;
            border: 1px solid rgba(37, 99, 235, 0.15) !important;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.05) !important;
        }
        .expansion-banner-badge::before {
            content: "" !important;
            width: 8px !important;
            height: 8px !important;
            background-color: #10b981 !important;
            border-radius: 50% !important;
            display: inline-block !important;
            box-shadow: 0 0 8px #10b981 !important;
            animation: live-dot 1.5s ease-in-out infinite alternate !important;
        }
        @keyframes live-dot {
            0% { opacity: 0.4; transform: scale(0.8); }
            100% { opacity: 1; transform: scale(1.2); }
        }
        .expansion-banner-bullets {
            display: flex !important;
            align-items: center !important;
            gap: 1.5rem !important;
            flex-wrap: wrap !important;
            font-size: 0.95rem !important;
            color: #334155 !important;
            font-weight: 700 !important;
        }
        .expansion-banner-bullet {
            display: flex !important;
            align-items: center !important;
            gap: 0.55rem !important;
            transition: all 0.3s ease !important;
        }
        .expansion-banner-card:hover .expansion-banner-bullet {
            transform: translateX(2px) !important;
        }
        [dir="rtl"] .expansion-banner-card:hover .expansion-banner-bullet {
            transform: translateX(-2px) !important;
        }
        .expansion-banner-bullet i {
            color: #4f46e5 !important;
            font-size: 1.05rem !important;
        }
        .expansion-banner-bullet:not(:last-child)::after {
            content: "" !important;
            width: 1.5px !important;
            height: 15px !important;
            background-color: #cbd5e1 !important;
            margin-left: 1.5rem !important;
            display: inline-block !important;
        }
        .expansion-banner-skyline {
            position: absolute !important;
            right: 0 !important;
            bottom: -5px !important;
            height: 110% !important;
            pointer-events: none !important;
            opacity: 0.22 !important;
            z-index: 1 !important;
            transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        .expansion-banner-card:hover .expansion-banner-skyline {
            opacity: 0.35 !important;
            transform: scale(1.05) !important;
        }
        [dir="rtl"] .expansion-banner-skyline {
            right: auto !important;
            left: 0 !important;
            transform: scaleX(-1) !important;
        }
        [dir="rtl"] .expansion-banner-card:hover .expansion-banner-skyline {
            transform: scaleX(-1) scale(1.05) !important;
        }
        [dir="rtl"] .expansion-banner-bullet:not(:last-child)::after {
            margin-left: 0 !important;
            margin-right: 1.5rem !important;
        }
        @media (max-width: 991.98px) {
            .expansion-banner-card {
                flex-direction: column !important;
                align-items: flex-start !important;
                padding: 1.6rem !important;
                gap: 1.2rem !important;
            }
            .expansion-banner-icon-container {
                width: 60px !important;
                height: 60px !important;
            }
            .expansion-banner-icon-container i {
                font-size: 1.9rem !important;
            }
            .expansion-banner-bullet:not(:last-child)::after {
                display: none !important;
            }
            .expansion-banner-bullets {
                gap: 0.85rem !important;
                flex-direction: column !important;
                align-items: flex-start !important;
            }
            .expansion-banner-skyline {
                opacity: 0.1 !important;
                height: 80% !important;
            }
        }

        .coverage-map-card {
            overflow: hidden !important;
            padding: 0 !important;
            border-radius: 24px !important;
            border: 1px solid rgba(37, 99, 235, 0.12) !important;
            box-shadow: 0 24px 55px -28px rgba(15, 23, 42, 0.38) !important;
            background: #ffffff !important;
        }

        .coverage-map-card img {
            display: block !important;
            width: 100% !important;
            height: auto !important;
            object-fit: contain !important;
        }

        @media (max-width: 767.98px) {
            .coverage-map-card {
                border-radius: 18px !important;
            }
        }
    </style>
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

        $featuredProviders = collect($featuredProviders ?? $topProviders ?? [])->take(12);
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

                                <div class="hero-subtitle-badge fade-in-up">
                                    {{ __('home.hero_subtitle') }}
                                </div>
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

        <!-- Toronto Expansion Card Section -->
        <section class="expansion-banner-section py-2">
            <div class="container">
                <div class="expansion-banner-card">
                    <!-- Rocket Circular Icon with Float Animation -->
                    <div class="expansion-banner-icon-container">
                        <i class="fas fa-rocket"></i>
                    </div>

                    <!-- Banner Content -->
                    <div class="expansion-banner-content">
                        <div class="expansion-banner-header">
                            <h2 class="expansion-banner-title">
                                {!! __('home.expansion_title') !!}
                            </h2>
                            <span class="expansion-banner-badge">{{ __('home.expansion_badge') }}</span>
                        </div>
                        <div class="expansion-banner-bullets">
                            <div class="expansion-banner-bullet">
                                <i class="fas fa-users"></i>
                                <span>{{ __('home.expansion_bullet1') }}</span>
                            </div>
                            <div class="expansion-banner-bullet">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>{{ __('home.expansion_bullet2') }}</span>
                            </div>
                            <div class="expansion-banner-bullet">
                                <i class="fas fa-user-check"></i>
                                <span>{{ __('home.expansion_bullet3') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Toronto Skyline Line Art Background -->
                    <svg class="expansion-banner-skyline" viewBox="0 0 350 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5,100 L25,100 L25,85 L45,85 L45,100 L55,100 L55,75 L75,75 L75,100 L85,100 L85,60 L105,60 L105,100 L115,100 L115,80 L135,80 L135,100 L150,100 L156,70 L156,40 L150,38 L150,33 L156,31 L156,10 L157,10 L157,31 L163,33 L163,38 L157,40 L157,70 L165,100 L180,100 L180,70 L195,70 L195,100 L210,100 L210,55 L230,55 L230,100 L245,100 L245,80 L265,80 L265,100 L280,100 L280,65 L300,65 L300,100 L320,100 L320,85 L345,85 L345,100" stroke="#2563eb" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
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
                                                <img src="{{ $provider->profile_image_url }}"
                                                    alt="{{ $provider->user->name ?? __('home.view_profile') }}" loading="lazy"
                                                    class="w-100 h-100 object-fit-cover provider-img">
                                            </div>

                                            <div class="premium-provider-body">
                                                <div class="home-provider-heading">
                                                    <h3 class="fw-bold mb-1 home-text-dark text-truncate provider-name">
                                                        {{ $provider->localized_company_name ?? $provider->user->name }}
                                                    </h3>

                                                </div>

                                                <p class="small home-text-muted mb-3 text-truncate provider-category">
                                                    {{ $provider->category->translated_name ?? $provider->category->name ?? __('home.providers_available') }}
                                                </p>

                                                <div class="d-flex align-items-center gap-2 mb-3">
                                                    <div class="premium-stars">
                                                        @if($provider->calculated_rating || $provider->rating)
                                                            @php $rating = $provider->calculated_rating ?? $provider->rating; @endphp
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

                                                @php
                                                    $locationHidden = $provider->location && ! $provider->location->is_active;
                                                    $canSeeLocation = ! $locationHidden || $provider->isOwner();
                                                @endphp
                                                @if($provider->location)
                                                    <p class="small home-text-muted mb-0 provider-location home-provider-location">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                        @if($canSeeLocation)
                                                            {{ $provider->location->localized_name ?? __('home.location') }}
                                                        @else
                                                            {{ __('service_provider.available_all_areas') }}
                                                        @endif
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

        <section class="home-section">
            <div class="container">
                <div class="home-shell home-section-panel fade-in-up">
                    <div class="home-section-head">
                        <div>
                            <h2 class="section-title mb-2">{{ __('home.explore_services') }}</h2>
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

        <section class="home-section">
            <div class="container">
                <div class="home-shell coverage-map-card fade-in-up">
                    <img src="{{ asset('images/speeda-coverage-map.png') }}"
                        alt="Speeda service coverage map for Ottawa, Gatineau, Montreal, Laval, and Toronto GTA"
                        loading="lazy">
                </div>
            </div>
        </section>

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
