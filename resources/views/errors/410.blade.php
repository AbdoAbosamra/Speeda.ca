@extends('layouts.app')

@section('title', '410 - Resource Gone')

@section('content')
<div class="container py-5 my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 text-center">
            <div class="card shadow-sm border-0 rounded-4 p-4 p-md-5">
                <div class="mb-4">
                    <span class="badge bg-secondary-subtle text-secondary fs-6 px-3 py-2 rounded-pill mb-3">
                        <i class="fas fa-archive me-1"></i> HTTP 410 Gone
                    </span>
                    <h1 class="display-1 fw-bold text-secondary mb-2">410</h1>
                    <h2 class="h3 fw-semibold text-dark mb-3">
                        {{ __('general.page_gone') }}
                    </h2>
                    <p class="text-muted mb-4">
                        {{ __('general.page_gone_desc') }}
                    </p>
                </div>

                <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                    <a href="{{ route('home') }}" class="btn btn-primary px-4 py-2 rounded-3">
                        <i class="fas fa-home me-2"></i> {{ __('general.home') }}
                    </a>
                    <a href="{{ route('service-providers.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3">
                        <i class="fas fa-search me-2"></i> {{ __('service_provider.browse_providers') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
