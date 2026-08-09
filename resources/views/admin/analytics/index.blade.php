@extends('layouts.admin')

@section('title', ' | Analytics')
@section('header_title', 'Analytics & Reports')

@section('admin_content')
<div class="space-y-6" x-data="analyticsApp()">

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
