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

                    <!-- Photo Mode Selector Tabs -->
                    <div class="flex bg-[var(--bg-cream-2)] p-1 rounded-[var(--radius-md)] border border-[var(--border-warm)] w-full max-w-sm">
                        <button type="button" @click="photoSyncMode = 'mobile'"
                                class="flex-1 py-1.5 px-3 rounded-[var(--radius-sm)] text-xs font-semibold font-['Inter'] transition-all duration-150 flex items-center justify-center gap-1.5"
                                :class="photoSyncMode === 'mobile' ? 'bg-white text-[var(--cjc-navy)] shadow-sm' : 'text-[var(--text-muted)] hover:text-[var(--cjc-navy)]'">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><path d="M12 18h.01"/></svg>
                            Mobile Phone / QR Sync
                        </button>
                        <button type="button" @click="photoSyncMode = 'webcam'"
                                class="flex-1 py-1.5 px-3 rounded-[var(--radius-sm)] text-xs font-semibold font-['Inter'] transition-all duration-150 flex items-center justify-center gap-1.5"
                                :class="photoSyncMode === 'webcam' ? 'bg-white text-[var(--cjc-navy)] shadow-sm' : 'text-[var(--text-muted)] hover:text-[var(--cjc-navy)]'">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                            PC Webcam
                        </button>
                    </div>

                    <!-- CAPTURED PHOTO DISPLAY (If photo already captured/synced) -->
                    <div x-show="capturedImage" class="flex flex-col items-center gap-4">
                        <div class="w-[220px] h-[270px] rounded-[var(--radius-lg)] border-2 border-[var(--cjc-navy)] overflow-hidden shadow-lg relative bg-black">
                            <img :src="capturedImage" class="w-full h-full object-cover" />
                            <div class="absolute top-2.5 right-2.5 w-7 h-7 rounded-full bg-green-600 flex items-center justify-center text-white shadow-md">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><path d="M3 8l3.5 3.5L13 4" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-green-700 font-semibold font-['Inter'] flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Photo Captured Successfully
                            </span>
                            <button type="button" @click="capturedImage = null; photoTaken = false; if(photoSyncMode === 'mobile') startPhotoSyncSession();" class="text-xs text-[var(--cjc-red)] underline font-medium hover:text-red-800 ml-2">
                                Change / Retake
                            </button>
                        </div>
                    </div>

                    <!-- MODE 1: Mobile Phone QR Code Sync -->
                    <div x-show="photoSyncMode === 'mobile' && !capturedImage" class="w-full flex flex-col items-center gap-4">
                        <div class="bg-[var(--bg-cream-2)] border border-[var(--border-warm)] rounded-[var(--radius-lg)] p-5 text-center flex flex-col items-center gap-3 w-full max-w-md shadow-sm">
                            
                            <!-- QR Code Display -->
                            <div class="bg-white p-3 border border-[var(--border-light)] rounded-[var(--radius-md)] shadow-sm relative">
                                <canvas id="qrcode-canvas" class="w-[180px] h-[180px]" :class="photoSyncMobileUrl ? 'block' : 'hidden'"></canvas>
                                <template x-if="!photoSyncMobileUrl">
                                    <div class="w-[180px] h-[180px] bg-gray-100 flex items-center justify-center text-xs text-gray-400">
                                        Generating QR Code…
                                    </div>
                                </template>
                            </div>

                            <!-- Pair Code Badge -->
                            <div class="flex flex-col items-center gap-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--text-subtle)] font-['Inter']">Pairing Session Code</span>
                                <span class="font-['JetBrains_Mono'] text-xl font-bold tracking-widest text-[var(--cjc-navy)] bg-white px-4 py-1 border border-[var(--border-warm)] rounded-md shadow-inner" x-text="photoSyncSessionId || 'LOADING…'"></span>
                            </div>

                            <div class="text-left w-full bg-white border border-[var(--border-light)] rounded-md p-3 text-xs text-[var(--text-muted)] leading-relaxed space-y-1.5">
                                <p class="m-0 font-semibold text-[var(--cjc-navy)] flex items-center gap-1">
                                    <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    How to take photo with Mobile Phone:
                                </p>
                                <ol class="m-0 pl-4 list-decimal text-[11px] space-y-1">
                                    <li>Scan the QR code above with <strong>any smartphone camera</strong>.</li>
                                    <li>Or open the <strong>LEMS Mobile App</strong> and enter Pair Code above.</li>
                                    <li>Snap the photo on mobile — it will <strong>automatically sync live</strong> to this screen!</li>
                                </ol>
                            </div>

                            <!-- Live Waiting Indicator -->
                            <div class="flex items-center gap-2 px-3.5 py-1.5 bg-blue-50 border border-blue-200 rounded-full text-blue-800 text-[11px] font-medium animate-pulse">
                                <span class="w-2 h-2 rounded-full bg-blue-600 block"></span>
                                Waiting for mobile camera photo sync…
                            </div>
                        </div>
                    </div>

                    <!-- MODE 2: PC Webcam Component -->
                    <div x-show="photoSyncMode === 'webcam' && !capturedImage" x-data="webcamApp">
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
                            </div>
                        </div>
                    </div>
                </div>
