@extends('layouts.kiosk')

@section('title', ' | Kiosk')

@section('kiosk_content')
<div x-data="kioskApp()" class="w-full min-h-screen flex flex-col relative z-10 bg-[var(--bg-cream)] hero-pattern cursor-pointer select-none overflow-hidden" 
     @mousemove="handleActivity()" @keydown="handleKey($event)" @click="activate()">
    
    <!-- IDLE STATE -->
    <template x-if="state === 'idle'">
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <header class="fade-in-up flex items-center justify-between px-12 py-7">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-full overflow-hidden border border-[var(--border-warm)] bg-white shrink-0">
                        <img src="/cjc-logo.jpeg" alt="CJC Logo" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <p class="m-0 text-[13px] font-semibold tracking-[0.06em] uppercase text-[var(--cjc-navy)] font-['Inter'] leading-tight">
                            Cor Jesu College
                        </p>
                        <p class="m-0 text-[11px] text-[var(--text-muted)] font-['Inter'] leading-[1.4]">
                            Learning Information Resource Center
                        </p>
                    </div>
                </div>

                <div class="text-right">
                    <p class="m-0 text-[11px] text-[var(--text-muted)] font-['Inter'] leading-[1.6]">
                        Library Entrance<br/>Monitoring System
                    </p>
                </div>
            </header>

            <!-- Center -->
            <main class="flex-1 flex flex-col items-center justify-center px-12 gap-9">
                <!-- Headline -->
                <div class="fade-in-up delay-1 text-center">
                    <h1 class="font-['Fraunces'] text-[clamp(72px,11vw,120px)] font-[800] text-[var(--cjc-navy)] m-0 leading-[0.9] tracking-[-0.02em]">
                        Welcome to <span class="text-[var(--cjc-red)]">LIRC</span>, CorJesian!
                    </h1>
                    <div class="w-16 h-[3px] bg-[var(--cjc-red)] rounded-[2px] mx-auto my-4"></div>
                    <p class="font-['Inter'] text-[clamp(15px,1.8vw,19px)] text-[var(--text-muted)] m-0 font-normal">
                        Scan your ID to enter the library.
                    </p>
                </div>

                <!-- Clock -->
                <div class="fade-in-up delay-2 text-center">
                    <div class="font-['Fraunces'] text-[clamp(60px,9vw,100px)] font-bold text-[var(--cjc-navy)] leading-none tracking-[-0.03em]">
                        <span x-text="clockHm">--:--</span><span class="text-[0.38em] text-[var(--cjc-red)] font-semibold ml-0.5 align-middle">:<span x-text="clockSec">--</span></span>
                    </div>
                    <div class="font-['Inter'] text-[13px] text-[var(--text-muted)] mt-2 tracking-[0.01em]" x-text="clockDate">
                        Loading...
                    </div>
                </div>

                <!-- CTA -->
                <div class="fade-in-up delay-3 flex items-center gap-2.5">
                    <span class="w-2 h-2 rounded-full bg-[var(--cjc-red)] block"></span>
                    <span class="font-['Inter'] text-[14px] font-semibold text-[var(--cjc-red)] tracking-[0.01em]">
                        Present your ID to begin
                    </span>
                </div>
            </main>

            <!-- Footer -->
            <footer class="fade-in-up delay-4 flex items-end justify-between px-12 py-7">
                <!-- Occupancy Widget (Removed per request) -->
                <div></div>

                <div class="text-right">
                    <p class="m-0 mb-2 text-[11px] text-[var(--text-subtle)] font-['Inter'] leading-[1.7]">
                        Touch anywhere to check in<br/>
                        <span class="opacity-60">LEMS · System v1.0</span>
                    </p>
                    <div class="flex items-center gap-2 mt-2 justify-end">
                        <a href="{{ route('register.index') }}" @click.stop class="inline-flex items-center gap-1.5 text-[11px] font-medium text-[var(--text-subtle)] font-['Inter'] no-underline px-2.5 py-1 border border-[var(--border-warm)] rounded-[var(--radius-sm)] bg-white/60 hover:text-[var(--cjc-navy)] hover:border-[var(--cjc-navy)] transition-colors">
                            <svg width="11" height="11" viewBox="0 0 11 11" fill="none">
                                <circle cx="5.5" cy="4" r="2" stroke="currentColor" stroke-width="1.2"/>
                                <path d="M2.5 9.5a3 3 0 016 0" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                            </svg>
                            Register
                        </a>
                        <a href="{{ route('admin.login') }}" @click.stop class="inline-flex items-center gap-1.5 text-[11px] font-medium text-[var(--text-subtle)] font-['Inter'] no-underline px-2.5 py-1 border border-[var(--border-warm)] rounded-[var(--radius-sm)] bg-white/60 hover:text-[var(--cjc-navy)] hover:border-[var(--cjc-navy)] transition-colors">
                            <svg width="11" height="11" viewBox="0 0 11 11" fill="none">
                                <rect x="1" y="1" width="9" height="9" rx="1.5" stroke="currentColor" stroke-width="1.2"/>
                                <path d="M3.5 5.5h4M5.5 3.5v4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                            </svg>
                            Admin
                        </a>
                    </div>
                </div>
            </footer>
        </div>
    </template>

    <!-- SCANNER STATE -->
    <template x-if="state === 'active'">
        <div class="flex-1 flex flex-col cursor-default" @click.stop>
            <!-- Header -->
            <header class="px-10 py-4 bg-white border-b border-[var(--border-warm)] flex items-center justify-between shadow-sm relative z-10">
                <div class="flex items-center gap-3">
                    <button @click.stop="deactivate()" class="flex items-center gap-1.5 px-3 py-1.5 bg-transparent border border-[var(--border-warm)] rounded-[var(--radius-md)] text-[12px] font-medium text-[var(--text-muted)] font-['Inter'] hover:border-[var(--cjc-navy)] hover:text-[var(--cjc-navy)] transition-colors">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                            <path d="M11 7H3M7 3L3 7l4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Back
                    </button>

                    <div class="w-px h-7 bg-[var(--border-warm)] mx-1"></div>

                    <div class="w-9 h-9 rounded-full overflow-hidden border border-[var(--border-warm)] bg-white shrink-0">
                        <img src="/cjc-logo.jpeg" alt="CJC" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <p class="m-0 text-[12px] font-semibold tracking-[0.06em] uppercase text-[var(--cjc-navy)] font-['Inter'] leading-[1.2]">
                            Cor Jesu College
                        </p>
                        <p class="m-0 text-[11px] text-[var(--text-muted)] font-['Inter']">
                            Library Entrance Monitoring System
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <x-kiosk.offline-sync-status />
                    
                    <span class="font-['JetBrains_Mono'] text-[14px] font-semibold text-[var(--cjc-navy)] tracking-[0.04em]" x-text="clockHm">
                        --:--
                    </span>

                    <a href="{{ route('admin.login') }}" @click.stop class="inline-flex items-center gap-1.5 text-[12px] font-medium text-[var(--text-muted)] font-['Inter'] no-underline px-3 py-1.5 border border-[var(--border-warm)] rounded-[var(--radius-md)] bg-transparent hover:text-[var(--cjc-navy)] hover:border-[var(--cjc-navy)] transition-colors ml-2">
                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                            <rect x="1.5" y="1.5" width="10" height="10" rx="2" stroke="currentColor" stroke-width="1.3" />
                            <path d="M4 6.5h5M6.5 4v5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                        </svg>
                        Admin
                    </a>
                </div>
            </header>

            <!-- Main Scanner Box -->
            <main class="flex-1 flex items-center justify-center p-10 pb-20">
                <div class="flex flex-col items-center w-full max-w-[500px]">
                    <div class="fade-in-up bg-white border border-[var(--border-light)] rounded-[var(--radius-xl)] shadow-[var(--shadow-lg)] w-full relative overflow-hidden">
                        
                        <div class="px-8 pt-7">
                            <h1 class="font-['Fraunces'] text-[22px] font-bold text-[var(--cjc-navy)] m-0 mb-1">Scan ID</h1>
                            <p class="text-[13px] text-[var(--text-muted)] font-['Inter'] m-0 mb-6">Present your Student ID to check in or out.</p>

                            <!-- Tabs -->
                            <div class="flex border-b border-[var(--border-light)] gap-6 mb-7">
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
                            
                            <!-- Tab: Scan (Hidden Input + Ready state) -->
                            <div x-show="tab === 'scan'" class="flex flex-col gap-4 animate-slide-in">
                                <label class="flex flex-col gap-1.5">
                                    <span class="text-[11px] font-semibold tracking-[0.07em] uppercase text-[var(--text-muted)] font-['Inter']">Scan or type ID</span>
                                    <input type="text" x-model="manualId" x-ref="barcodeInput" 
                                        @keydown.enter.prevent="if(manualId.trim()) submitManual()"
                                        @input="handleActivity()"
                                        placeholder="Scan ID here..." 
                                        class="w-full p-3 font-['JetBrains_Mono'] text-[18px] font-medium tracking-[0.06em] text-center bg-white border border-[var(--border-light)] rounded-[var(--radius-md)] text-[var(--cjc-navy)] outline-none focus:border-[var(--cjc-navy)] focus:shadow-[0_0_0_3px_rgba(15,39,68,0.08)] transition-all">
                                </label>
                                <p class="text-[11px] text-[var(--text-subtle)] font-['Inter'] text-center m-0">
                                    Press <kbd class="bg-[var(--bg-cream-2)] border border-[var(--border-warm)] rounded-[4px] px-[5px] py-[1px] text-[10px] font-['JetBrains_Mono']">Enter</kbd> to submit
                                </p>
                            </div>

                            <!-- Tab: Webcam -->
                            <div x-show="tab === 'webcam'" class="flex flex-col gap-3.5 items-center animate-slide-in">
                                <div class="w-full max-w-[340px] aspect-[4/3] bg-[#0a0a0a] rounded-[var(--radius-lg)] relative overflow-hidden border border-[var(--border-light)] flex items-center justify-center">
                                    <video id="kiosk-video" class="w-full h-full object-cover" :class="isCameraActive ? 'block' : 'hidden'"></video>
                                    
                                    <!-- Scanline -->
                                    <template x-if="isCameraActive">
                                        <div class="absolute left-0 right-0 h-[2px] bg-gradient-to-r from-transparent via-[var(--cjc-red)] to-transparent z-10 animate-[scanline_2s_linear_infinite]"></div>
                                    </template>

                                    <!-- Placeholder -->
                                    <template x-if="!isCameraActive">
                                        <div class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-black/70 p-4">
                                            <div class="w-7 h-7 border-2 border-white/15 border-t-white/70 rounded-full animate-[spin_0.8s_linear_infinite] mb-2.5"></div>
                                            <span class="text-white/85 text-[12px] font-['Inter'] font-semibold">Starting camera...</span>
                                        </div>
                                    </template>
                                </div>
                                <p class="text-[12px] text-[var(--text-muted)] font-['Inter'] text-center m-0">Point your camera at the barcode or QR code on your Student ID.</p>
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
                                    <span class="text-[11px] text-[var(--text-subtle)] font-['Inter']">Enter your Student ID or full name</span>
                                </label>
                                <button @click="submitManual()" :disabled="!manualId.trim() || isProcessing"
                                    class="w-full p-3 bg-[var(--cjc-red)] text-white border-none rounded-[var(--radius-md)] text-[14px] font-semibold font-['Inter'] cursor-pointer transition-opacity disabled:opacity-50 disabled:cursor-not-allowed hover:opacity-90">
                                    <span x-show="!isProcessing">Verify & Enter</span>
                                    <span x-show="isProcessing">Processing...</span>
                                </button>
                            </div>

                            <!-- Result Overlay / Card inside the box -->
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
            0% { top: 10%; }
            50% { top: 85%; }
            100% { top: 10%; }
        }
        /* Force hero pattern background here to ensure it overrides everything */
        .hero-pattern::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: url('/bg.jpg') !important;
            background-size: cover !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
            opacity: 0.30 !important;
            pointer-events: none !important;
            z-index: 0 !important;
        }
        .hero-pattern > * {
            position: relative;
            z-index: 1;
        }
    </style>
</div>
@endsection


