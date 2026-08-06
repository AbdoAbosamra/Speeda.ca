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

            {{-- Filters --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.activity_logs') }}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-muted">{{ __('admin.search') }}</label>
                            <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                                   placeholder="{{ __('admin.item') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small text-muted">{{ __('admin.action') }}</label>
                            <select name="action" class="form-select">
                                <option value="">{{ __('admin.all') }}</option>
                                @foreach($filterOptions['actions'] as $action)
                                    <option value="{{ $action }}" @selected(request('action') === $action)>
                                        {{ ucfirst(str_replace('_', ' ', $action)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small text-muted">{{ __('admin.item') }}</label>
                            <select name="model_type" class="form-select">
                                <option value="">{{ __('admin.all') }}</option>
                                @foreach($filterOptions['models'] as $model)
                                    <option value="{{ $model }}" @selected(request('model_type') === $model)>
                                        {{ class_basename($model) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small text-muted">{{ __('admin.admin') }}</label>
                            <select name="admin_id" class="form-select">
                                <option value="">{{ __('admin.all') }}</option>
                                @foreach($filterOptions['admins'] as $id => $name)
                                    <option value="{{ $id }}" @selected((string) request('admin_id') === (string) $id)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small text-muted">{{ __('admin.date') }}</label>
                            <div class="input-group">
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                        </div>
                        <div class="col-12 d-flex flex-wrap align-items-center gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="include_undone" value="1"
                                       id="includeUndone" @checked(request()->boolean('include_undone'))>
                                <label class="form-check-label small" for="includeUndone">
                                    {{ __('admin.include_undone') }}
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                <i class="fas fa-filter me-1"></i>{{ __('admin.filter') }}
                            </button>
                            <a href="{{ route('admin.activity_logs') }}" class="btn btn-outline-secondary rounded-pill px-4">
                                <i class="fas fa-undo me-1"></i>{{ __('admin.reset') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-lg" style="border-radius: 16px; background: white;">
                <div class="card-header bg-white" style="border-bottom: 2px solid #f1f5f9; border-radius: 16px 16px 0 0;">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-history me-2 text-primary"></i>{{ __('admin.recent_activity') }}
                        <span class="badge bg-secondary ms-2">{{ $logs->total() }}</span>
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
                                                <span class="fw-semibold">{{ $log->admin->name ?? 'System' }}</span>
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
                                            {{-- admin_logs has no "details" column; everything lives in the
                                                 JSON "changes" payload, so render that instead. --}}
                                            @php
                                                $changes = is_array($log->changes) ? $log->changes : [];
                                                $changedKeys = array_keys($changes['after'] ?? []);
                                                $extraKeys = array_keys(array_diff_key($changes, array_flip(['before', 'after', 'created', 'deleted'])));
                                            @endphp
                                            <small class="text-muted">
                                                @if($log->action === 'update' && $changedKeys)
                                                    {{ __('admin.changed') }}: {{ Str::limit(implode(', ', $changedKeys), 60) }}
                                                @elseif(isset($changes['deleted']))
                                                    {{ __('admin.deleted_item') }}
                                                @elseif(isset($changes['created']))
                                                    {{ __('admin.created_item') }}
                                                @elseif($extraKeys)
                                                    {{ Str::limit(collect($extraKeys)->map(fn ($k) => $k . ': ' . (is_scalar($changes[$k]) ? $changes[$k] : json_encode($changes[$k])))->implode(' · '), 80) }}
                                                @else
                                                    &mdash;
                                                @endif
                                            </small>
                                            @if($log->ip_address)
                                                <div class="text-muted" style="font-size: .72rem;">{{ $log->ip_address }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-semibold">{{ $log->created_at->format('Y-m-d') }}</span>
                                                <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($log->is_undone)
                                                <span class="badge bg-light text-muted border">
                                                    <i class="fas fa-rotate-left me-1"></i>{{ __('admin.undone') }}
                                                </span>
                                            @elseif(in_array($log->action, ['create', 'update', 'delete', 'deactivate', 'activate']) && $log->created_at->diffInHours(now()) < 24)
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
                        {{ $logs->links('components.global-pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
