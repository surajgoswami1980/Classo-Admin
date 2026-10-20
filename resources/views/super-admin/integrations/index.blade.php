@extends('layouts.app')
@section('title', 'Integrations')
@section('page-title', 'Integrations')

@php
    $f = fn ($group, $key) => view('super-admin.integrations._field', [
        'group' => $group, 'key' => $key, 'def' => $defs[$group][$key],
        'value' => $values[$group][$key] ?? null, 'source' => $sources[$group][$key] ?? 'none',
    ])->render();
    $gw = old('payment.gateway', $values['payment']['gateway'] ?? 'razorpay');
    $tabs = [
        'payments' => ['Payments', 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z', $status[$gw] ?? 'not_configured'],
        'firebase' => ['Firebase push', 'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0', $status['firebase']],
        'email' => ['Email', 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75', $status['email']],
        'sms' => ['SMS', 'M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z', $status['sms']],
    ];
    $card = 'rounded-2xl border border-gray-200/80 bg-white shadow-[0_1px_2px_rgba(16,24,40,.04)]';
@endphp

@section('content')
<div class="mx-auto max-w-6xl space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Integrations</h2>
            <p class="mt-1 text-sm text-gray-500">Global credentials for every school. Saved here, encrypted, and used by the admin panel and the API — no <code class="rounded bg-gray-100 px-1 text-[12px]">.env</code> edits needed.</p>
        </div>
        <div class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs text-gray-600 shadow-sm">
            <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
            Secrets encrypted with AES-256-GCM
        </div>
    </div>

    @unless($cryptoReady)
        <div class="flex gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
            <div>
                <p class="font-semibold">Encryption key missing — saving is disabled.</p>
                <p class="mt-1">Add the same <code class="rounded bg-red-100 px-1">SETTINGS_ENCRYPTION_KEY</code> to the admin and API <code>.env</code> files, then reload. Generate one with
                    <code class="rounded bg-red-100 px-1">php -r "echo base64_encode(random_bytes(32));"</code></p>
            </div>
        </div>
    @endunless

    {{-- Overview cards --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach($tabs as $key => [$label, $icon, $st])
            <a href="{{ route('admin.integrations.index', ['tab' => $key]) }}"
               class="{{ $card }} group p-4 transition hover:-translate-y-0.5 hover:shadow-md {{ $tab === $key ? 'ring-2 ring-primary-500/60 border-transparent' : '' }}">
                <div class="flex items-start justify-between gap-2">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $tab === $key ? 'bg-primary-600 text-white' : 'bg-gray-50 text-gray-600 group-hover:bg-primary-50 group-hover:text-primary-600' }} transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                    </span>
                    @include('super-admin.integrations._status', ['s' => $st])
                </div>
                <p class="mt-3 text-sm font-semibold text-gray-900">{{ $label }}</p>
                <p class="mt-0.5 truncate text-xs text-gray-500">
                    @switch($key)
                        @case('payments') Default: {{ $gw === 'cashfree' ? 'Cashfree' : 'Razorpay' }} @break
                        @case('firebase') {{ $firebaseProject ?: 'No service account' }} @break
                        @case('email') {{ ['smtp' => 'SMTP', 'ses' => 'Amazon SES', 'log' => 'Log only'][$values['email']['driver'] ?? 'log'] ?? 'Log only' }} @break
                        @case('sms') {{ ($values['sms']['provider'] ?? 'log') === 'http' ? 'HTTP gateway' : 'Log only' }} @break
                    @endswitch
                </p>
            </a>
        @endforeach
    </div>

    {{-- ═══════════════════════ PAYMENTS ═══════════════════════ --}}
    @if($tab === 'payments')
    <form method="POST" action="{{ route('admin.integrations.update', 'payments') }}" class="space-y-6" x-data="{ gw: '{{ $gw }}' }">
        @csrf @method('PUT')

        {{-- How money flows --}}
        <section class="{{ $card }} overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4">
                <h3 class="font-semibold text-gray-900">How payments flow</h3>
                <p class="text-sm text-gray-500">Every online payment (fees, store, events) is collected on the default gateway below.</p>
            </div>
            <ol class="grid gap-px bg-gray-100 sm:grid-cols-3">
                @foreach([
                    ['1', 'Parent pays', 'Checkout opens on the platform gateway.'],
                    ['2', 'Platform keeps fees', 'Platform fee + route/split fee stay in your account.'],
                    ['3', 'School gets its share', 'Sent automatically via Route / Easy Split when the school has a linked account. Otherwise marked for manual settlement.'],
                ] as [$n, $t, $d])
                    <li class="bg-white p-5">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-primary-50 text-xs font-bold text-primary-700">{{ $n }}</span>
                        <p class="mt-3 text-sm font-semibold text-gray-900">{{ $t }}</p>
                        <p class="mt-1 text-xs leading-relaxed text-gray-500">{{ $d }}</p>
                    </li>
                @endforeach
            </ol>
        </section>

        {{-- Default gateway + fees --}}
        <section class="{{ $card }} p-6">
            <div class="grid gap-6 lg:grid-cols-3">
                <div>
                    <h3 class="font-semibold text-gray-900">Default gateway & fees</h3>
                    <p class="mt-1 text-sm text-gray-500">Schools can override the fees on their own Payment Settings page.</p>
                </div>
                <div class="space-y-5 lg:col-span-2">
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Default gateway</p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach(['razorpay' => ['Razorpay', 'Split with Route', $status['razorpay']], 'cashfree' => ['Cashfree', 'Split with Easy Split', $status['cashfree']]] as $val => [$name, $sub, $st])
                                <label class="relative flex cursor-pointer items-center gap-3 rounded-xl border-2 p-4 transition"
                                       :class="gw === '{{ $val }}' ? 'border-primary-500 bg-primary-50/50' : 'border-gray-200 hover:border-gray-300'">
                                    <input type="radio" name="payment[gateway]" value="{{ $val }}" x-model="gw" class="sr-only">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-sm font-bold text-white {{ $val === 'razorpay' ? 'bg-[#0b72e7]' : 'bg-[#6933d3]' }}">{{ substr($name, 0, 1) }}</span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm font-semibold text-gray-900">{{ $name }}</span>
                                        <span class="block text-xs text-gray-500">{{ $sub }}</span>
                                    </span>
                                    @include('super-admin.integrations._status', ['s' => $st])
                                    <span x-show="gw === '{{ $val }}'" x-cloak class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-primary-600 text-white shadow">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        {!! $f('payment', 'platform_fee_percent') !!}
                        {!! $f('payment', 'gateway_fee_percent') !!}
                    </div>
                </div>
            </div>
        </section>

        {{-- Razorpay --}}
        <section class="{{ $card }} overflow-hidden" :class="gw === 'razorpay' ? 'ring-1 ring-primary-200' : ''">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#0b72e7] text-sm font-bold text-white">R</span>
                    <div>
                        <h3 class="font-semibold text-gray-900">Razorpay</h3>
                        <p class="text-xs text-gray-500">{{ (int) ($splitStats->razorpay_linked ?? 0) }} of {{ $totalSchools }} schools have a Route linked account</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span x-show="gw === 'razorpay'" x-cloak class="rounded-full bg-primary-600 px-2.5 py-1 text-xs font-semibold text-white">Default</span>
                    @include('super-admin.integrations._status', ['s' => $status['razorpay']])
                </div>
            </header>
            <div class="grid gap-5 p-6 sm:grid-cols-2">
                {!! $f('razorpay', 'key_id') !!}
                {!! $f('razorpay', 'mode') !!}
                {!! $f('razorpay', 'key_secret') !!}
                {!! $f('razorpay', 'webhook_secret') !!}
                <div class="sm:col-span-2">{!! $f('razorpay', 'route_enabled') !!}</div>
                <div class="sm:col-span-2 rounded-xl bg-gray-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Webhook URL</p>
                    <div class="mt-1.5 flex items-center gap-2" x-data="{ copied: false }">
                        <code class="min-w-0 flex-1 truncate rounded-lg border border-gray-200 bg-white px-3 py-2 font-mono text-xs text-gray-800">{{ $webhooks['razorpay'] }}</code>
                        <button type="button" @click="navigator.clipboard.writeText('{{ $webhooks['razorpay'] }}'); copied = true; setTimeout(() => copied = false, 1500)" class="shrink-0 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50" x-text="copied ? 'Copied' : 'Copy'"></button>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Paste this URL and the Webhook Secret into Razorpay Dashboard → Webhooks. Events: <code>payment.captured</code>, <code>payment.failed</code>, <code>order.paid</code>, <code>transfer.processed</code>, <code>transfer.failed</code>, <code>payment_link.paid</code>, <code>payment_link.expired</code>, <code>payment_link.cancelled</code>. Use your public API domain in production.</p>
                </div>
                <div class="sm:col-span-2">@include('super-admin.integrations._test', ['target' => 'razorpay', 'label' => 'Test Razorpay keys', 'needsTo' => null])</div>
            </div>
        </section>

        {{-- Cashfree --}}
        <section class="{{ $card }} overflow-hidden" :class="gw === 'cashfree' ? 'ring-1 ring-primary-200' : ''">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#6933d3] text-sm font-bold text-white">C</span>
                    <div>
                        <h3 class="font-semibold text-gray-900">Cashfree</h3>
                        <p class="text-xs text-gray-500">{{ (int) ($splitStats->cashfree_linked ?? 0) }} of {{ $totalSchools }} schools have an Easy Split vendor</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span x-show="gw === 'cashfree'" x-cloak class="rounded-full bg-primary-600 px-2.5 py-1 text-xs font-semibold text-white">Default</span>
                    @include('super-admin.integrations._status', ['s' => $status['cashfree']])
                </div>
            </header>
            <div class="grid gap-5 p-6 sm:grid-cols-2">
                {!! $f('cashfree', 'app_id') !!}
                {!! $f('cashfree', 'env') !!}
                <div class="sm:col-span-2">{!! $f('cashfree', 'secret_key') !!}</div>
                <div class="sm:col-span-2">{!! $f('cashfree', 'split_enabled') !!}</div>
                <div class="sm:col-span-2 rounded-xl bg-gray-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Webhook URL</p>
                    <div class="mt-1.5 flex items-center gap-2" x-data="{ copied: false }">
                        <code class="min-w-0 flex-1 truncate rounded-lg border border-gray-200 bg-white px-3 py-2 font-mono text-xs text-gray-800">{{ $webhooks['cashfree'] }}</code>
                        <button type="button" @click="navigator.clipboard.writeText('{{ $webhooks['cashfree'] }}'); copied = true; setTimeout(() => copied = false, 1500)" class="shrink-0 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50" x-text="copied ? 'Copied' : 'Copy'"></button>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Paste this URL into Cashfree Dashboard → Developers → Webhooks. Events: Payment success, Payment failed, User dropped, Payment link events.</p>
                </div>
                <div class="sm:col-span-2">@include('super-admin.integrations._test', ['target' => 'cashfree', 'label' => 'Test Cashfree keys', 'needsTo' => null])</div>
            </div>
        </section>

        <div class="sticky bottom-4 z-10 flex justify-end">
            <button type="submit" @disabled(!$cryptoReady) class="rounded-xl bg-primary-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700 active:scale-[.98] disabled:cursor-not-allowed disabled:opacity-50">Save payment settings</button>
        </div>
    </form>
    @endif

    {{-- ═══════════════════════ FIREBASE ═══════════════════════ --}}
    @if($tab === 'firebase')
    <form method="POST" action="{{ route('admin.integrations.update', 'firebase') }}" class="space-y-6">
        @csrf @method('PUT')
        <section class="{{ $card }} overflow-hidden">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-amber-400 to-orange-500 text-white">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M5.8 18.6 8.3 3.1c.1-.4.6-.5.8-.2l2.6 4.9-5.9 10.8Zm13.4.3L17 4.6c-.1-.4-.6-.5-.8-.2L5.6 18.9l5.8 3.3c.4.2.9.2 1.3 0l6.5-3.3ZM14.6 8.6l-1.9-3.6c-.2-.3-.6-.3-.8 0L6.3 15.4l8.3-6.8Z"/></svg>
                    </span>
                    <div>
                        <h3 class="font-semibold text-gray-900">Firebase Cloud Messaging</h3>
                        <p class="text-xs text-gray-500">Push notifications to the student, parent and teacher apps</p>
                    </div>
                </div>
                @include('super-admin.integrations._status', ['s' => $status['firebase']])
            </header>
            <div class="grid gap-5 p-6 sm:grid-cols-2">
                <div class="sm:col-span-2">{!! $f('firebase', 'enabled') !!}</div>
                @if($firebaseProject)
                    <div class="sm:col-span-2 flex items-center gap-3 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        Saved service account for project <strong class="font-mono">{{ $firebaseProject }}</strong>
                    </div>
                @endif
                {!! $f('firebase', 'service_account_json') !!}
                <div class="sm:col-span-2 rounded-xl bg-gray-50 p-4 text-xs leading-relaxed text-gray-600">
                    The API also needs the <code class="rounded bg-white px-1">firebase-admin</code> package (<code class="rounded bg-white px-1">npm i firebase-admin</code>) to send real pushes. Without it, pushes are logged only.
                </div>
                <div class="sm:col-span-2">@include('super-admin.integrations._test', ['target' => 'firebase', 'label' => 'Validate service account', 'needsTo' => null])</div>
            </div>
        </section>
        <div class="sticky bottom-4 z-10 flex justify-end">
            <button type="submit" @disabled(!$cryptoReady) class="rounded-xl bg-primary-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700 active:scale-[.98] disabled:cursor-not-allowed disabled:opacity-50">Save Firebase settings</button>
        </div>
    </form>
    @endif

    {{-- ═══════════════════════ EMAIL ═══════════════════════ --}}
    @if($tab === 'email')
    <form method="POST" action="{{ route('admin.integrations.update', 'email') }}" class="space-y-6" x-data="{ driver: '{{ old('email.driver', $values['email']['driver'] ?? 'log') }}' }">
        @csrf @method('PUT')
        <section class="{{ $card }} overflow-hidden">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-500 text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $tabs['email'][1] }}"/></svg>
                    </span>
                    <div>
                        <h3 class="font-semibold text-gray-900">Email delivery</h3>
                        <p class="text-xs text-gray-500">Login OTPs, password resets and notifications</p>
                    </div>
                </div>
                @include('super-admin.integrations._status', ['s' => $status['email']])
            </header>
            <div class="grid gap-5 p-6 sm:grid-cols-2">
                <div class="sm:col-span-2">{!! $f('email', 'driver') !!}</div>
                {!! $f('email', 'from_address') !!}
                {!! $f('email', 'from_name') !!}

                <div x-show="driver === 'smtp'" x-cloak class="sm:col-span-2 border-t border-gray-100 pt-5">
                    <p class="text-sm font-semibold text-gray-900">SMTP server</p>
                    <p class="text-xs text-gray-500">Gmail / Google Workspace, Zoho, Outlook, Mailgun, SendGrid…</p>
                </div>
                {!! $f('email', 'smtp_host') !!}
                {!! $f('email', 'smtp_port') !!}
                {!! $f('email', 'smtp_encryption') !!}
                {!! $f('email', 'smtp_username') !!}
                <div x-show="driver === 'smtp'" x-cloak class="sm:col-span-2">{!! $f('email', 'smtp_password') !!}</div>

                <div x-show="driver === 'ses'" x-cloak class="sm:col-span-2 border-t border-gray-100 pt-5">
                    <p class="text-sm font-semibold text-gray-900">Amazon SES</p>
                    <p class="text-xs text-gray-500">The IAM user needs <code>ses:SendEmail</code> and <code>ses:SendRawEmail</code>, and the from address must be verified in SES.</p>
                </div>
                {!! $f('email', 'ses_region') !!}
                {!! $f('email', 'ses_access_key') !!}
                <div x-show="driver === 'ses'" x-cloak class="sm:col-span-2">{!! $f('email', 'ses_secret_key') !!}</div>

                <div x-show="driver === 'log'" x-cloak class="sm:col-span-2 rounded-xl bg-sky-50 p-4 text-sm text-sky-800">
                    Emails are written to the logs instead of being sent. Choose SMTP or Amazon SES to deliver real email.
                </div>
                <div x-show="driver === 'smtp'" x-cloak class="sm:col-span-2 rounded-xl bg-gray-50 p-4 text-xs text-gray-600">
                    For SMTP the API also needs <code class="rounded bg-white px-1">nodemailer</code> (<code class="rounded bg-white px-1">npm i nodemailer</code>). The admin panel works without it.
                </div>
                <div class="sm:col-span-2">@include('super-admin.integrations._test', ['target' => 'email', 'label' => 'Send test email', 'needsTo' => 'email'])</div>
            </div>
        </section>
        <div class="sticky bottom-4 z-10 flex justify-end">
            <button type="submit" @disabled(!$cryptoReady) class="rounded-xl bg-primary-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700 active:scale-[.98] disabled:cursor-not-allowed disabled:opacity-50">Save email settings</button>
        </div>
    </form>
    @endif

    {{-- ═══════════════════════ SMS ═══════════════════════ --}}
    @if($tab === 'sms')
    <form method="POST" action="{{ route('admin.integrations.update', 'sms') }}" class="space-y-6" x-data="{ driver: '{{ old('sms.provider', $values['sms']['provider'] ?? 'log') }}' }">
        @csrf @method('PUT')
        <section class="{{ $card }} overflow-hidden">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500 text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $tabs['sms'][1] }}"/></svg>
                    </span>
                    <div>
                        <h3 class="font-semibold text-gray-900">SMS gateway</h3>
                        <p class="text-xs text-gray-500">Mobile OTPs and SMS alerts</p>
                    </div>
                </div>
                @include('super-admin.integrations._status', ['s' => $status['sms']])
            </header>
            <div class="grid gap-5 p-6 sm:grid-cols-2">
                <div class="sm:col-span-2">{!! $f('sms', 'provider') !!}</div>
                <div x-show="driver === 'http'" x-cloak class="sm:col-span-2">{!! $f('sms', 'api_url') !!}</div>
                {!! $f('sms', 'api_key') !!}
                {!! $f('sms', 'sender_id') !!}

                <div x-show="driver === 'http'" x-cloak class="sm:col-span-2 rounded-xl bg-gray-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">URL template examples</p>
                    <ul class="mt-2 space-y-1.5 font-mono text-[11px] text-gray-700">
                        <li><span class="font-sans font-semibold text-gray-500">Fast2SMS ·</span> https://www.fast2sms.com/dev/bulkV2?authorization={key}&amp;route=q&amp;numbers={phone}&amp;message={message}</li>
                        <li><span class="font-sans font-semibold text-gray-500">TextLocal ·</span> https://api.textlocal.in/send/?apikey={key}&amp;sender={sender}&amp;numbers={phone}&amp;message={message}</li>
                    </ul>
                    <p class="mt-2 text-xs text-gray-500">Indian DLT rules: the message text must match a template approved for your sender ID.</p>
                </div>
                <div x-show="driver === 'log'" x-cloak class="sm:col-span-2 rounded-xl bg-sky-50 p-4 text-sm text-sky-800">
                    SMS messages are written to the logs instead of being sent. Choose HTTP gateway to deliver real SMS.
                </div>
                <div class="sm:col-span-2">@include('super-admin.integrations._test', ['target' => 'sms', 'label' => 'Send test SMS', 'needsTo' => 'phone'])</div>
            </div>
        </section>
        <div class="sticky bottom-4 z-10 flex justify-end">
            <button type="submit" @disabled(!$cryptoReady) class="rounded-xl bg-primary-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700 active:scale-[.98] disabled:cursor-not-allowed disabled:opacity-50">Save SMS settings</button>
        </div>
    </form>
    @endif
</div>
@endsection
