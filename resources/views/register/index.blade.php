@extends('layouts.app')

@section('title', ' | Patron Registration')

@section('content')
<div x-data="registrationApp()" 
     class="min-h-screen bg-[var(--bg-cream)] hero-pattern flex flex-col relative overflow-hidden"
     @photo-captured.window="capturedImage = $event.detail.dataUrl; photoTaken = true;"
     @photo-retaken.window="capturedImage = null; photoTaken = false;">
     
    <style>
        .hero-pattern::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: url('/bg.jpg');
            background-size: cover;
            background-position: center;
            opacity: 0.30;
            z-index: 0;
        }
        
        .fade-in-up {
            animation: fadeInUp 0.4s ease-out;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .field-slide-enter {
            animation: fieldSlideIn 0.25s ease-out;
        }

        @keyframes fieldSlideIn {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

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

    <!-- Main -->
    <main class="flex-1 relative z-10 flex items-center justify-center p-6 md:p-8">
        <div class="fade-in-up w-full max-w-xl">
            <template x-if="step !== 'done'">
                <div>
                    <div class="mb-6 text-center">
                        <h1 class="font-['Fraunces'] text-2xl md:text-[28px] font-bold text-[var(--cjc-navy)] m-0 mb-1">
                            Patron Registration
                        </h1>
                        <p class="text-[13px] text-[var(--text-muted)] font-['Inter'] m-0">
                            Create your library account to check in and out.
                        </p>
                    </div>

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
                </div>
            </template>

            <!-- Card -->
            <div class="bg-white border border-[var(--border-light)] rounded-[var(--radius-xl)] shadow-[var(--shadow-lg)] overflow-hidden">

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
                                    <div class="relative" x-data="{ open: false }">
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
                                    <div class="relative" x-data="{ open: false }">
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
                                            <div class="relative" x-data="{ open: false }">
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

                <!-- STEP 2: Photo -->
                <div x-show="step === 'photo'" class="p-6 md:p-9 flex flex-col items-center gap-5" style="display: none;">
                    <div class="text-center">
                        <h2 class="font-['Fraunces'] text-lg font-bold text-[var(--cjc-navy)] m-0 mb-1">
                            Capture Your Photo
                        </h2>
                        <p class="text-xs text-[var(--text-muted)] font-['Inter'] m-0">
                            Your photo will be used for identification at the library entrance. <span class="text-[var(--text-subtle)] italic">You may skip this step.</span>
                        </p>
                    </div>

                    <!-- Webcam Component -->
                    <div x-data="webcamApp">
                        <div class="flex flex-col items-center gap-4">
                            <!-- Viewfinder -->
                            <div class="w-[280px] h-[340px] rounded-[var(--radius-lg)] border border-[var(--border-light)] relative overflow-hidden bg-[#0a0a0a] flex items-center justify-center">
                                
                                <video x-show="!captured" x-ref="video" autoplay playsinline muted
                                       class="w-full h-full object-cover transform scale-x-[-1]"
                                       :style="status === 'ready' ? 'display: block;' : 'display: none;'"></video>

                                <img x-show="captured && capturedImage" :src="capturedImage"
                                     class="w-full h-full object-cover" style="display: none;" />

                                <!-- Loading state -->
                                <div x-show="!captured && status === 'loading'" class="flex flex-col items-center gap-2.5">
                                    <svg width="32" height="32" viewBox="0 0 32 32" class="animate-spin" fill="none">
                                        <circle cx="16" cy="16" r="13" stroke="rgba(255,255,255,0.2)" stroke-width="2"/>
                                        <path d="M16 3A13 13 0 0129 16" stroke="white" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    <span class="text-[11px] text-white/50 font-['Inter']">Starting camera…</span>
                                </div>

                                <!-- Error state -->
                                <div x-show="!captured && status === 'error'" class="p-5 text-center flex flex-col items-center gap-2.5" style="display: none;">
                                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                                        <circle cx="16" cy="16" r="13" stroke="#ef4444" stroke-width="1.5"/>
                                        <path d="M16 9v8M16 21v2" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    <span class="text-[11px] text-[#fca5a5] font-['Inter'] leading-relaxed" x-text="errorMsg"></span>
                                    <button @click="startCamera()" class="mt-1 px-3.5 py-1.5 bg-white/10 border border-white/20 rounded-md text-white text-[11px] font-['Inter'] cursor-pointer">
                                        Try Again
                                    </button>
                                </div>

                                <!-- Corner brackets -->
                                <template x-if="status === 'ready' || captured">
                                    <div>
                                        <div class="absolute w-[22px] h-[22px] border-2 border-[var(--cjc-red)] top-3 left-3 border-r-0 border-b-0"></div>
                                        <div class="absolute w-[22px] h-[22px] border-2 border-[var(--cjc-red)] top-3 right-3 border-l-0 border-b-0"></div>
                                        <div class="absolute w-[22px] h-[22px] border-2 border-[var(--cjc-red)] bottom-3 left-3 border-r-0 border-t-0"></div>
                                        <div class="absolute w-[22px] h-[22px] border-2 border-[var(--cjc-red)] bottom-3 right-3 border-l-0 border-t-0"></div>
                                    </div>
                                </template>

                                <!-- Checkmark -->
                                <div x-show="captured" class="absolute top-2.5 right-2.5 w-[30px] h-[30px] rounded-full bg-[#16a34a] flex items-center justify-center" style="display: none;">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M3 8l3.5 3.5L13 4" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>

                                <!-- Live indicator -->
                                <div x-show="!captured && status === 'ready'" class="absolute top-2.5 left-2.5 flex items-center gap-1.5 bg-black/50 rounded-full px-2 py-[3px]" style="display: none;">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#ef4444] block animate-pulse"></span>
                                    <span class="text-[10px] text-white font-['Inter'] font-semibold tracking-wider">LIVE</span>
                                </div>
                            </div>
                            
                            <canvas x-ref="canvas" style="display: none;"></canvas>
                            
                            <!-- Buttons -->
                            <div class="flex gap-2.5">
                                <button x-show="!captured" @click="handleCapture()" :disabled="status !== 'ready'"
                                        class="px-7 py-2.5 rounded-[var(--radius-md)] text-[13px] font-semibold font-['Inter'] flex items-center gap-2 transition-colors duration-150"
                                        :class="status === 'ready' ? 'bg-[var(--cjc-navy)] text-white cursor-pointer' : 'bg-[var(--border-light)] text-[var(--text-subtle)] cursor-not-allowed'">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <circle cx="8" cy="8" r="3" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M6 1.5h4l1 2h2.5a1 1 0 011 1v8a1 1 0 01-1 1H1.5a1 1 0 01-1-1v-8a1 1 0 011-1H4L6 1.5z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                    </svg>
                                    Capture Photo
                                </button>
                                
                                <button x-show="captured" @click="handleRetake()" style="display: none;"
                                        class="px-7 py-2.5 bg-transparent text-[var(--text-muted)] border border-[var(--border-warm)] rounded-[var(--radius-md)] text-[13px] font-medium font-['Inter'] cursor-pointer flex items-center gap-2">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                        <path d="M1 7A6 6 0 1013 7M1 7V3M1 7H5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Retake Photo
                                </button>
                            </div>
                            
                            <p class="text-[11px] text-[var(--text-subtle)] font-['Inter'] text-center m-0 max-w-[280px]"
                               x-text="captured ? 'Photo captured successfully. Click Retake if you want a new one.' : 'Make sure your face is clearly visible and well-lit.'">
                            </p>
                        </div>
                    </div>
                </div>

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

            </div>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
    const COLLEGE_YEAR_LEVELS = ["1st Year", "2nd Year", "3rd Year", "4th Year", "5th Year"];
    const JHS_YEAR_LEVELS     = ["Grade 7", "Grade 8", "Grade 9", "Grade 10"];
    const SHS_YEAR_LEVELS     = ["Grade 11", "Grade 12"];

    function registrationApp() {
        return {
            departmentsData: [],
            patronCategories: [],
            patronCategoriesLoaded: false,
            registeredId: null,

            step: 'info',
            currentTime: '',
            submitting: false,
            submitError: '',

            // Photo state
            capturedImage: null,
            photoTaken: false,

            steps: [
                { id: "info",    label: "Patron Info", num: 1 },
                { id: "photo",   label: "Photo",       num: 2 },
                { id: "confirm", label: "Confirm",     num: 3 },
            ],

            form: {
                patronCategory: '',
                studentId: '',
                lastName: '',
                firstName: '',
                middleName: '',
                level: '',
                college: '',
                department: '',
                yearLevel: '',
                email: '',
            },

            errors: {},

            get stepIdx() {
                return this.steps.findIndex(s => s.id === this.step);
            },

            get isStudent() {
                return this.form.patronCategory === 'Student';
            },

            get isVisitor() {
                return this.form.patronCategory === 'Visitor';
            },

            get collegeOptions() {
                if (!this.form.level) return [];
                return this.departmentsData.filter(d => d.level === this.form.level).map(d => d.name).sort();
            },

            get programOptions() {
                if (!this.form.college) return [];
                const dept = this.departmentsData.find(d => d.level === this.form.level && d.name === this.form.college);
                if (!dept || !dept.programs) return [];
                return dept.programs.map(p => p.name).sort();
            },

            get yearOptions() {
                if (!this.form.level) return [];
                if (this.form.level === 'basic_ed') {
                    if (/junior/i.test(this.form.college)) return JHS_YEAR_LEVELS;
                    if (/senior/i.test(this.form.college)) return SHS_YEAR_LEVELS;
                    return ["Grade 7","Grade 8","Grade 9","Grade 10","Grade 11","Grade 12"];
                }
                if (this.form.department) {
                    const dept = this.departmentsData.find(d => d.level === this.form.level && d.name === this.form.college);
                    if (dept) {
                        const prog = dept.programs.find(p => p.name === this.form.department);
                        if (prog && prog.years) {
                            return Array.from({ length: prog.years }, (_, i) => {
                                const n = i + 1;
                                const s = ["st","nd","rd"][n - 1] || "th";
                                return `${n}${s} Year`;
                            });
                        }
                    }
                }
                return COLLEGE_YEAR_LEVELS;
            },

            async init() {
                this.updateTime();
                setInterval(() => this.updateTime(), 30000);

                // Fetch academics and patron categories in parallel
                const [acadRes, catRes] = await Promise.allSettled([
                    fetch('/api/academics'),
                    fetch('/api/patron-categories'),
                ]);

                if (acadRes.status === 'fulfilled' && acadRes.value.ok) {
                    this.departmentsData = await acadRes.value.json();
                }
                if (catRes.status === 'fulfilled' && catRes.value.ok) {
                    this.patronCategories = await catRes.value.json();
                } else {
                    this.patronCategories = ['Student','Employee','Post Graduate','Alumni','Visitor'];
                }
                this.patronCategoriesLoaded = true;
            },

            updateTime() {
                this.currentTime = new Date().toLocaleTimeString("en-PH", { hour: "2-digit", minute: "2-digit" });
            },

            onCategoryChange() {
                this.errors.patronCategory = '';
                // Clear academic fields when switching from Student
                this.form.level = '';
                this.form.college = '';
                this.form.department = '';
                this.form.yearLevel = '';
                this.errors.level = '';
                this.errors.college = '';
                this.errors.department = '';
                this.errors.yearLevel = '';
            },

            onLevelChange() {
                this.errors.level = '';
                this.form.college = '';
                this.form.department = '';
                this.form.yearLevel = '';
                this.errors.college = '';
                this.errors.department = '';
                this.errors.yearLevel = '';
            },

            onCollegeChange() {
                this.errors.college = '';
                this.form.department = '';
                this.errors.department = '';
                this.form.yearLevel = '';
                this.errors.yearLevel = '';
            },

            async checkPatronId() {
                this.errors.studentId = '';
            },

            validateInfo() {
                const e = {};

                if (!this.form.patronCategory) e.patronCategory = "Please select a patron category";

                if (!this.isVisitor && !this.form.studentId.trim()) {
                    e.studentId = "ID Number is required";
                }

                if (!this.form.lastName.trim())  e.lastName  = "Last name is required";
                if (!this.form.firstName.trim()) e.firstName = "First name is required";

                if (this.isStudent) {
                    if (!this.form.level)      e.level    = "Please select a level";
                    if (!this.form.college)    e.college  = this.form.level === "basic_ed" ? "Please select a department" : "Please select a college";
                    if (this.form.level === "college" && !this.form.department) e.department = "Please select a program";
                    if (!this.form.yearLevel)  e.yearLevel = "Please select a year level";
                }

                if (this.form.email.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) {
                    e.email = "Invalid email address";
                }

                this.errors = e;
                return Object.keys(e).length === 0;
            },

            handleBack() {
                if (this.step === 'info') {
                    window.location.href = "{{ route('kiosk.index') }}";
                } else if (this.step === 'photo') {
                    this.step = 'info';
                } else if (this.step === 'confirm') {
                    this.step = 'photo';
                }
            },

            async handleNext() {
                if (this.step === 'info') {
                    if (this.validateInfo()) this.step = 'photo';
                } else if (this.step === 'photo') {
                    this.step = 'confirm';
                } else if (this.step === 'confirm') {
                    this.submitForm();
                }
            },

            async submitForm() {
                this.submitting = true;
                this.submitError = '';

                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

                try {
                    const response = await fetch("{{ route('register.store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            ...this.form,
                            photoDataUrl: this.capturedImage
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        this.registeredId = data.id || this.form.studentId;
                        this.step = 'done';
                    } else {
                        if (data.errors) {
                            if (data.errors.studentId) {
                                this.errors.studentId = "This ID is already registered.";
                                this.step = 'info';
                            } else {
                                this.submitError = "Please check your inputs.";
                                this.step = 'info';
                                Object.keys(data.errors).forEach(key => {
                                    this.errors[key] = data.errors[key][0];
                                });
                            }
                        } else {
                            this.submitError = data.message || "Registration failed. Please try again.";
                        }
                    }
                } catch (err) {
                    this.submitError = "A network error occurred. Please try again.";
                } finally {
                    this.submitting = false;
                }
            }
        };
    }
</script>
@endpush
