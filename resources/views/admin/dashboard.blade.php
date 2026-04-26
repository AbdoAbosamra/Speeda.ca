@extends('layouts.app')

@section('content')

<!-- Main Content Area -->
<div class="admin-content-wrapper" >
    <div class="container py-4" >

        {{-- Header Section – Premium, Elevated Design --}}
        <div class="dashboard-header mb-5">
            <div class="header-content">
                <div class="header-badge">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </div>
                <h1 class="header-title">Admin Dashboard</h1>
                <p class="header-subtitle">
                    <span>Welcome back,</span>
                    <span class="fw-bold">{{ auth()->user()->name }}</span>
                </p>
            </div>
        </div>

        {{-- Visitor Statistics – Premium Cards Grid – Improved 4-column layout for better visibility --}}
        <div class="stats-section mb-5">
            <h5 class="section-title">
                <i class="fas fa-chart-bar me-2"></i>Traffic Overview
            </h5>
            <div class="row g-3 g-lg-4 row-cols-2 row-cols-md-4">
                <!-- Live Visitors -->
                <div class="col">
                    <div class="stat-card stat-card-live">
                        <div class="stat-card-header">
                            <div class="stat-icon stat-icon-indigo">
                                <i class="fas fa-users"></i>
                            </div>
                            <span class="stat-label">Live Visitors</span>
                        </div>
                        <div class="stat-value live-count">{{ $stats['liveVisitors'] ?? 0 }}</div>
                        <div class="stat-indicator stat-indicator-live">
                            <span class="indicator-dot"></span>
                            <span>Active Now</span>
                        </div>
                    </div>
                </div>

                <!-- Visitors Today -->
                <div class="col">
                    <div class="stat-card stat-card-today">
                        <div class="stat-card-header">
                            <div class="stat-icon stat-icon-pink">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <span class="stat-label">Today</span>
                        </div>
                        <div class="stat-value">{{ $stats['visitorsToday'] ?? 0 }}</div>
                        <div class="stat-meta">Unique Visitors</div>
                    </div>
                </div>

                <!-- Last 7 Days -->
                <div class="col">
                    <div class="stat-card stat-card-week">
                        <div class="stat-card-header">
                            <div class="stat-icon stat-icon-blue">
                                <i class="fas fa-calendar-week"></i>
                            </div>
                            <span class="stat-label">Last 7 Days</span>
                        </div>
                        <div class="stat-value">{{ $stats['last7Days'] ?? 0 }}</div>
                        <div class="stat-meta">Unique Visitors</div>
                    </div>
                </div>

                <!-- Total Visitors -->
                <div class="col">
                    <div class="stat-card stat-card-total">
                        <div class="stat-card-header">
                            <div class="stat-icon stat-icon-teal">
                                <i class="fas fa-infinity"></i>
                            </div>
                            <span class="stat-label">All Time</span>
                        </div>
                        <div class="stat-value">{{ $stats['totalVisitors'] ?? 0 }}</div>
                        <div class="stat-meta">Total Unique Visitors</div>
                    </div>
                </div>
            </div>
        </div>

{{-- Management & Quick Actions – Premium Command Cards --}}
        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-6">
                <div class="quick-actions-section">
                    <h5 class="section-title">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                    <div class="action-cards-grid">
                        <!-- Manage Locations -->
                        <a href="{{ route('admin.locations') }}" class="action-card">
                            <div class="action-card-icon bg-indigo">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="action-card-content">
                                <h6 class="action-card-title">Manage Locations</h6>
                                <p class="action-card-meta">{{ $stats['activeLocations'] ?? 0 }} Active / {{ $stats['totalLocations'] ?? 0 }} Total</p>
                            </div>
                            <i class="fas fa-chevron-right action-card-arrow"></i>
                        </a>

                        <!-- Manage Categories -->
                        <a href="{{ route('admin.categories') }}" class="action-card">
                            <div class="action-card-icon bg-green">
                                <i class="fas fa-folder"></i>
                            </div>
                            <div class="action-card-content">
                                <h6 class="action-card-title">Manage Categories</h6>
                                <p class="action-card-meta">{{ $stats['activeCategories'] ?? 0 }} Active / {{ $stats['totalCategories'] ?? 0 }} Total</p>
                            </div>
                            <i class="fas fa-chevron-right action-card-arrow"></i>
                        </a>

                        <!-- Users Management -->
                        <a href="{{ route('admin.users') }}" class="action-card">
                            <div class="action-card-icon bg-blue">
                                <i class="fas fa-users-cog"></i>
                            </div>
                            <div class="action-card-content">
                                <h6 class="action-card-title">Users Management</h6>
                                <p class="action-card-meta">{{ $stats['totalUsers'] ?? 0 }} Users</p>
                            </div>
                            <i class="fas fa-chevron-right action-card-arrow"></i>
                        </a>

                        <!-- Reviews Management -->
                        <a href="{{ route('admin.reviews') }}" class="action-card">
                            <div class="action-card-icon bg-orange">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="action-card-content">
                                <h6 class="action-card-title">Reviews Management</h6>
                                <p class="action-card-meta">{{ $stats['totalReviews'] ?? 0 }} Total Reviews</p>
                            </div>
                            <i class="fas fa-chevron-right action-card-arrow"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="notifications-section">
                    <h5 class="section-title">
                        <i class="fas fa-bell me-2"></i>Notifications
                    </h5>
                    <div class="notification-card">
                        <div class="notification-card-header">
                            <div class="notification-icon">
                                <i class="fas fa-paper-plane"></i>
                            </div>
                            <div class="notification-info">
                                <h6>Push Notifications</h6>
                                <p>Manage and send notifications to service providers</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.notifications.index') }}" class="btn btn-premium">
                            <i class="fas fa-cog me-2"></i>Manage
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Moderation Queue & Alerts --}}
        <div class="mb-5">
            <h5 class="section-title">
                <i class="fas fa-shield-alt me-2"></i>Moderation Queue
            </h5>
            <div class="row g-4">
                <!-- Pending Reviews -->
                <div class="col-md-4">
                    <a href="{{ route('admin.reviews', ['status' => 'pending']) }}" class="action-card">
                        <div class="action-card-icon bg-yellow">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="action-card-content">
                            <h6 class="action-card-title">Review Moderation</h6>
                            <p class="action-card-meta">
                                @if(($stats['pendingReviews'] ?? 0) > 0)
                                    <span class="badge badge-warning">{{ $stats['pendingReviews'] }}</span> Awaiting Approval
                                @else
                                    <i class="fas fa-check-circle text-success me-1"></i> All clear
                                @endif
                            </p>
                        </div>
                        <i class="fas fa-chevron-right action-card-arrow"></i>
                    </a>
                </div>

                <!-- New Users Today -->
                <div class="col-md-4">
                    <div class="stats-mini-card">
                        <div class="stats-mini-card-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="stats-mini-card-content">
                            <h6>New Users Today</h6>
                            <span class="stats-mini-value">{{ $stats['newUsersToday'] ?? 0 }}</span>
                            <span class="stats-mini-label">registered today</span>
                        </div>
                    </div>
                </div>

                <!-- Cache Clear -->
                <div class="col-md-4">
                    <div class="cache-card">
                        <form action="{{ route('admin.clear-cache') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-cache">
                                <i class="fas fa-broom me-2"></i>Clear Caches
                            </button>
                        </form>
                        <small class="cache-hint">Refresh application data</small>
                    </div>
                </div>
            </div>
        </div>

</div>
</div>

{{-- Premium Dashboard Styles --}}
<style>
    /* ===== HEADER SECTION ===== */
    .dashboard-header {
        padding: 1.5rem 2rem;
        background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(249,250,252,0.9));
        border-radius: 1.5rem;
        border: 1px solid var(--border-light);
        backdrop-filter: blur(12px);
        position: relative;
        overflow: hidden;
    }
    .dashboard-header::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 280px;
        height: 100%;
        background: linear-gradient(135deg, rgba(79,70,229,0.03) 0%, transparent 60%);
        pointer-events: none;
    }
    .header-content {
        position: relative;
        z-index: 1;
    }
    .header-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.4rem 1rem;
        background: linear-gradient(135deg, #eef2ff, #e0e7ff);
        color: var(--accent-indigo);
        border-radius: 2rem;
        font-size: 0.8rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        border: 1px solid rgba(79,70,229,0.1);
    }
    .header-badge i {
        font-size: 0.85rem;
    }
    .header-title {
        font-size: 2rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
        letter-spacing: -0.02em;
    }
    .header-subtitle {
        font-size: 1.1rem;
        color: var(--text-secondary);
        margin-bottom: 0;
    }
    .header-subtitle .fw-bold {
        color: var(--text-primary);
    }

    /* ===== SECTION TITLES ===== */
    .section-title {
        display: flex;
        align-items: center;
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--border-light);
    }
    .section-title i {
        color: var(--accent-indigo);
    }

    /* ===== PREMIUM STAT CARDS ===== */
    .stat-card {
        background: white;
        border-radius: 1.25rem;
        padding: 1.25rem 1.5rem;
        border: 1px solid var(--border-light);
        box-shadow: var(--shadow-sm);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        border-radius: 1.25rem 1.25rem 0 0;
        transition: height 0.25s ease;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px -12px rgba(0,0,0,0.08);
        border-color: rgba(79,70,229,0.15);
    }
    .stat-card:hover::before {
        height: 6px;
    }
    .stat-card-live::before { background: linear-gradient(90deg, #4f46e5, #818cf8); }
    .stat-card-today::before { background: linear-gradient(90deg, #db2777, #f472b6); }
    .stat-card-week::before { background: linear-gradient(90deg, #2563eb, #60a5fa); }
    .stat-card-total::before { background: linear-gradient(90deg, #0891b2, #22d3ee); }

    .stat-card-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    .stat-icon {
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        font-size: 1.15rem;
        transition: transform 0.25s ease;
    }
    .stat-card:hover .stat-icon {
        transform: scale(1.08) rotate(4deg);
    }
    .stat-icon-indigo { background: #eef2ff; color: #4f46e5; }
    .stat-icon-pink { background: #fdf2f8; color: #db2777; }
    .stat-icon-blue { background: #eff6ff; color: #2563eb; }
    .stat-icon-teal { background: #f0fdf9; color: #0891b2; }

    .stat-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .stat-value {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1.1;
        letter-spacing: -0.03em;
        margin-bottom: 0.5rem;
    }
    .stat-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .stat-indicator-live {
        color: #059669;
        background: #ecfdf5;
        padding: 0.35rem 0.75rem;
        border-radius: 2rem;
    }
    .indicator-dot {
        width: 8px;
        height: 8px;
        background: #10b981;
        border-radius: 50%;
        animation: pulse-live 2s infinite;
    }
    @keyframes pulse-live {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.6; transform: scale(1.2); }
    }
    .stat-meta {
        font-size: 0.8rem;
        color: var(--text-secondary);
        font-weight: 500;
    }

    /* ===== ACTION CARDS ===== */
    .action-cards-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    @media (max-width: 576px) {
        .action-cards-grid {
            grid-template-columns: 1fr;
        }
    }
    .action-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        background: white;
        border-radius: 1.25rem;
        border: 1px solid var(--border-light);
        text-decoration: none;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .action-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--accent-indigo);
        transform: scaleY(0);
        transition: transform 0.25s ease;
    }
    .action-card:hover {
        border-color: rgba(79,70,229,0.25);
        box-shadow: 0 12px 32px -8px rgba(79,70,229,0.12);
        transform: translateX(4px);
    }
    .action-card:hover::before {
        transform: scaleY(1);
    }
    .action-card-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        font-size: 1.2rem;
        flex-shrink: 0;
        transition: transform 0.25s ease;
    }
    .action-card:hover .action-card-icon {
        transform: scale(1.1) rotate(4deg);
    }
    .bg-indigo { background: #eef2ff; color: #4f46e5; }
    .bg-green { background: #ecfdf5; color: #059669; }
    .bg-blue { background: #eff6ff; color: #2563eb; }
    .bg-orange { background: #fff7ed; color: #d97706; }
    .bg-yellow { background: #fefce8; color: #b45309; }

    .action-card-content {
        flex: 1;
        min-width: 0;
    }
    .action-card-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }
    .action-card-meta {
        font-size: 0.8rem;
        color: var(--text-secondary);
        margin-bottom: 0;
    }
    .action-card-arrow {
        color: var(--text-secondary);
        font-size: 0.9rem;
        transition: transform 0.25s ease;
    }
    .action-card:hover .action-card-arrow {
        transform: translateX(4px);
        color: var(--accent-indigo);
    }

    /* ===== NOTIFICATION CARD ===== */
    .notification-card {
        background: white;
        border-radius: 1.25rem;
        padding: 1.5rem;
        border: 1px solid var(--border-light);
        display: flex;
        flex-direction: column;
        gap: 1rem;
        height: 100%;
    }
    .notification-card-header {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }
    .notification-icon {
        width: 52px;
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f5f3ff, #ede9fe);
        color: #7c3aed;
        border-radius: 16px;
        font-size: 1.3rem;
        flex-shrink: 0;
    }
    .notification-info h6 {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.35rem;
    }
    .notification-info p {
        font-size: 0.9rem;
        color: var(--text-secondary);
        margin-bottom: 0;
        line-height: 1.5;
    }

    /* ===== PREMIUM BUTTON ===== */
    .btn-premium {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        color: white;
        border: none;
        border-radius: 0.75rem;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 12px rgba(79,70,229,0.25);
    }
    .btn-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(79,70,229,0.35);
        color: white;
    }

    /* ===== BADGE ===== */
    .badge-warning {
        background: #fef3c7;
        color: #b45309;
        font-weight: 600;
        padding: 0.25rem 0.6rem;
        font-size: 0.75rem;
    }

    /* ===== STATS MINI CARD ===== */
    .stats-mini-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        background: white;
        border-radius: 1.25rem;
        border: 1px solid var(--border-light);
        height: 100%;
    }
    .stats-mini-card-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #ecfdf5;
        color: #059669;
        border-radius: 14px;
        font-size: 1.2rem;
    }
    .stats-mini-card-content h6 {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 0.25rem;
    }
    .stats-mini-value {
        font-size: 2rem;
        font-weight: 800;
        color: var(--text-primary);
        letter-spacing: -0.02em;
    }
    .stats-mini-label {
        font-size: 0.8rem;
        color: var(--text-secondary);
    }

    /* ===== CACHE CARD ===== */
    .cache-card {
        padding: 1.25rem 1.5rem;
        background: white;
        border-radius: 1.25rem;
        border: 1px solid var(--border-light);
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
    }
    .btn-cache {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1.25rem;
        background: white;
        color: var(--text-secondary);
        border: 2px dashed var(--border-light);
        border-radius: 0.75rem;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.25s ease;
    }
    .btn-cache:hover {
        border-color: var(--accent-indigo);
        color: var(--accent-indigo);
        background: #f8fafc;
    }
    .cache-hint {
        font-size: 0.75rem;
        color: var(--text-secondary);
        margin-top: 0.75rem;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .dashboard-header {
            padding: 1.25rem;
        }
        .header-title {
            font-size: 1.5rem;
        }
        .stat-value {
            font-size: 1.75rem;
        }
        .action-card {
            padding: 1rem;
        }
    }

    /* Admin Content Wrapper */
    .admin-content-wrapper {
        padding-top: 20px;
    }

    /* RTL Support */
    [dir="rtl"] .section-title i { margin-right: 0; margin-left: 0.5rem; }
    [dir="rtl"] .action-card:hover .action-card-arrow { transform: translateX(-4px); }
</style>

<script>
    // Live count update – untouched
    (function () {
        function updateLiveCount() {
            fetch('{{ route("admin.visitors.live-count") }}')
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

@endsection
