@extends('layouts.app')

@section('title', 'Store Orders')
@section('page-title', 'Store Orders')

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-3 gap-4">
        <div class="mc-card p-4"><p class="text-xs text-gray-500">Awaiting Collection</p><p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['purchased'] }}</p></div>
        <div class="mc-card p-4"><p class="text-xs text-gray-500">Completed</p><p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['completed'] }}</p></div>
        <div class="mc-card p-4"><p class="text-xs text-gray-500">Revenue</p><p class="text-2xl font-bold text-blue-600 mt-1">₹{{ number_format($stats['revenue']) }}</p></div>
    </div>

    <div class="flex items-center justify-between">
        <form method="GET" class="flex gap-2">
            <select name="status" class="form-input">
                <option value="">All statuses</option>
                @foreach(['purchased'=>'Awaiting Collection','completed'=>'Completed','pending_payment'=>'Pending Payment','cancelled'=>'Cancelled'] as $k=>$label)
                    <option value="{{ $k }}" {{ ($filters['status'] ?? '')===$k ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
        <a href="{{ panel_route('store.index') }}" class="btn btn-secondary">← Store Items</a>
    </div>

    <div class="mc-card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50/80 border-b border-gray-200"><tr>
                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Order</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Buyer</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Item</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Amount</th>
                <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Payment</th>
                <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Status</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Action</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($orders as $o)
                <tr class="hover:bg-primary-50/40 transition-colors">
                    <td class="px-4 py-3">
                        <p class="font-mono text-xs text-gray-700">{{ $o->order_number }}</p>
                        <p class="text-[11px] text-gray-400">{{ \Illuminate\Support\Carbon::parse($o->created_at)->format('d M, h:i A') }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900">{{ $o->buyer_name }}</p>
                        <p class="text-xs text-gray-400">{{ $o->class_name ? $o->class_name.($o->section_name? ' - '.$o->section_name:'') : ($o->buyer_phone ?? '') }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-700">{{ $o->item_name }} <span class="text-xs text-gray-400">×{{ $o->quantity }}</span></td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-900">₹{{ number_format($o->total_amount) }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $o->payment_status==='paid' ? 'text-green-700 bg-green-50' : ($o->payment_status==='failed' ? 'text-red-700 bg-red-50' : 'text-amber-700 bg-amber-50') }}">{{ ucfirst($o->payment_status) }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @php $sm = ['purchased'=>'text-amber-700 bg-amber-50','completed'=>'text-green-700 bg-green-50','pending_payment'=>'text-gray-600 bg-gray-100','cancelled'=>'text-red-700 bg-red-50'][$o->status] ?? 'text-gray-600 bg-gray-100'; @endphp
                        <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full {{ $sm }}">
                            @if($o->status==='purchased')<span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>@endif
                            {{ $o->status==='purchased' ? 'Awaiting Collection' : ucfirst(str_replace('_',' ',$o->status)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if($o->status==='purchased')
                        <form method="POST" action="{{ panel_route('store.orders.complete', $o->id) }}" class="inline" data-confirm="Please confirm to continue." data-confirm-title="Mark as collected/completed?" data-confirm-ok="Confirm" data-confirm-tone="success">@csrf
                            <button type="submit" class="btn btn-success btn-sm">Mark Collected</button>
                        </form>
                        @elseif($o->status==='completed')
                        <span class="text-xs text-green-600">✓ {{ $o->completed_at ? \Illuminate\Support\Carbon::parse($o->completed_at)->format('d M') : '' }}</span>
                        @else
                        <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                </tr>
                @empty<tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">No orders yet.</td></tr>@endforelse
            </tbody>
        </table>
        @if($orders->hasPages())<div class="px-4 py-3 border-t">{{ $orders->links() }}</div>@endif
    </div>
</div>
@endsection
