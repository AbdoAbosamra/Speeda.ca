@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'icon' => null,
    'iconPosition' => 'start',
    'loading' => false,
    'disabled' => false,
    'block' => false,
])

@php
    $isLoading = filter_var($loading, FILTER_VALIDATE_BOOLEAN);
    $isDisabled = filter_var($disabled, FILTER_VALIDATE_BOOLEAN) || $isLoading;
    $classes = trim(implode(' ', [
        'sp-btn',
        'sp-btn--' . $variant,
        'sp-btn--' . $size,
        filter_var($block, FILTER_VALIDATE_BOOLEAN) ? 'sp-btn--block' : '',
    ]));
@endphp

@if($href)
    <a
        href="{{ $isDisabled ? '#' : $href }}"
        @if($isDisabled) aria-disabled="true" tabindex="-1" @endif
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if($isLoading)
            <span class="sp-spinner sp-spinner--sm" aria-hidden="true"></span>
        @elseif($icon && $iconPosition === 'start')
            <i class="{{ $icon }} sp-btn__icon" aria-hidden="true"></i>
        @endif

        <span class="sp-btn__label">{{ $slot }}</span>

        @if(!$isLoading && $icon && $iconPosition === 'end')
            <i class="{{ $icon }} sp-btn__icon" aria-hidden="true"></i>
        @endif
    </a>
@else
    <button
        type="{{ $type }}"
        @disabled($isDisabled)
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if($isLoading)
            <span class="sp-spinner sp-spinner--sm" aria-hidden="true"></span>
        @elseif($icon && $iconPosition === 'start')
            <i class="{{ $icon }} sp-btn__icon" aria-hidden="true"></i>
        @endif

        <span class="sp-btn__label">{{ $slot }}</span>

        @if(!$isLoading && $icon && $iconPosition === 'end')
            <i class="{{ $icon }} sp-btn__icon" aria-hidden="true"></i>
        @endif
    </button>
@endif
