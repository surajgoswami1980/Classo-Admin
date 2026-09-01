@extends('layouts.app')
@section('title', 'Attendance Report')
@section('page-title', 'Attendance Report')
@section('content')
<div>
    <form method="GET" class="bg-white rounded-xl border p-5 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="student" {{ ($filters['type'] ?? 'student') === 'student' ? 'selected' : '' }}>Student</option>
                    <option value="staff" {{ ($filters['type'] ?? '') === 'staff' ? 'selected' : '' }}>Staff</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Class</label>
                <select name="class_id" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">All Classes</option>
                    @foreach($classes as $id => $name)
                        <option value="{{ $id }}" {{ ($filters['class_id'] ?? '') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
                <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
                <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">Generate</button>
            </div>
        </div>
    </form>

    @if($report->isNotEmpty())
    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Name</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Present</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Absent</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Late/Leave</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Total</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">%</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($report as $row)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $row->name }} <span class="text-xs text-gray-400">{{ $row->roll_number ?? $row->employee_id ?? '' }}</span></td>
                    <td class="px-4 py-3 text-center text-green-600 font-medium">{{ $row->present_days }}</td>
                    <td class="px-4 py-3 text-center text-red-600 font-medium">{{ $row->absent_days }}</td>
                    <td class="px-4 py-3 text-center text-yellow-600">{{ $row->late_days ?? $row->leave_days ?? 0 }}</td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ $row->total_days }}</td>
                    <td class="px-4 py-3 text-center font-bold {{ $row->total_days > 0 && ($row->present_days / $row->total_days * 100) < 75 ? 'text-red-600' : 'text-green-600' }}">
                        {{ $row->total_days > 0 ? round(($row->present_days / $row->total_days) * 100, 1) : 0 }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @elseif(!empty($filters['from_date']))
    <div class="bg-white rounded-xl border p-8 text-center text-gray-400">No attendance data found for the selected period</div>
    @endif
</div>
@endsection
