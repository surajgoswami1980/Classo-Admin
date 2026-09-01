@extends('layouts.app')
@section('title', 'Staff Attendance')
@section('page-title', 'Mark Staff Attendance')
@section('content')
<form method="POST" action="{{ panel_route('attendance.staff.mark') }}">
    @csrf
    <div class="bg-white rounded-xl border p-5 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
                <input type="date" name="date" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Emp ID</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Name</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Present</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Absent</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Leave</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Late</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($staff as $i => $member)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2.5 text-gray-600">{{ $member->employee_id ?? '—' }}</td>
                    <td class="px-4 py-2.5 font-medium text-gray-900">{{ $member->name }}</td>
                    <input type="hidden" name="attendance[{{ $i }}][user_id]" value="{{ $member->id }}">
                    <td class="px-4 py-2.5 text-center"><input type="radio" name="attendance[{{ $i }}][status]" value="present" checked class="w-4 h-4 text-green-600"></td>
                    <td class="px-4 py-2.5 text-center"><input type="radio" name="attendance[{{ $i }}][status]" value="absent" class="w-4 h-4 text-red-600"></td>
                    <td class="px-4 py-2.5 text-center"><input type="radio" name="attendance[{{ $i }}][status]" value="leave" class="w-4 h-4 text-blue-600"></td>
                    <td class="px-4 py-2.5 text-center"><input type="radio" name="attendance[{{ $i }}][status]" value="late" class="w-4 h-4 text-yellow-600"></td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">No staff found</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($staff->count() > 0)
        <div class="px-4 py-4 bg-gray-50 border-t flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">Save Staff Attendance</button>
        </div>
        @endif
    </div>
</form>
@endsection
