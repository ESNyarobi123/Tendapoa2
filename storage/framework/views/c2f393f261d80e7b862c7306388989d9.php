
<?php
    $manifestPath = public_path('build/manifest.json');
    $hasBuild = is_readable($manifestPath);
    $cssFile = null;
    $jsFile = null;
    if ($hasBuild) {
        $manifest = json_decode(file_get_contents($manifestPath), true);
        $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
        $jsFile = $manifest['resources/js/app.js']['file'] ?? null;
        $hasBuild = $cssFile !== null;
    }
?>
<?php if($hasBuild && $cssFile): ?>
    <link rel="stylesheet" href="<?php echo e(asset('build/'.$cssFile)); ?>">
    <?php if($jsFile): ?>
        <script type="module" src="<?php echo e(asset('build/'.$jsFile)); ?>"></script>
    <?php endif; ?>
<?php else: ?>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0', 300: '#6ee7b7',
                            400: '#34d399', 500: '#10b981', 600: '#059669', 700: '#047857',
                            800: '#065f46', 900: '#064e3b'
                        }
                    },
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] }
                }
            }
        };
    </script>
<?php endif; ?>
<?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/components/auth-head-assets.blade.php ENDPATH**/ ?>