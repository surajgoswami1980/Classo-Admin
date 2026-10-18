@extends('layouts.app')
@section('title', 'Payment Settings')
@section('page-title', 'Payment Gateway Settings')

@section('content')
<div x-data="{ gw: '{{ $current->active_gateway ?? 'none' }}' }" class="max-w-4xl mx-auto space-y-6">

    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-sm text-blue-800">
        Configure each school's own gateway so collected fees, store and event money settle into <strong>that school's account</strong>. Leave as "Platform default" to use the shared demo keys.
    </div>

    {{-- School picker --}}
    <form method="GET" class="flex items-center gap-3 bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
        <label class="text-sm font-semibold text-gray-600">School</label>
        <select name="school" onchange="this.form.submit()" class="flex-1 border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
            @foreach($schools as $s)
                <option value="{{ $s->id }}" @selected($selected && $selected->id == $s->id)>{{ $s->name }} ({{ $s->code }})</option>
            @endforeach
        </select>
    </form>

    @if($selected)
    <form method="POST" action="{{ route('admin.payment-settings.update', $selected->id) }}" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-6">
        @csrf
        <div>
            <h3 class="text-base font-bold text-gray-900">{{ $selected->name }}</h3>
            <p class="text-sm text-gray-500">Choose which gateway this school uses to collect payments.</p>
        </div>

        {{-- Gateway selector --}}
        <div class="grid sm:grid-cols-3 gap-3">
            @foreach([['none','Platform default','Use shared demo keys'],['razorpay','Razorpay','School\'s own Razorpay'],['cashfree','Cashfree','School\'s own Cashfree']] as $opt)
                <label class="cursor-pointer rounded-xl border-2 p-4 transition-all" :class="gw === '{{ $opt[0] }}' ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300'">
                    <input type="radio" name="active_gateway" value="{{ $opt[0] }}" x-model="gw" class="sr-only">
                    <p class="font-bold text-gray-900 text-sm">{{ $opt[1] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $opt[2] }}</p>
                </label>
            @endforeach
        </div>

        {{-- Razorpay --}}
        <div x-show="gw === 'razorpay'" x-transition class="rounded-xl border border-gray-100 bg-gray-50/60 p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h4 class="font-semibold text-gray-800">Razorpay Credentials</h4>
                <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="razorpay_enabled" value="1" @checked($current->razorpay_enabled ?? false) class="rounded border-gray-300 text-primary-600"> Enabled</label>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Key ID</label>
                <input type="text" name="razorpay_key_id" value="{{ $current->razorpay_key_id ?? '' }}" placeholder="rzp_live_xxxxx" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Key Secret</label>
                <input type="text" name="razorpay_key_secret" value="{{ $current->razorpay_key_secret ?? '' }}" placeholder="••••••••" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
            </div>
        </div>

        {{-- Cashfree --}}
        <div x-show="gw === 'cashfree'" x-transition class="rounded-xl border border-gray-100 bg-gray-50/60 p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h4 class="font-semibold text-gray-800">Cashfree Credentials</h4>
                <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="cashfree_enabled" value="1" @checked($current->cashfree_enabled ?? false) class="rounded border-gray-300 text-primary-600"> Enabled</label>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">App ID</label>
                <input type="text" name="cashfree_app_id" value="{{ $current->cashfree_app_id ?? '' }}" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Secret Key</label>
                <input type="text" name="cashfree_secret_key" value="{{ $current->cashfree_secret_key ?? '' }}" placeholder="••••••••" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Environment</label>
                <select name="cashfree_env" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                    <option value="TEST" @selected(($current->cashfree_env ?? 'TEST') === 'TEST')>Test (Sandbox)</option>
                    <option value="PROD" @selected(($current->cashfree_env ?? '') === 'PROD')>Production</option>
                </select>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white text-sm font-semibold rounded-lg hover:bg-primary-700 transition active:scale-95">Save Settings</button>
        </div>
    </form>
    @endif
</div>
@endsection
