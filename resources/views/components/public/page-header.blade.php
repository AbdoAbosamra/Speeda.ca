@props([
    'title' => null,
    'subtitle' => null,
])

<x-ui.page-header
    :title="$title"
    :subtitle="$subtitle"
    {{ $attributes->merge(['class' => 'page-header']) }}
>
    {{ $slot }}
</x-ui.page-header>
