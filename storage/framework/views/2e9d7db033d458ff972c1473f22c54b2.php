
            <!-- Header -->
            <header class="px-10 py-5 bg-white/50 border-b border-[var(--border-warm)] flex items-center justify-between shadow-sm backdrop-blur-md">
                <div class="flex items-center gap-4">
                    <button @click.stop="deactivate()" class="flex items-center gap-1.5 px-4 py-2 bg-white border border-[var(--border-light)] rounded-xl text-[13px] font-medium text-[var(--text-muted)] font-['Inter'] hover:border-[var(--cjc-navy)] hover:text-[var(--cjc-navy)] transition-colors shadow-sm">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                            <path d="M11 7H3M7 3L3 7l4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Back
                    </button>

                    <div class="w-px h-8 bg-[var(--border-light)] mx-2"></div>

                    <div class="w-10 h-10 rounded-full overflow-hidden border border-[var(--border-light)] bg-white shrink-0">
                        <img src="/CorJesu Logo.png" alt="CJC" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <p class="m-0 text-[13px] font-bold tracking-[0.06em] uppercase text-[var(--cjc-navy)] font-['Inter'] leading-[1.2]">
                            Cor Jesu College
                        </p>
                        <p class="m-0 text-[11px] text-[var(--text-muted)] font-['Inter']">
                            Library Entrance Monitoring System
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <?php if (isset($component)) { $__componentOriginal4ceef77e7dba9febcf3fa9be044731ca = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4ceef77e7dba9febcf3fa9be044731ca = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.kiosk.offline-sync-status','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('kiosk.offline-sync-status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4ceef77e7dba9febcf3fa9be044731ca)): ?>
<?php $attributes = $__attributesOriginal4ceef77e7dba9febcf3fa9be044731ca; ?>
<?php unset($__attributesOriginal4ceef77e7dba9febcf3fa9be044731ca); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4ceef77e7dba9febcf3fa9be044731ca)): ?>
<?php $component = $__componentOriginal4ceef77e7dba9febcf3fa9be044731ca; ?>
<?php unset($__componentOriginal4ceef77e7dba9febcf3fa9be044731ca); ?>
<?php endif; ?>
                    
                    <span class="font-['JetBrains_Mono'] text-[16px] font-semibold text-[var(--cjc-navy)] tracking-[0.04em] bg-white px-4 py-1.5 rounded-xl border border-[var(--border-light)] shadow-sm" x-text="clockHm">
                        --:--
                    </span>
                </div>
            </header
<?php /**PATH C:\Users\Alfie Lynard\OneDrive\Desktop\archive\LEMS\resources\views/components/kiosk/scanner-header.blade.php ENDPATH**/ ?>