<div class="select-option" data-value="">{{ __('auth.select_profession') }}</div>
@if(isset($professionGroups) && $professionGroups->count())
    @foreach($professionGroups as $groupName => $groupProfessions)
        <div class="select-group-header">
            {{ $groupName }}
        </div>
        @foreach($groupProfessions as $profession)
            <div class="select-option grouped-option" data-value="{{ $profession->id }}">
                {{ $profession->localized_name }}
            </div>
        @endforeach
    @endforeach
@elseif(isset($professions) && $professions)
    @foreach($professions as $profession)
        <div class="select-option" data-value="{{ $profession->id }}">
            {{ $profession->localized_name }}
        </div>
    @endforeach
@endif
