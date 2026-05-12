@extends('layouts.app')

@section('content')
    <div class="admin-content-wrapper" style="margin-inline-start: 0 !important;">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 fw-bold mb-1">{{ __('admin.activity_logs') }}</h1>
                    <p class="text-muted mb-0">{{ __('admin.activity_logs_description') }}</p>
                </div>
            </div>

            <div class="card border-0 shadow-lg" style="border-radius: 16px; background: white;">
                <div class="card-header bg-white" style="border-bottom: 2px solid #f1f5f9; border-radius: 16px 16px 0 0;">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-history me-2 text-primary"></i>{{ __('admin.recent_activity') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background: linear-gradient(135deg, #f8fafc, #f1f5f9);">
                                <tr>
                                    <th class="fw-bold py-3">{{ __('admin.admin') }}</th>
                                    <th class="fw-bold py-3">{{ __('admin.action') }}</th>
                                    <th class="fw-bold py-3">{{ __('admin.item') }}</th>
                                    <th class="fw-bold py-3">{{ __('admin.details') }}</th>
                                    <th class="fw-bold py-3">{{ __('admin.date') }}</th>
                                    <th class="fw-bold py-3 text-center">{{ __('admin.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2"
                                                    style="width: 32px; height: 32px;">
                                                    <i class="fas fa-user text-secondary"></i>
                                                </div>
                                                <span class="fw-semibold">{{ $log->user->name ?? 'System' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $badgeClass = match ($log->action) {
                                                    'create' => 'success',
                                                    'update' => 'info',
                                                    'delete' => 'danger',
                                                    'deactivate' => 'warning',
                                                    'activate' => 'success',
                                                    'undo' => 'dark',
                                                    default => 'secondary'
                                                };
                                                $icon = match ($log->action) {
                                                    'create' => 'plus',
                                                    'update' => 'edit',
                                                    'delete' => 'trash',
                                                    'deactivate' => 'ban',
                                                    'activate' => 'check',
                                                    'undo' => 'undo',
                                                    default => 'circle'
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $badgeClass }} rounded-pill px-3">
                                                <i class="fas fa-{{ $icon }} me-1"></i>{{ ucfirst($log->action) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark">{{ $log->model_name }}</span>
                                            <br>
                                            <small class="text-muted">{{ class_basename($log->model_type) }}
                                                #{{ $log->model_id }}</small>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                @if($log->action == 'update' && isset($log->changes['after']))
                                                    Changed: {{ implode(', ', array_keys($log->changes['after'])) }}
                                                @elseif($log->action == 'delete')
                                                    Deleted item
                                                @elseif($log->action == 'undo')
                                                    {{ $log->details }}
                                                @else
                                                    {{ Str::limit($log->details, 50) }}
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-semibold">{{ $log->created_at->format('Y-m-d') }}</span>
                                                <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if(in_array($log->action, ['create', 'update', 'delete', 'deactivate', 'activate']) && $log->created_at->diffInHours(now()) < 24)
                                                <form action="{{ route('admin.undo', $log->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-dark rounded-pill px-3"
                                                        title="{{ __('admin.undo') }}"
                                                        onclick="return confirm('{{ __('admin.confirm_undo') }}')">
                                                        <i class="fas fa-undo me-1"></i>{{ __('admin.undo') }}
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="fas fa-history fa-3x mb-3 d-block"></i>
                                                <p class="mb-0">{{ __('admin.no_activity_logs') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4 d-flex justify-content-center">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection