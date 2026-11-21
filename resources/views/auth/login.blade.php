<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin Login - {{ config('app.name', 'MyClasses') }}</title>

    @include('layouts.partials.head-assets')
    <script>
        // Login-only accent used by the decorative blobs.
        tailwind.config.theme.extend.colors.cyanx = { 400: '#22d3ee', 500: '#06d6c4' };
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        .mc-login-bg {
            background-color: #F3F6FB;
            background-image: radial-gradient(circle at 10% 15%, rgba(94,140,245,.13), transparent 24rem), radial-gradient(circle at 90% 85%, rgba(7,136,165,.08), transparent 22rem);
        }
        .mc-login-hero { background: radial-gradient(circle at 18% 12%, rgba(94,140,245,.28), transparent 24rem), linear-gradient(145deg, #0B2447 0%, #071A33 72%); }
        .mc-login-grid { background-image: linear-gradient(rgba(255,255,255,.035) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.035) 1px, transparent 1px); background-size: 40px 40px; mask-image: linear-gradient(to bottom, black, transparent 82%); }
        .float { animation: float 7s ease-in-out infinite; }
        .float:nth-child(2){animation-delay:-1.5s} .float:nth-child(3){animation-delay:-3s} .float:nth-child(4){animation-delay:-4.5s}
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }
        @keyframes login-enter { 0%{opacity:0;transform:translateY(12px)} 100%{opacity:1;transform:translateY(0)} }
        .login-enter { animation: login-enter .5s ease-out both; }
        .login-enter-1{animation-delay:.08s} .login-enter-2{animation-delay:.16s} .login-enter-3{animation-delay:.24s}
    </style>
</head>
<body class="h-full font-sans antialiased mc-login-bg text-gray-900">
    <a href="#login-form" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-primary-700 focus:shadow-pop">Skip to sign in</a>
    <div class="grid min-h-full lg:grid-cols-[minmax(0,1.08fr)_minmax(460px,.92fr)]">

        <!-- ─── LEFT: Brand showcase ─────────────────────────────── -->
        <section class="mc-login-hero relative hidden overflow-hidden px-14 text-white lg:flex lg:flex-col lg:justify-center xl:px-20" aria-labelledby="product-heading">
            <div class="mc-login-grid pointer-events-none absolute inset-0" aria-hidden="true"></div>
            <div class="float pointer-events-none absolute right-24 top-16 h-28 w-28 rounded-full bg-primary-400/20 blur-2xl" aria-hidden="true"></div>
            <div class="float pointer-events-none absolute bottom-24 left-10 h-36 w-36 rounded-full bg-cyanx-400/15 blur-3xl" aria-hidden="true"></div>

            <div class="relative z-10 max-w-xl">
                <!-- Logo + wordmark -->
                <div class="login-enter flex items-center gap-3">
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white shadow-lg shadow-black/15 ring-1 ring-white/70"><x-quilo-logo :size="38" /></span>
                    <div>
                        <div class="text-3xl font-black tracking-[-.035em] text-white xl:text-4xl">MyClasses</div>
                        <p class="mt-1 text-[11px] font-semibold uppercase tracking-[.18em] text-primary-200">School operations, unified</p>
                    </div>
                </div>

                <!-- Tagline -->
                <div class="login-enter login-enter-1 mt-10">
                    <p class="text-xs font-bold uppercase tracking-[.2em] text-primary-300">Education management platform</p>
                    <h1 id="product-heading" class="mt-3 max-w-lg text-4xl font-bold leading-[1.08] tracking-[-.04em] text-white xl:text-5xl">Run every school day with clarity.</h1>
                    <p class="mt-5 max-w-lg text-base leading-7 text-slate-300">Bring admissions, academics, attendance, fees, communication, and reporting into one secure workspace.</p>
                </div>

                <!-- Feature chips -->
                <div class="login-enter login-enter-2 mt-10 grid grid-cols-3 gap-3">
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
                        <div class="flex items-center gap-3 rounded-xl border border-white/10 bg-white/[.06] p-3 backdrop-blur" style="animation-delay: {{ 0.25 + $i*0.07 }}s">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/10 text-primary-200 ring-1 ring-white/10" aria-hidden="true">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $chip[3] }}" /></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-xs font-bold text-white">{{ $chip[0] }}</p>
                                <p class="truncate text-[10px] leading-tight text-slate-400">{{ $chip[1] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- mini dashboard preview card -->
                <div class="login-enter login-enter-3 mt-8 max-w-md rounded-2xl border border-white/10 bg-white/[.07] p-5 shadow-2xl shadow-black/15 backdrop-blur">
                    <div class="mb-4 flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_12px_rgba(52,211,153,.7)]" aria-hidden="true"></span>
                        <span class="text-sm font-bold text-white">Today's overview</span>
                        <span class="ml-auto text-[10px] font-semibold uppercase tracking-wider text-slate-400">Live workspace</span>
                    </div>
                    <div class="grid grid-cols-4 gap-2 mb-3">
                        @foreach([['1,248','Students','text-primary-600'],['86','Teachers','text-emerald-600'],['64','Classes','text-sky-600'],['₹12.4L','Fees','text-amber-600']] as $s)
                            <div class="rounded-lg bg-white/[.07] p-2 text-center ring-1 ring-white/5">
                                <p class="text-sm font-black text-white">{{ $s[0] }}</p>
                                <p class="text-[9px] text-slate-400">{{ $s[1] }}</p>
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
        </section>

        <!-- ─── RIGHT: Login card ────────────────────────────────── -->
        <main class="flex items-center justify-center px-4 py-8 sm:px-8 sm:py-12 lg:px-12">
            <div id="login-form" class="login-enter login-enter-1 w-full max-w-[440px]" tabindex="-1">
                <div class="rounded-3xl border border-white/90 bg-white/95 p-6 shadow-[0_28px_70px_-28px_rgba(7,26,51,.35)] ring-1 ring-gray-900/[.03] backdrop-blur sm:p-9">

                    <div class="mb-7 flex flex-col items-center text-center lg:hidden">
                        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-50 ring-1 ring-primary-100"><x-quilo-logo :size="38" /></span>
                        <div class="mt-3 text-2xl font-black tracking-[-.035em] text-gray-900">MyClasses</div>
                        <p class="mt-1 text-xs text-gray-500">School operations, unified</p>
                    </div>

                    <div class="mb-7">
                        <p class="mc-eyebrow mb-2">Secure admin portal</p>
                        <h2 class="text-2xl font-bold tracking-[-.03em] text-gray-900">Welcome back</h2>
                        <p class="mt-1.5 text-sm leading-6 text-gray-500">Sign in to manage your school workspace.</p>
                    </div>

                    @if($errors->any())
                        <div class="mb-5 flex items-start gap-2.5 rounded-xl border border-red-200 bg-red-50 p-3.5" role="alert">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                            <p class="text-sm text-red-700">{{ $errors->first() }}</p>
                        </div>
                    @endif

                    <div x-data="loginForms()">
                        <!-- Mode toggle -->
                        <div class="mb-6 flex rounded-xl bg-gray-100/80 p-1 ring-1 ring-gray-200/70" role="tablist" aria-label="Sign-in method">
                            <button id="password-tab" type="button" role="tab" @click="mode='password'" :aria-selected="(mode==='password').toString()" aria-controls="password-panel" :class="mode==='password' ? 'bg-white text-primary-700 shadow-sm ring-1 ring-gray-200/70' : 'text-gray-500 hover:text-gray-700'" class="flex-1 rounded-lg py-2 text-sm font-semibold transition">Password</button>
                            <button id="otp-tab" type="button" role="tab" @click="mode='otp'" :aria-selected="(mode==='otp').toString()" aria-controls="otp-panel" :class="mode==='otp' ? 'bg-white text-primary-700 shadow-sm ring-1 ring-gray-200/70' : 'text-gray-500 hover:text-gray-700'" class="flex-1 rounded-lg py-2 text-sm font-semibold transition">OTP</button>
                        </div>

                        <!-- PASSWORD -->
                        <form id="password-panel" role="tabpanel" aria-labelledby="password-tab" x-show="mode==='password'" method="POST" action="{{ route('login.submit') }}" class="space-y-4">
                            @csrf
                            <div>
                                <label for="login-email" class="form-label">Email address</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                                    </div>
                                    <input id="login-email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" placeholder="admin@school.com"
                                           class="form-input bg-gray-50/80 py-3 pl-11 pr-4 focus:bg-white">
                                </div>
                            </div>
                            <div x-data="{ showPassword: false }">
                                <label for="login-password" class="form-label">Password</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                                    </div>
                                    <input id="login-password" name="password" :type="showPassword ? 'text' : 'password'" autocomplete="current-password" placeholder="Enter your password"
                                           class="form-input bg-gray-50/80 py-3 pl-11 pr-12 focus:bg-white">
                                    <button type="button" @click="showPassword=!showPassword" :aria-label="showPassword ? 'Hide password' : 'Show password'" :aria-pressed="showPassword.toString()" class="absolute inset-y-0 right-0 flex items-center rounded-r-xl px-3.5 text-gray-400 transition hover:text-gray-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary-500">
                                        <svg x-show="!showPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                        <svg x-show="showPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="remember" class="form-check">
                                    <span class="text-sm text-gray-600">Remember me</span>
                                </label>
                                <span class="text-sm text-primary-600 font-medium">Forgot password?</span>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-full shadow-lg shadow-primary-700/20">
                                Sign in
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                            </button>
                        </form>

                        <!-- OTP -->
                        <div id="otp-panel" role="tabpanel" aria-labelledby="otp-tab" x-show="mode==='otp'" class="space-y-5">
                            <div x-show="msg" x-text="msg" :class="msgError ? 'bg-red-50 border-red-200 text-red-700' : 'bg-blue-50 border-blue-200 text-blue-700'" class="rounded-xl border p-3 text-sm" :role="msgError ? 'alert' : 'status'" aria-live="polite" style="display:none"></div>
                            <div x-show="otpStep===1" class="space-y-5">
                                <div>
                                    <label for="otp-identifier" class="form-label">Email or mobile</label>
                                    <input id="otp-identifier" type="text" x-model="identifier" autocomplete="username" placeholder="Registered email or mobile number"
                                           class="form-input bg-gray-50/80 py-3 focus:bg-white">
                                </div>
                                <div class="grid grid-cols-2 gap-2" role="radiogroup" aria-label="OTP delivery method">
                                    <button type="button" role="radio" @click="channel='email'" :aria-checked="(channel==='email').toString()" :class="channel==='email' ? 'border-primary-300 bg-primary-50 text-primary-700 ring-2 ring-primary-500/10' : 'border-gray-200 text-gray-500 hover:border-gray-300 hover:bg-gray-50'" class="rounded-xl border py-2.5 text-sm font-semibold transition">Email</button>
                                    <button type="button" role="radio" @click="channel='mobile'" :aria-checked="(channel==='mobile').toString()" :class="channel==='mobile' ? 'border-primary-300 bg-primary-50 text-primary-700 ring-2 ring-primary-500/10' : 'border-gray-200 text-gray-500 hover:border-gray-300 hover:bg-gray-50'" class="rounded-xl border py-2.5 text-sm font-semibold transition">Mobile</button>
                                </div>
                                <button type="button" @click="sendOtp()" :disabled="loading" class="btn btn-primary btn-lg w-full"><span x-text="loading ? 'Sending…' : 'Send OTP'"></span></button>
                            </div>
                            <div x-show="otpStep===2" class="space-y-5">
                                <p class="text-sm text-gray-500">OTP sent to <span class="font-medium text-gray-700" x-text="masked"></span></p>
                                <label for="login-otp" class="sr-only">Six-digit OTP</label>
                                <input id="login-otp" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6" x-model="otp" placeholder="______"
                                       class="form-input bg-gray-50/80 px-4 py-3 text-center font-mono text-2xl tracking-[0.5em] focus:bg-white">
                                <button type="button" @click="verifyOtp()" :disabled="loading" class="btn btn-primary btn-lg w-full"><span x-text="loading ? 'Verifying…' : 'Verify & sign in'"></span></button>
                                <div class="flex items-center justify-between text-sm">
                                    <button type="button" @click="otpStep=1; msg=''" class="text-gray-500 hover:text-primary-600">← Back</button>
                                    <button type="button" @click="sendOtp()" :disabled="loading" class="text-primary-600 hover:underline disabled:opacity-50">Resend OTP</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    @php $site = rtrim(config('app.website_url'), '/'); @endphp
                    <p class="mt-6 text-center text-xs leading-5 text-gray-500">
                        By signing in you agree to our
                        <a href="{{ $site }}/terms" target="_blank" rel="noopener" class="font-medium text-primary-600 hover:underline">Terms</a>
                        and <a href="{{ $site }}/privacy" target="_blank" rel="noopener" class="font-medium text-primary-600 hover:underline">Privacy Policy</a>.
                    </p>
                    <p class="mt-3 text-center text-xs text-gray-400">&copy; {{ date('Y') }} {{ config('app.name', 'MyClasses') }}. All rights reserved.</p>
                </div>
            </div>
        </main>
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
