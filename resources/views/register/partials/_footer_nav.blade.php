                <!-- Footer Nav -->
                <div x-show="step !== 'done'" class="px-9 py-4 pb-6 border-t border-[var(--border-light)] flex justify-between items-center bg-white" style="display: none;">
                    <button @click="handleBack()"
                            class="px-5 py-2.5 bg-transparent border border-[var(--border-warm)] rounded-[var(--radius-md)] text-[13px] font-medium text-[var(--text-muted)] font-['Inter'] cursor-pointer hover:bg-gray-50 transition-colors">
                        <span x-text="step === 'info' ? 'Cancel' : '← Back'"></span>
                    </button>

                    <button @click="handleNext()" :disabled="submitting"
                            class="px-7 py-2.5 bg-[var(--cjc-red)] text-white border-none rounded-[var(--radius-md)] text-[13px] font-semibold font-['Inter'] flex items-center justify-center gap-2 transition-colors min-w-[130px]"
                            :class="submitting ? 'opacity-70 cursor-not-allowed' : 'cursor-pointer hover:bg-red-700'">
                        
                        <template x-if="submitting">
                            <div class="flex items-center gap-2">
                                <svg width="14" height="14" viewBox="0 0 14 14" class="animate-spin" fill="none">
                                    <circle cx="7" cy="7" r="5.5" stroke="rgba(255,255,255,0.35)" stroke-width="1.5" />
                                    <path d="M7 1.5A5.5 5.5 0 0112.5 7" stroke="white" stroke-width="1.5" stroke-linecap="round" />
                                </svg>
                                <span>Submitting…</span>
                            </div>
                        </template>
                        <template x-if="!submitting">
                            <span x-text="step === 'confirm' ? 'Submit Registration' : 'Continue →'"></span>
                        </template>
                    </button>
                </div>
