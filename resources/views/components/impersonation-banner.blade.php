@if(is_impersonating())
<div class="flex items-center justify-between gap-3 px-4 py-2 bg-amber-500 text-white text-sm font-medium">
    <span class="flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
        Viewing <strong>{{ session('impersonating_school_name') }}</strong> as School Admin
    </span>
    <form method="POST" action="{{ route('impersonation.exit') }}">
        @csrf
        <button type="submit" class="px-3 py-1 bg-white/20 rounded-md hover:bg-white/30 transition">Exit — Return to Super Admin</button>
    </form>
</div>
@endif
