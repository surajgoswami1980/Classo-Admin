{{--
    Branded replacements for window.confirm() / window.alert().

    Confirm a form:  <form ... data-confirm="Delete this item?" data-confirm-title="Delete item"
                                data-confirm-ok="Delete" data-confirm-tone="danger">
    Confirm a link/button: same data-* attributes on the <a>/<button>.
    From JS:        await mcConfirm({ title, message, ok, tone })  → true/false
                    mcToast('Saved', 'success' | 'error' | 'info')
--}}
<div x-data="mcDialogs()" x-cloak @keydown.escape.window="open && cancel()">
    <!-- Confirm dialog -->
    <div x-show="open" class="fixed inset-0 z-[90] flex items-end justify-center p-4 sm:items-center" role="alertdialog" aria-modal="true" aria-labelledby="mc-confirm-title" aria-describedby="mc-confirm-msg">
        <div class="modal-backdrop" x-show="open" x-transition.opacity.duration.150ms @click="cancel()"></div>
        <div class="modal-panel max-w-md overflow-hidden"
             x-show="open"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-3 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 sm:scale-95">
            <div class="flex gap-4 px-6 pb-2 pt-6">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full"
                      :class="{ 'bg-red-50 text-red-600': tone === 'danger', 'bg-amber-50 text-amber-600': tone === 'warning', 'bg-primary-50 text-primary-600': tone === 'primary', 'bg-green-50 text-green-600': tone === 'success' }">
                    <template x-if="tone === 'danger'">@include('components.icons.trash', ['class' => 'w-5 h-5'])</template>
                    <template x-if="tone === 'warning'">@include('components.icons.exclamation', ['class' => 'w-5 h-5'])</template>
                    <template x-if="tone === 'primary'">@include('components.icons.exclamation', ['class' => 'w-5 h-5'])</template>
                    <template x-if="tone === 'success'">@include('components.icons.check-circle', ['class' => 'w-5 h-5'])</template>
                </span>
                <div class="min-w-0 pt-0.5">
                    <h2 id="mc-confirm-title" class="text-base font-semibold text-gray-900" x-text="title"></h2>
                    <p id="mc-confirm-msg" class="mt-1.5 text-sm leading-relaxed text-gray-500" x-text="message"></p>
                </div>
            </div>
            <div class="mt-4 flex flex-col-reverse gap-2 border-t border-gray-100 bg-gray-50/60 px-6 py-4 sm:flex-row sm:justify-end">
                <button type="button" class="btn btn-secondary" @click="cancel()" x-text="cancelText"></button>
                <button type="button" x-ref="ok" class="btn" @click="accept()" x-text="okText"
                        :class="{ 'btn-danger': tone === 'danger', 'bg-amber-500 text-white hover:bg-amber-600 shadow-sm': tone === 'warning', 'btn-primary': tone === 'primary', 'btn-success': tone === 'success' }"></button>
            </div>
        </div>
    </div>

    <!-- Toasts -->
    <div class="pointer-events-none fixed inset-x-0 bottom-4 z-[95] flex flex-col items-center gap-2 px-4 sm:inset-x-auto sm:right-6 sm:items-end" aria-live="polite">
        <template x-for="t in toasts" :key="t.id">
            <div x-show="t.show" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-2xl border bg-white px-4 py-3 shadow-pop"
                 :class="{ 'border-green-200': t.type === 'success', 'border-red-200': t.type === 'error', 'border-gray-200': t.type === 'info' }" role="status">
                <span class="mt-0.5 h-2 w-2 shrink-0 rounded-full" :class="{ 'bg-green-500': t.type === 'success', 'bg-red-500': t.type === 'error', 'bg-primary-500': t.type === 'info' }"></span>
                <p class="flex-1 text-sm text-gray-700" x-text="t.text"></p>
                <button type="button" class="-mr-1 rounded-lg p-0.5 text-gray-400 hover:text-gray-700" @click="t.show = false" aria-label="Dismiss">@include('components.icons.x-mark', ['class' => 'w-4 h-4'])</button>
            </div>
        </template>
    </div>
</div>

<script>
    function mcDialogs() {
        return {
            open: false, title: '', message: '', okText: 'Confirm', cancelText: 'Cancel', tone: 'danger', resolver: null,
            toasts: [], seq: 0,
            init() {
                window.mcConfirm = (opts = {}) => new Promise((resolve) => {
                    if (typeof opts === 'string') opts = { message: opts };
                    this.title = opts.title || 'Are you sure?';
                    this.message = opts.message || '';
                    this.okText = opts.ok || 'Confirm';
                    this.cancelText = opts.cancel || 'Cancel';
                    this.tone = opts.tone || 'danger';
                    this.resolver = resolve;
                    this.open = true;
                    this.$nextTick(() => this.$refs.ok && this.$refs.ok.focus());
                });
                window.mcToast = (text, type = 'info') => {
                    const t = { id: ++this.seq, text, type, show: true };
                    this.toasts.push(t);
                    setTimeout(() => { t.show = false; }, 4500);
                    setTimeout(() => { this.toasts = this.toasts.filter((x) => x.id !== t.id); }, 5000);
                };
                // Native alert() → toast. Keeps existing scripts working, but on-brand.
                window.alert = (msg) => window.mcToast(String(msg ?? ''), /fail|error|could not|cannot|invalid/i.test(String(msg)) ? 'error' : 'info');

                // One delegated handler for every [data-confirm] form, link or button.
                document.addEventListener('submit', (e) => this.intercept(e, e.target), true);
                document.addEventListener('click', (e) => {
                    const el = e.target.closest('a[data-confirm], button[data-confirm]:not([type=submit])');
                    if (el) this.intercept(e, el);
                }, true);
            },
            async intercept(e, el) {
                if (!el || !el.dataset || el.dataset.confirm === undefined || el.dataset.confirmed === '1') return;
                e.preventDefault();
                e.stopImmediatePropagation();
                const d = el.dataset;
                const ok = await window.mcConfirm({ title: d.confirmTitle, message: d.confirm, ok: d.confirmOk, tone: d.confirmTone });
                if (!ok) return;
                el.dataset.confirmed = '1';
                if (el.tagName === 'FORM') {
                    // requestSubmit keeps the submitter button + HTML validation.
                    el.requestSubmit ? el.requestSubmit(e.submitter || undefined) : el.submit();
                } else {
                    el.click();
                }
                setTimeout(() => { delete el.dataset.confirmed; }, 0);
            },
            accept() { this.open = false; this.resolver && this.resolver(true); this.resolver = null; },
            cancel() { this.open = false; this.resolver && this.resolver(false); this.resolver = null; },
        };
    }
</script>
