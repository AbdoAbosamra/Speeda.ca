@extends('layouts.app')
@section('title', __('offer_submission.my_offers'))
@include('admin.offers.partials.styles')
@section('content')
<div class="container so-scope ao-page py-5" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h1 class="fw-bold">{{ __('offer_submission.my_offers') }}</h1><p class="text-muted mb-0">{{ __('offer_submission.review_notice') }}</p></div>
        <a href="{{ route('provider.offers.create') }}" class="so-btn so-btn-primary"><i class="fas fa-plus"></i> {{ __('offer_submission.create') }}</a>
    </div>
    @if(session('success'))<div class="alert alert-success" role="status"><i class="fas fa-circle-check"></i> {{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger" role="alert"><i class="fas fa-circle-exclamation"></i> {{ session('error') }}</div>@endif
    <div class="d-grid gap-3">
        @forelse($offers as $offer)
            <article class="ao-card p-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h2 class="h5 fw-bold mb-0">{{ $offer->localized_title }}</h2>
                    <span class="ao-pill ao-pill--{{ $offer->status() }}">{{ __('offer_submission.'.$offer->status()) }}</span>
                </div>
                <p class="text-muted mt-2">{{ $offer->localized_summary }}</p>
                @if($offer->review_status === 'pending')
                    <p class="mb-0 text-warning-emphasis"><i class="far fa-clock"></i> {{ __('offer_submission.pending_notice') }}</p>
                    <small class="text-muted d-block">{{ __('offer_submission.submitted_at') }}: {{ $offer->submitted_at?->format('Y-m-d H:i') }}</small>
                    <a href="{{ route('provider.offers.edit', $offer) }}" class="so-btn so-btn-ghost mt-3"><i class="fas fa-pen"></i> {{ __('offer_submission.edit') }}</a>
                @elseif($offer->review_status === 'rejected')
                    <div class="alert alert-danger mb-3"><strong>{{ __('offer_submission.rejection_reason') }}:</strong> {{ $offer->rejection_reason }}</div>
                    <a href="{{ route('provider.offers.edit', $offer) }}" class="so-btn so-btn-primary"><i class="fas fa-rotate-right"></i> {{ __('offer_submission.edit_and_resubmit') }}</a>
                @elseif($offer->isLive())
                    <a href="{{ route('offers.show', $offer->slug) }}" class="so-btn so-btn-ghost">{{ __('offers.view_offer') }} <i class="fas fa-arrow-right"></i></a>
                @endif
            </article>
        @empty
            <div class="ao-card p-5 text-center"><i class="fas fa-bullhorn fs-1 text-primary mb-3"></i><p>{{ __('offer_submission.empty') }}</p></div>
        @endforelse
    </div>
    <div class="mt-4">{{ $offers->links() }}</div>
</div>
@endsection
