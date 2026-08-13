<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>About LEMS | Library Entrance & Attendance Monitoring System</title>
    <link rel="icon" type="image/png" href="/cjc-logo.jpeg">
    
    <!-- Google Fonts -->


    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        :root {
            --cjc-red: #c41e2a;
            --cjc-navy: #0f2744;
            --bg-light: #f8fafc;
        }
        body {
            font-family: 'Montserrat', sans-serif;
        }
        /* Custom gradient mask for the hero image to perfectly match the soft blend */
        .hero-mask {
            -webkit-mask-image: linear-gradient(to right, transparent, black 25%);
            mask-image: linear-gradient(to right, transparent, black 25%);
        }
    </style>
</head>
<body class="antialiased bg-[#f8f9fa] text-[var(--cjc-navy)] min-h-screen flex flex-col selection:bg-[var(--cjc-red)] selection:text-white" x-data="{ activeTab: 'system' }">

    <!-- Top Navigation Header (Spacious Layout) -->
    <header class="sticky top-0 z-50 bg-white shadow-sm py-5 px-4 md:px-8 lg:px-12 xl:px-20 flex justify-center">
        <div class="w-full flex items-center justify-between" style="max-width: 1400px;">
            <!-- Left Side: Menu, Logo, Title -->
            <div class="flex items-center gap-5">
                <!-- Back Button -->
                <a href="{{ route('kiosk.index') }}" title="Go Back to Kiosk" class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-600 hover:text-[var(--cjc-navy)] transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>

                <!-- Logo & Text -->
                <div class="flex items-center gap-4">
                    <img src="/cjc-logo.jpeg" alt="CJC Logo" class="w-12 h-12 rounded-full object-cover shadow-sm">
                    <div class="hidden sm:block">
                        <h1 class="text-sm font-black tracking-widest uppercase text-[var(--cjc-navy)] leading-none mb-1">
                            COR JESU COLLEGE
                        </h1>
                        <p class="text-[10px] text-gray-500 font-bold tracking-wide">
                            Learning Information Resource Center (LIRC)
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Right Side: Empty for spacing -->
            <div></div>
        </div>
    </header>

    <!-- Exact Hero Banner Layout -->
    <style>
        .hero-bg-custom { width: 100%; }
        .hero-gradient-custom { display: none; }
        .hero-mobile-mask { display: block; }
        @media (min-width: 1024px) {
            .hero-bg-custom { width: 66.666667%; }
            .hero-gradient-custom { display: block; }
            .hero-mobile-mask { display: none; }
        }
    </style>
    <section class="relative w-full bg-white z-0" style="padding-top: 100px; padding-bottom: 120px;">
        
        <!-- Background Image with Soft Masking (BULLETPROOF) -->
        <div class="hero-bg-custom" style="position: absolute; right: 0; top: 0; bottom: 0; z-index: -1;">
            <img src="/bg.jpg" alt="Campus Background" style="width: 100%; height: 100%; object-fit: cover; object-position: center right;">
            
            <!-- Extra Overlay for Mobile (White tint) -->
            <div class="hero-mobile-mask" style="position: absolute; top: 0; bottom: 0; left: 0; right: 0; background: rgba(255,255,255,0.85);"></div>
            
            <!-- Desktop Gradient (Fades the left edge of the image into the white background) -->
            <div class="hero-gradient-custom" style="position: absolute; top: 0; bottom: 0; left: 0; right: 0; background: linear-gradient(to right, rgba(255,255,255,1) 0%, rgba(255,255,255,0) 40%); pointer-events: none;"></div>
        </div>

        <!-- Hero Content Container -->
        <div class="mx-auto px-4 md:px-8 lg:px-12 xl:px-20 relative z-10" style="max-width: 1400px;">
            <div class="max-w-2xl">
                <!-- Red Pill Label -->
                <div class="inline-flex items-center px-4 py-1.5 rounded-full font-extrabold uppercase tracking-widest bg-red-50 text-[var(--cjc-red)] mb-6 shadow-sm border border-red-100" style="font-size: 11px; line-height: 1;">
                    ENTERPRISE LIBRARY SYSTEM
                </div>
                
                <!-- Hero Heading -->
                <h1 class="font-black text-[var(--cjc-navy)] tracking-tight mb-6 drop-shadow-md lg:drop-shadow-none" style="font-size: clamp(40px, 4.5vw, 60px); line-height: 1.05;">
                    Library Entrance & <br/>
                    Attendance <br/>
                    <span class="text-[var(--cjc-red)]">Monitoring System</span>
                </h1>
                
                <!-- Hero Paragraph -->
                <p class="text-gray-700 font-medium max-w-[540px] mb-12 drop-shadow-md lg:drop-shadow-none" style="font-size: 14px; line-height: 1.8;">
                    Designed exclusively for Cor Jesu College — LIRC to streamline student patron check-ins, real-time foot traffic analytics, mobile scanning synchronization, and consolidated academic attendance reports.
                </p>

                <!-- Floating Tabs Pill Container -->
                <div class="inline-flex flex-wrap items-center bg-white p-2 border border-gray-100 gap-1 mt-4" style="box-shadow: 0 8px 30px rgba(0,0,0,0.1); border-radius: 1rem;">
                    <button type="button" @click="activeTab = 'system'" 
                            class="px-5 py-2.5 rounded-[12px] font-bold text-sm transition-all duration-300 flex items-center gap-2"
                            :class="activeTab === 'system' ? 'bg-[var(--cjc-red)] text-white shadow-md' : 'bg-transparent text-[var(--cjc-navy)] hover:bg-red-50'">
                        <!-- FaInfoCircle -->
                        <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.043 8 8 119.083 8 256c0 136.997 111.043 248 248 248s248-111.003 248-248C504 119.083 392.957 8 256 8zm0 110c23.196 0 42 18.804 42 42s-18.804 42-42 42-42-18.804-42-42 18.804-42 42-42zm56 254c0 6.627-5.373 12-12 12h-88c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h12v-64h-12c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h64c6.627 0 12 5.373 12 12v100h12c6.627 0 12 5.373 12 12v24z"></path></svg>
                        About the System
                    </button>
                    
                    <button type="button" @click="activeTab = 'help'" 
                            class="px-5 py-2.5 rounded-[12px] font-bold text-sm transition-all duration-300 flex items-center gap-2"
                            :class="activeTab === 'help' ? 'bg-[var(--cjc-red)] text-white shadow-md' : 'bg-transparent text-[var(--cjc-navy)] hover:bg-gray-50'">
                        <!-- FaQuestion -->
                        <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 384 512" class="w-5 h-5" :class="activeTab === 'help' ? 'text-white' : 'text-[var(--cjc-red)]'" xmlns="http://www.w3.org/2000/svg"><path d="M202.021 0C122.202 0 70.503 32.703 29.914 91.026c-7.363 10.58-5.093 25.086 5.178 32.874l43.138 32.709c10.373 7.865 25.132 6.026 33.253-4.148 25.049-31.381 43.63-49.449 82.757-49.449 30.764 0 68.816 19.799 68.816 49.631 0 22.552-18.617 34.134-48.993 51.164-35.423 19.86-82.299 44.576-82.299 106.405V320c0 13.255 10.745 24 24 24h72.471c13.255 0 24-10.745 24-24v-5.773c0-42.86 125.268-46.556 125.268-160.627C377.504 66.256 286.902 0 202.021 0zM192 373.459c-38.196 0-69.271 31.075-69.271 69.271 0 38.195 31.075 69.27 69.271 69.27s69.271-31.075 69.271-69.27c0-38.196-31.075-69.271-69.271-69.271z"></path></svg>
                        Help & Support Desk
                    </button>
                    
                    <button type="button" @click="activeTab = 'developers'" 
                            class="px-5 py-2.5 rounded-[12px] font-bold text-sm transition-all duration-300 flex items-center gap-2"
                            :class="activeTab === 'developers' ? 'bg-[var(--cjc-red)] text-white shadow-md' : 'bg-transparent text-[var(--cjc-navy)] hover:bg-gray-50'">
                        <!-- FaLaptopCode -->
                        <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 640 512" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg"><path d="M255.03 261.65c6.25 6.25 16.38 6.25 22.63 0l11.31-11.31c6.25-6.25 6.25-16.38 0-22.63L253.25 192l35.71-35.72c6.25-6.25 6.25-16.38 0-22.63l-11.31-11.31c-6.25-6.25-16.38-6.25-22.63 0l-58.34 58.34c-6.25 6.25-6.25 16.38 0 22.63l58.35 58.34zm96.01-11.3l11.31 11.31c6.25 6.25 16.38 6.25 22.63 0l58.34-58.34c6.25-6.25 6.25-16.38 0-22.63l-58.34-58.34c-6.25-6.25-16.38-6.25-22.63 0l-11.31 11.31c-6.25 6.25-6.25 16.38 0 22.63L386.75 192l-35.71 35.72c-6.25 6.25-6.25 16.38 0 22.63zM624 416H381.54c-.74 19.81-14.71 32-32.74 32H288c-18.69 0-33.02-17.47-32.77-32H16c-8.8 0-16 7.2-16 16v16c0 35.2 28.8 64 64 64h512c35.2 0 64-28.8 64-64v-16c0-8.8-7.2-16-16-16zM576 48c0-26.4-21.6-48-48-48H112C85.6 0 64 21.6 64 48v336h512V48zm-64 272H128V64h384v256z"></path></svg>
                        Developers
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content Area -->
    <!-- The gradient fade at the bottom of the hero seamlessly transitions to bg-light -->
    <div class="w-full h-24 bg-gradient-to-b from-white to-[var(--bg-light)] -mt-24 z-0 relative pointer-events-none"></div>

    <main class="flex-1 w-full mx-auto px-4 md:px-8 lg:px-12 xl:px-20 pb-20 relative z-10" style="max-width: 1400px;">
        
        <!-- ==========================================
             TAB 1: ABOUT THE SYSTEM
             ========================================== -->
        <div x-show="activeTab === 'system'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
            
            <!-- Mission & Architecture (White Card) -->
            <div class="bg-white p-10 md:p-14 flex flex-col lg:flex-row items-center gap-16 relative overflow-hidden border border-gray-50" style="box-shadow: 0 4px 24px -8px rgba(0,0,0,0.08); border-radius: 32px;">
                <!-- Soft background blob matching the mockup -->
                <div class="absolute right-0 bottom-0 w-2/3 h-full bg-gradient-to-bl from-red-50 to-transparent opacity-50 z-0"></div>

                <div class="flex-1 space-y-5 relative z-10">
                    <div class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-extrabold bg-red-50 text-[var(--cjc-red)] uppercase tracking-widest border border-red-100" style="line-height: 1;">
                        MISSION & ARCHITECTURE
                    </div>
                    <h2 class="font-black text-[var(--cjc-navy)] tracking-tight" style="font-size: clamp(28px, 3vw, 42px); line-height: 1.15;">
                        Automating Library Foot Traffic & <br/> Academic Compliance
                    </h2>
                    <p class="text-[13px] md:text-sm text-gray-600 leading-[1.8] max-w-xl font-medium">
                        LIRC-LMS (Library Information & Resource Center - Library Management & Entrance Monitoring System) is an automated, real-time digital entrance monitoring and library management solution built specifically for Cor Jesu College (CJC).
                    </p>
                    <p class="text-[13px] md:text-sm text-gray-600 leading-[1.8] max-w-xl font-medium">     
                        The primary objective of LIRC-LMS is to upgrade the old LIRC (Library Information & Resource Center) System with a seamless, contactless, and intelligent digital kiosk system. 
                        It registers student and staff attendance upon entering or leaving the library, while providing library administrators with real-time occupancy monitoring, patron record management, and comprehensive attendance analytics.
                    </p>
                </div>

                <!-- Right Side Image Box (3D Scanner) -->
                <div class="flex-1 flex justify-center lg:justify-end w-full relative z-10">
                    <!-- Red blob behind image to match the mockup's floating pink background effect -->
                    <div class="absolute inset-0 rounded-full" style="background-color: rgba(254, 226, 226, 0.6); filter: blur(60px); transform: scale(0.85) translate(16px, 16px);"></div>
                    
                    <div class="relative w-full max-w-[480px] transform hover:-translate-y-2 transition-transform duration-300">
                        <!-- Use the actual 3D PNG and let it float naturally without borders -->
                        <img src="/images/3D.png" alt="3D Scanner & ID Verification" class="w-full h-auto object-contain drop-shadow-xl relative z-10">
                    </div>
                </div>
            </div>

            <!-- Features Grid (3x2) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pt-2">
                <!-- Feature 1 -->
                <div class="bg-white p-8 border border-gray-50 hover:shadow-lg transition-shadow group flex items-start gap-5" style="box-shadow: 0 4px 24px -8px rgba(0,0,0,0.06); border-radius: 24px;">
                    <div class="w-[46px] h-[46px] rounded-[14px] bg-red-50 text-[var(--cjc-red)] flex items-center justify-center shrink-0">
                        <svg class="w-[22px] h-[22px]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                    </div>
                    <div class="pt-1">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-extrabold text-gray-900 text-[14px]">Instant Barcode Scanning</h3>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-[var(--cjc-red)] group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </div>
                        <p class="text-[12px] text-gray-500 leading-relaxed font-semibold">Present student ID cards to the kiosk scanner for instant sound & photo check-in verification with 5-minute duplicate scan protection.</p>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white p-8 rounded-3xl shadow-[0_4px_24px_-8px_rgba(0,0,0,0.06)] border border-gray-50 hover:shadow-lg transition-shadow group flex items-start gap-5">
                    <div class="w-[46px] h-[46px] rounded-[14px] bg-blue-50 text-blue-500 flex items-center justify-center shrink-0">
                        <svg class="w-[22px] h-[22px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="pt-1">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-extrabold text-gray-900 text-[14px]">Mobile Scanner App</h3>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-500 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </div>
                        <p class="text-[12px] text-gray-500 leading-relaxed font-semibold">Library staff can scan student IDs anywhere inside the library using the official LEMS Mobile App synced via local Wi-Fi subnet.</p>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white p-8 rounded-3xl shadow-[0_4px_24px_-8px_rgba(0,0,0,0.06)] border border-gray-50 hover:shadow-lg transition-shadow group flex items-start gap-5">
                    <div class="w-[46px] h-[46px] rounded-[14px] bg-green-50 text-green-500 flex items-center justify-center shrink-0">
                        <svg class="w-[22px] h-[22px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div class="pt-1">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-extrabold text-gray-900 text-[14px]">Real-Time Heatmaps</h3>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-green-500 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </div>
                        <p class="text-[12px] text-gray-500 leading-relaxed font-semibold">Live graphics and doughnut charts auto-update every 2.5 seconds to visualize peak visitation hours and department ratios.</p>
                    </div>
                </div>

                <!-- Feature 4 -->
                <div class="bg-white p-8 rounded-3xl shadow-[0_4px_24px_-8px_rgba(0,0,0,0.06)] border border-gray-50 hover:shadow-lg transition-shadow group flex items-start gap-5">
                    <div class="w-[46px] h-[46px] rounded-[14px] bg-yellow-50 text-yellow-500 flex items-center justify-center shrink-0">
                        <svg class="w-[22px] h-[22px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div class="pt-1">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-extrabold text-gray-900 text-[14px]">Unified Report Generator</h3>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-yellow-500 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </div>
                        <p class="text-[12px] text-gray-500 leading-relaxed font-semibold">Extract official reports filtered by School Year, Month, Program, and Format (Excel .xlsx, Word .doc, PDF .pdf).</p>
                    </div>
                </div>

                <!-- Feature 5 -->
                <div class="bg-white p-8 rounded-3xl shadow-[0_4px_24px_-8px_rgba(0,0,0,0.06)] border border-gray-50 hover:shadow-lg transition-shadow group flex items-start gap-5">
                    <div class="w-[46px] h-[46px] rounded-[14px] bg-purple-50 text-purple-500 flex items-center justify-center shrink-0">
                        <svg class="w-[22px] h-[22px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div class="pt-1">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-extrabold text-gray-900 text-[14px]">Seating Statistics</h3>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-purple-500 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </div>
                        <p class="text-[12px] text-gray-500 leading-relaxed font-semibold">Track active library occupants in real-time to prevent overcrowding and ensure comfortable study environments.</p>
                    </div>
                </div>

                <!-- Feature 6 -->
                <div class="bg-white p-8 rounded-3xl shadow-[0_4px_24px_-8px_rgba(0,0,0,0.06)] border border-gray-50 hover:shadow-lg transition-shadow group flex items-start gap-5">
                    <div class="w-[46px] h-[46px] rounded-[14px] bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-[22px] h-[22px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div class="pt-1">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-extrabold text-gray-900 text-[14px]">Subnet Security</h3>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-600 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </div>
                        <p class="text-[12px] text-gray-500 leading-relaxed font-semibold">Token-authenticated LAN security prevents external unauthorized remote scanning while allowing seamless local hardware operation.</p>
                    </div>
                </div>
            </div>

            <!-- Technical Specifications Section -->
            <div class="rounded-[2rem] p-10 lg:p-14 shadow-2xl text-white relative overflow-hidden mt-8" style="background-color: #0b172a;">
                <!-- Subtle background glow -->
                <div class="absolute -right-20 -top-20 w-[400px] h-[400px] rounded-full" style="background-color: rgba(59, 130, 246, 0.1); filter: blur(80px);"></div>
                
                <div class="relative z-10">
                    <div class="text-xs font-extrabold text-yellow-500 uppercase tracking-widest mb-3">
                        TECHNICAL SPECIFICATIONS
                    </div>
                    <h2 class="font-black mb-12" style="font-size: clamp(24px, 2.5vw, 36px); line-height: 1.2;">
                        Enterprise Stack & Compatibility
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-10">
                        <!-- Backend -->
                        <div class="flex items-start gap-5">
                            <div class="w-12 h-12 rounded-[14px] bg-white/5 flex items-center justify-center shrink-0 border border-white/10">
                                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                            </div>
                            <div class="pt-1">
                                <h3 class="font-extrabold text-[13px] mb-1.5 text-gray-100">Backend Framework</h3>
                                <p class="text-[11px] text-gray-400 leading-[1.6] font-semibold">Laravel 11 (PHP 8.2+)<br/>with database-agnostic ORM</p>
                            </div>
                        </div>
                        <!-- Desktop App -->
                        <div class="flex items-start gap-5">
                            <div class="w-12 h-12 rounded-[14px] bg-white/5 flex items-center justify-center shrink-0 border border-white/10">
                                <svg class="w-6 h-6 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="pt-1">
                                <h3 class="font-extrabold text-[13px] mb-1.5 text-gray-100">Desktop Application</h3>
                                <p class="text-[11px] text-gray-400 leading-[1.6] font-semibold">Electron JS standalone launcher<br/>with offline fallback</p>
                            </div>
                        </div>
                        <!-- Mobile -->
                        <div class="flex items-start gap-5">
                            <div class="w-12 h-12 rounded-[14px] bg-white/5 flex items-center justify-center shrink-0 border border-white/10">
                                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="pt-1">
                                <h3 class="font-extrabold text-[13px] mb-1.5 text-gray-100">Mobile Scanner</h3>
                                <p class="text-[11px] text-gray-400 leading-[1.6] font-semibold">React Native Expo Android app<br/>with auto-subnet discovery</p>
                            </div>
                        </div>
                        <!-- DB -->
                        <div class="flex items-start gap-5">
                            <div class="w-12 h-12 rounded-[14px] bg-white/5 flex items-center justify-center shrink-0 border border-white/10">
                                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                            </div>
                            <div class="pt-1">
                                <h3 class="font-extrabold text-[13px] mb-1.5 text-gray-100">Database & Storage</h3>
                                <p class="text-[11px] text-gray-400 leading-[1.6] font-semibold">MySQL / SQLite with<br/>symlinked photo storage</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>

        <!-- ==========================================
             TAB 2: HELP & SUPPORT DESK
             ========================================== -->
        <div x-show="activeTab === 'help'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;" class="space-y-6">
            
            <!-- Top White Card: FAQs -->
            <div class="bg-white border border-gray-50 p-10 md:p-14" style="box-shadow: 0 4px 24px -8px rgba(0,0,0,0.08); border-radius: 32px;">
                
                <!-- Header & Search -->
                <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-10">
                    <div class="space-y-4">
                        <div class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-extrabold bg-orange-50 text-orange-600 uppercase tracking-widest border border-orange-100" style="line-height: 1;">
                            HELP & SUPPORT DESK
                        </div>
                        <h2 class="font-black text-[var(--cjc-navy)] tracking-tight" style="font-size: clamp(28px, 3vw, 42px); line-height: 1.1;">
                            Frequently Asked Questions
                        </h2>
                        <p class="text-sm text-gray-500 font-semibold">
                            Find answers to common questions about the Library Entrance & Attendance Monitoring System.
                        </p>
                    </div>
                    
                    <!-- Search Bar -->
                    <div class="w-full lg:w-72 relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" placeholder="Search FAQ topics..." class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-blue-100 focus:border-blue-400 outline-none transition-all placeholder-gray-400">
                    </div>
                </div>

                <!-- FAQ Accordion -->
                <div x-data="{ openFaq: 1 }" class="space-y-4">
                    
                    <!-- FAQ Item 1 -->
                    <div class="border border-gray-100 rounded-2xl overflow-hidden transition-all duration-300" :class="openFaq === 1 ? 'border-red-100 shadow-sm' : 'hover:border-gray-300 hover:shadow-sm'">
                        <button @click="openFaq = openFaq === 1 ? null : 1" class="w-full flex items-center justify-between p-5 bg-white text-left focus:outline-none">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-colors" :class="openFaq === 1 ? 'bg-red-50 text-red-500' : 'bg-gray-50 text-gray-400'">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                </div>
                                <h3 class="font-bold text-[14px] text-[var(--cjc-navy)]">1. How do students check in and check out at the library entrance?</h3>
                            </div>
                            <svg class="w-5 h-5 shrink-0 transition-transform duration-300" :class="openFaq === 1 ? 'text-red-500 rotate-180' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="openFaq === 1" x-collapse>
                            <div class="p-6 pt-2 bg-white">
                                <div class="bg-red-50/50 border border-red-50 rounded-xl p-6 text-[13px] text-gray-600 font-medium leading-relaxed">
                                    Present your official Cor Jesu College Student ID card to the barcode scanner at the kiosk entrance. The scanner will read your barcode, display your profile picture & academic department on the screen, and log your attendance automatically. A 5-minute cooldown buffer prevents accidental duplicate scans.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 2 -->
                    <div class="border border-gray-100 rounded-2xl overflow-hidden transition-all duration-300 hover:border-gray-300 hover:shadow-sm">
                        <button @click="openFaq = openFaq === 2 ? null : 2" class="w-full flex items-center justify-between p-5 bg-white text-left focus:outline-none">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                </div>
                                <h3 class="font-bold text-[14px] text-[var(--cjc-navy)]">2. What should I do if my Student ID card is unreadable or not registered?</h3>
                            </div>
                            <svg class="w-5 h-5 shrink-0 text-gray-400 transition-transform duration-300" :class="openFaq === 2 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="openFaq === 2" x-collapse>
                            <div class="p-6 pt-2 bg-white">
                                <div class="bg-blue-50/50 border border-blue-50 rounded-xl p-6 text-[13px] text-gray-600 font-medium leading-relaxed">
                                    If your ID card is worn out or unregistered, please visit the LIRC front desk. You can manually register your ID number at the registration terminal. If the barcode itself is damaged, you will need to request a replacement ID from the registrar's office.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 3 -->
                    <div class="border border-gray-100 rounded-2xl overflow-hidden transition-all duration-300 hover:border-gray-300 hover:shadow-sm">
                        <button @click="openFaq = openFaq === 3 ? null : 3" class="w-full flex items-center justify-between p-5 bg-white text-left focus:outline-none">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-green-50 text-green-500 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <h3 class="font-bold text-[14px] text-[var(--cjc-navy)]">3. How can librarians extract monthly attendance reports per academic program?</h3>
                            </div>
                            <svg class="w-5 h-5 shrink-0 text-gray-400 transition-transform duration-300" :class="openFaq === 3 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="openFaq === 3" x-collapse>
                            <div class="p-6 pt-2 bg-white">
                                <div class="bg-green-50/50 border border-green-50 rounded-xl p-6 text-[13px] text-gray-600 font-medium leading-relaxed">
                                    Librarians can log into the Admin Dashboard, navigate to the "Reports" section, select the desired month and academic program from the dropdown filters, and click "Export to Excel" or "Generate PDF".
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 4 -->
                    <div class="border border-gray-100 rounded-2xl overflow-hidden transition-all duration-300 hover:border-gray-300 hover:shadow-sm">
                        <button @click="openFaq = openFaq === 4 ? null : 4" class="w-full flex items-center justify-between p-5 bg-white text-left focus:outline-none">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-500 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.14 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                                </div>
                                <h3 class="font-bold text-[14px] text-[var(--cjc-navy)]">4. How does the LEMS Mobile Scanner Android app connect to the server?</h3>
                            </div>
                            <svg class="w-5 h-5 shrink-0 text-gray-400 transition-transform duration-300" :class="openFaq === 4 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="openFaq === 4" x-collapse>
                            <div class="p-6 pt-2 bg-white">
                                <div class="bg-purple-50/50 border border-purple-50 rounded-xl p-6 text-[13px] text-gray-600 font-medium leading-relaxed">
                                    The mobile app uses auto-subnet discovery to locate the LEMS local server on the library's Wi-Fi network. Ensure the Android device is connected to the same Wi-Fi network as the server PC, and the app will sync instantly.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cant find what you're looking for -->
                <div class="mt-8 pt-8 border-t border-gray-100 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <svg class="w-10 h-10 text-[var(--cjc-navy)]" viewBox="0 0 24 24" fill="currentColor"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12c0 1.8.48 3.5 1.34 5.03L2 22l4.97-1.34A9.97 9.97 0 0012 22z"/></svg>
                            <div class="absolute inset-0 flex items-center justify-center text-white font-black text-lg">?</div>
                        </div>
                        <div>
                            <h4 class="font-bold text-sm text-[var(--cjc-navy)]">Can't find what you're looking for?</h4>
                            <p class="text-[12px] font-semibold text-gray-500">Our support team is ready to assist you with any other questions.</p>
                        </div>
                    </div>
                    <a href="https://lirc.cjc.edu.ph" target="_blank" rel="noopener noreferrer" class="px-6 py-2.5 rounded-lg border-2 border-red-100 text-red-600 font-bold text-sm flex items-center gap-2 hover:bg-red-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414"/></svg>
                        Contact Support
                    </a>
                </div>
            </div>

            <!-- Bottom Dark Navy Card: Contacts -->
            <div class="bg-[#0b172a] rounded-[2rem] p-10 lg:p-14 shadow-2xl relative overflow-hidden mt-6">
                <!-- Subtle grid background effect -->
                <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 24px 24px;"></div>
                
                <div class="relative z-10">
                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 mb-10">
                        <div class="space-y-3">
                            <div class="text-xs font-extrabold text-yellow-500 uppercase tracking-widest">
                                LIBRARY DESK CONTACT
                            </div>
                            <h2 class="text-3xl font-black text-white">
                                Need Additional Assistance?
                            </h2>
                            <p class="text-[13px] text-gray-400 font-semibold max-w-lg">
                                Visit or email the LIRC Administrative Team for patron account issues or technical inquiries.
                            </p>
                        </div>
                        <a href="https://lirc.cjc.edu.ph" target="_blank" rel="noopener noreferrer" class="bg-[var(--cjc-red)] text-white px-6 py-3 rounded-xl flex items-center gap-2 font-bold text-sm hover:bg-red-800 transition-colors shadow-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Email LIRC Help Desk
                        </a>
                    </div>

                    <!-- Contact Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Box 1 -->
                        <div class="bg-[#12233f] border border-blue-900/50 rounded-2xl p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-8 h-8 rounded-lg bg-red-500/10 text-red-500 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <h3 class="font-extrabold text-sm text-gray-100">Campus Location</h3>
                            </div>
                            <p class="text-[12px] text-gray-400 font-semibold leading-relaxed">
                                Norbert Building, Second Floor<br/>Cor Jesu College<br/>Sacred Heart Avenue,<br/>Digos City, Davao del Sur
                            </p>
                        </div>

                        <!-- Box 2 -->
                        <div class="bg-[#12233f] border border-blue-900/50 rounded-2xl p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
                                </div>
                                <h3 class="font-extrabold text-sm text-gray-100">Official Email</h3>
                            </div>
                            <p class="text-[12px] text-gray-400 font-semibold leading-relaxed pt-2">
                                lirc@cjc.edu.ph
                            </p>
                        </div>

                        <!-- Box 3 -->
                        <div class="bg-[#12233f] border border-blue-900/50 rounded-2xl p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-8 h-8 rounded-lg bg-green-500/10 text-green-400 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <h3 class="font-extrabold text-sm text-gray-100">Operating Hours</h3>
                            </div>
                            <p class="text-[12px] text-gray-400 font-semibold leading-relaxed">
                                Mon – Fri: 7:30 AM – 6:00 PM<br/>
                                <span class="pt-1 block">Sat: 8:00 AM – 5:00 PM</span>
                            </p>
                        </div>

                        <!-- Box 4 -->
                        <div class="bg-[#12233f] border border-blue-900/50 rounded-2xl p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414"/></svg>
                                </div>
                                <h3 class="font-extrabold text-sm text-gray-100">Technical Support</h3>
                            </div>
                            <p class="text-[12px] text-gray-400 font-semibold leading-relaxed">
                                LEMS Systems Administrator<br/>
                                <span class="pt-1 block">& IT Services</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==========================================
             TAB 3: DEVELOPERS
             ========================================== -->
        <div x-show="activeTab === 'developers'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;" class="space-y-8">
            <div class="bg-white border border-gray-50 p-10 md:p-14" style="box-shadow: 0 4px 24px -8px rgba(0,0,0,0.08); border-radius: 32px;">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-extrabold bg-blue-50 text-blue-700 border border-blue-100 uppercase tracking-widest mb-6" style="line-height: 1;">
                    Meet the Team
                </div>
                <h2 class="font-black text-[var(--cjc-navy)] mb-6" style="font-size: clamp(26px, 2.5vw, 38px); line-height: 1.2;">
                    The Minds Behind LEMS
                </h2>
                <p class="text-sm text-gray-600 font-medium leading-relaxed max-w-4xl mb-12">
                    The Library Entrance & Attendance Monitoring System was developed by a dedicated team of Information Technology & Computer Science students from <strong>Cor Jesu College</strong>, committed to creating an efficient and modern solution for the Learning Information Resource Center.
                </p>

                <!-- Developers Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Developer 1 -->
                    <div class="p-8 bg-white border border-gray-100 text-center space-y-4 transform hover:-translate-y-1 transition-transform" style="box-shadow: 0 2px 12px -4px rgba(0,0,0,0.06); border-radius: 24px;">
                        <div class="mx-auto rounded-full bg-gray-50 border-4 border-white shadow-md flex items-center justify-center overflow-hidden shrink-0" style="width: 100px; height: 100px;">
                            <img src="/developers/Alfie Lynard Polacas.jpg" alt="Alfie Lynard Polacas" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div>
                            <h3 class="font-extrabold text-sm text-[var(--cjc-navy)]">Alfie Lynard Polacas</h3>
                            <p class="font-bold text-[var(--cjc-red)] uppercase tracking-wider mt-1" style="font-size: 10px;">Lead Developer</p>
                        </div>
                    </div>

                    <!-- Developer 2 -->
                    <div class="p-8 rounded-3xl bg-white shadow-[0_2px_12px_-4px_rgba(0,0,0,0.06)] border border-gray-100 text-center space-y-4 transform hover:-translate-y-1 transition-transform">
                        <div class="mx-auto rounded-full bg-gray-50 border-4 border-white shadow-md flex items-center justify-center overflow-hidden shrink-0" style="width: 100px; height: 100px;">
                            <img src="/developers/Rhea Grace Balatero.jpg" alt="Rhea Grace Balatero" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div>
                            <h3 class="font-extrabold text-sm text-[var(--cjc-navy)]">Rhea Grace Balatero</h3>
                            <p class="font-bold text-[var(--cjc-red)] uppercase tracking-wider mt-1" style="font-size: 10px;">Data Analyst / UI/UX Designer</p>
                        </div>
                    </div>

                    <!-- Developer 3 -->
                    <div class="p-8 rounded-3xl bg-white shadow-[0_2px_12px_-4px_rgba(0,0,0,0.06)] border border-gray-100 text-center space-y-4 transform hover:-translate-y-1 transition-transform">
                        <div class="mx-auto rounded-full bg-gray-50 border-4 border-white shadow-md flex items-center justify-center overflow-hidden shrink-0" style="width: 100px; height: 100px;">
                            <img src="/developers/John Mark Limsan.png" alt="John Mark Limsan" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div>
                            <h3 class="font-extrabold text-sm text-[var(--cjc-navy)]">John Mark Limsan</h3>
                            <p class="font-bold text-[var(--cjc-red)] uppercase tracking-wider mt-1" style="font-size: 10px;">System Analyst</p>
                        </div>
                    </div>
                    
                    <!-- Developer 4 -->
                    <div class="p-8 rounded-3xl bg-white shadow-[0_2px_12px_-4px_rgba(0,0,0,0.06)] border border-gray-100 text-center space-y-4 transform hover:-translate-y-1 transition-transform">
                        <div class="mx-auto rounded-full bg-gray-50 border-4 border-white shadow-md flex items-center justify-center overflow-hidden shrink-0" style="width: 100px; height: 100px;">
                            <img src="/developers/Z Andrie Barraba.jpg" alt="Z Andrie Barraba" style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
                        </div>
                        <div>
                            <h3 class="font-extrabold text-sm text-[var(--cjc-navy)]">Z Andrie Barraba</h3>
                            <p class="font-bold text-[var(--cjc-red)] uppercase tracking-wider mt-1" style="font-size: 10px;">Quality Assurance</p>
                        </div>
                    </div>
                </div>

                <!-- Developer Resources & Source Code Repository Card (Full Width) -->
                <div class="rounded-[2rem] p-10 lg:p-14 shadow-2xl text-white relative overflow-hidden border border-slate-800 mt-8 w-full" style="background-color: #0b172a;">
                    <!-- Background accent glow -->
                    <div class="absolute -right-20 -bottom-20 w-[400px] h-[400px] rounded-full" style="background-color: rgba(196, 30, 42, 0.15); filter: blur(80px); pointer-events: none;"></div>
                    <div class="absolute -left-20 -top-20 w-[300px] h-[300px] rounded-full" style="background-color: rgba(59, 130, 246, 0.15); filter: blur(80px); pointer-events: none;"></div>

                    <div class="relative z-10 space-y-8">
                        <!-- Section Header -->
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-8 border-b border-slate-800">
                            <div>
                                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-extrabold bg-red-500/10 text-red-400 border border-red-500/20 uppercase tracking-widest mb-3" style="line-height: 1;">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
                                    DEVELOPER RESOURCES & SOURCE CODE
                                </div>
                                <h2 class="font-black text-white" style="font-size: clamp(24px, 2.5vw, 36px); line-height: 1.2;">
                                    Official GitHub Repository
                                </h2>
                                <p class="text-xs md:text-sm text-slate-400 font-medium mt-2 max-w-2xl leading-relaxed">
                                    Access the official LEMS version-control repository for code inspection, local deployment, environment setup, and future development contributions.
                                </p>
                            </div>

                            <!-- Main Primary GitHub CTA Button -->
                            <div class="shrink-0">
                                <a href="https://github.com/RheaGrays/LIRC-LMS" target="_blank" rel="noopener noreferrer" 
                                   class="inline-flex items-center gap-3 px-6 py-4 rounded-xl bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600 text-white font-extrabold text-sm shadow-xl shadow-red-900/30 transition-all hover:scale-105 active:scale-95 group">
                                    <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
                                    <span>View Source Code on GitHub</span>
                                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                            </div>
                        </div>

                        <!-- Display URL Box & Quick Copy -->
                        <div class="bg-slate-900/80 border border-slate-800 rounded-xl p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 font-mono text-xs text-slate-300">
                            <div class="flex items-center gap-3 overflow-x-auto">
                                <span class="text-slate-500 select-none">$ git clone</span>
                                <span class="text-emerald-400 font-bold select-all">https://github.com/RheaGrays/LIRC-LMS.git</span>
                            </div>
                            <a href="https://github.com/RheaGrays/LIRC-LMS" target="_blank" rel="noopener noreferrer" class="text-xs font-sans font-bold text-red-400 hover:text-red-300 transition-colors shrink-0 flex items-center gap-1">
                                <span>github.com/RheaGrays/LIRC-LMS</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>

                        <!-- Maintainer & Contributor Workflow Capabilities Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 pt-2">
                            <!-- Box 1: Complete Source Code -->
                            <div class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800/80 space-y-2">
                                <div class="w-9 h-9 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center mb-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                                </div>
                                <h3 class="font-extrabold text-sm text-white">Full Core Codebase</h3>
                                <p class="text-xs text-slate-400 font-medium leading-relaxed">
                                    Access all Laravel backend controllers, Eloquent models, Blade templates, Electron launcher scripts, and Expo mobile scanner files.
                                </p>
                            </div>

                            <!-- Box 2: Environment Setup -->
                            <div class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800/80 space-y-2">
                                <div class="w-9 h-9 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <h3 class="font-extrabold text-sm text-white">Local Environment Setup</h3>
                                <p class="text-xs text-slate-400 font-medium leading-relaxed">
                                    Easily clone the repository directly to your workstation, configure standard dependencies, and run database migrations.
                                </p>
                            </div>

                            <!-- Box 3: Version Control & Contributions -->
                            <div class="p-6 rounded-2xl bg-slate-900/50 border border-slate-800/80 space-y-2">
                                <div class="w-9 h-9 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center mb-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                </div>
                                <h3 class="font-extrabold text-sm text-white">Version Control Workflow</h3>
                                <p class="text-xs text-slate-400 font-medium leading-relaxed">
                                    Pull updates, manage feature branches, push changes, and maintain system updates without manual file transfers.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

    </main>

    <!-- Page Footer -->
    <footer class="bg-white border-t border-gray-100 py-6 px-4 md:px-8 lg:px-12 xl:px-20 mt-auto">
        <div class="w-full mx-auto flex flex-col md:flex-row items-center justify-between gap-4 text-[10px] text-gray-500 font-bold" style="max-width: 1400px;">
            <div class="flex items-center gap-3">
                <img src="/cjc-logo.jpeg" alt="CJC Logo" class="w-6 h-6 rounded-full object-cover border border-gray-200">
                <span class="leading-tight">
                    &copy; 2026 Cor Jesu College — Learning Information Resource Center (LIRC).<br/>All rights reserved.
                </span>
            </div>

            <div class="flex items-center gap-6 text-gray-600">
                <a href="{{ route('kiosk.index') }}" class="hover:text-[var(--cjc-navy)] transition-colors">Kiosk</a>
                <span class="text-gray-300">|</span>
                <a href="{{ route('register.index') }}" class="hover:text-[var(--cjc-navy)] transition-colors">Registration</a>
                <span class="text-gray-300">|</span>
                <a href="https://github.com/RheaGrays/LIRC-LMS" target="_blank" rel="noopener noreferrer" class="hover:text-[var(--cjc-navy)] transition-colors text-red-600 font-bold flex items-center gap-1">
                    <span>Source Code</span>
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
                </a>
                <span class="text-gray-300">|</span>
                <a href="{{ route('admin.login') }}" class="hover:text-[var(--cjc-navy)] transition-colors">Admin Login</a>
            </div>
        </div>
    </footer>

</body>
</html>
