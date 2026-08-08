@props([
    'name',
    'value' => '',
    'options' => [],
    'placeholder' => 'Select Option',
    'onChangeSubmit' => true
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
        selectedVal: @js((string)$value),
        selectedLabel: @js($placeholder),
        optionsList: @js($formattedOptions)
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
            @click="open = !open" 
            @click.outside="open = false" 
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
         class="absolute z-50 left-0 right-0 mt-1.5 bg-white rounded-xl shadow-xl border border-gray-100 py-1.5 max-h-60 overflow-y-auto"
         style="display: none;">
         
        <template x-for="opt in optionsList" :key="opt.value">
            <button type="button" 
                    @click="
                        selectedVal = opt.value;
                        selectedLabel = opt.label;
                        open = false;
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
    </div>
</div>
