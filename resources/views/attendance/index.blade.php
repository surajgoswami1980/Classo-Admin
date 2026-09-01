@extends('layouts.app')

@section('title', 'Attendance Management')
@section('page-title', 'Attendance')

@section('content')
<div x-data="attendancePage()" x-init="init()">

    <!-- Tab Navigation -->
    <div class="flex gap-1 p-1 bg-gray-100 rounded-xl w-fit mb-6">
        <button @click="activeTab = 'student'"
                :class="activeTab === 'student' ? 'bg-white shadow-sm text-blue-700' : 'text-gray-600 hover:text-gray-900'"
                class="px-5 py-2 rounded-lg text-sm font-medium transition-all">
            Student Attendance
        </button>
        <button @click="activeTab = 'staff'"
                :class="activeTab === 'staff' ? 'bg-white shadow-sm text-blue-700' : 'text-gray-600 hover:text-gray-900'"
                class="px-5 py-2 rounded-lg text-sm font-medium transition-all">
            Staff / Teacher Attendance
        </button>
    </div>

    <!-- ═══ Student Attendance Tab ═══ -->
    <div x-show="activeTab === 'student'" x-transition>
        <!-- Filters -->
        <div class="bg-white rounded-xl border p-5 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Class</label>
                    <select x-model="filters.class_id" @change="fetchSections()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <option value="">Select Class</option>
                        <template x-for="cls in classes" :key="cls.id">
                            <option :value="cls.id" x-text="cls.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Section</label>
                    <select x-model="filters.section_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <option value="">Select Section</option>
                        <template x-for="sec in sections" :key="sec.id">
                            <option :value="sec.id" x-text="sec.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
                    <input type="date" x-model="filters.date" :max="today" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                <div class="flex items-end">
                    <button @click="fetchStudentAttendance()" class="w-full bg-blue-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-blue-700 transition">
                        Load Students
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div x-show="students.length > 0" class="flex gap-2 mb-4">
            <button @click="markAll('present')" class="text-xs px-3 py-1.5 bg-green-100 text-green-700 rounded-full hover:bg-green-200 font-medium">All Present</button>
            <button @click="markAll('absent')" class="text-xs px-3 py-1.5 bg-red-100 text-red-700 rounded-full hover:bg-red-200 font-medium">All Absent</button>
            <span class="ml-auto text-xs text-gray-500" x-show="isAlreadyMarked">
                ✓ Attendance already marked for this date
            </span>
        </div>

        <!-- Student List -->
        <div x-show="students.length > 0" class="bg-white rounded-xl border overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Roll</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Present</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Absent</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Late</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Half Day</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="student in students" :key="student.student_id">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-600" x-text="student.roll_number || '-'"></td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900" x-text="student.name || ('Student #' + student.student_id)"></td>
                            <td class="px-4 py-3 text-center">
                                <input type="radio" :name="'att_' + student.student_id" value="present"
                                       x-model="attendance[student.student_id]"
                                       class="w-4 h-4 text-green-600 focus:ring-green-500">
                            </td>
                            <td class="px-4 py-3 text-center">
                                <input type="radio" :name="'att_' + student.student_id" value="absent"
                                       x-model="attendance[student.student_id]"
                                       class="w-4 h-4 text-red-600 focus:ring-red-500">
                            </td>
                            <td class="px-4 py-3 text-center">
                                <input type="radio" :name="'att_' + student.student_id" value="late"
                                       x-model="attendance[student.student_id]"
                                       class="w-4 h-4 text-yellow-600 focus:ring-yellow-500">
                            </td>
                            <td class="px-4 py-3 text-center">
                                <input type="radio" :name="'att_' + student.student_id" value="half_day"
                                       x-model="attendance[student.student_id]"
                                       class="w-4 h-4 text-orange-600 focus:ring-orange-500">
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <!-- Stats & Submit -->
            <div class="px-4 py-4 bg-gray-50 border-t flex items-center justify-between">
                <div class="flex gap-4 text-xs">
                    <span class="text-green-600 font-medium">Present: <span x-text="countStatus('present')"></span></span>
                    <span class="text-red-600 font-medium">Absent: <span x-text="countStatus('absent')"></span></span>
                    <span class="text-yellow-600 font-medium">Late: <span x-text="countStatus('late')"></span></span>
                    <span class="text-gray-500">Total: <span x-text="students.length"></span></span>
                </div>
                <button @click="saveStudentAttendance()" :disabled="saving"
                        class="px-6 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition disabled:opacity-60">
                    <span x-text="saving ? 'Saving...' : (isAlreadyMarked ? 'Update Attendance' : 'Save Attendance')"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ═══ Staff Attendance Tab ═══ -->
    <div x-show="activeTab === 'staff'" x-transition>
        <div class="bg-white rounded-xl border p-5 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
                    <input type="date" x-model="staffFilters.date" :max="today" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Department</label>
                    <select x-model="staffFilters.department" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">All Departments</option>
                        <option value="teaching">Teaching Staff</option>
                        <option value="admin">Administrative</option>
                        <option value="support">Support Staff</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button @click="fetchStaffList()" class="w-full bg-blue-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-blue-700 transition">
                        Load Staff
                    </button>
                </div>
            </div>
        </div>

        <!-- Staff Table -->
        <div x-show="staffList.length > 0" class="bg-white rounded-xl border overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Emp ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Check-In</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Check-Out</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="staff in staffList" :key="staff.user_id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-600" x-text="staff.employee_id || '-'"></td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900" x-text="staff.name"></td>
                            <td class="px-4 py-3 text-sm text-gray-600 capitalize" x-text="staff.role"></td>
                            <td class="px-4 py-3 text-center">
                                <select x-model="staffAttendance[staff.user_id].status"
                                        class="text-sm border rounded-lg px-2 py-1 focus:ring-2 focus:ring-blue-500 outline-none">
                                    <option value="present">Present</option>
                                    <option value="absent">Absent</option>
                                    <option value="leave">Leave</option>
                                    <option value="late">Late</option>
                                    <option value="half_day">Half Day</option>
                                </select>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <input type="time" x-model="staffAttendance[staff.user_id].check_in"
                                       class="text-sm border rounded-lg px-2 py-1 focus:ring-2 focus:ring-blue-500 outline-none">
                            </td>
                            <td class="px-4 py-3 text-center">
                                <input type="time" x-model="staffAttendance[staff.user_id].check_out"
                                       class="text-sm border rounded-lg px-2 py-1 focus:ring-2 focus:ring-blue-500 outline-none">
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <div class="px-4 py-4 bg-gray-50 border-t flex justify-end">
                <button @click="saveStaffAttendance()" :disabled="saving"
                        class="px-6 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition disabled:opacity-60">
                    <span x-text="saving ? 'Saving...' : 'Save Staff Attendance'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function attendancePage() {
    return {
        activeTab: 'student',
        today: new Date().toISOString().split('T')[0],
        saving: false,
        isAlreadyMarked: false,

        // Student attendance
        classes: [],
        sections: [],
        students: [],
        attendance: {},
        filters: { class_id: '', section_id: '', date: new Date().toISOString().split('T')[0] },

        // Staff attendance
        staffList: [],
        staffAttendance: {},
        staffFilters: { date: new Date().toISOString().split('T')[0], department: '' },

        async init() {
            await this.fetchClasses();
        },

        async fetchClasses() {
            try {
                const res = await fetch('/api/proxy/school/classes', { headers: this.authHeaders() });
                const data = await res.json();
                this.classes = data.data || [];
            } catch (e) { console.error(e); }
        },

        async fetchSections() {
            if (!this.filters.class_id) return;
            try {
                const res = await fetch(`/api/proxy/school/sections?class_id=${this.filters.class_id}`, { headers: this.authHeaders() });
                const data = await res.json();
                this.sections = data.data || [];
            } catch (e) { console.error(e); }
        },

        async fetchStudentAttendance() {
            if (!this.filters.class_id || !this.filters.section_id || !this.filters.date) return;
            try {
                const params = new URLSearchParams(this.filters).toString();
                const res = await fetch(`/api/proxy/attendance/student/get?${params}`, { headers: this.authHeaders() });
                const data = await res.json();
                this.students = data.data?.students || [];
                this.isAlreadyMarked = data.data?.is_marked || false;

                // Initialize attendance
                this.attendance = {};
                this.students.forEach(s => {
                    this.attendance[s.student_id] = s.status || 'present';
                });
            } catch (e) { console.error(e); }
        },

        markAll(status) {
            this.students.forEach(s => { this.attendance[s.student_id] = status; });
        },

        countStatus(status) {
            return Object.values(this.attendance).filter(v => v === status).length;
        },

        async saveStudentAttendance() {
            this.saving = true;
            try {
                const payload = {
                    class_id: parseInt(this.filters.class_id),
                    section_id: parseInt(this.filters.section_id),
                    date: this.filters.date,
                    attendance: Object.entries(this.attendance).map(([sid, status]) => ({
                        student_id: parseInt(sid), status
                    }))
                };
                const res = await fetch('/api/proxy/attendance/student/mark', {
                    method: 'POST',
                    headers: { ...this.authHeaders(), 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                if (res.ok) {
                    this.isAlreadyMarked = true;
                    alert('Attendance saved successfully!');
                }
            } catch (e) { alert('Failed to save'); }
            this.saving = false;
        },

        async fetchStaffList() {
            try {
                const res = await fetch('/api/proxy/school/staff-list', { headers: this.authHeaders() });
                const data = await res.json();
                this.staffList = data.data || [];
                this.staffAttendance = {};
                this.staffList.forEach(s => {
                    this.staffAttendance[s.user_id] = { status: 'present', check_in: '09:00', check_out: '17:00' };
                });
            } catch (e) { console.error(e); }
        },

        async saveStaffAttendance() {
            this.saving = true;
            try {
                const payload = {
                    date: this.staffFilters.date,
                    attendance: Object.entries(this.staffAttendance).map(([uid, data]) => ({
                        user_id: parseInt(uid),
                        status: data.status,
                        check_in_time: data.check_in ? `${this.staffFilters.date}T${data.check_in}:00` : null,
                        check_out_time: data.check_out ? `${this.staffFilters.date}T${data.check_out}:00` : null,
                    }))
                };
                const res = await fetch('/api/proxy/attendance/staff/mark', {
                    method: 'POST',
                    headers: { ...this.authHeaders(), 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                if (res.ok) alert('Staff attendance saved!');
            } catch (e) { alert('Failed to save'); }
            this.saving = false;
        },

        authHeaders() {
            return { 'Authorization': `Bearer ${document.cookie.match(/token=([^;]+)/)?.[1] || ''}` };
        }
    };
}
</script>
@endpush
@endsection
