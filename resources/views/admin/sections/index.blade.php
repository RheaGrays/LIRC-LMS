@extends('layouts.admin')

@section('title', ' | Section Counters')
@section('header_title', 'Library Section Headcounts')

@section('admin_content')
<div x-data="sectionCounter()" x-init="initData()" class="min-h-screen">

    <!-- OVERVIEW / GRID STATE -->
    <div>
        
        <!-- Header & Dashboard Summary -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-[var(--cjc-navy)] m-0 tracking-tight">CJC Library</h1>
                        <p class="text-sm text-[var(--text-muted)] font-medium m-0">Seat Availability Kiosk</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-4xl font-bold text-[var(--cjc-navy)] leading-none tracking-tight">
                        <span x-text="clockHm">--:--</span><span class="text-[0.4em] text-[var(--text-muted)] ml-1 font-semibold align-text-top" x-text="clockSec">--</span>
                    </div>
                    <div class="text-xs text-[var(--text-subtle)] font-medium mt-1 uppercase tracking-wider" x-text="clockDate">
                        Loading...
                    </div>
                </div>
            </div>

            <!-- Stats Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-[var(--border-light)] p-8 flex items-center justify-between">
                
                <div class="flex-1 flex justify-between pr-12 border-r border-gray-100">
                    <div>
                        <p class="text-xs font-bold text-[var(--text-subtle)] uppercase tracking-wider mb-2">Total Seats</p>
                        <p class="text-3xl font-bold text-[var(--cjc-navy)] m-0" x-text="totalSeats">0</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-[var(--text-subtle)] uppercase tracking-wider mb-2">Occupied</p>
                        <p class="text-3xl font-bold text-[var(--cjc-navy)] m-0" x-text="totalOccupied">0</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-[var(--text-subtle)] uppercase tracking-wider mb-2">Available</p>
                        <p class="text-3xl font-bold text-[var(--cjc-navy)] m-0" x-text="availableSeats">0</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-[var(--text-subtle)] uppercase tracking-wider mb-2">Overall</p>
                        <p class="text-3xl font-bold text-[var(--cjc-navy)] m-0" x-text="overallPercent + '%'">0%</p>
                    </div>
                </div>
                
                <div class="pl-12 w-64 shrink-0">
                    <p class="text-[10px] font-bold text-[var(--text-subtle)] uppercase tracking-wider mb-3">Occupancy</p>
                    <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                        <div class="bg-[var(--cjc-navy)] h-3 rounded-full transition-all duration-500 ease-out" :style="`width: ${overallPercent}%`"></div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Sections Grid -->
        <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-[var(--text-subtle)] uppercase tracking-widest">Select A Section</span>
                <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                <span class="text-xs font-bold text-[var(--text-subtle)] uppercase tracking-widest" x-text="sections.length + ' Sections'"></span>
            </div>
            
            <button @click="openAddModal()" class="flex items-center gap-2 text-sm font-bold text-white bg-[var(--cjc-navy)] hover:bg-blue-900 px-4 py-2 rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Section
            </button>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-[var(--border-light)] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left whitespace-nowrap">
                    <thead class="bg-gray-50/80 border-b border-[var(--border-light)]">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-[var(--text-subtle)] uppercase tracking-wider">Code</th>
                            <th class="px-6 py-4 text-xs font-bold text-[var(--text-subtle)] uppercase tracking-wider">Section Name</th>
                            <th class="px-6 py-4 text-xs font-bold text-[var(--text-subtle)] uppercase tracking-wider">Occupancy</th>
                            <th class="px-6 py-4 text-xs font-bold text-[var(--text-subtle)] uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-xs font-bold text-[var(--text-subtle)] uppercase tracking-wider text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="section in sections" :key="section.id">
                            <tr class="hover:bg-gray-50/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="w-10 h-10 rounded-full border-2 flex items-center justify-center font-bold text-xs" 
                                         :class="section.occupied >= section.total ? 'border-[var(--cjc-red)] text-[var(--cjc-red)]' : 'border-[var(--cjc-navy)] text-[var(--cjc-navy)]'"
                                         x-text="section.id">
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <h3 class="text-lg font-bold text-[var(--cjc-navy)] m-0" x-text="section.name"></h3>
                                    <p class="text-xs text-[var(--text-subtle)] mt-1 font-medium"><span x-text="section.total"></span> total capacity</p>
                                </td>
                                <td class="px-6 py-4 min-w-[200px]">
                                    <div class="flex items-center gap-3 mb-1.5">
                                        <span class="font-bold text-sm" :class="section.occupied >= section.total ? 'text-[var(--cjc-red)]' : 'text-[var(--cjc-navy)]'" x-text="section.occupied"></span>
                                        <span class="text-xs text-[var(--text-subtle)]">/ <span x-text="section.total"></span> seats</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-1.5 rounded-full transition-all duration-500 ease-out" 
                                             :class="section.occupied >= section.total ? 'bg-[var(--cjc-red)]' : 'bg-[var(--cjc-navy)]'"
                                             :style="`width: ${(section.occupied / section.total) * 100}%`"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="inline-flex px-3 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider transition-colors"
                                         :class="section.occupied >= section.total ? 'bg-red-50 text-[var(--cjc-red)]' : 'bg-gray-50 text-gray-500'"
                                         x-text="section.occupied >= section.total ? 'Full' : 'Available'">
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="openModal(section)" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-[var(--cjc-navy)] bg-blue-50/50 hover:bg-blue-100 border border-blue-100 rounded-lg transition-colors">
                                        Log Seats
                                        <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div x-show="sections.length === 0" class="p-8 text-center text-sm font-bold text-gray-400">
                    No sections added yet.
                </div>
            </div>
        </div>
        
        <!-- Add Section Modal -->
        <div x-show="isAddModalOpen" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeAddModal()" x-show="isAddModalOpen" x-transition.opacity></div>
            
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-8 z-10" x-show="isAddModalOpen" x-transition.scale.origin.bottom>
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-[var(--cjc-navy)]">Add New Section</h2>
                    <button @click="closeAddModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <form @submit.prevent="submitNewSection">
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-[var(--text-subtle)] uppercase tracking-wider mb-2">Section Name</label>
                        <input type="text" x-model="newSection.name" required placeholder="e.g. Reference Room" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[var(--cjc-red)] focus:ring-[var(--cjc-red)] px-4 py-3">
                    </div>
                    
                    <div class="mb-8">
                        <label class="block text-sm font-bold text-[var(--text-subtle)] uppercase tracking-wider mb-2">Total Seats Capacity</label>
                        <input type="number" min="1" x-model="newSection.total" required placeholder="50" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[var(--cjc-red)] focus:ring-[var(--cjc-red)] px-4 py-3">
                    </div>
                    
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="closeAddModal()" class="px-5 py-2.5 text-sm font-bold text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-[var(--cjc-red)] hover:bg-[var(--cjc-red-dark)] rounded-lg transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Save Section
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- Tap Modal (Native LEMS Design) -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeModal()" x-show="modalOpen" x-transition.opacity></div>
        
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 z-10 mx-4" x-show="modalOpen" x-transition.scale.origin.bottom>
            
            <!-- Modal Header -->
            <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
                <h2 class="font-bold text-[var(--cjc-navy)] uppercase tracking-wider text-sm">Log Seat Activity</h2>
                <button @click="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <!-- Section Info -->
            <div class="text-center mb-8">
                <div class="inline-block px-3 py-1 bg-gray-100 text-[var(--text-muted)] font-bold text-xs rounded-md mb-3 tracking-wider">
                    <span x-text="activeSection?.id"></span>
                </div>
                <h3 class="text-3xl font-bold text-[var(--cjc-navy)] leading-tight mb-2" x-text="activeSection?.name"></h3>
                <p class="text-[var(--text-subtle)] text-sm font-medium">
                    <span :class="activeSection?.occupied >= activeSection?.total ? 'text-[var(--cjc-red)]' : ''">
                        <span x-text="activeSection?.occupied"></span> / <span x-text="activeSection?.total"></span> Seats Occupied
                    </span>
                </p>
            </div>
            
            <!-- Interactive Counter -->
            <div class="flex items-center justify-center gap-6 mb-8">
                <!-- Minus Button -->
                <button @click="updateSection(-1)" :disabled="activeSection?.occupied <= 0" 
                        class="w-14 h-14 rounded-full flex items-center justify-center bg-gray-50 border border-gray-200 text-gray-500 hover:bg-red-50 hover:border-red-200 hover:text-red-500 disabled:opacity-40 disabled:cursor-not-allowed transition-all shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"></path></svg>
                </button>
                
                <!-- Center Number -->
                <div class="w-20 text-center flex-shrink-0">
                    <span class="text-6xl font-black transition-colors duration-300" 
                          :class="activeSection?.occupied >= activeSection?.total ? 'text-[var(--cjc-red)]' : 'text-[var(--cjc-navy)]'" 
                          x-text="activeSection?.occupied"></span>
                </div>
                
                <!-- Plus Button -->
                <button @click="updateSection(1)" :disabled="activeSection?.occupied >= activeSection?.total" 
                        class="w-14 h-14 rounded-full flex items-center justify-center bg-gray-50 border border-gray-200 text-gray-500 hover:bg-green-50 hover:border-green-200 hover:text-green-600 disabled:opacity-40 disabled:cursor-not-allowed transition-all shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                </button>
            </div>
            
            <!-- Progress Line -->
            <div class="w-full bg-gray-100 rounded-full h-1.5 mb-4 overflow-hidden">
                <div class="h-1.5 rounded-full transition-all duration-500 ease-out bg-[var(--cjc-navy)]" 
                     :class="{'bg-[var(--cjc-red)]': activeSection?.occupied >= activeSection?.total}"
                     :style="`width: ${(activeSection?.occupied / activeSection?.total) * 100}%`"></div>
            </div>
            
            <!-- Footer text -->
            <div class="text-center">
                <p class="text-[11px] text-gray-400 font-bold uppercase tracking-wider flex items-center justify-center gap-1">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Changes saved automatically
                </p>
            </div>
            
        </div>
    </div>

</div>

<script>
function sectionCounter() {
    return {
        sections: @json($logs),
        activeSection: null,
        modalOpen: false,
        
        isAddModalOpen: false,
        newSection: { name: '', total: 50 },
        
        clockHm: '--:--',
        clockSec: '--',
        clockDate: 'Loading...',

        initData() {
            if(this.sections.length === 0) {
                let defaultNames = @json($settings['library_sections'] ?? ['General Reading', 'Discussion Room', 'Internet Section', 'Periodicals']);
                this.sections = defaultNames.map(name => {
                    return {
                        id: name.substring(0, 3).toUpperCase(),
                        name: name,
                        total: 50,
                        occupied: 0,
                        reserved: 0
                    };
                });
            }
            
            this.updateClock();
            setInterval(() => this.updateClock(), 1000);
        },

        get totalSeats() {
            return this.sections.reduce((sum, s) => sum + parseInt(s.total), 0);
        },
        
        get totalOccupied() {
            return this.sections.reduce((sum, s) => sum + parseInt(s.occupied), 0);
        },
        
        get availableSeats() {
            return Math.max(0, this.totalSeats - this.totalOccupied);
        },
        
        get overallPercent() {
            if(this.totalSeats === 0) return 0;
            return Math.round((this.totalOccupied / this.totalSeats) * 100);
        },

        openModal(section) {
            this.activeSection = section;
            this.modalOpen = true;
        },

        closeModal() {
            this.modalOpen = false;
            setTimeout(() => { this.activeSection = null; }, 300);
        },
        
        openAddModal() {
            this.newSection = { name: '', total: 50 };
            this.isAddModalOpen = true;
        },
        
        closeAddModal() {
            this.isAddModalOpen = false;
        },
        
        async submitNewSection() {
            if (!this.newSection.name) return;
            
            // Create a pseudo ID (first 3 letters uppercase)
            let newId = this.newSection.name.substring(0, 3).toUpperCase();
            
            // Avoid duplicate IDs by appending a number if necessary
            let count = 1;
            let originalId = newId;
            while(this.sections.find(s => s.id === newId)) {
                newId = originalId.substring(0, 2) + count;
                count++;
            }
            
            let sectionData = {
                id: newId,
                name: this.newSection.name,
                total: parseInt(this.newSection.total),
                occupied: 0,
                reserved: 0
            };
            
            // Optimistically add to UI
            this.sections.push(sectionData);
            this.closeAddModal();
            
            // Sync with backend
            try {
                await fetch('{{ route('admin.sections.upsert') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(sectionData)
                });
            } catch (e) {
                console.error('Failed to create new section', e);
            }
        },

        async updateSection(change) {
            if(!this.activeSection) return;
            
            let newOccupied = parseInt(this.activeSection.occupied) + change;
            if(newOccupied < 0) newOccupied = 0;
            if(newOccupied > this.activeSection.total) newOccupied = this.activeSection.total;
            
            this.activeSection.occupied = newOccupied;
            
            try {
                await fetch('{{ route('admin.sections.upsert') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        id: this.activeSection.id,
                        name: this.activeSection.name,
                        occupied: this.activeSection.occupied,
                        reserved: this.activeSection.reserved,
                        total: this.activeSection.total
                    })
                });
            } catch (e) {
                console.error('Failed to save section log', e);
            }
        },

        updateClock() {
            const now = new Date();
            let h = now.getHours();
            let m = now.getMinutes();
            let s = now.getSeconds();
            h = h % 12;
            h = h ? h : 12; 
            
            this.clockHm = h.toString().padStart(2, '0') + ':' + m.toString().padStart(2, '0');
            this.clockSec = s.toString().padStart(2, '0');
            
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            this.clockDate = now.toLocaleDateString('en-US', options);
        }
    }
}
</script>
@endsection
