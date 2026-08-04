@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[var(--bg-cream)] hero-pattern flex flex-col relative overflow-hidden">
    <!-- Main Content -->
    <main class="flex-1 relative z-10 flex flex-col min-h-screen">
        @yield('kiosk_content')
    </main>
</div>
@endsection

@push('scripts')
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    console.log('SW registered: ', registration);
                })
                .catch(registrationError => {
                    console.log('SW registration failed: ', registrationError);
                });
        });
    }
</script>
@endpush
