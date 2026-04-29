<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['jobs', 'role' => 'muhitaji']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['jobs', 'role' => 'muhitaji']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if(isset($jobs) && $jobs->isNotEmpty()): ?>
  <div class="rounded-2xl border border-rose-200/80 bg-gradient-to-r from-rose-50/90 to-white px-5 py-4 shadow-sm">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
      <div>
        <p class="text-[13px] font-bold text-rose-950">Hatua muhimu kwenye kazi</p>
        <p class="mt-0.5 text-[12px] text-rose-900/80">
          <?php if($role === 'mfanyakazi'): ?>
            Kuna kazi zinazohitaji kukubali, kuendelea, au kuwasilisha.
          <?php else: ?>
            Lipa escrow au thibitisha kazi zilizowasilishwa.
          <?php endif; ?>
        </p>
      </div>
    </div>
    <ul class="mt-4 space-y-2">
      <?php $__currentLoopData = $jobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-rose-100/80 bg-white/90 px-3 py-2.5">
          <div class="min-w-0 flex-1">
            <a href="<?php echo e(route('jobs.show', $j)); ?>" class="truncate text-[13px] font-semibold text-slate-900 hover:text-brand-700 hover:underline"><?php echo e($j->title); ?></a>
            <p class="text-[11px] text-slate-500">
              <?php if($role === 'mfanyakazi'): ?>
                <?php if($j->status === \App\Models\Job::S_FUNDED): ?>
                  Escrow imewekwa — kubali au kataa
                <?php elseif($j->status === \App\Models\Job::S_IN_PROGRESS): ?>
                  Kazi inaendelea — wasilisha ukimaliza
                <?php else: ?>
                  <?php echo e($j->status); ?>

                <?php endif; ?>
              <?php else: ?>
                <?php if($j->status === \App\Models\Job::S_AWAITING_PAYMENT): ?>
                  Subiri malipo ya escrow
                <?php elseif($j->status === \App\Models\Job::S_SUBMITTED): ?>
                  Mfanyakazi amewasilisha — thibitisha au omba marekebisho
                <?php else: ?>
                  <?php echo e($j->status); ?>

                <?php endif; ?>
              <?php endif; ?>
            </p>
          </div>
          <a href="<?php echo e(route('jobs.show', $j)); ?>" class="shrink-0 rounded-lg bg-rose-600 px-3 py-1.5 text-[11px] font-bold text-white hover:bg-rose-700">Fungua</a>
        </li>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
  </div>
<?php endif; ?>
<?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/components/dashboard-attention-jobs.blade.php ENDPATH**/ ?>