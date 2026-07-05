@props([
    'responsive' => true,
])

<section {{ $attributes->merge(['class' => 'admin-table-card']) }}>
    @isset($header)
        <div class="admin-card-header">{{ $header }}</div>
    @endisset

    @if(filter_var($responsive, FILTER_VALIDATE_BOOLEAN))
        <div class="table-responsive">{{ $slot }}</div>
    @else
        {{ $slot }}
    @endif
</section>
