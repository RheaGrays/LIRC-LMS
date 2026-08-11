<?php $__env->startSection('content'); ?>
<div class="min-h-screen flex flex-col relative overflow-hidden" style="background-color: #fefcf8;">
    <!-- Guaranteed Background Image Layer (Matches opacity and style of previous CSS) -->
    <div class="absolute inset-0 z-0 pointer-events-none">
        <img src="/bg.jpg" alt="Campus Background" class="w-full h-full object-cover" style="opacity: 0.35; mix-blend-mode: multiply;">
    </div>
    
    <!-- Main Content -->
    <main class="flex-1 relative z-10 flex flex-col min-h-screen">
        <?php echo $__env->yieldContent('kiosk_content'); ?>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    console.log('SW registered: ', registration);
                })
                .catch(registrationError => {
                    console.log('SW registration failed: ', registrationError);
                });
        });
    }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Alfie Lynard\OneDrive\Desktop\archive\LEMS\resources\views/layouts/kiosk.blade.php ENDPATH**/ ?>