@extends('layouts.app')

@section('title', ' | Registration')

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
                <img src="/cjc-logo.jpeg" alt="CJC" class="w-full h-full object-cover" />
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
                            Student Registration
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

                <!-- STEP 1: Student Info -->
                <div x-show="step === 'info'" class="p-6 md:p-9" style="display: none;">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-5">

                        <!-- Student ID -->
                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-[10px] font-bold tracking-[0.08em] uppercase text-[var(--text-muted)] font-['Inter'] mb-1.5">
                                Student ID Number <span class="text-[var(--cjc-red)]">*</span>
                            </label>
                            <input
                                type="text"
                                x-model="form.studentId"
                                @input="checkStudentId()"
                                placeholder="e.g. 2024-00123"
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

                        <!-- Cascading dropdowns -->
                        <div class="col-span-1 sm:col-span-2 flex flex-col gap-0">
                            
                            <!-- 1. Level -->
                            <div>
                                <label class="block text-[10px] font-bold tracking-[0.08em] uppercase text-[var(--text-muted)] font-['Inter'] mb-1.5">
                                    Level <span class="text-[var(--cjc-red)]">*</span>
                                </label>
                                <select
                                    x-model="form.level"
                                    @change="onLevelChange()"
                                    class="w-full px-[13px] py-[10px] font-['JetBrains_Mono'] text-sm font-medium bg-white border rounded-[var(--radius-md)] text-[var(--cjc-navy)] outline-none transition-all duration-150 box-border focus:border-[var(--cjc-navy)] focus:shadow-[0_0_0_3px_rgba(15,39,68,0.08)] appearance-none bg-no-repeat bg-[right_12px_center] pr-9 cursor-pointer"
                                    :class="{'border-[#dc2626]': errors.level, 'border-[var(--border-light)]': !errors.level}"
                                    style="background-image: url('data:image/svg+xml,%3Csvg width=\'12\' height=\'8\' viewBox=\'0 0 12 8\' fill=\'none\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath d=\'M1 1l5 5 5-5\' stroke=\'%236b7280\' stroke-width=\'1.5\' stroke-linecap=\'round\'/%3E%3C/svg%3E');"
                                >
                                    <option value="">— Select Level —</option>
                                    <option value="college">College</option>
                                    <option value="basic_ed">Basic Education</option>
                                </select>
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
                                <select
                                    x-model="form.college"
                                    @change="onCollegeChange()"
                                    :disabled="!form.level"
                                    class="w-full px-[13px] py-[10px] font-['JetBrains_Mono'] text-sm font-medium border rounded-[var(--radius-md)] text-[var(--cjc-navy)] outline-none transition-all duration-150 box-border focus:border-[var(--cjc-navy)] focus:shadow-[0_0_0_3px_rgba(15,39,68,0.08)] appearance-none bg-no-repeat bg-[right_12px_center] pr-9"
                                    :class="{
                                        'border-[#dc2626] bg-white cursor-pointer': errors.college && form.level, 
                                        'border-[var(--border-light)] bg-white cursor-pointer': !errors.college && form.level,
                                        'bg-gray-50 border-gray-200 text-gray-400 cursor-not-allowed opacity-50': !form.level
                                    }"
                                    :style="!form.level ? 'background-image: url(\'data:image/svg+xml,%3Csvg width=12 height=8 viewBox=0 0 12 8 fill=none xmlns=http://www.w3.org/2000/svg%3E%3Cpath d=M1 1l5 5 5-5 stroke=%23d1d5db stroke-width=1.5 stroke-linecap=round/%3E%3C/svg%3E\');' : 'background-image: url(\'data:image/svg+xml,%3Csvg width=12 height=8 viewBox=0 0 12 8 fill=none xmlns=http://www.w3.org/2000/svg%3E%3Cpath d=M1 1l5 5 5-5 stroke=%236b7280 stroke-width=1.5 stroke-linecap=round/%3E%3C/svg%3E\');'"
                                >
                                    <option value="" x-text="!form.level ? 'Select a level first' : (form.level === 'basic_ed' ? '— Select Department —' : '— Select College —')"></option>
                                    <template x-for="c in collegeOptions" :key="c">
                                        <option :value="c" x-text="c"></option>
                                    </template>
                                </select>
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
                                        <select
                                            x-model="form.department"
                                            @change="errors.department = ''"
                                            :disabled="!form.college"
                                            class="w-full px-[13px] py-[10px] font-['JetBrains_Mono'] text-sm font-medium border rounded-[var(--radius-md)] text-[var(--cjc-navy)] outline-none transition-all duration-150 box-border focus:border-[var(--cjc-navy)] focus:shadow-[0_0_0_3px_rgba(15,39,68,0.08)] appearance-none bg-no-repeat bg-[right_12px_center] pr-9"
                                            :class="{
                                                'border-[#dc2626] bg-white cursor-pointer': errors.department && form.college, 
                                                'border-[var(--border-light)] bg-white cursor-pointer': !errors.department && form.college,
                                                'bg-gray-50 border-gray-200 text-gray-400 cursor-not-allowed opacity-50': !form.college
                                            }"
                                            :style="!form.college ? 'background-image: url(\'data:image/svg+xml,%3Csvg width=12 height=8 viewBox=0 0 12 8 fill=none xmlns=http://www.w3.org/2000/svg%3E%3Cpath d=M1 1l5 5 5-5 stroke=%23d1d5db stroke-width=1.5 stroke-linecap=round/%3E%3C/svg%3E\');' : 'background-image: url(\'data:image/svg+xml,%3Csvg width=12 height=8 viewBox=0 0 12 8 fill=none xmlns=http://www.w3.org/2000/svg%3E%3Cpath d=M1 1l5 5 5-5 stroke=%236b7280 stroke-width=1.5 stroke-linecap=round/%3E%3C/svg%3E\');'"
                                        >
                                            <option value="" x-text="!form.college ? 'Select a college first' : '— Select Program —'"></option>
                                            <template x-for="p in programOptions" :key="p">
                                                <option :value="p" x-text="p"></option>
                                            </template>
                                        </select>
                                        <template x-if="errors.department">
                                            <p class="text-[11px] text-[#dc2626] font-['Inter'] mt-1" x-text="errors.department"></p>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- 4. Year Level -->
                        <div :class="form.level === 'basic_ed' ? 'col-span-1 sm:col-span-2' : ''">
                            <label class="block text-[10px] font-bold tracking-[0.08em] uppercase font-['Inter'] mb-1.5 transition-colors duration-200"
                                   :class="form.level ? 'text-[var(--text-muted)]' : 'text-gray-300'">
                                <span x-text="form.level === 'basic_ed' ? 'Grade Level' : 'Year Level'"></span>
                                <span :class="form.level ? 'text-[var(--cjc-red)]' : 'text-red-300'">*</span>
                            </label>
                            <select
                                x-model="form.yearLevel"
                                @change="errors.yearLevel = ''"
                                :disabled="!form.level || (form.level === 'basic_ed' && !form.college)"
                                class="w-full px-[13px] py-[10px] font-['JetBrains_Mono'] text-sm font-medium border rounded-[var(--radius-md)] text-[var(--cjc-navy)] outline-none transition-all duration-150 box-border focus:border-[var(--cjc-navy)] focus:shadow-[0_0_0_3px_rgba(15,39,68,0.08)] appearance-none bg-no-repeat bg-[right_12px_center] pr-9"
                                :class="{
                                    'border-[#dc2626] bg-white cursor-pointer': errors.yearLevel && form.level, 
                                    'border-[var(--border-light)] bg-white cursor-pointer': !errors.yearLevel && form.level && !(form.level === 'basic_ed' && !form.college),
                                    'bg-gray-50 border-gray-200 text-gray-400 cursor-not-allowed opacity-50': !form.level || (form.level === 'basic_ed' && !form.college)
                                }"
                                :style="(!form.level || (form.level === 'basic_ed' && !form.college)) ? 'background-image: url(\'data:image/svg+xml,%3Csvg width=12 height=8 viewBox=0 0 12 8 fill=none xmlns=http://www.w3.org/2000/svg%3E%3Cpath d=M1 1l5 5 5-5 stroke=%23d1d5db stroke-width=1.5 stroke-linecap=round/%3E%3C/svg%3E\');' : 'background-image: url(\'data:image/svg+xml,%3Csvg width=12 height=8 viewBox=0 0 12 8 fill=none xmlns=http://www.w3.org/2000/svg%3E%3Cpath d=M1 1l5 5 5-5 stroke=%236b7280 stroke-width=1.5 stroke-linecap=round/%3E%3C/svg%3E\');'"
                            >
                                <option value="" x-text="!form.level ? 'Select a level first' : (form.level === 'basic_ed' && !form.college ? 'Select a department first' : (form.level === 'basic_ed' ? '— Select Grade —' : '— Select Year —'))"></option>
                                <template x-for="y in yearOptions" :key="y">
                                    <option :value="y" x-text="y"></option>
                                </template>
                            </select>
                            <template x-if="errors.yearLevel">
                                <p class="text-[11px] text-[#dc2626] font-['Inter'] mt-1" x-text="errors.yearLevel"></p>
                            </template>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-[10px] font-bold tracking-[0.08em] uppercase text-[var(--text-muted)] font-['Inter'] mb-1.5">
                                Email Address <span class="text-[var(--cjc-red)]">*</span>
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

                        <!-- Contact -->
                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-[10px] font-bold tracking-[0.08em] uppercase text-[var(--text-muted)] font-['Inter'] mb-1.5">
                                Contact Number <span class="text-[var(--text-subtle)] font-normal normal-case tracking-normal">(optional)</span>
                            </label>
                            <input
                                type="tel"
                                x-model="form.contactNumber"
                                placeholder="09XX-XXX-XXXX"
                                class="w-full px-[13px] py-[10px] font-['JetBrains_Mono'] text-sm font-medium bg-white border border-[var(--border-light)] rounded-[var(--radius-md)] text-[var(--cjc-navy)] outline-none transition-all duration-150 box-border focus:border-[var(--cjc-navy)] focus:shadow-[0_0_0_3px_rgba(15,39,68,0.08)]"
                            />
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
                            <!-- Student ID -->
                            <div class="flex justify-between gap-4 py-1.5 border-b border-black/5">
                                <span class="text-[11px] text-[var(--text-muted)] font-['Inter'] shrink-0">Student ID</span>
                                <span class="text-[11px] font-semibold text-[var(--cjc-navy)] font-['JetBrains_Mono'] text-right" x-text="form.studentId"></span>
                            </div>
                            <!-- Name -->
                            <div class="flex justify-between gap-4 py-1.5 border-b border-black/5">
                                <span class="text-[11px] text-[var(--text-muted)] font-['Inter'] shrink-0">Name</span>
                                <span class="text-[11px] font-semibold text-[var(--cjc-navy)] font-['Inter'] text-right" 
                                      x-text="`${form.lastName.toUpperCase()}, ${form.firstName.toUpperCase()}${form.middleName ? ' ' + form.middleName.toUpperCase() : ''}`"></span>
                            </div>
                            <!-- Level -->
                            <div class="flex justify-between gap-4 py-1.5 border-b border-black/5">
                                <span class="text-[11px] text-[var(--text-muted)] font-['Inter'] shrink-0">Level</span>
                                <span class="text-[11px] font-semibold text-[var(--cjc-navy)] font-['Inter'] text-right" x-text="form.level === 'college' ? 'College' : 'Basic Education'"></span>
                            </div>
                            <!-- College/Dept -->
                            <div class="flex justify-between gap-4 py-1.5 border-b border-black/5">
                                <span class="text-[11px] text-[var(--text-muted)] font-['Inter'] shrink-0" x-text="form.level === 'basic_ed' ? 'Department' : 'College'"></span>
                                <span class="text-[11px] font-semibold text-[var(--cjc-navy)] font-['Inter'] text-right" x-text="form.college"></span>
                            </div>
                            <!-- Program (if college) -->
                            <template x-if="form.level === 'college'">
                                <div class="flex justify-between gap-4 py-1.5 border-b border-black/5">
                                    <span class="text-[11px] text-[var(--text-muted)] font-['Inter'] shrink-0">Program</span>
                                    <span class="text-[11px] font-semibold text-[var(--cjc-navy)] font-['Inter'] text-right" x-text="form.department"></span>
                                </div>
                            </template>
                            <!-- Year/Grade Level -->
                            <div class="flex justify-between gap-4 py-1.5 border-b border-black/5">
                                <span class="text-[11px] text-[var(--text-muted)] font-['Inter'] shrink-0" x-text="form.level === 'basic_ed' ? 'Grade Level' : 'Year Level'"></span>
                                <span class="text-[11px] font-semibold text-[var(--cjc-navy)] font-['Inter'] text-right" x-text="form.yearLevel"></span>
                            </div>
                            <!-- Email -->
                            <template x-if="form.email">
                                <div class="flex justify-between gap-4 py-1.5 border-b border-black/5">
                                    <span class="text-[11px] text-[var(--text-muted)] font-['Inter'] shrink-0">Email</span>
                                    <span class="text-[11px] font-semibold text-[var(--cjc-navy)] font-['Inter'] text-right" x-text="form.email"></span>
                                </div>
                            </template>
                            <!-- Contact -->
                            <template x-if="form.contactNumber">
                                <div class="flex justify-between gap-4 py-1.5 border-b border-black/5">
                                    <span class="text-[11px] text-[var(--text-muted)] font-['Inter'] shrink-0">Contact</span>
                                    <span class="text-[11px] font-semibold text-[var(--cjc-navy)] font-['Inter'] text-right" x-text="form.contactNumber"></span>
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
                        Welcome to the CJC Library, <strong class="text-[var(--cjc-navy)]" x-text="form.firstName"></strong>! Your account has been created. You can now scan your Student ID at the kiosk to check in.
                    </p>
                    <div class="bg-[var(--bg-cream-2)] border border-[var(--border-warm)] rounded-[var(--radius-md)] px-5 py-2.5 mt-1">
                        <span class="font-['JetBrains_Mono'] text-lg font-bold text-[var(--cjc-navy)] tracking-[0.05em]" x-text="form.studentId"></span>
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
    // Define the departments structure directly in JS
    const DEPARTMENTS = [
        { name: "Bachelor of Science in Accountancy", college: "College of Business and Accountancy", level: "college" },
        { name: "Bachelor of Science in Business Administration", college: "College of Business and Accountancy", level: "college" },
        { name: "Bachelor of Science in Information Technology", college: "College of Computer Studies", level: "college" },
        { name: "Bachelor of Science in Computer Science", college: "College of Computer Studies", level: "college" },
        { name: "Bachelor of Elementary Education", college: "College of Education", level: "college" },
        { name: "Bachelor of Secondary Education", college: "College of Education", level: "college" },
        { name: "Bachelor of Science in Civil Engineering", college: "College of Engineering", level: "college" },
        { name: "Bachelor of Science in Computer Engineering", college: "College of Engineering", level: "college" },
        { name: "Bachelor of Science in Nursing", college: "College of Health Sciences", level: "college" },
        { name: "Bachelor of Arts in Psychology", college: "College of Arts and Sciences", level: "college" },
        { name: "Junior High School", college: "Junior High School", level: "basic_ed" },
        { name: "Senior High School", college: "Senior High School", level: "basic_ed" },
    ];

    const COLLEGE_YEAR_LEVELS = ["1st Year", "2nd Year", "3rd Year", "4th Year", "5th Year"];
    const JHS_YEAR_LEVELS = ["Grade 7", "Grade 8", "Grade 9", "Grade 10"];
    const SHS_YEAR_LEVELS = ["Grade 11", "Grade 12"];

    function registrationApp() {
        return {
            step: 'info',
            currentTime: '',
            submitting: false,
            submitError: '',
            
            // Photo state
            capturedImage: null,
            photoTaken: false,

            steps: [
                { id: "info", label: "Student Info", num: 1 },
                { id: "photo", label: "Photo", num: 2 },
                { id: "confirm", label: "Confirm", num: 3 },
            ],

            form: {
                studentId: '',
                lastName: '',
                firstName: '',
                middleName: '',
                level: '',
                college: '',
                department: '',
                yearLevel: '',
                email: '',
                contactNumber: '',
            },

            errors: {},

            get stepIdx() {
                return this.steps.findIndex(s => s.id === this.step);
            },

            get collegeOptions() {
                if (!this.form.level) return [];
                const options = new Set(DEPARTMENTS.filter(d => d.level === this.form.level).map(d => d.college));
                return Array.from(options).sort();
            },

            get programOptions() {
                if (!this.form.college) return [];
                return DEPARTMENTS.filter(d => d.level === this.form.level && d.college === this.form.college).map(d => d.name).sort();
            },

            get yearOptions() {
                if (!this.form.level) return [];
                if (this.form.level === 'basic_ed') {
                    if (/junior/i.test(this.form.college)) return JHS_YEAR_LEVELS;
                    if (/senior/i.test(this.form.college)) return SHS_YEAR_LEVELS;
                    return [];
                }
                return COLLEGE_YEAR_LEVELS;
            },

            init() {
                this.updateTime();
                setInterval(() => this.updateTime(), 30000);
            },

            updateTime() {
                this.currentTime = new Date().toLocaleTimeString("en-PH", { hour: "2-digit", minute: "2-digit" });
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
            },

            async checkStudentId() {
                this.errors.studentId = '';
                // Since this runs locally, we could do an API check here, 
                // but the form submission will validate uniqueness anyway.
            },

            validateInfo() {
                const e = {};
                if (!this.form.studentId.trim()) e.studentId = "Student ID is required";
                if (!this.form.lastName.trim()) e.lastName = "Last name is required";
                if (!this.form.firstName.trim()) e.firstName = "First name is required";
                if (!this.form.level) e.level = "Please select a level";
                if (!this.form.college) e.college = this.form.level === "basic_ed" ? "Please select a department" : "Please select a college";
                if (this.form.level === "college" && !this.form.department) e.department = "Please select a program";
                if (!this.form.yearLevel) e.yearLevel = "Please select a year level";
                
                if (!this.form.email.trim()) {
                    e.email = "Email is required";
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) {
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
                    if (this.validateInfo()) {
                        this.step = 'photo';
                    }
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
                        this.step = 'done';
                    } else {
                        // Handle validation errors from Laravel
                        if (data.errors) {
                            if (data.errors.studentId) {
                                this.errors.studentId = "This Student ID is already registered.";
                                this.step = 'info';
                            } else {
                                this.submitError = "Please check your inputs.";
                                this.step = 'info';
                                // Merge errors
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
