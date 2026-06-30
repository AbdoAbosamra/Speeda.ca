@once
<style>
    .admin-sidebar {
        position: fixed;
        inset: 0 auto 0 0;
        width: 280px;
        height: 100vh;
        z-index: 1090;
        overflow-y: auto;
        background: #0f172a;
        border-right: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 18px 0 45px -32px rgba(15, 23, 42, 0.7);
    }

    .admin-sidebar-header {
        position: sticky;
        top: 0;
        z-index: 2;
        padding: 1.25rem;
        background: rgba(15, 23, 42, 0.96);
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .admin-sidebar-logo {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: #fff;
        font-weight: 800;
        font-size: 1.1rem;
    }

    .admin-sidebar-logo i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: #eef2ff;
        color: #4338ca;
    }

    .admin-sidebar-nav {
        padding: 1rem 0.75rem 1.5rem;
    }

    .admin-nav-group {
        margin-bottom: 1rem;
    }

    .admin-nav-label {
        padding: 0.75rem 0.75rem 0.5rem;
        color: #94a3b8;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .admin-nav-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-height: 46px;
        padding: 0.7rem 0.85rem;
        margin-bottom: 0.25rem;
        border-radius: 12px;
        color: #cbd5e1;
        font-weight: 700;
        font-size: 0.92rem;
        transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
    }

    .admin-nav-item i {
        width: 22px;
        color: currentColor;
    }

    .admin-nav-item:hover,
    .admin-nav-item.active {
        color: #fff;
        background: rgba(99, 102, 241, 0.22);
        transform: translateX(2px);
    }

    .admin-nav-item.admin-nav-public {
        margin-top: 0.75rem;
        background: rgba(255, 255, 255, 0.06);
    }

    @media (max-width: 768px) {
        .admin-sidebar {
            transform: translateX(-100%);
            transition: transform 0.25s ease;
        }

        body.sidebar-collapsed .admin-sidebar,
        .admin-sidebar.open {
            transform: translateX(0);
        }
    }
</style>
@endonce

<aside class="admin-sidebar" id="adminSidebar" aria-label="Admin navigation">
    <div class="admin-sidebar-header">
        <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-logo">
            <i class="fas fa-shield-alt"></i>
            <span>Speeda Admin</span>
        </a>
    </div>

    <nav class="admin-sidebar-nav">
        <div class="admin-nav-group">
            <div class="admin-nav-label">Workspace</div>
            <a href="{{ route('admin.dashboard') }}" class="admin-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-gauge-high"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.users') }}" class="admin-nav-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span>Manage Users</span>
            </a>
            <a href="{{ route('admin.provider_activity_monitor.index') }}" class="admin-nav-item {{ request()->routeIs('admin.provider_activity_monitor*') ? 'active' : '' }}">
                <i class="fas fa-briefcase"></i>
                <span>Manage Providers</span>
            </a>
            <a href="{{ route('admin.reviews') }}" class="admin-nav-item {{ request()->routeIs('admin.reviews*') ? 'active' : '' }}">
                <i class="fas fa-star"></i>
                <span>Manage Reviews</span>
            </a>
            <a href="{{ route('admin.blog.posts.index') }}" class="admin-nav-item {{ request()->routeIs('admin.blog.posts*') ? 'active' : '' }}">
                <i class="fas fa-newspaper"></i>
                <span>Manage Blogs</span>
            </a>
        </div>

        <div class="admin-nav-group">
            <div class="admin-nav-label">Content & Data</div>
            <a href="{{ route('admin.categories') }}" class="admin-nav-item {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
                <i class="fas fa-folder-tree"></i>
                <span>Categories</span>
            </a>
            <a href="{{ route('admin.locations') }}" class="admin-nav-item {{ request()->routeIs('admin.locations*') ? 'active' : '' }}">
                <i class="fas fa-location-dot"></i>
                <span>Locations</span>
            </a>
            <a href="{{ route('admin.comments') }}" class="admin-nav-item {{ request()->routeIs('admin.comments*') ? 'active' : '' }}">
                <i class="fas fa-comments"></i>
                <span>Comments</span>
            </a>
            <a href="{{ route('admin.notifications.index') }}" class="admin-nav-item {{ request()->routeIs('admin.notifications*') ? 'active' : '' }}">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
            </a>
        </div>

        <div class="admin-nav-group">
            <div class="admin-nav-label">Insights</div>
            <a href="{{ route('admin.visitors') }}" class="admin-nav-item {{ request()->routeIs('admin.visitors*') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i>
                <span>Visitor Analytics</span>
            </a>
            <a href="{{ route('admin.whatsapp_analytics.index') }}" class="admin-nav-item {{ request()->routeIs('admin.whatsapp_analytics*') ? 'active' : '' }}">
                <i class="fab fa-whatsapp"></i>
                <span>WhatsApp Analytics</span>
            </a>
            <a href="{{ route('admin.activity_logs') }}" class="admin-nav-item {{ request()->routeIs('admin.activity_logs*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i>
                <span>Activity Logs</span>
            </a>
            <a href="{{ route('home') }}" class="admin-nav-item admin-nav-public">
                <i class="fas fa-arrow-up-right-from-square"></i>
                <span>View Public Site</span>
            </a>
        </div>
    </nav>
</aside>
