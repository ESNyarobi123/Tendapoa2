<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'size' => 'md',
    'showText' => true,
    'href' => null,
    'class' => '',
    'textClass' => '',
    'imgClass' => '',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'size' => 'md',
    'showText' => true,
    'href' => null,
    'class' => '',
    'textClass' => '',
    'imgClass' => '',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $logoUrl = asset('images/brand/tendapoa-logo.jpg');

    $imgSizes = [
        'xs' => 'h-7 w-7',
        'sm' => 'h-8 w-8',
        'md' => 'h-9 w-9',
        'lg' => 'h-11 w-11',
        'xl' => 'h-14 w-14',
        'hero' => 'h-16 w-16 sm:h-20 sm:w-20',
    ];
    $imgSize = $imgSizes[$size] ?? $imgSizes['md'];
    $link = $href ?? route('home');
?>

<a href="<?php echo e($link); ?>" <?php echo e($attributes->merge(['class' => 'inline-flex items-center gap-2.5 no-underline '.$class])); ?>>
    <img
        src="<?php echo e($logoUrl); ?>"
        alt="TendaPoa"
        class="<?php echo e($imgSize); ?> shrink-0 rounded-xl object-cover shadow-sm ring-1 ring-black/10 <?php echo e($imgClass); ?>"
        width="64"
        height="64"
        loading="eager"
    >
    <?php if($showText): ?>
        <span class="font-extrabold tracking-tight <?php echo e($textClass); ?>">TendaPoa</span>
    <?php endif; ?>
</a>
<?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/components/brand-logo.blade.php ENDPATH**/ ?>