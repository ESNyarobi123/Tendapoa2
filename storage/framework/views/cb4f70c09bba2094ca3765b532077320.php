<?php $__env->startSection('title', 'Admin — Dashibodi ya mfanyakazi'); ?>

<?php $__env->startSection('content'); ?>
<div class="adm-subpage adm-stack">
    <div class="adm-page-head">
        <div>
            <h1 class="adm-page-head-title">Muonekano wa mfanyakazi</h1>
            <p class="adm-page-head-sub"><?php echo e($user->name); ?> · <?php echo e($user->email); ?></p>
        </div>
        <div class="adm-actions">
            <a href="<?php echo e(route('admin.user.details', $user)); ?>" class="adm-btn adm-btn--primary">Wasifu kamili</a>
            <a href="<?php echo e(route('admin.users')); ?>" class="adm-btn adm-btn--muted">← Watumiaji</a>
        </div>
    </div>

    <div class="adm-stat-row">
        <div class="adm-stat-card">
            <span class="adm-k">Kazi zilizopewa</span>
            <div class="adm-v"><?php echo e($stats['total_jobs']); ?></div>
        </div>
        <div class="adm-stat-card">
            <span class="adm-k">Zilizokamilika</span>
            <div class="adm-v" style="color:var(--adm-success);"><?php echo e($stats['completed_jobs']); ?></div>
        </div>
        <div class="adm-stat-card">
            <span class="adm-k">Salio la pochi</span>
            <div class="adm-v" style="color:var(--adm-accent);">TSh <?php echo e(number_format($stats['wallet_balance'])); ?></div>
        </div>
    </div>

    <div class="adm-card">
        <h2 class="adm-card-title">Kazi alizopewa</h2>
        <?php if($assignedJobs->isEmpty()): ?>
            <p style="color:var(--adm-muted);margin:0;">Hajapewa kazi bado.</p>
        <?php else: ?>
            <div class="adm-stack">
                <?php $__currentLoopData = $assignedJobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="adm-job-row">
                        <div style="min-width:0;flex:1;">
                            <div class="adm-v"><?php echo e($job->title); ?></div>
                            <div style="margin-top:0.35rem;display:flex;flex-wrap:wrap;gap:0.5rem 1rem;font-size:var(--adm-text-xs);color:var(--adm-muted);">
                                <span><?php echo e($job->category->name); ?></span>
                                <span>TSh <?php echo e(number_format($job->amount)); ?></span>
                                <?php
                                    $jc = match ($job->status) {
                                        'completed' => 'adm-badge--ok',
                                        'in_progress', 'assigned', 'submitted', 'funded', 'awaiting_payment', 'open', 'offered', 'disputed' => 'adm-badge--warn',
                                        'cancelled', 'expired', 'refunded' => 'adm-badge--danger',
                                        default => 'adm-badge--info',
                                    };
                                ?>
                                <span class="adm-badge <?php echo e($jc); ?>"><?php echo e($job->status); ?></span>
                            </div>
                            <p style="margin:0.35rem 0 0;font-size:var(--adm-text-sm);color:var(--adm-muted);">
                                Mteja: <a href="<?php echo e(route('admin.user.details', $job->muhitaji)); ?>" style="color:var(--adm-primary);font-weight:600;"><?php echo e($job->muhitaji->name); ?></a>
                            </p>
                        </div>
                        <div class="adm-job-row-actions">
                            <a href="<?php echo e(route('admin.job.details', $job)); ?>" class="adm-btn adm-btn--primary" style="font-size:var(--adm-text-xs);padding:0.35rem 0.65rem;">Maelezo</a>
                            <a href="<?php echo e(route('admin.chat.view', $job)); ?>" class="adm-btn adm-btn--success" style="font-size:var(--adm-text-xs);padding:0.35rem 0.65rem;">Mazungumzo</a>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="adm-pagination"><?php echo e($assignedJobs->links()); ?></div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/admin/user-dashboard-mfanyakazi.blade.php ENDPATH**/ ?>