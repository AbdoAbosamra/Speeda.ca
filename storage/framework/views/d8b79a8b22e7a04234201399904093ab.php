<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>"
      dir="<?php echo e(in_array(app()->getLocale(), ['ar', 'he', 'ur', 'fa']) ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'Speeda')); ?></title>
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/main-logo.png')); ?>">

    <!-- Google Font: Inter (clean, modern) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">

    <!-- Bootstrap + Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        /* ----- DESIGN SYSTEM – Premium Light Mode ----- */
        :root {
            --bg-body: #f9fbfd;
            --card-bg: #ffffff;
            --border-light: #eef2f6;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --accent-indigo: #4f46e5;
            --accent-soft-indigo: #eef2ff;
            --shadow-sm: 0 1px 3px 0 rgb(0 0 0 / 0.02), 0 1px 2px -1px rgb(0 0 0 / 0.02);
            --shadow-md: 0 4px 6px -2px rgb(0 0 0 / 0.02), 0 2px 4px -2px rgb(0 0 0 / 0.02);
            --shadow-hover: 0 20px 25px -8px rgb(0 0 0 / 0.03), 0 8px 10px -6px rgb(0 0 0 / 0.02);
            --radius-card: 1.25rem;
            --radius-btn: 0.75rem;
            --transition: all 0.15s ease;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-primary);
            line-height: 1.6;
        }

        /* ----- Typography ----- */
        h1, h2, h3, h4, h5, h6 {
            font-weight: 600;
            letter-spacing: -0.01em;
        }

        /* ----- Cards & Containers ----- */
        .card, .card-panel {
            background-color: var(--card-bg);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-card);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }
        .card:hover {
            box-shadow: var(--shadow-hover);
            border-color: #e2e8f0;
        }

        /* ----- Subtle Interactive Elements ----- */
        a:not(.btn):not(.dropdown-item):not(.nav-link) {
            color: var(--accent-indigo);
            text-decoration: none;
            transition: var(--transition);
        }
        a:not(.btn):not(.dropdown-item):not(.nav-link):hover {
            color: #4338ca;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        /* ----- Buttons – Clean, Minimal ----- */
        .btn {
            border-radius: var(--radius-btn);
            font-weight: 500;
            padding: 0.5rem 1.25rem;
            transition: var(--transition);
            border-width: 1px;
        }
        .btn-primary {
            background-color: var(--accent-indigo);
            border-color: var(--accent-indigo);
            color: white;
            box-shadow: 0 2px 4px rgba(79, 70, 229, 0.1);
        }
        .btn-primary:hover {
            background-color: #4338ca;
            border-color: #4338ca;
            box-shadow: 0 4px 8px rgba(79, 70, 229, 0.15);
            transform: translateY(-1px);
        }
        .btn-outline-secondary {
            border-color: var(--border-light);
            color: var(--text-secondary);
            background: white;
        }
        .btn-outline-secondary:hover {
            background-color: #f8fafc;
            border-color: #cbd5e1;
            color: var(--text-primary);
            transform: translateY(-1px);
        }

        /* ----- Admin Top Bar – Refined, glass-like ----- */
        .admin-top-bar {
            position: fixed;
            left: 280px;
            right: 0;
            top: 0;
            z-index: 1100;
            background: rgba(255, 255, 255, 0.90);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid var(--border-light);
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        }
        [dir="rtl"] .admin-top-bar {
            left: 0;
            right: 280px;
        }
        .admin-top-bar .btn {
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: 2rem;
        }

        /* ----- Quick Admin Link – Floating Pill ----- */
        .admin-quick-link {
            position: fixed;
            right: 1.5rem;
            bottom: 1.5rem;
            z-index: 2000;
            background: white;
            border: 1px solid var(--border-light);
            color: var(--text-primary);
            padding: 0.6rem 1.2rem;
            border-radius: 2rem;
            box-shadow: var(--shadow-md);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            transition: var(--transition);
            backdrop-filter: blur(8px);
        }
        [dir="rtl"] .admin-quick-link {
            right: auto;
            left: 1.5rem;
        }
        .admin-quick-link:hover {
            background: #f8fafc;
            border-color: var(--accent-indigo);
            color: var(--accent-indigo);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.1);
            transform: translateY(-2px);
        }
        .admin-quick-link i {
            color: var(--accent-indigo);
        }

        /* ----- RTL Spacing Helpers (Bootstrap 5 native) ----- */
        [dir="rtl"] .ms-auto { margin-right: auto !important; margin-left: 0 !important; }
        [dir="rtl"] .me-auto { margin-left: auto !important; margin-right: 0 !important; }

        /* ----- Page Header / Navigation Placeholder ----- */
        .navbar {
            background: white !important;
            border-bottom: 1px solid var(--border-light);
            box-shadow: var(--shadow-sm);
        }

        /* ----- Content Spacing ----- */
        .admin-top-bar + * {
            margin-top: 72px !important;
        }
        main.py-4 {
            padding-top: 2rem !important;
            padding-bottom: 3rem !important;
        }

        /* ----- Utility: subtle border radius ----- */
        .rounded-2xl { border-radius: 1rem; }
        .rounded-3xl { border-radius: 1.5rem; }

        /* ----- Icons color balance ----- */
        i[class^="fa-"], i[class*=" fa-"] {
            color: currentColor;
        }
    </style>
</head>
<body class="antialiased bg-light">

    <div class="min-vh-100 d-flex flex-column">
        <?php if(!request()->routeIs('admin.*')): ?>
            <?php echo $__env->make('layouts.navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>

        
        <?php if(auth()->guard()->check()): ?>
            <?php if(auth()->user()->isAdmin() && !request()->routeIs('admin.*')): ?>
                <a href="<?php echo e(route('admin.dashboard')); ?>" class="admin-quick-link" title="<?php echo e(__('admin.dashboard')); ?>">
                    <i class="fas fa-shield-alt fa-fw"></i>
                    <span><?php echo e(__('admin.dashboard')); ?></span>
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <?php if(request()->routeIs('admin.*')): ?>
    <style>
        /* إخفاء النافبار العام */
        .sp-nav,
        nav:not(.admin-sidebar):not(.admin-top-bar),
        header[class*="nav"] {
            display: none !important;
        }
        /* ضبط الـ main padding ليتوافق مع الـ admin bar الجديد */
        .admin-top-bar + * {
            margin-top: 64px !important;
        }
    </style>

    
    <?php if (isset($component)) { $__componentOriginalb4ba8a35f2c9dd0dc68e682f3c1f7be3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4ba8a35f2c9dd0dc68e682f3c1f7be3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-top-bar','data' => ['unreadNotifications' => 0]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-top-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['unreadNotifications' => 0]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb4ba8a35f2c9dd0dc68e682f3c1f7be3)): ?>
<?php $attributes = $__attributesOriginalb4ba8a35f2c9dd0dc68e682f3c1f7be3; ?>
<?php unset($__attributesOriginalb4ba8a35f2c9dd0dc68e682f3c1f7be3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb4ba8a35f2c9dd0dc68e682f3c1f7be3)): ?>
<?php $component = $__componentOriginalb4ba8a35f2c9dd0dc68e682f3c1f7be3; ?>
<?php unset($__componentOriginalb4ba8a35f2c9dd0dc68e682f3c1f7be3); ?>
<?php endif; ?>
<?php endif; ?>

        
        <?php if(isset($header)): ?>
            <header class="bg-white border-bottom py-3">
                <div class="container">
                    <h2 class="h4 fw-semibold mb-0"><?php echo e($header); ?></h2>
                </div>
            </header>
        <?php endif; ?>

        
        <main class="py-4 flex-grow-1">
            <div class="container">
                <?php if (isset($component)) { $__componentOriginalb73145fe1614d50b3151d575d31121ae = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb73145fe1614d50b3151d575d31121ae = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.error-handler','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('error-handler'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb73145fe1614d50b3151d575d31121ae)): ?>
<?php $attributes = $__attributesOriginalb73145fe1614d50b3151d575d31121ae; ?>
<?php unset($__attributesOriginalb73145fe1614d50b3151d575d31121ae); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb73145fe1614d50b3151d575d31121ae)): ?>
<?php $component = $__componentOriginalb73145fe1614d50b3151d575d31121ae; ?>
<?php unset($__componentOriginalb73145fe1614d50b3151d575d31121ae); ?>
<?php endif; ?>
            </div>
            <?php echo $__env->yieldContent('content'); ?>
        </main>

        
        <?php if (isset($component)) { $__componentOriginalf98a32c06d8462f5513d0fb3554f9141 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf98a32c06d8462f5513d0fb3554f9141 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.toast-notification','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('toast-notification'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf98a32c06d8462f5513d0fb3554f9141)): ?>
<?php $attributes = $__attributesOriginalf98a32c06d8462f5513d0fb3554f9141; ?>
<?php unset($__attributesOriginalf98a32c06d8462f5513d0fb3554f9141); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf98a32c06d8462f5513d0fb3554f9141)): ?>
<?php $component = $__componentOriginalf98a32c06d8462f5513d0fb3554f9141; ?>
<?php unset($__componentOriginalf98a32c06d8462f5513d0fb3554f9141); ?>
<?php endif; ?>
    </div>

    <?php if(!request()->routeIs('admin.*')): ?>
        <?php echo $__env->make('layouts.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH Y:\Speeda - Versions\Speeda\resources\views/layouts/app.blade.php ENDPATH**/ ?>