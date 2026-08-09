@extends('layouts.admin')

@section('title', ' | Dashboard')
@section('header_title', 'Dashboard')

@section('admin_content')
<div x-data="adminDashboard()" x-init="initDashboard()" class="space-y-6">

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Card 1: Today's Entries -->
        <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 p-6 relative overflow-hidden flex flex-col justify-between min-h-[160px]">
            <div class="flex items-start gap-4 relative z-10">
                <div class="w-14 h-14 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                </div>
                <div class="flex-1 mt-1">
                    <h3 class="text-xs font-bold text-slate-600 uppercase tracking-wide">Today's Entries</h3>
                    <div class="text-[32px] leading-none font-black text-red-600 mt-2" x-text="todayEntries">{{ $todayEntries }}</div>
                    <div class="text-[13px] text-gray-500 mt-2 font-medium">Total check-ins today</div>
                </div>
            </div>
            <!-- Red Wave SVG -->
            <div class="absolute bottom-0 left-0 w-full pointer-events-none">
                <svg viewBox="0 0 400 100" class="w-full h-auto text-red-50" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0,80 Q100,100 200,60 T400,80 L400,100 L0,100 Z" opacity="0.8"/>
                    <path d="M0,90 Q120,50 250,70 T400,90 L400,100 L0,100 Z" opacity="0.4"/>
                </svg>
            </div>
        </div>

        <!-- Card 2: Currently Inside -->
        <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 p-6 relative overflow-hidden flex flex-col justify-between min-h-[160px]">
            <div class="flex items-start gap-4 relative z-10">
                <div class="w-14 h-14 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </div>
                <div class="flex-1 mt-1">
                    <h3 class="text-xs font-bold text-slate-600 uppercase tracking-wide">Currently Inside</h3>
                    <div class="text-[32px] leading-none font-black text-green-600 mt-2" x-text="inside">{{ $inside }}</div>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="relative z-10 mt-6">
                <div class="text-[11px] font-semibold text-gray-500 mb-2">Max Capacity: <span x-text="maxOccupancy">{{ $maxOccupancy }}</span></div>
                <div class="flex items-center gap-3">
                    <div class="h-1.5 flex-1 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-green-500 rounded-full transition-all duration-500" :style="`width: ${maxOccupancy > 0 ? Math.min(100, Math.round((inside / maxOccupancy) * 100)) : 0}%`"></div>
                    </div>
                    <span class="text-xs font-bold text-green-600" x-text="`${maxOccupancy > 0 ? Math.min(100, Math.round((inside / maxOccupancy) * 100)) : 0}%`"></span>
                </div>
            </div>
        </div>

        <!-- Card 3: Active Students -->
        <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 p-6 relative overflow-hidden flex flex-col justify-between min-h-[160px]">
            <div class="flex items-start gap-4 relative z-10">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </div>
                <div class="flex-1 mt-1">
                    <h3 class="text-xs font-bold text-slate-600 uppercase tracking-wide">Active Students</h3>
                    <div class="text-[32px] leading-none font-black text-amber-500 mt-2" x-text="totalStudents">{{ $totalStudents }}</div>
                    <div class="text-[13px] text-gray-500 mt-2 font-medium">Currently enrolled</div>
                </div>
            </div>
            <!-- Amber Wave SVG -->
            <div class="absolute bottom-0 left-0 w-full pointer-events-none">
                <svg viewBox="0 0 400 100" class="w-full h-auto text-amber-50" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0,80 Q100,100 200,60 T400,80 L400,100 L0,100 Z" opacity="0.8"/>
                    <path d="M0,90 Q120,50 250,70 T400,90 L400,100 L0,100 Z" opacity="0.4"/>
                </svg>
            </div>
        </div>

        <!-- Card 4: Pending Violations -->
        <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 p-6 relative overflow-hidden flex flex-col justify-between min-h-[160px]">
            <div class="flex items-start gap-4 relative z-10">
                <div class="w-14 h-14 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div class="flex-1 mt-1">
                    <h3 class="text-xs font-bold text-slate-600 uppercase tracking-wide">Pending Violations</h3>
                    <div class="text-[32px] leading-none font-black text-slate-700 mt-2">—</div>
                    <div class="text-[13px] text-gray-500 mt-2 font-medium">No pending violations</div>
                </div>
            </div>
            <!-- Orange Wave SVG -->
            <div class="absolute bottom-0 left-0 w-full pointer-events-none">
                <svg viewBox="0 0 400 100" class="w-full h-auto text-orange-50" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0,80 Q100,100 200,60 T400,80 L400,100 L0,100 Z" opacity="0.8"/>
                    <path d="M0,90 Q120,50 250,70 T400,90 L400,100 L0,100 Z" opacity="0.4"/>
                </svg>
            </div>
        </div>
        
    </div>

    <!-- Recent Activity -->
    <div class="card p-0 overflow-hidden fade-in-up">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-white">
            <div class="flex items-center gap-2">
                <h3 class="text-base font-bold text-[var(--cjc-navy)]">Recent Activity</h3>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700 animate-pulse">● LIVE</span>
            </div>
            <a href="{{ route('admin.audit.index') }}" class="text-sm font-medium text-[var(--cjc-red)] hover:underline">View All</a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50/50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Student</th>
                        <th class="px-6 py-3 font-semibold">Program/Year</th>
                        <th class="px-6 py-3 font-semibold text-center">Status</th>
                        <th class="px-6 py-3 font-semibold text-right">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    <template x-for="log in recentLogs" :key="log.id">
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gray-100 overflow-hidden flex-shrink-0 relative">
                                        <template x-if="log.photo_url">
                                            <img :src="log.photo_url" 
                                                 x-on:error="$el.style.display='none'; $el.nextElementSibling.style.display='flex';" 
                                                 class="w-full h-full object-cover">
                                        </template>
                                        <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-400" :class="{'hidden': log.photo_url}">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900" x-text="log.name"></div>
                                        <div class="text-xs text-gray-500 font-medium" x-text="log.student_id"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <div class="text-gray-900 font-medium" x-text="log.dept"></div>
                                <div class="text-xs text-gray-500" x-text="log.year"></div>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <template x-if="log.status === 'entered'">
                                    <span class="badge-entered">Entered</span>
                                </template>
                                <template x-if="log.status !== 'entered'">
                                    <span class="badge-exited">Exited</span>
                                </template>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <span class="text-gray-600 font-medium" x-text="log.time_label"></span>
                            </td>
                        </tr>
                    </template>
                    <template x-if="!recentLogs || recentLogs.length === 0">
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                <p>No activity today.</p>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function adminDashboard() {
        return {
            todayEntries: {{ $todayEntries }},
            totalStudents: {{ $totalStudents }},
            inside: {{ $inside }},
            maxOccupancy: {{ $maxOccupancy }},
            recentLogs: @json($recentLogs),

            initDashboard() {
                setInterval(() => this.fetchStats(), 2500);
            },

            async fetchStats() {
                try {
                    const res = await fetch('{{ route('admin.dashboard.stats') }}');
                    if (res.ok) {
                        const data = await res.json();
                        this.todayEntries = data.todayEntries;
                        this.totalStudents = data.totalStudents;
                        this.inside = data.inside;
                        this.maxOccupancy = data.maxOccupancy;
                        this.recentLogs = data.recentLogs;
                    }
                } catch (e) {}
            }
        };
    }
</script>
@endsection
