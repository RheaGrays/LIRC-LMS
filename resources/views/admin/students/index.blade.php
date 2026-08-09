@extends('layouts.admin')

@section('title', ' | Patron Directory')
@section('header_title', 'Patron Directory & Violations')

@section('admin_content')
<div class="space-y-6" x-data="{
    showAddStudentModal: false,
    showImportModal: false,
    showViolationModal: false,
    selectedStudent: null,
    
    openViolationModal(student) {
        this.selectedStudent = student;
        this.showViolationModal = true;
    }
}">

    <div class="card bg-white border-t-4 border-t-[var(--cjc-red)]">
        <div class="p-6">
            
            <!-- Header Title + Action -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-xl font-bold text-[var(--cjc-navy)]">Patron Directory</h3>
                    <p class="text-sm text-gray-500">Manage patrons, filter by department or year level, and record violations.</p>
                </div>
                <div class="flex items-center gap-3 self-start md:self-auto flex-wrap">
                    <a href="{{ route('admin.students.export') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 border border-emerald-200 rounded-lg text-sm font-medium text-emerald-700 hover:bg-emerald-100 transition-colors shadow-sm whitespace-nowrap">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Export Excel
                    </a>
                    @if(Auth::guard('admin')->user()?->role === 'Super Admin')
                    <button @click="showImportModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm whitespace-nowrap">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Import Excel
                    </button>
                    @endif
                    <a href="{{ route('admin.students.archive') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm whitespace-nowrap">
                        <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                        Archive Inactive
                    </a>
                    <button @click="showAddStudentModal = true" class="btn-primary whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Patron
                    </button>
                </div>
            </div>

            <!-- Filter & Search Controls Bar -->
            <form method="GET" action="{{ route('admin.students.index') }}" class="mb-6 space-y-3 bg-gray-50/80 p-4 rounded-xl border border-gray-200">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                    
                    <!-- Search Input -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ID, Name, Dept..." 
                               class="w-full p-2 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-[var(--cjc-navy)]">
                    </div>

@php
    $catOptions = array_merge([['value' => '', 'label' => 'All Categories']], collect($patronCategories)->map(fn($c) => ['value' => $c, 'label' => $c])->toArray());
    $deptOptions = array_merge([['value' => '', 'label' => 'All Departments']], collect($departmentsList)->map(fn($d) => ['value' => (string)$d->id, 'label' => $d->name])->toArray());
    $progOptions = array_merge([['value' => '', 'label' => 'All Programs']], collect($programsList)->map(fn($p) => ['value' => (string)$p->id, 'label' => $p->name])->toArray());
    $ylOptions = array_merge([['value' => '', 'label' => 'All Year Levels']], collect($yearLevelsList)->map(fn($y) => ['value' => $y, 'label' => $y])->toArray());
    
    $sortByOptions = [
        ['value' => 'last_name', 'label' => 'Name (Last Name)'],
        ['value' => 'id', 'label' => 'ID Number'],
        ['value' => 'department_id', 'label' => 'Department'],
        ['value' => 'year_level', 'label' => 'Year Level'],
        ['value' => 'patron_category', 'label' => 'Patron Category'],
    ];
    $sortDirOptions = [
        ['value' => 'asc', 'label' => 'Ascending (A-Z, 1-9)'],
        ['value' => 'desc', 'label' => 'Descending (Z-A, 9-1)'],
    ];
@endphp

                    <!-- Category Filter -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Patron Category</label>
                        <x-custom-select name="category" :value="request('category')" :options="$catOptions" placeholder="All Categories" />
                    </div>

                    <!-- Department Filter -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Department</label>
                        <x-custom-select name="department_id" :value="request('department_id')" :options="$deptOptions" placeholder="All Departments" />
                    </div>

                    <!-- Program Filter -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Program</label>
                        <x-custom-select name="program_id" :value="request('program_id')" :options="$progOptions" placeholder="All Programs" />
                    </div>

                    <!-- Year Level Filter -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Year Level</label>
                        <x-custom-select name="year_level" :value="request('year_level')" :options="$ylOptions" placeholder="All Year Levels" />
                    </div>

                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-gray-200/70">
                    <!-- Sort By Dropdowns -->
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold text-gray-500">Sort By:</span>
                        <div class="w-48">
                            <x-custom-select name="sort_by" :value="request('sort_by', 'last_name')" :options="$sortByOptions" placeholder="Sort By" />
                        </div>
                        <div class="w-48">
                            <x-custom-select name="sort_dir" :value="request('sort_dir', 'asc')" :options="$sortDirOptions" placeholder="Order" />
                        </div>
                    </div>

                    <!-- Reset Filters Button -->
                    @if(request()->hasAny(['search', 'category', 'department_id', 'program_id', 'year_level', 'sort_by', 'sort_dir']))
                        <a href="{{ route('admin.students.index') }}" class="text-xs text-red-600 hover:text-red-800 font-semibold flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Clear Filters
                        </a>
                    @endif
                </div>
            </form>

            @php
                function sortLink($col, $label) {
                    $currentSort = request('sort_by', 'last_name');
                    $currentDir = request('sort_dir', 'asc');
                    $newDir = ($currentSort === $col && $currentDir === 'asc') ? 'desc' : 'asc';
                    $params = array_merge(request()->except(['sort_by', 'sort_dir']), ['sort_by' => $col, 'sort_dir' => $newDir]);
                    $url = route('admin.students.index', $params);
                    $icon = '';
                    if ($currentSort === $col) {
                        $icon = $currentDir === 'asc' ? ' ▲' : ' ▼';
                    }
                    return '<a href="' . e($url) . '" class="inline-flex items-center gap-1 hover:text-[var(--cjc-navy)] font-bold">' . e($label) . '<span class="text-xs text-[var(--cjc-red)]">' . $icon . '</span></a>';
                }
            @endphp

            <!-- Table Directory -->
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider border-b border-gray-200 select-none">
                            <th class="p-4">{!! sortLink('name', 'Patron Name') !!}</th>
                            <th class="p-4">{!! sortLink('id', 'ID Number') !!}</th>
                            <th class="p-4">{!! sortLink('patron_category', 'Category') !!}</th>
                            <th class="p-4">{!! sortLink('department_id', 'Department & Program') !!}</th>
                            <th class="p-4">{!! sortLink('year_level', 'Year Level') !!}</th>
                            <th class="p-4 text-center">{!! sortLink('violations_count', 'Violations') !!}</th>
                            <th class="p-4 text-right font-bold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($students as $student)
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="p-4">
                                    <div class="font-bold text-[var(--cjc-navy)]">{{ $student->full_name }}</div>
                                    @if($student->email)
                                        <div class="text-xs text-gray-500 font-mono">{{ $student->email }}</div>
                                    @endif
                                </td>
                                <td class="p-4 text-sm font-mono font-semibold text-gray-700">{{ $student->id }}</td>
                                <td class="p-4 text-sm">
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ $student->patron_category ?: 'Student' }}
                                    </span>
                                </td>
                                <td class="p-4 text-sm text-gray-700 font-medium">
                                    <div class="font-bold">{{ $student->academicDepartment?->name ?: '—' }}</div>
                                    <div class="text-xs text-gray-500">{{ $student->academicProgram?->name ?: '—' }}</div>
                                </td>
                                <td class="p-4 text-sm text-gray-600">
                                    {{ $student->year_level ?: '—' }}
                                </td>
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
                                    <button @click="openViolationModal({{ json_encode($student) }})" class="text-xs font-semibold px-3 py-1.5 rounded bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors">
                                        Manage Violations
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-12 text-center text-gray-400">
                                    No patrons found matching the selected filters.
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

    <!-- Add Patron Modal -->
    <div x-show="showAddStudentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" style="display: none;">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 overflow-hidden" @click.away="showAddStudentModal = false">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-[var(--cjc-navy)] text-lg">Add New Patron</h3>
                <button @click="showAddStudentModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('admin.students.store') }}" method="POST" class="p-6">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Patron Category *</label>
                        <select name="patron_category" required class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm bg-white">
                            @foreach($patronCategories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">ID Number *</label>
                        <input type="text" name="id" required placeholder="e.g. 2024-00123" class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">First Name *</label>
                        <input type="text" name="first_name" required placeholder="Juan" class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Last Name *</label>
                        <input type="text" name="last_name" required placeholder="Dela Cruz" class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Middle Name (Optional)</label>
                        <input type="text" name="middle_name" placeholder="Santos" class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Department</label>
                        <select name="department_id" class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm bg-white">
                            <option value="">None</option>
                            @foreach($departmentsList as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Program</label>
                        <select name="program_id" class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm bg-white">
                            <option value="">None</option>
                            @foreach($programsList as $prog)
                                <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Year Level (Optional)</label>
                        <input type="text" name="year_level" placeholder="e.g. 1st Year" class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Email Address (Optional)</label>
                        <input type="email" name="email" placeholder="juan@cjc.edu.ph" class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm">
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="showAddStudentModal = false" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-[var(--cjc-red)] hover:bg-red-700 rounded-md">Save Patron</button>
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
                                        <div class="font-bold text-gray-800" x-text="v.violation_type?.name || 'Unknown Type'"></div>
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
                        <p class="text-sm text-gray-500 italic">No violations recorded for this patron.</p>
                    </template>
                </div>

                <!-- Add New Violation -->
                <div>
                    <h4 class="text-sm font-bold text-gray-700 mb-3 border-b pb-2">Record New Violation</h4>
                    <form :action="`/admin/students/${selectedStudent?.id}/violations`" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
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

    <!-- Import Excel Modal -->
    <div x-show="showImportModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" @click="showImportModal = false">
                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                <div class="p-6">
                    <div class="flex items-center justify-between border-b pb-4 mb-4">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Import Patrons via Excel / CSV
                        </h3>
                        <button @click="showImportModal = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                    </div>
                    <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <p class="text-sm text-gray-600 mb-4">Upload an Excel Spreadsheet (<code>.xlsx</code>, <code>.xls</code>, or <code>.csv</code>) containing patron columns: <code>id</code>, <code>first_name</code>, <code>last_name</code>, <code>department</code>, <code>program</code>, <code>year_level</code>.</p>
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-500 transition-colors bg-gray-50/50 mb-4">
                            <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="showImportModal = false" class="btn-secondary">Cancel</button>
                            <button type="submit" class="btn-primary">Upload & Import</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
