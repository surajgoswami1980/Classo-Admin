<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code', 'Error') · {{ config('app.name', 'Quilo') }}</title>
    @include('layouts.partials.head-assets')
    <script>
        Object.assign(tailwind.config.theme.extend.keyframes, {
            floaty: { '0%,100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-12px)' } },
            popin: { '0%': { opacity: 0, transform: 'scale(.9)' }, '100%': { opacity: 1, transform: 'scale(1)' } },
        });
        Object.assign(tailwind.config.theme.extend.animation, { floaty: 'floaty 4s ease-in-out infinite', popin: 'popin .4s ease-out' });
    </script>
</head>
<body class="h-full font-sans antialiased bg-canvas">
    <div class="min-h-full flex items-center justify-center px-4 py-16">
        <div class="w-full max-w-lg text-center animate-popin">
            {{-- MyClasses mark --}}
            <div class="flex justify-center mb-6">
                <img src="{{ asset('images/myclasses-logo.svg') }}" alt="MyClasses" width="56" height="56" class="animate-floaty"
                     onerror="this.onerror=null;this.src='{{ asset('images/myclasses-logo.jpg') }}'">
            </div>

            {{-- Big code --}}
            <p class="text-7xl sm:text-8xl font-black tracking-tight bg-gradient-to-br @yield('code-gradient', 'from-primary-600 to-navy-800') bg-clip-text text-transparent">
                @yield('code', 'Error')
            </p>

            <h1 class="mt-2 text-xl sm:text-2xl font-bold text-gray-900">@yield('title', 'Something went wrong')</h1>
            <p class="mt-2 text-sm text-gray-500 max-w-sm mx-auto">@yield('message', 'An unexpected error occurred. Please try again.')</p>

            <div class="mt-8 flex items-center justify-center gap-3">
                <a href="{{ url('/') }}" class="btn btn-primary">
                    Go to Home
                </a>
                <button onclick="history.back()" class="btn btn-secondary">
                    Go Back
                </button>
            </div>

            @yield('extra')

            <p class="mt-10 text-xs text-gray-400">{{ config('app.name', 'MyClasses') }} — Education Management Platform</p>
        </div>
    </div>
</body>
</html>
