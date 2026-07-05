@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
    @endphp
    <nav class="speeda-pagination" dir="ltr" role="navigation" aria-label="Pagination Navigation" style="display:block !important;">
        <div class="speeda-pagination__card" style="display:flex !important; flex-direction:column !important;">
            <div class="speeda-pagination__meta" style="display:flex !important;">
                <div class="speeda-pagination__summary" style="display:inline-flex !important;">
                    <span class="speeda-pagination__emoji" aria-hidden="true">📄</span>
                    <span class="speeda-pagination__summary-text">
                        Showing <strong
                            class="speeda-pagination__number speeda-pagination__number--accent">{{ $paginator->firstItem() }}</strong>
                        to <strong
                            class="speeda-pagination__number speeda-pagination__number--accent">{{ $paginator->lastItem() }}</strong>
                        of <strong
                            class="speeda-pagination__number speeda-pagination__number--dark">{{ $paginator->total() }}</strong>
                        results
                    </span>
                </div>
                <div class="speeda-pagination__progress" style="display:inline-flex !important;">
                    <span class="speeda-pagination__emoji" aria-hidden="true">📊</span>
                    <span aria-label="Page {{ $currentPage }} of {{ $lastPage }}">
                        {{ $currentPage }} / {{ $lastPage }}
                    </span>
                    <progress class="speeda-pagination__progress-bar" value="{{ $currentPage }}" max="{{ $lastPage }}"
                        aria-hidden="true"></progress>
                </div>
            </div>

            <div class="speeda-pagination__buttons" style="display:flex !important;">
                {{-- Previous Page --}}
                @if ($paginator->onFirstPage())
                    <span class="speeda-pagination__nav speeda-pagination__nav--disabled" aria-disabled="true"
                        aria-label="Previous page" style="display:inline-flex !important;">
                        ← <span class="speeda-pagination__nav-label">Prev</span>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="speeda-pagination__nav" rel="prev"
                        aria-label="Previous page" style="display:inline-flex !important;">
                        ← <span class="speeda-pagination__nav-label">Prev</span>
                    </a>
                @endif

                {{-- Page Numbers --}}
                <div class="speeda-pagination__pages" style="display:flex !important;">
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span class="speeda-pagination__dots" aria-hidden="true" style="display:inline-flex !important;">⋯</span>
                        @endif
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $currentPage)
                                    <span class="speeda-pagination__page speeda-pagination__page--active"
                                        aria-current="page" style="display:inline-flex !important;">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="speeda-pagination__page" aria-label="Go to page {{ $page }}" style="display:inline-flex !important;">{{ $page }}</a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                </div>

                {{-- Mobile Count --}}
                <div class="speeda-pagination__mobile-count" style="display:flex !important;">
                    <span class="speeda-pagination__mobile-current">{{ $currentPage }}</span>
                    <span class="speeda-pagination__mobile-separator">/</span>
                    <span>{{ $lastPage }}</span>
                </div>

                {{-- Next Page --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="speeda-pagination__nav" aria-label="Next page" style="display:inline-flex !important;">
                        <span class="speeda-pagination__nav-label">Next</span> →
                    </a>
                @else
                    <span class="speeda-pagination__nav speeda-pagination__nav--disabled" aria-disabled="true"
                        aria-label="Next page" style="display:inline-flex !important;">
                        <span class="speeda-pagination__nav-label">Next</span> →
                    </span>
                @endif
            </div>
        </div>
    </nav>

    <style>
        .speeda-pagination {
            width: 100%;
            margin-top: 1.5rem;
        }

        .speeda-pagination__card {
            background: linear-gradient(135deg, #ffffff 0%, #fefefe 100%);
            border-radius: 28px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.02);
            padding: 16px 24px;
            border: 1px solid rgba(203, 213, 225, 0.4);
        }

        .speeda-pagination__meta {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid #eef2f6;
        }

        .speeda-pagination__summary {
            font-size: 14px;
            color: #475569;
            background: #f8fafc;
            padding: 6px 14px;
            border-radius: 40px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .speeda-pagination__emoji {
            font-weight: 500;
        }

        .speeda-pagination__number--accent {
            color: #3b82f6;
            margin: 0 4px;
        }

        .speeda-pagination__number--dark {
            color: #1e293b;
            margin: 0 4px;
        }

        .speeda-pagination__progress {
            font-size: 13px;
            color: #475569;
            background: #f1f5f9;
            padding: 4px 12px;
            border-radius: 30px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .speeda-pagination__progress-bar {
            width: 60px;
            height: 4px;
            border-radius: 10px;
            overflow: hidden;
            appearance: none;
            border: none;
        }

        .speeda-pagination__progress-bar::-webkit-progress-bar {
            background: #e2e8f0;
            border-radius: 10px;
        }

        .speeda-pagination__progress-bar::-webkit-progress-value {
            background: #3b82f6;
            border-radius: 10px;
        }

        .speeda-pagination__progress-bar::-moz-progress-bar {
            background: #3b82f6;
            border-radius: 10px;
        }

        .speeda-pagination__buttons {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .speeda-pagination__nav {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0 14px;
            background: #ffffff;
            color: #1e293b;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
            cursor: pointer;
            line-height: 1;
        }

        .speeda-pagination__nav:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.03);
        }

        .speeda-pagination__nav--disabled {
            background: #f1f5f9;
            color: #94a3b8;
            cursor: default;
            opacity: 0.6;
            pointer-events: none;
            box-shadow: none;
        }

        .speeda-pagination__nav-label {
            display: none;
        }

        @media (min-width: 640px) {
            .speeda-pagination__nav-label {
                display: inline;
            }
        }

        .speeda-pagination__pages {
            display: none;
            align-items: center;
            gap: 4px;
        }

        @media (min-width: 768px) {
            .speeda-pagination__pages {
                display: flex;
            }
        }

        .speeda-pagination__page {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            background: #ffffff;
            color: #334155;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        }

        .speeda-pagination__page:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            transform: translateY(-2px);
        }

        .speeda-pagination__page--active {
            background: #3b82f6;
            color: white;
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.25);
            transform: scale(1.02);
            cursor: default;
            border-color: #3b82f6;
        }

        .speeda-pagination__page--active:hover {
            background: #3b82f6;
            transform: scale(1.02);
        }

        .speeda-pagination__dots {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            color: #94a3b8;
            font-size: 18px;
            font-weight: 500;
        }

        .speeda-pagination__mobile-count {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 4px 14px;
            background: #f1f5f9;
            border-radius: 20px;
            font-size: 14px;
        }

        @media (min-width: 768px) {
            .speeda-pagination__mobile-count {
                display: none;
            }
        }

        .speeda-pagination__mobile-current {
            font-weight: 700;
            color: #3b82f6;
        }

        .speeda-pagination__mobile-separator {
            color: #94a3b8;
        }
    </style>
@endif