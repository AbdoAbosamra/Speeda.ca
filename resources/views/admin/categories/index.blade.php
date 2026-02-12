@extends('layouts.app')

@section('title', __('admin.manage_categories'))

@section('content')
<!-- Admin Categories Management with Tailwind + Alpine.js -->
<div class="admin-content-wrapper" style="margin-left: 0 !important;" x-data="{ 
    showInactive: true, 
    searchQuery: '', 
    selectedSection: 'all',
    get filteredCategories() {
        const rows = document.querySelectorAll('.category-row');
        return Array.from(rows).filter(row => {
            const isActive = row.dataset.active === 'true';
            const sectionId = row.dataset.section;
            const matchesSearch = this.searchQuery === '' || row.textContent.toLowerCase().includes(this.searchQuery.toLowerCase());
            const matchesSection = this.selectedSection === 'all' || sectionId === this.selectedSection;
            return (this.showInactive || isActive) && matchesSearch && matchesSection;
        });
    }
}">
<div class="container py-4">
    <!-- Header with Stats -->
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap">
        <div class="mb-3">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill mb-2">
                <i class="fas fa-folder me-1"></i> {{ __('admin.categories_management') }}
            </span>
            <h1 class="h3 fw-bold mb-1">{{ __('admin.manage_categories') }}</h1>
            <p class="text-muted mb-0">{{ __('admin.manage_all_categories_status') }}</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
            <i class="fas fa-plus me-2"></i>{{ __('admin.add_category') }}
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white border-0" style="border-radius: 16px; background: linear-gradient(135deg, #667eea, #764ba2);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 fw-bold">{{ $stats['totalCategories'] ?? count($allCategories) }}</h3>
                            <small class="opacity-75">{{ __('admin.total_categories') }}</small>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                            <i class="fas fa-folder fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white border-0" style="border-radius: 16px; background: linear-gradient(135deg, #10b981, #34d399);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 fw-bold">{{ $stats['activeCategories'] ?? $allCategories->where('is_active', true)->count() }}</h3>
                            <small class="opacity-75">{{ __('admin.active_categories') }}</small>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-secondary text-white border-0" style="border-radius: 16px; background: linear-gradient(135deg, #6b7280, #9ca3af);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 fw-bold">{{ $stats['inactiveCategories'] ?? $allCategories->where('is_active', false)->count() }}</h3>
                            <small class="opacity-75">{{ __('admin.inactive_categories') }}</small>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                            <i class="fas fa-ban fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white border-0" style="border-radius: 16px; background: linear-gradient(135deg, #3b82f6, #60a5fa);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 fw-bold">{{ count($sections) }}</h3>
                            <small class="opacity-75">{{ __('admin.sections') }}</small>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                            <i class="fas fa-th-large fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-muted mb-2">{{ __('admin.search') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" x-model="searchQuery" class="form-control border-0 bg-light" placeholder="{{ __('admin.search_categories_placeholder') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-muted mb-2">{{ __('admin.filter_by_section') }}</label>
                    <select x-model="selectedSection" class="form-select border-0 bg-light">
                        <option value="all">{{ __('admin.all_sections') }}</option>
                        @foreach($sections as $section)
                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" x-model="showInactive" id="showInactive" checked>
                        <label class="form-check-label fw-semibold" for="showInactive">
                            {{ __('admin.show_inactive_categories') }}
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="card border-0 shadow-lg" style="border-radius: 16px;">
        <div class="card-header bg-white border-bottom-0 pt-4 px-4">
            <h5 class="fw-bold mb-0">{{ __('admin.categories_list') }}</h5>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="fw-bold py-3 rounded-start">{{ __('admin.category') }}</th>
                            <th class="fw-bold py-3">{{ __('admin.section') }}</th>
                            <th class="fw-bold py-3">{{ __('admin.status') }}</th>
                            <th class="fw-bold py-3">{{ __('admin.providers_count') }}</th>
                            <th class="fw-bold py-3 rounded-end text-center">{{ __('admin.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allCategories as $category)
                        <tr class="category-row" 
                            data-category-id="{{ $category->id }}"
                            data-active="{{ $category->is_active ? 'true' : 'false' }}"
                            data-section="{{ $category->parent_id ?? 'root' }}"
                            style="{{ !$category->is_active ? 'background: #f8f9fa; opacity: 0.85;' : '' }}">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                         style="width: 44px; height: 44px; background: {{ $category->color ?? '#e5e7eb' }}; color: white;">
                                        <i class="fas {{ $category->icon ?? 'fa-folder' }}"></i>
                                    </div>
                                    <div>
                                        <strong class="{{ $category->is_active ? '' : 'text-muted' }}">{{ $category->name }}</strong>
                                        @if($category->is_section)
                                            <span class="badge bg-info ms-2">{{ __('admin.section') }}</span>
                                        @endif
                                        <br>
                                        <small class="text-muted">{{ Str::limit($category->description, 50) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($category->parent)
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-sitemap me-1"></i>{{ $category->parent->name }}
                                </span>
                                @elseif($category->is_section)
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        <i class="fas fa-star me-1"></i>{{ __('admin.main_section') }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($category->is_active)
                                    <span class="badge bg-success rounded-pill px-3 py-2">
                                        <i class="fas fa-check-circle me-1"></i>{{ __('admin.active') }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-3 py-2">
                                        <i class="fas fa-ban me-1"></i>{{ __('admin.inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    {{ $category->serviceProviders()->count() }} {{ __('admin.providers') }}
                                </span>
                            </td>
                            <td class="text-center">
                                <!-- Edit Button -->
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1">
                                    <i class="fas fa-edit me-1"></i>{{ __('admin.edit') }}
                                </a>
                                
                                <!-- Toggle Status Button -->
                                <form action="{{ route('admin.categories.toggle', $category) }}" method="POST" class="d-inline me-1">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            class="btn btn-sm {{ $category->is_active ? 'btn-warning' : 'btn-success' }} rounded-pill px-3"
                                            onclick="return confirm('{{ $category->is_active ? __('admin.confirm_deactivate_category') : __('admin.confirm_activate_category') }}')">
                                        @if($category->is_active)
                                            <i class="fas fa-ban me-1"></i>{{ __('admin.deactivate') }}
                                        @else
                                            <i class="fas fa-check me-1"></i>{{ __('admin.activate') }}
                                        @endif
                                    </button>
                                </form>
                                
                                <!-- Delete Button -->
                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                            onclick="return confirm('{{ __('admin.confirm_delete_category') }}')"
                                            {{ $category->is_active ? 'disabled' : '' }}>
                                        <i class="fas fa-trash me-1"></i>{{ __('admin.delete') }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block text-muted opacity-25"></i>
                                    <p class="mb-0">{{ __('admin.no_categories_found') }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Create Category Modal -->
<div class="modal fade" id="createCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0" style="border-radius: 16px;" x-data="{ activeTab: 'ar' }">
            <div class="modal-header bg-primary text-white" style="border-radius: 16px 16px 0 0;">
                <h5 class="modal-title fw-bold">{{ __('admin.create_category') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <!-- Language Tabs -->
                    <div class="nav nav-pills nav-fill mb-4 bg-light p-2 rounded-pill">
                        <button type="button" class="nav-link rounded-pill" :class="{ 'active bg-primary text-white': activeTab === 'ar' }" @click="activeTab = 'ar'">
                            🇸🇦 {{ __('admin.arabic') }}
                        </button>
                        <button type="button" class="nav-link rounded-pill" :class="{ 'active bg-primary text-white': activeTab === 'en' }" @click="activeTab = 'en'">
                            🇬🇧 {{ __('admin.english') }}
                        </button>
                        <button type="button" class="nav-link rounded-pill" :class="{ 'active bg-primary text-white': activeTab === 'fr' }" @click="activeTab = 'fr'">
                            🇫🇷 {{ __('admin.french') }}
                        </button>
                    </div>

                    <!-- Arabic Fields -->
                    <div x-show="activeTab === 'ar'" x-transition>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('admin.name_ar') }} *</label>
                            <input type="text" name="name_ar" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('admin.description_ar') }}</label>
                            <textarea name="description_ar" class="form-control" rows="3"></textarea>
                        </div>
                    </div>

                    <!-- English Fields -->
                    <div x-show="activeTab === 'en'" x-transition style="display: none;">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('admin.name_en') }}</label>
                            <input type="text" name="name_en" class="form-control">
                            <small class="text-muted">{{ __('admin.slug_generated_from_en') }}</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('admin.description_en') }}</label>
                            <textarea name="description_en" class="form-control" rows="3"></textarea>
                        </div>
                    </div>

                    <!-- French Fields -->
                    <div x-show="activeTab === 'fr'" x-transition style="display: none;">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('admin.name_fr') }}</label>
                            <input type="text" name="name_fr" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('admin.description_fr') }}</label>
                            <textarea name="description_fr" class="form-control" rows="3"></textarea>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Common Fields -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('admin.parent_category') }}</label>
                            <select name="parent_id" class="form-select">
                                <option value="">{{ __('admin.none_main_section') }}</option>
                                @foreach($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->localized_name ?? $section->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('admin.sort_order') }}</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('admin.icon') }}</label>
                            <input type="text" name="icon" class="form-control" placeholder="fa-folder">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('admin.color') }}</label>
                            <input type="color" name="color" class="form-control" value="#667eea">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="is_section" value="1" id="isSection">
                                <label class="form-check-label" for="isSection">{{ __('admin.is_main_section') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" checked>
                                <label class="form-check-label" for="isActive">{{ __('admin.active') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">{{ __('admin.cancel') }}</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">{{ __('admin.create') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modals -->
@foreach($allCategories as $category)
<div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0" style="border-radius: 16px;" x-data="{ activeTab: 'ar' }">
            <div class="modal-header bg-primary text-white" style="border-radius: 16px 16px 0 0;">
                <h5 class="modal-title fw-bold">{{ __('admin.edit_category') }}: {{ $category->localized_name ?? $category->name }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.categories.update', $category) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body p-4">
                    <!-- Language Tabs -->
                    <div class="nav nav-pills nav-fill mb-4 bg-light p-2 rounded-pill">
                        <button type="button" class="nav-link rounded-pill" :class="{ 'active bg-primary text-white': activeTab === 'ar' }" @click="activeTab = 'ar'">
                            🇸🇦 {{ __('admin.arabic') }}
                        </button>
                        <button type="button" class="nav-link rounded-pill" :class="{ 'active bg-primary text-white': activeTab === 'en' }" @click="activeTab = 'en'">
                            🇬🇧 {{ __('admin.english') }}
                        </button>
                        <button type="button" class="nav-link rounded-pill" :class="{ 'active bg-primary text-white': activeTab === 'fr' }" @click="activeTab = 'fr'">
                            🇫🇷 {{ __('admin.french') }}
                        </button>
                    </div>

                    <!-- Arabic Fields -->
                    <div x-show="activeTab === 'ar'" x-transition>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('admin.name_ar') }} *</label>
                            <input type="text" name="name_ar" class="form-control" value="{{ $category->name_ar }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('admin.description_ar') }}</label>
                            <textarea name="description_ar" class="form-control" rows="3">{{ $category->description_ar }}</textarea>
                        </div>
                    </div>

                    <!-- English Fields -->
                    <div x-show="activeTab === 'en'" x-transition style="display: none;">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('admin.name_en') }}</label>
                            <input type="text" name="name_en" class="form-control" value="{{ $category->name_en }}">
                            <small class="text-muted">{{ __('admin.slug_generated_from_en') }}</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('admin.description_en') }}</label>
                            <textarea name="description_en" class="form-control" rows="3">{{ $category->description_en }}</textarea>
                        </div>
                    </div>

                    <!-- French Fields -->
                    <div x-show="activeTab === 'fr'" x-transition style="display: none;">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('admin.name_fr') }}</label>
                            <input type="text" name="name_fr" class="form-control" value="{{ $category->name_fr }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('admin.description_fr') }}</label>
                            <textarea name="description_fr" class="form-control" rows="3">{{ $category->description_fr }}</textarea>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Slug Display (Read-only) -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('admin.slug') }}</label>
                        <input type="text" class="form-control bg-light" value="{{ $category->slug }}" disabled readonly>
                        <small class="text-muted">{{ __('admin.slug_auto_generated') }}</small>
                    </div>

                    <!-- Common Fields -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('admin.parent_category') }}</label>
                            <select name="parent_id" class="form-select">
                                <option value="">{{ __('admin.none_main_section') }}</option>
                                @foreach($sections as $section)
                                    @if($section->id !== $category->id)
                                    <option value="{{ $section->id }}" {{ $category->parent_id == $section->id ? 'selected' : '' }}>
                                        {{ $section->localized_name ?? $section->name }}
                                    </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('admin.sort_order') }}</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ $category->sort_order ?? 0 }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('admin.icon') }}</label>
                            <input type="text" name="icon" class="form-control" value="{{ $category->icon ?? 'fa-folder' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('admin.color') }}</label>
                            <input type="color" name="color" class="form-control" value="{{ $category->color ?? '#667eea' }}">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="is_section" value="1" id="isSection{{ $category->id }}" {{ $category->is_section ? 'checked' : '' }}>
                                <label class="form-check-label" for="isSection{{ $category->id }}">{{ __('admin.is_main_section') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive{{ $category->id }}" {{ $category->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="isActive{{ $category->id }}">{{ __('admin.active') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">{{ __('admin.cancel') }}</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">{{ __('admin.update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection
