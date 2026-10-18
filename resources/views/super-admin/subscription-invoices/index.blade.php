@extends('layouts.app')
@section('title', 'Subscription Invoices')
@section('page-title', 'Subscription Billing')

@section('content')
<div x-data="{ showCreate: {{ $errors->any() ? 'true' : 'false' }} }" class="space-y-6">

    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <p class="text-xs text-gray-500">Collected (paid)</p>
            <p class="mt-1 text-3xl font-bold text-emerald-600">₹{{ number_format($stats['total']) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <p class="text-xs text-gray-500">Unpaid invoices</p>
            <p class="mt-1 text-3xl font-bold text-amber-600">{{ $stats['unpaid'] }}</p>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">Invoices to Schools</h2>
        <button @click="showCreate=true" class="px-4 py-2.5 bg-primary-600 text-white rounded-lg text-sm font-semibold hover:bg-primary-700 transition active:scale-95">+ Raise Invoice</button>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                <tr>
                    <th class="px-5 py-3 text-left">Invoice</th>
                    <th class="px-5 py-3 text-left">School</th>
                    <th class="px-5 py-3 text-left">Plan / Period</th>
                    <th class="px-5 py-3 text-right">Amount</th>
                    <th class="px-5 py-3 text-center">Status</th>
                    <th class="px-5 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($invoices as $inv)
                    <tr class="hover:bg-gray-50/70 transition-colors">
                        <td class="px-5 py-4 font-mono text-xs text-gray-700">{{ $inv->invoice_number }}</td>
                        <td class="px-5 py-4 text-gray-900 font-medium">{{ $inv->school_name }}</td>
                        <td class="px-5 py-4 text-gray-500">{{ $inv->plan ?: '—' }} · {{ $inv->period ?: '—' }}</td>
                        <td class="px-5 py-4 text-right font-semibold text-gray-900">₹{{ number_format($inv->amount) }}</td>
                        <td class="px-5 py-4 text-center">
                            @php $m = ['paid'=>'bg-emerald-100 text-emerald-700','unpaid'=>'bg-amber-100 text-amber-700','cancelled'=>'bg-gray-100 text-gray-500']; @endphp
                            <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full {{ $m[$inv->status] }}">{{ ucfirst($inv->status) }}</span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            @if($inv->status === 'unpaid')
                                <form method="POST" action="{{ route('admin.subscription-invoices.cancel', $inv->id) }}" onsubmit="return confirm('Cancel this invoice?')">
                                    @csrf
                                    <button class="text-xs font-semibold text-red-500 hover:underline">Cancel</button>
                                </form>
                            @else
                                <span class="text-xs text-gray-300">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">No subscription invoices yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($invoices->hasPages())<div class="px-5 py-4 border-t border-gray-100">{{ $invoices->links() }}</div>@endif
    </div>

    {{-- Create modal --}}
    <div x-show="showCreate" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" @click.self="showCreate=false">
        <div x-show="showCreate" x-transition.scale class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Raise Subscription Invoice</h3>
            <form method="POST" action="{{ route('admin.subscription-invoices.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">School *</label>
                    <select name="school_id" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                        <option value="">Select school</option>
                        @foreach($schools as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>@endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Amount (₹) *</label>
                        <input type="number" name="amount" required min="1" step="0.01" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Plan</label>
                        <input type="text" name="plan" placeholder="Growth" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Period</label>
                    <input type="text" name="period" placeholder="Oct 2026 / Annual 2026-27" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 outline-none"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showCreate=false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-semibold">Raise Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
