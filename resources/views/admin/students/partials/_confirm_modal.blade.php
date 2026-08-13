    <!-- Custom Confirmation Dialog Modal -->
    <template x-teleport="body">
        <div x-show="showConfirmModal" 
             class="fixed inset-0 flex items-center justify-center" 
             style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh; z-index: 99999; background-color: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); display: none;"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden border border-gray-100" 
                 style="background: #ffffff; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); position: relative; z-index: 100000;"
                 @click.away="showConfirmModal = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95">
                <div class="p-6 sm:p-7 text-center" style="padding: 28px;">
                    <div class="w-14 h-14 rounded-full bg-red-50 text-red-600 flex items-center justify-center mx-auto mb-4 border border-red-100 shadow-sm"
                         style="width: 56px; height: 56px; margin: 0 auto 16px; border-radius: 50%; background-color: #fef2f2; border: 1px solid #fee2e2; display: flex; align-items: center; justify-content: center; color: #dc2626;">
                        <svg class="w-7 h-7" style="width: 28px; height: 28px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2" style="font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 8px;" x-text="confirmTitle"></h3>
                    <p class="text-sm text-gray-600 leading-relaxed mb-6" style="font-size: 14px; line-height: 1.6; color: #475569; margin-bottom: 24px;" x-text="confirmMessage"></p>
                    <div class="flex items-center justify-center gap-3" style="display: flex; justify-content: center; gap: 12px;">
                        <button type="button" 
                                @click="showConfirmModal = false" 
                                class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all cursor-pointer"
                                style="padding: 10px 20px; font-weight: 600; font-size: 14px; border-radius: 10px; background-color: #f1f5f9; color: #334155; border: none; cursor: pointer;">
                            Cancel
                        </button>
                        <form :action="confirmActionUrl" method="POST" class="inline" style="display: inline-block; margin: 0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="px-5 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-sm hover:shadow transition-all cursor-pointer flex items-center gap-2"
                                    style="padding: 10px 20px; font-weight: 600; font-size: 14px; border-radius: 10px; background-color: #dc2626; color: #ffffff; border: none; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                                <span x-text="confirmButtonText"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </template>
