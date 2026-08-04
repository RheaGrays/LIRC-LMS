export const QueueManager = {
    key: 'lems_offline_queue',
    syncing: false,

    getQueue() {
        try {
            return JSON.parse(localStorage.getItem(this.key) || '[]');
        } catch (e) {
            return [];
        }
    },

    saveQueue(queue) {
        localStorage.setItem(this.key, JSON.stringify(queue));
        window.dispatchEvent(new CustomEvent('queue-updated', { detail: { count: queue.length } }));
    },

    async enqueue(studentId) {
        const queue = this.getQueue();
        queue.push({
            id: crypto.randomUUID(),
            student_id: studentId,
            timestamp: new Date().toISOString()
        });
        this.saveQueue(queue);
    },

    startSyncTimer() {
        // Attempt sync every 10 seconds if online
        setInterval(() => {
            if (navigator.onLine) {
                this.sync();
            }
        }, 10000);
        
        // Also try immediately when coming back online
        window.addEventListener('online', () => this.sync());
    },

    async sync() {
        if (this.syncing || !navigator.onLine) return;
        
        const queue = this.getQueue();
        if (queue.length === 0) return;

        this.syncing = true;
        let successfulIds = [];
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        // Process sequentially to maintain order and logic (checkin vs checkout)
        for (const item of queue) {
            try {
                // 1. Determine action based on current DB state
                const lastRes = await fetch('/kiosk/last', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ student_id: item.student_id })
                });
                
                if (!lastRes.ok) continue; // Skip on error, keep in queue
                
                const lastData = await lastRes.json();
                const nextAction = lastData.action === 'check_in' ? 'check_out' : 'check_in';
                
                // 2. Log it
                const logRes = await fetch('/kiosk/log', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ 
                        student_id: item.student_id, 
                        action: nextAction,
                        // Note: we're using current time on server, but ideally we'd pass item.timestamp
                        // to the backend to log the exact offline time. For simplicity in porting, 
                        // we'll just let the backend use now(), or we can pass it if we update the backend.
                    })
                });

                if (logRes.ok) {
                    successfulIds.push(item.id);
                }
            } catch (err) {
                console.error("Sync error for item:", item, err);
                break; // Stop syncing on network error, try again later
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
