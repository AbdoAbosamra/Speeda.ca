<?php $__env->startSection('title', __('admin.manage_categories')); ?>

<?php $__env->startSection('content'); ?>
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
                <i class="fas fa-folder me-1"></i> <?php echo e(__('admin.categories_management')); ?>

            </span>
            <h1 class="h3 fw-bold mb-1"><?php echo e(__('admin.manage_categories')); ?></h1>
            <p class="text-muted mb-0"><?php echo e(__('admin.manage_all_categories_status')); ?></p>
        </div>
        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
            <i class="fas fa-plus me-2"></i><?php echo e(__('admin.add_category')); ?>

        </button>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white border-0" style="border-radius: 16px; background: linear-gradient(135deg, #667eea, #764ba2);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 fw-bold"><?php echo e($stats['totalCategories'] ?? count($allCategories)); ?></h3>
                            <small class="opacity-75"><?php echo e(__('admin.total_categories')); ?></small>
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
                            <h3 class="mb-0 fw-bold"><?php echo e($stats['activeCategories'] ?? $allCategories->where('is_active', true)->count()); ?></h3>
                            <small class="opacity-75"><?php echo e(__('admin.active_categories')); ?></small>
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
                            <h3 class="mb-0 fw-bold"><?php echo e($stats['inactiveCategories'] ?? $allCategories->where('is_active', false)->count()); ?></h3>
                            <small class="opacity-75"><?php echo e(__('admin.inactive_categories')); ?></small>
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
                            <h3 class="mb-0 fw-bold"><?php echo e(count($sections)); ?></h3>
                            <small class="opacity-75"><?php echo e(__('admin.sections')); ?></small>
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
                    <label class="form-label fw-semibold text-muted mb-2"><?php echo e(__('admin.search')); ?></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" x-model="searchQuery" class="form-control border-0 bg-light" placeholder="<?php echo e(__('admin.search_categories_placeholder')); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-muted mb-2"><?php echo e(__('admin.filter_by_section')); ?></label>
                    <select x-model="selectedSection" class="form-select border-0 bg-light">
                        <option value="all"><?php echo e(__('admin.all_sections')); ?></option>
                        <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($section->id); ?>"><?php echo e($section->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" x-model="showInactive" id="showInactive" checked>
                        <label class="form-check-label fw-semibold" for="showInactive">
                            <?php echo e(__('admin.show_inactive_categories')); ?>

                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="card border-0 shadow-lg" style="border-radius: 16px;">
        <div class="card-header bg-white border-bottom-0 pt-4 px-4">
            <h5 class="fw-bold mb-0"><?php echo e(__('admin.categories_list')); ?></h5>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="fw-bold py-3 rounded-start"><?php echo e(__('admin.category')); ?></th>
                            <th class="fw-bold py-3"><?php echo e(__('admin.section')); ?></th>
                            <th class="fw-bold py-3"><?php echo e(__('admin.status')); ?></th>
                            <th class="fw-bold py-3"><?php echo e(__('admin.providers_count')); ?></th>
                            <th class="fw-bold py-3 rounded-end text-center"><?php echo e(__('admin.actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $allCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="category-row" 
                            data-category-id="<?php echo e($category->id); ?>"
                            data-active="<?php echo e($category->is_active ? 'true' : 'false'); ?>"
                            data-section="<?php echo e($category->parent_id ?? 'root'); ?>"
                            style="<?php echo e(!$category->is_active ? 'background: #f8f9fa; opacity: 0.85;' : ''); ?>">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                         style="width: 44px; height: 44px; background: <?php echo e($category->color ?? '#e5e7eb'); ?>; color: white;">
                                        <i class="fas <?php echo e($category->icon ?? 'fa-folder'); ?>"></i>
                                    </div>
                                    <div>
                                        <strong class="<?php echo e($category->is_active ? '' : 'text-muted'); ?>"><?php echo e($category->name); ?></strong>
                                        <?php if($category->is_section): ?>
                                            <span class="badge bg-info ms-2"><?php echo e(__('admin.section')); ?></span>
                                        <?php endif; ?>
                                        <br>
                                        <small class="text-muted"><?php echo e(Str::limit($category->description, 50)); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if($category->parent): ?>
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-sitemap me-1"></i><?php echo e($category->parent->name); ?>

                                </span>
                                <?php elseif($category->is_section): ?>
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        <i class="fas fa-star me-1"></i><?php echo e(__('admin.main_section')); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($category->is_active): ?>
                                    <span class="badge bg-success rounded-pill px-3 py-2">
                                        <i class="fas fa-check-circle me-1"></i><?php echo e(__('admin.active')); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary rounded-pill px-3 py-2">
                                        <i class="fas fa-ban me-1"></i><?php echo e(__('admin.inactive')); ?>

                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <?php echo e($category->serviceProviders()->count()); ?> <?php echo e(__('admin.providers')); ?>

                                </span>
                            </td>
                            <td class="text-center">
                                <!-- Edit Button -->
                                <a href="<?php echo e(route('admin.categories.edit', $category)); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1">
                                    <i class="fas fa-edit me-1"></i><?php echo e(__('admin.edit')); ?>

                                </a>
                                
                                <!-- Toggle Status Button -->
                                <form action="<?php echo e(route('admin.categories.toggle', $category)); ?>" method="POST" class="d-inline me-1">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <button type="submit" 
                                            class="btn btn-sm <?php echo e($category->is_active ? 'btn-warning' : 'btn-success'); ?> rounded-pill px-3"
                                            onclick="return confirm('<?php echo e($category->is_active ? __('admin.confirm_deactivate_category') : __('admin.confirm_activate_category')); ?>')">
                                        <?php if($category->is_active): ?>
                                            <i class="fas fa-ban me-1"></i><?php echo e(__('admin.deactivate')); ?>

                                        <?php else: ?>
                                            <i class="fas fa-check me-1"></i><?php echo e(__('admin.activate')); ?>

                                        <?php endif; ?>
                                    </button>
                                </form>
                                
                                <!-- Delete Button -->
                                <form action="<?php echo e(route('admin.categories.destroy', $category)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" 
                                            class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                            onclick="return confirm('<?php echo e(__('admin.confirm_delete_category')); ?>')"
                                            <?php echo e($category->is_active ? 'disabled' : ''); ?>>
                                        <i class="fas fa-trash me-1"></i><?php echo e(__('admin.delete')); ?>

                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block text-muted opacity-25"></i>
                                    <p class="mb-0"><?php echo e(__('admin.no_categories_found')); ?></p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
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
                <h5 class="modal-title fw-bold"><?php echo e(__('admin.create_category')); ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo e(route('admin.categories.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body p-4">
                    <!-- Language Tabs -->
                    <div class="nav nav-pills nav-fill mb-4 bg-light p-2 rounded-pill">
                        <button type="button" class="nav-link rounded-pill" :class="{ 'active bg-primary text-white': activeTab === 'ar' }" @click="activeTab = 'ar'">
                            🇸🇦 <?php echo e(__('admin.arabic')); ?>

                        </button>
                        <button type="button" class="nav-link rounded-pill" :class="{ 'active bg-primary text-white': activeTab === 'en' }" @click="activeTab = 'en'">
                            🇬🇧 <?php echo e(__('admin.english')); ?>

                        </button>
                        <button type="button" class="nav-link rounded-pill" :class="{ 'active bg-primary text-white': activeTab === 'fr' }" @click="activeTab = 'fr'">
                            🇫🇷 <?php echo e(__('admin.french')); ?>

                        </button>
                    </div>

                    <!-- Arabic Fields -->
                    <div x-show="activeTab === 'ar'" x-transition>
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><?php echo e(__('admin.name_ar')); ?> *</label>
                            <input type="text" name="name_ar" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><?php echo e(__('admin.description_ar')); ?></label>
                            <textarea name="description_ar" class="form-control" rows="3"></textarea>
                        </div>
                    </div>

                    <!-- English Fields -->
                    <div x-show="activeTab === 'en'" x-transition style="display: none;">
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><?php echo e(__('admin.name_en')); ?></label>
                            <input type="text" name="name_en" class="form-control">
                            <small class="text-muted"><?php echo e(__('admin.slug_generated_from_en')); ?></small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><?php echo e(__('admin.description_en')); ?></label>
                            <textarea name="description_en" class="form-control" rows="3"></textarea>
                        </div>
                    </div>

                    <!-- French Fields -->
                    <div x-show="activeTab === 'fr'" x-transition style="display: none;">
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><?php echo e(__('admin.name_fr')); ?></label>
                            <input type="text" name="name_fr" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><?php echo e(__('admin.description_fr')); ?></label>
                            <textarea name="description_fr" class="form-control" rows="3"></textarea>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Common Fields -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><?php echo e(__('admin.parent_category')); ?></label>
                            <select name="parent_id" class="form-select">
                                <option value=""><?php echo e(__('admin.none_main_section')); ?></option>
                                <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($section->id); ?>"><?php echo e($section->localized_name ?? $section->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><?php echo e(__('admin.sort_order')); ?></label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><?php echo e(__('admin.icon')); ?></label>
                            <input type="text" name="icon" class="form-control" placeholder="fa-folder">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><?php echo e(__('admin.color')); ?></label>
                            <input type="color" name="color" class="form-control" value="#667eea">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="is_section" value="1" id="isSection">
                                <label class="form-check-label" for="isSection"><?php echo e(__('admin.is_main_section')); ?></label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" checked>
                                <label class="form-check-label" for="isActive"><?php echo e(__('admin.active')); ?></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal"><?php echo e(__('admin.cancel')); ?></button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4"><?php echo e(__('admin.create')); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modals -->
<?php $__currentLoopData = $allCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="modal fade" id="editCategoryModal<?php echo e($category->id); ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0" style="border-radius: 16px;" x-data="{ activeTab: 'ar' }">
            <div class="modal-header bg-primary text-white" style="border-radius: 16px 16px 0 0;">
                <h5 class="modal-title fw-bold"><?php echo e(__('admin.edit_category')); ?>: <?php echo e($category->localized_name ?? $category->name); ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo e(route('admin.categories.update', $category)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>
                <div class="modal-body p-4">
                    <!-- Language Tabs -->
                    <div class="nav nav-pills nav-fill mb-4 bg-light p-2 rounded-pill">
                        <button type="button" class="nav-link rounded-pill" :class="{ 'active bg-primary text-white': activeTab === 'ar' }" @click="activeTab = 'ar'">
                            🇸🇦 <?php echo e(__('admin.arabic')); ?>

                        </button>
                        <button type="button" class="nav-link rounded-pill" :class="{ 'active bg-primary text-white': activeTab === 'en' }" @click="activeTab = 'en'">
                            🇬🇧 <?php echo e(__('admin.english')); ?>

                        </button>
                        <button type="button" class="nav-link rounded-pill" :class="{ 'active bg-primary text-white': activeTab === 'fr' }" @click="activeTab = 'fr'">
                            🇫🇷 <?php echo e(__('admin.french')); ?>

                        </button>
                    </div>

                    <!-- Arabic Fields -->
                    <div x-show="activeTab === 'ar'" x-transition>
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><?php echo e(__('admin.name_ar')); ?> *</label>
                            <input type="text" name="name_ar" class="form-control" value="<?php echo e($category->name_ar); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><?php echo e(__('admin.description_ar')); ?></label>
                            <textarea name="description_ar" class="form-control" rows="3"><?php echo e($category->description_ar); ?></textarea>
                        </div>
                    </div>

                    <!-- English Fields -->
                    <div x-show="activeTab === 'en'" x-transition style="display: none;">
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><?php echo e(__('admin.name_en')); ?></label>
                            <input type="text" name="name_en" class="form-control" value="<?php echo e($category->name_en); ?>">
                            <small class="text-muted"><?php echo e(__('admin.slug_generated_from_en')); ?></small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><?php echo e(__('admin.description_en')); ?></label>
                            <textarea name="description_en" class="form-control" rows="3"><?php echo e($category->description_en); ?></textarea>
                        </div>
                    </div>

                    <!-- French Fields -->
                    <div x-show="activeTab === 'fr'" x-transition style="display: none;">
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><?php echo e(__('admin.name_fr')); ?></label>
                            <input type="text" name="name_fr" class="form-control" value="<?php echo e($category->name_fr); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold"><?php echo e(__('admin.description_fr')); ?></label>
                            <textarea name="description_fr" class="form-control" rows="3"><?php echo e($category->description_fr); ?></textarea>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Slug Display (Read-only) -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold"><?php echo e(__('admin.slug')); ?></label>
                        <input type="text" class="form-control bg-light" value="<?php echo e($category->slug); ?>" disabled readonly>
                        <small class="text-muted"><?php echo e(__('admin.slug_auto_generated')); ?></small>
                    </div>

                    <!-- Common Fields -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><?php echo e(__('admin.parent_category')); ?></label>
                            <select name="parent_id" class="form-select">
                                <option value=""><?php echo e(__('admin.none_main_section')); ?></option>
                                <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($section->id !== $category->id): ?>
                                    <option value="<?php echo e($section->id); ?>" <?php echo e($category->parent_id == $section->id ? 'selected' : ''); ?>>
                                        <?php echo e($section->localized_name ?? $section->name); ?>

                                    </option>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><?php echo e(__('admin.sort_order')); ?></label>
                            <input type="number" name="sort_order" class="form-control" value="<?php echo e($category->sort_order ?? 0); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><?php echo e(__('admin.icon')); ?></label>
                            <input type="text" name="icon" class="form-control" value="<?php echo e($category->icon ?? 'fa-folder'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold"><?php echo e(__('admin.color')); ?></label>
                            <input type="color" name="color" class="form-control" value="<?php echo e($category->color ?? '#667eea'); ?>">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="is_section" value="1" id="isSection<?php echo e($category->id); ?>" <?php echo e($category->is_section ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="isSection<?php echo e($category->id); ?>"><?php echo e(__('admin.is_main_section')); ?></label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive<?php echo e($category->id); ?>" <?php echo e($category->is_active ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="isActive<?php echo e($category->id); ?>"><?php echo e(__('admin.active')); ?></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal"><?php echo e(__('admin.cancel')); ?></button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4"><?php echo e(__('admin.update')); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH Y:\Speeda - Versions\Speeda\resources\views/admin/categories/index.blade.php ENDPATH**/ ?>