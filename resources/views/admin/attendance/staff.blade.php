@extends('layouts.app')
@section('title', 'Staff Attendance')
@section('page-title', 'Mark Staff Attendance')
@section('content')
<div x-data="staffAttendance()" x-init="init()" class="space-y-6">

    {{-- Filter bar --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 animate-fade-up">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Date</label>
                <input type="date" x-model="date" :max="today" @change="fetchRoster()" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 outline-none transition">
            </div>
            <div class="flex items-end gap-2 md:col-span-2">
                <button type="button" @click="markAll('present')" :disabled="!staff.length" class="px-3 py-2.5 text-xs font-semibold bg-emerald-50 text-emerald-700 rounded-lg hover:bg-emerald-100 transition disabled:opacity-40">All Present</button>
                <button type="button" @click="markAll('absent')" :disabled="!staff.length" class="px-3 py-2.5 text-xs font-semibold bg-rose-50 text-rose-700 rounded-lg hover:bg-rose-100 transition disabled:opacity-40">All Absent</button>
                <button type="button" @click="markAll('leave')" :disabled="!staff.length" class="px-3 py-2.5 text-xs font-semibold bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition disabled:opacity-40">All Leave</button>
            </div>
        </div>

        <div x-show="hasRecord" x-cloak x-transition class="mt-4 flex items-center gap-2 px-4 py-2.5 rounded-lg bg-blue-50 border border-blue-100 text-sm text-blue-700">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
            <span>Staff attendance was already recorded for this date. You're editing the existing record.</span>
        </div>
    </div>

    {{-- Summary pills --}}
    <div x-show="staff.length > 0" x-cloak class="grid grid-cols-2 sm:grid-cols-5 gap-3 animate-fade-up">
        <template x-for="pill in summaryPills" :key="pill.key">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-3 flex items-center justify-between">
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

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left">Emp ID</th>
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-center">Present</th>
                        <th class="px-4 py-3 text-center">Absent</th>
                        <th class="px-4 py-3 text-center">Leave</th>
                        <th class="px-4 py-3 text-center">Late</th>
                        <th class="px-4 py-3 text-center">Half Day</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <template x-for="(m, i) in staff" :key="m.id">
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-4 py-2.5 text-gray-500" x-text="m.employee_id || '—'"></td>
                            <td class="px-4 py-2.5">
                                <span class="font-medium text-gray-900" x-text="m.name"></span>
                                <span x-show="m.recorded" class="ml-2 text-[10px] font-semibold text-blue-500">●</span>
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
                <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white text-sm font-semibold rounded-lg hover:bg-primary-700 transition active:scale-95 shadow-lg shadow-primary-600/20">
                    <span x-text="hasRecord ? 'Update Attendance' : 'Save Staff Attendance'"></span>
                </button>
            </div>
        </div>
    </form>

    <div x-show="!loading && staff.length === 0" x-cloak class="text-center py-16 bg-white rounded-2xl border border-gray-100">
        <p class="text-gray-500 font-medium">No staff found for this school</p>
    </div>
</div>

@push('scripts')
<script>
function staffAttendance() {
    return {
        staff: [], attendance: {}, loading: false, hasRecord: false,
        date: new Date().toISOString().split('T')[0],
        today: new Date().toISOString().split('T')[0],
        options: [
            { val: 'present',  active: 'bg-emerald-500 border-emerald-500' },
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
                { key:'p', label:'Present', count:c('present'), color:'text-emerald-600' },
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
