import { BrowserMultiFormatReader } from '@zxing/browser';
import { QueueManager } from '../kiosk/offline-queue';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('mobileScannerApp', () => ({
        isCameraActive: false,
        isScanning: false,
        isProcessing: false,
        result: null,
        isOnline: navigator.onLine,
        codeReader: null,
        audioSuccess: null,
        showManualEntry: false,
        manualId: '',
        
        init() {
            this.audioSuccess = new Audio('/beep.mp3');
            
            window.addEventListener('online', () => {
                this.isOnline = true;
                QueueManager.syncOfflineScans();
            });
            window.addEventListener('offline', () => this.isOnline = false);

            this.codeReader = new BrowserMultiFormatReader(null, 350);
            
            QueueManager.startSyncTimer();
            
            // Auto start
            this.startCamera();
        },

        async startCamera() {
            const videoEl = document.getElementById('mobile-scanner-video');
            if (!videoEl) return;

            try {
                // Force environment (rear) camera
                const constraints = {
                    video: {
                        facingMode: "environment",
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                        frameRate: { ideal: 30 }
                    }
                };

                this.isCameraActive = true;
                this.isScanning = true;

                await this.codeReader.decodeFromConstraints(constraints, videoEl, (result, err) => {
                    if (result && !this.isProcessing && this.isScanning) {
                        this.processId(result.text);
                    }
                });
            } catch (err) {
                console.error("Camera start error:", err);
                this.isCameraActive = false;
                this.isScanning = false;
                alert("Could not start the rear camera. Please ensure camera permissions are granted.");
            }
        },

        async processId(id) {
            if (this.isProcessing) return;
            
            this.isScanning = false;
            this.isProcessing = true;
            this.result = null;

            // Vibrate phone if supported
            if (navigator.vibrate) navigator.vibrate(200);

            try {
                if (!navigator.onLine) {
                    await QueueManager.enqueue(id);
                    this.result = { status: 'offline', message: 'Saved offline (No Internet).', student: { full_name: 'Unknown (Offline)', id: id } };
                    this.audioSuccess.play().catch(e => {});
                } else {
                    try {
                        const controller = new AbortController();
                        const timeoutId = setTimeout(() => controller.abort(), 10000);
                        
                        const res = await fetch('/kiosk/process', {
                            method: 'POST',
                            headers: { 
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' 
                            },
                            body: JSON.stringify({ student_id: id }),
                            signal: controller.signal
                        });
                        clearTimeout(timeoutId);
                        
                        const processData = await res.json();
                        this.result = processData;
                        
                        if(processData.status === 'success') {
                            this.audioSuccess.play().catch(e => {});
                        }
                    } catch (networkErr) {
                        await QueueManager.enqueue(id);
                        this.result = { status: 'offline', message: 'Saved offline (Connection Dropped).', student: { full_name: 'Unknown (Offline)', id: id } };
                        this.audioSuccess.play().catch(e => {});
                    }
                }
            } catch (err) {
                this.result = { status: 'error', message: 'A system error occurred.' };
            } finally {
                this.isProcessing = false;
                
                // Auto-hide result after 3 seconds if it was successful or offline to keep scanning fast
                if (this.result?.status === 'success' || this.result?.status === 'offline') {
                    setTimeout(() => {
                        if (this.result) this.resetScanner();
                    }, 3000);
                }
            }
        },

        resetScanner() {
            this.result = null;
            this.isScanning = true;
        },

        submitManualEntry() {
            const id = this.manualId.trim();
            if (!id) return;
            this.showManualEntry = false;
            this.manualId = '';
            this.processId(id);
        }
    }));
});
