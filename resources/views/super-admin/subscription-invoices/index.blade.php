@extends('layouts.app')
@section('title', 'Subscription Invoices')
@section('page-title', 'Subscription Billing')

@section('content')
<div x-data="{ showCreate: {{ $errors->any() ? 'true' : 'false' }} }" class="space-y-6">

    <div class="grid grid-cols-2 gap-4">
        <div class="mc-card p-5">
            <p class="text-xs text-gray-500">Collected (paid)</p>
            <p class="mt-1 text-3xl font-bold text-green-600">₹{{ number_format($stats['total']) }}</p>
        </div>
        <div class="mc-card p-5">
            <p class="text-xs text-gray-500">Unpaid invoices</p>
            <p class="mt-1 text-3xl font-bold text-amber-600">{{ $stats['unpaid'] }}</p>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">Invoices to Schools</h2>
        <button @click="showCreate=true" class="btn btn-primary">+ Raise Invoice</button>
    </div>

    <div class="mc-card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50/80 border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                <tr>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Invoice</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">School</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Plan / Period</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Amount</th>
                    <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Status</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($invoices as $inv)
                    <tr class="hover:bg-primary-50/40 transition-colors">
                        <td class="px-5 py-4 font-mono text-xs text-gray-700">{{ $inv->invoice_number }}</td>
                        <td class="px-5 py-4 text-gray-900 font-medium">{{ $inv->school_name }}</td>
                        <td class="px-5 py-4 text-gray-500">{{ $inv->plan ?: '—' }} · {{ $inv->period ?: '—' }}</td>
                        <td class="px-5 py-4 text-right font-semibold text-gray-900">₹{{ number_format($inv->amount) }}</td>
                        <td class="px-5 py-4 text-center">
                            @php $m = ['paid'=>'bg-green-100 text-green-700','unpaid'=>'bg-amber-100 text-amber-700','cancelled'=>'bg-gray-100 text-gray-500']; @endphp
                            <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full {{ $m[$inv->status] }}">{{ ucfirst($inv->status) }}</span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            @if($inv->status === 'unpaid')
                                <form method="POST" action="{{ route('admin.subscription-invoices.cancel', $inv->id) }}" data-confirm="This action can't be undone." data-confirm-title="Cancel this invoice?" data-confirm-ok="Yes, cancel" data-confirm-tone="danger">
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
    <div x-show="showCreate" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-navy-950/50 backdrop-blur-[2px]" @click.self="showCreate=false">
        <div x-show="showCreate" x-transition.scale class="modal-panel max-w-md p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Raise Subscription Invoice</h3>
            <form method="POST" action="{{ route('admin.subscription-invoices.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">School *</label>
                    <select name="school_id" required class="form-input">
                        <option value="">Select school</option>
                        @foreach($schools as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>@endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Amount (₹) *</label>
                        <input type="number" name="amount" required min="1" step="0.01" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Plan</label>
                        <input type="text" name="plan" placeholder="Growth" class="form-input">
                    </div>
                </div>
                <div>
                    <label class="form-label">Period</label>
                    <input type="text" name="period" placeholder="Oct 2026 / Annual 2026-27" class="form-input">
                </div>
                <div>
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="2" class="form-input"></textarea>
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
