@extends('layouts.kiosk')

@section('title', ' | Kiosk')

@section('kiosk_content')
<div x-data="kioskApp()" class="w-full h-screen flex flex-col relative z-10 bg-transparent cursor-pointer select-none overflow-hidden" 
     @mousemove="handleActivity()" @keydown="handleKey($event)" @click="activate()">

    <!-- Transparent Background to allow hero-pattern to show through -->
    <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none bg-transparent"></div>
    
    <!-- IDLE STATE -->
    <template x-if="state === 'idle'">
        <div class="flex-1 flex flex-col relative z-10 overflow-hidden h-full">
            <!-- Header -->
            <header class="fade-in-up flex items-center justify-between px-12 py-5 shrink-0 z-20">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-full overflow-hidden border border-[var(--border-warm)] bg-white shrink-0 shadow-sm">
                        <img src="/cjc-logo.jpeg" alt="CJC Logo" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <p class="m-0 text-[14px] font-bold tracking-[0.08em] uppercase text-[var(--cjc-navy)] font-['Inter'] leading-tight">
                            Cor Jesu College
                        </p>
                        <p class="m-0 text-[11px] text-[var(--text-muted)] font-['Inter'] leading-[1.4] font-medium">
                            Learning Information Resource Center
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-6">
                    <div class="text-right">
                        <p class="m-0 text-[11px] text-[var(--text-muted)] font-['Inter'] leading-[1.6] font-medium">
                            Library Entrance<br/>Monitoring System
                        </p>
                    </div>
                    <div class="w-px h-8 bg-[var(--border-warm)] mx-1"></div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('register.index') }}" @click.stop class="inline-flex items-center gap-1.5 text-[12px] font-semibold text-[var(--cjc-navy)] font-['Inter'] no-underline px-4 py-1.5 border border-[var(--border-warm)] shadow-sm rounded-full bg-white/80 hover:bg-white transition-all backdrop-blur-md">
                            Register
                        </a>
                        <a href="{{ route('admin.login') }}" @click.stop class="inline-flex items-center gap-1.5 text-[12px] font-semibold text-[var(--cjc-navy)] font-['Inter'] no-underline px-4 py-1.5 border border-[var(--border-warm)] shadow-sm rounded-full bg-white/80 hover:bg-white transition-all backdrop-blur-md">
                            Admin
                        </a>
                    </div>
                </div>
            </header>

            <!-- Center -->
            <main class="flex-1 flex flex-col items-center justify-center px-12 gap-6 overflow-hidden">
                
                <!-- Headline & Clock (Grouped) -->
                <div class="flex flex-col items-center gap-1 shrink-0">
                    <div class="fade-in-up delay-1 text-center">
                        <h1 class="font-['Fraunces'] text-[clamp(40px,6vw,65px)] font-[800] text-[var(--cjc-navy)] m-0 leading-[1] tracking-[-0.02em]">
                            Welcome to <span class="text-[var(--cjc-red)]">LIRC</span>, CorJesian!
                        </h1>
                    </div>
                    <div class="fade-in-up delay-2 text-center">
                        <div class="font-['Fraunces'] text-[clamp(35px,5vw,55px)] font-bold text-[var(--cjc-navy)] leading-none tracking-[-0.02em]">
                            <span x-text="clockHm">--:--</span><span class="text-[0.4em] text-[var(--cjc-red)] font-bold ml-1 align-middle">:<span x-text="clockSec">--</span></span>
                        </div>
                        <div class="font-['Inter'] text-[14px] font-medium text-[var(--text-muted)] mt-1 tracking-[0.02em]" x-text="clockDate">
                            Loading...
                        </div>
                    </div>
                </div>

                <!-- CTA Button (Above container) -->
                <div class="fade-in-up delay-3 shrink-0">
                    <div class="flex items-center gap-3 bg-white/80 backdrop-blur-md px-7 py-3 rounded-full border border-[var(--border-warm)] shadow-md cursor-pointer hover:bg-white hover:scale-105 transition-all animate-bounce-slow" @click="activate()">
                        <span class="font-['Inter'] text-[15px] font-bold text-[var(--cjc-navy)] tracking-wide">
                            Present your ID to begin
                        </span>
                    </div>
                </div>

                <!-- Slideshow Wrapper -->
                <div class="fade-in-up delay-4 w-full flex flex-col items-center shrink-0 mt-6" x-data="kioskSlideshow()">
                    <!-- Glassmorphism Image Slider Container -->
                    <div class="relative w-full max-w-[800px] h-[350px] bg-white/40 backdrop-blur-xl border border-white/60 rounded-[28px] shadow-[0_12px_40px_rgba(15,39,68,0.12)] overflow-hidden flex shrink-0">
                        
                        <!-- Images -->
                        <template x-for="(item, index) in images" :key="index">
                            <div class="absolute inset-0 transition-opacity duration-1000"
                                 :class="currentIndex === index ? 'opacity-100 z-10' : 'opacity-0 z-0'">
                                <img :src="item.src" class="w-full h-full object-cover" />
                                <!-- Gradient Overlay for Text -->
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                            </div>
                        </template>

                        <!-- Card Info Content -->
                        <div class="absolute bottom-0 inset-x-0 p-8 z-10 flex flex-col items-center text-center">
                            <span class="inline-block px-4 py-1 rounded-full bg-[var(--cjc-red)] text-white text-[10px] font-bold uppercase tracking-[0.15em] mb-3 shadow-md" x-text="images[currentIndex].badge"></span>
                            <h2 class="font-['Inter'] text-[24px] font-bold text-white mb-1.5 tracking-tight drop-shadow-md" x-text="images[currentIndex].title"></h2>
                            <p class="font-['Inter'] text-white/90 text-[13px] font-medium max-w-[80%] drop-shadow-sm leading-relaxed" x-text="images[currentIndex].description"></p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </template>

    <!-- SCANNER STATE -->
    <template x-if="state === 'active'">
        <div class="flex-1 flex flex-col cursor-default relative z-20 bg-transparent" @click.stop>
            <!-- Header -->
            <header class="px-10 py-5 bg-white/50 border-b border-[var(--border-warm)] flex items-center justify-between shadow-sm backdrop-blur-md">
                <div class="flex items-center gap-4">
                    <button @click.stop="deactivate()" class="flex items-center gap-1.5 px-4 py-2 bg-white border border-[var(--border-light)] rounded-xl text-[13px] font-medium text-[var(--text-muted)] font-['Inter'] hover:border-[var(--cjc-navy)] hover:text-[var(--cjc-navy)] transition-colors shadow-sm">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                            <path d="M11 7H3M7 3L3 7l4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Back
                    </button>

                    <div class="w-px h-8 bg-[var(--border-light)] mx-2"></div>

                    <div class="w-10 h-10 rounded-full overflow-hidden border border-[var(--border-light)] bg-white shrink-0">
                        <img src="/cjc-logo.jpeg" alt="CJC" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <p class="m-0 text-[13px] font-bold tracking-[0.06em] uppercase text-[var(--cjc-navy)] font-['Inter'] leading-[1.2]">
                            Cor Jesu College
                        </p>
                        <p class="m-0 text-[11px] text-[var(--text-muted)] font-['Inter']">
                            Library Entrance Monitoring System
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <x-kiosk.offline-sync-status />
                    
                    <span class="font-['JetBrains_Mono'] text-[16px] font-semibold text-[var(--cjc-navy)] tracking-[0.04em] bg-white px-4 py-1.5 rounded-xl border border-[var(--border-light)] shadow-sm" x-text="clockHm">
                        --:--
                    </span>
                </div>
            </header>

            <!-- Main Scanner Box -->
            <main class="flex-1 flex items-center justify-center p-10 pb-20">
                <div class="flex flex-col items-center w-full max-w-[500px]">
                    <div class="fade-in-up bg-white border border-[var(--border-light)] rounded-[24px] shadow-[var(--shadow-lg)] w-full relative overflow-hidden">
                        
                        <div class="px-8 pt-8">
                            <h1 class="font-['Fraunces'] text-[26px] font-bold text-[var(--cjc-navy)] m-0 mb-2 tracking-tight">Scan ID</h1>
                            <p class="text-[14px] text-[var(--text-muted)] font-['Inter'] m-0 mb-7">Present your Student ID to check in or out.</p>

                            <!-- Tabs -->
                            <div x-show="!isProcessing" class="flex border-b border-[var(--border-light)] gap-6 mb-8">
                                <button class="pb-3 text-[14px] font-semibold transition-colors"
                                    :class="tab === 'scan' ? 'text-[var(--cjc-navy)] border-b-2 border-[var(--cjc-navy)]' : 'text-gray-400 hover:text-gray-600'"
                                    @click="tab = 'scan'; handleActivity()">Barcode / QR</button>
                                <button class="pb-3 text-[14px] font-semibold transition-colors"
                                    :class="tab === 'webcam' ? 'text-[var(--cjc-navy)] border-b-2 border-[var(--cjc-navy)]' : 'text-gray-400 hover:text-gray-600'"
                                    @click="tab = 'webcam'; handleActivity()">Webcam</button>
                                <button class="pb-3 text-[14px] font-semibold transition-colors"
                                    :class="tab === 'manual' ? 'text-[var(--cjc-navy)] border-b-2 border-[var(--cjc-navy)]' : 'text-gray-400 hover:text-gray-600'"
                                    @click="tab = 'manual'; handleActivity()">Manual Entry</button>
                            </div>
                        </div>

                        <div class="px-8 pb-8 min-h-[220px]">
                            
                            <!-- Form Content (Hidden while processing) -->
                            <div x-show="!isProcessing">
                                <!-- Tab: Scan -->
                                <div x-show="tab === 'scan'" class="flex flex-col gap-4 animate-slide-in">
                                    <label class="flex flex-col gap-2">
                                        <span class="text-[12px] font-semibold tracking-[0.08em] uppercase text-[var(--text-muted)] font-['Inter']">Scan or type ID</span>
                                        <input type="text" x-model="manualId" x-ref="barcodeInput" 
                                            @keydown.enter.prevent="if(manualId.trim()) submitManual()"
                                            @input="handleActivity()"
                                            placeholder="Scan ID here..." 
                                            class="w-full p-4 font-['JetBrains_Mono'] text-[20px] font-medium tracking-[0.06em] text-center bg-white border border-[var(--border-light)] rounded-[12px] text-[var(--cjc-navy)] outline-none focus:border-[var(--cjc-navy)] focus:shadow-[0_0_0_3px_rgba(15,39,68,0.08)] transition-all">
                                    </label>
                                    <p class="text-[12px] text-[var(--text-subtle)] font-['Inter'] text-center m-0">
                                        Press <kbd class="bg-[var(--bg-cream-2)] border border-[var(--border-warm)] rounded-[4px] px-[6px] py-[2px] text-[11px] font-['JetBrains_Mono']">Enter</kbd> to submit
                                    </p>
                                </div>

                                <!-- Tab: Webcam -->
                                <div x-show="tab === 'webcam'" class="flex flex-col gap-4 items-center animate-slide-in">
                                    <div class="w-full max-w-[340px] aspect-[4/3] bg-[#0a0a0a] rounded-[16px] relative overflow-hidden border border-[var(--border-light)] flex items-center justify-center">
                                        <video id="kiosk-video" class="w-full h-full object-cover" :class="isCameraActive ? 'block' : 'hidden'"></video>
                                        
                                        <!-- Scanline -->
                                        <template x-if="isCameraActive">
                                            <div class="absolute left-0 right-0 h-[2px] bg-gradient-to-r from-transparent via-[var(--cjc-red)] to-transparent z-10 animate-[scanline_2s_linear_infinite]"></div>
                                        </template>

                                        <!-- Placeholder -->
                                        <template x-if="!isCameraActive">
                                            <div class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-black/80 p-4">
                                                <div class="w-8 h-8 border-2 border-white/20 border-t-white rounded-full animate-[spin_0.8s_linear_infinite] mb-3"></div>
                                                <span class="text-white/80 text-[13px] font-['Inter'] font-medium">Starting camera...</span>
                                            </div>
                                        </template>
                                    </div>
                                    <p class="text-[13px] text-[var(--text-muted)] font-['Inter'] text-center m-0">Point your camera at the barcode or QR code on your Student ID.</p>
                                </div>

                                <!-- Tab: Manual -->
                                <div x-show="tab === 'manual'" class="flex flex-col gap-4 animate-slide-in relative">
                                    <label class="flex flex-col gap-2 relative">
                                        <span class="text-[12px] font-semibold tracking-[0.08em] uppercase text-[var(--text-muted)] font-['Inter']">Student ID or Name</span>
                                        <input type="text" x-model="manualId" x-ref="manualInput" 
                                            @keydown.enter.prevent="if(manualId.trim()) submitManual()"
                                            @input="handleActivity()"
                                            placeholder="Enter Student ID or Name" 
                                            class="w-full p-4 font-['JetBrains_Mono'] text-[16px] font-medium tracking-[0.05em] bg-white border border-[var(--border-light)] rounded-[12px] text-[var(--cjc-navy)] outline-none focus:border-[var(--cjc-navy)] focus:shadow-[0_0_0_3px_rgba(15,39,68,0.08)] transition-all">
                                    </label>
                                    
                                    <!-- Autocomplete Dropdown -->
                                    <div x-show="showSuggestions" @click.outside="showSuggestions = false" style="display: none;"
                                         class="absolute top-[82px] left-0 right-0 bg-white border border-[var(--border-light)] rounded-[12px] shadow-[0_10px_40px_rgba(15,39,68,0.1)] z-[100] overflow-hidden animate-slide-in">
                                        <template x-for="item in suggestions" :key="item.id">
                                            <div @click="selectSuggestion(item.id)"
                                                 class="flex items-center gap-3 p-3 hover:bg-[var(--bg-cream-2)] cursor-pointer transition-colors border-b border-gray-100 last:border-0">
                                                <img :src="item.photo" class="w-10 h-10 rounded-full object-cover bg-gray-100">
                                                <div class="flex flex-col">
                                                    <span class="text-[14px] font-bold text-[var(--cjc-navy)] font-['Inter']" x-text="item.name"></span>
                                                    <span class="text-[11px] font-semibold text-gray-500 font-['JetBrains_Mono']" x-text="item.id"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <button @click="submitManual()" :disabled="!manualId.trim()"
                                        class="w-full p-4 bg-[var(--cjc-red)] text-white border-none rounded-[12px] text-[15px] font-bold font-['Inter'] cursor-pointer transition-all disabled:opacity-50 disabled:cursor-not-allowed hover:bg-red-700 shadow-md">
                                        <span>Verify & Enter</span>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Processing State -->
                            <div x-show="isProcessing" class="flex flex-col items-center justify-center p-12 gap-5 fade-in-up">
                                <div class="w-12 h-12 border-4 border-[var(--bg-cream-2)] border-t-[var(--cjc-red)] rounded-full animate-spin"></div>
                                <span class="text-[16px] font-bold tracking-wide text-[var(--cjc-navy)] font-['Inter'] animate-pulse">Processing ID...</span>
                            </div>

                            <!-- Result Overlay -->
                            <div x-show="result && !isProcessing" class="mt-8 border-t border-[var(--border-light)] pt-8 fade-in-up animate-slide-in pb-4">
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


