<!doctype html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin — ' . ($settings['platform_name'] ?? 'TendaPoa'))</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @vite(['resources/js/admin-entry.js'])
    @stack('styles')
</head>
<body class="adm-body">
    @php
        $nav = $adminNavBadges ?? [];
        $wPending = (int) ($nav['withdrawals_pending'] ?? 0);
        $disputes = (int) ($nav['disputed_jobs'] ?? 0);
        $unread = auth()->check() ? auth()->user()->unreadNotifications->count() : 0;
    @endphp

    <button type="button" class="adm-menu-fab" id="admMobileMenu" aria-label="Fungua menyu">☰</button>
    <div class="adm-overlay" id="admOverlay" aria-hidden="true"></div>

    <div class="adm-shell">
        <aside class="adm-sidebar" id="admSidebar">
            <div class="adm-sidebar-head">
                <a href="{{ route('admin.dashboard') }}" class="adm-brand">
                    @if(isset($settings['platform_logo']))
                        <img src="{{ asset('storage/' . $settings['platform_logo']) }}" alt="" class="adm-brand-logo-img">
                    @else
                        <span class="adm-brand-mark">TP</span>
                    @endif
                    <span class="adm-brand-text">{{ $settings['platform_name'] ?? 'TendaPoa' }}</span>
                </a>
                <button type="button" class="adm-sidebar-toggle" id="admSidebarToggle" aria-label="Punguza menyu">
                    <span id="admToggleIcon">◀</span>
                </button>
            </div>

            @auth
                <div class="adm-profile">
                    <div class="adm-profile-av">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                    <div class="adm-profile-text">
                        <div class="adm-profile-name">{{ auth()->user()->name }}</div>
                        <div class="adm-profile-role">Msimamizi</div>
                    </div>
                </div>
            @endauth

            <nav class="adm-nav">
                <div class="adm-nav-label">Msingi</div>
                <a href="{{ route('admin.dashboard') }}"
                    class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon">📊</span>
                    <span class="adm-nav-text">Dashibodi</span>
                </a>
                <a href="{{ route('notifications.index') }}"
                    class="{{ request()->routeIs('notifications.index') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon">🔔</span>
                    <span class="adm-nav-text">
                        Taarifa
                        @if($unread > 0)
                            <span class="adm-nav-badge">{{ $unread > 99 ? '99+' : $unread }}</span>
                        @endif
                    </span>
                </a>
                <a href="{{ route('admin.users') }}"
                    class="{{ request()->routeIs('admin.users') || request()->routeIs('admin.user.*') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon">👥</span>
                    <span class="adm-nav-text">Watumiaji</span>
                </a>
                <a href="{{ route('admin.jobs') }}"
                    class="{{ request()->routeIs('admin.jobs') || request()->routeIs('admin.job.*') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon">💼</span>
                    <span class="adm-nav-text">
                        Kazi
                        @if($disputes > 0)
                            <span class="adm-nav-badge adm-nav-badge--amber" title="Mgogoro">{{ $disputes > 99 ? '99+' : $disputes }}</span>
                        @endif
                    </span>
                </a>
                <a href="{{ route('admin.categories') }}"
                    class="{{ request()->routeIs('admin.categories') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon">📁</span>
                    <span class="adm-nav-text">Makundi</span>
                </a>

                <div class="adm-nav-label">Fedha</div>
                <a href="{{ route('admin.withdrawals') }}"
                    class="{{ request()->routeIs('admin.withdrawals') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon">💰</span>
                    <span class="adm-nav-text">
                        Kutoa
                        @if($wPending > 0)
                            <span class="adm-nav-badge adm-nav-badge--cyan">{{ $wPending > 99 ? '99+' : $wPending }}</span>
                        @endif
                    </span>
                </a>
                <a href="{{ route('admin.commissions') }}"
                    class="{{ request()->routeIs('admin.commissions') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon">🏦</span>
                    <span class="adm-nav-text">Kamishoni</span>
                </a>

                <div class="adm-nav-label">Mawasiliano</div>
                <a href="{{ route('admin.chats') }}"
                    class="{{ request()->routeIs('admin.chats') || request()->routeIs('admin.chat.*') || request()->routeIs('admin.user.chats') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon">💬</span>
                    <span class="adm-nav-text">Mazungumzo</span>
                </a>
                <a href="{{ route('admin.broadcast') }}"
                    class="{{ request()->routeIs('admin.broadcast') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon">📢</span>
                    <span class="adm-nav-text">Tangazo</span>
                </a>

                <div class="adm-nav-label">Ripoti</div>
                <a href="{{ route('admin.analytics') }}"
                    class="{{ request()->routeIs('admin.analytics') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon">📈</span>
                    <span class="adm-nav-text">Takwimu</span>
                </a>
                <a href="{{ route('admin.completed-jobs') }}"
                    class="{{ request()->routeIs('admin.completed-jobs') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon">✅</span>
                    <span class="adm-nav-text">Kazi zilizomalika</span>
                </a>
                <a href="{{ route('admin.system-logs') }}"
                    class="{{ request()->routeIs('admin.system-logs') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon">📋</span>
                    <span class="adm-nav-text">Kumbukumbu</span>
                </a>
                <a href="{{ route('admin.system-settings') }}"
                    class="{{ request()->routeIs('admin.system-settings') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon">⚙️</span>
                    <span class="adm-nav-text">Mipangilio</span>
                </a>
            </nav>

            <div class="adm-sidebar-foot">
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="adm-nav-logout">
                        <span class="adm-nav-icon">🚪</span>
                        <span class="adm-nav-text">Toka</span>
                    </button>
                </form>
            </div>
        </aside>

        <main class="adm-main">
            @if(session('success'))
                <div class="adm-flash adm-flash--success" role="status">{{ session('success') }}</div>
            @endif
            @if(session('status'))
                <div class="adm-flash adm-flash--status" role="status">{{ session('status') }}</div>
            @endif
            @if(session('error'))
                <div class="adm-flash adm-flash--error" role="alert">{{ session('error') }}</div>
            @endif
            @if(isset($errors) && $errors->any())
                <div class="adm-flash adm-flash--error" role="alert">
                    <strong>Hakuna kufanikiwa:</strong>
                    <ul>
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        (function () {
            var sidebar = document.getElementById('admSidebar');
            var toggle = document.getElementById('admSidebarToggle');
            var icon = document.getElementById('admToggleIcon');
            var mobileBtn = document.getElementById('admMobileMenu');
            var overlay = document.getElementById('admOverlay');

            if (localStorage.getItem('admSidebarCollapsed') === '1') {
                sidebar.classList.add('is-collapsed');
                if (icon) icon.textContent = '▶';
            }

            if (toggle) {
                toggle.addEventListener('click', function () {
                    sidebar.classList.toggle('is-collapsed');
                    var c = sidebar.classList.contains('is-collapsed');
                    if (icon) icon.textContent = c ? '▶' : '◀';
                    localStorage.setItem('admSidebarCollapsed', c ? '1' : '0');
                });
            }

            function closeMobile() {
                sidebar.classList.remove('is-mobile-open');
                overlay.classList.remove('is-open');
            }

            function openMobile() {
                sidebar.classList.add('is-mobile-open');
                overlay.classList.add('is-open');
            }

            if (mobileBtn) {
                mobileBtn.addEventListener('click', function () {
                    if (sidebar.classList.contains('is-mobile-open')) closeMobile();
                    else openMobile();
                });
            }
            overlay.addEventListener('click', closeMobile);

            sidebar.querySelectorAll('a').forEach(function (a) {
                a.addEventListener('click', function () {
                    if (window.innerWidth <= 900) closeMobile();
                });
            });

            window.addEventListener('resize', function () {
                if (window.innerWidth > 900) closeMobile();
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
