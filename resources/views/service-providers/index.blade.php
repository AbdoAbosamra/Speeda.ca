<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('service_provider.service_providers') }} - Speeda</title>
    <link rel="icon" type="image/png" href="{{ asset('images/main-logo.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-dark: #3730a3;
            --primary-light: #6366f1;
            --secondary-color: #06b6d4;
            --accent-color: #8b5cf6;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-light: #94a3b8;
            --bg-white: #ffffff;
            --bg-glass: rgba(255, 255, 255, 0.85);
            --bg-subtle: #f8fafc;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            --shadow-glow-primary: 0 0 20px rgba(79, 70, 229, 0.25);
        }

        body {
            background: linear-gradient(135deg, #f8f9ff 0%, #eef1ff 100%);
            font-family: 'Figtree', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
            color: var(--text-primary);
        }

        .page-shell {
            padding: 2rem 0 4rem;
        }

        /* --- هيرو سيكشن مع الفلاتر المدمجة --- */
        .providers-hero {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.08);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .providers-hero::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(6, 182, 212, 0.1));
            border-radius: 50%;
            transform: translate(100px, -100px);
            z-index: 0;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-content h1 {
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .hero-content p {
            color: var(--text-secondary);
            font-size: 1.05rem;
            margin-bottom: 1.5rem;
        }

        /* --- الفلاتر المدمجة والمدمجة --- */
        .hero-filters {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }

        .hero-search {
            position: relative;
            flex: 1;
            min-width: 250px;
        }

        .hero-search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 0.95rem;
            background: var(--bg-subtle);
            transition: all 0.3s ease;
            color: var(--text-primary);
        }

        .hero-search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            background: var(--bg-white);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .hero-search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            pointer-events: none;
        }

        .hero-filter-select {
            padding: 0.75rem 2.5rem 0.75rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 0.95rem;
            background: var(--bg-subtle);
            transition: all 0.3s ease;
            color: var(--text-primary);
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            min-width: 150px;
        }

        .hero-filter-select:focus {
            outline: none;
            border-color: var(--primary-color);
            background: var(--bg-white);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .hero-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* --- بطاقات مقدمي الخدمات --- */
        .provider-card {
            border: none;
            border-radius: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: var(--shadow-md);
            overflow: hidden;
            height: 100%;
            position: relative;
        }

        .provider-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .provider-card:hover::before {
            opacity: 1;
        }

        .provider-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        .provider-card .card-body {
            padding: 1.5rem;
        }

        .provider-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.25rem;
        }

        .provider-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--bg-subtle);
            box-shadow: var(--shadow-sm);
            margin-right: 1rem;
            transition: all 0.3s ease;
        }

        .provider-card:hover .provider-avatar {
            border-color: var(--primary-color);
            transform: scale(1.05);
        }

        .provider-info h5 {
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
            font-size: 1.1rem;
        }

        .provider-info .profession {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 0;
        }

        .provider-address {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.75rem;
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            border-radius: 8px;
            border: 1px solid #d1d5db;
        }

        .provider-address i {
            color: #6b7280;
            margin-right: 0.5rem;
            font-size: 1rem;
        }

        .address-hidden {
            color: #6b7280;
            font-size: 0.9rem;
            font-style: italic;
        }

        .address-visible {
            color: var(--text-primary);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .provider-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1.25rem;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.875rem;
            background: var(--bg-subtle);
            border-radius: 50px;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .stat-item i {
            color: var(--primary-color);
        }

        .provider-location {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .provider-location i {
            color: var(--primary-color);
        }

        .provider-price {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .provider-price i {
            color: #10b981;
        }

        .provider-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .view-profile-btn {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .view-profile-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
            color: white;
        }

        .experience-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.875rem;
            background: rgba(139, 92, 246, 0.1);
            color: var(--accent-color);
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        /* --- تصميم حالة عدم وجود نتائج المحسّنة --- */
        .empty-state-modern {
            border-radius: 24px;
            background: linear-gradient(145deg, #ffffff, #f8fafc);
            padding: 4rem 2rem;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.08);
            text-align: center;
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.8s ease-out;
        }

        /* خلفية زخرفية خفيفة */
        .empty-state-modern::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.03) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* --- الأيقونات التوضيحية --- */
        .empty-state-illustration {
            position: relative;
            height: 120px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .main-icon {
            font-size: 4.5rem;
            color: var(--primary-color);
            opacity: 0.8;
            animation: float 3s ease-in-out infinite;
            z-index: 2;
            position: relative;
        }

        .secondary-icon {
            font-size: 3rem;
            color: var(--secondary-color);
            opacity: 0.4;
            position: absolute;
            bottom: 10px;
            right: calc(50% - 60px);
            animation: float 3s ease-in-out infinite 1s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        /* --- محتوى النص --- */
        .empty-state-content h3 {
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1rem;
            font-size: 1.75rem;
        }

        .empty-state-content p {
            color: var(--text-secondary);
            font-size: 1.05rem;
            max-width: 600px;
            margin: 0 auto 2.5rem;
            line-height: 1.6;
        }

        /* --- زر الإجراء --- */
        .empty-state-actions {
            margin-bottom: 2.5rem;
        }

        .btn-modern {
            padding: 0.875rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            position: relative;
            overflow: hidden;
        }

        .btn-primary-modern {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            border: none;
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.3);
        }

        .btn-primary-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-primary-modern:hover::before {
            left: 100%;
        }

        .btn-primary-modern:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(79, 70, 229, 0.4);
            color: white;
        }

        /* --- الاقتراحات السريعة --- */
        .empty-state-suggestions {
            border-top: 1px solid var(--border-color);
            padding-top: 2rem;
        }

        .suggestion-title {
            color: var(--text-secondary);
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .suggestion-chips {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            padding: 0.625rem 1.25rem;
            background: var(--bg-subtle);
            border: 1px solid var(--border-color);
            border-radius: 50px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .chip:hover {
            background: var(--bg-white);
            border-color: var(--primary-color);
            color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
        }

        /* --- تصميم متجاوب --- */
        @media (max-width: 768px) {
            .hero-filters {
                flex-direction: column;
                align-items: stretch;
            }

            .hero-search {
                min-width: 100%;
            }

            .hero-filter-select {
                min-width: 100%;
            }

            .provider-card {
                margin-bottom: 1.5rem;
            }

            .empty-state-modern {
                padding: 3rem 1.5rem;
            }

            .main-icon {
                font-size: 3.5rem;
            }

            .secondary-icon {
                font-size: 2.5rem;
            }

            .empty-state-content h3 {
                font-size: 1.5rem;
            }

            .suggestion-chips {
                flex-direction: column;
                width: 100%;
            }

            .chip {
                width: 100%;
                justify-content: center;
            }
        }

        /* --- تأثيرات التحميل --- */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .provider-card {
            animation: fadeIn 0.6s ease-out;
        }

        .provider-card:nth-child(1) { animation-delay: 0.1s; }
        .provider-card:nth-child(2) { animation-delay: 0.2s; }
        .provider-card:nth-child(3) { animation-delay: 0.3s; }
        .provider-card:nth-child(4) { animation-delay: 0.4s; }
        .provider-card:nth-child(5) { animation-delay: 0.5s; }
        .provider-card:nth-child(6) { animation-delay: 0.6s; }
    </style>
</head>
<body>
@include('components.main-nav')

<div class="container page-shell">
    <div class="providers-hero">
        <div class="hero-content">
            <div class="hero-actions">
                <div>
                    <p class="text-uppercase text-primary fw-semibold mb-2">{{ __('service_provider.discover_providers') }}</p>
                    <h1 class="mb-2">{{ __('service_provider.service_providers') }}</h1>
                </div>
                <a href="{{ route('home') }}" class="btn btn-outline-primary rounded-pill">
                    <i class="fas fa-arrow-left me-2"></i>{{ __('general.back_to_home') }}
                </a>
            </div>

            <p>{{ __('service_provider.browse_providers_description') }}</p>

            <!-- الفلاتر المدمجة -->
            <div class="hero-filters">
                <div class="hero-search">
                    <i class="fas fa-search hero-search-icon"></i>
                    <input type="text" class="hero-search-input" placeholder="{{ __('service_provider.search_providers') }}" value="{{ request('search') }}">
                </div>

                <select class="hero-filter-select" id="locationFilter">
                    <option value="">{{ __('service_provider.all_locations') }}</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}" {{ request('location') == $location->id ? 'selected' : '' }}>
                            {{ $location->city }}
                        </option>
                    @endforeach
                </select>

                <select class="hero-filter-select" id="categoryFilter">
                    <option value="">{{ __('service_provider.all_categories') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->translated_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="row g-4">
        @forelse($serviceProviders as $provider)
            <div class="col-xl-4 col-lg-4 col-md-6">
                <div class="card provider-card h-100">
                    <div class="card-body">
                        <div class="provider-header">
                            @if($provider->profile_image)
                                <img src="{{ asset('storage/' . $provider->profile_image) }}" alt="{{ $provider->company_name ?? $provider->user->name }}" class="provider-avatar">
                            @else
                                <div class="provider-avatar d-flex align-items-center justify-content-center bg-primary text-white">
                                    <i class="fas fa-user fa-2x"></i>
                                </div>
                            @endif
                            <div class="provider-info">
                                <h5>{{ $provider->company_name ?? $provider->user->name }}</h5>
                                <p class="profession">{{ $provider->category->translated_name ?? __('service_provider.uncategorized') }}</p>
                            </div>
                        </div>

                        {{-- Location (City) --}}
                        @if($provider->location)
                            <div class="provider-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>{{ $provider->location->city }}</span>
                            </div>
                        @endif

                        {{-- Address with hidden numbers --}}
                        <div class="provider-address" data-provider-id="{{ $provider->id }}">
                            <i class="fas fa-map-pin"></i>
                            <span class="address-content address-hidden">
                                @if($provider->address)
                                    {{ preg_replace('/\d/', '*', $provider->address) }}
                                @else
                                    {{ __('service_provider.address_not_provided') }}
                                @endif
                            </span>
                            <span class="address-content address-visible" style="display: none;">
                                {{ $provider->address ?? __('service_provider.address_not_provided') }}
                            </span>
                        </div>

                        <div class="provider-stats">
                            <div class="stat-item">
                                <i class="fas fa-eye"></i>
                                <span>{{ number_format($provider->views) }}</span>
                            </div>
                            @if(auth()->check() && auth()->id() === $provider->user_id && $provider->certification)
                                <div class="stat-item" style="color: #10b981;">
                                    <i class="fas fa-certificate"></i>
                                    <span>{{ __('service_provider.certified') }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="provider-actions">
                            <a href="{{ route('service-providers.show', $provider) }}" class="view-profile-btn">
                                {{ __('service_provider.view_profile') }}
                            </a>
                            @if($provider->experience_years)
                                <div class="experience-badge">
                                    <i class="fas fa-briefcase"></i>
                                    <span>{{ $provider->experience_years }} {{ __('service_provider.years') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="empty-state-modern">
                    <!-- أيقونات توضيحية متحركة -->
                    <div class="empty-state-illustration">
                        <i class="fas fa-search main-icon"></i>
                        <i class="fas fa-users secondary-icon"></i>
                    </div>

                    <!-- المحتوى الرئيسي -->
                    <div class="empty-state-content">
                        <h3>{{ __('service_provider.no_providers_found') }}</h3>
                        <p>{{ __('service_provider.no_providers_description') }}</p>

                        <!-- زر الإجراء الرئيسي -->
                        <div class="empty-state-actions">
                            <button class="btn-modern btn-primary-modern" onclick="resetFilters()">
                                <i class="fas fa-redo me-2"></i>
                                {{ __('service_provider.reset_filters') }}
                            </button>
                        </div>

                        <!-- اقتراحات سريعة للمستخدم -->
                        <div class="empty-state-suggestions">
                            <p class="suggestion-title">{{ __('service_provider.or_try_browsing') }}</p>
                            <div class="suggestion-chips">
                                <a href="{{ route('categories') }}" class="chip">
                                    <i class="fas fa-th-large me-1"></i>
                                    {{ __('categories.popular_categories') }}
                                </a>
                                <a href="{{ route('location') }}" class="chip">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    {{ __('location.nearby_locations') }}
                                </a>
                                <a href="{{ route('home') }}" class="chip">
                                    <i class="fas fa-star me-1"></i>
                                    {{ __('service_provider.top_rated') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if($serviceProviders->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $serviceProviders->links() }}
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.querySelector('.hero-search-input');
        const locationFilter = document.getElementById('locationFilter');
        const categoryFilter = document.getElementById('categoryFilter');

        // دالة تطبيق الفلاتر
        const applyFilters = () => {
            const filters = {
                search: searchInput.value,
                location: locationFilter.value,
                category: categoryFilter.value
            };

            // بناء رابط جديد مع الفلاتر
            const url = new URL(window.location);
            url.searchParams.set('search', filters.search);
            url.searchParams.set('location', filters.location);
            url.searchParams.set('category', filters.category);

            // إعادة التوجيه إلى الرابط الجديد
            window.location.href = url.toString();
        };

        // البحث عند الكتابة مع تأخير
        let searchTimeout;
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(applyFilters, 500);
        });

        // تطبيق الفلاتر عند التغيير
        locationFilter.addEventListener('change', applyFilters);
        categoryFilter.addEventListener('change', applyFilters);

        // دالة إعادة تعيين الفلاتر
        window.resetFilters = () => {
            searchInput.value = '';
            locationFilter.value = '';
            categoryFilter.value = '';
            applyFilters();
        };

        // إضافة تأثير بسيط عند التغيير
        [locationFilter, categoryFilter].forEach(select => {
            select.addEventListener('change', () => {
                select.style.borderColor = 'var(--primary-color)';
                setTimeout(() => {
                    select.style.borderColor = '';
                }, 300);
            });
        });

        // Check if address should be revealed (from session, not localStorage)
        const revealedContacts = @json($revealedContacts ?? []);
        revealedContacts.forEach(providerId => {
            const addressCard = document.querySelector(`[data-provider-id="${providerId}"]`);
            if (addressCard) {
                const hiddenSpan = addressCard.querySelector('.address-hidden');
                const visibleSpan = addressCard.querySelector('.address-visible');
                if (hiddenSpan && visibleSpan) {
                    hiddenSpan.style.display = 'none';
                    visibleSpan.style.display = 'inline';
                }
            }
        });
    });
</script>

@include('layouts.footer')

</body>
</html>
