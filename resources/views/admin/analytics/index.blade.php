@extends('layouts.admin')

@section('title', ' | Analytics')
@section('header_title', 'Analytics & Reports')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function analyticsApp() {
    return {
        period: 'today',
        term_id: '',
        loading: true,
        totalTraffic: 0,
        summary: { total_patrons: 0, today_traffic: 0, month_traffic: 0, active_dept: '-' },
        trafficChartInstance: null,
        deptChartInstance: null,
        pollTimer: null,
        
        init() {
            this.$nextTick(async () => {
                Chart.defaults.font.family = 'Inter, sans-serif';
                Chart.defaults.color = '#64748b';
                
                // Fetch data first, then render charts with populated data
                await this.fetchData(false);
                
                // Real-time auto-polling every 2.5s
                if (!this.pollTimer) {
                    this.pollTimer = setInterval(() => {
                        this.fetchData(true);
                    }, 2500);
                }
            });
        },
        
        async fetchData(isSilent = false) {
            if (!isSilent) {
                this.loading = true;
            }
            try {
                let url = `/admin/analytics/data?period=${this.period}&_t=${Date.now()}`;
                if (this.term_id) {
                    url += `&term_id=${this.term_id}`;
                }
                const response = await fetch(url);
                const data = await response.json();
                
                this.totalTraffic = (data.traffic.values || []).reduce((a, b) => a + b, 0);
                this.summary = data.summary || this.summary;
                
                // Render or Update Traffic Volume (Line Chart)
                const trafficCanvas = document.getElementById('trafficChart');
                if (trafficCanvas) {
                    const trafficCtx = trafficCanvas.getContext('2d');
                    
                    // Create beautiful gradient like the reference image
                    let gradient = trafficCtx.createLinearGradient(0, 0, 0, 400);
                    gradient.addColorStop(0, 'rgba(196, 30, 42, 0.5)'); // Strong red at top
                    gradient.addColorStop(1, 'rgba(196, 30, 42, 0.0)'); // Transparent at bottom

                    if (!this.trafficChartInstance) {
                        this.trafficChartInstance = new Chart(trafficCtx, {
                            type: 'line',
                            data: {
                                labels: data.traffic.labels,
                                datasets: [{
                                    label: 'Entries',
                                    data: data.traffic.values,
                                    borderColor: '#c41e2a',
                                    backgroundColor: gradient,
                                    borderWidth: 3,
                                    fill: true,
                                    tension: 0.4,
                                    pointBackgroundColor: '#ffffff',
                                    pointBorderColor: '#c41e2a',
                                    pointBorderWidth: 2,
                                    pointRadius: 4,
                                    pointHoverRadius: 6
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: { mode: 'index', intersect: false },
                                plugins: {
                                    legend: { display: false },
                                    tooltip: { backgroundColor: '#0f2744', padding: 12, cornerRadius: 8 }
                                },
                                scales: {
                                    y: { beginAtZero: true, grid: { borderDash: [4, 4], color: '#f1f5f9' }, ticks: { precision: 0 } },
                                    x: { grid: { display: false } }
                                }
                            }
                        });
                    } else {
                        this.trafficChartInstance.data.labels = data.traffic.labels;
                        this.trafficChartInstance.data.datasets[0].data = data.traffic.values;
                        this.trafficChartInstance.update();
                    }
                }
                
                // Render or Update Department Breakdown (Doughnut Chart)
                const deptCanvas = document.getElementById('deptChart');
                if (deptCanvas) {
                    // Match the vibrant colors from the reference
                    const colors = ['#c41e2a', '#d4a418', '#3b82f6', '#10b981', '#8b5cf6'];
                    if (!this.deptChartInstance) {
                        const deptCtx = deptCanvas.getContext('2d');
                        this.deptChartInstance = new Chart(deptCtx, {
                            type: 'doughnut',
                            data: {
                                labels: data.departments.labels,
                                datasets: [{
                                    data: data.departments.values,
                                    backgroundColor: colors.slice(0, data.departments.labels.length),
                                    borderWidth: 0,
                                    hoverOffset: 4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true, boxWidth: 8, font: { size: 11, weight: 'bold' } } },
                                    tooltip: { backgroundColor: '#0f2744', padding: 12, cornerRadius: 8 }
                                },
                                cutout: '75%'
                            }
                        });
                    } else {
                        this.deptChartInstance.data.labels = data.departments.labels;
                        this.deptChartInstance.data.datasets[0].data = data.departments.values;
                        this.deptChartInstance.data.datasets[0].backgroundColor = colors.slice(0, data.departments.labels.length);
                        this.deptChartInstance.update();
                    }
                }
                
            } catch (err) {
                console.error("Failed to load analytics data", err);
            } finally {
                if (!isSilent) {
                    this.loading = false;
                }
            }
        }
    };
}
</script>
@endpush

@section('admin_content')
<div class="space-y-6 pb-10" x-data="analyticsApp()">

    <!-- Dedicated Monthly Attendance Report Generator -->
    <div class="rounded-2xl p-6 bg-white border border-gray-100 shadow-sm mb-6 w-full">
        <div class="flex flex-col items-start gap-4 w-full">
            <div class="w-full flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-[var(--cjc-navy)] tracking-tight">Monthly Attendance Report</h2>
                    <p class="text-[13px] text-gray-500 mt-0.5 font-medium">Generate and download consolidated attendance logs.</p>
                </div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold bg-red-50 text-[var(--cjc-red)] uppercase tracking-wider border border-red-50">
                    <svg class="w-3.5 h-3.5 text-[var(--cjc-red)] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Report Generator
                </div>
            </div>
            
            <form action="{{ route('admin.analytics.export-monthly-report') }}" method="GET" @submit="if(window.showToast) window.showToast('Generating and downloading report...', 'info')" class="w-full flex flex-wrap items-end gap-3 bg-gray-50/60 p-4 rounded-xl border border-gray-100">
                
                <!-- 1. School Year Filter -->
                <div class="w-full sm:w-auto sm:flex-1 min-w-[160px]">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 mb-1.5 ml-1">School Year</label>
                    <select name="term_id" class="no-tomselect w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-[13px] text-gray-800 focus:outline-none focus:border-[var(--cjc-navy)] focus:ring-1 focus:ring-[var(--cjc-navy)] font-medium h-[44px] shadow-sm transition-shadow">
                        <option value="">AY {{ date('Y') }}-{{ date('Y') + 1 }} (Current)</option>
                        @foreach($terms as $term)
                            <option value="{{ $term->id }}">{{ $term->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 2. Month Filter -->
                <div class="w-full sm:w-auto sm:flex-1 min-w-[160px]">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 mb-1.5 ml-1">Month</label>
                    <input type="month" name="month" value="{{ date('Y-m') }}" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-[13px] text-gray-800 focus:outline-none focus:border-[var(--cjc-navy)] focus:ring-1 focus:ring-[var(--cjc-navy)] font-medium h-[44px] shadow-sm transition-shadow">
                </div>

                <!-- 3. Department Filter (NEW) -->
                <div class="w-full sm:w-auto sm:flex-1 min-w-[160px] relative" x-data="{ 
                    openDeptDropdown: false, 
                    selectedDeptId: '', 
                    selectedDeptName: 'All Departments' 
                }">
                    <input type="hidden" name="department_id" :value="selectedDeptId">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 mb-1.5 ml-1">Department</label>
                    
                    <button type="button" @click="openDeptDropdown = !openDeptDropdown" @click.outside="openDeptDropdown = false" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-[13px] text-gray-800 focus:outline-none focus:border-[var(--cjc-navy)] focus:ring-1 focus:ring-[var(--cjc-navy)] font-medium h-[44px] flex items-center justify-between gap-2 shadow-sm whitespace-nowrap transition-shadow">
                        <span class="truncate" x-text="selectedDeptName">All Departments</span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform shrink-0" :class="{'rotate-180': openDeptDropdown}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <!-- Dropdown Options Menu -->
                    <div x-show="openDeptDropdown" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" class="absolute left-0 mt-2 w-full min-w-[16rem] max-h-60 overflow-y-auto bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
                        <button type="button" @click="selectedDeptId = ''; selectedDeptName = 'All Departments'; openDeptDropdown = false" class="w-full px-4 py-2 text-left text-[13px] font-semibold flex items-center gap-2 hover:bg-gray-50 text-gray-800 transition-colors" :class="{'bg-red-50 text-[var(--cjc-red)] font-bold': selectedDeptId === ''}">
                            <span>🏢</span>
                            <span>All Departments</span>
                        </button>
                        @foreach($departments as $dept)
                            <button type="button" @click="selectedDeptId = '{{ $dept->id }}'; selectedDeptName = '{{ addslashes($dept->name) }}'; openDeptDropdown = false" class="w-full px-4 py-2 text-left text-[13px] font-medium hover:bg-gray-50 text-gray-700 transition-colors truncate" :class="{'bg-red-50 text-[var(--cjc-red)] font-bold': selectedDeptId === '{{ $dept->id }}'}">
                                {{ $dept->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- 4. Program Filter -->
                <div class="w-full sm:w-auto sm:flex-1 min-w-[160px] relative" x-data="{ 
                    openProgDropdown: false, 
                    selectedProgId: '', 
                    selectedProgName: 'All Programs' 
                }">
                    <input type="hidden" name="program_id" :value="selectedProgId">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 mb-1.5 ml-1">Program</label>
                    
                    <button type="button" @click="openProgDropdown = !openProgDropdown" @click.outside="openProgDropdown = false" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-[13px] text-gray-800 focus:outline-none focus:border-[var(--cjc-navy)] focus:ring-1 focus:ring-[var(--cjc-navy)] font-medium h-[44px] flex items-center justify-between gap-2 shadow-sm whitespace-nowrap transition-shadow">
                        <span class="truncate" x-text="selectedProgName">All Programs</span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform shrink-0" :class="{'rotate-180': openProgDropdown}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <!-- Dropdown Options Menu -->
                    <div x-show="openProgDropdown" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" class="absolute left-0 mt-2 w-full min-w-[16rem] max-h-60 overflow-y-auto bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
                        <button type="button" @click="selectedProgId = ''; selectedProgName = 'All Programs'; openProgDropdown = false" class="w-full px-4 py-2 text-left text-[13px] font-semibold flex items-center gap-2 hover:bg-gray-50 text-gray-800 transition-colors" :class="{'bg-red-50 text-[var(--cjc-red)] font-bold': selectedProgId === ''}">
                            <span>🎓</span>
                            <span>All Programs</span>
                        </button>
                        @foreach($programs as $prog)
                            <button type="button" @click="selectedProgId = '{{ $prog->id }}'; selectedProgName = '{{ addslashes($prog->name) }}'; openProgDropdown = false" class="w-full px-4 py-2 text-left text-[13px] font-medium hover:bg-gray-50 text-gray-700 transition-colors truncate" :class="{'bg-red-50 text-[var(--cjc-red)] font-bold': selectedProgId === '{{ $prog->id }}'}">
                                {{ $prog->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- 5. Format / Type Selector -->
                <div class="w-full sm:w-auto sm:flex-1 min-w-[140px] relative" x-data="{ openFormatDropdown: false, selectedFormat: 'excel' }">
                    <input type="hidden" name="format" :value="selectedFormat">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 mb-1.5 ml-1">Format</label>
                    
                    <button type="button" @click="openFormatDropdown = !openFormatDropdown" @click.outside="openFormatDropdown = false" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-[13px] text-gray-800 focus:outline-none focus:border-[var(--cjc-navy)] focus:ring-1 focus:ring-[var(--cjc-navy)] font-medium h-[44px] flex items-center justify-between gap-2 shadow-sm whitespace-nowrap transition-shadow">
                        <div class="flex items-center gap-2">
                            <template x-if="selectedFormat === 'excel'">
                                <span class="flex items-center gap-1.5 text-emerald-700 font-semibold whitespace-nowrap">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zM15.8 17.5L14 15l-1.8 2.5h-1.7l2.6-3.5-2.5-3.5h1.7l1.7 2.4 1.7-2.4h1.7l-2.5 3.5 2.6 3.5h-1.7zM13 9V3.5L18.5 9H13z"/></svg>
                                    Excel
                                </span>
                            </template>
                            <template x-if="selectedFormat === 'word'">
                                <span class="flex items-center gap-1.5 text-blue-700 font-semibold whitespace-nowrap">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm1.8 15.5h-1.6l-1.2-4.5-1.2 4.5H10.2L8.5 10h1.5l1.1 4.5 1.2-4.5h1.4l1.2 4.5 1.1-4.5h1.5l-1.7 7.5zM13 9V3.5L18.5 9H13z"/></svg>
                                    Word
                                </span>
                            </template>
                            <template x-if="selectedFormat === 'pdf'">
                                <span class="flex items-center gap-1.5 text-red-700 font-semibold whitespace-nowrap">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-9.5 8.5c0 .8-.7 1.5-1.5 1.5H7v2H5.5V9H8c.8 0 1.5.7 1.5 1.5v1zm5 2c0 .8-.7 1.5-1.5 1.5h-2.5V9h2.5c.8 0 1.5.7 1.5 1.5v3zm3.5-3.5h-3v5H18v-1.5h-1.5v-1H18V10z"/></svg>
                                    PDF
                                </span>
                            </template>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 transition-transform shrink-0" :class="{'rotate-180': openFormatDropdown}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <!-- Dropdown Options Menu -->
                    <div x-show="openFormatDropdown" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
                        <button type="button" @click="selectedFormat = 'excel'; openFormatDropdown = false" class="w-full px-4 py-2 text-left text-[13px] font-medium flex items-center gap-2 hover:bg-emerald-50 hover:text-emerald-700 transition-colors" :class="{'bg-emerald-50 text-emerald-700 font-semibold': selectedFormat === 'excel'}">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zM15.8 17.5L14 15l-1.8 2.5h-1.7l2.6-3.5-2.5-3.5h1.7l1.7 2.4 1.7-2.4h1.7l-2.5 3.5 2.6 3.5h-1.7zM13 9V3.5L18.5 9H13z"/></svg>
                            Excel (.xlsx)
                        </button>
                        <button type="button" @click="selectedFormat = 'word'; openFormatDropdown = false" class="w-full px-4 py-2 text-left text-[13px] font-medium flex items-center gap-2 hover:bg-blue-50 hover:text-blue-700 transition-colors" :class="{'bg-blue-50 text-blue-700 font-semibold': selectedFormat === 'word'}">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm1.8 15.5h-1.6l-1.2-4.5-1.2 4.5H10.2L8.5 10h1.5l1.1 4.5 1.2-4.5h1.4l1.2 4.5 1.1-4.5h1.5l-1.7 7.5zM13 9V3.5L18.5 9H13z"/></svg>
                            Word (.doc)
                        </button>
                        <button type="button" @click="selectedFormat = 'pdf'; openFormatDropdown = false" class="w-full px-4 py-2 text-left text-[13px] font-medium flex items-center gap-2 hover:bg-red-50 hover:text-red-700 transition-colors" :class="{'bg-red-50 text-red-700 font-semibold': selectedFormat === 'pdf'}">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-9.5 8.5c0 .8-.7 1.5-1.5 1.5H7v2H5.5V9H8c.8 0 1.5.7 1.5 1.5v1zm5 2c0 .8-.7 1.5-1.5 1.5h-2.5V9h2.5c.8 0 1.5.7 1.5 1.5v3zm3.5-3.5h-3v5H18v-1.5h-1.5v-1H18V10z"/></svg>
                            PDF (.pdf)
                        </button>
                    </div>
                </div>

                <!-- Export Button -->
                <div class="w-full sm:w-auto">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-transparent select-none mb-1.5 hidden sm:block">&nbsp;</label>
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-6 bg-[var(--cjc-red)] hover:bg-red-700 text-white text-[13px] font-bold rounded-lg shadow-sm transition-all whitespace-nowrap h-[44px] hover:-translate-y-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Generate
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Header & Controls -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 bg-transparent py-1 mb-2">
        <div>
            <h2 class="text-xl font-bold text-[var(--cjc-navy)] tracking-tight">Dashboard Analytics</h2>
            <p class="text-[13px] text-gray-500 mt-0.5">Overview of library activity and foot traffic</p>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto bg-white p-1.5 rounded-lg border border-gray-200 shadow-sm">
            <select x-model="term_id" @change="fetchData()" class="no-tomselect border-none bg-transparent font-medium text-[12px] w-full md:w-40 focus:ring-0 text-gray-700 cursor-pointer">
                <option value="">By Term (All Time)</option>
                @foreach($terms as $term)
                    <option value="{{ $term->id }}">{{ $term->name }}</option>
                @endforeach
            </select>
            <div class="w-px h-4 bg-gray-200"></div>
            <select x-model="period" @change="fetchData()" :disabled="term_id !== ''" class="no-tomselect border-none bg-transparent font-medium text-[12px] w-full md:w-40 focus:ring-0 text-gray-700 cursor-pointer" :class="{'opacity-40': term_id !== ''}">
                <option value="today">Today (Hourly)</option>
                <option value="week">This Week (Daily)</option>
                <option value="month">This Month (Daily)</option>
                <option value="year">This Year (Monthly)</option>
            </select>
        </div>
    </div>

    <!-- Summary Cards Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
        <!-- Card 1: Patrons -->
        <div class="bg-white rounded-[16px] p-5 border border-gray-100 shadow-sm flex flex-col relative overflow-hidden transition-all hover:shadow-md">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-[12px] bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <div class="flex items-center text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg> Active</div>
            </div>
            <div class="text-[12px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total Patrons</div>
            <div class="text-[28px] font-bold text-[var(--cjc-navy)] leading-none" x-text="summary.total_patrons.toLocaleString()">0</div>
        </div>

        <!-- Card 2: Today -->
        <div class="bg-white rounded-[16px] p-5 border border-gray-100 shadow-sm flex flex-col relative overflow-hidden transition-all hover:shadow-md">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-[12px] bg-[var(--cjc-red)]/10 text-[var(--cjc-red)] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div class="flex items-center text-[10px] font-bold text-[var(--cjc-red)] bg-red-50 px-2 py-0.5 rounded-md"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg> +Daily</div>
            </div>
            <div class="text-[12px] font-bold text-gray-400 uppercase tracking-wider mb-1">Today's Entries</div>
            <div class="text-[28px] font-bold text-[var(--cjc-navy)] leading-none" x-text="summary.today_traffic.toLocaleString()">0</div>
        </div>

        <!-- Card 3: Month -->
        <div class="bg-white rounded-[16px] p-5 border border-gray-100 shadow-sm flex flex-col relative overflow-hidden transition-all hover:shadow-md">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-[12px] bg-orange-50 text-orange-500 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
                <div class="flex items-center text-[10px] font-bold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-md"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg> ~Avg</div>
            </div>
            <div class="text-[12px] font-bold text-gray-400 uppercase tracking-wider mb-1">Monthly Traffic</div>
            <div class="text-[28px] font-bold text-[var(--cjc-navy)] leading-none" x-text="summary.month_traffic.toLocaleString()">0</div>
        </div>

        <!-- Card 4: Top Dept -->
        <div class="bg-white rounded-[16px] p-5 border border-gray-100 shadow-sm flex flex-col relative overflow-hidden transition-all hover:shadow-md">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-[12px] bg-emerald-50 text-emerald-500 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div class="w-6 h-6 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 cursor-pointer hover:bg-gray-100">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                </div>
            </div>
            <div class="text-[12px] font-bold text-gray-400 uppercase tracking-wider mb-2">Most Active Dept</div>
            <div class="text-[16px] font-bold text-[var(--cjc-navy)] leading-tight truncate" x-text="summary.active_dept" :title="summary.active_dept">-</div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        
        <!-- Main Line Chart -->
        <div class="lg:col-span-2 bg-white rounded-[20px] p-6 border border-gray-100 shadow-sm flex flex-col relative min-h-[380px]">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-[var(--cjc-navy)] tracking-tight">Traffic Volume</h3>
                    <p class="text-[13px] text-gray-500 font-medium mt-0.5" x-text="period === 'today' ? 'Hourly attendance for the current day' : (period === 'week' ? 'Daily attendance for the current week' : 'Historical traffic overview')"></p>
                </div>
                <template x-if="!loading && totalTraffic === 0">
                    <span class="text-[11px] font-semibold text-amber-600 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-md shadow-sm">No activity recorded</span>
                </template>
            </div>
            
            <div x-show="loading" class="absolute inset-0 bg-white/80 backdrop-blur-sm z-10 flex items-center justify-center rounded-[20px]">
                <div class="w-8 h-8 border-3 border-gray-200 border-t-[var(--cjc-red)] rounded-full animate-spin"></div>
            </div>
            
            <div class="flex-1 relative w-full h-full min-h-[280px]">
                <canvas id="trafficChart"></canvas>
            </div>
        </div>
        
        <!-- Department Breakdown (Doughnut) -->
        <div class="bg-white rounded-[20px] p-6 border border-gray-100 shadow-sm flex flex-col relative min-h-[380px]">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-[var(--cjc-navy)] tracking-tight">By Department</h3>
                    <p class="text-[13px] text-gray-500 font-medium mt-0.5">Distribution ratio</p>
                </div>
                <div class="w-6 h-6 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 cursor-pointer hover:bg-gray-100 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                </div>
            </div>
            
            <div x-show="loading" class="absolute inset-0 bg-white/80 backdrop-blur-sm z-10 flex items-center justify-center rounded-[20px]">
                <div class="w-8 h-8 border-3 border-gray-200 border-t-[var(--cjc-red)] rounded-full animate-spin"></div>
            </div>
            
            <div class="flex-1 relative w-full h-full flex flex-col items-center justify-center min-h-[220px]">
                <!-- Custom chart size wrapper -->
                <div class="relative w-[200px] h-[200px]">
                    <canvas id="deptChart"></canvas>
                    <!-- Center absolute total text -->
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none mb-10">
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Total</span>
                        <span class="text-2xl font-bold text-[var(--cjc-navy)] leading-none mt-1" x-text="totalTraffic.toLocaleString()"></span>
                    </div>
                </div>
            </div>
        </div>
        
    </div>

</div>
@endsection
