@extends('layouts.app')
@section('title', 'Attendance Report')
@section('page-title', 'Attendance Report')
@section('content')
<div>
    <form method="GET" class="mc-card p-5 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="form-label">Type</label>
                <select name="type" class="form-input">
                    <option value="student" {{ ($filters['type'] ?? 'student') === 'student' ? 'selected' : '' }}>Student</option>
                    <option value="staff" {{ ($filters['type'] ?? '') === 'staff' ? 'selected' : '' }}>Staff</option>
                </select>
            </div>
            <div>
                <label class="form-label">Class</label>
                <select name="class_id" class="form-input">
                    <option value="">All Classes</option>
                    @foreach($classes as $id => $name)
                        <option value="{{ $id }}" {{ ($filters['class_id'] ?? '') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">From</label>
                <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="form-input">
            </div>
            <div>
                <label class="form-label">To</label>
                <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="form-input">
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn btn-primary">Generate</button>
            </div>
        </div>
    </form>

    @if($report->isNotEmpty())
    <div class="mc-card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50/80 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Name</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Present</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Absent</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Late/Leave</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Total</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">%</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($report as $row)
                <tr class="hover:bg-primary-50/40 transition-colors">
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
    <div class="mc-card p-8 text-center text-gray-400">No attendance data found for the selected period</div>
    @endif
</div>
@endsection
