<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin Login - {{ config('app.name', 'MyClasses') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: { 50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd', 400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a' },
                        cyanx: { 400: '#22d3ee', 500: '#06d6c4' },
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        .mc-bg { background: linear-gradient(135deg, #eef5ff 0%, #e6f7fb 55%, #f3f9ff 100%); }
        .mc-hero-gradient { background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 45%, #06d6c4 100%); }
        .float { animation: float 6s ease-in-out infinite; }
        .float:nth-child(2){animation-delay:-1.5s} .float:nth-child(3){animation-delay:-3s} .float:nth-child(4){animation-delay:-4.5s}
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-16px)} }
        @keyframes fade-up { 0%{opacity:0;transform:translateY(16px)} 100%{opacity:1;transform:translateY(0)} }
        .fade-up { animation: fade-up .6s ease-out both; }
        .fade-up-1{animation-delay:.1s} .fade-up-2{animation-delay:.2s} .fade-up-3{animation-delay:.3s} .fade-up-4{animation-delay:.4s}
        .chip-pop { animation: fade-up .5s ease-out both; }
        @keyframes dash { to { stroke-dashoffset: 0; } }
    </style>
</head>
<body class="h-full font-sans antialiased mc-bg">
    <div class="min-h-full grid lg:grid-cols-2">

        <!-- ─── LEFT: Brand showcase ─────────────────────────────── -->
        <div class="relative hidden lg:flex flex-col justify-center px-14 xl:px-20 overflow-hidden">
            <!-- soft floating blobs -->
            <div class="float absolute top-16 right-24 w-28 h-28 rounded-full bg-primary-200/40 blur-xl"></div>
            <div class="float absolute bottom-24 left-10 w-36 h-36 rounded-full bg-cyanx-400/20 blur-2xl"></div>
            <div class="float absolute top-1/2 right-8 w-20 h-20 rounded-2xl bg-primary-300/20 blur-lg"></div>

            <div class="relative z-10 max-w-xl">
                <!-- Logo + wordmark -->
                <div class="flex items-center gap-3 fade-up">
                    <x-quilo-logo :size="60" />
                    <div class="leading-none">
                        <div class="text-4xl font-black tracking-tight">
                            <span class="text-primary-600">My</span><span class="text-gray-900">Classes</span>
                        </div>
                    </div>
                </div>

                <!-- Tagline -->
                <h1 class="mt-6 text-2xl xl:text-3xl font-extrabold fade-up fade-up-1">
                    <span class="bg-gradient-to-r from-primary-600 to-cyanx-500 bg-clip-text text-transparent">Manage Today. Build Brighter Tomorrows.</span>
                </h1>
                <p class="mt-3 text-gray-500 fade-up fade-up-1">Complete Education Management Platform</p>

                <!-- Feature chips -->
                <div class="mt-10 grid grid-cols-3 gap-4 fade-up fade-up-2">
                    @php
                        $chips = [
                            ['Admissions', 'Simplify Enrollments', 'bg-blue-100 text-blue-600', 'M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z'],
                            ['Academics', 'Plan & Manage', 'bg-emerald-100 text-emerald-600', 'M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5'],
                            ['Fees', 'Track Payments', 'bg-amber-100 text-amber-600', 'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                            ['Students', 'Engage Easily', 'bg-sky-100 text-sky-600', 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'],
                            ['Reports', 'Better Decisions', 'bg-rose-100 text-rose-600', 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z'],
                            ['More', 'All in One', 'bg-violet-100 text-violet-600', 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 .255c-.007.378.138.75.43.991l1.005.828c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.991l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.281Z'],
                        ];
                    @endphp
                    @foreach($chips as $i => $chip)
                        <div class="chip-pop flex flex-col items-center text-center gap-2" style="animation-delay: {{ 0.25 + $i*0.07 }}s">
                            <div class="flex items-center justify-center w-12 h-12 rounded-2xl {{ $chip[2] }} shadow-sm">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $chip[3] }}" /></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-800">{{ $chip[0] }}</p>
                                <p class="text-[10px] text-gray-400 leading-tight">{{ $chip[1] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- mini dashboard preview card -->
                <div class="mt-10 fade-up fade-up-4 rounded-2xl bg-white shadow-xl shadow-primary-900/5 border border-gray-100 p-5 max-w-md">
                    <div class="flex items-center gap-2 mb-4">
                        <x-quilo-logo :size="22" />
                        <span class="text-sm font-bold text-gray-800">Dashboard</span>
                        <span class="ml-auto text-[10px] text-gray-400">Live preview</span>
                    </div>
                    <div class="grid grid-cols-4 gap-2 mb-3">
                        @foreach([['1,248','Students','text-primary-600'],['86','Teachers','text-emerald-600'],['64','Classes','text-sky-600'],['₹12.4L','Fees','text-amber-600']] as $s)
                            <div class="bg-gray-50 rounded-lg p-2 text-center">
                                <p class="text-sm font-black {{ $s[2] }}">{{ $s[0] }}</p>
                                <p class="text-[9px] text-gray-400">{{ $s[1] }}</p>
                            </div>
                        @endforeach
                    </div>
                    <div class="h-14 flex items-end gap-1.5">
                        @foreach([42,60,48,78,56,88,70,64] as $h)
                            <div class="flex-1 bg-gradient-to-t from-primary-500 to-cyanx-400 rounded-t" style="height: {{ $h }}%"></div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── RIGHT: Login card ────────────────────────────────── -->
        <div class="flex items-center justify-center px-6 py-12 lg:px-12">
            <div class="w-full max-w-md fade-up fade-up-2">
                <div class="rounded-3xl bg-white/90 backdrop-blur shadow-2xl shadow-primary-900/10 border border-white/60 p-8 sm:p-10">

                    <!-- Logo -->
                    <div class="flex flex-col items-center text-center mb-7">
                        <x-quilo-logo :size="56" />
                        <div class="mt-2 text-2xl font-black tracking-tight">
                            <span class="text-primary-600">My</span><span class="text-gray-900">Classes</span>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-0.5">Manage Today. Build Brighter Tomorrows.</p>
                    </div>

                    <div class="text-center mb-6">
                        <h2 class="text-xl font-bold text-gray-900">Admin Login</h2>
                        <p class="mt-1 text-sm text-gray-500">Sign in to access your admin dashboard</p>
                    </div>

                    @if($errors->any())
                        <div class="mb-5 p-3.5 rounded-xl bg-red-50 border border-red-200 flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                            <p class="text-sm text-red-700">{{ $errors->first() }}</p>
                        </div>
                    @endif

                    <div x-data="loginForms()">
                        <!-- Mode toggle -->
                        <div class="flex p-1 mb-6 bg-gray-100 rounded-xl">
                            <button type="button" @click="mode='password'" :class="mode==='password' ? 'bg-white text-primary-600 shadow-sm' : 'text-gray-500'" class="flex-1 py-2 text-sm font-semibold rounded-lg transition-all">Password</button>
                            <button type="button" @click="mode='otp'" :class="mode==='otp' ? 'bg-white text-primary-600 shadow-sm' : 'text-gray-500'" class="flex-1 py-2 text-sm font-semibold rounded-lg transition-all">OTP</button>
                        </div>

                        <!-- PASSWORD -->
                        <form x-show="mode==='password'" method="POST" action="{{ route('login.submit') }}" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                                    </div>
                                    <input name="email" type="email" autocomplete="email" value="{{ old('email') }}" placeholder="admin@school.com"
                                           class="block w-full rounded-xl border border-gray-200 bg-gray-50 py-3 pl-11 pr-4 text-sm text-gray-900 placeholder:text-gray-400 focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none transition-all">
                                </div>
                            </div>
                            <div x-data="{ showPassword: false }">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                                    </div>
                                    <input name="password" :type="showPassword ? 'text' : 'password'" autocomplete="current-password" placeholder="Enter your password"
                                           class="block w-full rounded-xl border border-gray-200 bg-gray-50 py-3 pl-11 pr-11 text-sm text-gray-900 placeholder:text-gray-400 focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none transition-all">
                                    <button type="button" @click="showPassword=!showPassword" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 hover:text-gray-600">
                                        <svg x-show="!showPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                        <svg x-show="showPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    <span class="text-sm text-gray-600">Remember me</span>
                                </label>
                                <span class="text-sm text-primary-600 font-medium">Forgot password?</span>
                            </div>
                            <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-primary-600/30 hover:from-primary-700 hover:to-primary-600 transition-all active:scale-[0.98]">
                                Sign In
                            </button>
                        </form>

                        <!-- OTP -->
                        <div x-show="mode==='otp'" class="space-y-5">
                            <div x-show="msg" x-text="msg" :class="msgError ? 'bg-red-50 border-red-200 text-red-700' : 'bg-blue-50 border-blue-200 text-blue-700'" class="p-3 rounded-lg border text-sm" style="display:none"></div>
                            <div x-show="otpStep===1" class="space-y-5">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email or Mobile</label>
                                    <input type="text" x-model="identifier" placeholder="Registered email or mobile number"
                                           class="block w-full rounded-xl border border-gray-200 bg-gray-50 py-3 px-4 text-sm text-gray-900 placeholder:text-gray-400 focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none transition-all">
                                </div>
                                <div class="flex gap-2">
                                    <button type="button" @click="channel='email'" :class="channel==='email' ? 'border-primary-500 bg-blue-50 text-primary-600' : 'border-gray-200 text-gray-500'" class="flex-1 py-2.5 text-sm font-medium rounded-xl border transition">Email</button>
                                    <button type="button" @click="channel='mobile'" :class="channel==='mobile' ? 'border-primary-500 bg-blue-50 text-primary-600' : 'border-gray-200 text-gray-500'" class="flex-1 py-2.5 text-sm font-medium rounded-xl border transition">Mobile</button>
                                </div>
                                <button type="button" @click="sendOtp()" :disabled="loading" class="w-full rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-primary-600/30 transition disabled:opacity-60"><span x-text="loading ? 'Sending...' : 'Send OTP'"></span></button>
                            </div>
                            <div x-show="otpStep===2" class="space-y-5">
                                <p class="text-sm text-gray-500">OTP sent to <span class="font-medium text-gray-700" x-text="masked"></span></p>
                                <input type="text" inputmode="numeric" maxlength="6" x-model="otp" placeholder="______"
                                       class="block w-full rounded-xl border border-gray-200 bg-gray-50 py-3 px-4 text-center text-2xl tracking-[0.5em] font-mono text-gray-900 focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none transition-all">
                                <button type="button" @click="verifyOtp()" :disabled="loading" class="w-full rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-primary-600/30 transition disabled:opacity-60"><span x-text="loading ? 'Verifying...' : 'Verify & Sign In'"></span></button>
                                <div class="flex items-center justify-between text-sm">
                                    <button type="button" @click="otpStep=1; msg=''" class="text-gray-500 hover:text-primary-600">← Back</button>
                                    <button type="button" @click="sendOtp()" :disabled="loading" class="text-primary-600 hover:underline disabled:opacity-50">Resend OTP</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="mt-8 text-center text-xs text-gray-400">&copy; {{ date('Y') }} {{ config('app.name', 'MyClasses') }}. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function loginForms() {
            return {
                mode: 'password', otpStep: 1, identifier: '', channel: 'email', otp: '', masked: '', loading: false, msg: '', msgError: false,
                csrf: document.querySelector('meta[name="csrf-token"]').content,
                async sendOtp() {
                    if (!this.identifier.trim()) { this.flash('Enter your email or mobile', true); return; }
                    this.loading = true; this.msg = '';
                    try {
                        const res = await fetch('{{ route('login.otp.request') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' }, body: JSON.stringify({ identifier: this.identifier.trim(), channel: this.channel }) });
                        const data = await res.json();
                        if (!res.ok || data.success === false) { this.flash(data.message || 'Could not send OTP', true); return; }
                        this.masked = data.masked || this.identifier; this.otpStep = 2;
                        if (data.dev_otp) this.flash('Dev OTP: ' + data.dev_otp, false); else this.flash('OTP sent to ' + this.masked, false);
                    } catch (e) { this.flash('Network error. Try again.', true); } finally { this.loading = false; }
                },
                async verifyOtp() {
                    if (!this.otp.trim()) { this.flash('Enter the OTP', true); return; }
                    this.loading = true; this.msg = '';
                    try {
                        const res = await fetch('{{ route('login.otp.verify') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' }, body: JSON.stringify({ identifier: this.identifier.trim(), otp: this.otp.trim() }) });
                        const data = await res.json();
                        if (!res.ok || data.success === false) { this.flash(data.message || 'Invalid OTP', true); return; }
                        window.location.href = data.redirect || '{{ url('/') }}';
                    } catch (e) { this.flash('Network error. Try again.', true); } finally { this.loading = false; }
                },
                flash(m, isError) { this.msg = m; this.msgError = isError; },
            };
        }
    </script>
</body>
</html>
