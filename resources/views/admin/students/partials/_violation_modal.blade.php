    <!-- Manage Violations Modal -->
    <template x-teleport="body">
        <div x-show="showViolationModal" 
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" 
             style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh; z-index: 9999; display: none;" 
             x-cloak>
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden flex flex-col max-h-[90vh]" @click.away="showViolationModal = false">
                <div class="px-6 py-4 border-b border-slate-700/30 flex justify-between items-center bg-[var(--cjc-navy)] text-white">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg leading-tight">Manage Violations</h3>
                            <p class="text-xs text-white/70" x-text="selectedStudent?.first_name + ' ' + selectedStudent?.last_name + ' (' + (selectedStudent?.id || '') + ')'"></p>
                        </div>
                    </div>
                    <button @click="showViolationModal = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-white/60 hover:text-white hover:bg-white/10 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <div class="p-6 sm:p-7 flex-1 overflow-y-auto space-y-6" style="padding: 24px 28px;">
                    
                    <!-- Existing Violations -->
                    <div>
                        <div class="flex justify-between items-center mb-3.5 pb-2 border-b border-gray-100">
                            <div class="flex items-center gap-2">
                                <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Record History</h4>
                                <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-red-50 text-red-700 border border-red-200" 
                                      x-show="selectedStudent?.violations?.length > 0" 
                                      x-text="(selectedStudent?.violations?.length || 0) + ' active'"></span>
                            </div>
                            <span class="text-xs font-medium text-gray-500" x-show="!selectedStudent?.violations || selectedStudent?.violations?.length === 0">0 records</span>
                        </div>

                        <template x-if="selectedStudent?.violations?.length > 0">
                            <div class="space-y-3.5">
                                <template x-for="v in selectedStudent.violations" :key="v.id">
                                    <div class="rounded-xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm hover:border-slate-300 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                                         style="padding: 16px 20px; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 12px;">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                                <span class="font-bold text-gray-900 text-base" x-text="v.violation_type?.name || 'Violation'"></span>
                                                <span class="text-[11px] px-2.5 py-0.5 rounded-full uppercase font-bold tracking-wider" 
                                                      :class="v.severity === 'severe' ? 'bg-red-100 text-red-700 border border-red-200' : (v.severity === 'moderate' ? 'bg-orange-100 text-orange-700 border border-orange-200' : 'bg-amber-100 text-amber-800 border border-amber-200')"
                                                      x-text="v.severity"></span>
                                            </div>
                                            <div class="text-sm text-gray-600 mb-2 leading-relaxed" x-text="v.notes || 'No description provided.'"></div>
                                            <div class="flex items-center gap-1.5 text-xs text-gray-400 font-medium">
                                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <span x-text="'Incident Date: ' + (v.date ? (typeof v.date === 'string' ? v.date.split('T')[0] : v.date) : 'N/A')"></span>
                                            </div>
                                        </div>
                                        <div class="shrink-0 flex items-center justify-end sm:pl-3">
                                            <button type="button" 
                                                    @click="confirmSettleViolation(v)"
                                                    class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-red-50 hover:bg-red-100 text-red-600 hover:text-red-700 border border-red-200 hover:border-red-300 transition-all flex items-center gap-1.5 shadow-xs cursor-pointer"
                                                    style="padding: 8px 14px; font-weight: 600; border-radius: 8px;"
                                                    title="Remove settled violation">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                <span>Remove / Settle</span>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <template x-if="!selectedStudent?.violations || selectedStudent?.violations?.length === 0">
                            <div class="p-6 rounded-xl bg-gray-50 border border-dashed border-gray-200 text-center" style="padding: 24px;">
                                <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-2 border border-emerald-100">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-gray-700">Clean Record</p>
                                <p class="text-xs text-gray-400 mt-0.5">No active violations recorded for this patron.</p>
                            </div>
                        </template>
                    </div>

                    <!-- Add New Violation -->
                    <div class="pt-2 border-t border-gray-100">
                        <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-3.5">Record New Violation</h4>
                        <form :action="`/admin/students/${selectedStudent?.id}/violations`" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Type of Violation *</label>
                                    <select name="violation_type_id" required class="w-full p-2.5 border border-gray-300 rounded-lg focus:border-[var(--cjc-navy)] focus:ring-1 focus:ring-[var(--cjc-navy)] outline-none text-sm bg-white" style="padding: 10px 12px;">
                                        <option value="" disabled selected>Select Type</option>
                                        @foreach($violationTypes as $vType)
                                            <option value="{{ $vType->id }}">{{ $vType->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Severity *</label>
                                    <select name="severity" required class="w-full p-2.5 border border-gray-300 rounded-lg focus:border-[var(--cjc-navy)] focus:ring-1 focus:ring-[var(--cjc-navy)] outline-none text-sm bg-white" style="padding: 10px 12px;">
                                        <option value="minor">Minor</option>
                                        <option value="moderate">Moderate</option>
                                        <option value="severe">Severe</option>
                                    </select>
                                </div>
                                <div class="col-span-1 sm:col-span-2">
                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Incident Date *</label>
                                    <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="w-full p-2.5 border border-gray-300 rounded-lg focus:border-[var(--cjc-navy)] focus:ring-1 focus:ring-[var(--cjc-navy)] outline-none text-sm" style="padding: 10px 12px;">
                                </div>
                                <div class="col-span-1 sm:col-span-2">
                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Notes / Remarks</label>
                                    <textarea name="notes" rows="2" placeholder="Enter incident details or remarks..." class="w-full p-2.5 border border-gray-300 rounded-lg focus:border-[var(--cjc-navy)] focus:ring-1 focus:ring-[var(--cjc-navy)] outline-none text-sm" style="padding: 10px 12px;"></textarea>
                                </div>
                            </div>
                            <div class="flex justify-end gap-3 mt-4">
                                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm transition-all cursor-pointer flex items-center gap-2" style="padding: 10px 20px;">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span>Record Violation</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </template>
