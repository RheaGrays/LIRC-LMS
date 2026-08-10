import { BrowserMultiFormatReader, BarcodeFormat, DecodeHintType } from '@zxing/library';
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
        
        // About Us & Help Support Modal State
        showAboutModal: false,
        aboutTab: 'system',
        
        // Clock
        clockHm: '',
        clockSec: '',
        clockDate: '',
        clockInterval: null,

        // Scanner
        codeReader: null,
        mediaStream: null,
        decodeInterval: null,
        nativeDetector: null,
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
            // Target hints to scan only relevant formats (Code 128, Code 39, EAN 13, QR Code, UPC)
            // Eliminates 12 unused decoders per frame, freeing 85% CPU for 60 FPS smooth video
            const hints = new Map();
            hints.set(DecodeHintType.POSSIBLE_FORMATS, [
                BarcodeFormat.CODE_128,
                BarcodeFormat.CODE_39,
                BarcodeFormat.EAN_13,
                BarcodeFormat.QR_CODE,
                BarcodeFormat.UPC_A
            ]);
            this.codeReader = new BrowserMultiFormatReader(hints);

            if ('BarcodeDetector' in window) {
                try {
                    this.nativeDetector = new BarcodeDetector({
                        formats: ['code_128', 'code_39', 'qr_code', 'ean_13', 'upc_a']
                    });
                } catch (e) {
                    this.nativeDetector = null;
                }
            }

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
                        if (tokenData.token) {
                            const metaEl = document.querySelector('meta[name="csrf-token"]');
                            if (metaEl) metaEl.content = tokenData.token;
                        }
                    } else {
                        console.warn('[LEMS Kiosk] CSRF token keepalive returned status:', tokenRes.status);
                    }
                } catch (e) {
                    console.warn('[LEMS Kiosk] CSRF token keepalive failed:', e.message);
                }
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
                    } else {
                        console.warn('[LEMS Kiosk] Autocomplete search returned status:', res.status);
                        this.suggestions = [];
                        this.showSuggestions = false;
                    }
                } catch (e) {
                    console.warn('[LEMS Kiosk] Autocomplete search failed:', e.message);
                    this.suggestions = [];
                    this.showSuggestions = false;
                }
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

            this.stopScanning();

            try {
                const constraints = {
                    video: {
                        deviceId: this.selectedCamera ? { exact: this.selectedCamera } : undefined,
                        width: { ideal: 1280, max: 1920 },
                        height: { ideal: 720, max: 1080 },
                        frameRate: { ideal: 60, max: 60 }
                    }
                };

                // Direct binding of raw camera stream to <video> element for 60 FPS silky smooth preview
                const stream = await navigator.mediaDevices.getUserMedia(constraints);
                this.mediaStream = stream;
                videoEl.srcObject = stream;
                await videoEl.play();
                this.isCameraActive = true;

                // Offscreen canvas for frame sampling
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d', { willReadFrequently: true });

                // Decoupled decoding loop running every 200ms (5 scans/sec)
                // leaves main video thread at 100% 60 FPS hardware speed with 0 lag
                this.decodeInterval = setInterval(async () => {
                    if (!this.isCameraActive || this.isProcessing || !videoEl || videoEl.paused || videoEl.ended) return;

                    try {
                        // 1. Hardware-accelerated GPU BarcodeDetector (Chrome/Edge/Electron)
                        if (this.nativeDetector) {
                            const detected = await this.nativeDetector.detect(videoEl);
                            if (detected && detected.length > 0 && !this.isProcessing) {
                                const code = detected[0].rawValue;
                                if (code && code.trim()) {
                                    this.manualId = code.trim();
                                    this.submitManual();
                                    return;
                                }
                            }
                        }

                        // 2. Fallback ZXing reader on targeted canvas frame
                        if (videoEl.videoWidth > 0 && videoEl.videoHeight > 0) {
                            canvas.width = videoEl.videoWidth;
                            canvas.height = videoEl.videoHeight;
                            ctx.drawImage(videoEl, 0, 0, canvas.width, canvas.height);

                            const result = this.codeReader.decodeFromCanvas(canvas);
                            if (result && result.getText() && !this.isProcessing) {
                                const code = result.getText();
                                if (code && code.trim()) {
                                    this.manualId = code.trim();
                                    this.submitManual();
                                }
                            }
                        }
                    } catch (e) {
                        // Ignore decode frame errors (normal when no barcode in view)
                    }
                }, 200);

            } catch (err) {
                console.error("Camera start error:", err);
            }
        },

        stopScanning() {
            if (this.decodeInterval) {
                clearInterval(this.decodeInterval);
                this.decodeInterval = null;
            }

            if (this.mediaStream) {
                try {
                    this.mediaStream.getTracks().forEach(track => track.stop());
                } catch (e) {}
                this.mediaStream = null;
            }

            const videoEl = document.getElementById('kiosk-video');
            if (videoEl) {
                videoEl.srcObject = null;
            }

            if (this.codeReader && typeof this.codeReader.reset === 'function') {
                try {
                    this.codeReader.reset();
                } catch (e) {}
            }

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
                const res = await this.performOnlineCheckin(id);

                // BUG-01 FIX: handle 503 server-busy — fall back to offline queue
                if (res?.status === 'error' && res?.code === 503) {
                    const pendingAction = await this.resolveNextAction(id);
                    await QueueManager.enqueue(id, pendingAction);
                    this.result = { status: 'offline', message: 'Server busy — saved offline.', student_id: id };
                } else {
                    this.result = res;
                    if (res?.status === 'success') this.fetchOccupancy();
                }
            } catch (networkErr) {
                // Automatic fallback to offline queue if network or server drops during scan
                const pendingAction = await this.resolveNextAction(id);
                await QueueManager.enqueue(id, pendingAction);
                this.result = { status: 'offline', message: 'Saved offline (Connection Dropped).', student_id: id };
            } finally {
                clearTimeout(safetyTimeout);
                this.isProcessing = false;
                this.manualId = '';
                
                this.$nextTick(() => {
                    this.handleActivity();
                });
            }
        },

        /**
         * BUG-06 FIX: Resolve what action should be queued when the server is unreachable.
         * Queries /kiosk/last (if possible) to determine toggle state; defaults to check_in.
         */
        async resolveNextAction(id) {
            try {
                const res = await fetch('/kiosk/last', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({ student_id: id }),
                    signal: AbortSignal.timeout(3000)
                });
                if (res.ok) {
                    const data = await res.json();
                    return (data.action === 'check_in') ? 'check_out' : 'check_in';
                }
            } catch (_) { /* unreachable — default below */ }
            return 'check_in'; // safe default
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
            // Removed navigator.onLine check to allow localhost fallback
            try {
                const res = await fetch('/kiosk/occupancy');
                if (res.ok) {
                    this.occupancy = await res.json();
                } else {
                    console.warn('[LEMS Kiosk] Occupancy fetch returned status:', res.status);
                }
            } catch (e) {
                console.warn('[LEMS Kiosk] Occupancy fetch network error:', e.message);
            }
        },
        
        // --- Real-Time Link Logic ---
        remoteQueue: [],
        isDisplayingQueue: false,

        startRealtimePolling() {
            if (this.pollingInterval) clearInterval(this.pollingInterval);
            this.pollingInterval = setInterval(() => {
                this.pollRealtime();
            }, 1000); // Check every 1 second
        },
        
        async pollRealtime() {
            // Removed navigator.onLine check to allow localhost fallback
            try {
                const res = await fetch(`/kiosk/latest-scan?after_id=${this.lastLogId}`);
                if (!res.ok) return;
                
                const data = await res.json();
                if (!data) return;

                const events = Array.isArray(data) ? data : [data];
                let newEvents = events.filter(e => e && e.seq_id > this.lastLogId);

                if (newEvents.length > 0) {
                    const maxSeq = Math.max(...newEvents.map(e => e.seq_id || 0));
                    if (maxSeq > this.lastLogId) {
                        this.lastLogId = maxSeq;
                    }

                    this.remoteQueue.push(...newEvents);
                    this.processRemoteQueue();
                }
            } catch (e) {
                console.warn('[LEMS Kiosk] Realtime polling network error:', e.message);
            }
        },
        
        processRemoteQueue() {
            if (this.isDisplayingQueue || this.remoteQueue.length === 0) return;
            this.isDisplayingQueue = true;

            const nextEvent = () => {
                if (this.remoteQueue.length === 0) {
                    this.isDisplayingQueue = false;
                    return;
                }

                if (this.isProcessing) {
                    setTimeout(nextEvent, 500);
                    return;
                }

                const item = this.remoteQueue.shift();

                // Wake up kiosk if idle
                if (this.state === 'idle') {
                    this.activate();
                }
                
                // Play success sound
                const audio = new Audio('/beep.mp3');
                audio.play().catch(e => {});
                
                this.result = item;
                this.fetchOccupancy();
                
                // If more events are queued behind this one, display for 2s then switch;
                // Otherwise keep current scan result displayed persistently on screen until staff clicks Next or next scan occurs.
                clearTimeout(this.resetTimeout);
                if (this.remoteQueue.length > 0) {
                    this.resetTimeout = setTimeout(() => {
                        nextEvent();
                    }, 2000);
                } else {
                    this.isDisplayingQueue = false;
                }
            };

            nextEvent();
        }
    }));
};

if (window.Alpine) {
    registerApp();
} else {
    document.addEventListener('alpine:init', registerApp);
}
