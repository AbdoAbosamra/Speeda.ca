@extends('layouts.app')
@section('title', __('offer_submission.enroll'))
@section('content')
<div class="container py-5" style="max-width:760px" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
        <h1 class="h3 fw-bold">{{ __('offer_submission.enroll') }}</h1>
        <p class="text-muted">{{ __('offer_submission.enroll_intro') }}</p>
        @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ route('provider.enroll.store') }}" class="d-grid gap-3">
            @csrf
            <div><label class="form-label" for="name">{{ __('offer_submission.name') }}</label><input id="name" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required maxlength="255"></div>
            <div><label class="form-label" for="profession">{{ __('offer_submission.profession') }}</label><select id="profession" name="profession" class="form-select" required>
                <option value="">{{ __('offer_submission.choose') }}</option>
                @foreach($professions as $profession)<option value="{{ $profession->id }}" @selected((string) old('profession') === (string) $profession->id)>{{ $profession->translated_name }}</option>@endforeach
                <option value="other" @selected(old('profession') === 'other')>{{ __('offer_submission.other') }}</option>
            </select></div>
            <div><label class="form-label" for="city">{{ __('offer_submission.city') }}</label><select id="city" name="city" class="form-select" required>
                <option value="">{{ __('offer_submission.choose') }}</option>
                @foreach($locationGroups as $group => $locations)<optgroup label="{{ $group }}">@foreach($locations as $location)<option value="{{ $location->city }}" @selected(old('city') === $location->city)>{{ $location->city }}</option>@endforeach</optgroup>@endforeach
            </select></div>
            <div><label class="form-label" for="mobile">{{ __('offer_submission.phone') }}</label><input id="mobile" name="mobile" type="tel" class="form-control" dir="ltr" value="{{ old('mobile') }}" placeholder="+1 613 555 0123" required></div>
            <div><label class="form-label" for="whatsapp_number">{{ __('offer_submission.whatsapp') }}</label><input id="whatsapp_number" name="whatsapp_number" type="tel" class="form-control" dir="ltr" value="{{ old('whatsapp_number') }}" placeholder="+1 613 555 0123"></div>
            <label class="d-flex gap-2 align-items-center"><input type="checkbox" name="terms" value="1" required @checked(old('terms'))><span>{{ __('offer_submission.accept_terms') }} <a href="{{ route('terms-of-service') }}" target="_blank" rel="noopener">{{ __('offer_submission.terms') }}</a></span></label>
            <button type="submit" class="btn btn-primary py-3 rounded-3 fw-bold">{{ __('offer_submission.enroll_continue') }}</button>
        </form>
    </div>
</div>
@endsection
