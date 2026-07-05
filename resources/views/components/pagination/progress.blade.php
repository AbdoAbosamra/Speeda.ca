<div class="speeda-pagination__progress">
    <span class="speeda-pagination__emoji" aria-hidden="true">📊</span>
    <span aria-label="{{ __('pagination.page_progress', ['current' => $formatNumber($currentPage), 'last' => $formatNumber($lastPage)]) }}">
        {{ $formatNumber($currentPage) }} / {{ $formatNumber($lastPage) }}
    </span>
    <progress class="speeda-pagination__progress-bar" value="{{ $currentPage }}" max="{{ $lastPage }}" aria-hidden="true"></progress>
</div>
