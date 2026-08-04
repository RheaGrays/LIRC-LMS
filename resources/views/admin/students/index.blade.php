@extends('layouts.admin')

@section('title', ' | Students & Violations')
@section('header_title', 'Students & Violations')

@section('admin_content')
<div class="space-y-6" x-data="{
    showAddStudentModal: false,
    showViolationModal: false,
    selectedStudent: null,
    
    openViolationModal(student) {
        this.selectedStudent = student;
        this.showViolationModal = true;
    }
}">

    <div class="card bg-white border-t-4 border-t-[var(--cjc-red)]">
        <div class="p-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-xl font-bold text-[var(--cjc-navy)]">Student Directory</h3>
                    <p class="text-sm text-gray-500">Manage students and record their violations.</p>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <form method="GET" action="{{ route('admin.students.index') }}" class="flex-1 sm:w-64">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ID or Name..." class="w-full p-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:border-[var(--cjc-navy)]">
                    </form>
                    <button @click="showAddStudentModal = true" class="btn-primary whitespace-nowrap">
                        + Add Student
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider border-y border-gray-200">
                            <th class="p-4 font-semibold">Student</th>
                            <th class="p-4 font-semibold">ID Number</th>
                            <th class="p-4 font-semibold">Department</th>
                            <th class="p-4 font-semibold text-center">Violations</th>
                            <th class="p-4 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($students as $student)
                            <tr class="hover:bg-gray-50">
                                <td class="p-4">
                                    <div class="font-medium text-[var(--cjc-navy)]">{{ $student->full_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $student->year_level }}</div>
                                </td>
                                <td class="p-4 text-sm font-mono text-gray-700">{{ $student->id }}</td>
                                <td class="p-4 text-sm text-gray-700">{{ $student->department }}</td>
                                <td class="p-4 text-center">
                                    @if($student->violations_count > 0)
                                        <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full">
                                            {{ $student->violations_count }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">None</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right">
                                    <button @click="openViolationModal({{ json_encode($student) }})" class="text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors">
                                        Manage
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-gray-500">
                                    No students found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $students->links() }}
            </div>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div x-show="showAddStudentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" style="display: none;">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 overflow-hidden" @click.away="showAddStudentModal = false">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-[var(--cjc-navy)] text-lg">Add New Student</h3>
                <button @click="showAddStudentModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('admin.students.store') }}" method="POST" class="p-6">
                @csrf
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Student ID</label>
                        <input type="text" name="id" required class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">First Name</label>
                        <input type="text" name="first_name" required class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Last Name</label>
                        <input type="text" name="last_name" required class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Department</label>
                        <input type="text" name="department" required class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Year Level</label>
                        <input type="text" name="year_level" required class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none">
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="showAddStudentModal = false" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-[var(--cjc-red)] hover:bg-red-700 rounded-md">Save Student</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Manage Violations Modal -->
    <div x-show="showViolationModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" style="display: none;">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-4 overflow-hidden flex flex-col max-h-[90vh]" @click.away="showViolationModal = false">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-[var(--cjc-navy)] text-white">
                <h3 class="font-bold text-lg">Manage Violations: <span x-text="selectedStudent?.first_name + ' ' + selectedStudent?.last_name"></span></h3>
                <button @click="showViolationModal = false" class="text-white/60 hover:text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <div class="p-6 flex-1 overflow-y-auto">
                
                <!-- Existing Violations -->
                <div class="mb-8">
                    <h4 class="text-sm font-bold text-gray-700 mb-3 border-b pb-2">Record History</h4>
                    <template x-if="selectedStudent?.violations?.length > 0">
                        <div class="space-y-3">
                            <template x-for="v in selectedStudent.violations" :key="v.id">
                                <div class="p-3 border rounded-lg bg-gray-50 border-gray-200 relative">
                                    <div class="flex justify-between items-start mb-1">
                                        <div class="font-bold text-gray-800" x-text="v.type"></div>
                                        <span class="text-xs px-2 py-0.5 rounded-full uppercase font-bold" 
                                              :class="v.severity === 'severe' ? 'bg-red-100 text-red-700' : (v.severity === 'moderate' ? 'bg-orange-100 text-orange-700' : 'bg-yellow-100 text-yellow-700')"
                                              x-text="v.severity"></span>
                                    </div>
                                    <div class="text-sm text-gray-600 mb-2" x-text="v.notes || 'No notes provided.'"></div>
                                    <div class="text-xs text-gray-400" x-text="'Date: ' + v.date"></div>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!selectedStudent?.violations || selectedStudent?.violations?.length === 0">
                        <p class="text-sm text-gray-500 italic">No violations recorded for this student.</p>
                    </template>
                </div>

                <!-- Add New Violation -->
                <div>
                    <h4 class="text-sm font-bold text-gray-700 mb-3 border-b pb-2">Record New Violation</h4>
                    <form :action="`/admin/students/${selectedStudent?.id}/violations`" method="POST">
                        @csrf
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Type of Violation</label>
                                <input type="text" name="type" required placeholder="e.g. Noise, Food" class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Severity</label>
                                <select name="severity" required class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm bg-white">
                                    <option value="minor">Minor</option>
                                    <option value="moderate">Moderate</option>
                                    <option value="severe">Severe</option>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Incident Date</label>
                                <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Notes</label>
                                <textarea name="notes" rows="2" class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm"></textarea>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 mt-4">
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md">Record Violation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
