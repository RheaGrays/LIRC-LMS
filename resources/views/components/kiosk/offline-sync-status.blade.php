<div x-data="offlineSync()" 
     x-show="!isOnline || queueCount > 0" 
     class="flex items-center gap-3 bg-white/80 backdrop-blur-md px-4 py-2 rounded-full border shadow-sm transition-colors duration-300"
     :class="isOnline ? 'border-orange-300' : 'border-red-300'">
     
    <template x-if="!isOnline">
        <div class="flex items-center gap-2 text-red-600 font-semibold text-sm">
            <svg class="w-4 h-4 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414" />
            </svg>
            Offline Mode
        </div>
    </template>
    
    <template x-if="isOnline && queueCount > 0">
        <div class="flex items-center gap-2 text-orange-600 font-semibold text-sm">
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Syncing <span x-text="queueCount"></span> items...
        </div>
    </template>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('offlineSync', () => ({
        isOnline: navigator.onLine,
        queueCount: 0,
        
        init() {
            window.addEventListener('online', () => this.isOnline = true);
            window.addEventListener('offline', () => this.isOnline = false);
            
            // Listen for custom events from the offline queue JS
            window.addEventListener('queue-updated', (e) => {
                this.queueCount = e.detail.count;
            });
            
            // Initial check
            this.updateQueueCount();
            
            // Re-sync logic will be handled by resources/js/kiosk/offline-queue.js
        },
        
        updateQueueCount() {
            const queue = JSON.parse(localStorage.getItem('lems_offline_queue') || '[]');
            this.queueCount = queue.length;
        }
    }));
});
</script>
@endpush
