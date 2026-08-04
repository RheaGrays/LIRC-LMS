@extends('layouts.admin')

@section('title', ' | Settings')
@section('header_title', 'System Settings')

@section('admin_content')
<div class="max-w-4xl space-y-6">

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
@endsection
