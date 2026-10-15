{{--
    Shared body for the branded 4xx pages (403, 419, 429). Same card as 404.
    Expects: $code, $icon, $heading, $description, and optionally $note.
    The 500 page does NOT use this: it must render even when the layout's own
    queries are what failed, so it is self-contained.
--}}
@extends('layouts.app')

@section('title', $code . ' - ' . $heading)

@section('content')
<div class="container py-5 my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 text-center">
            <div class="card shadow-sm border-0 rounded-4 p-4 p-md-5">
                <div class="mb-4">
                    <span class="badge bg-primary-subtle text-primary fs-6 px-3 py-2 rounded-pill mb-3">
                        <i class="fas {{ $icon }} me-1"></i> HTTP {{ $code }}
                    </span>
                    <h1 class="display-1 fw-bold text-primary mb-2">{{ $code }}</h1>
                    <h2 class="h3 fw-semibold text-dark mb-3">{{ $heading }}</h2>
                    <p class="text-muted mb-2">{{ $description }}</p>
                    @if (!empty($note))
                        <p class="text-muted small mb-0">{{ $note }}</p>
                    @endif
                </div>

                <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('home') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3">
                        <i class="fas fa-arrow-left me-2"></i> {{ __('errors.go_back') }}
                    </a>
                    <a href="{{ route('home') }}" class="btn btn-primary px-4 py-2 rounded-3">
                        <i class="fas fa-home me-2"></i> {{ __('general.home') }}
                    </a>
                    @if ($code === 403)
                        <a href="{{ route('help-center') }}" class="btn btn-outline-primary px-4 py-2 rounded-3">
                            <i class="fas fa-life-ring me-2"></i> {{ __('errors.contact_support') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
