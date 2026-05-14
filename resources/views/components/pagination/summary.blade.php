<div class="speeda-pagination__summary">
    <span class="speeda-pagination__emoji" aria-hidden="true">📄</span>

    @if ($isLengthAware)
        <span class="speeda-pagination__summary-text">
            {!! __('pagination.showing_results', [
                'first' => '<strong class="speeda-pagination__number speeda-pagination__number--accent">' . e($formatNumber($firstItem)) . '</strong>',
                'last' => '<strong class="speeda-pagination__number speeda-pagination__number--accent">' . e($formatNumber($lastItem)) . '</strong>',
                'total' => '<strong class="speeda-pagination__number speeda-pagination__number--dark">' . e($formatNumber($total)) . '</strong>',
            ]) !!}
        </span>
    @else
        <span class="speeda-pagination__summary-text">{{ __('pagination.showing_page', ['page' => $formatNumber($currentPage)]) }}</span>
    @endif
</div>
