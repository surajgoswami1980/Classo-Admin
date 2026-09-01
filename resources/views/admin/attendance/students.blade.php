@extends('layouts.app')
@section('title', 'Student Attendance')
@section('page-title', 'Mark Student Attendance')
@section('content')
<div x-data="studentAttendance()" x-init="init()">
    <div class="bg-white rounded-xl border p-5 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Class</label>
                <select x-model="classId" @change="fetchStudents()" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Select Class</option>
                    @foreach($classes as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Section</label>
                <select x-model="sectionId" @change="fetchStudents()" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Select Section</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
                <input type="date" x-model="date" :max="today" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div class="flex items-end gap-2">
                <button @click="markAll('present')" class="px-3 py-2 text-xs bg-green-100 text-green-700 rounded-lg hover:bg-green-200">All Present</button>
                <button @click="markAll('absent')" class="px-3 py-2 text-xs bg-red-100 text-red-700 rounded-lg hover:bg-red-200">All Absent</button>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ panel_route('attendance.students.mark') }}">
        @csrf
        <input type="hidden" name="class_id" x-bind:value="classId">
        <input type="hidden" name="section_id" x-bind:value="sectionId">
        <input type="hidden" name="date" x-bind:value="date">

        <div x-show="students.length > 0" class="bg-white rounded-xl border overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Roll</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Name</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600">Present</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600">Absent</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600">Late</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600">Half Day</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <template x-for="(s, i) in students" :key="s.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2.5 text-gray-600" x-text="s.roll_number || '-'"></td>
                            <td class="px-4 py-2.5 font-medium text-gray-900" x-text="s.name"></td>
                            <td class="px-4 py-2.5 text-center"><input type="radio" :name="'attendance['+i+'][status]'" value="present" x-model="attendance[s.id]" class="w-4 h-4 text-green-600"><input type="hidden" :name="'attendance['+i+'][student_id]'" :value="s.id"></td>
                            <td class="px-4 py-2.5 text-center"><input type="radio" :name="'attendance['+i+'][status]'" value="absent" x-model="attendance[s.id]" class="w-4 h-4 text-red-600"></td>
                            <td class="px-4 py-2.5 text-center"><input type="radio" :name="'attendance['+i+'][status]'" value="late" x-model="attendance[s.id]" class="w-4 h-4 text-yellow-600"></td>
                            <td class="px-4 py-2.5 text-center"><input type="radio" :name="'attendance['+i+'][status]'" value="half_day" x-model="attendance[s.id]" class="w-4 h-4 text-orange-600"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <div class="px-4 py-4 bg-gray-50 border-t flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">Save Attendance</button>
            </div>
        </div>
        <div x-show="students.length === 0 && classId && sectionId" class="text-center py-12 text-gray-400 bg-white rounded-xl border">No students found for this class/section</div>
    </form>
</div>
@push('scripts')
<script>
function studentAttendance() {
    return {
        classId: '', sectionId: '', date: new Date().toISOString().split('T')[0],
        today: new Date().toISOString().split('T')[0],
        students: [], attendance: {},
        init() {},
        async fetchStudents() {
            if (!this.classId || !this.sectionId) return;
            try {
                const res = await fetch(`/api/students?class_id=${this.classId}&section_id=${this.sectionId}`);
                const data = await res.json();
                this.students = data.data || [];
                this.students.forEach(s => { this.attendance[s.id] = 'present'; });
            } catch(e) { this.students = []; }
        },
        markAll(status) { this.students.forEach(s => { this.attendance[s.id] = status; }); }
    };
}
</script>
@endpush
@endsection
