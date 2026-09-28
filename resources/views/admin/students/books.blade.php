@extends('layouts.app')

@section('title', 'Student Books')
@section('page-title', 'Library Books — ' . ($student->user_name ?? 'Student'))

@section('content')
<div class="space-y-5">
    <div class="bg-white rounded-xl border p-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $student->user_name }}</h2>
            <p class="text-sm text-gray-500">{{ $student->class_name }} - {{ $student->section_name }} · Roll {{ $student->roll_number ?? '—' }}</p>
        </div>
        <a href="{{ panel_route('students.index') }}" class="text-sm text-gray-600 hover:underline">&larr; Back to students</a>
    </div>

    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Book</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Issued</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Due</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Status</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Fine</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($issues as $issue)
                <tr class="hover:bg-gray-50">
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
