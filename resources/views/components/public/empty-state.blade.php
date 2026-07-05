@props([
    'icon' => null,
    'title' => null,
    'description' => null,
])

<x-ui.empty-state
    :icon="$icon"
    :title="$title"
    :description="$description"
    {{ $attributes->merge(['class' => 'empty-state']) }}
>
    {{ $slot }}

    @isset($actions)
        <x-slot:actions>{{ $actions }}</x-slot:actions>
    @endisset
</x-ui.empty-state>
