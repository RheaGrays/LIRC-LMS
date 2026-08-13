    <!-- Header -->
    <header class="relative z-10 px-6 py-3 md:px-10 md:py-3.5 bg-white border-b border-[var(--border-warm)] flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('kiosk.index') }}" 
               class="flex items-center gap-1.5 px-3 py-1.5 bg-transparent border border-[var(--border-warm)] rounded-md text-xs font-medium text-[var(--text-muted)] font-['Inter'] cursor-pointer transition-colors duration-150 hover:border-[var(--cjc-navy)] hover:text-[var(--cjc-navy)]">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                    <path d="M11 7H3M7 3L3 7l4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                Back to Kiosk
            </a>

            <div class="w-[1px] h-7 bg-[var(--border-warm)] hidden sm:block"></div>

            <div class="w-9 h-9 rounded-full overflow-hidden border border-[var(--border-warm)] bg-white shrink-0 hidden sm:block">
                <img src="/CorJesu Logo.png" alt="CJC" class="w-full h-full object-cover" />
            </div>
            <div class="hidden sm:block">
                <p class="m-0 text-xs font-semibold tracking-wide uppercase text-[var(--cjc-navy)] font-['Inter'] leading-tight">
                    Cor Jesu College
                </p>
                <p class="m-0 text-[11px] text-[var(--text-muted)] font-['Inter']">
                    Library Entrance Monitoring System
                </p>
            </div>
        </div>
        <span class="font-['JetBrains_Mono'] text-sm font-semibold text-[var(--cjc-navy)] tracking-wide" x-text="currentTime"></span>
    </header>
