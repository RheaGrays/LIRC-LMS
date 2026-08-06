@extends('layouts.app')

@section('content')
<div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside class="sidebar flex-shrink-0 z-20">
        <div class="px-6 py-6 mb-4 flex items-center justify-center border-b border-white/10">
            <h1 class="text-xl font-bold text-white tracking-tight">LEMS Admin</h1>
        </div>

        <nav class="flex-1 overflow-y-auto space-y-1 py-4">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg class="w-5 h-5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </a>
            
            <a href="{{ route('admin.students.index') }}" class="sidebar-nav-item {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Students & Violations
            </a>

            <a href="{{ route('admin.sections.index') }}" class="sidebar-nav-item {{ request()->routeIs('admin.sections.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                Section Counter
            </a>

            <a href="{{ route('admin.library-collections.index') }}" class="sidebar-nav-item {{ request()->routeIs('admin.library-collections.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                Library Slides
            </a>

            <a href="{{ route('admin.analytics.index') }}" class="sidebar-nav-item {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Analytics
            </a>

            <a href="{{ route('admin.audit.index') }}" class="sidebar-nav-item {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                Audit Log
            </a>

            <a href="{{ route('admin.settings.index') }}" class="sidebar-nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Settings
            </a>

            @if(auth('admin')->user()->isSuperAdmin())
            <a href="{{ route('admin.approvals.index') }}" class="sidebar-nav-item {{ request()->routeIs('admin.approvals.*') ? 'active' : '' }}">
                <svg class="w-5 h-5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                Approvals
            </a>
            @endif
        </nav>

        <div class="p-4 border-t border-white/10 mt-auto">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 text-white">
                    <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center font-bold text-sm">
                        {{ auth('admin')->user()->avatar_initials ?: substr(auth('admin')->user()->full_name, 0, 2) }}
                    </div>
                    <div class="text-sm">
                        <div class="font-semibold">{{ auth('admin')->user()->full_name }}</div>
                        <div class="text-xs text-white/60">{{ auth('admin')->user()->role }}</div>
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
@endsection
