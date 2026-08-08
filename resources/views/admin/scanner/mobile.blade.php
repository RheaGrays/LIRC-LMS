@extends('layouts.admin')

@section('title', 'Mobile Scanner')

@section('admin_content')
@vite(['resources/js/admin/scanner.js'])

<div x-data="mobileScannerApp()" class="w-full h-[calc(100dvh-64px)] flex flex-col bg-stone-100 -mx-4 -my-4 sm:mx-0 sm:my-0 overflow-hidden relative">
    
    <!-- Top Bar -->
    <div class="bg-white px-4 py-3 shadow-sm border-b border-gray-200 flex items-center justify-between shrink-0 relative z-20">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-red-50 rounded-full flex items-center justify-center text-red-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </div>
            <div>
                <h2 class="text-sm font-bold text-slate-800 m-0 leading-tight">Mobile Scanner</h2>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <div class="w-2 h-2 rounded-full" :class="isOnline ? 'bg-green-500' : 'bg-red-500'"></div>
                    <span class="text-[10px] font-bold tracking-wider uppercase text-gray-500" x-text="isOnline ? 'Online' : 'Offline'"></span>
                </div>
            </div>
        </div>
        <div>
            <x-kiosk.offline-sync-status />
        </div>
    </div>

    <!-- Scanner Viewfinder Area -->
    <div class="flex-1 relative bg-black overflow-hidden flex flex-col items-center justify-center">
        <!-- Video Element -->
        <video id="mobile-scanner-video" class="absolute inset-0 w-full h-full object-cover" autoplay playsinline></video>
        
        <!-- Reticle -->
        <div class="relative z-10 w-64 h-64 border-2 border-white/30 rounded-2xl flex flex-col items-center justify-center">
            <!-- Corner Brackets -->
            <div class="absolute inset-0 border-4 border-red-500 rounded-2xl" style="clip-path: polygon(0 0, 20% 0, 20% 100%, 0 100%, 0 20%, 0 100%, 100% 100%, 100% 80%, 100% 100%, 80% 100%, 100% 100%, 100% 0, 80% 0, 100% 0, 100% 20%, 0 0, 0 20%, 100% 20%, 100% 0, 20% 0);"></div>
            <!-- Corners using SVG for cleaner look -->
            <svg class="absolute inset-0 w-full h-full text-red-500" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="4">
                <path d="M10 25V10h15M75 10h15v15M10 75v15h15M75 90h15V75" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            
            <template x-if="isScanning">
                <div class="absolute left-0 right-0 h-0.5 bg-red-500 shadow-[0_0_10px_#ef4444] animate-[scanline_2s_linear_infinite]"></div>
            </template>
        </div>

        <!-- Overlays -->
        <div class="absolute bottom-6 left-0 right-0 px-6 z-20 flex flex-col items-center gap-3">
            <template x-if="!isCameraActive">
                <button @click="startCamera()" class="px-6 py-3 bg-red-600 text-white rounded-full font-bold shadow-lg flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h3l2-2h4l2 2h3a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="4"/></svg>
                    Start Scanner
                </button>
            </template>
            <p class="text-white/80 text-[13px] font-medium text-center bg-black/40 px-4 py-2 rounded-full backdrop-blur-sm mb-2" x-show="isCameraActive">
                Point camera at Student ID barcode
            </p>
            <button @click="showManualEntry = true" class="px-6 py-3 bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 text-white rounded-full font-bold shadow-lg flex items-center gap-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Type ID Manually
            </button>
        </div>
        
        <!-- Processing Overlay -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-md z-30 flex flex-col items-center justify-center transition-opacity" x-show="isProcessing" x-transition>
            <div class="w-12 h-12 border-4 border-white/20 border-t-red-500 rounded-full animate-spin"></div>
            <p class="text-white font-bold mt-4">Processing...</p>
        </div>
        
        <!-- Result Overlay -->
        <div class="absolute inset-x-4 top-1/2 -translate-y-1/2 bg-white rounded-2xl shadow-2xl p-6 z-40 transition-all transform scale-100"
             x-show="result" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-8 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 scale-95"
             style="display: none;">
            
            <div class="flex flex-col items-center text-center">
                <!-- Success Icon -->
                <template x-if="result?.status === 'success'">
                    <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </template>
                <!-- Offline Icon -->
                <template x-if="result?.status === 'offline'">
                    <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    </div>
                </template>
                <!-- Error Icon -->
                <template x-if="result?.status === 'error'">
                    <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                </template>

                <h3 class="text-xl font-bold text-gray-900 mb-1" x-text="result?.status === 'success' ? (result.action === 'check_in' ? 'Checked In' : 'Checked Out') : (result?.status === 'offline' ? 'Saved Offline' : 'Error')"></h3>
                <p class="text-gray-500 text-sm font-medium mb-4" x-text="result?.message || 'Processing completed.'"></p>
                
                <template x-if="result?.student">
                    <div class="bg-gray-50 p-3 rounded-xl w-full border border-gray-100 mb-4">
                        <p class="font-bold text-gray-900 uppercase text-sm" x-text="result.student.full_name"></p>
                        <p class="text-xs text-gray-500 font-mono mt-0.5" x-text="result.student.id"></p>
                    </div>
                </template>
                
                <button @click="resetScanner()" class="w-full py-3 bg-gray-900 text-white font-bold rounded-xl active:scale-95 transition-transform">
                    Scan Next ID
                </button>
            </div>
        </div>
    </div>

    <!-- Manual Entry Modal -->
    <div x-show="showManualEntry" class="absolute inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-sm" style="display: none;" x-transition>
        <div class="bg-white w-full sm:max-w-md rounded-t-3xl sm:rounded-3xl shadow-2xl p-6 transform transition-transform" @click.away="showManualEntry = false">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-black text-[var(--cjc-navy)]">Manual Entry</h3>
                <button @click="showManualEntry = false" class="p-2 text-gray-400 hover:text-gray-600 bg-gray-100 rounded-full transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="submitManualEntry">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Student / Patron ID</label>
                <input type="text" x-model="manualId" placeholder="e.g. 2024-00123" class="w-full text-lg p-4 bg-gray-50 border border-gray-300 rounded-xl mb-6 font-mono font-semibold focus:border-[var(--cjc-red)] focus:ring-[var(--cjc-red)] outline-none transition-all" autocomplete="off">
                <button type="submit" class="w-full py-4 bg-[var(--cjc-red)] text-white font-bold rounded-xl shadow-[0_4px_14px_rgba(196,30,42,0.3)] active:scale-95 transition-transform text-lg flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Process Check In / Out
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    @keyframes scanline {
        0% { top: 10%; }
        50% { top: 90%; }
        100% { top: 10%; }
    }
</style>

@endsection
