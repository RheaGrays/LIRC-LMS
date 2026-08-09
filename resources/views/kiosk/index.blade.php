@extends('layouts.kiosk')

@section('title', ' | Kiosk')

@section('kiosk_content')
<div x-data="kioskApp()" class="w-full h-screen flex flex-col relative z-10 bg-transparent cursor-pointer select-none overflow-hidden" 
     @mousemove.window="handleActivity()" @touchstart.window="handleActivity()" @keydown.window="handleKey($event)" @click="activate()">

    {{-- Splash Screen Overlay --}}
    <x-kiosk.splash-screen />

    <!-- Transparent Background to allow hero-pattern to show through -->
    <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none bg-transparent"></div>
    
    <!-- IDLE STATE -->
    <template x-if="state === 'idle'">
        <x-kiosk.idle-state />
    </template>

    <!-- SCANNER STATE -->
    <template x-if="state === 'active'">
        <div class="flex-1 flex flex-col cursor-default relative z-20 bg-transparent" @click.stop>
            {{-- Scanner Header --}}
            <x-kiosk.scanner-header />

            <!-- Main Scanner Box -->
            <main class="flex-1 flex items-center justify-center p-6 pb-16">
                <div class="flex flex-col items-center w-full max-w-[900px] transition-all duration-300">
                    <div class="bg-white border border-gray-100 rounded-[28px] shadow-[0_20px_50px_rgba(0,0,0,0.06)] w-full relative overflow-hidden flex flex-col min-h-[480px]">
                        
                        <div class="px-10 pt-10 pb-20 relative z-10" x-show="!result && !isProcessing">
                            {{-- Scanner Tabs (Header, Tab Bar, All 3 Tab Contents) --}}
                            <x-kiosk.scanner-tabs />
                        </div>

                        {{-- Bottom Red Swoosh Wave Graphics --}}
                        <x-kiosk.scanner-waves />
                            
                            <!-- Processing State -->
                            <div x-show="isProcessing" class="flex flex-col items-center justify-center p-12 gap-5 fade-in-up">
                                <div class="w-12 h-12 border-4 border-[var(--bg-cream-2)] border-t-[var(--cjc-red)] rounded-full animate-spin"></div>
                                <span class="text-[16px] font-bold tracking-wide text-[var(--cjc-navy)] font-['Inter'] animate-pulse">Processing ID...</span>
                            </div>

                            <!-- Result Overlay -->
                            <div x-show="result && !isProcessing" class="fade-in-up animate-slide-in pb-4">
                                <x-kiosk.status-card />
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </template>
    
    <style>
        @keyframes scanline {
            0% { top: 10%; }
            50% { top: 85%; }
            100% { top: 10%; }
        }
        .animate-bounce-slow {
            animation: bounce 3s infinite;
        }
    </style>

    <script>
        window.kioskLastLogId = {{ \App\Models\AttendanceLog::max('id') ?? 0 }};

        function kioskSlideshow() {
            return {
                images: @json($slideshowImages),
                currentIndex: 0,
                timer: null,
                init() {
                    // Preload images
                    this.images.forEach(item => {
                        const img = new Image();
                        img.src = item.src;
                    });
                    
                    this.startTimer();
                },
                startTimer() {
                    this.stopTimer();
                    if (this.images.length > 1) {
                        this.timer = setInterval(() => {
                            this.next();
                        }, 5000);
                    }
                },
                stopTimer() {
                    if (this.timer) clearInterval(this.timer);
                },
                next() {
                    this.currentIndex = (this.currentIndex + 1) % this.images.length;
                    this.startTimer();
                },
                prev() {
                    this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
                    this.startTimer();
                },
                goTo(index) {
                    this.currentIndex = index;
                    this.startTimer();
                }
            }
        }
    </script>
</div>
@endsection
