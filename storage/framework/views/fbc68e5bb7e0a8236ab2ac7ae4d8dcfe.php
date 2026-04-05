<?php $__env->startSection('title', 'Muhitaji — Dashibodi'); ?>

<?php $__env->startSection('content'); ?>
<?php
  $posted = (int)($posted ?? 0);
  $completed = (int)($completed ?? 0);
  $totalPaid = (int)($totalPaid ?? 0);
  $rate = $posted > 0 ? (int)round(($completed / max($posted,1)) * 100) : 0;
?>

<div class="flex min-h-screen bg-slate-100/90">
  <?php echo $__env->make('components.user-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <main class="tp-main w-full min-w-0 p-4 pt-16 sm:p-6 lg:pt-6">
    <div class="mx-auto max-w-6xl space-y-6">

      
      <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-700 via-brand-600 to-teal-600 p-6 text-white shadow-lg sm:p-8">
        <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-8 left-1/3 h-32 w-64 rounded-full bg-teal-400/20 blur-2xl"></div>
        <div class="relative">
          <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/70">Dashibodi ya mteja</p>
          <h1 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">Habari, <?php echo e(auth()->user()->name); ?></h1>
          <p class="mt-2 max-w-2xl text-[13px] leading-relaxed text-white/90">
            Muhtasari wa kazi, malipo, na salio — yote katika skrini moja.
          </p>
          <div class="mt-5 flex flex-wrap gap-2">
            <a href="<?php echo e(route('jobs.create')); ?>" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-[13px] font-bold text-brand-800 shadow-md transition hover:bg-brand-50">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
              Chapisha kazi
            </a>
            <a href="<?php echo e(route('my.jobs')); ?>" class="inline-flex items-center gap-2 rounded-xl border border-white/30 bg-white/10 px-4 py-2.5 text-[13px] font-semibold text-white backdrop-blur-sm transition hover:bg-white/20">
              Orodha ya kazi
            </a>
            <?php if(($pendingAppsCount ?? 0) > 0): ?>
            <a href="<?php echo e(route('my.applications')); ?>" class="inline-flex items-center gap-2 rounded-xl border border-amber-200/60 bg-amber-400/20 px-4 py-2.5 text-[13px] font-bold text-amber-50 backdrop-blur-sm transition hover:bg-amber-400/30">
              Maombi (<?php echo e($pendingAppsCount); ?>)
            </a>
            <?php endif; ?>
            <a href="<?php echo e(route('notifications.index')); ?>" class="inline-flex items-center gap-2 rounded-xl border border-white/20 px-4 py-2.5 text-[13px] font-medium text-white/95 hover:bg-white/10">Taarifa</a>
          </div>
        </div>
      </section>

      <?php if(($pendingAppsCount ?? 0) > 0): ?>
        <div class="flex flex-col gap-3 rounded-2xl border border-amber-200/80 bg-gradient-to-r from-amber-50 to-white px-5 py-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p class="text-[13px] font-bold text-amber-950">Una maombi yanayosubiri hatua yako</p>
            <p class="mt-0.5 text-[12px] text-amber-900/80"><?php echo e($pendingAppsCount); ?> maombi — chagua mfanyakazi, counter, au orodhesha kwenye kila kazi.</p>
          </div>
          <a href="<?php echo e(route('my.applications', ['filter' => 'hatua'])); ?>" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-amber-600 px-4 py-2.5 text-[12px] font-bold text-white shadow-sm hover:bg-amber-700">Angalia maombi</a>
        </div>
      <?php endif; ?>

      <?php if (isset($component)) { $__componentOriginal54d557ae50f3a7711901415abdb7d61e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal54d557ae50f3a7711901415abdb7d61e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-attention-jobs','data' => ['jobs' => $attentionJobs ?? collect(),'role' => 'muhitaji']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-attention-jobs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['jobs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attentionJobs ?? collect()),'role' => 'muhitaji']); ?>
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
        <article class="relative overflow-hidden rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50 to-white p-5 shadow-sm ring-1 ring-indigo-100/80">
          <div class="flex items-start justify-between">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-600/25">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
          </div>
          <p class="mt-4 text-[10px] font-bold uppercase tracking-wider text-indigo-600/80">Zilizochapishwa</p>
          <p class="mt-1 text-3xl font-bold tabular-nums text-slate-900"><?php echo e(number_format($posted)); ?></p>
          <p class="mt-1 text-[11px] text-slate-500"><?php echo e(max(0, $posted - $completed)); ?> bado hazijaisha</p>
        </article>

        <article class="relative overflow-hidden rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm ring-1 ring-emerald-100/80">
          <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-lg shadow-emerald-600/25">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <p class="mt-4 text-[10px] font-bold uppercase tracking-wider text-emerald-700/90">Zilizokamilika</p>
          <p class="mt-1 text-3xl font-bold tabular-nums text-emerald-800"><?php echo e(number_format($completed)); ?></p>
          <p class="mt-1 text-[11px] text-slate-500"><?php echo e($rate); ?>% kiwango cha ukamilishaji</p>
        </article>

        <article class="relative overflow-hidden rounded-2xl border border-sky-100 bg-gradient-to-br from-sky-50 to-white p-5 shadow-sm ring-1 ring-sky-100/80">
          <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-sky-600 text-white shadow-lg shadow-sky-600/25">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <p class="mt-4 text-[10px] font-bold uppercase tracking-wider text-sky-700/90">Malipo yaliyofanikiwa</p>
          <p class="mt-1 text-xl font-bold tabular-nums leading-tight text-slate-900"><?php echo e(number_format($totalPaid)); ?> <span class="text-[11px] font-semibold text-slate-500">TZS</span></p>
          <p class="mt-1 text-[11px] text-slate-500">Kupitia escrow</p>
        </article>

        <article class="relative overflow-hidden rounded-2xl border border-teal-200/60 bg-gradient-to-br from-teal-50 via-brand-50 to-white p-5 shadow-sm ring-1 ring-teal-100/80">
          <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-brand-600 to-teal-600 text-white shadow-lg shadow-brand-600/20">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
          </div>
          <p class="mt-4 text-[10px] font-bold uppercase tracking-wider text-brand-800/90">Salio la wallet</p>
          <p class="mt-1 text-xl font-bold tabular-nums text-brand-900"><?php echo e(number_format($available ?? 0)); ?> <span class="text-[11px] font-semibold text-brand-700/70">TZS</span></p>
          <a href="<?php echo e(route('withdraw.form')); ?>" class="mt-2 inline-flex text-[11px] font-bold text-brand-800 hover:underline">Toa pesa →</a>
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
            <?php $__currentLoopData = $notifications->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <div class="rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3 transition hover:bg-white hover:shadow-sm">
                <p class="text-[13px] font-semibold text-slate-900"><?php echo e($notif->data['title'] ?? 'Taarifa'); ?></p>
                <p class="mt-0.5 text-[12px] text-slate-600"><?php echo e($notif->data['message'] ?? ''); ?></p>
                <p class="mt-1.5 text-[10px] font-medium text-slate-400"><?php echo e($notif->created_at->diffForHumans()); ?></p>
              </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
          <?php if($notifications->count() > 4): ?>
            <div class="border-t border-slate-100 bg-slate-50/50 px-4 py-3 text-center">
              <a href="<?php echo e(route('notifications.index')); ?>" class="text-[12px] font-bold text-brand-700 hover:underline">Angalia taarifa zote</a>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <div class="grid gap-6 lg:grid-cols-2">
        
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md">
          <div class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-brand-50/50 to-white px-5 py-4">
            <h2 class="text-[14px] font-bold text-slate-900">Kazi za hivi karibuni</h2>
            <a href="<?php echo e(route('my.jobs')); ?>" class="text-[11px] font-bold text-brand-700 hover:underline">Ona zote</a>
          </div>
          <?php if($allJobs->count() > 0): ?>
            <ul class="divide-y divide-slate-100">
              <?php $__currentLoopData = $allJobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                  $sc = match($job->status) {
                    'completed' => 'bg-emerald-100 text-emerald-900',
                    'in_progress','funded' => 'bg-blue-100 text-blue-900',
                    'open','posted' => 'bg-amber-100 text-amber-900',
                    'awaiting_payment' => 'bg-fuchsia-100 text-fuchsia-900',
                    default => 'bg-slate-100 text-slate-700',
                  };
                  $thumb = null;
                  if ($job->image) {
                    $fp = storage_path('app/public/' . $job->image);
                    if (file_exists($fp)) {
                      $thumb = asset('storage/' . $job->image) . '?v=' . filemtime($fp);
                    }
                  }
                ?>
                <li class="flex gap-3 px-4 py-3 transition hover:bg-slate-50/90 sm:px-5">
                  <div class="h-12 w-12 shrink-0 overflow-hidden rounded-xl bg-slate-100 ring-1 ring-slate-200/80">
                    <?php if($thumb): ?>
                      <img src="<?php echo e($thumb); ?>" alt="" class="h-full w-full object-cover">
                    <?php else: ?>
                      <div class="flex h-full w-full items-center justify-center text-xs text-slate-400">📋</div>
                    <?php endif; ?>
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="truncate text-[13px] font-semibold text-slate-900"><?php echo e($job->title ?? 'Kazi'); ?></p>
                    <p class="mt-0.5 text-[11px] text-slate-500">
                      <?php echo e($job->category?->name ?? ''); ?>

                      <?php if($job->acceptedWorker): ?> · <?php echo e($job->acceptedWorker->name); ?> <?php endif; ?>
                    </p>
                    <div class="mt-1.5 flex flex-wrap items-center gap-2">
                      <span class="rounded-md px-2 py-0.5 text-[9px] font-bold uppercase <?php echo e($sc); ?>"><?php echo e(str_replace('_',' ',$job->status)); ?></span>
                      <span class="text-[12px] font-bold text-slate-800"><?php echo e(number_format($job->price ?? 0)); ?> TZS</span>
                    </div>
                  </div>
                </li>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
          <?php else: ?>
            <div class="px-5 py-12 text-center">
              <p class="text-[13px] font-medium text-slate-600">Bado hakuna kazi</p>
              <a href="<?php echo e(route('jobs.create')); ?>" class="mt-3 inline-flex rounded-xl bg-brand-600 px-4 py-2 text-[12px] font-bold text-white shadow-sm hover:bg-brand-700">Chapisha kazi ya kwanza</a>
            </div>
          <?php endif; ?>
        </div>

        
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md">
          <div class="border-b border-slate-100 bg-gradient-to-r from-rose-50/40 to-white px-5 py-4">
            <h2 class="text-[14px] font-bold text-slate-900">Historia ya malipo</h2>
            <p class="mt-0.5 text-[11px] text-slate-500">Muhtasari wa muamala wa hivi karibuni</p>
          </div>
          <?php if($paymentHistory->count() > 0): ?>
            <ul class="divide-y divide-slate-100">
              <?php $__currentLoopData = $paymentHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="flex items-center gap-3 px-4 py-3 sm:px-5">
                  <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-sm font-bold shadow-sm <?php echo e($payment->status === 'COMPLETED' ? 'bg-emerald-100 text-emerald-700' : ($payment->status === 'FAILED' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800')); ?>">
                    <?php echo e($payment->status === 'COMPLETED' ? '✓' : ($payment->status === 'FAILED' ? '✗' : '…')); ?>

                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="truncate text-[13px] font-semibold text-slate-900"><?php echo e($payment->job->title ?? 'Malipo'); ?></p>
                    <p class="text-[10px] text-slate-400"><?php echo e($payment->created_at->diffForHumans()); ?></p>
                  </div>
                  <span class="shrink-0 text-[12px] font-bold text-rose-600">-<?php echo e(number_format($payment->amount)); ?> <span class="text-[10px] font-medium">TZS</span></span>
                </li>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
          <?php else: ?>
            <div class="px-5 py-10 text-center text-[12px] text-slate-500">Hakuna malipo bado.</div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/muhitaji/dashboard.blade.php ENDPATH**/ ?>