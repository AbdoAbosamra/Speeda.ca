@if ($paginator->hasPages())
    <div class="premium-pagination-wrapper">
        {{-- Status --}}
        <div class="pagination-status">
            @if(app()->getLocale() === 'ar')
                عرض <b>{{ $paginator->firstItem() }}</b> إلى <b>{{ $paginator->lastItem() }}</b> من أصل <b>{{ $paginator->total() }}</b> نتيجة
            @else
                Showing <b>{{ $paginator->firstItem() }}</b> to <b>{{ $paginator->lastItem() }}</b> of <b>{{ $paginator->total() }}</b> results
            @endif
        </div>

        {{-- Navigation --}}
        <nav class="premium-pagination-nav">
            {{-- Previous Page --}}
            @if ($paginator->onFirstPage())
                <button type="button" class="pagination-nav-btn" disabled>
                    <i class="fas fa-chevron-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i>
                </button>
            @else
                <button type="button" wire:click="previousPage" class="pagination-nav-btn" wire:loading.attr="disabled">
                    <i class="fas fa-chevron-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i>
                </button>
            @endif

            {{-- Page Numbers --}}
            <div class="d-none d-md-flex align-items-center gap-1">
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="pagination-dots">{{ $element }}</span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <button type="button" class="pagination-link active" wire:key="paginator-page-{{ $page }}">
                                    {{ $page }}
                                </button>
                            @else
                                <button type="button" wire:click="gotoPage({{ $page }})" class="pagination-link" wire:key="paginator-page-{{ $page }}">
                                    {{ $page }}
                                </button>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Mobile Simple View --}}
            <div class="d-flex d-md-none align-items-center bg-light px-3 py-1 rounded-pill mx-2">
                <span class="fw-bold text-primary">{{ $paginator->currentPage() }}</span>
                <span class="mx-2 text-muted">/</span>
                <span class="text-muted">{{ $paginator->lastPage() }}</span>
            </div>

            {{-- Next Page --}}
            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage" class="pagination-nav-btn" wire:loading.attr="disabled">
                    <i class="fas fa-chevron-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i>
                </button>
            @else
                <button type="button" class="pagination-nav-btn" disabled>
                    <i class="fas fa-chevron-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i>
                </button>
            @endif
        </nav>
    </div>
@endif
