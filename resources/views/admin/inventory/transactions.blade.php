@extends('layouts.app')

@section('title', 'Stock Movements')
@section('page-title', 'Stock Movements')

@section('content')
<div class="space-y-5">
    <div class="flex justify-end">
        <a href="{{ panel_route('inventory.index') }}" class="text-sm text-gray-600 hover:underline">&larr; Back to inventory</a>
    </div>

    <div class="mc-card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50/80 border-b border-gray-200"><tr>
                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Date</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Item</th>
                <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Type</th>
                <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Qty</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Reference</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">By</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($transactions as $t)
                <tr class="hover:bg-primary-50/40 transition-colors">
                    <td class="px-4 py-3 text-gray-600 text-xs">{{ \Carbon\Carbon::parse($t->created_at)->format('d M Y, h:i A') }}</td>
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $t->item_name }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($t->type === 'in')<span class="text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded-full">IN</span>
                        @else<span class="text-xs font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">OUT</span>@endif
                    </td>
                    <td class="px-4 py-3 text-center text-gray-700">{{ $t->quantity }} {{ $t->unit }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $t->reference ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $t->created_by_name ?? '—' }}</td>
                </tr>
                @empty<tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">No stock movements yet.</td></tr>@endforelse
            </tbody>
        </table>
        @if($transactions->hasPages())<div class="px-4 py-3 border-t">{{ $transactions->links() }}</div>@endif
    </div>
</div>
@endsection
