@extends('layouts.admin')

@section('title', ' | Approvals')
@section('header_title', 'Admin Approvals')

@section('admin_content')
<div class="space-y-6">
    <div class="card p-0 overflow-hidden fade-in-up">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-white">
            <div>
                <h3 class="text-base font-bold text-[var(--cjc-navy)]">Pending Access Requests</h3>
                <p class="text-sm text-gray-500">Approve or reject staff registrations for the LEMS admin panel.</p>
            </div>
            <span class="inline-flex items-center justify-center min-w-[2rem] h-8 px-2 rounded-full font-bold text-sm bg-orange-100 text-orange-700 border border-orange-200">
                {{ $pending->count() }}
            </span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50/50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Date Requested</th>
                        <th class="px-6 py-4 font-semibold">User Details</th>
                        <th class="px-6 py-4 font-semibold">Requested Role</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($pending as $req)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $req->created_at->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $req->created_at->format('h:i A') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-[var(--cjc-navy)] text-base">{{ $req->full_name }}</div>
                                <div class="text-gray-500">{{ $req->email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded-md text-xs font-bold">{{ $req->role }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <form action="{{ route('admin.approvals.approve', $req->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn-primary !bg-green-600 hover:!bg-green-700 !px-4 !py-2">
                                            Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.approvals.reject', $req->id) }}" method="POST" class="inline" onsubmit="return confirm('Reject and delete this request?');">
                                        @csrf
                                        <button type="submit" class="btn-secondary !text-red-600 !border-red-200 hover:!bg-red-50 !px-4 !py-2">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-green-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <p class="text-base font-medium">All caught up!</p>
                                <p class="text-sm mt-1">There are no pending admin requests.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
