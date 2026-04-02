<?php ($user = auth()->user()); ?>

<?php if (! $__env->hasRenderedOnce('35da3a35-5e7b-4b49-bdcd-3c42a46a9b5f')): $__env->markAsRenderedOnce('35da3a35-5e7b-4b49-bdcd-3c42a46a9b5f'); ?>
    <style>
        /* ===============================================
           النظام اللوني الرباعي المتطور
           =============================================== */
        :root {
            /* الألوان الأساسية - Primary Colors */
            --primary-base: #3b82f6;
            --primary-50: #eff6ff;
            --primary-100: #dbeafe;
            --primary-200: #bfdbfe;
            --primary-300: #93c5fd;
            --primary-400: #60a5fa;
            --primary-500: #3b82f6;
            --primary-600: #2563eb;
            --primary-700: #1d4ed8;
            --primary-800: #1e40af;
            --primary-900: #1e3a8a;

            /* ألوان النجاح - Success Colors */
            --success-base: #10b981;
            --success-50: #ecfdf5;
            --success-100: #d1fae5;
            --success-200: #a7f3d0;
            --success-300: #6ee7b7;
            --success-400: #34d399;
            --success-500: #10b981;
            --success-600: #059669;
            --success-700: #047857;
            --success-800: #065f46;
            --success-900: #064e3b;

            /* ألوان التحذير - Warning Colors */
            --warning-base: #f59e0b;
            --warning-50: #fffbeb;
            --warning-100: #fef3c7;
            --warning-200: #fde68a;
            --warning-300: #fcd34d;
            --warning-400: #fbbf24;
            --warning-500: #f59e0b;
            --warning-600: #d97706;
            --warning-700: #b45309;
            --warning-800: #92400e;
            --warning-900: #78350f;

            /* ألوان الخطأ - Danger Colors */
            --danger-base: #ef4444;
            --danger-50: #fef2f2;
            --danger-100: #fee2e2;
            --danger-200: #fecaca;
            --danger-300: #fca5a5;
            --danger-400: #f87171;
            --danger-500: #ef4444;
            --danger-600: #dc2626;
            --danger-700: #b91c1c;
            --danger-800: #991b1b;
            --danger-900: #7f1d1d;

            /* ألوان النصوص */
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-light: #94a3b8;
            --text-white: #ffffff;

            /* خلفيات متدرجة */
            --bg-white: #ffffff;
            --bg-glass: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 250, 252, 0.92));
            --bg-glass-solid: rgba(255, 255, 255, 0.95);
            --bg-subtle: linear-gradient(135deg, #f8fafc, #f1f5f9);
            --bg-card: linear-gradient(135deg, #ffffff, #f9fafb);

            /* حدود وظلال */
            --border-color: #e2e8f0;
            --border-gradient: linear-gradient(135deg, #e2e8f0, #cbd5e1);
            --shadow-soft: 0 2px 8px rgba(15, 23, 42, 0.04);
            --shadow-md: 0 8px 24px rgba(15, 23, 42, 0.08);
            --shadow-lg: 0 16px 40px rgba(15, 23, 42, 0.12);
            --shadow-xl: 0 24px 64px rgba(15, 23, 42, 0.16);
            --shadow-glow: 0 0 32px rgba(99, 102, 241, 0.2);
            --shadow-glow-hover: 0 8px 40px rgba(99, 102, 241, 0.35);

            /* تدرجات مخصصة */
            --gradient-primary: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
            --gradient-secondary: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 50%, #14b8a6 100%);
            --gradient-soft: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(139, 92, 246, 0.08));
            --gradient-hover: linear-gradient(135deg, rgba(99, 102, 241, 0.12), rgba(168, 85, 247, 0.15));
        }

        /* وضع الليل المحسّن */
        body.dark-mode {
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-light: #94a3b8;
            --bg-white: #0f172a;
            --bg-glass: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.92));
            --bg-glass-solid: rgba(15, 23, 42, 0.95);
            --bg-subtle: linear-gradient(135deg, #1e293b, #334155);
            --bg-card: linear-gradient(135deg, #1e293b, #293548);
            --border-color: #334155;
            --shadow-soft: 0 2px 8px rgba(0, 0, 0, 0.2);
            --shadow-md: 0 8px 24px rgba(0, 0, 0, 0.3);
            --shadow-lg: 0 16px 40px rgba(0, 0, 0, 0.4);
            --shadow-xl: 0 24px 64px rgba(0, 0, 0, 0.5);
        }

        /* --- شريط التنقل الرئيسي المحسّن --- */
        .sp-nav {
            position: sticky;
            top: 0;
            z-index: 1100;
            background: var(--bg-glass-solid);
            backdrop-filter: blur(32px) saturate(180%);
            -webkit-backdrop-filter: blur(32px) saturate(180%);
            border-bottom: 1px solid rgba(226, 232, 240, 0.4);
            box-shadow: var(--shadow-soft);
            width: 100%;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            animation: navSlideDown 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes navSlideDown {
            0% {
                transform: translateY(-120%);
                opacity: 0;
                filter: blur(10px);
            }
            60% {
                transform: translateY(5px);
                opacity: 0.8;
            }
            100% {
                transform: translateY(0);
                opacity: 1;
                filter: blur(0);
            }
        }

        .sp-nav.scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: var(--shadow-md);
            backdrop-filter: blur(20px);
            border-bottom-color: var(--border-color);
        }

        .sp-nav-inner {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1rem 2.5rem;
            display: flex;
            align-items: center;
            gap: 3rem;
            transition: padding 0.3s ease;
        }

        /* Keep inner padding stable to avoid layout shifts when toggling .scrolled */
        .sp-nav.scrolled .sp-nav-inner {
            /* no padding change here to prevent content jump on scroll */
            padding: 1rem 2.5rem;
        }

        /* --- منطقة اللوجو المحسّنة --- */
        .sp-brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            flex-shrink: 0;
            margin-right: 1.5rem;
            animation: logoAppear 1s cubic-bezier(0.34, 1.56, 0.64, 1) 0.3s both;
            position: relative;
        }

        @keyframes logoAppear {
            0% {
                opacity: 0;
                transform: scale(0.8) rotate(-5deg);
                filter: blur(8px);
            }
            60% {
                transform: scale(1.05) rotate(2deg);
            }
            100% {
                opacity: 1;
                transform: scale(1) rotate(0deg);
                filter: blur(0);
            }
        }

        .sp-brand::before {
            content: '';
            position: absolute;
            inset: -8px;
            background: var(--gradient-primary);
            border-radius: 20px;
            opacity: 0;
            transition: opacity 0.4s ease;
            filter: blur(20px);
            z-index: -1;
        }

        .sp-brand:hover::before {
            opacity: 0.3;
        }

        .sp-brand:hover {
            transform: scale(1.1) translateY(-3px) rotate(2deg);
        }

        .sp-brand img {
            height: 110px;
            width: auto;
            filter: drop-shadow(0 4px 12px rgba(59, 130, 246, 0.12));
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.35s ease;
            transform-origin: left center;
            transform: none;
        }

        /* Use transform (no layout change) instead of changing the image height to avoid navbar reflow */
        .sp-nav.scrolled .sp-brand img {
            transform: scale(0.94);
        }

        .sp-brand:hover img {
            filter: drop-shadow(0 12px 32px rgba(99, 102, 241, 0.4)) brightness(1.05);
            transform: translateY(-4px);
        }

        /* --- زر القائمة للموبايل المحسّن --- */
        .sp-nav-toggle {
            margin-left: auto;
            background: var(--bg-card);
            border: 2px solid var(--border-color);
            border-radius: 18px;
            padding: 14px;
            display: none;
            cursor: pointer;
            color: var(--text-primary);
            flex-direction: column;
            gap: 6px;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: var(--shadow-soft);
            position: relative;
            overflow: hidden;
        }

        .sp-nav-toggle::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--gradient-primary);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .sp-nav-toggle:hover::before {
            opacity: 0.08;
        }

        .sp-nav-toggle:hover {
            border-color: var(--primary-color);
            box-shadow: var(--shadow-glow);
            transform: translateY(-3px) scale(1.05);
        }

        .sp-nav-toggle span {
            display: block;
            width: 28px;
            height: 3px;
            background: var(--gradient-primary);
            border-radius: 4px;
            transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            position: relative;
            z-index: 1;
        }

        .sp-nav.is-open .sp-nav-toggle span:nth-child(1) {
            transform: rotate(45deg) translate(6px, 6px);
        }
        .sp-nav.is-open .sp-nav-toggle span:nth-child(2) {
            opacity: 0;
            transform: scale(0);
        }
        .sp-nav.is-open .sp-nav-toggle span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -7px);
        }

        /* --- الروابط --- */
        .sp-nav-links {
            display: flex;
            align-items: center;
            gap: 2.5rem;
            margin-left: auto;
            flex: 1;
        }

        .sp-menu-links {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .sp-link {
            text-decoration: none;
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 0.9375rem;
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 0.75rem 1.25rem;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            overflow: hidden;
            white-space: nowrap;
        }

        .sp-link i {
            font-size: 1.0625rem;
            transition: transform 0.3s ease;
        }

        .sp-link::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--gradient-soft);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .sp-link::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: 10px;
            width: 0;
            height: 3px;
            background: var(--gradient-primary);
            border-radius: 3px;
            transform: translateX(-50%);
            transition: width 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.4);
        }

        .sp-link:hover::before {
            opacity: 1;
        }

        .sp-link:hover {
            color: var(--primary-600);
            transform: translateY(-2px);
        }

        .sp-link:hover i {
            transform: scale(1.15);
        }

        .sp-link:hover::after {
            width: calc(100% - 20px);
        }

        .sp-link.is-active {
            color: var(--primary-600);
            font-weight: 700;
            background: rgba(59, 130, 246, 0.08);
        }

        .sp-link.is-active::after {
            width: calc(100% - 20px);
        }

        .sp-link.is-active i {
            transform: scale(1.1);
        }

        .sp-link:active {
            transform: translateY(0);
        }

        /* --- الأزرار والإجراءات --- */
        .sp-actions {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .sp-pill, .sp-outline-pill {
            border-radius: 12px;
            padding: 0.75rem 1.75rem;
            font-weight: 600;
            font-size: 0.9375rem;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            position: relative;
            overflow: hidden;
            white-space: nowrap;
        }

        .sp-pill i, .sp-outline-pill i {
            font-size: 1rem;
            transition: transform 0.3s ease;
        }

        .sp-pill::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), transparent);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .sp-pill:hover::before {
            opacity: 1;
        }

        .sp-pill {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.25);
        }

        .sp-pill:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
            color: white;
        }

        .sp-pill:hover i {
            transform: scale(1.15);
        }

        .sp-pill:active {
            transform: translateY(0);
        }

        .sp-outline-pill {
            border: 2px solid var(--border-color);
            color: var(--text-secondary);
            background: var(--bg-card);
        }

        .sp-outline-pill::after {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--gradient-soft);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .sp-outline-pill:hover::after {
            opacity: 1;
        }

        .sp-outline-pill:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            transform: translateY(-4px) scale(1.02);
            box-shadow: var(--shadow-glow);
        }

        /* --- معلومات المستخدم المحسّنة --- */
        .sp-user-info {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 0.5rem 1rem 0.5rem 0.5rem;
            border-radius: 12px;
            background: white;
            border: 1.5px solid var(--border-strong);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .sp-user-info::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--gradient-soft);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .sp-user-info:hover::before {
            opacity: 1;
        }

        .sp-user-info:hover {
            border-color: var(--primary-500);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        }

        .sp-user-info:hover .sp-user-avatar {
            transform: scale(1.08);
        }

        .sp-user-info-name {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.9375rem;
            line-height: 1.4;
        }
        .sp-user-info-email {
            color: var(--text-tertiary);
            font-size: 0.8125rem;
            margin-top: 2px;
            line-height: 1.3;
        }

        .sp-user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid var(--primary-100);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sp-logout-button {
            background: white;
            border: 1.5px solid var(--danger-200);
            color: var(--danger-600);
            font-weight: 600;
            cursor: pointer;
            padding: 0.75rem 1.75rem;
            border-radius: 12px;
            font-size: 0.9375rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            position: relative;
            overflow: hidden;
            white-space: nowrap;
        }

        .sp-logout-button i {
            font-size: 1rem;
            transition: transform 0.3s ease;
        }

        .sp-logout-button::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(220, 38, 38, 0.1), transparent);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .sp-logout-button:hover::before {
            opacity: 1;
        }

        .sp-logout-button:hover {
            background: var(--danger-50);
            border-color: var(--danger-500);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
        }

        .sp-logout-button:hover i {
            transform: scale(1.15);
        }

        .sp-logout-button:active {
            transform: translateY(0);
        }

        /* --- شريط البحث المحسّن --- */
        .sp-search-container {
            position: relative;
            flex-grow: 1;
            max-width: 480px;
        }

        .sp-search-input {
            width: 100%;
            padding: 1rem 4rem 1rem 4rem;
            border: 2px solid var(--border-color);
            border-radius: 50px;
            font-size: 0.95rem;
            background: var(--bg-subtle);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            color: var(--text-primary);
            font-weight: 500;
        }

        .sp-search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            background: var(--bg-white);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12), var(--shadow-glow);
            transform: translateY(-3px) scale(1.01);
        }

        .sp-search-icon {
            position: absolute;
            left: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            pointer-events: none;
            font-size: 1.15rem;
            transition: all 0.3s ease;
        }

        .sp-search-input:focus ~ .sp-search-icon {
            color: var(--primary-color);
            transform: translateY(-50%) scale(1.1);
        }

        .sp-search-clear {
            position: absolute;
            right: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            cursor: pointer;
            background: var(--bg-subtle);
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: none;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .sp-search-input:not(:placeholder-shown) ~ .sp-search-clear {
            display: flex;
        }

        .sp-search-clear:hover {
            background: var(--danger-color);
            color: white;
            border-color: white;
            transform: translateY(-50%) scale(1.15) rotate(90deg);
        }

        /* نتائج البحث المحسّنة */
        .sp-search-results {
            position: absolute;
            top: 115%;
            left: 0;
            right: 0;
            background: var(--bg-white);
            border: 2px solid var(--border-color);
            border-radius: 20px;
            box-shadow: var(--shadow-xl);
            max-height: 450px;
            overflow-y: auto;
            display: none;
            z-index: 1000;
            overflow: hidden;
        }

        .sp-search-results.active {
            display: block;
            animation: searchResultsAppear 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes searchResultsAppear {
            0% {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
                filter: blur(4px);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
                filter: blur(0);
            }
        }

        .sp-search-result-item {
            padding: 1.25rem;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            display: flex;
            align-items: center;
            gap: 1.25rem;
            position: relative;
            overflow: hidden;
        }

        .sp-search-result-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--gradient-primary);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .sp-search-result-item:hover::before {
            transform: scaleY(1);
        }

        .sp-search-result-item:last-child {
            border-bottom: none;
        }

        .sp-search-result-item:hover {
            background: var(--gradient-soft);
            transform: translateX(8px);
        }

        /* --- نظام الإشعارات المحسّن --- */
        .sp-notification-bell {
            position: relative;
            cursor: pointer;
            padding: 0.875rem;
            border-radius: 50%;
            background: var(--bg-card);
            border: 2px solid var(--border-color);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sp-notification-bell::before {
            content: '';
            position: absolute;
            inset: -4px;
            background: var(--gradient-primary);
            border-radius: 50%;
            opacity: 0;
            transition: opacity 0.4s ease;
            filter: blur(12px);
        }

        .sp-notification-bell:hover::before {
            opacity: 0.3;
        }

        .sp-notification-bell:hover {
            background: var(--gradient-soft);
            border-color: var(--primary-color);
            transform: rotate(20deg) scale(1.15);
        }

        .sp-notification-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            font-size: 0.7rem;
            font-weight: 800;
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            min-width: 22px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.5);
            animation: notificationPulse 2.5s ease-in-out infinite;
        }

        @keyframes notificationPulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 4px 12px rgba(239, 68, 68, 0.5);
            }
            50% {
                transform: scale(1.15);
                box-shadow: 0 6px 20px rgba(239, 68, 68, 0.7);
            }
        }

        .sp-notification-dropdown {
            position: absolute;
            top: 125%;
            right: 0;
            width: 400px;
            background: var(--bg-white);
            border: 2px solid var(--border-color);
            border-radius: 24px;
            box-shadow: var(--shadow-xl);
            display: none;
            z-index: 1000;
            overflow: hidden;
        }

        .sp-notification-dropdown.active {
            display: block;
            animation: searchResultsAppear 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .sp-notification-header {
            padding: 1.5rem;
            border-bottom: 2px solid var(--border-color);
            font-weight: 800;
            color: var(--text-primary);
            background: var(--gradient-soft);
            letter-spacing: 0.5px;
        }

        .sp-notification-list {
            max-height: 420px;
            overflow-y: auto;
        }

        .sp-notification-item {
            padding: 1.25rem;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .sp-notification-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 0;
            background: var(--gradient-primary);
            transition: width 0.3s ease;
        }

        .sp-notification-item:hover::before {
            width: 4px;
        }

        .sp-notification-item:hover {
            background: var(--bg-subtle);
            transform: translateX(6px);
        }

        .sp-notification-item.unread {
            background: var(--gradient-soft);
            border-left: 4px solid var(--primary-color);
            font-weight: 600;
        }

        /* --- زر الوضع الليلي المحسّن --- */
        .sp-dark-mode-toggle {
            position: relative;
            width: 64px;
            height: 36px;
            background: var(--bg-card);
            border: 2px solid var(--border-color);
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.4s ease;
            display: flex;
            align-items: center;
            padding: 0 4px;
        }

        .sp-dark-mode-toggle:hover {
            border-color: var(--primary-color);
            box-shadow: var(--shadow-glow);
            transform: scale(1.05);
        }

        .sp-dark-mode-slider {
            width: 26px;
            height: 26px;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            border-radius: 50%;
            transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            box-shadow: 0 4px 12px rgba(251, 191, 36, 0.6);
        }

        body.dark-mode .sp-dark-mode-slider {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            transform: translateX(28px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.6);
        }

        /* --- شريط التقدم للتمرير المحسّن --- */
        .sp-scroll-progress {
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 5px;
            background: var(--gradient-primary);
            z-index: 9999;
            transition: width 0.1s ease;
            box-shadow: 0 3px 12px rgba(99, 102, 241, 0.6);
        }

        /* --- Mega Menu للفئات المحسّن --- */
        .sp-mega-menu-trigger {
            position: relative;
        }

        .sp-mega-menu {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            width: 820px;
            max-width: 90vw;
            background: var(--bg-white);
            border: 2px solid var(--border-color);
            border-radius: 28px;
            box-shadow: var(--shadow-xl);
            padding: 2.5rem;
            display: none;
            z-index: 1000;
            margin-top: 1.25rem;
            overflow: hidden;
        }

        .sp-mega-menu::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--gradient-soft);
            opacity: 0.5;
            pointer-events: none;
        }

        .sp-mega-menu.active {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.75rem;
            animation: megaMenuAppear 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes megaMenuAppear {
            0% {
                opacity: 0;
                transform: translateX(-50%) translateY(-30px) scale(0.9);
                filter: blur(8px);
            }
            100% {
                opacity: 1;
                transform: translateX(-50%) translateY(0) scale(1);
                filter: blur(0);
            }
        }

        .sp-mega-menu-item {
            padding: 1.25rem;
            border-radius: 16px;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            text-decoration: none;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
            z-index: 1;
            background: var(--bg-white);
            border: 2px solid transparent;
        }

        .sp-mega-menu-item::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--gradient-hover);
            opacity: 0;
            transition: opacity 0.4s ease;
            border-radius: 14px;
            z-index: -1;
        }

        .sp-mega-menu-item:hover::before {
            opacity: 1;
        }

        .sp-mega-menu-item:hover {
            border-color: var(--primary-color);
            transform: translateY(-4px) scale(1.02);
            box-shadow: var(--shadow-md);
        }

        .sp-mega-menu-icon {
            font-size: 2rem;
            width: 52px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--gradient-soft);
            border-radius: 14px;
            transition: all 0.4s ease;
        }

        .sp-mega-menu-item:hover .sp-mega-menu-icon {
            transform: scale(1.15) rotate(5deg);
        }

        /* --- دردشة سريعة محسّنة --- */
        .sp-quick-chat {
            position: fixed;
            bottom: 35px;
            right: 35px;
            z-index: 1000;
        }

        .sp-chat-bubble {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.6rem;
            cursor: pointer;
            box-shadow: 0 12px 32px rgba(16, 185, 129, 0.5);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            animation: chatBounce 3s ease-in-out infinite;
            border: 4px solid rgba(255, 255, 255, 0.3);
        }

        @keyframes chatBounce {
            0%, 100% { transform: translateY(0) scale(1); }
            25% { transform: translateY(-12px) scale(1.05); }
            50% { transform: translateY(0) scale(1); }
            75% { transform: translateY(-6px) scale(1.02); }
        }

        .sp-chat-bubble:hover {
            transform: scale(1.15) rotate(-5deg);
            box-shadow: 0 16px 48px rgba(16, 185, 129, 0.7);
            animation: none;
        }

        .sp-chat-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            font-size: 0.75rem;
            font-weight: 800;
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            min-width: 24px;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.6);
            animation: notificationPulse 2.5s ease-in-out infinite;
        }

        /* --- تجاوب الشاشات المحسّن --- */
        @media (max-width: 1200px) {
            .sp-search-container { display: none; }
            .sp-nav-inner { gap: 2.5rem; }
        }

        @media (max-width: 768px) {
            .sp-nav-inner {
                flex-direction: row;
                justify-content: space-between;
                padding: 1.25rem 1.75rem;
                gap: 1.25rem;
            }
            .sp-brand img { height: 67.2px; }
            .sp-nav-toggle { display: flex; margin-left: 0; }
            .sp-nav-links {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: var(--bg-white);
                border-top: 2px solid var(--border-color);
                box-shadow: var(--shadow-xl);
                flex-direction: column;
                align-items: stretch;
                padding: 2.5rem;
                gap: 2rem;
                display: none;
                animation: mobileMenuSlide 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            }
            @keyframes mobileMenuSlide {
                from {
                    opacity: 0;
                    transform: translateY(-20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            .sp-nav.is-open .sp-nav-links { display: flex; }
            .sp-menu-links {
                flex-direction: column;
                align-items: stretch;
                width: 100%;
                gap: 0.75rem;
            }
            .sp-link {
                padding: 1.25rem;
                border-radius: 14px;
                width: 100%;
                font-size: 1.05rem;
            }
            .sp-actions {
                flex-direction: column;
                width: 100%;
                gap: 1.25rem;
            }
            .sp-user-info {
                width: 100%;
                justify-content: center;
                padding: 1.25rem;
            }
            .sp-pill, .sp-outline-pill, .sp-logout-button {
                width: 100%;
                justify-content: center;
                padding: 1.25rem;
            }
        }
    </style>
<?php endif; ?>

<?php if (! $__env->hasRenderedOnce('3395496b-22ce-4458-abac-1199eae16755')): $__env->markAsRenderedOnce('3395496b-22ce-4458-abac-1199eae16755'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const nav = document.querySelector('.sp-nav');
            const navToggle = nav.querySelector('[data-sp-nav-toggle]');

            // Toggle Mobile Menu
            navToggle?.addEventListener('click', () => {
                nav.classList.toggle('is-open');
                document.body.style.overflow = nav.classList.contains('is-open') ? 'hidden' : '';
            });

            // Sticky Header Effect on Scroll
            let lastScroll = 0;
            window.addEventListener('scroll', () => {
                const currentScroll = window.pageYOffset;
                if (currentScroll > 80) {
                    nav.classList.add('scrolled');
                } else {
                    nav.classList.remove('scrolled');
                }
                lastScroll = currentScroll;
            });

            // Close mobile menu when clicking outside
            document.addEventListener('click', (e) => {
                if (nav.classList.contains('is-open') && !nav.contains(e.target) && !e.target.closest('[data-sp-nav-toggle]')) {
                    nav.classList.remove('is-open');
                    document.body.style.overflow = '';
                }
            });
        });
    </script>
<?php endif; ?>

<nav class="sp-nav" data-sp-nav>
    <div class="sp-nav-inner">
        <!-- اللوجو الموسع والمكبر -->
        <a class="sp-brand" href="<?php echo e(route('home')); ?>">
            <img src="<?php echo e(asset('images/main-logo.png')); ?>" alt="Speeda Logo">
        </a>

        <!-- زر القائمة للموبايل -->
        <button class="sp-nav-toggle" type="button" data-sp-nav-toggle aria-label="<?php echo e(__('general.toggle_navigation')); ?>">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <div class="sp-nav-links">
            <div class="sp-menu-links">
                <a class="sp-link <?php echo e(request()->routeIs('home') ? 'is-active' : ''); ?>" href="<?php echo e(route('home')); ?>">
                    <i class="fas fa-home"></i>
                    <?php echo e(__('general.home')); ?>

                </a>
                <a class="sp-link <?php echo e(request()->routeIs('location') ? 'is-active' : ''); ?>" href="<?php echo e(route('location')); ?>">
                    <i class="fas fa-map-marker-alt"></i>
                    <?php echo e(__('general.locations')); ?>

                </a>
                <a class="sp-link <?php echo e(request()->routeIs('categories') ? 'is-active' : ''); ?>" href="<?php echo e(route('categories')); ?>">
                    <i class="fas fa-th-large"></i>
                    <?php echo e(__('general.categories')); ?>

                </a>
                <a class="sp-link <?php echo e(request()->routeIs('service-providers.*') ? 'is-active' : ''); ?>" href="<?php echo e(route('service-providers.index')); ?>">
                    <i class="fas fa-users"></i>
                    <?php echo e(__('service_provider.service_providers')); ?>

                </a>
            </div>

            <!-- شريط البحث الجديد -->
            <!-- <div class="sp-search-container">
                <i class="fas fa-search sp-search-icon"></i>
                <input type="text" class="sp-search-input" placeholder="<?php echo e(__('general.search_for_services')); ?>">
            </div> -->

            <div class="sp-actions">
                <!-- Language Switcher -->
                <?php echo $__env->make('components.language-switcher', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <?php if(auth()->guard()->check()): ?>
                    <div class="sp-user-info">
                        <img src="<?php echo e($user->profile_photo_url ?? asset('images/user.png')); ?>" alt="<?php echo e($user->name); ?>" class="sp-user-avatar">
                        <div>
                            <span class="sp-user-info-name"><?php echo e($user->name); ?></span>
                            <div class="sp-user-info-email"><?php echo e($user->email); ?></div>
                        </div>
                    </div>
                    <?php ($provider = $user->serviceProvider); ?>
                    <?php if($provider): ?>
                        
                        <a href="<?php echo e(route('service-providers.show', $provider)); ?>" class="sp-pill <?php echo e(request()->url() === route('service-providers.show', $provider) ? 'is-active' : ''); ?>">
                            <i class="fas fa-id-card"></i>
                            <?php echo e(__('general.my_profile')); ?>

                        </a>
                    <?php elseif(auth()->user()->isAdmin()): ?>
                        
                        <a href="<?php echo e(route('admin.dashboard')); ?>" class="sp-pill">
                            <i class="fas fa-tachometer-alt"></i>
                            <?php echo e(__('admin.dashboard')); ?>

                        </a>
                    <?php else: ?>
                        
                        <a href="<?php echo e(route('dashboard')); ?>" class="sp-pill">
                            <i class="fas fa-tachometer-alt"></i>
                            <?php echo e(__('general.dashboard')); ?>

                        </a>
                    <?php endif; ?>
                    <form method="POST" action="<?php echo e(route('logout')); ?>" style="display: inline;">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="sp-logout-button">
                            <i class="fas fa-sign-out-alt"></i>
                            <?php echo e(__('general.logout')); ?>

                        </button>
                    </form>
                <?php else: ?>
                    <!-- <a href="<?php echo e(route('login')); ?>" class="sp-outline-pill">
                        <i class="fas fa-sign-in-alt"></i>
                        <?php echo e(__('general.login')); ?>

                    </a> -->
                    <a href="<?php echo e(route('register')); ?>" class="sp-pill">
                        <i class="fas fa-user-plus"></i>
                        <?php echo e(__('general.register')); ?>

                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<?php /**PATH Y:\Speeda - Versions\Speeda\resources\views/components/main-nav.blade.php ENDPATH**/ ?>