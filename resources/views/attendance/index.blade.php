@extends('layouts.app')

@section('title', 'Attendance Management')
@section('page-title', 'Attendance')

@section('content')
<div x-data="attendanceHub()" x-init="init()" class="space-y-6">

    {{-- Tab Navigation --}}
    <div class="flex gap-1 p-1 bg-gray-100 rounded-xl w-fit">
        <button @click="tab = 'student'"
                :class="tab === 'student' ? 'bg-white shadow-sm text-primary-700' : 'text-gray-600 hover:text-gray-900'"
                class="px-5 py-2 rounded-lg text-sm font-semibold transition-all">
            Student Attendance
        </button>
        <button @click="switchStaff()"
                :class="tab === 'staff' ? 'bg-white shadow-sm text-primary-700' : 'text-gray-600 hover:text-gray-900'"
                class="px-5 py-2 rounded-lg text-sm font-semibold transition-all">
            Staff / Teacher Attendance
        </button>
    </div>

    {{-- ═══ STUDENT TAB ═══ --}}
    <div x-show="tab === 'student'" x-transition class="space-y-6">
        <div class="mc-card p-5">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="form-label">Class</label>
                    <select x-model="classId" @change="onClassChange()" class="form-input">
                        <option value="">Select Class</option>
                        @foreach($navClasses ?? [] as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Section</label>
                    <select x-model="sectionId" @change="loadStudents()" :disabled="!classId" class="form-input disabled:bg-gray-50 disabled:text-gray-400">
                        <option value="">Select Section</option>
                        <template x-for="s in filteredSections" :key="s.id">
                            <option :value="s.id" x-text="s.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="form-label">Date</label>
                    <input type="date" x-model="date" :max="today" @change="loadStudents()" class="form-input">
                </div>
                <div class="flex items-end gap-2">
                    <button type="button" @click="markAll('present')" :disabled="!students.length" class="flex-1 px-3 py-2.5 text-xs font-semibold bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition disabled:opacity-40">All Present</button>
                    <button type="button" @click="markAll('absent')" :disabled="!students.length" class="flex-1 px-3 py-2.5 text-xs font-semibold bg-rose-50 text-rose-700 rounded-lg hover:bg-rose-100 transition disabled:opacity-40">All Absent</button>
                </div>
            </div>
            <div x-show="studentHasRecord" x-cloak x-transition class="mt-4 flex items-center gap-2 px-4 py-2.5 rounded-lg bg-blue-50 border border-blue-100 text-sm text-blue-700">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                <span>Attendance already recorded for this date — you're editing it.</span>
            </div>
        </div>

        <div x-show="students.length > 0" x-cloak class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            <template x-for="pill in studentPills" :key="pill.key">
                <div class="mc-card px-4 py-3 flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-500" x-text="pill.label"></span>
                    <span class="text-lg font-bold" :class="pill.color" x-text="pill.count"></span>
                </div>
            </template>
        </div>

        <div x-show="loading" x-cloak class="flex items-center justify-center py-16">
            <div class="w-8 h-8 border-2 border-primary-200 border-t-primary-600 rounded-full animate-spin"></div>
        </div>

        <form method="POST" action="{{ panel_route('attendance.students.mark') }}" x-show="!loading && students.length > 0" x-cloak>
            @csrf
            <input type="hidden" name="class_id" :value="classId">
            <input type="hidden" name="section_id" :value="sectionId">
            <input type="hidden" name="date" :value="date">
            <div class="mc-card overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50/80 border-b border-gray-200">
                        <tr class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Roll</th>
                            <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Name</th>
                            <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Present</th>
                            <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Absent</th>
                            <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Late</th>
                            <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Half Day</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <template x-for="(s, i) in students" :key="s.id">
                            <tr class="hover:bg-primary-50/40 transition-colors">
                                <td class="px-4 py-2.5 text-gray-500" x-text="s.roll_number || '—'"></td>
                                <td class="px-4 py-2.5"><span class="font-medium text-gray-900" x-text="s.name"></span></td>
                                <input type="hidden" :name="'attendance['+i+'][student_id]'" :value="s.id">
                                <template x-for="opt in studentOptions" :key="opt.val">
                                    <td class="px-4 py-2.5 text-center">
                                        <label class="inline-flex items-center justify-center cursor-pointer">
                                            <input type="radio" :name="'attendance['+i+'][status]'" :value="opt.val" x-model="attendance[s.id]" class="peer sr-only">
                                            <span class="w-6 h-6 rounded-full border-2 border-gray-200 flex items-center justify-center transition-all peer-checked:scale-110" :class="attendance[s.id]===opt.val ? opt.active : 'bg-white'">
                                                <svg x-show="attendance[s.id]===opt.val" class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                            </span>
                                        </label>
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div class="px-4 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-500"><span class="font-semibold text-gray-700" x-text="students.length"></span> students</p>
                    <button type="submit" class="btn btn-primary">
                        <span x-text="studentHasRecord ? 'Update Attendance' : 'Save Attendance'"></span>
                    </button>
                </div>
            </div>
        </form>

        <div x-show="!loading && students.length === 0 && classId && sectionId" x-cloak class="mc-card text-center py-16">
            <p class="text-gray-500 font-medium">No students found for this class & section</p>
        </div>
        <div x-show="!classId || !sectionId" x-cloak class="text-center py-16 bg-white rounded-2xl border border-dashed border-gray-200">
            <p class="text-gray-400">Select a class, section and date to load the roster.</p>
        </div>
    </div>

    {{-- ═══ STAFF TAB ═══ --}}
    <div x-show="tab === 'staff'" x-transition class="space-y-6">
        <div class="mc-card p-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Date</label>
                    <input type="date" x-model="staffDate" :max="today" @change="loadStaff()" class="form-input">
                </div>
                <div class="flex items-end gap-2 md:col-span-2">
                    <button type="button" @click="markAllStaff('present')" :disabled="!staff.length" class="px-3 py-2.5 text-xs font-semibold bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition disabled:opacity-40">All Present</button>
                    <button type="button" @click="markAllStaff('absent')" :disabled="!staff.length" class="px-3 py-2.5 text-xs font-semibold bg-rose-50 text-rose-700 rounded-lg hover:bg-rose-100 transition disabled:opacity-40">All Absent</button>
                    <button type="button" @click="markAllStaff('leave')" :disabled="!staff.length" class="px-3 py-2.5 text-xs font-semibold bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition disabled:opacity-40">All Leave</button>
                </div>
            </div>
            <div x-show="staffHasRecord" x-cloak x-transition class="mt-4 flex items-center gap-2 px-4 py-2.5 rounded-lg bg-blue-50 border border-blue-100 text-sm text-blue-700">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                <span>Staff attendance already recorded for this date — you're editing it.</span>
            </div>
        </div>

        <div x-show="staff.length > 0" x-cloak class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            <template x-for="pill in staffPills" :key="pill.key">
                <div class="mc-card px-4 py-3 flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-500" x-text="pill.label"></span>
                    <span class="text-lg font-bold" :class="pill.color" x-text="pill.count"></span>
                </div>
            </template>
        </div>

        <div x-show="staffLoading" x-cloak class="flex items-center justify-center py-16">
            <div class="w-8 h-8 border-2 border-primary-200 border-t-primary-600 rounded-full animate-spin"></div>
        </div>

        <form method="POST" action="{{ panel_route('attendance.staff.mark') }}" x-show="!staffLoading && staff.length > 0" x-cloak>
            @csrf
            <input type="hidden" name="date" :value="staffDate">
            <div class="mc-card overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50/80 border-b border-gray-200">
                        <tr class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Emp ID</th>
                            <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Name</th>
                            <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Present</th>
                            <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Absent</th>
                            <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Leave</th>
                            <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Late</th>
                            <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Half Day</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <template x-for="(m, i) in staff" :key="m.id">
                            <tr class="hover:bg-primary-50/40 transition-colors">
                                <td class="px-4 py-2.5 text-gray-500" x-text="m.employee_id || '—'"></td>
                                <td class="px-4 py-2.5"><span class="font-medium text-gray-900" x-text="m.name"></span></td>
                                <input type="hidden" :name="'attendance['+i+'][user_id]'" :value="m.id">
                                <template x-for="opt in staffOptions" :key="opt.val">
                                    <td class="px-4 py-2.5 text-center">
                                        <label class="inline-flex items-center justify-center cursor-pointer">
                                            <input type="radio" :name="'attendance['+i+'][status]'" :value="opt.val" x-model="staffAtt[m.id]" class="peer sr-only">
                                            <span class="w-6 h-6 rounded-full border-2 border-gray-200 flex items-center justify-center transition-all peer-checked:scale-110" :class="staffAtt[m.id]===opt.val ? opt.active : 'bg-white'">
                                                <svg x-show="staffAtt[m.id]===opt.val" class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                            </span>
                                        </label>
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div class="px-4 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-500"><span class="font-semibold text-gray-700" x-text="staff.length"></span> staff members</p>
                    <button type="submit" class="btn btn-primary">
                        <span x-text="staffHasRecord ? 'Update Attendance' : 'Save Staff Attendance'"></span>
                    </button>
                </div>
            </div>
        </form>

        <div x-show="!staffLoading && staff.length === 0" x-cloak class="mc-card text-center py-16">
            <p class="text-gray-500 font-medium">No staff found for this school</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
function attendanceHub() {
    return {
        tab: 'student',
        today: new Date().toISOString().split('T')[0],
        allSections: @json($navSections ?? []),

        // student
        classId: '', sectionId: '',
        date: new Date().toISOString().split('T')[0],
        students: [], attendance: {}, loading: false, studentHasRecord: false,
        studentOptions: [
            { val:'present',  active:'bg-green-500 border-green-500' },
            { val:'absent',   active:'bg-rose-500 border-rose-500' },
            { val:'late',     active:'bg-amber-500 border-amber-500' },
            { val:'half_day', active:'bg-orange-500 border-orange-500' },
        ],

        // staff
        staff: [], staffAtt: {}, staffLoading: false, staffHasRecord: false, staffLoaded: false,
        staffDate: new Date().toISOString().split('T')[0],
        staffOptions: [
            { val:'present',  active:'bg-green-500 border-green-500' },
            { val:'absent',   active:'bg-rose-500 border-rose-500' },
            { val:'leave',    active:'bg-blue-500 border-blue-500' },
            { val:'late',     active:'bg-amber-500 border-amber-500' },
            { val:'half_day', active:'bg-orange-500 border-orange-500' },
        ],

        init() {},
        get filteredSections() { return this.allSections.filter(s => String(s.class_id) === String(this.classId)); },
        get studentPills() {
            const c = (st) => this.students.filter(s => this.attendance[s.id] === st).length;
            return [
                { key:'t', label:'Total',    count:this.students.length, color:'text-gray-900' },
                { key:'p', label:'Present',  count:c('present'),  color:'text-green-600' },
                { key:'a', label:'Absent',   count:c('absent'),   color:'text-rose-600' },
                { key:'l', label:'Late',     count:c('late'),     color:'text-amber-600' },
                { key:'h', label:'Half Day', count:c('half_day'), color:'text-orange-600' },
            ];
        },
        get staffPills() {
            const c = (st) => this.staff.filter(m => this.staffAtt[m.id] === st).length;
            return [
                { key:'t', label:'Total',   count:this.staff.length, color:'text-gray-900' },
                { key:'p', label:'Present', count:c('present'), color:'text-green-600' },
                { key:'a', label:'Absent',  count:c('absent'),  color:'text-rose-600' },
                { key:'lv',label:'Leave',   count:c('leave'),   color:'text-blue-600' },
                { key:'l', label:'Late',    count:c('late'),    color:'text-amber-600' },
            ];
        },
        onClassChange() { this.sectionId=''; this.students=[]; this.studentHasRecord=false; },
        async loadStudents() {
            if (!this.classId || !this.sectionId) { this.students=[]; return; }
            this.loading = true;
            try {
                const url = `{{ panel_route('attendance.students.roster') }}?class_id=${this.classId}&section_id=${this.sectionId}&date=${this.date}`;
                const res = await fetch(url, { headers: { 'Accept':'application/json' } });
                const json = await res.json();
                this.students = json.data || [];
                this.studentHasRecord = !!json.has_record;
                this.attendance = {};
                this.students.forEach(s => { this.attendance[s.id] = s.status || 'present'; });
            } catch(e) { this.students=[]; } finally { this.loading=false; }
        },
        markAll(status) { this.students.forEach(s => { this.attendance[s.id] = status; }); },

        switchStaff() { this.tab='staff'; if (!this.staffLoaded) { this.staffLoaded=true; this.loadStaff(); } },
        async loadStaff() {
            this.staffLoading = true;
            try {
                const url = `{{ panel_route('attendance.staff.roster') }}?date=${this.staffDate}`;
                const res = await fetch(url, { headers: { 'Accept':'application/json' } });
                const json = await res.json();
                this.staff = json.data || [];
                this.staffHasRecord = !!json.has_record;
                this.staffAtt = {};
                this.staff.forEach(m => { this.staffAtt[m.id] = m.status || 'present'; });
            } catch(e) { this.staff=[]; } finally { this.staffLoading=false; }
        },
        markAllStaff(status) { this.staff.forEach(m => { this.staffAtt[m.id] = status; }); },
    };
}
</script>
@endpush
@endsection
