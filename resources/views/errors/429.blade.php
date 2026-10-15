@php
    $retryAfter = (int) (isset($exception) ? ($exception->getHeaders()['Retry-After'] ?? 0) : 0);
@endphp
@include('errors._card', [
    'code' => 429,
    'icon' => 'fa-hourglass-half',
    'heading' => __('errors.page_429_title'),
    'description' => __('errors.page_429_desc'),
    'note' => $retryAfter > 0 ? __('errors.page_429_retry', ['seconds' => $retryAfter]) : null,
])
