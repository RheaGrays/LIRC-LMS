@props([
    'name',
    'value' => '',
    'options' => [],
    'placeholder' => 'Select Option',
    'onChangeSubmit' => true,
    'searchable' => true
])

@php
    $formattedOptions = collect($options)->map(function($opt) {
        if (is_array($opt)) {
            return ['value' => (string)($opt['value'] ?? ''), 'label' => (string)($opt['label'] ?? '')];
        }
        return ['value' => (string)$opt, 'label' => (string)$opt];
    })->values()->all();
@endphp

<div x-data="{ 
        open: false, 
        search: '',
        selectedVal: @js((string)$value),
        selectedLabel: @js($placeholder),
        optionsList: @js($formattedOptions),
        get filteredOptions() {
            if (!this.search || !this.search.trim()) return this.optionsList;
            const q = this.search.toLowerCase().trim();
            return this.optionsList.filter(o => o.label.toLowerCase().includes(q));
        }
     }" 
     x-init="
        const found = optionsList.find(o => String(o.value) === String(selectedVal));
        if (found) {
            selectedLabel = found.label;
        } else if (!selectedVal && optionsList.length > 0 && optionsList[0].value === '') {
            selectedLabel = optionsList[0].label;
        }
     "
     class="relative w-full">
     
    <!-- Hidden Input for Form Submission -->
    <input type="hidden" name="{{ $name }}" :value="selectedVal">

    <!-- Trigger Button -->
    <button type="button" 
            @click="open = !open; if(open) { $nextTick(() => $refs.searchInput?.focus()); }" 
            @click.outside="open = false; search = ''" 
            class="w-full flex items-center justify-between gap-2 px-3 py-2 text-sm bg-white border border-gray-300 rounded-xl hover:border-[var(--cjc-red)] focus:outline-none focus:ring-2 focus:ring-[var(--cjc-red)]/20 transition-all shadow-sm">
        <span class="truncate font-medium text-gray-700" x-text="selectedLabel || @js($placeholder)"></span>
        <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <!-- Dropdown Floating Menu -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
         class="absolute z-50 left-0 right-0 mt-1.5 bg-white rounded-xl shadow-xl border border-gray-100 py-1.5 overflow-hidden"
         style="display: none;">
         
        @if($searchable)
        <!-- Search Bar -->
        <div class="px-2 pb-1.5 pt-1 border-b border-gray-100 bg-gray-50/90 sticky top-0 z-10" x-show="optionsList.length > 5">
            <div class="relative">
                <input type="text" 
                       x-ref="searchInput"
                       x-model="search"
                       @keydown.escape="open = false; search = ''"
                       placeholder="Search options..." 
                       class="w-full pl-7 pr-6 py-1 text-xs font-medium bg-white border border-gray-200 rounded-lg text-gray-800 focus:outline-none focus:border-[var(--cjc-red)] focus:ring-1 focus:ring-[var(--cjc-red)] placeholder-gray-400" />
                <svg class="w-3 h-3 text-gray-400 absolute left-2 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
                </svg>
                <button type="button" x-show="search" @click="search = ''" class="absolute right-1.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        @endif

        <div class="max-h-56 overflow-y-auto py-1">
            <template x-for="opt in filteredOptions" :key="opt.value">
                <button type="button" 
                        @click="
                            selectedVal = opt.value;
                            selectedLabel = opt.label;
                            open = false;
                            search = '';
                            if ({{ $onChangeSubmit ? 'true' : 'false' }}) {
                                $nextTick(() => { $el.closest('form').submit(); });
                            }
                        " 
                        class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-[var(--cjc-red)] flex items-center justify-between transition-colors font-medium">
                    <span x-text="opt.label"></span>
                    <svg x-show="String(selectedVal) === String(opt.value)" class="w-4 h-4 text-[var(--cjc-red)] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </button>
            </template>
            <div x-show="filteredOptions.length === 0" class="px-3 py-3 text-center text-xs text-gray-400 italic">
                No matching options
            </div>
        </div>
    </div>
</div>
