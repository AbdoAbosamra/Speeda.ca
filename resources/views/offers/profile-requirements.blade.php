@if(auth()->check() && auth()->id() === $serviceProvider->user_id)
<div class="container pt-4">
    <div class="alert alert-info d-flex flex-wrap gap-3 justify-content-between align-items-center mb-4">
        <div>
            @if(session('offer_profile_completion'))
                <strong>{{ __('offer_submission.profile_heading') }}</strong>
                @php($missingOfferFields = $serviceProvider->missingOfferProfileFields())
                <p class="mb-0">{{ $missingOfferFields ? __('offer_submission.complete_profile', ['fields' => implode(app()->getLocale() === 'ar' ? '، ' : ', ', $missingOfferFields)]) : __('offer_submission.profile_ready') }}</p>
            @else
                <strong>{{ __('offer_submission.create') }}</strong>
                <p class="mb-0">{{ __('offer_submission.review_notice') }}</p>
            @endif
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a class="btn btn-primary" href="{{ route('provider.offers.create') }}">{{ __('offer_submission.continue') }}</a>
            <a class="btn btn-outline-primary" href="{{ route('provider.offers.index') }}">{{ __('offer_submission.my_offers') }}</a>
        </div>
    </div>
</div>
@endif
