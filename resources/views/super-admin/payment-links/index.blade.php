@extends('layouts.app')
@section('title', 'Payment Links')
@section('page-title', 'Payment Links')

@php
    $card = 'rounded-2xl border border-gray-200/80 bg-white shadow-[0_1px_2px_rgba(16,24,40,.04)]';
    $inr = fn ($n) => '₹' . number_format((float) $n, 2);
    $input = 'w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 transition hover:border-gray-300 focus:outline-none focus:ring-4 focus:ring-primary-500/15 focus:border-primary-500';
    $label = 'mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500';
    $statusMap = [
        'created' => ['Awaiting payment', 'bg-amber-50 text-amber-700 ring-amber-600/20', 'bg-amber-500'],
        'partially_paid' => ['Partly paid', 'bg-sky-50 text-sky-700 ring-sky-600/20', 'bg-sky-500'],
        'paid' => ['Paid', 'bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'bg-emerald-500'],
        'expired' => ['Expired', 'bg-gray-100 text-gray-600 ring-gray-500/20', 'bg-gray-400'],
        'cancelled' => ['Cancelled', 'bg-gray-100 text-gray-600 ring-gray-500/20', 'bg-gray-400'],
        'failed' => ['Failed', 'bg-red-50 text-red-700 ring-red-600/20', 'bg-red-500'],
    ];
    $gwName = $gateway === 'cashfree' ? 'Cashfree' : 'Razorpay';
    $createdId = (int) request('created');
@endphp

@section('content')
<div class="mx-auto max-w-6xl space-y-6" x-data="{ open: {{ ($prefill['open'] ?? false) || $errors->any() ? 'true' : 'false' }} }">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Payment Links</h2>
            <p class="mt-1 text-sm text-gray-500">Send a secure {{ $gwName }} link for onboarding, setup or subscription fees. Paid in full into your platform account.</p>
        </div>
        <button type="button" @click="open = true" @disabled(!$gatewayReady)
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary-600/20 transition hover:bg-primary-700 active:scale-[.98] disabled:cursor-not-allowed disabled:opacity-50">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New payment link
        </button>
    </div>

    @unless($gatewayReady)
        <div class="flex gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
            <p>{{ $gwName }} is the default gateway but its keys aren't set. <a href="{{ route('admin.integrations.index', ['tab' => 'payments']) }}" class="font-semibold underline">Add them in Integrations</a> to create links.</p>
        </div>
    @endunless

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach([
            ['Collected', $inr($stats->collected ?? 0), (int) ($stats->paid_count ?? 0) . ' paid links', 'text-emerald-700'],
            ['Awaiting payment', $inr($stats->pending_amount ?? 0), (int) ($stats->pending_count ?? 0) . ' open links', 'text-amber-700'],
            ['Links sent', (int) ($stats->total ?? 0), 'All time', 'text-gray-900'],
            ['Conversion', ($stats->total ?? 0) ? round(($stats->paid_count / $stats->total) * 100) . '%' : '—', 'Paid ÷ sent', 'text-gray-900'],
        ] as [$l, $v, $sub, $c])
            <div class="{{ $card }} p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $l }}</p>
                <p class="mt-1.5 text-xl font-bold {{ $c }}">{{ $v }}</p>
                <p class="text-xs text-gray-400">{{ $sub }}</p>
            </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
            <input type="search" name="q" value="{{ $q }}" placeholder="Search name, phone, email, school or reference" class="{{ $input }} pl-10">
        </div>
        <div class="flex gap-1 overflow-x-auto rounded-xl border border-gray-200 bg-white p-1">
            @foreach(['' => 'All', 'created' => 'Awaiting', 'paid' => 'Paid', 'expired' => 'Expired', 'cancelled' => 'Cancelled'] as $val => $lbl)
                <a href="{{ route('admin.payment-links.index', array_filter(['status' => $val, 'q' => $q])) }}"
                   class="whitespace-nowrap rounded-lg px-3 py-1.5 text-sm font-medium transition {{ (string) $status === (string) $val ? 'bg-primary-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' }}">{{ $lbl }}</a>
            @endforeach
        </div>
    </form>

    {{-- List --}}
    <div class="{{ $card }} overflow-hidden">
        @forelse($links as $link)
            @php
                [$sLabel, $sCls, $sDot] = $statusMap[$link->status] ?? $statusMap['created'];
                $waText = rawurlencode("Hello {$link->customer_name}, please complete your payment of " . $inr($link->amount) . " for {$link->description}: {$link->short_url}");
                $waPhone = preg_replace('/\D/', '', $link->customer_phone);
                if (strlen($waPhone) === 10) $waPhone = '91' . $waPhone;
            @endphp
            <div class="flex flex-col gap-4 border-b border-gray-100 p-5 last:border-0 lg:flex-row lg:items-center {{ $createdId === $link->id ? 'bg-primary-50/50' : '' }}">
                <div class="flex min-w-0 flex-1 items-start gap-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $link->status === 'paid' ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-50 text-gray-500' }}">
                        @if($link->status === 'paid')
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        @else
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/></svg>
                        @endif
                    </span>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="truncate font-semibold text-gray-900">{{ $link->customer_name }}</p>
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-[11px] font-medium ring-1 ring-inset {{ $sCls }}"><span class="h-1.5 w-1.5 rounded-full {{ $sDot }}"></span>{{ $sLabel }}</span>
                            <span class="rounded-md bg-gray-100 px-1.5 py-0.5 text-[11px] font-medium text-gray-600">{{ $purposes[$link->purpose] ?? ucfirst($link->purpose) }}</span>
                        </div>
                        <p class="mt-0.5 truncate text-sm text-gray-500">{{ $link->description }}{{ $link->school_name ? ' · ' . $link->school_name : '' }}</p>
                        <p class="mt-1 text-xs text-gray-400">
                            <span class="font-mono">{{ $link->reference }}</span> · {{ $link->customer_phone }}
                            · {{ $link->status === 'paid' && $link->paid_at ? 'Paid ' . \Carbon\Carbon::parse($link->paid_at)->diffForHumans() : 'Sent ' . \Carbon\Carbon::parse($link->created_at)->diffForHumans() }}
                            @if($link->status === 'created' && $link->expires_at) · expires {{ \Carbon\Carbon::parse($link->expires_at)->format('d M') }}@endif
                            · {{ ucfirst($link->gateway) }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-4 lg:justify-end">
                    <p class="text-lg font-bold text-gray-900">{{ $inr($link->amount) }}</p>
                    <div class="flex items-center gap-1.5" x-data="{ copied: false }">
                        @if($link->status === 'created' && $link->short_url)
                            <button type="button" title="Copy link" aria-label="Copy link"
                                    @click="navigator.clipboard.writeText('{{ $link->short_url }}'); copied = true; setTimeout(() => copied = false, 1500)"
                                    class="flex h-9 items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184"/></svg>
                                <span x-text="copied ? 'Copied' : 'Copy'"></span>
                            </button>
                            <a href="https://wa.me/{{ $waPhone }}?text={{ $waText }}" target="_blank" rel="noopener" title="Share on WhatsApp" aria-label="Share on WhatsApp"
                               class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#25D366] text-white hover:brightness-95">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.21 3.08c.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.69.25-1.29.17-1.41-.07-.12-.27-.2-.57-.35M12.05 21.79h-.01a9.87 9.87 0 0 1-5.03-1.38l-.36-.21-3.74.98 1-3.65-.24-.37a9.86 9.86 0 0 1-1.51-5.26c0-5.45 4.44-9.88 9.89-9.88a9.82 9.82 0 0 1 6.99 2.9 9.82 9.82 0 0 1 2.89 6.99c0 5.45-4.44 9.88-9.88 9.88m8.41-18.3A11.82 11.82 0 0 0 12.05 0C5.5 0 .16 5.34.16 11.89c0 2.1.55 4.14 1.59 5.95L.06 24l6.3-1.65a11.88 11.88 0 0 0 5.68 1.45h.01c6.55 0 11.89-5.34 11.89-11.89 0-3.18-1.24-6.16-3.49-8.41"/></svg>
                            </a>
                        @endif
                        @if(in_array($link->status, ['created', 'partially_paid'], true))
                            <form method="POST" action="{{ route('admin.payment-links.refresh', $link->id) }}">@csrf
                                <button title="Refresh status from {{ ucfirst($link->gateway) }}" aria-label="Refresh status" class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 hover:text-gray-900">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                </button>
                            </form>
                        @endif
                        @if($link->status === 'created')
                            <form method="POST" action="{{ route('admin.payment-links.cancel', $link->id) }}" onsubmit="return confirm('Cancel this payment link? The customer will no longer be able to pay it.')">@csrf
                                <button title="Cancel link" aria-label="Cancel link" class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-400 hover:border-red-200 hover:bg-red-50 hover:text-red-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="px-6 py-16 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-50 text-primary-600">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/></svg>
                </div>
                <p class="mt-4 font-semibold text-gray-900">{{ $q || $status ? 'No links match' : 'No payment links yet' }}</p>
                <p class="mt-1 text-sm text-gray-500">Create one to collect an onboarding or setup fee from a new client.</p>
                @unless($q || $status)
                    <button type="button" @click="open = true" class="mt-5 rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">New payment link</button>
                @endunless
            </div>
        @endforelse
        @if($links->hasPages())
            <div class="border-t border-gray-100 px-5 py-3">{{ $links->links() }}</div>
        @endif
    </div>

    {{-- Create drawer --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-labelledby="pl-title" @keydown.escape.window="open = false">
        <div x-show="open" x-transition.opacity class="absolute inset-0 bg-gray-900/40 backdrop-blur-[2px]" @click="open = false"></div>
        <div x-show="open" x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
             x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
             class="absolute inset-y-0 right-0 flex w-full max-w-lg flex-col bg-white shadow-2xl"
             x-data="{
                schools: @js($schools->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'contact' => $s->principal_name ?: $s->name, 'email' => $s->email, 'phone' => $s->phone])),
                schoolId: '{{ old('school_id', $prefill['school_id'] ?? '') }}',
                amount: '{{ old('amount') }}',
                pick() {
                    const s = this.schools.find(x => String(x.id) === String(this.schoolId));
                    if (!s) return;
                    this.$refs.name.value ||= s.contact; this.$refs.email.value ||= (s.email || ''); this.$refs.phone.value ||= (s.phone || '');
                    if (!this.$refs.desc.value) this.$refs.desc.value = 'Onboarding fee — ' + s.name;
                },
             }">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <div>
                    <h3 id="pl-title" class="text-lg font-bold text-gray-900">New payment link</h3>
                    <p class="text-xs text-gray-500">Created on {{ $gwName }} · paid into your platform account</p>
                </div>
                <button type="button" @click="open = false" class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-700" aria-label="Close">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.payment-links.store') }}" class="flex min-h-0 flex-1 flex-col">
                @csrf
                <input type="hidden" name="enquiry_id" value="{{ old('enquiry_id', $prefill['enquiry_id'] ?? '') }}">
                <div class="flex-1 space-y-5 overflow-y-auto px-6 py-5">
                    <div>
                        <span class="{{ $label }}">Purpose</span>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($purposes as $val => $lbl)
                                <label class="cursor-pointer">
                                    <input type="radio" name="purpose" value="{{ $val }}" class="peer sr-only" @checked(old('purpose', 'onboarding') === $val)>
                                    <span class="block rounded-xl border-2 border-gray-200 px-3 py-2.5 text-center text-sm font-medium text-gray-700 transition peer-checked:border-primary-500 peer-checked:bg-primary-50 peer-checked:text-primary-700 peer-focus-visible:ring-4 peer-focus-visible:ring-primary-500/20">{{ $lbl }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label for="pl-amount" class="{{ $label }}">Amount</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-lg text-gray-400">₹</span>
                            <input id="pl-amount" type="number" name="amount" min="1" step="0.01" required x-model="amount" placeholder="0.00"
                                   class="{{ $input }} py-3 pl-9 text-2xl font-bold @error('amount') border-red-400 @enderror">
                        </div>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @foreach([5000, 10000, 25000, 50000] as $preset)
                                <button type="button" @click="amount = {{ $preset }}" class="rounded-lg border border-gray-200 px-2.5 py-1 text-xs font-medium text-gray-600 hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700">₹{{ number_format($preset) }}</button>
                            @endforeach
                        </div>
                        @error('amount')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="pl-desc" class="{{ $label }}">Description</label>
                        <input id="pl-desc" x-ref="desc" type="text" name="description" required maxlength="255" value="{{ old('description', $prefill['description'] ?? '') }}" placeholder="Onboarding fee — Green Valley School" class="{{ $input }}">
                        <p class="mt-1.5 text-xs text-gray-500">Shown to the customer on the payment page.</p>
                    </div>

                    <div class="rounded-2xl border border-gray-200 p-4 space-y-4">
                        <p class="text-sm font-semibold text-gray-900">Customer</p>
                        <div>
                            <label for="pl-school" class="{{ $label }}">Existing school <span class="normal-case font-normal text-gray-400">(optional)</span></label>
                            <select id="pl-school" name="school_id" x-model="schoolId" @change="pick()" class="{{ $input }}">
                                <option value="">— New client / not onboarded yet —</option>
                                @foreach($schools as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="pl-name" class="{{ $label }}">Name</label>
                            <input id="pl-name" x-ref="name" type="text" name="customer_name" required value="{{ old('customer_name', $prefill['customer_name'] ?? '') }}" class="{{ $input }} @error('customer_name') border-red-400 @enderror">
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="pl-phone" class="{{ $label }}">Mobile</label>
                                <input id="pl-phone" x-ref="phone" type="tel" name="customer_phone" required inputmode="tel" value="{{ old('customer_phone', $prefill['customer_phone'] ?? '') }}" placeholder="98765 43210" class="{{ $input }} @error('customer_phone') border-red-400 @enderror">
                                @error('customer_phone')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="pl-email" class="{{ $label }}">Email <span class="normal-case font-normal text-gray-400">(optional)</span></label>
                                <input id="pl-email" x-ref="email" type="email" name="customer_email" value="{{ old('customer_email', $prefill['customer_email'] ?? '') }}" class="{{ $input }} @error('customer_email') border-red-400 @enderror">
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="pl-exp" class="{{ $label }}">Expires in</label>
                            <select id="pl-exp" name="expires_in_days" class="{{ $input }}">
                                @foreach([3 => '3 days', 7 => '7 days', 15 => '15 days', 30 => '30 days'] as $d => $l)
                                    <option value="{{ $d }}" @selected((int) old('expires_in_days', 7) === $d)>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <span class="{{ $label }}">{{ $gwName }} also sends</span>
                            <div class="flex gap-4 pt-2.5 text-sm text-gray-700">
                                <label class="inline-flex items-center gap-2"><input type="checkbox" name="notify_sms" value="1" @checked(old('notify_sms', true)) class="rounded border-gray-300 text-primary-600"> SMS</label>
                                <label class="inline-flex items-center gap-2"><input type="checkbox" name="notify_email" value="1" @checked(old('notify_email', true)) class="rounded border-gray-300 text-primary-600"> Email</label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="pl-notes" class="{{ $label }}">Internal note <span class="normal-case font-normal text-gray-400">(optional)</span></label>
                        <textarea id="pl-notes" name="notes" rows="2" maxlength="1000" placeholder="Not shown to the customer" class="{{ $input }}">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="flex gap-3 border-t border-gray-100 bg-gray-50/60 px-6 py-4">
                    <button type="button" @click="open = false" class="flex-1 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="flex-[2] rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-primary-600/20 transition hover:bg-primary-700 active:scale-[.98]">
                        Create link<span x-show="amount" x-text="' · ₹' + Number(amount).toLocaleString('en-IN')"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
