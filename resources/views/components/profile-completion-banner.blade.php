@props(['provider', 'compact' => false])

@php
    $completion = (int) ($provider->profile_completion_percent ?? 0);
@endphp

@if($completion < 100)
    @php
        // Same three items as the stored percent (ServiceProvider::profileCompletionChecklist).
        $checks = $provider->profileCompletionChecklist();
        $missing = array_values(array_filter([
            $checks['photo'] ? null : __('service_provider.completion_missing_profile_photo'),
            $checks['experience'] ? null : __('service_provider.completion_missing_experience_years'),
            $checks['bio'] ? null : __('service_provider.completion_missing_description'),
        ]));
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

