                <!-- STEP 1: Patron Info -->
                <div x-show="step === 'info'" class="p-6 md:p-9" style="display: none;">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-5">

                        <!-- ── Patron Category (FIRST) ── -->
                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-[10px] font-bold tracking-[0.08em] uppercase text-[var(--text-muted)] font-['Inter'] mb-1.5">
                                Patron Category <span class="text-[var(--cjc-red)]">*</span>
                            </label>
                            <template x-if="patronCategoriesLoaded">
                                <div class="relative" x-data="{ open: false }">
                                    <button type="button" @click="open = !open"
                                        class="w-full px-[13px] py-[10px] text-left font-['JetBrains_Mono'] text-sm font-medium bg-white border rounded-[var(--radius-md)] text-[var(--cjc-navy)] outline-none transition-all duration-150 flex justify-between items-center focus:border-[var(--cjc-navy)] focus:shadow-[0_0_0_3px_rgba(15,39,68,0.08)]"
                                        :class="{'border-[#dc2626]': errors.patronCategory, 'border-[var(--border-light)]': !errors.patronCategory}">
                                        <span x-text="form.patronCategory || '— Select Category —'"></span>
                                        <svg class="w-4 h-4 text-gray-500 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </button>
                                    <div x-show="open" @click.away="open = false" x-transition.opacity.duration.200ms class="absolute z-50 w-full mt-1 bg-white border border-[var(--border-light)] rounded-[var(--radius-md)] shadow-lg overflow-hidden py-1" style="display: none;">
                                        <template x-for="cat in patronCategories" :key="cat">
                                            <div @click="form.patronCategory = cat; onCategoryChange(); open = false"
                                                 class="px-[13px] py-1.5 text-sm font-['JetBrains_Mono'] cursor-pointer transition-colors hover:bg-gray-50 flex items-center justify-between"
                                                 :class="form.patronCategory === cat ? 'text-[var(--cjc-red)] bg-red-50/50' : 'text-[var(--cjc-navy)]'">
                                                <span x-text="cat"></span>
                                                <svg x-show="form.patronCategory === cat" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!patronCategoriesLoaded">
                                <div class="w-full px-[13px] py-[10px] bg-gray-50 border border-[var(--border-light)] rounded-[var(--radius-md)] flex items-center gap-2 text-[var(--text-muted)] text-sm">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                    Loading categories…
                                </div>
                            </template>
                            <template x-if="errors.patronCategory">
                                <p class="text-[11px] text-[#dc2626] font-['Inter'] mt-1" x-text="errors.patronCategory"></p>
                            </template>
                        </div>

                        <!-- ── ID Number ── -->
                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-[10px] font-bold tracking-[0.08em] uppercase text-[var(--text-muted)] font-['Inter'] mb-1.5">
                                ID Number
                                <template x-if="isVisitor">
                                    <span class="text-[var(--text-subtle)] font-normal normal-case tracking-normal">(optional — auto-generated for Visitors)</span>
                                </template>
                                <template x-if="!isVisitor">
                                    <span class="text-[var(--cjc-red)]">*</span>
                                </template>
                            </label>
                            <input
                                type="text"
                                x-model="form.studentId"
                                @input="checkPatronId()"
                                :placeholder="isVisitor ? 'Leave blank to auto-generate' : 'e.g. 2024-00123'"
                                class="w-full px-[13px] py-[10px] font-['JetBrains_Mono'] text-base font-semibold tracking-[0.06em] bg-white border rounded-[var(--radius-md)] text-[var(--cjc-navy)] outline-none transition-all duration-150 box-border focus:border-[var(--cjc-navy)] focus:shadow-[0_0_0_3px_rgba(15,39,68,0.08)]"
                                :class="{'border-[#dc2626]': errors.studentId, 'border-[var(--border-light)]': !errors.studentId}"
                            />
                            <template x-if="errors.studentId">
                                <p class="text-[11px] text-[#dc2626] font-['Inter'] mt-1" x-text="errors.studentId"></p>
                            </template>
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label class="block text-[10px] font-bold tracking-[0.08em] uppercase text-[var(--text-muted)] font-['Inter'] mb-1.5">
                                Last Name <span class="text-[var(--cjc-red)]">*</span>
                            </label>
                            <input
                                type="text"
                                x-model="form.lastName"
                                @input="errors.lastName = ''"
                                placeholder="Dela Cruz"
                                class="w-full px-[13px] py-[10px] font-['JetBrains_Mono'] text-sm font-medium bg-white border rounded-[var(--radius-md)] text-[var(--cjc-navy)] outline-none transition-all duration-150 box-border focus:border-[var(--cjc-navy)] focus:shadow-[0_0_0_3px_rgba(15,39,68,0.08)]"
                                :class="{'border-[#dc2626]': errors.lastName, 'border-[var(--border-light)]': !errors.lastName}"
                            />
                            <template x-if="errors.lastName">
                                <p class="text-[11px] text-[#dc2626] font-['Inter'] mt-1" x-text="errors.lastName"></p>
                            </template>
                        </div>

                        <!-- First Name -->
                        <div>
                            <label class="block text-[10px] font-bold tracking-[0.08em] uppercase text-[var(--text-muted)] font-['Inter'] mb-1.5">
                                First Name <span class="text-[var(--cjc-red)]">*</span>
                            </label>
                            <input
                                type="text"
                                x-model="form.firstName"
                                @input="errors.firstName = ''"
                                placeholder="Juan"
                                class="w-full px-[13px] py-[10px] font-['JetBrains_Mono'] text-sm font-medium bg-white border rounded-[var(--radius-md)] text-[var(--cjc-navy)] outline-none transition-all duration-150 box-border focus:border-[var(--cjc-navy)] focus:shadow-[0_0_0_3px_rgba(15,39,68,0.08)]"
                                :class="{'border-[#dc2626]': errors.firstName, 'border-[var(--border-light)]': !errors.firstName}"
                            />
                            <template x-if="errors.firstName">
                                <p class="text-[11px] text-[#dc2626] font-['Inter'] mt-1" x-text="errors.firstName"></p>
                            </template>
                        </div>

                        <!-- Middle Name -->
                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-[10px] font-bold tracking-[0.08em] uppercase text-[var(--text-muted)] font-['Inter'] mb-1.5">
                                Middle Name <span class="text-[var(--text-subtle)] font-normal normal-case tracking-normal">(optional)</span>
                            </label>
                            <input
                                type="text"
                                x-model="form.middleName"
                                placeholder="Santos"
                                class="w-full px-[13px] py-[10px] font-['JetBrains_Mono'] text-sm font-medium bg-white border border-[var(--border-light)] rounded-[var(--radius-md)] text-[var(--cjc-navy)] outline-none transition-all duration-150 box-border focus:border-[var(--cjc-navy)] focus:shadow-[0_0_0_3px_rgba(15,39,68,0.08)]"
                            />
                        </div>

                        <!-- ── Academic Fields (Student only) ── -->
                        <div class="col-span-1 sm:col-span-2" x-show="isStudent" x-transition:enter="field-slide-enter" style="display:none;">
                            <div class="flex flex-col gap-0">

                                <!-- Divider label -->
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="h-px flex-1 bg-[var(--border-light)]"></div>
                                    <span class="text-[10px] font-bold tracking-widest uppercase text-[var(--text-subtle)] font-['Inter']">Academic Information</span>
                                    <div class="h-px flex-1 bg-[var(--border-light)]"></div>
                                </div>

                                <!-- 1. Level -->
                                <div>
                                    <label class="block text-[10px] font-bold tracking-[0.08em] uppercase text-[var(--text-muted)] font-['Inter'] mb-1.5">
                                        Level <span class="text-[var(--cjc-red)]">*</span>
                                    </label>
                                    <div class="relative" x-data="{ open: false }" :class="open ? 'z-50' : 'z-10'">
                                        <button type="button" @click="open = !open"
                                            class="w-full px-[13px] py-[10px] text-left font-['JetBrains_Mono'] text-sm font-medium bg-white border rounded-[var(--radius-md)] text-[var(--cjc-navy)] outline-none transition-all duration-150 flex justify-between items-center focus:border-[var(--cjc-navy)] focus:shadow-[0_0_0_3px_rgba(15,39,68,0.08)]"
                                            :class="{'border-[#dc2626]': errors.level, 'border-[var(--border-light)]': !errors.level}">
                                            <span x-text="form.level === 'college' ? 'College' : (form.level === 'basic_ed' ? 'Basic Education' : '— Select Level —')"></span>
                                            <svg class="w-4 h-4 text-gray-500 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </button>
                                        <div x-show="open" @click.away="open = false" x-transition.opacity.duration.200ms class="absolute z-50 w-full mt-1 bg-white border border-[var(--border-light)] rounded-[var(--radius-md)] shadow-lg overflow-hidden py-1" style="display: none;">
                                            <template x-for="opt in [{value: 'college', label: 'College'}, {value: 'basic_ed', label: 'Basic Education'}]">
                                                <div @click="form.level = opt.value; onLevelChange(); open = false"
                                                     class="px-[13px] py-1.5 text-sm font-['JetBrains_Mono'] cursor-pointer transition-colors hover:bg-gray-50 flex items-center justify-between"
                                                     :class="form.level === opt.value ? 'text-[var(--cjc-red)] bg-red-50/50' : 'text-[var(--cjc-navy)]'">
                                                    <span x-text="opt.label"></span>
                                                    <svg x-show="form.level === opt.value" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <template x-if="errors.level">
                                        <p class="text-[11px] text-[#dc2626] font-['Inter'] mt-1" x-text="errors.level"></p>
                                    </template>
                                </div>

                                <div class="h-2.5 ml-[9px]">
                                    <div class="w-[1px] h-full transition-colors duration-200"
                                         :class="form.level ? 'bg-[var(--cjc-navy)] opacity-100' : 'bg-gray-200 opacity-40'"></div>
                                </div>

                                <!-- 2. College / Department -->
                                <div>
                                    <label class="block text-[10px] font-bold tracking-[0.08em] uppercase font-['Inter'] mb-1.5 transition-colors duration-200"
                                           :class="form.level ? 'text-[var(--text-muted)]' : 'text-gray-300'">
                                        <span x-text="form.level === 'basic_ed' ? 'Department' : 'College / School'"></span>
                                        <span :class="form.level ? 'text-[var(--cjc-red)]' : 'text-red-300'">*</span>
                                    </label>
                                    <div class="relative" x-data="{ open: false }" :class="open ? 'z-50' : 'z-10'">
                                        <button type="button" @click="if(form.level) open = !open"
                                            :disabled="!form.level"
                                            class="w-full px-[13px] py-[10px] text-left font-['JetBrains_Mono'] text-sm font-medium border rounded-[var(--radius-md)] text-[var(--cjc-navy)] outline-none transition-all duration-150 flex justify-between items-center focus:border-[var(--cjc-navy)] focus:shadow-[0_0_0_3px_rgba(15,39,68,0.08)]"
                                            :class="{
                                                'border-[#dc2626] bg-white cursor-pointer': errors.college && form.level,
                                                'border-[var(--border-light)] bg-white cursor-pointer': !errors.college && form.level,
                                                'bg-gray-50 border-gray-200 text-gray-400 cursor-not-allowed opacity-50': !form.level
                                            }">
                                            <span class="truncate pr-4" x-text="form.college ? form.college : (!form.level ? 'Select a level first' : (form.level === 'basic_ed' ? '— Select Department —' : '— Select College —'))"></span>
                                            <svg class="w-4 h-4 transition-transform duration-200 shrink-0" :class="{'rotate-180': open, 'text-gray-300': !form.level, 'text-gray-500': form.level}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </button>
                                        <div x-show="open" @click.away="open = false" x-transition.opacity.duration.200ms class="absolute z-50 w-full mt-1 bg-white border border-[var(--border-light)] rounded-[var(--radius-md)] shadow-lg overflow-hidden max-h-60 overflow-y-auto py-1" style="display: none;">
                                            <template x-for="c in collegeOptions" :key="c">
                                                <div @click="form.college = c; onCollegeChange(); open = false"
                                                     class="px-[13px] py-1.5 text-sm font-['JetBrains_Mono'] cursor-pointer transition-colors hover:bg-gray-50 flex items-center justify-between"
                                                     :class="form.college === c ? 'text-[var(--cjc-red)] bg-red-50/50' : 'text-[var(--cjc-navy)]'">
                                                    <span x-text="c"></span>
                                                    <svg x-show="form.college === c" class="w-4 h-4 shrink-0 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <template x-if="errors.college">
                                        <p class="text-[11px] text-[#dc2626] font-['Inter'] mt-1" x-text="errors.college"></p>
                                    </template>
                                </div>

                                <!-- 3. Program (College only) -->
                                <template x-if="form.level === 'college'">
                                    <div>
                                        <div class="h-2.5 ml-[9px]">
                                            <div class="w-[1px] h-full transition-colors duration-200"
                                                 :class="form.college ? 'bg-[var(--cjc-navy)] opacity-100' : 'bg-gray-200 opacity-40'"></div>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold tracking-[0.08em] uppercase font-['Inter'] mb-1.5 transition-colors duration-200"
                                                   :class="form.college ? 'text-[var(--text-muted)]' : 'text-gray-300'">
                                                Program / Course <span :class="form.college ? 'text-[var(--cjc-red)]' : 'text-red-300'">*</span>
                                            </label>
                                            <div class="relative" x-data="{ open: false }" :class="open ? 'z-50' : 'z-10'">
                                                <button type="button" @click="if(form.college) open = !open"
                                                    :disabled="!form.college"
                                                    class="w-full px-[13px] py-[10px] text-left font-['JetBrains_Mono'] text-sm font-medium border rounded-[var(--radius-md)] text-[var(--cjc-navy)] outline-none transition-all duration-150 flex justify-between items-center focus:border-[var(--cjc-navy)] focus:shadow-[0_0_0_3px_rgba(15,39,68,0.08)]"
                                                    :class="{
                                                        'border-[#dc2626] bg-white cursor-pointer': errors.department && form.college,
                                                        'border-[var(--border-light)] bg-white cursor-pointer': !errors.department && form.college,
                                                        'bg-gray-50 border-gray-200 text-gray-400 cursor-not-allowed opacity-50': !form.college
                                                    }">
                                                    <span class="truncate pr-4" x-text="form.department ? form.department : (!form.college ? 'Select a college first' : '— Select Program —')"></span>
                                                    <svg class="w-4 h-4 transition-transform duration-200 shrink-0" :class="{'rotate-180': open, 'text-gray-300': !form.college, 'text-gray-500': form.college}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                                </button>
                                                <div x-show="open" @click.away="open = false" x-transition.opacity.duration.200ms class="absolute z-50 w-full mt-1 bg-white border border-[var(--border-light)] rounded-[var(--radius-md)] shadow-lg overflow-hidden max-h-60 overflow-y-auto py-1" style="display: none;">
                                                    <template x-for="p in programOptions" :key="p">
                                                        <div @click="form.department = p; errors.department = ''; open = false"
                                                             class="px-[13px] py-1.5 text-sm font-['JetBrains_Mono'] cursor-pointer transition-colors hover:bg-gray-50 flex items-center justify-between"
                                                             :class="form.department === p ? 'text-[var(--cjc-red)] bg-red-50/50' : 'text-[var(--cjc-navy)]'">
                                                            <span class="truncate" x-text="p"></span>
                                                            <svg x-show="form.department === p" class="w-4 h-4 shrink-0 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <template x-if="errors.department">
                                                <p class="text-[11px] text-[#dc2626] font-['Inter'] mt-1" x-text="errors.department"></p>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                <!-- 4. Year Level -->
                                <div>
                                    <div class="h-2.5 ml-[9px]">
                                        <div class="w-[1px] h-full transition-colors duration-200"
                                             :class="(form.level === 'college' ? form.department : form.college) ? 'bg-[var(--cjc-navy)] opacity-100' : 'bg-gray-200 opacity-40'"></div>
                                    </div>
                                    <label class="block text-[10px] font-bold tracking-[0.08em] uppercase font-['Inter'] mb-1.5 transition-colors duration-200"
                                           :class="form.level ? 'text-[var(--text-muted)]' : 'text-gray-300'">
                                        <span x-text="form.level === 'basic_ed' ? 'Grade Level' : 'Year Level'"></span>
                                        <span :class="form.level ? 'text-[var(--cjc-red)]' : 'text-red-300'">*</span>
                                    </label>
                                    <div class="relative" x-data="{ open: false }">
                                        <button type="button" @click="if(form.level && !(form.level === 'basic_ed' && !form.college)) open = !open"
                                            :disabled="!form.level || (form.level === 'basic_ed' && !form.college)"
                                            class="w-full px-[13px] py-[10px] text-left font-['JetBrains_Mono'] text-sm font-medium border rounded-[var(--radius-md)] text-[var(--cjc-navy)] outline-none transition-all duration-150 flex justify-between items-center focus:border-[var(--cjc-navy)] focus:shadow-[0_0_0_3px_rgba(15,39,68,0.08)]"
                                            :class="{
                                                'border-[#dc2626] bg-white cursor-pointer': errors.yearLevel && form.level,
                                                'border-[var(--border-light)] bg-white cursor-pointer': !errors.yearLevel && form.level && !(form.level === 'basic_ed' && !form.college),
                                                'bg-gray-50 border-gray-200 text-gray-400 cursor-not-allowed opacity-50': !form.level || (form.level === 'basic_ed' && !form.college)
                                            }">
                                            <span class="truncate pr-4" x-text="form.yearLevel ? form.yearLevel : (!form.level ? 'Select a level first' : (form.level === 'basic_ed' && !form.college ? 'Select a department first' : (form.level === 'basic_ed' ? '— Select Grade —' : '— Select Year —')))"></span>
                                            <svg class="w-4 h-4 transition-transform duration-200 shrink-0" :class="{'rotate-180': open, 'text-gray-300': !form.level || (form.level === 'basic_ed' && !form.college), 'text-gray-500': form.level && !(form.level === 'basic_ed' && !form.college)}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </button>
                                        <div x-show="open" @click.away="open = false" x-transition.opacity.duration.200ms class="absolute z-50 w-full mt-1 bg-white border border-[var(--border-light)] rounded-[var(--radius-md)] shadow-lg overflow-hidden max-h-60 overflow-y-auto py-1" style="display: none;">
                                            <template x-for="y in yearOptions" :key="y">
                                                <div @click="form.yearLevel = y; errors.yearLevel = ''; open = false"
                                                     class="px-[13px] py-1.5 text-sm font-['JetBrains_Mono'] cursor-pointer transition-colors hover:bg-gray-50 flex items-center justify-between"
                                                     :class="form.yearLevel === y ? 'text-[var(--cjc-red)] bg-red-50/50' : 'text-[var(--cjc-navy)]'">
                                                    <span x-text="y"></span>
                                                    <svg x-show="form.yearLevel === y" class="w-4 h-4 shrink-0 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <template x-if="errors.yearLevel">
                                        <p class="text-[11px] text-[#dc2626] font-['Inter'] mt-1" x-text="errors.yearLevel"></p>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-[10px] font-bold tracking-[0.08em] uppercase text-[var(--text-muted)] font-['Inter'] mb-1.5">
                                Email Address <span class="text-[var(--text-subtle)] font-normal normal-case tracking-normal">(optional)</span>
                            </label>
                            <input
                                type="email"
                                x-model="form.email"
                                @input="errors.email = ''"
                                placeholder="juan@cjc.edu.ph"
                                class="w-full px-[13px] py-[10px] font-['JetBrains_Mono'] text-sm font-medium bg-white border rounded-[var(--radius-md)] text-[var(--cjc-navy)] outline-none transition-all duration-150 box-border focus:border-[var(--cjc-navy)] focus:shadow-[0_0_0_3px_rgba(15,39,68,0.08)]"
                                :class="{'border-[#dc2626]': errors.email, 'border-[var(--border-light)]': !errors.email}"
                            />
                            <template x-if="errors.email">
                                <p class="text-[11px] text-[#dc2626] font-['Inter'] mt-1" x-text="errors.email"></p>
                            </template>
                        </div>

                    </div>
                </div>
