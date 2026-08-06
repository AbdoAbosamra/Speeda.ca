@props([
    'value' => null,   // row id; omit for the header "select all" box
    'master' => false, // true renders the header toggle
])

{{--
    Checkboxes carry the id in data-bulk-id rather than name="ids[]", because
    they live inside the table (outside the submission form). The bulk form
    turns the checked ones into hidden inputs at submit time.
--}}
@if($master)
    <input type="checkbox"
           class="admin-bulk-check"
           data-bulk-master
           @click="toggleAll($event)"
           aria-label="{{ __('admin.bulk_select_all') }}">
@else
    <input type="checkbox"
           class="admin-bulk-check"
           data-bulk-id="{{ $value }}"
           @change="recount()"
           aria-label="{{ __('admin.bulk_select_row') }} {{ $value }}">
@endif
