<?php $__env->startSection('title', 'Malipo ya escrow'); ?>

<?php $__env->startSection('content'); ?>
<?php
  $w = $wallet;
  $avail = (int) $w->available_balance;
  $held = (int) $w->held_balance;
?>
<div class="flex min-h-screen bg-slate-50">
  <?php echo $__env->make('components.user-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <main class="tp-main w-full min-w-0 p-4 pt-16 sm:p-6 lg:pt-6">
    <div class="mx-auto w-full max-w-[min(100%,960px)] space-y-5">

      <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <h1 class="text-lg font-bold tracking-tight text-slate-900 sm:text-xl">Lipa escrow ya kazi</h1>
          <p class="mt-0.5 max-w-xl text-[12px] leading-relaxed text-slate-500">
            Fedha zinahifadhiwa salama hadi uthibitishe kazi imekamilika. Unaweza kulipa kutoka <strong class="text-slate-700">wallet</strong> (salio linalopatikana) au <strong class="text-slate-700">simu</strong> (USSD push).
          </p>
        </div>
        <a href="<?php echo e(route('jobs.show', $job)); ?>" class="inline-flex shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-[12px] font-medium text-slate-700 shadow-sm hover:bg-slate-50">← Rudi kwenye kazi</a>
      </div>

      <?php if(session('success')): ?>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[12px] font-medium text-emerald-900"><?php echo e(session('success')); ?></div>
      <?php endif; ?>
      <?php if($errors->any()): ?>
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-[12px] font-medium text-red-900"><?php echo e($errors->first()); ?></div>
      <?php endif; ?>

      <div class="grid gap-5 lg:grid-cols-5 lg:items-start">
        <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 lg:col-span-2">
          <p class="text-[10px] font-bold uppercase tracking-wide text-amber-700">Inasubiri malipo</p>
          <h2 class="mt-1 text-[15px] font-bold text-slate-900"><?php echo e($job->title); ?></h2>
          <p class="mt-0.5 text-[12px] text-slate-500"><?php echo e($job->category->name ?? ''); ?></p>

          <?php if($job->selectedWorker): ?>
            <div class="mt-4 flex items-center gap-3 rounded-xl border border-emerald-100 bg-emerald-50/80 px-3 py-3">
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-[13px] font-bold text-white">
                <?php echo e(strtoupper(substr($job->selectedWorker->name, 0, 1))); ?>

              </div>
              <div class="min-w-0">
                <p class="text-[13px] font-semibold text-slate-900"><?php echo e($job->selectedWorker->name); ?></p>
                <p class="text-[11px] text-slate-600">Mfanyakazi aliyechaguliwa</p>
              </div>
            </div>
          <?php endif; ?>

          <div class="mt-4 rounded-xl border border-slate-100 bg-slate-50 px-4 py-4 text-center">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Kiasi cha escrow</p>
            <p class="mt-1 text-2xl font-extrabold tabular-nums text-emerald-700 sm:text-3xl"><?php echo e(number_format($agreedAmount)); ?> <span class="text-base font-bold text-emerald-600/90">TZS</span></p>
            <p class="mt-2 text-[11px] leading-relaxed text-slate-500">Haitatumwa kwa mfanyakazi hadi uthibitishe kazi imekamilika.</p>
          </div>

          <div class="mt-4 space-y-2 rounded-xl border border-slate-100 bg-white px-3 py-3 text-[11px] text-slate-600">
            <p class="flex justify-between"><span>Salio jumla (wallet)</span><span class="font-semibold tabular-nums text-slate-900"><?php echo e(number_format($w->balance)); ?> TZS</span></p>
            <p class="flex justify-between"><span>Imeshikiliwa (escrow nyingine)</span><span class="font-semibold tabular-nums text-slate-800"><?php echo e(number_format($held)); ?> TZS</span></p>
            <p class="flex justify-between border-t border-slate-100 pt-2"><span>Linalopatikana kulipa</span><span class="font-bold tabular-nums <?php echo e($canPayFromWallet ? 'text-emerald-700' : 'text-red-600'); ?>"><?php echo e(number_format($avail)); ?> TZS</span></p>
          </div>
        </section>

        <div class="space-y-4 lg:col-span-3">
          <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <h3 class="text-[13px] font-bold text-slate-900">1. Lipa kutoka wallet</h3>
            <p class="mt-1 text-[12px] text-slate-500">Tumia salio linalopatikana (siyo sehemu iliyoshikiliwa kwenye escrow).</p>
            <?php if($canPayFromWallet): ?>
              <form method="POST" action="<?php echo e(route('jobs.fund.wallet', $job)); ?>" class="mt-4">
                <?php echo csrf_field(); ?>
                <button type="submit" class="w-full rounded-xl bg-emerald-600 px-4 py-3 text-[13px] font-bold text-white shadow-sm hover:bg-emerald-700">
                  Thibitisha TZS <?php echo e(number_format($agreedAmount)); ?> kutoka wallet
                </button>
              </form>
            <?php else: ?>
              <div class="mt-4 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-[12px] leading-relaxed text-red-900">
                Salio halitoshi. Unahitaji <strong class="tabular-nums"><?php echo e(number_format(max(0, $agreedAmount - $avail))); ?> TZS</strong> zaidi ya salio linalopatikana.
              </div>
              <a href="<?php echo e(route('wallet.deposit')); ?>" class="mt-3 inline-flex w-full items-center justify-center rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-[12px] font-bold text-brand-800 hover:bg-brand-100">
                Ongeza salio kwenye wallet →
              </a>
            <?php endif; ?>
          </section>

          <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <h3 class="text-[13px] font-bold text-slate-900">2. Lipa kwa simu (M-Pesa / mitandao mingine)</h3>
            <p class="mt-1 text-[12px] text-slate-500">Push itatumwa; thibitisha kwenye simu. Fedha zitaunganishwa na escrow ya kazi hii.</p>
            <form method="POST" action="<?php echo e(route('jobs.fund.external', $job)); ?>" class="mt-4 space-y-3">
              <?php echo csrf_field(); ?>
              <div>
                <label for="fund-phone" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-600">Namba ya simu</label>
                <input
                  type="tel"
                  id="fund-phone"
                  name="phone"
                  required
                  pattern="^(0[6-7]\d{8}|255[6-7]\d{8})$"
                  value="<?php echo e(old('phone', Auth::user()->phone)); ?>"
                  placeholder="07xxxxxxxx"
                  class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-[13px] text-slate-900 shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"
                >
              </div>
              <button type="submit" class="w-full rounded-xl bg-brand-600 px-4 py-3 text-[13px] font-bold text-white shadow-sm hover:bg-brand-700">
                Tuma ombi la malipo TZS <?php echo e(number_format($agreedAmount)); ?>

              </button>
            </form>
          </section>

          <div class="flex flex-wrap gap-3 text-[11px] text-slate-600">
            <a href="<?php echo e(route('wallet.deposit')); ?>" class="font-semibold text-brand-700 hover:underline">Weka pesa wallet</a>
            <span class="text-slate-300">·</span>
            <a href="<?php echo e(route('withdraw.form')); ?>" class="font-semibold text-slate-700 hover:underline">Toa pesa</a>
            <span class="text-slate-300">·</span>
            <a href="<?php echo e(route('my.applications')); ?>" class="font-semibold text-slate-700 hover:underline">Maombi</a>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/jobs/fund.blade.php ENDPATH**/ ?>