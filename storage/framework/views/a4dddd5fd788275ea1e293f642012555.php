<?php $__env->startSection('content'); ?>
<div style="max-width:700px;margin:0 auto">
    <h2 style="font-size:22px;font-weight:700;margin-bottom:4px">⚠️ Mgogoro #<?php echo e($dispute->id); ?></h2>
    <span class="badge" style="background:<?php echo e($dispute->isOpen() ? '#fef3c7' : '#f0fdf4'); ?>;color:<?php echo e($dispute->isOpen() ? '#92400e' : '#166534'); ?>">
        <?php echo e($dispute->getStatusLabel()); ?>

    </span>

    
    <div class="card" style="margin:16px 0">
        <div style="display:flex;justify-content:space-between">
            <div>
                <div style="font-size:12px;color:#999">Kazi</div>
                <div style="font-weight:600"><?php echo e($dispute->job->title); ?></div>
            </div>
            <div style="text-align:right">
                <div style="font-size:12px;color:#999">Kiasi</div>
                <div style="font-weight:700;color:#10b981">TZS <?php echo e(number_format($dispute->job->agreed_amount ?? $dispute->job->price)); ?></div>
            </div>
        </div>
    </div>

    
    <div class="card" style="margin-bottom:16px">
        <div style="display:flex;gap:20px">
            <div style="flex:1">
                <div style="font-size:12px;color:#999;margin-bottom:4px">Alifungua mgogoro</div>
                <div style="font-weight:600"><?php echo e($dispute->raisedByUser->name); ?></div>
                <div style="font-size:12px;color:#666"><?php echo e($dispute->raisedByUser->role); ?></div>
            </div>
            <div style="flex:1">
                <div style="font-size:12px;color:#999;margin-bottom:4px">Dhidi ya</div>
                <div style="font-weight:600"><?php echo e($dispute->againstUser->name); ?></div>
                <div style="font-size:12px;color:#666"><?php echo e($dispute->againstUser->role); ?></div>
            </div>
        </div>
    </div>

    
    <div class="card" style="margin-bottom:16px">
        <div style="font-size:13px;font-weight:600;margin-bottom:6px">Sababu ya Mgogoro</div>
        <p style="color:#374151;font-size:14px;line-height:1.5;margin:0"><?php echo e($dispute->reason); ?></p>
    </div>

    
    <?php if($dispute->isResolved()): ?>
    <div class="card" style="margin-bottom:16px;background:#f0fdf4;border-color:#bbf7d0">
        <div style="font-size:13px;font-weight:600;margin-bottom:6px;color:#166534">Uamuzi</div>
        <?php if($dispute->resolution_note): ?>
            <p style="color:#374151;font-size:14px;margin:0 0 8px"><?php echo e($dispute->resolution_note); ?></p>
        <?php endif; ?>
        <div style="display:flex;gap:16px;font-size:13px">
            <?php if($dispute->worker_amount): ?>
                <div><span style="color:#999">Mfanyakazi:</span> <strong>TZS <?php echo e(number_format($dispute->worker_amount)); ?></strong></div>
            <?php endif; ?>
            <?php if($dispute->client_refund_amount): ?>
                <div><span style="color:#999">Muhitaji (refund):</span> <strong>TZS <?php echo e(number_format($dispute->client_refund_amount)); ?></strong></div>
            <?php endif; ?>
        </div>
        <?php if($dispute->resolvedByUser): ?>
            <div style="margin-top:8px;font-size:11px;color:#999">
                Imeamuliwa na <?php echo e($dispute->resolvedByUser->name); ?> — <?php echo e($dispute->resolved_at->format('d M Y H:i')); ?>

            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    
    <div style="margin-bottom:16px">
        <h3 style="font-size:16px;font-weight:600;margin-bottom:10px">Mazungumzo</h3>

        <?php $__empty_1 = true; $__currentLoopData = $dispute->messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="card" style="margin-bottom:8px;<?php echo e($msg->is_admin ? 'border-left:3px solid #ef4444' : ''); ?>">
            <div style="display:flex;justify-content:space-between;margin-bottom:4px">
                <span style="font-weight:600;font-size:13px">
                    <?php echo e($msg->user->name); ?>

                    <?php if($msg->is_admin): ?> <span style="color:#ef4444;font-size:11px">(Admin)</span> <?php endif; ?>
                </span>
                <span style="color:#999;font-size:11px"><?php echo e($msg->created_at->diffForHumans()); ?></span>
            </div>
            <p style="margin:0;font-size:14px;color:#374151"><?php echo e($msg->message); ?></p>
            <?php if($msg->attachment): ?>
                <a href="<?php echo e(url('image/' . $msg->attachment)); ?>" target="_blank" style="font-size:12px;color:#2563eb;margin-top:4px;display:inline-block">📎 Angalia Kiambatisho</a>
            <?php endif; ?>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div style="text-align:center;padding:20px;color:#999;font-size:13px">Hakuna ujumbe bado.</div>
        <?php endif; ?>
    </div>

    
    <?php if($dispute->isOpen()): ?>
    <div class="card">
        <h4 style="font-size:14px;font-weight:600;margin:0 0 8px">Ongeza Ujumbe</h4>
        <form method="POST" action="<?php echo e(route('disputes.show', $dispute)); ?>">
            <?php echo csrf_field(); ?>
            <textarea name="message" rows="3" required placeholder="Andika ujumbe wako hapa..." style="margin-bottom:10px"></textarea>
            <button type="submit" class="btn btn-primary">Tuma Ujumbe</button>
        </form>
    </div>
    <?php endif; ?>

    <div style="text-align:center;margin-top:16px">
        <a href="<?php echo e(route('jobs.show', $dispute->job)); ?>" style="color:#666;font-size:13px">← Rudi kwenye kazi</a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/disputes/show.blade.php ENDPATH**/ ?>