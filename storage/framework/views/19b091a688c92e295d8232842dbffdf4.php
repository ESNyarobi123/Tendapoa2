<?php $__env->startSection('title', 'Mfanyakazi — Kazi ulizopewa'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex min-h-screen bg-slate-50">
  <?php echo $__env->make('components.user-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <main class="tp-main w-full min-w-0 p-4 pt-16 sm:p-6 lg:pt-6">
    <div class="mx-auto max-w-4xl space-y-4">

      <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <h1 class="text-lg font-bold tracking-tight text-slate-900 sm:text-xl">Kazi ulizopewa</h1>
          <p class="mt-0.5 max-w-xl text-[12px] leading-relaxed text-slate-500">
            Simamia ofa, kazi zinazoendelea, na kamilisha kwa code ya mteja. Kila kazi ina kiungo cha maelezo kamili na mazungumzo.
          </p>
        </div>
        <div class="flex flex-wrap gap-2">
          <a href="<?php echo e(route('feed')); ?>" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-[12px] font-medium text-slate-700 shadow-sm hover:bg-slate-50">Tafuta kazi</a>
          <a href="<?php echo e(route('mfanyakazi.applications')); ?>" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-[12px] font-medium text-slate-700 shadow-sm hover:bg-slate-50">Maombi yangu</a>
          <a href="<?php echo e(route('dashboard')); ?>" class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-3 py-2 text-[12px] font-semibold text-white shadow-sm hover:bg-brand-700">Dashibodi</a>
        </div>
      </div>

      <?php if(session('status')): ?>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[12px] font-medium text-emerald-900"><?php echo e(session('status')); ?></div>
      <?php endif; ?>

      <?php
        $jobCount = isset($jobs) && (method_exists($jobs, 'count') ? $jobs->count() : (is_countable($jobs ?? []) ? count($jobs) : 0));
      ?>

      <?php if($jobCount > 0): ?>
        <p class="text-[11px] text-slate-600">
          <span class="inline-flex items-center gap-1 rounded-md bg-white px-2 py-1 font-semibold text-slate-800 ring-1 ring-slate-200"><?php echo e($jobs->total()); ?></span>
          jumla ya kazi kwenye ukurasa huu
        </p>

        <div class="space-y-3">
          <?php $__currentLoopData = $jobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
              $cat = $job->category->name ?? ($job->category_name ?? '—');
              $title = $job->title ?? 'Kazi';
              $status = strtolower((string) ($job->status ?? 'pending'));
              $mfResp = strtolower((string) ($job->mfanyakazi_response ?? $job->assignee_response ?? $job->worker_response ?? ''));
              $amount = (int) ($job->payout ?? $job->price ?? $job->budget ?? 0);
              $statusRing = match (true) {
                $status === 'funded' => 'bg-emerald-50 text-emerald-900 ring-emerald-200',
                $status === 'in_progress' => 'bg-rose-50 text-rose-900 ring-rose-200',
                in_array($status, ['submitted', 'ready_for_confirmation'], true) => 'bg-violet-50 text-violet-900 ring-violet-200',
                $status === 'completed' => 'bg-emerald-50 text-emerald-900 ring-emerald-200',
                $status === 'disputed' => 'bg-red-50 text-red-900 ring-red-200',
                $status === 'assigned' => 'bg-sky-50 text-sky-900 ring-sky-200',
                default => 'bg-slate-50 text-slate-700 ring-slate-200',
              };
            ?>

            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
              <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                <div class="min-w-0 flex-1 space-y-2">
                  <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex max-w-full truncate rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-700 ring-1 ring-slate-200/80"><?php echo e($cat); ?></span>
                    <span class="inline-flex rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide ring-1 <?php echo e($statusRing); ?>">
                      <?php switch($status):
                        case ('funded'): ?> Imefadhiliwa <?php break; ?>
                        <?php case ('in_progress'): ?> Inaendelea <?php break; ?>
                        <?php case ('submitted'): ?> Imewasilishwa <?php break; ?>
                        <?php case ('completed'): ?> Imekamilika <?php break; ?>
                        <?php case ('disputed'): ?> Mgogoro <?php break; ?>
                        <?php case ('assigned'): ?> Imepewa <?php break; ?>
                        <?php case ('ready_for_confirmation'): ?> Subiri uthibitisho <?php break; ?>
                        <?php default: ?> <?php echo e(strtoupper($status)); ?>

                      <?php endswitch; ?>
                    </span>
                  </div>
                  <h2 class="text-[14px] font-bold leading-snug text-slate-900"><?php echo e($title); ?></h2>
                  <div class="flex flex-wrap gap-x-3 gap-y-1 text-[11px] text-slate-500">
                    <?php if(! empty($job->location)): ?>
                      <span class="inline-flex items-center gap-0.5"><span class="text-slate-400">Mahali</span> <?php echo e($job->location); ?></span>
                    <?php endif; ?>
                    <?php if(! empty($job->created_at)): ?>
                      <span><?php echo e(\Illuminate\Support\Carbon::parse($job->created_at)->diffForHumans()); ?></span>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="shrink-0 text-left sm:text-right">
                  <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Kiasi</p>
                  <p class="text-[14px] font-extrabold tabular-nums text-emerald-700"><?php echo e(number_format($amount)); ?> <span class="text-[11px] font-bold text-emerald-600/90">TZS</span></p>
                </div>
              </div>

              <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4">
                <a href="<?php echo e(route('jobs.show', $job)); ?>" class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-3 py-2 text-[12px] font-semibold text-white shadow-sm hover:bg-brand-700">Fungua kazi</a>

                <?php if($status === 'funded'): ?>
                  <div class="w-full rounded-xl border border-emerald-200 bg-emerald-50/90 px-3 py-2.5 text-[11px] font-medium leading-snug text-emerald-900">
                    Mteja amelipa escrow. Kubali kazi ili uanze.
                  </div>
                  <form method="POST" action="<?php echo e(route('mfanyakazi.jobs.accept', $job)); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-3 py-2 text-[12px] font-semibold text-white shadow-sm hover:bg-emerald-700">Kubali kazi</button>
                  </form>
                  <form method="POST" action="<?php echo e(route('mfanyakazi.jobs.decline', $job)); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-white px-3 py-2 text-[12px] font-semibold text-red-700 shadow-sm hover:bg-red-50">Kataa</button>
                  </form>
                <?php elseif(($status === 'assigned' && ($mfResp === '' || $mfResp === 'pending')) || $status === 'offered'): ?>
                  <form method="POST" action="<?php echo e(route('mfanyakazi.jobs.accept', $job)); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-3 py-2 text-[12px] font-semibold text-white shadow-sm hover:bg-emerald-700">Kubali kazi</button>
                  </form>
                  <form method="POST" action="<?php echo e(route('mfanyakazi.jobs.decline', $job)); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-white px-3 py-2 text-[12px] font-semibold text-red-700 shadow-sm hover:bg-red-50">Kataa</button>
                  </form>
                <?php elseif($status === 'in_progress' || ($status === 'assigned' && ! in_array($mfResp, ['', 'pending'], true))): ?>
                  <div class="w-full rounded-xl border border-amber-200 bg-amber-50/90 px-3 py-2.5 text-[11px] leading-relaxed text-amber-950">
                    <span class="font-semibold text-amber-900">Hatua:</span> maliza kazi → omba code kwa mteja → ingiza code hapa chini ili kupokea malipo.
                  </div>
                  <button type="button" class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-3 py-2 text-[12px] font-semibold text-white shadow-sm hover:bg-brand-700" onclick="showCodeInputModal(<?php echo e($job->id); ?>)">
                    Maliza kazi (code)
                  </button>
                <?php elseif($status === 'submitted' || $status === 'ready_for_confirmation'): ?>
                  <span class="inline-flex items-center rounded-lg bg-violet-50 px-3 py-2 text-[11px] font-semibold text-violet-900 ring-1 ring-violet-200">Inasubiri uthibitisho wa mteja</span>
                <?php elseif($status === 'disputed'): ?>
                  <div class="w-full rounded-xl border border-red-200 bg-red-50 px-3 py-2.5 text-[11px] font-medium text-red-900">
                    Mgogoro unaendelea. Timu ya usimamizi inaweza kuingilia kati.
                  </div>
                <?php elseif($status === 'completed'): ?>
                  <span class="inline-flex items-center rounded-lg bg-emerald-50 px-3 py-2 text-[11px] font-semibold text-emerald-900 ring-1 ring-emerald-200">Imekamilika — malipo yamefanyika</span>
                <?php else: ?>
                  <span class="inline-flex items-center rounded-lg bg-slate-100 px-3 py-2 text-[11px] font-medium text-slate-700 ring-1 ring-slate-200">Hali: <?php echo e(strtoupper($status)); ?></span>
                <?php endif; ?>
              </div>
            </article>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="flex justify-center pt-2"><?php echo e(method_exists($jobs, 'links') ? $jobs->links() : ''); ?></div>
      <?php else: ?>
        <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-12 text-center">
          <p class="text-[14px] font-semibold text-slate-800">Hakuna kazi ulizopewa</p>
          <p class="mt-1 text-[12px] text-slate-500">Tafuta kazi au angalia maombi yako — mteja akikuchagua, kazi itaonekana hapa.</p>
          <div class="mt-5 flex flex-wrap justify-center gap-2">
            <a href="<?php echo e(route('feed')); ?>" class="inline-flex rounded-xl bg-brand-600 px-4 py-2.5 text-[12px] font-bold text-white shadow-sm hover:bg-brand-700">Tafuta kazi</a>
            <a href="<?php echo e(route('mfanyakazi.applications')); ?>" class="inline-flex rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-[12px] font-semibold text-slate-700 hover:bg-slate-50">Maombi yangu</a>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </main>
</div>


<div id="codeInputModal" class="fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[2px]" role="dialog" aria-modal="true" aria-labelledby="codeModalTitle">
  <div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-2xl border border-slate-200 bg-white p-5 shadow-xl sm:p-6" onclick="event.stopPropagation()">
    <div class="text-center">
      <p id="codeModalTitle" class="text-[15px] font-bold text-slate-900">Maliza kazi</p>
      <p class="mt-1 text-[12px] leading-relaxed text-slate-500">Ingiza code ya tarakimu 6 uliyopewa na mteja baada ya kumaliza kazi.</p>
    </div>

    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50/90 px-3 py-3 text-[11px] leading-relaxed text-amber-950">
      <p class="font-semibold text-amber-900">Kumbuka</p>
      <ol class="mt-1 list-decimal space-y-0.5 pl-4 marker:font-semibold">
        <li>Maliza kazi kwa mteja kikamilifu.</li>
        <li>Omba code ya ukamilishaji.</li>
        <li>Ingiza code hapa na thibitisha — malipo yatafanyika baada ya uthibitisho.</li>
      </ol>
    </div>

    <form id="codeInputForm" class="mt-4">
      <label for="muhitajiCode" class="mb-1.5 block text-[11px] font-semibold text-slate-700">Code ya mteja</label>
      <input
        type="text"
        id="muhitajiCode"
        name="muhitajiCode"
        inputmode="numeric"
        autocomplete="one-time-code"
        maxlength="6"
        pattern="[0-9]{6}"
        required
        placeholder="••••••"
        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-center text-[18px] font-bold tracking-[0.35em] text-slate-900 shadow-inner outline-none transition focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20"
      >
      <div class="mt-4 flex gap-2">
        <button type="button" class="flex-1 rounded-xl border border-slate-200 bg-white py-2.5 text-[12px] font-semibold text-slate-700 hover:bg-slate-50" onclick="closeCodeInputModal()">Funga</button>
        <button type="submit" class="flex-[1.4] rounded-xl bg-brand-600 py-2.5 text-[12px] font-bold text-white shadow-sm hover:bg-brand-700">Thibitisha</button>
      </div>
    </form>
  </div>
</div>

<script>
  let currentJobId = null;
  const modalEl = document.getElementById('codeInputModal');

  function showCodeInputModal(jobId) {
    currentJobId = jobId;
    modalEl.classList.remove('hidden');
    modalEl.classList.add('flex');
    const input = document.getElementById('muhitajiCode');
    input.value = '';
    input.focus();
  }

  function closeCodeInputModal() {
    modalEl.classList.add('hidden');
    modalEl.classList.remove('flex');
    document.getElementById('muhitajiCode').value = '';
    currentJobId = null;
  }

  document.getElementById('codeInputForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const code = document.getElementById('muhitajiCode').value.trim();
    if (!/^\d{6}$/.test(code)) {
      alert('Tafadhali ingiza code ya tarakimu 6');
      return;
    }
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = 'Inasubiri…';
    submitBtn.disabled = true;

    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('code', code);

    fetch(`/mfanyakazi/jobs/${currentJobId}/complete`, {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
      .then((response) => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
          return response.json();
        }
        return { success: true, message: 'Kazi imekamilika! Malipo yamefanyika.' };
      })
      .then((data) => {
        if (data.success !== false) {
          showNotification(data.message || 'Kazi imekamilika! Malipo yamefanyika kiotomatiki.', 'success');
          closeCodeInputModal();
          setTimeout(() => window.location.reload(), 1800);
        } else {
          showNotification(data.message || 'Kuna tatizo. Jaribu tena.', 'error');
          submitBtn.innerHTML = originalText;
          submitBtn.disabled = false;
        }
      })
      .catch(() => {
        showNotification('Kuna tatizo la mtandao. Jaribu tena.', 'error');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
      });
  });

  function showNotification(message, type) {
    const n = document.createElement('div');
    n.className =
      'fixed right-4 top-4 z-[1001] max-w-sm rounded-xl px-4 py-3 text-[12px] font-semibold shadow-lg transition-transform duration-300 ' +
      (type === 'success' ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white');
    n.style.transform = 'translateX(120%)';
    n.textContent = message;
    document.body.appendChild(n);
    requestAnimationFrame(() => {
      n.style.transform = 'translateX(0)';
    });
    setTimeout(() => {
      n.style.transform = 'translateX(120%)';
      setTimeout(() => n.remove(), 280);
    }, 2800);
  }

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeCodeInputModal();
  });

  modalEl.addEventListener('click', function (e) {
    if (e.target === modalEl) closeCodeInputModal();
  });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/mfanyakazi/assigned.blade.php ENDPATH**/ ?>