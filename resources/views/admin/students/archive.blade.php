@extends('layouts.admin')

@section('title', 'Archive Students')
@section('header', 'Student Archiving & Deactivation')

@section('content')
<div class="space-y-6 relative z-0 px-4 sm:px-6 lg:px-8 py-4">
    <!-- Subtle Background SVG abstract wave (top right) -->
    <div class="absolute -top-10 -right-10 -z-10 pointer-events-none opacity-[0.03]">
        <svg width="600" height="300" viewBox="0 0 600 300" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,50 Q150,150 300,50 T600,50" stroke="#dc2626" stroke-width="3" fill="none" />
            <path d="M0,70 Q150,170 300,70 T600,70" stroke="#dc2626" stroke-width="3" fill="none" />
            <path d="M0,90 Q150,190 300,90 T600,90" stroke="#dc2626" stroke-width="3" fill="none" />
            <path d="M0,110 Q150,210 300,110 T600,110" stroke="#dc2626" stroke-width="3" fill="none" />
            <path d="M0,130 Q150,230 300,130 T600,130" stroke="#dc2626" stroke-width="3" fill="none" />
        </svg>
    </div>

    <!-- Header & Back Button -->
    <div class="flex items-center justify-between mt-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Archive Inactive & Graduated Students</h2>
            <p class="text-sm text-gray-500 mt-1">Review students who are likely graduated or haven't visited the library in a long time.</p>
            <div class="w-12 h-[3px] bg-red-600 mt-3 rounded-full"></div>
        </div>
        <a href="{{ route('admin.students.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 border border-red-200 text-red-600 bg-white rounded-lg text-sm font-bold hover:bg-red-50 hover:border-red-300 transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Students
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-xl flex items-start gap-3 shadow-sm mt-4">
        <svg class="w-5 h-5 text-green-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <div class="text-sm font-medium text-green-800">{{ session('success') }}</div>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-xl shadow-sm mt-4">
        <ul class="list-disc list-inside text-sm font-medium text-red-800">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Filters Block -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100/60 mt-8">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center text-red-600 border border-red-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900">Filter Candidates</h3>
        </div>
        
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.students.archive', ['filter' => 'all']) }}" class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all shadow-sm {{ $filter === 'all' ? 'bg-red-600 text-white border border-red-600' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">All Flagged</a>
            <a href="{{ route('admin.students.archive', ['filter' => 'graduated']) }}" class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all shadow-sm {{ $filter === 'graduated' ? 'bg-red-600 text-white border border-red-600' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">Graduated Expected</a>
            <a href="{{ route('admin.students.archive', ['filter' => 'inactive_1_year']) }}" class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all shadow-sm {{ $filter === 'inactive_1_year' ? 'bg-red-600 text-white border border-red-600' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">Inactive > 1 Year</a>
            <a href="{{ route('admin.students.archive', ['filter' => 'inactive_4_years']) }}" class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all shadow-sm {{ $filter === 'inactive_4_years' ? 'bg-red-600 text-white border border-red-600' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">Inactive > 4 Years</a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1 -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100/60 flex items-center gap-4 transition-shadow hover:shadow-md">
            <div class="w-14 h-14 rounded-2xl bg-red-50 flex items-center justify-center text-red-600 shrink-0 border border-red-100">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div>
                <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Total Candidates</div>
                <div class="text-2xl font-black text-gray-900 leading-tight mt-0.5">{{ $stats['total'] }}</div>
                <div class="text-xs font-medium text-gray-400 mt-0.5">Students flagged</div>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100/60 flex items-center gap-4 transition-shadow hover:shadow-md">
            <div class="w-14 h-14 rounded-2xl bg-red-50 flex items-center justify-center text-red-600 shrink-0 border border-red-100">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
            </div>
            <div>
                <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Expected Graduates</div>
                <div class="text-2xl font-black text-gray-900 leading-tight mt-0.5">{{ $stats['graduated'] }}</div>
                <div class="text-xs font-medium text-gray-400 mt-0.5">Will graduate soon</div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100/60 flex items-center gap-4 transition-shadow hover:shadow-md">
            <div class="w-14 h-14 rounded-2xl bg-red-50 flex items-center justify-center text-red-600 shrink-0 border border-red-100">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Inactive > 1 Year</div>
                <div class="text-2xl font-black text-gray-900 leading-tight mt-0.5">{{ $stats['inactive_1'] }}</div>
                <div class="text-xs font-medium text-gray-400 mt-0.5">No library visit > 1 year</div>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100/60 flex items-center gap-4 transition-shadow hover:shadow-md">
            <div class="w-14 h-14 rounded-2xl bg-red-50 flex items-center justify-center text-red-600 shrink-0 border border-red-100">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Inactive > 4 Years</div>
                <div class="text-2xl font-black text-gray-900 leading-tight mt-0.5">{{ $stats['inactive_4'] }}</div>
                <div class="text-xs font-medium text-gray-400 mt-0.5">No library visit > 4 years</div>
            </div>
        </div>
    </div>

    <!-- Table Block -->
    <form action="{{ route('admin.students.archive.deactivate') }}" method="POST" id="bulkDeactivateForm">
        @csrf
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100/60 overflow-hidden mt-6">
            <!-- Table Header Toolbar -->
            <div class="p-5 border-b border-gray-100/60 flex flex-wrap justify-between items-center gap-4 bg-white">
                <div class="flex items-center gap-4 pl-1">
                    <input type="checkbox" id="selectAll" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-600">
                    <label for="selectAll" class="text-sm font-semibold text-gray-600 cursor-pointer select-none">Select All {{ count($candidates) }} Students</label>
                </div>
                <button type="submit" onclick="return confirm('Are you sure you want to deactivate these students?')" class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 text-white rounded-xl text-sm font-bold hover:bg-red-700 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" id="deactivateBtn" disabled>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Deactivate Selected
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600 border-collapse">
                    <thead class="bg-white text-[11px] uppercase text-gray-500 font-bold border-b border-gray-100 tracking-wider">
                        <tr>
                            <th class="px-6 py-4 w-12 text-center"></th>
                            <th class="px-6 py-4">Student</th>
                            <th class="px-6 py-4">Course / Dept</th>
                            <th class="px-6 py-4 text-center">Exp. Graduation</th>
                            <th class="px-6 py-4">Last Visit</th>
                            <th class="px-6 py-4 text-right pr-8">Flags</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($candidates as $student)
                            <tr class="hover:bg-gray-50/80 transition-colors group">
                                <td class="px-6 py-5 text-center">
                                    <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="student-checkbox w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-600">
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-gray-100 overflow-hidden shrink-0 border border-gray-200 shadow-sm">
                                            @if($student->photo_url)
                                                <img src="{{ $student->photo_url }}" class="w-full h-full object-cover">
                                            @else
                                                <svg class="w-full h-full text-gray-400 p-2" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 text-sm tracking-tight">{{ $student->full_name }}</div>
                                            <div class="text-[13px] text-gray-500 font-medium mt-0.5">{{ $student->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="text-gray-900 font-bold text-sm tracking-tight">{{ $student->academicDepartment?->name ?? '—' }}</div>
                                    <div class="text-[13px] text-gray-500 font-medium mt-0.5">Reg: {{ $student->created_at->format('Y') }} as {{ $student->year_level }}</div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="inline-block px-3 py-1 bg-red-50 text-red-600 font-bold text-xs rounded-md border border-red-100">{{ $student->expected_graduation_year }}</span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="font-bold text-gray-900 text-sm tracking-tight">{{ $student->last_visit_date }}</div>
                                    <div class="text-[13px] text-gray-500 font-medium mt-0.5">{{ $student->days_since_last_visit }} days ago</div>
                                </td>
                                <td class="px-6 py-5 text-right pr-8">
                                    <div class="flex items-center justify-end gap-3">
                                        <!-- Flag Icon -->
                                        @if($student->is_graduated || $student->days_since_last_visit > 365)
                                        <div class="w-8 h-8 rounded-full border border-red-200 text-red-500 flex items-center justify-center bg-white shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                                        </div>
                                        @endif
                                        
                                        <!-- 3 Dots Menu -->
                                        <button type="button" class="text-gray-400 hover:text-gray-700 transition-colors">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-100 shadow-sm">
                                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <p class="text-lg font-bold text-gray-900 tracking-tight">No candidates found</p>
                                    <p class="text-sm font-medium mt-1">There are no students matching the current filter criteria.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Table Footer -->
            <div class="px-6 py-4 border-t border-gray-100/60 bg-white flex items-center justify-between">
                <div class="text-sm font-medium text-gray-500">
                    Showing <span class="font-bold text-gray-900">1</span> to <span class="font-bold text-gray-900">{{ count($candidates) }}</span> of <span class="font-bold text-gray-900">{{ count($candidates) }}</span> students
                </div>
                
                <!-- Pagination (Aesthetic mock matching design) -->
                <div class="flex items-center gap-2">
                    <button type="button" class="w-9 h-9 rounded-lg border border-gray-200 flex items-center justify-center text-gray-400 hover:text-gray-600 bg-white shadow-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button" class="w-9 h-9 rounded-lg border border-red-600 flex items-center justify-center text-white bg-red-600 shadow-sm font-bold text-sm">
                        1
                    </button>
                    <button type="button" class="w-9 h-9 rounded-lg border border-gray-200 flex items-center justify-center text-gray-400 hover:text-gray-600 bg-white shadow-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
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
