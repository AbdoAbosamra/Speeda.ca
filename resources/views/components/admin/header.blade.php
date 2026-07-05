@props([
    'eyebrow' => null,
    'title' => null,
    'subtitle' => null,
])

<x-ui.page-header
    :eyebrow="$eyebrow"
    :title="$title"
    :subtitle="$subtitle"
    {{ $attributes->merge(['class' => 'admin-page-header']) }}
>
    {{ $slot }}

    @isset($actions)
        <x-slot:actions>{{ $actions }}</x-slot:actions>
    @endisset
</x-ui.page-header>
