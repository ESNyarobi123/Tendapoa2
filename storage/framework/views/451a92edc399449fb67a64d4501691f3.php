<?php $__env->startSection('title', 'Admin — Tangazo la taarifa'); ?>

<?php $__env->startSection('content'); ?>
<div class="adm-subpage adm-stack" style="max-width:42rem;">
    <div class="adm-page-head">
        <div>
            <h1 class="adm-page-head-title">Tuma taarifa (broadcast)</h1>
            <p class="adm-page-head-sub">Ujumbe utatumwa kama arifa kwa watumiaji walio na programu / wavuti</p>
        </div>
        <a href="<?php echo e(route('admin.dashboard')); ?>" class="adm-btn adm-btn--muted">← Dashibodi</a>
    </div>

    <div class="adm-card">
        <form method="POST" action="<?php echo e(route('admin.broadcast.send')); ?>">
            <?php echo csrf_field(); ?>

            <div class="adm-form-group">
                <label class="adm-label" for="title">Kichwa</label>
                <input id="title" class="adm-input" type="text" name="title" value="<?php echo e(old('title')); ?>" required autofocus>
                <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="adm-field-error"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="adm-form-group">
                <label class="adm-label" for="target">Lengo</label>
                <select id="target" name="target" class="adm-select">
                    <option value="all" <?php if(old('target', 'all') === 'all'): echo 'selected'; endif; ?>>Watumiaji wote</option>
                    <option value="muhitaji" <?php if(old('target') === 'muhitaji'): echo 'selected'; endif; ?>>Wahitaji tu</option>
                    <option value="mfanyakazi" <?php if(old('target') === 'mfanyakazi'): echo 'selected'; endif; ?>>Wafanyakazi tu</option>
                </select>
                <?php $__errorArgs = ['target'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="adm-field-error"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="adm-form-group">
                <label class="adm-label" for="message">Ujumbe</label>
                <textarea id="message" name="message" class="adm-textarea" required><?php echo e(old('message')); ?></textarea>
                <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="adm-field-error"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="adm-actions" style="justify-content:flex-end;">
                <button type="submit" class="adm-btn adm-btn--primary">Tuma taarifa</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/admin/broadcast.blade.php ENDPATH**/ ?>