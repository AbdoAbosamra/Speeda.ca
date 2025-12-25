@extends('layouts.app')

@section('title', __('service_provider.access_denied'))

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-lock fa-5x text-danger"></i>
                    </div>
                    <h1 class="display-4 text-danger mb-3">{{ $title ?? __('service_provider.access_denied') }}</h1>
                    <p class="lead text-muted mb-4">
                        {{ $message ?? __('service_provider.unauthorized_access_message') }}
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('service-providers.index') }}" class="btn btn-primary px-4">
                            <i class="fas fa-list me-2"></i>{{ __('service_provider.view_all_providers') }}
                        </a>
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary px-4">
                            <i class="fas fa-home me-2"></i>{{ __('general.home') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
