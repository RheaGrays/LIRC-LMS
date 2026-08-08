@extends('layouts.admin')

@section('title', 'Archive Students')

@section('header', 'Student Archiving & Deactivation')

@section('content')
<div class="space-y-6">
    <!-- Header & Back Button -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Archive Inactive & Graduated Students</h2>
            <p class="text-sm text-gray-500 mt-1">Review students who are likely graduated or haven't visited the library in a long time.</p>
        </div>
        <a href="{{ route('admin.students.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Students
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-md flex items-start gap-3">
        <svg class="w-5 h-5 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <div class="text-sm text-green-700">{{ session('success') }}</div>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
        <ul class="list-disc list-inside text-sm text-red-700">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Filters -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-wrap gap-4 items-center">
        <span class="text-sm font-medium text-gray-600">Filter Candidates:</span>
        <a href="{{ route('admin.students.archive', ['filter' => 'all']) }}" class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors {{ $filter === 'all' ? 'bg-[#7f1d1d] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">All Flagged</a>
        <a href="{{ route('admin.students.archive', ['filter' => 'graduated']) }}" class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors {{ $filter === 'graduated' ? 'bg-[#7f1d1d] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Graduated Expected</a>
        <a href="{{ route('admin.students.archive', ['filter' => 'inactive_1_year']) }}" class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors {{ $filter === 'inactive_1_year' ? 'bg-[#7f1d1d] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Inactive > 1 Year</a>
        <a href="{{ route('admin.students.archive', ['filter' => 'inactive_4_years']) }}" class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors {{ $filter === 'inactive_4_years' ? 'bg-[#7f1d1d] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Inactive > 4 Years</a>
    </div>

    <!-- Data Table & Form -->
    <form action="{{ route('admin.students.archive.deactivate') }}" method="POST" id="bulkDeactivateForm">
        @csrf
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <div class="flex items-center gap-3">
                    <input type="checkbox" id="selectAll" class="w-4 h-4 text-[#7f1d1d] border-gray-300 rounded focus:ring-[#7f1d1d]">
                    <label for="selectAll" class="text-sm font-medium text-gray-700 cursor-pointer">Select All {{ count($candidates) }} Students</label>
                </div>
                <button type="submit" onclick="return confirm('Are you sure you want to deactivate these students?')" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" id="deactivateBtn" disabled>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Deactivate Selected
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-semibold border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3 w-10"></th>
                            <th class="px-4 py-3">Student</th>
                            <th class="px-4 py-3">Course / Dept</th>
                            <th class="px-4 py-3">Exp. Graduation</th>
                            <th class="px-4 py-3">Last Visit</th>
                            <th class="px-4 py-3">Flags</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($candidates as $student)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-4 py-3">
                                    <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="student-checkbox w-4 h-4 text-[#7f1d1d] border-gray-300 rounded focus:ring-[#7f1d1d]">
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gray-100 overflow-hidden shrink-0 border border-gray-200">
                                            @if($student->photo_url)
                                                <img src="{{ $student->photo_url }}" class="w-full h-full object-cover">
                                            @else
                                                <svg class="w-full h-full text-gray-400 p-1.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900">{{ $student->full_name }}</div>
                                            <div class="text-xs text-gray-500 font-mono">{{ $student->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-gray-900 font-medium truncate max-w-[200px]" title="{{ $student->department }}">{{ $student->department }}</div>
                                    <div class="text-xs text-gray-500">Reg: {{ $student->created_at->format('Y') }} as {{ $student->year_level }}</div>
                                </td>
                                <td class="px-4 py-3 font-semibold {{ $student->is_graduated ? 'text-red-600' : 'text-gray-700' }}">
                                    {{ $student->expected_graduation_year }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $student->last_visit_date }}</div>
                                    <div class="text-xs {{ $student->days_since_last_visit > 365 ? 'text-red-500 font-semibold' : 'text-gray-500' }}">
                                        {{ $student->days_since_last_visit }} days ago
                                    </div>
                                </td>
                                <td class="px-4 py-3 flex gap-1 flex-wrap">
                                    @if($student->is_graduated)
                                        <span class="inline-block px-2 py-0.5 bg-red-100 text-red-700 text-[10px] font-bold uppercase rounded-full tracking-wider border border-red-200">Graduated</span>
                                    @endif
                                    @if($student->days_since_last_visit > 365)
                                        <span class="inline-block px-2 py-0.5 bg-orange-100 text-orange-700 text-[10px] font-bold uppercase rounded-full tracking-wider border border-orange-200">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <p class="text-base font-medium text-gray-900">No students found</p>
                                    <p class="text-sm">There are no students matching this filter.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.student-checkbox');
        const deactivateBtn = document.getElementById('deactivateBtn');

        const updateBtnState = () => {
            const checkedCount = document.querySelectorAll('.student-checkbox:checked').length;
            deactivateBtn.disabled = checkedCount === 0;
            deactivateBtn.innerHTML = checkedCount > 0 
                ? `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Deactivate Selected (${checkedCount})` 
                : `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Deactivate Selected`;
        };

        if(selectAll) {
            selectAll.addEventListener('change', (e) => {
                checkboxes.forEach(cb => cb.checked = e.target.checked);
                updateBtnState();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                const allChecked = Array.from(checkboxes).length > 0 && Array.from(checkboxes).every(c => c.checked);
                const someChecked = Array.from(checkboxes).some(c => c.checked);
                selectAll.checked = allChecked;
                selectAll.indeterminate = someChecked && !allChecked;
                updateBtnState();
            });
        });
    });
</script>
@endsection
