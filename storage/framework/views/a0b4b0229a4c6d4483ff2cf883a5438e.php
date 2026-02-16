<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['unreadNotifications' => 0]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['unreadNotifications' => 0]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $user = auth()->user();
    $isRtl = in_array(app()->getLocale(), ['ar', 'he', 'ur', 'fa']);
    $currentRoute = request()->route()?->getName();

    $breadcrumbs = [];
    $dashboardRoute = Route::has('admin.dashboard') ? route('admin.dashboard') : '#';

    if ($currentRoute === 'admin.dashboard' && Route::has('admin.dashboard')) {
        $breadcrumbs = [['label' => __('admin.dashboard'), 'url' => $dashboardRoute]];
    }
    elseif (str_starts_with($currentRoute ?? '', 'admin.categories') && Route::has('admin.categories')) {
        $breadcrumbs = [
            ['label' => __('admin.dashboard'), 'url' => $dashboardRoute],
            ['label' => __('admin.categories'), 'url' => route('admin.categories')]
        ];
        if (in_array($currentRoute, ['admin.categories.edit'])) {
            $breadcrumbs[] = ['label' => __('admin.edit'), 'url' => '#'];
        }
    }
    elseif (str_starts_with($currentRoute ?? '', 'admin.locations') && Route::has('admin.locations')) {
        $breadcrumbs = [
            ['label' => __('admin.dashboard'), 'url' => $dashboardRoute],
            ['label' => __('admin.locations'), 'url' => route('admin.locations')]
        ];
    }
    elseif (str_starts_with($currentRoute ?? '', 'admin.users') && Route::has('admin.users')) {
        $breadcrumbs = [
            ['label' => __('admin.dashboard'), 'url' => $dashboardRoute],
            ['label' => __('admin.users'), 'url' => route('admin.users')]
        ];
    }
    elseif (str_starts_with($currentRoute ?? '', 'admin.reviews') && Route::has('admin.reviews')) {
        $breadcrumbs = [
            ['label' => __('admin.dashboard'), 'url' => $dashboardRoute],
            ['label' => __('admin.reviews'), 'url' => route('admin.reviews')]
        ];
        if ($currentRoute === 'admin.reviews.show' && Route::has('admin.reviews.show')) {
            $breadcrumbs[] = ['label' => __('admin.view'), 'url' => '#'];
        }
    }
    elseif (str_starts_with($currentRoute ?? '', 'admin.comments') && Route::has('admin.comments')) {
        $breadcrumbs = [
            ['label' => __('admin.dashboard'), 'url' => $dashboardRoute],
            ['label' => __('admin.comments'), 'url' => route('admin.comments')]
        ];
        if ($currentRoute === 'admin.comments.show' && Route::has('admin.comments.show')) {
            $breadcrumbs[] = ['label' => __('admin.view'), 'url' => '#'];
        }
    }
    elseif (str_starts_with($currentRoute ?? '', 'admin.visitors') && Route::has('admin.visitors')) {
        $breadcrumbs = [
            ['label' => __('admin.dashboard'), 'url' => $dashboardRoute],
            ['label' => __('admin.visitors'), 'url' => route('admin.visitors')]
        ];
    }
    elseif (str_starts_with($currentRoute ?? '', 'admin.activity_logs') && Route::has('admin.activity_logs')) {
        $breadcrumbs = [
            ['label' => __('admin.dashboard'), 'url' => $dashboardRoute],
            ['label' => __('admin.activity_logs'), 'url' => route('admin.activity_logs')]
        ];
    }
    else {
        $breadcrumbs = [['label' => __('admin.dashboard'), 'url' => $dashboardRoute]];
    }
?>

<nav class="admin-top-bar" x-data="adminTopBar()" x-init="init()">
    <!-- Left Section -->
    <div class="admin-top-bar-left">
        <!-- Sidebar Toggle -->
        <button class="admin-toggle-btn" @click="toggleSidebar" aria-label="<?php echo e(__('admin.toggle_sidebar')); ?>">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Logo -->
        <?php if(Route::has('admin.dashboard')): ?>
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="admin-logo">
                <img src="<?php echo e(asset('images/main-logo.png')); ?>" alt="Speeda" height="36">
                <span class="brand-name">Speeda</span>
            </a>
        <?php else: ?>
            <span class="admin-logo">
                <img src="<?php echo e(asset('images/main-logo.png')); ?>" alt="Speeda" height="36">
                <span class="brand-name">Speeda</span>
            </span>
        <?php endif; ?>

        <!-- Breadcrumb -->
        <?php if(count($breadcrumbs) > 0 && $breadcrumbs[0]['url'] !== '#'): ?>
            <nav class="admin-breadcrumb" aria-label="breadcrumb">
                <ol class="breadcrumb-list">
                    <?php $__currentLoopData = $breadcrumbs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $crumb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($loop->last): ?>
                            <li class="breadcrumb-item active"><?php echo e($crumb['label']); ?></li>
                        <?php else: ?>
                            <li class="breadcrumb-item">
                                <a href="<?php echo e($crumb['url']); ?>"><?php echo e($crumb['label']); ?></a>
                                <span class="separator">/</span>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ol>
            </nav>
        <?php endif; ?>
    </div>

    <!-- Right Section -->
    <div class="admin-top-bar-right">
        <!-- Notifications -->
        <div class="admin-notifications" x-data="notificationsDropdown(<?php echo e($unreadNotifications); ?>)">
            <button class="admin-icon-btn" @click="toggle" :class="{ 'active': open }" aria-label="<?php echo e(__('admin.notifications')); ?>">
                <i class="fas fa-bell"></i>
                <span x-show="unreadCount > 0" x-text="unreadCount" class="notification-badge"></span>
            </button>
            <div x-show="open" @click.away="close" @keydown.escape.window="close"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="admin-dropdown notifications-dropdown">
                <div class="dropdown-header">
                    <span class="title"><?php echo e(__('admin.notifications') ?: 'Notifications'); ?></span>
                    <button @click="markAllAsRead" class="mark-read-btn" x-show="unreadCount > 0">
                        <?php echo e(__('admin.mark_all_read') ?: 'Mark all read'); ?>

                    </button>
                </div>
                <div class="dropdown-list">
                    <template x-if="notifications.length === 0">
                        <div class="empty-state">
                            <i class="fas fa-check-circle"></i>
                            <p><?php echo e(__('admin.no_notifications') ?: 'No notifications'); ?></p>
                        </div>
                    </template>
                    <template x-for="note in notifications" :key="note.id">
                        <a :href="note.link" class="dropdown-item" :class="{ 'unread': !note.read_at }">
                            <span class="item-icon" :style="{ background: note.color ? note.color + '10' : 'var(--accent-soft-indigo)' }">
                                <i :class="note.icon || 'fas fa-info-circle'"></i>
                            </span>
                            <span class="item-content">
                                <span class="item-title" x-text="note.title"></span>
                                <span class="item-time" x-text="note.time"></span>
                            </span>
                        </a>
                    </template>
                </div>
                <?php if(Route::has('admin.notifications')): ?>
                    <div class="dropdown-footer">
                        <a href="<?php echo e(route('admin.notifications')); ?>" class="view-all-link">
                            <?php echo e(__('admin.view_all_notifications') ?: 'View all'); ?>

                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- User Dropdown -->
        <div class="admin-user" x-data="userDropdown()">
            <button class="admin-user-btn" @click="toggle" :class="{ 'active': open }" aria-expanded="false">
                <span class="user-avatar">
                    <?php if($user && $user->profile_photo_url): ?>
                        <img src="<?php echo e($user->profile_photo_url); ?>" alt="<?php echo e($user->name); ?>">
                    <?php else: ?>
                        <span class="avatar-placeholder"><?php echo e($user ? substr($user->name, 0, 1) : 'A'); ?></span>
                    <?php endif; ?>
                </span>
                <span class="user-info">
                    <span class="user-name"><?php echo e($user->name ?? 'Admin'); ?></span>
                    <span class="user-role"><?php echo e($user && $user->isAdmin() ? (__('admin.administrator') ?: 'Administrator') : (__('admin.moderator') ?: 'Moderator')); ?></span>
                </span>
                <i class="fas fa-chevron-down user-chevron" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" @click.away="close" @keydown.escape.window="close"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="admin-dropdown user-dropdown">
                <div class="dropdown-header d-lg-none">
                    <span class="user-name"><?php echo e($user->name ?? 'Admin'); ?></span>
                    <span class="user-email"><?php echo e($user->email ?? ''); ?></span>
                </div>

 

                <?php if(Route::has('home')): ?>
                    <a href="<?php echo e(route('home')); ?>" class="dropdown-item">
                        <i class="fas fa-external-link-alt"></i> View Site
                    </a>
                <?php endif; ?>

                <?php if(Route::has('logout')): ?>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt"></i> <?php echo e(__('general.logout') ?: 'Logout'); ?>

                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<?php if (! $__env->hasRenderedOnce('5b3b335d-3fe6-45f1-971c-65222b7682d8')): $__env->markAsRenderedOnce('5b3b335d-3fe6-45f1-971c-65222b7682d8'); ?>
<style>
/* ===== ADMIN TOP BAR – PREMIUM, EVOLVED DESIGN ===== */
/* Enhanced with subtle gradients, sharper shadows, and fluid animations for a modern, high-end feel. 
   Fully integrated with Speeda's dashboard aesthetics: clean lines, indigo accents, and premium interactions. 
   No changes to Laravel logic or PHP – pure CSS evolution. */

:root {
    --admin-top-bar-height: 68px; /* Slightly slimmer for a more modern look */
    --admin-sidebar-width: 280px;
    --top-bar-blur: 16px; /* Increased blur for premium glassmorphism */
    --dropdown-shadow: 0 25px 40px -10px rgba(0,0,0,0.08), 0 10px 15px -5px rgba(0,0,0,0.04);
    --accent-indigo-gradient: linear-gradient(135deg, #4f46e5, #6366f1); /* Subtle gradient for depth */
    --border-subtle: rgba(226, 232, 240, 0.7); /* Softer borders */
    --transition-fast: all 0.12s ease-out; /* Faster for snappier interactions */
}

/* === Top Bar Container === */
.admin-top-bar {
    position: fixed;
    left: var(--admin-sidebar-width);
    right: 0;
    top: 0;
    height: var(--admin-top-bar-height);
    z-index: 1100;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 2rem; /* Wider padding for breathing room */
    background: rgba(255,255,255,0.92); /* Slightly more opaque for clarity */
    backdrop-filter: blur(var(--top-bar-blur));
    -webkit-backdrop-filter: blur(var(--top-bar-blur));
    border-bottom: 1px solid var(--border-subtle);
    box-shadow: 0 4px 12px rgba(0,0,0,0.03); /* Deeper shadow for elevation */
    transition: left 0.25s ease, right 0.25s ease, background 0.3s ease;
}

[dir="rtl"] .admin-top-bar {
    left: 0;
    right: var(--admin-sidebar-width);
}

.sidebar-collapsed .admin-top-bar {
    left: 80px;
}
[dir="rtl"] .sidebar-collapsed .admin-top-bar {
    right: 80px;
    left: 0;
}

.admin-top-bar:hover {
    background: rgba(255,255,255,0.98); /* Subtle hover feedback */
}

/* === Left Section === */
.admin-top-bar-left {
    display: flex;
    align-items: center;
    gap: 1.5rem; /* Increased gap for better spacing */
    flex: 1;
}

/* Toggle Button */
.admin-toggle-btn {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: 1px solid var(--border-subtle);
    border-radius: 14px; /* Softer radius */
    color: var(--text-secondary, #475569);
    font-size: 1.15rem;
    transition: var(--transition-fast);
    cursor: pointer;
}

.admin-toggle-btn:hover {
    background: var(--accent-soft-indigo, #eef2ff);
    color: var(--accent-indigo, #4f46e5);
    border-color: var(--accent-indigo);
    transform: translateY(-2px) scale(1.02); /* Enhanced hover lift */
    box-shadow: 0 4px 8px rgba(79,70,229,0.1);
}

/* Logo */
.admin-logo {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    text-decoration: none;
    color: var(--text-primary, #0f172a);
    font-weight: 800; /* Bolder for premium feel */
    font-size: 1.3rem;
    letter-spacing: -0.03em;
    transition: opacity 0.2s, transform 0.2s;
}

.admin-logo:hover {
    opacity: 0.95;
    transform: scale(1.02); /* Subtle zoom on hover */
}

.admin-logo img {
    height: 38px; /* Slightly larger for impact */
    width: auto;
    transition: transform 0.3s ease;
}

.admin-logo:hover img {
    transform: rotate(5deg) scale(1.05); /* Playful animation */
}

.brand-name {
    display: inline-block;
    background: var(--accent-indigo-gradient); /* Gradient text for logo */
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* === Breadcrumb – Sleek, Minimal === */
.admin-breadcrumb {
    margin-left: 1rem;
}

.breadcrumb-list {
    display: flex;
    align-items: center;
    list-style: none;
    padding: 0;
    margin: 0;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.breadcrumb-item {
    display: flex;
    align-items: center;
    color: var(--text-secondary, #64748b);
    font-size: 0.92rem;
    font-weight: 500;
    transition: color 0.2s;
}

.breadcrumb-item a {
    color: var(--text-secondary);
    text-decoration: none;
    transition: color 0.2s, transform 0.2s;
}

.breadcrumb-item a:hover {
    color: var(--accent-indigo);
    transform: translateX(2px); /* Subtle shift on hover */
}

.breadcrumb-item.active {
    color: var(--text-primary);
    font-weight: 700; /* Bolder active state */
}

.separator {
    margin-left: 0.5rem;
    color: #d1d5db; /* Softer separator */
}

[dir="rtl"] .separator {
    margin-left: 0;
    margin-right: 0.5rem;
}

/* === Right Section === */
.admin-top-bar-right {
    display: flex;
    align-items: center;
    gap: 0.75rem; /* Tighter for compact feel */
    flex-shrink: 0;
}

/* === Icon Buttons (Notifications, etc) === */
.admin-icon-btn {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: 1px solid var(--border-subtle);
    border-radius: 14px;
    color: var(--text-secondary);
    font-size: 1.15rem;
    transition: var(--transition-fast);
    cursor: pointer;
    position: relative;
}

.admin-icon-btn:hover,
.admin-icon-btn.active {
    background: var(--accent-soft-indigo);
    color: var(--accent-indigo);
    border-color: var(--accent-indigo);
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 6px 12px rgba(79,70,229,0.12); /* Deeper shadow */
}

.notification-badge {
    position: absolute;
    top: -6px;
    right: -6px;
    min-width: 22px;
    height: 22px;
    padding: 0 0.4rem;
    background: #ef4444;
    color: white;
    font-size: 0.7rem;
    font-weight: 700;
    border-radius: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid white;
    animation: pulse 1.5s infinite; /* Subtle pulse animation */
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

[dir="rtl"] .notification-badge {
    right: auto;
    left: -6px;
}

/* === Dropdowns – Elevated Card Style === */
.admin-dropdown {
    position: absolute;
    top: calc(100% + 12px);
    right: 0;
    width: 380px; /* Slightly wider for comfort */
    background: white;
    border: 1px solid var(--border-subtle);
    border-radius: 24px; /* Softer, modern radius */
    box-shadow: var(--dropdown-shadow);
    padding: 1rem;
    z-index: 1150;
    overflow: hidden; /* For smooth inner scrolling */
}

[dir="rtl"] .admin-dropdown {
    right: auto;
    left: 0;
}

.user-dropdown {
    width: 300px; /* Optimized for user menu */
}

/* Dropdown Header */
.dropdown-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem 1rem;
    border-bottom: 1px solid var(--border-subtle);
    margin-bottom: 0.75rem;
}

.dropdown-header .title {
    font-weight: 700;
    color: var(--text-primary);
    font-size: 1.1rem; /* Larger for hierarchy */
}

.mark-read-btn {
    background: transparent;
    border: none;
    color: var(--accent-indigo);
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    padding: 0.3rem 0.85rem;
    border-radius: 30px;
    transition: background 0.2s, transform 0.2s;
}

.mark-read-btn:hover {
    background: var(--accent-soft-indigo);
    transform: scale(1.03);
}

/* Dropdown List */
.dropdown-list {
    max-height: 380px;
    overflow-y: auto;
    padding: 0 0.5rem;
    scrollbar-width: thin; /* Slim scrollbar */
    scrollbar-color: var(--accent-indigo) #f1f5f9;
}

/* Custom Scrollbar */
.dropdown-list::-webkit-scrollbar {
    width: 6px;
}
.dropdown-list::-webkit-scrollbar-thumb {
    background: var(--accent-indigo);
    border-radius: 10px;
}
.dropdown-list::-webkit-scrollbar-track {
    background: #f1f5f9;
}

.empty-state {
    text-align: center;
    padding: 2.5rem 1.5rem;
    color: var(--text-secondary);
}

.empty-state i {
    font-size: 3rem;
    color: var(--border-light);
    margin-bottom: 0.75rem;
}

.empty-state p {
    font-size: 1rem;
    margin-bottom: 0;
}

/* Dropdown Item */
.dropdown-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.8rem 1rem;
    border-radius: 16px;
    color: var(--text-primary);
    font-size: 0.92rem;
    font-weight: 500;
    text-decoration: none;
    transition: var(--transition-fast);
    width: 100%;
    background: none;
    border: none;
    cursor: pointer;
}

.dropdown-item:hover {
    background: #f8fafc;
    transform: translateX(4px); /* Slide effect on hover */
}

.dropdown-item i {
    width: 1.3rem;
    color: var(--text-secondary);
    font-size: 1rem;
    transition: color 0.2s;
}

.dropdown-item:hover i {
    color: var(--accent-indigo);
}

.dropdown-item.text-danger:hover {
    background: #fef2f2;
}

.dropdown-item.text-danger:hover i {
    color: #b91c1c;
}

.dropdown-item.unread {
    background: var(--accent-soft-indigo);
    border-left: 4px solid var(--accent-indigo); /* Indicator for unread */
}

.item-icon {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--accent-soft-indigo);
    border-radius: 12px;
    color: var(--accent-indigo);
    flex-shrink: 0;
    transition: background 0.2s;
}

.dropdown-item:hover .item-icon {
    background: var(--accent-indigo);
    color: white;
}

.item-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.item-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--text-primary);
}

.item-time {
    font-size: 0.75rem;
    color: var(--text-secondary);
}

/* Dropdown Footer */
.dropdown-footer {
    padding: 1rem 0.75rem 0.5rem;
    border-top: 1px solid var(--border-subtle);
    margin-top: 0.5rem;
    text-align: center;
}

.view-all-link {
    color: var(--accent-indigo);
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    transition: text-decoration 0.2s;
}

.view-all-link:hover {
    text-decoration: underline wavy; /* Modern underline style */
}

/* === User Button === */
.admin-user-btn {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    background: white;
    border: 1px solid var(--border-subtle);
    border-radius: 50px; /* Pill shape */
    padding: 0.35rem 0.35rem 0.35rem 0.6rem;
    transition: var(--transition-fast);
    cursor: pointer;
}

.admin-user-btn:hover,
.admin-user-btn.active {
    border-color: var(--accent-indigo);
    background: linear-gradient(135deg, #fafafa, #f8fafc); /* Subtle gradient */
    box-shadow: 0 6px 14px rgba(79,70,229,0.08);
    transform: translateY(-2px) scale(1.01);
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 14px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--accent-soft-indigo);
    flex-shrink: 0;
    transition: box-shadow 0.2s;
}

.admin-user-btn:hover .user-avatar {
    box-shadow: 0 0 0 2px var(--accent-indigo); /* Glow on hover */
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    font-weight: 800;
    color: var(--accent-indigo);
    text-transform: uppercase;
    font-size: 1.2rem;
}

.user-info {
    display: flex;
    flex-direction: column;
    line-height: 1.35;
    text-align: left;
}

[dir="rtl"] .user-info {
    text-align: right;
}

.user-name {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text-primary);
}

.user-role {
    font-size: 0.75rem;
    color: var(--text-secondary);
}

.user-chevron {
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin-right: 0.6rem;
    transition: transform 0.25s ease;
}

[dir="rtl"] .user-chevron {
    margin-right: 0;
    margin-left: 0.6rem;
}

.rotate-180 {
    transform: rotate(180deg);
}

/* === Language Switcher === */
.language-switcher {
    border-top: 1px solid var(--border-subtle);
    border-bottom: 1px solid var(--border-subtle);
    margin: 0.75rem 0;
    padding: 0.35rem 0;
}

.language-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    background: none;
    border: none;
    padding: 0.7rem 1rem;
    color: var(--text-primary);
    font-size: 0.92rem;
    font-weight: 500;
    cursor: pointer;
    border-radius: 14px;
    transition: background 0.2s, transform 0.2s;
}

.language-btn:hover {
    background: #f8fafc;
    transform: scale(1.01);
}

.language-btn i:first-child {
    margin-right: 0.85rem;
    color: var(--text-secondary);
}

.language-options {
    padding-left: 1rem;
}

.language-options .dropdown-item {
    padding: 0.6rem 1rem;
    border-radius: 12px;
}

.language-options .dropdown-item.active {
    background: var(--accent-soft-indigo);
    color: var(--accent-indigo);
    font-weight: 700;
}

/* === Dropdown Divider === */
.dropdown-divider {
    height: 1px;
    background: var(--border-subtle);
    margin: 0.75rem 0;
}

/* === Responsive Enhancements === */
@media (max-width: 992px) {
    .admin-top-bar {
        padding: 0 1.5rem;
    }
    .admin-breadcrumb {
        display: none; /* Hide breadcrumbs on smaller screens for simplicity */
    }
    .user-info {
        display: none; /* Compact user button */
    }
    .admin-user-btn {
        padding: 0.35rem;
    }
    .notifications-dropdown {
        width: 360px;
        right: -30px;
    }
    [dir="rtl"] .notifications-dropdown {
        right: auto;
        left: -30px;
    }
}

@media (max-width: 768px) {
    .admin-top-bar {
        left: 0 !important;
        right: 0 !important;
        padding: 0 1.25rem;
    }
    [dir="rtl"] .admin-top-bar {
        left: 0 !important;
        right: 0 !important;
    }
    .admin-toggle-btn {
        width: 40px;
        height: 40px;
    }
    .admin-icon-btn {
        width: 40px;
        height: 40px;
    }
    .admin-logo img {
        height: 34px;
    }
}

@media (max-width: 576px) {
    .admin-top-bar {
        padding: 0 1rem;
    }
    .notifications-dropdown {
        width: 320px;
        right: -80px;
    }
    [dir="rtl"] .notifications-dropdown {
        right: auto;
        left: -80px;
    }
    .user-dropdown {
        width: 280px;
    }
}
</style>

<script>
document.addEventListener('alpine:init', () => {
    // Admin Top Bar State
    Alpine.data('adminTopBar', () => ({
        sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
        init() {
            window.addEventListener('toggle-sidebar', () => {
                this.sidebarCollapsed = !this.sidebarCollapsed;
                localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
                document.body.classList.toggle('sidebar-collapsed', this.sidebarCollapsed);
            });
            document.body.classList.toggle('sidebar-collapsed', this.sidebarCollapsed);
        },
        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
            document.body.classList.toggle('sidebar-collapsed', this.sidebarCollapsed);
            window.dispatchEvent(new CustomEvent('sidebar-toggled', { detail: { collapsed: this.sidebarCollapsed } }));
        }
    }));

    // Notifications Dropdown
    Alpine.data('notificationsDropdown', (initialUnread) => ({
        open: false,
        unreadCount: initialUnread,
        notifications: [
            {
                id: 1,
                title: '<?php echo e(__("admin.sample_notification_new_user") ?: "New user registered"); ?>',
                time: '<?php echo e(__("admin.time_just_now") ?: "Just now"); ?>',
                link: '#',
                icon: 'fas fa-user-plus',
                color: '#4f46e5',
                read_at: null
            },
            {
                id: 2,
                title: '<?php echo e(__("admin.sample_notification_new_provider") ?: "New service provider"); ?>',
                time: '<?php echo e(__("admin.time_1_hour_ago") ?: "1 hour ago"); ?>',
                link: '#',
                icon: 'fas fa-building',
                color: '#059669',
                read_at: null
            }
        ],
        toggle() { this.open = !this.open; },
        close() { this.open = false; },
        markAllAsRead() {
            this.unreadCount = 0;
            this.notifications.forEach(n => n.read_at = new Date());
        },
        updateCount(newCount) { this.unreadCount = newCount; }
    }));

    // User Dropdown
    Alpine.data('userDropdown', () => ({
        open: false,
        toggle() { this.open = !this.open; },
        close() { this.open = false; }
    }));
});
</script>
<?php endif; ?><?php /**PATH Y:\Speeda - Versions\Speeda\resources\views/components/admin-top-bar.blade.php ENDPATH**/ ?>