@extends('layouts.admin')

@section('title', ' | Patron Directory')
@section('header_title', 'Patron Directory & Violations')

@section('admin_content')
<div class="space-y-6" x-data="{
    showAddStudentModal: false,
    showEditStudentModal: false,
    showImportModal: false,
    showViolationModal: false,
    showConfirmModal: false,
    confirmActionUrl: '',
    confirmTitle: '',
    confirmMessage: '',
    confirmButtonText: 'Confirm',
    selectedStudent: null,
    editStudentData: {},
    
    openViolationModal(student) {
        this.selectedStudent = student;
        this.showViolationModal = true;
    },
    
    openEditModal(student) {
        this.editStudentData = { ...student };
        this.showEditStudentModal = true;
    },

    confirmSettleViolation(violation) {
        this.confirmActionUrl = `/admin/violations/${violation.id}`;
        this.confirmTitle = 'Settle / Remove Violation?';
        this.confirmMessage = `Are you sure you want to remove the '${violation.violation_type?.name || 'violation'}' record for ${this.selectedStudent?.first_name} ${this.selectedStudent?.last_name}? This will clear it from their active record.`;
        this.confirmButtonText = 'Yes, Settle Violation';
        this.showConfirmModal = true;
    },

    confirmDeletePatron(student) {
        this.confirmActionUrl = `/admin/students/${student.id}`;
        this.confirmTitle = 'Delete Patron?';
        this.confirmMessage = `Are you sure you want to delete patron ${student.first_name} ${student.last_name} (${student.id})? This action cannot be undone.`;
        this.confirmButtonText = 'Yes, Delete Patron';
        this.showConfirmModal = true;
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
                    <button @click="showImportModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm whitespace-nowrap">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Import Excel
                    </button>
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
                                    @php
                                        $vCount = (int) ($student->violations_count ?? ($student->relationLoaded('violations') ? $student->violations->count() : $student->violations()->count()));
                                    @endphp
                                    @if($vCount > 0)
                                        <span class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full shadow-sm">
                                            {{ $vCount }} {{ $vCount === 1 ? 'Violation' : 'Violations' }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">None</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right whitespace-nowrap">
                                    <button @click="openEditModal({{ json_encode($student) }})" class="text-xs font-semibold px-3 py-1.5 rounded bg-blue-100 hover:bg-blue-200 text-blue-700 transition-colors mr-1">
                                        Edit
                                    </button>
                                    @if(auth('admin')->user()->isSuperAdmin())
                                    <button type="button" @click="confirmDeletePatron({{ json_encode($student) }})" class="text-xs font-semibold px-3 py-1.5 rounded bg-red-100 hover:bg-red-200 text-red-700 transition-colors mr-1">
                                        Delete
                                    </button>
                                    @endif
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
    <template x-teleport="body">
        <div x-show="showAddStudentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" style="display: none;" x-cloak>
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
    </template>

    <!-- Edit Patron Modal -->
    <template x-teleport="body">
        <div x-show="showEditStudentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" style="display: none;" x-cloak>
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 overflow-hidden" @click.away="showEditStudentModal = false">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="font-bold text-[var(--cjc-navy)] text-lg">Edit Patron</h3>
                    <button @click="showEditStudentModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form :action="`/admin/students/${editStudentData.id}`" method="POST" class="p-6">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Patron Category *</label>
                            <select name="patron_category" x-model="editStudentData.patron_category" required class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm bg-white">
                                @foreach($patronCategories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">ID Number (Cannot be changed)</label>
                            <input type="text" x-model="editStudentData.id" disabled class="w-full p-2 border border-gray-300 rounded bg-gray-100 text-gray-500 outline-none text-sm font-mono cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">First Name *</label>
                            <input type="text" name="first_name" x-model="editStudentData.first_name" required class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Last Name *</label>
                            <input type="text" name="last_name" x-model="editStudentData.last_name" required class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Middle Name (Optional)</label>
                            <input type="text" name="middle_name" x-model="editStudentData.middle_name" class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Department</label>
                            <select name="department_id" x-model="editStudentData.department_id" class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm bg-white">
                                <option value="">None</option>
                                @foreach($departmentsList as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Program</label>
                            <select name="program_id" x-model="editStudentData.program_id" class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm bg-white">
                                <option value="">None</option>
                                @foreach($programsList as $prog)
                                    <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Year Level (Optional)</label>
                            <input type="text" name="year_level" x-model="editStudentData.year_level" class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Email Address (Optional)</label>
                            <input type="email" name="email" x-model="editStudentData.email" class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="showEditStudentModal = false" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-[var(--cjc-red)] hover:bg-red-700 rounded-md">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- Manage Violations Modal -->
    <template x-teleport="body">
        <div x-show="showViolationModal" 
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" 
             style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh; z-index: 9999; display: none;" 
             x-cloak>
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden flex flex-col max-h-[90vh]" @click.away="showViolationModal = false">
                <div class="px-6 py-4 border-b border-slate-700/30 flex justify-between items-center bg-[var(--cjc-navy)] text-white">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg leading-tight">Manage Violations</h3>
                            <p class="text-xs text-white/70" x-text="selectedStudent?.first_name + ' ' + selectedStudent?.last_name + ' (' + (selectedStudent?.id || '') + ')'"></p>
                        </div>
                    </div>
                    <button @click="showViolationModal = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-white/60 hover:text-white hover:bg-white/10 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <div class="p-6 sm:p-7 flex-1 overflow-y-auto space-y-6" style="padding: 24px 28px;">
                    
                    <!-- Existing Violations -->
                    <div>
                        <div class="flex justify-between items-center mb-3.5 pb-2 border-b border-gray-100">
                            <div class="flex items-center gap-2">
                                <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Record History</h4>
                                <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-red-50 text-red-700 border border-red-200" 
                                      x-show="selectedStudent?.violations?.length > 0" 
                                      x-text="(selectedStudent?.violations?.length || 0) + ' active'"></span>
                            </div>
                            <span class="text-xs font-medium text-gray-500" x-show="!selectedStudent?.violations || selectedStudent?.violations?.length === 0">0 records</span>
                        </div>

                        <template x-if="selectedStudent?.violations?.length > 0">
                            <div class="space-y-3.5">
                                <template x-for="v in selectedStudent.violations" :key="v.id">
                                    <div class="rounded-xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm hover:border-slate-300 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                                         style="padding: 16px 20px; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 12px;">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                                <span class="font-bold text-gray-900 text-base" x-text="v.violation_type?.name || 'Violation'"></span>
                                                <span class="text-[11px] px-2.5 py-0.5 rounded-full uppercase font-bold tracking-wider" 
                                                      :class="v.severity === 'severe' ? 'bg-red-100 text-red-700 border border-red-200' : (v.severity === 'moderate' ? 'bg-orange-100 text-orange-700 border border-orange-200' : 'bg-amber-100 text-amber-800 border border-amber-200')"
                                                      x-text="v.severity"></span>
                                            </div>
                                            <div class="text-sm text-gray-600 mb-2 leading-relaxed" x-text="v.notes || 'No description provided.'"></div>
                                            <div class="flex items-center gap-1.5 text-xs text-gray-400 font-medium">
                                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <span x-text="'Incident Date: ' + (v.date ? (typeof v.date === 'string' ? v.date.split('T')[0] : v.date) : 'N/A')"></span>
                                            </div>
                                        </div>
                                        <div class="shrink-0 flex items-center justify-end sm:pl-3">
                                            <button type="button" 
                                                    @click="confirmSettleViolation(v)"
                                                    class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-red-50 hover:bg-red-100 text-red-600 hover:text-red-700 border border-red-200 hover:border-red-300 transition-all flex items-center gap-1.5 shadow-xs cursor-pointer"
                                                    style="padding: 8px 14px; font-weight: 600; border-radius: 8px;"
                                                    title="Remove settled violation">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                <span>Remove / Settle</span>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <template x-if="!selectedStudent?.violations || selectedStudent?.violations?.length === 0">
                            <div class="p-6 rounded-xl bg-gray-50 border border-dashed border-gray-200 text-center" style="padding: 24px;">
                                <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-2 border border-emerald-100">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-gray-700">Clean Record</p>
                                <p class="text-xs text-gray-400 mt-0.5">No active violations recorded for this patron.</p>
                            </div>
                        </template>
                    </div>

                    <!-- Add New Violation -->
                    <div class="pt-2 border-t border-gray-100">
                        <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-3.5">Record New Violation</h4>
                        <form :action="`/admin/students/${selectedStudent?.id}/violations`" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Type of Violation *</label>
                                    <select name="violation_type_id" required class="w-full p-2.5 border border-gray-300 rounded-lg focus:border-[var(--cjc-navy)] focus:ring-1 focus:ring-[var(--cjc-navy)] outline-none text-sm bg-white" style="padding: 10px 12px;">
                                        <option value="" disabled selected>Select Type</option>
                                        @foreach($violationTypes as $vType)
                                            <option value="{{ $vType->id }}">{{ $vType->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Severity *</label>
                                    <select name="severity" required class="w-full p-2.5 border border-gray-300 rounded-lg focus:border-[var(--cjc-navy)] focus:ring-1 focus:ring-[var(--cjc-navy)] outline-none text-sm bg-white" style="padding: 10px 12px;">
                                        <option value="minor">Minor</option>
                                        <option value="moderate">Moderate</option>
                                        <option value="severe">Severe</option>
                                    </select>
                                </div>
                                <div class="col-span-1 sm:col-span-2">
                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Incident Date *</label>
                                    <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="w-full p-2.5 border border-gray-300 rounded-lg focus:border-[var(--cjc-navy)] focus:ring-1 focus:ring-[var(--cjc-navy)] outline-none text-sm" style="padding: 10px 12px;">
                                </div>
                                <div class="col-span-1 sm:col-span-2">
                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Notes / Remarks</label>
                                    <textarea name="notes" rows="2" placeholder="Enter incident details or remarks..." class="w-full p-2.5 border border-gray-300 rounded-lg focus:border-[var(--cjc-navy)] focus:ring-1 focus:ring-[var(--cjc-navy)] outline-none text-sm" style="padding: 10px 12px;"></textarea>
                                </div>
                            </div>
                            <div class="flex justify-end gap-3 mt-4">
                                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm transition-all cursor-pointer flex items-center gap-2" style="padding: 10px 20px;">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span>Record Violation</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Custom Confirmation Dialog Modal -->
    <template x-teleport="body">
        <div x-show="showConfirmModal" 
             class="fixed inset-0 flex items-center justify-center" 
             style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh; z-index: 99999; background-color: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); display: none;"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden border border-gray-100" 
                 style="background: #ffffff; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); position: relative; z-index: 100000;"
                 @click.away="showConfirmModal = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95">
                <div class="p-6 sm:p-7 text-center" style="padding: 28px;">
                    <div class="w-14 h-14 rounded-full bg-red-50 text-red-600 flex items-center justify-center mx-auto mb-4 border border-red-100 shadow-sm"
                         style="width: 56px; height: 56px; margin: 0 auto 16px; border-radius: 50%; background-color: #fef2f2; border: 1px solid #fee2e2; display: flex; align-items: center; justify-content: center; color: #dc2626;">
                        <svg class="w-7 h-7" style="width: 28px; height: 28px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2" style="font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 8px;" x-text="confirmTitle"></h3>
                    <p class="text-sm text-gray-600 leading-relaxed mb-6" style="font-size: 14px; line-height: 1.6; color: #475569; margin-bottom: 24px;" x-text="confirmMessage"></p>
                    <div class="flex items-center justify-center gap-3" style="display: flex; justify-content: center; gap: 12px;">
                        <button type="button" 
                                @click="showConfirmModal = false" 
                                class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all cursor-pointer"
                                style="padding: 10px 20px; font-weight: 600; font-size: 14px; border-radius: 10px; background-color: #f1f5f9; color: #334155; border: none; cursor: pointer;">
                            Cancel
                        </button>
                        <form :action="confirmActionUrl" method="POST" class="inline" style="display: inline-block; margin: 0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="px-5 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-sm hover:shadow transition-all cursor-pointer flex items-center gap-2"
                                    style="padding: 10px 20px; font-weight: 600; font-size: 14px; border-radius: 10px; background-color: #dc2626; color: #ffffff; border: none; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                                <span x-text="confirmButtonText"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Import Excel Modal -->
    <template x-teleport="body">
        <div x-show="showImportModal" class="fixed inset-0 z-50 overflow-y-auto" style="z-index: 9999;" x-cloak>
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
                            <p class="text-sm text-gray-600 mb-4">Upload an Excel Spreadsheet (<code>.xlsx</code>, <code>.xls</code>, or <code>.csv</code>) matching the exact export format. The columns must be in this exact order: <br>
                            <strong>1. ID, 2. Last Name, 3. First Name, 4. Middle Name, 5. Category (Student/Alumni), 6. Department, 7. Program, 8. Year Level, 9. Email</strong>.</p>
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
    </template>

</div>
@endsection
