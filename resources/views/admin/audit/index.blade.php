@extends('layouts.admin')

@section('title', ' | System Audit Log')

@section('admin_content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tight">System Audit Log</h1>
            <p class="text-[15px] font-medium text-gray-500 mt-1">Track and review all system activities and user actions.</p>
        </div>
    </div>

    <!-- Stats Card -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100/60 p-2 mt-2">
        <div class="grid grid-cols-1 md:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-gray-100">
            <!-- Stat 1 -->
            <div class="p-5 flex items-center gap-5">
                <div class="w-14 h-14 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center shrink-0 border border-red-100">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Total Logs</div>
                    <div class="text-2xl font-black text-red-600 leading-tight mt-1">{{ number_format($stats['total_logs']) }}</div>
                    <div class="text-[13px] font-medium text-gray-400 mt-1">All time activities</div>
                </div>
            </div>

            <!-- Stat 2 -->
            <div class="p-5 flex items-center gap-5">
                <div class="w-14 h-14 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center shrink-0 border border-green-100">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Today's Logs</div>
                    <div class="text-2xl font-black text-green-600 leading-tight mt-1">{{ number_format($stats['today_logs']) }}</div>
                    <div class="text-[13px] font-medium text-gray-400 mt-1">{{ \Carbon\Carbon::today()->format('M d, Y') }}</div>
                </div>
            </div>

            <!-- Stat 3 -->
            <div class="p-5 flex items-center gap-5">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center shrink-0 border border-amber-100">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">First Log</div>
                    <div class="text-xl font-black text-slate-700 leading-tight mt-1">
                        {{ $stats['first_log'] ? $stats['first_log']->logged_at->format('M d, Y') : 'N/A' }}
                    </div>
                    <div class="text-[13px] font-medium text-gray-400 mt-1">
                        {{ $stats['first_log'] ? $stats['first_log']->logged_at->format('h:i:s A') : '--' }}
                    </div>
                </div>
            </div>

            <!-- Stat 4 -->
            <div class="p-5 flex items-center gap-5">
                <div class="w-14 h-14 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 border border-purple-100">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">System</div>
                    <div class="text-xl font-black text-slate-700 leading-tight mt-1">Kiosk Scanner</div>
                    <div class="text-[13px] font-medium text-gray-400 mt-1">Primary System</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100/60 overflow-hidden">
        
        <!-- Filter Toolbar -->
        <div class="p-6 border-b border-gray-100/60 bg-white">
            <form action="{{ route('admin.audit.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
                <!-- Date -->
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Date</label>
                    <input type="date" name="date" value="{{ request('date') }}" class="w-full h-11 px-3.5 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-gray-700 font-medium shadow-xs">
                </div>
                
                <!-- Action (Custom Modern Dropdown) -->
                <div class="flex-1 min-w-[200px] relative" x-data="{ 
                    open: false, 
                    value: '{{ request('action', '') }}',
                    label: '{{ request('action') == 'check_in' ? 'Check In' : (request('action') == 'check_out' ? 'Check Out' : 'All Actions') }}',
                    select(val, text) {
                        this.value = val;
                        this.label = text;
                        this.open = false;
                    }
                }">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Action</label>
                    <input type="hidden" name="action" :value="value">
                    
                    <!-- Single Box Trigger Button -->
                    <button type="button" @click="open = !open" @click.outside="open = false" 
                            class="w-full h-11 px-3.5 bg-white border border-gray-200 rounded-xl flex items-center justify-between text-sm font-medium text-gray-700 shadow-xs hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 transition-all cursor-pointer">
                        <div class="flex items-center gap-2">
                            <template x-if="value === 'check_in'">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            </template>
                            <template x-if="value === 'check_out'">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            </template>
                            <template x-if="!value">
                                <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                            </template>
                            <span x-text="label"></span>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180 text-red-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Floating Custom Option Panel -->
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                         style="display: none;"
                         class="absolute left-0 right-0 top-[calc(100%+6px)] bg-white border border-gray-100 rounded-2xl shadow-xl z-50 p-1.5 space-y-1">
                        
                        <button type="button" @click="select('', 'All Actions')" 
                                :class="value === '' ? 'bg-red-50 text-red-700 font-bold' : 'text-gray-700 hover:bg-gray-50 font-medium'"
                                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm transition-colors text-left cursor-pointer">
                            <span class="flex items-center gap-2.5">
                                <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                All Actions
                            </span>
                            <template x-if="value === ''">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </template>
                        </button>

                        <button type="button" @click="select('check_in', 'Check In')" 
                                :class="value === 'check_in' ? 'bg-red-50 text-red-700 font-bold' : 'text-gray-700 hover:bg-gray-50 font-medium'"
                                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm transition-colors text-left cursor-pointer">
                            <span class="flex items-center gap-2.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Check In
                            </span>
                            <template x-if="value === 'check_in'">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </template>
                        </button>

                        <button type="button" @click="select('check_out', 'Check Out')" 
                                :class="value === 'check_out' ? 'bg-red-50 text-red-700 font-bold' : 'text-gray-700 hover:bg-gray-50 font-medium'"
                                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm transition-colors text-left cursor-pointer">
                            <span class="flex items-center gap-2.5">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                Check Out
                            </span>
                            <template x-if="value === 'check_out'">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </template>
                        </button>
                    </div>
                </div>

                <!-- Search -->
                <div class="flex-[2] min-w-[280px]">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ID or Name..." class="w-full h-11 px-3.5 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-gray-700 font-medium shadow-xs">
                </div>

                <!-- Buttons -->
                <div class="flex items-center gap-3">
                    <button type="submit" class="h-11 inline-flex items-center gap-2 px-6 bg-red-600 text-white rounded-xl text-sm font-bold hover:bg-red-700 transition-colors shadow-sm cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        Filter
                    </button>
                    <a href="{{ route('admin.audit.index') }}" class="h-11 inline-flex items-center gap-2 px-6 bg-white border border-gray-200 text-gray-600 rounded-xl text-sm font-bold hover:bg-gray-50 transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Reset
                    </a>
                </div>
            </form>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-white border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-wider w-[20%]">Time</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-wider w-[25%]">Student</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-wider w-[25%]">Program</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-wider w-[10%]">Action</th>
                        <th class="px-6 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-wider w-[20%]">Logged By / System</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 bg-white">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50/80 transition-colors group">
                            <!-- TIME -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-red-50 text-red-500 flex items-center justify-center shrink-0 border border-red-100 shadow-sm group-hover:scale-110 transition-transform">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <div>
                                        <div class="font-black text-slate-800 text-sm tracking-tight">{{ $log->logged_at->format('h:i:s A') }}</div>
                                        <div class="text-[13px] font-medium text-gray-500 mt-0.5">{{ $log->logged_at->format('M d, Y') }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- STUDENT -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 overflow-hidden shrink-0 border border-gray-200 shadow-sm">
                                        @if($log->student && $log->student->photo_url)
                                            <img src="{{ $log->student->photo_url }}" class="w-full h-full object-cover">
                                        @else
                                            <svg class="w-full h-full text-gray-400 p-2" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                        @endif
                                    </div>
                                    <div>
                                        @if($log->student)
                                            <div class="font-bold text-slate-800 text-sm tracking-tight uppercase">{{ $log->student->full_name }}</div>
                                            <div class="text-[13px] font-medium text-gray-500 mt-0.5 font-mono">{{ $log->student_id }}</div>
                                        @else
                                            <div class="font-bold text-red-500 text-sm tracking-tight uppercase">Unknown Student</div>
                                            <div class="text-[13px] font-medium text-gray-500 mt-0.5 font-mono">{{ $log->student_id }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- PROGRAM -->
                            <td class="px-6 py-4">
                                @if($log->student)
                                    <div class="font-bold text-slate-800 text-sm tracking-tight whitespace-normal max-w-[200px] leading-snug">{{ $log->student->academicDepartment?->name ?? '—' }}</div>
                                    <div class="text-[13px] font-medium text-gray-500 mt-1">{{ $log->student->year_level }}</div>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>

                            <!-- ACTION -->
                            <td class="px-6 py-4">
                                @if($log->action === 'check_in')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-green-50 border border-green-200 text-green-700 font-bold text-xs tracking-wide">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        Check In
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-50 border border-gray-200 text-gray-700 font-bold text-xs tracking-wide">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        Check Out
                                    </span>
                                @endif
                            </td>

                            <!-- LOGGED BY / SYSTEM -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-gray-500">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        <span class="text-[13px] font-medium tracking-tight">Kiosk Scanner (System)</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-100 shadow-sm">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <h3 class="text-lg font-black text-slate-800 tracking-tight">No audit logs found</h3>
                                <p class="text-sm font-medium text-gray-500 mt-1">Try adjusting your date or search filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Table Footer -->
        <div class="px-6 py-4 border-t border-gray-100 bg-white flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-sm font-medium text-gray-500">
                Showing <span class="font-bold text-gray-900">{{ $logs->firstItem() ?? 0 }}</span> to <span class="font-bold text-gray-900">{{ $logs->lastItem() ?? 0 }}</span> of <span class="font-bold text-gray-900">{{ number_format($logs->total()) }}</span> logs
            </div>
            
            @if($logs->hasPages())
                <div class="flex items-center gap-1.5">
                    {{-- Previous Page Link --}}
                    @if ($logs->onFirstPage())
                        <span class="w-9 h-9 rounded-lg border border-gray-200 flex items-center justify-center text-gray-300 bg-gray-50 cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </span>
                    @else
                        <a href="{{ $logs->previousPageUrl() }}" class="w-9 h-9 rounded-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:text-gray-700 bg-white shadow-sm transition-colors hover:border-gray-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($logs->links()->elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span class="w-9 h-9 flex items-center justify-center text-gray-400 font-bold tracking-widest">{{ $element }}</span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $logs->currentPage())
                                    <span class="w-9 h-9 rounded-lg border border-red-600 flex items-center justify-center text-white bg-red-600 shadow-sm font-bold text-sm">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="w-9 h-9 rounded-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:text-gray-700 bg-white shadow-sm transition-colors font-bold text-sm hover:border-gray-300">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($logs->hasMorePages())
                        <a href="{{ $logs->nextPageUrl() }}" class="w-9 h-9 rounded-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:text-gray-700 bg-white shadow-sm transition-colors hover:border-gray-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @else
                        <span class="w-9 h-9 rounded-lg border border-gray-200 flex items-center justify-center text-gray-300 bg-gray-50 cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
