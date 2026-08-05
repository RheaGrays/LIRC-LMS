@extends('layouts.app')

@section('title', ' | Admin Login')

@section('content')
<div class="min-h-screen bg-[var(--bg-cream)] hero-pattern flex items-center justify-center p-4 relative">
    
    <!-- Back to Kiosk Button -->
    <a href="{{ route('kiosk.index') }}" title="Back to Kiosk" style="position: fixed; top: 1.5rem; left: 1.5rem; z-index: 50;" class="flex items-center justify-center text-[var(--cjc-navy)] bg-white w-11 h-11 rounded-xl shadow-[0_2px_10px_rgb(0,0,0,0.08)] border border-gray-100 hover:text-[#c41e2a] hover:shadow-md hover:-translate-x-1 transition-all">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
    </a>


    <div class="w-full max-w-[28rem] relative z-10">
        <!-- Form Card -->
        <div class="bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-8 sm:p-10 relative overflow-hidden">
            
            <!-- Header (Logo + Titles) inside the card -->
            <div class="text-center mb-8">
                <a href="{{ route('kiosk.index') }}" title="Back to Kiosk" class="inline-block transition-transform hover:scale-105 active:scale-95 cursor-pointer">
                    <img src="/cjc-logo.jpeg" alt="CJC Logo" class="w-[4.5rem] h-[4.5rem] object-contain mx-auto mb-4 drop-shadow-sm" onerror="this.src='/logo.png'">
                </a>
                <h3 class="text-[0.65rem] font-bold tracking-[0.15em] text-[var(--cjc-navy)] uppercase mb-1">Cor Jesu College</h3>
                <h2 class="text-2xl sm:text-[1.65rem] font-extrabold text-[var(--cjc-navy)] mb-2" style="font-family: ui-serif, Georgia, Cambria, 'Times New Roman', Times, serif;">LIRC Admin Portal</h2>
                <p class="text-gray-500 text-[0.9rem]">Library Entrance Monitoring System</p>
            </div>

            <hr class="border-gray-100 mb-8">

            <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-6" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
                @csrf
                
                @if($errors->has('email'))
                <div class="p-3 bg-red-50 text-red-600 text-sm rounded-lg font-medium flex items-center gap-2 border border-red-100">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $errors->first('email') }}</span>
                </div>
                @endif
                
                @if(session('error'))
                <div class="p-3 bg-red-50 text-red-600 text-sm rounded-lg font-medium flex items-center gap-2 border border-red-100">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
                @endif

                <div>
                    <label for="email" class="block text-[0.7rem] font-bold tracking-wider text-gray-500 uppercase mb-2">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@cjc.edu.ph" required autofocus 
                           class="w-full px-4 py-3.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-gray-100 focus:border-gray-300 transition-colors">
                </div>

                <div x-data="{ showPassword: false }">
                    <label for="password" class="block text-[0.7rem] font-bold tracking-wider text-gray-500 uppercase mb-2">Password</label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" id="password" name="password" placeholder="••••••••" required 
                               class="w-full px-4 py-3.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-gray-100 focus:border-gray-300 transition-colors">
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                            <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="showPassword" style="display: none;" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full bg-[#c41e2a] hover:bg-[#a61a20] text-white font-bold py-3.5 px-4 rounded-xl transition-colors mt-2" :disabled="isSubmitting" :class="isSubmitting ? 'opacity-70 cursor-not-allowed' : ''">
                    <span x-show="!isSubmitting">Sign In</span>
                    <span x-show="isSubmitting" style="display: none;" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Signing in...
                    </span>
                </button>
            </form>
            
            <div class="mt-8 text-center text-sm text-gray-400 font-medium">
                Don't have an account? 
                <button type="button" x-data @click="$dispatch('open-signup-modal')" class="text-[var(--cjc-navy)] font-bold hover:underline">
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

            <form action="{{ route('admin.signup') }}" method="POST" class="space-y-5" x-data="{ isRegistering: false }" @submit="isRegistering = true">
                @csrf
                <div>
                    <label class="block text-[0.7rem] font-bold tracking-wider text-gray-500 uppercase mb-2">Full Name</label>
                    <input type="text" name="full_name" placeholder="Juan Dela Cruz" required 
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-gray-100 focus:border-gray-300 transition-colors">
                </div>
                
                <div>
                    <label class="block text-[0.7rem] font-bold tracking-wider text-gray-500 uppercase mb-2">Email Address</label>
                    <input type="email" name="email" placeholder="you@cjc.edu.ph" required 
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-gray-100 focus:border-gray-300 transition-colors">
                </div>

                <div x-data="{ 
                        dropdownOpen: false, 
                        selectedRole: 'Staff',
                        roles: ['Staff', 'Librarian', 'Super Admin'],
                        selectRole(role) {
                            this.selectedRole = role;
                            this.dropdownOpen = false;
                        }
                    }" class="relative">
                    <label class="block text-[0.7rem] font-bold tracking-wider text-gray-500 uppercase mb-2">Role</label>
                    
                    <!-- Hidden input for actual form submission -->
                    <input type="hidden" name="role" :value="selectedRole">
                    
                    <!-- Custom Select Button -->
                    <button type="button" @click="dropdownOpen = !dropdownOpen" @click.outside="dropdownOpen = false" 
                            class="w-full flex items-center justify-between px-4 py-3 border rounded-xl text-sm bg-white hover:bg-gray-50 transition-colors text-left focus:outline-none"
                            :class="dropdownOpen ? 'border-gray-300 ring-2 ring-gray-100' : 'border-gray-200'">
                        <span x-text="selectedRole" class="text-gray-800 font-medium"></span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="dropdownOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Custom Dropdown Menu -->
                    <div x-show="dropdownOpen" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         style="display: none;"
                         class="absolute z-50 w-full mt-2 bg-white rounded-xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] border border-gray-100 py-1.5 overflow-hidden">
                        
                        <template x-for="role in roles" :key="role">
                            <button type="button" @click="selectRole(role)"
                                    class="w-full text-left px-4 py-2.5 text-sm transition-colors flex items-center justify-between group"
                                    :class="selectedRole === role ? 'bg-red-50/50 text-[#c41e2a] font-semibold' : 'text-gray-700 hover:bg-gray-50'">
                                <span x-text="role"></span>
                                <svg x-show="selectedRole === role" class="w-4 h-4 text-[#c41e2a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[0.7rem] font-bold tracking-wider text-gray-500 uppercase mb-2">Password</label>
                        <input type="password" name="password" placeholder="••••••••" required minlength="8" 
                               class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-gray-100 focus:border-gray-300 transition-colors">
                    </div>
                    <div>
                        <label class="block text-[0.7rem] font-bold tracking-wider text-gray-500 uppercase mb-2">Confirm</label>
                        <input type="password" name="password_confirmation" placeholder="••••••••" required minlength="8" 
                               class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-gray-100 focus:border-gray-300 transition-colors">
                    </div>
                </div>
                
                <div class="pt-2 flex justify-end gap-3 mt-6">
                    <button type="button" @click="open = false" class="px-5 py-2.5 text-sm font-semibold text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="bg-[#c41e2a] hover:bg-[#a61a20] text-white px-6 py-2.5 text-sm font-bold rounded-xl transition-colors shadow-sm" :disabled="isRegistering">
                        <span x-show="!isRegistering">Submit Request</span>
                        <span x-show="isRegistering" style="display: none;" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Submitting...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<x-admin.toast />

@endsection
