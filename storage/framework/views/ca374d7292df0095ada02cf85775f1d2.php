<?php $__env->startSection('title', 'Mfanyakazi — Dashibodi'); ?>

<?php $__env->startSection('content'); ?>

<?php
  use Illuminate\Support\Facades\Route;
  use Illuminate\Support\Facades\Auth;

  $done      = (int)($done ?? 0);
  $earned    = (int)($earnTotal ?? 0);
  $withdrawn = (int)($withdrawn ?? 0);
  $available = (int)($available ?? 0);

  $feedUrl       = Route::has('feed') ? route('feed') : url('/feed');
  $assignedUrl   = Route::has('mfanyakazi.assigned') ? route('mfanyakazi.assigned') : url('/mfanyakazi/assigned');
  $withdrawUrl   = Route::has('withdraw.form') ? route('withdraw.form') : url('/withdraw');

  $thisMonthEarnings = \App\Models\Job::where('accepted_worker_id', Auth::id())
    ->where('status', 'completed')
    ->where('completed_at', '>=', now()->startOfMonth())
    ->sum('price');

  $avgCompletionTime = \App\Models\Job::where('accepted_worker_id', Auth::id())
    ->where('status', 'completed')
    ->whereNotNull('completed_at')
    ->whereNotNull('created_at')
    ->selectRaw('AVG(DATEDIFF(completed_at, created_at)) as avg_days')
    ->value('avg_days') ?? 0;
?>

<div class="flex min-h-screen bg-slate-100/90">
  <?php echo $__env->make('components.user-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <main class="tp-main w-full min-w-0 p-4 pt-16 sm:p-6 lg:pt-6">
    <div class="mx-auto max-w-6xl space-y-6">

      
      <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-teal-700 via-brand-600 to-emerald-600 p-6 text-white shadow-lg sm:p-8">
        <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
        <div class="pointer-events-none absolute bottom-0 left-0 h-40 w-72 rounded-full bg-emerald-400/15 blur-2xl"></div>
        <div class="relative">
          <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/70">Dashibodi ya mfanyakazi</p>
          <h1 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">Karibu, <?php echo e(Auth::user()->name ?? 'Mfanyakazi'); ?></h1>
          <p class="mt-2 max-w-2xl text-[13px] leading-relaxed text-white/90">
            Fuatilia mapato, ramani, kazi zinazoendelea, na taarifa — yote katika skrini moja.
          </p>
          <div class="mt-5 flex flex-wrap gap-2">
            <a href="<?php echo e($feedUrl); ?>" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-[13px] font-bold text-brand-800 shadow-md transition hover:bg-brand-50">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
              Tafuta kazi
            </a>
            <a href="<?php echo e($assignedUrl); ?>" class="inline-flex items-center gap-2 rounded-xl border border-white/30 bg-white/10 px-4 py-2.5 text-[13px] font-semibold text-white backdrop-blur-sm transition hover:bg-white/20">Kazi zangu</a>
            <?php if(Route::has('notifications.index')): ?>
              <a href="<?php echo e(route('notifications.index')); ?>" class="inline-flex items-center gap-2 rounded-xl border border-white/20 px-4 py-2.5 text-[13px] font-medium text-white/95 hover:bg-white/10">Taarifa</a>
            <?php endif; ?>
          </div>
        </div>
      </section>

      <?php if (isset($component)) { $__componentOriginal54d557ae50f3a7711901415abdb7d61e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal54d557ae50f3a7711901415abdb7d61e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-attention-jobs','data' => ['jobs' => $attentionJobs ?? collect(),'role' => 'mfanyakazi']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-attention-jobs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['jobs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attentionJobs ?? collect()),'role' => 'mfanyakazi']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal54d557ae50f3a7711901415abdb7d61e)): ?>
<?php $attributes = $__attributesOriginal54d557ae50f3a7711901415abdb7d61e; ?>
<?php unset($__attributesOriginal54d557ae50f3a7711901415abdb7d61e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal54d557ae50f3a7711901415abdb7d61e)): ?>
<?php $component = $__componentOriginal54d557ae50f3a7711901415abdb7d61e; ?>
<?php unset($__componentOriginal54d557ae50f3a7711901415abdb7d61e); ?>
<?php endif; ?>

      
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="relative overflow-hidden rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm ring-1 ring-emerald-100/80">
          <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-lg shadow-emerald-600/25">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <p class="mt-4 text-[10px] font-bold uppercase tracking-wider text-emerald-800/90">Zilizokamilika</p>
          <p class="mt-1 text-3xl font-bold tabular-nums text-slate-900"><?php echo e(number_format($done)); ?></p>
          <p class="mt-1 text-[11px] text-slate-500">Jumla ya kazi ulizomaliza</p>
        </article>

        <article class="relative overflow-hidden rounded-2xl border border-violet-100 bg-gradient-to-br from-violet-50 to-white p-5 shadow-sm ring-1 ring-violet-100/80">
          <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-600 text-white shadow-lg shadow-violet-600/25">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <p class="mt-4 text-[10px] font-bold uppercase tracking-wider text-violet-800/90">Mapato yote</p>
          <p class="mt-1 text-xl font-bold tabular-nums text-violet-900"><?php echo e(number_format($earned)); ?> <span class="text-[11px] font-semibold text-violet-600">TZS</span></p>
          <p class="mt-1 text-[11px] text-slate-500">Mwezi huu: <?php echo e(number_format($thisMonthEarnings)); ?> TZS</p>
        </article>

        <article class="relative overflow-hidden rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 to-white p-5 shadow-sm ring-1 ring-amber-100/80">
          <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-500 text-white shadow-lg shadow-amber-500/25">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
          </div>
          <p class="mt-4 text-[10px] font-bold uppercase tracking-wider text-amber-900/90">Inapatikana</p>
          <p class="mt-1 text-xl font-bold tabular-nums text-slate-900"><?php echo e(number_format($available)); ?> <span class="text-[11px] font-semibold text-slate-500">TZS</span></p>
          <a href="<?php echo e($withdrawUrl); ?>" class="mt-1 inline-flex text-[11px] font-bold text-amber-800 hover:underline">Toa pesa →</a>
        </article>

        <article class="relative overflow-hidden rounded-2xl border border-teal-200/60 bg-gradient-to-br from-teal-50 via-brand-50 to-white p-5 shadow-sm ring-1 ring-teal-100/80">
          <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-brand-600 to-teal-600 text-white shadow-lg shadow-brand-600/20">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <p class="mt-4 text-[10px] font-bold uppercase tracking-wider text-brand-900/90">Muda wastani</p>
          <p class="mt-1 text-3xl font-bold tabular-nums text-brand-800"><?php echo e(number_format($avgCompletionTime, 1)); ?> <span class="text-lg font-semibold text-brand-600">siku</span></p>
          <p class="mt-1 text-[11px] text-slate-500">Kukamilisha kazi</p>
        </article>
      </div>

      <?php if(isset($notifications) && $notifications->count() > 0): ?>
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md">
          <div class="flex items-center justify-between bg-gradient-to-r from-slate-50 to-white px-5 py-4 border-b border-slate-100">
            <div class="flex items-center gap-2">
              <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-700">🔔</span>
              <h2 class="text-[14px] font-bold text-slate-900">Taarifa mpya</h2>
              <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-900"><?php echo e($notifications->count()); ?></span>
            </div>
            <form method="POST" action="<?php echo e(route('notifications.readAll')); ?>"><?php echo csrf_field(); ?>
              <button type="submit" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-semibold text-slate-600 shadow-sm hover:bg-slate-50">Soma zote</button>
            </form>
          </div>
          <div class="space-y-2 p-4">
            <?php $__currentLoopData = $notifications->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <div class="flex gap-3 rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3 transition hover:bg-white hover:shadow-sm">
                <span class="text-xl leading-none">
                  <?php if(($notif->data['type'] ?? '') === 'admin_message'): ?> 📢
                  <?php elseif(str_contains($notif->data['type'] ?? '', 'job')): ?> 💼
                  <?php elseif(str_contains($notif->data['type'] ?? '', 'payment')): ?> 💰
                  <?php else: ?> 🔔 <?php endif; ?>
                </span>
                <div class="min-w-0 flex-1">
                  <p class="text-[13px] font-semibold text-slate-900"><?php echo e($notif->data['title'] ?? 'Taarifa'); ?></p>
                  <p class="text-[12px] text-slate-600"><?php echo e($notif->data['message'] ?? ''); ?></p>
                  <p class="mt-1 text-[11px] text-slate-400"><?php echo e($notif->created_at->diffForHumans()); ?></p>
                </div>
              </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
          <?php if($notifications->count() > 5): ?>
            <div class="border-t border-slate-100 bg-slate-50/50 px-4 py-3 text-center">
              <a href="<?php echo e(route('notifications.index')); ?>" class="text-[12px] font-bold text-brand-700 hover:underline">Angalia taarifa zote</a>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <div class="grid gap-6 lg:grid-cols-2">
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md">
          <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 bg-gradient-to-r from-sky-50/80 to-white px-5 py-4">
            <div class="flex items-center gap-2">
              <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-100 text-lg">🗺️</span>
              <h2 class="text-[14px] font-bold text-slate-900">Eneo la kazi</h2>
            </div>
            <button type="button" onclick="getCurrentLocationAndShowJobs()" class="rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-[12px] font-bold text-sky-900 shadow-sm hover:bg-sky-100">Ona ramani</button>
          </div>
          <div class="p-5 pt-4">
          <div class="relative h-64 overflow-hidden rounded-xl border border-slate-100 bg-slate-100" id="mapContainer">
            <div id="mapPlaceholder" class="absolute inset-0 flex flex-col items-center justify-center gap-2 text-center text-[13px] text-slate-500">
              <span class="text-3xl">🗺️</span>
              <span>Bonyeza “Ona ramani” ili kuona eneo lako na la kazi</span>
            </div>
            <div id="map" class="absolute inset-0 hidden h-full w-full z-[1]"></div>
          </div>
          <div id="jobLocationInfo" class="mt-3 hidden rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 text-[13px]">
            <p class="font-semibold text-slate-800">Eneo la kazi</p>
            <p id="jobLocationText" class="text-slate-600"></p>
            <p id="distanceInfo" class="mt-1 font-semibold text-brand-700"></p>
            <button type="button" onclick="getDirections()" class="mt-2 rounded-lg bg-brand-600 px-3 py-1.5 text-[12px] font-semibold text-white hover:bg-brand-700">Pata mwelekeo</button>
          </div>
          </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md">
          <div class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-brand-50/60 to-white px-5 py-4">
            <div class="flex items-center gap-2">
              <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-100 text-lg">💼</span>
              <h2 class="text-[14px] font-bold text-slate-900">Kazi zinazoendelea</h2>
            </div>
            <a href="<?php echo e($assignedUrl); ?>" class="text-[12px] font-bold text-brand-700 hover:underline">Ona zote</a>
          </div>
          <div class="p-5">
          <?php if($currentJobs->count() > 0): ?>
            <div class="space-y-4">
              <?php $__currentLoopData = $currentJobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                  $amt = $job->amount ?? $job->price ?? 0;
                  $st = str_replace('_', '-', $job->status ?? 'assigned');
                ?>
                <div class="job-card rounded-xl border border-slate-100 bg-slate-50/80 p-4"
                     data-job-id="<?php echo e($job->id); ?>"
                     data-lat="<?php echo e($job->lat ?? 0); ?>"
                     data-lng="<?php echo e($job->lng ?? 0); ?>"
                     data-address="<?php echo e($job->address_text ?? $job->location ?? 'Eneo haijasajiliwa'); ?>">
                  <div class="flex flex-wrap items-start justify-between gap-2">
                    <div class="min-w-0">
                      <p class="font-semibold text-slate-900"><?php echo e($job->title ?? 'Kazi'); ?></p>
                      <p class="mt-1 text-[11px] text-slate-500">📍 <?php echo e($job->location ?? '—'); ?> · <?php echo e($job->created_at?->diffForHumans() ?? ''); ?></p>
                    </div>
                    <span class="shrink-0 rounded-full bg-white px-2.5 py-0.5 text-[10px] font-bold uppercase text-slate-600 ring-1 ring-slate-200"><?php echo e(ucfirst(str_replace('_', ' ', $job->status ?? ''))); ?></span>
                  </div>
                  <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-slate-200/80 pt-3">
                    <span class="text-[14px] font-bold text-brand-700">TZS <?php echo e(number_format($amt)); ?></span>
                    <div>
                      <?php if($job->status === 'assigned'): ?>
                        <form method="POST" action="<?php echo e(route('mfanyakazi.jobs.accept', $job->id)); ?>" class="inline">
                          <?php echo csrf_field(); ?>
                          <button type="submit" class="rounded-lg bg-brand-600 px-3 py-1.5 text-[12px] font-semibold text-white hover:bg-brand-700">Kubali kazi</button>
                        </form>
                      <?php elseif($job->status === 'in_progress'): ?>
                        <button type="button" onclick="showCodeInputModal(<?php echo e($job->id); ?>, <?php echo json_encode($job->title, 15, 512) ?>)" class="rounded-lg bg-slate-900 px-3 py-1.5 text-[12px] font-semibold text-white hover:bg-slate-800">Maliza kazi</button>
                      <?php elseif($job->status === 'ready_for_confirmation'): ?>
                        <span class="text-[12px] font-medium text-amber-700">Inasubiri uthibitisho</span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
          <?php else: ?>
            <div class="py-10 text-center text-[13px] text-slate-500">
              <p class="text-2xl">📭</p>
              <p class="mt-2 font-medium text-slate-700">Hakuna kazi hivi sasa</p>
              <a href="<?php echo e($feedUrl); ?>" class="mt-3 inline-flex rounded-lg bg-brand-600 px-4 py-2 text-[13px] font-semibold text-white hover:bg-brand-700">Tafuta kazi</a>
            </div>
          <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <a href="<?php echo e($feedUrl); ?>" class="group flex items-center justify-center gap-2 rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50 to-white px-4 py-4 text-center text-[13px] font-bold text-indigo-900 shadow-sm ring-1 ring-indigo-100/80 transition hover:shadow-md">
          <span class="text-lg">🔍</span> Tafuta kazi mpya
        </a>
        <a href="<?php echo e(route('jobs.create-mfanyakazi')); ?>" class="group flex items-center justify-center gap-2 rounded-2xl border border-brand-200 bg-gradient-to-br from-brand-50 to-teal-50/50 px-4 py-4 text-center text-[13px] font-bold text-brand-900 shadow-sm ring-1 ring-brand-100 transition hover:shadow-md">
          <span class="text-lg">➕</span> Chapisha huduma
        </a>
        <a href="<?php echo e($withdrawUrl); ?>" class="flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-4 text-center text-[13px] font-bold text-slate-800 shadow-sm transition hover:bg-slate-50 sm:col-span-2 lg:col-span-1 <?php if($available <= 0): ?> pointer-events-none opacity-50 <?php endif; ?>">
          <span class="text-lg">💸</span> Toa pesa
        </a>
      </div>

      <div class="space-y-6">
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md">
          <div class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-emerald-50/80 to-white px-5 py-4">
            <h2 class="text-[14px] font-bold text-slate-900">Historia ya mapato</h2>
            <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-bold text-emerald-900"><?php echo e($earningsHistory->count()); ?></span>
          </div>
          <?php if($earningsHistory->count() > 0): ?>
            <ul class="divide-y divide-slate-100">
              <?php $__currentLoopData = $earningsHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $earning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="flex items-center justify-between gap-3 px-5 py-3 text-[13px]">
                  <div>
                    <p class="font-medium text-slate-900">Malipo ya kazi</p>
                    <p class="text-[11px] text-slate-500"><?php echo e($earning->description ?? ''); ?> · <?php echo e($earning->created_at->diffForHumans()); ?></p>
                  </div>
                  <span class="shrink-0 font-bold text-emerald-700">+<?php echo e(number_format($earning->amount)); ?> TZS</span>
                </li>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
          <?php else: ?>
            <p class="px-5 py-8 text-center text-[13px] text-slate-500">Bado hakuna mapato.</p>
          <?php endif; ?>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md">
          <div class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-rose-50/60 to-white px-5 py-4">
            <h2 class="text-[14px] font-bold text-slate-900">Maombi ya kutoa pesa</h2>
            <span class="rounded-full bg-rose-100 px-2.5 py-0.5 text-[11px] font-bold text-rose-900"><?php echo e($withdrawalsHistory->count()); ?></span>
          </div>
          <?php if($withdrawalsHistory->count() > 0): ?>
            <ul class="divide-y divide-slate-100">
              <?php $__currentLoopData = $withdrawalsHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $withdrawal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="flex items-center justify-between gap-3 px-5 py-3 text-[13px]">
                  <div>
                    <p class="font-medium text-slate-900"><?php echo e($withdrawal->network_type ? ucfirst($withdrawal->network_type) : 'Malipo'); ?></p>
                    <p class="text-[11px] text-slate-500"><?php echo e($withdrawal->account); ?> · <?php echo e($withdrawal->created_at->diffForHumans()); ?></p>
                  </div>
                  <span class="shrink-0 font-bold text-red-600">-<?php echo e(number_format($withdrawal->amount)); ?> TZS</span>
                </li>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
          <?php else: ?>
            <p class="px-5 py-8 text-center text-[13px] text-slate-500">Hujatoa ombi bado.</p>
          <?php endif; ?>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md">
          <div class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-violet-50/70 to-white px-5 py-4">
            <h2 class="text-[14px] font-bold text-slate-900">Kazi zilizokamilika</h2>
            <span class="rounded-full bg-violet-100 px-2.5 py-0.5 text-[11px] font-bold text-violet-900"><?php echo e($completedJobs->count()); ?></span>
          </div>
          <?php if($completedJobs->count() > 0): ?>
            <ul class="divide-y divide-slate-100">
              <?php $__currentLoopData = $completedJobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="flex flex-col gap-1 px-5 py-3 text-[13px] sm:flex-row sm:items-center sm:justify-between">
                  <div>
                    <p class="font-medium text-slate-900"><?php echo e($job->title ?? 'Kazi'); ?></p>
                    <p class="text-[11px] text-slate-500"><?php echo e($job->muhitaji->name ?? ''); ?> · <?php echo e($job->category?->name ?? ''); ?> · <?php echo e($job->completed_at->diffForHumans()); ?></p>
                  </div>
                  <span class="font-bold text-emerald-700">+<?php echo e(number_format($job->amount ?? $job->price ?? 0)); ?> TZS</span>
                </li>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
          <?php else: ?>
            <p class="px-5 py-8 text-center text-[13px] text-slate-500">Bado hakuna kazi zilizokamilika.</p>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </main>
</div>

<div id="codeInputModal" class="fixed inset-0 z-[1050] hidden items-center justify-center bg-black/40 p-4" aria-hidden="true">
  <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl" role="document">
    <h3 class="text-lg font-bold text-slate-900">Kazi imekamilika</h3>
    <p class="mt-1 text-[13px] text-slate-500">Omba nambari ya uthibitisho kutoka kwa mteja, kisha ingiza hapa.</p>
    <form id="codeInputForm" class="mt-4 space-y-4">
      <div>
        <label for="muhitajiCode" class="mb-1 block text-[13px] font-medium text-slate-700">Nambari (tarakimu 6)</label>
        <input type="text" id="muhitajiCode" maxlength="6" pattern="[0-9]{6}" required
          class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-center font-mono text-lg tracking-widest focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
          placeholder="••••••" autocomplete="one-time-code">
      </div>
      <div class="flex gap-2">
        <button type="button" onclick="closeCodeInputModal()" class="flex-1 rounded-lg border border-slate-200 py-2.5 text-[13px] font-semibold text-slate-700 hover:bg-slate-50">Funga</button>
        <button type="submit" class="flex-1 rounded-lg bg-brand-600 py-2.5 text-[13px] font-semibold text-white hover:bg-brand-700">Thibitisha</button>
      </div>
    </form>
  </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let map;
let userLocation = null;
let currentJob = null;
let userMarker = null;
let jobMarker = null;

function showJobLocations() {
  const jobCards = document.querySelectorAll('.job-card');
  if (jobCards.length === 0) {
    showNotification('Huna kazi zinazoendelea kwa sasa.', 'info');
    return;
  }
  const firstJobCard = jobCards[0];
  const jobLat = parseFloat(firstJobCard.getAttribute('data-lat'));
  const jobLng = parseFloat(firstJobCard.getAttribute('data-lng'));
  const jobAddress = firstJobCard.getAttribute('data-address');
  if (isNaN(jobLat) || isNaN(jobLng) || jobLat === 0 || jobLng === 0) {
    showNotification('Eneo la kazi halijasajiliwa. Wasiliana na mteja.', 'error');
    return;
  }
  showJobLocationOnMap({ lat: jobLat, lng: jobLng, address: jobAddress || 'Eneo la kazi' });
  updateJobLocationInfo({ lat: jobLat, lng: jobLng, address: jobAddress || 'Eneo la kazi' });
}

function getCurrentLocationAndShowJobs() {
  const button = document.querySelector('button[onclick="getCurrentLocationAndShowJobs()"]');
  const originalText = button ? button.textContent : '';
  if (button) { button.textContent = 'Inapata eneo…'; button.disabled = true; }
  if (!navigator.geolocation) {
    showNotification('Kivinjari hakiungi mkono GPS.', 'error');
    showJobLocations();
    if (button) { button.textContent = originalText; button.disabled = false; }
    return;
  }
  navigator.geolocation.getCurrentPosition(
    function(position) {
      userLocation = { lat: position.coords.latitude, lng: position.coords.longitude };
      showJobLocations();
      if (button) { button.textContent = originalText; button.disabled = false; }
    },
    function() {
      showNotification('Ruhusu eneo au jaribu tena.', 'error');
      showJobLocations();
      if (button) { button.textContent = originalText; button.disabled = false; }
    }
  );
}

function showLocationOnMap() {
  const mapPlaceholder = document.getElementById('mapPlaceholder');
  const mapDiv = document.getElementById('map');
  if (!mapPlaceholder || !mapDiv || !userLocation) return;
  mapPlaceholder.classList.add('hidden');
  mapDiv.classList.remove('hidden');
  if (!map) {
    map = L.map('map').setView([userLocation.lat, userLocation.lng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OSM' }).addTo(map);
  }
  if (userMarker) map.removeLayer(userMarker);
  userMarker = L.marker([userLocation.lat, userLocation.lng]).addTo(map)
    .bindPopup('<b>Eneo lako</b>').openPopup();
  map.setView([userLocation.lat, userLocation.lng], 13);
}

function showJobLocationOnMap(jobLocation) {
  const mapPlaceholder = document.getElementById('mapPlaceholder');
  const mapDiv = document.getElementById('map');
  if (!mapPlaceholder || !mapDiv) return;
  mapPlaceholder.classList.add('hidden');
  mapDiv.classList.remove('hidden');
  if (!map) {
    map = L.map('map').setView([jobLocation.lat, jobLocation.lng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OSM' }).addTo(map);
    setTimeout(function() { if (map) map.invalidateSize(); }, 200);
  }
  if (jobMarker) map.removeLayer(jobMarker);
  jobMarker = L.marker([jobLocation.lat, jobLocation.lng]).addTo(map)
    .bindPopup('<b>Eneo la kazi</b><br>' + jobLocation.address).openPopup();
  map.setView([jobLocation.lat, jobLocation.lng], 13);
  if (userLocation) {
    if (userMarker) map.removeLayer(userMarker);
    userMarker = L.marker([userLocation.lat, userLocation.lng]).addTo(map).bindPopup('Eneo lako');
    const distance = calculateDistance(userLocation.lat, userLocation.lng, jobLocation.lat, jobLocation.lng);
    updateDistanceInfo(distance);
    const routeLine = L.polyline([[userLocation.lat, userLocation.lng], [jobLocation.lat, jobLocation.lng]], {
      color: '#059669', weight: 3, opacity: 0.75, dashArray: '8,8'
    }).addTo(map);
    const group = new L.featureGroup([userMarker, jobMarker, routeLine]);
    map.fitBounds(group.getBounds().pad(0.15));
  }
}

function updateJobLocationInfo(jobLocation) {
  const jobLocationInfo = document.getElementById('jobLocationInfo');
  const jobLocationText = document.getElementById('jobLocationText');
  if (jobLocationInfo && jobLocationText) {
    jobLocationInfo.classList.remove('hidden');
    jobLocationText.textContent = jobLocation.address;
  }
}

function updateDistanceInfo(distance) {
  const el = document.getElementById('distanceInfo');
  if (el) el.textContent = 'Umbali: ' + distance.toFixed(2) + ' km';
}

function calculateDistance(lat1, lon1, lat2, lon2) {
  const R = 6371;
  const dLat = (lat2 - lat1) * Math.PI / 180;
  const dLon = (lon2 - lon1) * Math.PI / 180;
  const a = Math.sin(dLat/2) ** 2 + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon/2) ** 2;
  return R * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));
}

function getDirections() {
  if (!userLocation || !jobMarker) {
    showNotification('Ramani lazima ionyeshe eneo lako na la kazi.', 'error');
    return;
  }
  const ll = jobMarker.getLatLng();
  window.open('https://www.openstreetmap.org/directions?engine=fossgis_osrm_car&route=' + userLocation.lat + ',' + userLocation.lng + ';' + ll.lat + ',' + ll.lng, '_blank');
}

function showNotification(message, type = 'info') {
  const notification = document.createElement('div');
  const bg = type === 'success' ? '#059669' : type === 'error' ? '#dc2626' : '#2563eb';
  notification.style.cssText = 'position:fixed;top:20px;right:20px;background:'+bg+';color:#fff;padding:14px 18px;border-radius:12px;z-index:2000;font-weight:600;font-size:13px;max-width:280px;box-shadow:0 10px 25px rgba(0,0,0,.12);';
  notification.textContent = message;
  document.body.appendChild(notification);
  setTimeout(() => { notification.remove(); }, 4500);
}

function showCodeInputModal(jobId, jobTitle) {
  currentJob = { id: jobId, title: jobTitle };
  const modal = document.getElementById('codeInputModal');
  const input = document.getElementById('muhitajiCode');
  if (input) input.value = '';
  if (modal) {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.setAttribute('aria-hidden', 'false');
  }
}

function closeCodeInputModal() {
  currentJob = null;
  const modal = document.getElementById('codeInputModal');
  if (modal) {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.setAttribute('aria-hidden', 'true');
  }
}

document.getElementById('codeInputForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const code = document.getElementById('muhitajiCode').value;
  if (!code || code.length !== 6) {
    showNotification('Ingiza nambari ya tarakimu 6.', 'error');
    return;
  }
  if (!currentJob) {
    showNotification('Kazi haijapatikana.', 'error');
    return;
  }
  fetch('<?php echo e(url('/mfanyakazi/jobs')); ?>/' + currentJob.id + '/complete', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Accept': 'application/json'
    },
    body: JSON.stringify({ code: code })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showNotification('Kazi imethibitishwa.', 'success');
      closeCodeInputModal();
      setTimeout(() => window.location.reload(), 1500);
    } else {
      showNotification(data.message || 'Nambari si sahihi.', 'error');
    }
  })
  .catch(() => showNotification('Hitilafu ya mtandao.', 'error'));
});

document.addEventListener('keydown', function(e) {
  if (!e.ctrlKey && !e.metaKey) return;
  if (e.key === 'j') { e.preventDefault(); window.location.href = <?php echo json_encode($feedUrl, 15, 512) ?>; }
  if (e.key === 'a') { e.preventDefault(); window.location.href = <?php echo json_encode($assignedUrl, 15, 512) ?>; }
  if (e.key === 'w') { e.preventDefault(); window.location.href = <?php echo json_encode($withdrawUrl, 15, 512) ?>; }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/mfanyakazi/dashboard.blade.php ENDPATH**/ ?>