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
        let discardedIds = [];
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
                } else if (logRes.status >= 400 && logRes.status < 500 && logRes.status !== 429 && logRes.status !== 419) {
                    // REL-03 FIX: Permanent client/data validation errors (400 Bad Request, 404 Not Found, 422 Unprocessable Entity).
                    // The student ID does not exist, is invalid, or failed validation.
                    // Discard this item so it does NOT permanently block all valid offline scans queued behind it!
                    const errorText = await logRes.text();
                    console.warn(`[LEMS QueueManager] Discarding unprocessable queued scan ${item.id} (Student ID: "${item.student_id}") due to HTTP ${logRes.status}:`, errorText);
                    discardedIds.push(item.id);
                } else {
                    // Temporary errors (5xx server error, 429 rate limit, 419 CSRF timeout):
                    // Stop syncing to preserve order and retry on the next interval when server recovers.
                    const errorText = await logRes.text();
                    console.warn(`[LEMS QueueManager] Temporary server error HTTP ${logRes.status} for item ${item.id} — pausing sync:`, errorText);
                    break;
                }
            } catch (err) {
                console.error("[LEMS QueueManager] Network error during sync for item:", item, err);
                window.dispatchEvent(new CustomEvent('queue-sync-error', { detail: { error: err.message } }));
                break; // Stop syncing on network error, retry on next timer
            }
        }

        // Remove successful and discarded items from queue
        const idsToRemove = [...successfulIds, ...discardedIds];
        if (idsToRemove.length > 0) {
            const currentQueue = this.getQueue();
            const newQueue = currentQueue.filter(item => !idsToRemove.includes(item.id));
            this.saveQueue(newQueue);
        }

        this.syncing = false;
    }
};
