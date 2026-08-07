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
        barcodeBuffer: '',
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

        init() {
            // Pass 150ms interval to prevent continuous CPU decoding loops and eliminate video lag
            this.codeReader = new BrowserMultiFormatReader(null, 150);
            this.startClock();
            this.fetchOccupancy();
            setInterval(() => this.fetchOccupancy(), 30000);
            QueueManager.startSyncTimer();
            this.startSlideshow();

            // Listen for keydown globally to wake up from idle
            window.addEventListener('keydown', (e) => {
                if (this.state === 'idle' && e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
                    this.manualId = e.key;
                    this.activate(true);
                }
            });
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

        activate(fromKey = false) {
            this.state = 'active';
            this.tab = 'scan';
            this.result = null;
            this.isProcessing = false;
            this.barcodeBuffer = '';
            if (!fromKey) {
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
            this.barcodeBuffer = '';
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
                this.stopScanning();
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
            this.handleActivity();
            
            // Ignore if typing in an actual input field
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

            // Global barcode scanner capture
            if (e.key === 'Enter') {
                if (this.barcodeBuffer.trim()) {
                    this.manualId = this.barcodeBuffer.trim();
                    this.submitManual();
                }
                this.barcodeBuffer = '';
            } else if (e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
                this.barcodeBuffer += e.key;
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
                    this.selectedCamera = this.cameras[0].deviceId;
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
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                        frameRate: { ideal: 30, max: 60 }
                    }
                };

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
            if (this.codeReader) {
                this.codeReader.reset();
            }
            this.isCameraActive = false;
        },

        resetScan() {
            this.result = null;
            this.isProcessing = false;
            this.barcodeBuffer = '';
            this.manualId = '';
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
                    this.result = { status: 'offline', message: 'Saved offline.', student_id: id };
                } else {
                    const res = await this.performOnlineCheckin(id);
                    this.result = res;
                    if(res?.status === 'success') this.fetchOccupancy();
                }
            } catch (err) {
                this.result = { status: 'error', message: 'A system error occurred. Please try again.' };
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
            const timeoutId = setTimeout(() => controller.abort(), 7000);
            
            try {
                const processRes = await fetch('/kiosk/process', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
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
        }
    }));
};

if (window.Alpine) {
    registerApp();
} else {
    document.addEventListener('alpine:init', registerApp);
}
