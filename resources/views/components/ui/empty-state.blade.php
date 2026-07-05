@props([
    'icon' => null,
    'title' => null,
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'sp-empty']) }}>
    @if($icon)
        <span class="sp-empty__icon">
            <i class="{{ $icon }}" aria-hidden="true"></i>
        </span>
    @endif

    @if($title)
        <h3 class="sp-empty__title">{{ $title }}</h3>
    @endif

    @if($description)
        <p class="sp-empty__description">{{ $description }}</p>
    @endif

    {{ $slot }}

    @isset($actions)
        <div class="sp-empty__actions">{{ $actions }}</div>
    @endisset
</div>
