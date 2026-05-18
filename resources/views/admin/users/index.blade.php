@extends('layouts.app')

@section('title', 'Users Management')

@section('content')
<div class="container py-4">
    @if (session('message'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Users Management</h1>
            <p class="text-muted mb-0">Manage all users, their roles, and account status</p>
        </div>
        <a href="{{ route('admin.users.trash') }}" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fas fa-trash-alt me-2"></i>Trash Bin
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1.25rem;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-muted text-uppercase fw-semibold tracking-wide">Total Users</div>
                            <div class="h2 fw-bold mt-1 mb-0">{{ $stats['total'] }}</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: #eef2ff; color: #4f46e5;">
                            <i class="fas fa-users fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1.25rem;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-muted text-uppercase fw-semibold tracking-wide">Active</div>
                            <div class="h2 fw-bold mt-1 mb-0 text-success">{{ $stats['active'] }}</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: #ecfdf5; color: #059669;">
                            <i class="fas fa-user-check fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1.25rem;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-muted text-uppercase fw-semibold tracking-wide">Inactive</div>
                            <div class="h2 fw-bold mt-1 mb-0 text-warning">{{ $stats['inactive'] }}</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: #fefce8; color: #d97706;">
                            <i class="fas fa-user-slash fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1.25rem;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-muted text-uppercase fw-semibold tracking-wide">Providers</div>
                            <div class="h2 fw-bold mt-1 mb-0 text-primary">{{ $stats['providers'] }}</div>
                        </div>
                        <div class="rounded-3 p-3" style="background: #eff6ff; color: #2563eb;">
                            <i class="fas fa-briefcase fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 1.25rem;">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.users') }}" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0 rounded-start-pill"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-0 rounded-end-pill ps-0" 
                               placeholder="Search by name or email..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="role" class="form-select bg-light border-0 rounded-pill">
                        <option value="">All Roles</option>
                        <option value="client" {{ request('role') === 'client' ? 'selected' : '' }}>Client</option>
                        <option value="service_provider" {{ request('role') === 'service_provider' ? 'selected' : '' }}>Provider</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select bg-light border-0 rounded-pill">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary rounded-pill px-4 w-100">
                        <i class="fas fa-undo me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="card border-0 shadow-sm" style="border-radius: 1.25rem;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3">
                            <a href="{{ request()->fullUrlWithQuery(['sortField' => 'name', 'sortDirection' => request('sortField') === 'name' && request('sortDirection') === 'asc' ? 'desc' : 'asc']) }}" 
                               class="text-decoration-none text-muted small fw-semibold text-uppercase tracking-wide">
                                User <i class="fas fa-sort ms-1"></i>
                            </a>
                        </th>
                        <th class="py-3">
                            <a href="{{ request()->fullUrlWithQuery(['sortField' => 'role', 'sortDirection' => request('sortField') === 'role' && request('sortDirection') === 'asc' ? 'desc' : 'asc']) }}"
                               class="text-decoration-none text-muted small fw-semibold text-uppercase tracking-wide">
                                Role <i class="fas fa-sort ms-1"></i>
                            </a>
                        </th>
                        <th class="py-3 text-muted small fw-semibold text-uppercase tracking-wide">Activity</th>
                        <th class="py-3">
                            <a href="{{ request()->fullUrlWithQuery(['sortField' => 'is_active', 'sortDirection' => request('sortField') === 'is_active' && request('sortDirection') === 'asc' ? 'desc' : 'asc']) }}"
                               class="text-decoration-none text-muted small fw-semibold text-uppercase tracking-wide">
                                Status <i class="fas fa-sort ms-1"></i>
                            </a>
                        </th>
                        <th class="py-3">
                            <a href="{{ request()->fullUrlWithQuery(['sortField' => 'created_at', 'sortDirection' => request('sortField') === 'created_at' && request('sortDirection') === 'asc' ? 'desc' : 'asc']) }}"
                               class="text-decoration-none text-muted small fw-semibold text-uppercase tracking-wide">
                                Created <i class="fas fa-sort ms-1"></i>
                            </a>
                        </th>
                        <th class="pe-4 py-3 text-end text-muted small fw-semibold text-uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr class="{{ !$user->is_active ? 'opacity-50' : '' }}">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                         style="width: 40px; height: 40px; background: {{ $user->is_active ? 'linear-gradient(135deg, #4f46e5, #6366f1)' : '#cbd5e1' }};">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $user->name }}</div>
                                        <div class="small text-muted">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill px-3 py-2 fw-semibold
                                    @if($user->role === 'admin') bg-danger bg-opacity-10 text-danger
                                    @elseif($user->role === 'service_provider') bg-success bg-opacity-10 text-success
                                    @else bg-primary bg-opacity-10 text-primary @endif">
                                    {{ $user->role === 'service_provider' ? 'Provider' : ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-3 small text-muted">
                                    <span title="Reviews"><i class="fas fa-star text-warning me-1"></i>{{ $user->reviews_count ?? 0 }}</span>
                                    <span title="Comments"><i class="fas fa-comment text-primary me-1"></i>{{ $user->comments_count ?? 0 }}</span>
                                </div>
                            </td>
                            <td>
                                <form action="{{ route('admin.users.toggle', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" role="switch"
                                               onchange="this.form.submit()"
                                               {{ $user->is_active ? 'checked' : '' }}
                                               {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                        <span class="small ms-1 {{ $user->is_active ? 'text-success' : 'text-muted' }}">
                                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </form>
                            </td>
                            <td class="text-nowrap">
                                <span>{{ $user->created_at->format('M d, Y') }}</span>
                                <span class="d-block small text-muted">{{ $user->created_at->format('H:i') }}</span>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-light rounded-pill px-3" title="Edit">
                                        <i class="fas fa-pen me-1"></i>Edit
                                    </a>
                                    @if($user->id !== auth()->id() && $user->role !== 'admin')
                                        <form action="{{ route('admin.users.delete', $user) }}" method="POST" 
                                              onsubmit="return confirm('Move this user to trash?')" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3" title="Delete">
                                                <i class="fas fa-trash-alt me-1"></i>Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-search fa-3x text-muted opacity-25 mb-3 d-block"></i>
                                <h5 class="fw-bold">No users found</h5>
                                <p class="text-muted mb-0">No users match your current search or filter criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
            <div class="card-footer bg-white border-0 px-4 py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="small text-muted">
                        Showing <strong>{{ $users->firstItem() }}</strong> to <strong>{{ $users->lastItem() }}</strong> of <strong>{{ $users->total() }}</strong> users
                    </div>
                    {{ $users->links('components.global-pagination') }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
