<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Offline | LEMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #fefcf8; font-family: 'Inter', sans-serif; }
        .fraunces { font-family: 'Fraunces', serif; }
        .hero-pattern {
            position: absolute; inset: 0; background-image: url('/bg.jpg');
            background-size: cover; background-position: center; opacity: 0.3; z-index: 0;
        }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        .loader {
            width: 14px; height: 14px; border: 2px solid #d63637; border-top-color: transparent;
            border-radius: 50%; animation: spin 1s linear infinite;
        }
    </style>
</head>
<body class="w-full h-screen flex flex-col items-center justify-center relative overflow-hidden m-0">
    <div class="hero-pattern"></div>
    
    <div class="relative z-10 bg-white/60 backdrop-blur-xl border border-white/80 rounded-[28px] shadow-2xl p-10 max-w-[450px] w-[90%] text-center flex flex-col items-center">
        
        <div class="w-20 h-20 rounded-full overflow-hidden border border-gray-200 shadow-sm mb-6 bg-white">
            <img src="/cjc-logo.jpeg" alt="CJC" class="w-full h-full object-cover">
        </div>
        
        <h1 class="fraunces text-3xl font-bold text-[#0f2744] mb-3">System Offline</h1>
        <p class="text-[#64605a] text-[14px] font-medium leading-relaxed mb-8">
            The Library Entrance Monitoring System has temporarily lost connection to the database server.
        </p>

        <div class="flex items-center gap-3 bg-[#0f2744]/5 px-6 py-3 rounded-full border border-[#0f2744]/10 shadow-sm">
            <div class="loader"></div>
            <span class="text-[#0f2744] text-[13px] font-bold tracking-wide">Reconnecting...</span>
        </div>
        
        <p class="text-[#9c988f] text-[12px] mt-6 font-medium">
            Auto-refreshing in <span id="countdown" class="font-bold text-[#d63637]">10</span> seconds
        </p>
    </div>

    <script>
        let count = 10;
        setInterval(() => {
            count--;
            document.getElementById('countdown').innerText = count;
            if (count <= 0) {
                window.location.reload();
            }
        }, 1000);
    </script>
</body>
</html>
