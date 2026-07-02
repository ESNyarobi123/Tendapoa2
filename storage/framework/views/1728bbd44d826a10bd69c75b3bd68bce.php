<?php
    $activeVersion = \App\Models\AppVersion::getActive();

    $categoryEmoji = function (?string $name): string {
        $n = strtolower($name ?? '');
        if (str_contains($n, 'safisha') || str_contains($n, 'clean')) return '🧹';
        if (str_contains($n, 'nyumba') || str_contains($n, 'home')) return '🏠';
        if (str_contains($n, 'ofisi') || str_contains($n, 'office')) return '🏢';
        if (str_contains($n, 'ufundi') || str_contains($n, 'repair')) return '🔧';
        return '✨';
    };

    $imgHero = 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=1400&q=80';
    $imgGallery = [
        [
            'src' => 'https://images.unsplash.com/photo-1628177142898-406fcaaa4cbc?auto=format&fit=crop&w=800&q=80',
            'title' => 'Usafi wa nyumba',
            'caption' => 'Wafanyakazi walioidhinishwa wanafanya kazi kwa uangalifu',
        ],
        [
            'src' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=800&q=80',
            'title' => 'Usafi wa ofisi',
            'caption' => 'Mazingira safi kwa biashara yako kukua',
        ],
        [
            'src' => 'https://images.unsplash.com/photo-1527515637462-cff94eecc1ac?auto=format&fit=crop&w=800&q=80',
            'title' => 'Usafi wa kina',
            'caption' => 'Huduma ya kitaalamu — matokeo unayoyaona',
        ],
    ];
?>
<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>TendaPoa — Usafi na Huduma za Kuegemea Tanzania</title>
    <meta name="description" content="Pata wafanyakazi wa usafi na huduma nyingine kwa urahisi. Lipa salama kwa escrow kupitia app ya TendaPoa.">
    <?php if (isset($component)) { $__componentOriginal8ed66ee756d6151744dfb15be5d42a21 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8ed66ee756d6151744dfb15be5d42a21 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.brand-favicon','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('brand-favicon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8ed66ee756d6151744dfb15be5d42a21)): ?>
<?php $attributes = $__attributesOriginal8ed66ee756d6151744dfb15be5d42a21; ?>
<?php unset($__attributesOriginal8ed66ee756d6151744dfb15be5d42a21); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8ed66ee756d6151744dfb15be5d42a21)): ?>
<?php $component = $__componentOriginal8ed66ee756d6151744dfb15be5d42a21; ?>
<?php unset($__componentOriginal8ed66ee756d6151744dfb15be5d42a21); ?>
<?php endif; ?>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/landing.css']); ?>
</head>
<body class="tp-landing-body">

<header class="tp-landing-nav" id="landingNav">
    <div class="tp-landing-nav-inner">
        <?php if (isset($component)) { $__componentOriginal8741a05e11b0c77d19ec61b6b35b26b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8741a05e11b0c77d19ec61b6b35b26b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.brand-logo','data' => ['href' => ''.e(route('home')).'','class' => 'tp-landing-logo','textClass' => 'text-inherit','size' => 'md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('brand-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('home')).'','class' => 'tp-landing-logo','text-class' => 'text-inherit','size' => 'md']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8741a05e11b0c77d19ec61b6b35b26b3)): ?>
<?php $attributes = $__attributesOriginal8741a05e11b0c77d19ec61b6b35b26b3; ?>
<?php unset($__attributesOriginal8741a05e11b0c77d19ec61b6b35b26b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8741a05e11b0c77d19ec61b6b35b26b3)): ?>
<?php $component = $__componentOriginal8741a05e11b0c77d19ec61b6b35b26b3; ?>
<?php unset($__componentOriginal8741a05e11b0c77d19ec61b6b35b26b3); ?>
<?php endif; ?>
        <nav class="tp-landing-nav-links" aria-label="Menyu kuu">
            <a href="#app" class="tp-landing-nav-link">App</a>
            <a href="#usafi" class="tp-landing-nav-link">Huduma za usafi</a>
            <a href="#jinsi" class="tp-landing-nav-link">Jinsi inavyofanya</a>
            <?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(route('dashboard')); ?>" class="tp-landing-btn tp-landing-btn-brand ml-2">Dashibodi</a>
            <?php else: ?>
                <a href="<?php echo e(route('login')); ?>" class="tp-landing-nav-link">Ingia</a>
                <a href="<?php echo e(route('register')); ?>" class="tp-landing-btn tp-landing-btn-primary ml-2">Jisajili bure</a>
            <?php endif; ?>
        </nav>
        <button type="button" class="rounded-lg p-2 text-slate-700 md:hidden" id="mobileMenuBtn" aria-label="Menyu">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>
    <div class="tp-landing-mobile-menu" id="mobileMenu">
        <a href="#app" class="tp-landing-nav-link block">App</a>
        <a href="#usafi" class="tp-landing-nav-link block">Huduma za usafi</a>
        <a href="#jinsi" class="tp-landing-nav-link block">Jinsi inavyofanya</a>
        <?php if(auth()->guard()->check()): ?>
            <a href="<?php echo e(route('dashboard')); ?>" class="tp-landing-btn tp-landing-btn-brand mt-3 w-full">Dashibodi</a>
        <?php else: ?>
            <a href="<?php echo e(route('login')); ?>" class="tp-landing-nav-link block">Ingia</a>
            <a href="<?php echo e(route('register')); ?>" class="tp-landing-btn tp-landing-btn-primary mt-3 w-full">Jisajili bure</a>
        <?php endif; ?>
    </div>
</header>


<section class="tp-landing-hero-split" id="home">
    <div class="tp-landing-hero-copy">
        <span class="tp-landing-pill">🧹 Usafi · Huduma · Kazi salama</span>
        <h1 class="tp-landing-hero-title">
            Pata mfanyakazi wa <em>usafi</em> unayemwamini — haraka na salama
        </h1>
        <p class="tp-landing-hero-sub">
            TendaPoa inaunganisha wateja na wafanyakazi halisi wanaofanya usafi wa nyumba, ofisi, na mazingira mengine.
            Chapisha kazi kutoka simu, chagua mtoa huduma, na lipa kwa <strong>escrow</strong>.
        </p>
        <div class="tp-landing-hero-actions">
            <a href="<?php echo e(route('register')); ?>" class="tp-landing-btn tp-landing-btn-primary">
                Anza sasa — bure
            </a>
            <a href="#app" class="tp-landing-btn tp-landing-btn-secondary">
                Tazama app
            </a>
        </div>
        <div class="tp-landing-trust-row">
            <span class="tp-landing-trust-item">🔒 Malipo salama</span>
            <span class="tp-landing-trust-item">⭐ Wafanyakazi halisi</span>
            <span class="tp-landing-trust-item">📱 App ya Android</span>
        </div>
    </div>
    <div class="tp-landing-hero-photo-wrap">
        <img
            src="<?php echo e($imgHero); ?>"
            alt="Mfanyakazi wa usafi akifanya kazi — huduma ya TendaPoa"
            class="tp-landing-hero-photo"
            width="1400"
            height="933"
            loading="eager"
            fetchpriority="high"
        >
        <div class="tp-landing-hero-photo-overlay" aria-hidden="true"></div>
        <div class="tp-landing-hero-badge-float">
            <div class="tp-landing-float-card">
                <p class="text-2xl font-extrabold text-brand-700">500+</p>
                <p class="text-[11px] font-semibold text-slate-600">Kazi za usafi zilizokamilika</p>
            </div>
            <div class="tp-landing-float-card hidden sm:block">
                <p class="text-lg font-extrabold text-slate-900">★ 4.9</p>
                <p class="text-[11px] font-semibold text-slate-600">Kuridhika kwa wateja</p>
            </div>
        </div>
    </div>
</section>


<section class="tp-landing-app-section" id="app">
    <div class="tp-landing-container">
        <div class="tp-landing-section-head">
            <p class="tp-landing-kicker">App ya simu</p>
            <h2 class="tp-landing-section-title">Tazama jinsi app inavyofanya</h2>
            <p class="tp-landing-section-sub">
                Mteja anachapisha kazi kwa sekunde chache. Mfanyakazi anaona orodha ya kazi karibu naye na kuomba kufanya.
            </p>
        </div>
        <div class="tp-landing-phones">
            <?php echo $__env->make('partials.landing-phone-client', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('partials.landing-phone-worker', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
        <div class="mt-12 flex flex-col items-center justify-center gap-3 sm:flex-row">
            <a href="<?php echo e(route('register')); ?>" class="tp-landing-btn tp-landing-btn-primary">
                Jisajili kama Mteja
            </a>
            <a href="<?php echo e($activeVersion ? route('app.download') : '#'); ?>"
                class="tp-landing-btn tp-landing-btn-secondary <?php echo e($activeVersion ? '' : 'pointer-events-none opacity-50'); ?>">
                📱 Pakua App<?php echo e($activeVersion ? ' (v'.$activeVersion->version.')' : ''); ?>

            </a>
        </div>
    </div>
</section>


<section class="tp-landing-section bg-slate-50" id="usafi">
    <div class="tp-landing-container">
        <div class="tp-landing-section-head">
            <p class="tp-landing-kicker">Watu halisi, kazi halisi</p>
            <h2 class="tp-landing-section-title">Huduma za usafi zinazoonekana</h2>
            <p class="tp-landing-section-sub">
                Kutoka nyumba hadi ofisi — wafanyakazi wetu wanafanya kazi kwa uangalifu, uaminifu, na matokeo unayoyaona.
            </p>
        </div>
        <div class="tp-landing-gallery mt-12">
            <?php $__currentLoopData = $imgGallery; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <figure class="tp-landing-gallery-item">
                    <img src="<?php echo e($item['src']); ?>" alt="<?php echo e($item['title']); ?>" loading="lazy" width="800" height="600">
                    <figcaption class="tp-landing-gallery-cap">
                        <h3><?php echo e($item['title']); ?></h3>
                        <p><?php echo e($item['caption']); ?></p>
                    </figcaption>
                </figure>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>

<section class="tp-landing-section bg-white" id="jinsi">
    <div class="tp-landing-container">
        <div class="tp-landing-section-head">
            <p class="tp-landing-kicker">Hatua 3 tu</p>
            <h2 class="tp-landing-section-title">Jinsi TendaPoa inavyofanya</h2>
        </div>
        <div class="tp-landing-steps">
            <article class="tp-landing-step">
                <div class="tp-landing-step-num">1</div>
                <h3 class="mt-4 text-lg font-bold">Chapisha mahitaji yako</h3>
                <p class="mt-2 text-[14px] text-slate-600">Eleza kazi ya usafi au huduma unayohitaji, weka bei, na eneo lako.</p>
            </article>
            <article class="tp-landing-step">
                <div class="tp-landing-step-num">2</div>
                <h3 class="mt-4 text-lg font-bold">Chagua mfanyakazi</h3>
                <p class="mt-2 text-[14px] text-slate-600">Linganisha maombi, angalia profaili, na chagua mtoa huduma unaempenda.</p>
            </article>
            <article class="tp-landing-step">
                <div class="tp-landing-step-num">3</div>
                <h3 class="mt-4 text-lg font-bold">Lipa salama & thibitisha</h3>
                <p class="mt-2 text-[14px] text-slate-600">Malipo ya escrow yanamlinda mfanyakazi na wewe hadi kazi ikamilike.</p>
            </article>
        </div>
    </div>
</section>

<?php if($cats->count()): ?>
<section class="tp-landing-section bg-white" id="aina">
    <div class="tp-landing-container">
        <div class="tp-landing-section-head">
            <p class="tp-landing-kicker">Aina za huduma</p>
            <h2 class="tp-landing-section-title">Chagua kategoria unayohitaji</h2>
        </div>
        <div class="tp-landing-cat-grid">
            <?php $__currentLoopData = $cats->take(8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('register')); ?>" class="tp-landing-cat">
                    <span class="tp-landing-cat-emoji"><?php echo e($categoryEmoji($cat->name)); ?></span>
                    <span class="mt-3 text-[13px] font-bold text-slate-800"><?php echo e($cat->name); ?></span>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="tp-landing-section bg-slate-50">
    <div class="tp-landing-container">
        <div class="tp-landing-stats">
            <h2 class="text-center text-2xl font-extrabold sm:text-3xl">Watu wanaamini TendaPoa</h2>
            <div class="tp-landing-stats-grid">
                <div class="text-center">
                    <p class="tp-landing-stat-val" data-count="500">500+</p>
                    <p class="tp-landing-stat-lbl">Kazi zilizokamilika</p>
                </div>
                <div class="text-center">
                    <p class="tp-landing-stat-val" data-count="200">200+</p>
                    <p class="tp-landing-stat-lbl">Wateja kila mwaka</p>
                </div>
                <div class="text-center">
                    <p class="tp-landing-stat-val" data-count="5">5+</p>
                    <p class="tp-landing-stat-lbl">Miaka ya uzoefu</p>
                </div>
                <div class="text-center">
                    <p class="tp-landing-stat-val">100%</p>
                    <p class="tp-landing-stat-lbl">Escrow salama</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="tp-landing-section bg-white">
    <div class="tp-landing-container">
        <div class="tp-landing-dual-cta">
            <div class="tp-landing-cta-card tp-landing-cta-client">
                <p class="text-[11px] font-bold uppercase tracking-widest text-brand-200">Mteja</p>
                <h3 class="mt-2 text-2xl font-extrabold">Unahitaji usafi au huduma?</h3>
                <p class="mt-2 text-[14px] text-brand-50">Jisajili na uchapishe kazi yako leo.</p>
                <a href="<?php echo e(route('register')); ?>" class="tp-landing-btn mt-6 bg-white text-brand-800 hover:bg-slate-100">Anza kama Mteja</a>
            </div>
            <div class="tp-landing-cta-card tp-landing-cta-worker">
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-500">Mfanyakazi</p>
                <h3 class="mt-2 text-2xl font-extrabold">Unafanya usafi au huduma?</h3>
                <p class="mt-2 text-[14px] text-slate-600">Pata kazi karibu nawe na ujipatie kipato.</p>
                <a href="<?php echo e(route('register')); ?>" class="tp-landing-btn tp-landing-btn-brand mt-6">Anza kama Mfanyakazi</a>
            </div>
        </div>
    </div>
</section>

<footer class="tp-landing-footer">
    <div class="tp-landing-container py-12">
        <div class="grid gap-8 md:grid-cols-3">
            <div>
                <?php if (isset($component)) { $__componentOriginal8741a05e11b0c77d19ec61b6b35b26b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8741a05e11b0c77d19ec61b6b35b26b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.brand-logo','data' => ['href' => ''.e(route('home')).'','class' => 'tp-landing-logo tp-landing-logo--light','textClass' => 'text-inherit','size' => 'md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('brand-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('home')).'','class' => 'tp-landing-logo tp-landing-logo--light','text-class' => 'text-inherit','size' => 'md']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8741a05e11b0c77d19ec61b6b35b26b3)): ?>
<?php $attributes = $__attributesOriginal8741a05e11b0c77d19ec61b6b35b26b3; ?>
<?php unset($__attributesOriginal8741a05e11b0c77d19ec61b6b35b26b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8741a05e11b0c77d19ec61b6b35b26b3)): ?>
<?php $component = $__componentOriginal8741a05e11b0c77d19ec61b6b35b26b3; ?>
<?php unset($__componentOriginal8741a05e11b0c77d19ec61b6b35b26b3); ?>
<?php endif; ?>
                <p class="mt-4 text-[14px] leading-relaxed">Jukwaa la usafi na huduma — salama, rahisi, na la kuaminika.</p>
                <p class="mt-4 text-[12px]">© <?php echo e(date('Y')); ?> TendaPoa</p>
            </div>
            <div>
                <h4 class="text-sm font-bold text-slate-300">Viungo</h4>
                <nav class="mt-3 flex flex-col gap-2 text-[14px]">
                    <a href="#app" class="text-slate-400 no-underline hover:text-white">App</a>
                    <a href="#usafi" class="text-slate-400 no-underline hover:text-white">Huduma za usafi</a>
                    <a href="<?php echo e(route('policy.privacy')); ?>" class="text-slate-400 no-underline hover:text-white">Faragha</a>
                    <a href="<?php echo e(route('policy.terms')); ?>" class="text-slate-400 no-underline hover:text-white">Masharti</a>
                </nav>
            </div>
            <div>
                <a href="<?php echo e(route('register')); ?>" class="tp-landing-btn tp-landing-btn-primary">Jisajili bure</a>
            </div>
        </div>
    </div>
</footer>

<a href="https://wa.me/255626957138" target="_blank" rel="noopener noreferrer" class="tp-landing-wa" aria-label="WhatsApp">
    <svg width="28" height="28" viewBox="0 0 32 32" fill="currentColor"><path d="M16 0C7.164 0 0 7.164 0 16c0 2.824.744 5.476 2.044 7.772L0 32l8.52-1.984C10.716 31.256 13.276 32 16 32c8.836 0 16-7.164 16-16S24.836 0 16 0z"/></svg>
</a>

<script>
(function () {
    const nav = document.getElementById('landingNav');
    const menuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');

    window.addEventListener('scroll', () => {
        nav?.classList.toggle('is-scrolled', window.scrollY > 8);
    }, { passive: true });

    menuBtn?.addEventListener('click', () => mobileMenu?.classList.toggle('is-open'));

    document.querySelectorAll('a[href^="#"]').forEach((a) => {
        a.addEventListener('click', (e) => {
            const id = a.getAttribute('href');
            if (!id || id === '#') return;
            const el = document.querySelector(id);
            if (!el) return;
            e.preventDefault();
            mobileMenu?.classList.remove('is-open');
            el.scrollIntoView({ behavior: 'smooth' });
        });
    });

    const counters = document.querySelectorAll('[data-count]');
    const obs = new IntersectionObserver((entries) => {
        if (!entries.some((e) => e.isIntersecting)) return;
        counters.forEach((el) => {
            const target = parseInt(el.dataset.count || '0', 10);
            if (!target) return;
            let n = 0;
            const step = Math.max(1, Math.floor(target / 45));
            const tick = () => {
                n += step;
                if (n >= target) { el.textContent = target + '+'; return; }
                el.textContent = n + '+';
                requestAnimationFrame(tick);
            };
            tick();
        });
        obs.disconnect();
    }, { threshold: 0.3 });
    const stats = document.querySelector('.tp-landing-stats');
    if (stats) obs.observe(stats);
})();
</script>
</body>
</html>
<?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/home.blade.php ENDPATH**/ ?>