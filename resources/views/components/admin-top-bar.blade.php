@php
    $user = auth()->user();
    $isRtl = in_array(app()->getLocale(), ['ar', 'he', 'ur', 'fa']);
    $currentRoute = request()->route()?->getName();

    $dashboardRoute = Route::has('admin.dashboard') ? route('admin.dashboard') : '#';
    $breadcrumbs = [['label' => 'Dashboard', 'url' => $dashboardRoute]];

    // route-name prefix => [section label, index route name]. Longest prefix wins,
    // so admin.users.trash resolves before admin.users.
    $breadcrumbSections = [
        'admin.users.trash' => ['Users Trash', 'admin.users.trash'],
        'admin.users' => ['Users', 'admin.users'],
        'admin.categories' => ['Categories', 'admin.categories'],
        'admin.locations' => ['Locations', 'admin.locations'],
        'admin.blog.posts' => ['Blogs', 'admin.blog.posts.index'],
        'admin.legal-pages' => ['Legal Pages', 'admin.legal-pages.index'],
        'admin.reviews' => ['Reviews', 'admin.reviews'],
        'admin.comments' => ['Comments', 'admin.comments'],
        'admin.testimonials' => ['Testimonials', 'admin.testimonials.index'],
        'admin.notifications' => ['Notifications', 'admin.notifications.index'],
        'admin.broadcasts' => ['Email All Providers', 'admin.broadcasts.index'],
        'admin.email_journey' => ['Email Journey', 'admin.email_journey.index'],
        'admin.visitors' => ['Visitor Analytics', 'admin.visitors'],
        'admin.whatsapp_analytics' => ['WhatsApp Analytics', 'admin.whatsapp_analytics.index'],
        'admin.provider_activity_monitor' => ['Providers', 'admin.provider_activity_monitor.index'],
        'admin.login_activity' => ['Login Activity', 'admin.login_activity.index'],
        'admin.activity_logs' => ['Activity Logs', 'admin.activity_logs'],
    ];

    $leafLabels = [
        'create' => 'Create',
        'edit' => 'Edit',
        'show' => 'View',
        'trash' => null, // already a section
    ];

    if ($currentRoute && $currentRoute !== 'admin.dashboard') {
        foreach ($breadcrumbSections as $prefix => [$label, $indexRoute]) {
            if (!str_starts_with($currentRoute, $prefix) || !Route::has($indexRoute)) {
                continue;
            }

            $breadcrumbs[] = ['label' => $label, 'url' => route($indexRoute)];

            $leaf = \Illuminate\Support\Str::afterLast($currentRoute, '.');
            if (($leafLabels[$leaf] ?? null) && $currentRoute !== $indexRoute) {
                $breadcrumbs[] = ['label' => $leafLabels[$leaf], 'url' => '#'];
            }

            break;
        }
    }

    // This dropdown is the only admin navigation that is actually rendered, so
    // every admin module must appear here — anything missing is unreachable
    // without typing the URL by hand.
    $adminMenuGroups = [
        'Workspace' => [
            ['label' => 'Dashboard', 'icon' => 'fa-gauge-high', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
            ['label' => 'Users', 'icon' => 'fa-users', 'route' => 'admin.users', 'active' => 'admin.users'],
            ['label' => 'Users Trash', 'icon' => 'fa-trash-can', 'route' => 'admin.users.trash', 'active' => 'admin.users.trash'],
            ['label' => 'Providers', 'icon' => 'fa-briefcase', 'route' => 'admin.provider_activity_monitor.index', 'active' => 'admin.provider_activity_monitor*|admin.providers*'],
            ['label' => 'Login Activity', 'icon' => 'fa-right-to-bracket', 'route' => 'admin.login_activity.index', 'active' => 'admin.login_activity*'],
        ],
        'Content & Moderation' => [
            ['label' => 'Reviews', 'icon' => 'fa-star', 'route' => 'admin.reviews', 'active' => 'admin.reviews*'],
            ['label' => 'Comments', 'icon' => 'fa-comments', 'route' => 'admin.comments', 'active' => 'admin.comments*'],
            ['label' => 'Testimonials', 'icon' => 'fa-quote-right', 'route' => 'admin.testimonials.index', 'active' => 'admin.testimonials*'],
            ['label' => 'Blogs', 'icon' => 'fa-newspaper', 'route' => 'admin.blog.posts.index', 'active' => 'admin.blog.posts*'],
            ['label' => 'Legal Pages', 'icon' => 'fa-scale-balanced', 'route' => 'admin.legal-pages.index', 'active' => 'admin.legal-pages*'],
            ['label' => 'Categories', 'icon' => 'fa-folder-tree', 'route' => 'admin.categories', 'active' => 'admin.categories*'],
            ['label' => 'Locations', 'icon' => 'fa-location-dot', 'route' => 'admin.locations', 'active' => 'admin.locations*'],
        ],
        'Engagement & Insights' => [
            ['label' => 'Notifications', 'icon' => 'fa-bell', 'route' => 'admin.notifications.index', 'active' => 'admin.notifications*'],
            ['label' => 'Email All Providers', 'icon' => 'fa-bullhorn', 'route' => 'admin.broadcasts.index', 'active' => 'admin.broadcasts*'],
            ['label' => 'Email Journey', 'icon' => 'fa-envelope-open-text', 'route' => 'admin.email_journey.index', 'active' => 'admin.email_journey*'],
            ['label' => 'Email Templates', 'icon' => 'fa-pen-to-square', 'route' => 'admin.email_templates.index', 'active' => 'admin.email_templates*'],
            ['label' => 'Visitor Analytics', 'icon' => 'fa-chart-line', 'route' => 'admin.visitors', 'active' => 'admin.visitors*'],
            ['label' => 'WhatsApp Analytics', 'icon' => 'fa-comment-dots', 'route' => 'admin.whatsapp_analytics.index', 'active' => 'admin.whatsapp_analytics*'],
            ['label' => 'Activity Logs', 'icon' => 'fa-clipboard-list', 'route' => 'admin.activity_logs', 'active' => 'admin.activity_logs*'],
        ],
    ];
@endphp

{{-- @change 2026-04-14 TASK-5 | Removed admin top-bar notification scaffolding | Admin notifications now live on the dedicated page without navbar duplication | risk:LOW --}}
<nav class="admin-top-bar" x-data="adminTopBar()" x-init="init()">
    <!-- Left Section -->
    <div class="admin-top-bar-left">
        <div class="admin-menu" x-data="{ open: false }">
            <button class="admin-menu-btn" type="button" @click="open = !open" :class="{ 'active': open }" aria-label="Open admin menu">
                <i class="fas fa-table-cells-large"></i>
                <span>Admin Menu</span>
                <i class="fas fa-chevron-down admin-menu-chevron" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" @click.away="open = false" @keydown.escape.window="open = false"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-1"
                 class="admin-menu-dropdown">
                @foreach($adminMenuGroups as $groupLabel => $items)
                    <div class="admin-menu-group-label">{{ $groupLabel }}</div>
                    <div class="admin-menu-group">
                        @foreach($items as $item)
                            @if(Route::has($item['route']))
                                <a href="{{ route($item['route']) }}" class="admin-menu-item {{ request()->routeIs($item['active']) ? 'active' : '' }}">
                                    <i class="fas {{ $item['icon'] }}"></i>
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Logo -->
        @if(Route::has('admin.dashboard'))
            <a href="{{ route('admin.dashboard') }}" class="admin-logo">
                <img src="{{ asset('images/New_logo.png') }}" alt="Speeda" height="36">
                <span class="brand-name">Speeda</span>
            </a>
        @else
            <span class="admin-logo">
                <img src="{{ asset('images/New_logo.png') }}" alt="Speeda" height="36">
                <span class="brand-name">Speeda</span>
            </span>
        @endif

        <!-- Breadcrumb -->
        @if(count($breadcrumbs) > 0 && $breadcrumbs[0]['url'] !== '#')
            <nav class="admin-breadcrumb" aria-label="breadcrumb">
                <ol class="breadcrumb-list">
                    @foreach($breadcrumbs as $index => $crumb)
                        @if($loop->last)
                            <li class="breadcrumb-item active">{{ $crumb['label'] }}</li>
                        @else
                            <li class="breadcrumb-item">
                                <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
                                <span class="separator">/</span>
                            </li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        @endif
    </div>

    <!-- Right Section -->
    <div class="admin-top-bar-right">
        <!-- User Dropdown -->
        <div class="admin-user" x-data="userDropdown()">
            <button class="admin-user-btn" @click="toggle" :class="{ 'active': open }" aria-expanded="false">
                <span class="user-avatar">
                    {{-- User has no avatar column, so the initial is the avatar. --}}
                    <span class="avatar-placeholder">{{ $user ? mb_substr($user->name, 0, 1) : 'A' }}</span>
                </span>
                <span class="user-info">
                    <span class="user-name">{{ $user->name ?? 'Admin' }}</span>
                    <span class="user-role">{{ $user && $user->isAdmin() ? 'Administrator' : 'Moderator' }}</span>
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
                    <span class="user-name">{{ $user->name ?? 'Admin' }}</span>
                    <span class="user-email">{{ $user->email ?? '' }}</span>
                </div>

 

                @if(Route::has('home'))
                    <a href="{{ route('home') }}" class="dropdown-item">
                        <i class="fas fa-external-link-alt"></i> View Site
                    </a>
                @endif

                @if(Route::has('logout'))
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</nav>

@once
<style>
/* ===== ADMIN TOP BAR – PREMIUM, EVOLVED DESIGN ===== */
/* Enhanced with subtle gradients, sharper shadows, and fluid animations for a modern, high-end feel. 
   Fully integrated with Speeda's dashboard aesthetics: clean lines, indigo accents, and premium interactions. 
   No changes to Laravel logic or PHP – pure CSS evolution. */

:root {
    --admin-top-bar-height: 68px; /* Slightly slimmer for a more modern look */
    /* Kept at 0: the admin layout is top-bar only. The sidebar component that
       this variable used to offset was never rendered and has been removed. */
    --admin-sidebar-width: 0px;
    --top-bar-blur: 16px; /* Increased blur for premium glassmorphism */
    --dropdown-shadow: 0 25px 40px -10px rgba(0,0,0,0.08), 0 10px 15px -5px rgba(0,0,0,0.04);
    --accent-indigo-gradient: linear-gradient(135deg, #4f46e5, #6366f1); /* Subtle gradient for depth */
    --border-subtle: rgba(226, 232, 240, 0.7); /* Softer borders */
    --transition-fast: all 0.12s ease-out; /* Faster for snappier interactions */
}

/* === Top Bar Container === */
.admin-top-bar {
    position: fixed;
    left: 0;
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
    right: 0;
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
.admin-toggle-btn,
.admin-menu-btn {
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

.admin-menu-btn {
    width: auto;
    gap: 0.55rem;
    padding: 0 0.95rem;
    font-size: 0.9rem;
    font-weight: 800;
}

.admin-toggle-btn:hover,
.admin-menu-btn:hover,
.admin-menu-btn.active {
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
}

.admin-menu {
    position: relative;
}

.admin-menu-chevron {
    font-size: 0.72rem;
    transition: transform 0.2s ease;
}

.admin-menu-dropdown {
    position: absolute;
    top: calc(100% + 12px);
    left: 0;
    z-index: 1200;
    width: min(420px, calc(100vw - 2rem));
    max-height: min(72vh, 640px);
    overflow-y: auto;
    padding: 0.75rem;
    border: 1px solid var(--border-subtle);
    border-radius: 20px;
    background: #fff;
    box-shadow: var(--dropdown-shadow);
}

[dir="rtl"] .admin-menu-dropdown {
    left: auto;
    right: 0;
}

.admin-menu-group {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.35rem;
}

.admin-menu-group-label {
    padding: 0.6rem 0.5rem 0.35rem;
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #94a3b8;
}

.admin-menu-group-label:not(:first-child) {
    margin-top: 0.4rem;
    border-top: 1px solid var(--border-subtle);
    padding-top: 0.75rem;
}

.admin-menu-item {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    min-height: 44px;
    padding: 0.7rem 0.75rem;
    border-radius: 14px;
    color: var(--text-primary);
    font-size: 0.84rem;
    font-weight: 800;
    text-decoration: none !important;
}

.admin-menu-item i {
    width: 18px;
    color: #6366f1;
}

.admin-menu-item:hover,
.admin-menu-item.active {
    background: #eef2ff;
    color: #4338ca;
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
    .admin-logo img {
        height: 34px;
    }
}

@media (max-width: 576px) {
    .admin-top-bar {
        padding: 0 1rem;
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
            document.body.classList.remove('sidebar-collapsed');
        },
        toggleSidebar() {
            return;
        }
    }));

    // User Dropdown
    Alpine.data('userDropdown', () => ({
        open: false,
        toggle() { this.open = !this.open; },
        close() { this.open = false; }
    }));
});
</script>
@endonce
