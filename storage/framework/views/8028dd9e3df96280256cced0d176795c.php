<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Jisajili — TendaPoa</title>
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
<body class="min-h-full bg-slate-100 font-sans text-sm text-slate-800 antialiased">
    <a href="<?php echo e(route('home')); ?>" class="absolute left-4 top-4 z-10 inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-[13px] font-medium text-slate-600 transition hover:bg-white hover:text-brand-700">
        ← Rudi Nyumbani
    </a>

    <div class="mx-auto max-w-5xl px-4 py-16 pt-20 lg:flex lg:min-h-screen lg:items-center lg:py-12">
        <div class="mb-6 hidden w-full flex-col justify-center rounded-2xl bg-gradient-to-br from-brand-600 to-brand-800 p-10 text-white shadow-sm lg:mb-0 lg:mr-0 lg:flex lg:w-[38%] lg:rounded-r-none">
            <div class="mb-5 flex h-11 w-11 items-center justify-center rounded-xl bg-white/15 text-lg font-bold">T</div>
            <h1 class="text-2xl font-bold tracking-tight">Fungua akaunti</h1>
            <p class="mt-3 text-[13px] leading-relaxed text-white/85">Jiunge na wateja na wafanyakazi. Weka eneo na simu kwa urahisi.</p>
            <ul class="mt-6 space-y-2 text-[12px] text-white/80">
                <li>• Malipo na escrow</li>
                <li>• Mazungumzo kwenye kila kazi</li>
                <li>• Dashibodi wazi kwa kila jukumu</li>
            </ul>
        </div>

        <div class="w-full rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm sm:p-8 lg:max-h-[calc(100vh-4rem)] lg:overflow-y-auto lg:rounded-l-none lg:border-l-0">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-slate-900">Anza sasa</h2>
                <p class="mt-1 text-[13px] text-slate-500">Jaza taarifa za msingi. Unaweza kusasisha baadaye.</p>
            </div>

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

            <form method="post" action="<?php echo e(route('register.post')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>

                <div>
                    <label for="name" class="mb-1.5 block text-[13px] font-medium text-slate-700">Jina kamili</label>
                    <input type="text" id="name" name="name" value="<?php echo e(old('name')); ?>" required autofocus
                        class="block w-full rounded-lg border border-slate-200 px-3 py-2.5 text-[13px] focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                        placeholder="Jina lako">
                </div>
                <div>
                    <label for="email" class="mb-1.5 block text-[13px] font-medium text-slate-700">Barua pepe</label>
                    <input type="email" id="email" name="email" value="<?php echo e(old('email')); ?>" required
                        class="block w-full rounded-lg border border-slate-200 px-3 py-2.5 text-[13px] focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                        placeholder="jina@example.com">
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="password" class="mb-1.5 block text-[13px] font-medium text-slate-700">Neno siri</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required
                                class="block w-full rounded-lg border border-slate-200 px-3 py-2.5 pr-10 text-[13px] focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            <button type="button" onclick="togglePassword('password')" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" aria-label="Onyesha">👁</button>
                        </div>
                    </div>
                    <div>
                        <label for="password_confirmation" class="mb-1.5 block text-[13px] font-medium text-slate-700">Rudia neno siri</label>
                        <div class="relative">
                            <input type="password" id="password_confirmation" name="password_confirmation" required
                                class="block w-full rounded-lg border border-slate-200 px-3 py-2.5 pr-10 text-[13px] focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            <button type="button" onclick="togglePassword('password_confirmation')" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" aria-label="Onyesha">👁</button>
                        </div>
                    </div>
                </div>

                <div>
                    <p class="mb-2 text-[13px] font-medium text-slate-700">Nafasi yako</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition <?php echo e(old('role', 'muhitaji') === 'muhitaji' ? 'border-brand-500 bg-brand-50/40' : 'border-slate-200 hover:border-slate-300'); ?>">
                            <input type="radio" name="role" value="muhitaji" class="mt-0.5 text-brand-600 focus:ring-brand-500" <?php echo e(old('role', 'muhitaji') === 'muhitaji' ? 'checked' : ''); ?> required>
                            <span>
                                <span class="block text-[13px] font-semibold text-slate-900">Muhitaji</span>
                                <span class="text-[12px] text-slate-500">Ninatafuta wafanyakazi</span>
                            </span>
                        </label>
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition <?php echo e(old('role') === 'mfanyakazi' ? 'border-brand-500 bg-brand-50/40' : 'border-slate-200 hover:border-slate-300'); ?>">
                            <input type="radio" name="role" value="mfanyakazi" class="mt-0.5 text-brand-600 focus:ring-brand-500" <?php echo e(old('role') === 'mfanyakazi' ? 'checked' : ''); ?>>
                            <span>
                                <span class="block text-[13px] font-semibold text-slate-900">Mfanyakazi</span>
                                <span class="text-[12px] text-slate-500">Ninafanya kazi</span>
                            </span>
                        </label>
                    </div>
                </div>

                <div>
                    <label for="phone" class="mb-1.5 block text-[13px] font-medium text-slate-700">Simu (hiari)</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo e(old('phone')); ?>"
                        class="block w-full rounded-lg border border-slate-200 px-3 py-2.5 text-[13px] focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                        placeholder="07xxxxxxxx">
                </div>

                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-4">
                    <p class="mb-3 text-[12px] font-semibold uppercase tracking-wide text-slate-500">Mahali (hiari)</p>
                    <div class="grid gap-3 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
                        <div>
                            <label for="lat" class="mb-1 block text-[11px] text-slate-500">Latitude</label>
                            <input type="text" id="lat" name="lat" value="<?php echo e(old('lat')); ?>"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[13px] focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="Lat">
                        </div>
                        <div>
                            <label for="lng" class="mb-1 block text-[11px] text-slate-500">Longitude</label>
                            <input type="text" id="lng" name="lng" value="<?php echo e(old('lng')); ?>"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[13px] focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="Lng">
                        </div>
                        <button type="button" id="gps" onclick="getGPSLocation()"
                            class="rounded-lg bg-brand-600 px-4 py-2 text-[13px] font-semibold text-white hover:bg-brand-700 sm:shrink-0">
                            GPS
                        </button>
                    </div>
                    <p class="mt-2 text-[11px] text-slate-500">Bonyeza GPS ili kujaza eneo (ruhusa ya kivinjari inahitajika).</p>
                </div>

                <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-[13px] font-semibold text-white shadow-sm hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                    Sajili akaunti
                </button>
            </form>

            <p class="mt-6 border-t border-slate-100 pt-6 text-center text-[13px] text-slate-600">
                Tayari una akaunti?
                <a href="<?php echo e(route('login')); ?>" class="font-semibold text-brand-700 hover:text-brand-800">Ingia</a>
            </p>
        </div>
    </div>

    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            if (!input) return;
            input.type = input.type === 'password' ? 'text' : 'password';
        }
        function getGPSLocation() {
            const latInput = document.getElementById('lat');
            const lngInput = document.getElementById('lng');
            const gpsBtn = document.getElementById('gps');
            if (!navigator.geolocation) {
                alert('Kivinjari hakiungi mkono GPS.');
                return;
            }
            gpsBtn.disabled = true;
            gpsBtn.textContent = '…';
            navigator.geolocation.getCurrentPosition(
                pos => {
                    latInput.value = pos.coords.latitude.toFixed(6);
                    lngInput.value = pos.coords.longitude.toFixed(6);
                    gpsBtn.textContent = 'GPS';
                    gpsBtn.disabled = false;
                },
                () => {
                    alert('Haiwezekani kupata eneo. Angalia ruhusa za eneo.');
                    gpsBtn.textContent = 'GPS';
                    gpsBtn.disabled = false;
                }
            );
        }
    </script>
</body>
</html>
<?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/auth/register.blade.php ENDPATH**/ ?>