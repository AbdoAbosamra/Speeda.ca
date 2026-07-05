@extends('layouts.app')

@section('title', 'Edit User: ' . $user->name)

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 fw-bold mb-1">Edit User</h1>
                    <p class="text-muted mb-0">Update user details and manage roles.</p>
                </div>
                <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <form action="{{ route('admin.users.update', $user) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Full Name</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Email Address</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">User Role</label>
                                <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                    <option value="client" {{ old('role', $user->role) === 'client' ? 'selected' : '' }}>Client</option>
                                    <option value="service_provider" {{ old('role', $user->role) === 'service_provider' ? 'selected' : '' }}>Service Provider</option>
                                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrator</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text text-warning mt-2 small">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Changing role may affect visibility and permissions.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Account Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                           id="is_active" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                           {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        {{ $user->is_active ? 'Active Account' : 'Inactive Account' }}
                                    </label>
                                </div>
                            </div>

                            <div class="col-12 mt-5">
                                <hr class="my-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" onclick="history.back()" class="btn btn-light rounded-pill px-4">Cancel</button>
                                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold">
                                        <i class="fas fa-save me-2"></i>Save Changes
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Danger Zone -->
            @if($user->id !== auth()->id() && $user->role !== 'admin')
                <div class="card border-0 shadow-sm mt-4" style="border-radius: 20px; border-inline-start: 5px solid #ef4444 !important;">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="text-danger fw-bold mb-1">Delete User</h5>
                            <p class="text-muted mb-0 small">This will move the user to the trash bin.</p>
                        </div>
                        <form action="{{ route('admin.users.delete', $user) }}" method="POST" onsubmit="return confirm('Move this user to trash?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger rounded-pill px-4">
                                <i class="fas fa-trash-alt me-2"></i>Move to Trash
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
