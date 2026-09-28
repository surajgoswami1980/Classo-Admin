@extends('layouts.app')

@section('title', 'Stock Movements')
@section('page-title', 'Stock Movements')

@section('content')
<div class="space-y-5">
    <div class="flex justify-end">
        <a href="{{ panel_route('inventory.index') }}" class="text-sm text-gray-600 hover:underline">&larr; Back to inventory</a>
    </div>

    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b"><tr>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Item</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">Type</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">Qty</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Reference</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">By</th>
            </tr></thead>
            <tbody class="divide-y">
                @forelse($transactions as $t)
                <tr class="hover:bg-gray-50">
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
