@extends('layouts.admin')

@section('title', ' | Departments')
@section('header_title', 'Departments & Programs')

@section('admin_content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="mb-4">
        <h3 class="text-xl font-bold text-[var(--cjc-navy)]">Manage Departments & Programs</h3>
        <p class="text-sm text-gray-500">Add or remove academic levels, departments, and programs used in the registration forms.</p>
    </div>

    <!-- Departments Section -->
    <div class="card">
        <div class="flex justify-between items-center mb-4">
            <h4 class="font-semibold text-lg text-[var(--cjc-navy)]">Colleges & Departments</h4>
            <button onclick="document.getElementById('add-dept-modal').classList.remove('hidden')" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Department
            </button>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="text-xs uppercase bg-gray-50 text-gray-600 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Level</th>
                        <th class="px-6 py-3 font-semibold">Department / College Name</th>
                        <th class="px-6 py-3 font-semibold">Programs Count</th>
                        <th class="px-6 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($departments as $dept)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4">
                            @if($dept->level === 'college')
                                <span class="badge-entered">College</span>
                            @else
                                <span class="badge-moderate">Basic Education</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-medium text-[var(--cjc-navy)]">{{ $dept->name }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $dept->programs->count() }}</td>
                        <td class="px-6 py-4 text-right">
                            <button type="button" onclick="editDept({{ $dept->id }}, '{{ addslashes($dept->name) }}', '{{ $dept->level }}')" class="text-blue-500 hover:text-blue-700 font-medium text-xs">Edit</button>
                            <form action="{{ route('admin.academics.departments.destroy', $dept->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this department? All its programs will also be deleted.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-xs ml-3">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-400">No departments added yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Programs Section -->
    <div class="card mt-6">
        <div class="flex justify-between items-center mb-4">
            <h4 class="font-semibold text-lg text-[var(--cjc-navy)]">Programs & Courses</h4>
            <button onclick="document.getElementById('add-prog-modal').classList.remove('hidden')" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Program
            </button>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="text-xs uppercase bg-gray-50 text-gray-600 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Department</th>
                        <th class="px-6 py-3 font-semibold">Program Name</th>
                        <th class="px-6 py-3 font-semibold">Code</th>
                        <th class="px-6 py-3 font-semibold">Years</th>
                        <th class="px-6 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php $hasPrograms = false; @endphp
                    @foreach($departments as $dept)
                        @foreach($dept->programs as $prog)
                        @php $hasPrograms = true; @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-gray-500">{{ $dept->name }}</td>
                            <td class="px-6 py-4 font-medium text-[var(--cjc-navy)]">{{ $prog->name }}</td>
                            <td class="px-6 py-4">{{ $prog->code ?: '-' }}</td>
                            <td class="px-6 py-4">{{ $prog->years }}</td>
                            <td class="px-6 py-4 text-right">
                                <button type="button" onclick="editProg({{ $prog->id }}, {{ $prog->department_id }}, '{{ addslashes($prog->name) }}', '{{ addslashes($prog->code) }}', {{ $prog->years }})" class="text-blue-500 hover:text-blue-700 font-medium text-xs">Edit</button>
                                <form action="{{ route('admin.academics.programs.destroy', $prog->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this program?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-xs ml-3">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                    @if(!$hasPrograms)
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">No programs added yet.</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Department Modal -->
<div id="add-dept-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="this.parentElement.classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-xl shadow-2xl p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-[var(--cjc-navy)]">Add Department</h3>
            <button onclick="document.getElementById('add-dept-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <form action="{{ route('admin.academics.departments.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Level</label>
                <select name="level" class="input" required>
                    <option value="college">College</option>
                    <option value="basic_ed">Basic Education</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Department Name</label>
                <input type="text" name="name" class="input" placeholder="e.g. College of Computer Studies" required>
            </div>
            <div class="pt-2 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('add-dept-modal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-md">Cancel</button>
                <button type="submit" class="btn-primary">Save Department</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Program Modal -->
<div id="add-prog-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="this.parentElement.classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-xl shadow-2xl p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-[var(--cjc-navy)]">Add Program</h3>
            <button onclick="document.getElementById('add-prog-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <form action="{{ route('admin.academics.programs.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Department</label>
                <select name="department_id" class="input" required>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }} ({{ ucfirst($dept->level) }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Program Name</label>
                <input type="text" name="name" class="input" placeholder="e.g. Bachelor of Science in Information Technology" required>
            </div>
            <div class="flex gap-4">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Code (Optional)</label>
                    <input type="text" name="code" class="input" placeholder="e.g. BSIT">
                </div>
                <div class="w-32">
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Max Years</label>
                    <input type="number" name="years" class="input" value="4" min="1" max="10" required>
                </div>
            </div>
            <div class="pt-2 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('add-prog-modal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-md">Cancel</button>
                <button type="submit" class="btn-primary">Save Program</button>
            </div>
        </form>
    </div>
</div>
<!-- Edit Department Modal -->
<div id="edit-dept-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="this.parentElement.classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-xl shadow-2xl p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-[var(--cjc-navy)]">Edit Department</h3>
            <button onclick="document.getElementById('edit-dept-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <form id="edit-dept-form" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Level</label>
                <select name="level" id="edit-dept-level" class="input" required>
                    <option value="college">College</option>
                    <option value="basic_ed">Basic Education</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Department Name</label>
                <input type="text" name="name" id="edit-dept-name" class="input" required>
            </div>
            <div class="pt-2 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('edit-dept-modal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-md">Cancel</button>
                <button type="submit" class="btn-primary">Update Department</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Program Modal -->
<div id="edit-prog-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="this.parentElement.classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-xl shadow-2xl p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-[var(--cjc-navy)]">Edit Program</h3>
            <button onclick="document.getElementById('edit-prog-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <form id="edit-prog-form" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Department</label>
                <select name="department_id" id="edit-prog-dept" class="input" required>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }} ({{ ucfirst($dept->level) }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Program Name</label>
                <input type="text" name="name" id="edit-prog-name" class="input" required>
            </div>
            <div class="flex gap-4">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Code (Optional)</label>
                    <input type="text" name="code" id="edit-prog-code" class="input" placeholder="e.g. BSIT">
                </div>
                <div class="w-32">
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Max Years</label>
                    <input type="number" name="years" id="edit-prog-years" class="input" min="1" max="10" required>
                </div>
            </div>
            <div class="pt-2 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('edit-prog-modal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-md">Cancel</button>
                <button type="submit" class="btn-primary">Update Program</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editDept(id, name, level) {
        document.getElementById('edit-dept-form').action = `/admin/academics/departments/${id}`;
        document.getElementById('edit-dept-name').value = name;
        document.getElementById('edit-dept-level').value = level;
        document.getElementById('edit-dept-modal').classList.remove('hidden');
    }
    
    function editProg(id, deptId, name, code, years) {
        document.getElementById('edit-prog-form').action = `/admin/academics/programs/${id}`;
        document.getElementById('edit-prog-dept').value = deptId;
        document.getElementById('edit-prog-name').value = name;
        document.getElementById('edit-prog-code').value = code;
        document.getElementById('edit-prog-years').value = years;
        document.getElementById('edit-prog-modal').classList.remove('hidden');
    }
</script>
@endsection
