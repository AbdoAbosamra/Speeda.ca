@props([
    'as' => 'section',
    'flat' => false,
    'interactive' => false,
])

@php
    $tag = in_array($as, ['article', 'aside', 'div', 'section'], true) ? $as : 'section';
    $classes = trim(implode(' ', [
        'sp-card',
        filter_var($flat, FILTER_VALIDATE_BOOLEAN) ? 'sp-card--flat' : '',
        filter_var($interactive, FILTER_VALIDATE_BOOLEAN) ? 'sp-card--interactive' : '',
    ]));
@endphp

<{{ $tag }} {{ $attributes->merge(['class' => $classes]) }}>
    @isset($header)
        <div class="sp-card__header">{{ $header }}</div>
    @endisset

    <div class="sp-card__body">{{ $slot }}</div>

    @isset($footer)
        <div class="sp-card__footer">{{ $footer }}</div>
    @endisset
</{{ $tag }}>
