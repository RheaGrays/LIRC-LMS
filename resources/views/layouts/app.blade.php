<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="icon" type="image/png" href="/CorJesu Logo.png">
        <link rel="apple-touch-icon" href="/CorJesu Logo.png">
        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#0f2744">



        <!-- Scripts & Styles -->
        <style>[x-cloak] { display: none !important; }</style>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        @stack('styles')
    </head>
    <body class="font-sans antialiased bg-[#fcf9f2] text-[var(--cjc-navy)]">
        <!-- FALLBACK: Shown only when JS fails (hidden by Alpine once it boots) -->
        <div id="js-crash-fallback" style="display:none; position:fixed; inset:0; z-index:99999; background:#0f172a; color:#fff; font-family:system-ui, -apple-system, sans-serif; align-items:center; justify-content:center; text-align:center;">
            <div style="max-width:480px; padding:40px; background:#1e293b; border-radius:16px; border:1px solid #334155; margin:20px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5);">
                <div style="width:56px; height:56px; background:#fee2e2; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                    <svg style="width:28px; height:28px; color:#dc2626;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h2 style="color:#f87171; font-size:22px; font-weight:700; margin:0 0 12px;">Interface Loading Error</h2>
                <p style="color:#94a3b8; font-size:14px; line-height:1.6; margin:0 0 24px;">The application encountered an error while initializing the interface. This could be due to a script interruption or connectivity issue.</p>
                <div style="display:flex; gap:12px; justify-content:center;">
                    <button onclick="window.location.reload()" style="background:#c41e3a; color:#fff; border:none; padding:12px 24px; border-radius:10px; font-weight:bold; font-size:14px; cursor:pointer; transition:background 0.2s;">
                        Reload Application
                    </button>
                </div>
            </div>
        </div>
        <script>
            // Show fallback if Alpine hasn't initialized within 10 seconds
            setTimeout(function() {
                if (!window.Alpine || !document.querySelector('[x-data]')) {
                    var el = document.getElementById('js-crash-fallback');
                    if (el) el.style.display = 'flex';
                }
            }, 10000);
        </script>
        @yield('content')

        @stack('scripts')
    </body>
</html>
