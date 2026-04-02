@extends('layouts.app')

@section('content')
    <div class="admin-content-wrapper" style="margin-left: 0 !important;">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 fw-bold mb-1">Provider Activity Monitor</h1>
                    <p class="text-muted mb-0">Internal provider activity aggregated from analytics + gallery media.</p>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>

            <div class="card border-0 shadow-lg" style="border-radius: 16px; background: #fff;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background: linear-gradient(135deg, #f8fafc, #f1f5f9);">
                                <tr>
                                    <th class="fw-bold py-3">Provider</th>
                                    <th class="fw-bold py-3">Profile Views</th>
                                    <th class="fw-bold py-3">WhatsApp Clicks</th>
                                    <th class="fw-bold py-3">Completion</th>
                                    <th class="fw-bold py-3">Images Status</th>
                                    <th class="fw-bold py-3">Created</th>
                                    <th class="fw-bold py-3">Last Activity</th>
                                    <th class="fw-bold py-3 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($providers as $p)
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td>
                                            <div class="d-flex flex-column">
                                                <a href="{{ route('service-providers.show', $p->id) }}" class="fw-semibold">
                                                    {{ $p->company_name ?: ('Provider #' . $p->id) }}
                                                </a>
                                                <small class="text-muted">ID: {{ $p->id }}</small>
                                            </div>
                                        </td>
                                        <td>{{ (int) $p->profile_views }}</td>
                                        <td>{{ (int) $p->whatsapp_clicks }}</td>
                                        <td>
                                            <span class="badge bg-{{ ((int) $p->profile_completion_percent) >= 100 ? 'success' : 'warning' }} rounded-pill">
                                                {{ (int) $p->profile_completion_percent }}%
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="{{ $p->has_profile_photo ? 'text-success' : 'text-danger' }}">
                                                    {{ $p->has_profile_photo ? 'Photo: Yes' : 'Photo: No' }}
                                                </span>
                                                <span class="text-muted">Gallery: {{ (int) $p->gallery_count }}/4</span>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $p->created_at ? \Carbon\Carbon::parse($p->created_at)->format('Y-m-d') : '-' }}
                                            </small>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $p->last_activity_at ? \Carbon\Carbon::parse($p->last_activity_at)->diffForHumans() : '-' }}
                                            </small>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex gap-2 justify-content-end">
                                                <a href="{{ route('service-providers.show', $p->id) }}"
                                                    class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                    <i class="fas fa-user me-1"></i>Profile
                                                </a>
                                                <a href="{{ route('admin.provider_activity_monitor.show', $p->id) }}"
                                                    class="btn btn-sm btn-primary rounded-pill px-3">
                                                    <i class="fas fa-chart-line me-1"></i>Analytics
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <i class="fas fa-chart-line fa-3x text-muted mb-3 d-block"></i>
                                            <p class="text-muted mb-0">No provider activity yet.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 d-flex justify-content-center pb-4">
                        {{ $providers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

