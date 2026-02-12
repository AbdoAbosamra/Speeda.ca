

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

            <!-- Filter Tabs and Per Page Selector -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
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
                        <form method="GET" action="<?php echo e(route('admin.reviews')); ?>" class="d-flex align-items-center gap-2">
                            <?php if(request('status')): ?>
                                <input type="hidden" name="status" value="<?php echo e(request('status')); ?>">
                            <?php endif; ?>
                            <label for="per_page" class="text-muted small mb-0"><?php echo e(__('admin.show')); ?>:</label>
                            <select name="per_page" id="per_page" class="form-select form-select-sm rounded-pill" 
                                    style="width: auto; min-width: 80px;" onchange="this.form.submit()">
                                <option value="10" <?php echo e(request('per_page', 20) == 10 ? 'selected' : ''); ?>>10</option>
                                <option value="25" <?php echo e(request('per_page', 20) == 25 ? 'selected' : ''); ?>>25</option>
                                <option value="50" <?php echo e(request('per_page', 20) == 50 ? 'selected' : ''); ?>>50</option>
                                <option value="100" <?php echo e(request('per_page', 20) == 100 ? 'selected' : ''); ?>>100</option>
                            </select>
                            <span class="text-muted small"><?php echo e(__('admin.per_page')); ?></span>
                        </form>
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
                                    <th class="fw-bold py-3"><?php echo e(__('admin.review_title')); ?></th>
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
                                            <?php if(isset($review->serviceProvider) && $review->serviceProvider): ?>
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-2"
                                                        style="width: 36px; height: 36px; font-size: 0.875rem;">
                                                        <?php echo e(strtoupper(substr($review->serviceProvider->user->name ?? 'P', 0, 1))); ?>

                                                    </div>
                                                    <div>
                                                        <strong><?php echo e($review->serviceProvider->user->name ?? __('admin.unknown')); ?></strong>
                                                        <div class="text-muted small">ID: <?php echo e($review->serviceProvider->id ?? 'N/A'); ?></div>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted"><i class="fas fa-exclamation-circle me-1"></i><?php echo e(__('admin.not_available')); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3">
                                            <?php if(isset($review->title) && $review->title): ?>
                                                <strong><?php echo e(Str::limit($review->title, 40)); ?></strong>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
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
                                        <td class="py-3" style="max-width: 200px;">
                                            <?php if(isset($review->review_text) && $review->review_text): ?>
                                                <a href="#" class="text-decoration-none"
                                                   data-bs-toggle="modal"
                                                   data-bs-target="#reviewModal<?php echo e($review->id); ?>"
                                                   style="color: #4361ee;">
                                                    <i class="fas fa-comment-dots me-1"></i>
                                                    <?php echo e(Str::limit($review->review_text, 60) ?: 'View Review'); ?>

                                                    <i class="fas fa-expand-alt ms-1 small"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
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
                                            <small class="text-muted"><?php echo e(isset($review->created_at) && $review->created_at ? $review->created_at->format('M d, Y') : '-'); ?></small>
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
                                        <td colspan="8" class="text-center py-5">
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

    <!-- Review Modals -->
    <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($review->review_text): ?>
            <div class="modal fade" id="reviewModal<?php echo e($review->id); ?>" tabindex="-1" aria-labelledby="reviewModalLabel<?php echo e($review->id); ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="border-radius: 16px; border: none;">
                        <div class="modal-header" style="background: linear-gradient(135deg, #4361ee, #3f37c9); border-radius: 16px 16px 0 0;">
                            <h5 class="modal-title text-white" id="reviewModalLabel<?php echo e($review->id); ?>">
                                <i class="fas fa-comment-dots me-2"></i><?php echo e(__('admin.review_details')); ?>

                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <!-- Review Meta -->
                            <div class="d-flex align-items-center mb-3 pb-3" style="border-bottom: 1px solid #e2e8f0;">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                                     style="width: 40px; height: 40px; font-size: 1rem;">
                                    <?php echo e(strtoupper(substr($review->client->name ?? 'U', 0, 1))); ?>

                                </div>
                                <div>
                                    <strong><?php echo e($review->client->name ?? __('admin.unknown')); ?></strong>
                                    <div class="text-muted small">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo e($i <= $review->rating ? 'text-warning' : 'text-muted'); ?>" style="font-size: 0.75rem;"></i>
                                        <?php endfor; ?>
                                        <span class="ms-1"><?php echo e($review->rating); ?>/5</span>
                                    </div>
                                </div>
                                <div class="ms-auto text-muted small">
                                    <?php echo e($review->created_at->format('M d, Y')); ?>

                                </div>
                            </div>

                            <!-- Full Review Text -->
                            <div class="bg-light p-3 rounded-3" style="max-height: 400px; overflow-y: auto;">
                                <p class="mb-0" style="white-space: pre-wrap; line-height: 1.8;"><?php echo e($review->review_text); ?></p>
                            </div>

                            <!-- Provider Info -->
                            <div class="mt-3 pt-3" style="border-top: 1px solid #e2e8f0;">
                                <small class="text-muted">
                                    <i class="fas fa-briefcase me-1"></i>
                                    <?php echo e(__('admin.provider')); ?>:
                                    <strong><?php echo e($review->serviceProvider->user->name ?? __('admin.not_available')); ?></strong>
                                    <?php if($review->serviceProvider): ?>
                                        <span class="text-muted">(ID: <?php echo e($review->serviceProvider->id); ?>)</span>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                        <div class="modal-footer" style="border-top: 1px solid #e2e8f0;">
                            <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i><?php echo e(__('general.close')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH Y:\Speeda - Versions\Speeda\resources\views/admin/reviews/index.blade.php ENDPATH**/ ?>