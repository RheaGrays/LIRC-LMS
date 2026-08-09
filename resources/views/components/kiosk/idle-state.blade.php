
        <div class="flex-1 flex flex-col relative z-10 overflow-hidden h-full">
            <!-- Header -->
            <header class="flex items-center justify-between px-12 py-5 shrink-0 z-20">
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
                        <button type="button" @click.stop="showAboutModal = true; aboutTab = 'system'" class="inline-flex items-center gap-1.5 text-[12px] font-semibold text-[var(--cjc-navy)] font-['Inter'] px-4 py-1.5 border border-[var(--border-warm)] shadow-sm rounded-full bg-white/80 hover:bg-white transition-all backdrop-blur-md cursor-pointer">
                            ℹ️ About Us
                        </button>
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
                    <div class="text-center">
                        <h1 class="font-['Fraunces'] text-[clamp(40px,6vw,65px)] font-[800] text-[var(--cjc-navy)] m-0 leading-[1] tracking-[-0.02em]">
                            Welcome to <span class="text-[var(--cjc-red)]">LIRC</span>, CorJesian!
                        </h1>
                    </div>
                    <div class="text-center">
                        <div class="font-['Fraunces'] text-[clamp(35px,5vw,55px)] font-bold text-[var(--cjc-navy)] leading-none tracking-[-0.02em]">
                            <span x-text="clockHm">--:--</span><span class="text-[0.4em] text-[var(--cjc-red)] font-bold ml-1 align-middle">:<span x-text="clockSec">--</span></span>
                        </div>
                        <div class="font-['Inter'] text-[14px] font-medium text-[var(--text-muted)] mt-1 tracking-[0.02em]" x-text="clockDate">
                            Loading...
                        </div>
                    </div>
                </div>

                <!-- CTA Button (Above container) -->
                <div class="shrink-0">
                    <div class="flex items-center gap-3 bg-white/80 backdrop-blur-md px-7 py-3 rounded-full border border-[var(--border-warm)] shadow-md cursor-pointer hover:bg-white hover:scale-105 transition-all animate-bounce-slow" @click="activate()">
                        <span class="font-['Inter'] text-[15px] font-bold text-[var(--cjc-navy)] tracking-wide">
                            Present your ID to begin
                        </span>
                    </div>
                </div>

                <!-- Slideshow Wrapper -->
                <div class="w-full flex flex-col items-center shrink-0 mt-6" x-data="kioskSlideshow()">
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

            <!-- ABOUT US & HELP/SUPPORT MODAL -->
            <div x-show="showAboutModal" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/60 backdrop-blur-md"
                 @click.self="showAboutModal = false"
                 style="display: none;">
                
                <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-3xl overflow-hidden flex flex-col max-h-[90vh] relative animate-fade-in" @click.stop>
                    
                    <!-- Modal Header -->
                    <div class="bg-[var(--cjc-navy)] text-white px-8 py-6 flex items-center justify-between shrink-0 relative overflow-hidden">
                        <!-- Background Gold Glow -->
                        <div class="absolute -right-10 -top-10 w-40 h-40 bg-amber-500/20 rounded-full blur-2xl pointer-events-none"></div>
                        
                        <div class="flex items-center gap-4 relative z-10">
                            <div class="w-12 h-12 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center shrink-0 p-1">
                                <img src="/CorJesu Logo.png" alt="CJC Logo" class="w-full h-full object-contain">
                            </div>
                            <div>
                                <h2 class="text-xl font-bold font-['Fraunces'] text-amber-400 leading-tight">About LEMS</h2>
                                <p class="text-xs text-slate-300 font-['Inter']">Library Entrance & Attendance Monitoring System</p>
                            </div>
                        </div>

                        <!-- Close Button -->
                        <button type="button" @click="showAboutModal = false" class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-all cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Navigation Tabs inside Modal -->
                    <div class="flex border-b border-gray-200 bg-gray-50 px-8 shrink-0">
                        <button type="button" @click="aboutTab = 'system'" 
                                class="py-3.5 px-6 font-bold text-xs uppercase tracking-wider transition-all border-b-2 font-['Inter'] flex items-center gap-2 cursor-pointer"
                                :class="aboutTab === 'system' ? 'border-[var(--cjc-red)] text-[var(--cjc-red)] bg-white' : 'border-transparent text-gray-500 hover:text-gray-800'">
                            <span>ℹ️ About the System</span>
                        </button>
                        <button type="button" @click="aboutTab = 'help'" 
                                class="py-3.5 px-6 font-bold text-xs uppercase tracking-wider transition-all border-b-2 font-['Inter'] flex items-center gap-2 cursor-pointer"
                                :class="aboutTab === 'help' ? 'border-[var(--cjc-red)] text-[var(--cjc-red)] bg-white' : 'border-transparent text-gray-500 hover:text-gray-800'">
                            <span>❓ Help & Support</span>
                        </button>
                    </div>

                    <!-- Modal Body Content (Scrollable) -->
                    <div class="p-8 overflow-y-auto space-y-6 flex-1 text-gray-800 font-['Inter']">
                        
                        <!-- TAB 1: ABOUT THE SYSTEM -->
                        <div x-show="aboutTab === 'system'" class="space-y-6">
                            <div class="bg-amber-50/60 border border-amber-200/70 p-5 rounded-2xl">
                                <h3 class="text-base font-bold text-[var(--cjc-navy)] mb-2 font-['Fraunces']">What is LEMS?</h3>
                                <p class="text-sm text-gray-700 leading-relaxed">
                                    The <strong>Library Entrance & Attendance Monitoring System (LEMS)</strong> is an automated digital management platform designed specifically for the <strong>Cor Jesu College — Learning Information Resource Center (LIRC)</strong>. It provides seamless barcode scanning for student check-ins, real-time foot traffic tracking, and automated multi-format reporting for librarians and administrative staff.
                                </p>
                            </div>

                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">Key System Capabilities</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div class="p-4 rounded-xl border border-gray-200 bg-white shadow-sm flex items-start gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-red-100 text-red-600 flex items-center justify-center shrink-0 text-lg">⚡</div>
                                        <div>
                                            <h5 class="text-sm font-bold text-[var(--cjc-navy)]">Instant ID Scan</h5>
                                            <p class="text-xs text-gray-500 mt-0.5">High-speed barcode scanner with sound & photo verification.</p>
                                        </div>
                                    </div>

                                    <div class="p-4 rounded-xl border border-gray-200 bg-white shadow-sm flex items-start gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 text-lg">📱</div>
                                        <div>
                                            <h5 class="text-sm font-bold text-[var(--cjc-navy)]">Mobile Scanner App</h5>
                                            <p class="text-xs text-gray-500 mt-0.5">Sync attendance remotely via Android smartphones.</p>
                                        </div>
                                    </div>

                                    <div class="p-4 rounded-xl border border-gray-200 bg-white shadow-sm flex items-start gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 text-lg">📊</div>
                                        <div>
                                            <h5 class="text-sm font-bold text-[var(--cjc-navy)]">Real-Time Analytics</h5>
                                            <p class="text-xs text-gray-500 mt-0.5">Live graphics by Department, Program, and Hour.</p>
                                        </div>
                                    </div>

                                    <div class="p-4 rounded-xl border border-gray-200 bg-white shadow-sm flex items-start gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 text-lg">📄</div>
                                        <div>
                                            <h5 class="text-sm font-bold text-[var(--cjc-navy)]">Multi-Format Reports</h5>
                                            <p class="text-xs text-gray-500 mt-0.5">Export official logs in Excel (.xlsx), Word (.doc), and PDF.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 text-xs text-gray-600 flex items-center justify-between">
                                <span><strong>System Version:</strong> LEMS v1.0.0 (Enterprise Edition)</span>
                                <span>Cor Jesu College — Digos City</span>
                            </div>
                        </div>

                        <!-- TAB 2: HELP & SUPPORT -->
                        <div x-show="aboutTab === 'help'" class="space-y-6">
                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">Frequently Asked Questions (FAQ)</h4>
                                
                                <div class="space-y-3">
                                    <div class="p-4 rounded-xl border border-gray-200 bg-white">
                                        <h5 class="text-sm font-bold text-[var(--cjc-navy)]">Q: How do I check in at the library entrance?</h5>
                                        <p class="text-xs text-gray-600 mt-1">A: Simply present your official Cor Jesu College Student ID card to the barcode scanner at the kiosk entrance. The system will log your attendance automatically with visual and audio feedback.</p>
                                    </div>

                                    <div class="p-4 rounded-xl border border-gray-200 bg-white">
                                        <h5 class="text-sm font-bold text-[var(--cjc-navy)]">Q: What if my Student ID photo or details are missing?</h5>
                                        <p class="text-xs text-gray-600 mt-1">A: Click the <strong>"Register"</strong> button at the top header of this landing page to submit your student photo, ID number, and academic details.</p>
                                    </div>

                                    <div class="p-4 rounded-xl border border-gray-200 bg-white">
                                        <h5 class="text-sm font-bold text-[var(--cjc-navy)]">Q: Can library staff scan IDs away from the main kiosk?</h5>
                                        <p class="text-xs text-gray-600 mt-1">A: Yes! Authorized library personnel can use the LEMS Mobile Scanner Android app to log student attendance anywhere within the campus network.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact & Support Desk Card -->
                            <div class="bg-[var(--cjc-navy)] text-white p-6 rounded-2xl relative overflow-hidden shadow-md">
                                <h4 class="text-base font-bold text-amber-400 mb-3 font-['Fraunces']">LIRC Help & Support Desk</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-slate-200">
                                    <div>
                                        <span class="font-bold text-white block">🏢 Office Location:</span>
                                        <span>Main Library Building, Cor Jesu College, Sacred Heart Ave, Digos City</span>
                                    </div>
                                    <div>
                                        <span class="font-bold text-white block">✉️ Email Contact:</span>
                                        <span>lirc@corjesucollege.edu.ph</span>
                                    </div>
                                    <div>
                                        <span class="font-bold text-white block">🕒 Operating Hours:</span>
                                        <span>Mon - Fri: 7:30 AM – 6:00 PM | Sat: 8:00 AM – 5:00 PM</span>
                                    </div>
                                    <div>
                                        <span class="font-bold text-white block">🛡️ Technical Support:</span>
                                        <span>LIRC Systems & IT Administration</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-gray-50 border-t border-gray-200 px-8 py-4 flex items-center justify-between shrink-0">
                        <span class="text-xs text-gray-500 font-medium">Need immediate assistance? Visit the LIRC Help Desk.</span>
                        <button type="button" @click="showAboutModal = false" class="px-5 py-2 bg-[var(--cjc-navy)] hover:bg-[var(--cjc-navy-dark)] text-white text-xs font-bold rounded-xl transition-all cursor-pointer">
                            Close
                        </button>
                    </div>

                </div>
            </div>
        </div>
