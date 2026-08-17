<div class="bg-white w-full h-full relative flex flex-col"
     :class="{
         'border-red-500': result?.status === 'error',
         'border-orange-500': result?.status === 'offline'
     }">

    <!-- Unregistered Student State -->
    <template x-if="result?.status === 'error' && result?.action === 'unregistered'">
        <div class="p-8 pb-10 text-center flex-1 flex flex-col items-center justify-center relative w-full overflow-hidden">
            
            <!-- Floating Sparkles (Background) -->
            <div class="absolute inset-0 pointer-events-none z-0">
                <div class="absolute top-[15%] left-[25%] w-2 h-2 bg-blue-600 transform rotate-45 opacity-60 rounded-sm"></div>
                <div class="absolute top-[22%] left-[28%] w-2 h-2 border border-blue-300 transform rotate-45 opacity-60"></div>
                <div class="absolute top-[35%] left-[22%] text-blue-600 font-bold opacity-60 rotate-[15deg] text-xl leading-none">+</div>
                
                <div class="absolute top-[18%] right-[25%] w-2 h-2 bg-blue-600 transform rotate-45 opacity-60 rounded-sm"></div>
                <div class="absolute top-[26%] right-[28%] w-2 h-2 border border-blue-300 transform rotate-45 opacity-60"></div>
                <div class="absolute top-[32%] right-[20%] w-2.5 h-2.5 border-[1.5px] border-blue-600 transform rotate-45 opacity-60 rounded-sm"></div>
            </div>

            <!-- Info / Registration Icon -->
            <div class="relative z-10 mb-4 mt-2">
                <div class="w-32 h-32 rounded-full bg-blue-50/70 flex items-center justify-center mx-auto relative before:absolute before:inset-2 before:rounded-full before:bg-blue-50 before:shadow-[0_0_30px_rgba(37,99,235,0.15)]">
                    <div class="w-[85px] h-[85px] rounded-full bg-white border-[3px] border-blue-100 flex items-center justify-center shadow-md relative z-10">
                        <svg class="w-[45px] h-[45px] text-[#0f2744]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Text Content -->
            <h3 class="text-[34px] font-extrabold text-[#08152c] mb-1 relative z-10 tracking-tight">Registration Required</h3>
            
            <!-- Decorative Line -->
            <div class="flex items-center justify-center gap-2 mb-3 relative z-10">
                <div class="h-[1.5px] w-8 bg-blue-400"></div>
                <div class="w-1.5 h-1.5 rounded-full bg-blue-600"></div>
                <div class="h-[1.5px] w-8 bg-blue-400"></div>
            </div>

            <p class="text-[16px] text-gray-600 font-medium relative z-10 max-w-[500px]" x-text="result?.message"></p>

            <!-- ID Display Box -->
            <div class="mt-6 mb-6 w-full max-w-[550px] bg-blue-50/50 border border-blue-100 rounded-[16px] p-4 flex items-center justify-between shadow-[0_4px_20px_rgba(0,0,0,0.02)] relative z-10 text-left mx-auto">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100/70 text-[#0f2744] flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="7" width="18" height="12" rx="2" />
                            <circle cx="8" cy="12" r="2" />
                            <path d="M5 16c0-1.5 1.5-3 3-3s3 1.5 3 3" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Scanned ID Number</div>
                        <div class="text-[16px] font-bold text-[#08152c] font-['JetBrains_Mono']" x-text="result?.student?.id"></div>
                    </div>
                </div>
                <span class="px-3 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-full uppercase tracking-wider">Unregistered</span>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col gap-3 w-full max-w-[550px] mx-auto relative z-10">
                <a :href="'/register?student_id=' + encodeURIComponent(result?.student?.id || '')" 
                   class="w-full p-[18px] bg-gradient-to-b from-[#0f2744] to-[#0a192f] text-white rounded-[14px] flex items-center justify-center gap-3 shadow-[0_8px_20px_rgba(15,39,68,0.25)] hover:shadow-[0_8px_25px_rgba(15,39,68,0.35)] hover:from-[#163a66] hover:to-[#0f2744] transition-all no-underline">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span class="text-[17px] font-bold tracking-wide">Register This Student ID</span>
                </a>
                
                <button @click="resetScan()" class="w-full p-[14px] bg-white border border-gray-200 text-gray-700 rounded-[14px] flex items-center justify-center gap-2 hover:bg-gray-50 transition-all text-[15px] font-bold tracking-wide shadow-sm">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 8V6a2 2 0 012-2h3M5 16v2a2 2 0 002 2h3M19 8V6a2 2 0 00-2-2h-3M19 16v2a2 2 0 01-2 2h-3" />
                    </svg>
                    Scan Another ID
                </button>
            </div>
        </div>
    </template>

    <!-- Error & Cooldown State (Excludes Unregistered) -->
    <template x-if="(result?.status === 'error' && result?.action !== 'unregistered') || result?.status === 'cooldown'">
        <div class="p-8 pb-10 text-center flex-1 flex flex-col items-center justify-center relative w-full overflow-hidden">
            
            <!-- Floating Sparkles (Background) -->
            <div class="absolute inset-0 pointer-events-none z-0">
                <div class="absolute top-[15%] left-[25%] w-2 h-2 bg-red-600 transform rotate-45 opacity-80 rounded-sm"></div>
                <div class="absolute top-[22%] left-[28%] w-2 h-2 border border-red-300 transform rotate-45 opacity-60"></div>
                <div class="absolute top-[35%] left-[22%] text-red-600 font-bold opacity-80 rotate-[15deg] text-xl leading-none">&times;</div>
                
                <div class="absolute top-[18%] right-[25%] w-2 h-2 bg-red-600 transform rotate-45 opacity-80 rounded-sm"></div>
                <div class="absolute top-[26%] right-[28%] w-2 h-2 border border-red-300 transform rotate-45 opacity-60"></div>
                <div class="absolute top-[32%] right-[20%] w-2.5 h-2.5 border-[1.5px] border-red-600 transform rotate-45 opacity-80 rounded-sm"></div>
            </div>

            <!-- Warning Icon -->
            <div class="relative z-10 mb-4 mt-2">
                <div class="w-32 h-32 rounded-full bg-red-50/50 flex items-center justify-center mx-auto relative before:absolute before:inset-2 before:rounded-full before:bg-red-50 before:shadow-[0_0_30px_rgba(220,38,38,0.15)]">
                    <div class="w-[85px] h-[85px] rounded-full bg-white border-[3px] border-red-100 flex items-center justify-center shadow-md relative z-10">
                        <!-- Switch icon based on error or cooldown -->
                        <template x-if="result?.status === 'cooldown'">
                            <svg class="w-[45px] h-[45px] text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>
                        <template x-if="result?.status === 'error'">
                            <svg class="w-[45px] h-[45px] text-[#e31818]" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Text Content -->
            <h3 class="text-[34px] font-extrabold text-[#08152c] mb-1 relative z-10 tracking-tight" x-text="result?.status === 'cooldown' ? 'Please Wait' : 'Access Denied'"></h3>
            
            <!-- Decorative Line -->
            <div class="flex items-center justify-center gap-2 mb-3 relative z-10">
                <div class="h-[1.5px] w-8 bg-red-400"></div>
                <div class="w-1.5 h-1.5 rounded-full bg-red-600"></div>
                <div class="h-[1.5px] w-8 bg-red-400"></div>
            </div>

            <p class="text-[17px] text-[#e31818] font-medium relative z-10" x-text="result?.message"></p>

            <!-- Info Box (Only for Access Denied / Inactive) -->
            <template x-if="result?.status === 'error'">
                <div class="mt-8 mb-6 w-full max-w-[550px] bg-white border border-gray-100 rounded-[16px] p-5 flex items-center gap-5 shadow-[0_4px_20px_rgba(0,0,0,0.03)] relative z-10 text-left mx-auto">
                    <div class="w-[52px] h-[52px] rounded-[14px] bg-red-50 flex items-center justify-center shrink-0">
                        <svg class="w-7 h-7 text-[#e31818]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <rect x="3" y="7" width="18" height="12" rx="2" />
                            <circle cx="8" cy="12" r="2" />
                            <path d="M5 16c0-1.5 1.5-3 3-3s3 1.5 3 3" />
                            <path d="M14 12h4M14 15h2" stroke-linecap="round" />
                            <circle cx="12" cy="7" r="1.5" fill="currentColor" stroke="currentColor" />
                        </svg>
                    </div>
                    <div class="h-10 w-px bg-gray-200 shrink-0"></div>
                    <div>
                        <h4 class="text-[#08152c] font-bold text-[15px] mb-0.5" x-text="result?.action === 'inactive' ? 'Inactive Account' : 'Access Restricted'"></h4>
                        <p class="text-gray-500 text-[13px] m-0" x-text="result?.action === 'inactive' ? 'This patron account is marked inactive. Please consult the librarian.' : 'Please verify with the library administrator.'"></p>
                    </div>
                </div>
            </template>
            
            <template x-if="result?.status === 'cooldown'">
                <div class="mt-8 mb-6 h-4"></div>
            </template>

            <!-- Button -->
            <button @click="resetScan()" class="w-full max-w-[550px] mx-auto p-[18px] bg-gradient-to-b from-[#e31818] to-[#bd0e0e] text-white rounded-[14px] flex items-center justify-center gap-3 shadow-[0_8px_20px_rgba(227,24,24,0.25)] hover:shadow-[0_8px_25px_rgba(227,24,24,0.35)] hover:from-[#f01919] hover:to-[#a80c0c] transition-all relative z-10">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8V6a2 2 0 012-2h3M5 16v2a2 2 0 002 2h3M19 8V6a2 2 0 00-2-2h-3M19 16v2a2 2 0 01-2 2h-3" />
                    <line x1="10" y1="12" x2="14" y2="12" stroke-width="2" stroke-linecap="round" />
                </svg>
                <span class="text-[17px] font-bold tracking-wide">Scan Another ID</span>
            </button>
        </div>
    </template>

    <!-- Offline State -->
    <template x-if="result?.status === 'offline'">
        <div class="p-8 text-center flex-1 flex flex-col items-center justify-center">
            <div class="w-20 h-20 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-3xl font-bold text-gray-900 mb-3">Saved Offline</h3>
            <p class="text-xl text-gray-600">Action for ID <span class="font-bold text-gray-900" x-text="result?.student_id"></span> has been queued.</p>
            <p class="text-base text-orange-600 mt-2 font-medium">Will sync when connection restores.</p>
            <button @click="resetScan()" class="mt-8 w-full p-4 bg-orange-500 text-white rounded-[12px] text-[16px] font-bold shadow-md hover:bg-orange-600 transition-colors">Scan Another ID</button>
        </div>
    </template>

    <!-- Success State -->
    <template x-if="result?.status === 'success'">
        <div class="flex flex-col w-full h-full relative">
            
            <!-- Custom Header Area -->
            <div class="relative w-full h-[140px] flex items-center px-8 z-10 overflow-hidden shrink-0">
                <!-- Red Swoosh Background (Top Right) -->
                <div class="absolute top-0 right-0 w-[65%] h-full">
                    <svg viewBox="0 0 300 140" preserveAspectRatio="none" class="w-full h-full">
                        <path d="M300,0 L300,140 L120,140 C140,90 80,30 0,0 Z" fill="#b90e22" opacity="0.3"/>
                        <path d="M300,0 L300,140 L160,140 C160,140 120,20 0,0 Z" fill="#D31027"/>
                    </svg>
                </div>

                <!-- Left Content: Scan ID -->
                <div class="relative z-10 flex items-center gap-4 w-1/2">
                    <div class="w-14 h-14 bg-red-50 rounded-xl border-2 border-red-100 flex items-center justify-center shrink-0">
                        <!-- Custom Scan Icon matching design -->
                        <svg class="w-8 h-8 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 8V6a2 2 0 012-2h2M4 16v2a2 2 0 002 2h2M20 8V6a2 2 0 00-2-2h-2M20 16v2a2 2 0 01-2 2h-2" stroke-linecap="round"/>
                            <rect x="7" y="8" width="10" height="8" rx="1" fill="#fee2e2" stroke="none" />
                            <rect x="7" y="8" width="10" height="8" rx="1" />
                            <circle cx="10.5" cy="11.5" r="1.5" fill="currentColor" stroke="none" />
                            <path d="M14 11h1M14 13h2" stroke-linecap="round" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-[22px] font-bold text-[#0a192f] leading-tight m-0 tracking-tight">Scan ID</h2>
                        <p class="text-[12px] text-gray-500 leading-tight m-0 mt-1">Present your Student ID<br>to check in or out.</p>
                    </div>
                </div>

                <!-- Right Content: Cor Jesu Logo -->
                <div class="relative z-10 w-1/2 flex justify-end pr-4">
                    <img src="/CorJesu Logo.png" alt="Cor Jesu College" class="h-[75px] w-auto drop-shadow-md">
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1 px-8 pb-8 flex flex-col items-center bg-white rounded-t-[30px] relative z-20 -mt-6">
                
                <!-- Checkmark & Welcome -->
                <div class="text-center mt-6 mb-6 relative w-full">
                    <!-- Sparkles/Confetti -->
                    <div class="absolute inset-0 pointer-events-none opacity-60 z-0">
                        <div class="absolute top-4 left-[25%] w-2 h-2 bg-red-400 rounded-full"></div>
                        <div class="absolute top-8 left-[20%] w-3 h-3 bg-red-200 transform rotate-45"></div>
                        <div class="absolute top-14 left-[30%] w-1.5 h-1.5 bg-red-500 transform rotate-45"></div>
                        <div class="absolute top-2 right-[25%] w-2 h-2 bg-red-500 rounded-full"></div>
                        <div class="absolute top-10 right-[20%] w-3 h-3 bg-red-300 transform rotate-45"></div>
                        <div class="absolute top-16 right-[28%] w-1.5 h-1.5 bg-red-400 transform rotate-45"></div>
                    </div>

                    <!-- Red Checkmark Circle -->
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-red-50 border-[3px] border-red-100 mb-4 relative z-10 shadow-[0_0_20px_rgba(211,16,39,0.1)]">
                        <div class="w-14 h-14 rounded-full bg-red-50 border-2 border-red-500 flex items-center justify-center">
                            <svg class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                    
                    <h1 class="text-4xl font-extrabold text-[#0a192f] tracking-tight mb-2 relative z-10">Welcome!</h1>
                    <div class="flex items-center justify-center gap-3 relative z-10">
                        <div class="w-8 h-px bg-red-500"></div>
                        <p class="text-[15px] font-bold text-red-600 m-0 tracking-wide" x-text="result?.message || 'Successfully checked in.'"></p>
                        <div class="w-8 h-px bg-red-500"></div>
                    </div>
                </div>

                <!-- Student Info Card (Matches Screenshot) -->
                <div class="w-full max-w-[700px] bg-white rounded-2xl border border-gray-100 shadow-[0_4px_24px_rgba(0,0,0,0.04)] p-6 mb-6">
                    <div class="flex flex-col sm:flex-row gap-6 mb-6">
                        <!-- Photo (Vertical Rectangle) -->
                        <div class="w-[120px] h-[160px] flex-shrink-0 bg-gray-50 rounded-xl overflow-hidden border-2 border-red-500 shadow-md">
                            <template x-if="result?.student?.photo_url">
                                <img :src="result.student.photo_url" alt="Photo" class="w-full h-full object-cover" x-on:error="$el.style.display='none'; $el.nextElementSibling.style.display='flex'">
                            </template>
                            <div class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-100" :class="{'hidden': result?.student?.photo_url}" style="display: none;">
                                <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            </div>
                        </div>
                        
                        <!-- Details Rows -->
                        <div class="flex-1 flex flex-col justify-center gap-5">
                            <!-- Name Row -->
                            <div class="flex gap-4 items-center border-b border-gray-50 pb-3">
                                <div class="w-10 h-10 rounded-full bg-red-50 text-red-500 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[10px] font-bold text-gray-400 tracking-widest uppercase mb-0.5">Student Name</div>
                                    <div class="text-[16px] font-extrabold text-[#0a192f] leading-tight break-words" x-text="result?.student?.name"></div>
                                </div>
                            </div>
                            
                            <!-- ID Row -->
                            <div class="flex gap-4 items-center border-b border-gray-50 pb-3">
                                <div class="w-10 h-10 rounded-full bg-red-50 text-red-500 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold text-gray-400 tracking-widest uppercase mb-0.5">ID Number</div>
                                    <div class="text-[15px] font-bold text-[#0a192f]" x-text="result?.student?.id"></div>
                                </div>
                            </div>

                            <!-- Course Row -->
                            <div class="flex gap-4 items-center">
                                <div class="w-10 h-10 rounded-full bg-red-50 text-red-500 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold text-gray-400 tracking-widest uppercase mb-0.5">Department / Course</div>
                                    <div class="text-[13px] font-bold text-[#0a192f] leading-snug" x-text="result?.student?.dept"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Summary Row -->
                    <div class="bg-[#fcf5f5] rounded-xl p-4 flex justify-between items-center flex-wrap gap-4 border border-red-50">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <div>
                                <div class="text-[9px] font-bold text-gray-400 tracking-widest uppercase">Date</div>
                                <div class="text-[13px] font-semibold text-[#0a192f]" x-text="clockDate"></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <div class="text-[9px] font-bold text-gray-400 tracking-widest uppercase">Time</div>
                                <div class="text-[13px] font-semibold text-[#0a192f]" x-text="clockHm"></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <div>
                                <div class="text-[9px] font-bold text-gray-400 tracking-widest uppercase">Status</div>
                                <div class="mt-0.5 px-2 py-0.5 bg-red-100 text-red-600 text-[10px] font-bold tracking-widest rounded-md">CHECKED IN</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Button -->
                <button @click="resetScan()" class="w-full max-w-[700px] mt-2 p-4 bg-[#D31027] text-white rounded-[12px] flex items-center justify-center gap-3 shadow-lg hover:bg-red-800 transition-colors">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V6a2 2 0 012-2h2M4 16v2a2 2 0 002 2h2M20 8V6a2 2 0 00-2-2h-2M20 16v2a2 2 0 01-2 2h-2" />
                    </svg>
                    <span class="text-[16px] font-bold tracking-wide">Scan Next Student</span>
                </button>
            </div>
        </div>
    </template>
</div>
