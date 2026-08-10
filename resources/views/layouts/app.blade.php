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
        @yield('content')

        @stack('scripts')
    </body>
</html>
