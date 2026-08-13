                    <!-- Step indicator -->
                    <div class="flex items-center justify-center gap-0 mb-7">
                        <template x-for="(s, i) in steps" :key="s.id">
                            <div class="flex items-center">
                                <div class="flex flex-col items-center gap-1">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center transition-colors duration-300"
                                         :class="{'bg-[var(--cjc-red)] border-none': i < stepIdx, 'bg-[var(--cjc-navy)] border-none': i === stepIdx, 'bg-[var(--bg-cream-2)] border border-[var(--border-warm)]': i > stepIdx}">
                                        
                                        <template x-if="i < stepIdx">
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                                <path d="M2.5 7l3.5 3.5 5.5-6" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </template>
                                        <template x-if="i >= stepIdx">
                                            <span class="font-['JetBrains_Mono'] text-xs font-bold"
                                                  :class="{'text-white': i === stepIdx, 'text-[var(--text-muted)]': i > stepIdx}" x-text="s.num">
                                            </span>
                                        </template>
                                    </div>
                                    <span class="text-[10px] font-semibold tracking-wider uppercase font-['Inter']"
                                          :class="{'text-[var(--cjc-navy)]': i === stepIdx, 'text-[var(--text-subtle)]': i !== stepIdx}" x-text="s.label">
                                    </span>
                                </div>
                                <template x-if="i < steps.length - 1">
                                    <div class="w-[40px] sm:w-[60px] h-[1px] mx-2 mb-5 transition-colors duration-300"
                                         :class="{'bg-[var(--cjc-red)]': i < stepIdx, 'bg-[var(--border-warm)]': i >= stepIdx}"></div>
                                </template>
                            </div>
                        </template>
                    </div>
