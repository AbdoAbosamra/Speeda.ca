@props([
    'variant' => 'neutral',
    'size' => 'md',
    'icon' => null,
])

<span {{ $attributes->merge(['class' => 'sp-badge sp-badge--' . $variant . ' sp-badge--' . $size]) }}>
    @if($icon)
        <i class="{{ $icon }}" aria-hidden="true"></i>
    @endif
    <span>{{ $slot }}</span>
</span>
