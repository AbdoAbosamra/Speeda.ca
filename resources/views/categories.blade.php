<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ar', 'he', 'ur', 'fa']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('categories.page_title') }} - {{ config('app.name', 'Speeda') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/main-logo.png') }}">
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --light-bg: #f8f9fa;
            --dark-text: #212529;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --card-hover-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        /* Modal z-index fix */
        .modal {
            z-index: 10000 !important;
        }

        .modal-backdrop {
            z-index: 9999 !important;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--dark-text);
            line-height: 1.6;
            background-color: #f8f9fa;
        }

        /* Navigation */
        .navbar-brand img {
            width: 120px;
            height: auto;
            transition: transform 0.3s ease;
        }

        .navbar-brand img:hover {
            transform: scale(1.05);
        }

        .nav-link {
            font-weight: 500;
            transition: color 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: var(--primary-color);
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
        }

        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: 8px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-1px);
        }

        /* Section Cards */
        .section-card {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            margin-bottom: 3rem;
            box-shadow: var(--card-shadow);
            transition: all 0.4s ease;
            border-left: 4px solid transparent;
        }

        .section-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-hover-shadow);
            border-left-color: var(--primary-color);
        }

        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f8f9fa;
        }

        .section-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.5rem;
            font-size: 2rem;
            color: white;
            transition: transform 0.3s ease;
        }

        .section-card:hover .section-icon {
            transform: scale(1.1);
        }

        .section-title {
            flex: 1;
        }

        .section-title h2 {
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .section-title p {
            color: #666;
            margin-bottom: 0;
        }

        /* Category Cards */
        .category-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
            text-decoration: none;
            color: inherit;
            display: block;
            height: 100%;
        }

        .category-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            text-decoration: none;
            color: inherit;
            border-color: var(--primary-color);
        }

        .category-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.25rem;
            color: white;
        }

        .category-card h4 {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
            font-size: 1.1rem;
        }

        .category-card p {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 0;
        }

        /* Quick Navigation */
        .quick-nav {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 3rem;
            box-shadow: var(--card-shadow);
        }

        .quick-nav-item {
            text-align: center;
            padding: 1rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
        }

        .quick-nav-item:hover {
            background: var(--light-bg);
            transform: translateY(-2px);
            text-decoration: none;
            color: inherit;
        }

        .quick-nav-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: white;
        }

        /* Statistics */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--card-hover-shadow);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Search Section */
        .search-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 3rem;
            color: white;
            margin-bottom: 3rem;
        }

        /* Page Header */
        .page-header {
            padding: 2rem 0;
            text-align: center;
        }

        .page-header h1 {
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: var(--secondary-color);
            font-size: 1.2rem;
        }

        /* Breadcrumb */
        .breadcrumb {
            background: transparent;
            padding: 0.75rem 0;
        }

        .breadcrumb-item a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .breadcrumb-item a:hover {
            color: #0056b3;
        }

        /* Location Alert */
        .location-alert {
            border-radius: 10px;
            border-left: 4px solid var(--primary-color);
        }

        /* Empty State */
        .empty-state {
            padding: 3rem 1rem;
            text-align: center;
            background: white;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar-brand img {
                width: 100px;
            }

            .section-card {
                padding: 2rem;
                margin-bottom: 2rem;
            }

            .section-header {
                flex-direction: column;
                text-align: center;
            }

            .section-icon {
                margin-right: 0;
                margin-bottom: 1rem;
            }

            .page-header h1 {
                font-size: 1.75rem;
            }

            .page-header p {
                font-size: 1rem;
            }

            .search-section {
                padding: 2rem;
            }
        }

        @media (max-width: 576px) {
            .section-card {
                padding: 1.5rem;
            }

            .category-card {
                padding: 1.25rem;
            }

            .quick-nav {
                padding: 1.5rem;
            }
        }

        /* Loading Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .section-card {
            animation: fadeIn 0.6s ease-out;
        }

        .category-card {
            animation: fadeIn 0.4s ease-out;
        }

        /* Gradient Colors for Sections */
        .automotive-bg { background: linear-gradient(135deg, #DC2626, #EF4444); }
        .home-bg { background: linear-gradient(135deg, #059669, #10B981); }
        .professional-bg { background: linear-gradient(135deg, #2563EB, #3B82F6); }
        .personal-bg { background: linear-gradient(135deg, #EC4899, #F472B6); }
        .technical-bg { background: linear-gradient(135deg, #7C3AED, #8B5CF6); }
        .event-bg { background: linear-gradient(135deg, #F59E0B, #FBBF24); }
        .health-bg { background: linear-gradient(135deg, #EF4444, #F87171); }

        /* Category icon backgrounds */
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
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="fas fa-home me-1"></i>{{ __('general.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('location') }}"><i class="fas fa-map-marker-alt me-1"></i>{{ __('general.locations') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-tags me-1"></i>{{ __('general.categories') }}</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <h1>
            @if($selectedCity)
                {{ __('categories.professional_services_in', ['city' => $selectedCity]) }}
            @else
                {{ __('categories.browse_categories') }}
            @endif
        </h1>
        <p>
            @if($selectedCity)
                {{ __('categories.find_trusted_in_city', ['city' => $selectedCity]) }}
            @else
                {{ __('categories.discover_professionals') }}
            @endif
        </p>
    </div>

    <!-- Search Section -->
    <div class="search-section">
        <div class="row align-items-center">
            <div class="col-md-12">
                <h3 class="mb-3">{{ __('categories.find_right_professional_title') }}</h3>
                <p class="mb-4">{{ __('categories.find_right_professional_desc') }}</p>

                <!-- Search Form -->
                <form method="GET" action="{{ route('categories') }}" class="mt-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-8">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input
                                    type="text"
                                    name="search"
                                    class="form-control border-start-0 ps-0"
                                    placeholder="{{ __('categories.search_categories_placeholder') }}"
                                    value="{{ $search ?? '' }}"
                                    style="font-size: 1.1rem; padding: 0.75rem 1rem;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-grid gap-2 d-md-flex">
                                <button type="submit" class="btn btn-light btn-lg flex-fill">
                                    <i class="fas fa-search me-2"></i>
                                    {{ __('categories.search_button') }}
                                </button>
                                @if($search)
                                    <a href="{{ route('categories') }}" class="btn btn-outline-light btn-lg">
                                        <i class="fas fa-times me-2"></i>
                                        {{ __('categories.clear_search') }}
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
                        <small class="text-white-50">
                            <i class="fas fa-info-circle me-1"></i>
                            {{ __('categories.search_results_for', ['query' => $search]) }}
                        </small>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Navigation -->
    <div class="quick-nav">
        <h4 class="text-center mb-4">{{ __('categories.quick_navigation') }}</h4>
        <div class="row">
            <div class="col-md-3 col-6 mb-3">
                <a href="#automotive" class="quick-nav-item">
                    <div class="quick-nav-icon automotive-bg">
                        <i class="fas fa-car"></i>
                    </div>
                    <h6>{{ __('categories.automotive') }}</h6>
                </a>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <a href="#home" class="quick-nav-item">
                    <div class="quick-nav-icon home-bg">
                        <i class="fas fa-home"></i>
                    </div>
                    <h6>{{ __('categories.home_services') }}</h6>
                </a>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <a href="#professional" class="quick-nav-item">
                    <div class="quick-nav-icon professional-bg">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <h6>{{ __('categories.professional') }}</h6>
                </a>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <a href="#personal" class="quick-nav-item">
                    <div class="quick-nav-icon personal-bg">
                        <i class="fas fa-spa"></i>
                    </div>
                    <h6>{{ __('categories.personal_care') }}</h6>
                </a>
            </div>
        </div>
    </div>

    {{-- Unified Error Handler --}}
    <x-error-handler />

    <!-- Location Alert -->
    @if($selectedCity)
    <div class="alert location-alert alert-info mb-5">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-map-marker-alt me-2"></i>
                <strong>{{ __('categories.showing_services_in') }} {{ $selectedCity }}</strong>
            </div>
            <a href="{{ route('categories') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-globe me-1"></i> {{ __('categories.show_all_cities') }}
            </a>
        </div>
    </div>
    @endif

    <!-- Statistics -->
    <div class="row mb-5">
        <div class="col-md-3 col-6 mb-3">
            <div class="stat-card">
                <div class="stat-number text-primary">{{ $stats['totalSections'] ?? 0 }}</div>
                <div class="stat-label">{{ __('categories.stat_service_sections') }}</div>
            </div>
        </div>
        <div class="col-md-4 col-6 mb-3">
            <div class="stat-card">
                <div class="stat-number text-success">{{ $stats['totalCategories'] ?? 0 }}</div>
                <div class="stat-label">{{ __('categories.stat_professions') }}</div>
            </div>
        </div>
        <div class="col-md-4 col-6 mb-3">
            <div class="stat-card">
                <div class="stat-number text-warning">{{ $stats['totalLocations'] ?? 0 }}</div>
                <div class="stat-label">{{ __('categories.stat_locations') }}</div>
            </div>
        </div>
    </div>

    <!-- Categories Sections -->
    @forelse($sections ?? [] as $section)
    <div class="section-card" id="{{ Str::slug($section->name) }}">
        <div class="section-header">
            <div class="section-icon {{ Str::slug($section->name) }}-bg">
                <i class="{{ $section->icon }}"></i>
            </div>
            <div class="section-title">
                <h2>{{ $section->translated_name }}</h2>
                <p>{{ $section->translated_description }}</p>
            </div>
        </div>

        <div class="row">
            @foreach($section->children as $category)
            <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                <a href="{{ route('service-providers.index', ['category' => $category->id, 'city' => $selectedCity]) }}"
                   class="category-card">
                    <div class="category-icon cat-bg-{{ ($loop->index % 6) + 1 }}">
                        <i class="{{ $category->icon }}"></i>
                    </div>
                    <h4>{{ $category->translated_name }}</h4>
                    <p>{{ $category->translated_description }}</p>
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @empty
    <!-- Empty State -->
    <div class="section-card">
        <div class="empty-state">
            <i class="fas fa-tags fa-4x mb-4"></i>
            <h3 class="text-muted mb-3">{{ __('categories.none_available') }}</h3>
            <p class="text-muted mb-4">{{ __('categories.adding_categories_message') }}</p>
        </div>
    </div>
    @endforelse

    <!-- Call to Action Section -->
    @if(isset($sections) && count($sections) > 0)
    <div class="section-card text-center">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <i class="fas fa-hands-helping fa-3x text-primary mb-4"></i>
                <h3 class="mb-3">{{ __('categories.cant_find_what_you_need') }}</h3>
                <p class="mb-4">{{ __('categories.help_you_find_message') }}</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="#" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#suggestionModal">
                        <i class="fas fa-lightbulb me-2"></i> {{ __('categories.suggest_category') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Suggestion Modal -->
<div class="modal fade" id="suggestionModal" tabindex="-1" aria-labelledby="suggestionModalLabel" aria-hidden="true" style="z-index: 10000;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="suggestionModalLabel">
                    <i class="fas fa-lightbulb me-2"></i>
                    {{ __('categories.suggest_new_category') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="suggestionForm">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label fw-bold">
                            <i class="fas fa-tag me-1"></i>
                            {{ __('categories.category_name') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="categoryName" placeholder="{{ __('categories.category_name_placeholder') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="categoryDescription" class="form-label fw-bold">
                            <i class="fas fa-align-left me-1"></i>
                            {{ __('categories.description') }}
                            <span class="text-muted small">({{ __('general.optional') }})</span>
                        </label>
                        <textarea class="form-control" id="categoryDescription" rows="3" placeholder="{{ __('categories.description_placeholder') }}"></textarea>
                    </div>
                    <div class="alert alert-info mb-0">
                        <i class="fab fa-facebook-messenger me-2"></i>
                        <strong>{{ __('categories.note') ?? 'Note' }}:</strong>
                        {{ __('categories.messenger_redirect_info') ?? 'You will be redirected to Messenger to send your suggestion.' }}
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    {{ __('general.cancel') }}
                </button>
                <button type="button" class="btn btn-primary" onclick="submitSuggestion()">
                    <i class="fab fa-facebook-messenger me-1"></i>
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

    document.addEventListener('DOMContentLoaded', function() {
        console.log('Enhanced Categories page loaded successfully');

        // Add loading animation to sections and categories
        const sectionCards = document.querySelectorAll('.section-card');
        const categoryCards = document.querySelectorAll('.category-card');

        sectionCards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.2}s`;
        });

        categoryCards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.05}s`;
        });

        // Smooth scrolling for quick navigation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
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

        // Build Messenger message
        const message = `{{ __('categories.new_category_suggestion') ?? 'New Category Suggestion' }}:\n${categoryName}${categoryDescription ? '\n' + categoryDescription : ''}`;

        // Open Messenger with pre-filled message
        const messengerUrl = `https://m.me/61583422931690?text=${encodeURIComponent(message)}`;
        window.open(messengerUrl, '_blank');

        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('suggestionModal'));
        modal.hide();

        // Reset form
        document.getElementById('suggestionForm').reset();
    }
</script>

{{-- Toast Notification System --}}
<x-toast-notification />

@include('layouts.footer')

</body>
</html>
