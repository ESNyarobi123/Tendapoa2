@php
  $user = auth()->user();
  $isMuhitaji = $user && $user->role === 'muhitaji';
  $isMfanyakazi = $user && $user->role === 'mfanyakazi';
  $unreadCount = $user ? $user->unreadNotifications->count() : 0;
@endphp

<!-- Mobile Menu Button -->
<button class="tp-mobile-btn" id="mobileMenuBtn" onclick="toggleSidebar()">
  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
</button>
<div class="tp-overlay" id="mobileOverlay" onclick="closeSidebar()"></div>

<!-- Sidebar -->
<aside class="tp-sidebar" id="sidebar">
  <!-- Brand -->
  <div class="tp-sidebar-brand">
    <div class="tp-brand-icon">T</div>
    <span class="tp-brand-text">TendaPoa</span>
    <button class="tp-collapse-btn" onclick="toggleSidebarCollapse()" title="Toggle">
      <svg id="toggleIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    </button>
  </div>

  <!-- Profile -->
  <a href="{{ route('profile.edit') }}" class="tp-profile">
    <img src="{{ $user->profile_photo_url ?? '' }}" alt="" class="tp-avatar">
    <div class="tp-profile-info">
      <div class="tp-profile-name">{{ $user->name }}</div>
      <div class="tp-profile-role">{{ $isMuhitaji ? 'Mteja' : 'Mfanyakazi' }}</div>
    </div>
  </a>

  <!-- Menu -->
  <nav class="tp-menu">
    <a href="{{ route('dashboard') }}" class="tp-menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      <span class="tp-menu-text">Dashibodi</span>
    </a>

    <a href="{{ route('notifications.index') }}" class="tp-menu-item {{ request()->routeIs('notifications.index') ? 'active' : '' }}">
      <span class="tp-icon-wrap">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        @if($unreadCount > 0)<span class="tp-notif-dot"></span>@endif
      </span>
      <span class="tp-menu-text">Taarifa</span>
      @if($unreadCount > 0)<span class="tp-badge-count">{{ $unreadCount }}</span>@endif
    </a>

    <div class="tp-menu-divider"></div>

    @if($isMuhitaji)
      <a href="{{ route('jobs.create') }}" class="tp-menu-item {{ request()->routeIs('jobs.create') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        <span class="tp-menu-text">Chapisha Kazi</span>
      </a>
      <a href="{{ route('my.jobs') }}" class="tp-menu-item {{ request()->routeIs('my.jobs') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <span class="tp-menu-text">Kazi Zangu</span>
      </a>
      <a href="{{ route('my.applications') }}" class="tp-menu-item {{ request()->routeIs('my.applications') ? 'active' : '' }}">
        <span class="tp-icon-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          @if(($pendingAppsCount ?? 0) > 0)<span class="tp-notif-dot"></span>@endif
        </span>
        <span class="tp-menu-text">Maombi</span>
        @if(($pendingAppsCount ?? 0) > 0)<span class="tp-badge-count">{{ $pendingAppsCount }}</span>@endif
      </a>
      <a href="{{ route('my.jobs', ['status' => 'awaiting_payment']) }}" class="tp-menu-item {{ request()->fullUrlIs(route('my.jobs', ['status' => 'awaiting_payment'])) ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <span class="tp-menu-text">Lipia Escrow</span>
      </a>
      <a href="{{ route('wallet.deposit') }}" class="tp-menu-item {{ request()->routeIs('wallet.deposit', 'wallet.deposit.submit', 'wallet.deposit.wait', 'wallet.deposit.poll') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>
        <span class="tp-menu-text">Weka pesa</span>
      </a>
      <a href="{{ route('withdraw.form') }}" class="tp-menu-item {{ request()->routeIs('withdraw.*') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        <span class="tp-menu-text">Toa Pesa</span>
      </a>
    @elseif($isMfanyakazi)
      <a href="{{ route('feed') }}" class="tp-menu-item {{ request()->routeIs('feed') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <span class="tp-menu-text">Tafuta Kazi</span>
      </a>
      <a href="{{ route('mfanyakazi.assigned') }}" class="tp-menu-item {{ request()->routeIs('mfanyakazi.assigned') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <span class="tp-menu-text">Kazi Zangu</span>
      </a>
      <a href="{{ route('mfanyakazi.applications') }}" class="tp-menu-item {{ request()->routeIs('mfanyakazi.applications') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        <span class="tp-menu-text">Maombi yangu</span>
      </a>
      <a href="{{ route('jobs.create-mfanyakazi') }}" class="tp-menu-item {{ request()->routeIs('jobs.create-mfanyakazi') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        <span class="tp-menu-text">Chapisha Huduma</span>
      </a>
      <a href="{{ route('wallet.deposit') }}" class="tp-menu-item {{ request()->routeIs('wallet.deposit', 'wallet.deposit.submit', 'wallet.deposit.wait', 'wallet.deposit.poll') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>
        <span class="tp-menu-text">Weka pesa</span>
      </a>
      <a href="{{ route('withdraw.form') }}" class="tp-menu-item {{ request()->routeIs('withdraw.*') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        <span class="tp-menu-text">Toa Pesa</span>
      </a>
    @endif

    <div class="tp-menu-divider"></div>

    <a href="{{ route('chat.index') }}" class="tp-menu-item {{ request()->routeIs('chat.*') ? 'active' : '' }}">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      <span class="tp-menu-text">Mazungumzo</span>
    </a>
    <a href="{{ route('home') }}" class="tp-menu-item">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      <span class="tp-menu-text">Nyumbani</span>
    </a>

    <div class="tp-menu-spacer"></div>

    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="tp-menu-item tp-logout">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        <span class="tp-menu-text">Toka</span>
      </button>
    </form>
  </nav>
</aside>

<script>
  window.toggleSidebarCollapse=function(){const s=document.getElementById('sidebar'),i=document.getElementById('toggleIcon');if(!s||!i)return;s.classList.toggle('collapsed');i.innerHTML=s.classList.contains('collapsed')?'<polyline points="9 18 15 12 9 6"/>':'<polyline points="15 18 9 12 15 6"/>';localStorage.setItem('sidebarCollapsed',s.classList.contains('collapsed'))};
  window.toggleSidebar=function(){const s=document.getElementById('sidebar'),o=document.getElementById('mobileOverlay');if(!s||!o)return;s.classList.toggle('mobile-open');o.classList.toggle('active')};
  window.closeSidebar=function(){const s=document.getElementById('sidebar'),o=document.getElementById('mobileOverlay');if(!s||!o)return;s.classList.remove('mobile-open');o.classList.remove('active')};
  document.addEventListener('DOMContentLoaded',function(){const s=document.getElementById('sidebar'),i=document.getElementById('toggleIcon');if(!s||!i)return;if(window.innerWidth>1024){const v=localStorage.getItem('sidebarCollapsed');if(v===null||v==='true'){s.classList.add('collapsed');i.innerHTML='<polyline points="9 18 15 12 9 6"/>';localStorage.setItem('sidebarCollapsed','true')}}if(window.innerWidth>1024){let t;s.addEventListener('mouseenter',function(){clearTimeout(t);if(this.classList.contains('collapsed')){this.classList.remove('collapsed');i.innerHTML='<polyline points="15 18 9 12 15 6"/>'}});s.addEventListener('mouseleave',function(){if(localStorage.getItem('sidebarCollapsed')==='true'){t=setTimeout(()=>{this.classList.add('collapsed');i.innerHTML='<polyline points="9 18 15 12 9 6"/>'},300)}})}document.addEventListener('click',function(e){if(window.innerWidth<=1024){const s=document.getElementById('sidebar'),b=document.getElementById('mobileMenuBtn');if(s&&b&&!s.contains(e.target)&&!b.contains(e.target))window.closeSidebar()}})});
  window.addEventListener('resize',function(){const o=document.getElementById('mobileOverlay'),s=document.getElementById('sidebar');if(!o||!s)return;if(window.innerWidth>1024)o.classList.remove('active');else s.classList.remove('collapsed')});
</script>