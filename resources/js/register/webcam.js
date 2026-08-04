export default () => ({
    status: 'loading', // 'loading', 'ready', 'error'
    errorMsg: '',
    captured: false,
    capturedImage: null,
    stream: null,

    init() {
        this.$watch('captured', (value) => {
            if (!value) this.startCamera();
        });
        if (!this.captured) {
            this.startCamera();
        }
    },

    async startCamera() {
        this.status = 'loading';
        this.errorMsg = '';
        try {
            const mediaStream = await navigator.mediaDevices.getUserMedia({
                video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: "user" },
                audio: false,
            });
            this.stream = mediaStream;
            
            // Allow DOM to update so video element is present
            this.$nextTick(() => {
                const video = this.$refs.video;
                if (video) {
                    video.srcObject = mediaStream;
                    video.play().then(() => {
                        this.status = 'ready';
                    }).catch(err => {
                        console.error(err);
                    });
                }
            });
        } catch (err) {
            this.status = 'error';
            if (err.name === "NotAllowedError") {
                this.errorMsg = "Camera access was denied. Please allow camera access in your browser settings.";
            } else if (err.name === "NotFoundError") {
                this.errorMsg = "No camera found on this device.";
            } else {
                this.errorMsg = "Could not access camera: " + err.message;
            }
        }
    },

    stopCamera() {
        if (this.stream) {
            this.stream.getTracks().forEach(t => t.stop());
            this.stream = null;
        }
    },

    handleCapture() {
        const video = this.$refs.video;
        const canvas = this.$refs.canvas;
        if (!video || !canvas) return;

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext("2d");
        if (!ctx) return;

        // Mirror the image (selfie-style)
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0);
        
        const dataUrl = canvas.toDataURL("image/jpeg", 0.85);
        this.stopCamera();
        
        this.capturedImage = dataUrl;
        this.captured = true;
        
        // Dispatch event to parent component
        this.$dispatch('photo-captured', { dataUrl });
    },

    handleRetake() {
        this.capturedImage = null;
        this.captured = false;
        // Watcher on 'captured' will trigger startCamera()
        this.$dispatch('photo-retaken');
    },

    destroy() {
        this.stopCamera();
    }
});
