@props(['provider', 'compact' => false])

@php
    $completion = (int) ($provider->profile_completion_percent ?? 0);
@endphp

@if($completion < 100)
    @php
        $missing = [];

        if (blank($provider->profile_image)) {
            $missing[] = __('service_provider.completion_missing_profile_photo');
        }

        if (!filled($provider->experience_years) || (int) $provider->experience_years <= 0) {
            $missing[] = __('service_provider.completion_missing_experience_years');
        }

        if (blank($provider->address)) {
            $missing[] = __('service_provider.completion_missing_address');
        }

        // Secondary fields (still contribute to the score)
        if (blank($provider->bio)) {
            $missing[] = __('service_provider.completion_missing_description');
        }

        $galleryCount = $provider->getMedia('provider_gallery')->count();
        if ($galleryCount < 4) {
            $missing[] = __('service_provider.completion_missing_gallery');
        }

        $servicesComplete = is_array($provider->services_offered)
            ? count(array_filter($provider->services_offered)) > 0
            : filled($provider->services_offered);
        if (!$servicesComplete) {
            $missing[] = __('service_provider.completion_missing_services_offered');
        }
    @endphp

    <div class="alert alert-warning border-0 shadow-sm {{ $compact ? 'py-2' : '' }}" style="border-radius:16px;">
        <div class="d-flex align-items-start gap-3">
            <div style="width:40px; height:40px; border-radius:12px; background:linear-gradient(135deg,#f59e0b,#fbbf24); display:flex; align-items:center; justify-content:center; color:#fff;">
                <i class="fas fa-bolt"></i>
            </div>

            <div class="flex-grow-1">
                <div class="fw-bold mb-1">
                    {{ __('service_provider.profile_completion_banner_title', ['percent' => $completion]) }}
                </div>

                @if(!empty($missing))
                    <div class="text-muted mb-2">
                        {{ __('service_provider.profile_completion_banner_missing_label') }}
                    </div>

                    <ul class="mb-0 ps-3">
                        @foreach($missing as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-muted">
                        {{ __('service_provider.profile_completion_banner_generic') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif

