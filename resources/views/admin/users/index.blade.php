@extends('layouts.app')

@section('content')
<!-- sidebar removed - full width admin content -->
<div class="admin-content-wrapper" style="margin-left: 0 !important;">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">{{ __('admin.manage_users') }}</h1>
            <p class="text-muted mb-0">{{ __('admin.manage_all_users') }}</p>
        </div>
    </div>

    <!-- Search and Filter - Enhanced -->
    <div class="card border-0 shadow-lg mb-4" style="border-radius: 16px; background: white;">
        <div class="card-header bg-white" style="border-bottom: 2px solid #f1f5f9; border-radius: 16px 16px 0 0;">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-search me-2 text-primary"></i>{{ __('admin.search_and_filter') }}
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.users') }}" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('admin.search_users') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-0 bg-light" 
                               placeholder="{{ __('admin.search_users') }}" 
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('admin.filter_by_role') }}</label>
                    <select name="role" class="form-select border-0 bg-light">
                        <option value="">{{ __('admin.all_roles') }}</option>
                        <option value="client" {{ request('role') === 'client' ? 'selected' : '' }}>{{ __('admin.role_client') }}</option>
                        <option value="service_provider" {{ request('role') === 'service_provider' ? 'selected' : '' }}>{{ __('admin.role_service_provider') }}</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>{{ __('admin.role_admin') }}</option>
                    </select>
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
    <div class="card border-0 shadow-lg" style="border-radius: 16px; background: white;">
        <div class="card-header bg-white" style="border-bottom: 2px solid #f1f5f9; border-radius: 16px 16px 0 0;">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-users me-2 text-primary"></i>{{ __('admin.users_list') }}
                </h5>
                <span class="badge bg-primary rounded-pill px-3 py-2">{{ $users->total() }} {{ __('admin.users') }}</span>
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
                            <th class="fw-bold py-3">{{ __('admin.created_at') }}</th>
                            <th class="fw-bold py-3 text-center">{{ __('admin.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: all 0.3s;" 
                            onmouseover="this.style.background='#f8fafc'" 
                            onmouseout="this.style.background='white'">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" 
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-user text-primary"></i>
                                    </div>
                                    <strong>{{ $user->name }}</strong>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge rounded-pill px-3 py-2 fw-semibold" 
                                      style="background: {{ $user->role === 'admin' ? '#ef4444' : ($user->role === 'service_provider' ? '#10b981' : '#3b82f6') }};">
                                    {{ __('admin.role_' . $user->role) }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $user->created_at->format('Y-m-d H:i') }}</td>
                            <td class="text-center">
                                @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.delete', $user) }}" method="POST" 
                                      onsubmit="return confirm('{{ __('admin.confirm_delete_user') }}');" 
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3" 
                                            style="transition: all 0.3s;"
                                            onmouseover="this.style.transform='scale(1.05)'"
                                            onmouseout="this.style.transform='scale(1)'">
                                        <i class="fas fa-trash me-1"></i>{{ __('admin.delete') }}
                                    </button>
                                </form>
                                @else
                                <span class="badge bg-secondary rounded-pill px-3 py-2">
                                    <i class="fas fa-lock me-1"></i>{{ __('admin.cannot_delete_yourself') }}
                                </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
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
