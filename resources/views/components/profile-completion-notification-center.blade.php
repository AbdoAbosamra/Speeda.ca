@props(['provider'])

@php
    $completion = (int) ($provider->profile_completion_percent ?? 0);

    $missingPhoto = blank($provider->profile_image);
    $missingExperience = !filled($provider->experience_years) || (int) $provider->experience_years <= 0;
    $missingAddress = blank($provider->address);

    $shouldShowPopup = $completion < 100 && blank($provider->profile_completion_popup_shown_at);

    // Only the first time: mark as shown so old providers don't see the popup again.
    if ($shouldShowPopup) {
        $provider->updateQuietly([
            'profile_completion_popup_shown_at' => now(),
        ]);
    }
@endphp

@if($completion < 100)
    @if($shouldShowPopup)
        <div class="sp-completion-popup-backdrop position-fixed top-0 start-0 w-100 h-100"
            style="z-index: 2000; background: rgba(0,0,0,0.45); backdrop-filter: blur(4px);">
            <div class="d-flex align-items-center justify-content-center p-3" style="min-height: 100vh;">
                <div class="bg-white rounded-4 shadow-lg p-4 p-md-5"
                    style="max-width: 760px; width: 100%;">
                    <div class="d-flex align-items-start gap-3">
                        <div class="d-flex align-items-center justify-content-center"
                            style="width: 48px; height: 48px; border-radius: 16px; background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff;">
                            <i class="fas fa-trophy"></i>
                        </div>

                        <div class="flex-grow-1">
                            <div class="d-flex align-items-start justify-content-between gap-3">
                                <div>
                                    <h3 class="fw-bold mb-2">
                                        {{ __('service_provider.completion_popup_title') }}
                                    </h3>
                                    <p class="text-muted mb-3" style="white-space: pre-line;">
                                        {{ __('service_provider.completion_popup_message') }}
                                    </p>
                                </div>
                                <button type="button" class="btn-close" aria-label="Close"
                                    onclick="this.closest('.sp-completion-popup-backdrop')?.remove()"></button>
                            </div>

                            <div class="mb-3">
                                <div class="fw-semibold mb-2 text-dark">
                                    {{ __('service_provider.completion_popup_missing_fields_title') }}
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    @if($missingPhoto)
                                        <span class="badge rounded-pill px-3 py-2 border"
                                            style="border-color: rgba(239,68,68,0.35); background: rgba(239,68,68,0.08); color: #b91c1c;">
                                            <i class="fas fa-image me-1"></i>{{ __('service_provider.completion_field_profile_photo') }}
                                        </span>
                                    @endif
                                    @if($missingExperience)
                                        <span class="badge rounded-pill px-3 py-2 border"
                                            style="border-color: rgba(239,68,68,0.35); background: rgba(239,68,68,0.08); color: #b91c1c;">
                                            <i class="fas fa-calendar-alt me-1"></i>{{ __('service_provider.completion_field_experience_years') }}
                                        </span>
                                    @endif
                                    @if($missingAddress)
                                        <span class="badge rounded-pill px-3 py-2 border"
                                            style="border-color: rgba(239,68,68,0.35); background: rgba(239,68,68,0.08); color: #b91c1c;">
                                            <i class="fas fa-map-pin me-1"></i>{{ __('service_provider.completion_field_address') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <button type="button" class="btn btn-primary px-4 py-2 fw-semibold"
                                    onclick="spCompletionScrollToMissing()">
                                    <i class="fas fa-arrow-down me-2"></i>{{ __('service_provider.completion_popup_cta') }}
                                </button>
                                <button type="button" class="btn btn-outline-secondary px-4 py-2"
                                    onclick="this.closest('.sp-completion-popup-backdrop')?.remove()">
                                    {{ __('general.cancel') }}
                                </button>
                            </div>

                            <div class="small text-muted mt-3">
                                {{ __('service_provider.completion_popup_current_score', ['percent' => $completion]) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .sp-completion-highlight {
                outline: 3px solid rgba(79, 70, 229, 0.45) !important;
                box-shadow: 0 0 0 6px rgba(79, 70, 229, 0.12) !important;
                border-radius: 12px;
            }
        </style>

        <script>
            function spCompletionScrollToMissing() {
                const ids = [];
                @if($missingPhoto)
                    ids.push('profileImageInput');
                @endif
                @if($missingExperience)
                    ids.push('experienceYearsInput');
                @endif
                @if($missingAddress)
                    ids.push('addressInput');
                @endif

                if (!ids.length) return;

                const el = document.getElementById(ids[0]);
                if (!el) return;

                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                el.classList.add('sp-completion-highlight');

                setTimeout(function () {
                    el.classList.remove('sp-completion-highlight');
                }, 2500);
            }
        </script>
    @else
        <x-profile-completion-banner :provider="$provider" :compact="true" />
    @endif
@endif

