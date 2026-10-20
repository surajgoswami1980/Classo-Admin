@extends('layouts.app')
@section('title', 'Payment Settings — ' . $school->name)
@section('page-title', 'Payment Settings')

@php
    $gw = $defaults['gateway'];
    $gwLabel = $gw === 'cashfree' ? 'Cashfree' : 'Razorpay';
    $splitName = $gw === 'cashfree' ? 'Easy Split' : 'Route';
    $platformPct = old('platform_fee_percent', $current->platform_fee_percent ?? null);
    $gatewayPct = old('gateway_fee_percent', $current->gateway_fee_percent ?? null);
    $splitOn = (bool) old('split_enabled', $current->split_enabled ?? false);
    $linked = old('razorpay_linked_account_id', $current->razorpay_linked_account_id ?? '');
    $vendor = old('cashfree_vendor_id', $current->cashfree_vendor_id ?? '');
    $activeAccount = $gw === 'cashfree' ? $vendor : $linked;
    $savedSplit = (bool) ($current->split_enabled ?? false);
    $mode = !$defaults['split_product_on'] ? 'product_off' : ($savedSplit && $activeAccount ? 'auto' : ($savedSplit ? 'missing' : 'manual'));
    $inr = fn ($n) => '₹' . number_format((float) $n, 2);
    $pct = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
    $card = 'rounded-2xl border border-gray-200/80 bg-white shadow-[0_1px_2px_rgba(16,24,40,.04)]';
@endphp

@section('content')
<div class="mx-auto max-w-5xl space-y-6"
     x-data="{
        split: {{ $splitOn ? 'true' : 'false' }},
        platform: '{{ $platformPct }}',
        route: '{{ $gatewayPct }}',
        amount: 10000,
        defPlatform: {{ $defaults['platform_fee_percent'] }},
        defRoute: {{ $defaults['gateway_fee_percent'] }},
        get p() { return this.platform === '' || this.platform === null ? this.defPlatform : parseFloat(this.platform) || 0 },
        get r() { return this.route === '' || this.route === null ? this.defRoute : parseFloat(this.route) || 0 },
        get a() { return Math.max(0, parseFloat(this.amount) || 0) },
        money(n) { return '₹' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) },
        get pf() { return this.a * this.p / 100 },
        get rf() { return this.a * this.r / 100 },
        get share() { return Math.max(0, this.a - this.pf - this.rf) },
        width(v) { return this.a ? (v / this.a * 100).toFixed(2) + '%' : '0%' },
     }">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.schools.index') }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 shadow-sm transition hover:text-gray-900" aria-label="Back to schools">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            </a>
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-lg font-bold text-white shadow-md shadow-primary-600/20">{{ strtoupper(substr($school->name, 0, 1)) }}</div>
            <div>
                <h2 class="text-xl font-bold tracking-tight text-gray-900">{{ $school->name }}</h2>
                <p class="text-sm text-gray-500"><span class="font-mono">{{ $school->code }}</span> · payouts via {{ $gwLabel }} {{ $splitName }}</p>
            </div>
        </div>
        <a href="{{ route('admin.integrations.index', ['tab' => 'payments']) }}" class="inline-flex items-center gap-2 self-start rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 sm:self-auto">
            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75"/></svg>
            Gateway & default fees
        </a>
    </div>

    {{-- Mode banner --}}
    @php
        $banners = [
            'auto' => ['bg-green-50 border-green-200 text-green-900', 'text-green-600', 'Automatic payouts are on', "Each payment sends this school's share to its {$gwLabel} {$splitName} account."],
            'manual' => ['bg-gray-50 border-gray-200 text-gray-800', 'text-gray-500', 'Platform default — manual settlement', "Payments are collected on the platform {$gwLabel} account. You pay this school its share yourself."],
            'missing' => ['bg-amber-50 border-amber-200 text-amber-900', 'text-amber-600', "Split is on, but there's no {$gwLabel} account", "Add the {$gwLabel} " . ($gw === 'cashfree' ? 'vendor ID' : 'linked account ID') . ' below — until then payments fall back to manual settlement.'],
            'product_off' => ['bg-amber-50 border-amber-200 text-amber-900', 'text-amber-600', "{$gwLabel} {$splitName} is switched off platform-wide", 'Turn it on in Integrations → Payments to send shares automatically. Until then every school is on manual settlement.'],
        ];
        [$bCls, $iCls, $bTitle, $bText] = $banners[$mode];
    @endphp
    <div class="flex gap-3 rounded-2xl border p-4 {{ $bCls }}">
        <svg class="mt-0.5 h-5 w-5 shrink-0 {{ $iCls }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            @if($mode === 'auto')<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>@else<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>@endif
        </svg>
        <div class="text-sm">
            <p class="font-semibold">{{ $bTitle }}</p>
            <p class="mt-0.5 opacity-90">{{ $bText }}</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach([
            ['Collected online', $inr($stats->collected ?? 0), (int) ($stats->payments ?? 0) . ' payments', 'text-gray-900'],
            ['Platform + route fees', $inr(($stats->platform_fee ?? 0) + ($stats->gateway_fee ?? 0)), 'Kept by the platform', 'text-gray-900'],
            ['Auto-settled', $inr($stats->auto_settled ?? 0), 'Sent via ' . $splitName, 'text-green-700'],
            ['Owed to school', $inr($stats->pending_manual ?? 0), 'Manual settlement', ($stats->pending_manual ?? 0) > 0 ? 'text-amber-700' : 'text-gray-900'],
        ] as [$label, $value, $sub, $color])
            <div class="{{ $card }} p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $label }}</p>
                <p class="mt-1.5 text-lg font-bold {{ $color }}">{{ $value }}</p>
                <p class="text-xs text-gray-400">{{ $sub }}</p>
            </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('admin.schools.payment-settings.update', $school) }}" class="grid gap-6 lg:grid-cols-5">
        @csrf
        @method('PUT')

        <div class="space-y-6 lg:col-span-3">
            {{-- Split toggle + accounts --}}
            <section class="{{ $card }} overflow-hidden">
                <label class="flex cursor-pointer items-start justify-between gap-6 p-6">
                    <span>
                        <span class="block text-base font-semibold text-gray-900">Automatic split payout</span>
                        <span class="mt-0.5 block text-sm text-gray-500">Send this school's share straight to its own bank account.</span>
                    </span>
                    <span class="relative mt-1 inline-flex shrink-0 items-center">
                        <input type="hidden" name="split_enabled" value="0">
                        <input type="checkbox" name="split_enabled" value="1" x-model="split" class="peer sr-only">
                        <span class="h-6 w-11 rounded-full bg-gray-200 transition-colors peer-checked:bg-primary-600 peer-focus-visible:ring-4 peer-focus-visible:ring-primary-500/20"></span>
                        <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                        <span class="sr-only">Enable automatic split payout</span>
                    </span>
                </label>

                <div class="space-y-5 border-t border-gray-100 p-6 transition" :class="split ? '' : 'opacity-50'">
                    @foreach([
                        ['razorpay', 'razorpay_linked_account_id', 'Razorpay Route — Linked account ID', 'acc_XXXXXXXXXXXXXX', 'Razorpay Dashboard → Route → Linked Accounts', $linked, 'bg-[#0b72e7]', 'R'],
                        ['cashfree', 'cashfree_vendor_id', 'Cashfree Easy Split — Vendor ID', 'school_vendor_01', 'Cashfree Dashboard → Easy Split → Vendors', $vendor, 'bg-[#6933d3]', 'C'],
                    ] as [$prov, $field, $label, $ph, $hint, $val, $color, $letter])
                        <div class="rounded-xl border p-4 {{ $gw === $prov ? 'border-primary-200 bg-primary-50/40' : 'border-gray-200' }}">
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <label for="{{ $field }}" class="flex items-center gap-2 text-sm font-semibold text-gray-900">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-md text-[11px] font-bold text-white {{ $color }}">{{ $letter }}</span>
                                    {{ $label }}
                                </label>
                                @if($gw === $prov)
                                    <span class="rounded-full bg-primary-600 px-2 py-0.5 text-[11px] font-semibold text-white">In use</span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500">Standby</span>
                                @endif
                            </div>
                            <input id="{{ $field }}" type="text" name="{{ $field }}" value="{{ $val }}" placeholder="{{ $ph }}" autocomplete="off" spellcheck="false"
                                   class="w-full rounded-xl border bg-white px-3.5 py-2.5 font-mono text-sm transition focus:outline-none focus:ring-4 focus:ring-primary-500/15 focus:border-primary-500 {{ $errors->has($field) ? 'border-red-400' : 'border-gray-200' }}">
                            @error($field)
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @else
                                <p class="mt-1.5 text-xs text-gray-500">{{ $hint }}{{ $gw !== $prov ? " · used if you switch the default gateway to " . ucfirst($prov) : '' }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Fees --}}
            <section class="{{ $card }} p-6">
                <h3 class="text-base font-semibold text-gray-900">Fees for this school</h3>
                <p class="mt-0.5 text-sm text-gray-500">Leave blank to use the platform defaults ({{ $pct($defaults['platform_fee_percent']) }}% + {{ $pct($defaults['gateway_fee_percent']) }}%).</p>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    @foreach([
                        ['platform_fee_percent', 'Platform fee', 'platform', $defaults['platform_fee_percent']],
                        ['gateway_fee_percent', 'Route / split fee', 'route', $defaults['gateway_fee_percent']],
                    ] as [$field, $label, $model, $def])
                        <div>
                            <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                            <div class="relative">
                                <input id="{{ $field }}" type="number" step="0.01" min="0" max="50" name="{{ $field }}" x-model="{{ $model }}" placeholder="{{ $pct($def) }}"
                                       class="w-full rounded-xl border bg-white px-3.5 py-2.5 pr-9 text-sm transition focus:outline-none focus:ring-4 focus:ring-primary-500/15 focus:border-primary-500 {{ $errors->has($field) ? 'border-red-400' : 'border-gray-200' }}">
                                <span class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center text-sm text-gray-400">%</span>
                            </div>
                            <p class="mt-1.5 text-xs" :class="{{ $model }} === '' || {{ $model }} === null ? 'text-gray-400' : 'text-primary-700'"
                               x-text="{{ $model }} === '' || {{ $model }} === null ? 'Using default' : 'Custom for this school'"></p>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        {{-- Live calculator --}}
        <aside class="lg:col-span-2">
            <div class="{{ $card }} sticky top-6 overflow-hidden">
                <div class="border-b border-gray-100 p-5">
                    <p class="text-sm font-semibold text-gray-900">Payout preview</p>
                    <label for="calc-amount" class="mt-3 block text-xs font-medium text-gray-500">If a parent pays</label>
                    <div class="relative mt-1">
                        <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-gray-400">₹</span>
                        <input id="calc-amount" type="number" min="0" step="100" x-model="amount" class="form-input bg-gray-50 pl-8 pr-3 text-lg font-bold text-gray-900">
                    </div>
                </div>
                <div class="space-y-4 p-5">
                    <div class="flex h-3 overflow-hidden rounded-full bg-gray-100" aria-hidden="true">
                        <div class="bg-green-500 transition-all" :style="{ width: width(share) }"></div>
                        <div class="bg-primary-500 transition-all" :style="{ width: width(pf) }"></div>
                        <div class="bg-amber-400 transition-all" :style="{ width: width(rf) }"></div>
                    </div>
                    <dl class="space-y-2.5 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="flex items-center gap-2 text-gray-600"><span class="h-2.5 w-2.5 rounded-full bg-primary-500"></span>Platform fee (<span x-text="p"></span>%)</dt>
                            <dd class="font-semibold text-gray-900" x-text="money(pf)"></dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="flex items-center gap-2 text-gray-600"><span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span>Route fee (<span x-text="r"></span>%)</dt>
                            <dd class="font-semibold text-gray-900" x-text="money(rf)"></dd>
                        </div>
                        <div class="flex items-center justify-between border-t border-dashed border-gray-200 pt-2.5">
                            <dt class="flex items-center gap-2 font-semibold text-gray-900"><span class="h-2.5 w-2.5 rounded-full bg-green-500"></span>School receives</dt>
                            <dd class="text-lg font-bold text-green-700" x-text="money(share)"></dd>
                        </div>
                    </dl>
                    <p class="rounded-xl px-3 py-2 text-xs" :class="split ? 'bg-green-50 text-green-800' : 'bg-amber-50 text-amber-800'"
                       x-text="split ? 'Sent automatically via {{ $gwLabel }} {{ $splitName }}.' : 'Held in the platform account — settle with the school manually.'"></p>
                </div>
                <div class="flex gap-3 border-t border-gray-100 bg-gray-50/60 p-5">
                    <a href="{{ route('admin.schools.index') }}" class="btn btn-secondary flex-1">Cancel</a>
                    <button type="submit" class="btn btn-primary flex-1 shadow-md">Save</button>
                </div>
            </div>
        </aside>
    </form>
</div>
@endsection
