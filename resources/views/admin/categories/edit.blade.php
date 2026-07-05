@extends('layouts.app')

@section('title', __('admin.edit_category'))

@section('content')
<div class="admin-content-wrapper" style="margin-inline-start: 0 !important;">
    <div class="container py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill mb-2">
                    <i class="fas fa-folder me-1"></i> {{ __('admin.categories_management') }}
                </span>
                <h1 class="h3 fw-bold mb-1">{{ __('admin.edit_category') }}</h1>
                <p class="text-muted mb-0">{{ $category->localized_name ?? $category->name }}</p>
            </div>
            <a href="{{ route('admin.categories') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fas fa-arrow-left me-2"></i>{{ __('admin.back_to_list') }}
            </a>
        </div>

        <!-- Edit Form Card -->
        <div class="card border-0 shadow-lg" style="border-radius: 16px;">
            <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                <h5 class="fw-bold mb-0">{{ __('admin.category_details') }}</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.categories.update', $category) }}" method="POST" x-data="{ activeTab: 'ar' }">
                    @csrf
                    @method('PATCH')

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
                            <input type="text" name="name_ar" class="form-control @error('name_ar') is-invalid @enderror" value="{{ old('name_ar', $category->name_ar) }}" required>
                            @error('name_ar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('admin.description_ar') }}</label>
                            <textarea name="description_ar" class="form-control @error('description_ar') is-invalid @enderror" rows="3">{{ old('description_ar', $category->description_ar) }}</textarea>
                            @error('description_ar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- English Fields -->
                    <div x-show="activeTab === 'en'" x-transition style="display: none;">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('admin.name_en') }}</label>
                            <input type="text" name="name_en" class="form-control @error('name_en') is-invalid @enderror" value="{{ old('name_en', $category->name_en) }}">
                            <small class="text-muted">{{ __('admin.slug_generated_from_en') }}</small>
                            @error('name_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('admin.description_en') }}</label>
                            <textarea name="description_en" class="form-control @error('description_en') is-invalid @enderror" rows="3">{{ old('description_en', $category->description_en) }}</textarea>
                            @error('description_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- French Fields -->
                    <div x-show="activeTab === 'fr'" x-transition style="display: none;">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('admin.name_fr') }}</label>
                            <input type="text" name="name_fr" class="form-control @error('name_fr') is-invalid @enderror" value="{{ old('name_fr', $category->name_fr) }}">
                            @error('name_fr')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('admin.description_fr') }}</label>
                            <textarea name="description_fr" class="form-control @error('description_fr') is-invalid @enderror" rows="3">{{ old('description_fr', $category->description_fr) }}</textarea>
                            @error('description_fr')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                            <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                                <option value="">{{ __('admin.none_main_section') }}</option>
                                @foreach($sections as $section)
                                    @if($section->id !== $category->id)
                                    <option value="{{ $section->id }}" {{ old('parent_id', $category->parent_id) == $section->id ? 'selected' : '' }}>
                                        {{ $section->localized_name ?? $section->name }}
                                    </option>
                                    @endif
                                @endforeach
                            </select>
                            @error('parent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('admin.sort_order') }}</label>
                            <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $category->sort_order ?? 0) }}">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('admin.icon') }}</label>
                            <input type="text" name="icon" class="form-control @error('icon') is-invalid @enderror" value="{{ old('icon', $category->icon ?? 'fa-folder') }}">
                            @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('admin.color') }}</label>
                            <input type="color" name="color" class="form-control @error('color') is-invalid @enderror" value="{{ old('color', $category->color ?? '#667eea') }}">
                            @error('color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="is_section" value="1" id="isSection" {{ old('is_section', $category->is_section) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isSection">{{ __('admin.is_main_section') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isActive">{{ __('admin.active') }}</label>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between align-items-center mt-4 pt-4 border-top">
                        <a href="{{ route('admin.categories') }}" class="btn btn-light rounded-pill px-4">
                            {{ __('admin.cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="fas fa-save me-2"></i>{{ __('admin.update') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
