@php
  $isAdmin = auth()->check() && auth()->user()->role === 'admin';
  $layout = $isAdmin ? 'layouts.admin' : 'layouts.app';
  $useUserSidebar = auth()->check() && in_array(auth()->user()->role, ['muhitaji', 'mfanyakazi'], true);
  $filter = request('filter') === 'unread' ? 'unread' : 'all';
  $dashUrl = $isAdmin ? route('admin.dashboard') : route('dashboard');

  $groupNotifications = function ($items) {
      return $items->groupBy(function ($notification) {
          if ($notification->created_at->isToday()) {
              return 'Leo';
          }
          if ($notification->created_at->isYesterday()) {
              return 'Jana';
          }
          if ($notification->created_at->isCurrentWeek()) {
              return 'Wiki hii';
          }

          return 'Zamani';
      });
  };

  $groupOrder = ['Leo', 'Jana', 'Wiki hii', 'Zamani'];
@endphp
@extends($layout)
@section('title', 'Taarifa')

@section('content')
@if($isAdmin)
<div class="adm-page adm-notif-page">
    @include('admin.partials.page-hero', [
        'title' => 'Kituo cha Taarifa',
        'subtitle' => 'Kazi, malipo, huduma, na masasisho ya mfumo — yote mahali pamoja.',
        'icon' => '🔔',
        'actions' => ($unreadCount > 0
            ? '<form action="' . route('notifications.readAll') . '" method="POST" class="adm-inline-form" style="display:inline;margin:0;">'
                . csrf_field()
                . '<button type="submit" class="adm-btn adm-btn--primary">✓ Soma zote</button></form>'
            : '')
            . '<a class="adm-btn adm-btn--ghost" href="' . $dashUrl . '">Dashibodi</a>',
    ])

    <div class="adm-stat-grid adm-notif-stat-grid">
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">📬</span>
            <span class="adm-stat-tile__val">{{ number_format($totalCount) }}</span>
            <span class="adm-stat-tile__lbl">Jumla</span>
        </div>
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">✨</span>
            <span class="adm-stat-tile__val">{{ number_format($unreadCount) }}</span>
            <span class="adm-stat-tile__lbl">Zisizosomwa</span>
        </div>
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">📄</span>
            <span class="adm-stat-tile__val">{{ $notifications->count() }}</span>
            <span class="adm-stat-tile__lbl">Ukurasa huu ({{ $notifications->total() }})</span>
        </div>
    </div>

    <div class="adm-notif-filters adm-card">
        <span>Onyesha</span>
        <a href="{{ route('notifications.index') }}"
            class="adm-notif-pill {{ $filter === 'all' ? 'is-active' : '' }}">Zote</a>
        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}"
            class="adm-notif-pill {{ $filter === 'unread' ? 'is-active-warn' : '' }}">
            Zisizosomwa
            @if($unreadCount > 0)
                <span class="adm-notif-pill-count">{{ $unreadCount }}</span>
            @endif
        </a>
    </div>

    @if($notifications->count() > 0)
        @php $adminGroups = $groupNotifications($notifications); @endphp
        @foreach($groupOrder as $groupLabel)
            @if($adminGroups->has($groupLabel))
                <h3 class="adm-notif-group-label">{{ $groupLabel }}</h3>
                <ul class="adm-notif-list">
                    @foreach ($adminGroups[$groupLabel] as $notification)
                        @include('notifications.partials.card', ['notification' => $notification, 'theme' => 'admin'])
                    @endforeach
                </ul>
            @endif
        @endforeach
        <div class="adm-pagination">
            {{ $notifications->appends(request()->query())->links() }}
        </div>
    @else
        <div class="adm-empty adm-card">
            <span class="adm-empty__ico" aria-hidden="true">{{ $filter === 'unread' ? '✨' : '🔔' }}</span>
            @if($filter === 'unread')
                <h3>Hakuna zisizosomwa</h3>
                <p>Umesoma taarifa zote. Chagua &ldquo;Zote&rdquo; kuona historia.</p>
                <a href="{{ route('notifications.index') }}" class="adm-btn adm-btn--primary">Ona taarifa zote</a>
            @else
                <h3>Bado hakuna taarifa</h3>
                <p>Utapokea taarifa kuhusu kazi, malipo, na masasisho ya mfumo hapa.</p>
                <a href="{{ $dashUrl }}" class="adm-btn adm-btn--primary">Rudi dashibodi</a>
            @endif
        </div>
    @endif
</div>
@else
<div class="flex min-h-screen bg-slate-50">
  @if($useUserSidebar)
    @include('components.user-sidebar')
  @endif

  <main class="{{ $useUserSidebar ? 'tp-main w-full min-w-0 p-4 pt-16 sm:p-6 lg:pt-6' : 'w-full px-4 py-6 sm:px-6' }}">
    <div class="tp-notif-page">

      @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[13px] font-semibold text-emerald-900 shadow-sm">
          {{ session('success') }}
        </div>
      @endif

      <section class="tp-notif-hero">
        <div class="tp-notif-hero-glow" aria-hidden="true"></div>
        <div class="tp-notif-hero-inner">
          <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
              <span class="tp-notif-hero-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                Kituo cha taarifa
              </span>
              <h1 class="tp-notif-hero-title">Taarifa zako</h1>
              <p class="tp-notif-hero-sub">
                Fuatilia kazi, malipo, huduma, na ujumbe — kila kitu muhimu kiko hapa, wazi na rahisi kusoma.
              </p>
            </div>
            <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
              @if($unreadCount > 0)
                <form action="{{ route('notifications.readAll') }}" method="POST" class="w-full sm:w-auto">
                  @csrf
                  <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-5 py-2.5 text-[12px] font-bold text-slate-900 shadow-lg transition hover:bg-slate-100 sm:w-auto">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    Soma zote
                  </button>
                </form>
              @endif
              <a href="{{ $dashUrl }}" class="inline-flex w-full items-center justify-center rounded-xl border border-white/20 bg-white/10 px-5 py-2.5 text-[12px] font-semibold text-white backdrop-blur-sm transition hover:bg-white/15 sm:w-auto">
                Dashibodi
              </a>
            </div>
          </div>

          <div class="tp-notif-hero-stats">
            <div class="tp-notif-stat">
              <p class="tp-notif-stat-val">{{ number_format($totalCount) }}</p>
              <p class="tp-notif-stat-lbl">Jumla</p>
            </div>
            <div class="tp-notif-stat tp-notif-stat--hot">
              <p class="tp-notif-stat-val">{{ number_format($unreadCount) }}</p>
              <p class="tp-notif-stat-lbl">Zisizosomwa</p>
            </div>
            <div class="tp-notif-stat">
              <p class="tp-notif-stat-val">{{ $notifications->count() }}</p>
              <p class="tp-notif-stat-lbl">Ukurasa huu</p>
            </div>
          </div>
        </div>
      </section>

      <div class="tp-notif-toolbar">
        <p class="hidden px-2 text-[11px] font-bold uppercase tracking-wide text-slate-500 sm:block">Chuja taarifa</p>
        <div class="tp-notif-segment w-full sm:w-auto sm:min-w-[280px]">
          <a href="{{ route('notifications.index') }}" class="{{ $filter === 'all' ? 'is-active' : '' }}">
            Zote
          </a>
          <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" class="{{ $filter === 'unread' ? 'is-active-warn' : '' }}">
            Zisizosomwa
            @if($unreadCount > 0)
              <span class="tp-notif-segment-count">{{ $unreadCount }}</span>
            @endif
          </a>
        </div>
      </div>

      @if($notifications->count() > 0)
        @php $userGroups = $groupNotifications($notifications); @endphp
        @foreach($groupOrder as $groupLabel)
          @if($userGroups->has($groupLabel))
            <section class="tp-notif-group">
              <h2 class="tp-notif-group-title">{{ $groupLabel }}</h2>
              <ul class="space-y-3">
                @foreach ($userGroups[$groupLabel] as $notification)
                  @include('notifications.partials.card', ['notification' => $notification, 'theme' => 'app'])
                @endforeach
              </ul>
            </section>
          @endif
        @endforeach

        <div class="pt-2 text-center text-[12px] text-slate-600 [&_.pagination]:flex [&_.pagination]:flex-wrap [&_.pagination]:justify-center [&_.pagination]:gap-1.5 [&_a]:rounded-xl [&_a]:border [&_a]:border-slate-200 [&_a]:bg-white [&_a]:px-3.5 [&_a]:py-2 [&_a]:font-semibold [&_a]:text-slate-700 [&_a]:shadow-sm [&_a]:transition [&_a]:hover:border-brand-200 [&_a]:hover:text-brand-700 [&_span]:rounded-xl [&_span]:bg-brand-600 [&_span]:px-3.5 [&_span]:py-2 [&_span]:font-bold [&_span]:text-white">
          {{ $notifications->appends(request()->query())->links() }}
        </div>
      @else
        <div class="tp-notif-empty">
          <div class="tp-notif-empty-icon">
            {{ $filter === 'unread' ? '✨' : '🔔' }}
          </div>
          @if($filter === 'unread')
            <h3 class="mt-6 text-xl font-extrabold text-slate-900">Hakuna zisizosomwa</h3>
            <p class="mx-auto mt-2 max-w-sm text-[14px] leading-relaxed text-slate-600">
              Hongera! Umesoma taarifa zote. Rudi hapa wakati wowote kupata masasisho mapya.
            </p>
            <a href="{{ route('notifications.index') }}" class="tp-notif-btn-primary mt-6 inline-flex w-full max-w-xs sm:w-auto">
              Ona taarifa zote
            </a>
          @else
            <h3 class="mt-6 text-xl font-extrabold text-slate-900">Bado hakuna taarifa</h3>
            <p class="mx-auto mt-2 max-w-sm text-[14px] leading-relaxed text-slate-600">
              Utapokea arifa kuhusu kazi, malipo, huduma, na ujumbe kutoka TendaPoa hapa.
            </p>
            <a href="{{ $dashUrl }}" class="tp-notif-btn-primary mt-6 inline-flex w-full max-w-xs sm:w-auto">
              Rudi dashibodi
            </a>
          @endif
        </div>
      @endif
    </div>
  </main>
</div>
@endif
@endsection
