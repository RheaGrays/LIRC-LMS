export const QueueManager = {
    key: 'lems_offline_queue',
    syncing: false,

    getQueue() {
        try {
            return JSON.parse(localStorage.getItem(this.key) || '[]');
        } catch (e) {
            console.error('[LEMS QueueManager] Failed to read queue from localStorage:', e);
            return [];
        }
    },

    saveQueue(queue) {
        try {
            localStorage.setItem(this.key, JSON.stringify(queue));
            window.dispatchEvent(new CustomEvent('queue-updated', { detail: { count: queue.length } }));
        } catch (e) {
            console.error('[LEMS QueueManager] Failed to save queue to localStorage:', e);
        }
    },

    // BUG-06 FIX: enqueue now accepts the action so check_out scans are replayed correctly.
    // Defaults to 'check_in' for backward compatibility with any existing callers.
    async enqueue(studentId, action = 'check_in') {
        try {
            const queue = this.getQueue();
            queue.push({
                id: crypto.randomUUID(),
                student_id: studentId,
                action: action,
                timestamp: new Date().toISOString()
            });
            this.saveQueue(queue);
        } catch (e) {
            console.error('[LEMS QueueManager] Failed to enqueue student ID:', studentId, e);
        }
    },

    startSyncTimer() {
        // Attempt sync every 10 seconds if online
        setInterval(() => {
            this.sync().catch(err => console.warn('[LEMS QueueManager] Sync interval error:', err));
        }, 10000);
        
        // Also try immediately when coming back online
        window.addEventListener('online', () => {
            this.sync().catch(err => console.warn('[LEMS QueueManager] Online event sync error:', err));
        });
    },

    async sync() {
        if (this.syncing) return;
        
        const queue = this.getQueue();
        if (queue.length === 0) return;

        this.syncing = true;
        let successfulIds = [];
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        // Process sequentially to maintain order and logic
        for (const item of queue) {
            try {
                // BUG-06 FIX: Use the stored action instead of always forcing 'check_in'.
                // Previously, any check_out queued offline would be replayed as check_in.
                const nextAction = item.action || 'check_in';
                
                const logRes = await fetch('/kiosk/log', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '' 
                    },
                    body: JSON.stringify({ 
                        student_id: item.student_id, 
                        action: nextAction,
                    })
                });

                if (logRes.ok) {
                    successfulIds.push(item.id);
                } else {
                    const errorText = await logRes.text();
                    console.warn(`[LEMS QueueManager] Kiosk sync HTTP ${logRes.status} for item ${item.id}:`, errorText);
                    // Non-ok response (e.g. 422 or 500) -> halt sync loop to avoid discarding failed items
                    break;
                }
            } catch (err) {
                console.error("[LEMS QueueManager] Network error during sync for item:", item, err);
                window.dispatchEvent(new CustomEvent('queue-sync-error', { detail: { error: err.message } }));
                break; // Stop syncing on network error, retry on next timer
            }
        }

        // Remove successful items from queue
        if (successfulIds.length > 0) {
            const currentQueue = this.getQueue();
            const newQueue = currentQueue.filter(item => !successfulIds.includes(item.id));
            this.saveQueue(newQueue);
        }

        this.syncing = false;
    }
};
