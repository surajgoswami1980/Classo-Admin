<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo $__env->yieldContent('title', 'Dashboard'); ?> - <?php echo e(config('app.name', 'School ERP Admin')); ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />

    <!-- Tailwind CSS CDN (replace with Vite build in production) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: { 50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd', 400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a' },
                        sidebar: { bg: '#0f172a', hover: '#1e293b', active: '#334155', text: '#94a3b8', textActive: '#f8fafc' },
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="h-full font-sans antialiased bg-gray-50" x-data="{ sidebarOpen: false, sidebarCollapsed: false }">

    <div class="flex flex-col h-full">
        <?php echo $__env->make('components.impersonation-banner', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="flex flex-1 min-h-0">
        <!-- ═══════════════════════════════════════════════════════════════════
             SIDEBAR
             ═══════════════════════════════════════════════════════════════════ -->
        <!-- Mobile overlay -->
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-gray-900/80 lg:hidden" @click="sidebarOpen = false"></div>

        <!-- Sidebar Panel -->
        <aside :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', sidebarCollapsed ? 'lg:w-20' : 'lg:w-72']"
               class="fixed inset-y-0 left-0 z-50 flex flex-col w-72 transition-all duration-300 ease-in-out bg-sidebar-bg lg:translate-x-0 lg:static lg:z-auto">

            <!-- School Logo & Name -->
            <div class="flex items-center h-16 px-6 border-b border-white/10 shrink-0">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-primary-600 shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                        </svg>
                    </div>
                    <span x-show="!sidebarCollapsed" class="text-lg font-bold text-white truncate">
                        <?php echo e(auth()->user()?->school?->name ?? config('app.name')); ?>

                    </span>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <?php
                    $prefix = auth()->user()->hasRole('super-admin') ? 'admin' : 'user';
                    $navItems = [
                        ['name' => 'Dashboard', 'route' => $prefix . '.dashboard', 'icon' => 'home'],
                    ];
                    if (auth()->user()->hasRole('super-admin')) {
                        $navItems[] = ['name' => 'Schools', 'route' => 'admin.schools.index', 'icon' => 'home'];
                        $navItems[] = ['name' => 'Subscriptions', 'route' => 'admin.subscriptions.index', 'icon' => 'briefcase'];
                        $navItems[] = ['name' => 'Revenue', 'route' => 'admin.revenue', 'icon' => 'chart-bar'];
                        $navItems[] = ['name' => 'Resources & Permissions', 'route' => 'admin.resources.index', 'icon' => 'cog-6-tooth'];
                    }
                    $navItems = array_merge($navItems, [
                        ['name' => 'Sessions', 'route' => $prefix . '.academic.sessions', 'icon' => 'calendar'],
                        ['name' => 'Classes', 'route' => $prefix . '.academic.classes', 'icon' => 'academic-cap'],
                        ['name' => 'Sections', 'route' => $prefix . '.academic.sections', 'icon' => 'users'],
                        ['name' => 'Students', 'route' => $prefix . '.students.index', 'icon' => 'users'],
                        ['name' => 'Teachers', 'route' => $prefix . '.teachers.index', 'icon' => 'academic-cap'],
                        ['name' => 'Attendance', 'route' => $prefix . '.attendance.index', 'icon' => 'clipboard-check'],
                        ['name' => 'Fees', 'route' => $prefix . '.fees.index', 'icon' => 'currency-rupee'],
                        ['name' => 'Exams', 'route' => $prefix . '.exams.index', 'icon' => 'document-text'],
                        ['name' => 'Transport', 'route' => $prefix . '.transport.index', 'icon' => 'truck'],
                    ]);
                    $canSeeTeam = auth()->user()->hasRole('super-admin')
                        || auth()->user()->hasRole('school-admin')
                        || has_resource_permission('team.view');
                    if ($canSeeTeam) {
                        $navItems[] = ['name' => 'Team', 'route' => $prefix . '.team.index', 'icon' => 'briefcase'];
                    }
                ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $navItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e($item['route'] !== '#' ? route($item['route']) : '#'); ?>"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150
                              <?php echo e(request()->routeIs($item['route'] . '*') ? 'bg-sidebar-active text-sidebar-textActive' : 'text-sidebar-text hover:bg-sidebar-hover hover:text-sidebar-textActive'); ?>">
                        <?php echo $__env->make('components.icons.' . $item['icon'], ['class' => 'w-5 h-5 shrink-0'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <span x-show="!sidebarCollapsed"><?php echo e($item['name']); ?></span>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </nav>

            <!-- Collapse Toggle (Desktop) -->
            <div class="hidden lg:flex items-center justify-center p-3 border-t border-white/10">
                <button @click="sidebarCollapsed = !sidebarCollapsed"
                        class="p-2 rounded-lg text-sidebar-text hover:bg-sidebar-hover hover:text-sidebar-textActive transition-colors">
                    <svg x-show="!sidebarCollapsed" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5" />
                    </svg>
                    <svg x-show="sidebarCollapsed" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 4.5l7.5 7.5-7.5 7.5m-6-15l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
            </div>
        </aside>

        <!-- ═══════════════════════════════════════════════════════════════════
             MAIN CONTENT
             ═══════════════════════════════════════════════════════════════════ -->
        <div class="flex flex-col flex-1 min-w-0">
            <!-- Top Header Bar -->
            <header class="sticky top-0 z-30 flex items-center justify-between h-16 px-4 bg-white border-b border-gray-200 sm:px-6 lg:px-8">
                <!-- Mobile menu button -->
                <button @click="sidebarOpen = true" class="p-2 -ml-2 text-gray-500 rounded-md lg:hidden hover:text-gray-900 hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <!-- Page title -->
                <h1 class="text-lg font-semibold text-gray-900 lg:text-xl"><?php echo $__env->yieldContent('page-title', 'Dashboard'); ?></h1>

                <!-- Right section -->
                <div class="flex items-center gap-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('super-admin') && !is_impersonating()): ?>
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                            <span class="hidden sm:inline">Switch School</span>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             class="absolute right-0 z-50 w-72 mt-2 bg-white rounded-xl shadow-lg ring-1 ring-gray-200 py-1 max-h-96 overflow-y-auto">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">View as School Admin</p>
                            </div>
                            <?php $switcherSchools = \App\Models\School::active()->orderBy('name')->limit(50)->get(['id','name','code']); ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $switcherSchools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $switcherSchool): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <form method="POST" action="<?php echo e(route('admin.schools.switch', $switcherSchool)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="flex items-center justify-between w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors text-left">
                                        <span class="truncate"><?php echo e($switcherSchool->name); ?></span>
                                        <span class="font-mono text-xs text-gray-400 shrink-0 ml-2"><?php echo e($switcherSchool->code); ?></span>
                                    </button>
                                </form>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <p class="px-4 py-3 text-sm text-gray-400">No active schools yet</p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <!-- Notifications -->
                    <button class="relative p-2 text-gray-500 rounded-lg hover:bg-gray-100 hover:text-gray-900">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>

                    <!-- User dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-3 p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                                <span class="text-sm font-semibold text-primary-700">
                                    <?php echo e(substr(auth()->user()?->name ?? 'A', 0, 1)); ?>

                                </span>
                            </div>
                            <div class="hidden sm:block text-left">
                                <p class="text-sm font-medium text-gray-900"><?php echo e(auth()->user()?->name ?? 'Admin'); ?></p>
                                <p class="text-xs text-gray-500"><?php echo e(auth()->user()?->roles?->first()?->name ?? 'admin'); ?></p>
                            </div>
                            <svg class="hidden w-4 h-4 text-gray-400 sm:block" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <!-- Dropdown menu -->
                        <div x-show="open" @click.away="open = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 z-50 w-56 mt-2 bg-white rounded-xl shadow-lg ring-1 ring-gray-200 py-1">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-medium text-gray-900"><?php echo e(auth()->user()?->name); ?></p>
                                <p class="text-xs text-gray-500 truncate"><?php echo e(auth()->user()?->email); ?></p>
                            </div>
                            <a href="#" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                My Profile
                            </a>
                            <a href="#" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                Settings
                            </a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <form method="POST" action="<?php echo e(route('logout')); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" /></svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-auto">
                <!-- Flash Messages -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                         class="mb-6 flex items-center gap-3 p-4 rounded-lg bg-green-50 border border-green-200">
                        <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <p class="text-sm font-medium text-green-800"><?php echo e(session('success')); ?></p>
                        <button @click="show = false" class="ml-auto text-green-600 hover:text-green-800">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
                    <div x-data="{ show: true }" x-show="show"
                         class="mb-6 flex items-center gap-3 p-4 rounded-lg bg-red-50 border border-red-200">
                        <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        <p class="text-sm font-medium text-red-800"><?php echo e(session('error')); ?></p>
                        <button @click="show = false" class="ml-auto text-red-600 hover:text-red-800">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php echo $__env->yieldContent('content'); ?>
            </main>
        </div>
    </div>
    </div>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Appsquadz_API\school-erp-admin\resources\views/layouts/app.blade.php ENDPATH**/ ?>