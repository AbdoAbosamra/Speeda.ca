@extends('layouts.app')

@section('content')
    <div class="admin-content-wrapper" style="margin-inline-start: 0 !important;">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 fw-bold mb-1">Provider Analytics Details</h1>
                    <p class="text-muted mb-0">
                        Provider: {{ $provider->company_name ?: ('Provider #' . $provider->id) }}
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('service-providers.show', $provider->id) }}" class="btn btn-outline-secondary rounded-pill">
                        <i class="fas fa-user me-2"></i>View Profile
                    </a>
                    <a href="{{ route('admin.provider_activity_monitor.index') }}" class="btn btn-outline-secondary rounded-pill">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted mb-2">Profile Views</div>
                            <div class="display-6 fw-bold text-primary">{{ (int) ($summary->profile_views ?? 0) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted mb-2">WhatsApp Clicks</div>
                            <div class="display-6 fw-bold text-primary">{{ (int) ($summary->whatsapp_clicks ?? 0) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted mb-2">Last Activity</div>
                            <div class="display-6 fw-bold text-primary">
                                @if(!empty($summary->last_activity_at))
                                    {{ \Carbon\Carbon::parse($summary->last_activity_at)->diffForHumans() }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-lg" style="border-radius:16px;">
                <div class="card-header bg-white" style="border-bottom: 2px solid #f1f5f9; border-radius:16px 16px 0 0;">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-clock-rotate-left me-2 text-primary"></i>Event Timeline
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background: linear-gradient(135deg, #f8fafc, #f1f5f9);">
                                <tr>
                                    <th class="fw-bold py-3">Type</th>
                                    <th class="fw-bold py-3">Event</th>
                                    <th class="fw-bold py-3">Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($events as $e)
                                    <tr>
                                        <td>
                                            <span class="badge bg-{{ $e->action_type === 'view' ? 'secondary' : ($e->action_type === 'click_whatsapp' ? 'success' : 'primary') }}">
                                                {{ $e->action_type }}
                                            </span>
                                        </td>
                                        <td><span class="text-muted">Privacy-safe event metadata</span></td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-semibold">
                                                    {{ \Carbon\Carbon::parse($e->created_at)->format('Y-m-d') }}
                                                </span>
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::parse($e->created_at)->format('H:i:s') }}
                                                </small>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">
                                            No analytics events found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 d-flex justify-content-center">
                        {{ $events->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
