@extends('layouts.admin')

@section('title', ' | Analytics')
@section('header_title', 'Analytics & Reports')

@section('admin_content')
<div class="space-y-6" x-data="analyticsApp()">

    <!-- Dedicated Monthly Attendance Report Generator (Per Program / Per Month) -->
    <div class="rounded-2xl p-6 bg-white border border-gray-200 shadow-sm">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-red-50 text-[var(--cjc-red)] border border-red-200 mb-3 uppercase tracking-wider">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Official Librarian Report Generator
                </div>
                <h2 class="text-xl font-bold text-[var(--cjc-navy)]">Monthly Attendance Report per Program</h2>
                <p class="text-sm text-gray-500 mt-1">Generate and download consolidated attendance logs aggregated per academic program and month.</p>
            </div>
            
            <form action="{{ route('admin.analytics.export-monthly-report') }}" method="GET" class="w-full lg:w-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3 bg-gray-50 p-4 rounded-xl border border-gray-200">
                <!-- Month Picker -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-600 mb-1">Select Month</label>
                    <input type="month" name="month" value="{{ date('Y-m') }}" required class="w-full sm:w-auto px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-800 focus:outline-none focus:border-[var(--cjc-navy)] font-medium">
                </div>

                <!-- Program Dropdown -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-600 mb-1">Filter Program</label>
                    <select name="program_id" class="no-tomselect w-full sm:w-56 px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-800 focus:outline-none focus:border-[var(--cjc-navy)] font-medium">
                        <option value="">All Programs</option>
                        @foreach($programs as $prog)
                            <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Export Button -->
                <div class="sm:self-end">
                    <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[var(--cjc-red)] hover:bg-red-700 text-white text-sm font-bold rounded-lg shadow-sm transition-all whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Download Excel Report
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
            <h3 class="text-base font-bold text-gray-800 mb-4">Traffic Volume</h3>
            
            <div x-show="loading" class="absolute inset-0 bg-white/80 backdrop-blur-sm z-10 flex items-center justify-center">
                <div class="w-10 h-10 border-4 border-gray-200 border-t-[var(--cjc-red)] rounded-full animate-spin"></div>
            </div>
            
            <div class="flex-1 relative w-full h-full">
                <canvas id="trafficChart"></canvas>
            </div>
        </div>
        
        <!-- Department Breakdown (Doughnut) -->
        <div class="card p-6 bg-white flex flex-col relative min-h-[400px]">
            <h3 class="text-base font-bold text-gray-800 mb-4">By Department</h3>
            
            <div x-show="loading" class="absolute inset-0 bg-white/80 backdrop-blur-sm z-10 flex items-center justify-center">
                <div class="w-10 h-10 border-4 border-gray-200 border-t-[var(--cjc-red)] rounded-full animate-spin"></div>
            </div>
            
            <div class="flex-1 relative w-full h-full flex items-center justify-center">
                <canvas id="deptChart"></canvas>
            </div>
        </div>
        
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('analyticsApp', () => ({
        period: 'today',
        term_id: '',
        loading: true,
        trafficChartInstance: null,
        deptChartInstance: null,
        
        init() {
            // Wait a tick for canvas to render
            this.$nextTick(() => {
                this.initCharts();
                this.fetchData();
            });
        },
        
        initCharts() {
            Chart.defaults.font.family = 'Inter, sans-serif';
            Chart.defaults.color = '#64748b';
            
            const trafficCtx = document.getElementById('trafficChart').getContext('2d');
            this.trafficChartInstance = new Chart(trafficCtx, {
                type: 'line',
                data: { labels: [], datasets: [] },
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
            
            const deptCtx = document.getElementById('deptChart').getContext('2d');
            this.deptChartInstance = new Chart(deptCtx, {
                type: 'doughnut',
                data: { labels: [], datasets: [] },
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
        },
        
        async fetchData() {
            this.loading = true;
            try {
                let url = `/admin/analytics/data?period=${this.period}&_t=${Date.now()}`;
                if (this.term_id) {
                    url += `&term_id=${this.term_id}`;
                }
                const response = await fetch(url);
                const data = await response.json();
                
                // Update Traffic Chart
                this.trafficChartInstance.data = {
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
                };
                this.trafficChartInstance.update();
                
                // Update Dept Chart
                const colors = ['#c41e2a', '#d4a418', '#0f2744', '#3b82f6', '#10b981'];
                this.deptChartInstance.data = {
                    labels: data.departments.labels,
                    datasets: [{
                        data: data.departments.values,
                        backgroundColor: colors.slice(0, data.departments.labels.length),
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                };
                this.deptChartInstance.update();
                
            } catch (err) {
                console.error("Failed to load analytics data", err);
            } finally {
                this.loading = false;
            }
        }
    }));
});
</script>
@endpush
