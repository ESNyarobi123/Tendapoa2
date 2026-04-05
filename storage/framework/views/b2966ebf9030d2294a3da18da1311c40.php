<?php $__env->startSection('title', 'Mazungumzo'); ?>

<?php $__env->startSection('content'); ?>
<?php
  $role = auth()->user()->role;
  $totalUnread = $conversations->sum('unread_count');
?>

<div class="flex min-h-screen bg-slate-100/90">
  <?php echo $__env->make('components.user-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <main class="tp-main w-full min-w-0 p-4 pt-16 sm:p-6 lg:pt-6">
    <div class="mx-auto w-full max-w-6xl space-y-6">

      
      <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-violet-600 to-brand-600 p-6 text-white shadow-lg sm:p-8">
        <div class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-white/10 blur-3xl"></div>
        <div class="pointer-events-none absolute bottom-0 left-1/3 h-32 w-64 rounded-full bg-fuchsia-400/15 blur-2xl"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/75">Mazungumzo</p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">Mazungumzo yako</h1>
            <p class="mt-2 max-w-xl text-[13px] leading-relaxed text-white/90">
              Ongea na <?php echo e($role === 'mfanyakazi' ? 'wahitaji' : 'wafanyakazi'); ?> kupitia kazi ulizounganishwa nazo.
            </p>
          </div>
          <div class="flex flex-wrap gap-3">
            <div class="rounded-2xl border border-white/25 bg-white/10 px-5 py-4 backdrop-blur-sm">
              <p class="text-[10px] font-bold uppercase tracking-wide text-white/75">Mazungumzo</p>
              <p class="mt-1 text-2xl font-bold tabular-nums"><?php echo e($conversations->count()); ?></p>
            </div>
            <div class="rounded-2xl border border-white/25 bg-white/10 px-5 py-4 backdrop-blur-sm">
              <p class="text-[10px] font-bold uppercase tracking-wide text-white/75">Haujasoma</p>
              <p class="mt-1 text-2xl font-bold tabular-nums <?php echo e($totalUnread > 0 ? 'text-amber-200' : ''); ?>"><?php echo e($totalUnread); ?></p>
            </div>
          </div>
        </div>
      </section>

      <?php if($conversations->isNotEmpty()): ?>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div class="relative flex-1 sm:max-w-md">
            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">🔍</span>
            <input type="search" id="convSearch" autocomplete="off" placeholder="Tafuta kwa jina au kichwa cha kazi…"
              class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-[13px] shadow-sm placeholder:text-slate-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
          </div>
          <div class="flex flex-wrap gap-2">
            <button type="button" data-filter="all" class="conv-filter rounded-full border border-slate-200 bg-white px-4 py-2 text-[12px] font-bold text-slate-700 shadow-sm ring-2 ring-brand-500 ring-offset-2">Zote</button>
            <button type="button" data-filter="unread" class="conv-filter rounded-full border border-slate-200 bg-white px-4 py-2 text-[12px] font-bold text-slate-700 shadow-sm hover:bg-slate-50">Haujasoma tu</button>
          </div>
        </div>
        <p id="convEmptyFilter" class="hidden text-center text-[13px] text-slate-500">Hakuna mazungumzo yanayolingana na utafutaji.</p>
      <?php endif; ?>

      <?php if($conversations->isEmpty()): ?>
        <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-white px-6 py-16 text-center shadow-inner">
          <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-100 to-violet-100 text-4xl shadow-sm ring-1 ring-indigo-100">💬</div>
          <h2 class="mt-6 text-lg font-bold text-slate-900">Bado hakuna mazungumzo</h2>
          <p class="mx-auto mt-2 max-w-md text-[13px] leading-relaxed text-slate-600">
            <?php if($role === 'mfanyakazi'): ?>
              Utaweza kuzungumza na mteja ukichaguliwa kwa kazi. Angalia feed na omba kazi kwanza.
            <?php else: ?>
              Chapisha kazi na uchague mfanyakazi ili mazungumzo ya kazi yafunguke hapa.
            <?php endif; ?>
          </p>
          <a href="<?php echo e(route('feed')); ?>" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 px-5 py-2.5 text-[13px] font-bold text-white shadow-lg shadow-brand-600/20 hover:from-brand-700 hover:to-indigo-700">
            Tazama kazi
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
          </a>
        </div>
      <?php else: ?>
        <div class="space-y-3" id="convList">
          <?php $__currentLoopData = $conversations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($conv && $conv->job && $conv->other_user): ?>
              <?php
                $isWorker = $conv->other_user->role === 'mfanyakazi';
                $workerIdParam = $role === 'muhitaji' ? '?worker_id=' . $conv->other_user->id : '';
                $searchHaystack = strtolower($conv->other_user->name . ' ' . $conv->job->title . ' ' . ($conv->job->category?->name ?? ''));
                $st = $conv->job->status ?? '';
                $stClass = match ($st) {
                  'assigned' => 'bg-amber-100 text-amber-900 ring-amber-200/80',
                  'in_progress' => 'bg-violet-100 text-violet-900 ring-violet-200/80',
                  'completed' => 'bg-emerald-100 text-emerald-900 ring-emerald-200/80',
                  'pending_payment' => 'bg-rose-100 text-rose-900 ring-rose-200/80',
                  default => 'bg-slate-100 text-slate-800 ring-slate-200/80',
                };
              ?>
              <a href="<?php echo e(route('chat.show', $conv->job)); ?><?php echo e($workerIdParam); ?>"
                data-conv-item
                data-search="<?php echo e(e($searchHaystack)); ?>"
                data-unread="<?php echo e((int) $conv->unread_count); ?>"
                class="conv-card group flex gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm ring-1 ring-slate-100/80 transition hover:border-brand-200 hover:shadow-md sm:p-5 <?php echo e($conv->unread_count > 0 ? 'border-l-4 border-l-brand-500 bg-gradient-to-r from-brand-50/40 to-white' : ''); ?>">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-xl font-bold text-white shadow-md <?php echo e($isWorker ? 'bg-gradient-to-br from-emerald-500 to-teal-600' : 'bg-gradient-to-br from-indigo-500 to-violet-600'); ?>">
                  <?php echo e(mb_substr($conv->other_user->name, 0, 1)); ?>

                </div>
                <div class="min-w-0 flex-1">
                  <div class="flex flex-wrap items-center gap-2">
                    <h2 class="truncate text-[15px] font-bold text-slate-900"><?php echo e($conv->other_user->name); ?></h2>
                    <?php if($conv->unread_count > 0): ?>
                      <span class="shrink-0 rounded-full bg-brand-600 px-2.5 py-0.5 text-[10px] font-bold text-white shadow-sm"><?php echo e($conv->unread_count); ?> mpya</span>
                    <?php endif; ?>
                    <?php if($st === 'assigned'): ?>
                      <span class="shrink-0 rounded-full bg-emerald-100 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide text-emerald-800">Mpya</span>
                    <?php endif; ?>
                  </div>
                  <p class="mt-1 truncate text-[13px] font-medium text-slate-600">📋 <?php echo e($conv->job->title); ?></p>
                  <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-slate-500">
                    <span><?php echo e($conv->job->category?->name ?? '—'); ?></span>
                    <span class="font-semibold tabular-nums text-slate-700"><?php echo e(number_format($conv->job->price)); ?> TZS</span>
                    <time class="text-slate-400"><?php echo e(\Carbon\Carbon::parse($conv->last_message_at)->diffForHumans()); ?></time>
                  </div>
                </div>
                <div class="hidden shrink-0 flex-col items-end gap-2 sm:flex">
                  <span class="rounded-lg px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide ring-1 <?php echo e($stClass); ?>">
                    <?php switch($st):
                      case ('assigned'): ?> Imekabidhiwa <?php break; ?>
                      <?php case ('in_progress'): ?> Inaendelea <?php break; ?>
                      <?php case ('completed'): ?> Imekamilika <?php break; ?>
                      <?php case ('pending_payment'): ?> Malipo <?php break; ?>
                      <?php default: ?> <?php echo e(ucfirst(str_replace('_', ' ', $st))); ?>

                    <?php endswitch; ?>
                  </span>
                  <span class="text-[10px] font-semibold text-slate-400"><?php echo e($isWorker ? 'Mfanyakazi' : 'Muhitaji'); ?></span>
                  <svg class="h-5 w-5 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </div>
              </a>
            <?php endif; ?>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      <?php endif; ?>

    </div>
  </main>
</div>

<?php if($conversations->isNotEmpty()): ?>
<script>
(function () {
  var search = document.getElementById('convSearch');
  var list = document.getElementById('convList');
  var emptyMsg = document.getElementById('convEmptyFilter');
  var filterMode = 'all';

  function applyFilters() {
    var q = (search && search.value) ? search.value.toLowerCase().trim() : '';
    var n = 0;
    list.querySelectorAll('[data-conv-item]').forEach(function (el) {
      var hay = (el.getAttribute('data-search') || '').toLowerCase();
      var unread = parseInt(el.getAttribute('data-unread') || '0', 10);
      var matchQ = !q || hay.indexOf(q) !== -1;
      var matchF = filterMode === 'all' || unread > 0;
      var show = matchQ && matchF;
      el.classList.toggle('hidden', !show);
      if (show) n++;
    });
    if (emptyMsg) emptyMsg.classList.toggle('hidden', n > 0);
  }

  if (search) search.addEventListener('input', applyFilters);

  document.querySelectorAll('.conv-filter').forEach(function (btn) {
    btn.addEventListener('click', function () {
      filterMode = btn.getAttribute('data-filter') || 'all';
      document.querySelectorAll('.conv-filter').forEach(function (b) {
        b.classList.remove('ring-2', 'ring-brand-500', 'ring-offset-2');
      });
      btn.classList.add('ring-2', 'ring-brand-500', 'ring-offset-2');
      applyFilters();
    });
  });
})();
</script>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/chat/index.blade.php ENDPATH**/ ?>