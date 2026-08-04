@extends('layouts.app')

@section('title', ' | Admin Login')

@section('content')
<div class="min-h-screen bg-[var(--bg-cream)] hero-pattern flex items-center justify-center p-4 relative">
    
    <!-- Back to Kiosk Button -->
    <a href="{{ route('kiosk.index') }}" style="position: fixed; top: 1.5rem; left: 1.5rem; z-index: 50;" class="flex items-center gap-2 text-[var(--cjc-navy)] bg-white/90 px-4 py-2 rounded-lg shadow border border-[var(--border-light)] hover:text-[var(--cjc-red)] hover:shadow-md transition-all font-semibold">
        <svg style="width: 1.25rem; height: 1.25rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Kiosk
    </a>

    <div class="w-full max-w-md relative z-10">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-white rounded-full shadow-lg mx-auto flex items-center justify-center mb-4 p-2 border-2 border-[var(--cjc-gold)]">
                <img src="/logo.png" alt="CJC Logo" class="w-full h-full object-contain" onerror="this.style.display='none'">
                <span class="font-bold text-[var(--cjc-red)] text-lg" style="display: none;" onload="this.previousElementSibling.style.display==='none' ? this.style.display='block' : null">CJC</span>
            </div>
            <h2 class="text-2xl font-bold text-[var(--cjc-navy)]">LEMS Admin</h2>
            <p class="text-[var(--text-muted)] text-sm font-medium mt-1">Sign in to manage the library system</p>
        </div>

        <!-- Form Card -->
        <div class="card p-8 bg-white/95 backdrop-blur-sm border-t-4 border-t-[var(--cjc-red)] shadow-xl relative overflow-hidden">
            
            <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-5" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
                @csrf
                
                @if($errors->has('email'))
                <div class="p-3 bg-red-50 text-red-600 text-sm rounded-md font-medium flex items-center gap-2 mb-4 border border-red-100">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $errors->first('email') }}</span>
                </div>
                @endif
                
                @if(session('error'))
                <div class="p-3 bg-red-50 text-red-600 text-sm rounded-md font-medium flex items-center gap-2 mb-4 border border-red-100">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
                @endif

                <div>
                    <label for="email" class="block text-sm font-semibold text-[var(--text-warm)] mb-1.5">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus class="input focus:ring-[var(--cjc-red)]">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-[var(--text-warm)] mb-1.5">Password</label>
                    <input type="password" id="password" name="password" required class="input focus:ring-[var(--cjc-red)]">
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="remember" name="remember" class="rounded border-gray-300 text-[var(--cjc-red)] shadow-sm focus:ring-[var(--cjc-red)] w-4 h-4 cursor-pointer">
                    <label for="remember" class="ml-2 text-sm text-[var(--text-muted)] cursor-pointer select-none">Remember me</label>
                </div>

                <button type="submit" class="btn-primary w-full justify-center text-[14px] py-2.5 mt-2 shadow-sm shadow-[var(--cjc-red)]/20" :disabled="isSubmitting" :class="isSubmitting ? 'opacity-70 cursor-not-allowed' : ''">
                    <span x-show="!isSubmitting">Sign In</span>
                    <span x-show="isSubmitting" style="display: none;" class="flex items-center gap-2">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Signing in...
                    </span>
                </button>
            </form>
            
            <div class="mt-6 text-center text-sm text-[var(--text-subtle)] border-t border-[var(--border-light)] pt-6">
                Don't have an account? 
                <button type="button" x-data @click="$dispatch('open-signup-modal')" class="text-[var(--cjc-navy)] font-semibold hover:underline">
                    Request Access
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Signup Modal (Alpine.js) -->
<div x-data="{ open: false }" 
     @open-signup-modal.window="open = true"
     @keydown.escape.window="open = false"
     x-show="open" 
     style="display: none;"
     class="fixed inset-0 z-50 flex items-center justify-center p-4">
    
    <!-- Backdrop -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm"
         @click="open = false"></div>

    <!-- Modal Content -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         class="relative bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        
        <div class="p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-xl font-bold text-[var(--cjc-navy)]">Request Admin Access</h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            
            <p class="text-sm text-gray-500 mb-6">Create an account. A Super Admin must approve your request before you can log in.</p>

            <form action="{{ route('admin.signup') }}" method="POST" class="space-y-4" x-data="{ isRegistering: false }" @submit="isRegistering = true">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="full_name" required class="input">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" required class="input">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Role</label>
                    <select name="role" required class="input bg-white">
                        <option value="Staff">Staff</option>
                        <option value="Librarian">Librarian</option>
                        <option value="Super Admin">Super Admin</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" required minlength="8" class="input">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" required minlength="8" class="input">
                </div>
                
                <div class="pt-4 flex justify-end gap-3 border-t border-gray-100 mt-6">
                    <button type="button" @click="open = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" :disabled="isRegistering">
                        <span x-show="!isRegistering">Submit Request</span>
                        <span x-show="isRegistering" style="display: none;">Submitting...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<x-admin.toast />

@endsection
