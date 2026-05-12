@extends('layouts.app')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
    <!-- sidebar removed - full width admin content -->
    <div class="admin-content-wrapper" style="margin-inline-start: 0 !important;">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 fw-bold mb-1">{{ __('admin.manage_locations') }}</h1>
                    <p class="text-muted mb-0">{{ __('admin.manage_all_locations') }}</p>
                </div>
            </div>

            <!-- Add Location Form - Enhanced -->
            <div class="card border-0 shadow-lg mb-4" style="border-radius: 16px; background: white;">
                <div class="card-header bg-white" style="border-bottom: 2px solid #f1f5f9; border-radius: 16px 16px 0 0;">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-plus-circle me-2 text-success"></i>{{ __('admin.add_location') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.locations.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">{{ __('admin.city') }}</label>
                                <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                                    value="{{ old('city') }}" required
                                    style="border-radius: 12px; border: 2px solid #e2e8f0; padding: 0.75rem;">
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">{{ __('admin.image') }}</label>
                                <input type="file" name="image" class="form-control @error('image') is-invalid @enderror"
                                    accept="image/*"
                                    style="border-radius: 12px; border: 2px solid #e2e8f0; padding: 0.75rem;">
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">{{ __('admin.is_active') }}</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" checked
                                        style="width: 3rem; height: 1.5rem; cursor: pointer;">
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100"
                                    style="border-radius: 12px; padding: 0.75rem; font-weight: 600; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border: none;">
                                    <i class="fas fa-plus me-2"></i>{{ __('admin.add') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Locations Table - Enhanced -->
            <div class="card border-0 shadow-lg" style="border-radius: 16px; background: white;">
                <div class="card-header bg-white" style="border-bottom: 2px solid #f1f5f9; border-radius: 16px 16px 0 0;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-map-marker-alt me-2 text-primary"></i>{{ __('admin.locations_list') }}
                        </h5>
                        <span class="badge bg-success rounded-pill px-3 py-2">{{ $locations->total() }}
                            {{ __('admin.active_locations') }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background: linear-gradient(135deg, #f8fafc, #f1f5f9);">
                                <tr>
                                    <th class="fw-bold py-3">{{ __('admin.image') }}</th>
                                    <th class="fw-bold py-3">{{ __('admin.city') }}</th>
                                    <th class="fw-bold py-3">{{ __('admin.status') }}</th>
                                    <th class="fw-bold py-3">{{ __('admin.created_at') }}</th>
                                    <th class="fw-bold py-3 text-center">{{ __('admin.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($locations as $location)
                                    <tr style="border-bottom: 1px solid #f1f5f9; transition: all 0.3s;"
                                        onmouseover="this.style.background='#f8fafc'"
                                        onmouseout="this.style.background='white'">
                                        <td>
                                            @if($location->image)
                                                <img src="{{ Storage::url($location->image) }}" alt="{{ $location->city }}"
                                                    class="img-thumbnail rounded-circle"
                                                    style="width: 60px; height: 60px; object-fit: cover; border: 3px solid #e2e8f0;">
                                            @else
                                                <div class="bg-gradient rounded-circle d-flex align-items-center justify-content-center"
                                                    style="width: 60px; height: 60px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                                    <i class="fas fa-map-marker-alt text-white fa-lg"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td><strong class="fs-5">{{ $location->city }}</strong></td>
                                        <td>
                                            <span
                                                class="badge rounded-pill px-3 py-2 fw-semibold bg-{{ $location->is_active ? 'success' : 'secondary' }}">
                                                <i
                                                    class="fas fa-{{ $location->is_active ? 'check' : 'times' }}-circle me-1"></i>
                                                {{ $location->is_active ? __('admin.active') : __('admin.inactive') }}
                                            </span>
                                        </td>
                                        <td class="text-muted">{{ $location->created_at->format('Y-m-d') }}</td>
                                        <td class="text-center">
                                            @if($location->is_active)
                                                <form action="{{ route('admin.locations.deactivate', $location) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-warning rounded-pill px-3 me-2"
                                                        title="{{ __('admin.deactivate') }}">
                                                        <i class="fas fa-ban me-1"></i>{{ __('admin.deactivate') }}
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.locations.activate', $location) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 me-2"
                                                        title="{{ __('admin.activate') }}">
                                                        <i class="fas fa-check me-1"></i>{{ __('admin.activate') }}
                                                    </button>
                                                </form>
                                            @endif

                                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 me-2"
                                                data-bs-toggle="modal" data-bs-target="#editModal{{ $location->id }}"
                                                style="transition: all 0.3s;" onmouseover="this.style.transform='scale(1.05)'"
                                                onmouseout="this.style.transform='scale(1)'">
                                                <i class="fas fa-edit me-1"></i>{{ __('admin.edit') }}
                                            </button>

                                            @if(!$location->is_active)
                                                <form action="{{ route('admin.locations.delete', $location) }}" method="POST"
                                                    onsubmit="return confirm('{{ __('admin.confirm_delete_location') }}');"
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
                                            @endif

                                            <!-- Edit Modal - Enhanced -->
                                            <div class="modal fade" id="editModal{{ $location->id }}" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content" style="border-radius: 16px; border: none;">
                                                        <div class="modal-header"
                                                            style="border-bottom: 2px solid #f1f5f9; border-radius: 16px 16px 0 0;">
                                                            <h5 class="modal-title fw-bold">
                                                                <i
                                                                    class="fas fa-edit me-2 text-primary"></i>{{ __('admin.edit_location') }}
                                                            </h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form action="{{ route('admin.locations.update', $location) }}"
                                                            method="POST" enctype="multipart/form-data">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label
                                                                        class="form-label fw-semibold">{{ __('admin.city') }}</label>
                                                                    <input type="text" name="city" class="form-control"
                                                                        value="{{ $location->city }}" required
                                                                        style="border-radius: 12px; border: 2px solid #e2e8f0; padding: 0.75rem;">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label
                                                                        class="form-label fw-semibold">{{ __('admin.image') }}</label>
                                                                    @if($location->image)
                                                                        <div class="mb-2">
                                                                            <img src="{{ Storage::url($location->image) }}"
                                                                                alt="{{ $location->city }}"
                                                                                class="img-thumbnail rounded"
                                                                                style="max-width: 200px; border: 2px solid #e2e8f0;">
                                                                        </div>
                                                                    @endif
                                                                    <input type="file" name="image" class="form-control"
                                                                        accept="image/*"
                                                                        style="border-radius: 12px; border: 2px solid #e2e8f0; padding: 0.75rem;">
                                                                    <small
                                                                        class="text-muted">{{ __('admin.current_image_will_be_replaced') }}</small>
                                                                </div>
                                                                <div class="form-check form-switch">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="is_active" {{ $location->is_active ? 'checked' : '' }}
                                                                        style="width: 3rem; height: 1.5rem; cursor: pointer;">
                                                                    <label
                                                                        class="form-check-label fw-semibold">{{ __('admin.is_active') }}</label>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer" style="border-top: 2px solid #f1f5f9;">
                                                                <button type="button"
                                                                    class="btn btn-secondary rounded-pill px-4"
                                                                    data-bs-dismiss="modal">{{ __('admin.cancel') }}</button>
                                                                <button type="submit" class="btn btn-primary rounded-pill px-4"
                                                                    style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border: none; font-weight: 600;">
                                                                    <i class="fas fa-save me-2"></i>{{ __('admin.save') }}
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                                <p class="mb-0">{{ __('admin.no_locations') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4 d-flex justify-content-center">
                        {{ $locations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection