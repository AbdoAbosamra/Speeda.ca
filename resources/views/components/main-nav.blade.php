@php
    $user = auth()->user();
    $provider = $user?->serviceProvider;
    $unreadCount = $unreadCount ?? 0;
    $hasUnread = $unreadCount > 0;
    $readIds = $readNotificationIds ?? [];
    $isServiceProvider = $user?->isServiceProvider();
@endphp

@once
    <style>
        /* ===============================================
               1. ROOT / DESIGN TOKENS
               =============================================== */
        :root {
            --space-1: 0.25rem;
            --space-2: 0.5rem;
            --space-3: 0.75rem;
            --space-4: 1rem;
            --space-5: 1.25rem;
            --space-6: 1.5rem;
            --space-8: 2rem;
            --space-10: 2.5rem;
            --space-12: 3rem;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --radius-full: 9999px;
            --transition-fast: 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-base: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-bounce: 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            --transition-smooth: 0.5s cubic-bezier(0.4, 0, 0.2, 1);
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
            --success-50: #ecfdf5;
            --success-500: #10b981;
            --success-600: #059669;
            --warning-500: #f59e0b;
            --danger-50: #fef2f2;
            --danger-100: #fee2e2;
            --danger-200: #fecaca;
            --danger-500: #ef4444;
            --danger-600: #dc2626;
            --danger-700: #b91c1c;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --text-inverse: #ffffff;
            --surface-white: #ffffff;
            --surface-glass: rgba(255, 255, 255, 0.95);
            --surface-subtle: #f8fafc;
            --surface-card: #f9fafb;
            --border-default: #e2e8f0;
            --border-subtle: rgba(226, 232, 240, 0.4);
            --shadow-xs: 0 1px 2px rgba(15, 23, 42, 0.04);
            --shadow-sm: 0 2px 8px rgba(15, 23, 42, 0.04);
            --shadow-md: 0 8px 24px rgba(15, 23, 42, 0.08);
            --shadow-lg: 0 16px 40px rgba(15, 23, 42, 0.12);
            --shadow-xl: 0 24px 64px rgba(15, 23, 42, 0.16);
            --gradient-primary: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
            --gradient-primary-subtle: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(139, 92, 246, 0.08));
            --gradient-primary-hover: linear-gradient(135deg, rgba(99, 102, 241, 0.12), rgba(168, 85, 247, 0.15));
            --gradient-success: linear-gradient(135deg, #10b981, #059669);
            --gradient-danger: linear-gradient(135deg, #ef4444, #dc2626);
            --gradient-danger-subtle: linear-gradient(135deg, rgba(220, 38, 38, 0.08), transparent);
            --glow-primary: 0 0 32px rgba(99, 102, 241, 0.2);
            --glow-primary-hover: 0 8px 40px rgba(99, 102, 241, 0.35);
            --glow-danger: 0 4px 12px rgba(239, 68, 68, 0.5);
            --glow-success: 0 12px 32px rgba(16, 185, 129, 0.5);
        }

        body.dark-mode {
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --surface-white: #0f172a;
            --surface-glass: rgba(15, 23, 42, 0.95);
            --surface-subtle: #1e293b;
            --surface-card: #1e293b;
            --border-default: #334155;
            --border-subtle: rgba(51, 65, 85, 0.4);
            --shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.15);
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.2);
            --shadow-md: 0 8px 24px rgba(0, 0, 0, 0.3);
            --shadow-lg: 0 16px 40px rgba(0, 0, 0, 0.4);
            --shadow-xl: 0 24px 64px rgba(0, 0, 0, 0.5);
        }

        .sp-nav {
            position: sticky;
            top: 0;
            z-index: 1300;
            width: 100%;
            background: var(--surface-glass);
            backdrop-filter: blur(32px) saturate(180%);
            -webkit-backdrop-filter: blur(32px) saturate(180%);
            border-bottom: 1px solid var(--border-subtle);
            box-shadow: var(--shadow-xs);
            transition: background var(--transition-smooth), box-shadow var(--transition-smooth), border-color var(--transition-smooth);
            animation: navEnter 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }

        @keyframes navEnter {
            from { transform: translateY(-100%); opacity: 0; filter: blur(10px); }
            to { transform: translateY(0); opacity: 1; filter: blur(0); }
        }

        .sp-nav.is-scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: var(--shadow-md);
            border-bottom-color: var(--border-default);
        }

        body.dark-mode .sp-nav.is-scrolled { background: rgba(15, 23, 42, 0.98); }

        .sp-nav__inner {
            max-width: 1400px;
            margin: 0 auto;
            padding: var(--space-4) var(--space-10);
            display: flex;
            align-items: center;
            gap: var(--space-8);
        }

        .sp-brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            flex-shrink: 0;
            position: relative;
            transition: transform var(--transition-bounce);
            animation: brandEnter 1s cubic-bezier(0.34, 1.56, 0.64, 1) 0.2s both;
        }

        @keyframes brandEnter {
            from { opacity: 0; transform: scale(0.8); filter: blur(8px); }
            to { opacity: 1; transform: scale(1); filter: blur(0); }
        }

        .sp-brand::after {
            content: '';
            position: absolute;
            inset: -8px;
            z-index: -1;
            background: var(--gradient-primary);
            border-radius: var(--radius-lg);
            opacity: 0;
            filter: blur(20px);
            transition: opacity var(--transition-base);
            pointer-events: none;
        }

        .sp-brand:hover::after { opacity: 0.3; }
        .sp-brand:hover { transform: scale(1.05) translateY(-2px); }

        .sp-brand__img {
            height: 104px;
            width: auto;
            transform-origin: left center;
            filter: drop-shadow(0 4px 12px rgba(59, 130, 246, 0.12));
            transition: transform var(--transition-bounce), filter var(--transition-base);
        }

        .sp-nav.is-scrolled .sp-brand__img { transform: scale(0.92); }

        .sp-brand:hover .sp-brand__img {
            filter: drop-shadow(0 12px 32px rgba(99, 102, 241, 0.4)) brightness(1.05);
            transform: translateY(-4px);
        }

        /* زر التبديل - مخفي على الشاشات الكبيرة */
        .sp-nav__toggle {
            display: none;
            z-index: 1400;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            padding: var(--space-3);
            background: var(--surface-white);
            border: 2px solid var(--border-default);
            border-radius: var(--radius-md);
            cursor: pointer;
            color: var(--text-primary);
            box-shadow: var(--shadow-xs);
            transition: all var(--transition-bounce);
        }

        .sp-nav__toggle:hover { border-color: var(--primary-500); box-shadow: var(--glow-primary); transform: scale(1.05); }
        .sp-nav__toggle:focus-visible { outline: 3px solid var(--primary-500); outline-offset: 2px; }

        .sp-nav__toggle-bar {
            display: block;
            position: absolute;
            width: 24px;
            height: 2.5px;
            background: var(--gradient-primary);
            border-radius: var(--radius-full);
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .sp-nav__toggle-bar:nth-child(1) { transform: translateY(-8px); }
        .sp-nav__toggle-bar:nth-child(2) { transform: translateY(0); }
        .sp-nav__toggle-bar:nth-child(3) { transform: translateY(8px); }

        .sp-nav.is-open .sp-nav__toggle-bar:nth-child(1) { transform: rotate(45deg) translate(5px, 5px); }
        .sp-nav.is-open .sp-nav__toggle-bar:nth-child(2) { opacity: 0; transform: scale(0); }
        .sp-nav.is-open .sp-nav__toggle-bar:nth-child(3) { transform: rotate(-45deg) translate(6px, -6px); }

        .sp-nav__links {
            display: flex;
            align-items: center;
            gap: var(--space-8);
            margin-inline-start: auto;
            flex: 1;
            z-index: 99999;
        }

        .sp-nav__menu {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            flex-wrap: wrap;
        }

        .sp-link {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-3) var(--space-4);
            overflow: hidden;
            white-space: nowrap;
            font-weight: 600;
            font-size: 0.9375rem;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: var(--radius-md);
            transition: color var(--transition-base), transform var(--transition-base), background var(--transition-base);
        }

        .sp-link i { font-size: 1rem; transition: transform var(--transition-base); }

        .sp-link::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--gradient-primary-subtle);
            opacity: 0;
            transition: opacity var(--transition-base);
        }

        .sp-link::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: 8px;
            width: 0;
            height: 2.5px;
            background: var(--gradient-primary);
            border-radius: var(--radius-full);
            transform: translateX(-50%);
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.4);
            transition: width var(--transition-bounce);
        }

        .sp-link:hover { color: var(--primary-600); transform: translateY(-1px); }
        .sp-link:hover::before { opacity: 1; }
        .sp-link:hover::after { width: calc(100% - 24px); }
        .sp-link:hover i { transform: scale(1.12); }
        .sp-link[aria-current="page"] { color: var(--primary-600); font-weight: 700; background: rgba(59, 130, 246, 0.08); }
        .sp-link[aria-current="page"]::after { width: calc(100% - 24px); }
        .sp-link:focus-visible { outline: 3px solid var(--primary-500); outline-offset: 2px; }
        .sp-link:active { transform: translateY(0); }

        .sp-actions {
            display: flex;
            align-items: center;
            gap: var(--space-4);
        }

        .sp-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-2);
            padding: var(--space-3) var(--space-6);
            overflow: hidden;
            white-space: nowrap;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.9375rem;
            color: inherit;
            text-decoration: none;
            cursor: pointer;
            transition: all var(--transition-base);
        }

        .sp-btn i { font-size: 1rem; transition: transform var(--transition-base); }
        .sp-btn:focus-visible { outline: 3px solid var(--primary-500); outline-offset: 2px; }

        .sp-btn--primary {
            background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
            color: var(--text-inverse);
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.25);
        }

        .sp-btn--primary::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), transparent);
            opacity: 0;
            transition: opacity var(--transition-base);
        }

        .sp-btn--primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4); color: var(--text-inverse); }
        .sp-btn--primary:hover::before { opacity: 1; }
        .sp-btn--primary:hover i { transform: scale(1.12); }
        .sp-btn--primary:active { transform: translateY(0); }

        .sp-btn--danger {
            background: var(--surface-white);
            border: 1.5px solid var(--danger-200);
            color: var(--danger-600);
        }

        .sp-btn--danger::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--gradient-danger-subtle);
            opacity: 0;
            transition: opacity var(--transition-base);
        }

        .sp-btn--danger:hover { background: var(--danger-50); border-color: var(--danger-500); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25); }
        .sp-btn--danger:hover::before { opacity: 1; }
        .sp-btn--danger:hover i { transform: scale(1.12); }
        .sp-btn--danger:active { transform: translateY(0); }

        .sp-user {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-2) var(--space-4) var(--space-2) var(--space-2);
            overflow: hidden;
            background: var(--surface-white);
            border: 1.5px solid var(--border-default);
            border-radius: var(--radius-md);
            cursor: default;
            position: relative;
            transition: all var(--transition-base);
        }

        .sp-user::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--gradient-primary-subtle);
            opacity: 0;
            transition: opacity var(--transition-base);
            pointer-events: none;
        }

        .sp-user:hover { border-color: var(--primary-400); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.12); }
        .sp-user:hover::before { opacity: 1; }

        .sp-user__avatar {
            position: relative;
            z-index: 1;
            width: 40px;
            height: 40px;
            object-fit: cover;
            border: 2px solid var(--primary-100);
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-xs);
            transition: transform var(--transition-bounce);
        }

        .sp-user:hover .sp-user__avatar { transform: scale(1.05); }

        .sp-user__details { position: relative; z-index: 1; min-width: 0; }

        .sp-user__name {
            display: block;
            max-width: 140px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            font-weight: 600;
            font-size: 0.875rem;
            line-height: 1.4;
            color: var(--text-primary);
        }

        .sp-user__email {
            display: block;
            max-width: 140px;
            margin-top: 1px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            font-size: 0.75rem;
            line-height: 1.3;
            color: var(--text-muted);
        }

        .sp-notif { position: relative; }

        .sp-notif__trigger {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            width: 44px;
            height: 44px;
            background: var(--surface-white);
            border: 2px solid var(--border-default);
            border-radius: var(--radius-full);
            cursor: pointer;
            color: var(--text-primary);
            transition: all var(--transition-bounce);
        }

        .sp-notif__trigger::before {
            content: '';
            position: absolute;
            inset: -4px;
            background: var(--gradient-primary);
            border-radius: var(--radius-full);
            opacity: 0;
            filter: blur(12px);
            transition: opacity var(--transition-base);
            pointer-events: none;
        }

        .sp-notif__trigger:hover { background: var(--gradient-primary-subtle); border-color: var(--primary-500); transform: rotate(15deg) scale(1.1); }
        .sp-notif__trigger:hover::before { opacity: 0.3; }
        .sp-notif__trigger:focus-visible { outline: 3px solid var(--primary-500); outline-offset: 2px; }

        .sp-notif--unread .sp-notif__trigger:hover { animation: bellShake 0.6s cubic-bezier(0.36, 0.07, 0.19, 0.97) both; }

        @keyframes bellShake {
            0% { transform: rotate(15deg) scale(1.1); }
            15% { transform: rotate(-18deg) scale(1.1); }
            30% { transform: rotate(14deg) scale(1.1); }
            45% { transform: rotate(-10deg) scale(1.1); }
            60% { transform: rotate(6deg) scale(1.1); }
            75% { transform: rotate(-3deg) scale(1.1); }
            100% { transform: rotate(0deg) scale(1.1); }
        }

        .sp-notif__badge {
            position: absolute;
            top: -6px;
            right: -6px;
            min-width: 20px;
            padding: 2px 6px;
            background: var(--gradient-danger);
            border-radius: var(--radius-md);
            line-height: 1.4;
            text-align: center;
            font-weight: 800;
            font-size: 0.6875rem;
            color: var(--text-inverse);
            box-shadow: var(--glow-danger);
            transition: opacity 0.4s ease, transform 0.4s ease;
        }

        .sp-notif__badge[hidden] { display: none; }

        @keyframes badgePulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.15); }
        }

        .sp-notif--unread .sp-notif__badge { animation: badgePulse 2s ease-in-out infinite; }

        .sp-notif--unread .sp-notif__trigger { border-color: rgba(239, 68, 68, 0.3); box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.08); }
        .sp-notif--unread .sp-notif__trigger:hover { border-color: var(--primary-500); box-shadow: none; }

        .sp-notif__dropdown {
            position: absolute;
            top: calc(100% + 12px);
            right: 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            width: 400px;
            max-width: 90vw;
            max-height: 480px;
            background: var(--surface-white);
            border: 2px solid var(--border-default);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            visibility: hidden;
            opacity: 0;
            transform: translateY(-8px) scale(0.96);
            transition: opacity var(--transition-base), transform var(--transition-bounce), visibility var(--transition-base);
        }

        .sp-notif__dropdown[aria-hidden="false"] { visibility: visible; opacity: 1; transform: translateY(0) scale(1); }

        .sp-notif__header {
            display: flex;
            flex-shrink: 0;
            align-items: center;
            justify-content: space-between;
            padding: var(--space-5);
            background: var(--gradient-primary-subtle);
            border-bottom: 1px solid var(--border-default);
            font-weight: 700;
            color: var(--text-primary);
            flex-wrap: wrap;
            gap: var(--space-2);
        }

        .sp-notif__header-left { display: flex; align-items: center; gap: var(--space-2); }

        .sp-notif__header-count {
            min-width: 22px;
            padding: 2px 8px;
            background: var(--gradient-primary);
            border-radius: var(--radius-full);
            text-align: center;
            font-weight: 700;
            font-size: 0.75rem;
            color: var(--text-inverse);
        }

        .sp-notif__list {
            position: relative;
            flex: 1;
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        .sp-notif__list::after {
            content: '';
            position: sticky;
            z-index: 2;
            bottom: 0;
            display: block;
            height: 50px;
            background: linear-gradient(to bottom, transparent, var(--surface-white));
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .sp-notif__list.is-at-bottom::after { opacity: 0; }

        .sp-notif__footer-btn {
            padding: var(--space-2) var(--space-3);
            white-space: nowrap;
            background: none;
            border: none;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 0.8125rem;
            color: var(--primary-600);
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .sp-notif__footer-btn:hover { background: rgba(59, 130, 246, 0.08); color: var(--primary-700); }

        .sp-notif__item {
            position: relative;
            padding: var(--space-4) var(--space-5);
            border-bottom: 1px solid var(--border-subtle);
            cursor: pointer;
            transition: all var(--transition-base);
        }

        .sp-notif__item:last-child { border-bottom: none; }

        .sp-notif__item::before {
            content: '';
            position: absolute;
            inset-inline-start: 0;
            top: 0;
            bottom: 0;
            width: 0;
            background: var(--gradient-primary);
            transition: width var(--transition-base);
        }

        .sp-notif__item:hover { background: var(--surface-subtle); transform: translateX(4px); }
        .sp-notif__item:hover::before { width: 4px; }

        .sp-notif__item--unread { background: rgba(99, 102, 241, 0.04); border-inline-start: 3px solid var(--primary-500); }
        .sp-notif__item--unread::before { display: none; }
        .sp-notif__item--unread:hover { background: rgba(99, 102, 241, 0.08); }

        .sp-notif__dot {
            display: inline-block;
            flex-shrink: 0;
            width: 8px;
            height: 8px;
            background: var(--primary-500);
            border-radius: var(--radius-full);
            animation: dotPulse 2s ease-in-out infinite;
        }

        @keyframes dotPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(0.85); }
        }

        .sp-notif__title { display: flex; align-items: center; gap: var(--space-2); margin-bottom: var(--space-1); font-weight: 600; font-size: 0.875rem; color: var(--text-primary); }
        .sp-notif__message { font-size: 0.8125rem; line-height: 1.5; color: var(--text-secondary); }
        .sp-notif__time { display: flex; align-items: center; gap: var(--space-1); margin-top: var(--space-2); font-size: 0.75rem; color: var(--text-muted); }

        .sp-notif__empty { padding: var(--space-10) var(--space-6); text-align: center; color: var(--text-muted); }
        .sp-notif__empty i { display: block; margin-bottom: var(--space-4); font-size: 2.5rem; opacity: 0.3; }
        .sp-notif__empty-text { font-weight: 600; font-size: 0.9375rem; color: var(--text-secondary); }
        .sp-notif__empty-sub { font-size: 0.8125rem; margin-top: var(--space-1); }

        .sp-scroll-progress { position: fixed; top: 0; left: 0; z-index: 9999; width: 0%; height: 3px; background: var(--gradient-primary); box-shadow: 0 0 12px rgba(99, 102, 241, 0.5); transition: width 0.1s linear; }

        .sp-theme-toggle { position: relative; width: 56px; height: 32px; padding: 3px; background: var(--surface-subtle); border: 2px solid var(--border-default); border-radius: var(--radius-full); cursor: pointer; transition: all var(--transition-base); }
        .sp-theme-toggle:hover { border-color: var(--primary-500); box-shadow: var(--glow-primary); transform: scale(1.05); }
        .sp-theme-toggle:focus-visible { outline: 3px solid var(--primary-500); outline-offset: 2px; }

        .sp-theme-toggle__thumb { display: flex; align-items: center; justify-content: center; width: 22px; height: 22px; background: linear-gradient(135deg, #fbbf24, #f59e0b); border-radius: var(--radius-full); font-size: 0.6875rem; box-shadow: 0 2px 8px rgba(251, 191, 36, 0.5); transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55); }
        body.dark-mode .sp-theme-toggle__thumb { background: linear-gradient(135deg, #6366f1, #4f46e5); transform: translateX(24px); box-shadow: 0 2px 8px rgba(99, 102, 241, 0.5); }

        .sp-chat { position: fixed; bottom: var(--space-8); right: var(--space-8); z-index: 1000; }
        .sp-chat__btn { display: flex; align-items: center; justify-content: center; width: 56px; height: 56px; background: var(--gradient-success); border: 3px solid rgba(255, 255, 255, 0.3); border-radius: var(--radius-full); font-size: 1.4rem; color: var(--text-inverse); cursor: pointer; box-shadow: var(--glow-success); transition: all var(--transition-bounce); animation: chatFloat 3s ease-in-out infinite; }
        @keyframes chatFloat { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
        .sp-chat__btn:hover { transform: scale(1.12) rotate(-5deg); box-shadow: 0 16px 48px rgba(16, 185, 129, 0.7); animation: none; }

        /* ===============================================
               MODAL (Premium)
               =============================================== */
        #notifDetailModal + .modal-backdrop,
        .modal-backdrop.show {
            backdrop-filter: blur(16px) saturate(150%);
            -webkit-backdrop-filter: blur(16px) saturate(150%);
        }

        #notifDetailModal .modal-dialog {
            transform: translateY(50px) scale(0.92);
            opacity: 0;
            transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.4s ease;
        }

        #notifDetailModal.show .modal-dialog {
            transform: translateY(0) scale(1);
            opacity: 1;
        }

        #notifDetailModal .modal-content {
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: var(--radius-xl);
            background: var(--surface-glass);
            backdrop-filter: blur(24px) saturate(200%);
            -webkit-backdrop-filter: blur(24px) saturate(200%);
            box-shadow: 0 0 0 1px rgba(226, 232, 240, 0.6), 0 4px 6px -1px rgba(15, 23, 42, 0.05), 0 20px 50px -12px rgba(15, 23, 42, 0.3), 0 0 100px -20px rgba(99, 102, 241, 0.2);
            overflow: hidden;
        }

        body.dark-mode #notifDetailModal .modal-content {
            border-color: rgba(51, 65, 85, 0.6);
            box-shadow: 0 0 0 1px rgba(51, 65, 85, 0.8), 0 20px 50px -12px rgba(0, 0, 0, 0.6), 0 0 100px -20px rgba(99, 102, 241, 0.3);
        }

        #notifDetailModal .modal-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.7), transparent);
            z-index: 10;
            pointer-events: none;
        }

        body.dark-mode #notifDetailModal .modal-content::before {
            background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.15), transparent);
        }

        #notifDetailModal .modal-header {
            background: var(--gradient-primary);
            color: var(--text-inverse);
            border: none;
            padding: var(--space-8) var(--space-8) var(--space-6);
            position: relative;
            overflow: hidden;
        }

        #notifDetailModal .modal-header::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 20% 80%, rgba(255,255,255,0.15) 0%, transparent 50%);
            pointer-events: none;
        }

        #notifDetailModal .modal-header .btn-close {
            position: absolute;
            top: var(--space-5);
            right: var(--space-5);
            width: 38px;
            height: 38px;
            background: rgba(255, 255, 255, 0.15);
            border: none;
            border-radius: var(--radius-full);
            filter: brightness(0) invert(1);
            opacity: 0.9;
            transition: all var(--transition-bounce);
            box-shadow: 0 4px 14px rgba(0,0,0,0.15);
            z-index: 2;
        }

        [dir="rtl"] #notifDetailModal .modal-header .btn-close {
            right: auto;
            left: var(--space-5);
        }

        #notifDetailModal .modal-header .btn-close:hover {
            transform: scale(1.2) rotate(90deg);
            opacity: 1;
            background: rgba(255, 255, 255, 0.3);
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }

        #notifDetailModal .modal-meta {
            position: relative;
            z-index: 1;
            font-size: 0.875rem;
            opacity: 0.9;
            margin-bottom: var(--space-3);
            display: flex;
            align-items: center;
            gap: var(--space-2);
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        #notifDetailModal .modal-title {
            position: relative;
            z-index: 1;
            font-weight: 800;
            font-size: 1.625rem;
            line-height: 1.3;
            margin: 0;
            letter-spacing: -0.02em;
        }

        #notifDetailModal .modal-body {
            padding: var(--space-10) var(--space-8);
            color: var(--text-primary);
            font-size: 1.0625rem;
            line-height: 1.85;
            background: linear-gradient(180deg, var(--surface-subtle) 0%, var(--surface-white) 100%);
            white-space: pre-wrap;
            word-break: break-word;
        }

        #notifDetailModal .modal-footer {
            border-top: 1px solid var(--border-default);
            padding: var(--space-5) var(--space-8);
            background: var(--surface-subtle);
            justify-content: flex-end;
            gap: var(--space-3);
        }

        .sp-notif--mobile { display: none; }
        .sp-notif--desktop { display: block; }

        /* ===============================================
               RESPONSIVE (MOBILE PUSH MENU)
               =============================================== */
        @media (max-width: 1200px) { .sp-nav__inner { gap: var(--space-6); } }
        @media (max-width: 992px) { .sp-user__name, .sp-user__email { max-width: 100px; } }

        @media (max-width: 768px) {
            .sp-nav__inner {
                flex-wrap: wrap;
                padding: var(--space-4) var(--space-5);
                gap: var(--space-3);
            }

            .sp-brand {
                margin-inline-end: auto;  /* يدفع العناصر التالية إلى الطرف الآخر */
            }

            .sp-brand__img { height: 64px; }

            .sp-notif--mobile {
                display: block;
            }

            .sp-notif--desktop {
                display: none;
            }

            .sp-nav__toggle {
                display: flex;
                margin-inline-start: 0; /* يلتصق بجوار إشعار الموبايل */
            }

            /* القائمة - push menu */
            .sp-nav__links {
                width: 100%;
                order: 2;
                position: static;
                display: block;
                max-height: 0;
                overflow-y: hidden;
                padding: 0 var(--space-5);
                background: var(--surface-glass);
                backdrop-filter: blur(24px);
                -webkit-backdrop-filter: blur(24px);
                border-radius: 0 0 var(--radius-lg) var(--radius-lg);
                transition: max-height 0.4s ease, padding 0.3s ease;
                visibility: visible;
                opacity: 1;
                transform: none;
                box-shadow: none;
                margin-top: 0;
            }

            .sp-nav.is-open .sp-nav__links {
                max-height: 80vh;
                padding-top: var(--space-6);
                padding-bottom: var(--space-8);
                overflow-y: auto;
                border-top: 1px solid var(--border-subtle);
                margin-top: var(--space-3);
            }

            .sp-nav__menu {
                flex-direction: column;
                width: 100%;
                gap: var(--space-2);
                margin-bottom: var(--space-6);
            }

            .sp-link {
                padding: var(--space-4) var(--space-5);
                width: 100%;
                font-size: 1rem;
                border-radius: var(--radius-lg);
                white-space: normal;
            }

            .sp-actions {
                flex-direction: column;
                width: 100%;
                gap: var(--space-3);
                padding-top: var(--space-4);
                border-top: 1px solid var(--border-default);
            }

            .sp-user {
                width: 100%;
                justify-content: center;
                padding: var(--space-4);
            }

            .sp-user__name, .sp-user__email {
                max-width: none;
            }

            .sp-btn {
                width: 100%;
                padding: var(--space-4);
            }

            /* Notification icon is now outside the mobile menu, so we keep its natural 44x44 size */

            .sp-notif__dropdown {
                position: fixed;
                top: 90px; /* Below the mobile header */
                left: var(--space-4);
                right: var(--space-4);
                width: calc(100% - (var(--space-4) * 2));
                max-width: none;
                max-height: calc(100vh - 120px);
                z-index: 1500;
                transform-origin: top;
            }

            .sp-chat {
                bottom: var(--space-4);
                right: var(--space-4);
            }

            #notifDetailModal .modal-title { font-size: 1.35rem; }
            #notifDetailModal .modal-body { padding: var(--space-6) var(--space-5); }
            #notifDetailModal .modal-header { padding: var(--space-6) var(--space-5) var(--space-4); }
            #notifDetailModal .modal-footer { padding: var(--space-4) var(--space-5); }
        }

        /* RTL Fixes */
        [dir="rtl"] .sp-notif__item--unread { border-inline-start: none; border-inline-end: 3px solid var(--primary-500); }
        [dir="rtl"] .sp-notif__item::before { inset-inline-start: auto; inset-inline-end: 0; }
        [dir="rtl"] .sp-notif__item:hover { transform: translateX(-4px); }

        /* Reduced Motion */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.1ms !important;
            }
            #notifDetailModal .modal-dialog {
                transition: opacity 0.1s ease !important;
                transform: none !important;
            }
            #notifDetailModal.show .modal-dialog { transform: none !important; }
            #notifDetailModal .modal-header .btn-close { transition: none !important; }
        }
    </style>
@endonce

@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const nav = document.querySelector('.sp-nav');
            if (!nav) return;

            const toggle = nav.querySelector('.sp-nav__toggle');
            const linksContainer = nav.querySelector('.sp-nav__links');
            const scrollProgress = document.getElementById('scrollProgress');

            // --- Notifications Logic ---
            const notifsContainers = document.querySelectorAll('.sp-notif');

            const markAllAsRead = async (btnElement, wrapperElement) => {
                const originalHTML = btnElement?.innerHTML;
                try {
                    if (btnElement) {
                        btnElement.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> جارٍ...';
                        btnElement.disabled = true;
                    }
                    const res = await fetch('{{ route("notifications.mark-as-read") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    if (data.success) {
                        // Sync across all instances
                        document.querySelectorAll('.sp-notif__badge').forEach(b => b.setAttribute('hidden', ''));
                        document.querySelectorAll('.sp-notif').forEach(w => w.classList.remove('sp-notif--unread'));
                        document.querySelectorAll('.sp-notif__item--unread').forEach(el => {
                            el.classList.remove('sp-notif__item--unread');
                            el.querySelector('.sp-notif__dot')?.remove();
                        });
                        document.querySelectorAll('.sp-notif__header-count').forEach(c => c.setAttribute('hidden', ''));
                        document.querySelectorAll('.sp-notif__footer-btn').forEach(btn => btn.style.display = 'none');
                    }
                } catch (err) {
                    console.error('Error marking as read:', err);
                    if (btnElement) {
                        btnElement.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> خطأ!';
                        setTimeout(() => { btnElement.innerHTML = originalHTML || ''; btnElement.disabled = false; }, 2000);
                    }
                }
            };

            const closeAllNotifs = () => {
                notifsContainers.forEach(otherWrapper => {
                    otherWrapper.querySelector('.sp-notif__dropdown')?.setAttribute('aria-hidden', 'true');
                    otherWrapper.querySelector('.sp-notif__trigger')?.setAttribute('aria-expanded', 'false');
                });
            };

            notifsContainers.forEach(wrapper => {
                const trigger = wrapper.querySelector('.sp-notif__trigger');
                const dropdown = wrapper.querySelector('.sp-notif__dropdown');
                const badge = wrapper.querySelector('.sp-notif__badge');
                const list = wrapper.querySelector('.sp-notif__list');
                const markAllBtn = wrapper.querySelector('.sp-notif__footer-btn');

                if (list) {
                    list.addEventListener('scroll', () => {
                        list.classList.toggle('is-at-bottom', list.scrollHeight - list.scrollTop <= list.clientHeight + 20);
                    }, { passive: true });
                }

                if (markAllBtn) {
                    markAllBtn.addEventListener('click', () => markAllAsRead(markAllBtn, wrapper));
                }

                if (trigger && dropdown) {
                    trigger.addEventListener('click', async (e) => {
                        e.stopPropagation();
                        const wasOpen = dropdown.getAttribute('aria-hidden') === 'false';
                        
                        // Close all others
                        closeAllNotifs();
                        
                        // Close mobile menu if it is open
                        if (nav.classList.contains('is-open')) {
                            nav.classList.remove('is-open');
                            toggle?.setAttribute('aria-expanded', 'false');
                            linksContainer?.setAttribute('aria-hidden', 'true');
                        }

                        if (wasOpen) return; // if it was open, it's now closed by closeAllNotifs

                        dropdown.setAttribute('aria-hidden', 'false');
                        trigger.setAttribute('aria-expanded', 'true');
                        if (badge && !badge.hasAttribute('hidden')) await markAllAsRead(null, wrapper);
                    });
                }
            });

            // Close notifications on outside click
            document.addEventListener('click', (e) => {
                let insideNotif = false;
                notifsContainers.forEach(wrapper => {
                    const trigger = wrapper.querySelector('.sp-notif__trigger');
                    const dropdown = wrapper.querySelector('.sp-notif__dropdown');
                    if (trigger && dropdown && (trigger.contains(e.target) || dropdown.contains(e.target))) {
                        insideNotif = true;
                    }
                });
                
                if (!insideNotif) {
                    closeAllNotifs();
                }
                
                if (nav.classList.contains('is-open') && !nav.contains(e.target)) {
                    toggleMenu(); // Closes menu if clicking completely outside
                }
            });

            // Toggle Mobile Menu
            const toggleMenu = () => {
                const isOpen = nav.classList.toggle('is-open');
                toggle?.setAttribute('aria-expanded', String(isOpen));
                linksContainer?.setAttribute('aria-hidden', String(!isOpen));
                
                // If we are opening the menu, close any open notifications
                if (isOpen) {
                    closeAllNotifs();
                }
            };

            if (toggle) {
                toggle.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleMenu();
                });
            }

            let ticking = false;
            window.addEventListener('scroll', () => {
                if (!ticking) {
                    requestAnimationFrame(() => {
                        nav.classList.toggle('is-scrolled', window.scrollY > 60);
                        if (scrollProgress) {
                            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
                            scrollProgress.style.width = `${docHeight > 0 ? (window.scrollY / docHeight) * 100 : 0}%`;
                        }
                        ticking = false;
                    });
                    ticking = true;
                }
            }, { passive: true });

            const notifModalEl = document.getElementById('notifDetailModal');
            const notifModal = notifModalEl ? new bootstrap.Modal(notifModalEl) : null;

            document.querySelectorAll('.sp-notif__item').forEach(item => {
                item.addEventListener('click', () => {
                    const title = item.getAttribute('data-notif-title');
                    const message = item.getAttribute('data-notif-message');
                    const time = item.getAttribute('data-notif-time');
                    const modalTitle = document.getElementById('notifDetailTitle');
                    const modalMessage = document.getElementById('notifDetailMessage');
                    const modalDate = document.querySelector('#notifDetailDate span');
                    if (modalTitle) modalTitle.textContent = title;
                    if (modalMessage) modalMessage.textContent = message;
                    if (modalDate) modalDate.textContent = time;
                    notifModal?.show();
                    closeNotif();
                });
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    if (nav.classList.contains('is-open')) { toggleMenu(); toggle?.focus(); }
                }
            });
        });
    </script>
@endonce

<div class="sp-scroll-progress" id="scrollProgress" aria-hidden="true"></div>

<nav class="sp-nav" role="navigation" aria-label="{{ __('general.main_navigation') }}">
    <div class="sp-nav__inner">
        <a class="sp-brand" href="{{ route('home') }}" aria-label="{{ config('app.name', 'Speeda') }} - {{ __('general.home') }}">
            <img class="sp-brand__img" src="{{ asset('images/main-logo.png') }}" alt="{{ config('app.name', 'Speeda') }}" loading="eager">
        </a>

        @if($isServiceProvider)
            <div class="sp-notif sp-notif--mobile {{ $hasUnread ? 'sp-notif--unread' : '' }}">
                <a href="{{ route('notifications.index') }}" class="sp-notif__trigger" aria-label="{{ __('admin.notifications') }}{{ $hasUnread ? ' (' . $unreadCount . ' ' . __('general.new') . ')' : '' }}">
                    <i class="fas fa-bell" aria-hidden="true"></i>
                    <span class="sp-notif__badge" @if(!$hasUnread) hidden @endif>{{ $unreadCount }}</span>
                </a>
            </div>
        @endif

        <button class="sp-nav__toggle" type="button" aria-label="{{ __('general.toggle_navigation') }}" aria-expanded="false" aria-controls="navLinks">
            <span class="sp-nav__toggle-bar"></span>
            <span class="sp-nav__toggle-bar"></span>
            <span class="sp-nav__toggle-bar"></span>
        </button>

        <div class="sp-nav__links" id="navLinks" aria-hidden="false">
            <div class="sp-nav__menu" role="menubar">
                <a class="sp-link" href="{{ route('home') }}" role="menuitem" @if(request()->routeIs('home')) aria-current="page" @endif>
                    <i class="fas fa-home" aria-hidden="true"></i> {{ __('general.home') }}
                </a>
                <a class="sp-link" href="{{ route('categories') }}" role="menuitem" @if(request()->routeIs('categories')) aria-current="page" @endif>
                    <i class="fas fa-th-large" aria-hidden="true"></i> {{ __('general.categories') }}
                </a>
                <a class="sp-link" href="{{ route('service-providers.index') }}" role="menuitem" @if(request()->routeIs('service-providers.*')) aria-current="page" @endif>
                    <i class="fas fa-users" aria-hidden="true"></i> {{ __('service_provider.service_providers') }}
                </a>
            </div>

            <div class="sp-actions">
                @include('components.language-switcher')

                @if($isServiceProvider)
                    <div class="sp-notif sp-notif--desktop {{ $hasUnread ? 'sp-notif--unread' : '' }}">
                        <a href="{{ route('notifications.index') }}" class="sp-notif__trigger" aria-label="{{ __('admin.notifications') }}{{ $hasUnread ? ' (' . $unreadCount . ' ' . __('general.new') . ')' : '' }}">
                            <i class="fas fa-bell" aria-hidden="true"></i>
                            <span class="sp-notif__badge" @if(!$hasUnread) hidden @endif>{{ $unreadCount }}</span>
                        </a>
                    </div>
                @endif

                @auth
                    <div class="sp-user" aria-label="{{ $user->name }}">
                        <img class="sp-user__avatar" src="{{ $user->profile_photo_url ?? asset('images/user.png') }}" alt="{{ $user->name }}" loading="lazy">
                        <div class="sp-user__details">
                            <span class="sp-user__name">{{ $user->name }}</span>
                            <span class="sp-user__email">{{ $user->email }}</span>
                        </div>
                    </div>

                    @if($provider)
                        <a href="{{ route('service-providers.show', $provider) }}" class="sp-btn sp-btn--primary" @if(request()->url() === route('service-providers.show', $provider)) aria-current="page" @endif>
                            <i class="fas fa-id-card" aria-hidden="true"></i> {{ __('general.my_profile') }}
                        </a>
                    @elseif($user->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="sp-btn sp-btn--primary">
                            <i class="fas fa-tachometer-alt" aria-hidden="true"></i> {{ __('admin.dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="sp-btn sp-btn--primary">
                            <i class="fas fa-tachometer-alt" aria-hidden="true"></i> {{ __('general.dashboard') }}
                        </a>
                    @endif

                    <form method="POST" action="{{ route('logout') }}" style="display: contents;">
                        @csrf
                        <button type="submit" class="sp-btn sp-btn--danger">
                            <i class="fas fa-sign-out-alt" aria-hidden="true"></i> {{ __('general.logout') }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}?tab=login" class="sp-btn sp-btn--primary">
                        <i class="fas fa-sign-in-alt" aria-hidden="true"></i> {{ __('general.login') }}
                    </a>
                    @guest
                        <a href="{{ route('login') }}?tab=register" class="sp-btn sp-btn--primary">
                            <i class="fas fa-user-plus" aria-hidden="true"></i> {{ __('general.register') }}
                        </a>
                    @endguest
                @endauth
            </div>
        </div>
    </div>
</nav>
<!-- Notification Detail Modal -->
<div class="modal fade" id="notifDetailModal" tabindex="-1" aria-labelledby="notifDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('general.close') }}"></button>
                <div class="modal-meta" id="notifDetailDate">
                    <i class="fas fa-clock" aria-hidden="true"></i>
                    <span></span>
                </div>
                <h5 class="modal-title" id="notifDetailTitle"></h5>
            </div>
            <div class="modal-body" id="notifDetailMessage"></div>
            <div class="modal-footer">
                <button type="button" class="sp-btn sp-btn--primary" data-bs-dismiss="modal">
                    {{ __('general.close') }}
                </button>
            </div>
        </div>
    </div>
</div>

