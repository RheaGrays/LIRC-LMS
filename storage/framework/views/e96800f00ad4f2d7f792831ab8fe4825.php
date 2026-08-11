<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <link rel="icon" type="image/png" href="/CorJesu Logo.png">
        <link rel="apple-touch-icon" href="/CorJesu Logo.png">
        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#0f2744">



        <!-- Scripts & Styles -->
        <style>[x-cloak] { display: none !important; }</style>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
        
        <?php echo $__env->yieldPushContent('styles'); ?>
    </head>
    <body class="font-sans antialiased bg-[#fcf9f2] text-[var(--cjc-navy)]">
        <?php echo $__env->yieldContent('content'); ?>

        <?php echo $__env->yieldPushContent('scripts'); ?>
    </body>
</html>
<?php /**PATH C:\Users\Alfie Lynard\OneDrive\Desktop\archive\LEMS\resources\views/layouts/app.blade.php ENDPATH**/ ?>