@extends('layouts.admin')

@section('title', ' | Section Counters')
@section('header_title', 'Library Section Headcounts')

@section('admin_content')
<div x-data="sectionCounter()" x-init="initData()" class="min-h-screen">

    <!-- OVERVIEW / GRID STATE -->
    <div>
        
        <!-- Header & Dashboard Summary -->
        <div class="mb-8 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div>
                    <h1 class="text-3xl font-black text-[var(--cjc-navy)] m-0 tracking-tight">Seating Statistics</h1>
                    <p class="text-[13px] text-gray-400 font-medium m-0 mt-0.5">Real-time occupancy &bull; CJC Library &bull; <span x-text="sections.length"></span> sections</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="bg-gray-100 rounded-xl p-1 flex shadow-sm">
                    <button @click="viewMode = 'overview'" :class="viewMode === 'overview' ? 'bg-[var(--cjc-red)] text-white shadow-md' : 'text-gray-500 hover:text-gray-800'" class="px-5 py-2 rounded-lg text-xs font-bold transition-all">Overview</button>
                    <button @click="viewMode = 'seatmap'; alert('Seat Map visualization is coming soon!')" :class="viewMode === 'seatmap' ? 'bg-[var(--cjc-red)] text-white shadow-md' : 'text-gray-500 hover:text-gray-800'" class="px-5 py-2 rounded-lg text-xs font-bold transition-all">Seat Map</button>
                </div>
                <button @click="alert('Hourly report generation will be sent to your email.')" class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 text-xs font-bold transition-all shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Hourly Report
                </button>
                <button @click="openAddModal()" class="px-5 py-2.5 rounded-xl bg-[var(--cjc-red)] hover:bg-red-800 text-white text-xs font-bold transition-all shadow-sm shadow-red-900/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Section
                </button>
                <button @click="exportCSV()" class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 text-xs font-bold transition-all shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Export CSV
                </button>
            </div>
        </div>

        <!-- 4 Top Cards -->
        <div class="grid grid-cols-4 gap-6 mb-6">
            <div class="bg-white/80 backdrop-blur-xl rounded-[24px] shadow-sm border border-white p-6 relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-br from-gray-50 to-transparent opacity-50 pointer-events-none"></div>
                <div class="relative z-10">
                    <p class="text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.2em] mb-3">Total Seats</p>
                    <p class="text-3xl font-bold text-[var(--cjc-navy)] mt-1.5 leading-none" x-text="totalSeats">0</p>
                    <p class="text-[13px] text-gray-400 mt-2 font-medium">across all sections</p>
                </div>
            </div>
            
            <div class="bg-white/80 backdrop-blur-xl rounded-[24px] shadow-sm border border-white p-6 relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-br from-[var(--cjc-red)]/5 to-transparent opacity-50 pointer-events-none group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                    <p class="text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.2em] mb-3">Occupied</p>
                    <p class="text-3xl font-bold text-[var(--cjc-red)] mt-1.5 leading-none" x-text="totalOccupied">0</p>
                    <p class="text-[13px] text-gray-400 mt-2 font-medium"><span x-text="overallPercent + '%'"></span> utilization</p>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-[24px] shadow-sm border border-white p-6 relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-transparent opacity-50 pointer-events-none group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                    <p class="text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.2em] mb-3">Reserved</p>
                    <p class="text-3xl font-bold text-amber-500 mt-1.5 leading-none" x-text="totalReserved">0</p>
                    <p class="text-[13px] text-gray-400 mt-2 font-medium">pending check-in</p>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-[24px] shadow-sm border border-white p-6 relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-br from-green-500/5 to-transparent opacity-50 pointer-events-none group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                    <p class="text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.2em] mb-3">Available</p>
                    <p class="text-3xl font-bold text-green-500 mt-1.5 leading-none" x-text="availableSeats">0</p>
                    <p class="text-[13px] text-gray-400 mt-2 font-medium">seats open now</p>
                </div>
            </div>
        </div>

        <!-- Overall Occupancy -->
        <div class="bg-white/80 backdrop-blur-xl rounded-[24px] shadow-sm border border-white p-7 mb-6 relative overflow-hidden">
            <div class="flex justify-between items-end mb-4">
                <p class="text-[12px] font-extrabold text-gray-500 uppercase tracking-[0.15em]">Overall Occupancy</p>
                <p class="text-3xl font-bold text-orange-500 mt-1.5 leading-none drop-shadow-sm" x-text="overallPercent + '%'">0%</p>
            </div>
            <div class="w-full h-3.5 bg-gray-100 rounded-full overflow-hidden flex shadow-inner">
                <div class="h-full bg-gradient-to-r from-orange-400 to-orange-500 transition-all duration-700 ease-out shadow-sm" :style="`width: ${overallPercent}%`"></div>
            </div>
            <div class="flex items-center gap-8 mt-5">
                <div class="flex items-center gap-2.5"><span class="w-3 h-3 rounded-full bg-orange-500 shadow-sm"></span><span class="text-[13px] text-gray-400 font-semibold">Occupied (<span x-text="overallPercent + '%'"></span>)</span></div>
                <div class="flex items-center gap-2.5"><span class="w-3 h-3 rounded-full bg-amber-400 shadow-sm"></span><span class="text-[13px] text-gray-400 font-semibold">Reserved (0%)</span></div>
                <div class="flex items-center gap-2.5"><span class="w-3 h-3 rounded-full bg-gray-200 shadow-sm"></span><span class="text-[13px] text-gray-400 font-semibold">Available (<span x-text="(100-overallPercent) + '%'"></span>)</span></div>
            </div>
        </div>

        <!-- Chart Placeholders (Temporarily hidden until real data tracking is implemented. DO NOT DELETE - Design approved) -->
        <!--
        <div class="grid grid-cols-3 gap-6 mb-10">
            <div class="bg-white/50 backdrop-blur-md rounded-[24px] border border-gray-100 p-6 flex flex-col h-[280px] relative overflow-hidden group hover:border-gray-300 transition-colors">
                <div class="flex justify-between items-center z-10 mb-2">
                    <p class="text-[12px] font-extrabold text-gray-800 uppercase tracking-widest">Today, Hourly</p>
                </div>
                <div class="flex-1 relative w-full h-full">
                    <canvas id="hourlyChart"></canvas>
                </div>
            </div>
            <div class="bg-white/50 backdrop-blur-md rounded-[24px] border border-gray-100 p-6 flex flex-col h-[280px] relative overflow-hidden group hover:border-gray-300 transition-colors">
                <div class="flex justify-between items-center z-10 mb-2">
                    <p class="text-[12px] font-extrabold text-gray-800 uppercase tracking-widest">This Week</p>
                </div>
                <div class="flex-1 relative w-full h-full">
                    <canvas id="weeklyChart"></canvas>
                </div>
            </div>
            <div class="bg-white/50 backdrop-blur-md rounded-[24px] border border-gray-100 p-6 flex flex-col h-[280px] relative overflow-hidden group hover:border-gray-300 transition-colors">
                <div class="flex justify-between items-center z-10 mb-2">
                    <p class="text-[12px] font-extrabold text-gray-800 uppercase tracking-widest">Peak Hours</p>
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <div class="flex-1 relative w-full h-full">
                    <canvas id="peakChart"></canvas>
                </div>
            </div>
        </div>
        -->

        <!-- Sections Grid -->
        <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold text-[var(--text-subtle)] uppercase tracking-widest">Filter:</span>
                <div class="flex items-center gap-2">
                    <button @click="filter = 'all'" :class="filter === 'all' ? 'bg-[var(--cjc-red)] text-white shadow-sm border-transparent' : 'bg-white border-gray-200 text-gray-500 hover:bg-gray-50'" class="text-[11px] font-bold px-4 py-1.5 rounded-full border transition-colors">All Sections</button>
                    <button @click="filter = 'available'" :class="filter === 'available' ? 'bg-[var(--cjc-red)] text-white shadow-sm border-transparent' : 'bg-white border-gray-200 text-gray-500 hover:bg-gray-50'" class="text-[11px] font-bold px-4 py-1.5 rounded-full border transition-colors">Available</button>
                    <button @click="filter = 'hightraffic'" :class="filter === 'hightraffic' ? 'bg-[var(--cjc-red)] text-white shadow-sm border-transparent' : 'bg-white border-gray-200 text-gray-500 hover:bg-gray-50'" class="text-[11px] font-bold px-4 py-1.5 rounded-full border transition-colors">High Traffic</button>
                    <button @click="filter = 'critical'" :class="filter === 'critical' ? 'bg-[var(--cjc-red)] text-white shadow-sm border-transparent' : 'bg-white border-gray-200 text-gray-500 hover:bg-gray-50'" class="text-[11px] font-bold px-4 py-1.5 rounded-full border transition-colors">Critical</button>
                </div>
            </div>
            
            <div class="text-[13px] text-gray-400 font-medium">
                <span x-text="filteredSections.length"></span> of <span x-text="sections.length"></span> sections
            </div>
        </div>

        <div class="space-y-5 pb-20">
            <template x-for="section in filteredSections" :key="section.id">
                <div class="bg-white rounded-[24px] shadow-sm border border-white p-7 relative group hover:shadow-md transition-shadow">
                    
                    <div class="flex items-start justify-between mb-8">
                        <div class="flex items-center gap-5">
                            <div class="w-14 h-14 rounded-full border-[2px] border-red-100 flex items-center justify-center font-black text-[13px] tracking-widest text-[var(--cjc-red)] shadow-sm bg-red-50/50" x-text="section.id">
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-[var(--cjc-navy)] m-0" x-text="section.name"></h3>
                                <p class="text-[13px] text-gray-400 mt-1 font-medium"><span x-text="section.total"></span> seats total</p>
                            </div>
                        </div>

                        <div class="flex flex-col items-end gap-3">
                            <!-- % Badge and Edit/Delete Actions -->
                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <p class="text-3xl font-bold leading-none drop-shadow-sm transition-colors"
                                       :class="section.occupied >= section.total ? 'text-[var(--cjc-red)]' : 'text-green-500'"
                                       x-text="Math.round((section.occupied / section.total) * 100) + '%'">
                                    </p>
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider mt-2 border shadow-sm transition-colors"
                                          :class="section.occupied >= section.total ? 'bg-red-50 text-[var(--cjc-red)] border-red-100' : 'bg-green-50 text-green-600 border-green-100'"
                                          x-text="section.occupied >= section.total ? 'Critical' : 'Available'">
                                    </span>
                                </div>

                                <!-- Card Actions -->
                                <div class="flex items-center gap-2 border-l border-gray-100 pl-4">
                                    <button @click="openEditModal(section)" class="w-8 h-8 rounded-lg border border-gray-200 text-gray-400 hover:text-blue-500 hover:border-blue-200 hover:bg-blue-50 flex items-center justify-center transition-all shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <button @click="deleteSection(section)" class="w-8 h-8 rounded-lg border border-gray-200 text-gray-400 hover:text-[var(--cjc-red)] hover:border-red-200 hover:bg-red-50 flex items-center justify-center transition-all shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Inline Counters for quick logging -->
                            <div class="inline-flex items-center gap-1.5 bg-white border border-[var(--border-light)] rounded-xl p-1 shadow-sm mt-1">
                                <button @click="updateSectionInline(section, -1)" :disabled="section.occupied <= 0" 
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-red-50 hover:text-red-500 disabled:opacity-40 disabled:cursor-not-allowed transition-colors cursor-pointer shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"></path></svg>
                                </button>
                                <span class="font-black text-[var(--cjc-navy)] w-8 text-center text-lg" x-text="section.occupied"></span>
                                <button @click="updateSectionInline(section, 1)" :disabled="section.occupied >= section.total" 
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-green-50 hover:text-green-600 disabled:opacity-40 disabled:cursor-not-allowed transition-colors cursor-pointer shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden mb-3">
                        <div class="h-full transition-all duration-700 ease-out" 
                             :class="section.occupied >= section.total ? 'bg-[var(--cjc-red)]' : 'bg-green-500'"
                             :style="`width: ${(section.occupied / section.total) * 100}%`"></div>
                    </div>
                    
                    <!-- Stats legend -->
                    <div class="flex items-center gap-6">
                        <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full shadow-sm" :class="section.occupied >= section.total ? 'bg-[var(--cjc-red)]' : 'bg-green-500'"></span><span class="text-[11px] text-gray-400 font-semibold"><span x-text="section.occupied"></span> Occupied</span></div>
                        <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-amber-400 shadow-sm"></span><span class="text-[11px] text-gray-400 font-semibold">0 Reserved</span></div>
                        <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-gray-300 shadow-sm"></span><span class="text-[11px] text-gray-400 font-semibold"><span x-text="section.total - section.occupied"></span> Available</span></div>
                    </div>

                </div>
            </template>
            <div x-show="sections.length === 0" class="p-8 text-center text-sm font-bold text-gray-400 bg-white/50 rounded-2xl border border-gray-100">
                No sections added yet.
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
                        <button type="button" @click="closeAddModal()" class="px-5 py-2.5 text-sm font-bold text-gray-600 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-[var(--cjc-red)] hover:bg-red-700 rounded-lg transition-colors flex items-center gap-2 cursor-pointer shadow-md">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Save Section
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Section Modal -->
        <div x-show="isEditModalOpen" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeEditModal()" x-show="isEditModalOpen" x-transition.opacity></div>
            
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-8 z-10" x-show="isEditModalOpen" x-transition.scale.origin.bottom>
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-[var(--cjc-navy)]">Edit Section</h2>
                    <button @click="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <form @submit.prevent="submitEditSection">
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-[var(--text-subtle)] uppercase tracking-wider mb-2">Section Code</label>
                        <input type="text" x-model="editSectionData.id" disabled class="w-full rounded-lg border-gray-300 shadow-sm bg-gray-100 text-gray-500 px-4 py-3 cursor-not-allowed">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-[var(--text-subtle)] uppercase tracking-wider mb-2">Section Name</label>
                        <input type="text" x-model="editSectionData.name" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[var(--cjc-navy)] focus:ring-[var(--cjc-navy)] px-4 py-3">
                    </div>
                    
                    <div class="mb-8">
                        <label class="block text-sm font-bold text-[var(--text-subtle)] uppercase tracking-wider mb-2">Total Seats Capacity</label>
                        <input type="number" min="1" x-model="editSectionData.total" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[var(--cjc-navy)] focus:ring-[var(--cjc-navy)] px-4 py-3">
                    </div>
                    
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="closeEditModal()" class="px-5 py-2.5 text-sm font-bold text-gray-600 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-[var(--cjc-navy)] hover:bg-blue-900 rounded-lg transition-colors flex items-center gap-2 cursor-pointer shadow-md">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function sectionCounter() {
    return {
        sections: {!! json_encode($logs) !!},
        activeSection: null,
        
        filter: 'all',
        viewMode: 'overview',
        
        isAddModalOpen: false,
        newSection: { name: '', total: 50 },

        isEditModalOpen: false,
        editSectionData: { id: '', name: '', total: 50, occupied: 0, reserved: 0 },
        
        clockHm: '--:--',
        clockSec: '--',
        clockDate: 'Loading...',

        initData() {
            if(this.sections.length === 0) {
                let defaultNames = {!! json_encode($settings['library_sections'] ?? ['General Reading', 'Discussion Room', 'Internet Section', 'Periodicals']) !!};
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

            // Initialize mock charts (Temporarily disabled pending real data)
            // setTimeout(() => this.initCharts(), 500);
        },

        initCharts() {
            // Setup generic options
            const defaultOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 10 } } },
                    y: { grid: { borderDash: [4, 4], color: '#f3f4f6' }, border: { display: false }, ticks: { font: { family: 'Inter', size: 10 }, padding: 10 } }
                }
            };

            // Today Hourly (Line Chart)
            const ctxHourly = document.getElementById('hourlyChart');
            if (ctxHourly) {
                new Chart(ctxHourly, {
                    type: 'line',
                    data: {
                        labels: ['8am', '10am', '12pm', '2pm', '4pm', '6pm'],
                        datasets: [{
                            data: [12, 45, 80, 65, 90, 40],
                            borderColor: '#1e3a8a', // cjc-navy
                            backgroundColor: 'rgba(30, 58, 138, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#1e3a8a',
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }]
                    },
                    options: defaultOptions
                });
            }

            // This Week (Bar Chart)
            const ctxWeekly = document.getElementById('weeklyChart');
            if (ctxWeekly) {
                new Chart(ctxWeekly, {
                    type: 'bar',
                    data: {
                        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                        datasets: [{
                            data: [320, 450, 410, 500, 580, 210],
                            backgroundColor: '#9ca3af',
                            hoverBackgroundColor: '#f97316', // orange
                            borderRadius: 4,
                            barThickness: 12
                        }]
                    },
                    options: defaultOptions
                });
            }

            // Peak Hours (Line Area)
            const ctxPeak = document.getElementById('peakChart');
            if (ctxPeak) {
                new Chart(ctxPeak, {
                    type: 'line',
                    data: {
                        labels: ['M', 'T', 'W', 'T', 'F', 'S'],
                        datasets: [{
                            data: [75, 82, 60, 95, 100, 40],
                            borderColor: '#dc2626', // red
                            backgroundColor: 'rgba(220, 38, 38, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                            pointRadius: 0,
                            pointHoverRadius: 5
                        }]
                    },
                    options: defaultOptions
                });
            }
        },

        get totalSeats() {
            return this.sections.reduce((sum, s) => sum + parseInt(s.total), 0);
        },
        
        get totalReserved() {
            return this.sections.reduce((sum, s) => sum + parseInt(s.reserved || 0), 0);
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

        get filteredSections() {
            if (this.filter === 'available') {
                return this.sections.filter(s => s.occupied < s.total);
            } else if (this.filter === 'hightraffic') {
                return this.sections.filter(s => (s.occupied / s.total) >= 0.75 && s.occupied < s.total);
            } else if (this.filter === 'critical') {
                return this.sections.filter(s => s.occupied >= s.total);
            }
            return this.sections;
        },

        openAddModal() {
            this.newSection = { name: '', total: 50 };
            this.isAddModalOpen = true;
        },
        
        closeAddModal() {
            this.isAddModalOpen = false;
        },

        openEditModal(section) {
            this.editSectionData = { ...section };
            this.isEditModalOpen = true;
        },

        closeEditModal() {
            this.isEditModalOpen = false;
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

        async submitEditSection() {
            if (!this.editSectionData.name) return;
            
            const idx = this.sections.findIndex(s => s.id === this.editSectionData.id);
            if(idx !== -1) {
                // Ensure occupied does not exceed the new total capacity
                let total = parseInt(this.editSectionData.total);
                let occupied = this.editSectionData.occupied;
                if (occupied > total) occupied = total;

                // Optimistically update UI
                this.sections[idx].name = this.editSectionData.name;
                this.sections[idx].total = total;
                this.sections[idx].occupied = occupied;
            }
            
            this.closeEditModal();
            
            // Sync with backend
            try {
                await fetch('{{ route('admin.sections.upsert') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        id: this.editSectionData.id,
                        name: this.editSectionData.name,
                        total: parseInt(this.editSectionData.total),
                        occupied: this.editSectionData.occupied,
                        reserved: this.editSectionData.reserved
                    })
                });
            } catch (e) {
                console.error('Failed to update section', e);
            }
        },

        deleteSection(section) {
            if(confirm('Are you sure you want to delete the ' + section.name + ' section?')) {
                this.sections = this.sections.filter(s => s.id !== section.id);
            }
        },

        exportCSV() {
            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "Section ID,Section Name,Total Capacity,Occupied,Reserved,Status\n";
            
            this.sections.forEach(s => {
                let status = s.occupied >= s.total ? "Critical" : "Available";
                let name = s.name.replace(/"/g, '""'); // escape quotes
                let row = `${s.id},"${name}",${s.total},${s.occupied},${s.reserved},${status}`;
                csvContent += row + "\r\n";
            });
            
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "library_seating_statistics.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },

        async updateSectionInline(section, change) {
            let newOccupied = parseInt(section.occupied) + change;
            if(newOccupied < 0) newOccupied = 0;
            if(newOccupied > section.total) newOccupied = section.total;
            
            section.occupied = newOccupied;
            
            try {
                await fetch('{{ route('admin.sections.upsert') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        id: section.id,
                        name: section.name,
                        occupied: section.occupied,
                        reserved: section.reserved,
                        total: section.total
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
