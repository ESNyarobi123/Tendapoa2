<!doctype html>
<html lang="sw" class="h-full">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>TendaPoa — @yield('title', 'Jukwaa la Kazi')</title>
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
@vite(['resources/css/app.css', 'resources/js/app.js'])
@stack('styles')
</head>
<body class="h-full bg-gray-50 font-sans text-gray-800 text-sm antialiased">

@php
  $hasSidebar = auth()->check() && in_array(auth()->user()->role, ['muhitaji', 'mfanyakazi']);
@endphp

{{-- ===== GUEST / ADMIN TOP NAV ===== --}}
@if(!$hasSidebar)
<header class="sticky top-0 z-40 bg-white/80 backdrop-blur border-b border-gray-200">
  <div class="max-w-7xl mx-auto flex items-center justify-between h-14 px-4 sm:px-6">
    <a href="{{ route('home') }}" class="flex items-center gap-2 font-extrabold text-brand-600 text-lg tracking-tight">
      <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
      TendaPoa
    </a>
    <nav class="flex items-center gap-1">
      <a href="{{ route('home') }}" class="px-3 py-1.5 rounded-lg text-gray-600 hover:text-brand-700 hover:bg-brand-50 transition text-[13px] font-medium">Nyumbani</a>
      <a href="/feed" class="px-3 py-1.5 rounded-lg text-gray-600 hover:text-brand-700 hover:bg-brand-50 transition text-[13px] font-medium">Kazi Zote</a>
      @auth
        <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-lg text-gray-600 hover:text-brand-700 hover:bg-brand-50 transition text-[13px] font-medium">Dashboard</a>
        <a href="{{ route('chat.index') }}" class="px-3 py-1.5 rounded-lg text-gray-600 hover:text-brand-700 hover:bg-brand-50 transition text-[13px] font-medium">Mazungumzo</a>
        @if(auth()->user()->role === 'admin')
          <div class="relative group">
            <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 rounded-lg text-red-600 hover:bg-red-50 transition text-[13px] font-semibold">Admin</a>
            <div class="absolute right-0 top-full mt-1 w-48 bg-white rounded-xl shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 py-1">
              <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-[13px] text-gray-600 hover:bg-gray-50 hover:text-gray-900">Dashboard</a>
              <a href="{{ route('admin.users') }}" class="block px-4 py-2 text-[13px] text-gray-600 hover:bg-gray-50 hover:text-gray-900">Users</a>
              <a href="{{ route('admin.jobs') }}" class="block px-4 py-2 text-[13px] text-gray-600 hover:bg-gray-50 hover:text-gray-900">Jobs</a>
              <a href="{{ route('admin.chats') }}" class="block px-4 py-2 text-[13px] text-gray-600 hover:bg-gray-50 hover:text-gray-900">Chats</a>
              <a href="{{ route('admin.system-settings') }}" class="block px-4 py-2 text-[13px] text-gray-600 hover:bg-gray-50 hover:text-gray-900">Settings</a>
              <a href="{{ route('admin.analytics') }}" class="block px-4 py-2 text-[13px] text-gray-600 hover:bg-gray-50 hover:text-gray-900">Analytics</a>
            </div>
          </div>
        @endif
        <form method="post" action="{{ route('logout') }}" class="inline">
          @csrf
          <button type="submit" class="px-3 py-1.5 rounded-lg text-gray-500 hover:text-red-600 hover:bg-red-50 transition text-[13px] font-medium">Toka</button>
        </form>
      @else
        <a href="{{ route('login') }}" class="px-3 py-1.5 rounded-lg text-gray-600 hover:text-brand-700 hover:bg-brand-50 transition text-[13px] font-medium">Ingia</a>
        <a href="{{ route('register') }}" class="ml-1 px-4 py-1.5 rounded-lg bg-brand-600 text-white hover:bg-brand-700 transition text-[13px] font-semibold shadow-sm">Jisajili</a>
      @endauth
    </nav>
  </div>
</header>
@endif

{{-- ===== MAIN CONTENT ===== --}}
@if($hasSidebar)
  @yield('content')
@else
  <main class="max-w-5xl mx-auto px-4 sm:px-6 py-6">
    {{-- Flash messages --}}
    @if(session('success'))
      <div class="mb-4 px-4 py-3 rounded-xl bg-brand-50 border border-brand-200 text-brand-800 text-[13px] font-medium">
        {{ session('success') }}
      </div>
    @endif
    @if(session('error'))
      <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-[13px] font-medium">
        {{ session('error') }}
      </div>
    @endif
    @yield('content')
  </main>
@endif

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
@stack('scripts')
</body>
</html>
