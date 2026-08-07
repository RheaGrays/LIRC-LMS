@extends('layouts.kiosk')

@section('title', ' | Kiosk')

@section('kiosk_content')
<div x-data="kioskApp()" class="w-full h-screen flex flex-col relative z-10 bg-transparent cursor-pointer select-none overflow-hidden" 
     @mousemove="handleActivity()" @keydown="handleKey($event)" @click="activate()">

    <!-- CINEMATIC ANIMATED SPLASH SCREEN OVERLAY (MATCHING OFFICIAL DESIGN MOCKUP) -->
    <div x-show="showSplash" 
         x-transition:leave="transition ease-out duration-700"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-98 pointer-events-none"
         class="fixed inset-0 z-[9999] bg-[#fcf9f2] flex flex-col justify-between p-8 md:p-12 overflow-hidden text-[#1e293b] font-['Inter'] select-none">
        
        <!-- Background Campus Watermark & Seals -->
        <div class="absolute inset-0 z-0 opacity-15 pointer-events-none bg-cover bg-right-bottom mix-blend-multiply"
             style="background-image: url('/bg.jpg');"></div>
        
        <!-- Left CJC Seal Watermark -->
        <div class="absolute -left-20 top-1/2 -translate-y-1/2 z-0 opacity-10 pointer-events-none w-[550px] h-[550px]">
            <img src="/CorJesu Logo.png" class="w-full h-full object-contain filter grayscale">
        </div>

        <!-- Top-Right Crimson & Gold Curved Ribbon Graphic -->
        <div class="absolute -top-16 -right-16 z-0 pointer-events-none w-[450px] h-[350px]">
            <svg viewBox="0 0 400 300" fill="none" class="w-full h-full">
                <path d="M100 0 C 250 80, 320 180, 400 300 L 400 0 Z" fill="#7f1d1d"/>
                <path d="M140 0 C 270 90, 340 200, 400 330 L 400 0 Z" fill="#991b1b" opacity="0.8"/>
                <path d="M80 0 C 230 70, 300 170, 400 280" stroke="#d97706" stroke-width="6" fill="none"/>
            </svg>
        </div>

        <!-- Bottom-Left Crimson & Gold Curved Ribbon Graphic -->
        <div class="absolute -bottom-16 -left-16 z-0 pointer-events-none w-[450px] h-[350px]">
            <svg viewBox="0 0 400 300" fill="none" class="w-full h-full">
                <path d="M0 300 C 150 220, 250 100, 320 0 L 0 0 Z" fill="#7f1d1d"/>
                <path d="M0 300 C 130 200, 230 80, 300 0 L 0 0 Z" fill="#991b1b" opacity="0.8"/>
                <path d="M0 300 C 170 230, 270 120, 340 0" stroke="#d97706" stroke-width="6" fill="none"/>
            </svg>
        </div>

        <!-- Dot Matrix Accent (Top Right) -->
        <div class="absolute top-12 right-64 z-0 pointer-events-none opacity-20 hidden md:block">
            <div class="grid grid-cols-6 gap-2">
                <template x-for="i in 24">
                    <div class="w-1.5 h-1.5 rounded-full bg-[#7f1d1d]"></div>
                </template>
            </div>
        </div>

        <!-- Dot Matrix Accent (Bottom Right) -->
        <div class="absolute bottom-12 right-16 z-0 pointer-events-none opacity-20 hidden md:block">
            <div class="grid grid-cols-6 gap-2">
                <template x-for="i in 24">
                    <div class="w-1.5 h-1.5 rounded-full bg-[#7f1d1d]"></div>
                </template>
            </div>
        </div>

        <!-- Top Navigation / Branding Bar -->
        <div class="relative z-10 w-full flex justify-between items-center">
            <div class="flex items-center gap-2.5 bg-white/60 backdrop-blur-sm px-3.5 py-1.5 rounded-full border border-stone-200/80 shadow-xs">
                <svg class="w-4 h-4 text-[#7f1d1d]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span class="text-xs font-bold tracking-wider text-[#1e293b] font-['Inter'] uppercase">LIRC KIOSK OS V1.0</span>
            </div>

            <div class="flex items-center gap-3 text-right">
                <div class="flex flex-col items-end">
                    <span class="text-xs font-black tracking-wider text-[#7f1d1d] font-['Fraunces'] uppercase leading-none">COR JESU COLLEGE</span>
                    <span class="text-[9px] font-bold tracking-widest text-amber-600 font-['Inter'] uppercase mt-0.5">COMMUNITY | APOSTLESHIP | EXCELLENCE</span>
                </div>
                <div class="w-8 h-8 rounded-full bg-white p-0.5 shadow-sm border border-stone-200 shrink-0">
                    <img src="/CorJesu Logo.png" alt="CJC Shield" class="w-full h-full object-contain">
                </div>
            </div>
        </div>

        <!-- Center Emblem, Title & Subtitles -->
        <div class="relative z-10 flex flex-col items-center text-center my-auto px-4">
            <!-- Sunburst Radiance Glow Emblem -->
            <div class="relative mb-6">
                <!-- Radiant Sunburst Glow -->
                <div class="absolute -inset-8 rounded-full bg-gradient-to-r from-amber-400/30 via-red-500/20 to-amber-400/30 blur-xl animate-pulse"></div>
                <!-- Outer Gold Ring -->
                <div class="relative w-36 h-36 md:w-44 md:h-44 rounded-full p-2 bg-gradient-to-b from-amber-300 via-amber-500 to-amber-700 shadow-2xl flex items-center justify-center">
                    <div class="w-full h-full rounded-full bg-white p-2 shadow-inner flex items-center justify-center">
                        <img src="/CorJesu Logo.png" alt="CJC Crest" class="w-full h-full object-contain filter drop-shadow-md">
                    </div>
                </div>
            </div>

            <!-- Main Title: COR JESU COLLEGE -->
            <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight font-['Fraunces'] text-[#7f1d1d] m-0 uppercase drop-shadow-xs">
                COR JESU COLLEGE
            </h1>

            <!-- Elegant Divider with Center Diamond -->
            <div class="flex items-center justify-center gap-3 w-full max-w-lg my-3">
                <div class="h-px bg-gradient-to-r from-transparent via-stone-300 to-amber-500 flex-1"></div>
                <div class="w-2 h-2 rotate-45 bg-amber-500 shrink-0"></div>
                <div class="h-px bg-gradient-to-l from-transparent via-stone-300 to-amber-500 flex-1"></div>
            </div>

            <!-- Subheader: LIBRARY INFORMATION & RESOURCE CENTER -->
            <h2 class="text-xs md:text-sm font-extrabold tracking-[0.2em] text-[#7f1d1d] uppercase m-0 font-['Inter']">
                LIBRARY INFORMATION & RESOURCE CENTER
            </h2>
            <p class="text-[11px] md:text-xs text-slate-500 font-medium tracking-wide mt-1 mb-6 font-['Inter']">
                Library Entrance Monitoring & Attendance System
            </p>

            <!-- Motto Badge -->
            <div class="flex items-center gap-2 bg-white/80 border border-amber-200/80 px-4 py-1.5 rounded-full shadow-xs">
                <svg class="w-3.5 h-3.5 text-[#7f1d1d]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span class="text-[10px] font-bold tracking-widest text-[#7f1d1d] font-['Inter'] uppercase">❖ COMMUNITY. APOSTLESHIP. EXCELLENCE. ❖</span>
            </div>
        </div>

        <!-- Bottom Loading Bar & Status Indicator -->
        <div class="relative z-10 w-full max-w-md mx-auto flex flex-col items-center gap-2 pb-4">
            <div class="w-full flex items-center gap-3">
                <div class="flex-1 h-3 bg-stone-200/80 rounded-full overflow-hidden p-0.5 border border-stone-300/60 shadow-inner">
                    <div class="h-full bg-gradient-to-r from-[#7f1d1d] via-red-600 to-amber-500 rounded-full transition-all duration-150 ease-out shadow-sm"
                         :style="`width: ${splashProgress}%`"></div>
                </div>
                <span class="font-mono text-xs font-black text-[#7f1d1d] shrink-0" x-text="`${splashProgress}%`">0%</span>
            </div>
            <p class="text-[11px] font-semibold text-slate-600 font-['Inter'] m-0" x-text="splashStatus">
                Welcome to CJC Library!
            </p>
        </div>
    </div>

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
                        <img src="/CorJesu Logo.png" alt="CJC" class="w-full h-full object-cover">
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
            <main class="flex-1 flex items-center justify-center p-6 pb-16">
                <div class="flex flex-col items-center w-full max-w-[900px] transition-all duration-300">
                    <div class="fade-in-up bg-white border border-gray-100 rounded-[28px] shadow-[0_20px_50px_rgba(0,0,0,0.06)] w-full relative overflow-hidden flex flex-col min-h-[480px]">
                        
                        <div class="px-10 pt-10 pb-20 relative z-10" x-show="!result && !isProcessing">
                            <!-- Header Area -->
                            <div class="flex justify-between items-center mb-8">
                                <div class="flex items-center gap-4">
                                    <!-- Red Header Icon -->
                                    <div style="color: #dc2626;" class="shrink-0 flex items-center justify-center">
                                        <svg class="w-14 h-14" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 14V9a3 3 0 0 1 3-3h5M34 6h5a3 3 0 0 1 3 3v5M6 34v5a3 3 0 0 0 3 3h5M34 42h5a3 3 0 0 0 3-3v-5" stroke-width="3"/>
                                            <rect x="12" y="14" width="24" height="20" rx="3" fill="#fef2f2" stroke="currentColor" stroke-width="2" />
                                            <circle cx="19" cy="24" r="3.5" fill="currentColor" stroke="none" />
                                            <path d="M26 21h7M26 27h5" stroke-width="2.5" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="font-bold text-gray-900 text-[30px] m-0 leading-tight tracking-tight">Scan ID</h1>
                                        <p class="text-gray-500 text-[15px] font-medium m-0 mt-0.5">Present your Student ID to check in or out.</p>
                                    </div>
                                </div>
                                <!-- CorJesu Logo -->
                                <div class="shrink-0">
                                    <img src="/CorJesu Logo.png" alt="Cor Jesu Logo" class="h-20 w-auto object-contain">
                                </div>
                            </div>

                            <!-- Tabs Bar with Vertical Dividers -->
                            <div class="flex items-center gap-6 border-b border-gray-100 pb-0 mb-8 relative">
                                <button class="pb-3 text-[15px] flex items-center gap-2.5 transition-all relative font-bold"
                                    :class="tab === 'scan' ? 'text-[#dc2626]' : 'text-gray-400 hover:text-gray-600'"
                                    @click="tab = 'scan'; handleActivity()">
                                    <svg class="w-5 h-5" :style="tab === 'scan' ? 'color: #dc2626;' : 'color: #9ca3af;'" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M3 4h3v3H3V4zm0 7h3v3H3v-3zm0 7h3v3H3v-3zm7-14h3v3h-3V4zm0 7h3v3h-3v-3zm0 7h3v3h-3v-3zm7-14h3v3h-3V4zm0 7h3v3h-3v-3zm0 7h3v3h-3v-3z"/>
                                    </svg>
                                    <span>Barcode / QR</span>
                                    <div class="absolute bottom-0 left-0 right-0 h-[3px] bg-[#dc2626] rounded-t-full" x-show="tab === 'scan'"></div>
                                </button>

                                <div class="w-px h-5 bg-gray-200 shrink-0 mb-3"></div>

                                <button class="pb-3 text-[15px] flex items-center gap-2.5 transition-all relative font-semibold"
                                    :class="tab === 'webcam' ? 'text-[#dc2626]' : 'text-gray-400 hover:text-gray-600'"
                                    @click="tab = 'webcam'; handleActivity()">
                                    <svg class="w-5 h-5" :style="tab === 'webcam' ? 'color: #dc2626;' : 'color: #9ca3af;'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="7" width="18" height="12" rx="2"/><circle cx="12" cy="13" r="3"/><path d="M8 7V5h8v2"/>
                                    </svg>
                                    <span>Webcam</span>
                                    <div class="absolute bottom-0 left-0 right-0 h-[3px] bg-[#dc2626] rounded-t-full" x-show="tab === 'webcam'" style="display: none;"></div>
                                </button>

                                <div class="w-px h-5 bg-gray-200 shrink-0 mb-3"></div>

                                <button class="pb-3 text-[15px] flex items-center gap-2.5 transition-all relative font-semibold"
                                    :class="tab === 'manual' ? 'text-[#dc2626]' : 'text-gray-400 hover:text-gray-600'"
                                    @click="tab = 'manual'; handleActivity()">
                                    <svg class="w-5 h-5" :style="tab === 'manual' ? 'color: #dc2626;' : 'color: #9ca3af;'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                    <span>Manual Entry</span>
                                    <div class="absolute bottom-0 left-0 right-0 h-[3px] bg-[#dc2626] rounded-t-full" x-show="tab === 'manual'" style="display: none;"></div>
                                </button>
                            </div>

                            <!-- Tab 1: Barcode / QR (Exact match of final mockup) -->
                            <div x-show="tab === 'scan'" class="flex flex-col gap-6 animate-slide-in w-full py-2">
                                <label class="flex flex-col gap-2.5">
                                    <span class="text-[11px] font-bold tracking-[0.1em] uppercase text-gray-500">SCAN OR TYPE ID</span>
                                    
                                    <!-- Input Container with soft red border and dot matrix background -->
                                    <div style="border: 1.5px solid #f87171; background: linear-gradient(90deg, #fff5f5 0%, #ffffff 50%), radial-gradient(#fca5a5 1px, transparent 1px); background-size: 100% 100%, 10px 10px;" 
                                         class="w-full py-3.5 px-5 rounded-2xl flex items-center shadow-sm relative overflow-hidden">
                                        
                                        <!-- Red Scan Icon -->
                                        <div style="color: #dc2626;" class="shrink-0 flex items-center justify-center">
                                            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M4 7V5a2 2 0 0 1 2-2h2M18 3h2a2 2 0 0 1 2 2v2M4 17v2a2 2 0 0 0 2 2h2M18 21h2a2 2 0 0 0 2-2v-2"/>
                                                <path d="M9 12h6" stroke-width="2"/>
                                            </svg>
                                        </div>

                                        <!-- Divider Line -->
                                        <div class="w-px h-7 bg-gray-200 mx-4 shrink-0"></div>

                                        <!-- Input Field -->
                                        <input type="text" x-model="manualId" x-ref="barcodeInput" 
                                            @keydown.enter.prevent="if(manualId.trim()) submitManual()"
                                            @input="handleActivity()"
                                            placeholder="Scan ID here..." 
                                            style="outline: none !important; border: none !important; box-shadow: none !important;"
                                            class="w-full bg-transparent border-none text-[18px] font-sans text-gray-800 outline-none focus:outline-none focus:ring-0 focus:border-none shadow-none placeholder:text-gray-400">
                                    </div>
                                </label>

                                <p class="text-[14px] text-gray-500 text-center m-0 mt-2">
                                    Press <kbd style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;" class="rounded-md px-3 py-1 text-[13px] font-bold mx-1.5 shadow-sm">Enter</kbd> to submit
                                </p>
                            </div>

                            <!-- Tab 2: Webcam -->
                            <div x-show="tab === 'webcam'" class="flex flex-col gap-4 items-center animate-slide-in w-full max-w-[480px] mx-auto pt-2" style="display: none;">
                                <div class="w-full aspect-[16/10] bg-black rounded-2xl relative overflow-hidden flex items-center justify-center border-4 border-white shadow-lg">
                                    <!-- Reticle Corner Brackets -->
                                    <div style="color: #dc2626;" class="absolute inset-4 pointer-events-none z-20">
                                        <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none" fill="none" stroke="currentColor" stroke-width="3">
                                            <path d="M8 20v-12h12M92 20v-12h-12M8 80v12h12M92 80v12h-12" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>

                                    <video id="kiosk-video" class="w-full h-full object-cover z-10" :class="isCameraActive ? 'block' : 'hidden'"></video>
                                    
                                    <!-- Scanline -->
                                    <template x-if="isCameraActive">
                                        <div style="background: #dc2626; box-shadow: 0 0 10px #dc2626;" class="absolute left-0 right-0 h-[2px] z-30 animate-[scanline_2s_linear_infinite]"></div>
                                    </template>

                                    <!-- Placeholder -->
                                    <template x-if="!isCameraActive">
                                        <div class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-black">
                                            <svg class="w-12 h-12 text-white/20 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path d="M3 9a2 2 0 012-2h3l2-2h4l2 2h3a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                                <circle cx="12" cy="13" r="3"/>
                                            </svg>
                                            <div class="w-7 h-7 border-2 border-white/20 border-t-white rounded-full animate-spin"></div>
                                        </div>
                                    </template>
                                </div>
                                <div class="text-center">
                                    <h4 class="font-bold text-gray-900 text-[16px] m-0 mb-1">Ready to scan</h4>
                                    <p class="text-[13px] text-gray-500 m-0">Point your camera at the barcode or QR code on your Student ID.</p>
                                </div>
                            </div>

                            <!-- Tab 3: Manual Entry -->
                            <div x-show="tab === 'manual'" class="flex flex-col gap-4 animate-slide-in relative w-full max-w-[500px] mx-auto pt-2" style="display: none;">
                                <label class="flex flex-col gap-2">
                                    <span class="text-[11px] font-bold tracking-[0.08em] uppercase text-gray-400">Student ID or Name</span>
                                    <div class="relative w-full flex items-center">
                                        <div style="color: #dc2626;" class="absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none z-10 flex items-center justify-center">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                                            </svg>
                                        </div>
                                        <input type="text" x-model="manualId" x-ref="manualInput" 
                                            @keydown.enter.prevent="if(manualId.trim()) submitManual()"
                                            @input="handleActivity()"
                                            placeholder="Enter Student ID or Name" 
                                            class="w-full py-3.5 pl-12 pr-4 text-[15px] text-gray-800 bg-white border border-gray-200 rounded-xl outline-none focus:border-red-500 focus:ring-2 focus:ring-red-100 transition-all font-sans">
                                    </div>
                                </label>
                                
                                <!-- Autocomplete Dropdown -->
                                <div x-show="showSuggestions" @click.outside="showSuggestions = false" style="display: none;"
                                     class="absolute top-[75px] left-0 right-0 bg-white border border-gray-200 rounded-xl shadow-lg z-[100] overflow-hidden">
                                    <template x-for="item in suggestions" :key="item.id">
                                        <div @click="selectSuggestion(item.id)"
                                             class="flex items-center gap-3 p-3 hover:bg-gray-50 cursor-pointer transition-colors border-b border-gray-100 last:border-0">
                                            <img :src="item.photo" class="w-9 h-9 rounded-full object-cover bg-gray-100">
                                            <div class="flex flex-col">
                                                <span class="text-[14px] font-bold text-gray-900" x-text="item.name"></span>
                                                <span class="text-[11px] font-semibold text-gray-500" x-text="item.id"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <button @click="submitManual()"
                                    style="background-color: #dc2626;"
                                    class="w-full py-3.5 text-white border-none rounded-xl text-[15px] font-bold cursor-pointer transition-all hover:opacity-90 shadow-md flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span>Verify & Enter</span>
                                </button>
                                
                                <div style="background-color: #fef2f2; border: 1px solid #fee2e2; color: #dc2626;" class="w-full px-4 py-3 rounded-lg flex items-center gap-2.5 text-[13px] font-medium">
                                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Make sure the information is correct before submitting.</span>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Red Swoosh Wave Graphics -->
                        <div class="absolute bottom-0 inset-x-0 overflow-hidden pointer-events-none rounded-b-[28px] h-16 z-0">
                            <svg class="absolute bottom-0 w-full h-16" viewBox="0 0 1200 120" preserveAspectRatio="none">
                                <path d="M0,40 C300,110 600,10 1200,60 L1200,120 L0,120 Z" fill="#fca5a5" opacity="0.3"></path>
                                <path d="M0,60 C400,120 800,20 1200,80 L1200,120 L0,120 Z" fill="#dc2626"></path>
                            </svg>
                        </div>
                            
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


