<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>About LEMS | Library Entrance & Attendance Monitoring System</title>
    <link rel="icon" type="image/png" href="/cjc-logo.jpeg">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,700;0,9..144,800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        :root {
            --cjc-red: #c41e2a;
            --cjc-red-dark: #9e1520;
            --cjc-navy: #0f2744;
            --cjc-navy-dark: #09192d;
            --cjc-gold: #d4a418;
            --bg-cream: #fcf9f2;
        }
    </style>
</head>
<body class="font-sans antialiased bg-[var(--bg-cream)] text-[var(--cjc-navy)] min-h-screen flex flex-col selection:bg-[var(--cjc-red)] selection:text-white" x-data="{ searchFaq: '', activeTab: 'system' }">

    <!-- Top Navigation Header -->
    <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-200/80 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <a href="{{ route('kiosk.index') }}" class="flex items-center gap-3.5 group">
                <div class="w-12 h-12 rounded-full overflow-hidden border border-gray-200 bg-white shrink-0 shadow-sm group-hover:scale-105 transition-all">
                    <img src="/cjc-logo.jpeg" alt="CJC Logo" class="w-full h-full object-cover">
                </div>
                <div>
                    <h1 class="text-sm font-bold tracking-wider uppercase text-[var(--cjc-navy)] font-['Inter'] leading-tight group-hover:text-[var(--cjc-red)] transition-colors">
                        Cor Jesu College
                    </h1>
                    <p class="text-xs text-gray-500 font-['Inter'] font-medium">
                        Learning Information Resource Center (LIRC)
                    </p>
                </div>
            </a>

            <div class="flex items-center gap-3">
                <a href="{{ route('kiosk.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold bg-gray-100 hover:bg-gray-200 text-[var(--cjc-navy)] transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
                    Kiosk Entrance
                </a>
                <a href="{{ route('register.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold bg-[var(--cjc-navy)] text-white hover:bg-[var(--cjc-navy-dark)] transition-all shadow-sm">
                    Register Patron
                </a>
                <a href="{{ route('admin.login') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold bg-[var(--cjc-red)] text-white hover:bg-[var(--cjc-red-dark)] transition-all shadow-sm">
                    Admin Portal
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">

        <!-- Hero Banner Section -->
        <section class="relative bg-gradient-to-br from-[var(--cjc-navy)] via-[#153459] to-[var(--cjc-navy-dark)] text-white py-20 px-4 sm:px-6 lg:px-8 overflow-hidden shadow-xl">
            <!-- Decorative Glow Backgrounds -->
            <div class="absolute -right-20 -top-20 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-20 -bottom-20 w-96 h-96 bg-red-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="max-w-5xl mx-auto text-center relative z-10 space-y-6">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-extrabold uppercase tracking-widest bg-amber-400/20 text-amber-300 border border-amber-400/30 backdrop-blur-md">
                    Enterprise Library System
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black font-['Fraunces'] tracking-tight text-white leading-tight">
                    Library Entrance & Attendance <span class="text-amber-400">Monitoring System</span>
                </h1>
                <p class="text-base sm:text-lg text-slate-300 max-w-3xl mx-auto leading-relaxed font-normal">
                    Designed exclusively for <strong>Cor Jesu College — LIRC</strong> to streamline student patron check-ins, real-time foot traffic analytics, mobile scanning synchronization, and consolidated academic attendance reports.
                </p>

                <!-- Navigation Tabs -->
                <div class="pt-6 flex justify-center gap-3">
                    <button type="button" @click="activeTab = 'system'" 
                            class="px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition-all flex items-center gap-2 cursor-pointer shadow-md"
                            :class="activeTab === 'system' ? 'bg-[var(--cjc-red)] text-white scale-105' : 'bg-white/10 hover:bg-white/20 text-slate-200'">
                        <span>ℹ️ About the System</span>
                    </button>
                    <button type="button" @click="activeTab = 'help'" 
                            class="px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition-all flex items-center gap-2 cursor-pointer shadow-md"
                            :class="activeTab === 'help' ? 'bg-[var(--cjc-red)] text-white scale-105' : 'bg-white/10 hover:bg-white/20 text-slate-200'">
                        <span>❓ Help & Support Desk</span>
                    </button>
                </div>
            </div>
        </section>

        <!-- Stats Counter Section -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative z-20">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-6 rounded-2xl border border-gray-200/80 shadow-lg text-center transform hover:-translate-y-1 transition-all">
                    <div class="text-3xl sm:text-4xl font-extrabold text-[var(--cjc-navy)] font-['Fraunces']">{{ number_format($totalStudents) }}</div>
                    <div class="text-xs font-bold uppercase tracking-wider text-gray-500 mt-1">Registered Patrons</div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-gray-200/80 shadow-lg text-center transform hover:-translate-y-1 transition-all">
                    <div class="text-3xl sm:text-4xl font-extrabold text-[var(--cjc-red)] font-['Fraunces']">{{ number_format($totalLogs) }}</div>
                    <div class="text-xs font-bold uppercase tracking-wider text-gray-500 mt-1">Attendance Scans Recorded</div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-gray-200/80 shadow-lg text-center transform hover:-translate-y-1 transition-all">
                    <div class="text-3xl sm:text-4xl font-extrabold text-amber-600 font-['Fraunces']">{{ number_format($totalDepartments) }}</div>
                    <div class="text-xs font-bold uppercase tracking-wider text-gray-500 mt-1">Academic Departments</div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-gray-200/80 shadow-lg text-center transform hover:-translate-y-1 transition-all">
                    <div class="text-3xl sm:text-4xl font-extrabold text-emerald-600 font-['Fraunces']">{{ number_format($totalPrograms) }}</div>
                    <div class="text-xs font-bold uppercase tracking-wider text-gray-500 mt-1">Academic Programs</div>
                </div>
            </div>
        </section>

        <!-- Dynamic Body Content -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-16">

            <!-- TAB 1: ABOUT THE SYSTEM -->
            <div x-show="activeTab === 'system'" x-transition class="space-y-12">
                
                <!-- System Mission Card -->
                <div class="bg-white rounded-3xl p-8 sm:p-12 border border-gray-200/80 shadow-xl space-y-6">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-xs font-bold bg-red-50 text-[var(--cjc-red)] border border-red-200 uppercase tracking-wider">
                        Mission & Architecture
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-[var(--cjc-navy)] font-['Fraunces']">
                        Automating Library Foot Traffic & Academic Compliance
                    </h2>
                    <p class="text-sm sm:text-base text-gray-700 leading-relaxed max-w-4xl">
                        The <strong>Library Entrance & Attendance Monitoring System (LEMS)</strong> was developed to eliminate manual logbooks and provide Cor Jesu College librarians with real-time insight into patron visitation patterns. By leveraging high-speed barcode readers and mobile app synchronization, LEMS records exact entry timestamps, patron categories, and program affiliations with zero friction.
                    </p>

                    <!-- Features Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pt-6 border-t border-gray-100">
                        <div class="p-6 rounded-2xl bg-gray-50 border border-gray-200/70 space-y-3">
                            <div class="w-12 h-12 rounded-xl bg-red-100 text-[var(--cjc-red)] flex items-center justify-center text-2xl font-bold">⚡</div>
                            <h3 class="text-base font-bold text-[var(--cjc-navy)]">Instant Barcode Scanning</h3>
                            <p class="text-xs text-gray-600 leading-relaxed">Present student ID cards to the kiosk scanner for instant sound & photo check-in verification with 5-minute duplicate scan protection.</p>
                        </div>

                        <div class="p-6 rounded-2xl bg-gray-50 border border-gray-200/70 space-y-3">
                            <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-2xl font-bold">📱</div>
                            <h3 class="text-base font-bold text-[var(--cjc-navy)]">Mobile Scanner App</h3>
                            <p class="text-xs text-gray-600 leading-relaxed">Library staff can scan student IDs anywhere inside the library using the official LEMS Mobile App synced via local Wi-Fi subnet.</p>
                        </div>

                        <div class="p-6 rounded-2xl bg-gray-50 border border-gray-200/70 space-y-3">
                            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xl font-bold">📊</div>
                            <h3 class="text-base font-bold text-[var(--cjc-navy)]">Real-Time Heatmaps</h3>
                            <p class="text-xs text-gray-600 leading-relaxed">Live graphics and doughnut charts auto-update every 2.5 seconds to visualize peak visitation hours and department ratios.</p>
                        </div>

                        <div class="p-6 rounded-2xl bg-gray-50 border border-gray-200/70 space-y-3">
                            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-2xl font-bold">📄</div>
                            <h3 class="text-base font-bold text-[var(--cjc-navy)]">Unified Report Generator</h3>
                            <p class="text-xs text-gray-600 leading-relaxed">Extract official reports filtered by School Year, Month, Program, and Format (Excel .xlsx, Word .doc, PDF .pdf).</p>
                        </div>

                        <div class="p-6 rounded-2xl bg-gray-50 border border-gray-200/70 space-y-3">
                            <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-2xl font-bold">👥</div>
                            <h3 class="text-base font-bold text-[var(--cjc-navy)]">Seating Statistics</h3>
                            <p class="text-xs text-gray-600 leading-relaxed">Track active library occupants in real-time to prevent overcrowding and ensure comfortable study environments.</p>
                        </div>

                        <div class="p-6 rounded-2xl bg-gray-50 border border-gray-200/70 space-y-3">
                            <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-2xl font-bold">🛡️</div>
                            <h3 class="text-base font-bold text-[var(--cjc-navy)]">Subnet Security</h3>
                            <p class="text-xs text-gray-600 leading-relaxed">Token-authenticated LAN security prevents external unauthorized remote scanning while allowing seamless local hardware operation.</p>
                        </div>
                    </div>
                </div>

                <!-- Technical Specification Card -->
                <div class="bg-[var(--cjc-navy)] text-white rounded-3xl p-8 sm:p-12 shadow-xl relative overflow-hidden">
                    <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="relative z-10 space-y-6">
                        <div class="text-xs font-extrabold uppercase tracking-widest text-amber-400">Technical Specifications</div>
                        <h2 class="text-2xl font-bold font-['Fraunces']">Enterprise Stack & Compatibility</h2>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-xs text-slate-300">
                            <div class="p-4 rounded-xl bg-white/10 border border-white/10">
                                <span class="font-bold text-white block text-sm mb-1">Backend Framework</span>
                                <span>Laravel 11 (PHP 8.2+) with database-agnostic ORM</span>
                            </div>
                            <div class="p-4 rounded-xl bg-white/10 border border-white/10">
                                <span class="font-bold text-white block text-sm mb-1">Desktop Application</span>
                                <span>Electron JS standalone launcher with offline fallback</span>
                            </div>
                            <div class="p-4 rounded-xl bg-white/10 border border-white/10">
                                <span class="font-bold text-white block text-sm mb-1">Mobile Scanner</span>
                                <span>React Native Expo Android app with auto-subnet discovery</span>
                            </div>
                            <div class="p-4 rounded-xl bg-white/10 border border-white/10">
                                <span class="font-bold text-white block text-sm mb-1">Database & Storage</span>
                                <span>MySQL / SQLite with symlinked photo storage</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- TAB 2: HELP & SUPPORT DESK -->
            <div x-show="activeTab === 'help'" x-transition class="space-y-10">
                
                <!-- FAQ Header & Search -->
                <div class="bg-white rounded-3xl p-8 sm:p-10 border border-gray-200/80 shadow-xl space-y-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 uppercase tracking-wider mb-2">
                                Help & Support Desk
                            </div>
                            <h2 class="text-2xl font-bold text-[var(--cjc-navy)] font-['Fraunces']">Frequently Asked Questions</h2>
                        </div>

                        <!-- FAQ Search Input -->
                        <div class="w-full sm:w-72 relative">
                            <input type="text" x-model="searchFaq" placeholder="Search FAQ topics..." class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-[var(--cjc-navy)] font-medium">
                            <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>

                    <!-- Expandable Accordion List -->
                    <div class="space-y-4 pt-4">
                        
                        <!-- FAQ Item 1 -->
                        <div x-data="{ open: true }" 
                             x-show="!searchFaq || 'how to check in student id scanning'.includes(searchFaq.toLowerCase())"
                             class="border border-gray-200 rounded-2xl overflow-hidden bg-white shadow-sm">
                            <button type="button" @click="open = !open" class="w-full p-5 text-left font-bold text-sm text-[var(--cjc-navy)] flex items-center justify-between bg-gray-50/50 hover:bg-gray-50 transition-colors">
                                <span>1. How do students check in and check out at the library entrance?</span>
                                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-collapse class="p-5 text-xs sm:text-sm text-gray-600 leading-relaxed border-t border-gray-100 bg-white">
                                Present your official Cor Jesu College Student ID card to the barcode scanner at the kiosk entrance. The scanner will read your barcode, display your profile picture & academic department on the screen, and log your attendance automatically. A 5-minute cooldown buffer prevents accidental duplicate scans.
                            </div>
                        </div>

                        <!-- FAQ Item 2 -->
                        <div x-data="{ open: false }" 
                             x-show="!searchFaq || 'register student missing photo profile details'.includes(searchFaq.toLowerCase())"
                             class="border border-gray-200 rounded-2xl overflow-hidden bg-white shadow-sm">
                            <button type="button" @click="open = !open" class="w-full p-5 text-left font-bold text-sm text-[var(--cjc-navy)] flex items-center justify-between bg-gray-50/50 hover:bg-gray-50 transition-colors">
                                <span>2. What should I do if my Student ID card is unreadable or not registered?</span>
                                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-collapse class="p-5 text-xs sm:text-sm text-gray-600 leading-relaxed border-t border-gray-100 bg-white">
                                Click the <strong>"Register Patron"</strong> button on the header of the main kiosk screen or navigation bar. Complete the registration form with your Student ID number, Full Name, Department, Program, and upload an official profile photo. Once submitted, your ID will work instantly at the scanner kiosk.
                            </div>
                        </div>

                        <!-- FAQ Item 3 -->
                        <div x-data="{ open: false }" 
                             x-show="!searchFaq || 'export generate attendance report excel word pdf'.includes(searchFaq.toLowerCase())"
                             class="border border-gray-200 rounded-2xl overflow-hidden bg-white shadow-sm">
                            <button type="button" @click="open = !open" class="w-full p-5 text-left font-bold text-sm text-[var(--cjc-navy)] flex items-center justify-between bg-gray-50/50 hover:bg-gray-50 transition-colors">
                                <span>3. How can librarians extract monthly attendance reports per academic program?</span>
                                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-collapse class="p-5 text-xs sm:text-sm text-gray-600 leading-relaxed border-t border-gray-100 bg-white">
                                Log in to the <strong>Admin Portal</strong>, navigate to <strong>Analytics</strong>, and locate the <strong>Official Librarian Report Generator</strong>. Select the <strong>School Year</strong>, <strong>Month</strong>, <strong>Program</strong>, and output <strong>Format (Excel, Word, PDF)</strong>. Click <strong>Generate Report</strong> to stream the report file directly.
                            </div>
                        </div>

                        <!-- FAQ Item 4 -->
                        <div x-data="{ open: false }" 
                             x-show="!searchFaq || 'mobile scanner android connect network ip'.includes(searchFaq.toLowerCase())"
                             class="border border-gray-200 rounded-2xl overflow-hidden bg-white shadow-sm">
                            <button type="button" @click="open = !open" class="w-full p-5 text-left font-bold text-sm text-[var(--cjc-navy)] flex items-center justify-between bg-gray-50/50 hover:bg-gray-50 transition-colors">
                                <span>4. How does the LEMS Mobile Scanner Android app connect to the server?</span>
                                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-collapse class="p-5 text-xs sm:text-sm text-gray-600 leading-relaxed border-t border-gray-100 bg-white">
                                Connect the Android mobile device to the same campus Wi-Fi network as the LEMS host computer. Open the Mobile App settings and enter the host IPv4 address (e.g. <code>http://192.168.1.5:8000</code>). Test the connection to ensure real-time scanning.
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Contact & Support Desk Card -->
                <div class="bg-gradient-to-br from-[var(--cjc-navy)] to-[var(--cjc-navy-dark)] text-white rounded-3xl p-8 sm:p-10 shadow-xl relative overflow-hidden space-y-6">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative z-10 border-b border-white/10 pb-6">
                        <div>
                            <span class="text-xs font-extrabold uppercase tracking-widest text-amber-400">Library Desk Contact</span>
                            <h3 class="text-2xl font-bold font-['Fraunces'] mt-1">Need Additional Assistance?</h3>
                            <p class="text-xs text-slate-300 mt-1">Visit or email the LIRC Administrative Team for patron account issues or technical inquiries.</p>
                        </div>

                        <a href="mailto:lirc@corjesucollege.edu.ph" class="inline-flex items-center gap-2 px-6 py-3 bg-[var(--cjc-red)] hover:bg-[var(--cjc-red-dark)] text-white text-xs font-bold rounded-xl shadow-lg transition-all whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Email LIRC Help Desk
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-xs text-slate-200 relative z-10">
                        <div>
                            <span class="font-bold text-white block text-sm mb-1">🏢 Campus Location</span>
                            <span>Main Library Building, Cor Jesu College, Sacred Heart Avenue, Digos City, Davao del Sur</span>
                        </div>

                        <div>
                            <span class="font-bold text-white block text-sm mb-1">✉️ Official Email</span>
                            <span>lirc@corjesucollege.edu.ph</span>
                        </div>

                        <div>
                            <span class="font-bold text-white block text-sm mb-1">🕒 Operating Hours</span>
                            <span>Mon – Fri: 7:30 AM – 6:00 PM<br/>Sat: 8:00 AM – 5:00 PM</span>
                        </div>

                        <div>
                            <span class="font-bold text-white block text-sm mb-1">🛡️ Technical Support</span>
                            <span>LEMS Systems Administrator & IT Services</span>
                        </div>
                    </div>
                </div>

            </div>

        </section>

    </main>

    <!-- Page Footer -->
    <footer class="bg-white border-t border-gray-200 py-8 px-4 sm:px-6 lg:px-8 mt-auto">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-500 font-medium">
            <div class="flex items-center gap-2">
                <img src="/cjc-logo.jpeg" alt="CJC Logo" class="w-5 h-5 rounded-full object-cover">
                <span>&copy; {{ date('Y') }} Cor Jesu College — Learning Information Resource Center (LIRC). All rights reserved.</span>
            </div>

            <div class="flex items-center gap-4">
                <a href="{{ route('kiosk.index') }}" class="hover:text-[var(--cjc-navy)] transition-colors">Kiosk</a>
                <span>•</span>
                <a href="{{ route('register.index') }}" class="hover:text-[var(--cjc-navy)] transition-colors">Registration</a>
                <span>•</span>
                <a href="{{ route('admin.login') }}" class="hover:text-[var(--cjc-navy)] transition-colors">Admin Login</a>
            </div>
        </div>
    </footer>

</body>
</html>
