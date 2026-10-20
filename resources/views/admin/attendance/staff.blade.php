@extends('layouts.app')
@section('title', 'Staff Attendance')
@section('page-title', 'Mark Staff Attendance')
@section('content')
<div x-data="staffAttendance()" x-init="init()" class="space-y-6">

    {{-- Filter bar --}}
    <div class="mc-card p-5 animate-fade-up">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="form-label">Date</label>
                <input type="date" x-model="date" :max="today" @change="fetchRoster()" class="form-input">
            </div>
            <div class="flex items-end gap-2 md:col-span-2">
                <button type="button" @click="markAll('present')" :disabled="!staff.length" class="px-3 py-2.5 text-xs font-semibold bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition disabled:opacity-40">All Present</button>
                <button type="button" @click="markAll('absent')" :disabled="!staff.length" class="px-3 py-2.5 text-xs font-semibold bg-rose-50 text-rose-700 rounded-lg hover:bg-rose-100 transition disabled:opacity-40">All Absent</button>
                <button type="button" @click="markAll('leave')" :disabled="!staff.length" class="px-3 py-2.5 text-xs font-semibold bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition disabled:opacity-40">All Leave</button>
            </div>
        </div>

        <div x-show="dayOff" x-cloak x-transition class="mt-4 flex items-center gap-2 px-4 py-2.5 rounded-lg bg-rose-50 border border-rose-100 text-sm text-rose-700">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
            <span x-text="dayOff && dayOff.kind === 'holiday' ? 'Holiday: ' + dayOff.title + ' — attendance can\'t be marked on this day.' : 'Weekly off day — attendance can\'t be marked.'"></span>
        </div>

        <div x-show="hasRecord && !dayOff" x-cloak x-transition class="mt-4 flex items-center gap-2 px-4 py-2.5 rounded-lg bg-blue-50 border border-blue-100 text-sm text-blue-700">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
            <span>Staff attendance was already recorded for this date. You're editing the existing record.</span>
        </div>
    </div>

    {{-- Summary pills --}}
    <div x-show="staff.length > 0" x-cloak class="grid grid-cols-2 sm:grid-cols-5 gap-3 animate-fade-up">
        <template x-for="pill in summaryPills" :key="pill.key">
            <div class="mc-card px-4 py-3 flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500" x-text="pill.label"></span>
                <span class="text-lg font-bold" :class="pill.color" x-text="pill.count"></span>
            </div>
        </template>
    </div>

    <div x-show="loading" x-cloak class="flex items-center justify-center py-16">
        <div class="w-8 h-8 border-2 border-primary-200 border-t-primary-600 rounded-full animate-spin"></div>
    </div>

    <form method="POST" action="{{ panel_route('attendance.staff.mark') }}" x-show="!loading && staff.length > 0" x-cloak>
        @csrf
        <input type="hidden" name="date" :value="date">

        <div class="mc-card overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80 border-b border-gray-200">
                    <tr class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Emp ID</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Name</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">How marked</th>
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
                            <td class="px-4 py-2.5">
                                <span class="font-medium text-gray-900" x-text="m.name"></span>
                                <span x-show="m.recorded" class="ml-2 text-[10px] font-semibold text-blue-500">●</span>
                            </td>
                            <td class="px-4 py-2.5">
                                <template x-if="m.method">
                                    <div>
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium ring-1 ring-inset" :class="methodClass(m.method)" x-text="methodLabel(m.method)"></span>
                                        <p class="mt-0.5 text-[11px] text-gray-500" x-show="m.check_in">
                                            <span x-text="'In ' + m.check_in"></span><span x-show="m.check_out" x-text="' · Out ' + m.check_out"></span>
                                            <span x-show="m.late_minutes > 0" class="text-amber-600" x-text="' · ' + m.late_minutes + 'm late'"></span>
                                        </p>
                                        <p class="text-[11px] text-gray-400" x-show="m.distance !== null || m.face_score">
                                            <span x-show="m.distance !== null" x-text="m.distance + ' m from school'"></span>
                                            <span x-show="m.face_score" x-text="' · face ' + Math.round(m.face_score) + '%'"></span>
                                        </p>
                                    </div>
                                </template>
                                <span x-show="!m.method" class="text-xs text-gray-300">—</span>
                            </td>
                            <input type="hidden" :name="'attendance['+i+'][user_id]'" :value="m.id">
                            <template x-for="opt in options" :key="opt.val">
                                <td class="px-4 py-2.5 text-center">
                                    <label class="inline-flex items-center justify-center cursor-pointer">
                                        <input type="radio" :name="'attendance['+i+'][status]'" :value="opt.val" x-model="attendance[m.id]" class="peer sr-only">
                                        <span class="w-6 h-6 rounded-full border-2 border-gray-200 flex items-center justify-center transition-all peer-checked:scale-110"
                                              :class="attendance[m.id]===opt.val ? opt.active : 'bg-white'">
                                            <svg x-show="attendance[m.id]===opt.val" class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
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
                <button type="submit" :disabled="!!dayOff" class="btn btn-primary disabled:opacity-40">
                    <span x-text="hasRecord ? 'Update Attendance' : 'Save Staff Attendance'"></span>
                </button>
            </div>
        </div>
    </form>

    <div x-show="!loading && staff.length === 0" x-cloak class="mc-card text-center py-16">
        <p class="text-gray-500 font-medium">No staff found for this school</p>
    </div>
</div>

@push('scripts')
<script>
function staffAttendance() {
    return {
        staff: [], attendance: {}, loading: false, hasRecord: false, dayOff: null,
        methodLabel(m) { return ({ manual: 'Admin', app: 'App', geo: 'Location', face: 'Face', face_geo: 'Face + location', biometric: 'Fingerprint' })[m] || m; },
        methodClass(m) { return ({ manual: 'bg-gray-50 text-gray-600 ring-gray-500/20', geo: 'bg-sky-50 text-sky-700 ring-sky-600/20', app: 'bg-sky-50 text-sky-700 ring-sky-600/20', face: 'bg-primary-50 text-primary-700 ring-primary-600/20', face_geo: 'bg-primary-50 text-primary-700 ring-primary-600/20', biometric: 'bg-green-50 text-green-700 ring-green-600/20' })[m] || 'bg-gray-50 text-gray-600 ring-gray-500/20'; },
        date: new Date().toISOString().split('T')[0],
        today: new Date().toISOString().split('T')[0],
        options: [
            { val: 'present',  active: 'bg-green-500 border-green-500' },
            { val: 'absent',   active: 'bg-rose-500 border-rose-500' },
            { val: 'leave',    active: 'bg-blue-500 border-blue-500' },
            { val: 'late',     active: 'bg-amber-500 border-amber-500' },
            { val: 'half_day', active: 'bg-orange-500 border-orange-500' },
        ],
        init() { this.fetchRoster(); },
        get summaryPills() {
            const c = (st) => this.staff.filter(m => this.attendance[m.id] === st).length;
            return [
                { key:'t', label:'Total',   count:this.staff.length, color:'text-gray-900' },
                { key:'p', label:'Present', count:c('present'), color:'text-green-600' },
                { key:'a', label:'Absent',  count:c('absent'),  color:'text-rose-600' },
                { key:'lv',label:'Leave',   count:c('leave'),   color:'text-blue-600' },
                { key:'l', label:'Late',    count:c('late'),    color:'text-amber-600' },
            ];
        },
        async fetchRoster() {
            this.loading = true;
            try {
                const url = `{{ panel_route('attendance.staff.roster') }}?date=${this.date}`;
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const json = await res.json();
                this.staff = json.data || [];
                this.hasRecord = !!json.has_record;
                this.dayOff = json.day_off || null;
                this.attendance = {};
                this.staff.forEach(m => { this.attendance[m.id] = m.status || 'present'; });
            } catch (e) {
                this.staff = [];
            } finally {
                this.loading = false;
            }
        },
        markAll(status) { this.staff.forEach(m => { this.attendance[m.id] = status; }); },
    };
}
</script>
@endpush
@endsection
