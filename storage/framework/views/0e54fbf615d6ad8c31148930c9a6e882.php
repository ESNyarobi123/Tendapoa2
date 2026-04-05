<?php $__env->startSection('title', 'Kazi Zangu'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex min-h-screen bg-slate-50">
  <?php echo $__env->make('components.user-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <main class="tp-main w-full min-w-0 p-4 pt-16 sm:p-6 lg:pt-6">
    <div class="mx-auto max-w-6xl space-y-5">

      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-lg font-bold tracking-tight text-slate-900 sm:text-xl">Kazi zangu</h1>
          <p class="mt-0.5 text-[12px] text-slate-500">Orodha yako — picha, bei, na hatua kwa kila kazi.</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <a href="<?php echo e(route('jobs.create')); ?>" class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-3 py-2 text-[12px] font-semibold text-white shadow-sm hover:bg-brand-700">+ Chapisha kazi</a>
          <a href="<?php echo e(route('dashboard')); ?>" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-[12px] font-medium text-slate-700 hover:bg-slate-50">Dashibodi</a>
        </div>
      </div>

      <?php
        $statusFilter = request('status');
        $filterLink = function (?string $st) {
          return $st === null ? route('my.jobs') : route('my.jobs', ['status' => $st]);
        };
      ?>
      <div class="flex flex-wrap gap-1.5 rounded-xl border border-slate-200 bg-white p-1.5 shadow-sm">
        <a href="<?php echo e($filterLink(null)); ?>" class="rounded-lg px-3 py-1.5 text-[11px] font-semibold <?php echo e(!$statusFilter ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50'); ?>">Zote</a>
        <a href="<?php echo e($filterLink('open')); ?>" class="rounded-lg px-3 py-1.5 text-[11px] font-semibold <?php echo e($statusFilter === 'open' ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50'); ?>">Wazi</a>
        <a href="<?php echo e($filterLink('posted')); ?>" class="rounded-lg px-3 py-1.5 text-[11px] font-semibold <?php echo e($statusFilter === 'posted' ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50'); ?>">Imetangazwa</a>
        <a href="<?php echo e($filterLink('awaiting_payment')); ?>" class="rounded-lg px-3 py-1.5 text-[11px] font-semibold <?php echo e($statusFilter === 'awaiting_payment' ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50'); ?>">Lipia escrow</a>
        <a href="<?php echo e($filterLink('in_progress')); ?>" class="rounded-lg px-3 py-1.5 text-[11px] font-semibold <?php echo e($statusFilter === 'in_progress' ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50'); ?>">Inaendelea</a>
        <a href="<?php echo e($filterLink('completed')); ?>" class="rounded-lg px-3 py-1.5 text-[11px] font-semibold <?php echo e($statusFilter === 'completed' ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50'); ?>">Imekamilika</a>
      </div>

      <div class="flex flex-wrap gap-3 text-[11px] text-slate-600">
        <span class="inline-flex items-center gap-1.5 rounded-md bg-white px-2 py-1 ring-1 ring-slate-200"><strong class="text-slate-900"><?php echo e($jobs->total()); ?></strong> jumla</span>
        <span class="inline-flex items-center gap-1.5 rounded-md bg-white px-2 py-1 ring-1 ring-slate-200">Ukurasa <strong class="text-slate-900"><?php echo e($jobs->currentPage()); ?></strong> / <?php echo e($jobs->lastPage()); ?></span>
      </div>

      <?php if(session('success')): ?>
        <div class="rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-[12px] font-medium text-brand-900"><?php echo e(session('success')); ?></div>
      <?php endif; ?>

      <?php if($jobs->count() > 0): ?>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
          <?php $__currentLoopData = $jobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
              $stSlug = str_replace('_', '-', $job->status);
              $statusStyle = match($job->status) {
                'open', 'posted' => 'bg-blue-50 text-blue-800 ring-blue-100',
                'awaiting_payment', 'pending_payment' => 'bg-amber-50 text-amber-900 ring-amber-100',
                'funded', 'assigned' => 'bg-emerald-50 text-emerald-900 ring-emerald-100',
                'in_progress' => 'bg-fuchsia-50 text-fuchsia-900 ring-fuchsia-100',
                'submitted' => 'bg-violet-50 text-violet-900 ring-violet-100',
                'completed' => 'bg-emerald-50 text-emerald-800 ring-emerald-100',
                'disputed' => 'bg-red-50 text-red-800 ring-red-100',
                'cancelled', 'expired', 'refunded' => 'bg-slate-100 text-slate-600 ring-slate-200',
                default => 'bg-slate-50 text-slate-700 ring-slate-200',
              };
              $img = $job->image_url;
            ?>
            <article class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:border-brand-200 hover:shadow-md">
              <div class="relative h-36 shrink-0 bg-slate-100 sm:h-40">
                <?php if($img): ?>
                  <img src="<?php echo e($img); ?>" alt="" class="h-full w-full object-cover"
                    onerror="this.style.display='none';this.nextElementSibling?.classList.remove('hidden');">
                  <div class="hidden h-full w-full flex-col items-center justify-center bg-slate-100 text-slate-400">
                    <span class="text-2xl opacity-50">📷</span>
                    <span class="mt-1 text-[10px] font-medium">Hakuna picha</span>
                  </div>
                <?php else: ?>
                  <div class="flex h-full w-full flex-col items-center justify-center text-slate-400">
                    <span class="text-2xl opacity-50">📷</span>
                    <span class="mt-1 text-[10px] font-medium">Hakuna picha</span>
                  </div>
                <?php endif; ?>
                <span class="absolute right-2 top-2 max-w-[85%] truncate rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide ring-1 <?php echo e($statusStyle); ?>">
                  <?php switch($job->status):
                    case ('open'): ?> Wazi <?php break; ?>
                    <?php case ('awaiting_payment'): ?> Inasubiri malipo <?php break; ?>
                    <?php case ('funded'): ?> Imefadhiliwa <?php break; ?>
                    <?php case ('in_progress'): ?> Inaendelea <?php break; ?>
                    <?php case ('submitted'): ?> Imewasilishwa <?php break; ?>
                    <?php case ('completed'): ?> Imekamilika <?php break; ?>
                    <?php case ('disputed'): ?> Mgogoro <?php break; ?>
                    <?php case ('cancelled'): ?> Imefutwa <?php break; ?>
                    <?php case ('refunded'): ?> Imerudishwa <?php break; ?>
                    <?php case ('expired'): ?> Imepitwa <?php break; ?>
                    <?php case ('posted'): ?> Imetangazwa <?php break; ?>
                    <?php case ('pending_payment'): ?> Inasubiri malipo <?php break; ?>
                    <?php case ('assigned'): ?> Imepewa <?php break; ?>
                    <?php default: ?> <?php echo e(ucfirst(str_replace('_', ' ', $job->status))); ?>

                  <?php endswitch; ?>
                </span>
                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/65 to-transparent px-2 pb-2 pt-8">
                  <span class="text-[13px] font-bold text-white drop-shadow"><?php echo e(number_format($job->price)); ?> <span class="text-[10px] font-semibold opacity-90">TZS</span></span>
                </div>
              </div>

              <?php if(in_array($job->status, ['funded', 'in_progress', 'submitted', 'assigned']) && $job->completion_code): ?>
                <div class="border-b border-slate-100 bg-brand-50/50 px-3 py-2">
                  <p class="text-[10px] font-semibold uppercase tracking-wide text-brand-800">Nambari ya uthibitisho</p>
                  <button type="button" onclick="copyToClipboard('<?php echo e($job->completion_code); ?>')" class="mt-1 w-full rounded-lg bg-white py-1.5 text-center font-mono text-[14px] font-bold tracking-[0.2em] text-brand-700 ring-1 ring-brand-200"><?php echo e($job->completion_code); ?></button>
                  <p class="mt-1 text-[9px] text-slate-500">Bofya kunakili. Mpe mfanyakazi baada ya kazi kukamilika.</p>
                </div>
              <?php endif; ?>

              <?php if(in_array($job->status, ['funded', 'in_progress', 'submitted', 'awaiting_payment', 'assigned']) && ($job->acceptedWorker || $job->selectedWorker)): ?>
                <div class="flex items-center gap-2 border-b border-slate-100 px-3 py-2">
                  <?php $w = $job->acceptedWorker ?? $job->selectedWorker; ?>
                  <img src="<?php echo e($w->profile_photo_url ?: 'https://ui-avatars.com/api/?name='.urlencode($w->name).'&background=e2e8f0&color=475569&size=64'); ?>" alt="" class="h-8 w-8 shrink-0 rounded-lg object-cover ring-1 ring-slate-200">
                  <div class="min-w-0">
                    <p class="text-[9px] font-semibold uppercase tracking-wide text-slate-400">Mfanyakazi</p>
                    <p class="truncate text-[12px] font-semibold text-slate-800"><?php echo e($w->name); ?></p>
                  </div>
                </div>
              <?php endif; ?>

              <?php if(in_array($job->status, ['open', 'posted']) && ($job->applications_count ?? 0) > 0): ?>
                <div class="border-b border-slate-100 bg-slate-50 px-3 py-1.5 text-[11px] font-medium text-slate-700">✋ <?php echo e($job->applications_count); ?> maombi</div>
              <?php endif; ?>

              <div class="flex flex-1 flex-col p-3">
                <h2 class="line-clamp-2 text-[14px] font-bold leading-snug text-slate-900"><?php echo e($job->title); ?></h2>
                <div class="mt-1.5 flex flex-wrap gap-x-2 gap-y-0.5 text-[10px] text-slate-500">
                  <?php if($job->category): ?><span><?php echo e($job->category->name); ?></span><?php endif; ?>
                  <span>· <?php echo e($job->created_at->format('d M Y')); ?></span>
                  <span>· <?php echo e($job->comments_count ?? 0); ?> maoni</span>
                </div>
                <?php if($job->description): ?>
                  <p class="mt-2 line-clamp-2 flex-1 text-[11px] leading-relaxed text-slate-500"><?php echo e(\Illuminate\Support\Str::limit(strip_tags($job->description), 140)); ?></p>
                <?php endif; ?>

                <div class="mt-3 flex flex-wrap gap-1.5 border-t border-slate-100 pt-2.5">
                  <?php if($job->status === 'awaiting_payment'): ?>
                    <a href="<?php echo e(route('jobs.fund', $job)); ?>" class="inline-flex flex-1 min-w-[6rem] items-center justify-center rounded-lg bg-brand-600 px-2 py-1.5 text-[11px] font-semibold text-white hover:bg-brand-700">Lipia escrow</a>
                  <?php endif; ?>
                  <?php if($job->status === 'submitted'): ?>
                    <a href="<?php echo e(route('jobs.show', $job)); ?>" class="inline-flex flex-1 min-w-[6rem] items-center justify-center rounded-lg bg-brand-600 px-2 py-1.5 text-[11px] font-semibold text-white hover:bg-brand-700">Thibitisha</a>
                  <?php endif; ?>
                  <?php if($job->status === 'pending_payment'): ?>
                    <a href="<?php echo e(route('jobs.pay.wait', $job)); ?>" class="inline-flex flex-1 min-w-[6rem] items-center justify-center rounded-lg bg-brand-600 px-2 py-1.5 text-[11px] font-semibold text-white hover:bg-brand-700">Lipa</a>
                  <?php endif; ?>
                  <?php if(in_array($job->status, ['open', 'posted', 'pending_payment', 'awaiting_payment', 'funded']) && !in_array($job->status, ['in_progress', 'submitted', 'completed'])): ?>
                    <form action="<?php echo e(route('jobs.cancel', $job)); ?>" method="POST" class="inline-flex flex-1" onsubmit="return confirm('Futa kazi hii?');">
                      <?php echo csrf_field(); ?>
                      <button type="submit" class="w-full rounded-lg border border-red-200 bg-red-50 px-2 py-1.5 text-[11px] font-semibold text-red-700 hover:bg-red-100">Futa</button>
                    </form>
                  <?php endif; ?>
                  <?php if(in_array($job->status, ['open', 'posted'])): ?>
                    <a href="<?php echo e(route('jobs.edit', $job)); ?>" class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-2 py-1.5 text-[11px] font-semibold text-slate-700 hover:bg-slate-50">Hariri</a>
                  <?php endif; ?>
                  <a href="<?php echo e(route('jobs.show', $job)); ?>" class="inline-flex flex-1 min-w-[5rem] items-center justify-center rounded-lg bg-slate-900 px-2 py-1.5 text-[11px] font-semibold text-white hover:bg-slate-800">Angalia</a>
                </div>
              </div>
            </article>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="flex justify-center pt-2 text-[12px] text-slate-600 [&_.pagination]:flex [&_.pagination]:flex-wrap [&_.pagination]:justify-center [&_.pagination]:gap-1 [&_a]:rounded-lg [&_a]:border [&_a]:border-slate-200 [&_a]:px-2.5 [&_a]:py-1 [&_a]:font-medium [&_a]:text-slate-700 [&_a:hover]:bg-slate-50 [&_span]:rounded-lg [&_span]:bg-brand-50 [&_span]:px-2.5 [&_span]:py-1 [&_span]:font-semibold [&_span]:text-brand-800">
          <?php echo e($jobs->appends(request()->query())->links()); ?>

        </div>
      <?php else: ?>
        <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-14 text-center">
          <p class="text-3xl opacity-40">📋</p>
          <p class="mt-2 text-[15px] font-semibold text-slate-800">Hakuna kazi</p>
          <p class="mt-1 text-[12px] text-slate-500">
            <?php if(request('status')): ?>
              Hakuna kazi za hali hii. <a href="<?php echo e(route('my.jobs')); ?>" class="font-semibold text-brand-700 hover:underline">Ona zote</a>
            <?php else: ?>
              Chapisha kazi yako ya kwanza.
            <?php endif; ?>
          </p>
          <a href="<?php echo e(route('jobs.create')); ?>" class="mt-4 inline-flex rounded-lg bg-brand-600 px-4 py-2 text-[12px] font-semibold text-white hover:bg-brand-700">Chapisha kazi</a>
        </div>
      <?php endif; ?>

    </div>
  </main>
</div>

<script>
function copyToClipboard(text) {
  navigator.clipboard.writeText(text).then(function() {
    const n = document.createElement('div');
    n.className = 'fixed top-4 right-4 z-[2000] rounded-lg bg-brand-600 px-4 py-2 text-[12px] font-semibold text-white shadow-lg';
    n.textContent = 'Imenakiliwa';
    document.body.appendChild(n);
    setTimeout(function() { n.remove(); }, 2000);
  });
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/muhitaji/my_jobs.blade.php ENDPATH**/ ?>