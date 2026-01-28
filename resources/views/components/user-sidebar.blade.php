@php
  $user = auth()->user();
  $isMuhitaji = $user && $user->role === 'muhitaji';
  $isMfanyakazi = $user && $user->role === 'mfanyakazi';
@endphp

<!-- Mobile Menu Button -->
<button class="mobile-menu-btn" id="mobileMenuBtn" onclick="toggleSidebar()">
  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
    stroke-linecap="round" stroke-linejoin="round">
    <line x1="3" y1="12" x2="21" y2="12"></line>
    <line x1="3" y1="6" x2="21" y2="6"></line>
    <line x1="3" y1="18" x2="21" y2="18"></line>
  </svg>
</button>

<!-- Mobile Overlay -->
<div class="mobile-overlay" id="mobileOverlay" onclick="closeSidebar()"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <button class="sidebar-toggle" onclick="toggleSidebarCollapse()" title="Toggle Sidebar">
      <svg id="toggleIcon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="15 18 9 12 15 6"></polyline>
      </svg>
    </button>
    <div class="sidebar-logo">{{ $isMuhitaji ? '🏠' : '💼' }} Tendapoa</div>
  </div>

  <div class="sidebar-profile"
    style="padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1);">
    <div class="profile-img-container" style="position: relative; width: 80px; height: 80px; margin: 0 auto 10px;">
      <img src="{{ auth()->user()->profile_photo_url }}" alt="Profile"
        style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 3px solid #2563eb;">
      <a href="{{ route('profile.edit') }}"
        style="position: absolute; bottom: 0; right: 0; background: #2563eb; color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round">
          <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
          <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
        </svg>
      </a>
    </div>
    <div class="profile-name" style="color: white; font-weight: 600; font-size: 1rem;">{{ auth()->user()->name }}</div>
    <div class="profile-role" style="color: rgba(255,255,255,0.6); font-size: 0.8rem; text-transform: uppercase;">
      {{ auth()->user()->role }}</div>
  </div>

  <nav class="sidebar-menu">
    <a href="{{ route('dashboard') }}" class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
      <span class="menu-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="3" width="7" height="7"></rect>
          <rect x="14" y="3" width="7" height="7"></rect>
          <rect x="14" y="14" width="7" height="7"></rect>
          <rect x="3" y="14" width="7" height="7"></rect>
        </svg>
      </span>
      <span class="menu-text">Dashibodi</span>
    </a>

    <a href="{{ route('notifications.index') }}"
      class="menu-item {{ request()->routeIs('notifications.index') ? 'active' : '' }}">
      <span class="menu-icon" style="position: relative;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
          <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        @if(auth()->user()->unreadNotifications->count() > 0)
          <span
            style="position: absolute; top: -5px; right: -5px; background: #ef4444; color: white; border-radius: 50%; width: 8px; height: 8px; border: 1px solid #1e293b;"></span>
        @endif
      </span>
      <span class="menu-text">
        Taarifa
        @if(auth()->user()->unreadNotifications->count() > 0)
          <span
            style="background: #ef4444; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem; margin-left: auto;">{{ auth()->user()->unreadNotifications->count() }}</span>
        @endif
      </span>
    </a>

    @if($isMuhitaji)
      <a href="{{ route('jobs.create') }}" class="menu-item {{ request()->routeIs('jobs.create') ? 'active' : '' }}">
        <span class="menu-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg>
        </span>
        <span class="menu-text">Chapisha Kazi</span>
      </a>

      <a href="{{ route('my.jobs') }}" class="menu-item {{ request()->routeIs('my.jobs') ? 'active' : '' }}">
        <span class="menu-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
            <polyline points="10 9 9 9 8 9"></polyline>
          </svg>
        </span>
        <span class="menu-text">Kazi Zangu</span>
      </a>

      <a href="{{ route('my.jobs', ['status' => 'pending_payment']) }}"
        class="menu-item {{ request()->fullUrlIs(route('my.jobs', ['status' => 'pending_payment'])) ? 'active' : '' }}">
        <span class="menu-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>
          </svg>
        </span>
        <span class="menu-text">Malipo Yanayosubiri</span>
      </a>

      <a href="{{ route('withdraw.form') }}" class="menu-item {{ request()->routeIs('withdraw.*') ? 'active' : '' }}">
        <span class="menu-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
            <line x1="1" y1="10" x2="23" y2="10"></line>
          </svg>
        </span>
        <span class="menu-text">Toa Pesa</span>
      </a>
    @elseif($isMfanyakazi)
      <a href="{{ route('feed') }}" class="menu-item {{ request()->routeIs('feed') ? 'active' : '' }}">
        <span class="menu-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.35-4.35"></path>
          </svg>
        </span>
        <span class="menu-text">Tafuta Kazi</span>
      </a>

      <a href="{{ route('mfanyakazi.assigned') }}"
        class="menu-item {{ request()->routeIs('mfanyakazi.assigned') ? 'active' : '' }}">
        <span class="menu-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
            <polyline points="10 9 9 9 8 9"></polyline>
          </svg>
        </span>
        <span class="menu-text">Kazi Zangu</span>
      </a>

      <a href="{{ route('jobs.create-mfanyakazi') }}"
        class="menu-item {{ request()->routeIs('jobs.create-mfanyakazi') ? 'active' : '' }}">
        <span class="menu-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg>
        </span>
        <span class="menu-text">Chapisha Huduma</span>
      </a>

      <a href="{{ route('withdraw.form') }}" class="menu-item {{ request()->routeIs('withdraw.*') ? 'active' : '' }}">
        <span class="menu-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
            <line x1="1" y1="10" x2="23" y2="10"></line>
          </svg>
        </span>
        <span class="menu-text">Toa Pesa</span>
      </a>
    @endif

    <a href="{{ route('chat.index') }}" class="menu-item {{ request()->routeIs('chat.*') ? 'active' : '' }}">
      <span class="menu-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
      </span>
      <span class="menu-text">Mazungumzo</span>
    </a>

    <a href="{{ route('home') }}" class="menu-item">
      <span class="menu-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round">
          <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
          <polyline points="9 22 9 12 15 12 15 22"></polyline>
        </svg>
      </span>
      <span class="menu-text">Nyumbani</span>
    </a>

    <form method="POST" action="{{ route('logout') }}" style="margin: 8px 12px;">
      @csrf
      <button type="submit" class="menu-item logout-btn"
        style="width: 100%; background: transparent; border: none; cursor: pointer; text-align: left;">
        <span class="menu-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
          </svg>
        </span>
        <span class="menu-text">Toka</span>
      </button>
    </form>
  </nav>
</aside>

<style>
  /* Sidebar Styles */
  .sidebar {
    position: fixed;
    left: 0;
    top: 0;
    height: 100vh;
    width: 280px;
    background: #1e293b;
    box-shadow: 4px 0 24px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow-y: auto;
    overflow-x: hidden;
    border-right: 1px solid rgba(255, 255, 255, 0.05);
  }

  .sidebar.collapsed {
    width: 80px;
  }

  .sidebar-header {
    padding: 24px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    display: flex;
    align-items: center;
    gap: 12px;
    min-height: 80px;
    background: rgba(0, 0, 0, 0.1);
  }

  .sidebar-logo {
    font-size: 1.5rem;
    font-weight: 800;
    color: white;
    white-space: nowrap;
    overflow: hidden;
    transition: opacity 0.3s;
    letter-spacing: -0.5px;
  }

  .sidebar.collapsed .sidebar-logo {
    opacity: 0;
    width: 0;
  }

  .sidebar-toggle {
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
    flex-shrink: 0;
    font-size: 14px;
  }

  .sidebar-toggle svg {
    width: 14px;
    height: 14px;
  }

  .sidebar-toggle:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  }

  .sidebar-menu {
    padding: 12px 0;
  }

  .menu-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 20px;
    color: rgba(255, 255, 255, 0.85);
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    margin: 2px 12px;
    border-radius: 10px;
    font-weight: 500;
  }

  .menu-item:hover {
    background: rgba(37, 99, 235, 0.15);
    color: white;
    transform: translateX(4px);
  }

  .menu-item.active {
    background: #2563eb;
    color: white;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    font-weight: 600;
  }

  .menu-item.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 70%;
    background: white;
    border-radius: 0 3px 3px 0;
  }

  .menu-item.logout-btn {
    color: rgba(239, 68, 68, 0.9);
  }

  .menu-item.logout-btn:hover {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
  }

  .menu-icon {
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: transform 0.3s;
  }

  .menu-icon svg {
    width: 100%;
    height: 100%;
    stroke-width: 2.5;
  }

  .menu-item:hover .menu-icon {
    transform: scale(1.1);
  }

  .menu-item.active .menu-icon {
    transform: scale(1.1);
  }

  .menu-text {
    font-weight: 600;
    font-size: 0.95rem;
    white-space: nowrap;
    transition: opacity 0.3s;
  }

  .sidebar.collapsed .menu-text {
    opacity: 0;
    width: 0;
    overflow: hidden;
  }

  /* Mobile Menu Button */
  .mobile-menu-btn {
    display: none;
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 1001;
    background: #2563eb;
    color: white;
    border: none;
    width: 48px;
    height: 48px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    cursor: pointer;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    transition: all 0.3s;
  }

  .mobile-menu-btn:hover {
    background: #1e40af;
    transform: scale(1.05);
    box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
  }

  .mobile-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
    opacity: 0;
    transition: opacity 0.3s;
  }

  .mobile-overlay.active {
    opacity: 1;
  }

  /* Responsive */
  @media (max-width: 1024px) {
    .sidebar {
      transform: translateX(-100%);
    }

    .sidebar.mobile-open {
      transform: translateX(0);
    }

    .mobile-menu-btn {
      display: flex;
    }

    .mobile-overlay {
      display: block;
    }
  }
</style>

<script>
  // Sidebar functionality - Global functions for onclick handlers
  window.toggleSidebarCollapse = function () {
    const sidebar = document.getElementById('sidebar');
    const toggleIcon = document.getElementById('toggleIcon');
    if (!sidebar || !toggleIcon) return;

    sidebar.classList.toggle('collapsed');
    if (sidebar.classList.contains('collapsed')) {
      toggleIcon.innerHTML = '<polyline points="9 18 15 12 9 6"></polyline>';
      toggleIcon.setAttribute('transform', 'rotate(0)');
    } else {
      toggleIcon.innerHTML = '<polyline points="15 18 9 12 15 6"></polyline>';
      toggleIcon.setAttribute('transform', 'rotate(0)');
    }

    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
  };

  window.toggleSidebar = function () {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('mobileOverlay');
    if (!sidebar || !overlay) return;

    sidebar.classList.toggle('mobile-open');
    overlay.classList.toggle('active');
  };

  window.closeSidebar = function () {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('mobileOverlay');
    if (!sidebar || !overlay) return;

    sidebar.classList.remove('mobile-open');
    overlay.classList.remove('active');
  };

  // Initialize sidebar
  document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const toggleIcon = document.getElementById('toggleIcon');
    if (!sidebar || !toggleIcon) return;

    // Auto-collapse on desktop by default
    if (window.innerWidth > 1024) {
      const savedState = localStorage.getItem('sidebarCollapsed');
      if (savedState === null) {
        // First time - auto collapse
        sidebar.classList.add('collapsed');
        toggleIcon.innerHTML = '<polyline points="9 18 15 12 9 6"></polyline>';
        localStorage.setItem('sidebarCollapsed', 'true');
      } else if (savedState === 'true') {
        sidebar.classList.add('collapsed');
        toggleIcon.innerHTML = '<polyline points="9 18 15 12 9 6"></polyline>';
      }
    }

    // Auto-collapse on desktop hover
    if (window.innerWidth > 1024) {
      let hoverTimeout;
      let isHovering = false;

      sidebar.addEventListener('mouseenter', function () {
        isHovering = true;
        clearTimeout(hoverTimeout);
        if (this.classList.contains('collapsed')) {
          this.classList.remove('collapsed');
          toggleIcon.innerHTML = '<polyline points="15 18 9 12 15 6"></polyline>';
        }
      });

      sidebar.addEventListener('mouseleave', function () {
        isHovering = false;
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsed) {
          hoverTimeout = setTimeout(() => {
            if (!isHovering) {
              this.classList.add('collapsed');
              toggleIcon.innerHTML = '<polyline points="9 18 15 12 9 6"></polyline>';
            }
          }, 300);
        }
      });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function (e) {
      if (window.innerWidth <= 1024) {
        const sidebar = document.getElementById('sidebar');
        const menuBtn = document.getElementById('mobileMenuBtn');
        if (sidebar && menuBtn && !sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
          window.closeSidebar();
        }
      }
    });
  });

  // Handle window resize
  window.addEventListener('resize', function () {
    const overlay = document.getElementById('mobileOverlay');
    const sidebar = document.getElementById('sidebar');
    if (!overlay || !sidebar) return;

    if (window.innerWidth > 1024) {
      overlay.classList.remove('active');
    } else {
      sidebar.classList.remove('collapsed');
    }
  });
</script>