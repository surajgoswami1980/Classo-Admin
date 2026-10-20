<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#082B5B">

    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'MyClasses') }}</title>

    @include('layouts.partials.head-assets')

    <!-- Alpine.js for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('styles')
</head>
@php
    [
        'isSuper' => $isSuper, 'prefix' => $prefix, 'navGroups' => $navGroups,
        'activeGroup' => $activeGroup, 'activeItem' => $activeItem, 'navSearch' => $navSearch,
    ] = \App\Support\AdminNavigation::build();
    $authUser = auth()->user();
    $pageTitle = trim($__env->yieldContent('page-title', 'Dashboard'));
    $panelLabel = $isSuper ? (is_impersonating() ? 'Viewing as school' : 'Super Admin') : 'School Panel';
    $brandName = is_impersonating() ? session('impersonating_school_name') : ($authUser?->school?->name ?? config('app.name', 'MyClasses'));
    $initials = collect(preg_split('/\s+/', trim($authUser?->name ?? 'A')))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
@endphp
<body class="h-full font-sans antialiased bg-canvas text-gray-900"
      x-data="{
          sidebarOpen: false,
          sidebarCollapsed: localStorage.getItem('mc.sidebar') === '1',
          toggleCollapse() { this.sidebarCollapsed = !this.sidebarCollapsed; localStorage.setItem('mc.sidebar', this.sidebarCollapsed ? '1' : '0'); },
      }"
      @keydown.escape.window="sidebarOpen = false">

    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-primary-700 focus:shadow-pop">Skip to content</a>

    <div class="flex flex-col h-full">
        @include('components.impersonation-banner')
    <div class="flex flex-1 min-h-0">

        <!-- ═══════════════════════════ SIDEBAR ═══════════════════════════ -->
        <div x-show="sidebarOpen" x-cloak
             x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-navy-950/60 backdrop-blur-[2px] lg:hidden" @click="sidebarOpen = false" aria-hidden="true"></div>

        <aside id="app-sidebar"
               :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', sidebarCollapsed ? 'lg:w-[76px]' : 'lg:w-[264px]']"
               class="fixed inset-y-0 left-0 z-50 flex w-[264px] -translate-x-full flex-col bg-gradient-to-b from-navy-800 to-navy-900 text-sidebar-text shadow-2xl shadow-navy-950/30 transition-[transform,width] duration-300 ease-out lg:static lg:z-auto lg:translate-x-0 lg:shadow-none"
               aria-label="Main navigation">

            <!-- Brand -->
            <div class="flex h-16 shrink-0 items-center gap-3 border-b border-white/[.06] px-4" :class="sidebarCollapsed && 'lg:justify-center lg:px-0'">
                <a href="{{ route($prefix . '.dashboard') }}" class="flex min-w-0 items-center gap-3 rounded-xl focus-visible:ring-2 focus-visible:ring-white/40">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white shadow-lg shadow-navy-950/30">
                        <x-quilo-logo :size="28" />
                    </span>
                    <span class="min-w-0" :class="sidebarCollapsed && 'lg:hidden'">
                        <span class="block truncate text-[15px] font-bold leading-tight text-white">{{ $brandName }}</span>
                        <span class="block truncate text-[11px] font-medium text-sidebar-text">{{ $panelLabel }}</span>
                    </span>
                </a>
                <button type="button" @click="sidebarOpen = false" class="ml-auto rounded-lg p-1.5 text-sidebar-text hover:bg-white/5 hover:text-white lg:hidden" aria-label="Close menu">
                    @include('components.icons.x-mark', ['class' => 'w-5 h-5'])
                </button>
            </div>

            <!-- Navigation -->
            <nav class="mc-scroll-dark flex-1 space-y-1 overflow-y-auto overflow-x-hidden px-3 py-4">
                @foreach($navGroups as $group)
                    @php $groupActive = collect($group['items'])->contains('active', true); @endphp
                    <div x-data="{ open: {{ $groupActive || $loop->first ? 'true' : (count($group['items']) <= 4 ? 'true' : 'false') }} }" class="pb-1">
                        <button type="button" @click="open = !open"
                                class="flex w-full items-center justify-between rounded-md px-3 pb-1.5 pt-3 text-[10.5px] font-semibold uppercase tracking-[.12em] text-sidebar-text/70 transition hover:text-white"
                                :class="sidebarCollapsed && 'lg:hidden'" :aria-expanded="open.toString()">
                            <span>{{ $group['label'] }}</span>
                            <svg class="h-3.5 w-3.5 transition-transform duration-200" :class="open ? 'rotate-0' : '-rotate-90'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                        </button>
                        <div x-show="sidebarCollapsed" x-cloak class="mx-3 my-2 hidden h-px bg-white/[.07] lg:block" aria-hidden="true"></div>

                        <div x-show="open || sidebarCollapsed"
                             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                             class="space-y-0.5">
                            @foreach($group['items'] as $item)
                                <a href="{{ $item['url'] }}"
                                   @if($item['active']) aria-current="page" @endif
                                   class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13.5px] font-medium transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/40
                                          {{ $item['active'] ? 'bg-primary-600 text-white shadow-lg shadow-primary-900/40' : 'text-sidebar-text hover:bg-white/[.06] hover:text-white' }}"
                                   :class="sidebarCollapsed && 'lg:justify-center lg:px-0'">
                                    @if($item['active'])<span class="absolute -left-3 top-1/2 h-6 w-1 -translate-y-1/2 rounded-r-full bg-white" aria-hidden="true"></span>@endif
                                    <span class="shrink-0 {{ $item['active'] ? 'text-white' : 'text-sidebar-text/80 group-hover:text-white' }}">
                                        @include('components.icons.' . $item['icon'], ['class' => 'w-5 h-5'])
                                    </span>
                                    <span class="truncate" :class="sidebarCollapsed && 'lg:sr-only'">{{ $item['name'] }}</span>
                                    @if(!empty($item['badge']) && $item['badge'] > 0)
                                        <span class="ml-auto inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-red-500 px-1.5 text-[11px] font-bold text-white"
                                              :class="sidebarCollapsed && 'lg:absolute lg:right-2 lg:top-1.5 lg:h-2 lg:min-w-0 lg:w-2 lg:p-0 lg:text-[0px]'">
                                            {{ $item['badge'] > 99 ? '99+' : $item['badge'] }}
                                        </span>
                                    @endif
                                    <!-- Tooltip when collapsed -->
                                    <span x-show="sidebarCollapsed" x-cloak role="tooltip"
                                          class="pointer-events-none absolute left-full top-1/2 z-50 ml-3 hidden -translate-y-1/2 whitespace-nowrap rounded-lg bg-navy-950 px-2.5 py-1.5 text-xs font-medium text-white opacity-0 shadow-pop transition-opacity duration-150 group-hover:opacity-100 group-focus-visible:opacity-100 lg:block">
                                        {{ $item['name'] }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>

            <!-- Collapse toggle (desktop) -->
            <div class="hidden shrink-0 border-t border-white/[.06] p-3 lg:block">
                <button type="button" @click="toggleCollapse()"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-[13px] font-medium text-sidebar-text transition hover:bg-white/[.06] hover:text-white"
                        :class="sidebarCollapsed && 'justify-center px-0'"
                        :aria-label="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'">
                    <svg class="h-5 w-5 shrink-0 transition-transform duration-300" :class="sidebarCollapsed && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5" /></svg>
                    <span x-show="!sidebarCollapsed">Collapse</span>
                </button>
            </div>
        </aside>

        <!-- ═══════════════════════════ MAIN ═══════════════════════════ -->
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-30 border-b border-gray-200/80 bg-white/85 backdrop-blur-md supports-[backdrop-filter]:bg-white/75">
                <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
                    <button type="button" @click="sidebarOpen = true" class="-ml-1 rounded-xl p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-900 lg:hidden" aria-label="Open menu" aria-controls="app-sidebar">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                    </button>

                    <!-- Title + breadcrumb -->
                    <div class="min-w-0 flex-1">
                        @if($activeGroup && $activeItem && $activeItem['name'] !== 'Dashboard')
                            <nav aria-label="Breadcrumb" class="hidden sm:block">
                                <ol class="flex items-center gap-1.5 text-xs font-medium text-gray-500">
                                    <li><a href="{{ route($prefix . '.dashboard') }}" class="hover:text-primary-600">Home</a></li>
                                    <li aria-hidden="true">@include('components.icons.chevron-right', ['class' => 'w-3 h-3 text-gray-300'])</li>
                                    <li>{{ $activeGroup['label'] }}</li>
                                    @if(strcasecmp(e($activeItem['name']), $pageTitle) !== 0)
                                        <li aria-hidden="true">@include('components.icons.chevron-right', ['class' => 'w-3 h-3 text-gray-300'])</li>
                                        <li><a href="{{ $activeItem['url'] }}" class="hover:text-primary-600">{{ $activeItem['name'] }}</a></li>
                                    @endif
                                </ol>
                            </nav>
                        @endif
                        <h1 class="truncate text-[17px] font-semibold leading-tight text-gray-900 lg:text-lg">{!! $pageTitle !!}</h1>
                    </div>

                    <!-- Quick navigation search (jumps to any module in the menu) -->
                    <div class="relative hidden md:block"
                         x-data="{ q: '', open: false, idx: 0, items: @js($navSearch),
                                   get results() { const s = this.q.trim().toLowerCase(); return s ? this.items.filter(i => (i.name + ' ' + i.group).toLowerCase().includes(s)).slice(0, 8) : []; },
                                   go(i) { if (i) window.location = i.url; } }"
                         @keydown.window.prevent.ctrl.k="$refs.q.focus()" @keydown.window.prevent.meta.k="$refs.q.focus()"
                         @click.outside="open = false">
                        <label for="mc-search" class="sr-only">Go to page</label>
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">@include('components.icons.magnifying-glass', ['class' => 'w-4 h-4'])</span>
                        <input id="mc-search" x-ref="q" x-model="q" type="search" autocomplete="off" placeholder="Go to page…"
                               @focus="open = true" @input="open = true; idx = 0"
                               @keydown.down.prevent="idx = Math.min(idx + 1, results.length - 1)" @keydown.up.prevent="idx = Math.max(idx - 1, 0)"
                               @keydown.enter.prevent="go(results[idx])" @keydown.escape="open = false; $el.blur()"
                               role="combobox" :aria-expanded="(open && results.length > 0).toString()" aria-controls="mc-search-list"
                               class="h-10 w-56 rounded-xl border border-gray-200 bg-gray-50 pl-9 pr-12 text-sm text-gray-900 placeholder:text-gray-400 transition focus:w-72 focus:border-primary-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-primary-500/15 lg:w-64">
                        <kbd class="pointer-events-none absolute right-2.5 top-1/2 hidden -translate-y-1/2 rounded-md border border-gray-200 bg-white px-1.5 py-0.5 text-[10px] font-semibold text-gray-400 lg:block">Ctrl K</kbd>
                        <ul id="mc-search-list" x-show="open && results.length" x-cloak x-transition.opacity.duration.100ms role="listbox"
                            class="absolute right-0 z-50 mt-2 w-80 overflow-hidden rounded-xl border border-gray-200 bg-white py-1.5 shadow-pop">
                            <template x-for="(r, i) in results" :key="r.url">
                                <li role="option" :aria-selected="(i === idx).toString()">
                                    <a :href="r.url" @mouseenter="idx = i" :class="i === idx ? 'bg-primary-50 text-primary-700' : 'text-gray-700'" class="flex items-center justify-between px-3.5 py-2 text-sm">
                                        <span x-text="r.name" class="font-medium"></span>
                                        <span x-text="r.group" class="text-xs text-gray-400"></span>
                                    </a>
                                </li>
                            </template>
                        </ul>
                    </div>

                    <div class="flex items-center gap-1.5 sm:gap-2">
                        @if($isSuper && !is_impersonating())
                            <!-- School switcher -->
                            <div x-data="{ open: false, f: '' }" class="relative" @keydown.escape="open = false">
                                <button type="button" @click="open = !open; $nextTick(() => open && $refs.sf.focus())" :aria-expanded="open.toString()" aria-haspopup="true"
                                        class="flex h-10 items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50">
                                    @include('components.icons.building', ['class' => 'w-4 h-4 text-gray-400'])
                                    <span class="hidden xl:inline">Switch School</span>
                                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                                </button>
                                <div x-show="open" x-cloak @click.outside="open = false"
                                     x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1 scale-[.98]" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                     class="absolute right-0 z-50 mt-2 w-80 origin-top-right overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-pop">
                                    <div class="border-b border-gray-100 p-3">
                                        <p class="mb-2 px-1 text-[11px] font-semibold uppercase tracking-wider text-gray-500">View as school admin</p>
                                        <input x-ref="sf" x-model="f" type="search" placeholder="Filter schools…" aria-label="Filter schools" class="form-input py-2">
                                    </div>
                                    @php $switcherSchools = \App\Models\School::active()->orderBy('name')->limit(50)->get(['id','name','code']); @endphp
                                    <div class="mc-scroll max-h-80 overflow-y-auto py-1">
                                        @forelse($switcherSchools as $switcherSchool)
                                            <form method="POST" action="{{ route('admin.schools.switch', $switcherSchool) }}"
                                                  x-show="!f || @js(mb_strtolower($switcherSchool->name . ' ' . $switcherSchool->code)).includes(f.toLowerCase())">
                                                @csrf
                                                <button type="submit" class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm text-gray-700 transition hover:bg-primary-50/60">
                                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-xs font-bold text-primary-700">{{ mb_strtoupper(mb_substr($switcherSchool->name, 0, 1)) }}</span>
                                                    <span class="min-w-0 flex-1 truncate font-medium">{{ $switcherSchool->name }}</span>
                                                    <span class="shrink-0 font-mono text-[11px] text-gray-400">{{ $switcherSchool->code }}</span>
                                                </button>
                                            </form>
                                        @empty
                                            <p class="px-4 py-6 text-center text-sm text-gray-400">No active schools yet</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(\Illuminate\Support\Facades\Route::has($prefix . '.notifications.index'))
                            <!-- Notifications: opens the existing Notifications module -->
                            <a href="{{ route($prefix . '.notifications.index') }}" title="Notifications"
                               class="relative flex h-10 w-10 items-center justify-center rounded-xl text-gray-500 transition hover:bg-gray-100 hover:text-gray-900">
                                <span class="sr-only">Notifications</span>
                                @include('components.icons.bell', ['class' => 'w-5 h-5'])
                            </a>
                        @endif

                        <!-- Profile menu -->
                        <div x-data="{ open: false }" class="relative" @keydown.escape="open = false">
                            <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="true"
                                    class="flex items-center gap-2.5 rounded-xl p-1 pr-1.5 transition hover:bg-gray-100 sm:pr-2.5">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 text-[13px] font-bold text-white shadow-sm">{{ $initials ?: 'A' }}</span>
                                <span class="hidden text-left sm:block">
                                    <span class="block max-w-[140px] truncate text-sm font-semibold leading-tight text-gray-900">{{ $authUser?->name ?? 'Admin' }}</span>
                                    <span class="block max-w-[140px] truncate text-[11px] capitalize leading-tight text-gray-500">{{ str_replace('-', ' ', $authUser?->roles?->first()?->name ?? 'admin') }}</span>
                                </span>
                                <svg class="hidden h-4 w-4 text-gray-400 transition-transform sm:block" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                            </button>
                            <div x-show="open" x-cloak @click.outside="open = false"
                                 x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1 scale-[.98]" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                 class="absolute right-0 z-50 mt-2 w-64 origin-top-right overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-pop" role="menu">
                                <div class="flex items-center gap-3 border-b border-gray-100 px-4 py-3.5">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-sm font-bold text-primary-700">{{ $initials ?: 'A' }}</span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-gray-900">{{ $authUser?->name }}</p>
                                        <p class="truncate text-xs text-gray-500">{{ $authUser?->email }}</p>
                                    </div>
                                </div>
                                <div class="p-1.5">
                                    <a href="{{ panel_route('profile') }}" role="menuitem" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50 hover:text-gray-900">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                        My Profile
                                    </a>
                                    <a href="{{ panel_route('settings') }}" role="menuitem" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50 hover:text-gray-900">
                                        @include('components.icons.cog-6-tooth', ['class' => 'w-4 h-4 text-gray-400'])
                                        Settings
                                    </a>
                                </div>
                                <div class="border-t border-gray-100 p-1.5">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" role="menuitem" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" /></svg>
                                            Sign out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main id="main-content" class="mc-content mc-scroll flex-1 overflow-auto" tabindex="-1">
                <div class="mx-auto w-full max-w-[1440px] px-4 py-5 sm:px-6 sm:py-6 lg:px-8 lg:py-7">
                    <!-- Flash messages -->
                    @if(session('success'))
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)" x-transition.opacity.duration.200ms role="status"
                             class="mb-5 flex items-center gap-3 rounded-2xl border border-green-200 bg-green-50 px-4 py-3.5">
                            @include('components.icons.check-circle', ['class' => 'w-5 h-5 shrink-0 text-green-600'])
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                            <button type="button" @click="show = false" class="ml-auto rounded-lg p-1 text-green-600 hover:bg-green-100" aria-label="Dismiss">@include('components.icons.x-mark', ['class' => 'w-4 h-4'])</button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div x-data="{ show: true }" x-show="show" x-transition.opacity.duration.200ms role="alert"
                             class="mb-5 flex items-center gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3.5">
                            @include('components.icons.exclamation', ['class' => 'w-5 h-5 shrink-0 text-red-600'])
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                            <button type="button" @click="show = false" class="ml-auto rounded-lg p-1 text-red-600 hover:bg-red-100" aria-label="Dismiss">@include('components.icons.x-mark', ['class' => 'w-4 h-4'])</button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div x-data="{ show: true }" x-show="show" x-transition.opacity.duration.200ms role="alert"
                             class="mb-5 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3.5">
                            @include('components.icons.exclamation', ['class' => 'w-5 h-5 shrink-0 mt-0.5 text-red-600'])
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-red-800">Please fix the following:</p>
                                <ul class="mt-1 list-disc list-inside space-y-0.5 text-sm text-red-700">
                                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                                </ul>
                            </div>
                            <button type="button" @click="show = false" class="rounded-lg p-1 text-red-600 hover:bg-red-100" aria-label="Dismiss">@include('components.icons.x-mark', ['class' => 'w-4 h-4'])</button>
                        </div>
                    @endif

                    <div class="animate-fade-in">
                        @yield('content')
                    </div>
                </div>
            </main>
        </div>
    </div>
    </div>

    @include('layouts.partials.dialogs')

    @stack('scripts')
</body>
</html>
