@extends('layouts.admin')

@section('title', ' | Library Collections')
@section('header_title', 'Kiosk Library Collections')

@section('admin_content')
<div class="max-w-5xl space-y-6">

    {{-- Flash --}}
    @if(session('success'))
    <div class="flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm font-medium fade-in-up">
        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Description --}}
    <div class="fade-in-up">
        <p class="text-sm text-gray-500">
            Manage the collection slides displayed on the <strong>Kiosk idle screen</strong>. Each active slide rotates automatically every 5 seconds at the bottom of the kiosk display.
        </p>
    </div>

    {{-- Existing Collections --}}
    <div class="card p-0 overflow-hidden fade-in-up">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-[var(--cjc-navy)]">Current Slides</h3>
            <span class="text-xs text-gray-400">{{ $collections->count() }} slide(s)</span>
        </div>

        @if($collections->isEmpty())
        <div class="py-16 text-center text-gray-400 text-sm">
            <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            No collection slides yet. Add one below.
        </div>
        @else
        <div class="divide-y divide-gray-100">
            @foreach($collections as $col)
            <div x-data="{ editing: false }" class="px-6 py-4">

                {{-- View Mode --}}
                <div x-show="!editing" class="flex items-start gap-4">
                    {{-- Badge swatch --}}
                    <div class="w-3 h-3 rounded-full mt-1.5 shrink-0" style="background: {{ $col->badge_color }}"></div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-semibold text-white"
                                  style="background: {{ $col->badge_color }}">
                                {{ $col->badge }}
                            </span>
                            @if(!$col->is_active)
                            <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-500">Hidden</span>
                            @endif
                            <span class="text-[11px] text-gray-400">#{{ $col->sort_order }}</span>
                        </div>
                        <p class="text-sm font-semibold text-[var(--cjc-navy)] mb-0.5">{{ $col->title }}</p>
                        <p class="text-xs text-gray-500 line-clamp-2">{{ $col->description }}</p>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button @click="editing = true"
                                class="px-3 py-1.5 text-xs font-semibold text-[var(--cjc-navy)] border border-gray-200 rounded-lg hover:border-[var(--cjc-navy)] hover:bg-[var(--bg-cream)] transition-colors">
                            Edit
                        </button>
                        <form action="{{ route('admin.library-collections.destroy', $col) }}" method="POST"
                              onsubmit="return confirm('Delete this slide?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="px-3 py-1.5 text-xs font-semibold text-red-500 border border-red-100 rounded-lg hover:bg-red-50 hover:border-red-300 transition-colors">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Edit Mode --}}
                <div x-show="editing" x-cloak>
                    <form action="{{ route('admin.library-collections.update', $col) }}" method="POST" class="space-y-3">
                        @csrf @method('PUT')
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Badge Label</label>
                                <input type="text" name="badge" value="{{ $col->badge }}" class="input" required maxlength="60">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Badge Color</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" name="badge_color" value="{{ $col->badge_color }}"
                                           class="w-10 h-10 rounded cursor-pointer border border-gray-200 p-0.5">
                                    <input type="text" id="color_text_{{ $col->id }}" value="{{ $col->badge_color }}"
                                           class="input font-mono text-sm" maxlength="7"
                                           oninput="document.querySelector('[name=badge_color]').value=this.value">
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Slide Title</label>
                            <input type="text" name="title" value="{{ $col->title }}" class="input" required maxlength="120">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Description</label>
                            <textarea name="description" class="input resize-none h-20" required maxlength="400">{{ $col->description }}</textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Sort Order</label>
                                <input type="number" name="sort_order" value="{{ $col->sort_order }}" class="input" min="0">
                            </div>
                            <div class="flex items-end pb-1">
                                <label class="flex items-center gap-2 cursor-pointer select-none">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" {{ $col->is_active ? 'checked' : '' }}
                                           class="w-4 h-4 rounded border-gray-300 text-[var(--cjc-red)] focus:ring-[var(--cjc-red)] cursor-pointer">
                                    <span class="text-sm font-medium text-gray-700">Active (visible on kiosk)</span>
                                </label>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 pt-1">
                            <button type="submit" class="btn-primary text-sm">Save Changes</button>
                            <button type="button" @click="editing = false" class="btn-secondary text-sm">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Add New Slide --}}
    <div class="card p-0 overflow-hidden fade-in-up" x-data="{ open: false }">
        <button @click="open = !open"
                class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 transition-colors">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-[var(--cjc-red)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="text-sm font-bold text-[var(--cjc-navy)]">Add New Collection Slide</span>
            </div>
            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" x-collapse class="border-t border-gray-100">
            <form action="{{ route('admin.library-collections.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Badge Label <span class="text-red-500">*</span></label>
                        <input type="text" name="badge" placeholder="e.g. Book Collection" class="input" required maxlength="60">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Badge Color <span class="text-red-500">*</span></label>
                        <div class="flex items-center gap-2" x-data="{ hex: '#c0392b' }">
                            <input type="color" name="badge_color" x-model="hex"
                                   class="w-10 h-10 rounded cursor-pointer border border-gray-200 p-0.5">
                            <input type="text" x-model="hex" @input="$el.closest('[x-data]').querySelector('[name=badge_color]').value = $el.value"
                                   class="input font-mono text-sm" maxlength="7" placeholder="#c0392b">
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Slide Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" placeholder="e.g. Print & Reference Archives" class="input" required maxlength="120">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Description <span class="text-red-500">*</span></label>
                    <textarea name="description" class="input resize-none h-24" required maxlength="400"
                              placeholder="A short description shown on the kiosk preview card..."></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Sort Order</label>
                        <input type="number" name="sort_order" value="{{ $collections->max('sort_order') + 1 }}" class="input" min="0">
                    </div>
                    <div class="flex items-end pb-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" checked
                                   class="w-4 h-4 rounded border-gray-300 text-[var(--cjc-red)] focus:ring-[var(--cjc-red)] cursor-pointer">
                            <span class="text-sm font-medium text-gray-700">Active (visible on kiosk)</span>
                        </label>
                    </div>
                </div>
                <div class="pt-1">
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Slide
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Preview Note --}}
    <div class="text-xs text-gray-400 text-center pb-4">
        Changes appear on the kiosk immediately on next page load &nbsp;·&nbsp;
        <a href="{{ route('kiosk.index') }}" target="_blank" class="text-[var(--cjc-red)] hover:underline">Preview Kiosk</a>
    </div>

</div>
@endsection
