@extends('layouts.kiosk')

@section('title', ' | Kiosk')

@section('kiosk_content')
<div x-data="kioskApp()" class="w-full min-h-screen flex flex-col relative z-10 hero-pattern cursor-pointer select-none overflow-hidden" 
     @mousemove="handleActivity()" @keydown="handleKey($event)" @click="activate()">

    <!-- Transparent Background to allow hero-pattern to show through -->
    <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none bg-transparent"></div>
    
    <!-- IDLE STATE (Option A Modal Container) -->
    <template x-if="state === 'idle'">
        <div class="flex-1 flex items-center justify-center p-4 md:p-8 min-h-screen relative z-10">

            <!-- Background Overlay for contrast -->
            <div class="absolute inset-0 z-0 bg-black/40 pointer-events-none"></div>

            <!-- CENTRAL MODAL WINDOW CARD (Option A Design) -->
            <div class="relative z-10 w-full max-w-[960px] bg-[#fefcf8] rounded-[24px] md:rounded-[28px] shadow-[0_25px_60px_rgba(0,0,0,0.35)] border border-[#0f2744]/15 overflow-hidden flex flex-col transition-all duration-300" @click.stop>
                
                <!-- TOP NAVY HEADER BAR -->
                <header class="bg-[#0f2744] text-white px-6 md:px-8 py-3.5 flex items-center justify-between border-b border-white/10 shrink-0 select-none">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-full overflow-hidden border border-white/30 bg-white shrink-0">
                            <img src="/cjc-logo.jpeg" alt="CJC Logo" class="w-full h-full object-cover">
                        </div>
                        <div class="flex items-center gap-2 text-[12px] md:text-[13px] font-['Inter']">
                            <span class="font-bold tracking-wide text-white">Cor Jesu College</span>
                            <span class="text-white/40 font-light">/</span>
                            <span class="text-white/80 font-normal">Learning Information Resource Center</span>
                        </div>
                    </div>
                    <div class="hidden sm:block text-[11px] md:text-[12px] text-white/70 font-['Inter'] font-medium tracking-wide">
                        Library Entrance Monitoring System
                    </div>
                </header>

                <!-- CARD BODY CONTAINER -->
                <div class="p-6 md:p-10 flex-1 flex flex-col justify-between relative bg-[#fefcf8]">
                    
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 md:gap-8 items-center flex-1">
                        
                        <!-- LEFT COLUMN: Headline, Clock, Date, Scan CTA -->
                        <div class="md:col-span-6 flex flex-col justify-center gap-4 py-2">
                            <!-- Headline -->
                            <div>
                                <h1 class="font-['Fraunces'] text-[clamp(32px,4vw,50px)] font-extrabold text-[#0f2744] m-0 leading-[1.05] tracking-[-0.02em]">
                                    Welcome to<br>
                                    <span class="text-[#c41e2a]">LIRC</span>, CorJesian!
                                </h1>
                            </div>

                            <!-- Digital Clock & Date -->
                            <div class="mt-2">
                                <div class="font-['Fraunces'] text-[clamp(44px,5.5vw,68px)] font-bold text-[#0f2744] leading-none tracking-[-0.03em] flex items-baseline">
                                    <span x-text="clockHm">--:--</span>
                                    <span class="text-[0.45em] text-[#c41e2a] font-semibold ml-1.5 align-baseline">:<span x-text="clockSec">--</span></span>
                                </div>
                                <div class="font-['Inter'] text-[13px] text-[#64605a] mt-2 font-medium tracking-wide" x-text="clockDate">
                                    Loading...
                                </div>
                            </div>

                            <!-- Scan ID Button CTA -->
                            <div class="mt-4">
                                <button @click="activate()" class="inline-flex items-center justify-center gap-2.5 px-8 py-3.5 rounded-full bg-[#c41e2a] hover:bg-[#a01822] text-white font-['Inter'] font-bold text-[14px] md:text-[15px] shadow-[0_6px_20px_rgba(196,30,42,0.35)] hover:shadow-[0_8px_24px_rgba(196,30,42,0.45)] transition-all cursor-pointer transform hover:scale-[1.02] active:scale-[0.98]">
                                    <span class="relative flex h-2.5 w-2.5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-white"></span>
                                    </span>
                                    <span>Scan your ID to Begin</span>
                                </button>
                            </div>
                        </div>

                        <!-- VERTICAL DIVIDER LINE -->
                        <div class="hidden md:flex md:col-span-1 justify-center items-center h-full py-4">
                            <div class="w-[1px] h-[85%] bg-[#e8e4de]"></div>
                        </div>

                        <!-- RIGHT COLUMN: Facility Photo Showcase Card -->
                        <div class="md:col-span-5 flex flex-col items-center justify-center relative">
                            
                            <template x-if="slides && slides.length > 0">
                                <div class="relative w-full aspect-[4/3] rounded-2xl overflow-hidden shadow-lg border border-[#0f2744]/10 bg-black/10 group">
                                    
                                    <!-- Slides Image Container -->
                                    <template x-for="(slide, idx) in slides" :key="idx">
                                        <div x-show="currentSlide === idx"
                                             x-transition:enter="transition ease-out duration-700"
                                             x-transition:enter-start="opacity-0 scale-105"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-400"
                                             x-transition:leave-start="opacity-100 scale-100"
                                             x-transition:leave-end="opacity-0 scale-95"
                                             class="absolute inset-0">
                                            
                                            <!-- Image -->
                                            <img :src="slide.image || '/images/facility1.jpg'" :alt="slide.title"
                                                 class="w-full h-full object-cover object-center">
                                            
                                            <!-- Gradient Overlay at bottom for caption -->
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent"></div>

                                            <!-- Bottom Info Caption inside image -->
                                            <div class="absolute bottom-0 left-0 right-0 p-4 text-white z-10">
                                                <h3 class="font-['Fraunces'] text-[15px] md:text-[16px] font-bold m-0 leading-tight" x-text="slide.title"></h3>
                                                <p class="font-['Inter'] text-[11px] text-white/80 m-0 mt-0.5 line-clamp-1" x-text="slide.description"></p>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Top Right Overlapping Red Circular Badge -->
                                    <div class="absolute top-3 right-3 z-20">
                                        <div class="w-14 h-14 md:w-16 md:h-16 rounded-full bg-[#c41e2a] text-white font-bold text-[10px] md:text-[11px] flex items-center justify-center text-center p-1.5 shadow-lg uppercase tracking-wider font-['Inter'] leading-tight border-2 border-white/20"
                                             x-text="slides[currentSlide]?.badge || 'E-Library'">
                                            E-Library
                                        </div>
                                    </div>

                                    <!-- Left Navigation Arrow -->
                                    <button @click.stop="prevSlide()"
                                            class="absolute left-2.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-white/85 hover:bg-white text-[#0f2744] flex items-center justify-center shadow-md border border-black/5 transition-all z-20 cursor-pointer"
                                            aria-label="Previous slide">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                        </svg>
                                    </button>

                                    <!-- Right Navigation Arrow -->
                                    <button @click.stop="nextSlide()"
                                            class="absolute right-2.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-white/85 hover:bg-white text-[#0f2744] flex items-center justify-center shadow-md border border-black/5 transition-all z-20 cursor-pointer"
                                            aria-label="Next slide">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- FOOTER ACTIONS INSIDE THE CARD (Register & Admin buttons bottom right) -->
                    <div class="flex items-center justify-between pt-6 mt-6 border-t border-[#e8e4de]/60">
                        <div class="text-[11px] text-[#9c988f] font-['Inter']">
                            Touch anywhere to check in &nbsp;·&nbsp; LEMS v1.0
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('register.index') }}" @click.stop
                               class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-[#0f2744] font-['Inter'] no-underline px-3.5 py-1.5 rounded-full border border-[#0f2744]/30 bg-white hover:bg-[#0f2744] hover:text-white transition-all shadow-xs">
                                <svg width="11" height="11" viewBox="0 0 11 11" fill="none">
                                    <circle cx="5.5" cy="4" r="2" stroke="currentColor" stroke-width="1.3"/>
                                    <path d="M2.5 9.5a3 3 0 016 0" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                                </svg>
                                Register
                            </a>
                            <a href="{{ route('admin.login') }}" @click.stop
                               class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-[#0f2744] font-['Inter'] no-underline px-3.5 py-1.5 rounded-full border border-[#0f2744]/30 bg-white hover:bg-[#0f2744] hover:text-white transition-all shadow-xs">
                                <svg width="11" height="11" viewBox="0 0 11 11" fill="none">
                                    <rect x="1" y="1" width="9" height="9" rx="1.5" stroke="currentColor" stroke-width="1.3"/>
                                    <path d="M3.5 5.5h4M5.5 3.5v4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                                </svg>
                                Admin
                            </a>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </template>

    <!-- SCANNER STATE (unchanged) -->
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
                            <div class="flex border-b border-[var(--border-light)] gap-6 mb-8">
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
                                    
                                    <template x-if="isCameraActive">
                                        <div class="absolute left-0 right-0 h-[2px] bg-gradient-to-r from-transparent via-[var(--cjc-red)] to-transparent z-10 animate-[scanline_2s_linear_infinite]"></div>
                                    </template>

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
                            <div x-show="tab === 'manual'" class="flex flex-col gap-3.5 animate-slide-in">
                                <label class="flex flex-col gap-1.5">
                                    <span class="text-[11px] font-semibold tracking-[0.07em] uppercase text-[var(--text-muted)] font-['Inter']">Student ID or Name</span>
                                    <input type="text" x-model="manualId" x-ref="manualInput" 
                                        @keydown.enter.prevent="if(manualId.trim()) submitManual()"
                                        @input="handleActivity()"
                                        placeholder="Enter Student ID or Name" 
                                        class="w-full p-3 font-['JetBrains_Mono'] text-[16px] font-medium tracking-[0.05em] bg-white border border-[var(--border-light)] rounded-[var(--radius-md)] text-[var(--cjc-navy)] outline-none focus:border-[var(--cjc-navy)] focus:shadow-[0_0_0_3px_rgba(15,39,68,0.08)] transition-all">
                                    <span class="text-[11px] text-[var(--text-subtle)] font-['Inter']">Enter exactly as printed on your ID card</span>
                                </label>
                                <button @click="submitManual()" :disabled="!manualId.trim() || isProcessing"
                                    class="w-full p-4 bg-[var(--cjc-red)] text-white border-none rounded-[12px] text-[15px] font-bold font-['Inter'] cursor-pointer transition-all disabled:opacity-50 disabled:cursor-not-allowed hover:bg-red-700 shadow-md">
                                    <span x-show="!isProcessing">Verify & Enter</span>
                                    <span x-show="isProcessing">Processing...</span>
                                </button>
                            </div>

                            <!-- Result Overlay -->
                            <div x-show="result && !isProcessing" class="mt-6 border-t border-[var(--border-light)] pt-6 fade-in-up">
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
            0%   { top: 10%; }
            50%  { top: 85%; }
            100% { top: 10%; }
        }
        .animate-bounce-slow {
            animation: bounce 3s infinite;
        }

        /* Background photo — now at 0.55 opacity for dark cinematic look */
        .hero-pattern::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: url('/bg.jpg') !important;
            background-size: cover !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
            opacity: 0.55 !important;
            pointer-events: none !important;
            z-index: 0 !important;
        }
        .hero-pattern > * {
            position: relative;
            z-index: 1;
        }

        /* Frosted glass preview card */
        .kiosk-preview-card {
            background: rgba(15, 15, 20, 0.55);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }
    </style>

    <script>
        function kioskSlideshow() {
            return {
                images: {!! json_encode($slideshowImages) !!},
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

@push('scripts')
<script>
    // Inject server-side collections or fallback default facilities into the Alpine component
    const dbCollections = {!! json_encode($collections->map(fn($c) => ['badge' => $c->badge, 'badge_color' => $c->badge_color, 'title' => $c->title, 'description' => $c->description, 'image' => '/images/facility1.jpg'])->values()) !!};
    const slideshowFacilities = {!! json_encode(array_map(fn($s) => ['badge' => $s['badge'], 'title' => $s['title'], 'description' => $s['description'], 'image' => $s['src']], $slideshowImages ?? [])) !!};
    const defaultFacilities = {!! json_encode($defaultFacilities ?? []) !!};
    window._kioskCollections = (dbCollections && dbCollections.length > 0) ? dbCollections : (slideshowFacilities.length > 0 ? slideshowFacilities : defaultFacilities);
</script>
@endpush
@endsection
