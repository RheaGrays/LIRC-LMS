<!-- resources/views/components/admin/camera-modal.blade.php -->
<div x-data="cameraModal()" 
     @open-camera.window="open = true; initCamera()"
     @keydown.escape.window="closeCamera()"
     x-show="open" style="display: none;" 
     class="fixed inset-0 z-[60] flex items-center justify-center p-4">
    
    <div x-show="open" class="fixed inset-0 bg-black/80 backdrop-blur-sm" @click="closeCamera()"></div>

    <div x-show="open" class="relative bg-black rounded-xl shadow-2xl w-full max-w-lg overflow-hidden border border-white/20">
        <div class="px-4 py-3 border-b border-white/10 flex items-center justify-between text-white">
            <h3 class="text-sm font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 text-red-500 animate-pulse" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg>
                Camera Active
            </h3>
            <button type="button" @click="closeCamera()" class="text-white/60 hover:text-white">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        
        <div class="relative aspect-video bg-gray-900 flex items-center justify-center">
            <video x-ref="video" class="w-full h-full object-cover" autoplay playsinline></video>
            
            <div x-show="error" class="absolute inset-0 flex items-center justify-center p-6 text-center text-red-400 font-medium">
                <span x-text="error"></span>
            </div>
            
            <!-- Target guide -->
            <div class="absolute inset-0 pointer-events-none p-8 hidden md:block">
                <div class="w-full h-full border-2 border-white/20 rounded-[100%] border-dashed"></div>
            </div>
        </div>
        
        <div class="p-4 bg-gray-900 border-t border-white/10 flex justify-center">
            <button type="button" @click="capturePhoto()" :disabled="!!error" class="w-14 h-14 rounded-full bg-white border-4 border-gray-300 hover:scale-95 transition-transform flex items-center justify-center focus:outline-none">
            </button>
        </div>
        
        <canvas x-ref="canvas" style="display: none;"></canvas>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('cameraModal', () => ({
        open: false,
        stream: null,
        error: '',
        
        async initCamera() {
            this.error = '';
            try {
                this.stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } } 
                });
                this.$refs.video.srcObject = this.stream;
            } catch (err) {
                this.error = 'Could not access camera. Please allow permissions.';
                console.error(err);
            }
        },
        
        closeCamera() {
            this.open = false;
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
                this.stream = null;
            }
        },
        
        capturePhoto() {
            if (!this.stream) return;
            
            const video = this.$refs.video;
            const canvas = this.$refs.canvas;
            
            // Set canvas dim to match video
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            
            // Draw
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            // Compress and convert to base64 jpeg
            const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
            
            // Dispatch back to student form component
            this.$dispatch('input', dataUrl); // Fallback for standard Alpine usage
            
            // Specifically set it on the parent student object
            const parentData = this.$data.$parent || document.querySelector('[x-data="{ student"]').__x.$data;
            if(parentData && parentData.student) {
                parentData.student.photo_base64 = dataUrl;
            }
            
            this.closeCamera();
        }
    }));
});
</script>
@endpush
