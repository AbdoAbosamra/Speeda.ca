@extends('layouts.app')

@section('content')
    <!-- sidebar removed - full width admin content -->
    <div class="admin-content-wrapper" style="margin-left: 0 !important;">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 fw-bold mb-1">{{ __('admin.manage_categories') }}</h1>
                    <p class="text-muted mb-0">{{ __('admin.manage_all_categories') }}</p>
                </div>
            </div>

            <!-- Add Category Form - Enhanced -->
            <div class="card border-0 shadow-lg mb-4" style="border-radius: 16px; background: white;">
                <div class="card-header bg-white" style="border-bottom: 2px solid #f1f5f9; border-radius: 16px 16px 0 0;">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-plus-circle me-2 text-success"></i>{{ __('admin.add_category') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.categories.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">{{ __('admin.category_name') }}</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" required
                                    style="border-radius: 12px; border: 2px solid #e2e8f0; padding: 0.75rem;">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">{{ __('admin.parent_category') }}</label>
                                <select name="parent_id" class="form-select"
                                    style="border-radius: 12px; border: 2px solid #e2e8f0; padding: 0.75rem;">
                                    <option value="">{{ __('admin.none') }}</option>
                                    @foreach($sections as $section)
                                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">{{ __('admin.is_section') }}</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_section"
                                        style="width: 3rem; height: 1.5rem; cursor: pointer;">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">{{ __('admin.is_active') }}</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" checked
                                        style="width: 3rem; height: 1.5rem; cursor: pointer;">
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">{{ __('admin.description') }}</label>
                                <textarea name="description" class="form-control" rows="2"
                                    style="border-radius: 12px; border: 2px solid #e2e8f0; padding: 0.75rem;">{{ old('description') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ __('admin.icon') }} (Font Awesome class)</label>
                                <input type="text" name="icon" class="form-control" placeholder="fas fa-icon"
                                    value="{{ old('icon') }}"
                                    style="border-radius: 12px; border: 2px solid #e2e8f0; padding: 0.75rem;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ __('admin.color') }}</label>
                                <input type="color" name="color" class="form-control form-control-color"
                                    value="{{ old('color', '#3b82f6') }}" style="border-radius: 12px; height: 45px;">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary"
                                    style="border-radius: 12px; padding: 0.75rem 2rem; font-weight: 600; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); border: none;">
                                    <i class="fas fa-plus me-2"></i>{{ __('admin.add') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Categories List - Enhanced -->
            <div class="card border-0 shadow-lg" style="border-radius: 16px; background: white;">
                <div class="card-header bg-white" style="border-bottom: 2px solid #f1f5f9; border-radius: 16px 16px 0 0;">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-folder me-2 text-primary"></i>{{ __('admin.categories_list') }}
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($sections as $section)
                        <div class="mb-4 p-4 rounded"
                            style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-left: 4px solid #6366f1;">
                            <h5 class="fw-bold mb-3 d-flex align-items-center">
                                <i class="fas fa-folder-open me-2 text-primary"></i>{{ $section->name }}
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead style="background: white;">
                                        <tr>
                                            <th class="fw-bold">{{ __('admin.name') }}</th>
                                            <th class="fw-bold">{{ __('admin.status') }}</th>
                                            <th class="fw-bold text-center">{{ __('admin.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($section->children as $category)
                                            <tr style="border-bottom: 1px solid #e2e8f0; transition: all 0.3s;"
                                                onmouseover="this.style.background='white'"
                                                onmouseout="this.style.background='transparent'">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @php
                                                            $iconClass = $category->icon ?? 'fas fa-tags';
                                                            $iconColor = $category->color ?? '#6366f1';
                                                        @endphp
                                                        <i class="{{ $iconClass }} me-2" style="color: {{ $iconColor }};"></i>
                                                        <strong>{{ $category->name }}</strong>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge rounded-pill px-3 py-2 fw-semibold bg-{{ $category->is_active ? 'success' : 'secondary' }}">
                                                        <i
                                                            class="fas fa-{{ $category->is_active ? 'check' : 'times' }}-circle me-1"></i>
                                                        {{ $category->is_active ? __('admin.active') : __('admin.inactive') }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    @if($category->is_active)
                                                        <form action="{{ route('admin.categories.deactivate', $category) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-sm btn-warning rounded-pill px-3 me-2"
                                                                title="{{ __('admin.deactivate') }}">
                                                                <i class="fas fa-ban me-1"></i>{{ __('admin.deactivate') }}
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form action="{{ route('admin.categories.activate', $category) }}" method="POST"
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
                                                        data-bs-toggle="modal" data-bs-target="#editModal{{ $category->id }}"
                                                        style="transition: all 0.3s;"
                                                        onmouseover="this.style.transform='scale(1.05)'"
                                                        onmouseout="this.style.transform='scale(1)'">
                                                        <i class="fas fa-edit me-1"></i>{{ __('admin.edit') }}
                                                    </button>

                                                    @if(!$category->is_active)
                                                        <form action="{{ route('admin.categories.delete', $category) }}" method="POST"
                                                            onsubmit="return confirm('{{ __('admin.confirm_delete_category') }}');"
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
                                                    <div class="modal fade" id="editModal{{ $category->id }}" tabindex="-1">
                                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                                            <div class="modal-content" style="border-radius: 16px; border: none;">
                                                                <div class="modal-header"
                                                                    style="border-bottom: 2px solid #f1f5f9; border-radius: 16px 16px 0 0;">
                                                                    <h5 class="modal-title fw-bold">
                                                                        <i
                                                                            class="fas fa-edit me-2 text-primary"></i>{{ __('admin.edit_category') }}
                                                                    </h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form action="{{ route('admin.categories.update', $category) }}"
                                                                    method="POST">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <div class="modal-body">
                                                                        <div class="row g-3">
                                                                            <div class="col-md-6">
                                                                                <label
                                                                                    class="form-label fw-semibold">{{ __('admin.category_name') }}</label>
                                                                                <input type="text" name="name" class="form-control"
                                                                                    value="{{ $category->name }}" required
                                                                                    style="border-radius: 12px; border: 2px solid #e2e8f0; padding: 0.75rem;">
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label
                                                                                    class="form-label fw-semibold">{{ __('admin.parent_category') }}</label>
                                                                                <select name="parent_id" class="form-select"
                                                                                    style="border-radius: 12px; border: 2px solid #e2e8f0; padding: 0.75rem;">
                                                                                    <option value="">{{ __('admin.none') }}</option>
                                                                                    @foreach($sections as $s)
                                                                                        <option value="{{ $s->id }}" {{ $category->parent_id == $s->id ? 'selected' : '' }}>
                                                                                            {{ $s->name }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-12">
                                                                                <label
                                                                                    class="form-label fw-semibold">{{ __('admin.description') }}</label>
                                                                                <textarea name="description" class="form-control"
                                                                                    rows="2"
                                                                                    style="border-radius: 12px; border: 2px solid #e2e8f0; padding: 0.75rem;">{{ $category->description }}</textarea>
                                                                            </div>
                                                                            <div class="col-md-4">
                                                                                <div class="form-check form-switch">
                                                                                    <input class="form-check-input" type="checkbox"
                                                                                        name="is_section" {{ $category->is_section ? 'checked' : '' }}
                                                                                        style="width: 3rem; height: 1.5rem; cursor: pointer;">
                                                                                    <label
                                                                                        class="form-check-label fw-semibold">{{ __('admin.is_section') }}</label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-4">
                                                                                <div class="form-check form-switch">
                                                                                    <input class="form-check-input" type="checkbox"
                                                                                        name="is_active" {{ $category->is_active ? 'checked' : '' }}
                                                                                        style="width: 3rem; height: 1.5rem; cursor: pointer;">
                                                                                    <label
                                                                                        class="form-check-label fw-semibold">{{ __('admin.is_active') }}</label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-4">
                                                                                <label
                                                                                    class="form-label fw-semibold">{{ __('admin.sort_order') }}</label>
                                                                                <input type="number" name="sort_order"
                                                                                    class="form-control"
                                                                                    value="{{ $category->sort_order }}"
                                                                                    style="border-radius: 12px; border: 2px solid #e2e8f0; padding: 0.75rem;">
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label
                                                                                    class="form-label fw-semibold">{{ __('admin.icon') }}</label>
                                                                                <input type="text" name="icon" class="form-control"
                                                                                    value="{{ $category->icon }}"
                                                                                    style="border-radius: 12px; border: 2px solid #e2e8f0; padding: 0.75rem;">
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <label
                                                                                    class="form-label fw-semibold">{{ __('admin.color') }}</label>
                                                                                <input type="color" name="color"
                                                                                    class="form-control form-control-color"
                                                                                    value="{{ $category->color ?? '#3b82f6' }}"
                                                                                    style="border-radius: 12px; height: 45px;">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer"
                                                                        style="border-top: 2px solid #f1f5f9;">
                                                                        <button type="button"
                                                                            class="btn btn-secondary rounded-pill px-4"
                                                                            data-bs-dismiss="modal">{{ __('admin.cancel') }}</button>
                                                                        <button type="submit"
                                                                            class="btn btn-primary rounded-pill px-4"
                                                                            style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); border: none; font-weight: 600;">
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
                                                <td colspan="3" class="text-center text-muted py-3">
                                                    <i
                                                        class="fas fa-folder-open me-2"></i>{{ __('admin.no_categories_in_section') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                            <p class="text-muted">{{ __('admin.no_categories') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection