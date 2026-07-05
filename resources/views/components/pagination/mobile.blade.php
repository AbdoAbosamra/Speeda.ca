@if ($isLengthAware)
    <div class="speeda-pagination__mobile-count">
        <span class="speeda-pagination__mobile-current">{{ $formatNumber($currentPage) }}</span>
        <span class="speeda-pagination__mobile-separator">/</span>
        <span>{{ $formatNumber($lastPage) }}</span>
    </div>
@endif
