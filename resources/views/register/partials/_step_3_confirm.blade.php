                <!-- STEP 3: Confirm -->
                <div x-show="step === 'confirm'" class="p-6 md:p-9" style="display: none;">
                    <h2 class="font-['Fraunces'] text-lg font-bold text-[var(--cjc-navy)] m-0 mb-1">
                        Review Your Information
                    </h2>
                    <p class="text-xs text-[var(--text-muted)] font-['Inter'] m-0 mb-5">
                        Please verify all details before submitting.
                    </p>

                    <div class="flex gap-5 items-start">
                        <!-- Photo preview -->
                        <div class="shrink-0 w-20 h-24 rounded-[var(--radius-md)] bg-[var(--bg-cream-2)] border border-[var(--border-warm)] overflow-hidden flex flex-col items-center justify-center gap-1">
                            <template x-if="capturedImage">
                                <img :src="capturedImage" class="w-full h-full object-cover" />
                            </template>
                            <template x-if="!capturedImage">
                                <div class="flex flex-col items-center justify-center gap-1">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <circle cx="12" cy="9" r="4" stroke="#cbd5e1" stroke-width="1.5" />
                                        <path d="M4 21c0-4.418 3.582-8 8-8s8 3.582 8 8" stroke="#cbd5e1" stroke-width="1.5" />
                                    </svg>
                                    <span class="text-[9px] text-[#94a3b8] font-['Inter']">NO PHOTO</span>
                                </div>
                            </template>
                        </div>

                        <!-- Info rows -->
                        <div class="flex-1 bg-[var(--bg-cream-2)] rounded-[var(--radius-md)] px-4 py-3">
                            <!-- Patron Category -->
                            <div class="flex justify-between gap-4 py-1.5 border-b border-black/5">
                                <span class="text-[11px] text-[var(--text-muted)] font-['Inter'] shrink-0">Category</span>
                                <span class="text-[11px] font-semibold text-[var(--cjc-red)] font-['Inter'] text-right" x-text="form.patronCategory"></span>
                            </div>
                            <!-- Patron ID -->
                            <div class="flex justify-between gap-4 py-1.5 border-b border-black/5">
                                <span class="text-[11px] text-[var(--text-muted)] font-['Inter'] shrink-0">ID Number</span>
                                <span class="text-[11px] font-semibold text-[var(--cjc-navy)] font-['JetBrains_Mono'] text-right" x-text="form.studentId || '(auto-generated)'"></span>
                            </div>
                            <!-- Name -->
                            <div class="flex justify-between gap-4 py-1.5 border-b border-black/5">
                                <span class="text-[11px] text-[var(--text-muted)] font-['Inter'] shrink-0">Name</span>
                                <span class="text-[11px] font-semibold text-[var(--cjc-navy)] font-['Inter'] text-right"
                                      x-text="`${form.lastName.toUpperCase()}, ${form.firstName.toUpperCase()}${form.middleName ? ' ' + form.middleName.toUpperCase() : ''}`"></span>
                            </div>
                            <!-- Academic info (Student only) -->
                            <template x-if="isStudent">
                                <div>
                                    <div class="flex justify-between gap-4 py-1.5 border-b border-black/5">
                                        <span class="text-[11px] text-[var(--text-muted)] font-['Inter'] shrink-0">Level</span>
                                        <span class="text-[11px] font-semibold text-[var(--cjc-navy)] font-['Inter'] text-right" x-text="form.level === 'college' ? 'College' : 'Basic Education'"></span>
                                    </div>
                                    <div class="flex justify-between gap-4 py-1.5 border-b border-black/5">
                                        <span class="text-[11px] text-[var(--text-muted)] font-['Inter'] shrink-0" x-text="form.level === 'basic_ed' ? 'Department' : 'College'"></span>
                                        <span class="text-[11px] font-semibold text-[var(--cjc-navy)] font-['Inter'] text-right" x-text="form.college"></span>
                                    </div>
                                    <template x-if="form.level === 'college'">
                                        <div class="flex justify-between gap-4 py-1.5 border-b border-black/5">
                                            <span class="text-[11px] text-[var(--text-muted)] font-['Inter'] shrink-0">Program</span>
                                            <span class="text-[11px] font-semibold text-[var(--cjc-navy)] font-['Inter'] text-right" x-text="form.department"></span>
                                        </div>
                                    </template>
                                    <div class="flex justify-between gap-4 py-1.5 border-b border-black/5">
                                        <span class="text-[11px] text-[var(--text-muted)] font-['Inter'] shrink-0" x-text="form.level === 'basic_ed' ? 'Grade Level' : 'Year Level'"></span>
                                        <span class="text-[11px] font-semibold text-[var(--cjc-navy)] font-['Inter'] text-right" x-text="form.yearLevel"></span>
                                    </div>
                                </div>
                            </template>
                            <!-- Email -->
                            <template x-if="form.email">
                                <div class="flex justify-between gap-4 py-1.5 border-b border-black/5">
                                    <span class="text-[11px] text-[var(--text-muted)] font-['Inter'] shrink-0">Email</span>
                                    <span class="text-[11px] font-semibold text-[var(--cjc-navy)] font-['Inter'] text-right" x-text="form.email"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                    
                    <p class="text-[11px] text-[var(--text-subtle)] font-['Inter'] mt-4 mb-0 leading-relaxed">
                        By submitting, you agree that this information will be used solely for library access tracking at Cor Jesu College.
                    </p>
                    
                    <!-- Global Error -->
                    <template x-if="submitError">
                        <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded text-red-600 text-sm font-['Inter']" x-text="submitError"></div>
                    </template>
                </div>
