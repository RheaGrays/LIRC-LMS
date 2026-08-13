@extends('layouts.app')

@section('title', ' | Patron Registration')

@section('content')
<div x-data="registrationApp()" 
     class="min-h-screen bg-[var(--bg-cream)] hero-pattern flex flex-col relative overflow-hidden"
     @photo-captured.window="capturedImage = $event.detail.dataUrl; photoTaken = true;"
     @photo-retaken.window="capturedImage = null; photoTaken = false;">
     
@include('register.partials._styles')

@include('register.partials._header')

    <!-- Main -->
    <main class="flex-1 relative z-10 flex items-center justify-center p-6 md:p-8">
        <div class="fade-in-up w-full max-w-xl">
            <template x-if="step !== 'done'">
                <div>
                    <div class="mb-6 text-center">
                        <h1 class="font-['Fraunces'] text-2xl md:text-[28px] font-bold text-[var(--cjc-navy)] m-0 mb-1">
                            Patron Registration
                        </h1>
                        <p class="text-[13px] text-[var(--text-muted)] font-['Inter'] m-0">
                            Create your library account to check in and out.
                        </p>
                    </div>

@include('register.partials._step_indicator')
                </div>
            </template>

            <!-- Card -->
            <div class="bg-white border border-[var(--border-light)] rounded-[var(--radius-xl)] shadow-[var(--shadow-lg)] overflow-hidden">

@include('register.partials._step_1_info')

@include('register.partials._step_2_photo')

@include('register.partials._step_3_confirm')

@include('register.partials._step_4_done')

@include('register.partials._footer_nav')

            </div>
        </div>
    </main>
</div>
@endsection

@include('register.partials._scripts')
