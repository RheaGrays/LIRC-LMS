                <!-- STEP 4: DONE -->
                <div x-show="step === 'done'" class="p-12 px-9 text-center flex flex-col items-center gap-4" style="display: none;">
                    <div class="w-[72px] h-[72px] rounded-full bg-[#dcfce7] flex items-center justify-center mb-1">
                        <svg width="34" height="34" viewBox="0 0 34 34" fill="none">
                            <path d="M7 17l7 7L27 9" stroke="#16a34a" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <h2 class="font-['Fraunces'] text-[26px] font-bold text-[var(--cjc-navy)] m-0">
                        You're Registered!
                    </h2>
                    <p class="text-sm text-[var(--text-muted)] font-['Inter'] m-0 max-w-[360px] leading-relaxed">
                        Welcome to the CJC Library, <strong class="text-[var(--cjc-navy)]" x-text="form.firstName"></strong>! Your patron account has been created. You can now use your ID at the kiosk to check in.
                    </p>
                    <div class="bg-[var(--bg-cream-2)] border border-[var(--border-warm)] rounded-[var(--radius-md)] px-5 py-2.5 mt-1">
                        <span class="font-['JetBrains_Mono'] text-lg font-bold text-[var(--cjc-navy)] tracking-[0.05em]" x-text="registeredId || form.studentId"></span>
                    </div>
                    <a href="{{ route('kiosk.index') }}"
                            class="mt-2 px-8 py-3 bg-[var(--cjc-red)] text-white border-none rounded-[var(--radius-md)] text-sm font-semibold font-['Inter'] cursor-pointer flex items-center gap-2 hover:bg-red-700 transition-colors">
                        <svg width="15" height="15" viewBox="0 0 15 15" fill="none">
                            <path d="M7 2l5.5 5.5L7 13M12.5 7.5H1" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Go to Check-In Kiosk
                    </a>
                </div>
