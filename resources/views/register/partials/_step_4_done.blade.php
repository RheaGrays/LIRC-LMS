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
                    <div class="mt-4 flex flex-col sm:flex-row items-center justify-center gap-3 w-full max-w-md">
                        <button type="button"
                                @click="resetForm()"
                                class="flex-1 w-full sm:w-auto px-5 py-2.5 bg-[var(--cjc-navy)] hover:bg-[#1a385c] text-white rounded-lg text-xs md:text-sm font-semibold font-['Inter'] whitespace-nowrap cursor-pointer inline-flex items-center justify-center gap-2 transition-all shadow-sm hover:shadow">
                            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 5v14M5 12h14"/>
                            </svg>
                            <span>Register Another</span>
                        </button>

                        <a href="{{ route('kiosk.index') }}"
                           class="flex-1 w-full sm:w-auto px-5 py-2.5 bg-[var(--cjc-red)] hover:bg-red-700 text-white rounded-lg text-xs md:text-sm font-semibold font-['Inter'] whitespace-nowrap cursor-pointer inline-flex items-center justify-center gap-2 transition-all shadow-sm hover:shadow">
                            <svg class="w-4 h-4 shrink-0" viewBox="0 0 15 15" fill="none">
                                <path d="M7 2l5.5 5.5L7 13M12.5 7.5H1" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span>Go to Kiosk</span>
                        </a>
                    </div>
                </div>
