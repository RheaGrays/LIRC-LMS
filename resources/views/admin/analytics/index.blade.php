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
                
                // Render or Update Traffic Volume (Line Chart)
                const trafficCanvas = document.getElementById('trafficChart');
                if (trafficCanvas) {
                    if (!this.trafficChartInstance) {
                        const trafficCtx = trafficCanvas.getContext('2d');
                        this.trafficChartInstance = new Chart(trafficCtx, {
                            type: 'line',
                            data: {
                                labels: data.traffic.labels,
                                datasets: [{
                                    label: 'Entries',
                                    data: data.traffic.values,
                                    borderColor: '#c41e2a',
                                    backgroundColor: 'rgba(196, 30, 42, 0.1)',
                                    borderWidth: 2,
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
                    const colors = ['#c41e2a', '#d4a418', '#0f2744', '#3b82f6', '#10b981'];
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
                                    legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } },
                                    tooltip: { backgroundColor: '#0f2744', padding: 12, cornerRadius: 8 }
                                },
                                cutout: '70%'
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
<div class="space-y-6" x-data="analyticsApp()">

    <!-- Dedicated Monthly Attendance Report Generator (Per Program / Per Month) -->
    <div class="rounded-2xl p-6 bg-white border border-gray-200 shadow-sm">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-red-50 text-[var(--cjc-red)] border border-red-200 mb-3 uppercase tracking-wider">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Official Librarian Report Generator
                </div>
                <h2 class="text-xl font-bold text-[var(--cjc-navy)]">Monthly Attendance Report per Program</h2>
                <p class="text-sm text-gray-500 mt-1">Generate and download consolidated attendance logs aggregated per academic program and month.</p>
            </div>
            
            <form action="{{ route('admin.analytics.export-monthly-report') }}" method="GET" @submit="if(window.showToast) window.showToast('Generating and downloading report...', 'info')" class="w-full flex flex-col xl:flex-row items-stretch xl:items-end gap-3 bg-gray-50 p-4 rounded-xl border border-gray-200">
                <!-- 1. School Year Filter -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-600 mb-1">School Year</label>
                    <select name="term_id" class="no-tomselect w-full xl:w-48 px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-800 focus:outline-none focus:border-[var(--cjc-navy)] font-semibold h-[42px]">
                        <option value="">AY {{ date('Y') }}-{{ date('Y') + 1 }} (Current)</option>
                        @foreach($terms as $term)
                            <option value="{{ $term->id }}">{{ $term->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 2. Month Filter -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-600 mb-1">Month</label>
                    <input type="month" name="month" value="{{ date('Y-m') }}" class="w-full xl:w-auto px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-800 focus:outline-none focus:border-[var(--cjc-navy)] font-semibold h-[42px]">
                </div>

                <!-- 3. Program Filter -->
                <div class="relative" x-data="{ 
                    openProgDropdown: false, 
                    selectedProgId: '', 
                    selectedProgName: 'All Programs' 
                }">
                    <input type="hidden" name="program_id" :value="selectedProgId">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-600 mb-1">Program</label>
                    
                    <button type="button" @click="openProgDropdown = !openProgDropdown" @click.outside="openProgDropdown = false" class="w-full xl:w-56 px-3.5 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-800 focus:outline-none focus:border-[var(--cjc-navy)] font-semibold h-[42px] flex items-center justify-between gap-2 shadow-sm whitespace-nowrap">
                        <span class="truncate" x-text="selectedProgName">All Programs</span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform shrink-0" :class="{'rotate-180': openProgDropdown}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <!-- Dropdown Options Menu -->
                    <div x-show="openProgDropdown" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" class="absolute left-0 mt-1 w-64 max-h-60 overflow-y-auto bg-white rounded-xl shadow-xl border border-gray-200 py-1.5 z-50">
                        <button type="button" @click="selectedProgId = ''; selectedProgName = 'All Programs'; openProgDropdown = false" class="w-full px-3.5 py-2 text-left text-sm font-semibold flex items-center gap-2 hover:bg-gray-100 text-gray-800 transition-colors" :class="{'bg-red-50 text-[var(--cjc-red)] font-bold': selectedProgId === ''}">
                            <span>🎓</span>
                            <span>All Programs</span>
                        </button>
                        @foreach($programs as $prog)
                            <button type="button" @click="selectedProgId = '{{ $prog->id }}'; selectedProgName = '{{ addslashes($prog->name) }}'; openProgDropdown = false" class="w-full px-3.5 py-2 text-left text-sm font-medium hover:bg-gray-100 text-gray-700 transition-colors truncate" :class="{'bg-red-50 text-[var(--cjc-red)] font-bold': selectedProgId === '{{ $prog->id }}'}">
                                {{ $prog->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- 4. Format / Type Selector -->
                <div class="relative" x-data="{ openFormatDropdown: false, selectedFormat: 'excel' }">
                    <input type="hidden" name="format" :value="selectedFormat">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-600 mb-1">Type / Format</label>
                    
                    <button type="button" @click="openFormatDropdown = !openFormatDropdown" @click.outside="openFormatDropdown = false" class="w-full xl:w-auto min-w-[175px] px-3.5 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-800 focus:outline-none focus:border-[var(--cjc-navy)] font-bold h-[42px] flex items-center justify-between gap-2 shadow-sm whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <template x-if="selectedFormat === 'excel'">
                                <span class="flex items-center gap-1.5 text-emerald-700 font-bold whitespace-nowrap">
                                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zM15.8 17.5L14 15l-1.8 2.5h-1.7l2.6-3.5-2.5-3.5h1.7l1.7 2.4 1.7-2.4h1.7l-2.5 3.5 2.6 3.5h-1.7zM13 9V3.5L18.5 9H13z"/></svg>
                                    Excel (.xlsx)
                                </span>
                            </template>
                            <template x-if="selectedFormat === 'word'">
                                <span class="flex items-center gap-1.5 text-blue-700 font-bold whitespace-nowrap">
                                    <svg class="w-4 h-4 text-blue-600 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm1.8 15.5h-1.6l-1.2-4.5-1.2 4.5H10.2L8.5 10h1.5l1.1 4.5 1.2-4.5h1.4l1.2 4.5 1.1-4.5h1.5l-1.7 7.5zM13 9V3.5L18.5 9H13z"/></svg>
                                    Word (.doc)
                                </span>
                            </template>
                            <template x-if="selectedFormat === 'pdf'">
                                <span class="flex items-center gap-1.5 text-red-700 font-bold whitespace-nowrap">
                                    <svg class="w-4 h-4 text-red-600 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-9.5 8.5c0 .8-.7 1.5-1.5 1.5H7v2H5.5V9H8c.8 0 1.5.7 1.5 1.5v1zm5 2c0 .8-.7 1.5-1.5 1.5h-2.5V9h2.5c.8 0 1.5.7 1.5 1.5v3zm3.5-3.5h-3v5H18v-1.5h-1.5v-1H18V10z"/></svg>
                                    PDF / Print
                                </span>
                            </template>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 transition-transform shrink-0 ml-1" :class="{'rotate-180': openFormatDropdown}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <!-- Dropdown Options Menu -->
                    <div x-show="openFormatDropdown" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" class="absolute left-0 mt-1 w-52 bg-white rounded-xl shadow-xl border border-gray-200 py-1.5 z-50">
                        <button type="button" @click="selectedFormat = 'excel'; openFormatDropdown = false" class="w-full px-3.5 py-2 text-left text-sm font-semibold flex items-center gap-2.5 hover:bg-emerald-50 hover:text-emerald-700 transition-colors" :class="{'bg-emerald-50 text-emerald-700': selectedFormat === 'excel'}">
                            <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zM15.8 17.5L14 15l-1.8 2.5h-1.7l2.6-3.5-2.5-3.5h1.7l1.7 2.4 1.7-2.4h1.7l-2.5 3.5 2.6 3.5h-1.7zM13 9V3.5L18.5 9H13z"/></svg>
                            </div>
                            <div>
                                <div class="font-bold">Excel Spreadsheet</div>
                                <div class="text-[11px] text-gray-500 font-normal">Microsoft Excel (.xlsx)</div>
                            </div>
                        </button>

                        <button type="button" @click="selectedFormat = 'word'; openFormatDropdown = false" class="w-full px-3.5 py-2 text-left text-sm font-semibold flex items-center gap-2.5 hover:bg-blue-50 hover:text-blue-700 transition-colors" :class="{'bg-blue-50 text-blue-700': selectedFormat === 'word'}">
                            <div class="w-7 h-7 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm1.8 15.5h-1.6l-1.2-4.5-1.2 4.5H10.2L8.5 10h1.5l1.1 4.5 1.2-4.5h1.4l1.2 4.5 1.1-4.5h1.5l-1.7 7.5zM13 9V3.5L18.5 9H13z"/></svg>
                            </div>
                            <div>
                                <div class="font-bold">Word Document</div>
                                <div class="text-[11px] text-gray-500 font-normal">Microsoft Word (.doc)</div>
                            </div>
                        </button>

                        <button type="button" @click="selectedFormat = 'pdf'; openFormatDropdown = false" class="w-full px-3.5 py-2 text-left text-sm font-semibold flex items-center gap-2.5 hover:bg-red-50 hover:text-red-700 transition-colors" :class="{'bg-red-50 text-red-700': selectedFormat === 'pdf'}">
                            <div class="w-7 h-7 rounded-lg bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-9.5 8.5c0 .8-.7 1.5-1.5 1.5H7v2H5.5V9H8c.8 0 1.5.7 1.5 1.5v1zm5 2c0 .8-.7 1.5-1.5 1.5h-2.5V9h2.5c.8 0 1.5.7 1.5 1.5v3zm3.5-3.5h-3v5H18v-1.5h-1.5v-1H18V10z"/></svg>
                            </div>
                            <div>
                                <div class="font-bold">PDF Document</div>
                                <div class="text-[11px] text-gray-500 font-normal">Printable PDF (.pdf)</div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Export Button -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-transparent select-none mb-1 hidden xl:block">&nbsp;</label>
                    <button type="submit" class="w-full xl:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[var(--cjc-red)] hover:bg-red-700 text-white text-sm font-bold rounded-lg shadow-sm transition-all whitespace-nowrap h-[42px]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Header & Controls -->
    <div class="card p-5 bg-white flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-[var(--cjc-navy)]">Attendance Overview</h2>
            <p class="text-sm text-gray-500">Visualize library foot traffic and trends</p>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <select x-model="term_id" @change="fetchData()" class="no-tomselect input bg-white font-medium text-sm w-full md:w-48">
                <option value="">By Term (All Time)</option>
                @foreach($terms as $term)
                    <option value="{{ $term->id }}">{{ $term->name }}</option>
                @endforeach
            </select>
            <select x-model="period" @change="fetchData()" :disabled="term_id !== ''" class="no-tomselect input bg-white font-medium text-sm w-full md:w-48" :class="{'opacity-50': term_id !== ''}">
                <option value="today">Today (Hourly)</option>
                <option value="week">This Week (Daily)</option>
                <option value="month">This Month (Daily)</option>
                <option value="year">This Year (Monthly)</option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Main Line Chart -->
        <div class="lg:col-span-2 card p-6 bg-white flex flex-col relative min-h-[400px]">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-gray-800">Traffic Volume</h3>
                <template x-if="!loading && totalTraffic === 0">
                    <span class="text-xs font-semibold text-amber-600 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full">No activity recorded for this period</span>
                </template>
            </div>
            
            <div x-show="loading" class="absolute inset-0 bg-white/80 backdrop-blur-sm z-10 flex items-center justify-center">
                <div class="w-10 h-10 border-4 border-gray-200 border-t-[var(--cjc-red)] rounded-full animate-spin"></div>
            </div>
            
            <div class="flex-1 relative w-full h-full min-h-[300px]">
                <canvas id="trafficChart"></canvas>
            </div>
        </div>
        
        <!-- Department Breakdown (Doughnut) -->
        <div class="card p-6 bg-white flex flex-col relative min-h-[400px]">
            <h3 class="text-base font-bold text-gray-800 mb-4">By Department</h3>
            
            <div x-show="loading" class="absolute inset-0 bg-white/80 backdrop-blur-sm z-10 flex items-center justify-center">
                <div class="w-10 h-10 border-4 border-gray-200 border-t-[var(--cjc-red)] rounded-full animate-spin"></div>
            </div>
            
            <div class="flex-1 relative w-full h-full flex items-center justify-center min-h-[300px]">
                <canvas id="deptChart"></canvas>
            </div>
        </div>
        
    </div>

</div>
@endsection
