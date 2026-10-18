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
            {{-- MyClasses mark --}}
            <div class="flex justify-center mb-6">
                <svg width="52" height="52" viewBox="0 0 100 100" fill="none" class="animate-floaty">
                    <defs>
                        <linearGradient id="mcE" x1="12" y1="20" x2="88" y2="88" gradientUnits="userSpaceOnUse"><stop stop-color="#2563EB"/><stop offset=".55" stop-color="#1E90FF"/><stop offset="1" stop-color="#06D6C4"/></linearGradient>
                        <linearGradient id="mcEc" x1="28" y1="12" x2="72" y2="36" gradientUnits="userSpaceOnUse"><stop stop-color="#1E63E6"/><stop offset="1" stop-color="#0FB5D6"/></linearGradient>
                    </defs>
                    <path d="M20 82 L20 48 L50 72 L80 48 L80 82" stroke="url(#mcE)" stroke-width="11" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M50 70 L34 60 L34 52 L50 61 L66 52 L66 60 Z" fill="#DBEAFE"/>
                    <path d="M50 15 L73 25 L50 35 L27 25 Z" fill="url(#mcEc)"/>
                    <path d="M39 31 L39 39 Q50 45 61 39 L61 31" fill="#1E3A8A"/>
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

            <p class="mt-10 text-xs text-gray-400">{{ config('app.name', 'MyClasses') }} — Education Management Platform</p>
        </div>
    </div>
</body>
</html>
