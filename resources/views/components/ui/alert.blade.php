@props([
    'variant' => 'info',
    'title' => null,
    'icon' => null,
])

@php
    $role = in_array($variant, ['danger', 'warning'], true) ? 'alert' : 'status';
@endphp

<div role="{{ $role }}" {{ $attributes->merge(['class' => 'sp-alert sp-alert--' . $variant]) }}>
    @if($icon)
        <span class="sp-alert__icon">
            <i class="{{ $icon }}" aria-hidden="true"></i>
        </span>
    @endif

    <div class="sp-alert__content">
        @if($title)
            <h3 class="sp-alert__title">{{ $title }}</h3>
        @endif
        <div class="sp-alert__body">{{ $slot }}</div>
    </div>
</div>
