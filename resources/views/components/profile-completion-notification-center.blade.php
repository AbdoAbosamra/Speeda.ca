@props(['provider'])

@php
    $completion = (int) ($provider->profile_completion_percent ?? 0);

    // Identify missing fields (Unified logic from banner)
    $missingFields = [];
    $missingPhoto = blank($provider->profile_image);
    $missingExperience = !filled($provider->experience_years) || (int) $provider->experience_years <= 0;
    $missingAddress = blank($provider->address);

    $servicesComplete = is_array($provider->services_offered)
        ? count(array_filter($provider->services_offered)) > 0
        : filled($provider->services_offered);
    $missingServices = !$servicesComplete;

    if ($missingPhoto) $missingFields[] = ['icon' => 'fa-image', 'label' => __('service_provider.completion_field_profile_photo'), 'id' => 'profileImageInput'];
    if ($missingExperience) $missingFields[] = ['icon' => 'fa-calendar-alt', 'label' => __('service_provider.completion_field_experience_years'), 'id' => 'experienceYearsInput'];
    if ($missingAddress) $missingFields[] = ['icon' => 'fa-map-pin', 'label' => __('service_provider.completion_field_address'), 'id' => 'addressInput'];
    if ($missingServices) $missingFields[] = ['icon' => 'fa-concierge-bell', 'label' => __('service_provider.completion_missing_services_offered'), 'id' => 'servicesInput'];

    // Modal Visibility: Show if < 100% AND not dismissed in current session
    $isDismissed = session()->get('profile_completion_popup_dismissed', false);
    $shouldShowPopup = $completion < 100 && !$isDismissed;

    // On which pages should we NOT redirect, but instead SCROLL to the field?
    // The edit form is embedded in 'service-providers.show' for the owner.
    $isOnEditPage = request()->routeIs('service-providers.show') || request()->routeIs('profile.show') || request()->routeIs('service-providers.edit');
@endphp

@if($shouldShowPopup)
    <div id="spCompletionPopup" class="sp-completion-popup-backdrop position-fixed top-0 start-0 w-100 h-100"
        style="z-index: 2000; background: rgba(0,0,0,0.5); backdrop-filter: blur(6px); display: block;">
        <div class="d-flex align-items-center justify-content-center p-3" style="min-height: 100vh;">
            <div class="bg-white rounded-4 shadow-lg p-4 p-md-5 animate__animated animate__zoomIn"
                style="max-width: 760px; width: 100%; position: relative;">
                
                <button type="button" class="btn-close position-absolute top-0 end-0 m-4" 
                    onclick="dismissSPCompletionPopup()" aria-label="Close"></button>

                <div class="d-flex align-items-start gap-3">
                    <div class="d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width: 56px; height: 56px; border-radius: 18px; background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff; font-size: 1.5rem;">
                        <i class="fas fa-rocket"></i>
                    </div>

                    <div class="flex-grow-1">
                        <h3 class="fw-bold mb-2 text-dark">
                            {{ __('service_provider.completion_popup_title') }}
                        </h3>
                        <p class="text-muted mb-4" style="white-space: pre-line; line-height: 1.6;">
                            {{ __('service_provider.completion_popup_message') }}
                        </p>

                        @if(!empty($missingFields))
                            <div class="mb-4">
                                <div class="fw-bold mb-3 text-dark d-flex align-items-center gap-2">
                                    <i class="fas fa-clipboard-list text-primary"></i>
                                    {{ __('service_provider.completion_popup_missing_fields_title') }}
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($missingFields as $field)
                                        <span class="badge rounded-pill px-3 py-2 border d-flex align-items-center gap-2"
                                            style="border-color: rgba(239,68,68,0.2); background: rgba(239,68,68,0.05); color: #dc2626; font-weight: 500;">
                                            <i class="fas {{ $field['icon'] }} small"></i>{{ $field['label'] }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="d-flex align-items-center gap-3 flex-wrap pt-2">
                            @if($isOnEditPage)
                                <button type="button" class="btn btn-primary px-5 py-2 fw-bold shadow-sm"
                                    onclick="spCompletionAction()">
                                    <i class="fas fa-magic me-2"></i>{{ __('service_provider.completion_popup_cta') }}
                                </button>
                            @else
                                <a href="{{ route('service-providers.edit', $provider) }}" class="btn btn-primary px-5 py-2 fw-bold shadow-sm">
                                    <i class="fas fa-edit me-2"></i>{{ __('service_provider.completion_popup_cta') }}
                                </a>
                            @endif
                            
                            <button type="button" class="btn btn-light px-4 py-2 text-muted fw-semibold"
                                onclick="dismissSPCompletionPopup()">
                                {{ __('general.cancel') }}
                            </button>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex align-items-center justify-content-between">
                            <div class="fw-bold text-dark">
                                {{ __('service_provider.completion_popup_current_score', ['percent' => $completion]) }}
                            </div>
                            <div class="progress flex-grow-1 ms-3" style="height: 8px; border-radius: 4px; max-width: 200px; background: #f3f4f6;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                    role="progressbar" style="width: {{ $completion }}%; border-radius: 4px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .sp-completion-highlight {
            outline: 3px solid rgba(79, 70, 229, 0.6) !important;
            box-shadow: 0 0 0 8px rgba(79, 70, 229, 0.15) !important;
            transition: all 0.3s ease;
        }
    </style>

    <script>
        function dismissSPCompletionPopup() {
            const popup = document.getElementById('spCompletionPopup');
            if (popup) popup.remove();
            
            // Call an endpoint to dismiss for session (optional, but cleaner)
            fetch('{{ route("service-providers.dismiss-completion-popup") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            });
        }

        function spCompletionAction() {
            @if($isOnEditPage)
                const ids = {!! json_encode(array_column($missingFields, 'id')) !!};
                if (!ids.length) return;

                const popup = document.getElementById('spCompletionPopup');
                if (popup) popup.style.display = 'none';

                const el = document.getElementById(ids[0]);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    el.classList.add('sp-completion-highlight');
                    setTimeout(() => el.classList.remove('sp-completion-highlight'), 3000);
                }
            @endif
        }
    </script>
@endif


