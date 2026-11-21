{{-- MyClasses design system: fonts, Tailwind tokens, shared component classes.
     Used by the app layout, the login page and the error layout so every screen
     shares one visual language. --}}
<link rel="icon" type="image/svg+xml" href="{{ asset('images/myclasses-logo.svg') }}">
<link rel="apple-touch-icon" href="{{ asset('images/myclasses-logo.jpg') }}">
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />

<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                colors: {
                    primary: { 50: '#EEF4FF', 100: '#DCE8FF', 200: '#BDD2FF', 300: '#91B4FF', 400: '#5E8CF5', 500: '#3868E8', 600: '#2754D7', 700: '#2043B0', 800: '#203B8C', 900: '#1F356F', 950: '#14214B' },
                    // Indigo remains mapped to the product blue so legacy views stay visually consistent.
                    indigo: { 50: '#EEF4FF', 100: '#DCE8FF', 200: '#BDD2FF', 300: '#91B4FF', 400: '#5E8CF5', 500: '#3868E8', 600: '#2754D7', 700: '#2043B0', 800: '#203B8C', 900: '#1F356F', 950: '#14214B' },
                    gray: { 50: '#F8FAFD', 100: '#F1F4F9', 200: '#E3E8F0', 300: '#CBD3DF', 400: '#94A0B2', 500: '#667085', 600: '#475467', 700: '#344054', 800: '#1D2939', 900: '#101828', 950: '#0C1425' },
                    navy: { 700: '#173A68', 800: '#112F58', 900: '#0B2447', 950: '#071A33' },
                    ink: '#101828',
                    canvas: '#F3F6FB',
                    info: { 50: '#ECFDFF', 100: '#CFF9FE', 600: '#0788A5', 700: '#0E6F83' },
                    sidebar: { bg: '#071A33', hover: 'rgba(255,255,255,.07)', active: 'rgba(94,140,245,.18)', text: '#AFC0D8', textActive: '#FFFFFF' },
                },
                boxShadow: {
                    card: '0 1px 2px rgba(16,24,40,.025), 0 5px 16px -8px rgba(16,24,40,.12)',
                    'card-hover': '0 18px 38px -18px rgba(20,33,75,.28), 0 4px 12px rgba(16,24,40,.06)',
                    pop: '0 22px 48px -16px rgba(7,26,51,.28), 0 8px 18px -8px rgba(7,26,51,.12)',
                    shell: '0 18px 55px -30px rgba(7,26,51,.28)',
                },
                keyframes: {
                    'fade-up': { '0%': { opacity: 0, transform: 'translateY(8px)' }, '100%': { opacity: 1, transform: 'translateY(0)' } },
                    'fade-in': { '0%': { opacity: 0 }, '100%': { opacity: 1 } },
                    'scale-in': { '0%': { opacity: 0, transform: 'scale(.97)' }, '100%': { opacity: 1, transform: 'scale(1)' } },
                    shimmer: { '100%': { transform: 'translateX(100%)' } },
                },
                animation: {
                    'fade-up': 'fade-up .35s ease-out both',
                    'fade-in': 'fade-in .25s ease-out both',
                    'scale-in': 'scale-in .2s ease-out both',
                },
            }
        }
    }
</script>

<style type="text/tailwindcss">
    @layer base {
        html { -webkit-tap-highlight-color: transparent; color-scheme: light; }
        body { @apply text-gray-900; font-feature-settings: 'cv02', 'cv03', 'cv04', 'cv11'; }
        ::selection { @apply bg-primary-100 text-primary-950; }
        :focus-visible { outline: 2px solid #3868E8; outline-offset: 3px; }
        [x-cloak] { display: none !important; }
    }

    @layer components {
        /* ── Surfaces ─────────────────────────────────────────── */
        .mc-card { @apply relative overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-card; }
        .mc-card-header { @apply flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-gradient-to-r from-white to-gray-50/60 px-5 py-4; }
        .mc-card-title { @apply text-[15px] font-bold tracking-[-.01em] text-gray-900; }
        .mc-card-body { @apply p-5; }
        .mc-section-title, .mc-eyebrow { @apply text-[11px] font-bold uppercase tracking-[.12em] text-gray-500; }
        .mc-interactive-row { @apply transition duration-150 hover:bg-primary-50/45 focus-within:bg-primary-50/45; }
        .mc-link { @apply rounded-md font-semibold text-primary-600 transition hover:text-primary-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2; }

        /* ── Buttons ──────────────────────────────────────────── */
        .btn { @apply inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-xl border border-transparent px-4 py-2.5 text-sm font-semibold shadow-sm transition duration-150 select-none focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 active:translate-y-px; }
        .btn-primary { @apply bg-primary-600 text-white shadow-primary-600/20 hover:bg-primary-700 hover:shadow-md hover:shadow-primary-700/20; }
        .btn-secondary { @apply border-gray-200 bg-white text-gray-700 hover:border-gray-300 hover:bg-gray-50; }
        .btn-danger { @apply bg-red-600 text-white shadow-red-600/20 hover:bg-red-700; }
        .btn-danger-soft { @apply bg-red-50 text-red-700 hover:bg-red-100; }
        .btn-success { @apply bg-green-600 text-white shadow-sm hover:bg-green-700; }
        .btn-ghost { @apply text-gray-600 hover:bg-gray-100 hover:text-gray-900; }
        .btn-sm { @apply rounded-lg px-3 py-1.5 text-xs; }
        .btn-lg { @apply px-5 py-3 text-[15px]; }
        .btn-icon { @apply inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-primary-50 hover:text-primary-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500; }
        .btn-icon-danger { @apply hover:bg-red-50 hover:text-red-600; }

        /* ── Forms ────────────────────────────────────────────── */
        .form-label { @apply mb-1.5 block text-[13px] font-semibold text-gray-700; }
        .form-label.req::after, .req::after { content: ' *'; @apply text-red-500; }
        .form-input { @apply block w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm transition placeholder:text-gray-400 hover:border-gray-300 focus:border-primary-500 focus:outline-none focus:ring-4 focus:ring-primary-500/10 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500; }
        .form-input.is-invalid { @apply border-red-400 focus:border-red-500 focus:ring-red-500/15; }
        select.form-input { @apply pr-9; }
        .form-check { @apply h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500; }
        .form-help { @apply mt-1.5 text-xs text-gray-500; }
        .form-error { @apply mt-1.5 text-xs font-medium text-red-600; }

        /* ── Tables ───────────────────────────────────────────── */
        .table-wrap { @apply overflow-x-auto; scrollbar-gutter: stable; }
        .table-mc { @apply w-full border-separate border-spacing-0 text-sm; }
        .table-mc thead { @apply bg-gray-50/90; }
        .table-mc thead th { @apply whitespace-nowrap border-b border-gray-200 px-5 py-3.5 text-left text-[10.5px] font-bold uppercase tracking-[.1em] text-gray-500; }
        .table-mc tbody tr { @apply transition-colors last:[&>td]:border-0 hover:bg-primary-50/45; }
        .table-mc tbody td { @apply border-b border-gray-100 px-5 py-3.5 align-middle text-gray-600; }

        /* ── Badges ───────────────────────────────────────────── */
        .badge { @apply inline-flex items-center gap-1 whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset; }
        .badge-success { @apply bg-green-50 text-green-700 ring-green-600/20; }
        .badge-warning { @apply bg-amber-50 text-amber-700 ring-amber-600/20; }
        .badge-danger { @apply bg-red-50 text-red-700 ring-red-600/20; }
        .badge-info { @apply bg-cyan-50 text-cyan-700 ring-cyan-600/20; }
        .badge-primary { @apply bg-primary-50 text-primary-700 ring-primary-600/20; }
        .badge-neutral { @apply bg-gray-100 text-gray-600 ring-gray-500/15; }

        /* ── Tabs ─────────────────────────────────────────────── */
        .tabs { @apply flex gap-1 overflow-x-auto rounded-xl bg-gray-100 p-1; }
        .tab { @apply whitespace-nowrap rounded-lg px-3.5 py-2 text-sm font-medium text-gray-600 transition hover:text-gray-900; }
        .tab-active { @apply bg-white text-primary-700 shadow-sm; }

        /* ── Modals / drawers ─────────────────────────────────── */
        .modal-backdrop { @apply fixed inset-0 z-50 bg-navy-950/50 backdrop-blur-[2px]; }
        .modal-panel { @apply relative w-full rounded-2xl bg-white shadow-pop; }

        /* ── Misc ─────────────────────────────────────────────── */
        .card-hover { @apply transition duration-200 hover:-translate-y-0.5 hover:shadow-card-hover; }
        .skeleton { @apply relative overflow-hidden rounded-lg bg-gray-100; }
        .skeleton::after { content: ''; @apply absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/70 to-transparent; animation: shimmer 1.4s infinite; }
    }
</style>

<style>
    @keyframes shimmer { 100% { transform: translateX(100%); } }
    @keyframes fade-up { 0% { opacity: 0; transform: translateY(8px); } 100% { opacity: 1; transform: translateY(0); } }

    /* Low-specificity defaults give legacy screens the same finish while
       allowing every existing utility class to keep precedence. */
    :where(.mc-content) :where(th) { font-size: 10.5px; text-transform: uppercase; letter-spacing: .09em; color: #667085; font-weight: 700; }
    :where(.mc-content) :where(thead) { background-color: #F8FAFD; }
    :where(.mc-content) :where(table) { border-color: #E3E8F0; }
    :where(.mc-content) :where(input:not([type=checkbox]):not([type=radio]):not([type=file]):not([type=range]):not([type=color]), select, textarea) { border-color: #E3E8F0; transition: border-color .15s ease, box-shadow .15s ease, background-color .15s ease; }
    :where(.mc-content) :where(input:not([type=checkbox]):not([type=radio]):not([type=file]):not([type=range]):not([type=color]), select, textarea):hover { border-color: #CBD3DF; }
    :where(.mc-content) :where(input:not([type=checkbox]):not([type=radio]):not([type=file]):not([type=range]):not([type=color]), select, textarea):focus { border-color: #3868E8; box-shadow: 0 0 0 4px rgba(56,104,232,.10); outline: none; }
    :where(.mc-content) :where(input[type=checkbox], input[type=radio]) { accent-color: #2754D7; }
    :where(.mc-content) :where(a, button, input, select, textarea) { scroll-margin: 6rem; }

    .mc-shell-canvas {
        background-color: #F3F6FB;
        background-image:
            radial-gradient(circle at 92% 2%, rgba(94,140,245,.09), transparent 24rem),
            linear-gradient(rgba(255,255,255,.55), rgba(255,255,255,0) 14rem);
    }
    .mc-sidebar {
        background:
            radial-gradient(circle at 15% 0%, rgba(94,140,245,.24), transparent 18rem),
            linear-gradient(180deg, #0B2447 0%, #071A33 68%, #06172D 100%);
    }
    .mc-sidebar::after {
        content: '';
        position: absolute;
        inset: 0 0 0 auto;
        width: 1px;
        background: linear-gradient(to bottom, rgba(255,255,255,.14), rgba(255,255,255,.025));
        pointer-events: none;
    }
    .mc-nav-active { box-shadow: inset 0 1px 0 rgba(255,255,255,.08), 0 8px 18px -14px rgba(0,0,0,.7); }
    .mc-topbar { box-shadow: 0 1px 0 rgba(16,24,40,.04), 0 8px 30px -24px rgba(7,26,51,.28); }
    .mc-menu { box-shadow: 0 22px 48px -16px rgba(7,26,51,.26), 0 8px 18px -8px rgba(7,26,51,.12); }
    .mc-content-panel { animation: fade-up .3s ease-out both; }

    .stagger > * { animation: fade-up .32s ease-out both; }
    .stagger > *:nth-child(2) { animation-delay: .04s } .stagger > *:nth-child(3) { animation-delay: .08s }
    .stagger > *:nth-child(4) { animation-delay: .12s } .stagger > *:nth-child(5) { animation-delay: .16s }
    .stagger > *:nth-child(6) { animation-delay: .20s } .stagger > *:nth-child(n+7) { animation-delay: .24s }

    .mc-scroll { scrollbar-width: thin; scrollbar-color: rgba(102,112,133,.32) transparent; }
    .mc-scroll::-webkit-scrollbar { width: 8px; height: 8px; }
    .mc-scroll::-webkit-scrollbar-track { background: transparent; }
    .mc-scroll::-webkit-scrollbar-thumb { background: rgba(102,112,133,.28); background-clip: padding-box; border: 2px solid transparent; border-radius: 9999px; }
    .mc-scroll::-webkit-scrollbar-thumb:hover { background: rgba(102,112,133,.48); background-clip: padding-box; }
    .mc-scroll-dark { scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.16) transparent; }
    .mc-scroll-dark::-webkit-scrollbar { width: 7px; }
    .mc-scroll-dark::-webkit-scrollbar-thumb { background: rgba(255,255,255,.14); background-clip: padding-box; border: 2px solid transparent; border-radius: 9999px; }

    @media (max-width: 639px) {
        .mc-card-header, .mc-card-body { padding-left: 1rem; padding-right: 1rem; }
        .table-mc thead th, .table-mc tbody td { padding-left: 1rem; padding-right: 1rem; }
    }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after { animation-duration: .01ms !important; animation-delay: 0ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; scroll-behavior: auto !important; }
    }
</style>
