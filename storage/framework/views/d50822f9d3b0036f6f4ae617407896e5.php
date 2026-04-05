<!doctype html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Admin — ' . ($settings['platform_name'] ?? 'TendaPoa')); ?></title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/admin-entry.js']); ?>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="adm-body">
    <?php
        $nav = $adminNavBadges ?? [];
        $wPending = (int) ($nav['withdrawals_pending'] ?? 0);
        $disputes = (int) ($nav['disputed_jobs'] ?? 0);
        $unread = auth()->check() ? auth()->user()->unreadNotifications->count() : 0;
    ?>

    <button type="button" class="adm-menu-fab" id="admMobileMenu" aria-label="Fungua menyu">☰</button>
    <div class="adm-overlay" id="admOverlay" aria-hidden="true"></div>

    <div class="adm-shell">
        <aside class="adm-sidebar" id="admSidebar">
            <div class="adm-sidebar-head">
                <a href="<?php echo e(route('admin.dashboard')); ?>" class="adm-brand">
                    <?php if(isset($settings['platform_logo'])): ?>
                        <img src="<?php echo e(asset('storage/' . $settings['platform_logo'])); ?>" alt="" class="adm-brand-logo-img">
                    <?php else: ?>
                        <span class="adm-brand-mark">TP</span>
                    <?php endif; ?>
                    <span class="adm-brand-text"><?php echo e($settings['platform_name'] ?? 'TendaPoa'); ?></span>
                </a>
                <button type="button" class="adm-sidebar-toggle" id="admSidebarToggle" aria-label="Punguza menyu">
                    <span id="admToggleIcon">◀</span>
                </button>
            </div>

            <?php if(auth()->guard()->check()): ?>
                <div class="adm-profile">
                    <div class="adm-profile-av"><?php echo e(strtoupper(substr(auth()->user()->name, 0, 2))); ?></div>
                    <div class="adm-profile-text">
                        <div class="adm-profile-name"><?php echo e(auth()->user()->name); ?></div>
                        <div class="adm-profile-role">Msimamizi</div>
                    </div>
                </div>
            <?php endif; ?>

            <nav class="adm-nav">
                <div class="adm-nav-label">Msingi</div>
                <a href="<?php echo e(route('admin.dashboard')); ?>"
                    class="<?php echo e(request()->routeIs('admin.dashboard') ? 'is-active' : ''); ?>">
                    <span class="adm-nav-icon">📊</span>
                    <span class="adm-nav-text">Dashibodi</span>
                </a>
                <a href="<?php echo e(route('notifications.index')); ?>"
                    class="<?php echo e(request()->routeIs('notifications.index') ? 'is-active' : ''); ?>">
                    <span class="adm-nav-icon">🔔</span>
                    <span class="adm-nav-text">
                        Taarifa
                        <?php if($unread > 0): ?>
                            <span class="adm-nav-badge"><?php echo e($unread > 99 ? '99+' : $unread); ?></span>
                        <?php endif; ?>
                    </span>
                </a>
                <a href="<?php echo e(route('admin.users')); ?>"
                    class="<?php echo e(request()->routeIs('admin.users') || request()->routeIs('admin.user.*') ? 'is-active' : ''); ?>">
                    <span class="adm-nav-icon">👥</span>
                    <span class="adm-nav-text">Watumiaji</span>
                </a>
                <a href="<?php echo e(route('admin.jobs')); ?>"
                    class="<?php echo e(request()->routeIs('admin.jobs') || request()->routeIs('admin.job.*') ? 'is-active' : ''); ?>">
                    <span class="adm-nav-icon">💼</span>
                    <span class="adm-nav-text">
                        Kazi
                        <?php if($disputes > 0): ?>
                            <span class="adm-nav-badge adm-nav-badge--amber" title="Mgogoro"><?php echo e($disputes > 99 ? '99+' : $disputes); ?></span>
                        <?php endif; ?>
                    </span>
                </a>
                <a href="<?php echo e(route('admin.categories')); ?>"
                    class="<?php echo e(request()->routeIs('admin.categories') ? 'is-active' : ''); ?>">
                    <span class="adm-nav-icon">📁</span>
                    <span class="adm-nav-text">Makundi</span>
                </a>

                <div class="adm-nav-label">Fedha</div>
                <a href="<?php echo e(route('admin.withdrawals')); ?>"
                    class="<?php echo e(request()->routeIs('admin.withdrawals') ? 'is-active' : ''); ?>">
                    <span class="adm-nav-icon">💰</span>
                    <span class="adm-nav-text">
                        Kutoa
                        <?php if($wPending > 0): ?>
                            <span class="adm-nav-badge adm-nav-badge--cyan"><?php echo e($wPending > 99 ? '99+' : $wPending); ?></span>
                        <?php endif; ?>
                    </span>
                </a>
                <a href="<?php echo e(route('admin.commissions')); ?>"
                    class="<?php echo e(request()->routeIs('admin.commissions') ? 'is-active' : ''); ?>">
                    <span class="adm-nav-icon">🏦</span>
                    <span class="adm-nav-text">Kamishoni</span>
                </a>

                <div class="adm-nav-label">Mawasiliano</div>
                <a href="<?php echo e(route('admin.chats')); ?>"
                    class="<?php echo e(request()->routeIs('admin.chats') || request()->routeIs('admin.chat.*') || request()->routeIs('admin.user.chats') ? 'is-active' : ''); ?>">
                    <span class="adm-nav-icon">💬</span>
                    <span class="adm-nav-text">Mazungumzo</span>
                </a>
                <a href="<?php echo e(route('admin.broadcast')); ?>"
                    class="<?php echo e(request()->routeIs('admin.broadcast') ? 'is-active' : ''); ?>">
                    <span class="adm-nav-icon">📢</span>
                    <span class="adm-nav-text">Tangazo</span>
                </a>

                <div class="adm-nav-label">Ripoti</div>
                <a href="<?php echo e(route('admin.analytics')); ?>"
                    class="<?php echo e(request()->routeIs('admin.analytics') ? 'is-active' : ''); ?>">
                    <span class="adm-nav-icon">📈</span>
                    <span class="adm-nav-text">Takwimu</span>
                </a>
                <a href="<?php echo e(route('admin.completed-jobs')); ?>"
                    class="<?php echo e(request()->routeIs('admin.completed-jobs') ? 'is-active' : ''); ?>">
                    <span class="adm-nav-icon">✅</span>
                    <span class="adm-nav-text">Kazi zilizomalika</span>
                </a>
                <a href="<?php echo e(route('admin.system-logs')); ?>"
                    class="<?php echo e(request()->routeIs('admin.system-logs') ? 'is-active' : ''); ?>">
                    <span class="adm-nav-icon">📋</span>
                    <span class="adm-nav-text">Kumbukumbu</span>
                </a>
                <a href="<?php echo e(route('admin.system-settings')); ?>"
                    class="<?php echo e(request()->routeIs('admin.system-settings') ? 'is-active' : ''); ?>">
                    <span class="adm-nav-icon">⚙️</span>
                    <span class="adm-nav-text">Mipangilio</span>
                </a>
            </nav>

            <div class="adm-sidebar-foot">
                <form method="POST" action="<?php echo e(route('logout')); ?>" class="m-0">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="adm-nav-logout">
                        <span class="adm-nav-icon">🚪</span>
                        <span class="adm-nav-text">Toka</span>
                    </button>
                </form>
            </div>
        </aside>

        <main class="adm-main">
            <?php if(session('success')): ?>
                <div class="adm-flash adm-flash--success" role="status"><?php echo e(session('success')); ?></div>
            <?php endif; ?>
            <?php if(session('status')): ?>
                <div class="adm-flash adm-flash--status" role="status"><?php echo e(session('status')); ?></div>
            <?php endif; ?>
            <?php if(session('error')): ?>
                <div class="adm-flash adm-flash--error" role="alert"><?php echo e(session('error')); ?></div>
            <?php endif; ?>
            <?php if(isset($errors) && $errors->any()): ?>
                <div class="adm-flash adm-flash--error" role="alert">
                    <strong>Hakuna kufanikiwa:</strong>
                    <ul>
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($err); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php echo $__env->yieldContent('content'); ?>
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
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/layouts/admin.blade.php ENDPATH**/ ?>