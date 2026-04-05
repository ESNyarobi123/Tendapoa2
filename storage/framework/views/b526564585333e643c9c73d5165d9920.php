<?php $__env->startSection('title', 'Admin — Ufuatiliaji wa shughuli'); ?>

<?php $__env->startSection('content'); ?>
<div class="adm-subpage adm-stack">
    <div class="adm-page-head">
        <div>
            <h1 class="adm-page-head-title">Shughuli za mtumiaji</h1>
            <p class="adm-page-head-sub"><?php echo e($user->name); ?> · <?php echo e($user->email); ?></p>
        </div>
        <div class="adm-actions">
            <a href="<?php echo e(route('admin.user.details', $user)); ?>" class="adm-btn adm-btn--primary">Wasifu</a>
            <a href="<?php echo e(route('admin.users')); ?>" class="adm-btn adm-btn--muted">← Watumiaji</a>
        </div>
    </div>

    <div class="adm-card">
        <h2 class="adm-card-title">Mfululizo wa matukio</h2>
        <?php if($activities->isEmpty()): ?>
            <p style="color:var(--adm-muted);margin:0;">Hakuna shughuli za hivi karibuni.</p>
        <?php else: ?>
            <?php $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($activity['type'] === 'job'): ?>
                    <div class="adm-tl-item adm-tl-item--job">
                        <div class="adm-tl-ico">📋</div>
                        <div style="min-width:0;flex:1;">
                            <div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:0.5rem;">
                                <span class="adm-v"><?php echo e($activity['data']->title); ?></span>
                                <span style="font-size:var(--adm-text-xs);color:var(--adm-muted);"><?php echo e($activity['timestamp']->diffForHumans()); ?></span>
                            </div>
                            <p style="margin:0.35rem 0 0;font-size:var(--adm-text-sm);color:var(--adm-muted);">
                                Kazi <?php echo e($activity['data']->user_id === $user->id ? 'aliyochapisha' : 'aliyopewa'); ?> · <?php echo e($activity['data']->status); ?>

                            </p>
                            <a href="<?php echo e(route('admin.job.details', $activity['data'])); ?>" style="font-size:var(--adm-text-xs);color:var(--adm-primary);font-weight:600;margin-top:0.35rem;display:inline-block;">Angalia kazi →</a>
                        </div>
                    </div>
                <?php elseif($activity['type'] === 'message'): ?>
                    <div class="adm-tl-item adm-tl-item--msg">
                        <div class="adm-tl-ico">💬</div>
                        <div style="min-width:0;flex:1;">
                            <div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:0.5rem;">
                                <span class="adm-v">Ujumbe wa faragha</span>
                                <span style="font-size:var(--adm-text-xs);color:var(--adm-muted);"><?php echo e($activity['timestamp']->diffForHumans()); ?></span>
                            </div>
                            <p style="margin:0.35rem 0 0;font-size:var(--adm-text-sm);color:var(--adm-muted);">
                                <?php echo e($activity['data']->sender_id === $user->id ? 'Imetumwa kwa' : 'Imepokea kutoka'); ?>

                                <span class="adm-v" style="font-weight:600;"><?php echo e($activity['data']->sender_id === $user->id ? $activity['data']->receiver->name : $activity['data']->sender->name); ?></span>
                            </p>
                            <p style="margin:0.35rem 0 0;font-size:var(--adm-text-sm);padding:0.5rem;border-radius:6px;background:rgba(0,0,0,.2);"><?php echo e(Str::limit($activity['data']->message, 120)); ?></p>
                            <?php if($activity['data']->job): ?>
                                <a href="<?php echo e(route('admin.chat.view', $activity['data']->job)); ?>" style="font-size:var(--adm-text-xs);color:var(--adm-primary);font-weight:600;margin-top:0.35rem;display:inline-block;">Mazungumzo kamili →</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php elseif($activity['type'] === 'transaction'): ?>
                    <div class="adm-tl-item adm-tl-item--txn">
                        <div class="adm-tl-ico">💰</div>
                        <div style="min-width:0;flex:1;">
                            <div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:0.5rem;">
                                <span class="adm-v"><?php echo e(ucfirst($activity['data']->type)); ?></span>
                                <span style="font-size:var(--adm-text-xs);color:var(--adm-muted);"><?php echo e($activity['timestamp']->diffForHumans()); ?></span>
                            </div>
                            <?php
                                $tt = strtoupper((string) $activity['data']->type);
                                $isOut = str_contains($tt, 'WITHDRAW') || str_contains($tt, 'DEBIT') || str_contains($tt, 'FEE') || str_contains($tt, 'HOLD');
                            ?>
                            <p style="margin:0.35rem 0 0;font-size:var(--adm-text-sm);">
                                Kiasi (<?php echo e($activity['data']->type); ?>):
                                <span class="<?php echo e($isOut ? 'adm-amount-debit' : 'adm-amount-credit'); ?>">
                                    <?php echo e($isOut ? '-' : '+'); ?>TSh <?php echo e(number_format($activity['data']->amount)); ?>

                                </span>
                            </p>
                            <?php if($activity['data']->description): ?>
                                <p style="margin:0.25rem 0 0;font-size:var(--adm-text-xs);color:var(--adm-muted);"><?php echo e($activity['data']->description); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/admin/user-monitor.blade.php ENDPATH**/ ?>