

<?php $__env->startSection('content'); ?>
    <!-- Admin Reviews Management -->
    <div class="admin-content-wrapper" style="margin-left: 0 !important;">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 fw-bold mb-1"><?php echo e(__('admin.manage_reviews')); ?></h1>
                    <p class="text-muted mb-0"><?php echo e(__('admin.manage_all_reviews')); ?></p>
                </div>
                <a href="<?php echo e(route('admin.dashboard')); ?>" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="fas fa-arrow-left me-2"></i><?php echo e(__('admin.back_to_dashboard')); ?>

                </a>
            </div>

            <!-- Filter Tabs -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-body py-3">
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="<?php echo e(route('admin.reviews')); ?>"
                            class="btn <?php echo e(!request('status') ? 'btn-primary' : 'btn-outline-secondary'); ?> rounded-pill px-3">
                            <i class="fas fa-list me-1"></i><?php echo e(__('admin.all')); ?>

                        </a>
                        <a href="<?php echo e(route('admin.reviews', ['status' => 'pending'])); ?>"
                            class="btn <?php echo e(request('status') === 'pending' ? 'btn-warning' : 'btn-outline-secondary'); ?> rounded-pill px-3">
                            <i class="fas fa-clock me-1"></i><?php echo e(__('admin.pending')); ?>

                        </a>
                        <a href="<?php echo e(route('admin.reviews', ['status' => 'active'])); ?>"
                            class="btn <?php echo e(request('status') === 'active' ? 'btn-success' : 'btn-outline-secondary'); ?> rounded-pill px-3">
                            <i class="fas fa-check me-1"></i><?php echo e(__('admin.approved')); ?>

                        </a>
                    </div>
                </div>
            </div>

            <!-- Reviews List -->
            <div class="card border-0 shadow-lg" style="border-radius: 16px; background: white;">
                <div class="card-header bg-white" style="border-bottom: 2px solid #f1f5f9; border-radius: 16px 16px 0 0;">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-star me-2 text-warning"></i><?php echo e(__('admin.reviews_list')); ?>

                        <span class="badge bg-secondary ms-2"><?php echo e($reviews->total()); ?></span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background: #f8fafc;">
                                <tr>
                                    <th class="fw-bold px-4 py-3"><?php echo e(__('admin.client')); ?></th>
                                    <th class="fw-bold py-3"><?php echo e(__('admin.provider')); ?></th>
                                    <th class="fw-bold py-3 text-center"><?php echo e(__('admin.rating')); ?></th>
                                    <th class="fw-bold py-3"><?php echo e(__('admin.review_text')); ?></th>
                                    <th class="fw-bold py-3"><?php echo e(__('admin.status')); ?></th>
                                    <th class="fw-bold py-3"><?php echo e(__('admin.date')); ?></th>
                                    <th class="fw-bold py-3 text-center"><?php echo e(__('admin.actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td class="px-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                                                    style="width: 36px; height: 36px; font-size: 0.875rem;">
                                                    <?php echo e(strtoupper(substr($review->client->name ?? 'U', 0, 1))); ?>

                                                </div>
                                                <div>
                                                    <strong><?php echo e($review->client->name ?? __('admin.unknown')); ?></strong>
                                                    <div class="text-muted small"><?php echo e($review->client->email ?? ''); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            <?php if($review->serviceProviderProfile): ?>
                                                <strong><?php echo e($review->serviceProviderProfile->user->name ?? __('admin.unknown')); ?></strong>
                                            <?php else: ?>
                                                <span class="text-muted"><?php echo e(__('admin.not_available')); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 text-center">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <?php for($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo e($i <= $review->rating ? 'text-warning' : 'text-muted'); ?>"
                                                        style="font-size: 0.875rem;"></i>
                                                <?php endfor; ?>
                                                <span class="ms-2 fw-bold"><?php echo e($review->rating); ?></span>
                                            </div>
                                        </td>
                                        <td class="py-3" style="max-width: 250px;">
                                            <div class="text-truncate" title="<?php echo e($review->review_text); ?>">
                                                <?php echo e(Str::limit($review->review_text, 80) ?: '-'); ?>

                                            </div>
                                        </td>
                                        <td class="py-3">
                                            <?php if($review->is_active): ?>
                                                <span class="badge rounded-pill px-3 py-2 bg-success">
                                                    <i class="fas fa-check-circle me-1"></i><?php echo e(__('admin.approved')); ?>

                                                </span>
                                            <?php elseif($review->admin_approved_at): ?>
                                                <span class="badge rounded-pill px-3 py-2 bg-danger">
                                                    <i class="fas fa-times-circle me-1"></i><?php echo e(__('admin.rejected')); ?>

                                                </span>
                                            <?php else: ?>
                                                <span class="badge rounded-pill px-3 py-2 bg-warning text-dark">
                                                    <i class="fas fa-clock me-1"></i><?php echo e(__('admin.pending')); ?>

                                                </span>
                                            <?php endif; ?>
                                            <?php if($review->is_featured): ?>
                                                <span class="badge rounded-pill px-3 py-2 bg-info ms-1">
                                                    <i class="fas fa-star me-1"></i><?php echo e(__('admin.featured')); ?>

                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3">
                                            <small class="text-muted"><?php echo e($review->created_at->format('M d, Y')); ?></small>
                                        </td>
                                        <td class="py-3 text-center">
                                            <div class="btn-group">
                                                <?php if(!$review->is_active && !$review->admin_approved_at): ?>
                                                    <!-- Pending Review Actions -->
                                                    <form action="<?php echo e(route('admin.reviews.approve', $review)); ?>" method="POST"
                                                        class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 me-1"
                                                            title="<?php echo e(__('admin.approve')); ?>">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <form action="<?php echo e(route('admin.reviews.reject', $review)); ?>" method="POST"
                                                        class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3 me-1"
                                                            title="<?php echo e(__('admin.reject')); ?>"
                                                            onclick="return confirm('<?php echo e(__('admin.confirm_reject_review')); ?>');">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <?php if($review->is_active && !$review->is_featured): ?>
                                                    <form action="<?php echo e(route('admin.reviews.feature', $review)); ?>" method="POST"
                                                        class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="btn btn-sm btn-info rounded-pill px-3 me-1"
                                                            title="<?php echo e(__('admin.feature')); ?>">
                                                            <i class="fas fa-star"></i>
                                                        </button>
                                                    </form>
                                                <?php elseif($review->is_featured): ?>
                                                    <form action="<?php echo e(route('admin.reviews.unfeature', $review)); ?>" method="POST"
                                                        class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit"
                                                            class="btn btn-sm btn-outline-info rounded-pill px-3 me-1"
                                                            title="<?php echo e(__('admin.unfeature')); ?>">
                                                            <i class="far fa-star"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <form action="<?php echo e(route('admin.reviews.delete', $review)); ?>" method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('<?php echo e(__('admin.confirm_delete_review')); ?>');">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit"
                                                        class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                                        title="<?php echo e(__('admin.delete')); ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                            <p class="text-muted"><?php echo e(__('admin.no_reviews')); ?></p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if($reviews->hasPages()): ?>
                    <div class="card-footer bg-white" style="border-top: 2px solid #f1f5f9; border-radius: 0 0 16px 16px;">
                        <?php echo e($reviews->appends(request()->query())->links()); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH Y:\Speeda - Versions\Speeda\resources\views/admin/reviews/index.blade.php ENDPATH**/ ?>