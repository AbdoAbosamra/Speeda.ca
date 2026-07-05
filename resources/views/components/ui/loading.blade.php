@props([
    'label' => null,
    'size' => 'md',
    'block' => false,
])

@php
    $classes = trim(implode(' ', [
        'sp-loading',
        filter_var($block, FILTER_VALIDATE_BOOLEAN) ? 'sp-loading--block' : '',
    ]));
    $spinnerClass = trim('sp-spinner ' . ($size !== 'md' ? 'sp-spinner--' . $size : ''));
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    <span class="{{ $spinnerClass }}" aria-hidden="true"></span>
    @if($label)
        <span>{{ $label }}</span>
    @endif
</span>
