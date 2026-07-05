@php
    $isRtl = app()->getLocale() === 'ar';
    $isLengthAware = method_exists($paginator, 'total') && method_exists($paginator, 'lastPage');
    $currentPage = $paginator->currentPage();
    $lastPage = $isLengthAware ? max(1, $paginator->lastPage()) : null;
    $progressPercent = $isLengthAware ? min(100, max(0, ($currentPage / $lastPage) * 100)) : 0;
    $firstItem = method_exists($paginator, 'firstItem') ? $paginator->firstItem() : null;
    $lastItem = method_exists($paginator, 'lastItem') ? $paginator->lastItem() : null;
    $total = $isLengthAware ? $paginator->total() : null;

    $formatNumber = static function ($value): string {
        if ($value === null) {
            return '0';
        }

        if (class_exists(\NumberFormatter::class)) {
            return (new \NumberFormatter(app()->getLocale(), \NumberFormatter::DECIMAL))->format((int) $value);
        }

        return number_format((int) $value);
    };

    $window = 2;
    $pages = [];

    if ($isLengthAware) {
        for ($page = 1; $page <= $lastPage; $page++) {
            if ($page === 1 || $page === $lastPage || abs($page - $currentPage) <= $window) {
                $pages[] = $page;
            } elseif (end($pages) !== '...') {
                $pages[] = '...';
            }
        }
    }

    $prevIcon = $isRtl ? '→' : '←';
    $nextIcon = $isRtl ? '←' : '→';
@endphp

<nav class="speeda-pagination" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" role="navigation" aria-label="{{ __('pagination.navigation_label') }}">
    <div class="speeda-pagination__card">
        <div class="speeda-pagination__meta">
            @include('components.pagination.summary')

            @if ($isLengthAware)
                @include('components.pagination.progress')
            @endif
        </div>

        @include('components.pagination.buttons')
    </div>
</nav>
