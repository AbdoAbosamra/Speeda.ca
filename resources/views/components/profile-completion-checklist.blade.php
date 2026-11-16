@props(['provider', 'linkable' => false])

{{-- The three items that make 100%; each is a third of the bar. --}}
@php
    $checks = $provider->profileCompletionChecklist();
    $items = [
        'photo' => ['label' => __('service_provider.completion_field_profile_photo'), 'icon' => 'fa-image', 'target' => 'edit-photo'],
        'experience' => ['label' => __('service_provider.completion_field_experience_years'), 'icon' => 'fa-award', 'target' => 'experienceYearsInput'],
        'bio' => ['label' => __('service_provider.completion_field_description'), 'icon' => 'fa-align-left', 'target' => 'bioInput'],
    ];
@endphp

<ul class="sp-checklist" aria-label="{{ __('service_provider.profile_completion_title') }}">
    @foreach($items as $key => $item)
        @php $done = $checks[$key]; @endphp
        <li class="sp-checklist-item {{ $done ? 'is-done' : 'is-missing' }}" data-completion-item="{{ $key }}">
            @if(! $done && $linkable)
                <a href="#{{ $item['target'] }}" class="sp-checklist-link" data-completion-target="{{ $item['target'] }}">
            @endif
            <i class="fas {{ $done ? 'fa-circle-check' : 'fa-circle-plus' }} sp-checklist-state" aria-hidden="true"></i>
            <span>{{ $item['label'] }}</span>
            <span class="visually-hidden">{{ $done ? '✓' : '—' }}</span>
            @if(! $done && $linkable)
                </a>
            @endif
        </li>
    @endforeach
</ul>

@once
    <style>
        .sp-checklist{list-style:none;margin:.75rem 0 0;padding:0;display:flex;flex-wrap:wrap;gap:.5rem;}
        .sp-checklist-item{display:flex;align-items:center;gap:.4rem;padding:.35rem .75rem;border-radius:999px;font-size:.85rem;font-weight:600;border:1px solid transparent;}
        .sp-checklist-item.is-done{background:#ecfdf5;border-color:#a7f3d0;color:#047857;}
        .sp-checklist-item.is-missing{background:#fff7ed;border-color:#fed7aa;color:#c2410c;}
        .sp-checklist-link{display:flex;align-items:center;gap:.4rem;color:inherit;text-decoration:none;}
        .sp-checklist-link:hover span{text-decoration:underline;}
    </style>
    <script>
        document.addEventListener('click', function (e) {
            const link = e.target.closest('[data-completion-target]');
            if (!link) return;
            const el = document.getElementById(link.dataset.completionTarget);
            if (!el) return;
            e.preventDefault();
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (el.matches('input, textarea')) el.focus({ preventScroll: true });
        });
    </script>
@endonce
