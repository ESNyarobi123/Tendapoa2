<?php
  $isAdmin = auth()->check() && auth()->user()->role === 'admin';
  $layout = $isAdmin ? 'layouts.admin' : 'layouts.app';
  $useUserSidebar = auth()->check() && in_array(auth()->user()->role, ['muhitaji', 'mfanyakazi'], true);
  $filter = request('filter') === 'unread' ? 'unread' : 'all';
  $dashUrl = $isAdmin ? route('admin.dashboard') : route('dashboard');
?>

<?php $__env->startSection('title', 'Taarifa'); ?>

<?php $__env->startSection('content'); ?>
<?php if($isAdmin): ?>
  <main class="adm-notifications-main">
    <div class="adm-notif-page">
      <section class="adm-notif-hero">
        <div class="adm-notif-hero-inner">
          <div>
            <p class="adm-notif-kicker">Kituo cha taarifa</p>
            <h1>Taarifa zako</h1>
            <p class="adm-notif-lead">Kazi, malipo, na masasisho ya mfumo — yote hapa (data halisi kutoka database).</p>
          </div>
          <div class="adm-notif-actions-top">
            <?php if($unreadCount > 0): ?>
              <form action="<?php echo e(route('notifications.readAll')); ?>" method="POST" class="m-0 inline">
                <?php echo csrf_field(); ?>
                <button type="submit" class="adm-notif-btn adm-notif-btn--solid">✓ Soma zote</button>
              </form>
            <?php endif; ?>
            <a href="<?php echo e($dashUrl); ?>" class="adm-notif-btn adm-notif-btn--outline">Dashibodi</a>
          </div>
        </div>
      </section>

      <div class="adm-notif-stats">
        <div class="adm-notif-stat">
          <div class="adm-notif-stat-label">Jumla</div>
          <div class="adm-notif-stat-value"><?php echo e(number_format($totalCount)); ?></div>
        </div>
        <div class="adm-notif-stat">
          <div class="adm-notif-stat-label">Zisizosomwa</div>
          <div class="adm-notif-stat-value"><?php echo e(number_format($unreadCount)); ?></div>
        </div>
        <div class="adm-notif-stat">
          <div class="adm-notif-stat-label">Ukurasa huu</div>
          <div class="adm-notif-stat-value"><?php echo e($notifications->count()); ?></div>
          <div class="adm-notif-stat-sub">Kati ya <?php echo e($notifications->total()); ?> zinazolingana</div>
        </div>
      </div>

      <div class="adm-notif-filters">
        <span>Onyesha</span>
        <a href="<?php echo e(route('notifications.index', array_filter(['filter' => null]))); ?>"
          class="adm-notif-pill <?php echo e($filter === 'all' ? 'is-active' : ''); ?>">Zote</a>
        <a href="<?php echo e(route('notifications.index', ['filter' => 'unread'])); ?>"
          class="adm-notif-pill <?php echo e($filter === 'unread' ? 'is-active-warn' : ''); ?>">
          Zisizosomwa
          <?php if($unreadCount > 0): ?>
            <span class="adm-notif-pill-count"><?php echo e($unreadCount); ?></span>
          <?php endif; ?>
        </a>
      </div>

      <?php if($notifications->count() > 0): ?>
        <ul class="adm-notif-list">
          <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
              $type = (string) ($notification->data['type'] ?? 'info');
              $icon = '🔔';
              $iconMod = '';
              if ($type === 'admin_message' || str_contains($type, 'admin')) {
                  $icon = '📢';
                  $iconMod = 'adm-notif-icon--violet';
              } elseif (str_contains($type, 'job')) {
                  $icon = '💼';
                  $iconMod = 'adm-notif-icon--sky';
              } elseif (str_contains($type, 'money') || str_contains($type, 'payment') || isset($notification->data['earnings'])) {
                  $icon = '💰';
                  $iconMod = 'adm-notif-icon--emerald';
              } elseif (str_contains($type, 'alert') || str_contains($type, 'cancel')) {
                  $icon = '⚠️';
                  $iconMod = 'adm-notif-icon--rose';
              }
              $isUnread = ! $notification->read_at;
            ?>
            <li>
              <article class="adm-notif-card <?php echo e($isUnread ? 'adm-notif-card--unread' : ''); ?>">
                <?php if($isUnread): ?>
                  <span class="adm-notif-dot" title="Haijasomwa"></span>
                <?php endif; ?>
                <div class="adm-notif-card-inner">
                  <div class="adm-notif-icon <?php echo e($iconMod); ?>"><?php echo e($icon); ?></div>
                  <div class="adm-notif-card-body">
                    <div class="adm-notif-card-head">
                      <h2 class="adm-notif-card-title"><?php echo e($notification->data['title'] ?? 'Taarifa'); ?></h2>
                      <time class="adm-notif-card-time" datetime="<?php echo e($notification->created_at->toIso8601String()); ?>">
                        <?php echo e($notification->created_at->diffForHumans()); ?>

                      </time>
                    </div>
                    <p class="adm-notif-card-msg"><?php echo e($notification->data['message'] ?? 'Hakuna maelezo ya ziada.'); ?></p>
                    <div class="adm-notif-card-actions">
                      <?php if(! empty($notification->data['action_url'])): ?>
                        <a href="<?php echo e($notification->data['action_url']); ?>" class="adm-notif-btn adm-notif-btn--solid">Fungua →</a>
                      <?php endif; ?>
                      <?php if($isUnread): ?>
                        <form method="POST" action="<?php echo e(route('notifications.read', $notification->id)); ?>" class="m-0 inline">
                          <?php echo csrf_field(); ?>
                          <button type="submit" class="adm-notif-btn">Weka kama imesomwa</button>
                        </form>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </article>
            </li>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
        <div class="adm-notif-pagination">
          <?php echo e($notifications->appends(request()->query())->links()); ?>

        </div>
      <?php else: ?>
        <div class="adm-notif-empty">
          <div class="adm-notif-empty-icon"><?php echo e($filter === 'unread' ? '✨' : '🔔'); ?></div>
          <?php if($filter === 'unread'): ?>
            <h3>Hakuna zisizosomwa</h3>
            <p>Umesoma taarifa zote. Chagua &ldquo;Zote&rdquo; kuona historia.</p>
            <a href="<?php echo e(route('notifications.index')); ?>" class="adm-notif-btn adm-notif-btn--solid">Ona taarifa zote</a>
          <?php else: ?>
            <h3>Bado hakuna taarifa</h3>
            <p>Utapokea taarifa kuhusu kazi, malipo, na masasisho ya mfumo hapa.</p>
            <a href="<?php echo e($dashUrl); ?>" class="adm-notif-btn adm-notif-btn--solid">Rudi dashibodi</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>
<?php else: ?>
<div class="flex min-h-screen bg-slate-100/90">
  <?php if($useUserSidebar): ?>
    <?php echo $__env->make('components.user-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <?php endif; ?>

  <main class="<?php echo e($useUserSidebar ? 'tp-main w-full min-w-0 p-4 pt-16 sm:p-6 lg:pt-6' : 'w-full max-w-3xl mx-auto px-4 py-8 sm:px-6'); ?>">
    <div class="mx-auto max-w-2xl lg:max-w-3xl space-y-6">

      <?php if(session('success')): ?>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[13px] font-semibold text-emerald-900 shadow-sm">
          <?php echo e(session('success')); ?>

        </div>
      <?php endif; ?>

      <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 via-orange-500 to-rose-500 p-6 text-white shadow-lg sm:p-8">
        <div class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-white/15 blur-3xl"></div>
        <div class="pointer-events-none absolute bottom-0 left-1/4 h-32 w-56 rounded-full bg-rose-300/20 blur-2xl"></div>
        <div class="relative flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/80">Kituo cha taarifa</p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">Taarifa zako</h1>
            <p class="mt-2 max-w-md text-[13px] leading-relaxed text-white/90">
              Fuatilia kazi, malipo, na ujumbe kutoka TendaPoa — zote mahali pamoja.
            </p>
          </div>
          <div class="flex shrink-0 flex-wrap gap-2">
            <?php if($unreadCount > 0): ?>
              <form action="<?php echo e(route('notifications.readAll')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-white/95 px-4 py-2.5 text-[12px] font-bold text-orange-900 shadow-md transition hover:bg-white">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                  Soma zote
                </button>
              </form>
            <?php endif; ?>
            <a href="<?php echo e($dashUrl); ?>" class="inline-flex items-center justify-center rounded-xl border border-white/35 bg-white/10 px-4 py-2.5 text-[12px] font-semibold text-white backdrop-blur-sm transition hover:bg-white/20">Dashboard</a>
          </div>
        </div>
      </section>

      <div class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm ring-1 ring-slate-100/80">
          <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Jumla</p>
          <p class="mt-1 text-2xl font-bold tabular-nums text-slate-900"><?php echo e(number_format($totalCount)); ?></p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 to-white p-4 shadow-sm ring-1 ring-amber-100/80">
          <p class="text-[10px] font-bold uppercase tracking-wider text-amber-800">Zisizosomwa</p>
          <p class="mt-1 text-2xl font-bold tabular-nums text-amber-900"><?php echo e(number_format($unreadCount)); ?></p>
        </div>
        <div class="rounded-2xl border border-brand-100 bg-gradient-to-br from-brand-50 to-teal-50/30 p-4 shadow-sm ring-1 ring-brand-100/80">
          <p class="text-[10px] font-bold uppercase tracking-wider text-brand-800">Ukurasa huu</p>
          <p class="mt-1 text-2xl font-bold tabular-nums text-brand-900"><?php echo e($notifications->count()); ?></p>
          <p class="mt-0.5 text-[10px] text-brand-700/70">Kati ya <?php echo e($notifications->total()); ?> zinazolingana</p>
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <span class="mr-1 text-[11px] font-bold uppercase tracking-wide text-slate-500">Onyesha:</span>
        <a href="<?php echo e(route('notifications.index', array_filter(['filter' => null]))); ?>"
          class="inline-flex items-center rounded-full px-4 py-2 text-[12px] font-bold transition <?php echo e($filter === 'all' ? 'bg-slate-900 text-white shadow-md' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50'); ?>">
          Zote
        </a>
        <a href="<?php echo e(route('notifications.index', ['filter' => 'unread'])); ?>"
          class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-[12px] font-bold transition <?php echo e($filter === 'unread' ? 'bg-amber-600 text-white shadow-md shadow-amber-600/25' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50'); ?>">
          Zisizosomwa
          <?php if($unreadCount > 0): ?>
            <span class="rounded-full bg-white/25 px-2 py-0.5 text-[10px] tabular-nums"><?php echo e($unreadCount); ?></span>
          <?php endif; ?>
        </a>
      </div>

      <?php if($notifications->count() > 0): ?>
        <ul class="space-y-3">
          <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
              $type = (string) ($notification->data['type'] ?? 'info');
              $icon = '🔔';
              $iconWrap = 'bg-slate-100 text-slate-600 ring-slate-200/80';

              if ($type === 'admin_message' || str_contains($type, 'admin')) {
                  $icon = '📢';
                  $iconWrap = 'bg-violet-100 text-violet-700 ring-violet-200/80';
              } elseif (str_contains($type, 'job')) {
                  $icon = '💼';
                  $iconWrap = 'bg-sky-100 text-sky-700 ring-sky-200/80';
              } elseif (str_contains($type, 'money') || str_contains($type, 'payment') || isset($notification->data['earnings'])) {
                  $icon = '💰';
                  $iconWrap = 'bg-emerald-100 text-emerald-700 ring-emerald-200/80';
              } elseif (str_contains($type, 'alert') || str_contains($type, 'cancel')) {
                  $icon = '⚠️';
                  $iconWrap = 'bg-rose-100 text-rose-700 ring-rose-200/80';
              }

              $isUnread = ! $notification->read_at;
              $borderUnread = $isUnread
                  ? 'border-l-4 border-l-brand-600 bg-gradient-to-r from-brand-50/80 to-white ring-brand-100/60'
                  : 'border-l-4 border-l-transparent';
            ?>
            <li>
              <article class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm transition hover:border-slate-300 hover:shadow-md <?php echo e($borderUnread); ?>">
                <?php if($isUnread): ?>
                  <span class="absolute right-4 top-4 h-2 w-2 rounded-full bg-brand-500 shadow-[0_0_0_3px_rgba(255,255,255,1)]" title="Haijasomwa"></span>
                <?php endif; ?>
                <div class="flex gap-4 p-4 sm:p-5">
                  <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-xl ring-1 <?php echo e($iconWrap); ?> shadow-sm">
                    <?php echo e($icon); ?>

                  </div>
                  <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-start justify-between gap-2 pr-6 sm:pr-0">
                      <h2 class="text-[15px] font-bold leading-snug text-slate-900">
                        <?php echo e($notification->data['title'] ?? 'Taarifa'); ?>

                      </h2>
                      <time class="shrink-0 text-[11px] font-medium text-slate-400" datetime="<?php echo e($notification->created_at->toIso8601String()); ?>">
                        <?php echo e($notification->created_at->diffForHumans()); ?>

                      </time>
                    </div>
                    <p class="mt-2 text-[13px] leading-relaxed text-slate-600">
                      <?php echo e($notification->data['message'] ?? 'Hakuna maelezo ya ziada.'); ?>

                    </p>
                    <div class="mt-4 flex flex-wrap items-center gap-2">
                      <?php if(! empty($notification->data['action_url'])): ?>
                        <a href="<?php echo e($notification->data['action_url']); ?>"
                          class="inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-4 py-2 text-[12px] font-bold text-white shadow-sm transition hover:bg-brand-700">
                          Fungua
                          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                      <?php endif; ?>
                      <?php if($isUnread): ?>
                        <form method="POST" action="<?php echo e(route('notifications.read', $notification->id)); ?>" class="inline">
                          <?php echo csrf_field(); ?>
                          <button type="submit" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-[12px] font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                            Weka kama imesomwa
                          </button>
                        </form>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </article>
            </li>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>

        <div class="flex justify-center pt-4 text-[12px] text-slate-600 [&_.pagination]:flex [&_.pagination]:flex-wrap [&_.pagination]:justify-center [&_.pagination]:gap-1 [&_a]:rounded-lg [&_a]:border [&_a]:border-slate-200 [&_a]:px-3 [&_a]:py-1.5 [&_a]:font-medium [&_a]:text-slate-700 [&_a:hover]:bg-slate-50 [&_span]:rounded-lg [&_span]:bg-brand-50 [&_span]:px-3 [&_span]:py-1.5 [&_span]:font-semibold [&_span]:text-brand-800">
          <?php echo e($notifications->appends(request()->query())->links()); ?>

        </div>
      <?php else: ?>
        <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-white/80 px-6 py-16 text-center shadow-inner">
          <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-100 to-slate-50 text-4xl shadow-sm ring-1 ring-slate-200/80">
            <?php if($filter === 'unread'): ?>
              ✨
            <?php else: ?>
              🔔
            <?php endif; ?>
          </div>
          <?php if($filter === 'unread'): ?>
            <h3 class="mt-6 text-lg font-bold text-slate-900">Hakuna zisizosomwa</h3>
            <p class="mx-auto mt-2 max-w-sm text-[13px] leading-relaxed text-slate-600">Umesoma taarifa zote! Unaweza kuona historia kwa kuchagua &ldquo;Zote&rdquo; hapo juu.</p>
            <a href="<?php echo e(route('notifications.index')); ?>" class="mt-6 inline-flex rounded-xl bg-slate-900 px-5 py-2.5 text-[13px] font-bold text-white shadow-md hover:bg-slate-800">Ona taarifa zote</a>
          <?php else: ?>
            <h3 class="mt-6 text-lg font-bold text-slate-900">Bado hakuna taarifa</h3>
            <p class="mx-auto mt-2 max-w-sm text-[13px] leading-relaxed text-slate-600">Utapokea taarifa kuhusu kazi, malipo, na masasisho ya mfumo hapa.</p>
            <a href="<?php echo e($dashUrl); ?>" class="mt-6 inline-flex rounded-xl bg-gradient-to-r from-brand-600 to-teal-600 px-5 py-2.5 text-[13px] font-bold text-white shadow-lg shadow-brand-600/20 hover:from-brand-700 hover:to-teal-700">Rudi dashboard</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>

    </div>
  </main>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make($layout, array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/notifications/index.blade.php ENDPATH**/ ?>