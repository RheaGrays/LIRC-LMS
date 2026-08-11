
                            <!-- Header Area -->
                            <div class="flex justify-between items-center mb-8">
                                <div class="flex items-center gap-4">
                                    <!-- Red Header Icon -->
                                    <div style="color: #dc2626;" class="shrink-0 flex items-center justify-center">
                                        <svg class="w-14 h-14" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 14V9a3 3 0 0 1 3-3h5M34 6h5a3 3 0 0 1 3 3v5M6 34v5a3 3 0 0 0 3 3h5M34 42h5a3 3 0 0 0 3-3v-5" stroke-width="3"/>
                                            <rect x="12" y="14" width="24" height="20" rx="3" fill="#fef2f2" stroke="currentColor" stroke-width="2" />
                                            <circle cx="19" cy="24" r="3.5" fill="currentColor" stroke="none" />
                                            <path d="M26 21h7M26 27h5" stroke-width="2.5" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h1 class="font-bold text-gray-900 text-[30px] m-0 leading-tight tracking-tight">Scan ID</h1>
                                        <p class="text-gray-500 text-[15px] font-medium m-0 mt-0.5">Present your Student ID to check in or out.</p>
                                    </div>
                                </div>
                                <!-- CorJesu Logo -->
                                <div class="shrink-0">
                                    <img src="/CorJesu Logo.png" alt="Cor Jesu Logo" class="h-20 w-auto object-contain">
                                </div>
                            </div>

                            <!-- Tabs Bar with Vertical Dividers -->
                            <div class="flex items-center gap-6 border-b border-gray-100 pb-0 mb-8 relative">
                                <button class="pb-3 text-[15px] flex items-center gap-2.5 transition-all relative font-bold"
                                    :class="tab === 'scan' ? 'text-[#dc2626]' : 'text-gray-400 hover:text-gray-600'"
                                    @click="tab = 'scan'; handleActivity()">
                                    <svg class="w-5 h-5" :style="tab === 'scan' ? 'color: #dc2626;' : 'color: #9ca3af;'" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M3 4h3v3H3V4zm0 7h3v3H3v-3zm0 7h3v3H3v-3zm7-14h3v3h-3V4zm0 7h3v3h-3v-3zm0 7h3v3h-3v-3zm7-14h3v3h-3V4zm0 7h3v3h-3v-3zm0 7h3v3h-3v-3z"/>
                                    </svg>
                                    <span>Barcode / QR</span>
                                    <div class="absolute bottom-0 left-0 right-0 h-[3px] bg-[#dc2626] rounded-t-full" x-show="tab === 'scan'"></div>
                                </button>

                                <div class="w-px h-5 bg-gray-200 shrink-0 mb-3"></div>

                                <button class="pb-3 text-[15px] flex items-center gap-2.5 transition-all relative font-semibold"
                                    :class="tab === 'webcam' ? 'text-[#dc2626]' : 'text-gray-400 hover:text-gray-600'"
                                    @click="tab = 'webcam'; handleActivity()">
                                    <svg class="w-5 h-5" :style="tab === 'webcam' ? 'color: #dc2626;' : 'color: #9ca3af;'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="7" width="18" height="12" rx="2"/><circle cx="12" cy="13" r="3"/><path d="M8 7V5h8v2"/>
                                    </svg>
                                    <span>Webcam</span>
                                    <div class="absolute bottom-0 left-0 right-0 h-[3px] bg-[#dc2626] rounded-t-full" x-show="tab === 'webcam'" style="display: none;"></div>
                                </button>

                                <div class="w-px h-5 bg-gray-200 shrink-0 mb-3"></div>

                                <button class="pb-3 text-[15px] flex items-center gap-2.5 transition-all relative font-semibold"
                                    :class="tab === 'manual' ? 'text-[#dc2626]' : 'text-gray-400 hover:text-gray-600'"
                                    @click="tab = 'manual'; handleActivity()">
                                    <svg class="w-5 h-5" :style="tab === 'manual' ? 'color: #dc2626;' : 'color: #9ca3af;'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                    <span>Manual Entry</span>
                                    <div class="absolute bottom-0 left-0 right-0 h-[3px] bg-[#dc2626] rounded-t-full" x-show="tab === 'manual'" style="display: none;"></div>
                                </button>
                            </div>

                            <!-- Tab 1: Barcode / QR (Exact match of final mockup) -->
                            <div x-show="tab === 'scan'" class="flex flex-col gap-6 animate-slide-in w-full py-2">
                                <label class="flex flex-col gap-2.5">
                                    <span class="text-[11px] font-bold tracking-[0.1em] uppercase text-gray-500">SCAN OR TYPE ID</span>
                                    
                                    <!-- Input Container with soft red border and dot matrix background -->
                                    <div style="border: 1.5px solid #f87171; background: linear-gradient(90deg, #fff5f5 0%, #ffffff 50%), radial-gradient(#fca5a5 1px, transparent 1px); background-size: 100% 100%, 10px 10px;" 
                                         class="w-full py-3.5 px-5 rounded-2xl flex items-center shadow-sm relative overflow-hidden">
                                        
                                        <!-- Red Scan Icon -->
                                        <div style="color: #dc2626;" class="shrink-0 flex items-center justify-center">
                                            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M4 7V5a2 2 0 0 1 2-2h2M18 3h2a2 2 0 0 1 2 2v2M4 17v2a2 2 0 0 0 2 2h2M18 21h2a2 2 0 0 0 2-2v-2"/>
                                                <path d="M9 12h6" stroke-width="2"/>
                                            </svg>
                                        </div>

                                        <!-- Divider Line -->
                                        <div class="w-px h-7 bg-gray-200 mx-4 shrink-0"></div>

                                        <!-- Input Field -->
                                        <input type="text" x-model="manualId" x-ref="barcodeInput" 
                                            @keydown.enter.prevent="if(manualId.trim()) submitManual()"
                                            @input="handleActivity()"
                                            placeholder="Scan ID here..." 
                                            style="outline: none !important; border: none !important; box-shadow: none !important;"
                                            class="w-full bg-transparent border-none text-[18px] font-sans text-gray-800 outline-none focus:outline-none focus:ring-0 focus:border-none shadow-none placeholder:text-gray-400">
                                    </div>
                                </label>

                                <p class="text-[14px] text-gray-500 text-center m-0 mt-2">
                                    Press <kbd style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;" class="rounded-md px-3 py-1 text-[13px] font-bold mx-1.5 shadow-sm">Enter</kbd> to submit
                                </p>
                            </div>

                            <!-- Tab 2: Webcam -->
                            <div x-show="tab === 'webcam'" class="flex flex-col gap-4 items-center animate-slide-in w-full max-w-[480px] mx-auto pt-2" style="display: none;">
                                <div class="w-full aspect-[16/10] bg-black rounded-2xl relative overflow-hidden flex items-center justify-center border-4 border-white shadow-lg">
                                    <!-- Reticle Corner Brackets -->
                                    <div style="color: #dc2626;" class="absolute inset-4 pointer-events-none z-20">
                                        <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none" fill="none" stroke="currentColor" stroke-width="3">
                                            <path d="M8 20v-12h12M92 20v-12h-12M8 80v12h12M92 80v12h-12" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>

                                    <video id="kiosk-video" class="w-full h-full object-cover z-10" style="transform: scaleX(-1);" :class="isCameraActive ? 'block' : 'hidden'"></video>
                                    
                                    <!-- Scanline -->
                                    <template x-if="isCameraActive">
                                        <div style="background: #dc2626; box-shadow: 0 0 10px #dc2626;" class="absolute left-0 right-0 h-[2px] z-30 animate-[scanline_2s_linear_infinite]"></div>
                                    </template>

                                    <!-- Placeholder / Error State -->
                                    <template x-if="!isCameraActive">
                                        <div class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-black px-4 text-center">
                                            <template x-if="cameraError">
                                                <div class="flex flex-col items-center">
                                                    <svg class="w-12 h-12 text-red-500 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                    </svg>
                                                    <p class="text-white font-medium text-sm m-0" x-text="cameraError"></p>
                                                    <button @click="initScanner()" class="mt-4 px-4 py-1.5 bg-white/10 hover:bg-white/20 text-white text-sm rounded-full transition-colors border border-white/20">
                                                        Retry
                                                    </button>
                                                </div>
                                            </template>
                                            <template x-if="!cameraError">
                                                <div class="flex flex-col items-center">
                                                    <svg class="w-12 h-12 text-white/20 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                        <path d="M3 9a2 2 0 012-2h3l2-2h4l2 2h3a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                                        <circle cx="12" cy="13" r="3"/>
                                                    </svg>
                                                    <div class="w-7 h-7 border-2 border-white/20 border-t-white rounded-full animate-spin"></div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                                <div class="text-center">
                                    <h4 class="font-bold text-gray-900 text-[16px] m-0 mb-1">Ready to scan</h4>
                                    <p class="text-[13px] text-gray-500 m-0">Point your camera at the barcode or QR code on your Student ID.</p>
                                </div>
                            </div>

                            <!-- Tab 3: Manual Entry -->
                            <div x-show="tab === 'manual'" class="flex flex-col gap-4 animate-slide-in relative w-full max-w-[500px] mx-auto pt-2" style="display: none;">
                                <label class="flex flex-col gap-2">
                                    <span class="text-[11px] font-bold tracking-[0.08em] uppercase text-gray-400">Student ID or Name</span>
                                    <div class="relative w-full flex items-center">
                                        <div style="color: #dc2626;" class="absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none z-10 flex items-center justify-center">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                                            </svg>
                                        </div>
                                        <input type="text" x-model="manualId" x-ref="manualInput" 
                                            @keydown.enter.prevent="if(manualId.trim()) submitManual()"
                                            @input="handleActivity()"
                                            placeholder="Enter Student ID or Name" 
                                            class="w-full py-3.5 pl-12 pr-4 text-[15px] text-gray-800 bg-white border border-gray-200 rounded-xl outline-none focus:border-red-500 focus:ring-2 focus:ring-red-100 transition-all font-sans">
                                    </div>
                                </label>
                                
                                <!-- Autocomplete Dropdown -->
                                <div x-show="showSuggestions" @click.outside="showSuggestions = false" style="display: none;"
                                     class="absolute top-[75px] left-0 right-0 bg-white border border-gray-200 rounded-xl shadow-lg z-[100] overflow-hidden">
                                    <template x-for="item in suggestions" :key="item.id">
                                        <div @click="selectSuggestion(item.id)"
                                             class="flex items-center gap-3 p-3 hover:bg-gray-50 cursor-pointer transition-colors border-b border-gray-100 last:border-0">
                                            <img :src="item.photo" class="w-9 h-9 rounded-full object-cover bg-gray-100">
                                            <div class="flex flex-col">
                                                <span class="text-[14px] font-bold text-gray-900" x-text="item.name"></span>
                                                <span class="text-[11px] font-semibold text-gray-500" x-text="item.id"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <button @click="submitManual()"
                                    style="background-color: #dc2626;"
                                    class="w-full py-3.5 text-white border-none rounded-xl text-[15px] font-bold cursor-pointer transition-all hover:opacity-90 shadow-md flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span>Verify & Enter</span>
                                </button>
                                
                                <div style="background-color: #fef2f2; border: 1px solid #fee2e2; color: #dc2626;" class="w-full px-4 py-3 rounded-lg flex items-center gap-2.5 text-[13px] font-medium">
                                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Make sure the information is correct before submitting.</span>
                                </div>
                            </div>
<?php /**PATH C:\Users\Alfie Lynard\OneDrive\Desktop\archive\LEMS\resources\views/components/kiosk/scanner-tabs.blade.php ENDPATH**/ ?>