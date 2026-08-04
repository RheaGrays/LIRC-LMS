@extends('layouts.admin')

@section('title', ' | Violations')
@section('header_title', 'Violations: ' . $student->name)

@section('admin_content')
<div class="space-y-6" x-data="{ 
    openAdd: false,
    openEdit: false,
    violation: { id: '', violation_type: 'No ID', severity: 'Minor', remarks: '' }
}">

    <!-- Header & Student Info -->
    <div class="card bg-white border-t-4 border-t-[var(--cjc-red)] flex flex-col md:flex-row md:items-center justify-between gap-6 p-6">
        <div class="flex items-center gap-5">
            <div class="w-20 h-20 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0 border-2 border-white shadow-md">
                @if($student->photo_url)
                    <img src="{{ $student->photo_url }}" class="w-full h-full object-cover">
                @else
                    <svg class="w-full h-full text-gray-300 p-3" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                @endif
            </div>
            <div>
                <h2 class="text-2xl font-bold text-[var(--cjc-navy)] leading-tight">{{ $student->name }}</h2>
                <div class="text-gray-500 font-mono text-sm mt-1 mb-2">{{ $student->id }}</div>
                <div class="flex items-center gap-2 text-xs font-semibold">
                    <span class="px-2.5 py-1 bg-gray-100 rounded-md text-gray-700 border border-gray-200">{{ $student->dept }}</span>
                    <span class="px-2.5 py-1 bg-gray-100 rounded-md text-gray-700 border border-gray-200">{{ $student->year }}</span>
                </div>
            </div>
        </div>
        
        <div class="flex gap-3">
            <a href="{{ route('admin.students.index') }}" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back
            </a>
            <button @click="openAdd = true" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Record Violation
            </button>
        </div>
    </div>

    <!-- Violations Table -->
    <div class="card p-0 overflow-hidden fade-in-up-2">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-white">
            <h3 class="text-base font-bold text-[var(--cjc-navy)]">Violation History</h3>
            <span class="text-sm font-semibold text-gray-500">Total: {{ $student->violations->count() }}</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50/50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Date & Time</th>
                        <th class="px-6 py-3 font-semibold">Violation Type</th>
                        <th class="px-6 py-3 font-semibold">Severity</th>
                        <th class="px-6 py-3 font-semibold">Recorded By</th>
                        <th class="px-6 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($student->violations as $v)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $v->created_at->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $v->created_at->format('h:i A') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-[var(--cjc-navy)]">{{ $v->violation_type }}</div>
                                @if($v->remarks)
                                    <div class="text-xs text-gray-500 mt-1 max-w-xs truncate" title="{{ $v->remarks }}">{{ $v->remarks }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if(strtolower($v->severity) === 'minor')
                                    <span class="badge-minor">Minor</span>
                                @elseif(strtolower($v->severity) === 'moderate')
                                    <span class="badge-moderate">Moderate</span>
                                @else
                                    <span class="badge-severe">Severe</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500 text-xs">
                                {{ $v->admin->full_name ?? 'System' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="violation = {{ $v->toJson() }}; openEdit = true" class="p-2 text-blue-600 hover:bg-blue-50 rounded-md transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    </button>
                                    <form action="{{ route('admin.violations.destroy', $v->id) }}" method="POST" onsubmit="return confirm('Delete this violation permanently?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-md transition-colors" title="Delete">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-green-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <p class="text-base font-medium">Clean Record!</p>
                                <p class="text-sm mt-1">This student has no recorded violations.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modal -->
    <div x-show="openAdd" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="openAdd" class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="openAdd = false"></div>
        <div x-show="openAdd" class="relative bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
            <form action="{{ route('admin.violations.store', $student->id) }}" method="POST">
                @csrf
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-[var(--cjc-navy)]">Record Violation</h3>
                    <button type="button" @click="openAdd = false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Violation Type <span class="text-red-500">*</span></label>
                        <select name="violation_type" required class="input bg-white">
                            <option value="No ID">No ID / Forgetting ID</option>
                            <option value="Fake ID">Using someone else's ID</option>
                            <option value="Improper Uniform">Improper Uniform</option>
                            <option value="Disruptive Behavior">Disruptive Behavior</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Severity <span class="text-red-500">*</span></label>
                        <select name="severity" required class="input bg-white">
                            <option value="Minor">Minor (Warning)</option>
                            <option value="Moderate">Moderate</option>
                            <option value="Severe">Severe</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Remarks (Optional)</label>
                        <textarea name="remarks" rows="3" class="input resize-none" placeholder="Provide additional details..."></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                    <button type="button" @click="openAdd = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Save Violation</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Modal -->
    <div x-show="openEdit" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="openEdit" class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="openEdit = false"></div>
        <div x-show="openEdit" class="relative bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
            <form :action="`/admin/violations/${violation.id}`" method="POST">
                @csrf
                @method('PUT')
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-[var(--cjc-navy)]">Edit Violation</h3>
                    <button type="button" @click="openEdit = false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Violation Type <span class="text-red-500">*</span></label>
                        <select name="violation_type" x-model="violation.violation_type" required class="input bg-white">
                            <option value="No ID">No ID / Forgetting ID</option>
                            <option value="Fake ID">Using someone else's ID</option>
                            <option value="Improper Uniform">Improper Uniform</option>
                            <option value="Disruptive Behavior">Disruptive Behavior</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Severity <span class="text-red-500">*</span></label>
                        <select name="severity" x-model="violation.severity" required class="input bg-white">
                            <option value="Minor">Minor (Warning)</option>
                            <option value="Moderate">Moderate</option>
                            <option value="Severe">Severe</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Remarks (Optional)</label>
                        <textarea name="remarks" x-model="violation.remarks" rows="3" class="input resize-none" placeholder="Provide additional details..."></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                    <button type="button" @click="openEdit = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Update Violation</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
