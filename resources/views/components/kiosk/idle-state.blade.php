
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
        </div>
