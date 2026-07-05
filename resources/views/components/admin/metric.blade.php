@props([
    'label',
    'value',
])

<article {{ $attributes }}>
    <span>{{ $label }}</span>
    <strong>{{ $value }}</strong>
</article>
