@extends('layouts.app')
@section('title', 'Student Attendance')
@section('page-title', 'Mark Student Attendance')
@section('content')
<div x-data="studentAttendance()" x-init="init()" class="space-y-6">

    {{-- Filter bar --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 animate-fade-up">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Class</label>
                <select x-model="classId" @change="onClassChange()" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 outline-none transition">
                    <option value="">Select Class</option>
                    @foreach($classes as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Section</label>
                <select x-model="sectionId" @change="fetchRoster()" :disabled="!classId" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 outline-none transition disabled:bg-gray-50 disabled:text-gray-400">
                    <option value="">Select Section</option>
                    <template x-for="s in filteredSections" :key="s.id">
                        <option :value="s.id" x-text="s.name"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Date</label>
                <input type="date" x-model="date" :max="today" @change="fetchRoster()" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 outline-none transition">
            </div>
            <div class="flex items-end gap-2">
                <button type="button" @click="markAll('present')" :disabled="!students.length" class="flex-1 px-3 py-2.5 text-xs font-semibold bg-emerald-50 text-emerald-700 rounded-lg hover:bg-emerald-100 transition disabled:opacity-40">All Present</button>
                <button type="button" @click="markAll('absent')" :disabled="!students.length" class="flex-1 px-3 py-2.5 text-xs font-semibold bg-rose-50 text-rose-700 rounded-lg hover:bg-rose-100 transition disabled:opacity-40">All Absent</button>
            </div>
        </div>

        {{-- status banner for existing record --}}
        <div x-show="hasRecord" x-cloak x-transition class="mt-4 flex items-center gap-2 px-4 py-2.5 rounded-lg bg-blue-50 border border-blue-100 text-sm text-blue-700">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
            <span>Attendance was already recorded for this date. You're editing the existing record.</span>
        </div>
    </div>

    {{-- Summary pills --}}
    <div x-show="students.length > 0" x-cloak class="grid grid-cols-2 sm:grid-cols-5 gap-3 animate-fade-up">
        <template x-for="pill in summaryPills" :key="pill.key">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-3 flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500" x-text="pill.label"></span>
                <span class="text-lg font-bold" :class="pill.color" x-text="pill.count"></span>
            </div>
        </template>
    </div>

    {{-- loading --}}
    <div x-show="loading" x-cloak class="flex items-center justify-center py-16">
        <div class="w-8 h-8 border-2 border-primary-200 border-t-primary-600 rounded-full animate-spin"></div>
    </div>

    <form method="POST" action="{{ panel_route('attendance.students.mark') }}" x-show="!loading && students.length > 0" x-cloak>
        @csrf
        <input type="hidden" name="class_id" :value="classId">
        <input type="hidden" name="section_id" :value="sectionId">
        <input type="hidden" name="date" :value="date">

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left">Roll</th>
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-center">Present</th>
                        <th class="px-4 py-3 text-center">Absent</th>
                        <th class="px-4 py-3 text-center">Late</th>
                        <th class="px-4 py-3 text-center">Half Day</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <template x-for="(s, i) in students" :key="s.id">
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-4 py-2.5 text-gray-500" x-text="s.roll_number || '—'"></td>
                            <td class="px-4 py-2.5">
                                <span class="font-medium text-gray-900" x-text="s.name"></span>
                                <span x-show="s.recorded" class="ml-2 text-[10px] font-semibold text-blue-500">●</span>
                            </td>
                            <input type="hidden" :name="'attendance['+i+'][student_id]'" :value="s.id">
                            <template x-for="opt in options" :key="opt.val">
                                <td class="px-4 py-2.5 text-center">
                                    <label class="inline-flex items-center justify-center cursor-pointer">
                                        <input type="radio" :name="'attendance['+i+'][status]'" :value="opt.val" x-model="attendance[s.id]" class="peer sr-only">
                                        <span class="w-6 h-6 rounded-full border-2 border-gray-200 flex items-center justify-center transition-all peer-checked:scale-110"
                                              :class="attendance[s.id]===opt.val ? opt.active : 'bg-white'">
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
                <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white text-sm font-semibold rounded-lg hover:bg-primary-700 transition active:scale-95 shadow-lg shadow-primary-600/20">
                    <span x-text="hasRecord ? 'Update Attendance' : 'Save Attendance'"></span>
                </button>
            </div>
        </div>
    </form>

    {{-- empty states --}}
    <div x-show="!loading && students.length === 0 && classId && sectionId" x-cloak class="text-center py-16 bg-white rounded-2xl border border-gray-100">
        <svg class="w-12 h-12 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z" /></svg>
        <p class="mt-3 text-gray-500 font-medium">No students found for this class & section</p>
    </div>

    <div x-show="!classId || !sectionId" x-cloak class="text-center py-16 bg-white rounded-2xl border border-dashed border-gray-200">
        <p class="text-gray-400">Select a class, section and date to load the roster.</p>
    </div>
</div>

@push('scripts')
<script>
function studentAttendance() {
    return {
        allSections: @json($sections),
        classId: '', sectionId: '',
        date: new Date().toISOString().split('T')[0],
        today: new Date().toISOString().split('T')[0],
        students: [], attendance: {}, loading: false, hasRecord: false,
        options: [
            { val: 'present',  active: 'bg-emerald-500 border-emerald-500' },
            { val: 'absent',   active: 'bg-rose-500 border-rose-500' },
            { val: 'late',     active: 'bg-amber-500 border-amber-500' },
            { val: 'half_day', active: 'bg-orange-500 border-orange-500' },
        ],
        init() {},
        get filteredSections() {
            return this.allSections.filter(s => String(s.class_id) === String(this.classId));
        },
        get summaryPills() {
            const c = (st) => this.students.filter(s => this.attendance[s.id] === st).length;
            return [
                { key:'t', label:'Total',    count:this.students.length, color:'text-gray-900' },
                { key:'p', label:'Present',  count:c('present'),  color:'text-emerald-600' },
                { key:'a', label:'Absent',   count:c('absent'),   color:'text-rose-600' },
                { key:'l', label:'Late',     count:c('late'),     color:'text-amber-600' },
                { key:'h', label:'Half Day', count:c('half_day'), color:'text-orange-600' },
            ];
        },
        onClassChange() {
            this.sectionId = '';
            this.students = [];
            this.hasRecord = false;
        },
        async fetchRoster() {
            if (!this.classId || !this.sectionId) { this.students = []; return; }
            this.loading = true;
            try {
                const url = `{{ panel_route('attendance.students.roster') }}?class_id=${this.classId}&section_id=${this.sectionId}&date=${this.date}`;
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const json = await res.json();
                this.students = json.data || [];
                this.hasRecord = !!json.has_record;
                this.attendance = {};
                this.students.forEach(s => { this.attendance[s.id] = s.status || 'present'; });
            } catch (e) {
                this.students = [];
            } finally {
                this.loading = false;
            }
        },
        markAll(status) { this.students.forEach(s => { this.attendance[s.id] = status; }); },
    };
}
</script>
@endpush
@endsection
