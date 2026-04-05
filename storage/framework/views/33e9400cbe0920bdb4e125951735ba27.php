<?php $__env->startSection('title', 'Admin — Maelezo ya kazi'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $statusClass = match ($job->status) {
        'completed' => 'adm-badge--ok',
        'in_progress', 'assigned', 'submitted', 'funded', 'awaiting_payment', 'open', 'offered', 'ready_for_confirmation', 'disputed' => 'adm-badge--warn',
        'cancelled', 'expired', 'refunded' => 'adm-badge--danger',
        default => 'adm-badge--info',
    };
?>
<div class="adm-subpage adm-stack">
    <div class="adm-page-head">
        <div>
            <h1 class="adm-page-head-title">Maelezo ya kazi</h1>
            <p class="adm-page-head-sub"><?php echo e($job->title); ?></p>
        </div>
        <div class="adm-actions">
            <?php if($job->status !== 'completed'): ?>
                <form action="<?php echo e(route('admin.job.force-complete', $job)); ?>" method="POST" class="adm-actions" style="margin:0;">
                    <?php echo csrf_field(); ?>
                    <button type="button" class="adm-btn adm-btn--success"
                        onclick="var f=this.closest('form'); (typeof tpConfirm==='function'?tpConfirm('Thibitisha force-complete kwa kazi hii?'):Promise.resolve(confirm('Force complete?'))).then(function(ok){ if(ok) f.submit(); });">
                        Force complete
                    </button>
                </form>
            <?php endif; ?>
            <?php if($job->status !== 'cancelled'): ?>
                <form action="<?php echo e(route('admin.job.force-cancel', $job)); ?>" method="POST" class="adm-actions" style="margin:0;">
                    <?php echo csrf_field(); ?>
                    <button type="button" class="adm-btn adm-btn--danger"
                        onclick="var f=this.closest('form'); (typeof tpConfirm==='function'?tpConfirm('Thibitisha kughairi kazi hii kwa nguvu?'):Promise.resolve(confirm('Force cancel?'))).then(function(ok){ if(ok) f.submit(); });">
                        Force cancel
                    </button>
                </form>
            <?php endif; ?>
            <?php if($job->accepted_worker_id): ?>
                <a href="<?php echo e(route('admin.chat.view', $job)); ?>" class="adm-btn adm-btn--accent">💬 Mazungumzo</a>
            <?php endif; ?>
            <a href="<?php echo e(route('admin.jobs')); ?>" class="adm-btn adm-btn--muted">← Kazi zote</a>
        </div>
    </div>

    <div class="adm-card">
        <h2 class="adm-card-title">Taarifa za kazi</h2>
        <div class="adm-grid2">
            <div>
                <span class="adm-k">Kichwa</span>
                <span class="adm-v"><?php echo e($job->title); ?></span>
            </div>
            <div>
                <span class="adm-k">Hali</span>
                <span class="adm-badge <?php echo e($statusClass); ?>"><?php echo e($job->status); ?></span>
            </div>
            <div>
                <span class="adm-k">Bei</span>
                <span class="adm-v">TSh <?php echo e(number_format($job->amount)); ?></span>
            </div>
            <div>
                <span class="adm-k">Kundi</span>
                <span class="adm-v"><?php echo e($job->category->name); ?></span>
            </div>
            <div>
                <span class="adm-k">Aliyechapisha</span>
                <span class="adm-v"><a href="<?php echo e(route('admin.user.details', $job->muhitaji)); ?>"><?php echo e($job->muhitaji->name); ?></a></span>
            </div>
            <div>
                <span class="adm-k">Mfanyakazi</span>
                <span class="adm-v">
                    <?php if($job->acceptedWorker): ?>
                        <a href="<?php echo e(route('admin.user.details', $job->acceptedWorker)); ?>"><?php echo e($job->acceptedWorker->name); ?></a>
                    <?php else: ?>
                        <span style="color:var(--adm-muted)">Hajateuliwa</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="adm-grid2-span">
                <span class="adm-k">Maelezo</span>
                <p class="adm-v" style="font-weight:500;margin:0;"><?php echo e($job->description ?? 'Hakuna maelezo'); ?></p>
            </div>
            <div>
                <span class="adm-k">Tarehe</span>
                <span class="adm-v"><?php echo e($job->created_at->format('d M Y H:i')); ?></span>
            </div>
            <?php if($job->completed_at): ?>
                <div>
                    <span class="adm-k">Imekamilika</span>
                    <span class="adm-v"><?php echo e($job->completed_at->format('d M Y H:i')); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if($job->accepted_worker_id && $job->privateMessages->count() > 0): ?>
        <div class="adm-card">
            <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:0.5rem;margin-bottom:0.75rem;">
                <h2 class="adm-card-title" style="margin:0;">Ujumbe wa faragha (<?php echo e($job->privateMessages->count()); ?>)</h2>
                <a href="<?php echo e(route('admin.chat.view', $job)); ?>" style="font-size:var(--adm-text-sm);color:var(--adm-primary);font-weight:600;">Fungua mazungumzo →</a>
            </div>
            <div style="max-height:24rem;overflow-y:auto;display:flex;flex-direction:column;gap:0.5rem;">
                <?php $__currentLoopData = $job->privateMessages->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="adm-msg-preview <?php echo e($message->sender_id === $job->user_id ? 'adm-msg-preview--a' : 'adm-msg-preview--b'); ?>">
                        <span class="adm-v" style="font-size:var(--adm-text-xs);"><?php echo e($message->sender->name); ?></span>
                        <span style="color:var(--adm-muted);font-size:0.7rem;margin-left:0.35rem;"><?php echo e($message->created_at->diffForHumans()); ?></span>
                        <p style="margin:0.35rem 0 0;font-size:var(--adm-text-sm);color:var(--adm-text);"><?php echo e(Str::limit($message->message, 150)); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="adm-card">
        <h2 class="adm-card-title">Maoni na maombi (<?php echo e($job->comments->count()); ?>)</h2>
        <?php if($job->comments->isEmpty()): ?>
            <p style="color:var(--adm-muted);margin:0;">Hakuna maoni bado.</p>
        <?php else: ?>
            <?php $__currentLoopData = $job->comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="adm-comment">
                    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:0.5rem;margin-bottom:0.35rem;">
                        <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                            <span class="adm-v"><?php echo e($comment->user->name); ?></span>
                            <?php if($comment->is_application): ?>
                                <span class="adm-badge adm-badge--ok">Maombi</span>
                            <?php endif; ?>
                        </div>
                        <span style="font-size:var(--adm-text-xs);color:var(--adm-muted);"><?php echo e($comment->created_at->diffForHumans()); ?></span>
                    </div>
                    <p style="margin:0;color:var(--adm-text);"><?php echo e($comment->message); ?></p>
                    <?php if($comment->bid_amount): ?>
                        <p style="margin:0.35rem 0 0;font-size:var(--adm-text-sm);color:var(--adm-accent);">Bei aliyopendekeza: TSh <?php echo e(number_format($comment->bid_amount)); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
    </div>

    <?php if($job->payment): ?>
        <div class="adm-card">
            <h2 class="adm-card-title">Malipo</h2>
            <div class="adm-grid2">
                <div>
                    <span class="adm-k">Kiasi</span>
                    <span class="adm-v">TSh <?php echo e(number_format($job->payment->amount)); ?></span>
                </div>
                <div>
                    <span class="adm-k">Hali</span>
                    <span class="adm-v"><?php echo e($job->payment->status); ?></span>
                </div>
                <div>
                    <span class="adm-k">Kumbukumbu</span>
                    <span class="adm-v"><?php echo e($job->payment->reference ?? '—'); ?></span>
                </div>
                <div>
                    <span class="adm-k">Tarehe</span>
                    <span class="adm-v"><?php echo e($job->payment->created_at->format('d M Y H:i')); ?></span>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/admin/job-details.blade.php ENDPATH**/ ?>