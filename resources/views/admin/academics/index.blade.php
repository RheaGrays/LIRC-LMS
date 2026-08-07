@extends('layouts.admin')

@section('title', ' | Departments')
@section('header_title', 'Departments & Programs')

@section('admin_content')
<div class="max-w-6xl mx-auto space-y-6">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h3 class="text-xl font-bold text-[var(--cjc-navy)]">Manage Departments & Programs</h3>
            <p class="text-sm text-gray-500">Organize your academic hierarchy. Click any department card to view or manage its programs.</p>
        </div>
        <button onclick="openAddDept()" class="btn-primary self-start sm:self-auto shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Department
        </button>
    </div>

    {{-- Success flash --}}
    @if(session('success'))
    <div class="flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm font-medium shadow-sm">
        <svg class="w-5 h-5 shrink-0 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Validation / Duplicate Error Flash --}}
    @if($errors->any())
    <div class="flex items-start gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm font-medium shadow-sm">
        <svg class="w-5 h-5 mt-0.5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <div>
            <strong class="block text-red-800 text-base font-bold mb-1">Cannot Save Data:</strong>
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <!-- Hierarchical Accordion List of Departments & Programs -->
    <div class="space-y-4">
        @forelse($departments as $dept)
        <div x-data="{ open: true }" class="bg-white border border-[var(--border-light)] rounded-xl shadow-sm overflow-hidden transition-all duration-200">
            
            <!-- Accordion Header (Department Row) -->
            <div class="px-5 py-4 bg-white hover:bg-gray-50/70 flex items-center justify-between gap-4 cursor-pointer select-none transition-colors border-b border-transparent"
                 :class="open ? 'border-gray-100 bg-gray-50/30' : ''"
                 @click="open = !open">
                
                <!-- Left: Expand Icon + Level Badge + Name + Count -->
                <div class="flex items-center gap-3.5 min-w-0">
                    <button type="button" class="text-gray-400 hover:text-gray-600 focus:outline-none transition-transform duration-200"
                            :class="open ? 'rotate-90 text-[var(--cjc-navy)]' : ''">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    @if($dept->level === 'college')
                        <span class="badge-entered shrink-0">College</span>
                    @else
                        <span class="badge-moderate shrink-0">Basic Education</span>
                    @endif

                    <h4 class="font-bold text-base text-[var(--cjc-navy)] truncate">{{ $dept->name }}</h4>

                    <span class="px-2.5 py-0.5 bg-gray-100 text-gray-600 border border-gray-200 rounded-full text-xs font-semibold shrink-0">
                        {{ $dept->programs->count() }} {{ Str::plural('Program', $dept->programs->count()) }}
                    </span>
                </div>

                <!-- Right: Actions (Add Program, Edit Dept, Delete Dept) -->
                <div class="flex items-center gap-3 shrink-0" @click.stop>
                    <button type="button" 
                            onclick="openAddProg({{ $dept->id }})" 
                            class="px-3 py-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 rounded-lg text-xs font-semibold transition-colors flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Program
                    </button>

                    <div class="w-px h-4 bg-gray-200"></div>

                    <button type="button" 
                            onclick="openEditDept({{ $dept->id }}, '{{ addslashes($dept->name) }}', '{{ $dept->level }}')" 
                            class="text-blue-600 hover:text-blue-800 font-medium text-xs">
                        Edit
                    </button>

                    <form id="del-dept-{{ $dept->id }}" action="{{ route('admin.academics.departments.destroy', $dept->id) }}" method="POST" class="inline-block">
                        @csrf @method('DELETE')
                        <button type="button" 
                                onclick="confirmDelete('del-dept-{{ $dept->id }}', 'Delete Department', 'Are you sure you want to delete <strong>{{ addslashes($dept->name) }}</strong>?<br><span class=\'text-red-500 text-xs mt-1 block\'>All its programs will also be permanently deleted.</span>')" 
                                class="text-red-500 hover:text-red-700 font-medium text-xs">
                            Delete
                        </button>
                    </form>
                </div>
            </div>

            <!-- Accordion Content (Nested Programs Table) -->
            <div x-show="open" x-transition.opacity.duration.200ms class="p-5 bg-gray-50/40 border-t border-gray-100">
                @if($dept->programs->count() > 0)
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-2xs">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="text-[11px] uppercase bg-gray-50 text-gray-500 font-bold tracking-wider border-b border-gray-200">
                            <tr>
                                <th class="px-5 py-3">Program / Course Name</th>
                                <th class="px-5 py-3">Code</th>
                                <th class="px-5 py-3">Duration</th>
                                <th class="px-5 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($dept->programs as $prog)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="px-5 py-3.5 font-semibold text-[var(--cjc-navy)]">
                                    {{ $prog->name }}
                                </td>
                                <td class="px-5 py-3.5 text-gray-600 font-mono text-xs">
                                    {{ $prog->code ?: '—' }}
                                </td>
                                <td class="px-5 py-3.5 text-gray-600 text-xs">
                                    <span class="px-2 py-0.5 bg-gray-100 rounded border border-gray-200 font-medium">
                                        {{ $prog->years }} {{ Str::plural('Year', $prog->years) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right flex items-center justify-end gap-3">
                                    <button type="button" 
                                            onclick="openEditProg({{ $prog->id }}, {{ $prog->department_id }}, '{{ addslashes($prog->name) }}', '{{ addslashes($prog->code) }}', {{ $prog->years }})" 
                                            class="text-blue-600 hover:text-blue-800 font-medium text-xs">
                                        Edit
                                    </button>
                                    
                                    <form id="del-prog-{{ $prog->id }}" action="{{ route('admin.academics.programs.destroy', $prog->id) }}" method="POST" class="inline-block">
                                        @csrf @method('DELETE')
                                        <button type="button" 
                                                onclick="confirmDelete('del-prog-{{ $prog->id }}', 'Delete Program', 'Are you sure you want to delete <strong>{{ addslashes($prog->name) }}</strong>? This cannot be undone.')" 
                                                class="text-red-500 hover:text-red-700 font-medium text-xs">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="py-8 px-4 text-center border-2 border-dashed border-gray-200 rounded-lg bg-white">
                    <p class="text-sm text-gray-500 mb-2 m-0">No programs created for this department yet.</p>
                    <button type="button" onclick="openAddProg({{ $dept->id }})" class="btn-secondary text-xs px-3 py-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add First Program
                    </button>
                </div>
                @endif
            </div>

        </div>
        @empty
        <div class="card text-center py-12 text-gray-400">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <p class="text-base font-semibold text-gray-600 m-0 mb-1">No Departments Found</p>
            <p class="text-xs text-gray-400 m-0 mb-4">Get started by creating your first department or college.</p>
            <button onclick="openAddDept()" class="btn-primary">Add Department</button>
        </div>
        @endforelse
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     DELETE CONFIRMATION MODAL
══════════════════════════════════════════════════════════ --}}
<div id="confirm-modal" class="fixed inset-0 z-[100] hidden" aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeConfirm()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm bg-white rounded-2xl shadow-2xl p-6 flex flex-col items-center gap-4 z-10">
        <div id="confirm-icon-wrap" class="w-14 h-14 rounded-full flex items-center justify-center shrink-0" style="background-color: #fee2e2;">
            <svg class="w-7 h-7" style="color: #dc2626;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        </div>
        <div class="text-center">
            <h3 id="confirm-title" class="text-lg font-bold text-[var(--cjc-navy)] mb-1">Confirm Deletion</h3>
            <p id="confirm-message" class="text-sm text-gray-500 leading-relaxed"></p>
        </div>
        <div class="flex items-center gap-3 w-full mt-2">
            <button type="button" onclick="closeConfirm()" style="background-color: #f3f4f6; color: #374151;" class="flex-1 px-4 py-2.5 text-sm font-semibold rounded-xl hover:bg-gray-200 transition-colors">
                Cancel
            </button>
            <button type="button" id="confirm-ok-btn" onclick="executeConfirm()" style="background-color: #dc2626 !important; color: #ffffff !important; display: inline-flex !important; align-items: center !important; justify-content: center !important;" class="flex-1 px-4 py-2.5 text-sm font-bold rounded-xl shadow-md transition-colors hover:opacity-90">
                Delete
            </button>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     ADD DEPARTMENT MODAL
══════════════════════════════════════════════════════════ --}}
<div id="add-dept-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="document.getElementById('add-dept-modal').classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-xl shadow-2xl p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-[var(--cjc-navy)]">Add Department</h3>
            <button type="button" onclick="document.getElementById('add-dept-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <form id="add-dept-form" action="{{ route('admin.academics.departments.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Level</label>
                <select name="level" id="add-dept-level" class="input no-tomselect" required>
                    <option value="college" {{ old('level') === 'college' ? 'selected' : '' }}>College</option>
                    <option value="basic_ed" {{ old('level') === 'basic_ed' ? 'selected' : '' }}>Basic Education</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Department Name</label>
                <input type="text" name="name" id="add-dept-name" value="{{ old('name') }}" class="input" placeholder="e.g. College of Computing and Information Sciences" required>
                @error('name')
                    <p class="text-xs text-red-600 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="pt-2 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('add-dept-modal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-md">Cancel</button>
                <button type="submit" class="btn-primary">Save Department</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     ADD PROGRAM MODAL
══════════════════════════════════════════════════════════ --}}
<div id="add-prog-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="document.getElementById('add-prog-modal').classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-xl shadow-2xl p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-[var(--cjc-navy)]">Add Program</h3>
            <button type="button" onclick="document.getElementById('add-prog-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <form id="add-prog-form" action="{{ route('admin.academics.programs.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Department</label>
                <select name="department_id" id="add-prog-dept" class="input no-tomselect" required>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }} ({{ ucfirst($dept->level) }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Program Name</label>
                <input type="text" name="name" id="add-prog-name" class="input" placeholder="e.g. Bachelor of Science in Computer Science" required>
                @error('name')
                    <p class="text-xs text-red-600 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex gap-4">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Code (Optional)</label>
                    <input type="text" name="code" class="input" placeholder="e.g. BSCS">
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

{{-- ══════════════════════════════════════════════════════════
     EDIT DEPARTMENT MODAL
══════════════════════════════════════════════════════════ --}}
<div id="edit-dept-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="document.getElementById('edit-dept-modal').classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-xl shadow-2xl p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-[var(--cjc-navy)]">Edit Department</h3>
            <button type="button" onclick="document.getElementById('edit-dept-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <form id="edit-dept-form" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Level</label>
                <select name="level" id="edit-dept-level" class="input no-tomselect" required>
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

{{-- ══════════════════════════════════════════════════════════
     EDIT PROGRAM MODAL
══════════════════════════════════════════════════════════ --}}
<div id="edit-prog-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="document.getElementById('edit-prog-modal').classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-xl shadow-2xl p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-[var(--cjc-navy)]">Edit Program</h3>
            <button type="button" onclick="document.getElementById('edit-prog-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <form id="edit-prog-form" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Department</label>
                <select name="department_id" id="edit-prog-dept" class="input no-tomselect" required>
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
    let _pendingAction = null;

    function confirmDelete(formId, title, message) {
        document.getElementById('confirm-title').textContent = title;
        document.getElementById('confirm-message').innerHTML = message;
        _pendingAction = () => document.getElementById(formId).submit();
        document.getElementById('confirm-modal').classList.remove('hidden');
    }

    function closeConfirm() {
        document.getElementById('confirm-modal').classList.add('hidden');
        _pendingAction = null;
    }

    function executeConfirm() {
        if (_pendingAction) {
            _pendingAction();
            _pendingAction = null;
        }
        closeConfirm();
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeConfirm(); });

    function openAddDept() {
        document.getElementById('add-dept-form').reset();
        document.getElementById('add-dept-modal').classList.remove('hidden');
    }

    function openAddProg(deptId = null) {
        document.getElementById('add-prog-form').reset();
        if (deptId) {
            document.getElementById('add-prog-dept').value = deptId;
        }
        document.getElementById('add-prog-modal').classList.remove('hidden');
    }

    function openEditDept(id, name, level) {
        document.getElementById('edit-dept-form').action = `/admin/academics/departments/${id}`;
        document.getElementById('edit-dept-name').value = name;
        document.getElementById('edit-dept-level').value = level;
        document.getElementById('edit-dept-modal').classList.remove('hidden');
    }

    function openEditProg(id, deptId, name, code, years) {
        document.getElementById('edit-prog-form').action = `/admin/academics/programs/${id}`;
        document.getElementById('edit-prog-dept').value = deptId;
        document.getElementById('edit-prog-name').value = name;
        document.getElementById('edit-prog-code').value = code;
        document.getElementById('edit-prog-years').value = years;
        document.getElementById('edit-prog-modal').classList.remove('hidden');
    }

    // Automatically re-open modal if validation error returned
    @if($errors->any())
        @if(old('level') || old('name'))
            document.addEventListener('DOMContentLoaded', () => {
                document.getElementById('add-dept-modal').classList.remove('hidden');
            });
        @endif
    @endif
</script>
@endsection
