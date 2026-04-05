<?php $__env->startSection('content'); ?>
  <h2>Thibitisha Malipo</h2>
  <div class="card">
    <p>Ombi la malipo limetumwa. Tafadhali thibitisha kwenye M-Pesa/TigoPesa/Airtel Money.</p>
    <p>Order: <b><?php echo e($job->payment->order_id); ?></b> • Kiasi: <b><?php echo e(number_format($job->payment->amount)); ?> TZS</b></p>
    <div id="st" class="badge"><?php echo e($job->payment->status); ?></div>

    <div style="margin-top: 20px;">
      <form action="<?php echo e(route('jobs.pay.retry', $job)); ?>" method="POST" style="display:inline;"
        onsubmit="return confirm('Kutuma ombi jipya la malipo?');">
        <?php echo csrf_field(); ?>
        <button type="submit" class="btn btn-primary"
          style="background: #2563eb; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; margin-right: 10px;">
          Jaribu Kulipa Tena
        </button>
      </form>

      <form action="<?php echo e(route('jobs.cancel', $job)); ?>" method="POST" style="display:inline;"
        onsubmit="return confirm('Je, una uhakika unataka kufuta kazi hii na kuacha kulipa?');">
        <?php echo csrf_field(); ?>
        <button type="submit" class="btn btn-danger"
          style="background: #ef4444; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer;">
          Futa Kazi (Sitaki Kulipa)
        </button>
      </form>
    </div>
    <p id="status-text" style="color: #666; font-style: italic; margin-top: 10px;">Inahakiki Malipo...</p>
  </div>

  <script>
    let startTime = new Date().getTime();
    const timeoutMs = 10 * 60 * 1000; // 10 minutes

    // Visual text animation
    setInterval(() => {
      const el = document.getElementById('status-text');
      if (el) {
        if (el.textContent.includes('...')) el.textContent = 'Inahakiki Malipo';
        else el.textContent += '.';
      }
    }, 500);

    setInterval(async () => {
      // Check timeout
      if (new Date().getTime() - startTime > timeoutMs) {
        // Redirect to pending jobs list after 10 mins
        window.location.href = '<?php echo e(route('my.jobs')); ?>';
        return;
      }

      try {
        const r = await fetch('<?php echo e(route('jobs.pay.poll', $job)); ?>?t=' + new Date().getTime());
        const j = await r.json();

        if (j.done) {
          document.getElementById('st').textContent = 'COMPLETED';
          document.getElementById('st').style.backgroundColor = 'green';
          document.getElementById('st').style.color = 'white';

          document.getElementById('status-text').textContent = 'Malipo Yamekamilika! Unapelekwa...';
          document.getElementById('status-text').style.color = 'green';
          document.getElementById('status-text').style.fontWeight = 'bold';

          setTimeout(() => {
            location.href = '<?php echo e(route('my.jobs')); ?>';
          }, 1000);
        }
      } catch (e) { console.error('Poll error', e); }
    }, 3000);
  </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/jobs/wait.blade.php ENDPATH**/ ?>