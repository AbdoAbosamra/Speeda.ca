

<?php $__env->startSection('content'); ?>
    <!-- Admin Comments Management -->
    <div class="admin-content-wrapper" style="margin-left: 0 !important;">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 fw-bold mb-1"><?php echo e(__('admin.manage_comments')); ?></h1>
                    <p class="text-muted mb-0"><?php echo e(__('admin.manage_all_comments')); ?></p>
                </div>
                <a href="<?php echo e(route('admin.dashboard')); ?>" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="fas fa-arrow-left me-2"></i><?php echo e(__('admin.back_to_dashboard')); ?>

                </a>
            </div>

            <!-- Filter Tabs -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-body py-3">
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="<?php echo e(route('admin.comments')); ?>"
                            class="btn <?php echo e(!request('status') ? 'btn-primary' : 'btn-outline-secondary'); ?> rounded-pill px-3">
                            <i class="fas fa-list me-1"></i><?php echo e(__('admin.all')); ?>

                        </a>
                        <a href="<?php echo e(route('admin.comments', ['status' => 'pending'])); ?>"
                            class="btn <?php echo e(request('status') === 'pending' ? 'btn-warning' : 'btn-outline-secondary'); ?> rounded-pill px-3">
                            <i class="fas fa-clock me-1"></i><?php echo e(__('admin.pending')); ?>

                        </a>
                        <a href="<?php echo e(route('admin.comments', ['status' => 'active'])); ?>"
                            class="btn <?php echo e(request('status') === 'active' ? 'btn-success' : 'btn-outline-secondary'); ?> rounded-pill px-3">
                            <i class="fas fa-check me-1"></i><?php echo e(__('admin.approved')); ?>

                        </a>
                        <a href="<?php echo e(route('admin.comments', ['status' => 'flagged'])); ?>"
                            class="btn <?php echo e(request('status') === 'flagged' ? 'btn-danger' : 'btn-outline-secondary'); ?> rounded-pill px-3">
                            <i class="fas fa-flag me-1"></i><?php echo e(__('admin.flagged')); ?>

                        </a>
                        <a href="<?php echo e(route('admin.comments', ['status' => 'rejected'])); ?>"
                            class="btn <?php echo e(request('status') === 'rejected' ? 'btn-dark' : 'btn-outline-secondary'); ?> rounded-pill px-3">
                            <i class="fas fa-ban me-1"></i><?php echo e(__('admin.rejected')); ?>

                        </a>
                    </div>
                </div>
            </div>

            <!-- Comments List -->
            <div class="card border-0 shadow-lg" style="border-radius: 16px; background: white;">
                <div class="card-header bg-white" style="border-bottom: 2px solid #f1f5f9; border-radius: 16px 16px 0 0;">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-comments me-2 text-info"></i><?php echo e(__('admin.comments_list')); ?>

                        <span class="badge bg-secondary ms-2"><?php echo e($comments->total()); ?></span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background: #f8fafc;">
                                <tr>
                                    <th class="fw-bold px-4 py-3"><?php echo e(__('admin.user')); ?></th>
                                    <th class="fw-bold py-3"><?php echo e(__('admin.content')); ?></th>
                                    <th class="fw-bold py-3"><?php echo e(__('admin.type')); ?></th>
                                    <th class="fw-bold py-3"><?php echo e(__('admin.status')); ?></th>
                                    <th class="fw-bold py-3"><?php echo e(__('admin.date')); ?></th>
                                    <th class="fw-bold py-3 text-center"><?php echo e(__('admin.actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td class="px-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center me-2"
                                                    style="width: 36px; height: 36px; font-size: 0.875rem;">
                                                    <?php echo e(strtoupper(substr($comment->user->name ?? 'U', 0, 1))); ?>

                                                </div>
                                                <div>
                                                    <strong><?php echo e($comment->user->name ?? __('admin.unknown')); ?></strong>
                                                    <div class="text-muted small"><?php echo e($comment->user->email ?? ''); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3" style="max-width: 300px;">
                                            <div class="text-truncate" title="<?php echo e($comment->content); ?>">
                                                <?php echo e(Str::limit($comment->content, 100)); ?>

                                            </div>
                                            <?php if($comment->rejection_reason): ?>
                                                <div class="text-danger small mt-1">
                                                    <i class="fas fa-info-circle me-1"></i><?php echo e(__('admin.rejection_reason')); ?>:
                                                    <?php echo e($comment->rejection_reason); ?>

                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3">
                                            <?php
                                                $typeLabel = class_basename($comment->commentable_type ?? 'Unknown');
                                            ?>
                                            <span class="badge rounded-pill px-3 py-2 bg-light text-dark">
                                                <i class="fas fa-link me-1"></i><?php echo e($typeLabel); ?>

                                            </span>
                                        </td>
                                        <td class="py-3">
                                            <?php if($comment->is_active): ?>
                                                <span class="badge rounded-pill px-3 py-2 bg-success">
                                                    <i class="fas fa-check-circle me-1"></i><?php echo e(__('admin.approved')); ?>

                                                </span>
                                            <?php elseif($comment->is_flagged): ?>
                                                <span class="badge rounded-pill px-3 py-2 bg-danger">
                                                    <i class="fas fa-flag me-1"></i><?php echo e(__('admin.flagged')); ?>

                                                </span>
                                            <?php elseif($comment->rejection_reason): ?>
                                                <span class="badge rounded-pill px-3 py-2 bg-dark">
                                                    <i class="fas fa-ban me-1"></i><?php echo e(__('admin.rejected')); ?>

                                                </span>
                                            <?php else: ?>
                                                <span class="badge rounded-pill px-3 py-2 bg-warning text-dark">
                                                    <i class="fas fa-clock me-1"></i><?php echo e(__('admin.pending')); ?>

                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3">
                                            <small class="text-muted"><?php echo e($comment->created_at->format('M d, Y')); ?></small>
                                            <div class="text-muted small"><?php echo e($comment->created_at->format('H:i')); ?></div>
                                        </td>
                                        <td class="py-3 text-center">
                                            <div class="btn-group">
                                                <?php if(!$comment->is_active && !$comment->rejection_reason): ?>
                                                    <!-- Pending Comment Actions -->
                                                    <form action="<?php echo e(route('admin.comments.approve', $comment)); ?>" method="POST"
                                                        class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 me-1"
                                                            title="<?php echo e(__('admin.approve')); ?>">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 me-1"
                                                        title="<?php echo e(__('admin.reject')); ?>" data-bs-toggle="modal"
                                                        data-bs-target="#rejectModal<?php echo e($comment->id); ?>">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php endif; ?>

                                                <?php if(!$comment->is_flagged && $comment->is_active): ?>
                                                    <form action="<?php echo e(route('admin.comments.flag', $comment)); ?>" method="POST"
                                                        class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit"
                                                            class="btn btn-sm btn-outline-warning rounded-pill px-3 me-1"
                                                            title="<?php echo e(__('admin.flag')); ?>">
                                                            <i class="fas fa-flag"></i>
                                                        </button>
                                                    </form>
                                                <?php elseif($comment->is_flagged): ?>
                                                    <form action="<?php echo e(route('admin.comments.unflag', $comment)); ?>" method="POST"
                                                        class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 me-1"
                                                            title="<?php echo e(__('admin.unflag')); ?>">
                                                            <i class="fas fa-check"></i> <?php echo e(__('admin.approve')); ?>

                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <form action="<?php echo e(route('admin.comments.delete', $comment)); ?>" method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('<?php echo e(__('admin.confirm_delete_comment')); ?>');">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit"
                                                        class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                                        title="<?php echo e(__('admin.delete')); ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>

                                            <!-- Reject Modal -->
                                            <div class="modal fade" id="rejectModal<?php echo e($comment->id); ?>" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content" style="border-radius: 16px; border: none;">
                                                        <div class="modal-header" style="border-bottom: 2px solid #f1f5f9;">
                                                            <h5 class="modal-title fw-bold">
                                                                <i
                                                                    class="fas fa-times-circle me-2 text-danger"></i><?php echo e(__('admin.reject_comment')); ?>

                                                            </h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form action="<?php echo e(route('admin.comments.reject', $comment)); ?>"
                                                            method="POST">
                                                            <?php echo csrf_field(); ?>
                                                            <div class="modal-body">
                                                                <label
                                                                    class="form-label fw-semibold"><?php echo e(__('admin.rejection_reason')); ?>

                                                                    (<?php echo e(__('admin.optional')); ?>)</label>
                                                                <textarea name="reason" class="form-control" rows="3"
                                                                    placeholder="<?php echo e(__('admin.enter_rejection_reason')); ?>"
                                                                    style="border-radius: 12px; border: 2px solid #e2e8f0;"></textarea>
                                                            </div>
                                                            <div class="modal-footer" style="border-top: 2px solid #f1f5f9;">
                                                                <button type="button"
                                                                    class="btn btn-secondary rounded-pill px-4"
                                                                    data-bs-dismiss="modal">
                                                                    <?php echo e(__('admin.cancel')); ?>

                                                                </button>
                                                                <button type="submit" class="btn btn-danger rounded-pill px-4">
                                                                    <i class="fas fa-times me-1"></i><?php echo e(__('admin.reject')); ?>

                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                            <p class="text-muted"><?php echo e(__('admin.no_comments')); ?></p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if($comments->hasPages()): ?>
                    <div class="card-footer bg-white" style="border-top: 2px solid #f1f5f9; border-radius: 0 0 16px 16px;">
                        <?php echo e($comments->appends(request()->query())->links()); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH Y:\Speeda - Versions\Speeda\resources\views/admin/comments/index.blade.php ENDPATH**/ ?>