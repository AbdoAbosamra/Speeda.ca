@props([
    'variant' => 'subtle',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'icon' => null,
    'label' => null,
    'loading' => false,
    'disabled' => false,
])

@php
    $isLoading = filter_var($loading, FILTER_VALIDATE_BOOLEAN);
    $isDisabled = filter_var($disabled, FILTER_VALIDATE_BOOLEAN) || $isLoading;
    $classes = trim(implode(' ', [
        'sp-icon-btn',
        'sp-icon-btn--' . $variant,
        $size !== 'md' ? 'sp-icon-btn--' . $size : '',
    ]));
    $accessibleLabel = $label ?: trim(strip_tags((string) $slot));
@endphp

@if($href)
    <a
        href="{{ $isDisabled ? '#' : $href }}"
        aria-label="{{ $accessibleLabel }}"
        @if($isDisabled) aria-disabled="true" tabindex="-1" @endif
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if($isLoading)
            <span class="sp-spinner sp-spinner--sm" aria-hidden="true"></span>
        @elseif($icon)
            <i class="{{ $icon }}" aria-hidden="true"></i>
        @else
            {{ $slot }}
        @endif
    </a>
@else
    <button
        type="{{ $type }}"
        aria-label="{{ $accessibleLabel }}"
        @disabled($isDisabled)
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if($isLoading)
            <span class="sp-spinner sp-spinner--sm" aria-hidden="true"></span>
        @elseif($icon)
            <i class="{{ $icon }}" aria-hidden="true"></i>
        @else
            {{ $slot }}
        @endif
    </button>
@endif
