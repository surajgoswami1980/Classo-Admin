@extends('layouts.app')

@section('title', 'Issued Books')
@section('page-title', 'Issued Books')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex gap-2">
            <a href="{{ panel_route('library.issues') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ empty($filters['filter']) && empty($filters['status']) ? 'bg-blue-600 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50' }}">All</a>
            <a href="{{ panel_route('library.issues', ['status' => 'issued']) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ ($filters['status'] ?? '') === 'issued' ? 'bg-blue-600 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50' }}">Currently Issued</a>
            <a href="{{ panel_route('library.issues', ['filter' => 'overdue']) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ ($filters['filter'] ?? '') === 'overdue' ? 'bg-red-600 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50' }}">Overdue</a>
            <a href="{{ panel_route('library.issues', ['filter' => 'expiring']) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ ($filters['filter'] ?? '') === 'expiring' ? 'bg-amber-500 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50' }}">Expiring (7d)</a>
            <a href="{{ panel_route('library.issues', ['status' => 'returned']) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ ($filters['status'] ?? '') === 'returned' ? 'bg-gray-800 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50' }}">Returned</a>
        </div>
        <a href="{{ panel_route('library.index') }}" class="text-sm text-gray-600 hover:underline">&larr; Back to catalog</a>
    </div>

    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Book</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Borrower</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Issued</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Due</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Days Left</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Fine</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Status</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($issues as $issue)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900">{{ $issue->book_title }}</p>
                        <p class="text-xs text-gray-400">{{ $issue->book_author }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ $issue->borrower_name ?? '—' }}
                        @if($issue->roll_number)<span class="text-xs text-gray-400 block">Roll {{ $issue->roll_number }}</span>@endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ \Carbon\Carbon::parse($issue->issue_date)->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ \Carbon\Carbon::parse($issue->due_date)->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($issue->status === 'returned')
                            <span class="text-gray-400">—</span>
                        @elseif($issue->is_overdue)
                            <span class="text-xs font-medium text-red-700 bg-red-50 px-2 py-0.5 rounded-full">{{ abs($issue->days_to_expire) }}d overdue</span>
                        @else
                            <span class="text-xs font-medium text-gray-700 bg-gray-100 px-2 py-0.5 rounded-full">{{ $issue->days_to_expire }}d</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right {{ $issue->current_fine > 0 ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                        ₹{{ number_format($issue->current_fine, 2) }}
                        @if($issue->fine_paid)<span class="text-[10px] text-green-600 block">paid</span>@endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($issue->status === 'returned')
                            <span class="text-xs font-medium text-gray-700 bg-gray-100 px-2 py-0.5 rounded-full">Returned</span>
                        @else
                            <span class="text-xs font-medium text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full">Issued</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        @if($issue->status !== 'returned')
                        <form method="POST" action="{{ panel_route('library.issues.return', $issue->id) }}" class="inline" onsubmit="return confirm('Mark this book as returned?')">
                            @csrf
                            <button type="submit" class="text-xs font-medium text-teal-600 hover:underline">Return</button>
                        </form>
                        @endif
                        @if($issue->fine_amount > 0 && !$issue->fine_paid)
                        <form method="POST" action="{{ panel_route('library.issues.fine-paid', $issue->id) }}" class="inline ml-3">
                            @csrf
                            <button type="submit" class="text-xs font-medium text-green-600 hover:underline">Mark Fine Paid</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400">No records found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($issues->hasPages())
        <div class="px-4 py-3 border-t">{{ $issues->links() }}</div>
        @endif
    </div>
</div>
@endsection
