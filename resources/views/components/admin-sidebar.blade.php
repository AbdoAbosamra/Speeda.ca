@once
<style>
    /* Hide main navigation in admin pages - Multiple selectors for compatibility */
    body:has(.admin-sidebar) .sp-nav,
    body:has(.admin-content-wrapper) .sp-nav,
    .admin-content-wrapper ~ .sp-nav,
    .admin-content-wrapper + .sp-nav {
        display: none !important;
    }

    .admin-sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 280px;
        background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
        z-index: 1050;
        overflow-y: auto;
        transition: transform 0.3s ease;
    }

    .admin-sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .admin-sidebar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
    }

    .admin-sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 3px;
    }

    .admin-sidebar-header {
        padding: 1.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.05);
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .admin-sidebar-logo {
        color: white;
        font-size: 1.5rem;
        font-weight: 700;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        transition: all 0.3s ease;
    }

    .admin-sidebar-logo:hover {
        color: #6366f1;
        transform: translateX(5px);
    }

    .admin-sidebar-logo i {
        font-size: 2rem;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .admin-sidebar-nav {
        padding: 1rem 0;
    }

    .admin-nav-item {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        color: rgba(255, 255, 255, 0.95);
        text-decoration: none;
        transition: all 0.18s ease;
        border-left: 3px solid transparent;
        position: relative;
        margin: 0.25rem 0.4rem;
        border-radius: 8px;
        font-size: 0.95rem;
    }

    .admin-nav-item:hover {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        border-left-color: #6366f1;
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
    }

    .admin-nav-item.active {
        background: rgba(99, 102, 241, 0.2);
        color: white;
        border-left-color: #6366f1;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .admin-nav-item.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(180deg, #6366f1, #8b5cf6);
    }

    .admin-nav-item i {
        width: 36px;
        height: 36px;
        margin-right: 0.75rem;
        font-size: 0.95rem;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: rgba(255,255,255,0.03);
        box-shadow: inset 0 -2px 0 rgba(0,0,0,0.05);
    }

    .admin-content-wrapper {
        margin-left: 280px;
        min-height: 100vh;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        padding-top: 0;
        transition: margin-left 0.3s ease;
    }

    .admin-mobile-toggle {
        display: none;
        position: fixed;
        top: 1rem;
        left: 1rem;
        z-index: 1001;
        background: linear-gradient(135deg, #1e293b, #0f172a);
        color: white;
        border: none;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .admin-mobile-toggle:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
    }

    .admin-sidebar-divider {
        padding: 1rem 1.5rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        margin-top: 1rem;
    }

    @media (max-width: 768px) {
        .admin-sidebar {
            transform: translateX(-100%);
        }

        .admin-sidebar.open {
            transform: translateX(0);
        }

        .admin-content-wrapper {
            margin-left: 0;
        }

        .admin-mobile-toggle {
            display: block;
        }
    }
</style>
@endonce

<button class="admin-mobile-toggle" onclick="toggleAdminSidebar()">
    <i class="fas fa-bars"></i>
</button>

<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-header">
        <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-logo">
            <i class="fas fa-shield-alt"></i>
            <span>Speeda Admin</span>
        </a>
    </div>
    <nav class="admin-sidebar-nav">
        {{-- Site navigation (rich, route-safe quick links) --}}
        <div style="padding:0.75rem 0.75rem 0.25rem 0.75rem;">
            <h6 style="color:rgba(255,255,255,0.85); font-size:0.9rem; margin:0 0 0.6rem 0;">{{ __('general.site_navigation') ?? 'Site' }}</h6>
            <ul style="list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:0.25rem;">
                @if(Route::has('home'))
                <li>
                    <a href="{{ route('home') }}" class="admin-nav-item" style="color:#fff !important; background: rgba(255,255,255,0.02) !important;">
                        <i class="fas fa-home" style="color:#fff; background: linear-gradient(90deg,#6366f1,#8b5cf6); -webkit-background-clip:text; -webkit-text-fill-color:transparent;"></i>
                        <span>{{ __('general.home') ?? 'Home' }}</span>
                    </a>
                </li>
                @endif

                @if(Route::has('location'))
                <li>
                    <a href="{{ route('location') }}" class="admin-nav-item" style="color:#fff !important; background: rgba(255,255,255,0.02) !important;">
                        <i class="fas fa-map-marker-alt" style="color:#fff; background: linear-gradient(90deg,#ef4444,#f97316); -webkit-background-clip:text; -webkit-text-fill-color:transparent;"></i>
                        <span>{{ __('general.locations') ?? 'Locations' }}</span>
                    </a>
                </li>
                @endif

                @if(Route::has('categories'))
                <li>
                    <a href="{{ route('categories') }}" class="admin-nav-item" style="color:#fff !important; background: rgba(255,255,255,0.02) !important;">
                        <i class="fas fa-th-large" style="color:#fff; background: linear-gradient(90deg,#10b981,#06b6d4); -webkit-background-clip:text; -webkit-text-fill-color:transparent;"></i>
                        <span>{{ __('general.categories') ?? 'Categories' }}</span>
                    </a>
                </li>
                @endif

                @if(Route::has('service-providers.index'))
                <li>
                    <a href="{{ route('service-providers.index') }}" class="admin-nav-item" style="color:#fff !important; background: rgba(255,255,255,0.02) !important;">
                        <i class="fas fa-users" style="color:#fff; background: linear-gradient(90deg,#8b5cf6,#64748b); -webkit-background-clip:text; -webkit-text-fill-color:transparent;"></i>
                        <span>{{ __('service_provider.service_providers') ?? 'Providers' }}</span>
                    </a>
                </li>
                @endif

                @if(Route::has('service-providers.create'))
                <li>
                    <a href="{{ route('service-providers.create') }}" class="admin-nav-item" style="color:#fff !important; background: rgba(255,255,255,0.02) !important;">
                        <i class="fas fa-user-plus" style="color:#fff; background: linear-gradient(90deg,#06b6d4,#3b82f6); -webkit-background-clip:text; -webkit-text-fill-color:transparent;"></i>
                        <span>{{ __('service_provider.add_provider') ?? 'Add Provider' }}</span>
                    </a>
                </li>
                @endif

                @if(Route::has('about-us'))
                <li>
                    <a href="{{ route('about-us') }}" class="admin-nav-item" style="color:#fff !important; background: rgba(255,255,255,0.02) !important;">
                        <i class="fas fa-info-circle" style="color:#fff; background: linear-gradient(90deg,#f59e0b,#ef4444); -webkit-background-clip:text; -webkit-text-fill-color:transparent;"></i>
                        <span>{{ __('general.about_us') ?? 'About' }}</span>
                    </a>
                </li>
                @endif

                @if(Route::has('help-center'))
                <li>
                    <a href="{{ route('help-center') }}" class="admin-nav-item" style="color:#fff !important; background: rgba(255,255,255,0.02) !important;">
                        <i class="fas fa-question-circle" style="color:#fff; background: linear-gradient(90deg,#60a5fa,#7c3aed); -webkit-background-clip:text; -webkit-text-fill-color:transparent;"></i>
                        <span>{{ __('general.help_center') ?? 'Help' }}</span>
                    </a>
                </li>
                @endif

                @if(Route::has('privacy-policy'))
                <li>
                    <a href="{{ route('privacy-policy') }}" class="admin-nav-item" style="color:#fff !important; background: rgba(255,255,255,0.02) !important;">
                        <i class="fas fa-user-secret" style="color:#fff; background: linear-gradient(90deg,#06b6d4,#4ade80); -webkit-background-clip:text; -webkit-text-fill-color:transparent;"></i>
                        <span>{{ __('general.privacy') ?? 'Privacy' }}</span>
                    </a>
                </li>
                @endif

                @if(Route::has('terms-of-service'))
                <li>
                    <a href="{{ route('terms-of-service') }}" class="admin-nav-item" style="color:#fff !important; background: rgba(255,255,255,0.02) !important;">
                        <i class="fas fa-file-contract" style="color:#fff; background: linear-gradient(90deg,#22c55e,#16a34a); -webkit-background-clip:text; -webkit-text-fill-color:transparent;"></i>
                        <span>{{ __('general.terms') ?? 'Terms' }}</span>
                    </a>
                </li>
                @endif
            </ul>
        </div>

        <a href="{{ route('admin.dashboard') }}"
           class="admin-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i>
            <span>{{ __('admin.dashboard') }}</span>
        </a>
        <a href="{{ route('admin.locations') }}"
           class="admin-nav-item {{ request()->routeIs('admin.locations*') ? 'active' : '' }}">
            <i class="fas fa-map-marker-alt"></i>
            <span>{{ __('admin.manage_locations') }}</span>
        </a>
        <a href="{{ route('admin.categories') }}"
           class="admin-nav-item {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
            <i class="fas fa-folder"></i>
            <span>{{ __('admin.manage_categories') }}</span>
        </a>
        <a href="{{ route('admin.visitors') }}"
           class="admin-nav-item {{ request()->routeIs('admin.visitors*') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i>
            <span>Visitor Analytics</span>
        </a>
        <div class="admin-sidebar-divider">
            <a href="{{ route('service-providers.index') }}" class="admin-nav-item">
                <i class="fas fa-external-link-alt"></i>
                <span>{{ __('admin.view_site') }}</span>
            </a>
        </div>
    </nav>
</aside>

<script>
function toggleAdminSidebar() {
    document.getElementById('adminSidebar').classList.toggle('open');
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('adminSidebar');
    const toggle = document.querySelector('.admin-mobile-toggle');

    if (window.innerWidth <= 768 &&
        sidebar &&
        !sidebar.contains(event.target) &&
        !toggle.contains(event.target) &&
        sidebar.classList.contains('open')) {
        sidebar.classList.remove('open');
    }
});
</script>
