<?php $__env->startSection('title', 'Maombi yangu'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex min-h-screen bg-slate-50">
  <?php echo $__env->make('components.user-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <main class="tp-main w-full min-w-0 p-4 pt-16 sm:p-6 lg:pt-6">
    <div class="mx-auto max-w-6xl space-y-5">

      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-lg font-bold tracking-tight text-slate-900 sm:text-xl">Maombi yangu</h1>
          <p class="mt-0.5 text-[12px] text-slate-500">Ombi lako baada ya kuomba linaonekana hapa — sio kwenye orodha ya wengine. Mteja akikuchagua, hali itabadilika kuwa <strong>Umechaguliwa</strong> kisha utafuata hatua za malipo na kazi.</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <a href="<?php echo e(route('feed')); ?>" class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-3 py-2 text-[12px] font-semibold text-white shadow-sm hover:bg-brand-700">Tafuta kazi</a>
          <a href="<?php echo e(route('dashboard')); ?>" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-[12px] font-medium text-slate-700 hover:bg-slate-50">Dashibodi</a>
        </div>
      </div>

      <?php if(session('success')): ?>
        <div class="rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-[12px] font-medium text-brand-900"><?php echo e(session('success')); ?></div>
      <?php endif; ?>
      <?php if($errors->any()): ?>
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-[12px] font-medium text-red-900"><?php echo e($errors->first()); ?></div>
      <?php endif; ?>

      <div class="flex flex-wrap gap-3 text-[11px] text-slate-600">
        <span class="inline-flex items-center gap-1.5 rounded-md bg-white px-2 py-1 ring-1 ring-slate-200"><strong class="text-slate-900"><?php echo e($applications->total()); ?></strong> jumla</span>
      </div>

      <?php if($applications->count() > 0): ?>
        <div class="space-y-3">
          <?php $__currentLoopData = $applications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $app): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
              $job = $app->job;
              $stStyle = match($app->status) {
                'applied' => 'bg-amber-50 text-amber-900 ring-amber-100',
                'shortlisted' => 'bg-sky-50 text-sky-900 ring-sky-100',
                'countered' => 'bg-violet-50 text-violet-900 ring-violet-100',
                'accepted_counter' => 'bg-indigo-50 text-indigo-900 ring-indigo-100',
                'selected' => 'bg-emerald-50 text-emerald-900 ring-emerald-100',
                'rejected' => 'bg-slate-100 text-slate-600 ring-slate-200',
                'withdrawn' => 'bg-slate-100 text-slate-500 ring-slate-200',
                default => 'bg-slate-50 text-slate-700 ring-slate-200',
              };
            ?>
            <article class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:gap-4">
              <div class="min-w-0 flex-1">
                <span class="inline-flex rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide ring-1 <?php echo e($stStyle); ?>"><?php echo e($app->getStatusLabel()); ?></span>
                <p class="mt-2 truncate text-[14px] font-bold text-slate-900"><?php echo e($job?->title ?? 'Kazi'); ?></p>
                <p class="mt-0.5 text-[12px] text-slate-600">
                  Mteja: <span class="font-semibold text-slate-800"><?php echo e($job?->muhitaji?->name ?? '—'); ?></span>
                  <?php if($job?->category): ?> · <?php echo e($job->category->name); ?> <?php endif; ?>
                </p>
                <p class="mt-1 text-[12px] font-semibold tabular-nums text-slate-800">
                  Ombi lako: <?php echo e(number_format($app->proposed_amount)); ?> TZS
                  <?php if($app->counter_amount): ?>
                    <span class="font-normal text-slate-500">· Counter: <?php echo e(number_format($app->counter_amount)); ?> TZS</span>
                  <?php endif; ?>
                </p>
                <?php if($app->status === \App\Models\JobApplication::STATUS_SELECTED): ?>
                  <p class="mt-2 text-[11px] font-medium text-emerald-800">🎉 Mteja amekuchagua. Fuata hatua kwenye ukurasa wa kazi (malipo ya escrow, kisha kuanza kazi).</p>
                <?php elseif($app->status === \App\Models\JobApplication::STATUS_REJECTED): ?>
                  <p class="mt-2 text-[11px] text-slate-500">Ombi halijakubaliwa kwenye kazi hii. Unaweza kuomba kazi nyingine.</p>
                <?php endif; ?>
              </div>
              <div class="flex shrink-0 flex-col gap-2 sm:items-end">
                <?php if($job): ?>
                  <a href="<?php echo e(route('jobs.show', $job)); ?>" class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-[12px] font-semibold text-white shadow-sm hover:bg-brand-700">
                    Fungua kazi
                  </a>
                <?php endif; ?>
                <span class="text-[10px] text-slate-400"><?php echo e($app->updated_at->diffForHumans()); ?></span>
              </div>
            </article>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="pt-2"><?php echo e($applications->links()); ?></div>
      <?php else: ?>
        <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-14 text-center">
          <p class="text-[14px] font-medium text-slate-700">Bado hujawasilisha ombi lolote.</p>
          <p class="mt-1 text-[12px] text-slate-500">Tafuta kazi, fungua tangazo, na bonyeza <strong>Omba kazi</strong>.</p>
          <a href="<?php echo e(route('feed')); ?>" class="mt-5 inline-flex rounded-xl bg-brand-600 px-4 py-2.5 text-[12px] font-bold text-white shadow-sm hover:bg-brand-700">Tafuta kazi</a>
        </div>
      <?php endif; ?>

    </div>
  </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/mfanyakazi/my_applications.blade.php ENDPATH**/ ?>