@extends('layouts.app')

@section('content')
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/airbnb.css">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <style>
        /* Custom Modern Tom Select Styling */
        .ts-wrapper {
            position: relative !important;
            width: 100% !important;
        }
        .ts-control {
            border: 1px solid #d1d5db !important;
            border-radius: 8px !important;
            padding: 8px 36px 8px 12px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
            background-color: #fff !important;
            color: var(--cjc-navy) !important;
            min-height: 38px !important;
            cursor: pointer !important;
            transition: all 150ms ease !important;
            display: flex !important;
            align-items: center !important;
        }
        .ts-control.focus, .ts-wrapper.focus .ts-control {
            border-color: var(--cjc-red) !important;
            box-shadow: 0 0 0 3px rgba(196, 30, 42, 0.12) !important;
        }
        .ts-dropdown {
            border-radius: 12px !important;
            border: 1px solid #e5e7eb !important;
            box-shadow: 0 12px 30px -5px rgba(0, 0, 0, 0.12), 0 4px 10px -4px rgba(0, 0, 0, 0.05) !important;
            font-size: 13px !important;
            color: var(--cjc-navy) !important;
            margin-top: 6px !important;
            padding: 6px !important;
            background: #ffffff !important;
            z-index: 100 !important;
            overflow: hidden !important;
        }
        .ts-dropdown .option {
            padding: 9px 12px !important;
            border-radius: 8px !important;
            font-weight: 500 !important;
            color: #374151 !important;
            cursor: pointer !important;
            transition: all 120ms ease !important;
            margin-bottom: 2px !important;
        }
        .ts-dropdown .option:last-child {
            margin-bottom: 0 !important;
        }
        .ts-dropdown .option:hover, .ts-dropdown .option.active {
            background-color: #f3f4f6 !important;
            color: var(--cjc-navy) !important;
            font-weight: 600 !important;
        }
        .ts-dropdown .option.selected {
            background-color: #fef2f2 !important;
            color: var(--cjc-red) !important;
            font-weight: 700 !important;
        }
    </style>
@endpush
<div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside class="sidebar flex-shrink-0 z-20">
        <div class="px-6 py-6 mb-4 flex items-center gap-3 border-b border-white/10">
            <div class="w-9 h-9 rounded-full overflow-hidden bg-white shrink-0 shadow-sm border border-white/20 p-0.5">
                <img src="/CorJesu Logo.png" alt="CJC Logo" class="w-full h-full object-cover rounded-full">
            </div>
            <div class="flex flex-col justify-center">
                <h1 class="text-[22px] font-black text-amber-500 font-['Fraunces'] leading-none tracking-wide drop-shadow-md">LIRC</h1>
                <span class="text-[10px] font-bold text-slate-300 tracking-[0.1em] uppercase mt-0.5 drop-shadow-sm">{{ auth('admin')->user()->role ?? 'SUPER ADMIN' }}</span>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-2 py-4">
            <!-- MAIN Category -->
            <div class="px-4 mb-2 mt-2">
                <span class="text-[11px] font-bold text-white/40 tracking-wider uppercase">Main</span>
            </div>
            
            <a href="{{ route('admin.dashboard') }}" class="sidebar-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                Dashboard
            </a>
            
            <a href="{{ route('admin.students.index') }}" class="sidebar-nav-item {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Patrons
            </a>

            <a href="{{ route('admin.library-collections.index') }}" class="sidebar-nav-item {{ request()->routeIs('admin.library-collections.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                Library Slides
            </a>

            <a href="{{ route('admin.analytics.index') }}" class="sidebar-nav-item {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                </svg>
                Analytics
            </a>


            <a href="{{ route('admin.sections.index') }}" class="sidebar-nav-item {{ request()->routeIs('admin.sections.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Section Counter
            </a>
            
            <a href="{{ route('admin.statistics.index') }}" class="sidebar-nav-item {{ request()->routeIs('admin.statistics.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                Seating Statistics
            </a>

            <!-- SYSTEM Category -->
            <div class="px-4 mb-2 mt-6">
                <span class="text-[11px] font-bold text-white/40 tracking-wider uppercase">System</span>
            </div>

            @if(auth('admin')->user()->isSuperAdmin())
            <a href="{{ route('admin.approvals.index') }}" class="sidebar-nav-item {{ request()->routeIs('admin.approvals.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Approvals
            </a>
            @endif

            <a href="{{ route('admin.audit.index') }}" class="sidebar-nav-item {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Audit Logs
            </a>

            <a href="{{ route('admin.academics.index') }}" class="sidebar-nav-item {{ request()->routeIs('admin.academics.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Departments
            </a>

            @if(auth('admin')->user()->isSuperAdmin())
            <a href="{{ route('admin.settings.index') }}" class="sidebar-nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Settings
            </a>
            @endif
        </nav>

        <div class="p-4 border-t border-white/10 mt-auto">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 text-white min-w-0">
                    <div class="w-8 h-8 flex-shrink-0 rounded-full bg-white/20 flex items-center justify-center font-bold text-sm">
                        {{ auth('admin')->user()->avatar_initials ?: substr(auth('admin')->user()->full_name, 0, 2) }}
                    </div>
                    <div class="text-sm min-w-0 overflow-hidden">
                        <div class="font-semibold truncate leading-tight" title="{{ auth('admin')->user()->full_name }}">{{ auth('admin')->user()->full_name }}</div>
                        <div class="text-xs text-white/60 truncate mt-0.5" title="{{ auth('admin')->user()->role }}">{{ auth('admin')->user()->role }}</div>
                    </div>
                </div>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="p-2 text-white/60 hover:text-white rounded-md hover:bg-white/10 transition-colors" title="Logout">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden bg-[var(--bg-cream)]">
        <header class="h-16 flex-shrink-0 bg-white border-b border-[var(--border-light)] px-8 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-[var(--cjc-navy)]">@yield('header_title')</h2>
            
            <div class="flex items-center gap-4">
                <a href="{{ route('kiosk.index') }}" target="_blank" class="text-sm font-medium text-[var(--cjc-red)] hover:text-[var(--cjc-red-dark)] flex items-center gap-1">
                    Open Kiosk
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8">
            @yield('admin_content')
        </div>
    </main>
</div>

<!-- Flash Messages (Toasts) -->
<x-admin.toast />

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Flatpickr
            flatpickr('input[type="date"]', {
                dateFormat: "Y-m-d",
                allowInput: true
            });

            // Initialize Tom Select for filter dropdowns only (exclude modals and flatpickr)
            document.querySelectorAll('select:not(.flatpickr-monthDropdown-months):not(.no-tomselect)').forEach((el) => {
                if (!el.closest('.fixed') && !el.closest('#add-dept-modal') && !el.closest('#edit-dept-modal') && !el.closest('#add-prog-modal') && !el.closest('#edit-prog-modal')) {
                    new TomSelect(el, {
                        create: false,
                        sortField: false
                    });
                }
            });
        });
    </script>
@endpush
@endsection
