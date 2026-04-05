<?php $__env->startSection('title', 'Toa pesa'); ?>

<?php
  $s = $settings ?? [];
  $minW = (int) ($s['min_withdrawal'] ?? 5000);
  $feeW = (int) ($s['withdrawal_fee'] ?? 0);
  $bal = (int) $wallet->balance;
  $avail = (int) $wallet->available_balance;
  $maxAmount = max(0, $avail - $feeW);
  $canWithdraw = $avail >= ($minW + $feeW);
  $useTpShell = auth()->check() && in_array(auth()->user()->role, ['muhitaji', 'mfanyakazi'], true);
  $netOld = old('network_type', 'vodacom');
?>

<?php $__env->startSection('content'); ?>
<div class="flex min-h-screen bg-slate-100/90">
  <?php if($useTpShell): ?>
    <?php echo $__env->make('components.user-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <?php endif; ?>

  <main class="<?php echo e($useTpShell ? 'tp-main w-full min-w-0 p-4 pt-16 sm:p-6 lg:pt-6' : 'w-full max-w-6xl mx-auto px-4 py-8 sm:px-6 lg:px-8'); ?>">
    <div class="mx-auto w-full max-w-6xl space-y-6 lg:space-y-8">

      <?php if(session('status')): ?>
        <div class="rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-[13px] font-semibold text-brand-900">
          <?php echo e(session('status')); ?>

        </div>
      <?php endif; ?>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:gap-8 lg:items-start">

      <div class="space-y-6 lg:col-span-5">
      
      <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-600 via-teal-600 to-brand-700 p-6 text-white shadow-xl sm:p-8">
        <div class="pointer-events-none absolute -right-12 -top-12 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>
        <div class="pointer-events-none absolute bottom-0 left-0 h-24 w-48 rounded-full bg-teal-300/15 blur-2xl"></div>
        <div class="relative">
          <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/75">Wallet</p>
          <h1 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">Toa pesa</h1>
          <p class="mt-2 max-w-xl text-[13px] leading-relaxed text-white/90">
            <?php if(auth()->user()->role === 'muhitaji'): ?>
              Toa salio linalopatikana (siyo kiasi kilichoshikiliwa escrow). Malipo kwenda M-Pesa / mitandao mingine; ada ya kutoa inaweza kutozwa.
            <?php else: ?>
              Omba malipo kwenda M-Pesa, TigoPesa, au Airtel Money. Kiasi kitakatwa pamoja na ada ya kutoa.
            <?php endif; ?>
          </p>
          <div class="mt-6 rounded-2xl border border-white/20 bg-white/10 px-5 py-4 backdrop-blur-md">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-white/80">Salio linalopatikana</p>
            <p class="mt-1 text-3xl font-bold tabular-nums tracking-tight"><?php echo e(number_format($avail)); ?> <span class="text-lg font-semibold text-white/85">TZS</span></p>
            <?php if($bal !== $avail): ?>
              <p class="mt-1 text-[11px] text-white/75">Jumla ya wallet: <?php echo e(number_format($bal)); ?> TZS (ikiwemo escrow)</p>
            <?php endif; ?>
            <?php if($feeW > 0): ?>
              <p class="mt-2 text-[12px] text-white/85">Ada ya kutoa: <span class="font-bold tabular-nums"><?php echo e(number_format($feeW)); ?> TZS</span> kwa kila ombi</p>
            <?php endif; ?>
            <div class="mt-3 flex flex-wrap items-center gap-2">
              <?php if($canWithdraw): ?>
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/20 px-2.5 py-1 text-[11px] font-bold">✓ Unaweza kuomba kutoa</span>
              <?php else: ?>
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-rose-500/30 px-2.5 py-1 text-[11px] font-bold">Salio halitoshi (angalau <?php echo e(number_format($minW + $feeW)); ?> TZS)</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </section>

      <?php if(auth()->user()->role === 'mfanyakazi'): ?>
        <div class="rounded-2xl border border-amber-200/80 bg-gradient-to-r from-amber-50 to-orange-50/40 px-4 py-3 text-[12px] leading-relaxed text-amber-950 shadow-sm ring-1 ring-amber-100/80">
          <span class="font-bold">Makato ya huduma:</span> Kila kazi inaweza kukatwa <?php echo e($s['commission_rate'] ?? '10'); ?>% kama ada ya mfumo kabla ya kuonekana hapa. Hiki ni kile kilichobaki baada ya makato hayo.
        </div>
      <?php endif; ?>

      
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-1 lg:gap-3">
        <div class="rounded-xl border border-slate-200/80 bg-white p-3 shadow-sm ring-1 ring-slate-100/80">
          <p class="text-[9px] font-bold uppercase tracking-wider text-slate-500">Kiwango cha chini</p>
          <p class="mt-0.5 text-sm font-bold tabular-nums text-slate-900"><?php echo e(number_format($minW)); ?></p>
        </div>
        <div class="rounded-xl border border-slate-200/80 bg-white p-3 shadow-sm ring-1 ring-slate-100/80">
          <p class="text-[9px] font-bold uppercase tracking-wider text-slate-500">Kiasi cha juu</p>
          <p class="mt-0.5 text-sm font-bold tabular-nums text-brand-700"><?php echo e(number_format($maxAmount)); ?></p>
        </div>
        <div class="col-span-2 rounded-xl border border-violet-100 bg-violet-50/80 p-3 shadow-sm ring-1 ring-violet-100/80 sm:col-span-1 lg:col-span-1">
          <p class="text-[9px] font-bold uppercase tracking-wider text-violet-800">Jumla itakayokatwa</p>
          <p class="mt-0.5 text-sm font-bold tabular-nums text-violet-900" id="summaryDebit">0 TZS</p>
          <p class="mt-0.5 text-[10px] text-violet-700/90" id="summaryHint">Ingiza kiasi hapa chini</p>
        </div>
      </div>

      </div>

      <div class="space-y-4 lg:col-span-7">
      <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-lg ring-1 ring-slate-100/80">
        <?php if($errors->any()): ?>
          <div class="border-b border-red-100 bg-red-50/95 px-5 py-4">
            <p class="text-[12px] font-bold text-red-800">Tafadhali angalia</p>
            <ul class="mt-2 list-inside list-disc text-[12px] text-red-700">
              <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($e); ?></li>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post" action="<?php echo e(route('withdraw.submit')); ?>" id="withdrawForm" class="space-y-5 p-5 sm:p-6">
          <?php echo csrf_field(); ?>

          <div>
            <label class="mb-2 block text-[11px] font-bold uppercase tracking-wide text-slate-600" for="amount">Kiasi unachotaka kutoa (TZS)</label>
            <input type="number" name="amount" id="amount" required
              class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-3.5 text-lg font-bold tabular-nums text-slate-900 shadow-inner focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20"
              placeholder="<?php echo e(number_format($minW)); ?>"
              min="<?php echo e($minW); ?>"
              max="<?php echo e($maxAmount); ?>"
              value="<?php echo e(old('amount')); ?>"
              <?php echo e($canWithdraw ? '' : 'disabled'); ?>>
            <div class="mt-2 flex flex-wrap gap-2" id="quickAmounts" <?php echo e($canWithdraw ? '' : 'hidden'); ?>>
              <button type="button" data-pct="0.25" class="quick-amt rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-bold text-slate-700 shadow-sm hover:border-brand-300 hover:bg-brand-50">25%</button>
              <button type="button" data-pct="0.5" class="quick-amt rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-bold text-slate-700 shadow-sm hover:border-brand-300 hover:bg-brand-50">50%</button>
              <button type="button" data-pct="0.75" class="quick-amt rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-bold text-slate-700 shadow-sm hover:border-brand-300 hover:bg-brand-50">75%</button>
              <button type="button" data-pct="1" class="quick-amt rounded-lg border border-brand-200 bg-brand-50 px-3 py-1.5 text-[11px] font-bold text-brand-800 shadow-sm hover:bg-brand-100">Zote</button>
            </div>
            <p class="mt-2 text-[11px] text-slate-500">Kati ya <span class="font-semibold text-slate-700 tabular-nums"><?php echo e(number_format($minW)); ?></span> na <span class="font-semibold text-slate-700 tabular-nums"><?php echo e(number_format($maxAmount)); ?></span> TZS</p>
          </div>

          <div>
            <p class="mb-2 block text-[11px] font-bold uppercase tracking-wide text-slate-600">Njia ya malipo</p>
            <div class="grid grid-cols-3 gap-2">
              <button type="button" data-method="mpesa" data-network="vodacom" class="pay-card flex flex-col items-center gap-2 rounded-xl border-2 border-slate-200 bg-slate-50/50 px-2 py-4 text-center transition hover:border-brand-300 hover:bg-brand-50/50">
                <span class="text-2xl">📱</span>
                <span class="text-[10px] font-bold uppercase leading-tight text-slate-700">M-Pesa</span>
              </button>
              <button type="button" data-method="tigopesa" data-network="tigo" class="pay-card flex flex-col items-center gap-2 rounded-xl border-2 border-slate-200 bg-slate-50/50 px-2 py-4 text-center transition hover:border-brand-300 hover:bg-brand-50/50">
                <span class="text-2xl">🟠</span>
                <span class="text-[10px] font-bold uppercase leading-tight text-slate-700">TigoPesa</span>
              </button>
              <button type="button" data-method="airtel" data-network="airtel" class="pay-card flex flex-col items-center gap-2 rounded-xl border-2 border-slate-200 bg-slate-50/50 px-2 py-4 text-center transition hover:border-brand-300 hover:bg-brand-50/50">
                <span class="text-2xl">🔵</span>
                <span class="text-[10px] font-bold uppercase leading-tight text-slate-700">Airtel</span>
              </button>
            </div>
            <input type="hidden" name="method" id="selectedMethod" value="<?php echo e(old('method', 'mpesa')); ?>">
            <p class="mt-2 text-[11px] text-slate-500">Chaguo linalinganisha na mtandao chini (unaweza kubadilisha).</p>
          </div>

          <div>
            <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wide text-slate-600" for="phone_number">Namba ya simu</label>
            <input type="tel" name="phone_number" id="phone_number" required autocomplete="tel"
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-[15px] font-medium tabular-nums tracking-wide focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
              placeholder="07XXXXXXXX au 2557XXXXXXXX"
              value="<?php echo e(old('phone_number')); ?>">
            <p class="mt-1 text-[11px] text-slate-500">Tumia namba iliyosajiliwa kwenye pochi ya simu.</p>
          </div>

          <div>
            <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wide text-slate-600" for="registered_name">Jina lililosajiliwa kwenye simu</label>
            <input type="text" name="registered_name" id="registered_name" required
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-[14px] focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
              placeholder="Kama linaonekana kwenye M-Pesa / benki ya simu"
              value="<?php echo e(old('registered_name', auth()->user()->name)); ?>">
          </div>

          <div>
            <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-wide text-slate-600" for="network_type">Mtandao</label>
            <select name="network_type" id="network_type" required
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-[14px] font-medium focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
              <option value="vodacom" <?php if($netOld === 'vodacom'): echo 'selected'; endif; ?>>Vodacom (M-Pesa)</option>
              <option value="tigo" <?php if($netOld === 'tigo'): echo 'selected'; endif; ?>>Tigo (TigoPesa)</option>
              <option value="airtel" <?php if($netOld === 'airtel'): echo 'selected'; endif; ?>>Airtel Money</option>
              <option value="halotel" <?php if($netOld === 'halotel'): echo 'selected'; endif; ?>>Halotel</option>
              <option value="ttcl" <?php if($netOld === 'ttcl'): echo 'selected'; endif; ?>>TTCL</option>
            </select>
            <p class="mt-1 text-[11px] text-slate-500">Lazima liendane na namba na huduma unayotumia.</p>
          </div>

          <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-center sm:justify-between">
            <a href="<?php echo e(route('dashboard')); ?>" class="inline-flex justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-center text-[13px] font-semibold text-slate-700 shadow-sm hover:bg-slate-50">Rudi dashboard</a>
            <button type="submit" id="submitBtn"
              class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-brand-600 px-6 py-3.5 text-[14px] font-bold text-white shadow-lg shadow-emerald-600/25 transition enabled:hover:from-emerald-700 enabled:hover:to-brand-700 disabled:cursor-not-allowed disabled:opacity-45"
              <?php echo e($canWithdraw ? '' : 'disabled'); ?>>
              <span>💸</span> Tuma ombi la kutoa
            </button>
          </div>
        </form>
      </div>

      <p class="text-center text-[11px] leading-relaxed text-slate-500 lg:text-left">
        Ombi lako litapitia uthibitisho. Kama malipo hayajafanikiwa, salio linaweza kurudishwa kwenye wallet yako.
      </p>
      </div>

      </div>
    </div>
  </main>
</div>

<script>
(function () {
  var minW = <?php echo e($minW); ?>;
  var feeW = <?php echo e($feeW); ?>;
  var maxAmount = <?php echo e($maxAmount); ?>;
  var availableBalance = <?php echo e($avail); ?>;
  var canWithdraw = <?php echo e($canWithdraw ? 'true' : 'false'); ?>;

  var amountEl = document.getElementById('amount');
  var methodInput = document.getElementById('selectedMethod');
  var networkEl = document.getElementById('network_type');
  var summaryDebit = document.getElementById('summaryDebit');
  var summaryHint = document.getElementById('summaryHint');
  var form = document.getElementById('withdrawForm');
  var submitBtn = document.getElementById('submitBtn');

  function floorTo100(n) {
    if (n <= 0) return 0;
    return Math.floor(n / 100) * 100;
  }

  function updateSummary() {
    var raw = parseInt(amountEl.value, 10);
    if (isNaN(raw) || raw < 0) raw = 0;
    var debit = raw + feeW;
    summaryDebit.textContent = debit.toLocaleString('en-US') + ' TZS';
    if (raw === 0) {
      summaryHint.textContent = 'Ingiza kiasi hapo juu';
      return;
    }
    if (raw < minW) {
      summaryHint.textContent = 'Chini ya kiwango cha chini (' + minW.toLocaleString('en-US') + ' TZS)';
      summaryHint.className = 'mt-0.5 text-[10px] text-rose-600 font-semibold';
      return;
    }
    if (debit > availableBalance) {
      summaryHint.textContent = 'Jumla inazidi salio linalopatikana';
      summaryHint.className = 'mt-0.5 text-[10px] text-rose-600 font-semibold';
      return;
    }
    summaryHint.textContent = 'Utapokea simu: ' + raw.toLocaleString('en-US') + ' TZS · Ada: ' + feeW.toLocaleString('en-US') + ' TZS';
    summaryHint.className = 'mt-0.5 text-[10px] text-violet-700/90';
  }

  function clearPayCards() {
    document.querySelectorAll('.pay-card').forEach(function (b) {
      b.classList.remove('pay-on', 'border-brand-500', 'bg-brand-50', 'ring-2', 'ring-brand-500/30');
      b.classList.add('border-slate-200', 'bg-slate-50/50');
    });
  }

  function setPayCardActive(btn) {
    clearPayCards();
    btn.classList.add('pay-on', 'border-brand-500', 'bg-brand-50', 'ring-2', 'ring-brand-500/30');
    btn.classList.remove('border-slate-200', 'bg-slate-50/50');
    methodInput.value = btn.getAttribute('data-method');
    var net = btn.getAttribute('data-network');
    if (net && networkEl) networkEl.value = net;
  }

  document.querySelectorAll('.pay-card').forEach(function (btn) {
    btn.addEventListener('click', function () {
      setPayCardActive(btn);
    });
  });

  function syncCardFromNetwork() {
    var map = { vodacom: 'mpesa', tigo: 'tigopesa', airtel: 'airtel' };
    var m = map[networkEl.value];
    if (m) {
      var c = document.querySelector('.pay-card[data-method="' + m + '"]');
      if (c) setPayCardActive(c);
    } else {
      clearPayCards();
      methodInput.value = networkEl.value || 'mpesa';
    }
  }
  syncCardFromNetwork();

  document.querySelectorAll('.quick-amt').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (!canWithdraw || maxAmount < minW) return;
      var pct = parseFloat(btn.getAttribute('data-pct'), 10);
      var v = floorTo100(maxAmount * pct);
      if (v < minW) v = minW;
      if (v > maxAmount) v = maxAmount;
      amountEl.value = v;
      amountEl.dispatchEvent(new Event('input'));
    });
  });

  amountEl.addEventListener('input', function () {
    var v = parseInt(amountEl.value, 10);
    if (isNaN(v)) {
      updateSummary();
      return;
    }
    if (v > maxAmount) {
      amountEl.value = maxAmount;
      v = maxAmount;
    }
    if (v < minW && v > 0) {
      amountEl.setCustomValidity('Kiwango cha chini ni TZS ' + minW.toLocaleString('en-US'));
    } else {
      amountEl.setCustomValidity('');
    }
    updateSummary();
  });

  networkEl.addEventListener('change', syncCardFromNetwork);

  form.addEventListener('submit', function (e) {
    if (!canWithdraw) {
      e.preventDefault();
      return;
    }
    var v = parseInt(amountEl.value, 10);
    if (isNaN(v) || v < minW || v + feeW > availableBalance) {
      e.preventDefault();
      amountEl.reportValidity();
    }
  });

  updateSummary();
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/mfanyakazi/withdraw.blade.php ENDPATH**/ ?>