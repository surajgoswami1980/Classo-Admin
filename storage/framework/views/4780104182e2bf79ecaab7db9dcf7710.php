<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title>Login - <?php echo e(config('app.name', 'School ERP Admin')); ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: { 50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd', 400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a' },
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        .login-gradient {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #0ea5e9 100%);
        }
        .floating-shape {
            animation: float 6s ease-in-out infinite;
        }
        .floating-shape:nth-child(2) { animation-delay: -2s; }
        .floating-shape:nth-child(3) { animation-delay: -4s; }
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }
    </style>
</head>
<body class="h-full font-sans antialiased">
    <div class="flex min-h-full">

        <!-- Left Panel - Branding / Illustration -->
        <div class="hidden lg:flex lg:w-1/2 xl:w-[55%] login-gradient relative overflow-hidden">
            <!-- Decorative floating shapes -->
            <div class="floating-shape absolute top-20 left-20 w-32 h-32 bg-white/10 rounded-2xl rotate-12"></div>
            <div class="floating-shape absolute top-1/3 right-20 w-24 h-24 bg-white/5 rounded-full"></div>
            <div class="floating-shape absolute bottom-32 left-1/3 w-40 h-40 bg-white/5 rounded-3xl -rotate-12"></div>
            <div class="absolute bottom-20 right-32 w-16 h-16 bg-white/10 rounded-xl rotate-45"></div>

            <!-- Content -->
            <div class="relative z-10 flex flex-col justify-center px-12 xl:px-20">
                <div class="max-w-lg">
                    <!-- Logo -->
                    <div class="flex items-center gap-3 mb-12">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm">
                            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                            </svg>
                        </div>
                        <span class="text-2xl font-bold text-white">School ERP</span>
                    </div>

                    <!-- Headline -->
                    <h1 class="text-4xl xl:text-5xl font-bold text-white leading-tight mb-6">
                        Manage your school, <br>
                        <span class="text-blue-200">effortlessly.</span>
                    </h1>

                    <p class="text-lg text-blue-100/80 mb-10 leading-relaxed">
                        A complete administration platform for modern schools. Track attendance, manage fees, organize exams, and more — all from one place.
                    </p>

                    <!-- Feature highlights -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-white/15">
                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </div>
                            <span class="text-sm text-blue-100">Multi-school management with role-based access</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-white/15">
                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </div>
                            <span class="text-sm text-blue-100">Real-time attendance tracking for students & staff</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-white/15">
                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </div>
                            <span class="text-sm text-blue-100">Automated fee management & invoice generation</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel - Login Form -->
        <div class="flex flex-1 flex-col justify-center px-6 py-12 lg:px-12 xl:px-20 bg-white">
            <div class="w-full max-w-sm mx-auto">

                <!-- Mobile logo (hidden on desktop) -->
                <div class="flex items-center gap-3 mb-10 lg:hidden">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-primary-600">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-900">School ERP</span>
                </div>

                <!-- Form header -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900">Sign in to your account</h2>
                    <p class="mt-2 text-sm text-gray-500">Enter your credentials to access the admin panel</p>
                </div>

                <!-- Error messages -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                    <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                            </svg>
                            <p class="text-sm text-red-700"><?php echo e($errors->first()); ?></p>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <!-- Login Forms (Password / OTP) -->
                <div x-data="loginForms()">

                    <!-- Mode toggle -->
                    <div class="flex p-1 mb-6 bg-gray-100 rounded-xl">
                        <button type="button" @click="mode='password'"
                                :class="mode==='password' ? 'bg-white text-primary-600 shadow-sm' : 'text-gray-500'"
                                class="flex-1 py-2 text-sm font-medium rounded-lg transition-all">Password</button>
                        <button type="button" @click="mode='otp'"
                                :class="mode==='otp' ? 'bg-white text-primary-600 shadow-sm' : 'text-gray-500'"
                                class="flex-1 py-2 text-sm font-medium rounded-lg transition-all">OTP</button>
                    </div>

                    <!-- ─── PASSWORD LOGIN ─────────────────────────────── -->
                    <form x-show="mode==='password'" method="POST" action="<?php echo e(route('login.submit')); ?>" class="space-y-5">
                        <?php echo csrf_field(); ?>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                    </svg>
                                </div>
                                <input id="email" name="email" type="email" autocomplete="email"
                                       value="<?php echo e(old('email')); ?>"
                                       class="block w-full rounded-xl border border-gray-300 bg-gray-50 py-3 pl-11 pr-4 text-sm text-gray-900 placeholder:text-gray-400 focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none transition-all"
                                       placeholder="admin@school.com">
                            </div>
                        </div>

                        <div x-data="{ showPassword: false }">
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                    </svg>
                                </div>
                                <input id="password" name="password" :type="showPassword ? 'text' : 'password'" autocomplete="current-password"
                                       class="block w-full rounded-xl border border-gray-300 bg-gray-50 py-3 pl-11 pr-11 text-sm text-gray-900 placeholder:text-gray-400 focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none transition-all"
                                       placeholder="Enter your password">
                                <button type="button" @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 hover:text-gray-600">
                                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    <svg x-show="showPassword" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="remember" id="remember"
                                       class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-gray-600">Remember me</span>
                            </label>
                        </div>

                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-all duration-150 active:scale-[0.98]">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                            </svg>
                            Sign In
                        </button>
                    </form>

                    <!-- ─── OTP LOGIN ──────────────────────────────────── -->
                    <div x-show="mode==='otp'" class="space-y-5">
                        <!-- Error / info banner -->
                        <div x-show="msg" x-text="msg" :class="msgError ? 'bg-red-50 border-red-200 text-red-700' : 'bg-blue-50 border-blue-200 text-blue-700'"
                             class="p-3 rounded-lg border text-sm" style="display:none"></div>

                        <!-- Step A: identifier + channel -->
                        <div x-show="otpStep===1" class="space-y-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email or Mobile</label>
                                <input type="text" x-model="identifier"
                                       class="block w-full rounded-xl border border-gray-300 bg-gray-50 py-3 px-4 text-sm text-gray-900 placeholder:text-gray-400 focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none transition-all"
                                       placeholder="Registered email or mobile number">
                            </div>
                            <div class="flex gap-2">
                                <button type="button" @click="channel='email'"
                                        :class="channel==='email' ? 'border-primary-500 bg-blue-50 text-primary-600' : 'border-gray-200 text-gray-500'"
                                        class="flex-1 py-2.5 text-sm font-medium rounded-xl border transition">Email</button>
                                <button type="button" @click="channel='mobile'"
                                        :class="channel==='mobile' ? 'border-primary-500 bg-blue-50 text-primary-600' : 'border-gray-200 text-gray-500'"
                                        class="flex-1 py-2.5 text-sm font-medium rounded-xl border transition">Mobile</button>
                            </div>
                            <button type="button" @click="sendOtp()" :disabled="loading"
                                    class="w-full rounded-xl bg-primary-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 transition disabled:opacity-60">
                                <span x-text="loading ? 'Sending...' : 'Send OTP'"></span>
                            </button>
                        </div>

                        <!-- Step B: enter OTP -->
                        <div x-show="otpStep===2" class="space-y-5">
                            <p class="text-sm text-gray-500">OTP sent to <span class="font-medium text-gray-700" x-text="masked"></span></p>
                            <input type="text" inputmode="numeric" maxlength="6" x-model="otp"
                                   class="block w-full rounded-xl border border-gray-300 bg-gray-50 py-3 px-4 text-center text-2xl tracking-[0.5em] font-mono text-gray-900 focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none transition-all"
                                   placeholder="______">
                            <button type="button" @click="verifyOtp()" :disabled="loading"
                                    class="w-full rounded-xl bg-primary-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 transition disabled:opacity-60">
                                <span x-text="loading ? 'Verifying...' : 'Verify & Sign In'"></span>
                            </button>
                            <div class="flex items-center justify-between text-sm">
                                <button type="button" @click="otpStep=1; msg=''" class="text-gray-500 hover:text-primary-600">← Back</button>
                                <button type="button" @click="sendOtp()" :disabled="loading" class="text-primary-600 hover:underline disabled:opacity-50">Resend OTP</button>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    function loginForms() {
                        return {
                            mode: 'password',
                            otpStep: 1,
                            identifier: '',
                            channel: 'email',
                            otp: '',
                            masked: '',
                            loading: false,
                            msg: '',
                            msgError: false,
                            csrf: document.querySelector('meta[name="csrf-token"]').content,
                            async sendOtp() {
                                if (!this.identifier.trim()) { this.flash('Enter your email or mobile', true); return; }
                                this.loading = true; this.msg = '';
                                try {
                                    const res = await fetch('<?php echo e(route('login.otp.request')); ?>', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' },
                                        body: JSON.stringify({ identifier: this.identifier.trim(), channel: this.channel }),
                                    });
                                    const data = await res.json();
                                    if (!res.ok || data.success === false) { this.flash(data.message || 'Could not send OTP', true); return; }
                                    this.masked = data.masked || this.identifier;
                                    this.otpStep = 2;
                                    if (data.dev_otp) this.flash('Dev OTP: ' + data.dev_otp, false);
                                    else this.flash('OTP sent to ' + this.masked, false);
                                } catch (e) { this.flash('Network error. Try again.', true); }
                                finally { this.loading = false; }
                            },
                            async verifyOtp() {
                                if (!this.otp.trim()) { this.flash('Enter the OTP', true); return; }
                                this.loading = true; this.msg = '';
                                try {
                                    const res = await fetch('<?php echo e(route('login.otp.verify')); ?>', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' },
                                        body: JSON.stringify({ identifier: this.identifier.trim(), otp: this.otp.trim() }),
                                    });
                                    const data = await res.json();
                                    if (!res.ok || data.success === false) { this.flash(data.message || 'Invalid OTP', true); return; }
                                    window.location.href = data.redirect || '<?php echo e(url('/')); ?>';
                                } catch (e) { this.flash('Network error. Try again.', true); }
                                finally { this.loading = false; }
                            },
                            flash(m, isError) { this.msg = m; this.msgError = isError; },
                        };
                    }
                </script>

                <!-- Footer -->
                <p class="mt-10 text-center text-xs text-gray-400">
                    &copy; <?php echo e(date('Y')); ?> <?php echo e(config('app.name')); ?>. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views/auth/login.blade.php ENDPATH**/ ?>