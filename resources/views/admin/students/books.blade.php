@extends('layouts.app')

@section('title', 'Student Books')
@section('page-title', 'Library Books — ' . ($student->user_name ?? 'Student'))

@section('content')
<div class="space-y-5">
    <div class="mc-card p-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $student->user_name }}</h2>
            <p class="text-sm text-gray-500">{{ $student->class_name }} - {{ $student->section_name }} · Roll {{ $student->roll_number ?? '—' }}</p>
        </div>
        <a href="{{ panel_route('students.index') }}" class="text-sm text-gray-600 hover:underline">&larr; Back to students</a>
    </div>

    <div class="mc-card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50/80 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Book</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Issued</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Due</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Status</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Fine</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($issues as $issue)
                <tr class="hover:bg-primary-50/40 transition-colors">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900">{{ $issue->book_title }}</p>
                        <p class="text-xs text-gray-400">{{ $issue->book_author }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ \Carbon\Carbon::parse($issue->issue_date)->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ \Carbon\Carbon::parse($issue->due_date)->format('d M Y') }}
                        @if($issue->is_overdue)<span class="text-xs text-red-600 block">{{ abs($issue->days_to_expire) }}d overdue</span>@endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($issue->status === 'returned')
                            <span class="text-xs font-medium text-gray-700 bg-gray-100 px-2 py-0.5 rounded-full">Returned</span>
                        @elseif($issue->is_overdue)
                            <span class="text-xs font-medium text-red-700 bg-red-50 px-2 py-0.5 rounded-full">Overdue</span>
                        @else
                            <span class="text-xs font-medium text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full">Issued</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right {{ $issue->current_fine > 0 ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                        ₹{{ number_format($issue->current_fine, 2) }}
                        @if($issue->fine_paid)<span class="text-[10px] text-green-600 block">paid</span>@endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-12 text-center text-gray-400">No books issued to this student.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
