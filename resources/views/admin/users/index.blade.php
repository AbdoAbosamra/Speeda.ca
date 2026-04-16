@extends('layouts.app')

@section('title', __('admin.users_management'))

@section('content')
<!-- Admin Users Management with Tailwind + Alpine.js -->
<div class="admin-content-wrapper" style="margin-left: 0 !important;" x-data="{ showInactive: true, searchQuery: '', get visibleUsers() { return this.$refs.usersTable ? [...this.$refs.usersTable.querySelectorAll('tbody tr[data-user-id]')].filter(row => { const isActive = row.dataset.active === 'true'; const matchesSearch = this.searchQuery === '' || row.textContent.toLowerCase().includes(this.searchQuery.toLowerCase()); return (this.showInactive || isActive) && matchesSearch; }) : []; } }">
<div class="container py-4">
    <!-- Header with Stats -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">{{ __('admin.manage_users') }}</h1>
            <p class="text-muted mb-0">{{ __('admin.manage_all_users_status') }}</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white" style="border-radius: 12px; box-shadow: 0 4px 20px rgba(59, 130, 246, 0.3);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 fw-bold">{{ $stats['total'] ?? $users->total() }}</h3>
                            <small class="opacity-75">{{ __('admin.total_users') }}</small>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-users fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white" style="border-radius: 12px; box-shadow: 0 4px 20px rgba(34, 197, 94, 0.3);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 fw-bold">{{ $stats['active'] ?? 0 }}</h3>
                            <small class="opacity-75">{{ __('admin.active_users') }}</small>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-user-check fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-secondary text-white" style="border-radius: 12px; box-shadow: 0 4px 20px rgba(108, 117, 125, 0.3);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 fw-bold">{{ $stats['inactive'] ?? 0 }}</h3>
                            <small class="opacity-75">{{ __('admin.inactive_users') }}</small>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-user-slash fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white" style="border-radius: 12px; box-shadow: 0 4px 20px rgba(14, 165, 233, 0.3);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 fw-bold">{{ $stats['providers'] ?? 0 }}</h3>
                            <small class="opacity-75">{{ __('admin.service_providers') }}</small>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-briefcase fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter - Enhanced with Alpine.js -->
    <div class="card border-0 shadow-lg mb-4" style="border-radius: 16px; background: white;">
        <div class="card-header bg-white" style="border-bottom: 2px solid #f1f5f9; border-radius: 16px 16px 0 0;">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-filter me-2 text-primary"></i>{{ __('admin.search_and_filter') }}
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.users') }}" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">{{ __('admin.search_users') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-0 bg-light" 
                               placeholder="{{ __('admin.search_users_placeholder') }}" 
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('admin.filter_by_role') }}</label>
                    <select name="role" class="form-select border-0 bg-light">
                        <option value="">{{ __('admin.all_roles') }}</option>
                        <option value="client" {{ request('role') === 'client' ? 'selected' : '' }}>{{ __('admin.role_client') }}</option>
                        <option value="service_provider" {{ request('role') === 'service_provider' ? 'selected' : '' }}>{{ __('admin.role_service_provider') }}</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>{{ __('admin.role_admin') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">{{ __('admin.show_status') }}</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" id="showInactive" x-model="showInactive" style="transform: scale(1.2);">
                        <label class="form-check-label" for="showInactive" x-text="showInactive ? '{{ __('admin.showing_all') }}' : '{{ __('admin.active_only') }}'"></label>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100" style="border-radius: 12px; padding: 0.75rem; font-weight: 600;">
                        <i class="fas fa-search me-2"></i>{{ __('admin.search') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table - Enhanced -->
    <div class="card border-0 shadow-lg" style="border-radius: 16px; background: white;" x-ref="usersTable">
        <div class="card-header bg-white" style="border-bottom: 2px solid #f1f5f9; border-radius: 16px 16px 0 0;">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-users me-2 text-primary"></i>{{ __('admin.users_list') }}
                </h5>
                <div class="d-flex gap-2">
                    <span class="badge bg-success rounded-pill px-3 py-2">{{ $stats['active'] ?? 0 }} {{ __('admin.active') }}</span>
                    <span class="badge bg-secondary rounded-pill px-3 py-2">{{ $stats['inactive'] ?? 0 }} {{ __('admin.inactive') }}</span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: linear-gradient(135deg, #f8fafc, #f1f5f9);">
                        <tr>
                            <th class="fw-bold py-3">{{ __('admin.name') }}</th>
                            <th class="fw-bold py-3">{{ __('admin.email') }}</th>
                            <th class="fw-bold py-3">{{ __('admin.role') }}</th>
                            <th class="fw-bold py-3">{{ __('admin.status') }}</th>
                            <th class="fw-bold py-3">{{ __('admin.created_at') }}</th>
                            <th class="fw-bold py-3 text-center">{{ __('admin.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr 
                            data-user-id="{{ $user->id }}"
                            data-active="{{ $user->is_active ? 'true' : 'false' }}"
                            style="border-bottom: 1px solid #f1f5f9; transition: all 0.3s; {{ !$user->is_active ? 'background: #f8f9fa; opacity: 0.75;' : '' }}" 
                            onmouseover="this.style.background='{{ $user->is_active ? '#f8fafc' : '#e9ecef' }}'" 
                            onmouseout="this.style.background='{{ $user->is_active ? 'white' : '#f8f9fa' }}'">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div 
                                        class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                        style="width: 40px; height: 40px; background: {{ $user->is_active ? 'linear-gradient(135deg, #667eea, #764ba2)' : '#6c757d' }}; color: white; font-weight: bold;">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <strong class="{{ $user->is_active ? '' : 'text-muted' }}">{{ $user->name }}</strong>
                                        @if(!$user->is_active)
                                            <span class="badge bg-secondary ms-2">{{ __('admin.inactive') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="{{ $user->is_active ? '' : 'text-muted' }}">{{ $user->email }}</td>
                            <td>
                                <span class="badge rounded-pill px-3 py-2 fw-semibold" 
                                      style="background: {{ $user->role === 'admin' ? '#ef4444' : ($user->role === 'service_provider' ? '#10b981' : '#3b82f6') }};">
                                    {{ __('admin.role_' . $user->role) }}
                                </span>
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success rounded-pill px-3 py-2">
                                        <i class="fas fa-check-circle me-1"></i>{{ __('admin.active') }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-3 py-2">
                                        <i class="fas fa-ban me-1"></i>{{ __('admin.inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $user->created_at->format('Y-m-d H:i') }}</td>
                            <td class="text-center">
                                @if($user->id !== auth()->id())
                                    <!-- Toggle Status Button -->
                                    <form action="{{ route('admin.users.toggle', $user) }}" method="POST" 
                                          onsubmit="return confirm('{{ $user->is_active ? __('admin.confirm_deactivate_user') : __('admin.confirm_activate_user') }}');" 
                                          class="d-inline me-1">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="btn btn-sm {{ $user->is_active ? 'btn-warning' : 'btn-success' }} rounded-pill px-3" 
                                                style="transition: all 0.3s;"
                                                onmouseover="this.style.transform='scale(1.05)'"
                                                onmouseout="this.style.transform='scale(1)'">
                                            @if($user->is_active)
                                                <i class="fas fa-ban me-1"></i>{{ __('admin.deactivate') }}
                                            @else
                                                <i class="fas fa-check me-1"></i>{{ __('admin.activate') }}
                                            @endif
                                        </button>
                                    </form>
                                    
                                    <!-- Delete Button -->
                                    <form action="{{ route('admin.users.delete', $user) }}" method="POST" 
                                          onsubmit="return confirm('{{ __('admin.confirm_hard_delete_user') }}');" 
                                          class="d-inline"
                                          title="{{ __('admin.delete') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3" 
                                                style="transition: all 0.3s;"
                                                onmouseover="this.style.background='#dc3545'; this.style.color='white'; this.style.transform='scale(1.05)'"
                                                onmouseout="this.style.background='transparent'; this.style.color='#dc3545'; this.style.transform='scale(1)'">
                                            <i class="fas fa-user-times me-1"></i>{{ __('admin.delete') }}
                                        </button>
                                    </form>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-3 py-2">
                                        <i class="fas fa-lock me-1"></i>{{ __('admin.current_user') }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    <p class="mb-0">{{ __('admin.no_users_found') }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-center">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
</div>
@endsection
