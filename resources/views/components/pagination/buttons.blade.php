<div class="speeda-pagination__buttons">
    @if ($paginator->onFirstPage())
        <span
            class="speeda-pagination__nav speeda-pagination__nav--disabled"
            aria-disabled="true"
            aria-label="{{ __('pagination.previous') }}"
        >
            {{ $prevIcon }} <span class="speeda-pagination__nav-label">{{ __('pagination.previous_short') }}</span>
        </span>
    @else
        <a
            href="{{ $paginator->previousPageUrl() }}"
            wire:click.prevent="previousPage"
            rel="prev"
            class="speeda-pagination__nav"
            aria-label="{{ __('pagination.previous') }}"
        >
            {{ $prevIcon }} <span class="speeda-pagination__nav-label">{{ __('pagination.previous_short') }}</span>
        </a>
    @endif

    @if ($isLengthAware)
        <div class="speeda-pagination__pages">
            @foreach ($pages as $page)
                @if ($page === '...')
                    <span class="speeda-pagination__dots" aria-hidden="true">⋯</span>
                @elseif ($page === $currentPage)
                    <span
                        class="speeda-pagination__page speeda-pagination__page--active"
                        aria-current="page"
                    >
                        {{ $formatNumber($page) }}
                    </span>
                @else
                    <a
                        href="{{ $paginator->url($page) }}"
                        wire:click.prevent="gotoPage({{ $page }})"
                        class="speeda-pagination__page"
                        aria-label="{{ __('pagination.go_to_page', ['page' => $formatNumber($page)]) }}"
                    >
                        {{ $formatNumber($page) }}
                    </a>
                @endif
            @endforeach
        </div>

        @include('components.pagination.mobile')
    @endif

    @if ($paginator->hasMorePages())
        <a
            href="{{ $paginator->nextPageUrl() }}"
            wire:click.prevent="nextPage"
            rel="next"
            class="speeda-pagination__nav"
            aria-label="{{ __('pagination.next') }}"
        >
            <span class="speeda-pagination__nav-label">{{ __('pagination.next_short') }}</span> {{ $nextIcon }}
        </a>
    @else
        <span
            class="speeda-pagination__nav speeda-pagination__nav--disabled"
            aria-disabled="true"
            aria-label="{{ __('pagination.next') }}"
        >
            <span class="speeda-pagination__nav-label">{{ __('pagination.next_short') }}</span> {{ $nextIcon }}
        </span>
    @endif
</div>
