<?php $__env->startSection('title', __('admin.users_management')); ?>

<?php $__env->startSection('content'); ?>
<!-- Admin Users Management with Tailwind + Alpine.js -->
<div class="admin-content-wrapper" style="margin-left: 0 !important;" x-data="{ showInactive: true, searchQuery: '', get visibleUsers() { return this.$refs.usersTable ? [...this.$refs.usersTable.querySelectorAll('tbody tr[data-user-id]')].filter(row => { const isActive = row.dataset.active === 'true'; const matchesSearch = this.searchQuery === '' || row.textContent.toLowerCase().includes(this.searchQuery.toLowerCase()); return (this.showInactive || isActive) && matchesSearch; }) : []; } }">
<div class="container py-4">
    <!-- Header with Stats -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1"><?php echo e(__('admin.manage_users')); ?></h1>
            <p class="text-muted mb-0"><?php echo e(__('admin.manage_all_users_status')); ?></p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white" style="border-radius: 12px; box-shadow: 0 4px 20px rgba(59, 130, 246, 0.3);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 fw-bold"><?php echo e($stats['total'] ?? $users->total()); ?></h3>
                            <small class="opacity-75"><?php echo e(__('admin.total_users')); ?></small>
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
                            <h3 class="mb-0 fw-bold"><?php echo e($stats['active'] ?? 0); ?></h3>
                            <small class="opacity-75"><?php echo e(__('admin.active_users')); ?></small>
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
                            <h3 class="mb-0 fw-bold"><?php echo e($stats['inactive'] ?? 0); ?></h3>
                            <small class="opacity-75"><?php echo e(__('admin.inactive_users')); ?></small>
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
                            <h3 class="mb-0 fw-bold"><?php echo e($stats['providers'] ?? 0); ?></h3>
                            <small class="opacity-75"><?php echo e(__('admin.service_providers')); ?></small>
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
                <i class="fas fa-filter me-2 text-primary"></i><?php echo e(__('admin.search_and_filter')); ?>

            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('admin.users')); ?>" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold"><?php echo e(__('admin.search_users')); ?></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-0 bg-light" 
                               placeholder="<?php echo e(__('admin.search_users_placeholder')); ?>" 
                               value="<?php echo e(request('search')); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold"><?php echo e(__('admin.filter_by_role')); ?></label>
                    <select name="role" class="form-select border-0 bg-light">
                        <option value=""><?php echo e(__('admin.all_roles')); ?></option>
                        <option value="client" <?php echo e(request('role') === 'client' ? 'selected' : ''); ?>><?php echo e(__('admin.role_client')); ?></option>
                        <option value="service_provider" <?php echo e(request('role') === 'service_provider' ? 'selected' : ''); ?>><?php echo e(__('admin.role_service_provider')); ?></option>
                        <option value="admin" <?php echo e(request('role') === 'admin' ? 'selected' : ''); ?>><?php echo e(__('admin.role_admin')); ?></option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold"><?php echo e(__('admin.show_status')); ?></label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" id="showInactive" x-model="showInactive" style="transform: scale(1.2);">
                        <label class="form-check-label" for="showInactive" x-text="showInactive ? '<?php echo e(__('admin.showing_all')); ?>' : '<?php echo e(__('admin.active_only')); ?>'"></label>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100" style="border-radius: 12px; padding: 0.75rem; font-weight: 600;">
                        <i class="fas fa-search me-2"></i><?php echo e(__('admin.search')); ?>

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
                    <i class="fas fa-users me-2 text-primary"></i><?php echo e(__('admin.users_list')); ?>

                </h5>
                <div class="d-flex gap-2">
                    <span class="badge bg-success rounded-pill px-3 py-2"><?php echo e($stats['active'] ?? 0); ?> <?php echo e(__('admin.active')); ?></span>
                    <span class="badge bg-secondary rounded-pill px-3 py-2"><?php echo e($stats['inactive'] ?? 0); ?> <?php echo e(__('admin.inactive')); ?></span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: linear-gradient(135deg, #f8fafc, #f1f5f9);">
                        <tr>
                            <th class="fw-bold py-3"><?php echo e(__('admin.name')); ?></th>
                            <th class="fw-bold py-3"><?php echo e(__('admin.email')); ?></th>
                            <th class="fw-bold py-3"><?php echo e(__('admin.role')); ?></th>
                            <th class="fw-bold py-3"><?php echo e(__('admin.status')); ?></th>
                            <th class="fw-bold py-3"><?php echo e(__('admin.created_at')); ?></th>
                            <th class="fw-bold py-3 text-center"><?php echo e(__('admin.actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr 
                            data-user-id="<?php echo e($user->id); ?>"
                            data-active="<?php echo e($user->is_active ? 'true' : 'false'); ?>"
                            style="border-bottom: 1px solid #f1f5f9; transition: all 0.3s; <?php echo e(!$user->is_active ? 'background: #f8f9fa; opacity: 0.75;' : ''); ?>" 
                            onmouseover="this.style.background='<?php echo e($user->is_active ? '#f8fafc' : '#e9ecef'); ?>'" 
                            onmouseout="this.style.background='<?php echo e($user->is_active ? 'white' : '#f8f9fa'); ?>'">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div 
                                        class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                        style="width: 40px; height: 40px; background: <?php echo e($user->is_active ? 'linear-gradient(135deg, #667eea, #764ba2)' : '#6c757d'); ?>; color: white; font-weight: bold;">
                                        <?php echo e(substr($user->name, 0, 1)); ?>

                                    </div>
                                    <div>
                                        <strong class="<?php echo e($user->is_active ? '' : 'text-muted'); ?>"><?php echo e($user->name); ?></strong>
                                        <?php if(!$user->is_active): ?>
                                            <span class="badge bg-secondary ms-2"><?php echo e(__('admin.inactive')); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="<?php echo e($user->is_active ? '' : 'text-muted'); ?>"><?php echo e($user->email); ?></td>
                            <td>
                                <span class="badge rounded-pill px-3 py-2 fw-semibold" 
                                      style="background: <?php echo e($user->role === 'admin' ? '#ef4444' : ($user->role === 'service_provider' ? '#10b981' : '#3b82f6')); ?>;">
                                    <?php echo e(__('admin.role_' . $user->role)); ?>

                                </span>
                            </td>
                            <td>
                                <?php if($user->is_active): ?>
                                    <span class="badge bg-success rounded-pill px-3 py-2">
                                        <i class="fas fa-check-circle me-1"></i><?php echo e(__('admin.active')); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary rounded-pill px-3 py-2">
                                        <i class="fas fa-ban me-1"></i><?php echo e(__('admin.inactive')); ?>

                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted"><?php echo e($user->created_at->format('Y-m-d H:i')); ?></td>
                            <td class="text-center">
                                <?php if($user->id !== auth()->id()): ?>
                                    <!-- Toggle Status Button -->
                                    <form action="<?php echo e(route('admin.users.toggle', $user)); ?>" method="POST" 
                                          onsubmit="return confirm('<?php echo e($user->is_active ? __('admin.confirm_deactivate_user') : __('admin.confirm_activate_user')); ?>');" 
                                          class="d-inline me-1">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>
                                        <button type="submit" 
                                                class="btn btn-sm <?php echo e($user->is_active ? 'btn-warning' : 'btn-success'); ?> rounded-pill px-3" 
                                                style="transition: all 0.3s;"
                                                onmouseover="this.style.transform='scale(1.05)'"
                                                onmouseout="this.style.transform='scale(1)'">
                                            <?php if($user->is_active): ?>
                                                <i class="fas fa-ban me-1"></i><?php echo e(__('admin.deactivate')); ?>

                                            <?php else: ?>
                                                <i class="fas fa-check me-1"></i><?php echo e(__('admin.activate')); ?>

                                            <?php endif; ?>
                                        </button>
                                    </form>
                                    
                                    <!-- Delete Button -->
                                    <form action="<?php echo e(route('admin.users.delete', $user)); ?>" method="POST" 
                                          onsubmit="return confirm('<?php echo e(__('admin.confirm_delete_user')); ?>');" 
                                          class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3" 
                                                style="transition: all 0.3s;"
                                                onmouseover="this.style.transform='scale(1.05)'"
                                                onmouseout="this.style.transform='scale(1)'">
                                            <i class="fas fa-trash me-1"></i><?php echo e(__('admin.delete')); ?>

                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge bg-secondary rounded-pill px-3 py-2">
                                        <i class="fas fa-lock me-1"></i><?php echo e(__('admin.current_user')); ?>

                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    <p class="mb-0"><?php echo e(__('admin.no_users_found')); ?></p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-center">
                <?php echo e($users->links()); ?>

            </div>
        </div>
    </div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH Y:\Speeda - Versions\Speeda\resources\views/admin/users/index.blade.php ENDPATH**/ ?>