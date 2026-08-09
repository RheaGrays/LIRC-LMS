<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Capture Patron Photo | CJC Library</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Fraunces:wght@600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --cjc-navy: #0f2744;
            --cjc-red: #c41e3a;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0b131f;
            color: #ffffff;
            touch-action: manipulation;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between p-4">

    <!-- Header -->
    <header class="text-center py-2">
        <div class="inline-flex items-center gap-2 mb-1">
            <img src="/CorJesu Logo.png" alt="CJC Logo" class="w-6 h-6 object-contain" />
            <span class="font-['Fraunces'] font-bold text-white text-base">CJC Library Registration</span>
        </div>
        <p class="text-xs text-gray-400 font-['Inter'] m-0">Mobile Camera Photo Capture Sync</p>
        @if($sessionId)
            <div class="mt-2 inline-block px-3 py-1 bg-blue-900/60 border border-blue-500/40 rounded-full">
                <span class="text-[11px] text-blue-300 font-['JetBrains_Mono']">Session: <strong>{{ $sessionId }}</strong></span>
            </div>
        @endif
    </header>

    <!-- Main Container -->
    <main x-data="mobileCameraApp('{{ $sessionId }}')" class="flex-1 flex flex-col items-center justify-center max-w-md mx-auto w-full py-4">

        <!-- SUCCESS STATE -->
        <template x-if="uploaded">
            <div class="text-center bg-gray-900/90 border border-green-500/40 rounded-2xl p-6 shadow-2xl flex flex-col items-center gap-4 w-full">
                <div class="w-16 h-16 rounded-full bg-green-500/20 border border-green-500 flex items-center justify-center">
                    <svg class="w-8 h-8 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-['Fraunces'] text-xl font-bold text-white mb-1">Photo Uploaded & Synced!</h2>
                    <p class="text-xs text-gray-300 leading-relaxed">Your photo has been synced with the registration screen on the PC. You may now close this browser tab.</p>
                </div>
                <button @click="resetCamera()" class="w-full py-3 bg-gray-800 border border-gray-700 rounded-xl text-xs font-semibold text-gray-300 uppercase tracking-wider">
                    Take Another Photo
                </button>
            </div>
        </template>

        <!-- CAMERA INTERFACE -->
        <template x-if="!uploaded">
            <div class="w-full flex flex-col items-center gap-4">

                <!-- Hidden Native Camera File Input -->
                <input type="file" x-ref="fileInput" accept="image/*" capture="user" class="hidden" @change="handleFileSelected($event)" />

                <!-- Session Input if missing -->
                <div x-show="!sessionId" class="w-full bg-gray-900/90 border border-gray-800 rounded-xl p-4 mb-2">
                    <label class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-2">Pairing Session Code</label>
                    <input type="text" x-model="sessionId" placeholder="e.g. REG-X82A" class="w-full px-3 py-2 bg-black border border-gray-700 rounded-lg text-white font-['JetBrains_Mono'] uppercase tracking-widest text-center text-lg font-bold" />
                </div>

                <!-- Photo Preview Card / Camera Trigger Viewport -->
                <div class="w-full aspect-[3/4] max-h-[380px] rounded-2xl border border-gray-800 relative overflow-hidden bg-black flex flex-col items-center justify-center shadow-2xl p-4 text-center">

                    <!-- Captured Photo Preview -->
                    <template x-if="capturedImage">
                        <img :src="capturedImage" class="w-full h-full object-cover rounded-xl" />
                    </template>

                    <!-- Initial Placeholder State (Before Snap) -->
                    <template x-if="!capturedImage">
                        <div class="flex flex-col items-center gap-3 p-4">
                            <div class="w-20 h-20 rounded-full bg-red-950/60 border border-red-500/40 flex items-center justify-center text-red-400">
                                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h3l2-2h4l2 2h3a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <circle cx="12" cy="13" r="3" stroke-width="1.5" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-['Fraunces'] font-bold text-white text-base mb-1">Take Patron Photo</h3>
                                <p class="text-xs text-gray-400 leading-relaxed">Tap the button below to open your phone's camera and capture the photo.</p>
                            </div>
                        </div>
                    </template>

                    <!-- Processing Overlay -->
                    <div x-show="processingImage" class="absolute inset-0 bg-black/80 flex flex-col items-center justify-center gap-2">
                        <svg class="w-8 h-8 animate-spin text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span class="text-xs text-gray-300 font-['Inter']">Optimizing photo…</span>
                    </div>

                    <!-- Overlay Corner Brackets -->
                    <div class="absolute inset-4 pointer-events-none border-2 border-red-500/30 rounded-xl"></div>
                </div>

                <!-- Action Controls -->
                <div class="w-full flex flex-col items-center gap-3">
                    <template x-if="!capturedImage">
                        <button @click="openPhoneCamera()" :disabled="!sessionId || processingImage"
                                class="w-full py-3.5 bg-[#c41e3a] active:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl text-white font-bold text-sm tracking-wider uppercase shadow-lg transition-transform active:scale-95 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <circle cx="12" cy="12" r="3" stroke-width="2"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h3l2-2h4l2 2h3a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            </svg>
                            Open Phone Camera
                        </button>
                    </template>

                    <template x-if="capturedImage">
                        <div class="w-full flex gap-3">
                            <button @click="openPhoneCamera()" class="flex-1 py-3 bg-gray-800 hover:bg-gray-700 rounded-xl text-xs font-semibold text-gray-300 uppercase tracking-wider">
                                Retake
                            </button>
                            <button @click="uploadPhoto()" :disabled="uploading" class="flex-1 py-3 bg-green-600 active:bg-green-700 disabled:opacity-50 rounded-xl text-xs font-bold text-white uppercase tracking-wider flex items-center justify-center gap-2">
                                <template x-if="uploading">
                                    <span class="text-xs">Syncing…</span>
                                </template>
                                <template x-if="!uploading">
                                    <span>Sync to Registration</span>
                                </template>
                            </button>
                        </div>
                    </template>

                    <p x-show="uploadError" class="text-xs text-red-400 text-center" x-text="uploadError"></p>
                </div>

            </div>
        </template>
    </main>

    <!-- Footer -->
    <footer class="text-center text-[11px] text-gray-500 py-2 font-['Inter']">
        Cor Jesu College — Library Information & Resource Center
    </footer>

    <script>
        function mobileCameraApp(initialSessionId) {
            return {
                sessionId: initialSessionId || '',
                capturedImage: null,
                processingImage: false,
                uploading: false,
                uploaded: false,
                uploadError: '',

                openPhoneCamera() {
                    this.$refs.fileInput.click();
                },

                handleFileSelected(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    this.processingImage = true;
                    this.uploadError = '';

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const img = new Image();
                        img.onload = () => {
                            // Resize image on client to max 800x800 for optimal fast upload
                            const maxDim = 800;
                            let width = img.width;
                            let height = img.height;

                            if (width > height) {
                                if (width > maxDim) {
                                    height = Math.round((height * maxDim) / width);
                                    width = maxDim;
                                }
                            } else {
                                if (height > maxDim) {
                                    width = Math.round((width * maxDim) / height);
                                    height = maxDim;
                                }
                            }

                            const canvas = document.createElement('canvas');
                            canvas.width = width;
                            canvas.height = height;
                            const ctx = canvas.getContext('2d');
                            ctx.drawImage(img, 0, 0, width, height);

                            // Compress to lightweight 80% JPEG
                            this.capturedImage = canvas.toDataURL('image/jpeg', 0.80);
                            this.processingImage = false;
                        };
                        img.onerror = () => {
                            this.uploadError = 'Failed to process image.';
                            this.processingImage = false;
                        };
                        img.src = e.target.result;
                    };
                    reader.onerror = () => {
                        this.uploadError = 'Failed to read file.';
                        this.processingImage = false;
                    };
                    reader.readAsDataURL(file);
                },

                resetCamera() {
                    this.uploaded = false;
                    this.capturedImage = null;
                    this.uploadError = '';
                },

                async uploadPhoto() {
                    if (!this.sessionId || !this.capturedImage) {
                        this.uploadError = 'Missing session code or photo.';
                        return;
                    }
                    this.uploading = true;
                    this.uploadError = '';
                    try {
                        const res = await fetch('/api/register/photo-session/upload', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                session_id: this.sessionId,
                                photoDataUrl: this.capturedImage
                            })
                        });

                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.uploaded = true;
                        } else {
                            this.uploadError = data.message || 'Upload failed. Please try again.';
                        }
                    } catch (e) {
                        this.uploadError = 'Network error during upload.';
                    } finally {
                        this.uploading = false;
                    }
                }
            };
        }
    </script>
</body>
</html>
