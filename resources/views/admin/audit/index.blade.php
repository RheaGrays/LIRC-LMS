@extends('layouts.admin')

@section('title', ' | Audit Log')
@section('header_title', 'System Audit Log')

@section('admin_content')
<div class="space-y-6">

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
        <form action="{{ route('admin.audit.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <input type="date" name="date" value="{{ request('date', date('Y-m-d')) }}" class="input w-full sm:w-40 bg-white">
            <select name="action" class="input bg-white w-full sm:w-40">
                <option value="">All Actions</option>
                <option value="check_in" {{ request('action') == 'check_in' ? 'selected' : '' }}>Check In</option>
                <option value="check_out" {{ request('action') == 'check_out' ? 'selected' : '' }}>Check Out</option>
            </select>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ID or Name..." class="input w-full sm:w-48">
            <button type="submit" class="btn-secondary">Filter</button>
            @if(request()->anyFilled(['date', 'action', 'search']) && request('date') != date('Y-m-d'))
                <a href="{{ route('admin.audit.index') }}" class="text-sm text-[var(--cjc-red)] hover:underline">Clear</a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="card p-0 overflow-hidden fade-in-up">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50/50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Time</th>
                        <th class="px-6 py-4 font-semibold">Student</th>
                        <th class="px-6 py-4 font-semibold">Program</th>
                        <th class="px-6 py-4 font-semibold text-center">Action</th>
                        <th class="px-6 py-4 font-semibold text-right">Logged By / System</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-3 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $log->logged_at->format('h:i:s A') }}</div>
                                <div class="text-xs text-gray-500">{{ $log->logged_at->format('M d, Y') }}</div>
                            </td>
                            <td class="px-6 py-3">
                                @if($log->student)
                                    <div class="font-semibold text-[var(--cjc-navy)]">{{ $log->student->name }}</div>
                                    <div class="text-xs font-mono text-gray-500">{{ $log->student_id }}</div>
                                @else
                                    <span class="text-red-500 font-medium">Unknown ({{ $log->student_id }})</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                @if($log->student)
                                    <span class="font-medium text-gray-700">{{ $log->student->dept }}</span>
                                    <div class="text-xs text-gray-500">{{ $log->student->year }}</div>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-center">
                                @if($log->action === 'check_in')
                                    <span class="badge-entered">Check In</span>
                                @else
                                    <span class="badge-exited">Check Out</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right text-gray-500 text-xs">
                                Kiosk Scanner (System)
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                                <p class="text-base font-medium">No audit logs found.</p>
                                <p class="text-sm mt-1">Adjust your filters to see more results.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
