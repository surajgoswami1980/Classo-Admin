@if(is_impersonating())
<div class="flex flex-wrap items-center justify-between gap-2 border-b border-amber-300/70 bg-gradient-to-r from-amber-500 to-amber-400 px-4 py-2 text-sm font-medium text-amber-950 shadow-sm" role="status">
    <span class="flex min-w-0 items-center gap-2">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-white/45" aria-hidden="true"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg></span>
        <span class="truncate">Viewing <strong>{{ session('impersonating_school_name') }}</strong> as school admin</span>
    </span>
    <form method="POST" action="{{ route('impersonation.exit') }}">
        @csrf
        <button type="submit" class="rounded-lg bg-amber-950/10 px-3 py-1.5 text-xs font-bold transition hover:bg-amber-950/15 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-950/50">Exit to Super Admin</button>
    </form>
</div>
@endif
