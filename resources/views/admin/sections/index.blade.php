@extends('layouts.admin')

@section('title', ' | Section Counters')
@section('header_title', 'Library Section Headcounts')

@section('admin_content')
<div class="space-y-6" x-data="{ 
    sections: {{ json_encode($settings['library_sections'] ?? ['General Reading', 'Discussion Room', 'Internet Section', 'Periodicals']) }},
    logs: {{ json_encode($logs) }},
    
    // Method to update count locally and trigger save
    updateCount(sectionName, newCount) {
        if(newCount < 0) return;
        
        let existing = this.logs.find(l => l.section_name === sectionName);
        if(existing) {
            existing.headcount = newCount;
        } else {
            this.logs.push({ section_name: sectionName, headcount: newCount });
        }
        
        this.saveCounts(sectionName, newCount);
    },
    
    getCount(sectionName) {
        let existing = this.logs.find(l => l.section_name === sectionName);
        return existing ? existing.headcount : 0;
    },
    
    async saveCounts(sectionName, headcount) {
        try {
            await fetch('{{ route('admin.sections.upsert') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ section_name: sectionName, headcount: headcount })
            });
        } catch (e) {
            console.error('Failed to save section log', e);
        }
    }
}">

    <div class="card bg-white border-t-4 border-t-[var(--cjc-gold)]">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 bg-yellow-50 text-yellow-600 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
            </div>
            <div>
                <h3 class="text-xl font-bold text-[var(--cjc-navy)]">Section Headcounts</h3>
                <p class="text-gray-500 text-sm mt-1">Manually record the number of students present in specific library sections.</p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <template x-for="section in sections" :key="section">
                <div class="border border-gray-200 rounded-xl p-5 bg-gray-50 hover:bg-white transition-colors hover:shadow-md hover:border-gray-300 group">
                    <h4 class="font-bold text-[var(--cjc-navy)] mb-4 text-center" x-text="section"></h4>
                    
                    <div class="flex items-center justify-center gap-4">
                        <button @click="updateCount(section, getCount(section) - 1)" class="w-10 h-10 rounded-full bg-white border border-gray-300 flex items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-[var(--cjc-red)] focus:outline-none transition-colors shadow-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg>
                        </button>
                        
                        <div class="text-3xl font-bold text-gray-900 w-16 text-center select-none font-mono" x-text="getCount(section)"></div>
                        
                        <button @click="updateCount(section, getCount(section) + 1)" class="w-10 h-10 rounded-full bg-white border border-gray-300 flex items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-green-600 focus:outline-none transition-colors shadow-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        </button>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <span class="text-xs font-semibold text-green-600 opacity-0 transition-opacity" :class="{'opacity-100': true}">Auto-saved</span>
                    </div>
                </div>
            </template>
        </div>
        
        <div class="mt-8 text-sm text-gray-500 bg-blue-50/50 p-4 rounded-lg border border-blue-100 flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <div>
                <span class="font-bold text-[var(--cjc-navy)] block mb-1">How this works:</span>
                Counts adjusted here are automatically saved and date-stamped. You can configure the list of library sections in the System Settings page.
            </div>
        </div>
    </div>

</div>
@endsection
