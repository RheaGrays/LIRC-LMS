
    <!-- CINEMATIC ANIMATED SPLASH SCREEN OVERLAY (MATCHING OFFICIAL DESIGN MOCKUP) -->
    <div x-show="showSplash" 
         x-cloak
         style="display: none !important;"
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
        <div class="absolute top-0 right-0 z-0 pointer-events-none w-[300px] md:w-[420px] h-[200px] md:h-[280px]">
            <svg viewBox="0 0 450 300" preserveAspectRatio="none" fill="none" class="w-full h-full">
                <path d="M450 0 L 150 0 C 260 80, 370 190, 450 300 Z" fill="#7f1d1d"/>
                <path d="M450 0 L 180 0 C 280 90, 380 200, 450 300 Z" fill="#991b1b" opacity="0.6"/>
                <path d="M140 0 C 250 80, 360 185, 450 290" stroke="#d97706" stroke-width="6" fill="none"/>
            </svg>
        </div>

        <!-- Bottom-Left Crimson & Gold Curved Ribbon Graphic (PERFECT CORNER WAVE) -->
        <div class="absolute bottom-0 left-0 z-0 pointer-events-none w-[300px] md:w-[420px] h-[200px] md:h-[280px]">
            <svg viewBox="0 0 450 300" preserveAspectRatio="none" fill="none" class="w-full h-full">
                <path d="M0 300 L 0 60 C 120 180, 260 250, 450 300 Z" fill="#7f1d1d"/>
                <path d="M0 300 L 0 90 C 140 195, 270 255, 450 300 Z" fill="#991b1b" opacity="0.6"/>
                <path d="M0 50 C 120 175, 260 248, 440 300" stroke="#d97706" stroke-width="6" fill="none"/>
            </svg>
        </div>

        <!-- Dot Matrix Accent (Top Right) -->
        <div class="absolute top-10 right-72 z-0 pointer-events-none opacity-20 hidden lg:block">
            <div class="grid grid-cols-6 gap-2">
                <template x-for="i in 24">
                    <div class="w-1.5 h-1.5 rounded-full bg-[#7f1d1d]"></div>
                </template>
            </div>
        </div>

        <!-- Dot Matrix Accent (Bottom Right) -->
        <div class="absolute bottom-10 right-16 z-0 pointer-events-none opacity-20 hidden lg:block">
            <div class="grid grid-cols-6 gap-2">
                <template x-for="i in 24">
                    <div class="w-1.5 h-1.5 rounded-full bg-[#7f1d1d]"></div>
                </template>
            </div>
        </div>

        <!-- Top Navigation / Branding Bar -->
        <div class="relative z-20 w-full flex justify-between items-center">
            <div class="flex items-center gap-2.5 bg-white/70 backdrop-blur-md px-4 py-2 rounded-full border border-stone-200/80 shadow-xs">
                <svg class="w-4 h-4 text-[#7f1d1d]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span class="text-xs font-bold tracking-wider text-[#1e293b] font-['Inter'] uppercase">LIRC KIOSK OS V1.0</span>
            </div>

            <div class="flex items-center gap-3 text-right bg-white/70 backdrop-blur-md px-4 py-2 rounded-full border border-stone-200/80 shadow-xs">
                <div class="flex flex-col items-end">
                    <span class="text-xs font-black tracking-wider text-[#7f1d1d] font-['Fraunces'] uppercase leading-none">COR JESU COLLEGE</span>
                    <span class="text-[9px] font-bold tracking-widest text-amber-600 font-['Inter'] uppercase mt-0.5">COMMUNITY | APOSTLESHIP | EXCELLENCE</span>
                </div>
                <div class="w-7 h-7 rounded-full bg-white p-0.5 shadow-sm border border-stone-200 shrink-0">
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
