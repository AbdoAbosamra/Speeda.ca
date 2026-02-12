<?php $__env->startSection('content'); ?>

<!-- Main Content Area -->
<div class="admin-content-wrapper" >
    <div class="container py-4" >

        
        <div class="d-flex flex-wrap align-items-end justify-content-between mb-5">
            <div>
                <span class="badge bg-soft-indigo text-indigo px-3 py-2 rounded-pill mb-2 fw-semibold">
                    <i class="fas fa-home me-1"></i> Dashboard
                </span>
                <h1 class="display-6 fw-bold mb-1" style="color: var(--text-primary);"><?php echo e(__('admin.dashboard')); ?></h1>
                <p class="text-secondary fs-5 mb-0"><?php echo e(__('admin.welcome_back')); ?>,
                    <span class="fw-semibold text-dark"><?php echo e(auth()->user()->name); ?></span> 👋
                </p>
            </div>
        </div>

        
        <div class="row g-4 mb-5 row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-6">
            <!-- Live Visitors -->
            <div class="col">
                <div class="card h-100 border-0 shadow-sm card-hover">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="icon-circle bg-indigo-soft text-indigo">
                                <i class="fas fa-users"></i>
                            </span>
                            <span class="text-uppercase small fw-semibold text-secondary"><?php echo e(__('admin.live_visitors_label')); ?></span>
                        </div>
                        <h2 class="fw-bold mb-0 display-5 live-count" style="color: #4f46e5;"><?php echo e($stats['liveVisitors'] ?? 0); ?></h2>
                        <span class="badge bg-green-soft text-green mt-2">
                            <i class="fas fa-circle fa-2xs me-1" style="color: #10b981;"></i> <?php echo e(__('admin.active_now')); ?>

                        </span>
                    </div>
                </div>
            </div>

            <!-- Visitors Today -->
            <div class="col">
                <div class="card h-100 border-0 shadow-sm card-hover">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="icon-circle bg-pink-soft text-pink">
                                <i class="fas fa-calendar-day"></i>
                            </span>
                            <span class="text-uppercase small fw-semibold text-secondary"><?php echo e(__('admin.time_period_today')); ?></span>
                        </div>
                        <h2 class="fw-bold mb-0 display-5" style="color: #db2777;"><?php echo e($stats['visitorsToday'] ?? 0); ?></h2>
                        <span class="text-secondary small fw-semibold mt-2 d-block"><?php echo e(__('admin.unique_visitors_label')); ?></span>
                    </div>
                </div>
            </div>

            <!-- Last 7 Days -->
            <div class="col">
                <div class="card h-100 border-0 shadow-sm card-hover">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="icon-circle bg-blue-soft text-blue">
                                <i class="fas fa-calendar-week"></i>
                            </span>
                            <span class="text-uppercase small fw-semibold text-secondary"><?php echo e(__('admin.time_period_last_7_days')); ?></span>
                        </div>
                        <h2 class="fw-bold mb-0 display-5" style="color: #2563eb;"><?php echo e($stats['last7Days'] ?? 0); ?></h2>
                        <span class="text-green small fw-semibold mt-2 d-block"><i class="fas fa-check-circle me-1"></i> <?php echo e(__('admin.unique_visitors_label')); ?></span>
                    </div>
                </div>
            </div>

            <!-- Last 30 Days -->
            <div class="col">
                <div class="card h-100 border-0 shadow-sm card-hover">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="icon-circle bg-green-soft text-green">
                                <i class="fas fa-calendar-alt"></i>
                            </span>
                            <span class="text-uppercase small fw-semibold text-secondary"><?php echo e(__('admin.time_period_last_30_days')); ?></span>
                        </div>
                        <h2 class="fw-bold mb-0 display-5" style="color: #059669;"><?php echo e($stats['last30Days'] ?? 0); ?></h2>
                        <span class="text-secondary small fw-semibold mt-2 d-block"><?php echo e(__('admin.unique_visitors_label')); ?></span>
                    </div>
                </div>
            </div>

            <!-- Last 12 Months -->
            <div class="col">
                <div class="card h-100 border-0 shadow-sm card-hover">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="icon-circle bg-orange-soft text-orange">
                                <i class="fas fa-chart-line"></i>
                            </span>
                            <span class="text-uppercase small fw-semibold text-secondary"><?php echo e(__('admin.time_period_last_12_months')); ?></span>
                        </div>
                        <h2 class="fw-bold mb-0 display-5" style="color: #d97706;"><?php echo e($stats['last12Months'] ?? 0); ?></h2>
                        <span class="text-secondary small fw-semibold mt-2 d-block"><?php echo e(__('admin.unique_visitors_label')); ?></span>
                    </div>
                </div>
            </div>

            <!-- Total Visitors (All-Time) -->
            <div class="col">
                <div class="card h-100 border-0 shadow-sm card-hover">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="icon-circle bg-teal-soft text-teal">
                                <i class="fas fa-infinity"></i>
                            </span>
                            <span class="text-uppercase small fw-semibold text-secondary"><?php echo e(__('admin.time_period_all_time')); ?></span>
                        </div>
                        <h2 class="fw-bold mb-0 display-5" style="color: #0891b2;"><?php echo e($stats['totalVisitors'] ?? 0); ?></h2>
                        <span class="text-secondary small fw-semibold mt-2 d-block"><?php echo e(__('admin.total_unique_visitors_label')); ?></span>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="mb-5">
            <h5 class="fw-bold mb-3"><?php echo e(__('Quick Actions')); ?></h5>
            <div class="row g-4">
                <!-- Manage Locations -->
                <div class="col-md-4">
                    <a href="<?php echo e(route('admin.locations')); ?>" class="action-card text-decoration-none d-block p-4 rounded-4 border bg-white shadow-sm">
                        <div class="d-flex align-items-center">
                            <span class="icon-circle bg-indigo-soft text-indigo me-3">
                                <i class="fas fa-map-marker-alt"></i>
                            </span>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1 text-dark"><?php echo e(__('admin.manage_locations')); ?></h6>
                                <small class="text-secondary"><?php echo e($stats['activeLocations'] ?? 0); ?> Active / <?php echo e($stats['totalLocations'] ?? 0); ?> Total</small>
                            </div>
                            <i class="fas fa-chevron-right text-secondary"></i>
                        </div>
                    </a>
                </div>
                <!-- Manage Categories -->
                <div class="col-md-4">
                    <a href="<?php echo e(route('admin.categories')); ?>" class="action-card text-decoration-none d-block p-4 rounded-4 border bg-white shadow-sm">
                        <div class="d-flex align-items-center">
                            <span class="icon-circle bg-green-soft text-green me-3">
                                <i class="fas fa-folder"></i>
                            </span>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1 text-dark"><?php echo e(__('admin.manage_categories')); ?></h6>
                                <small class="text-secondary"><?php echo e($stats['activeCategories'] ?? 0); ?> Active / <?php echo e($stats['totalCategories'] ?? 0); ?> Total</small>
                            </div>
                            <i class="fas fa-chevron-right text-secondary"></i>
                        </div>
                    </a>
                </div>
                <!-- Users Management -->
                <div class="col-md-4">
                    <a href="<?php echo e(route('admin.users')); ?>" class="action-card text-decoration-none d-block p-4 rounded-4 border bg-white shadow-sm">
                        <div class="d-flex align-items-center">
                            <span class="icon-circle bg-blue-soft text-blue me-3">
                                <i class="fas fa-users-cog"></i>
                            </span>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1 text-dark"><?php echo e(__('admin.users_management')); ?></h6>
                                <small class="text-secondary"><?php echo e($stats['totalUsers'] ?? 0); ?> <?php echo e(__('admin.users')); ?></small>
                            </div>
                            <i class="fas fa-chevron-right text-secondary"></i>
                        </div>
                    </a>
                </div>
                <!-- Reviews Management -->
                <div class="col-md-4">
                    <a href="<?php echo e(route('admin.reviews')); ?>" class="action-card text-decoration-none d-block p-4 rounded-4 border bg-white shadow-sm">
                        <div class="d-flex align-items-center">
                            <span class="icon-circle bg-orange-soft text-orange me-3">
                                <i class="fas fa-star"></i>
                            </span>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1 text-dark"><?php echo e(__('admin.reviews_management')); ?></h6>
                                <small class="text-secondary"><?php echo e($stats['totalReviews'] ?? 0); ?> <?php echo e(__('admin.reviews_total')); ?></small>
                            </div>
                            <i class="fas fa-chevron-right text-secondary"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        
        <div class="mb-5">
            <h5 class="fw-bold mb-3"><?php echo e(__('admin.moderation_queue')); ?></h5>
            <div class="row g-4">
                <!-- Pending Reviews -->
                <div class="col-md-4">
                    <a href="<?php echo e(route('admin.reviews', ['status' => 'pending'])); ?>" class="action-card text-decoration-none d-block p-4 rounded-4 border bg-white shadow-sm">
                        <div class="d-flex align-items-center">
                            <span class="icon-circle bg-yellow-soft text-yellow me-3">
                                <i class="fas fa-star"></i>
                            </span>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1 text-dark"><?php echo e(__('admin.review_moderation')); ?></h6>
                                <small class="text-secondary">
                                    <?php if(($stats['pendingReviews'] ?? 0) > 0): ?>
                                        <span class="badge bg-warning bg-opacity-20 text-warning me-1"><?php echo e($stats['pendingReviews']); ?></span>
                                        <?php echo e(__('admin.awaiting_approval')); ?>

                                    <?php else: ?>
                                        <i class="fas fa-check-circle text-success me-1"></i> No pending reviews
                                    <?php endif; ?>
                                </small>
                            </div>
                            <i class="fas fa-chevron-right text-secondary"></i>
                        </div>
                    </a>
                </div>
                <!-- Pending Comments - REMOVED: Comment system deprecated, Review system handles comments -->
                <!--
                <div class="col-md-4">
                    <a href="<?php echo e(route('admin.comments', ['status' => 'pending'])); ?>" class="action-card text-decoration-none d-block p-4 rounded-4 border bg-white shadow-sm">
                        <div class="d-flex align-items-center">
                            <span class="icon-circle bg-blue-soft text-blue me-3">
                                <i class="fas fa-comments"></i>
                            </span>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1 text-dark"><?php echo e(__('admin.comment_moderation')); ?></h6>
                                <small class="text-secondary">
                                    <?php if(($stats['pendingComments'] ?? 0) > 0): ?>
                                        <span class="badge bg-warning bg-opacity-20 text-warning me-1"><?php echo e($stats['pendingComments']); ?></span>
                                        <?php echo e(__('admin.awaiting_approval')); ?>

                                    <?php else: ?>
                                        <i class="fas fa-check-circle text-success me-1"></i> No pending comments
                                    <?php endif; ?>
                                </small>
                            </div>
                            <i class="fas fa-chevron-right text-secondary"></i>
                        </div>
                    </a>
                </div>
                -->
                <!-- New Users Today -->
                <div class="col-md-4">
                    <div class="d-block p-4 rounded-4 border bg-white shadow-sm">
                        <div class="d-flex align-items-center">
                            <span class="icon-circle bg-green-soft text-green me-3">
                                <i class="fas fa-user-plus"></i>
                            </span>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1 text-dark"><?php echo e(__('admin.new_users_today')); ?></h6>
                                <small class="text-secondary">
                                    <span class="badge bg-success me-1"><?php echo e($stats['newUsersToday'] ?? 0); ?></span>
                                    registered today
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="row">
            <div class="col-md-4">
                <div class="card border-0 bg-transparent">
                    <div class="card-body p-0">
                        <form action="<?php echo e(route('admin.clear-cache')); ?>" method="POST" class="d-flex">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2 py-3 fw-semibold" style="border: 2px dashed #e2e8f0; border-radius: 1rem;">
                                <i class="fas fa-broom"></i> <?php echo e(__('admin.clear_caches')); ?>

                            </button>
                        </form>
                        <small class="text-secondary d-block text-center mt-2 fw-medium" style="font-size: 0.8rem;">
                            <?php echo e(__('admin.clear_cache_help_text')); ?>

                        </small>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>


<style>
    /* Icon Circles – soft, uniform */
    .icon-circle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        border-radius: 16px;
        font-size: 1.25rem;
        transition: var(--transition);
    }
    .bg-indigo-soft { background: #eef2ff; }
    .bg-pink-soft   { background: #fdf2f8; }
    .bg-blue-soft   { background: #eff6ff; }
    .bg-green-soft  { background: #ecfdf5; }
    .bg-orange-soft { background: #fff7ed; }
    .bg-teal-soft   { background: #f0fdf9; }
    .bg-purple-soft { background: #f5f3ff; }
    .bg-yellow-soft { background: #fefce8; }

    .text-indigo { color: #4f46e5 !important; }
    .text-pink   { color: #db2777 !important; }
    .text-blue   { color: #2563eb !important; }
    .text-green  { color: #059669 !important; }
    .text-orange { color: #d97706 !important; }
    .text-teal   { color: #0891b2 !important; }
    .text-purple { color: #7c3aed !important; }
    .text-yellow { color: #b45309 !important; }

    /* Card hover – subtle elevation */
    .card-hover {
        transition: var(--transition);
    }
    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover) !important;
        border-color: #e2e8f0 !important;
    }

    /* Action cards – refined */
    .action-card {
        transition: var(--transition);
        border: 1px solid var(--border-light);
        background: white;
    }
    .action-card:hover {
        border-color: var(--accent-indigo) !important;
        box-shadow: var(--shadow-hover);
        transform: translateY(-2px);
    }
    .action-card:hover .icon-circle {
        background: var(--accent-indigo) !important;
        color: white !important;
    }
    .action-card:hover .fa-chevron-right {
        color: var(--accent-indigo) !important;
        transform: translateX(4px);
    }
    .fa-chevron-right {
        transition: transform 0.15s ease;
    }

    /* Badge custom */
    .badge.bg-soft-indigo {
        background: #eef2ff;
        color: #4f46e5;
    }
    .bg-green-soft { background: #ecfdf5; }
    .text-green { color: #10b981; }

    /* Display adjustments */
    .display-5 {
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1.2;
        letter-spacing: -0.02em;
    }
    .admin-content-wrapper{
        padding-top: 20px;
        /* margin-top: 20px; */
    }

    /* RTL tweaks */
    [dir="rtl"] .me-3 { margin-left: 1rem !important; margin-right: 0 !important; }
    [dir="rtl"] .fa-chevron-right { transform: scaleX(-1); }
    [dir="rtl"] .action-card:hover .fa-chevron-right { transform: scaleX(-1) translateX(-4px); }
</style>

<script>
    // Live count update – untouched
    (function () {
        function updateLiveCount() {
            fetch('<?php echo e(route("admin.visitors.live-count")); ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const elem = document.querySelector('.live-count');
                        if (elem) elem.textContent = data.count;
                    }
                })
                .catch(error => console.error('Error fetching live count:', error));
        }
        updateLiveCount();
        setInterval(updateLiveCount, 30000);
    })();
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH Y:\Speeda - Versions\Speeda\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>