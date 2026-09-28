@extends('layouts.app')

@section('title', 'Student Attendance')
@section('page-title', 'Attendance — ' . ($student->user_name ?? 'Student'))

@section('content')
<div class="space-y-5">
    <div class="bg-white rounded-xl border p-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $student->user_name }}</h2>
            <p class="text-sm text-gray-500">{{ $student->class_name }} - {{ $student->section_name }} · Roll {{ $student->roll_number ?? '—' }}</p>
        </div>
        <a href="{{ panel_route('students.index') }}" class="text-sm text-gray-600 hover:underline">&larr; Back to students</a>
    </div>

    {{-- Range filter --}}
    <div class="flex flex-wrap gap-2">
        @foreach(['last_7' => 'Last 7 Days', 'last_30' => 'Last 30 Days', 'this_month' => 'This Month', 'last_month' => 'Last Month', 'this_year' => 'This Year'] as $key => $lbl)
            <a href="{{ panel_route('students.attendance', $student->id) }}?range={{ $key }}"
               class="px-3 py-1.5 rounded-lg text-sm font-medium {{ $range === $key ? 'bg-blue-600 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                {{ $lbl }}
            </a>
        @endforeach
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <div class="bg-white rounded-xl border p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $total }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Marked Days</p>
        </div>
        <div class="bg-green-50 rounded-xl border border-green-100 p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $counts['present'] }}</p>
            <p class="text-xs text-green-700 mt-0.5">Present</p>
        </div>
        <div class="bg-red-50 rounded-xl border border-red-100 p-4 text-center">
            <p class="text-2xl font-bold text-red-500">{{ $counts['absent'] }}</p>
            <p class="text-xs text-red-700 mt-0.5">Absent</p>
        </div>
        <div class="bg-amber-50 rounded-xl border border-amber-100 p-4 text-center">
            <p class="text-2xl font-bold text-amber-500">{{ $counts['late'] }}</p>
            <p class="text-xs text-amber-700 mt-0.5">Late</p>
        </div>
        <div class="bg-blue-50 rounded-xl border border-blue-100 p-4 text-center">
            <p class="text-2xl font-bold {{ $percentage >= 75 ? 'text-blue-600' : 'text-red-500' }}">{{ $percentage }}%</p>
            <p class="text-xs text-blue-700 mt-0.5">Attendance</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border overflow-hidden">
        <div class="px-4 py-3 border-b text-sm font-medium text-gray-600">{{ $label }} — daily records</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Day</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Status</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Remarks</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($records as $rec)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-900">{{ \Carbon\Carbon::parse($rec->date)->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ \Carbon\Carbon::parse($rec->date)->format('l') }}</td>
                    <td class="px-4 py-3 text-center">
                        @php
                            $badge = [
                                'present'  => 'text-green-700 bg-green-50',
                                'absent'   => 'text-red-700 bg-red-50',
                                'late'     => 'text-amber-700 bg-amber-50',
                                'half_day' => 'text-gray-700 bg-gray-100',
                            ][$rec->status] ?? 'text-gray-700 bg-gray-100';
                        @endphp
                        <span class="text-xs font-medium {{ $badge }} px-2 py-0.5 rounded-full">{{ ucfirst(str_replace('_', ' ', $rec->status)) }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $rec->remarks ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-12 text-center text-gray-400">No attendance records in this range.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
