@extends('layouts.app')

@section('title', __('admin.manage_categories'))

@section('content')
<!-- Admin Categories Management -->
<div class="admin-content-wrapper" style="margin-inline-start: 0 !important;">
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
                            <h3 class="mb-0 fw-bold">{{ $stats['totalCategories'] }}</h3>
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
                            <h3 class="mb-0 fw-bold">{{ $stats['activeCategories'] }}</h3>
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
                            <h3 class="mb-0 fw-bold">{{ $stats['inactiveCategories'] }}</h3>
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
                            <h3 class="mb-0 fw-bold">{{ $stats['sections'] }}</h3>
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

    {{-- Filters & Search — submitted to the server (the previous Alpine bindings
         were never applied to the table rows, so none of these did anything). --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.categories') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-muted mb-2">{{ __('admin.search') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control border-0 bg-light"
                               placeholder="{{ __('admin.search_categories_placeholder') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-muted mb-2">{{ __('admin.filter_by_section') }}</label>
                    <select name="section" class="form-select border-0 bg-light">
                        <option value="">{{ __('admin.all_sections') }}</option>
                        <option value="root" @selected($sectionId === 'root')>{{ __('admin.main_section') }}</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}" @selected((string) $sectionId === (string) $section->id)>
                                {{ $section->localized_name ?? $section->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-muted mb-2">{{ __('admin.status') }}</label>
                    <select name="status" class="form-select border-0 bg-light">
                        <option value="">{{ __('admin.all') }}</option>
                        <option value="active" @selected($status === 'active')>{{ __('admin.active') }}</option>
                        <option value="inactive" @selected($status === 'inactive')>{{ __('admin.inactive') }}</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-3 flex-grow-1">
                        <i class="fas fa-filter"></i>
                    </button>
                    <a href="{{ route('admin.categories') }}" class="btn btn-outline-secondary rounded-pill px-3">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="card border-0 shadow-lg" style="border-radius: 16px;">
        <div class="card-header bg-white border-bottom-0 pt-4 px-4">
            <h5 class="fw-bold mb-0">{{ __('admin.categories_list') }}</h5>
        </div>
        <div class="card-body px-4 pb-4">
            <x-admin.bulk-form
                :action="route('admin.categories.bulk')"
                label="categories"
                :actions="[
                    'activate'   => ['label' => __('admin.activate_bulk'), 'icon' => 'fa-check', 'variant' => 'success'],
                    'deactivate' => ['label' => __('admin.deactivate_bulk'), 'icon' => 'fa-ban', 'variant' => 'warning'],
                    'delete'     => ['label' => __('admin.delete'), 'icon' => 'fa-trash', 'variant' => 'danger', 'confirm' => __('admin.bulk_confirm_delete')],
                ]"
            >
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 rounded-start" style="width:1%;"><x-admin.bulk-checkbox master /></th>
                            <th class="fw-bold py-3">{{ __('admin.category') }}</th>
                            <th class="fw-bold py-3">{{ __('admin.section') }}</th>
                            <th class="fw-bold py-3">{{ __('admin.status') }}</th>
                            <th class="fw-bold py-3">{{ __('admin.providers_count') }}</th>
                            <th class="fw-bold py-3 rounded-end text-center">{{ __('admin.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allCategories as $category)
                        <tr class="category-row"
                            style="{{ !$category->is_active ? 'background: #f8f9fa; opacity: 0.85;' : '' }}">
                            <td><x-admin.bulk-checkbox :value="$category->id" /></td>
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
                                {{-- withCount() on the query; this used to fire one COUNT per row. --}}
                                <span class="badge bg-light text-dark">
                                    {{ $category->service_providers_count }} {{ __('admin.providers') }}
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
                            <td colspan="6" class="text-center py-5">
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
            </x-admin.bulk-form>

            @if($allCategories->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    {{ $allCategories->links('components.global-pagination') }}
                </div>
            @endif
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
                        {{-- SEO fields: supported by StoreCategoryRequest and the
                             controller, but previously had no input anywhere. --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('admin.meta_title') }}</label>
                            <input type="text" name="meta_title" class="form-control" maxlength="255"
                                   value="{{ old('meta_title') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('admin.meta_description') }}</label>
                            <textarea name="meta_description" class="form-control" rows="2" maxlength="500">{{ old('meta_description') }}</textarea>
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

{{-- NOTE: the per-category edit modals that used to be rendered here have been
     removed. Nothing opened them (the Edit button links to admin.categories.edit),
     so they duplicated the edit form once per row for no benefit. --}}


@endsection
