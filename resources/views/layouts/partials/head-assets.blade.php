{{-- MyClasses design system: fonts, Tailwind tokens, shared component classes.
     Used by the app layout, the login page and the error layout so every screen
     shares one visual language. --}}
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />

<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                colors: {
                    primary: { 50: '#EFF6FF', 100: '#DBEAFE', 200: '#BFDBFE', 300: '#93C5FD', 400: '#60A5FA', 500: '#3B82F6', 600: '#2563EB', 700: '#1D4ED8', 800: '#1E40AF', 900: '#1E3A8A' },
                    // Indigo is folded into the brand blue so older pages stay on-palette.
                    indigo: { 50: '#EFF6FF', 100: '#DBEAFE', 200: '#BFDBFE', 300: '#93C5FD', 400: '#60A5FA', 500: '#3B82F6', 600: '#2563EB', 700: '#1D4ED8', 800: '#1E40AF', 900: '#1E3A8A' },
                    // Neutral scale tuned to the brand text (#14213D), secondary (#64748B) and borders (#E2E8F0).
                    gray: { 50: '#F8FAFC', 100: '#F1F5F9', 200: '#E2E8F0', 300: '#CBD5E1', 400: '#94A3B8', 500: '#64748B', 600: '#475569', 700: '#334155', 800: '#1E293B', 900: '#14213D', 950: '#0B1530' },
                    navy: { 700: '#0B3670', 800: '#082B5B', 900: '#061D40', 950: '#04142E' },
                    ink: '#14213D',
                    canvas: '#F4F8FE',
                    info: { 50: '#ECFEFF', 100: '#CFFAFE', 600: '#0891B2', 700: '#0E7490' },
                    sidebar: { bg: '#082B5B', hover: 'rgba(255,255,255,.06)', active: '#2563EB', text: '#A9BCD8', textActive: '#FFFFFF' },
                },
                boxShadow: {
                    card: '0 1px 2px rgba(16,24,40,.04), 0 1px 3px rgba(16,24,40,.06)',
                    'card-hover': '0 10px 30px -12px rgba(37,99,235,.22), 0 2px 6px rgba(16,24,40,.06)',
                    pop: '0 16px 40px -12px rgba(6,29,64,.28), 0 4px 12px rgba(6,29,64,.08)',
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
        html { -webkit-tap-highlight-color: transparent; }
        body { @apply text-gray-900; }
        :focus-visible { outline: 2px solid #2563EB; outline-offset: 2px; }
        [x-cloak] { display: none !important; }
    }

    @layer components {
        /* ── Surfaces ─────────────────────────────────────────── */
        .mc-card { @apply bg-white rounded-2xl border border-gray-200/80 shadow-card; }
        .mc-card-header { @apply flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-b border-gray-100; }
        .mc-card-title { @apply text-[15px] font-semibold text-gray-900; }
        .mc-card-body { @apply p-5; }
        .mc-section-title { @apply text-xs font-semibold uppercase tracking-wider text-gray-500; }

        /* ── Buttons ──────────────────────────────────────────── */
        .btn { @apply inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-xl px-4 py-2.5 text-sm font-semibold transition duration-150 select-none focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 active:scale-[.98]; }
        .btn-primary { @apply bg-primary-600 text-white shadow-sm shadow-primary-600/20 hover:bg-primary-700; }
        .btn-secondary { @apply border border-gray-200 bg-white text-gray-700 shadow-sm hover:border-gray-300 hover:bg-gray-50; }
        .btn-danger { @apply bg-red-600 text-white shadow-sm hover:bg-red-700; }
        .btn-danger-soft { @apply bg-red-50 text-red-700 hover:bg-red-100; }
        .btn-success { @apply bg-green-600 text-white shadow-sm hover:bg-green-700; }
        .btn-ghost { @apply text-gray-600 hover:bg-gray-100 hover:text-gray-900; }
        .btn-sm { @apply rounded-lg px-3 py-1.5 text-xs; }
        .btn-lg { @apply px-5 py-3 text-[15px]; }
        .btn-icon { @apply inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-primary-50 hover:text-primary-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500; }
        .btn-icon-danger { @apply hover:bg-red-50 hover:text-red-600; }

        /* ── Forms ────────────────────────────────────────────── */
        .form-label { @apply mb-1.5 block text-sm font-medium text-gray-700; }
        .form-label.req::after, .req::after { content: ' *'; @apply text-red-500; }
        .form-input { @apply block w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:border-primary-500 focus:outline-none focus:ring-4 focus:ring-primary-500/15 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500; }
        .form-input.is-invalid { @apply border-red-400 focus:border-red-500 focus:ring-red-500/15; }
        select.form-input { @apply pr-9; }
        .form-check { @apply h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500; }
        .form-help { @apply mt-1.5 text-xs text-gray-500; }
        .form-error { @apply mt-1.5 text-xs font-medium text-red-600; }

        /* ── Tables ───────────────────────────────────────────── */
        .table-wrap { @apply overflow-x-auto; }
        .table-mc { @apply w-full text-sm; }
        .table-mc thead { @apply bg-gray-50/80 border-b border-gray-200; }
        .table-mc thead th { @apply px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap; }
        .table-mc tbody tr { @apply border-b border-gray-100 transition-colors last:border-0 hover:bg-primary-50/40; }
        .table-mc tbody td { @apply px-4 py-3 text-gray-600 align-middle; }

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

    /* Zero-specificity defaults so legacy markup inside the content area looks
       consistent; any utility class on the element still wins. */
    :where(.mc-content) :where(th) { font-size: 11px; text-transform: uppercase; letter-spacing: .05em; color: #64748B; font-weight: 600; }
    :where(.mc-content) :where(thead) { background-color: #F8FAFC; }
    :where(.mc-content) :where(input:not([type=checkbox]):not([type=radio]):not([type=file]):not([type=range]):not([type=color]), select, textarea) { border-color: #E2E8F0; }
    :where(.mc-content) :where(input[type=checkbox], input[type=radio]) { accent-color: #2563EB; }

    .stagger > * { animation: fade-up .35s ease-out both; }
    .stagger > *:nth-child(2) { animation-delay: .04s } .stagger > *:nth-child(3) { animation-delay: .08s }
    .stagger > *:nth-child(4) { animation-delay: .12s } .stagger > *:nth-child(5) { animation-delay: .16s }
    .stagger > *:nth-child(6) { animation-delay: .20s } .stagger > *:nth-child(n+7) { animation-delay: .24s }

    .mc-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
    .mc-scroll::-webkit-scrollbar-thumb { background: rgba(148,163,184,.35); border-radius: 9999px; }
    .mc-scroll-dark::-webkit-scrollbar { width: 6px; }
    .mc-scroll-dark::-webkit-scrollbar-thumb { background: rgba(255,255,255,.12); border-radius: 9999px; }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; scroll-behavior: auto !important; }
    }
</style>
