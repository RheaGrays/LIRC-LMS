import { BrowserMultiFormatReader } from '@zxing/browser';
import { QueueManager } from './offline-queue';

const registerApp = () => {
    window.Alpine.data('kioskApp', () => ({
        state: 'idle', // 'idle' | 'active'
        tab: 'scan',
        manualId: '',
        suggestions: [],
        showSuggestions: false,
        searchDebounce: null,
        isProcessing: false,
        result: null,
        resetTimeout: null,
        inactivityTimeout: null,
        occupancy: { inside: 0, max: 200 },
        
        // Clock
        clockHm: '',
        clockSec: '',
        clockDate: '',
        clockInterval: null,

        // Scanner
        codeReader: null,
        cameras: [],
        selectedCamera: '',
        isCameraActive: false,

        // Slideshow
        slides: window._kioskCollections || [],
        currentSlide: 0,
        slideTimer: null,

        // Splash Screen State
        showSplash: new URLSearchParams(window.location.search).has('boot') && !sessionStorage.getItem('splashShown'),
        splashProgress: 0,
        splashStatus: 'Initializing System Hardware...',
        
        // Real-Time Link
        lastLogId: window.kioskLastLogId || 0,
        pollingInterval: null,

        init() {
            // Set 350ms decoding interval (3 scans/sec) to eliminate CPU lag and keep video feed 60fps smooth
            this.codeReader = new BrowserMultiFormatReader(null, 350);
            this.runSplashSequence();
            this.startClock();
            this.fetchOccupancy();
            setInterval(() => this.fetchOccupancy(), 30000);
            QueueManager.startSyncTimer();
            this.startSlideshow();
            this.startRealtimePolling();

            // CSRF Token Keepalive (Refreshes token every 4 mins to prevent 419 Page Expired)
            setInterval(async () => {
                try {
                    const tokenRes = await fetch('/csrf-token');
                    if (tokenRes.ok) {
                        const tokenData = await tokenRes.json();
                        if (tokenData.token) document.querySelector('meta[name="csrf-token"]').content = tokenData.token;
                    }
                } catch (e) {}
            }, 240000);
        },

        runSplashSequence() {
            if (!this.showSplash) {
                return;
            }

            sessionStorage.setItem('splashShown', 'true');
            this.showSplash = true;
            this.splashProgress = 0;
            let p = 0;
            const timer = setInterval(() => {
                p += 1;
                this.splashProgress = Math.min(p, 100);

                if (p < 30) {
                    this.splashStatus = 'Initializing Hardware Scanners...';
                } else if (p < 65) {
                    this.splashStatus = 'Connecting to Library Cloud Database...';
                } else if (p < 90) {
                    this.splashStatus = 'Loading Kiosk Interface...';
                } else {
                    this.splashStatus = 'Welcome to CJC Library!';
                }

                if (p >= 100) {
                    clearInterval(timer);
                    setTimeout(() => {
                        this.showSplash = false;
                        // Removing URL parameter so reloading the page doesn't replay the splash
                        if (window.history.replaceState) {
                            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                            window.history.replaceState({path: cleanUrl}, '', cleanUrl);
                        }
                    }, 500);
                }
            }, 40);
        },

        // --- Slideshow Logic ---
        startSlideshow() {
            if (this.slideTimer) clearInterval(this.slideTimer);
            this.slideTimer = setInterval(() => {
                if (this.state === 'idle' && this.slides.length > 1) {
                    this.currentSlide = (this.currentSlide + 1) % this.slides.length;
                }
            }, 5000);
        },

        nextSlide() {
            if (this.slides.length < 2) return;
            this.currentSlide = (this.currentSlide + 1) % this.slides.length;
            this.startSlideshow(); // reset timer on manual navigation
        },

        prevSlide() {
            if (this.slides.length < 2) return;
            this.currentSlide = (this.currentSlide - 1 + this.slides.length) % this.slides.length;
            this.startSlideshow();
        },

        goToSlide(idx) {
            this.currentSlide = idx;
            this.startSlideshow();
        },

        startClock() {
            const tick = () => {
                const now = new Date();
                this.clockHm = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
                this.clockSec = now.toLocaleTimeString('en-PH', { second: '2-digit' }).padStart(2, '0');
                this.clockDate = now.toLocaleDateString('en-PH', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            };
            tick();
            this.clockInterval = setInterval(tick, 1000);
        },

        activate(fromKey = false, initialChar = '') {
            this.state = 'active';
            this.tab = 'scan';
            this.result = null;
            this.isProcessing = false;
            if (fromKey) {
                this.manualId = initialChar;
            } else {
                this.manualId = '';
            }
            this.handleActivity();
            
            // Focus barcode input after DOM updates
            this.$nextTick(() => {
                this.$refs.barcodeInput?.focus();
            });
        },

        deactivate() {
            this.state = 'idle';
            this.result = null;
            this.isProcessing = false;
            this.manualId = '';
            this.stopScanning();
            clearTimeout(this.inactivityTimeout);
        },

        handleActivity() {
            if (this.state === 'idle') return;
            clearTimeout(this.inactivityTimeout);
            this.inactivityTimeout = setTimeout(() => {
                this.deactivate();
            }, 60000); // 60s timeout
            
            // Manage camera state based on tab
            if (this.tab === 'webcam') {
                if (!this.isCameraActive) this.initScanner();
            } else {
                if (this.isCameraActive) this.stopScanning();
            }

            // Focus appropriate input
            this.$nextTick(() => {
                if (this.tab === 'scan') this.$refs.barcodeInput?.focus();
                if (this.tab === 'manual') {
                    this.$refs.manualInput?.focus();
                    this.fetchSuggestions();
                }
            });
        },

        handleKey(e) {
            // Wake up from idle
            if (this.state === 'idle' && e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
                this.activate(true, e.key);
                return;
            }

            this.handleActivity();
            
            // Ignore if typing in an actual input field
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

            // Global barcode scanner capture
            if (e.key === 'Enter') {
                if (this.manualId.trim()) {
                    this.submitManual();
                }
            } else if (e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
                this.manualId += e.key;
            }
        },

        async fetchSuggestions() {
            const query = this.manualId.trim();
            if (query.length < 2) {
                this.suggestions = [];
                this.showSuggestions = false;
                return;
            }
            
            clearTimeout(this.searchDebounce);
            this.searchDebounce = setTimeout(async () => {
                try {
                    const res = await fetch(`/kiosk/search?q=${encodeURIComponent(query)}`);
                    if (res.ok) {
                        this.suggestions = await res.json();
                        this.showSuggestions = this.suggestions.length > 0;
                    }
                } catch (e) {}
            }, 250);
        },

        selectSuggestion(id) {
            this.manualId = id;
            this.showSuggestions = false;
            this.submitManual();
        },

        // --- Camera Logic ---
        async initScanner() {
            try {
                const videoInputDevices = await BrowserMultiFormatReader.listVideoInputDevices();
                this.cameras = videoInputDevices;
                
                if (this.cameras.length > 0) {
                    // Filter out mobile phone links (e.g. Phone Link / DroidCam) and prefer built-in / USB Webcams
                    const preferredCam = this.cameras.find(c => {
                        const label = (c.label || '').toLowerCase();
                        return (label.includes('usb') || label.includes('integrated') || label.includes('webcam') || label.includes('camera') || label.includes('hd')) 
                            && !label.includes('phone') && !label.includes('droid') && !label.includes('virtual');
                    });

                    this.selectedCamera = preferredCam ? preferredCam.deviceId : this.cameras[0].deviceId;
                    this.startScanning();
                }
            } catch (err) {
                console.error("Camera init error:", err);
            }
        },

        async startScanning() {
            const videoEl = document.getElementById('kiosk-video');
            if (!videoEl) return;

            try {
                const constraints = {
                    video: {
                        deviceId: this.selectedCamera ? { exact: this.selectedCamera } : undefined,
                        width: { ideal: 640, max: 1280 },
                        height: { ideal: 480, max: 720 },
                        frameRate: { ideal: 30, max: 60 }
                    }
                };

                // Decode throttle set to 350ms to keep live video stream 60fps fluid
                await this.codeReader.decodeFromConstraints(constraints, videoEl, (result, err) => {
                    if (result && !this.isProcessing) {
                        this.manualId = result.text;
                        this.submitManual();
                    }
                });
                this.isCameraActive = true;
            } catch (err) {
                try {
                    await this.codeReader.decodeFromVideoDevice(this.selectedCamera, videoEl, (result, err) => {
                        if (result && !this.isProcessing) {
                            this.manualId = result.text;
                            this.submitManual();
                        }
                    });
                    this.isCameraActive = true;
                } catch (e) {
                    console.error("Scanner start error:", e);
                }
            }
        },

        stopScanning() {
            if (!this.isCameraActive) return;

            try {
                if (this.codeReader) {
                    if (typeof this.codeReader.reset === 'function') {
                        this.codeReader.reset();
                    } else if (typeof this.codeReader.stopAsyncDecode === 'function') {
                        this.codeReader.stopAsyncDecode();
                    } else if (typeof this.codeReader.stopContinuousDecode === 'function') {
                        this.codeReader.stopContinuousDecode();
                    }
                }
            } catch (e) {}

            try {
                const videoEl = document.getElementById('kiosk-video');
                if (videoEl && videoEl.srcObject) {
                    const stream = videoEl.srcObject;
                    if (stream && stream.getTracks) {
                        stream.getTracks().forEach(track => track.stop());
                    }
                    videoEl.srcObject = null;
                }
            } catch (e) {}

            this.isCameraActive = false;
        },

        resetScan() {
            this.result = null;
            this.isProcessing = false;
            this.manualId = '';
            this.handleActivity();
            this.$nextTick(() => {
                if (this.tab === 'scan') this.$refs.barcodeInput?.focus();
                if (this.tab === 'manual') this.$refs.manualInput?.focus();
            });
        },

        // --- Processing Logic ---
        submitManual() {
            if (!this.manualId || !this.manualId.trim()) return;
            this.showSuggestions = false;
            this.processId(this.manualId.trim());
        },

        async processId(id) {
            if (this.isProcessing) return;
            this.isProcessing = true;
            this.result = null;
            clearTimeout(this.resetTimeout);
            
            const safetyTimeout = setTimeout(() => {
                if (this.isProcessing) {
                    this.isProcessing = false;
                    this.result = { status: 'error', message: 'Request timed out. Please try again.' };
                }
            }, 8000);

            const audio = new Audio('/beep.mp3');
            audio.play().catch(e => {});

            try {
                if (!navigator.onLine) {
                    await QueueManager.enqueue(id);
                    this.result = { status: 'offline', message: 'Saved offline (No Internet).', student_id: id };
                } else {
                    try {
                        const res = await this.performOnlineCheckin(id);
                        this.result = res;
                        if(res?.status === 'success') this.fetchOccupancy();
                    } catch (networkErr) {
                        // Automatic fallback to offline queue if network or server drops during scan
                        await QueueManager.enqueue(id);
                        this.result = { status: 'offline', message: 'Saved offline (Connection Dropped).', student_id: id };
                    }
                }
            } catch (err) {
                await QueueManager.enqueue(id);
                this.result = { status: 'offline', message: 'Saved offline.', student_id: id };
            } finally {
                clearTimeout(safetyTimeout);
                this.isProcessing = false;
                this.manualId = '';
                
                this.$nextTick(() => {
                    this.handleActivity();
                });
            }
        },
        
        async performOnlineCheckin(id) {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 20000); // Increased to 20s for slow school networks
            
            try {
                const processRes = await fetch('/kiosk/process', {
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
                
                const processData = await processRes.json();
                if (processData.status === 'error') {
                    return { status: 'error', message: processData.message, student: processData.student };
                }
                return processData;
            } catch (e) {
                clearTimeout(timeoutId);
                if (e.name === 'AbortError') {
                    return { status: 'error', message: 'Database query timed out. Please try again.' };
                }
                return { status: 'error', message: 'A system error occurred. Please try again.' };
            }
        },
        
        async fetchOccupancy() {
            if(!navigator.onLine) return;
            try {
                const res = await fetch('/kiosk/occupancy');
                if (res.ok) this.occupancy = await res.json();
            } catch (e) {}
        },
        
        // --- Real-Time Link Logic ---
        startRealtimePolling() {
            if (this.pollingInterval) clearInterval(this.pollingInterval);
            this.pollingInterval = setInterval(() => {
                this.pollRealtime();
            }, 2000); // Check every 2 seconds
        },
        
        async pollRealtime() {
            if (!navigator.onLine) return;
            try {
                const res = await fetch(`/kiosk/latest-scan?after_id=${this.lastLogId}`);
                if (!res.ok) return;
                
                const data = await res.json();
                if (data && data.id) {
                    this.lastLogId = data.id; // Update high-water mark
                    
                    // If we're already processing something locally, don't interrupt
                    if (this.isProcessing) return;
                    
                    // Trigger the animation for the remote scan!
                    this.triggerRemoteScan(data);
                }
            } catch (e) {}
        },
        
        triggerRemoteScan(data) {
            // Wake up kiosk if idle
            if (this.state === 'idle') {
                this.activate();
            }
            
            // Play success sound
            const audio = new Audio('/beep.mp3');
            audio.play().catch(e => {});
            
            // Reset state
            this.isProcessing = true;
            this.result = null;
            clearTimeout(this.resetTimeout);
            
            // Show result
            setTimeout(() => {
                this.isProcessing = false;
                this.result = data;
                this.fetchOccupancy();
                
                // Clear result after 4 seconds
                this.resetTimeout = setTimeout(() => {
                    this.result = null;
                    if (this.state === 'active' && !this.isCameraActive && this.tab === 'webcam') {
                         this.initScanner(); // Restart camera if needed
                    }
                    this.handleActivity();
                }, 4000);
            }, 500); // slight delay to feel natural
        }
    }));
};

if (window.Alpine) {
    registerApp();
} else {
    document.addEventListener('alpine:init', registerApp);
}
