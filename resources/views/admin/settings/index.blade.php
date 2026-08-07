@extends('layouts.admin')

@section('title', ' | Settings')
@section('header_title', 'System Settings')

@section('admin_content')
<div class="max-w-7xl mx-auto space-y-6">

    <div class="card mb-6 p-0 overflow-hidden fade-in-up">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white">
            <div>
                <h3 class="text-lg font-bold text-[var(--cjc-navy)]">Academic Term Management</h3>
            </div>
            <button type="button" onclick="document.getElementById('add-term-modal').classList.remove('hidden')" class="btn-primary text-sm px-4 py-2 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Add Term
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50/50 font-bold tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Academic Year</th>
                        <th class="px-6 py-4">Semester</th>
                        <th class="px-6 py-4">Start Date</th>
                        <th class="px-6 py-4">End Date</th>
                        <th class="px-6 py-4">Holidays</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($terms as $term)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 font-bold text-[var(--cjc-navy)]">{{ $term->academic_year }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $term->semester }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $term->start_date->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $term->end_date->format('M d, Y') }}</td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-medium border border-gray-200">{{ $term->holidays }} holidays</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($term->status === 'Active')
                                <span class="px-3 py-1 bg-green-50 text-green-700 rounded-full text-xs font-medium border border-green-200">Active</span>
                            @else
                                <span class="px-3 py-1 bg-gray-50 text-gray-600 rounded-full text-xs font-medium border border-gray-200">Archived</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button type="button" onclick="editTerm({{ $term->id }}, '{{ addslashes($term->academic_year) }}', '{{ addslashes($term->semester) }}', '{{ $term->start_date->format('Y-m-d') }}', '{{ $term->end_date->format('Y-m-d') }}', {{ $term->holidays }}, '{{ $term->status }}')" class="text-blue-500 hover:text-blue-700 font-medium text-xs mr-3">Edit</button>
                            <form action="{{ route('admin.settings.terms.destroy', $term->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this term?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500 bg-gray-50/30">
                            No academic terms found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card p-0 overflow-hidden fade-in-up">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            
            <!-- Global Capacity -->
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-bold text-[var(--cjc-navy)] mb-1">Library Capacity</h3>
                <p class="text-sm text-gray-500 mb-5">Set the maximum number of students allowed inside the library simultaneously.</p>
                
                <div class="max-w-xs">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Max Occupancy Limit</label>
                    <input type="number" name="max_occupancy" value="{{ $settings['max_occupancy'] ?? 200 }}" class="input" min="1">
                </div>
                
                <div class="mt-4 flex items-center">
                    <input type="hidden" name="show_occupancy" value="0">
                    <input type="checkbox" id="show_occupancy" name="show_occupancy" value="1" {{ ($settings['show_occupancy'] ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-[var(--cjc-red)] shadow-sm focus:ring-[var(--cjc-red)] w-4 h-4 cursor-pointer">
                    <label for="show_occupancy" class="ml-2 text-sm text-gray-700 font-medium cursor-pointer select-none">Display live occupancy counter on the Kiosk screen</label>
                </div>
            </div>

            <!-- Patron Categories -->
            <div class="p-6 border-b border-gray-100" x-data="{
                categories: {{ json_encode($settings['patron_categories'] ?? ['Student', 'Employee', 'Post Graduate', 'Alumni', 'Visitor']) }},
                newCategory: '',
                addCategory() {
                    const val = this.newCategory.trim();
                    if (val !== '' && !this.categories.includes(val)) {
                        this.categories.push(val);
                        this.newCategory = '';
                    }
                },
                removeCategory(index) {
                    this.categories.splice(index, 1);
                }
            }">
                <h3 class="text-lg font-bold text-[var(--cjc-navy)] mb-1">Patron Categories</h3>
                <p class="text-sm text-gray-500 mb-5">Define who can register and check in to the library. The <strong class="text-[var(--cjc-navy)]">Student</strong> category enables the academic cascade (Department → Program → Year Level) in the registration form.</p>

                <div class="space-y-3 mb-4 max-w-md">
                    <template x-for="(cat, index) in categories" :key="index">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 flex items-center gap-2 px-3 py-2 bg-gray-50 border border-gray-200 rounded-md">
                                <span class="w-2 h-2 rounded-full bg-[var(--cjc-navy)] shrink-0"></span>
                                <input type="text" x-model="categories[index]" :name="'patron_categories['+index+']'" class="flex-1 bg-transparent text-sm font-medium text-[var(--cjc-navy)] outline-none border-none focus:ring-0 p-0">
                            </div>
                            <button type="button" @click="removeCategory(index)" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors" title="Remove">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </template>
                    <template x-if="categories.length === 0">
                        <p class="text-sm text-gray-400 italic">No categories defined. Add at least one.</p>
                    </template>
                </div>

                <div class="flex items-center gap-2 max-w-md">
                    <input type="text" x-model="newCategory" @keydown.enter.prevent="addCategory()" placeholder="New category name..." class="input flex-1">
                    <button type="button" @click="addCategory()" class="btn-secondary whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Add
                    </button>
                </div>

                <div class="mt-5 flex items-center gap-2.5 px-4 py-3 bg-blue-50 border border-blue-200 rounded-lg max-w-md">
                    <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <p class="text-xs text-blue-700 m-0">Departments, Programs, and Year Levels are managed in the <a href="{{ route('admin.academics.index') }}" class="font-semibold underline hover:text-blue-900">Academic Setup →</a> page.</p>
                </div>
            </div>

            <!-- Library Sections (AlpineJS Dynamic List) -->
            <div class="p-6 border-b border-gray-100" x-data="{
                sections: {{ json_encode($settings['library_sections'] ?? ['General Reading', 'Discussion Room', 'Internet Section', 'Periodicals']) }},
                newSection: '',
                addSection() {
                    if(this.newSection.trim() !== '') {
                        this.sections.push(this.newSection.trim());
                        this.newSection = '';
                    }
                },
                removeSection(index) {
                    this.sections.splice(index, 1);
                }
            }">
                <h3 class="text-lg font-bold text-[var(--cjc-navy)] mb-1">Library Sections</h3>
                <p class="text-sm text-gray-500 mb-5">Define the sections used for manual headcount tracking.</p>
                
                <div class="space-y-3 mb-4 max-w-md">
                    <template x-for="(section, index) in sections" :key="index">
                        <div class="flex items-center gap-2">
                            <input type="text" x-model="sections[index]" :name="'library_sections['+index+']'" class="input bg-gray-50 font-medium">
                            <button type="button" @click="removeSection(index)" class="p-2 text-red-500 hover:bg-red-50 rounded-md transition-colors" title="Remove">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </template>
                </div>
                
                <div class="flex items-center gap-2 max-w-md">
                    <input type="text" x-model="newSection" @keydown.enter.prevent="addSection()" placeholder="New Section Name..." class="input">
                    <button type="button" @click="addSection()" class="btn-secondary whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Add
                    </button>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-gray-50 flex justify-end">
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                    Save Configuration
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add Term Modal -->
<div id="add-term-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="this.parentElement.classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-xl shadow-2xl p-6">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-xl font-bold text-[var(--cjc-navy)]">Add Academic Term</h3>
            <button type="button" onclick="this.closest('#add-term-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        
        <form action="{{ route('admin.settings.terms.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Academic Year</label>
                    <input type="text" name="academic_year" class="input" placeholder="e.g. 2025-2026" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Semester</label>
                    <select name="semester" class="input" required>
                        <option value="1st Semester">1st Semester</option>
                        <option value="2nd Semester">2nd Semester</option>
                        <option value="Summer">Summer</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Start Date</label>
                    <input type="date" name="start_date" class="input" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">End Date</label>
                    <input type="date" name="end_date" class="input" required>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Holidays</label>
                    <input type="number" name="holidays" class="input" value="0" min="0" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Status</label>
                    <select name="status" class="input" required>
                        <option value="Active">Active</option>
                        <option value="Archived">Archived</option>
                    </select>
                </div>
            </div>
            <div class="pt-2 flex justify-end gap-3">
                <button type="button" onclick="this.closest('#add-term-modal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-md">Cancel</button>
                <button type="submit" class="btn-primary">Save Term</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Term Modal -->
<div id="edit-term-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="this.parentElement.classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-xl shadow-2xl p-6">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-xl font-bold text-[var(--cjc-navy)]">Edit Academic Term</h3>
            <button type="button" onclick="this.closest('#edit-term-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        
        <form id="edit-term-form" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Academic Year</label>
                    <input type="text" name="academic_year" id="edit-term-ay" class="input" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Semester</label>
                    <select name="semester" id="edit-term-sem" class="input" required>
                        <option value="1st Semester">1st Semester</option>
                        <option value="2nd Semester">2nd Semester</option>
                        <option value="Summer">Summer</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Start Date</label>
                    <input type="date" name="start_date" id="edit-term-start" class="input" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">End Date</label>
                    <input type="date" name="end_date" id="edit-term-end" class="input" required>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Holidays</label>
                    <input type="number" name="holidays" id="edit-term-holidays" class="input" min="0" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Status</label>
                    <select name="status" id="edit-term-status" class="input" required>
                        <option value="Active">Active</option>
                        <option value="Archived">Archived</option>
                    </select>
                </div>
            </div>
            <div class="pt-2 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('edit-term-modal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-md">Cancel</button>
                <button type="submit" class="btn-primary">Update Term</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editTerm(id, ay, sem, start, end, holidays, status) {
        document.getElementById('edit-term-form').action = `/admin/settings/terms/${id}`;
        document.getElementById('edit-term-ay').value = ay;
        document.getElementById('edit-term-sem').value = sem;
        document.getElementById('edit-term-start').value = start;
        document.getElementById('edit-term-end').value = end;
        document.getElementById('edit-term-holidays').value = holidays;
        document.getElementById('edit-term-status').value = status;
        document.getElementById('edit-term-modal').classList.remove('hidden');
    }
</script>
@endsection
