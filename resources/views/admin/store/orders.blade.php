@extends('layouts.app')

@section('title', 'Store Orders')
@section('page-title', 'Store Orders')

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Awaiting Collection</p><p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['purchased'] }}</p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Completed</p><p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['completed'] }}</p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Revenue</p><p class="text-2xl font-bold text-blue-600 mt-1">₹{{ number_format($stats['revenue']) }}</p></div>
    </div>

    <div class="flex items-center justify-between">
        <form method="GET" class="flex gap-2">
            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All statuses</option>
                @foreach(['purchased'=>'Awaiting Collection','completed'=>'Completed','pending_payment'=>'Pending Payment','cancelled'=>'Cancelled'] as $k=>$label)
                    <option value="{{ $k }}" {{ ($filters['status'] ?? '')===$k ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm font-medium hover:bg-gray-900">Filter</button>
        </form>
        <a href="{{ panel_route('store.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">← Store Items</a>
    </div>

    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b"><tr>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Order</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Buyer</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Item</th>
                <th class="px-4 py-3 text-right font-medium text-gray-600">Amount</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">Payment</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">Status</th>
                <th class="px-4 py-3 text-right font-medium text-gray-600">Action</th>
            </tr></thead>
            <tbody class="divide-y">
                @forelse($orders as $o)
                <tr class="hover:bg-gray-50 transition-colors">
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
                        <form method="POST" action="{{ panel_route('store.orders.complete', $o->id) }}" class="inline" onsubmit="return confirm('Mark as collected/completed?')">@csrf
                            <button type="submit" class="text-xs font-semibold text-white bg-green-600 hover:bg-green-700 px-3 py-1.5 rounded-lg transition active:scale-95">Mark Collected</button>
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
