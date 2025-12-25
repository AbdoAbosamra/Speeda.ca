<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $serviceProvider->business_name ?? $serviceProvider->company_name ?? $serviceProvider->user->name }} - {{ __('service_provider.profile_title') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Toastr CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">

    <style>
        .select2-container--default .select2-selection--multiple {
            min-height: 38px;
            padding-bottom: 0;
            border: 1px solid #ced4da !important;
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #e9ecef;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            padding: 0 0.5rem;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            margin-right: 5px;
            color: #6c757d;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #dc3545;
        }
        .form-control[readonly] {
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }
        .form-control[disabled] {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            opacity: 0.7;
        }
        .form-control.border-primary {
            border-color: #0d6efd !important;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .invalid-feedback {
            display: block;
        }
        .btn-circle {
            width: 30px;
            height: 30px;
            padding: 0;
            border-radius: 50%;
        }
        .card {
            border: none;
            border-radius: 12px;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            border-radius: 12px 12px 0 0 !important;
        }
        .img-thumbnail {
            border: 3px solid #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        select[multiple] {
            min-height: 80px;
        }
        .alert {
            border: none;
            border-radius: 8px;
        }
        .badge {
            font-size: 0.75em;
            padding: 0.35em 0.65em;
        }
        @media (max-width: 768px) {
            .row.text-center .col-4 {
                margin-bottom: 10px;
            }

            .card-body {
                padding: 1rem;
            }

            select[multiple] {
                min-height: 100px;
            }
        }
        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        /* Premium WhatsApp Country Badge Design */
        .whatsapp-country-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 46px;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #991b1b 100%);
            border-radius: 12px;
            padding: 0 1rem;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3),
                        0 2px 6px rgba(0, 0, 0, 0.1),
                        inset 0 -2px 8px rgba(0, 0, 0, 0.15),
                        inset 0 2px 4px rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            cursor: default;
        }

        .whatsapp-country-badge::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: badgeShine 3s ease-in-out infinite;
        }

        @keyframes badgeShine {
            0%, 100% { left: -100%; }
            50% { left: 100%; }
        }

        .whatsapp-country-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(220, 38, 38, 0.4),
                        0 3px 8px rgba(0, 0, 0, 0.15),
                        inset 0 -2px 8px rgba(0, 0, 0, 0.15),
                        inset 0 2px 4px rgba(255, 255, 255, 0.25);
        }

        .whatsapp-country-badge .flag-emoji {
            font-size: 1.5rem;
            line-height: 1;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
            animation: flagWave 2.5s ease-in-out infinite;
            position: relative;
            z-index: 1;
        }

        @keyframes flagWave {
            0%, 100% { transform: rotate(0deg) scale(1); }
            25% { transform: rotate(-5deg) scale(1.05); }
            75% { transform: rotate(5deg) scale(1.05); }
        }

        .whatsapp-country-badge .country-code {
            font-size: 1.1rem;
            font-weight: 700;
            color: white;
            letter-spacing: 1px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3),
                         0 1px 2px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 1;
        }

        .whatsapp-country-badge .country-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.95);
            letter-spacing: 1.5px;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
            background: rgba(255, 255, 255, 0.15);
            padding: 3px 8px;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            position: relative;
            z-index: 1;
        }

        .whatsapp-country-badge::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .whatsapp-country-badge {
                height: 42px;
                gap: 8px;
            }
            .whatsapp-country-badge .flag-emoji {
                font-size: 1.3rem;
            }
            .whatsapp-country-badge .country-code {
                font-size: 1rem;
            }
            .whatsapp-country-badge .country-name {
                font-size: 0.75rem;
                padding: 2px 6px;
            }
        }
    </style>
</head>
<body>
    @include('components.main-nav')

    <div class="container py-4">
        <div class="row">
            <div class="col-lg-4">
                <!-- Profile Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center">
                        <div class="position-relative d-inline-block mb-3">
                            <img src="{{ $serviceProvider->profile_image_url }}"
                                 alt="{{ $serviceProvider->business_name ?? $serviceProvider->company_name ?? $serviceProvider->user->name }}"
                                 class="rounded-circle img-thumbnail"
                                 style="width: 150px; height: 150px; object-fit: cover;">
                            @if($isOwner)
                                <button type="button"
                                        class="btn btn-primary btn-sm position-absolute bottom-0 end-0 rounded-circle"
                                        style="width: 30px; height: 30px; padding: 0;"
                                        onclick="document.getElementById('profile_image').click()">
                                    <i class="fas fa-camera"></i>
                                </button>
                            @endif
                        </div>

                        <h4 class="mb-1">{{ $serviceProvider->business_name ?? $serviceProvider->company_name ?? $serviceProvider->user->name }}</h4>
                        <p class="text-muted mb-2">{{ $serviceProvider->user->profession ?? __('service_provider.not_specified') }}</p>

                        <div class="d-flex justify-content-center align-items-center mb-2">
                            <span class="text-warning me-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star{{ $i <= $serviceProvider->rating ? '' : '-o' }}"></i>
                                @endfor
                            </span>
                            <span class="text-muted">({{ number_format($serviceProvider->rating, 1) }})</span>
                        </div>

                        <div class="mb-3">
                            @if($serviceProvider->certification)
                                <span class="badge" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); font-size: 0.875rem; padding: 0.5rem 1rem;">
                                    <i class="fas fa-certificate"></i> {{ __('service_provider.certified') }}
                                </span>
                            @endif
                        </div>

                        <div class="row text-center">
                            <div class="col-4">
                                <div class="fw-bold">{{ $serviceProvider->formatted_views }}</div>
                                <small class="text-muted">{{ __('service_provider.views') }}</small>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold">{{ $serviceProvider->experience_years ?: 0 }}</div>
                                <small class="text-muted">{{ __('service_provider.years') }}</small>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold">{{ $serviceProvider->reviews_count }}</div>
                                <small class="text-muted">{{ __('service_provider.reviews') }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('service_provider.contact_information') }}
                    </div>
                    <div class="card-body">
                        @if($serviceProvider->phone)
                            <div class="mb-2">
                                <i class="fas fa-phone text-primary me-2"></i>
                                <a href="tel:{{ $serviceProvider->phone }}">{{ $serviceProvider->phone }}</a>
                            </div>
                        @endif

                        @php($contactEmail = $serviceProvider->contact_email ?? $serviceProvider->user->email)
                        @if($contactEmail)
                            <div class="mb-2">
                                <i class="fas fa-envelope text-primary me-2"></i>
                                <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>
                            </div>
                        @endif

                        @if($serviceProvider->service_locations)
                            <div class="mb-0">
                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                {{ $serviceProvider->service_locations }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                {{-- Unified Error Handler --}}
                <x-error-handler />

                <!-- Profile Form -->
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ __('service_provider.profile_details') }}
                        @if($isOwner)
                            <button type="button" class="btn btn-primary btn-sm" id="editProfileBtn">
                                <i class="fas fa-edit me-1"></i>{{ __('service_provider.edit_profile') }}
                            </button>
                        @endif
                    </div>
                    <div class="card-body">
                        <form method="POST"
                              action="{{ route('service-providers.profile.update', $serviceProvider) }}"
                              enctype="multipart/form-data"
                              id="profileForm">
                            @csrf
                            @method('PUT')

                            <!-- Hidden file input for image upload -->
                            <input type="file"
                                   id="profile_image"
                                   name="profile_image"
                                   accept="image/*"
                                   style="display: none;"
                                   onchange="previewImage(this)">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="business_name" class="form-label">{{ __('service_provider.business_name') }} *</label>
                                    <input type="text"
                                           class="form-control @error('business_name') is-invalid @enderror"
                                           id="business_name"
                                           name="business_name"
                                           value="{{ old('business_name', $serviceProvider->business_name ?? $serviceProvider->company_name) }}"
                                           @if(!$isOwner) readonly @endif
                                           required>
                                    @error('business_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="profession" class="form-label">{{ __('service_provider.profession') }} *</label>
                                    <input type="text"
                                           class="form-control @error('profession') is-invalid @enderror"
                                           id="profession"
                                           name="profession"
                                           value="{{ old('profession', $serviceProvider->user->profession ?? '') }}"
                                           readonly
                                           required>
                                    @error('profession')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="experience_years" class="form-label">{{ __('service_provider.years_of_experience') }}</label>
                                    <input type="number"
                                           class="form-control @error('experience_years') is-invalid @enderror"
                                           id="experience_years"
                                           name="experience_years"
                                           value="{{ old('experience_years', $serviceProvider->experience_years) }}"
                                           min="0"
                                           max="50"
                                           @if(!$isOwner) readonly @endif>
                                    @error('experience_years')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">{{ __('service_provider.phone') }}</label>
                                    <input type="tel"
                                           class="form-control @error('phone') is-invalid @enderror"
                                           id="phone"
                                           name="phone"
                                           value="{{ old('phone', $serviceProvider->phone) }}"
                                           @if(!$isOwner) readonly @endif>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="contact_email" class="form-label">{{ __('general.email') }}</label>
                                    <input type="email"
                                           class="form-control @error('contact_email') is-invalid @enderror"
                                           id="contact_email"
                                           name="contact_email"
                                           value="{{ old('contact_email', $serviceProvider->contact_email ?? $serviceProvider->user->email) }}"
                                           @if(!$isOwner) readonly @endif>
                                    @error('contact_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- WhatsApp Number (Required) --}}
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="whatsapp_number" class="form-label">
                                        <i class="fab fa-whatsapp text-success"></i> {{ __('service_provider.whatsapp_number') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="row g-2">
                                        {{-- Country Code Dropdown --}}
                                        <div class="col-md-4">
                                            @php
                                                $currentWhatsApp = old('whatsapp_number', $serviceProvider->whatsapp_number ?? '');
                                                $countryCode = '+1'; // Default
                                                $number = $currentWhatsApp;

                                                if (preg_match('/^(\+\d{1,4})(.+)$/', $currentWhatsApp, $matches)) {
                                                    $countryCode = $matches[1];
                                                    $number = $matches[2];
                                                }
                                            @endphp
                                            <div>
                                                <div class="whatsapp-country-badge">
                                                    <span class="flag-emoji">🍁</span>
                                                    <span class="country-code">+1</span>
                                                    <span class="country-name">CA</span>
                                                </div>
                                                <input type="hidden" name="whatsapp_country_code" id="whatsapp_country_code" value="+1">
                                                @error('whatsapp_country_code')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- WhatsApp Number Input --}}
                                        <div class="col-md-8">
                                            <input type="tel"
                                                   class="form-control @error('whatsapp_number') is-invalid @enderror"
                                                   id="whatsapp_number"
                                                   name="whatsapp_number"
                                                   value="{{ old('whatsapp_number', $number) }}"
                                                   placeholder="5141234567"
                                                   required
                                                   @if(!$isOwner) readonly @endif>
                                            @error('whatsapp_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Enter your WhatsApp number without country code</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="address" class="form-label">{{ __('general.address') }}</label>
                                    <input type="text"
                                           class="form-control @error('address') is-invalid @enderror"
                                           id="address"
                                           name="address"
                                           value="{{ old('address', $serviceProvider->address) }}"
                                           placeholder="{{ __('general.address_placeholder') }}"
                                           @if(!$isOwner) readonly @endif>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="location_id" class="form-label">{{ __('service_provider.service_location') }} *</label>
                                    <select class="form-control @error('location_id') is-invalid @enderror"
                                            id="location_id"
                                            name="location_id"
                                            style="width: 100%;"
                                            @if(!$isOwner) disabled @endif
                                            required>
                                        <option value="">{{ __('service_provider.select_location') }}</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}"
                                                    @if(old('location_id', $serviceProvider->location_id) == $location->id) selected @endif>
                                                {{ $location->city }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('location_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="bio" class="form-label">{{ __('service_provider.bio') }}</label>
                                <textarea class="form-control @error('bio') is-invalid @enderror"
                                          id="bio"
                                          name="bio"
                                          rows="4"
                                          @if(!$isOwner) readonly @endif>{{ old('bio', $serviceProvider->bio) }}</textarea>
                                @error('bio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if($isOwner)
                                <div class="d-flex justify-content-end gap-2" id="formActions" style="display: none;">
                                    <button type="button" class="btn btn-secondary" id="cancelBtn">{{ __('general.cancel') }}</button>
                                    <button type="submit" class="btn btn-primary">{{ __('service_provider.save_changes') }}</button>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $(document).ready(function() {

            // Show error messages as toast notifications
            @if($errors->any())
                @foreach($errors->all() as $error)
                    toastr.error('{{ addslashes($error) }}');
                @endforeach
            @endif

            // Show success message if exists
            @if(session('success'))
                toastr.success('{{ addslashes(session('success')) }}');
            @endif

            // Show error message if exists
            @if(session('error'))
                toastr.error('{{ addslashes(session('error')) }}');
            @endif

            // Handle edit profile button click
            const editBtn = document.getElementById('editProfileBtn');
            const cancelBtn = document.getElementById('cancelBtn');
            const formActions = document.getElementById('formActions');
            const form = document.getElementById('profileForm');
            const readonlyInputs = form.querySelectorAll('[readonly]');
            const disabledSelect = form.querySelector('select[disabled]');

            if (editBtn) {
                editBtn.addEventListener('click', function() {
                    // Enable editing for all readonly inputs
                    readonlyInputs.forEach(input => {
                        if (input.id === 'profession') {
                            return;
                        }
                        input.removeAttribute('readonly');
                        input.classList.add('border-primary');
                    });

                    // Enable the locations select
                    if (disabledSelect) {
                        disabledSelect.removeAttribute('disabled');
                        disabledSelect.classList.add('border-primary');
                    }

                    // Show form actions
                    if (formActions) {
                        formActions.style.display = 'flex';
                    }
                    editBtn.style.display = 'none';
                });
            }

            // Handle cancel button click
            if (cancelBtn) {
                cancelBtn.addEventListener('click', function() {
                    // Disable editing for all inputs
                    readonlyInputs.forEach(input => {
                        input.setAttribute('readonly', 'readonly');
                        input.classList.remove('border-primary');
                    });

                    // Disable the locations select
                    if (disabledSelect) {
                        disabledSelect.setAttribute('disabled', 'disabled');
                        disabledSelect.classList.remove('border-primary');
                    }

                    // Hide form actions and show edit button
                    if (formActions) {
                        formActions.style.display = 'none';
                    }
                    if (editBtn) {
                        editBtn.style.display = 'block';
                    }

                    // Reset the form
                    form.reset();
                });
            }

            // Hide form actions
            if (formActions) {
                formActions.style.display = 'none';
            }
            if (editBtn) {
                editBtn.style.display = 'block';
            }
        });

        // Image preview function
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                const file = input.files[0];
                const maxSize = 5 * 1024 * 1024; // 5MB

                // Validate file size
                if (file.size > maxSize) {
                    alert("{{ __('validation.image_size_too_large') }}");
                    input.value = '';
                    return;
                }

                // Validate file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    alert("{{ __('validation.invalid_image_format') }}");
                    input.value = '';
                    return;
                }

                reader.onload = function(e) {
                    document.querySelector('.rounded-circle.img-thumbnail').src = e.target.result;
                }
                reader.readAsDataURL(file);

                // Automatically submit form after image selection
                setTimeout(() => {
                    if (confirm("{{ __('service_provider.confirm_update_profile_image') }}")) {
                        const formData = new FormData();
                        formData.append('profile_image', file);
                        formData.append('_token', '{{ csrf_token() }}');

                        fetch('{{ route('service-providers.profile.image-upload') }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error("{{ __('service_provider.failed_upload_image') }}");
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                document.querySelector('.rounded-circle.img-thumbnail').src = data.image_url;
                                if (window.toastr) {
                                    toastr.success(data.message || "{{ __('service_provider.profile_image_updated') }}");
                                }
                            } else {
                                throw new Error(data.message || "{{ __('service_provider.failed_upload_image') }}");
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert("{{ __('service_provider.error_updating_image') }}");
                            document.querySelector('.rounded-circle.img-thumbnail').src = '{{ $serviceProvider->profile_image_url }}';
                        })
                        .finally(() => {
                            input.value = '';
                        });
                    } else {
                        // Reset image if user cancels
                        document.querySelector('.rounded-circle.img-thumbnail').src = '{{ $serviceProvider->profile_image_url }}';
                        input.value = '';
                    }
                }, 100);
            }
        }

        // Form validation
        function validateForm() {
            const requiredFields = ['business_name', 'profession'];
            let isValid = true;

            requiredFields.forEach(field => {
                const input = document.getElementById(field);
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    isValid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            });

            const locationSelect = document.getElementById('location_id');
            if (!locationSelect.value) {
                locationSelect.classList.add('is-invalid');
                isValid = false;
            } else {
                locationSelect.classList.remove('is-invalid');
            }

            return isValid;
        }

        // Add form validation on submit
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                alert("{{ __('validation.fill_required_fields') }}");
            }
        });
    </script>

    {{-- Toast Notification System --}}
    <x-toast-notification />
</body>
</html>
