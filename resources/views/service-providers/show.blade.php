@extends('layouts.app')

@push('styles')
    @vite(['resources/css/provider-profile.css'])
@endpush

@section('content')
    {{-- Notification Card --}}
    @include('components.notification-card')

    <div class="container mt-4">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}"><i
                            class="fas fa-home me-1"></i>{{ __('general.home') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('service-providers.index') }}"><i
                            class="fas fa-list me-1"></i>{{ __('service_provider.providers_label') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    {{ $serviceProvider->company_name ?? $serviceProvider->user->name }}
                </li>
            </ol>
        </nav>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Unified Error Handler --}}
        <x-error-handler />

        <div class="row">
            <!-- Main Provider Information -->
            <div class="col-lg-8">
                <div class="profile-card">
                    <!-- Profile Header -->
                    <div class="profile-header"></div>

                    <!-- Profile Image -->
                    <div class="profile-image-container" @if(auth()->check() && auth()->id() === $serviceProvider->user_id) id="profileImageClickable" style="cursor: pointer;" title="{{ __('service_provider.click_to_change_image') }}" @endif>
                        <img src="{{ $serviceProvider->display_image_url }}"
                            alt="{{ $serviceProvider->company_name ?? $serviceProvider->user->name }}"
                            class="profile-image" loading="lazy" id="profileImagePreview"
                            onerror="this.onerror=null;this.src='{{ $serviceProvider->default_image_url }}';">
                        {{-- Camera overlay for owner --}}
                        @if(auth()->check() && auth()->id() === $serviceProvider->user_id)
                            <div id="imageOverlay" style="position:absolute;top:0;left:0;width:100%;height:100%;border-radius:50%;background:rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity 0.3s;cursor:pointer;">
                                <i class="fas fa-camera fa-2x text-white"></i>
                            </div>
                            <input type="file" id="profileImageInput" accept="image/jpeg,image/png,image/webp" style="display:none;">
                            {{-- Loading spinner --}}
                            <div id="imageUploadSpinner" style="display:none;position:absolute;top:0;left:0;width:100%;height:100%;border-radius:50%;background:rgba(255,255,255,0.8);align-items:center;justify-content:center;">
                                <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
                            </div>
                        @endif
                    </div>

                    <!-- Profile Content -->
                    <div class="profile-content">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h1 class="fw-bold mb-2">
                                    {{ $serviceProvider->company_name ?? $serviceProvider->user->name }}
                                </h1>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-briefcase me-1"></i>
                                    {{ $serviceProvider->category->translated_name ?? __('service_provider.uncategorized') }}
                                </p>

                                {{-- Languages Spoken --}}
                                @if($serviceProvider->languages && count($serviceProvider->languages) > 0)
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        @foreach($serviceProvider->languages as $langCode)
                                            @php
                                                // Map language codes and full names to translation keys
                                                $langMap = [
                                                    // Language codes
                                                    'ar' => 'arabic',
                                                    'en' => 'english',
                                                    'fr' => 'french',
                                                    // Full names (from old data)
                                                    'English' => 'english',
                                                    'French' => 'french',
                                                    'Arabic' => 'arabic',
                                                ];
                                                $label = $langMap[$langCode] ?? strtolower($langCode);
                                            @endphp
                                            <span class="language-badge">
                                                <i class="fas fa-globe-americas"></i>
                                                {{ __('service_provider.' . $label) }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                {{-- Endorsement Button - visible to all (component handles auth) --}}
                                @if(!auth()->check() || auth()->id() !== $serviceProvider->user_id)
                                    <x-endorsement-button :service-provider="$serviceProvider" />
                                @endif


                            </div>
                        </div>

                        @if(auth()->id() === $serviceProvider->user_id)
                            <!-- Owner-only Edit Section -->

                            {{-- Engagement Popup / Banner --}}
                            <x-profile-completion-popup :serviceProvider="$serviceProvider" />

                            {{-- Profile Completion Progress Bar --}}
                            @php $pct = $serviceProvider->profile_completion_percent ?? 0; @endphp
                            <div class="mb-4 p-3 rounded-4 shadow-sm" style="background: white;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold"><i class="fas fa-tasks text-primary me-2"></i>{{ __('service_provider.profile_completion_title') }}</span>
                                    <span class="badge rounded-pill @if($pct >= 80) bg-success @elseif($pct >= 50) bg-warning text-dark @else bg-danger @endif px-3">{{ $pct }}%</span>
                                </div>
                                <div class="progress" style="height: 10px; border-radius: 5px;" id="completionProgressBar">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $pct }}%; background: linear-gradient(90deg, @if($pct >= 80) #10b981,#059669 @elseif($pct >= 50) #f59e0b,#d97706 @else #ef4444,#dc2626 @endif); border-radius: 5px; transition: width 0.6s ease;" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>



                            <div class="mb-4">
                                <h4 class="fw-bold text-secondary mb-3"><i
                                        class="fas fa-edit me-2"></i>{{ __('service_provider.edit_profile') }}</h4>

                                <form action="{{ route('service-providers.profile.update', $serviceProvider->id) }}"
                                    method="POST" enctype="multipart/form-data" id="profileUpdateForm">
                                    @csrf
                                    @method('PUT')

                                    {{-- Basic Information --}}
                                    <div class="card mb-3 border-0 shadow-sm">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0"><i
                                                    class="fas fa-info-circle me-2"></i>{{ __('service_provider.basic_information') }}
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    <i class="fas fa-building text-primary me-1"></i>
                                                    {{ __('service_provider.company_activity_name') }} <span
                                                        class="text-danger">*</span>
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="fas fa-briefcase text-muted"></i>
                                                    </span>
                                                    <input type="text" name="business_name"
                                                        class="form-control form-control-lg border-start-0"
                                                        value="{{ old('business_name', $serviceProvider->company_name) }}"
                                                        placeholder="مثال: ورشة السلام للسباكة" required>
                                                </div>
                                                @error('business_name')
                                                    <small class="text-danger"><i
                                                            class="fas fa-exclamation-circle me-1"></i>{{ $message }}</small>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    <i class="fas fa-align-left text-primary me-1"></i>
                                                    {{ __('general.description') }}
                                                </label>
                                                <textarea name="bio" class="form-control form-control-lg" rows="5"
                                                    placeholder="{{ __('service_provider.description_hint') }}"
                                                    style="resize: vertical; min-height: 120px;">{{ old('bio', $serviceProvider->bio) }}</textarea>
                                                <small class="text-muted">
                                                    <i class="fas fa-lightbulb text-warning me-1"></i>
                                                    {{ __('service_provider.description_helper') }}
                                                </small>
                                                @error('bio')
                                                    <small class="text-danger d-block mt-1"><i
                                                            class="fas fa-exclamation-circle me-1"></i>{{ $message }}</small>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    <i class="fas fa-award text-primary me-1"></i>
                                                    {{ __('service_provider.experience_years_label') }}
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="fas fa-calendar-check text-muted"></i>
                                                    </span>
                                                    <input type="number" name="experience_years" id="experienceYearsInput"
                                                        class="form-control form-control-lg border-start-0"
                                                        value="{{ old('experience_years', $serviceProvider->experience_years) }}"
                                                        min="0" max="50" placeholder="{{ __('general.example') }}: 5">
                                                    <span class="input-group-text bg-light">
                                                        <span
                                                            class="badge bg-primary">{{ __('service_provider.years') }}</span>
                                                    </span>
                                                </div>
                                                @error('experience_years')
                                                    <small class="text-danger"><i
                                                            class="fas fa-exclamation-circle me-1"></i>{{ $message }}</small>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    <i class="fas fa-language text-primary me-1"></i>
                                                    {{ __('service_provider.languages_spoken') }}
                                                </label>
                                                <div class="row g-2 px-1">
                                                    @foreach(['ar' => 'arabic', 'en' => 'english', 'fr' => 'french'] as $code => $label)
                                                        @php $isChecked = in_array($code, old('languages', $serviceProvider->languages ?? [])); @endphp
                                                        <div class="col-md-4">
                                                            <div class="custom-checkbox-card {{ $isChecked ? 'checked' : '' }}"
                                                                 onclick="const cb = this.querySelector('input'); cb.checked = !cb.checked; this.classList.toggle('checked', cb.checked);">
                                                                <input class="d-none" type="checkbox" name="languages[]"
                                                                    value="{{ $code }}" {{ $isChecked ? 'checked' : '' }}>
                                                                <div class="d-flex align-items-center justify-content-between p-3" style="cursor: pointer;">
                                                                    <span class="fw-semibold">{{ __('service_provider.' . $label) }}</span>
                                                                    <i class="fas fa-check-circle check-icon"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                @error('languages')
                                                    <small class="text-danger d-block mt-1"><i
                                                            class="fas fa-exclamation-circle me-1"></i>{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Contact Information - Category is READ-ONLY --}}
                                    <div class="card mb-3 border-0 shadow-sm">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0"><i
                                                    class="fas fa-phone me-2"></i>{{ __('service_provider.contact_info') }}
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label
                                                    class="form-label fw-bold">{{ __('service_provider.job_specialization') }}</label>

                                                @php
                                                    $othersNames = ['other', 'others', 'أخرى'];
                                                    $isOthersCategory = $serviceProvider->category && (
                                                        in_array(strtolower(trim($serviceProvider->category->name)), $othersNames) ||
                                                        in_array(strtolower(trim($serviceProvider->category->translated_name)), $othersNames)
                                                    );
                                                @endphp

                                                @if($isOthersCategory)
                                                    {{-- EDITABLE DROPDOWN: Only for "Others" category --}}
                                                    <select name="category_id" class="form-control form-control-lg" required>
                                                        <option value="">-- {{ __('service_provider.select_category') }} --
                                                        </option>
                                                        @foreach($categories as $cat)
                                                            <option value="{{ $cat->id }}" {{ old('category_id', $serviceProvider->category_id) == $cat->id ? 'selected' : '' }}>
                                                                {{ collect($cat->hierarchy_labels)->implode(' / ') }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <small class="text-info d-block mt-2">
                                                        <i class="fas fa-check-circle me-1"></i>
                                                        {{ __('service_provider.you_can_change_category') }}
                                                    </small>
                                                @else
                                                    {{-- READ-ONLY TEXT: For locked categories --}}
                                                    <input type="text" class="form-control form-control-lg bg-light"
                                                        value="{{ $serviceProvider->category->translated_name ?? __('service_provider.not_specified') }}"
                                                        disabled readonly>
                                                    <small class="text-warning d-block mt-2">
                                                        <i class="fas fa-lock me-1"></i>
                                                        {{ __('service_provider.category_locked_message') }}
                                                    </small>
                                                @endif
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    <i class="fas fa-phone-alt text-primary me-1"></i>
                                                    {{ __('general.phone') }} <span class="text-danger">*</span>
                                                </label>
                                                <div class="row g-2">
                                                    <div class="col-md-4">
                                                        <div class="whatsapp-country-badge">
                                                            <span class="flag-emoji">🍁</span>
                                                            <span class="country-code">+1</span>
                                                            <span class="country-name">CA</span>
                                                        </div>
                                                        <input type="hidden" name="phone_country_code" value="+1">
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="phone" class="form-control form-control-lg"
                                                            value="{{ old('phone', preg_replace('/^\+1/', '', $serviceProvider->phone)) }}"
                                                            placeholder="6135204877" pattern="[0-9]{10,15}" minlength="10"
                                                            maxlength="15" required>
                                                        <small class="text-muted d-block mt-1">
                                                            <i
                                                                class="fas fa-info-circle me-1"></i>{{ __('service_provider.enter_10_digit_number') }}
                                                        </small>
                                                    </div>
                                                </div>
                                                @error('phone')
                                                    <small class="text-danger d-block mt-1"><i
                                                            class="fas fa-exclamation-circle me-1"></i>{{ $message }}</small>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label
                                                    class="form-label fw-bold">{{ __('service_provider.whatsapp_number') }}
                                                    <span class="text-danger">*</span></label>
                                                <div class="row g-2">
                                                    <div class="col-md-4">
                                                        <div class="whatsapp-country-badge">
                                                            <span class="flag-emoji">🍁</span>
                                                            <span class="country-code">+1</span>
                                                            <span class="country-name">CA</span>
                                                        </div>
                                                        <input type="hidden" name="whatsapp_country_code" value="+1">
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="whatsapp_number"
                                                            class="form-control form-control-lg"
                                                            value="{{ old('whatsapp_number', preg_replace('/^\+1/', '', $serviceProvider->whatsapp_number)) }}"
                                                            placeholder="6135204877" pattern="[0-9]{10,15}" minlength="10"
                                                            maxlength="15" required>
                                                        <small class="text-muted d-block mt-1">
                                                            <i
                                                                class="fas fa-info-circle me-1"></i>{{ __('service_provider.enter_10_digit_number') }}
                                                        </small>
                                                    </div>
                                                </div>
                                                @error('whatsapp_number')
                                                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Location Information --}}
                                    <div class="card mb-3 border-0 shadow-sm">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0"><i
                                                    class="fas fa-map-marker-alt me-2"></i>{{ __('service_provider.location_section') }}
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    <i class="fas fa-map-marked-alt text-primary me-1"></i>
                                                    {{ __('general.location') }}
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="fas fa-city text-info"></i>
                                                    </span>
                                                    <select name="location_id"
                                                        class="form-select form-select-lg border-start-0">
                                                        <option value="">{{ __('general.select_location_placeholder') }}
                                                        </option>
                                                        @foreach($locations ?? [] as $loc)
                                                            <option value="{{ $loc->id }}" {{ $serviceProvider->location_id == $loc->id ? 'selected' : '' }}>
                                                                {{ $loc->city ?? $loc->name ?? __('general.location') . ' ' . $loc->id }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                @error('location_id')
                                                    <small class="text-danger"><i
                                                            class="fas fa-exclamation-circle me-1"></i>{{ $message }}</small>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    <i class="fas fa-map-pin text-primary me-1"></i>
                                                    {{ __('general.address') }}
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="fas fa-location-dot text-danger"></i>
                                                    </span>
                                                    <input type="text" name="address" id="addressInput"
                                                        class="form-control form-control-lg border-start-0"
                                                        value="{{ old('address', $serviceProvider->address) }}"
                                                        placeholder="{{ __('general.example') }}: {{ __('general.address_placeholder') }}">
                                                </div>
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle text-info me-1"></i>
                                                    {{ __('service_provider.address_english_only_hint') }}
                                                </small>
                                                @error('address')
                                                    <small class="text-danger d-block mt-1"><i
                                                            class="fas fa-exclamation-circle me-1"></i>{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Services & Files --}}
                                    <div class="card mb-3 border-0 shadow-sm">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0"><i
                                                    class="fas fa-briefcase me-2"></i>{{ __('service_provider.services_files') }}
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    <i class="fas fa-list-check text-primary me-1"></i>
                                                    {{ __('service_provider.services_provided') }}
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="fas fa-tools text-warning"></i>
                                                    </span>
                                                    <input type="text" name="services_offered"
                                                        class="form-control form-control-lg border-start-0"
                                                        value="{{ old('services_offered', is_array($serviceProvider->services_offered) ? implode(', ', $serviceProvider->services_offered) : $serviceProvider->services_offered) }}"
                                                        placeholder="{{ __('general.example') }}: {{ __('service_provider.services_offered_input_hint') }}">
                                                </div>
                                                <small class="text-muted">
                                                    <i class="fas fa-lightbulb text-warning me-1"></i>
                                                    {{ __('service_provider.separate_services_comma') }}
                                                </small>
                                                @error('services_offered')
                                                    <small class="text-danger d-block mt-1"><i
                                                            class="fas fa-exclamation-circle me-1"></i>{{ $message }}</small>
                                                @enderror
                                            </div>


                                            {{-- Gallery: AJAX per-image management (Inline Alpine Widget) --}}
                                            @php
                                                $galleryMedia = $serviceProvider->getMedia('gallery');
                                                $isOwner = auth()->check() && auth()->id() === $serviceProvider->user_id;
                                                $maxImages = 4;
                                            @endphp

                                            @if($isOwner || $galleryMedia->count() > 0)
                                            <div class="mb-4"
                                                 x-data="galleryManager({
                                                     providerId: {{ $serviceProvider->id }},
                                                     images: {{ Js::from($galleryMedia->map(fn ($m) => [
                                                         'id' => $m->id,
                                                         'thumb_url' => $serviceProvider->getMediaPublicUrl($m, $m->hasGeneratedConversion('gallery_thumb') ? 'gallery_thumb' : null) ?? $m->getUrl(),
                                                     ])->values()) }},
                                                     max: {{ $maxImages }},
                                                     isOwner: {{ $isOwner ? 'true' : 'false' }},
                                                     storeUrl: '{{ route('provider.gallery.store', $serviceProvider->id) }}',
                                                     csrfToken: '{{ csrf_token() }}'
                                                 })">

                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <label class="form-label fw-bold mb-0">
                                                        <i class="fas fa-images text-primary me-1"></i>
                                                        {{ __('service_provider.gallery_upload_title') ?? 'Gallery' }}
                                                    </label>
                                                    <span class="badge bg-primary text-white" x-text="images.length + ' / ' + max + ' photos'"></span>
                                                </div>

                                                <div x-show="images.length >= max" class="text-secondary small mb-3">
                                                    <i class="fas fa-info-circle me-1"></i> Maximum 4 photos reached.
                                                </div>

                                                {{-- Inline Flash Errors --}}
                                                <div x-show="errorMessage" x-transition style="display: none;">
                                                    <div class="alert alert-danger p-2 small mb-3 rounded d-flex align-items-center">
                                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                                        <span x-text="errorMessage"></span>
                                                        <button type="button" class="btn-close ms-auto" @click="errorMessage = ''" style="padding: 0.5rem;"></button>
                                                    </div>
                                                </div>

                                                <div class="row row-cols-2 row-cols-md-4 g-3">
                                                    {{-- Existing Images --}}
                                                    <template x-for="(img, index) in images" :key="img.id">
                                                        <div class="col">
                                                            <div class="gallery-cell position-relative overflow-hidden"
                                                                 style="aspect-ratio: 1/1; border-radius: 12px; border: 1px solid #e1e5eb; background: #f8f9fa;"
                                                                 @mouseenter="if(isOwner && confirmId !== img.id) hoverId = img.id"
                                                                 @mouseleave="hoverId = null">

                                                                {{-- Delete Confirmation State --}}
                                                                <div x-show="confirmId === img.id"
                                                                     class="position-absolute top-0 start-0 w-100 h-100"
                                                                     style="background: rgba(220,53,69,0.15); z-index: 5; display: none;">
                                                                    <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center">
                                                                        <span class="text-danger fw-bold mb-2 small text-center px-1">Delete this photo?</span>
                                                                        <div class="d-flex gap-2">
                                                                            <button type="button" class="btn btn-sm btn-danger rounded-circle" @click="executeDelete(img)" title="Confirm" style="width: 32px; height: 32px; padding: 0;"><i class="fas fa-check"></i></button>
                                                                            <button type="button" class="btn btn-sm btn-secondary rounded-circle" @click="confirmId = null; hoverId = null" title="Cancel" style="width: 32px; height: 32px; padding: 0;"><i class="fas fa-times"></i></button>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                {{-- Normal State: Image --}}
                                                                <img :src="img.thumb_url" alt="Gallery Photo" class="w-100 h-100 object-fit-cover" x-show="confirmId !== img.id" loading="lazy">

                                                                {{-- Loading Spinner --}}
                                                                <div x-show="uploadingId === img.id" class="position-absolute top-0 start-0 w-100 h-100" style="background: rgba(255,255,255,0.7); z-index: 10; display: none;">
                                                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center">
                                                                        <div class="spinner-border text-primary" role="status"></div>
                                                                    </div>
                                                                </div>

                                                                {{-- Hover Actions (Owner Only) --}}
                                                                <div x-show="isOwner && hoverId === img.id && confirmId !== img.id && uploadingId !== img.id"
                                                                     x-transition.opacity.duration.200ms
                                                                     class="position-absolute top-0 start-0 w-100 h-100"
                                                                     style="background: rgba(0,0,0,0.55); z-index: 2; display: none;">
                                                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center gap-2">
                                                                        <button type="button" class="btn btn-outline-light rounded-circle border-0" @click="startReplace(img)" title="Replace" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: transparent;">
                                                                            <i class="fas fa-pencil-alt"></i>
                                                                        </button>
                                                                        <button type="button" class="btn btn-outline-light rounded-circle border-0" @click="confirmId = img.id" title="Delete" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: transparent;">
                                                                            <i class="fas fa-trash-alt"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>

                                                    {{-- Upload Previews during add --}}
                                                    <template x-for="(preview, index) in addPreviews" :key="'preview-'+index">
                                                        <div class="col">
                                                            <div class="gallery-cell position-relative overflow-hidden" style="aspect-ratio: 1/1; border-radius: 12px; border: 1px solid #e1e5eb;">
                                                                <img :src="preview" class="w-100 h-100 object-fit-cover" style="opacity: 0.6;">
                                                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                                                                    <div class="spinner-border text-primary" role="status"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>

                                                    {{-- Add Placeholders (to fill up to 4 cells) --}}
                                                    <template x-if="isOwner && (images.length + addPreviews.length) < max">
                                                        <template x-for="i in (max - images.length - addPreviews.length)" :key="'placeholder-'+i">
                                                            <div class="col">
                                                                <div @click="$refs.galleryAddInput.click()"
                                                                     class="gallery-cell position-relative d-flex bg-light align-items-center justify-content-center"
                                                                     style="aspect-ratio: 1/1; border-radius: 12px; border: 2px dashed #cbd5e1; cursor: pointer; transition: all 0.2s;">
                                                                    <i class="fas fa-plus text-secondary fa-2x opacity-50"></i>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </template>
                                                </div>

                                                {{-- Hidden Inputs --}}
                                                <input type="file" x-ref="galleryAddInput" class="d-none" accept="image/jpeg,image/png,image/webp" @change="handleAddFile">
                                                <input type="file" x-ref="galleryReplaceInput" class="d-none" accept="image/jpeg,image/png,image/webp" @change="handleReplaceFile">
                                            </div>

                                            <script>
                                                document.addEventListener('alpine:init', () => {
                                                    Alpine.data('galleryManager', (config) => ({
                                                        providerId: config.providerId,
                                                        images: config.images,
                                                        max: config.max,
                                                        isOwner: config.isOwner,
                                                        storeUrl: config.storeUrl,
                                                        csrfToken: config.csrfToken,

                                                        hoverId: null,
                                                        confirmId: null,
                                                        uploadingId: null,
                                                        pendingReplaceImg: null,
                                                        addPreviews: [],
                                                        errorMessage: '',

                                                        startReplace(img) {
                                                            this.pendingReplaceImg = img;
                                                            this.$refs.galleryReplaceInput.value = '';
                                                            this.$refs.galleryReplaceInput.click();
                                                        },

                                                        validateFile(file) {
                                                            const allowed = ['image/jpeg', 'image/png', 'image/webp'];
                                                            if (!allowed.includes(file.type)) {
                                                                this.errorMessage = 'Please select a JPG, PNG, or WebP image.';
                                                                return false;
                                                            }
                                                            if (file.size > 5 * 1024 * 1024) {
                                                                this.errorMessage = 'File is too large. Maximum size is 5MB.';
                                                                return false;
                                                            }
                                                            return true;
                                                        },

                                                        async handleAddFile(e) {
                                                            const file = e.target.files[0];
                                                            if (!file) return;
                                                            if (!this.validateFile(file)) return;
                                                            this.errorMessage = '';

                                                            // Generate Preview
                                                            const reader = new FileReader();
                                                            reader.onload = (e) => {
                                                                this.addPreviews.push(e.target.result);
                                                                // Call upload immediately after preview loads
                                                                this.uploadNewImage(file, e.target.result);
                                                            };
                                                            reader.readAsDataURL(file);
                                                        },

                                                        async uploadNewImage(file, previewData) {
                                                            const formData = new FormData();
                                                            formData.append('gallery_image', file);

                                                            try {
                                                                const res = await fetch(this.storeUrl, {
                                                                    method: 'POST',
                                                                    body: formData,
                                                                    headers: {
                                                                        'X-CSRF-TOKEN': this.csrfToken,
                                                                        'Accept': 'application/json'
                                                                    }
                                                                });
                                                                const data = await res.json();
                                                                if (data.success) {
                                                                    this.images.push(data.media);
                                                                } else {
                                                                    this.errorMessage = data.message || 'Upload failed.';
                                                                }
                                                            } catch (err) {
                                                                this.errorMessage = 'Network error during upload.';
                                                            } finally {
                                                                // Remove this specific preview slot
                                                                this.addPreviews = this.addPreviews.filter(p => p !== previewData);
                                                                this.$refs.galleryAddInput.value = '';
                                                            }
                                                        },

                                                        async handleReplaceFile(e) {
                                                            const file = e.target.files[0];
                                                            if (!file || !this.pendingReplaceImg) return;
                                                            if (!this.validateFile(file)) return;
                                                            this.errorMessage = '';

                                                            this.uploadingId = this.pendingReplaceImg.id;

                                                            // Preview immediately
                                                            const reader = new FileReader();
                                                            reader.onload = (ev) => {
                                                                const idx = this.images.findIndex(i => i.id === this.pendingReplaceImg.id);
                                                                if(idx !== -1) this.images[idx].thumb_url = ev.target.result;
                                                            };
                                                            reader.readAsDataURL(file);

                                                            const formData = new FormData();
                                                            formData.append('gallery_image', file);
                                                            const updateUrl = `/service-providers/profile/${this.providerId}/gallery/${this.pendingReplaceImg.id}/replace`;

                                                            try {
                                                                const res = await fetch(updateUrl, {
                                                                    method: 'POST',
                                                                    body: formData,
                                                                    headers: {
                                                                        'X-CSRF-TOKEN': this.csrfToken,
                                                                        'Accept': 'application/json'
                                                                    }
                                                                });
                                                                const data = await res.json();
                                                                if (data.success) {
                                                                    // Update image details from server
                                                                    const idx = this.images.findIndex(i => i.id === this.pendingReplaceImg.id);
                                                                    if(idx !== -1) {
                                                                        this.images[idx].id = data.media.id;
                                                                        this.images[idx].thumb_url = data.media.thumb_url;
                                                                    }
                                                                } else {
                                                                    this.errorMessage = data.message || 'Replace failed.';
                                                                }
                                                            } catch (err) {
                                                                this.errorMessage = 'Network error during replace.';
                                                            } finally {
                                                                this.uploadingId = null;
                                                                this.pendingReplaceImg = null;
                                                                this.$refs.galleryReplaceInput.value = '';
                                                                this.hoverId = null;
                                                            }
                                                        },

                                                        async executeDelete(img) {
                                                            this.uploadingId = img.id;
                                                            this.confirmId = null;
                                                            const deleteUrl = `/service-providers/profile/${this.providerId}/gallery/${img.id}/ajax`;

                                                            try {
                                                                const res = await fetch(deleteUrl, {
                                                                    method: 'DELETE',
                                                                    headers: {
                                                                        'X-CSRF-TOKEN': this.csrfToken,
                                                                        'Accept': 'application/json'
                                                                    }
                                                                });
                                                                const data = await res.json();
                                                                if (data.success) {
                                                                    this.images = this.images.filter(i => i.id !== img.id);
                                                                } else {
                                                                    this.errorMessage = data.message || 'Delete failed.';
                                                                }
                                                            } catch (err) {
                                                                this.errorMessage = 'Network error during delete.';
                                                            } finally {
                                                                this.uploadingId = null;
                                                                this.hoverId = null;
                                                            }
                                                        }
                                                    }));
                                                });
                                            </script>
                                            <style>
                                                .gallery-cell:hover { border-color: #6c757d !important; }
                                            </style>
                                            @endif
                                            {{-- End Gallery Inline Widget --}}


                                        </div>
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="card border-0 shadow-sm bg-light">
                                        <div class="card-body">
                                            <div class="d-flex gap-3 justify-content-between align-items-center flex-wrap">
                                                <div>
                                                    <h6 class="mb-1 fw-bold text-dark">
                                                        <i class="fas fa-check-circle text-success me-2"></i>
                                                        {{ __('service_provider.ready_to_save') }}
                                                    </h6>
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        {{ __('service_provider.verify_info_before_save') }}
                                                    </small>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('service-providers.show', $serviceProvider->id) }}"
                                                        class="btn btn-outline-secondary btn-lg px-4"
                                                        style="border-radius: 12px;">
                                                        <i class="fas fa-times-circle me-2"></i>{{ __('general.cancel') }}
                                                    </a>
                                                    <button type="submit" class="btn btn-primary btn-lg px-5"
                                                        style="border-radius: 12px; box-shadow: 0 4px 15px rgba(67, 97, 238, 0.4);">
                                                        <i class="fas fa-save me-2"></i>{{ __('general.save_changes') }}
                                                        <i class="fas fa-arrow-left ms-2"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                                {{-- Old per-image management replaced by AJAX gallery grid above --}}
                            </div>
                        @endif

                        <!-- Business Description -->
                        <div class="mb-4">
                            <h4 class="fw-bold text-primary mb-3">
                                <i class="fas fa-info-circle me-2"></i>{{ __('service_provider.about_us') }}
                            </h4>
                            <p class="fs-6">{{ $serviceProvider->bio ?? __('service_provider.no_description') }}</p>
                        </div>

                        @if($galleryImages->count() > 0)
                            {{-- @change 2026-04-12 TASK-1 | Added read-only public gallery section with Alpine lightbox | Let visitors browse provider gallery without edit actions | risk:LOW --}}
                            <div class="mb-4"
                                x-data="{
                                    items: {{ Js::from($galleryImages->values()) }},
                                    isOpen: false,
                                    activeIndex: 0,
                                    openLightbox(index) {
                                        this.activeIndex = index;
                                        this.isOpen = true;
                                        document.body.classList.add('overflow-hidden');
                                    },
                                    closeLightbox() {
                                        this.isOpen = false;
                                        document.body.classList.remove('overflow-hidden');
                                    },
                                    nextImage() {
                                        this.activeIndex = (this.activeIndex + 1) % this.items.length;
                                    },
                                    previousImage() {
                                        this.activeIndex = (this.activeIndex - 1 + this.items.length) % this.items.length;
                                    }
                                }"
                                x-on:keydown.escape.window="if (isOpen) closeLightbox()"
                                x-on:keydown.arrow-right.window="if (isOpen) nextImage()"
                                x-on:keydown.arrow-left.window="if (isOpen) previousImage()">
                                <h4 class="fw-bold text-primary mb-3">
                                    <i class="fas fa-images me-2"></i>{{ __('service_provider.gallery_upload_title') ?? 'Gallery' }}
                                </h4>

                                <div class="public-gallery-grid">
                                    @foreach($galleryImages as $galleryIndex => $galleryImage)
                                        <button type="button" class="public-gallery-card"
                                            @click="openLightbox({{ $galleryIndex }})">
                                            <img src="{{ $galleryImage['thumb_url'] }}"
                                                alt="{{ __('service_provider.gallery_image_alt') }}"
                                                loading="lazy">
                                        </button>
                                    @endforeach
                                </div>

                                <template x-if="isOpen">
                                    <div class="public-gallery-lightbox" @click.self="closeLightbox()">
                                        <button type="button" class="public-gallery-close" @click="closeLightbox()"
                                            aria-label="{{ __('general.close') }}">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <button type="button" class="public-gallery-nav prev" @click="previousImage()"
                                            aria-label="{{ __('general.previous') }}">
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                        <img :src="items[activeIndex].large_url"
                                            alt="{{ __('service_provider.gallery_image_alt') }}">
                                        <button type="button" class="public-gallery-nav next" @click="nextImage()"
                                            aria-label="{{ __('general.next') }}">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                        <div class="public-gallery-counter">
                                            <span x-text="`${activeIndex + 1} / ${items.length}`"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        @endif

                        <!-- Services Offered -->
                        <div class="mb-4">
                            <h4 class="fw-bold text-primary mb-3">
                                <i
                                    class="fas fa-list-check me-2"></i>{{ __('service_provider.services_offered_title') }}
                            </h4>
                            @php
                                $services = $serviceProvider->services_offered;
                                if (is_string($services)) {
                                    $services = json_decode($services, true) ?? explode(',', $services);
                                }
                                $services = is_array($services) ? array_filter(array_map('trim', $services)) : [];
                            @endphp
                            @if(!empty($services))
                                <div class="d-flex flex-wrap">
                                    @foreach ($services as $service)
                                        <span class="service-badge">
                                            <i class="fas fa-check-circle"></i>{{ $service }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">{{ __('service_provider.no_services_listed') }}</p>
                            @endif
                        </div>


                        <!-- Reviews Section -->
                        <div class="mt-5">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="fw-bold text-primary">
                                    <i class="fas fa-star me-2"></i>{{ __('service_provider.customer_reviews_title') }}
                                </h4>
                                @if(auth()->check() && auth()->user()->isClient() && !$hasReviewed)
                                    <button class="btn btn-primary" onclick="openReviewModal()">
                                        <i class="fas fa-pen me-2"></i>{{ __('reviews.write_review') }}
                                    </button>
                                @endif
                            </div>

                            @if($reviewStats['total_count'] > 0)
                                <!-- Rating Summary -->
                                <div class="card border-0 shadow-sm mb-4"
                                    style="border-radius: 16px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                    <div class="card-body p-4">
                                        <div class="row align-items-center">
                                            <div class="col-md-4 text-center">
                                                <div class="rating-big">
                                                    <span
                                                        class="display-3 fw-bold text-primary">{{ number_format($reviewStats['average_rating'], 1) }}</span>
                                                    <div class="stars-large mt-2"
                                                        style="font-size: 1.5rem; color: #f59e0b;">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            @if($i <= round($reviewStats['average_rating']))
                                                                <i class="fas fa-star"></i>
                                                            @else
                                                                <i class="far fa-star"></i>
                                                            @endif
                                                        @endfor
                                                    </div>
                                                    <p class="text-muted mt-2">{{ $reviewStats['total_count'] }}
                                                        {{ __('reviews.reviews_total') }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                @foreach([5, 4, 3, 2, 1] as $star)
                                                    @php
                                                        $count = $reviewStats[$star . '_star'] ?? 0;
                                                        $breakdown = $reviewStats['breakdown'][$star] ?? ['count' => 0, 'percentage' => 0];
                                                        $percentage = $breakdown['percentage'] ?? 0;
                                                    @endphp
                                                    <div class="rating-bar d-flex align-items-center mb-2">
                                                        <span class="me-2" style="min-width: 20px;">{{ $star }}</span>
                                                        <i class="fas fa-star text-warning me-2"
                                                            style="font-size: 0.75rem;"></i>
                                                        <div class="progress flex-grow-1" style="height: 8px;">
                                                            <div class="progress-bar bg-warning" role="progressbar"
                                                                aria-valuenow="{{ $percentage }}" aria-valuemin="0"
                                                                aria-valuemax="100" style="width: {{ $percentage }}%"></div>
                                                        </div>
                                                        <span class="ms-2 text-muted"
                                                            style="min-width: 40px; font-size: 0.875rem;">{{ $count }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reviews List -->
                                <div class="reviews-container">
                                    @foreach($reviews as $review)
                                        <div class="review-card"
                                            style="background: white; border-radius: 16px; padding: 1.5rem; margin-bottom: 1rem; box-shadow: 0 2px 12px rgba(0,0,0,0.06);">
                                            <div class="review-header"
                                                style="display: flex; align-items: center; margin-bottom: 1rem;">
                                                <div class="review-avatar"
                                                    style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; margin-right: 1rem;">
                                                    {{ strtoupper(substr($review->client->name ?? 'U', 0, 1)) }}
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 fw-bold">
                                                        {{ $review->client->name ?? __('reviews.anonymous') }}
                                                    </h6>
                                                    <div class="review-rating" style="color: #f59e0b;">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            @if($i <= $review->rating)
                                                                <i class="fas fa-star"></i>
                                                            @else
                                                                <i class="far fa-star"></i>
                                                            @endif
                                                        @endfor
                                                        <span class="text-muted ms-2"
                                                            style="font-size: 0.875rem;">{{ $review->created_at->diffForHumans() }}</span>
                                                    </div>
                                                </div>
                                                @if($review->is_featured)
                                                    <span class="badge bg-warning"
                                                        style="background: linear-gradient(135deg, #f59e0b, #fbbf24);">
                                                        <i class="fas fa-thumbs-up me-1"></i>{{ __('reviews.featured') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="mb-0" style="color: #4b5563; line-height: 1.6;">{{ $review->review_text }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Pagination -->
                                @if($reviews->hasPages())
                                    <div class="d-flex justify-content-center mt-4">
                                        {{ $reviews->links() }}
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-5"
                                    style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 16px;">
                                    <i class="fas fa-comment-slash fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">{{ __('reviews.no_reviews_yet') }}</p>
                                    @if(auth()->check() && auth()->user()->isClient())
                                        <button class="btn btn-primary mt-2" onclick="openReviewModal()">
                                            <i class="fas fa-pen me-2"></i>{{ __('reviews.be_first_review') }}
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information Sidebar -->
            <div class="col-lg-4">
                <div class="contact-card mb-4">
                    <div class="p-4">
                        <h4 class="fw-bold text-primary mb-4 text-center">
                            <i class="fas fa-address-card me-2"></i>{{ __('service_provider.contact_information') }}
                        </h4>

                        <!-- WhatsApp Number (Hidden until button click) -->
                        @php
                            $whatsappDisplay = $serviceProvider->whatsapp_number ?? $serviceProvider->phone;
                            if ($isContactRevealed) {
                                // Show full number if already revealed
                                $displayWhatsapp = $whatsappDisplay;
                                $whatsappClass = 'text-success fw-bold';
                            } else {
                                // Hide last 3 digits if not revealed
                                if (strlen($whatsappDisplay) > 3) {
                                    $displayWhatsapp = substr($whatsappDisplay, 0, -3) . '***';
                                } else {
                                    $displayWhatsapp = '***';
                                }
                                $whatsappClass = 'text-muted';
                            }
                        @endphp
                        <div class="contact-item">
                            <div class="d-flex align-items-center">
                                <div class="contact-icon"
                                    style="background: linear-gradient(135deg, #25D366, #128C7E);">
                                    <i class="fab fa-whatsapp"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold">{{ __('service_provider.whatsapp_number') }}</h6>
                                    <span id="whatsappNumber" class="{{ $whatsappClass }}">{{ $displayWhatsapp }}</span>
                                    @if(!$isContactRevealed)
                                        <small class="d-block text-muted" style="font-size: 0.75rem;"><i
                                                class="fas fa-lock me-1"></i>{{ __('service_provider.contact_reveal_hint') }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="contact-item">
                            <div class="d-flex align-items-center">
                                <div class="contact-icon location-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold">{{ __('general.location') }}</h6>
                                    <p class="mb-0">
                                        {{ $serviceProvider->location->city ?? __('service_provider.location_not_specified') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Address (Hidden numbers until WhatsApp button clicked) -->
                        @if($serviceProvider->address)
                            <div class="contact-item">
                                <div class="d-flex align-items-center">
                                    <div class="contact-icon"
                                        style="background: linear-gradient(135deg, #6366f1, #4f46e5);">
                                        <i class="fas fa-map-pin"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">{{ __('general.address') }}</h6>
                                        @php
                                            if ($isContactRevealed) {
                                                // Show full address if already revealed
                                                $displayAddress = $serviceProvider->address;
                                                $addressClass = 'text-primary fw-bold';
                                            } else {
                                                // Hide all numbers in address
                                                $displayAddress = preg_replace('/\d/', '*', $serviceProvider->address);
                                                $addressClass = '';
                                            }
                                        @endphp
                                        <p class="mb-0 small {{ $addressClass }}" id="addressText">{{ $displayAddress }}</p>
                                        @if(!$isContactRevealed)
                                            <small class="text-muted" style="font-size: 0.7rem;"><i
                                                    class="fas fa-lock me-1"></i>{{ __('service_provider.address_reveal_hint') }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Email -->
                        <div class="contact-item">
                            <div class="d-flex align-items-center">
                                <div class="contact-icon email-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold">{{ __('general.email_address') }}
                                        <a href="mailto:{{ $serviceProvider->user->email }}"
                                            class="text-decoration-none">
                                            {{ $serviceProvider->user->email }}
                                        </a>
                                    </h6>
                                </div>
                            </div>
                        </div>

                        {{-- Business Views: keep public, hide from the provider owner --}}
                        @if(!auth()->check() || auth()->id() !== $serviceProvider->user_id)
                            <div class="contact-item">
                                <div class="d-flex align-items-center">
                                    <div class="contact-icon hours-icon">
                                        <i class="fas fa-eye"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 fw-bold">{{ __('service_provider.profile_views') }}</h6>
                                        <p class="mb-0">{{ number_format($serviceProvider->views) }}
                                            {{ __('service_provider.views_label') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Quick Action Buttons -->
                    <div class="p-4 bg-light">
                        @php
                            $whatsappNumber = $serviceProvider->whatsapp_number ?? $serviceProvider->phone;
                            // Clean number - remove all non-digit and non-plus characters
                            $whatsappNumber = preg_replace('/[^0-9+]/', '', $whatsappNumber);

                            // Ensure number starts with +
                            if (!str_starts_with($whatsappNumber, '+')) {
                                // Check if it's a Canadian number (starts with 1) or Egyptian (starts with 0 or direct digits)
                                if (str_starts_with($whatsappNumber, '1') && strlen($whatsappNumber) == 11) {
                                    // Canadian number
                                    $whatsappNumber = '+' . $whatsappNumber;
                                } elseif (str_starts_with($whatsappNumber, '0')) {
                                    // Egyptian number starting with 0
                                    $whatsappNumber = '+20' . ltrim($whatsappNumber, '0');
                                } else {
                                    // Assume Egyptian if no country code
                                    $whatsappNumber = '+20' . $whatsappNumber;
                                }
                            }

                            // Clean version for API (no +)
                            $whatsappNumberClean = str_replace('+', '', $whatsappNumber);
                        @endphp

                        @if(!empty($whatsappNumberClean))
                            {{-- WhatsApp Button (tracks analytics then opens WhatsApp) --}}
                            <button
                                onclick="revealContactInfo('{{ $whatsappNumberClean }}', '{{ $serviceProvider->whatsapp_number ?? $serviceProvider->phone }}', '{{ $serviceProvider->address ?? '' }}')"
                                class="btn w-100 mb-3"
                                style="background: linear-gradient(135deg, #25D366, #128C7E); border: none; border-radius: 50px; padding: 0.75rem 2rem; font-weight: 600; color: white; box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);">
                                <i class="fab fa-whatsapp me-2"></i> {{ __('service_provider.contact_whatsapp') }}
                            </button>
                        @endif

                        <a href="mailto:{{ $serviceProvider->user->email }}" class="btn btn-outline-primary w-100"
                            id="emailContactBtn">
                            <i class="fas fa-envelope me-2"></i> {{ __('service_provider.send_email') }}
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <!-- Similar Providers Section -->
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="fw-bold mb-4 text-primary">
                    <i class="fas fa-users me-2"></i>{{ __('service_provider.similar_providers') }}
                </h3>

                @if($similarProviders->count())
                    <div class="row">
                        @foreach ($similarProviders as $similar)
                            <div class="col-md-3 mb-4">
                                <div class="similar-provider-card">
                                    <div class="similar-provider-image">
                                        <img src="{{ $similar->display_image_url }}"
                                            alt="{{ $similar->company_name ?? $similar->user->name }}" loading="lazy" decoding="async"
                                            onerror="this.onerror=null;this.src='{{ $similar->default_image_url }}';">
                                    </div>
                                    <div class="similar-provider-content">
                                        <h6 class="fw-bold mb-2">{{ $similar->company_name ?? $similar->user->name }}</h6>
                                        <p class="text-muted small mb-3">{{ Str::limit($similar->bio ?? '', 60) }}</p>

                                        <!-- Display category and rating -->
                                        <div class="mb-3">
                                            <span class="badge bg-primary small">
                                                <i class="fas fa-briefcase me-1"></i>
                                                {{ $similar->category->translated_name ?? __('service_provider.uncategorized') }}
                                            </span>
                                            @if($similar->rating > 0)
                                                <span class="badge bg-warning text-dark small">
                                                    <i class="fas fa-star"></i> {{ number_format($similar->rating, 1) }}
                                                </span>
                                            @endif
                                        </div>

                                        <a href="{{ route('service-providers.show', $similar->id) }}"
                                            class="btn btn-outline-primary btn-sm rounded-pill w-100"
                                            style="transition: var(--transition);"
                                            onmouseover="this.style.transform='translateY(-2px)'"
                                            onmouseout="this.style.transform='translateY(0)'">
                                            <i class="fas fa-eye me-1"></i> {{ __('service_provider.view_profile') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-info-circle fa-2x text-muted mb-3"></i>
                        <p class="text-muted">{{ __('service_provider.no_similar_providers') }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Back Button -->
        <div class="text-center mt-4 mb-5">
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fas fa-arrow-left me-2"></i> {{ __('general.back') }}
            </a>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">{{ __('service_provider.gallery_image_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="{{ $serviceProvider->default_image_url }}" alt="{{ __('service_provider.gallery_image_alt') }}" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <!-- Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; border: none;">
                <div class="modal-header border-0"
                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="modal-title text-white fw-bold" id="reviewModalLabel">
                        <i class="fas fa-star me-2"></i>{{ __('reviews.write_review') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="reviewForm" action="{{ route('reviews.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="service_provider_id" value="{{ $serviceProvider->id }}">
                    <div class="modal-body p-4">
                        <!-- Rating -->
                        <div class="mb-4 text-center">
                            <label class="form-label fw-bold d-block mb-3">{{ __('reviews.your_rating') }}</label>
                            <div class="star-rating-input" style="font-size: 2rem; color: #d1d5db; cursor: pointer;">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star rating-star" data-rating="{{ $i }}"
                                        onclick="setRating({{ $i }})"></i>
                                @endfor
                            </div>
                            <input type="hidden" name="rating" id="ratingInput" value="5" required>
                            <small class="text-muted rating-text">{{ __('reviews.excellent') }}</small>
                        </div>

                        <!-- Review Text -->
                        <div class="mb-3">
                            <label for="reviewText" class="form-label fw-bold">{{ __('reviews.your_review') }}</label>
                            <textarea class="form-control" id="reviewText" name="review_text" rows="4"
                                placeholder="{{ __('reviews.review_placeholder') }}" required minlength="10"
                                maxlength="1000" style="border-radius: 12px; resize: none;"></textarea>
                            <small class="text-muted char-count">0 / 1000</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            {{ __('general.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary"
                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                            <i class="fas fa-paper-plane me-2"></i>{{ __('reviews.submit_review') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <script>
        // Review Modal Functions
        function openReviewModal() {
            const modal = new bootstrap.Modal(document.getElementById('reviewModal'));
            modal.show();
        }

        function setRating(rating) {
            document.getElementById('ratingInput').value = rating;
            const stars = document.querySelectorAll('.rating-star');
            const ratingTexts = {
                1: '{{ __('reviews.poor') }}',
                2: '{{ __('reviews.fair') }}',
                3: '{{ __('reviews.good') }}',
                4: '{{ __('reviews.very_good') }}',
                5: '{{ __('reviews.excellent') }}'
            };

            stars.forEach((star, index) => {
                if (index < rating) {
                    star.style.color = '#f59e0b';
                    star.classList.remove('far');
                    star.classList.add('fas');
                } else {
                    star.style.color = '#d1d5db';
                    star.classList.remove('fas');
                    star.classList.add('far');
                }
            });

            document.querySelector('.rating-text').textContent = ratingTexts[rating];
        }

        // Character counter for review
        document.addEventListener('DOMContentLoaded', function () {
            const reviewText = document.getElementById('reviewText');
            if (reviewText) {
                reviewText.addEventListener('input', function () {
                    document.querySelector('.char-count').textContent = this.value.length + ' / 1000';
                });
            }

            // Initialize rating
            setRating(5);
        });

        // Validate file size before upload
        function validateFileSize(input, maxSizeMB) {
            if (input.files && input.files[0]) {
                const fileSize = input.files[0].size / 1024 / 1024; // Convert to MB
                const fileName = input.files[0].name;
                const fileType = input.files[0].type;

                // Check file size
                if (fileSize > maxSizeMB) {
                    alert(`{{ __('service_provider.file_too_large') }} ${fileName} (${fileSize.toFixed(2)}MB). {{ __('service_provider.max_allowed') }}: ${maxSizeMB}MB`);
                    input.value = ''; // Clear the input
                    return false;
                }

                // Additional validation for images
                if (fileType.startsWith('image/')) {
                    const img = new Image();
                    img.onload = function () {
                        console.log(`Image dimensions: ${this.width}x${this.height}`);
                    };
                    img.src = URL.createObjectURL(input.files[0]);
                }

                console.log(`File validated: ${fileName} (${fileSize.toFixed(2)}MB)`);
                return true;
            }
        }

        function validateMultipleFilesSize(input, maxSizeMB) {
            if (!input.files || input.files.length === 0) {
                return true;
            }

            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

            for (const file of input.files) {
                const fileSize = file.size / 1024 / 1024; // Convert to MB
                if (fileSize > maxSizeMB) {
                    alert(`{{ __('service_provider.file_too_large_gallery') }} (${file.name}) (${fileSize.toFixed(2)}MB). {{ __('service_provider.max_allowed') }}: ${maxSizeMB}MB`);
                    input.value = '';
                    return false;
                }

                if (!allowedTypes.includes(file.type)) {
                    alert(`{{ __('service_provider.gallery_upload_invalid_type') }}`);
                    input.value = '';
                    return false;
                }
            }

            return true;
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Image Modal
            const imageModal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');

            imageModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const imageSrc = button.getAttribute('data-image');
                modalImage.src = imageSrc;
            });

            // Save/unsave is handled via a standard POST form to keep server-side
            // rendering and redirects. The previous fetch-based implementation
            // was removed to comply with view-only (no-API) policy.

            // Toast notification function
            window.showToast = function (message, type = 'success') {
                const toastContainer = document.querySelector('.toast-container');
                const toastElement = toastContainer.querySelector('.custom-toast');
                const toastMessage = toastElement.querySelector('.toast-message');
                const toastIcon = toastElement.querySelector('.toast-icon i');

                toastMessage.textContent = message;
                toastElement.className = `custom-toast toast-${type}`;

                if (type === 'success') {
                    toastIcon.className = 'fas fa-check-circle';
                } else {
                    toastIcon.className = 'fas fa-exclamation-circle';
                }

                toastContainer.setAttribute('x-show', 'true');

                setTimeout(() => {
                    toastContainer.setAttribute('x-show', 'false');
                }, 3000);
            }

            // English-only Address Validation
            const addressInput = document.getElementById('addressInput');
            if (addressInput) {
                addressInput.addEventListener('input', function() {
                    const originalValue = this.value;
                    const newValue = originalValue.replace(/[^a-zA-Z0-9\s\-_.,#&'\/\@]/g, '');

                    if (originalValue !== newValue) {
                        this.value = newValue;
                        if(typeof window.showToast === 'function'){
                            window.showToast('{{ __("service_provider.address_english_only_hint") }}', 'error');
                        }
                    }
                });
            }

            // Click-to-change Profile Image
            const imageContainer = document.getElementById('profileImageClickable');
            const imageInput = document.getElementById('profileImageInput');
            const imageOverlay = document.getElementById('imageOverlay');
            const imageSpinner = document.getElementById('imageUploadSpinner');
            let imagePreview = document.getElementById('profileImagePreview');

            @if(auth()->check() && auth()->id() === $serviceProvider->user_id)
            if (imageContainer && imageInput) {
                imageContainer.addEventListener('mouseenter', function() {
                    if(imageOverlay) imageOverlay.style.opacity = '1';
                });
                imageContainer.addEventListener('mouseleave', function() {
                    if(imageOverlay) imageOverlay.style.opacity = '0';
                });
                imageContainer.addEventListener('click', function(e) {
                    if(e.target === imageInput) return; // Prevent loop
                    imageInput.click();
                });

                imageInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    // Validate client-side
                    if (typeof validateFileSize === 'function' && !validateFileSize(this, 2)) return;

                    // Show spinner
                    if(imageSpinner) imageSpinner.style.display = 'flex';

                    // Prepare form data
                    const formData = new FormData();
                    formData.append('profile_image', file);
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                    // AJAX Request
                    fetch('{{ route("service-providers.profile.image-upload", $serviceProvider->id) }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update preview
                            imagePreview = document.getElementById('profileImagePreview'); // Re-fetch in case it was replaced
                            if (imagePreview) {
                                if (imagePreview.tagName === 'IMG') {
                                    imagePreview.src = data.image_url;
                                } else {
                                    // Replace placeholder div with img
                                    const img = document.createElement('img');
                                    img.src = data.image_url;
                                    img.alt = '{{ $serviceProvider->company_name ?? $serviceProvider->user->name }}';
                                    img.className = 'profile-image';
                                    img.id = 'profileImagePreview';
                                    img.loading = 'lazy';
                                    imagePreview.replaceWith(img);
                                }
                            }
                            if(typeof window.showToast === 'function') window.showToast(data.message, 'success');

                            // Update progress bar
                            if (data.completion_percent !== undefined) {
                                const progressBar = document.querySelector('#completionProgressBar .progress-bar');
                                const percentBadge = document.querySelector('#completionProgressBar').closest('.mb-4').querySelector('.badge');
                                if (progressBar && percentBadge) {
                                    progressBar.style.width = data.completion_percent + '%';
                                    progressBar.setAttribute('aria-valuenow', data.completion_percent);
                                    percentBadge.textContent = data.completion_percent + '%';

                                    // Update colors
                                    let newBg = '';
                                    let newBadgeClass = 'badge rounded-pill px-3 ';
                                    if(data.completion_percent >= 80) {
                                        newBg = 'linear-gradient(90deg, #10b981,#059669)';
                                        newBadgeClass += 'bg-success';
                                    } else if(data.completion_percent >= 50) {
                                        newBg = 'linear-gradient(90deg, #f59e0b,#d97706)';
                                        newBadgeClass += 'bg-warning text-dark';
                                    } else {
                                        newBg = 'linear-gradient(90deg, #ef4444,#dc2626)';
                                        newBadgeClass += 'bg-danger';
                                    }
                                    progressBar.style.background = newBg;
                                    percentBadge.className = newBadgeClass;
                                }
                            }
                        } else {
                            if(typeof window.showToast === 'function') window.showToast(data.message || 'Error occurred', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        if(typeof window.showToast === 'function') window.showToast('Upload failed', 'error');
                    })
                    .finally(() => {
                        if(imageSpinner) imageSpinner.style.display = 'none';
                        imageInput.value = ''; // Reset
                    });
                });
            }
            @endif
        });

        // Track WhatsApp click (internal analytics) + reveal contact (privacy) + open WhatsApp
        async function revealContactInfo(whatsappClean, whatsappDisplay, address) {

            // Store reveal in SESSION (server-side) instead of localStorage
            // This ensures only the user who clicked can see the info
            const providerId = {{ $serviceProvider->id }};
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            // Internal analytics: click_whatsapp (dedupe is intentionally not applied to clicks)
            let analyticsPromise = Promise.resolve();
            if (csrfToken) {
                analyticsPromise = fetch(`/service-providers/${providerId}/analytics/click`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ action_type: 'click_whatsapp' }),
                    keepalive: true,
                }).catch(() => {});
            }

            // Store reveal in session via AJAX request
            if (csrfToken) {
                fetch(`/service-providers/${providerId}/reveal-contact`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({})
                }).catch(err => console.error('Failed to store reveal:', err));
            }

            // Reveal WhatsApp number
            const whatsappElement = document.getElementById('whatsappNumber');
            if (whatsappElement) {
                whatsappElement.textContent = whatsappDisplay;
                whatsappElement.classList.remove('text-muted');
                whatsappElement.classList.add('text-success', 'fw-bold');
            }

            // Reveal address
            const addressElement = document.getElementById('addressText');
            if (addressElement && address) {
                addressElement.textContent = address;
                addressElement.classList.add('text-primary', 'fw-bold');
            }

            // Prepare WhatsApp message
            const businessName = {!! json_encode($serviceProvider->company_name ?? $serviceProvider->user->name) !!};
            const whatsappMessage = {!! json_encode(__("service_provider.whatsapp_message")) !!};
            const businessLabel = {!! json_encode(__("service_provider.business_name")) !!};

            // Validate that we have required data
            if (!businessName || !whatsappMessage) {
                console.error('WhatsApp Error: Missing required data', {
                    businessName: businessName,
                    whatsappMessage: whatsappMessage
                });
                alert('{{ __("general.error") }}: Cannot send WhatsApp message. Missing information.');
                return;
            }

            const message = whatsappMessage + '\n' + businessLabel + ': ' + businessName;

            // Encode message for URL
            const encodedMessage = encodeURIComponent(message);

            // Create WhatsApp URL with message
            // Use api.whatsapp.com for better compatibility with WhatsApp Desktop and Web
            const whatsappUrl = `https://api.whatsapp.com/send?phone=${whatsappClean}&text=${encodedMessage}`;

            // Debug: Log the URL (you can remove this later)
            console.log('WhatsApp URL:', whatsappUrl);
            console.log('WhatsApp phone:', whatsappClean);
            console.log('Message:', message);

            // Attempt analytics write before redirecting (timeout keeps UX snappy)
            try {
                await Promise.race([
                    analyticsPromise,
                    new Promise((resolve) => setTimeout(resolve, 150)),
                ]);
            } catch (e) {
                // Ignore analytics errors: user action must still proceed.
            }

            // Open WhatsApp after a short delay
            setTimeout(function () {
                // Try to open in new window
                const newWindow = window.open(whatsappUrl, '_blank');

                // Fallback: If popup blocked, try opening in same window
                if (!newWindow || newWindow.closed || typeof newWindow.closed === 'undefined') {
                    window.location.href = whatsappUrl;
                }
            }, 0);
        }

        // ========== PROFILE EDIT FORM VALIDATION & UX ENHANCEMENTS ==========

        // Get the profile edit form if it exists
        const profileForm = document.querySelector('form[action*="profile.update"]');

        if (profileForm) {
            let isSubmitting = false;

            // Legacy profile image preview removed - now handled by AJAX auto-save camera overlay

            // File validation for certification
            const certInput = profileForm.querySelector('input[name="certification"]');
            if (certInput) {
                certInput.addEventListener('change', function (e) {
                    const file = e.target.files[0];
                    if (file) {
                        // Validate file size (10MB)
                        if (file.size > 10 * 1024 * 1024) {
                            alert('{{ __("sp_validation.sp_cert_size") }}');
                            e.target.value = '';
                            return;
                        }

                        // Validate file type
                        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'application/pdf'];
                        if (!validTypes.includes(file.type)) {
                            alert('{{ __("sp_validation.sp_cert_mimes") }}');
                            e.target.value = '';
                            return;
                        }

                        // Show file name
                        const fileName = file.name;
                        const fileInfo = certInput.parentElement.querySelector('.file-info') || document.createElement('small');
                        fileInfo.className = 'file-info text-success d-block mt-1';
                        fileInfo.innerHTML = '<i class="fas fa-check-circle me-1"></i>{{ __("general.selected") }}: ' + fileName;
                        if (!certInput.parentElement.querySelector('.file-info')) {
                            certInput.parentElement.appendChild(fileInfo);
                        }
                    }
                });
            }

            // Character counter for bio
            const bioTextarea = profileForm.querySelector('textarea[name="bio"]');
            if (bioTextarea) {
                const maxLength = 2000;
                const counter = document.createElement('small');
                counter.className = 'char-counter text-muted d-block text-end mt-1';
                bioTextarea.parentElement.appendChild(counter);

                function updateCounter() {
                    const remaining = maxLength - bioTextarea.value.length;
                    counter.textContent = remaining + ' {{ __("general.characters_remaining") }}';
                    counter.className = remaining < 100 ? 'char-counter text-warning d-block text-end mt-1' : 'char-counter text-muted d-block text-end mt-1';
                }

                updateCounter();
                bioTextarea.addEventListener('input', updateCounter);
            }

            // Character counter for services
            const servicesInput = profileForm.querySelector('input[name="services_offered"]');
            if (servicesInput) {
                const maxLength = 1000;
                const counter = document.createElement('small');
                counter.className = 'char-counter text-muted d-block text-end mt-1';
                servicesInput.parentElement.appendChild(counter);

                function updateCounter() {
                    const remaining = maxLength - servicesInput.value.length;
                    counter.textContent = remaining + ' {{ __("general.characters_remaining") }}';
                    counter.className = remaining < 50 ? 'char-counter text-warning d-block text-end mt-1' : 'char-counter text-muted d-block text-end mt-1';
                }

                updateCounter();
                servicesInput.addEventListener('input', updateCounter);
            }

            // Phone number formatting
            const phoneInput = profileForm.querySelector('input[name="phone"]');
            if (phoneInput) {
                phoneInput.addEventListener('input', function (e) {
                    // Remove all non-numeric characters except +
                    let value = e.target.value.replace(/[^\d+]/g, '');
                    e.target.value = value;
                });
            }

            const whatsappInput = profileForm.querySelector('input[name="whatsapp_number"]');
            if (whatsappInput) {
                whatsappInput.addEventListener('input', function (e) {
                    // Remove all non-numeric characters except +
                    let value = e.target.value.replace(/[^\d+]/g, '');
                    e.target.value = value;
                });
            }

            // Enhanced form submission with upload progress
            profileForm.addEventListener('submit', function (e) {
                // Prevent double submission
                if (isSubmitting) {
                    e.preventDefault();
                    return false;
                }

                // Basic validation
                const businessName = profileForm.querySelector('input[name="business_name"]');
                if (businessName && businessName.value.trim().length < 3) {
                    e.preventDefault();
                    alert('{{ __("sp_validation.sp_business_name_min") }}');
                    businessName.focus();
                    return false;
                }

                const phone = profileForm.querySelector('input[name="phone"]');
                if (phone && phone.value.trim().length < 10) {
                    e.preventDefault();
                    alert('{{ __("sp_validation.sp_phone_min") }}');
                    phone.focus();
                    return false;
                }

                const email = profileForm.querySelector('input[name="contact_email"]');
                if (email && email.value && !isValidEmail(email.value)) {
                    e.preventDefault();
                    alert('{{ __("sp_validation.sp_email_format") }}');
                    email.focus();
                    return false;
                }

                // Mark as submitting
                isSubmitting = true;

                // Show enhanced loading state with progress
                const submitBtn = profileForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    const originalText = submitBtn.innerHTML;

                    // Create progress indicator
                    submitBtn.innerHTML = `
                        <div class="d-flex align-items-center justify-content-center">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            <span>{{ __("general.saving") }}...</span>
                        </div>
                    `;

                    // Add upload progress overlay
                    const progressOverlay = document.createElement('div');
                    progressOverlay.className = 'upload-progress-overlay';
                    progressOverlay.style.cssText = `
                        position: fixed;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background: rgba(0, 0, 0, 0.7);
                        z-index: 9999;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    `;
                    progressOverlay.innerHTML = `
                        <div class="bg-white p-4 rounded-3 shadow-lg text-center" style="max-width: 300px;">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <h6 class="mb-2">{{ __("general.saving") }}...</h6>
                            <p class="small text-muted mb-0">{{ __("service_provider.please_wait_updating") }}</p>
                        </div>
                    `;
                    document.body.appendChild(progressOverlay);

                    // Restore button after timeout (fallback)
                    setTimeout(function () {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                        isSubmitting = false;
                        if (progressOverlay.parentElement) {
                            progressOverlay.remove();
                        }
                    }, 30000); // 30 seconds timeout
                }

                return true;
            });

            // Email validation helper
            function isValidEmail(email) {
                const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                return regex.test(email);
            }
        }

        // Show validation errors prominently
        @if($errors->any())
            window.addEventListener('DOMContentLoaded', function () {
                // Scroll to first error
                const firstError = document.querySelector('.text-danger');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.classList.add('animate-shake');
                }

                // Show error summary alert
                const errorList = @json($errors->all());
                if (errorList.length > 0) {
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                    alertDiv.style.zIndex = '9999';
                    alertDiv.style.maxWidth = '500px';
                    alertDiv.innerHTML = `
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                                <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>{{ __("validation.please_correct_errors") }}</h6>
                                                <ul class="mb-0 small">
                                                    ${errorList.map(error => '<li>' + error + '</li>').join('')}
                                                </ul>
                                            `;
                    document.body.appendChild(alertDiv);

                    // Auto dismiss after 10 seconds
                    setTimeout(() => {
                        alertDiv.remove();
                    }, 10000);
                }
            });
        @endif

        @if(session('success'))
            window.addEventListener('DOMContentLoaded', function () {
                showToast('{{ session("success") }}', 'success');
            });
        @endif
    </script>

    <style>
@endsection
