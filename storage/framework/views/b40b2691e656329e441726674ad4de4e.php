<?php $__env->startSection('title', ' | Kiosk'); ?>

<?php $__env->startSection('kiosk_content'); ?>
<div x-data="kioskApp()" class="w-full h-screen flex flex-col relative z-10 bg-transparent cursor-pointer select-none overflow-hidden" 
     @mousemove.window="handleActivity()" @touchstart.window="handleActivity()" @keydown.window="handleKey($event)" @click="activate()">

    
    <?php if (isset($component)) { $__componentOriginal3fca2292eba1ad95b7c0e34e61bc95d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3fca2292eba1ad95b7c0e34e61bc95d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.kiosk.splash-screen','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('kiosk.splash-screen'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3fca2292eba1ad95b7c0e34e61bc95d0)): ?>
<?php $attributes = $__attributesOriginal3fca2292eba1ad95b7c0e34e61bc95d0; ?>
<?php unset($__attributesOriginal3fca2292eba1ad95b7c0e34e61bc95d0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3fca2292eba1ad95b7c0e34e61bc95d0)): ?>
<?php $component = $__componentOriginal3fca2292eba1ad95b7c0e34e61bc95d0; ?>
<?php unset($__componentOriginal3fca2292eba1ad95b7c0e34e61bc95d0); ?>
<?php endif; ?>

    <!-- Transparent Background to allow hero-pattern to show through -->
    <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none bg-transparent"></div>
    
    <!-- IDLE STATE -->
    <template x-if="state === 'idle'">
        <?php if (isset($component)) { $__componentOriginal511e97206b323a7f9349153b5871b4e0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511e97206b323a7f9349153b5871b4e0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.kiosk.idle-state','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('kiosk.idle-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511e97206b323a7f9349153b5871b4e0)): ?>
<?php $attributes = $__attributesOriginal511e97206b323a7f9349153b5871b4e0; ?>
<?php unset($__attributesOriginal511e97206b323a7f9349153b5871b4e0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511e97206b323a7f9349153b5871b4e0)): ?>
<?php $component = $__componentOriginal511e97206b323a7f9349153b5871b4e0; ?>
<?php unset($__componentOriginal511e97206b323a7f9349153b5871b4e0); ?>
<?php endif; ?>
    </template>

    <!-- SCANNER STATE -->
    <template x-if="state === 'active'">
        <div class="flex-1 flex flex-col cursor-default relative z-20 bg-transparent" @click.stop>
            
            <?php if (isset($component)) { $__componentOriginal9d756e8cb55b7fd1b872ebadb309931f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9d756e8cb55b7fd1b872ebadb309931f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.kiosk.scanner-header','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('kiosk.scanner-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9d756e8cb55b7fd1b872ebadb309931f)): ?>
<?php $attributes = $__attributesOriginal9d756e8cb55b7fd1b872ebadb309931f; ?>
<?php unset($__attributesOriginal9d756e8cb55b7fd1b872ebadb309931f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9d756e8cb55b7fd1b872ebadb309931f)): ?>
<?php $component = $__componentOriginal9d756e8cb55b7fd1b872ebadb309931f; ?>
<?php unset($__componentOriginal9d756e8cb55b7fd1b872ebadb309931f); ?>
<?php endif; ?>

            <!-- Main Scanner Box -->
            <main class="flex-1 flex items-center justify-center p-6 pb-16">
                <div class="flex flex-col items-center w-full max-w-[900px] transition-all duration-300">
                    <div class="bg-white border border-gray-100 rounded-[28px] shadow-[0_20px_50px_rgba(0,0,0,0.06)] w-full relative overflow-hidden flex flex-col min-h-[480px]">
                        
                        <div class="px-10 pt-10 pb-20 relative z-10" x-show="!result && !isProcessing">
                            
                            <?php if (isset($component)) { $__componentOriginale110357f7ebfc1b37e34c36cf2f76a7d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale110357f7ebfc1b37e34c36cf2f76a7d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.kiosk.scanner-tabs','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('kiosk.scanner-tabs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale110357f7ebfc1b37e34c36cf2f76a7d)): ?>
<?php $attributes = $__attributesOriginale110357f7ebfc1b37e34c36cf2f76a7d; ?>
<?php unset($__attributesOriginale110357f7ebfc1b37e34c36cf2f76a7d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale110357f7ebfc1b37e34c36cf2f76a7d)): ?>
<?php $component = $__componentOriginale110357f7ebfc1b37e34c36cf2f76a7d; ?>
<?php unset($__componentOriginale110357f7ebfc1b37e34c36cf2f76a7d); ?>
<?php endif; ?>
                        </div>

                        
                        <?php if (isset($component)) { $__componentOriginal8b11921d5dd4cb10aceeeb204c1c0e9a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8b11921d5dd4cb10aceeeb204c1c0e9a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.kiosk.scanner-waves','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('kiosk.scanner-waves'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8b11921d5dd4cb10aceeeb204c1c0e9a)): ?>
<?php $attributes = $__attributesOriginal8b11921d5dd4cb10aceeeb204c1c0e9a; ?>
<?php unset($__attributesOriginal8b11921d5dd4cb10aceeeb204c1c0e9a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8b11921d5dd4cb10aceeeb204c1c0e9a)): ?>
<?php $component = $__componentOriginal8b11921d5dd4cb10aceeeb204c1c0e9a; ?>
<?php unset($__componentOriginal8b11921d5dd4cb10aceeeb204c1c0e9a); ?>
<?php endif; ?>
                            
                            <!-- Processing State -->
                            <div x-show="isProcessing" class="flex flex-col items-center justify-center p-12 gap-5 fade-in-up">
                                <div class="w-12 h-12 border-4 border-[var(--bg-cream-2)] border-t-[var(--cjc-red)] rounded-full animate-spin"></div>
                                <span class="text-[16px] font-bold tracking-wide text-[var(--cjc-navy)] font-['Inter'] animate-pulse">Processing ID...</span>
                            </div>

                            <!-- Result Overlay -->
                            <div x-show="result && !isProcessing" class="fade-in-up animate-slide-in pb-4">
                                <?php if (isset($component)) { $__componentOriginal5efd276f0b00efd3bb8181ed3ef01bc7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5efd276f0b00efd3bb8181ed3ef01bc7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.kiosk.status-card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('kiosk.status-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5efd276f0b00efd3bb8181ed3ef01bc7)): ?>
<?php $attributes = $__attributesOriginal5efd276f0b00efd3bb8181ed3ef01bc7; ?>
<?php unset($__attributesOriginal5efd276f0b00efd3bb8181ed3ef01bc7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5efd276f0b00efd3bb8181ed3ef01bc7)): ?>
<?php $component = $__componentOriginal5efd276f0b00efd3bb8181ed3ef01bc7; ?>
<?php unset($__componentOriginal5efd276f0b00efd3bb8181ed3ef01bc7); ?>
<?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </template>
    
    <style>
        @keyframes scanline {
            0% { top: 10%; }
            50% { top: 85%; }
            100% { top: 10%; }
        }
        .animate-bounce-slow {
            animation: bounce 3s infinite;
        }
    </style>

    <script>
        window.kioskLastLogId = <?php echo e(\App\Models\AttendanceLog::max('id') ?? 0); ?>;

        function kioskSlideshow() {
            return {
                images: <?php echo json_encode($slideshowImages, 15, 512) ?>,
                currentIndex: 0,
                timer: null,
                init() {
                    // Preload images
                    this.images.forEach(item => {
                        const img = new Image();
                        img.src = item.src;
                    });
                    
                    this.startTimer();
                },
                startTimer() {
                    this.stopTimer();
                    if (this.images.length > 1) {
                        this.timer = setInterval(() => {
                            this.next();
                        }, 5000);
                    }
                },
                stopTimer() {
                    if (this.timer) clearInterval(this.timer);
                },
                next() {
                    this.currentIndex = (this.currentIndex + 1) % this.images.length;
                    this.startTimer();
                },
                prev() {
                    this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
                    this.startTimer();
                },
                goTo(index) {
                    this.currentIndex = index;
                    this.startTimer();
                }
            }
        }
    </script>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.kiosk', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Alfie Lynard\OneDrive\Desktop\archive\LEMS\resources\views/kiosk/index.blade.php ENDPATH**/ ?>