<div class="card bg-white border-2 overflow-hidden relative" 
     :class="{
         'border-green-500': result?.status === 'success' && result?.action === 'check_in',
         'border-gray-400': result?.status === 'success' && result?.action === 'check_out',
         'border-red-500': result?.status === 'error',
         'border-orange-500': result?.status === 'offline'
     }">
    
    <!-- Top colored bar based on status -->
    <div class="absolute top-0 left-0 right-0 h-2"
         :class="{
             'bg-green-500': result?.status === 'success' && result?.action === 'check_in',
             'bg-gray-500': result?.status === 'success' && result?.action === 'check_out',
             'bg-red-500': result?.status === 'error',
             'bg-orange-500': result?.status === 'offline'
         }"></div>

    <div class="p-6 pt-8">
        <!-- Error State -->
        <template x-if="result?.status === 'error'">
            <div class="text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Access Denied</h3>
                <p class="text-lg text-red-600 font-medium" x-text="result?.message"></p>
                <button @click="resetScan()" class="mt-6 btn-primary">Scan Another ID</button>
            </div>
        </template>

        <!-- Offline Queue State -->
        <template x-if="result?.status === 'offline'">
            <div class="text-center">
                <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Saved Offline</h3>
                <p class="text-gray-600">Action for ID <span class="font-bold" x-text="result?.student_id"></span> has been queued.</p>
                <p class="text-sm text-orange-600 mt-2">Will sync when connection restores.</p>
            </div>
        </template>

        <!-- Success State -->
        <template x-if="result?.status === 'success'">
            <div>
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center p-3 rounded-full mb-3 shadow-sm"
                         :class="result?.action === 'check_in' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'">
                        <svg x-show="result?.action === 'check_in'" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        <svg x-show="result?.action === 'check_out'" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </div>
                    <h3 class="text-3xl font-bold tracking-tight text-[var(--cjc-navy)]"
                        x-text="result?.action === 'check_in' ? 'Welcome!' : 'Time Out!'"></h3>
                    <p class="text-gray-500 font-medium mt-1" x-text="result?.message"></p>
                </div>
                
                <div class="flex flex-col sm:flex-row items-center gap-6 bg-gray-50 rounded-xl p-4 border border-gray-100 shadow-inner">
                    <!-- Photo -->
                    <div class="w-24 h-24 sm:w-32 sm:h-32 flex-shrink-0 bg-white rounded-lg border-2 border-white shadow-md overflow-hidden relative">
                        <template x-if="result?.student?.photo_url">
                            <img :src="result.student.photo_url" alt="Photo" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!result?.student?.photo_url">
                            <div class="w-full h-full bg-gray-100 flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                        </template>
                    </div>
                    
                    <!-- Details -->
                    <div class="flex-1 text-center sm:text-left space-y-2">
                        <div>
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Student Name</div>
                            <div class="text-xl font-bold text-gray-900 leading-tight" x-text="result?.student?.name"></div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 pt-2">
                            <div>
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">ID Number</div>
                                <div class="font-semibold text-gray-700" x-text="result?.student?.id"></div>
                            </div>
                            <div>
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Department</div>
                                <div class="font-semibold text-gray-700 truncate" :title="result?.student?.dept" x-text="result?.student?.dept"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

    </div>
</div>
