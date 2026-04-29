<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Ingia — TendaPoa</title>
    <?php if (isset($component)) { $__componentOriginal49ef10f66da3120a6a0dd67699ffe19c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal49ef10f66da3120a6a0dd67699ffe19c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.auth-head-assets','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('auth-head-assets'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal49ef10f66da3120a6a0dd67699ffe19c)): ?>
<?php $attributes = $__attributesOriginal49ef10f66da3120a6a0dd67699ffe19c; ?>
<?php unset($__attributesOriginal49ef10f66da3120a6a0dd67699ffe19c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal49ef10f66da3120a6a0dd67699ffe19c)): ?>
<?php $component = $__componentOriginal49ef10f66da3120a6a0dd67699ffe19c; ?>
<?php unset($__componentOriginal49ef10f66da3120a6a0dd67699ffe19c); ?>
<?php endif; ?>
</head>
<body class="h-full bg-slate-100 font-sans text-sm text-slate-800 antialiased">
    <div class="min-h-full">
        <a href="<?php echo e(route('home')); ?>" class="absolute left-4 top-4 z-10 inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-[13px] font-medium text-slate-600 transition hover:bg-white hover:text-brand-700">
            ← Rudi Nyumbani
        </a>

        <div class="mx-auto flex min-h-full max-w-5xl flex-col justify-center px-4 py-16 lg:flex-row lg:items-stretch lg:gap-0 lg:py-12">
            <div class="hidden overflow-hidden rounded-2xl rounded-r-none bg-gradient-to-br from-brand-600 to-brand-800 p-10 text-white shadow-sm lg:flex lg:w-[42%] lg:flex-col lg:justify-center">
                <div class="mb-6 flex h-11 w-11 items-center justify-center rounded-xl bg-white/15 text-lg font-bold">T</div>
                <h1 class="text-2xl font-bold tracking-tight">TendaPoa</h1>
                <p class="mt-3 max-w-sm text-[13px] leading-relaxed text-white/85">
                    Onganisha na wafanyakazi au wateja. Malipo salama, mawasiliano wazi, na kazi zinazoonekana wazi.
                </p>
            </div>

            <div class="w-full rounded-2xl border border-slate-200/80 bg-white p-8 shadow-sm lg:max-w-none lg:flex-1 lg:rounded-l-none lg:border-l-0 lg:p-10">
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-slate-900">Karibu tena</h2>
                    <p class="mt-1 text-[13px] text-slate-500">Ingia ili kuendelea kwenye akaunti yako.</p>
                </div>

                <?php if(session('status')): ?>
                    <div class="mb-4 rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-[13px] font-medium text-brand-800">
                        <?php echo e(session('status')); ?>

                    </div>
                <?php endif; ?>

                <?php if($errors->any()): ?>
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-[13px] text-red-800">
                        <p class="font-semibold">Angalia makosa</p>
                        <ul class="mt-2 list-inside list-disc space-y-0.5">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($e); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?php echo e(route('login.post')); ?>" class="space-y-5">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label for="email" class="mb-1.5 block text-[13px] font-medium text-slate-700">Barua pepe</label>
                        <input type="email" id="email" name="email" value="<?php echo e(old('email')); ?>" required autofocus
                            class="block w-full rounded-lg border border-slate-200 px-3 py-2.5 text-[13px] text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                            placeholder="jina@example.com" autocomplete="username">
                    </div>
                    <div>
                        <label for="password" class="mb-1.5 block text-[13px] font-medium text-slate-700">Neno siri</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required
                                class="block w-full rounded-lg border border-slate-200 px-3 py-2.5 pr-11 text-[13px] text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                                placeholder="••••••••" autocomplete="current-password">
                            <button type="button" onclick="togglePassword()" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600" aria-label="Onyesha neno siri">
                                <span id="pw-toggle">👁</span>
                            </button>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-3 text-[13px]">
                        <label class="inline-flex cursor-pointer items-center gap-2 text-slate-600">
                            <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" <?php echo e(old('remember') ? 'checked' : ''); ?>>
                            Kumbuka mimi
                        </label>
                        <a href="<?php echo e(route('password.otp.request')); ?>" class="font-medium text-brand-700 hover:text-brand-800">Umesahau neno siri?</a>
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-[13px] font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                        Ingia
                    </button>
                </form>

                <p class="mt-8 border-t border-slate-100 pt-6 text-center text-[13px] text-slate-600">
                    Huna akaunti?
                    <a href="<?php echo e(route('register')); ?>" class="font-semibold text-brand-700 hover:text-brand-800">Jisajili</a>
                </p>
            </div>
        </div>
    </div>
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const label = document.getElementById('pw-toggle');
            if (input.type === 'password') {
                input.type = 'text';
                label.textContent = '🙈';
            } else {
                input.type = 'password';
                label.textContent = '👁';
            }
        }
    </script>
</body>
</html>
<?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/auth/login.blade.php ENDPATH**/ ?>