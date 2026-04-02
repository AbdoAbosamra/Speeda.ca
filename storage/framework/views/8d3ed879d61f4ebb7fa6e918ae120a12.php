
<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>"
    x-data="{ saved: <?php echo json_encode(auth()->check() && auth()->user()->savedProviders->contains($serviceProvider->id), 15, 512) ?> }">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e($serviceProvider->company_name ?? $serviceProvider->user->name); ?> - Speeda</title>
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/main-logo.png')); ?>">

    <!-- Preconnect to CDNs for faster loading -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">

    <!-- Google Fonts -->
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Bootstrap 5 + Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- Custom CSS -->
    
    <?php echo $__env->make('partials.meta-pixel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #f72585;
            --light-bg: #f8f9fa;
            --dark-text: #212529;
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --card-hover-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        body {
            font-family: 'Figtree', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--dark-text);
            line-height: 1.6;
            background-color: #f5f7ff;
            overflow-x: hidden;
        }

        /* Animated Background */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(135deg, #f5f7ff 0%, #e9ecef 100%);
            overflow: hidden;
        }

        .animated-bg::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(67, 97, 238, 0.1) 0%, rgba(247, 37, 133, 0.05) 100%);
            animation: rotate 30s linear infinite;
        }

        @keyframes rotate {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Navbar Styles */
        .navbar {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.9) !important;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
        }

        .navbar-brand img {
            width: 120px;
            height: auto;
            transition: var(--transition);
        }

        .navbar-brand img:hover {
            transform: scale(1.05);
        }

        .nav-link {
            font-weight: 500;
            transition: var(--transition);
            position: relative;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: var(--primary-color);
        }

        /* Button Styles */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 50px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
        }

        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .btn-save {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
            z-index: 10;
        }

        .btn-save:hover {
            transform: scale(1.1);
        }

        .btn-save.saved {
            color: var(--accent-color);
        }

        /* Card Styles */
        .profile-card {
            background: white;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: var(--transition);
            position: relative;
        }

        .profile-header {
            height: 200px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            position: relative;
            overflow: hidden;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('<?php echo e(asset('images/pattern.svg')); ?>') repeat;
            opacity: 0.1;
        }

        .profile-image-container {
            position: absolute;
            bottom: -60px;
            left: 30px;
            top: 110px;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: white;
            padding: 5px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            z-index: 2;
        }

        .profile-image {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .profile-content {
            padding: 80px 30px 30px;
        }

        .rating-display {
            background: rgba(255, 193, 7, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            border: 1px solid rgba(255, 193, 7, 0.5);
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .verification-badge {
            background: rgba(76, 175, 80, 0.2);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        /* Contact Card Styles */
        .contact-card {
            background: white;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: var(--transition);
        }

        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-hover-shadow);
        }

        .contact-item {
            padding: 1.5rem;
            border-bottom: 1px solid #f0f0f0;
            transition: var(--transition);
        }

        .contact-item:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .contact-item:last-child {
            border-bottom: none;
        }

        .contact-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.2rem;
            color: white;
        }

        .phone-icon {
            background: linear-gradient(135deg, #4361ee, #3f37c9);
        }

        .email-icon {
            background: linear-gradient(135deg, #f72585, #b5179e);
        }

        .location-icon {
            background: linear-gradient(135deg, #7209b7, #560bad);
        }

        .hours-icon {
            background: linear-gradient(135deg, #3a0ca3, #3f37c9);
        }

        .website-icon {
            background: linear-gradient(135deg, #4cc9f0, #4361ee);
        }

        /* Premium WhatsApp Country Badge */
        .whatsapp-country-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #dc2626, #b91c1c, #991b1b);
            padding: 12px 20px;
            border-radius: 12px;
            box-shadow:
                0 4px 6px rgba(220, 38, 38, 0.2),
                0 8px 16px rgba(220, 38, 38, 0.15),
                inset 0 1px 2px rgba(255, 255, 255, 0.2),
                inset 0 -1px 2px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.15);
            position: relative;
            overflow: hidden;
            height: 46px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: default;
            width: 100%;
            justify-content: center;
        }

        .whatsapp-country-badge::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg,
                    transparent 30%,
                    rgba(255, 255, 255, 0.15) 50%,
                    transparent 70%);
            animation: badgeShine 3s ease-in-out infinite;
        }

        @keyframes badgeShine {

            0%,
            100% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }

            50% {
                transform: translateX(100%) translateY(100%) rotate(45deg);
            }
        }

        .whatsapp-country-badge:hover {
            transform: translateY(-2px);
            box-shadow:
                0 6px 12px rgba(220, 38, 38, 0.3),
                0 12px 24px rgba(220, 38, 38, 0.2),
                inset 0 1px 2px rgba(255, 255, 255, 0.25),
                inset 0 -1px 2px rgba(0, 0, 0, 0.25);
        }

        .whatsapp-country-badge .flag-emoji {
            font-size: 1.5rem;
            line-height: 1;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
            animation: flagWave 2.5s ease-in-out infinite;
            display: inline-block;
        }

        @keyframes flagWave {

            0%,
            100% {
                transform: rotate(-5deg);
            }

            50% {
                transform: rotate(5deg);
            }
        }

        .whatsapp-country-badge .country-code {
            font-weight: 700;
            font-size: 1rem;
            color: white;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            letter-spacing: 0.5px;
        }

        .whatsapp-country-badge .country-name {
            background: rgba(255, 255, 255, 0.25);
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.75rem;
            color: white;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
            letter-spacing: 1px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow:
                0 2px 4px rgba(0, 0, 0, 0.1),
                inset 0 1px 1px rgba(255, 255, 255, 0.2);
        }

        /* Responsive Design for Badge */
        @media (max-width: 768px) {
            .whatsapp-country-badge {
                padding: 10px 16px;
                height: 42px;
                gap: 6px;
            }

            .whatsapp-country-badge .flag-emoji {
                font-size: 1.3rem;
            }

            .whatsapp-country-badge .country-code {
                font-size: 0.9rem;
            }

            .whatsapp-country-badge .country-name {
                font-size: 0.7rem;
                padding: 3px 8px;
            }
        }

        /* Enhanced Form Styles */
        .form-control-lg,
        .form-select-lg {
            font-size: 1.05rem;
            font-weight: 500;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background-color: #ffffff;
        }

        .form-control-lg:focus,
        .form-select-lg:focus {
            border-color: #4361ee;
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
            transform: translateY(-1px);
            background-color: #ffffff;
        }

        .form-control-lg:hover,
        .form-select-lg:hover {
            border-color: #cbd5e1;
        }

        textarea.form-control-lg {
            font-family: 'Figtree', sans-serif;
            line-height: 1.6;
        }

        /* Input Group Styling */
        .input-group {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .input-group:focus-within {
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.15);
            transform: translateY(-1px);
        }

        .input-group-text {
            border: 2px solid #e2e8f0;
            padding: 12px 14px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .input-group:focus-within .input-group-text {
            border-color: #4361ee;
            background-color: rgba(67, 97, 238, 0.05) !important;
        }

        .input-group .form-control-lg {
            box-shadow: none;
        }

        /* Card Header Enhancements */
        .card-header {
            font-weight: 600;
            border-bottom: 3px solid rgba(255, 255, 255, 0.2);
            letter-spacing: 0.5px;
            padding: 1rem 1.5rem;
        }

        .card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        /* Labels Enhancement */
        .form-label {
            font-size: 0.95rem;
            margin-bottom: 0.75rem;
            color: #334155;
            display: flex;
            align-items: center;
        }

        /* Small Text Improvements */
        small.text-muted {
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            margin-top: 0.5rem;
        }

        /* Badge in Input Group */
        .input-group-text .badge {
            font-size: 0.75rem;
            padding: 4px 10px;
            font-weight: 600;
        }

        /* Service Badge Styles */
        .service-badge {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin: 0.25rem;
            transition: var(--transition);
        }

        .service-badge:hover {
            background: rgba(67, 97, 238, 0.2);
            transform: translateY(-2px);

        }

        /* Gallery Styles */
        .gallery-container {
            margin-top: 2rem;
        }

        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            height: 200px;
            cursor: pointer;
            transition: var(--transition);
        }

        .gallery-item:hover {
            transform: scale(1.03);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .gallery-item:hover img {
            transform: scale(1.1);
        }

        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: var(--transition);
        }

        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }

        /* Reviews Section */
        .review-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }

        .review-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .review-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .review-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 1rem;
            object-fit: cover;
        }

        .review-rating {
            color: #ffc107;
        }

        /* Similar Providers */
        .similar-provider-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
            height: 100%;
        }

        .similar-provider-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .similar-provider-image {
            height: 150px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            position: relative;
            overflow: hidden;
        }

        .similar-provider-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .similar-provider-content {
            padding: 1.5rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .profile-header {
                height: 150px;
            }

            .profile-image-container {
                width: 100px;
                height: 100px;
                bottom: -50px;
                left: 20px;
            }

            .profile-content {
                padding: 70px 20px 20px;
            }

            .action-buttons .btn {
                display: block;
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Toast Notification */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }

        .custom-toast {
            background: white;
            border-radius: 10px;
            padding: 1rem 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            min-width: 300px;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .toast-success {
            border-left: 4px solid #4CAF50;
        }

        .toast-error {
            border-left: 4px solid #f44336;
        }

        .toast-icon {
            margin-right: 1rem;
            font-size: 1.5rem;
        }

        .toast-success .toast-icon {
            color: #4CAF50;
        }

        .toast-error .toast-icon {
            color: #f44336;
        }

        /* ===== RTL Support ===== */
        [dir="rtl"] .toast-container {
            right: auto;
            left: 20px;
        }

        [dir="rtl"] .toast-icon {
            margin-right: 0;
            margin-left: 1rem;
        }

        [dir="rtl"] .toast-success {
            border-left: none;
            border-right: 4px solid #4CAF50;
        }

        [dir="rtl"] .toast-error {
            border-left: none;
            border-right: 4px solid #f44336;
        }

        [dir="rtl"] .nav-link.active::after {
            left: auto;
            right: 0;
        }

        [dir="rtl"] .action-buttons {
            flex-direction: row-reverse;
        }

        [dir="rtl"] .btn {
            flex-direction: row-reverse;
        }

        [dir="rtl"] .me-1,
        [dir="rtl"] .me-2,
        [dir="rtl"] .me-3 {
            margin-right: 0 !important;
            margin-left: 0.25rem !important;
        }

        [dir="rtl"] .ms-1,
        [dir="rtl"] .ms-2,
        [dir="rtl"] .ms-3 {
            margin-left: 0 !important;
            margin-right: 0.25rem !important;
        }

        [dir="rtl"] .text-start {
            text-align: right !important;
        }

        [dir="rtl"] .text-end {
            text-align: left !important;
        }
    </style>
</head>

<body>
    
    <?php if(config('facebook.enabled') && !request()->routeIs('admin.*')): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof fbq === 'function') {
                    var spViewEventId = 'vc_<?php echo e($serviceProvider->id); ?>_' + Date.now();
                    fbq('track', 'ViewContent', {
                        content_name: <?php echo json_encode($serviceProvider->company_name ?? $serviceProvider->user->name); ?>,
                        content_ids: ['<?php echo $serviceProvider->id; ?>'],
                        content_category: <?php echo json_encode($serviceProvider->category->translated_name ?? 'Uncategorized'); ?>,
                        content_type: 'service_provider',
                        language: '<?php echo e(app()->getLocale()); ?>'
                    }, { eventID: spViewEventId });
                    // Store event_id for CAPI deduplication
                    window.__spViewEventId = spViewEventId;
                }
            });
        </script>
    <?php endif; ?>

    <!-- Animated Background -->
    <div class="animated-bg"></div>

    <!-- Toast Container -->
    <div class="toast-container" x-data="{ showToast: false, message: '', type: 'success' }" x-show="showToast"
        x-transition>
        <div class="custom-toast" :class="`toast-${type}`">
            <div class="toast-icon">
                <i class="fas" :class="type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'"></i>
            </div>
            <div class="toast-message" x-text="message"></div>
        </div>
    </div>

    <?php echo $__env->make('components.main-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <?php echo $__env->make('components.notification-card', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="container mt-4">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>"><i
                            class="fas fa-home me-1"></i><?php echo e(__('general.home')); ?></a></li>
                <li class="breadcrumb-item"><a href="<?php echo e(route('service-providers.index')); ?>"><i
                            class="fas fa-list me-1"></i><?php echo e(__('service_provider.providers_label')); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php echo e($serviceProvider->company_name ?? $serviceProvider->user->name); ?>

                </li>
            </ol>
        </nav>

        <!-- Flash Messages -->
        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        
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

        <div class="row">
            <!-- Main Provider Information -->
            <div class="col-lg-8">
                <div class="profile-card">
                    <!-- Profile Header -->
                    <div class="profile-header"></div>

                    <!-- Profile Image -->
                    <div class="profile-image-container" <?php if(auth()->check() && auth()->id() === $serviceProvider->user_id): ?> id="profileImageClickable" style="cursor: pointer;" title="<?php echo e(__('service_provider.click_to_change_image')); ?>" <?php endif; ?>>
                        <?php if($serviceProvider->profile_image): ?>
                            <img src="<?php echo e(Storage::url($serviceProvider->profile_image)); ?>"
                                alt="<?php echo e($serviceProvider->company_name ?? $serviceProvider->user->name); ?>"
                                class="profile-image" loading="lazy" id="profileImagePreview">
                        <?php else: ?>
                            <div class="profile-image d-flex align-items-center justify-content-center" id="profileImagePreview"
                                style="background: linear-gradient(135deg, var(--primary-color), var(--accent-color));">
                                <i class="fas fa-user fa-4x text-white"></i>
                            </div>
                        <?php endif; ?>
                        
                        <?php if(auth()->check() && auth()->id() === $serviceProvider->user_id): ?>
                            <div id="imageOverlay" style="position:absolute;top:0;left:0;width:100%;height:100%;border-radius:50%;background:rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity 0.3s;cursor:pointer;">
                                <i class="fas fa-camera fa-2x text-white"></i>
                            </div>
                            <input type="file" id="profileImageInput" accept="image/jpeg,image/png,image/webp" style="display:none;">
                            
                            <div id="imageUploadSpinner" style="display:none;position:absolute;top:0;left:0;width:100%;height:100%;border-radius:50%;background:rgba(255,255,255,0.8);align-items:center;justify-content:center;">
                                <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Profile Content -->
                    <div class="profile-content">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h1 class="fw-bold mb-2">
                                    <?php echo e($serviceProvider->company_name ?? $serviceProvider->user->name); ?>

                                </h1>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-briefcase me-1"></i>
                                    <?php echo e($serviceProvider->category->translated_name ?? __('service_provider.uncategorized')); ?>

                                </p>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                
                                <?php if(!auth()->check() || auth()->id() !== $serviceProvider->user_id): ?>
                                    <?php if (isset($component)) { $__componentOriginal0687486ebb43e4ac695125e22d5c874c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0687486ebb43e4ac695125e22d5c874c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.endorsement-button','data' => ['serviceProvider' => $serviceProvider]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('endorsement-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['service-provider' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($serviceProvider)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0687486ebb43e4ac695125e22d5c874c)): ?>
<?php $attributes = $__attributesOriginal0687486ebb43e4ac695125e22d5c874c; ?>
<?php unset($__attributesOriginal0687486ebb43e4ac695125e22d5c874c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0687486ebb43e4ac695125e22d5c874c)): ?>
<?php $component = $__componentOriginal0687486ebb43e4ac695125e22d5c874c; ?>
<?php unset($__componentOriginal0687486ebb43e4ac695125e22d5c874c); ?>
<?php endif; ?>
                                <?php endif; ?>

                                <!-- Certified Badge (only visible to owner) -->
                                <?php if(auth()->check() && auth()->id() === $serviceProvider->user_id && $serviceProvider->certification): ?>
                                    <div class="verification-badge"
                                        style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                        <i class="fas fa-certificate"></i>
                                        <span><?php echo e(__('service_provider.certified')); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if(auth()->id() === $serviceProvider->user_id): ?>
                            <!-- Owner-only Edit Section -->

                            
                            <?php if (isset($component)) { $__componentOriginal037f8e4db3e2dbc9091d29eab3779257 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal037f8e4db3e2dbc9091d29eab3779257 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.profile-completion-popup','data' => ['serviceProvider' => $serviceProvider]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('profile-completion-popup'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['serviceProvider' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($serviceProvider)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal037f8e4db3e2dbc9091d29eab3779257)): ?>
<?php $attributes = $__attributesOriginal037f8e4db3e2dbc9091d29eab3779257; ?>
<?php unset($__attributesOriginal037f8e4db3e2dbc9091d29eab3779257); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal037f8e4db3e2dbc9091d29eab3779257)): ?>
<?php $component = $__componentOriginal037f8e4db3e2dbc9091d29eab3779257; ?>
<?php unset($__componentOriginal037f8e4db3e2dbc9091d29eab3779257); ?>
<?php endif; ?>

                            
                            <?php $pct = $serviceProvider->profile_completion_percent ?? 0; ?>
                            <div class="mb-4 p-3 rounded-4 shadow-sm" style="background: white;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold"><i class="fas fa-tasks text-primary me-2"></i><?php echo e(__('service_provider.profile_completion_title')); ?></span>
                                    <span class="badge rounded-pill <?php if($pct >= 80): ?> bg-success <?php elseif($pct >= 50): ?> bg-warning text-dark <?php else: ?> bg-danger <?php endif; ?> px-3"><?php echo e($pct); ?>%</span>
                                </div>
                                <div class="progress" style="height: 10px; border-radius: 5px;" id="completionProgressBar">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo e($pct); ?>%; background: linear-gradient(90deg, <?php if($pct >= 80): ?> #10b981,#059669 <?php elseif($pct >= 50): ?> #f59e0b,#d97706 <?php else: ?> #ef4444,#dc2626 <?php endif; ?>); border-radius: 5px; transition: width 0.6s ease;" aria-valuenow="<?php echo e($pct); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>



                            <div class="mb-4">
                                <h4 class="fw-bold text-secondary mb-3"><i
                                        class="fas fa-edit me-2"></i><?php echo e(__('service_provider.edit_profile')); ?></h4>

                                <form action="<?php echo e(route('service-providers.profile.update', $serviceProvider->id)); ?>"
                                    method="POST" enctype="multipart/form-data" id="profileUpdateForm">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>

                                    
                                    <div class="card mb-3 border-0 shadow-sm">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0"><i
                                                    class="fas fa-info-circle me-2"></i><?php echo e(__('service_provider.basic_information')); ?>

                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    <i class="fas fa-building text-primary me-1"></i>
                                                    <?php echo e(__('service_provider.company_activity_name')); ?> <span
                                                        class="text-danger">*</span>
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="fas fa-briefcase text-muted"></i>
                                                    </span>
                                                    <input type="text" name="business_name"
                                                        class="form-control form-control-lg border-start-0"
                                                        value="<?php echo e(old('business_name', $serviceProvider->company_name)); ?>"
                                                        placeholder="مثال: ورشة السلام للسباكة" required>
                                                </div>
                                                <?php $__errorArgs = ['business_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <small class="text-danger"><i
                                                            class="fas fa-exclamation-circle me-1"></i><?php echo e($message); ?></small>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    <i class="fas fa-align-left text-primary me-1"></i>
                                                    <?php echo e(__('general.description')); ?>

                                                </label>
                                                <textarea name="bio" class="form-control form-control-lg" rows="5"
                                                    placeholder="<?php echo e(__('service_provider.description_hint')); ?>"
                                                    style="resize: vertical; min-height: 120px;"><?php echo e(old('bio', $serviceProvider->bio)); ?></textarea>
                                                <small class="text-muted">
                                                    <i class="fas fa-lightbulb text-warning me-1"></i>
                                                    <?php echo e(__('service_provider.description_helper')); ?>

                                                </small>
                                                <?php $__errorArgs = ['bio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <small class="text-danger d-block mt-1"><i
                                                            class="fas fa-exclamation-circle me-1"></i><?php echo e($message); ?></small>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    <i class="fas fa-award text-primary me-1"></i>
                                                    <?php echo e(__('service_provider.experience_years_label')); ?>

                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="fas fa-calendar-check text-muted"></i>
                                                    </span>
                                                    <input type="number" name="experience_years" id="experienceYearsInput"
                                                        class="form-control form-control-lg border-start-0"
                                                        value="<?php echo e(old('experience_years', $serviceProvider->experience_years)); ?>"
                                                        min="0" max="50" placeholder="<?php echo e(__('general.example')); ?>: 5">
                                                    <span class="input-group-text bg-light">
                                                        <span
                                                            class="badge bg-primary"><?php echo e(__('service_provider.years')); ?></span>
                                                    </span>
                                                </div>
                                                <?php $__errorArgs = ['experience_years'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <small class="text-danger"><i
                                                            class="fas fa-exclamation-circle me-1"></i><?php echo e($message); ?></small>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                    </div>

                                    
                                    <div class="card mb-3 border-0 shadow-sm">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0"><i
                                                    class="fas fa-phone me-2"></i><?php echo e(__('service_provider.contact_info')); ?>

                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label
                                                    class="form-label fw-bold"><?php echo e(__('service_provider.job_specialization')); ?></label>

                                                <?php
                                                    $othersNames = ['other', 'others', 'أخرى'];
                                                    $isOthersCategory = $serviceProvider->category && (
                                                        in_array(strtolower(trim($serviceProvider->category->name)), $othersNames) ||
                                                        in_array(strtolower(trim($serviceProvider->category->translated_name)), $othersNames)
                                                    );
                                                ?>

                                                <?php if($isOthersCategory): ?>
                                                    
                                                    <select name="category_id" class="form-control form-control-lg" required>
                                                        <option value="">-- <?php echo e(__('service_provider.select_category')); ?> --
                                                        </option>
                                                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <option value="<?php echo e($cat->id); ?>" <?php echo e(old('category_id', $serviceProvider->category_id) == $cat->id ? 'selected' : ''); ?>>
                                                                <?php echo e($cat->translated_name ?? $cat->name); ?>

                                                            </option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                    <small class="text-info d-block mt-2">
                                                        <i class="fas fa-check-circle me-1"></i>
                                                        <?php echo e(__('service_provider.you_can_change_category')); ?>

                                                    </small>
                                                <?php else: ?>
                                                    
                                                    <input type="text" class="form-control form-control-lg bg-light"
                                                        value="<?php echo e($serviceProvider->category->translated_name ?? __('service_provider.not_specified')); ?>"
                                                        disabled readonly>
                                                    <small class="text-warning d-block mt-2">
                                                        <i class="fas fa-lock me-1"></i>
                                                        <?php echo e(__('service_provider.category_locked_message')); ?>

                                                    </small>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    <i class="fas fa-phone-alt text-primary me-1"></i>
                                                    <?php echo e(__('general.phone')); ?> <span class="text-danger">*</span>
                                                </label>
                                                <div class="row g-2">
                                                    <div class="col-md-4">
                                                        <div class="whatsapp-country-badge">
                                                            <span class="flag-emoji">🍁</span>
                                                            <span class="country-code">+1</span>
                                                            <span class="country-name">CA</span>
                                                        </div>
                                                        <input type="hidden" name="phone_country_code" value="+1">
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="phone" class="form-control form-control-lg"
                                                            value="<?php echo e(old('phone', preg_replace('/^\+1/', '', $serviceProvider->phone))); ?>"
                                                            placeholder="6135204877" pattern="[0-9]{10,15}" minlength="10"
                                                            maxlength="15" required>
                                                        <small class="text-muted d-block mt-1">
                                                            <i
                                                                class="fas fa-info-circle me-1"></i><?php echo e(__('service_provider.enter_10_digit_number')); ?>

                                                        </small>
                                                    </div>
                                                </div>
                                                <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <small class="text-danger d-block mt-1"><i
                                                            class="fas fa-exclamation-circle me-1"></i><?php echo e($message); ?></small>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>

                                            <div class="mb-3">
                                                <label
                                                    class="form-label fw-bold"><?php echo e(__('service_provider.whatsapp_number')); ?>

                                                    <span class="text-danger">*</span></label>
                                                <div class="row g-2">
                                                    <div class="col-md-4">
                                                        <div class="whatsapp-country-badge">
                                                            <span class="flag-emoji">🍁</span>
                                                            <span class="country-code">+1</span>
                                                            <span class="country-name">CA</span>
                                                        </div>
                                                        <input type="hidden" name="whatsapp_country_code" value="+1">
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="whatsapp_number"
                                                            class="form-control form-control-lg"
                                                            value="<?php echo e(old('whatsapp_number', preg_replace('/^\+1/', '', $serviceProvider->whatsapp_number))); ?>"
                                                            placeholder="6135204877" pattern="[0-9]{10,15}" minlength="10"
                                                            maxlength="15" required>
                                                        <small class="text-muted d-block mt-1">
                                                            <i
                                                                class="fas fa-info-circle me-1"></i><?php echo e(__('service_provider.enter_10_digit_number')); ?>

                                                        </small>
                                                    </div>
                                                </div>
                                                <?php $__errorArgs = ['whatsapp_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <small class="text-danger d-block mt-1"><?php echo e($message); ?></small>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                    </div>

                                    
                                    <div class="card mb-3 border-0 shadow-sm">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0"><i
                                                    class="fas fa-map-marker-alt me-2"></i><?php echo e(__('service_provider.location_section')); ?>

                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    <i class="fas fa-map-marked-alt text-primary me-1"></i>
                                                    <?php echo e(__('general.location')); ?>

                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="fas fa-city text-info"></i>
                                                    </span>
                                                    <select name="location_id"
                                                        class="form-select form-select-lg border-start-0">
                                                        <option value=""><?php echo e(__('general.select_location_placeholder')); ?>

                                                        </option>
                                                        <?php $__currentLoopData = $locations ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <option value="<?php echo e($loc->id); ?>" <?php echo e($serviceProvider->location_id == $loc->id ? 'selected' : ''); ?>>
                                                                <?php echo e($loc->city ?? $loc->name ?? __('general.location') . ' ' . $loc->id); ?>

                                                            </option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                </div>
                                                <?php $__errorArgs = ['location_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <small class="text-danger"><i
                                                            class="fas fa-exclamation-circle me-1"></i><?php echo e($message); ?></small>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    <i class="fas fa-map-pin text-primary me-1"></i>
                                                    <?php echo e(__('general.address')); ?>

                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="fas fa-location-dot text-danger"></i>
                                                    </span>
                                                    <input type="text" name="address" id="addressInput"
                                                        class="form-control form-control-lg border-start-0"
                                                        value="<?php echo e(old('address', $serviceProvider->address)); ?>"
                                                        placeholder="<?php echo e(__('general.example')); ?>: <?php echo e(__('general.address_placeholder')); ?>">
                                                </div>
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle text-info me-1"></i>
                                                    <?php echo e(__('service_provider.address_english_only_hint')); ?>

                                                </small>
                                                <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <small class="text-danger d-block mt-1"><i
                                                            class="fas fa-exclamation-circle me-1"></i><?php echo e($message); ?></small>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                    </div>

                                    
                                    <div class="card mb-3 border-0 shadow-sm">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0"><i
                                                    class="fas fa-briefcase me-2"></i><?php echo e(__('service_provider.services_files')); ?>

                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    <i class="fas fa-list-check text-primary me-1"></i>
                                                    <?php echo e(__('service_provider.services_provided')); ?>

                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="fas fa-tools text-warning"></i>
                                                    </span>
                                                    <input type="text" name="services_offered"
                                                        class="form-control form-control-lg border-start-0"
                                                        value="<?php echo e(old('services_offered', is_array($serviceProvider->services_offered) ? implode(', ', $serviceProvider->services_offered) : $serviceProvider->services_offered)); ?>"
                                                        placeholder="<?php echo e(__('general.example')); ?>: <?php echo e(__('service_provider.services_offered_input_hint')); ?>">
                                                </div>
                                                <small class="text-muted">
                                                    <i class="fas fa-lightbulb text-warning me-1"></i>
                                                    <?php echo e(__('service_provider.separate_services_comma')); ?>

                                                </small>
                                                <?php $__errorArgs = ['services_offered'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <small class="text-danger d-block mt-1"><i
                                                            class="fas fa-exclamation-circle me-1"></i><?php echo e($message); ?></small>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold"><?php echo e(__('general.profile_image')); ?></label>
                                                <input type="file" name="profile_image" id="profileImageInput"
                                                    class="form-control" accept="image/jpeg,image/jpg,image/png,image/webp"
                                                    onchange="validateFileSize(this, 2)">
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    <?php echo e(__('service_provider.profile_logo_image')); ?> -
                                                    <?php echo e(__('service_provider.max_size_2mb')); ?>

                                                </small>
                                                <?php if($serviceProvider->profile_image): ?>
                                                    <div class="mt-2">
                                                        <img src="<?php echo e($serviceProvider->profile_image_url); ?>" class="rounded"
                                                            style="width: 80px; height: 80px; object-fit: cover;">
                                                        <small
                                                            class="text-muted d-block"><?php echo e(__('service_provider.current_image')); ?></small>
                                                    </div>
                                                <?php endif; ?>
                                                <?php $__errorArgs = ['profile_image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <small class="text-danger d-block"><?php echo e($message); ?></small>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>

                                            <?php
                                                $currentGallery = $serviceProvider->getMedia('provider_gallery')->take(4);
                                            ?>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    <i class="fas fa-images text-primary me-1"></i>
                                                    <?php echo e(__('service_provider.gallery_upload_title')); ?>

                                                </label>

                                                <input type="file"
                                                    name="gallery_images[]"
                                                    id="galleryImagesInput"
                                                    class="form-control"
                                                    accept="image/jpeg,image/png,image/webp"
                                                    multiple
                                                    onchange="validateMultipleFilesSize(this, 10, 4)">

                                                <small class="text-muted d-block">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    <?php echo e(__('service_provider.gallery_upload_hint')); ?>

                                                </small>

                                                <?php if($currentGallery->count() > 0): ?>
                                                    <div class="mt-2">
                                                        <div class="row g-2">
                                                            <?php $__currentLoopData = $currentGallery; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <div class="col-6 col-md-3">
                                                                    <img src="<?php echo e($media->hasGeneratedConversion('gallery_thumb') ? $media->getUrl('gallery_thumb') : $media->getUrl()); ?>"
                                                                        alt="<?php echo e(__('service_provider.gallery_image_alt')); ?>"
                                                                        style="width:100%; aspect-ratio:1/1; object-fit:cover; border-radius:12px;"
                                                                        loading="lazy">
                                                                </div>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <?php $__errorArgs = ['gallery_images'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <small class="text-danger d-block"><?php echo e($message); ?></small>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>

                                            <div class="mb-3">
                                                <label
                                                    class="form-label fw-bold"><?php echo e(__('service_provider.certification')); ?></label>
                                                <input type="file" name="certification" id="certificationInput"
                                                    class="form-control"
                                                    accept="image/jpeg,image/jpg,image/png,image/webp,application/pdf"
                                                    onchange="validateFileSize(this, 2)">
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    <?php echo e(__('service_provider.certificate_or_license')); ?> -
                                                    <?php echo e(__('service_provider.max_size_2mb')); ?>

                                                </small>
                                                <?php if($serviceProvider->certification): ?>
                                                    <div class="mt-2">
                                                        <span class="badge bg-success"><i class="fas fa-check-circle"></i>
                                                            <?php echo e(__('service_provider.certificate_uploaded')); ?></span>
                                                        <a href="<?php echo e(asset('storage/' . $serviceProvider->certification)); ?>"
                                                            target="_blank" class="badge bg-primary"><i class="fas fa-eye"></i>
                                                            <?php echo e(__('service_provider.view')); ?></a>
                                                    </div>
                                                <?php endif; ?>
                                                <?php $__errorArgs = ['certification'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <small class="text-danger d-block"><?php echo e($message); ?></small>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                    </div>

                                    
                                    <div class="card border-0 shadow-sm bg-light">
                                        <div class="card-body">
                                            <div class="d-flex gap-3 justify-content-between align-items-center flex-wrap">
                                                <div>
                                                    <h6 class="mb-1 fw-bold text-dark">
                                                        <i class="fas fa-check-circle text-success me-2"></i>
                                                        <?php echo e(__('service_provider.ready_to_save')); ?>

                                                    </h6>
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        <?php echo e(__('service_provider.verify_info_before_save')); ?>

                                                    </small>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <a href="<?php echo e(route('service-providers.show', $serviceProvider->id)); ?>"
                                                        class="btn btn-outline-secondary btn-lg px-4"
                                                        style="border-radius: 12px;">
                                                        <i class="fas fa-times-circle me-2"></i><?php echo e(__('general.cancel')); ?>

                                                    </a>
                                                    <button type="submit" class="btn btn-primary btn-lg px-5"
                                                        style="border-radius: 12px; box-shadow: 0 4px 15px rgba(67, 97, 238, 0.4);">
                                                        <i class="fas fa-save me-2"></i><?php echo e(__('general.save_changes')); ?>

                                                        <i class="fas fa-arrow-left ms-2"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>

                        <!-- Business Description -->
                        <div class="mb-4">
                            <h4 class="fw-bold text-primary mb-3">
                                <i class="fas fa-info-circle me-2"></i><?php echo e(__('service_provider.about_us')); ?>

                            </h4>
                            <p class="fs-6"><?php echo e($serviceProvider->bio ?? __('service_provider.no_description')); ?></p>
                        </div>

                        <!-- Services Offered -->
                        <div class="mb-4">
                            <h4 class="fw-bold text-primary mb-3">
                                <i
                                    class="fas fa-list-check me-2"></i><?php echo e(__('service_provider.services_offered_title')); ?>

                            </h4>
                            <?php
                                $services = $serviceProvider->services_offered;
                                if (is_string($services)) {
                                    $services = json_decode($services, true) ?? explode(',', $services);
                                }
                                $services = is_array($services) ? array_filter(array_map('trim', $services)) : [];
                            ?>
                            <?php if(!empty($services)): ?>
                                <div class="d-flex flex-wrap">
                                    <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="service-badge">
                                            <i class="fas fa-check-circle"></i><?php echo e($service); ?>

                                        </span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted"><?php echo e(__('service_provider.no_services_listed')); ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Gallery Section -->
                        <?php
                            $providerGallery = $serviceProvider->getMedia('provider_gallery');
                        ?>
                        <?php if($providerGallery->count() > 0): ?>
                            <div class="gallery-container">
                                <h4 class="fw-bold text-primary mb-3">
                                    <i class="fas fa-images me-2"></i><?php echo e(__('service_provider.gallery_title')); ?>

                                </h4>

                                <div class="row g-3">
                                    <?php $__currentLoopData = $providerGallery->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="col-6 col-md-3">
                                            <div class="gallery-item" data-bs-toggle="modal" data-bs-target="#imageModal"
                                                data-image="<?php echo e($media->hasGeneratedConversion('gallery_large') ? $media->getUrl('gallery_large') : $media->getUrl()); ?>">
                                                <img src="<?php echo e($media->hasGeneratedConversion('gallery_thumb') ? $media->getUrl('gallery_thumb') : $media->getUrl()); ?>"
                                                    alt="<?php echo e(__('service_provider.gallery_image_alt')); ?>"
                                                    loading="lazy"
                                                    decoding="async"
                                                    width="600"
                                                    height="600"
                                                    style="width:100%; aspect-ratio:1/1; object-fit:cover; border-radius:12px;"
                                                    onerror="this.onerror=null;this.src='/images/placeholder-image.png';">
                                                <div class="gallery-overlay">
                                                    <i class="fas fa-search-plus text-white fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Reviews Section -->
                        <div class="mt-5">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="fw-bold text-primary">
                                    <i class="fas fa-star me-2"></i><?php echo e(__('service_provider.customer_reviews_title')); ?>

                                </h4>
                                <?php if(auth()->check() && auth()->user()->isClient() && !$hasReviewed): ?>
                                    <button class="btn btn-primary" onclick="openReviewModal()">
                                        <i class="fas fa-pen me-2"></i><?php echo e(__('reviews.write_review')); ?>

                                    </button>
                                <?php endif; ?>
                            </div>

                            <?php if($reviewStats['total_count'] > 0): ?>
                                <!-- Rating Summary -->
                                <div class="card border-0 shadow-sm mb-4"
                                    style="border-radius: 16px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                    <div class="card-body p-4">
                                        <div class="row align-items-center">
                                            <div class="col-md-4 text-center">
                                                <div class="rating-big">
                                                    <span
                                                        class="display-3 fw-bold text-primary"><?php echo e(number_format($reviewStats['average_rating'], 1)); ?></span>
                                                    <div class="stars-large mt-2"
                                                        style="font-size: 1.5rem; color: #f59e0b;">
                                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                                            <?php if($i <= round($reviewStats['average_rating'])): ?>
                                                                <i class="fas fa-star"></i>
                                                            <?php else: ?>
                                                                <i class="far fa-star"></i>
                                                            <?php endif; ?>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <p class="text-muted mt-2"><?php echo e($reviewStats['total_count']); ?>

                                                        <?php echo e(__('reviews.reviews_total')); ?>

                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <?php $__currentLoopData = [5, 4, 3, 2, 1]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $star): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php
                                                        $count = $reviewStats[$star . '_star'] ?? 0;
                                                        $breakdown = $reviewStats['breakdown'][$star] ?? ['count' => 0, 'percentage' => 0];
                                                        $percentage = $breakdown['percentage'] ?? 0;
                                                    ?>
                                                    <div class="rating-bar d-flex align-items-center mb-2">
                                                        <span class="me-2" style="min-width: 20px;"><?php echo e($star); ?></span>
                                                        <i class="fas fa-star text-warning me-2"
                                                            style="font-size: 0.75rem;"></i>
                                                        <div class="progress flex-grow-1" style="height: 8px;">
                                                            <div class="progress-bar bg-warning" role="progressbar"
                                                                aria-valuenow="<?php echo e($percentage); ?>" aria-valuemin="0"
                                                                aria-valuemax="100" style="width: <?php echo e($percentage); ?>%"></div>
                                                        </div>
                                                        <span class="ms-2 text-muted"
                                                            style="min-width: 40px; font-size: 0.875rem;"><?php echo e($count); ?></span>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reviews List -->
                                <div class="reviews-container">
                                    <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="review-card"
                                            style="background: white; border-radius: 16px; padding: 1.5rem; margin-bottom: 1rem; box-shadow: 0 2px 12px rgba(0,0,0,0.06);">
                                            <div class="review-header"
                                                style="display: flex; align-items: center; margin-bottom: 1rem;">
                                                <div class="review-avatar"
                                                    style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; margin-right: 1rem;">
                                                    <?php echo e(strtoupper(substr($review->client->name ?? 'U', 0, 1))); ?>

                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 fw-bold">
                                                        <?php echo e($review->client->name ?? __('reviews.anonymous')); ?>

                                                    </h6>
                                                    <div class="review-rating" style="color: #f59e0b;">
                                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                                            <?php if($i <= $review->rating): ?>
                                                                <i class="fas fa-star"></i>
                                                            <?php else: ?>
                                                                <i class="far fa-star"></i>
                                                            <?php endif; ?>
                                                        <?php endfor; ?>
                                                        <span class="text-muted ms-2"
                                                            style="font-size: 0.875rem;"><?php echo e($review->created_at->diffForHumans()); ?></span>
                                                    </div>
                                                </div>
                                                <?php if($review->is_featured): ?>
                                                    <span class="badge bg-warning"
                                                        style="background: linear-gradient(135deg, #f59e0b, #fbbf24);">
                                                        <i class="fas fa-thumbs-up me-1"></i><?php echo e(__('reviews.featured')); ?>

                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="mb-0" style="color: #4b5563; line-height: 1.6;"><?php echo e($review->review_text); ?>

                                            </p>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>

                                <!-- Pagination -->
                                <?php if($reviews->hasPages()): ?>
                                    <div class="d-flex justify-content-center mt-4">
                                        <?php echo e($reviews->links()); ?>

                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="text-center py-5"
                                    style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 16px;">
                                    <i class="fas fa-comment-slash fa-3x text-muted mb-3"></i>
                                    <p class="text-muted"><?php echo e(__('reviews.no_reviews_yet')); ?></p>
                                    <?php if(auth()->check() && auth()->user()->isClient()): ?>
                                        <button class="btn btn-primary mt-2" onclick="openReviewModal()">
                                            <i class="fas fa-pen me-2"></i><?php echo e(__('reviews.be_first_review')); ?>

                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information Sidebar -->
            <div class="col-lg-4">
                <div class="contact-card mb-4">
                    <div class="p-4">
                        <h4 class="fw-bold text-primary mb-4 text-center">
                            <i class="fas fa-address-card me-2"></i><?php echo e(__('service_provider.contact_information')); ?>

                        </h4>

                        <!-- WhatsApp Number (Hidden until button click) -->
                        <?php
                            $whatsappDisplay = $serviceProvider->whatsapp_number ?? $serviceProvider->phone;
                            if ($isContactRevealed) {
                                // Show full number if already revealed
                                $displayWhatsapp = $whatsappDisplay;
                                $whatsappClass = 'text-success fw-bold';
                            } else {
                                // Hide last 3 digits if not revealed
                                if (strlen($whatsappDisplay) > 3) {
                                    $displayWhatsapp = substr($whatsappDisplay, 0, -3) . '***';
                                } else {
                                    $displayWhatsapp = '***';
                                }
                                $whatsappClass = 'text-muted';
                            }
                        ?>
                        <div class="contact-item">
                            <div class="d-flex align-items-center">
                                <div class="contact-icon"
                                    style="background: linear-gradient(135deg, #25D366, #128C7E);">
                                    <i class="fab fa-whatsapp"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold"><?php echo e(__('service_provider.whatsapp_number')); ?></h6>
                                    <span id="whatsappNumber" class="<?php echo e($whatsappClass); ?>"><?php echo e($displayWhatsapp); ?></span>
                                    <?php if(!$isContactRevealed): ?>
                                        <small class="d-block text-muted" style="font-size: 0.75rem;"><i
                                                class="fas fa-lock me-1"></i><?php echo e(__('service_provider.contact_reveal_hint')); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="contact-item">
                            <div class="d-flex align-items-center">
                                <div class="contact-icon location-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold"><?php echo e(__('general.location')); ?></h6>
                                    <p class="mb-0">
                                        <?php echo e($serviceProvider->location->city ?? __('service_provider.location_not_specified')); ?>

                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Address (Hidden numbers until WhatsApp button clicked) -->
                        <?php if($serviceProvider->address): ?>
                            <div class="contact-item">
                                <div class="d-flex align-items-center">
                                    <div class="contact-icon"
                                        style="background: linear-gradient(135deg, #6366f1, #4f46e5);">
                                        <i class="fas fa-map-pin"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold"><?php echo e(__('general.address')); ?></h6>
                                        <?php
                                            if ($isContactRevealed) {
                                                // Show full address if already revealed
                                                $displayAddress = $serviceProvider->address;
                                                $addressClass = 'text-primary fw-bold';
                                            } else {
                                                // Hide all numbers in address
                                                $displayAddress = preg_replace('/\d/', '*', $serviceProvider->address);
                                                $addressClass = '';
                                            }
                                        ?>
                                        <p class="mb-0 small <?php echo e($addressClass); ?>" id="addressText"><?php echo e($displayAddress); ?></p>
                                        <?php if(!$isContactRevealed): ?>
                                            <small class="text-muted" style="font-size: 0.7rem;"><i
                                                    class="fas fa-lock me-1"></i><?php echo e(__('service_provider.address_reveal_hint')); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Email -->
                        <div class="contact-item">
                            <div class="d-flex align-items-center">
                                <div class="contact-icon email-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold"><?php echo e(__('general.email_address')); ?>

                                        <a href="mailto:<?php echo e($serviceProvider->user->email); ?>"
                                            class="text-decoration-none">
                                            <?php echo e($serviceProvider->user->email); ?>

                                        </a>
                                </div>
                            </div>
                        </div>

                        <!-- Certification (Only visible to owner) -->
                        <?php if(auth()->check() && auth()->id() === $serviceProvider->user_id && $serviceProvider->is_certified && $serviceProvider->certification): ?>
                            <div class="contact-item">
                                <div class="d-flex align-items-center">
                                    <div class="contact-icon"
                                        style="background: linear-gradient(135deg, #10b981, #059669);">
                                        <i class="fas fa-certificate"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 fw-bold"><?php echo e(__('service_provider.certification')); ?></h6>
                                        <?php if(Str::endsWith($serviceProvider->certification, '.pdf')): ?>
                                            <a href="<?php echo e(asset('storage/' . $serviceProvider->certification)); ?>" target="_blank"
                                                class="text-decoration-none">
                                                <i class="fas fa-file-pdf text-danger"></i>
                                                <?php echo e(__('service_provider.view_certificate_pdf')); ?>

                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo e(asset('storage/' . $serviceProvider->certification)); ?>" target="_blank"
                                                class="text-decoration-none">
                                                <i class="fas fa-image text-primary"></i>
                                                <?php echo e(__('service_provider.view_certificate')); ?>

                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        
                        <?php if(!auth()->check() || auth()->id() !== $serviceProvider->user_id): ?>
                            <div class="contact-item">
                                <div class="d-flex align-items-center">
                                    <div class="contact-icon hours-icon">
                                        <i class="fas fa-eye"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 fw-bold"><?php echo e(__('service_provider.profile_views')); ?></h6>
                                        <p class="mb-0"><?php echo e(number_format($serviceProvider->views)); ?>

                                            <?php echo e(__('service_provider.views_label')); ?>

                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Action Buttons -->
                    <div class="p-4 bg-light">
                        <?php
                            $whatsappNumber = $serviceProvider->whatsapp_number ?? $serviceProvider->phone;
                            // Clean number - remove all non-digit and non-plus characters
                            $whatsappNumber = preg_replace('/[^0-9+]/', '', $whatsappNumber);

                            // Ensure number starts with +
                            if (!str_starts_with($whatsappNumber, '+')) {
                                // Check if it's a Canadian number (starts with 1) or Egyptian (starts with 0 or direct digits)
                                if (str_starts_with($whatsappNumber, '1') && strlen($whatsappNumber) == 11) {
                                    // Canadian number
                                    $whatsappNumber = '+' . $whatsappNumber;
                                } elseif (str_starts_with($whatsappNumber, '0')) {
                                    // Egyptian number starting with 0
                                    $whatsappNumber = '+20' . ltrim($whatsappNumber, '0');
                                } else {
                                    // Assume Egyptian if no country code
                                    $whatsappNumber = '+20' . $whatsappNumber;
                                }
                            }

                            // Clean version for API (no +)
                            $whatsappNumberClean = str_replace('+', '', $whatsappNumber);
                        ?>

                        <?php if(!empty($whatsappNumberClean)): ?>
                            
                            <button
                                onclick="revealContactInfo('<?php echo e($whatsappNumberClean); ?>', '<?php echo e($serviceProvider->whatsapp_number ?? $serviceProvider->phone); ?>', '<?php echo e($serviceProvider->address ?? ''); ?>')"
                                class="btn w-100 mb-3"
                                style="background: linear-gradient(135deg, #25D366, #128C7E); border: none; border-radius: 50px; padding: 0.75rem 2rem; font-weight: 600; color: white; box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);">
                                <i class="fab fa-whatsapp me-2"></i> <?php echo e(__('service_provider.contact_whatsapp')); ?>

                            </button>
                        <?php endif; ?>

                        <a href="mailto:<?php echo e($serviceProvider->user->email); ?>" class="btn btn-outline-primary w-100"
                            id="emailContactBtn"
                            onclick="if(typeof fbq==='function'){fbq('track','Lead',{content_name:<?php echo json_encode($serviceProvider->company_name ?? $serviceProvider->user->name); ?>,content_ids:['<?php echo $serviceProvider->id; ?>'],contact_type:'email',language:'<?php echo e(app()->getLocale()); ?>'});}">
                            <i class="fas fa-envelope me-2"></i> <?php echo e(__('service_provider.send_email')); ?>

                        </a>
                    </div>
                </div>

                <!-- Category Info Card -->
                <?php if($serviceProvider->category): ?>
                    <div class="card shadow-sm border-0 rounded-4 mt-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="fas fa-info-circle me-2"></i><?php echo e(__('service_provider.category_info_title')); ?>

                            </h6>
                            <div class="mb-2">
                                <strong><?php echo e(__('service_provider.category_label')); ?></strong>
                                <?php echo e($serviceProvider->category->translated_name); ?>

                            </div>
                            <?php if($serviceProvider->category->parent): ?>
                                <div class="mb-2">
                                    <strong><?php echo e(__('service_provider.main_category_label')); ?></strong>
                                    <?php echo e($serviceProvider->category->parent->translated_name); ?>

                                </div>
                            <?php endif; ?>
                            <?php if($serviceProvider->category->description): ?>
                                <div>
                                    <strong><?php echo e(__('general.description')); ?>:</strong>
                                    <p class="mt-1 small text-muted"><?php echo e($serviceProvider->category->translated_description); ?>

                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Similar Providers Section -->
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="fw-bold mb-4 text-primary">
                    <i class="fas fa-users me-2"></i><?php echo e(__('service_provider.similar_providers')); ?>

                </h3>

                <?php if($similarProviders->count()): ?>
                    <div class="row">
                        <?php $__currentLoopData = $similarProviders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $similar): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-md-3 mb-4">
                                <div class="similar-provider-card">
                                    <div class="similar-provider-image">
                                        <?php
                                            $similarGalleryMedia = null;
                                            if (isset($similar->media) && $similar->relationLoaded('media')) {
                                                $similarGalleryMedia = $similar->media->where('collection_name', 'provider_gallery')->first();
                                            } else {
                                                $similarGalleryMedia = $similar->getMedia('provider_gallery')->first();
                                            }

                                            $similarAvatarUrl = null;
                                            if ($similarGalleryMedia) {
                                                $similarAvatarUrl = $similarGalleryMedia->hasGeneratedConversion('gallery_thumb')
                                                    ? $similarGalleryMedia->getUrl('gallery_thumb')
                                                    : $similarGalleryMedia->getUrl();
                                            }
                                        ?>
                                        <img src="<?php echo e($similarAvatarUrl ?? $similar->profile_image_url); ?>"
                                            alt="<?php echo e($similar->company_name ?? $similar->user->name); ?>" loading="lazy" decoding="async">
                                    </div>
                                    <div class="similar-provider-content">
                                        <h6 class="fw-bold mb-2"><?php echo e($similar->company_name ?? $similar->user->name); ?></h6>
                                        <p class="text-muted small mb-3"><?php echo e(Str::limit($similar->bio ?? '', 60)); ?></p>

                                        <!-- Display category and rating -->
                                        <div class="mb-3">
                                            <span class="badge bg-primary small">
                                                <i class="fas fa-briefcase me-1"></i>
                                                <?php echo e($similar->category->translated_name ?? __('service_provider.uncategorized')); ?>

                                            </span>
                                            <?php if($similar->rating > 0): ?>
                                                <span class="badge bg-warning text-dark small">
                                                    <i class="fas fa-star"></i> <?php echo e(number_format($similar->rating, 1)); ?>

                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <a href="<?php echo e(route('service-providers.show', $similar->id)); ?>"
                                            class="btn btn-outline-primary btn-sm rounded-pill w-100"
                                            style="transition: var(--transition);"
                                            onmouseover="this.style.transform='translateY(-2px)'"
                                            onmouseout="this.style.transform='translateY(0)'">
                                            <i class="fas fa-eye me-1"></i> <?php echo e(__('service_provider.view_profile')); ?>

                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-info-circle fa-2x text-muted mb-3"></i>
                        <p class="text-muted"><?php echo e(__('service_provider.no_similar_providers')); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Back Button -->
        <div class="text-center mt-4 mb-5">
            <a href="<?php echo e(url()->previous()); ?>" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fas fa-arrow-left me-2"></i> <?php echo e(__('general.back')); ?>

            </a>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel"><?php echo e(__('service_provider.gallery_image_title')); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="<?php echo e(__('service_provider.gallery_image_alt')); ?>" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <!-- Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; border: none;">
                <div class="modal-header border-0"
                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="modal-title text-white fw-bold" id="reviewModalLabel">
                        <i class="fas fa-star me-2"></i><?php echo e(__('reviews.write_review')); ?>

                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="reviewForm" action="<?php echo e(route('reviews.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="service_provider_id" value="<?php echo e($serviceProvider->id); ?>">
                    <div class="modal-body p-4">
                        <!-- Rating -->
                        <div class="mb-4 text-center">
                            <label class="form-label fw-bold d-block mb-3"><?php echo e(__('reviews.your_rating')); ?></label>
                            <div class="star-rating-input" style="font-size: 2rem; color: #d1d5db; cursor: pointer;">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star rating-star" data-rating="<?php echo e($i); ?>"
                                        onclick="setRating(<?php echo e($i); ?>)"></i>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="rating" id="ratingInput" value="5" required>
                            <small class="text-muted rating-text"><?php echo e(__('reviews.excellent')); ?></small>
                        </div>

                        <!-- Review Text -->
                        <div class="mb-3">
                            <label for="reviewText" class="form-label fw-bold"><?php echo e(__('reviews.your_review')); ?></label>
                            <textarea class="form-control" id="reviewText" name="review_text" rows="4"
                                placeholder="<?php echo e(__('reviews.review_placeholder')); ?>" required minlength="10"
                                maxlength="1000" style="border-radius: 12px; resize: none;"></textarea>
                            <small class="text-muted char-count">0 / 1000</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <?php echo e(__('general.cancel')); ?>

                        </button>
                        <button type="submit" class="btn btn-primary"
                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                            <i class="fas fa-paper-plane me-2"></i><?php echo e(__('reviews.submit_review')); ?>

                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <script>
        // Review Modal Functions
        function openReviewModal() {
            const modal = new bootstrap.Modal(document.getElementById('reviewModal'));
            modal.show();
        }

        function setRating(rating) {
            document.getElementById('ratingInput').value = rating;
            const stars = document.querySelectorAll('.rating-star');
            const ratingTexts = {
                1: '<?php echo e(__('reviews.poor')); ?>',
                2: '<?php echo e(__('reviews.fair')); ?>',
                3: '<?php echo e(__('reviews.good')); ?>',
                4: '<?php echo e(__('reviews.very_good')); ?>',
                5: '<?php echo e(__('reviews.excellent')); ?>'
            };

            stars.forEach((star, index) => {
                if (index < rating) {
                    star.style.color = '#f59e0b';
                    star.classList.remove('far');
                    star.classList.add('fas');
                } else {
                    star.style.color = '#d1d5db';
                    star.classList.remove('fas');
                    star.classList.add('far');
                }
            });

            document.querySelector('.rating-text').textContent = ratingTexts[rating];
        }

        // Character counter for review
        document.addEventListener('DOMContentLoaded', function () {
            const reviewText = document.getElementById('reviewText');
            if (reviewText) {
                reviewText.addEventListener('input', function () {
                    document.querySelector('.char-count').textContent = this.value.length + ' / 1000';
                });
            }

            // Initialize rating
            setRating(5);
        });

        // Validate file size before upload
        function validateFileSize(input, maxSizeMB) {
            if (input.files && input.files[0]) {
                const fileSize = input.files[0].size / 1024 / 1024; // Convert to MB
                const fileName = input.files[0].name;
                const fileType = input.files[0].type;

                // Check file size
                if (fileSize > maxSizeMB) {
                    alert(`<?php echo e(__('service_provider.file_too_large')); ?> ${fileName} (${fileSize.toFixed(2)}MB). <?php echo e(__('service_provider.max_allowed')); ?>: ${maxSizeMB}MB`);
                    input.value = ''; // Clear the input
                    return false;
                }

                // Additional validation for images
                if (fileType.startsWith('image/')) {
                    const img = new Image();
                    img.onload = function () {
                        console.log(`Image dimensions: ${this.width}x${this.height}`);
                    };
                    img.src = URL.createObjectURL(input.files[0]);
                }

                console.log(`File validated: ${fileName} (${fileSize.toFixed(2)}MB)`);
                return true;
            }
        }

        function validateMultipleFilesSize(input, maxSizeMB, maxFilesCount = 4) {
            if (!input.files || input.files.length === 0) {
                return true;
            }

            if (input.files.length > maxFilesCount) {
                alert(`<?php echo e(__('service_provider.gallery_upload_max_images')); ?> (${maxFilesCount})`);
                input.value = '';
                return false;
            }

            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

            for (const file of input.files) {
                const fileSize = file.size / 1024 / 1024; // Convert to MB
                if (fileSize > maxSizeMB) {
                    alert(`<?php echo e(__('service_provider.file_too_large_gallery')); ?> (${file.name}) (${fileSize.toFixed(2)}MB). <?php echo e(__('service_provider.max_allowed')); ?>: ${maxSizeMB}MB`);
                    input.value = '';
                    return false;
                }

                if (!allowedTypes.includes(file.type)) {
                    alert(`<?php echo e(__('service_provider.gallery_upload_invalid_type')); ?>`);
                    input.value = '';
                    return false;
                }
            }

            return true;
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Image Modal
            const imageModal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');

            imageModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const imageSrc = button.getAttribute('data-image');
                modalImage.src = imageSrc;
            });

            // Save/unsave is handled via a standard POST form to keep server-side
            // rendering and redirects. The previous fetch-based implementation
            // was removed to comply with view-only (no-API) policy.

            // Toast notification function
            window.showToast = function (message, type = 'success') {
                const toastContainer = document.querySelector('.toast-container');
                const toastElement = toastContainer.querySelector('.custom-toast');
                const toastMessage = toastElement.querySelector('.toast-message');
                const toastIcon = toastElement.querySelector('.toast-icon i');

                toastMessage.textContent = message;
                toastElement.className = `custom-toast toast-${type}`;

                if (type === 'success') {
                    toastIcon.className = 'fas fa-check-circle';
                } else {
                    toastIcon.className = 'fas fa-exclamation-circle';
                }

                toastContainer.setAttribute('x-show', 'true');

                setTimeout(() => {
                    toastContainer.setAttribute('x-show', 'false');
                }, 3000);
            }

            // English-only Address Validation
            const addressInput = document.getElementById('addressInput');
            if (addressInput) {
                addressInput.addEventListener('input', function() {
                    const originalValue = this.value;
                    const newValue = originalValue.replace(/[^a-zA-Z0-9\s\-_.,#&'\/\@]/g, '');

                    if (originalValue !== newValue) {
                        this.value = newValue;
                        if(typeof window.showToast === 'function'){
                            window.showToast('<?php echo e(__("service_provider.address_english_only_hint")); ?>', 'error');
                        }
                    }
                });
            }

            // Click-to-change Profile Image
            const imageContainer = document.getElementById('profileImageClickable');
            const imageInput = document.getElementById('profileImageInput');
            const imageOverlay = document.getElementById('imageOverlay');
            const imageSpinner = document.getElementById('imageUploadSpinner');
            let imagePreview = document.getElementById('profileImagePreview');

            <?php if(auth()->check() && auth()->id() === $serviceProvider->user_id): ?>
            if (imageContainer && imageInput) {
                imageContainer.addEventListener('mouseenter', function() {
                    if(imageOverlay) imageOverlay.style.opacity = '1';
                });
                imageContainer.addEventListener('mouseleave', function() {
                    if(imageOverlay) imageOverlay.style.opacity = '0';
                });
                imageContainer.addEventListener('click', function(e) {
                    if(e.target === imageInput) return; // Prevent loop
                    imageInput.click();
                });

                imageInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    // Validate client-side
                    if (typeof validateFileSize === 'function' && !validateFileSize(this, 2)) return;

                    // Show spinner
                    if(imageSpinner) imageSpinner.style.display = 'flex';

                    // Prepare form data
                    const formData = new FormData();
                    formData.append('profile_image', file);
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                    // AJAX Request
                    fetch('<?php echo e(route("service-providers.profile.image-upload", $serviceProvider->id)); ?>', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update preview
                            imagePreview = document.getElementById('profileImagePreview'); // Re-fetch in case it was replaced
                            if (imagePreview) {
                                if (imagePreview.tagName === 'IMG') {
                                    imagePreview.src = data.image_url;
                                } else {
                                    // Replace placeholder div with img
                                    const img = document.createElement('img');
                                    img.src = data.image_url;
                                    img.alt = '<?php echo e($serviceProvider->company_name ?? $serviceProvider->user->name); ?>';
                                    img.className = 'profile-image';
                                    img.id = 'profileImagePreview';
                                    img.loading = 'lazy';
                                    imagePreview.replaceWith(img);
                                }
                            }
                            if(typeof window.showToast === 'function') window.showToast(data.message, 'success');

                            // Update progress bar
                            if (data.completion_percent !== undefined) {
                                const progressBar = document.querySelector('#completionProgressBar .progress-bar');
                                const percentBadge = document.querySelector('#completionProgressBar').closest('.mb-4').querySelector('.badge');
                                if (progressBar && percentBadge) {
                                    progressBar.style.width = data.completion_percent + '%';
                                    progressBar.setAttribute('aria-valuenow', data.completion_percent);
                                    percentBadge.textContent = data.completion_percent + '%';

                                    // Update colors
                                    let newBg = '';
                                    let newBadgeClass = 'badge rounded-pill px-3 ';
                                    if(data.completion_percent >= 80) {
                                        newBg = 'linear-gradient(90deg, #10b981,#059669)';
                                        newBadgeClass += 'bg-success';
                                    } else if(data.completion_percent >= 50) {
                                        newBg = 'linear-gradient(90deg, #f59e0b,#d97706)';
                                        newBadgeClass += 'bg-warning text-dark';
                                    } else {
                                        newBg = 'linear-gradient(90deg, #ef4444,#dc2626)';
                                        newBadgeClass += 'bg-danger';
                                    }
                                    progressBar.style.background = newBg;
                                    percentBadge.className = newBadgeClass;
                                }
                            }
                        } else {
                            if(typeof window.showToast === 'function') window.showToast(data.message || 'Error occurred', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        if(typeof window.showToast === 'function') window.showToast('Upload failed', 'error');
                    })
                    .finally(() => {
                        if(imageSpinner) imageSpinner.style.display = 'none';
                        imageInput.value = ''; // Reset
                    });
                });
            }
            <?php endif; ?>
        });

        // Track WhatsApp click (internal analytics) + reveal contact (privacy) + open WhatsApp
        async function revealContactInfo(whatsappClean, whatsappDisplay, address) {
            // Meta Pixel: Track Lead event (WhatsApp contact)
            if (typeof fbq === 'function') {
                var leadEventId = 'lead_<?php echo e($serviceProvider->id); ?>_' + Date.now();
                fbq('track', 'Lead', {
                    content_name: <?php echo json_encode($serviceProvider->company_name ?? $serviceProvider->user->name); ?>,
                    content_ids: ['<?php echo $serviceProvider->id; ?>'],
                    content_category: <?php echo json_encode($serviceProvider->category->translated_name ?? 'Uncategorized'); ?>,
                    contact_type: 'whatsapp',
                    language: '<?php echo e(app()->getLocale()); ?>'
                }, { eventID: leadEventId });
            }

            // Store reveal in SESSION (server-side) instead of localStorage
            // This ensures only the user who clicked can see the info
            const providerId = <?php echo e($serviceProvider->id); ?>;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            // Internal analytics: click_whatsapp (dedupe is intentionally not applied to clicks)
            let analyticsPromise = Promise.resolve();
            if (csrfToken) {
                analyticsPromise = fetch(`/service-providers/${providerId}/analytics/click`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ action_type: 'click_whatsapp' }),
                    keepalive: true,
                }).catch(() => {});
            }

            // Store reveal in session via AJAX request
            if (csrfToken) {
                fetch(`/service-providers/${providerId}/reveal-contact`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({})
                }).catch(err => console.error('Failed to store reveal:', err));
            }

            // Reveal WhatsApp number
            const whatsappElement = document.getElementById('whatsappNumber');
            if (whatsappElement) {
                whatsappElement.textContent = whatsappDisplay;
                whatsappElement.classList.remove('text-muted');
                whatsappElement.classList.add('text-success', 'fw-bold');
            }

            // Reveal address
            const addressElement = document.getElementById('addressText');
            if (addressElement && address) {
                addressElement.textContent = address;
                addressElement.classList.add('text-primary', 'fw-bold');
            }

            // Prepare WhatsApp message
            const businessName = <?php echo json_encode($serviceProvider->company_name ?? $serviceProvider->user->name); ?>;
            const whatsappMessage = <?php echo json_encode(__("service_provider.whatsapp_message")); ?>;
            const businessLabel = <?php echo json_encode(__("service_provider.business_name")); ?>;

            // Validate that we have required data
            if (!businessName || !whatsappMessage) {
                console.error('WhatsApp Error: Missing required data', {
                    businessName: businessName,
                    whatsappMessage: whatsappMessage
                });
                alert('<?php echo e(__("general.error")); ?>: Cannot send WhatsApp message. Missing information.');
                return;
            }

            const message = whatsappMessage + '\n' + businessLabel + ': ' + businessName;

            // Encode message for URL
            const encodedMessage = encodeURIComponent(message);

            // Create WhatsApp URL with message
            // Use api.whatsapp.com for better compatibility with WhatsApp Desktop and Web
            const whatsappUrl = `https://api.whatsapp.com/send?phone=${whatsappClean}&text=${encodedMessage}`;

            // Debug: Log the URL (you can remove this later)
            console.log('WhatsApp URL:', whatsappUrl);
            console.log('WhatsApp phone:', whatsappClean);
            console.log('Message:', message);

            // Attempt analytics write before redirecting (timeout keeps UX snappy)
            try {
                await Promise.race([
                    analyticsPromise,
                    new Promise((resolve) => setTimeout(resolve, 150)),
                ]);
            } catch (e) {
                // Ignore analytics errors: user action must still proceed.
            }

            // Open WhatsApp after a short delay
            setTimeout(function () {
                // Try to open in new window
                const newWindow = window.open(whatsappUrl, '_blank');

                // Fallback: If popup blocked, try opening in same window
                if (!newWindow || newWindow.closed || typeof newWindow.closed === 'undefined') {
                    window.location.href = whatsappUrl;
                }
            }, 0);
        }

        // ========== PROFILE EDIT FORM VALIDATION & UX ENHANCEMENTS ==========

        // Get the profile edit form if it exists
        const profileForm = document.querySelector('form[action*="profile.update"]');

        if (profileForm) {
            let isSubmitting = false;

            // Enhanced file preview for profile image with progress
            const profileImageInput = profileForm.querySelector('input[name="profile_image"]');
            if (profileImageInput) {
                // Create preview container
                const previewContainer = document.createElement('div');
                previewContainer.className = 'image-preview-container mt-3';
                previewContainer.style.cssText = 'display: none; position: relative;';
                profileImageInput.parentElement.appendChild(previewContainer);

                profileImageInput.addEventListener('change', function (e) {
                    const file = e.target.files[0];
                    if (file) {
                        // Validate file size (5MB)
                        if (file.size > 5 * 1024 * 1024) {
                            alert('<?php echo e(__("sp_validation.sp_image_size")); ?>');
                            e.target.value = '';
                            return;
                        }

                        // Validate file type
                        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                        if (!validTypes.includes(file.type)) {
                            alert('<?php echo e(__("sp_validation.sp_image_mimes")); ?>');
                            e.target.value = '';
                            return;
                        }

                        // Show preview with loading
                        previewContainer.innerHTML = `
                            <div class="position-relative">
                                <img src="" class="img-thumbnail" style="max-width: 200px; max-height: 200px; object-fit: cover;">
                                <div class="spinner-border spinner-border-sm position-absolute top-50 start-50 translate-middle text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 remove-preview">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <small class="text-success d-block mt-2">
                                <i class="fas fa-check-circle me-1"></i>${file.name} (${(file.size / 1024).toFixed(1)} KB)
                            </small>
                        `;
                        previewContainer.style.display = 'block';

                        const reader = new FileReader();
                        reader.onload = function (event) {
                            const img = previewContainer.querySelector('img');
                            img.src = event.target.result;
                            previewContainer.querySelector('.spinner-border').remove();

                            // Also update existing preview if present
                            const existingPreview = profileImageInput.parentElement.querySelector('.rounded');
                            if (existingPreview && existingPreview.tagName === 'IMG') {
                                existingPreview.src = event.target.result;
                            }
                        };
                        reader.readAsDataURL(file);

                        // Remove preview functionality
                        previewContainer.querySelector('.remove-preview').addEventListener('click', function () {
                            e.target.value = '';
                            previewContainer.style.display = 'none';
                        });
                    }
                });
            }

            // File validation for certification
            const certInput = profileForm.querySelector('input[name="certification"]');
            if (certInput) {
                certInput.addEventListener('change', function (e) {
                    const file = e.target.files[0];
                    if (file) {
                        // Validate file size (10MB)
                        if (file.size > 10 * 1024 * 1024) {
                            alert('<?php echo e(__("sp_validation.sp_cert_size")); ?>');
                            e.target.value = '';
                            return;
                        }

                        // Validate file type
                        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'application/pdf'];
                        if (!validTypes.includes(file.type)) {
                            alert('<?php echo e(__("sp_validation.sp_cert_mimes")); ?>');
                            e.target.value = '';
                            return;
                        }

                        // Show file name
                        const fileName = file.name;
                        const fileInfo = certInput.parentElement.querySelector('.file-info') || document.createElement('small');
                        fileInfo.className = 'file-info text-success d-block mt-1';
                        fileInfo.innerHTML = '<i class="fas fa-check-circle me-1"></i><?php echo e(__("general.selected")); ?>: ' + fileName;
                        if (!certInput.parentElement.querySelector('.file-info')) {
                            certInput.parentElement.appendChild(fileInfo);
                        }
                    }
                });
            }

            // Character counter for bio
            const bioTextarea = profileForm.querySelector('textarea[name="bio"]');
            if (bioTextarea) {
                const maxLength = 2000;
                const counter = document.createElement('small');
                counter.className = 'char-counter text-muted d-block text-end mt-1';
                bioTextarea.parentElement.appendChild(counter);

                function updateCounter() {
                    const remaining = maxLength - bioTextarea.value.length;
                    counter.textContent = remaining + ' <?php echo e(__("general.characters_remaining")); ?>';
                    counter.className = remaining < 100 ? 'char-counter text-warning d-block text-end mt-1' : 'char-counter text-muted d-block text-end mt-1';
                }

                updateCounter();
                bioTextarea.addEventListener('input', updateCounter);
            }

            // Character counter for services
            const servicesInput = profileForm.querySelector('input[name="services_offered"]');
            if (servicesInput) {
                const maxLength = 1000;
                const counter = document.createElement('small');
                counter.className = 'char-counter text-muted d-block text-end mt-1';
                servicesInput.parentElement.appendChild(counter);

                function updateCounter() {
                    const remaining = maxLength - servicesInput.value.length;
                    counter.textContent = remaining + ' <?php echo e(__("general.characters_remaining")); ?>';
                    counter.className = remaining < 50 ? 'char-counter text-warning d-block text-end mt-1' : 'char-counter text-muted d-block text-end mt-1';
                }

                updateCounter();
                servicesInput.addEventListener('input', updateCounter);
            }

            // Phone number formatting
            const phoneInput = profileForm.querySelector('input[name="phone"]');
            if (phoneInput) {
                phoneInput.addEventListener('input', function (e) {
                    // Remove all non-numeric characters except +
                    let value = e.target.value.replace(/[^\d+]/g, '');
                    e.target.value = value;
                });
            }

            const whatsappInput = profileForm.querySelector('input[name="whatsapp_number"]');
            if (whatsappInput) {
                whatsappInput.addEventListener('input', function (e) {
                    // Remove all non-numeric characters except +
                    let value = e.target.value.replace(/[^\d+]/g, '');
                    e.target.value = value;
                });
            }

            // Enhanced form submission with upload progress
            profileForm.addEventListener('submit', function (e) {
                // Prevent double submission
                if (isSubmitting) {
                    e.preventDefault();
                    return false;
                }

                // Basic validation
                const businessName = profileForm.querySelector('input[name="business_name"]');
                if (businessName && businessName.value.trim().length < 3) {
                    e.preventDefault();
                    alert('<?php echo e(__("sp_validation.sp_business_name_min")); ?>');
                    businessName.focus();
                    return false;
                }

                const phone = profileForm.querySelector('input[name="phone"]');
                if (phone && phone.value.trim().length < 10) {
                    e.preventDefault();
                    alert('<?php echo e(__("sp_validation.sp_phone_min")); ?>');
                    phone.focus();
                    return false;
                }

                const email = profileForm.querySelector('input[name="contact_email"]');
                if (email && email.value && !isValidEmail(email.value)) {
                    e.preventDefault();
                    alert('<?php echo e(__("sp_validation.sp_email_format")); ?>');
                    email.focus();
                    return false;
                }

                // Mark as submitting
                isSubmitting = true;

                // Show enhanced loading state with progress
                const submitBtn = profileForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    const originalText = submitBtn.innerHTML;

                    // Create progress indicator
                    submitBtn.innerHTML = `
                        <div class="d-flex align-items-center justify-content-center">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            <span><?php echo e(__("general.saving")); ?>...</span>
                        </div>
                    `;

                    // Add upload progress overlay
                    const progressOverlay = document.createElement('div');
                    progressOverlay.className = 'upload-progress-overlay';
                    progressOverlay.style.cssText = `
                        position: fixed;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background: rgba(0, 0, 0, 0.7);
                        z-index: 9999;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    `;
                    progressOverlay.innerHTML = `
                        <div class="bg-white p-4 rounded-3 shadow-lg text-center" style="max-width: 300px;">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <h6 class="mb-2"><?php echo e(__("general.saving")); ?>...</h6>
                            <p class="small text-muted mb-0"><?php echo e(__("service_provider.please_wait_updating")); ?></p>
                        </div>
                    `;
                    document.body.appendChild(progressOverlay);

                    // Restore button after timeout (fallback)
                    setTimeout(function () {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                        isSubmitting = false;
                        if (progressOverlay.parentElement) {
                            progressOverlay.remove();
                        }
                    }, 30000); // 30 seconds timeout
                }

                return true;
            });

            // Email validation helper
            function isValidEmail(email) {
                const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                return regex.test(email);
            }
        }

        // Show validation errors prominently
        <?php if($errors->any()): ?>
            window.addEventListener('DOMContentLoaded', function () {
                // Scroll to first error
                const firstError = document.querySelector('.text-danger');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.classList.add('animate-shake');
                }

                // Show error summary alert
                const errorList = <?php echo json_encode($errors->all(), 15, 512) ?>;
                if (errorList.length > 0) {
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                    alertDiv.style.zIndex = '9999';
                    alertDiv.style.maxWidth = '500px';
                    alertDiv.innerHTML = `
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                                <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i><?php echo e(__("validation.please_correct_errors")); ?></h6>
                                                <ul class="mb-0 small">
                                                    ${errorList.map(error => '<li>' + error + '</li>').join('')}
                                                </ul>
                                            `;
                    document.body.appendChild(alertDiv);

                    // Auto dismiss after 10 seconds
                    setTimeout(() => {
                        alertDiv.remove();
                    }, 10000);
                }
            });
        <?php endif; ?>

        <?php if(session('success')): ?>
            window.addEventListener('DOMContentLoaded', function () {
                showToast('<?php echo e(session("success")); ?>', 'success');
            });
        <?php endif; ?>
    </script>

    <style>
        .char-counter {
            font-size: 0.85rem;
        }

        .file-info {
            font-size: 0.9rem;
        }

        .animate-shake {
            animation: shake 0.5s;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                transform: translateX(-5px);
            }

            20%,
            40%,
            60%,
            80% {
                transform: translateX(5px);
            }
        }

        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: 0.15em;
        }
    </style>
</body>

</html>
<?php /**PATH Y:\Speeda - Versions\Speeda\resources\views/service-providers/show.blade.php ENDPATH**/ ?>