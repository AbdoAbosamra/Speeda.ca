<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ in_array(app()->getLocale(), ['ar', 'he', 'ur', 'fa']) ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('categories.page_title') }} - {{ config('app.name', 'Speeda') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/main-logo.png') }}">
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: #4F46E5; /* Indigo */
            --primary-dark: #4338ca;
            --primary-light: #818cf8;
            --accent-color: #06b6d4; /* Cyan */
            --secondary-color: #64748b;
            --light-bg: #f8fafc;
            --surface-color: #ffffff;
            --dark-text: #1e293b;
            --muted-text: #64748b;
            --border-color: #e2e8f0;

            /* Modern Shadows */
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-hover: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);

            --radius-md: 12px;
            --radius-lg: 20px;
            --radius-xl: 30px;
        }

        /* Modal z-index fix */
        .modal {
            z-index: 10000 !important;
        }
        .modal-backdrop {
            z-index: 9999 !important;
        }

        body {
            font-family: 'Inter', 'Cairo', sans-serif;
            color: var(--dark-text);
            background-color: var(--light-bg);
            background-image:
                radial-gradient(at 0% 0%, hsla(253,16%,7%,0) 0, transparent 50%),
                radial-gradient(at 50% 0%, hsla(225,39%,30%,0) 0, transparent 50%),
                radial-gradient(at 100% 0%, hsla(339,49%,30%,0) 0, transparent 50%);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* Navigation Adjustments */
        .navbar-brand img {
            width: 140px;
            height: auto;
        }

        /* Buttons Redesign */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border: none;
            border-radius: var(--radius-md);
            padding: 0.6rem 1.8rem; /* Slightly reduced padding */
            font-weight: 600;
            letter-spacing: 0.025em;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 14px 0 rgba(79, 70, 229, 0.39);
            position: relative;
            overflow: hidden;
            font-size: 0.95rem; /* Slightly smaller text */
        }

        .btn-primary::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(255,255,255,0.2), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.23);
            color: white;
        }
        .btn-primary:hover::after { opacity: 1; }

        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            background: transparent;
            border-radius: var(--radius-md);
            padding: 0.4rem 1.4rem; /* Reduced padding */
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        }

        .btn-light {
            border-radius: var(--radius-md);
            font-weight: 600;
            padding: 0.6rem 1.8rem; /* Reduced padding */
            color: var(--primary-dark);
            background: #fff;
            border: none;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        .btn-light:hover {
            background: #f1f5f9;
            color: var(--primary-color);
            transform: translateY(-1px);
        }

        /* Breadcrumb */
        .breadcrumb {
            background: transparent;
            padding: 0.8rem 0; /* Reduced padding */
            font-size: 0.85rem;
        }
        .breadcrumb-item a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .breadcrumb-item a:hover { color: var(--primary-color); }
        .breadcrumb-item.active {
            color: var(--primary-color);
            font-weight: 600;
        }

        /* Page Header */
        .page-header {
            padding: 1.8rem 0 0.8rem 0; /* Reduced padding */
            text-align: center;
        }
        .page-header h1 {
            font-weight: 800;
            font-size: 2.2rem; /* Reduced size */
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.4rem;
            letter-spacing: -0.02em;
        }
        .page-header p {
            color: var(--muted-text);
            font-size: 1.05rem; /* Reduced size */
            max-width: 600px;
            margin: 0 auto;
        }

        /* Search Hero Section */
        .search-section {
            background: linear-gradient(135deg, #ffffff 0%, #f0f4ff 100%);
            border-radius: var(--radius-lg);
            padding: 3rem 1.5rem; /* Reduced padding */
            margin-bottom: 3.5rem; /* Reduced margin */
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255,255,255,0.5);
        }

        .search-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.1) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
            z-index: 0;
        }
        .search-section::after {
            content: '';
            position: absolute;
            bottom: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(6, 182, 212, 0.1) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
            z-index: 0;
        }

        .search-content { position: relative; z-index: 1; }
        .search-section h3 {
            font-weight: 700;
            font-size: 1.5rem; /* Reduced size */
            color: var(--dark-text);
            margin-bottom: 0.5rem;
        }
        .search-section p { font-size: 0.95rem; } /* Reduced size */

        .input-group-lg {
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
        }
        .input-group-lg:focus-within {
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
            border-color: var(--primary-light);
        }
        .input-group-text {
            background: white;
            border: none;
            padding-left: 1.2rem; /* Reduced padding */
            color: var(--secondary-color);
        }
        .form-control {
            border: none;
            font-weight: 500;
            color: var(--dark-text);
            font-size: 0.95rem; /* Reduced size */
        }
        .form-control:focus { box-shadow: none; }

        /* Quick Navigation */
        .quick-nav {
            background: var(--surface-color);
            border-radius: var(--radius-lg);
            padding: 1.8rem; /* Reduced padding */
            margin-bottom: 3.5rem; /* Reduced margin */
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }
        .quick-nav-title {
            font-weight: 700;
            font-size: 1.15rem; /* Reduced size */
            margin-bottom: 1.2rem;
            color: var(--dark-text);
            text-align: center;
            position: relative;
        }
        .quick-nav-title::after {
            content: '';
            display: block;
            width: 40px;
            height: 3px;
            background: var(--primary-color);
            margin: 0.4rem auto 0;
            border-radius: 2px;
        }

        .quick-nav-item {
            text-align: center;
            padding: 1.2rem 0.8rem; /* Reduced padding */
            border-radius: var(--radius-md);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            color: inherit;
            display: block;
            height: 100%;
            border: 1px solid transparent;
        }
        .quick-nav-item:hover {
            background: #f8fafc;
            transform: translateY(-5px);
            border-color: rgba(79, 70, 229, 0.1);
            box-shadow: var(--shadow-md);
            text-decoration: none;
            color: var(--primary-color);
        }

        .quick-nav-icon {
            width: 60px; /* Reduced size */
            height: 60px;
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.8rem;
            font-size: 1.5rem; /* Reduced size */
            color: white;
            box-shadow: var(--shadow-md);
            transition: transform 0.3s ease;
        }
        .quick-nav-item:hover .quick-nav-icon { transform: scale(1.1) rotate(5deg); }

        /* Section Cards */
        .section-card {
            background: var(--surface-color);
            border-radius: var(--radius-lg);
            padding: 2rem; /* Reduced padding */
            margin-bottom: 3.5rem; /* Reduced margin */
            box-shadow: var(--shadow-md);
            transition: all 0.4s ease;
            border: 1px solid var(--border-color);
            position: relative;
            scroll-margin-top: 110px;
            overflow: hidden;
        }
        .section-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .section-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-hover); }
        .section-card:hover::before { opacity: 1; }

        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem; /* Reduced margin */
            padding-bottom: 1.2rem;
            border-bottom: 1px solid var(--border-color);
        }

        .section-icon {
            width: 80px; /* Reduced size */
            height: 80px;
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.2rem;
            font-size: 2rem; /* Reduced size */
            color: white;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            position: relative;
            z-index: 2;
        }
        [dir="rtl"] .section-icon {
            margin-right: 0;
            margin-left: 1.2rem;
        }

        .section-title h2 {
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: var(--dark-text);
            font-size: 1.5rem; /* Reduced size */
        }
        .section-title p { color: var(--muted-text); margin-bottom: 0; font-weight: 500; font-size: 0.9rem; }

        /* Category Cards */
        .category-card {
            background: white;
            border-radius: var(--radius-md);
            padding: 1.5rem; /* Reduced padding */
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid var(--border-color);
            text-decoration: none;
            color: inherit;
            display: block;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            text-decoration: none;
            color: inherit;
            border-color: var(--primary-light);
            background: #fcfcff;
        }

        .category-icon {
            width: 50px; /* Reduced size */
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.3rem; /* Reduced size */
            color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .category-card h4 {
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--dark-text);
            font-size: 1.1rem; /* Reduced size */
        }

        .category-card p {
            color: var(--muted-text);
            font-size: 0.9rem; /* Reduced size */
            line-height: 1.5;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* SUBCATEGORY LIST - Updated for clarity and size */
        .subcategory-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: auto;
        }

        .subcategory-chip {
            display: inline-flex;
            align-items: center;
            /* Increased padding and font size for clarity */
            padding: 0.5rem 0.9rem;
            border-radius: 8px; /* Slightly less rounded for modern look */
            background: #f1f5f9;
            color: #334155; /* Darker text for better readability */
            font-size: 0.9rem; /* Increased from 0.75rem */
            font-weight: 700; /* Bold for clarity */
            border: 1px solid #e2e8f0; /* Added border for definition */
            transition: all 0.2s;
        }

        .category-card:hover .subcategory-chip {
            background: #e0e7ff;
            color: var(--primary-color);
            border-color: var(--primary-light);
        }

        /* Statistics / Location Alert */
        .stat-card {
            background: white;
            border-radius: var(--radius-md);
            padding: 1.5rem; /* Reduced padding */
            text-align: center;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
        .stat-number { font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--primary-color); }

        .location-alert {
            border-radius: var(--radius-md);
            border: none;
            background: linear-gradient(135deg, #eff6ff, #fff);
            box-shadow: var(--shadow-sm);
            color: var(--dark-text);
            border-left: 4px solid var(--primary-color);
        }

        /* Empty State */
        .empty-state {
            padding: 3rem 2rem; /* Reduced padding */
            text-align: center;
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            border: 1px dashed var(--border-color);
        }
        .empty-state i { font-size: 3.5rem; color: #cbd5e1; margin-bottom: 1.5rem; display: block; }

        /* Modal Redesign */
        .modal-content { border: none; border-radius: var(--radius-lg); box-shadow: var(--shadow-hover); overflow: hidden; }
        .modal-header { background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); color: white; border: none; padding: 1.2rem; } /* Reduced padding */
        .modal-body { padding: 1.5rem; background: #f8fafc; }
        .modal-footer { border: none; padding: 1.2rem; background: white; }
        .form-control, .form-control:focus {
            background-color: #f8fafc;
            border: 1px solid var(--border-color);
            padding: 0.7rem 1rem;
            font-size: 0.95rem;
        }

        /* Scroll Animation Classes */
        .reveal { opacity: 0; transform: translateY(30px); transition: all 0.8s cubic-bezier(0.5, 0, 0, 1); }
        .reveal.active { opacity: 1; transform: translateY(0); }

        /* Responsive Design */
        @media (max-width: 768px) {
            .page-header h1 { font-size: 1.8rem; }
            .search-section { padding: 2rem 1.2rem; margin-bottom: 2.5rem; }
            .section-card { padding: 1.5rem 1.2rem; margin-bottom: 2.5rem; }
            .section-header { flex-direction: column; text-align: center; gap: 1rem; }
            .section-icon { margin: 0; }
            [dir="rtl"] .section-icon { margin: 0; }
            .quick-nav { padding: 1.2rem; }
        }

        /* Gradient Utilities */
        .grad-automotive { background: linear-gradient(135deg, #DC2626, #EF4444); }
        .grad-home { background: linear-gradient(135deg, #059669, #10B981); }
        .grad-professional { background: linear-gradient(135deg, #2563EB, #3B82F6); }
        .grad-personal { background: linear-gradient(135deg, #EC4899, #F472B6); }
        .grad-technical { background: linear-gradient(135deg, #7C3AED, #8B5CF6); }
        .grad-event { background: linear-gradient(135deg, #F59E0B, #FBBF24); }
        .grad-health { background: linear-gradient(135deg, #EF4444, #F87171); }

        .cat-bg-1 { background: linear-gradient(135deg, #667eea, #764ba2); }
        .cat-bg-2 { background: linear-gradient(135deg, #f093fb, #f5576c); }
        .cat-bg-3 { background: linear-gradient(135deg, #4facfe, #00f2fe); }
        .cat-bg-4 { background: linear-gradient(135deg, #43e97b, #38f9d7); }
        .cat-bg-5 { background: linear-gradient(135deg, #fa709a, #fee140); }
        .cat-bg-6 { background: linear-gradient(135deg, #30cfd0, #330867); }
    </style>
</head>

<body>
    @include('components.main-nav')

    <div class="container mt-4">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center">
                <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="fas fa-home me-1"></i>{{ __('general.home') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-tags me-1"></i>{{ __('general.categories') }}</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="page-header reveal">
            <h1>
                @if($selectedCity)
                    {{ __('categories.professional_services_in', ['city' => $selectedCity->city]) }}
                @else
                    {{ __('categories.browse_categories') }}
                @endif
            </h1>
            <p>
                @if($selectedCity)
                    {{ __('categories.find_trusted_in_city', ['city' => $selectedCity->city]) }}
                @else
                    {{ __('categories.discover_professionals') }}
                @endif
            </p>
        </div>

        <!-- Search Section -->
        <div class="search-section reveal">
            <div class="search-content">
                <div class="row align-items-center">
                    <div class="col-lg-10 mx-auto text-center">
                        <h3 class="mb-2">{{ __('categories.find_right_professional_title') }}</h3>
                        <p class="mb-4 text-muted">{{ __('categories.find_right_professional_desc') }}</p>

                        <!-- Search Form -->
                        <form method="GET" action="{{ route('categories') }}" class="mt-4">
                            <div class="row g-2 align-items-center justify-content-center">
                                <div class="col-md-8">
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <input type="text" name="search" class="form-control ps-0"
                                            placeholder="{{ __('categories.search_categories_placeholder') }}"
                                            value="{{ $search ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            {{ __('categories.search_button') }}
                                        </button>
                                        @if($search)
                                            <a href="{{ route('categories') }}" class="btn btn-light btn-sm mt-1">
                                                <i class="fas fa-times me-1"></i> {{ __('categories.clear_search') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @if($selectedCity)
                                <input type="hidden" name="city_id" value="{{ request()->input('city_id') }}">
                            @endif
                        </form>

                        @if($search)
                            <div class="mt-3">
                                <span class="badge bg-white text-primary shadow-sm">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ __('categories.search_results_for', ['query' => $search]) }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Navigation -->
        <div class="quick-nav reveal">
            <h4 class="quick-nav-title">{{ __('categories.quick_navigation') }}</h4>
            <div class="row g-3">
                @foreach($sections ?? [] as $section)
                    <div class="col-lg-2 col-md-4 col-6">
                        <a href="#{{ $section->anchor_id }}" class="quick-nav-item">
                            <div class="quick-nav-icon" style="background: linear-gradient(135deg, {{ $section->color ?? '#2563EB' }}, {{ $section->color ?? '#2563EB' }}cc);">
                                <i class="{{ $section->icon }}"></i>
                            </div>
                            <h6 class="mb-0 fw-bold">{{ $section->translated_name }}</h6>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Unified Error Handler --}}
        <x-error-handler />

        <!-- Location Alert -->
        @if($selectedCity)
            <div class="alert location-alert d-flex align-items-center mb-5 reveal" role="alert">
                <i class="fas fa-map-marker-alt fa-2x me-3 text-primary"></i>
                <div class="flex-grow-1">
                    <strong>{{ __('categories.showing_services_in') }} {{ $selectedCity->city }}</strong>
                </div>
                <a href="{{ route('categories') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    {{ __('categories.show_all_cities') }}
                </a>
            </div>
        @endif

        <!-- Categories Sections -->
        @forelse($sections ?? [] as $section)
            <div class="section-card reveal" id="{{ $section->anchor_id }}">
                <div class="section-header">
                    <div class="section-icon" style="background: linear-gradient(135deg, {{ $section->color ?? '#2563EB' }}, {{ $section->color ?? '#2563EB' }}cc);">
                        <i class="{{ $section->icon }}"></i>
                    </div>
                    <div class="section-title">
                        <h2>{{ $section->translated_name }}</h2>
                        <p>Explore the best services in {{ $section->translated_name }}</p>
                    </div>
                </div>

                <div class="row g-3">
                    @foreach($section->children as $category)
                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <a href="{{ route('service-providers.index', array_filter(['category' => $category->slug, 'location' => $selectedCity?->id])) }}"
                                class="category-card">
                                <div class="category-icon cat-bg-{{ ($loop->index % 6) + 1 }}">
                                    <i class="{{ $category->icon }}"></i>
                                </div>
                                <h4>{{ $category->translated_name }}</h4>
                                <p>{{ $category->description ?? 'Find top rated professionals for this service.' }}</p>

                                {{-- Updated Subcategory Logic Area --}}
                                @if($category->children->isNotEmpty())
                                    <div class="subcategory-list">
                                        @foreach($category->children->take(3) as $child) {{-- Limit chips for design --}}
                                            <span class="subcategory-chip">{{ $child->translated_name }}</span>
                                        @endforeach
                                        @if($category->children->count() > 3)
                                            <span class="subcategory-chip">+{{ $category->children->count() - 3 }}</span>
                                        @endif
                                    </div>
                                @endif
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <!-- Empty State -->
            <div class="section-card reveal">
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h3 class="text-muted fw-bold mb-3">{{ __('categories.none_available') }}</h3>
                    <p class="text-muted mb-0">{{ __('categories.adding_categories_message') }}</p>
                </div>
            </div>
        @endforelse

        <!-- Call to Action Section -->
        @if(isset($sections) && count($sections) > 0)
            <div class="section-card text-center bg-white reveal">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="mb-4 d-inline-block p-3 rounded-circle bg-light text-primary">
                            <i class="fas fa-hands-helping fa-2x"></i>
                        </div>
                        <h3 class="fw-bold mb-3">{{ __('categories.cant_find_what_you_need') }}</h3>
                        <p class="text-muted mb-4">{{ __('categories.help_you_find_message') }}</p>
                        <a href="#" class="btn btn-outline-primary px-4 py-2" data-bs-toggle="modal" data-bs-target="#suggestionModal">
                            <i class="fas fa-plus-circle me-2"></i> {{ __('categories.suggest_category') }}
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Suggestion Modal -->
    <div class="modal fade" id="suggestionModal" tabindex="-1" aria-labelledby="suggestionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="suggestionModalLabel">
                        <i class="fas fa-lightbulb me-2"></i>
                        {{ __('categories.suggest_new_category') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="suggestionForm">
                        <div class="mb-4">
                            <label for="categoryName" class="form-label fw-bold text-dark">
                                {{ __('categories.category_name') }}
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="fas fa-tag text-muted"></i></span>
                                <input type="text" class="form-control border-start-0" id="categoryName"
                                    placeholder="{{ __('categories.category_name_placeholder') }}" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="categoryDescription" class="form-label fw-bold text-dark">
                                {{ __('categories.description') }}
                                <span class="text-muted small fw-normal">({{ __('general.optional') }})</span>
                            </label>
                            <textarea class="form-control" id="categoryDescription" rows="4"
                                placeholder="{{ __('categories.description_placeholder') }}"></textarea>
                        </div>
                        <div class="alert alert-light border border-info d-flex align-items-center">
                            <i class="fab fa-facebook-messenger fa-2x text-primary me-3"></i>
                            <div class="small text-muted">
                                <strong class="d-block text-dark">{{ __('categories.note') ?? 'Note' }}:</strong>
                                {{ __('categories.messenger_redirect_info') ?? 'You will be redirected to Messenger to send your suggestion.' }}
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light text-muted" data-bs-dismiss="modal">
                        {{ __('general.cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary" onclick="submitSuggestion()">
                        <i class="fab fa-facebook-messenger me-2"></i>
                        {{ __('categories.send_via_messenger') ?? __('categories.submit_suggestion') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script>
        const requiredFieldsMessage = @json(__('validation.fill_required_fields'));
        const suggestionSuccessMessage = @json(__('categories.suggestion_success'));

        document.addEventListener('DOMContentLoaded', function () {
            console.log('Enhanced Categories page loaded successfully');

            // Scroll Reveal Animation using Intersection Observer
            const observerOptions = {
                root: null,
                rootMargin: '0px',
                threshold: 0.1
            };

            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            const revealElements = document.querySelectorAll('.reveal');
            revealElements.forEach(el => observer.observe(el));

            // Smooth scrolling for quick navigation
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;

                    const target = document.querySelector(targetId);
                    if (target) {
                        const headerOffset = 100;
                        const elementPosition = target.getBoundingClientRect().top;
                        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                        window.scrollTo({
                            top: offsetPosition,
                            behavior: "smooth"
                        });
                    }
                });
            });
        });

        function submitSuggestion() {
            const categoryName = document.getElementById('categoryName').value;
            const categoryDescription = document.getElementById('categoryDescription').value;

            if (!categoryName) {
                alert(requiredFieldsMessage);
                return;
            }

            const message = `{{ __('categories.new_category_suggestion') ?? 'New Category Suggestion' }}:\n${categoryName}${categoryDescription ? '\n' + categoryDescription : ''}`;
            const messengerUrl = `https://m.me/61583422931690?text=${encodeURIComponent(message)}`;
            window.open(messengerUrl, '_blank');

            const modal = bootstrap.Modal.getInstance(document.getElementById('suggestionModal'));
            modal.hide();

            document.getElementById('suggestionForm').reset();
        }
    </script>

    {{-- Toast Notification System --}}
    <x-toast-notification />

    @include('layouts.footer')

</body>

</html>
