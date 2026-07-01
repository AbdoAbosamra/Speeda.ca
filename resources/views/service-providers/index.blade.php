@extends('layouts.app')

@push('styles')
    @vite(['resources/css/providers.css'])
@endpush

@section('content')
    <div class="container page-shell">
        <!-- قسم الهيرو المحسّن -->
        <div class="hero-section glass-effect">
            <div class="hero-content">
                <div class="hero-header">
                    <div class="hero-text">
                        <p class="text-uppercase text-primary fw-semibold mb-2">
                            <i class="fas fa-compass me-2"></i>{{ __('service_provider.discover_providers') }}
                        </p>
                        <h1 class="mb-2">{{ __('service_provider.service_providers') }}</h1>
                        <p class="hero-subtitle">{{ __('service_provider.browse_providers_description') }}</p>
                    </div>
                    <a href="{{ route('home') }}" class="btn-back">
                        <i class="fas fa-arrow-left"></i>
                        {{ __('general.back_to_home') }}
                    </a>
                </div>

                <!-- نظام الفلاتر المحسّن -->
                <div class="filters-grid">
                    <div class="search-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" id="searchInput"
                            placeholder="{{ __('service_provider.search_providers') }}" value="{{ request('search') }}">
                    </div>

                    <div class="select-wrapper">
                        {{-- @change 2026-04-12 TASK-3 | Replaced raw location options with two fixed cluster choices |
                        Restrict public filtering to approved metropolitan clusters only | risk:LOW --}}
                        <select class="filter-select" id="locationFilter">
                            <option value="">{{ __('service_provider.all_locations') }}</option>
                            @foreach($locationClusters as $clusterKey => $clusterLabel)
                                <option value="{{ $clusterKey }}" {{ request('location') === $clusterKey ? 'selected' : '' }}>
                                    {{ $clusterLabel }}
                                </option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down select-arrow"></i>
                    </div>

                    <div class="select-wrapper">
                        <select class="filter-select" id="categoryFilter">
                            <option value="">{{ __('service_provider.all_categories') }}</option>
                            @php
                                $regularCategories = ($categories ?? collect([]))->filter(fn($cat) => strtolower($cat->translated_name) !== 'others' && $cat->slug !== 'others');
                                $othersCategory = ($categories ?? collect([]))->first(fn($cat) => strtolower($cat->translated_name) === 'others' || $cat->slug === 'others');
                            @endphp
                            @foreach($regularCategories as $category)
                                <option value="{{ $category->slug }}" {{ request('category') == $category->slug || request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->translated_name }}
                                </option>
                            @endforeach
                            @if($othersCategory)
                                <option value="{{ $othersCategory->slug }}" {{ request('category') == $othersCategory->slug || request('category') == $othersCategory->id ? 'selected' : '' }}>
                                    {{ $othersCategory->translated_name }}
                                </option>
                            @endif
                        </select>
                        <i class="fas fa-chevron-down select-arrow"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- شبكة البطاقات المحسّنة -->
        <div class="providers-grid">
            @forelse($serviceProviders as $provider)
                <div class="provider-card fade-in" data-provider-id="{{ $provider->id }}">
                    <div class="card-header">
                        @if($provider->featured)
                            <x-ui.badge variant="warning" class="provider-badge">
                                <i class="fas fa-crown me-1"></i> {{ __('service_provider.featured') }}
                            </x-ui.badge>
                        @endif

                        <div class="provider-header">
                            <div class="avatar-container">
                                <img src="{{ $provider->display_image_url }}"
                                    alt="{{ $provider->localized_company_name ?? $provider->user->name }}" class="provider-avatar"
                                    loading="lazy" decoding="async"
                                    onerror="this.onerror=null;this.src='{{ $provider->default_image_url }}';">


                            </div>

                            <div class="provider-info">
                                <h3>{{ $provider->localized_company_name ?? $provider->user->name }}</h3>
                                <p class="provider-category">
                                    {{ $provider->category->translated_name ?? __('service_provider.uncategorized') }}
                                </p>
                                <div class="rating-display" data-provider-id="{{ $provider->id }}">
                                    <div class="stars">
                                        @php
                                            // PERFORMANCE: Use cached calculated_rating column (no subquery)
                                            $displayRating = $provider->calculated_rating ?? $provider->rating ?? 0;
                                            $reviewCount = $provider->reviews_count ?? 0;
                                        @endphp
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i
                                                class="fas fa-star {{ $i <= round($displayRating) ? 'text-warning' : 'text-muted' }}"></i>
                                        @endfor
                                    </div>
                                    <span class="rating-score">{{ number_format($displayRating, 1) }}</span>
                                    <span class="reviews-count">({{ $reviewCount }})</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- معلومات الموقع -->
                    <div class="location-section">
                        <div class="location-info" data-provider-id="{{ $provider->id }}">
                            <i class="fas fa-map-marker-alt location-icon"></i>
                            <div class="address-text">
                                @if(($provider->is_available_area ?? false) && !in_array($provider->location_id, $activeLocationIds ?? []))
                                    <x-ui.badge variant="success" class="badge rounded-pill mb-1">
                                        <i class="fas fa-circle-check me-1"></i>{{ __('service_provider.available_in_area') }}
                                    </x-ui.badge>
                                @endif
                                @if($provider->location)
                                    <div class="mb-1 fw-bold text-primary">{{ $provider->location->localized_name }}</div>
                                @endif
                                <span class="address-content hidden-address" style="display: block;">
                                    @if($provider->address)
                                        {{ preg_replace('/\d/', '*', $provider->address) }}
                                    @else
                                        {{ __('service_provider.address_not_provided') }}
                                    @endif
                                </span>
                                <span class="address-content full-address" style="display: none;">
                                    {{ $provider->address ?? __('service_provider.address_not_provided') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- الإحصائيات -->
                    <div class="stats-grid">
                        <div class="stat-item">
                            <i class="fas fa-eye stat-icon"></i>
                            <div class="stat-value">{{ number_format($provider->views) }}</div>
                            <div class="stat-label">{{ __('service_provider.stat_views') }}</div>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-thumbs-up stat-icon"></i>
                            <div class="stat-value" data-endorsements-count="{{ $provider->id }}">
                                {{ $provider->endorsements_count ?? 0 }}
                            </div>
                            <div class="stat-label">{{ __('service_provider.stat_recommends') }}</div>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-briefcase stat-icon"></i>
                            <div class="stat-value">{{ $provider->experience_years ?? '0' }}</div>
                            <div class="stat-label">{{ __('service_provider.stat_years') }}</div>
                        </div>
                    </div>

                    <!-- أزرار التفاعل -->
                    <div class="card-footer">
                        <div class="action-buttons">
                            @if(auth()->check() && auth()->user()->isClient())
                                @php
                                    // PERFORMANCE: Use preloaded is_endorsed attribute (from withExists in controller)
                                    // This avoids N+1 query that would occur with isEndorsedBy() in a loop
                                    $isEndorsed = $provider->is_endorsed ?? false;
                                @endphp
                                <form action="{{ route('endorsements.toggle', $provider->id) }}" method="POST"
                                    style="display: inline;">
                                    @csrf
                                    <button type="submit"
                                        class="btn-action btn-recommend {{ $isEndorsed ? 'recommended' : '' }}">
                                        <i class="{{ $isEndorsed ? 'fas' : 'far' }} fa-thumbs-up"></i>
                                        <span>{{ $isEndorsed ? __('service_provider.recommended') : __('service_provider.recommend') }}</span>
                                    </button>
                                </form>
                            @else
                                <button class="btn-action btn-recommend" disabled>
                                    <i class="far fa-thumbs-up"></i>
                                    <span>{{ __('service_provider.recommend') }}</span>
                                </button>
                            @endif

                            @if(auth()->check())
                                <button class="btn-action btn-rate"
                                    onclick="openRateModal({{ $provider->id }}, '{{ addslashes($provider->localized_company_name ?? $provider->user->name) }}')">
                                    <i class="fas fa-star"></i>
                                    <span>{{ __('service_provider.rate_provider') }}</span>
                                </button>
                            @else
                                <a href="{{ route('register') }}?redirect={{ urlencode(route('reviews.create', $provider->id)) }}"
                                    class="btn-action btn-rate">
                                    <i class="fas fa-star"></i>
                                    <span>{{ __('service_provider.rate_provider') }}</span>
                                </a>
                            @endif
                        </div>

                        <a href="{{ route('service-providers.show', $provider) }}" class="btn-profile">
                            <i class="fas fa-user-circle"></i>
                            {{ __('service_provider.view_full_profile') }}
                        </a>

                        <!-- @if($provider->experience_years)
                                        <div class="experience-badge">
                                            <i class="fas fa-briefcase"></i>
                                            <span>{{ $provider->experience_years }} {{ __('service_provider.years') }} Experience</span>
                                        </div>
                                    @endif -->
                    </div>
                </div>
            @empty
                <!-- حالة عدم وجود نتائج محسّنة -->
                <x-public.empty-state
                    icon="fas fa-users"
                    title="{{ __('service_provider.no_providers_found') }}"
                    description="{{ __('service_provider.no_providers_description') }}"
                >
                    <x-slot:actions>
                        <x-ui.button type="button" icon="fas fa-redo" class="btn-primary" onclick="resetFilters()">
                            {{ __('service_provider.reset_filters') }}
                        </x-ui.button>
                        <x-ui.button :href="route('home')" variant="secondary" icon="fas fa-home" class="btn-back">
                            Return Home
                        </x-ui.button>
                    </x-slot:actions>

                    <div class="suggestions-section">
                        <h3 class="suggestions-title">{{ __('service_provider.or_try_browsing') }}</h3>
                        <div class="suggestions-grid">
                            <a href="{{ route('categories') }}" class="suggestion-card">
                                <div class="suggestion-icon">
                                    <i class="fas fa-th-large"></i>
                                </div>
                                <div class="suggestion-text">{{ __('categories.popular_categories') }}</div>
                            </a>
                            <a href="{{ route('service-providers.index') }}" class="suggestion-card">
                                <div class="suggestion-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="suggestion-text">{{ __('service_provider.discover_providers') }}</div>
                            </a>
                            <a href="{{ route('home') }}" class="suggestion-card">
                                <div class="suggestion-icon">
                                    <i class="fas fa-star"></i>
                                </div>
                                <div class="suggestion-text">{{ __('service_provider.top_rated') }}</div>
                            </a>
                        </div>
                    </div>
                </x-public.empty-state>
            @endforelse
        </div>

        <!-- الترقيم الصفحي الفاخر (Premium Pagination) -->
        @if($serviceProviders->hasPages())
            {{ $serviceProviders->links('components.global-pagination') }}
        @endif
    </div>

    <!-- Modal للتقييم -->
    <div id="rateModal" class="rate-modal">
        <div class="rate-modal-content">
            <div class="rate-modal-header">
                <h2><i class="fas fa-star me-2 text-warning"></i><span id="providerNameInModal"></span></h2>
                <button class="rate-modal-close" onclick="closeRateModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="rate-modal-body">
                <form id="rateForm" method="POST" action="{{ route('reviews.store') }}">
                    @csrf

                    <input type="hidden" id="providerIdInput" name="service_provider_id">

                    <!-- نظام التقييم بالنجوم -->
                    <div class="rating-section">
                        <label class="rating-label">{{ __('reviews.rating') }} <span
                                class="text-danger">*</span></label>
                        <div class="star-rating" id="starRating">
                            @for ($i = 1; $i <= 5; $i++)
                                <input type="radio" id="rating-{{ $i }}" name="rating" value="{{ $i }}" required>
                                <label for="rating-{{ $i }}" class="star-label">
                                    <i class="fas fa-star"></i>
                                </label>
                            @endfor
                        </div>
                        <p class="rating-text" id="ratingText">{{ __('reviews.select_your_rating') }}</p>
                    </div>

                    <!-- حقل التقييم -->
                    <div class="form-group mb-4">
                        <label for="review_text" class="form-label">{{ __('reviews.review_text') }} <span
                                class="text-danger">*</span></label>
                        <textarea id="review_text" name="review_text" class="form-control review-textarea" rows="5"
                            placeholder="{{ __('reviews.review_placeholder') }}" required minlength="10"
                            maxlength="1000"></textarea>
                        <div class="char-count">
                            <span id="charCount">0</span> / 1000
                        </div>
                    </div>

                    <!-- أزرار التصرف -->
                    <div class="rate-modal-actions">
                        <button type="button" class="btn-cancel" onclick="closeRateModal()">
                            <i class="fas fa-times me-2"></i> {{ __('general.cancel') }}
                        </button>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-check me-2"></i> {{ __('reviews.submit_review') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- سكريبتات JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // عناصر الفلاتر
            const searchInput = document.getElementById('searchInput');
            const locationFilter = document.getElementById('locationFilter');
            const categoryFilter = document.getElementById('categoryFilter');

            // مؤقت للبحث
            let searchTimeout;

            // دالة تطبيق الفلاتر
            function applyFilters() {
                const params = new URLSearchParams(window.location.search);
                params.delete('page'); // Reset pagination when filters change

                if (searchInput.value) {
                    params.set('search', searchInput.value);
                } else {
                    params.delete('search');
                }

                if (locationFilter.value) {
                    params.set('location', locationFilter.value);
                } else {
                    params.delete('location');
                }

                if (categoryFilter.value) {
                    params.set('category', categoryFilter.value);
                } else {
                    params.delete('category');
                }

                // تحديث URL وإعادة التحميل
                window.location.href = `${window.location.pathname}?${params.toString()}`;
            }

            // البحث مع تأخير
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(applyFilters, 600);
            });

            // تطبيق الفلاتر عند التغيير
            locationFilter.addEventListener('change', applyFilters);
            categoryFilter.addEventListener('change', applyFilters);

            // تأثير الفلاتر عند التركيز
            [searchInput, locationFilter, categoryFilter].forEach(element => {
                element.addEventListener('focus', function () {
                    this.style.transform = 'scale(1.02)';
                });

                element.addEventListener('blur', function () {
                    this.style.transform = 'scale(1)';
                });
            });

            // إعادة تعيين الفلاتر
            window.resetFilters = function () {
                searchInput.value = '';
                locationFilter.value = '';
                categoryFilter.value = '';
                applyFilters();
            };

            // كشف عناوين مقدمي الخدمات
            const revealedContacts = @json($revealedContacts ?? []);
            revealedContacts.forEach(providerId => {
                const addressContainer = document.querySelector(`.location-info[data-provider-id="${providerId}"]`);
                if (addressContainer) {
                    const hiddenAddress = addressContainer.querySelector('.hidden-address');
                    const fullAddress = addressContainer.querySelector('.full-address');

                    if (hiddenAddress && fullAddress) {
                        hiddenAddress.style.display = 'none';
                        fullAddress.style.display = 'block';
                        addressContainer.classList.add('pulse');
                    }
                }
            });

            // تفعيل تأثير Hover على البطاقات
            const providerCards = document.querySelectorAll('.provider-card');
            providerCards.forEach(card => {
                card.addEventListener('mouseenter', function () {
                    this.style.zIndex = '10';
                });

                card.addEventListener('mouseleave', function () {
                    this.style.zIndex = '1';
                });
            });
        });

        // دوال Modal للتقييم
        document.addEventListener('DOMContentLoaded', function () {
            // ======================
            // دوال التحكم في الـ Modal
            // ======================
            window.openRateModal = function (providerId, providerName) {
                console.log('Opening modal for provider:', providerId, providerName);

                const modal = document.getElementById('rateModal');
                if (!modal) {
                    console.error('Modal element not found');
                    return;
                }

                // تحديث محتوى الـ Modal
                const updates = {
                    'providerNameInModal': el => el.textContent = providerName,
                    'providerIdInput': el => el.value = providerId,
                    'charCount': el => el.textContent = '0',
                    'ratingText': el => el.textContent = 'Select your rating'
                };

                Object.entries(updates).forEach(([id, fn]) => {
                    const el = document.getElementById(id);
                    if (el) fn(el);
                });

                // إعادة تعيين النموذج
                const rateForm = document.getElementById('rateForm');
                if (rateForm) rateForm.reset();

                // تفعيل الـ Modal
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
                console.log('Modal opened successfully');
            };

            window.closeRateModal = function () {
                const modal = document.getElementById('rateModal');
                if (modal) modal.classList.remove('active');
                document.body.style.overflow = 'auto';
            };

            // ======================
            // أحداث إغلاق الـ Modal
            // ======================
            const modal = document.getElementById('rateModal');
            if (modal) {
                // إغلاق عند الضغط خارج الـ Modal
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) window.closeRateModal();
                });

                // إغلاق بـ ESC
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && modal.classList.contains('active')) {
                        window.closeRateModal();
                    }
                });
            }

            // ======================
            // عداد الأحرف
            // ======================
            const reviewText = document.getElementById('review_text');
            const charCount = document.getElementById('charCount');
            if (reviewText && charCount) {
                reviewText.addEventListener('input', () => {
                    charCount.textContent = reviewText.value.length;
                });
            }

            // ======================
            // نظام التقييم بالنجوم
            // ======================
            const starContainers = document.querySelectorAll('.star-rating');
            const ratingTexts = {
                '1': @json(__('reviews.poor')),
                '2': @json(__('reviews.fair')),
                '3': @json(__('reviews.good')),
                '4': @json(__('reviews.very_good')),
                '5': @json(__('reviews.excellent'))
            };

            starContainers.forEach(container => {
                const labels = container.querySelectorAll('.star-label');
                const inputs = container.querySelectorAll('input');
                const ratingDisplay = document.getElementById('ratingText');

                // تحديث العرض عند التغيير
                inputs.forEach(input => {
                    input.addEventListener('change', () => {
                        const value = input.value;
                        // تحديث النجوم النشطة
                        labels.forEach((label, i) => {
                            label.classList.toggle('active', i < value);
                        });
                        // تحديث نص التقييم
                        if (ratingDisplay && ratingTexts[value]) {
                            ratingDisplay.textContent = ratingTexts[value];
                        }
                    });

                    // تأثير التمرير
                    input.addEventListener('mouseenter', () => {
                        const hoverValue = parseInt(input.value);
                        labels.forEach((label, i) => {
                            label.style.color = i < hoverValue ? 'var(--warning)' : 'var(--gray-300)';
                            label.style.transform = i < hoverValue ? 'scale(1.15)' : 'scale(1)';
                        });
                    });
                });

                // إعادة التعيين بعد مغادرة المنطقة
                container.addEventListener('mouseleave', () => {
                    const checked = container.querySelector('input:checked');
                    const currentValue = checked ? parseInt(checked.value) : 0;

                    labels.forEach((label, i) => {
                        label.style.color = i < currentValue ? 'var(--warning)' : 'var(--gray-300)';
                        label.style.transform = 'scale(1)';
                        label.classList.toggle('active', i < currentValue);
                    });
                });
            });

            // ======================
            // معالجة إرسال التقييم
            // ======================
            const rateForm = document.getElementById('rateForm');
            if (rateForm) {
                rateForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const submitBtn = rateForm.querySelector('.btn-submit');
                    if (!submitBtn) return;

                    const originalHTML = submitBtn.innerHTML;
                    const rating = rateForm.querySelector('input[name="rating"]:checked')?.value;
                    const reviewText = rateForm.querySelector('textarea[name="review_text"]')?.value.trim();

                    // التحقق من الصحة
                    if (!rating) {
                        alert('Please select a rating');
                        return;
                    }
                    if (!reviewText || reviewText.length < 10) {
                        alert('Please write a review (minimum 10 characters)');
                        return;
                    }

                    // إعداد الزر للإرسال
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Submitting...';

                    try {
                        // جلب CSRF Token بأمان
                        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                        if (!csrfMeta) throw new Error('CSRF token not found');

                        const response = await fetch(rateForm.action || '{{ route("reviews.store") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfMeta.content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: new URLSearchParams(new FormData(rateForm))
                        });

                        const data = await response.json();

                        if (!response.ok) throw data;
                        if (!data.success) throw { message: data.message || 'Submission failed' };

                        // نجاح الإرسال: إغلاق الـ Modal وتحديث الواجهة
                        window.closeRateModal();
                        updateProviderCard(data.review, document.getElementById('providerIdInput')?.value);
                        showSuccessMessage('Your review has been submitted successfully!');

                    } catch (error) {
                        console.error('Submission error:', error);
                        const errorMsg = extractErrorMessage(error);
                        alert(`Submission failed: ${errorMsg}`);
                    } finally {
                        // استعادة حالة الزر
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalHTML;
                    }
                });
            }

            // ======================
            // دوال مساعدة
            // ======================
            function updateProviderCard(review, providerId) {
                if (!review || !providerId) return;

                document.querySelectorAll(`.provider-card[data-provider-id="${providerId}"]`).forEach(card => {
                    const starsContainer = card.querySelector('.stars');
                    const ratingScore = card.querySelector('.rating-score');
                    const reviewsCount = card.querySelector('.reviews-count');

                    if (starsContainer) {
                        const rating = Math.round(review.rating || 0);
                        starsContainer.innerHTML = Array.from({ length: 5 }, (_, i) =>
                            `<i class="fas fa-star ${i < rating ? 'text-warning' : 'text-muted'}"></i>`
                        ).join('');
                    }

                    if (ratingScore) ratingScore.textContent = (review.rating || 0).toFixed(1);
                    if (reviewsCount) reviewsCount.textContent = `(${review.reviews_count || 0})`;
                });
            }

            function showSuccessMessage(message) {
                const container = document.querySelector('.page-shell') || document.body;
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show';
                alert.style.cssText = 'margin:1rem; position:fixed; top:1rem; right:1rem; z-index:9999;';
                alert.innerHTML = `
            <strong>Success!</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
                container.appendChild(alert);

                // إزالة تلقائية بعد 5 ثوانٍ
                setTimeout(() => {
                    alert.classList.remove('show');
                    alert.classList.add('fade');
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            }

            function extractErrorMessage(error) {
                if (typeof error === 'string') return error;
                if (error?.errors) return Object.values(error.errors).flat().join(', ');
                if (error?.message) return error.message;
                if (error?.statusText) return error.statusText;
                return 'An unexpected error occurred';
            }
        });

        // تأثير تحميل الصفحة
        window.addEventListener('load', function () {
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.5s ease';

            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);
        });
    </script>

@endsection
