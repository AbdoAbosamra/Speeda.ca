<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(__('service_provider.service_providers')); ?> - Speeda</title>
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/main-logo.png')); ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        /* ===== نظام التصميم الأساسي ===== */
        :root {
            /* نظام ألوان احترافي */
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --primary-light: #6366f1;
            --secondary: #0ea5e9;
            --accent: #8b5cf6;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;

            /* درجات الرمادي المنظمة */
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;

            /* الظلال */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            --shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            --shadow-primary: 0 10px 30px -5px rgba(79, 70, 229, 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, var(--gray-50) 0%, #f0f4ff 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
            color: var(--gray-800);
            line-height: 1.6;
        }

        /* ===== تنسيقات النصوص الأساسية ===== */
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-weight: 700;
            line-height: 1.2;
        }

        a {
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .container {
            max-width: 1280px;
        }

        /* ===== رأس الصفحة ===== */
        .page-shell {
            padding: 2.5rem 0 5rem;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ===== قسم الهيرو المحسّن ===== */
        .hero-section {
            background: white;
            border-radius: 24px;
            padding: 3rem;
            margin-bottom: 3rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 20%, rgba(79, 70, 229, 0.08) 0%, transparent 70%);
            z-index: 0;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
        }

        .hero-text h1 {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            color: var(--gray-600);
            font-weight: 400;
            max-width: 600px;
        }

        /* زر العودة */
        .btn-back {
            padding: 0.875rem 1.5rem;
            background: white;
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            color: var(--gray-700);
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .btn-back:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateX(-4px);
        }

        /* ===== نظام الفلاتر المحسّن ===== */
        .filters-grid {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 1rem;
            margin-top: 2rem;
        }

        .search-wrapper {
            position: relative;
        }

        .search-input {
            width: 100%;
            height: 56px;
            padding: 0 1rem 0 3.5rem;
            background: var(--gray-50);
            border: 2px solid var(--gray-200);
            border-radius: 14px;
            font-size: 1rem;
            font-weight: 500;
            color: var(--gray-800);
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: var(--shadow-primary);
        }

        .search-icon {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-500);
            font-size: 1.1rem;
            pointer-events: none;
        }

        .select-wrapper {
            position: relative;
        }

        .filter-select {
            height: 56px;
            min-width: 180px;
            padding: 0 2.5rem 0 1.25rem;
            background: var(--gray-50);
            border: 2px solid var(--gray-200);
            border-radius: 14px;
            font-size: 1rem;
            font-weight: 500;
            color: var(--gray-800);
            appearance: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: var(--shadow-primary);
        }

        .select-arrow {
            position: absolute;
            right: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-500);
            pointer-events: none;
        }

        /* ===== شبكة البطاقات المحسّنة ===== */
        .providers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        /* ===== بطاقة مقدم الخدمة المحسّنة ===== */
        .provider-card {
            background: white;
            border-radius: 24px;
            border: 1px solid var(--gray-200);
            overflow: visible;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            min-height: 100%;
            display: flex;
            flex-direction: column;
            animation: cardSlideIn 0.6s ease-out;
        }

        @keyframes cardSlideIn {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .provider-card:hover {
            transform: translateY(-12px);
            box-shadow: var(--shadow-2xl);
            border-color: var(--primary);
        }

        .card-header {
            padding: 1.75rem 1.75rem 1rem;
            position: relative;
        }

        .provider-badge {
            position: absolute;
            top: 1.75rem;
            right: 1.75rem;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            box-shadow: var(--shadow-sm);
        }

        .provider-header {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .avatar-container {
            position: relative;
            flex-shrink: 0;
        }

        .provider-avatar {
            width: 80px;
            height: 80px;
            border-radius: 18px;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: var(--shadow-lg);
            transition: all 0.3s ease;
        }

        .provider-card:hover .provider-avatar {
            transform: scale(1.08);
            border-color: var(--primary-light);
        }

        .verified-check {
            position: absolute;
            bottom: -5px;
            right: -5px;
            width: 28px;
            height: 28px;
            background: var(--success);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            border: 3px solid white;
            box-shadow: var(--shadow-md);
        }

        .provider-info h3 {
            font-size: 1.375rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.375rem;
            line-height: 1.3;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .provider-category {
            color: var(--gray-600);
            font-size: 0.9375rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .rating-display {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stars {
            color: var(--warning);
            font-size: 0.9rem;
        }

        .rating-score {
            font-weight: 700;
            color: var(--gray-900);
            font-size: 1rem;
        }

        .reviews-count {
            color: var(--gray-500);
            font-size: 0.875rem;
        }

        /* ===== معلومات الموقع ===== */
        .location-section {
            padding: 0 1.75rem;
            margin-bottom: 1.5rem;
        }

        .location-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: linear-gradient(135deg, var(--gray-50), var(--gray-100));
            border-radius: 12px;
            border: 1px solid var(--gray-200);
            transition: all 0.3s ease;
        }

        .location-info:hover {
            border-color: var(--primary);
            background: white;
        }

        .location-icon {
            color: var(--secondary);
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .address-text {
            flex: 1;
            font-size: 0.9375rem;
            color: var(--gray-700);
            font-weight: 500;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .hidden-address {
            color: var(--gray-500);
            font-style: italic;
        }

        .full-address {
            color: var(--gray-800);
            font-weight: 600;
        }

        /* ===== إحصائيات مقدم الخدمة ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
            padding: 0 1.75rem;
            margin-bottom: 1.75rem;
        }

        .stat-item {
            text-align: center;
            padding: 0.875rem;
            background: var(--gray-50);
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .stat-item:hover {
            background: white;
            box-shadow: var(--shadow-sm);
        }

        .stat-icon {
            color: var(--primary);
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.8125rem;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ===== أزرار التفاعل ===== */
        .card-footer {
            padding: 1.75rem;
            border-top: 1px solid var(--gray-100);
            margin-top: auto;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .btn-action {
            flex: 1;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            border: none;
            font-weight: 600;
            font-size: 0.9375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-action::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-action:hover::before {
            left: 100%;
        }

        .btn-recommend {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: white;
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.3);
        }

        .btn-recommend:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(79, 70, 229, 0.4);
        }

        .btn-rate {
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
            color: var(--gray-900);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.3);
        }

        .btn-rate:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(245, 158, 11, 0.4);
        }

        .btn-profile {
            width: 100%;
            padding: 1.125rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 14px;
            font-weight: 700;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .btn-profile::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.7s ease;
        }

        .btn-profile:hover::before {
            left: 100%;
        }

        .btn-profile:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(14, 165, 233, 0.3);
            letter-spacing: 0.5px;
        }

        /* ===== شارة الخبرة ===== */
        .experience-badge {
            position: absolute;
            bottom: 1.75rem;
            right: 1.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(139, 92, 246, 0.05));
            color: var(--accent);
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 700;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(139, 92, 246, 0.2);
        }

        /* ===== حالة عدم وجود نتائج ===== */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 6rem 2rem;
            background: white;
            border-radius: 24px;
            border: 2px dashed var(--gray-300);
            animation: fadeIn 0.8s ease-out;
        }

        .empty-illustration {
            font-size: 5rem;
            color: var(--gray-300);
            margin-bottom: 2rem;
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .empty-title {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--gray-800);
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .empty-description {
            font-size: 1.125rem;
            color: var(--gray-600);
            max-width: 500px;
            margin: 0 auto 3rem;
            line-height: 1.7;
        }

        .empty-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-bottom: 3rem;
        }

        .btn-primary {
            padding: 1rem 2.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 14px;
            font-weight: 700;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px rgba(79, 70, 229, 0.3);
        }

        .suggestions-section {
            border-top: 1px solid var(--gray-200);
            padding-top: 3rem;
        }

        .suggestions-title {
            font-size: 1rem;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1.5rem;
            font-weight: 700;
        }

        .suggestions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .suggestion-card {
            padding: 1.5rem;
            background: var(--gray-50);
            border-radius: 16px;
            text-align: center;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .suggestion-card:hover {
            background: white;
            border-color: var(--primary);
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .suggestion-icon {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .suggestion-text {
            font-weight: 600;
            color: var(--gray-800);
        }

        /* ===== Modal Container ===== */
        .rate-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.95) 0%, rgba(17, 24, 39, 0.98) 100%);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            overflow-y: auto;
            padding: 2rem;
        }

        .rate-modal.active {
            opacity: 1;
            pointer-events: all;
            animation: modalFadeIn 0.5s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                backdrop-filter: blur(0px);
            }

            to {
                opacity: 1;
                backdrop-filter: blur(12px);
            }
        }

        /* ===== Modal Content - Glassmorphism Design ===== */
        .rate-modal-content {
            background: linear-gradient(145deg, #f9fafb, #ffffff);
            border-radius: 28px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4),
                0 0 0 1px rgba(255, 255, 255, 0.1),
                inset 0 -5px 20px rgba(0, 0, 0, 0.05);
            max-width: 550px;
            width: 95%;
            max-height: 90vh;
            overflow-y: auto;
            transform: scale(0.9);
            opacity: 0;
            animation: modalContentSlideUp 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            animation-delay: 0.1s;
            position: relative;
            border: 1px solid #e5e7eb;
        }

        @keyframes modalContentSlideUp {
            from {
                transform: scale(0.9) translateY(30px);
                opacity: 0;
                filter: blur(5px);
            }

            to {
                transform: scale(1) translateY(0);
                opacity: 1;
                filter: blur(0);
            }
        }

        /* ===== Modal Header ===== */
        .rate-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2.5rem 2.5rem 1.5rem;
            border-bottom: 2px solid #f3f4f6;
            background: linear-gradient(135deg,
                    rgba(99, 102, 241, 0.08) 0%,
                    rgba(245, 158, 11, 0.08) 100%);
            position: sticky;
            top: 0;
            z-index: 10;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .rate-modal-header h2 {
            font-size: 1.8rem;
            font-weight: 800;
            color: #111827;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
        }

        .rate-modal-header h2::before {
            content: '⭐';
            font-size: 1.6rem;
            animation: starPulse 2s ease-in-out infinite;
        }

        @keyframes starPulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.2);
                opacity: 0.8;
            }
        }

        .rate-modal-close {
            background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
            border: 2px solid #e5e7eb;
            font-size: 1.8rem;
            color: #6b7280;
            cursor: pointer;
            padding: 0.8rem 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
        }

        .rate-modal-close::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .rate-modal-close:hover::before {
            left: 100%;
        }

        .rate-modal-close:hover {
            background: linear-gradient(135deg, #e5e7eb, #d1d5db);
            color: #374151;
            transform: scale(1.1) rotate(90deg);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            border-color: #d1d5db;
        }

        /* ===== Modal Body ===== */
        .rate-modal-body {
            padding: 2rem 2.5rem 2.5rem;
        }

        /* ===== Rating Section ===== */
        .rating-section {
            margin-bottom: 2.5rem;
            text-align: center;
        }

        .rating-label {
            display: block;
            font-weight: 800;
            color: #111827;
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .rating-label::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #6366f1, #f59e0b);
            border-radius: 3px;
        }

        /* ===== Star Rating - Advanced Design ===== */
        .star-rating {
            display: flex;
            gap: 1.2rem;
            justify-content: center;
            margin-bottom: 1.5rem;
            position: relative;
            padding: 1rem;
            background: linear-gradient(135deg, #f9fafb, #f3f4f6);
            border-radius: 20px;
            border: 2px solid #e5e7eb;
            box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .star-rating::before {
            content: '';
            position: absolute;
            top: -8px;
            left: -8px;
            right: -8px;
            bottom: -8px;
            background: linear-gradient(45deg, #6366f1, #f59e0b, #10b981);
            border-radius: 28px;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
            filter: blur(15px);
        }

        .star-rating:hover::before {
            opacity: 0.3;
        }

        .star-rating input {
            display: none;
        }

        .star-label {
            font-size: 3.2rem;
            color: #d1d5db;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 0.8rem;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            background: transparent;
            border: 2px solid transparent;
        }

        .star-label::before {
            content: '★';
            position: absolute;
            font-size: 3.2rem;
            color: transparent;
            -webkit-text-stroke: 1px #d1d5db;
            transition: all 0.3s ease;
        }

        .star-label:hover::before,
        .star-label:hover~.star-label::before {
            -webkit-text-stroke: 1px #f59e0b;
            text-shadow: 0 0 15px rgba(245, 158, 11, 0.6);
        }

        .star-label.active::before {
            color: #f59e0b;
            -webkit-text-stroke: 0;
            text-shadow: 0 0 20px rgba(245, 158, 11, 0.8),
                0 0 40px rgba(245, 158, 11, 0.6);
            animation: starGlow 1.5s ease-in-out infinite alternate;
        }

        @keyframes starGlow {
            from {
                text-shadow: 0 0 10px rgba(245, 158, 11, 0.5),
                    0 0 20px rgba(245, 158, 11, 0.3);
            }

            to {
                text-shadow: 0 0 25px rgba(245, 158, 11, 1),
                    0 0 50px rgba(245, 158, 11, 0.8);
            }
        }

        .star-label:hover {
            transform: scale(1.3) translateY(-5px);
            z-index: 10;
            color: #f59e0b;
        }

        .star-label:hover::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 120%;
            height: 120%;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.3) 0%, transparent 70%);
            border-radius: 50%;
            animation: pulse 1s ease-out;
        }

        @keyframes pulse {
            0% {
                opacity: 0.8;
                transform: translate(-50%, -50%) scale(0.8);
            }

            100% {
                opacity: 0;
                transform: translate(-50%, -50%) scale(1.5);
            }
        }

        /* ===== Rating Text Display ===== */
        .rating-text {
            text-align: center;
            color: #6b7280;
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
            padding: 1rem;
            background: linear-gradient(135deg, #f9fafb, #f3f4f6);
            border-radius: 12px;
            border-left: 4px solid #f59e0b;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        /* ===== Form Section ===== */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.8rem;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-label::before {
            content: '📝';
            font-size: 1.1rem;
        }

        /* ===== Review Textarea - Modern Design ===== */
        .review-textarea {
            width: 100%;
            padding: 1.2rem 1.5rem;
            border: 3px solid #e5e7eb;
            border-radius: 16px;
            font-family: inherit;
            font-size: 1rem;
            color: #374151;
            resize: vertical;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: linear-gradient(135deg, #f9fafb, #ffffff);
            min-height: 120px;
            max-height: 300px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .review-textarea::placeholder {
            color: #9ca3af;
            opacity: 1;
        }

        .review-textarea:focus {
            outline: none;
            border-color: #f59e0b;
            background: white;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.15),
                0 8px 25px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        /* ===== Character Counter ===== */
        .char-count {
            text-align: right;
            font-size: 0.85rem;
            color: #6b7280;
            margin-top: 0.8rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .char-count.warning {
            color: #d97706;
            animation: shake 0.3s ease;
        }

        .char-count.success {
            color: #10b981;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-3px);
            }

            75% {
                transform: translateX(3px);
            }
        }

        /* ===== Modal Actions ===== */
        .rate-modal-actions {
            display: flex;
            gap: 1.2rem;
            margin-top: 2.5rem;
            padding-top: 2.5rem;
            border-top: 2px solid #f3f4f6;
            position: sticky;
            bottom: 0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.9), white);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding-bottom: 1.5rem;
        }

        /* ===== Buttons - Advanced Design ===== */
        .btn-cancel,
        .btn-submit {
            flex: 1;
            padding: 1.1rem 1.5rem;
            border: none;
            border-radius: 16px;
            font-weight: 700;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-cancel {
            background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
            color: #4b5563;
            border: 2px solid #d1d5db;
        }

        .btn-cancel::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: left 0.6s ease;
        }

        .btn-cancel:hover::before {
            left: 100%;
        }

        .btn-cancel:hover {
            background: linear-gradient(135deg, #e5e7eb, #d1d5db);
            color: #374151;
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-color: #9ca3af;
        }

        .btn-cancel:active {
            transform: translateY(0) scale(0.98);
        }

        .btn-submit {
            background: linear-gradient(135deg, #f59e0b, #fbbf24, #d97706);
            color: #111827;
            box-shadow: 0 6px 25px rgba(245, 158, 11, 0.4),
                0 0 20px rgba(245, 158, 11, 0.2);
            position: relative;
            overflow: hidden;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg,
                    transparent,
                    rgba(255, 255, 255, 0.3),
                    transparent);
            transform: rotate(45deg);
            transition: all 0.6s ease;
        }

        .btn-submit:hover::before {
            left: 100%;
        }

        .btn-submit::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg,
                    rgba(255, 255, 255, 0.2),
                    transparent,
                    rgba(255, 255, 255, 0.1));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .btn-submit:hover::after {
            opacity: 1;
        }

        .btn-submit:hover {
            transform: translateY(-6px) scale(1.05);
            box-shadow: 0 12px 40px rgba(245, 158, 11, 0.6),
                0 0 30px rgba(245, 158, 11, 0.4);
            background: linear-gradient(135deg, #fbbf24, #f59e0b, #f59e0b);
        }

        .btn-submit:active {
            transform: translateY(2px) scale(0.98);
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.2);
        }

        /* ===== Loading State ===== */
        .btn-submit.loading {
            position: relative;
            color: transparent;
        }

        .btn-submit.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 24px;
            height: 24px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: #111827;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }

        /* ===== Responsive Design ===== */
        @media (max-width: 768px) {
            .rate-modal-content {
                max-width: 95%;
                border-radius: 24px;
            }

            .rate-modal-header h2 {
                font-size: 1.5rem;
            }

            .star-label {
                font-size: 2.8rem;
            }

            .rate-modal-actions {
                flex-direction: column;
            }

            .btn-cancel,
            .btn-submit {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .rate-modal {
                padding: 1rem;
            }

            .rate-modal-content {
                border-radius: 20px;
            }

            .rate-modal-header,
            .rate-modal-body {
                padding: 1.5rem;
            }

            .star-label {
                font-size: 2.5rem;
            }

            .rating-text {
                font-size: 0.9rem;
            }
        }

        /* ===== Micro Interactions ===== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .rating-section,
        .form-group {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .rating-section {
            animation-delay: 0.2s;
        }

        .form-group {
            animation-delay: 0.4s;
        }

        .rate-modal-actions {
            animation: fadeInUp 0.6s ease-out 0.6s forwards;
        }

        /* ===== Success Animation ===== */
        @keyframes successBounce {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        .success-animation {
            animation: successBounce 0.5s ease-out;
        }

        /* ===== Custom Scrollbar ===== */
        .rate-modal-content::-webkit-scrollbar {
            width: 8px;
        }

        .rate-modal-content::-webkit-scrollbar-track {
            background: #f9fafb;
            border-radius: 10px;
        }

        .rate-modal-content::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #6366f1, #f59e0b);
            border-radius: 10px;
            border: 2px solid #f9fafb;
        }

        .rate-modal-content::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #4f46e5, #d97706);
        }

        /* ===== التصميم المتجاوب ===== */
        @media (max-width: 640px) {
            .rate-modal-content {
                width: 95%;
                border-radius: 16px;
            }

            .rate-modal-header,
            .rate-modal-body {
                padding: 1.5rem;
            }

            .star-label {
                font-size: 2rem;
                padding: 0.25rem;
            }

            .rate-modal-actions {
                flex-direction: column-reverse;
            }
        }

        @media (max-width: 1200px) {
            .providers-grid {
                grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
                gap: 1.5rem;
            }
        }

        @media (max-width: 992px) {
            .hero-section {
                padding: 2rem;
            }

            .hero-text h1 {
                font-size: 2.5rem;
            }

            .filters-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .filter-select {
                min-width: 100%;
            }

            .providers-grid {
                grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .page-shell {
                padding: 1.5rem 0 3rem;
            }

            .hero-section {
                padding: 1.5rem;
            }

            .hero-header {
                flex-direction: column;
                gap: 1.5rem;
            }

            .hero-text h1 {
                font-size: 2rem;
            }

            .providers-grid {
                grid-template-columns: 1fr;
                gap: 1.25rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .action-buttons {
                flex-direction: column;
            }

            .empty-state {
                padding: 4rem 1.5rem;
            }

            .empty-title {
                font-size: 1.75rem;
            }

            .empty-actions {
                flex-direction: column;
            }

            .suggestions-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .provider-header {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .provider-info {
                text-align: center;
            }

            .experience-badge {
                position: relative;
                bottom: auto;
                right: auto;
                margin-top: 1rem;
                justify-content: center;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ===== RTL Support ===== */
        [dir="rtl"] .provider-badge {
            right: auto;
            left: 1.75rem;
        }

        [dir="rtl"] .provider-header {
            flex-direction: row-reverse;
        }

        [dir="rtl"] .verified-check {
            right: auto;
            left: -5px;
        }

        [dir="rtl"] .provider-info {
            text-align: right;
        }

        [dir="rtl"] .location-info {
            flex-direction: row-reverse;
        }

        [dir="rtl"] .address-text {
            text-align: right;
        }

        [dir="rtl"] .action-buttons {
            flex-direction: row-reverse;
        }

        [dir="rtl"] .experience-badge {
            right: auto;
            left: 1.75rem;
        }

        [dir="rtl"] .rating-display {
            flex-direction: row-reverse;
        }

        [dir="rtl"] .stats-grid {
            direction: rtl;
        }

        [dir="rtl"] .btn-action {
            flex-direction: row-reverse;
        }

        [dir="rtl"] .btn-profile {
            flex-direction: row-reverse;
        }

        [dir="rtl"] .select-arrow {
            right: auto;
            left: 1.25rem;
            transform: translateY(-50%) scaleX(-1);
        }

        [dir="rtl"] .search-icon {
            left: auto;
            right: 1.25rem;
        }

        [dir="rtl"] .filter-select {
            padding: 0 1.25rem 0 2.5rem;
        }

        /* ===== تأثيرات التحميل المتتالية ===== */
        .provider-card:nth-child(1) {
            animation-delay: 0.1s;
        }

        .provider-card:nth-child(2) {
            animation-delay: 0.2s;
        }

        .provider-card:nth-child(3) {
            animation-delay: 0.3s;
        }

        .provider-card:nth-child(4) {
            animation-delay: 0.4s;
        }

        .provider-card:nth-child(5) {
            animation-delay: 0.5s;
        }

        .provider-card:nth-child(6) {
            animation-delay: 0.6s;
        }

        /* ===== تحسينات إضافية ===== */
        .fade-in {
            animation: fadeIn 0.8s ease-out forwards;
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(79, 70, 229, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(79, 70, 229, 0);
            }
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>

<body>
    <?php echo $__env->make('components.main-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="container page-shell">
        <!-- قسم الهيرو المحسّن -->
        <div class="hero-section glass-effect">
            <div class="hero-content">
                <div class="hero-header">
                    <div class="hero-text">
                        <p class="text-uppercase text-primary fw-semibold mb-2">
                            <i class="fas fa-compass me-2"></i><?php echo e(__('service_provider.discover_providers')); ?>

                        </p>
                        <h1 class="mb-2"><?php echo e(__('service_provider.service_providers')); ?></h1>
                        <p class="hero-subtitle"><?php echo e(__('service_provider.browse_providers_description')); ?></p>
                    </div>
                    <a href="<?php echo e(route('home')); ?>" class="btn-back">
                        <i class="fas fa-arrow-left"></i>
                        <?php echo e(__('general.back_to_home')); ?>

                    </a>
                </div>

                <!-- نظام الفلاتر المحسّن -->
                <div class="filters-grid">
                    <div class="search-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" id="searchInput"
                            placeholder="<?php echo e(__('service_provider.search_providers')); ?>" value="<?php echo e(request('search')); ?>">
                    </div>

                    <div class="select-wrapper">
                        <select class="filter-select" id="locationFilter">
                            <option value=""><?php echo e(__('service_provider.all_locations')); ?></option>
                            <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($location->id); ?>" <?php echo e(request('location') == $location->id ? 'selected' : ''); ?>>
                                    <i class="fas fa-map-marker-alt me-2"></i><?php echo e($location->city); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <i class="fas fa-chevron-down select-arrow"></i>
                    </div>

                    <div class="select-wrapper">
                        <select class="filter-select" id="categoryFilter">
                            <option value=""><?php echo e(__('service_provider.all_categories')); ?></option>
                            <?php
                                $categories = $categories ?? collect([]);
                                $othersNames = ['other', 'others', 'أخرى'];
                                $others = $categories->filter(function ($c) use ($othersNames) {
                                    return in_array(strtolower(trim($c->translated_name)), $othersNames);
                                });
                                $othersFirst = $others->first();
                                $categoriesFiltered = $categories->reject(function ($c) use ($othersNames) {
                                    return in_array(strtolower(trim($c->translated_name)), $othersNames);
                                });
                            ?>

                            <?php $__currentLoopData = $categoriesFiltered; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($category->id); ?>" <?php echo e(request('category') == $category->id ? 'selected' : ''); ?>>
                                    <?php echo e($category->translated_name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            <?php if($others->isNotEmpty()): ?>
                                <option value="others" <?php echo e(request('category') == 'others' ? 'selected' : ''); ?>>
                                    <?php echo e(__('categories.others')); ?>

                                </option>
                            <?php endif; ?>
                        </select>
                        <i class="fas fa-chevron-down select-arrow"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- شبكة البطاقات المحسّنة -->
        <div class="providers-grid">
            <?php $__empty_1 = true; $__currentLoopData = $serviceProviders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $provider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="provider-card fade-in">
                    <div class="card-header">
                        <?php if($provider->featured): ?>
                            <div class="provider-badge">
                                <i class="fas fa-crown me-1"></i> <?php echo e(__('service_provider.featured')); ?>

                            </div>
                        <?php endif; ?>

                        <div class="provider-header">
                            <div class="avatar-container">
                                <?php if($provider->profile_image): ?>
                                    <img src="<?php echo e(asset('storage/' . $provider->profile_image)); ?>"
                                        alt="<?php echo e($provider->company_name ?? $provider->user->name); ?>" class="provider-avatar">
                                <?php else: ?>
                                    <div class="provider-avatar d-flex align-items-center justify-content-center"
                                        style="background: linear-gradient(135deg, var(--primary), var(--secondary));">
                                        <i class="fas fa-user fa-2x text-white"></i>
                                    </div>
                                <?php endif; ?>

                                <?php if($provider->certification): ?>
                                    <div class="verified-check">
                                        <i class="fas fa-check"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="provider-info">
                                <h3><?php echo e($provider->company_name ?? $provider->user->name); ?></h3>
                                <p class="provider-category">
                                    <?php echo e($provider->category->translated_name ?? __('service_provider.uncategorized')); ?>

                                </p>
                                <div class="rating-display" data-provider-id="<?php echo e($provider->id); ?>">
                                    <div class="stars">
                                        <?php
                                            $displayRating = $provider->live_rating ?? $provider->rating ?? 0;
                                            $reviewCount = $provider->reviews_count ?? 0;
                                        ?>
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i
                                                class="fas fa-star <?php echo e($i <= round($displayRating) ? 'text-warning' : 'text-muted'); ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="rating-score"><?php echo e(number_format($displayRating, 1)); ?></span>
                                    <span class="reviews-count">(<?php echo e($reviewCount); ?>)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- معلومات الموقع -->
                    <div class="location-section">
                        <div class="location-info" data-provider-id="<?php echo e($provider->id); ?>">
                            <i class="fas fa-map-marker-alt location-icon"></i>
                            <div class="address-text">
                                <?php if($provider->location): ?>
                                    <div class="mb-1 fw-bold text-primary"><?php echo e($provider->location->city); ?></div>
                                <?php endif; ?>
                                <span class="address-content hidden-address" style="display: block;">
                                    <?php if($provider->address): ?>
                                        <?php echo e(preg_replace('/\d/', '*', $provider->address)); ?>

                                    <?php else: ?>
                                        <?php echo e(__('service_provider.address_not_provided')); ?>

                                    <?php endif; ?>
                                </span>
                                <span class="address-content full-address" style="display: none;">
                                    <?php echo e($provider->address ?? __('service_provider.address_not_provided')); ?>

                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- الإحصائيات -->
                    <div class="stats-grid">
                        <div class="stat-item">
                            <i class="fas fa-eye stat-icon"></i>
                            <div class="stat-value"><?php echo e(number_format($provider->views)); ?></div>
                            <div class="stat-label"><?php echo e(__('service_provider.stat_views')); ?></div>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-thumbs-up stat-icon"></i>
                            <div class="stat-value" data-endorsements-count="<?php echo e($provider->id); ?>">
                                <?php echo e($provider->endorsements_count ?? 0); ?></div>
                            <div class="stat-label"><?php echo e(__('service_provider.stat_recommends')); ?></div>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-briefcase stat-icon"></i>
                            <div class="stat-value"><?php echo e($provider->experience_years ?? '0'); ?></div>
                            <div class="stat-label"><?php echo e(__('service_provider.stat_years')); ?></div>
                        </div>
                    </div>

                    <!-- أزرار التفاعل -->
                    <div class="card-footer">
                        <div class="action-buttons">
                            <?php if(auth()->check() && auth()->user()->isClient()): ?>
                                <?php
                                    $isEndorsed = $provider->isEndorsedBy(auth()->id());
                                ?>
                                <form action="<?php echo e(route('endorsements.toggle', $provider->id)); ?>" method="POST"
                                    style="display: inline;">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit"
                                        class="btn-action btn-recommend <?php echo e($isEndorsed ? 'recommended' : ''); ?>">
                                        <i class="<?php echo e($isEndorsed ? 'fas' : 'far'); ?> fa-thumbs-up"></i>
                                        <span><?php echo e($isEndorsed ? __('service_provider.recommended') : __('service_provider.recommend')); ?></span>
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="btn-action btn-recommend" disabled>
                                    <i class="far fa-thumbs-up"></i>
                                    <span><?php echo e(__('service_provider.recommend')); ?></span>
                                </button>
                            <?php endif; ?>

                            <?php if(auth()->check()): ?>
                                <button class="btn-action btn-rate"
                                    onclick="openRateModal(<?php echo e($provider->id); ?>, '<?php echo e(addslashes($provider->company_name ?? $provider->user->name)); ?>')">
                                    <i class="fas fa-star"></i>
                                    <span><?php echo e(__('service_provider.rate_provider')); ?></span>
                                </button>
                            <?php else: ?>
                                <a href="<?php echo e(route('register')); ?>?redirect=<?php echo e(urlencode(route('reviews.create', $provider->id))); ?>"
                                    class="btn-action btn-rate">
                                    <i class="fas fa-star"></i>
                                    <span><?php echo e(__('service_provider.rate_provider')); ?></span>
                                </a>
                            <?php endif; ?>
                        </div>

                        <a href="<?php echo e(route('service-providers.show', $provider)); ?>" class="btn-profile">
                            <i class="fas fa-user-circle"></i>
                            <?php echo e(__('service_provider.view_full_profile')); ?>

                        </a>

                        <!-- <?php if($provider->experience_years): ?>
                            <div class="experience-badge">
                                <i class="fas fa-briefcase"></i>
                                <span><?php echo e($provider->experience_years); ?> <?php echo e(__('service_provider.years')); ?> Experience</span>
                            </div>
                        <?php endif; ?> -->
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <!-- حالة عدم وجود نتائج محسّنة -->
                <div class="empty-state">
                    <div class="empty-illustration">
                        <i class="fas fa-users"></i>
                    </div>

                    <h2 class="empty-title"><?php echo e(__('service_provider.no_providers_found')); ?></h2>
                    <p class="empty-description"><?php echo e(__('service_provider.no_providers_description')); ?></p>

                    <div class="empty-actions">
                        <button class="btn-primary" onclick="resetFilters()">
                            <i class="fas fa-redo me-2"></i>
                            <?php echo e(__('service_provider.reset_filters')); ?>

                        </button>
                        <a href="<?php echo e(route('home')); ?>" class="btn-back">
                            <i class="fas fa-home me-2"></i>
                            Return Home
                        </a>
                    </div>

                    <div class="suggestions-section">
                        <h3 class="suggestions-title"><?php echo e(__('service_provider.or_try_browsing')); ?></h3>
                        <div class="suggestions-grid">
                            <a href="<?php echo e(route('categories')); ?>" class="suggestion-card">
                                <div class="suggestion-icon">
                                    <i class="fas fa-th-large"></i>
                                </div>
                                <div class="suggestion-text"><?php echo e(__('categories.popular_categories')); ?></div>
                            </a>
                            <a href="<?php echo e(route('location')); ?>" class="suggestion-card">
                                <div class="suggestion-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="suggestion-text"><?php echo e(__('location.nearby_locations')); ?></div>
                            </a>
                            <a href="<?php echo e(route('home')); ?>" class="suggestion-card">
                                <div class="suggestion-icon">
                                    <i class="fas fa-star"></i>
                                </div>
                                <div class="suggestion-text"><?php echo e(__('service_provider.top_rated')); ?></div>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- الترقيم الصفحي -->
        <?php if($serviceProviders->hasPages()): ?>
            <div class="d-flex justify-content-center mt-5">
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <?php echo e($serviceProviders->links()); ?>

                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal للتقييم -->
    <div id="rateModal" class="rate-modal">
        <div class="rate-modal-content">
            <div class="rate-modal-header">
                <h2><i class="fas fa-star me-2 text-warning"></i><span id="providerNameInModal"></span></h2>
                <button class="rate-modal-close" onclick="closeRateModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="rate-modal-body">
                <form id="rateForm" method="POST" action="<?php echo e(route('reviews.store')); ?>">
                    <?php echo csrf_field(); ?>

                    <input type="hidden" id="providerIdInput" name="provider_id">

                    <!-- نظام التقييم بالنجوم -->
                    <div class="rating-section">
                        <label class="rating-label"><?php echo e(__('reviews.rating')); ?> <span
                                class="text-danger">*</span></label>
                        <div class="star-rating" id="starRating">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <input type="radio" id="rating-<?php echo e($i); ?>" name="rating" value="<?php echo e($i); ?>" required>
                                <label for="rating-<?php echo e($i); ?>" class="star-label">
                                    <i class="fas fa-star"></i>
                                </label>
                            <?php endfor; ?>
                        </div>
                        <p class="rating-text" id="ratingText"><?php echo e(__('reviews.select_your_rating')); ?></p>
                    </div>

                    <!-- حقل التقييم -->
                    <div class="form-group mb-4">
                        <label for="review_text" class="form-label"><?php echo e(__('reviews.review_text')); ?> <span
                                class="text-danger">*</span></label>
                        <textarea id="review_text" name="review_text" class="form-control review-textarea" rows="5"
                            placeholder="<?php echo e(__('reviews.review_placeholder')); ?>" required minlength="10"
                            maxlength="1000"></textarea>
                        <div class="char-count">
                            <span id="charCount">0</span> / 1000
                        </div>
                    </div>

                    <!-- أزرار التصرف -->
                    <div class="rate-modal-actions">
                        <button type="button" class="btn-cancel" onclick="closeRateModal()">
                            <i class="fas fa-times me-2"></i> <?php echo e(__('general.cancel')); ?>

                        </button>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-check me-2"></i> <?php echo e(__('reviews.submit_review')); ?>

                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- سكريبتات JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // عناصر الفلاتر
            const searchInput = document.getElementById('searchInput');
            const locationFilter = document.getElementById('locationFilter');
            const categoryFilter = document.getElementById('categoryFilter');

            // مؤقت للبحث
            let searchTimeout;

            // دالة تطبيق الفلاتر
            function applyFilters() {
                const params = new URLSearchParams(window.location.search);

                if (searchInput.value) {
                    params.set('search', searchInput.value);
                } else {
                    params.delete('search');
                }

                if (locationFilter.value) {
                    params.set('location', locationFilter.value);
                } else {
                    params.delete('location');
                }

                if (categoryFilter.value) {
                    params.set('category', categoryFilter.value);
                } else {
                    params.delete('category');
                }

                // تحديث URL وإعادة التحميل
                window.location.href = `${window.location.pathname}?${params.toString()}`;
            }

            // البحث مع تأخير
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(applyFilters, 600);
            });

            // تطبيق الفلاتر عند التغيير
            locationFilter.addEventListener('change', applyFilters);
            categoryFilter.addEventListener('change', applyFilters);

            // تأثير الفلاتر عند التركيز
            [searchInput, locationFilter, categoryFilter].forEach(element => {
                element.addEventListener('focus', function () {
                    this.style.transform = 'scale(1.02)';
                });

                element.addEventListener('blur', function () {
                    this.style.transform = 'scale(1)';
                });
            });

            // إعادة تعيين الفلاتر
            window.resetFilters = function () {
                searchInput.value = '';
                locationFilter.value = '';
                categoryFilter.value = '';
                applyFilters();
            };

            // كشف عناوين مقدمي الخدمات
            const revealedContacts = <?php echo json_encode($revealedContacts ?? [], 15, 512) ?>;
            revealedContacts.forEach(providerId => {
                const addressContainer = document.querySelector(`.location-info[data-provider-id="${providerId}"]`);
                if (addressContainer) {
                    const hiddenAddress = addressContainer.querySelector('.hidden-address');
                    const fullAddress = addressContainer.querySelector('.full-address');

                    if (hiddenAddress && fullAddress) {
                        hiddenAddress.style.display = 'none';
                        fullAddress.style.display = 'block';
                        addressContainer.classList.add('pulse');
                    }
                }
            });

            // تفعيل تأثير Hover على البطاقات
            const providerCards = document.querySelectorAll('.provider-card');
            providerCards.forEach(card => {
                card.addEventListener('mouseenter', function () {
                    this.style.zIndex = '10';
                });

                card.addEventListener('mouseleave', function () {
                    this.style.zIndex = '1';
                });
            });
        });

        // دوال Modal للتقييم
        document.addEventListener('DOMContentLoaded', function () {
            // ======================
            // دوال التحكم في الـ Modal
            // ======================
            window.openRateModal = function (providerId, providerName) {
                console.log('Opening modal for provider:', providerId, providerName);

                const modal = document.getElementById('rateModal');
                if (!modal) {
                    console.error('Modal element not found');
                    return;
                }

                // تحديث محتوى الـ Modal
                const updates = {
                    'providerNameInModal': el => el.textContent = providerName,
                    'providerIdInput': el => el.value = providerId,
                    'charCount': el => el.textContent = '0',
                    'ratingText': el => el.textContent = 'Select your rating'
                };

                Object.entries(updates).forEach(([id, fn]) => {
                    const el = document.getElementById(id);
                    if (el) fn(el);
                });

                // إعادة تعيين النموذج
                const rateForm = document.getElementById('rateForm');
                if (rateForm) rateForm.reset();

                // تفعيل الـ Modal
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
                console.log('Modal opened successfully');
            };

            window.closeRateModal = function () {
                const modal = document.getElementById('rateModal');
                if (modal) modal.classList.remove('active');
                document.body.style.overflow = 'auto';
            };

            // ======================
            // أحداث إغلاق الـ Modal
            // ======================
            const modal = document.getElementById('rateModal');
            if (modal) {
                // إغلاق عند الضغط خارج الـ Modal
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) window.closeRateModal();
                });

                // إغلاق بـ ESC
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && modal.classList.contains('active')) {
                        window.closeRateModal();
                    }
                });
            }

            // ======================
            // عداد الأحرف
            // ======================
            const reviewText = document.getElementById('review_text');
            const charCount = document.getElementById('charCount');
            if (reviewText && charCount) {
                reviewText.addEventListener('input', () => {
                    charCount.textContent = reviewText.value.length;
                });
            }

            // ======================
            // نظام التقييم بالنجوم
            // ======================
            const starContainers = document.querySelectorAll('.star-rating');
            const ratingTexts = {
                '1': <?php echo json_encode(__('reviews.poor'), 15, 512) ?>,
                '2': <?php echo json_encode(__('reviews.fair'), 15, 512) ?>,
                '3': <?php echo json_encode(__('reviews.good'), 15, 512) ?>,
                '4': <?php echo json_encode(__('reviews.very_good'), 15, 512) ?>,
                '5': <?php echo json_encode(__('reviews.excellent'), 15, 512) ?>
            };

            starContainers.forEach(container => {
                const labels = container.querySelectorAll('.star-label');
                const inputs = container.querySelectorAll('input');
                const ratingDisplay = document.getElementById('ratingText');

                // تحديث العرض عند التغيير
                inputs.forEach(input => {
                    input.addEventListener('change', () => {
                        const value = input.value;
                        // تحديث النجوم النشطة
                        labels.forEach((label, i) => {
                            label.classList.toggle('active', i < value);
                        });
                        // تحديث نص التقييم
                        if (ratingDisplay && ratingTexts[value]) {
                            ratingDisplay.textContent = ratingTexts[value];
                        }
                    });

                    // تأثير التمرير
                    input.addEventListener('mouseenter', () => {
                        const hoverValue = parseInt(input.value);
                        labels.forEach((label, i) => {
                            label.style.color = i < hoverValue ? 'var(--warning)' : 'var(--gray-300)';
                            label.style.transform = i < hoverValue ? 'scale(1.15)' : 'scale(1)';
                        });
                    });
                });

                // إعادة التعيين بعد مغادرة المنطقة
                container.addEventListener('mouseleave', () => {
                    const checked = container.querySelector('input:checked');
                    const currentValue = checked ? parseInt(checked.value) : 0;

                    labels.forEach((label, i) => {
                        label.style.color = i < currentValue ? 'var(--warning)' : 'var(--gray-300)';
                        label.style.transform = 'scale(1)';
                        label.classList.toggle('active', i < currentValue);
                    });
                });
            });

            // ======================
            // معالجة إرسال التقييم
            // ======================
            const rateForm = document.getElementById('rateForm');
            if (rateForm) {
                rateForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const submitBtn = rateForm.querySelector('.btn-submit');
                    if (!submitBtn) return;

                    const originalHTML = submitBtn.innerHTML;
                    const rating = rateForm.querySelector('input[name="rating"]:checked')?.value;
                    const reviewText = rateForm.querySelector('textarea[name="review_text"]')?.value.trim();

                    // التحقق من الصحة
                    if (!rating) {
                        alert('Please select a rating');
                        return;
                    }
                    if (!reviewText || reviewText.length < 10) {
                        alert('Please write a review (minimum 10 characters)');
                        return;
                    }

                    // إعداد الزر للإرسال
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Submitting...';

                    try {
                        // جلب CSRF Token بأمان
                        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                        if (!csrfMeta) throw new Error('CSRF token not found');

                        const response = await fetch(rateForm.action || '<?php echo e(route("reviews.store")); ?>', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfMeta.content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: new URLSearchParams(new FormData(rateForm))
                        });

                        const data = await response.json();

                        if (!response.ok) throw data;
                        if (!data.success) throw { message: data.message || 'Submission failed' };

                        // نجاح الإرسال: إغلاق الـ Modal وتحديث الواجهة
                        window.closeRateModal();
                        updateProviderCard(data.review, document.getElementById('providerIdInput')?.value);
                        showSuccessMessage('Your review has been submitted successfully!');

                    } catch (error) {
                        console.error('Submission error:', error);
                        const errorMsg = extractErrorMessage(error);
                        alert(`Submission failed: ${errorMsg}`);
                    } finally {
                        // استعادة حالة الزر
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalHTML;
                    }
                });
            }

            // ======================
            // دوال مساعدة
            // ======================
            function updateProviderCard(review, providerId) {
                if (!review || !providerId) return;

                document.querySelectorAll(`.provider-card[data-provider-id="${providerId}"]`).forEach(card => {
                    const starsContainer = card.querySelector('.stars');
                    const ratingScore = card.querySelector('.rating-score');
                    const reviewsCount = card.querySelector('.reviews-count');

                    if (starsContainer) {
                        const rating = Math.round(review.rating || 0);
                        starsContainer.innerHTML = Array.from({ length: 5 }, (_, i) =>
                            `<i class="fas fa-star ${i < rating ? 'text-warning' : 'text-muted'}"></i>`
                        ).join('');
                    }

                    if (ratingScore) ratingScore.textContent = (review.rating || 0).toFixed(1);
                    if (reviewsCount) reviewsCount.textContent = `(${review.reviews_count || 0})`;
                });
            }

            function showSuccessMessage(message) {
                const container = document.querySelector('.page-shell') || document.body;
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show';
                alert.style.cssText = 'margin:1rem; position:fixed; top:1rem; right:1rem; z-index:9999;';
                alert.innerHTML = `
            <strong>Success!</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
                container.appendChild(alert);

                // إزالة تلقائية بعد 5 ثوانٍ
                setTimeout(() => {
                    alert.classList.remove('show');
                    alert.classList.add('fade');
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            }

            function extractErrorMessage(error) {
                if (typeof error === 'string') return error;
                if (error?.errors) return Object.values(error.errors).flat().join(', ');
                if (error?.message) return error.message;
                if (error?.statusText) return error.statusText;
                return 'An unexpected error occurred';
            }
        });

        // تأثير تحميل الصفحة
        window.addEventListener('load', function () {
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.5s ease';

            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);
        });
    </script>

    <?php echo $__env->make('layouts.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

</body>

</html><?php /**PATH Y:\Speeda - Versions\Speeda\resources\views/service-providers/index.blade.php ENDPATH**/ ?>