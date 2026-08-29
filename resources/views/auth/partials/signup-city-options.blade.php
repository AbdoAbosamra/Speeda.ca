<div class="select-option" data-value="">{{ __('auth.select_city') }}</div>
@foreach(($locationGroups ?? []) as $groupName => $groupLocations)
    <div class="select-group-header">
        {{ $groupName }}
    </div>
    @foreach($groupLocations as $location)
        <div class="select-option grouped-option" data-value="{{ $location->city }}">
            {{ $location->city }}
        </div>
    @endforeach
@endforeach
