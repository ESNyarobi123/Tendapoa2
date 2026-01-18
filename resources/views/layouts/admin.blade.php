<!doctype html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin — TendaPoa')</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        /* ====== Admin Layout - Dark Theme with Sidebar ====== */
        :root {
            --sidebar-bg: linear-gradient(180deg, #0f0f23 0%, #1a1a3e 100%);
            --primary: #6366f1;
            --secondary: #06b6d4;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #f43f5e;
            --card-bg: rgba(255,255,255,0.05);
            --card-bg-hover: rgba(255,255,255,0.08);
            --text-primary: #ffffff;
            --text-muted: #94a3b8;
            --border: rgba(255,255,255,0.1);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.4), 0 4px 6px -2px rgba(0, 0, 0, 0.3);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: system-ui, Segoe UI, Roboto, Ubuntu, Arial, sans-serif;
            background: linear-gradient(135deg, #0a0a1a 0%, #1a1a3e 50%, #2d1b69 100%);
            color: var(--text-primary);
            margin: 0;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Admin Container */
        .admin-container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Sidebar */
        .admin-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 280px;
            background: var(--sidebar-bg);
            backdrop-filter: blur(20px);
            border-right: 1px solid var(--border);
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .admin-sidebar.sidebar-collapsed {
            width: 70px;
        }

        .admin-sidebar:hover:not(.sidebar-collapsed) {
            width: 280px;
        }

        /* Sidebar Header */
        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-height: 80px;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            white-space: nowrap;
            overflow: hidden;
        }

        .sidebar-logo-icon {
            font-size: 28px;
            min-width: 32px;
        }

        .sidebar-logo-text {
            font-size: 20px;
            font-weight: 800;
            color: var(--text-primary);
            opacity: 1;
            transition: opacity 0.3s;
        }

        .sidebar-collapsed .sidebar-logo-text {
            opacity: 0;
            width: 0;
        }

        .sidebar-toggle {
            background: rgba(255,255,255,0.1);
            border: none;
            color: var(--text-primary);
            width: 32px;
            height: 32px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: all 0.3s;
            min-width: 32px;
            flex-shrink: 0;
        }

        .sidebar-toggle:hover {
            background: rgba(255,255,255,0.2);
            transform: scale(1.1);
        }

        /* User Profile Section */
        .sidebar-profile {
            padding: 20px;
            border-bottom: 1px solid var(--border);
        }

        .profile-info {
            display: flex;
            align-items: center;
            gap: 12px;
            white-space: nowrap;
            overflow: hidden;
        }

        .profile-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }

        .profile-details {
            overflow: hidden;
            transition: opacity 0.3s;
        }

        .sidebar-collapsed .profile-details {
            opacity: 0;
            width: 0;
        }

        .profile-name {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 2px;
        }

        .profile-role {
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Sidebar Menu */
        .sidebar-menu {
            padding: 16px 0;
            overflow-y: auto;
            overflow-x: hidden;
            flex: 1;
            height: calc(100vh - 240px);
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.3) transparent;
        }

        .sidebar-menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.3s;
            white-space: nowrap;
            position: relative;
            margin: 4px 12px;
            border-radius: 12px;
        }

        .sidebar-menu-item:hover {
            background: var(--card-bg);
            color: var(--text-primary);
            transform: translateX(4px);
        }

        .sidebar-menu-item.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--text-primary);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }

        .sidebar-menu-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 60%;
            background: var(--text-primary);
            border-radius: 0 4px 4px 0;
        }

        .sidebar-menu-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            min-width: 28px;
            width: 28px;
            height: 28px;
            text-align: center;
            flex-shrink: 0;
            opacity: 1 !important;
            line-height: 1;
        }

        .sidebar-menu-text {
            font-size: 14px;
            font-weight: 600;
            transition: opacity 0.3s;
        }

        .sidebar-collapsed .sidebar-menu-text {
            opacity: 0;
            width: 0;
        }

        /* Sidebar Footer */
        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid var(--border);
            margin-top: auto;
        }

        /* Main Content Area */
        .admin-main {
            flex: 1;
            margin-left: 280px;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
            padding: 24px;
        }

        .admin-sidebar.sidebar-collapsed + .admin-main {
            margin-left: 70px;
        }

        /* Top Bar */
        .admin-topbar {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 16px 24px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .topbar-title {
            font-size: 24px;
            font-weight: 800;
            color: var(--text-primary);
        }

        .topbar-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        /* Glass Card */
        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 24px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            transition: all 0.3s;
        }

        .glass-card:hover {
            background: var(--card-bg-hover);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        /* Stat Card */
        .stat-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 24px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            transition: all 0.3s;
        }

        .stat-card:hover {
            background: var(--card-bg-hover);
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        /* Live Indicator */
        .pulse-live {
            width: 8px;
            height: 8px;
            background: var(--success);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
        }

        /* Mobile Overlay */
        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .mobile-overlay.active {
            opacity: 1;
        }

        /* Mobile Menu Button */
        .mobile-menu-btn {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: var(--primary);
            color: white;
            border: none;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            font-size: 20px;
            cursor: pointer;
            box-shadow: var(--shadow-lg);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .admin-sidebar {
                transform: translateX(-100%);
                z-index: 1000;
            }

            .admin-sidebar.mobile-open {
                transform: translateX(0);
            }

            .admin-main {
                margin-left: 0 !important;
                padding: 80px 16px 24px;
            }

            .mobile-overlay {
                display: block;
            }

            .sidebar-toggle {
                display: none;
            }
        }

        /* Scrollbar Styling */
        .sidebar-menu::-webkit-scrollbar {
            width: 8px;
        }

        .sidebar-menu::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 4px;
            margin: 8px 0;
        }

        .sidebar-menu::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-menu::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
        
        /* Ensure icons are always visible */
        .sidebar-menu-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            min-width: 28px;
            width: 28px;
            height: 28px;
            text-align: center;
            flex-shrink: 0;
            opacity: 1 !important;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Mobile Menu Button -->
        <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle menu">☰</button>

        <!-- Mobile Overlay -->
        <div class="mobile-overlay" id="mobileOverlay"></div>

        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <!-- Sidebar Header -->
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <span class="sidebar-logo-icon">🧹</span>
                    <span class="sidebar-logo-text">TENDAPOA</span>
                </div>
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                    <span id="toggleIcon">◀</span>
                </button>
            </div>

            <!-- User Profile -->
            @auth
            <div class="sidebar-profile">
                <div class="profile-info">
                    <div class="profile-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="profile-details">
                        <div class="profile-name">{{ auth()->user()->name }}</div>
                        <div class="profile-role">@admin • Super Admin</div>
                    </div>
                </div>
            </div>
            @endauth

            <!-- Sidebar Menu -->
            <nav class="sidebar-menu">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <span class="sidebar-menu-icon">📊</span>
                    <span class="sidebar-menu-text">Dashboard</span>
                </a>
                <a href="{{ route('admin.users') }}" class="sidebar-menu-item {{ request()->routeIs('admin.users') || request()->routeIs('admin.user.*') ? 'active' : '' }}">
                    <span class="sidebar-menu-icon">👥</span>
                    <span class="sidebar-menu-text">Users</span>
                </a>
                <a href="{{ route('admin.jobs') }}" class="sidebar-menu-item {{ request()->routeIs('admin.jobs') || request()->routeIs('admin.job.*') ? 'active' : '' }}">
                    <span class="sidebar-menu-icon">💼</span>
                    <span class="sidebar-menu-text">Jobs</span>
                </a>
                <a href="{{ route('admin.withdrawals') }}" class="sidebar-menu-item {{ request()->routeIs('admin.withdrawals') || request()->routeIs('admin.withdrawals.*') ? 'active' : '' }}">
                    <span class="sidebar-menu-icon">💰</span>
                    <span class="sidebar-menu-text">Withdrawals</span>
                </a>
                <a href="{{ route('admin.chats') }}" class="sidebar-menu-item {{ request()->routeIs('admin.chats') || request()->routeIs('admin.chat.*') ? 'active' : '' }}">
                    <span class="sidebar-menu-icon">💬</span>
                    <span class="sidebar-menu-text">Conversations</span>
                </a>
                <a href="{{ route('admin.analytics') }}" class="sidebar-menu-item {{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
                    <span class="sidebar-menu-icon">📈</span>
                    <span class="sidebar-menu-text">Analytics</span>
                </a>
                <a href="{{ route('admin.system-logs') }}" class="sidebar-menu-item {{ request()->routeIs('admin.system-logs') ? 'active' : '' }}">
                    <span class="sidebar-menu-icon">📋</span>
                    <span class="sidebar-menu-text">System Logs</span>
                </a>
                <a href="{{ route('admin.system-settings') }}" class="sidebar-menu-item {{ request()->routeIs('admin.system-settings') || request()->routeIs('admin.system-settings.*') ? 'active' : '' }}">
                    <span class="sidebar-menu-icon">⚙️</span>
                    <span class="sidebar-menu-text">Settings</span>
                </a>
            </nav>

            <!-- Sidebar Footer -->
            <div class="sidebar-footer">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="sidebar-menu-item" style="width: 100%; margin: 0; border-radius: 12px; cursor: pointer; background: none; font: inherit;">
                        <span class="sidebar-menu-icon">🚪</span>
                        <span class="sidebar-menu-text">Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            @yield('content')
        </main>
    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // Sidebar Management
        const sidebar = document.getElementById('adminSidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const toggleIcon = document.getElementById('toggleIcon');
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileOverlay = document.getElementById('mobileOverlay');
        let autoHideTimer = null;
        const AUTO_HIDE_DELAY = 3000;

        // Load saved state from localStorage
        const savedState = localStorage.getItem('sidebarCollapsed');
        if (savedState === 'true') {
            sidebar.classList.add('sidebar-collapsed');
            toggleIcon.textContent = '▶';
        }

        // Toggle sidebar
        function toggleSidebar() {
            sidebar.classList.toggle('sidebar-collapsed');
            const isCollapsed = sidebar.classList.contains('sidebar-collapsed');
            toggleIcon.textContent = isCollapsed ? '▶' : '◀';
            localStorage.setItem('sidebarCollapsed', isCollapsed);
            
            // Clear auto-hide timer
            if (autoHideTimer) {
                clearTimeout(autoHideTimer);
                autoHideTimer = null;
            }
        }

        // Auto-hide on mouse leave (desktop only)
        if (window.innerWidth > 768) {
            sidebar.addEventListener('mouseleave', () => {
                if (!sidebar.classList.contains('sidebar-collapsed')) {
                    autoHideTimer = setTimeout(() => {
                        sidebar.classList.add('sidebar-collapsed');
                        toggleIcon.textContent = '▶';
                        localStorage.setItem('sidebarCollapsed', 'true');
                    }, AUTO_HIDE_DELAY);
                }
            });

            sidebar.addEventListener('mouseenter', () => {
                if (autoHideTimer) {
                    clearTimeout(autoHideTimer);
                    autoHideTimer = null;
                }
            });
        }

        // Manual toggle
        sidebarToggle.addEventListener('click', toggleSidebar);

        // Mobile menu toggle
        function toggleMobileMenu() {
            sidebar.classList.toggle('mobile-open');
            mobileOverlay.classList.toggle('active');
        }

        mobileMenuBtn.addEventListener('click', toggleMobileMenu);
        mobileOverlay.addEventListener('click', toggleMobileMenu);

        // Close mobile menu on route change
        document.querySelectorAll('.sidebar-menu-item').forEach(item => {
            item.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('mobile-open');
                    mobileOverlay.classList.remove('active');
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('mobile-open');
                mobileOverlay.classList.remove('active');
            }
        });
    </script>
    @stack('scripts')
</body>
</html>