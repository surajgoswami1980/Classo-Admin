<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code', 'Error') · {{ config('app.name', 'Quilo') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: {
            fontFamily: { sans: ['Inter', 'sans-serif'] },
            colors: { primary: { 50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a8a' } },
            keyframes: {
                floaty: { '0%,100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-12px)' } },
                popin: { '0%': { opacity: 0, transform: 'scale(.9)' }, '100%': { opacity: 1, transform: 'scale(1)' } },
            },
            animation: { floaty: 'floaty 4s ease-in-out infinite', popin: 'popin .4s ease-out' },
        } } }
    </script>
</head>
<body class="h-full font-sans antialiased bg-gradient-to-br from-slate-50 via-white to-blue-50">
    <div class="min-h-full flex items-center justify-center px-4 py-16">
        <div class="w-full max-w-lg text-center animate-popin">
            {{-- Quilo mark --}}
            <div class="flex justify-center mb-6">
                <svg width="48" height="48" viewBox="0 0 100 100" fill="none" class="animate-floaty">
                    <defs>
                        <linearGradient id="qr" x1="15" y1="10" x2="85" y2="90" gradientUnits="userSpaceOnUse"><stop stop-color="#2EA7FF"/><stop offset=".55" stop-color="#1E6FE0"/><stop offset="1" stop-color="#1746B0"/></linearGradient>
                        <linearGradient id="qt" x1="45" y1="55" x2="80" y2="95" gradientUnits="userSpaceOnUse"><stop stop-color="#2BD4C4"/><stop offset="1" stop-color="#15A58E"/></linearGradient>
                    </defs>
                    <circle cx="46" cy="42" r="30" stroke="url(#qr)" stroke-width="14"/>
                    <path d="M50 54 L84 92 L64 92 L40 64 Z" fill="url(#qt)"/>
                </svg>
            </div>

            {{-- Big code --}}
            <p class="text-7xl sm:text-8xl font-black tracking-tight bg-gradient-to-br @yield('code-gradient', 'from-blue-600 to-indigo-600') bg-clip-text text-transparent">
                @yield('code', 'Error')
            </p>

            <h1 class="mt-2 text-xl sm:text-2xl font-bold text-gray-900">@yield('title', 'Something went wrong')</h1>
            <p class="mt-2 text-sm text-gray-500 max-w-sm mx-auto">@yield('message', 'An unexpected error occurred. Please try again.')</p>

            <div class="mt-8 flex items-center justify-center gap-3">
                <a href="{{ url('/') }}" class="px-5 py-2.5 bg-primary-600 text-white rounded-xl text-sm font-semibold shadow-sm hover:bg-primary-700 transition active:scale-95">
                    Go to Home
                </a>
                <button onclick="history.back()" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-50 transition active:scale-95">
                    Go Back
                </button>
            </div>

            @yield('extra')

            <p class="mt-10 text-xs text-gray-400">{{ config('app.name', 'Quilo') }} — Multi-Tenant SaaS ERP</p>
        </div>
    </div>
</body>
</html>
