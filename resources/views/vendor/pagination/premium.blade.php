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
                <a href="{{ $paginator->previousPageUrl() }}" class="pagination-nav-btn">
                    <i class="fas fa-chevron-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i>
                </a>
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
                                <span class="pagination-link active">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
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
                <a href="{{ $paginator->nextPageUrl() }}" class="pagination-nav-btn">
                    <i class="fas fa-chevron-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i>
                </a>
            @else
                <button type="button" class="pagination-nav-btn" disabled>
                    <i class="fas fa-chevron-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i>
                </button>
            @endif
        </nav>
    </div>
@endif
