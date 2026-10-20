{{--
    "Test connection" button using the SAVED credentials.
    Vars: $target, $label, $needsTo (null | 'email' | 'phone')
--}}
<div x-data="{
        busy: false, result: null, to: '',
        async run() {
            this.busy = true; this.result = null;
            try {
                const res = await fetch('{{ route('admin.integrations.test', $target) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify({ to: this.to }),
                });
                const data = await res.json().catch(() => ({}));
                this.result = { ok: !!data.ok, message: data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Request failed (' + res.status + ')') };
            } catch (e) { this.result = { ok: false, message: 'Network error: ' + e.message }; }
            this.busy = false;
        }
     }" class="space-y-2">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
        @if(!empty($needsTo))
            <input x-model="to" type="{{ $needsTo === 'email' ? 'email' : 'tel' }}" placeholder="{{ $needsTo === 'email' ? 'Send test to… you@company.com' : 'Send test to… +91 98765 43210' }}"
                   aria-label="Test recipient"
                   class="form-input sm:max-w-xs">
        @endif
        <button type="button" @click="run" :disabled="busy" class="btn btn-secondary">
            <svg x-show="busy" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"/></svg>
            <svg x-show="!busy" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
            <span x-text="busy ? 'Testing…' : '{{ $label }}'"></span>
        </button>
    </div>
    <p x-show="result" x-cloak role="status" class="flex items-start gap-2 rounded-lg px-3 py-2 text-sm"
       :class="result?.ok ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-700'">
        <span x-text="result?.ok ? '✓' : '✕'" class="font-bold"></span>
        <span x-text="result?.message"></span>
    </p>
    <p class="text-xs text-gray-400">Uses the saved settings — save first if you changed anything.</p>
</div>
