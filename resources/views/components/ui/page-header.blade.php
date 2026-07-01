@props([
    'eyebrow' => null,
    'title' => null,
    'subtitle' => null,
])

<header {{ $attributes->merge(['class' => 'sp-page-header']) }}>
    <div class="sp-page-header__content">
        @if($eyebrow)
            <p class="sp-page-header__eyebrow">{{ $eyebrow }}</p>
        @endif

        @if($title)
            <h1 class="sp-page-header__title">{{ $title }}</h1>
        @endif

        @if($subtitle)
            <p class="sp-page-header__subtitle">{{ $subtitle }}</p>
        @endif

        {{ $slot }}
    </div>

    @isset($actions)
        <div class="sp-page-header__actions">{{ $actions }}</div>
    @endisset
</header>
