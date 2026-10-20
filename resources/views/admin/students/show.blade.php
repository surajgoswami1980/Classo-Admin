@extends('layouts.app')
@section('title', 'Student Details')
@section('page-title', 'Student Details')
@section('content')
<div class="max-w-4xl">
    <div class="flex items-center justify-between mb-6">
        <a href="{{ panel_route('students.index') }}" class="text-sm text-gray-500 hover:text-blue-600">← Back to list</a>
        <a href="{{ panel_route('students.edit', $student->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Edit Student</a>
    </div>

    <div class="mc-card p-6 mb-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center text-xl font-bold text-blue-700">
                {{ strtoupper(substr($student->user_name ?? 'S', 0, 1)) }}
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $student->user_name ?? '—' }}</h2>
                <p class="text-sm text-gray-500">{{ $student->class_name ?? '' }} - {{ $student->section_name ?? '' }} | Roll: {{ $student->roll_number ?? '—' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div><p class="text-xs text-gray-500">Admission No</p><p class="text-sm font-medium">{{ $student->admission_number ?? '—' }}</p></div>
            <div><p class="text-xs text-gray-500">Roll Number</p><p class="text-sm font-medium">{{ $student->roll_number ?? '—' }}</p></div>
            <div><p class="text-xs text-gray-500">Email</p><p class="text-sm">{{ $student->email ?? '—' }}</p></div>
            <div><p class="text-xs text-gray-500">Phone</p><p class="text-sm">{{ $student->phone ?? '—' }}</p></div>
            <div><p class="text-xs text-gray-500">Gender</p><p class="text-sm">{{ ucfirst($student->gender ?? '—') }}</p></div>
            <div><p class="text-xs text-gray-500">DOB</p><p class="text-sm">{{ $student->date_of_birth ?? '—' }}</p></div>
            <div><p class="text-xs text-gray-500">Blood Group</p><p class="text-sm">{{ $student->blood_group ?? '—' }}</p></div>
            <div><p class="text-xs text-gray-500">Status</p><p class="text-sm font-medium {{ $student->status === 'active' ? 'text-green-600' : 'text-red-600' }}">{{ ucfirst($student->status ?? '') }}</p></div>
            <div><p class="text-xs text-gray-500">Admission Date</p><p class="text-sm">{{ $student->admission_date ?? '—' }}</p></div>
            <div><p class="text-xs text-gray-500">Father</p><p class="text-sm">{{ $student->father_name ?? '—' }} {{ $student->father_phone ? '('.$student->father_phone.')' : '' }}</p></div>
            <div><p class="text-xs text-gray-500">Mother</p><p class="text-sm">{{ $student->mother_name ?? '—' }}</p></div>
            <div><p class="text-xs text-gray-500">Address</p><p class="text-sm">{{ $student->address ?? '—' }}, {{ $student->city ?? '' }} {{ $student->state ?? '' }}</p></div>
        </div>
    </div>

    <!-- Fee profile & per-student override -->
    <div class="mc-card p-6" x-data="{ edit:null, amount:'', reason:'' }">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Fees</h3>
            <span class="text-xs text-gray-400">Adjust a category amount for this student (scholarship, sibling discount, penalty…)</span>
        </div>

        @if(session('success'))<div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-2 text-sm mb-3">{{ session('success') }}</div>@endif

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-2 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Category</th>
                        <th class="px-4 py-2 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Type</th>
                        <th class="px-4 py-2 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Class Amount</th>
                        <th class="px-4 py-2 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Effective</th>
                        <th class="px-4 py-2 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($feeProfile as $f)
                        @php $eff = $f->override_amount !== null ? $f->override_amount : $f->class_amount; @endphp
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $f->category_name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ str_replace('_',' ', $f->category_type) }}</td>
                            <td class="px-4 py-3 text-gray-700">₹{{ number_format($f->class_amount) }}</td>
                            <td class="px-4 py-3 font-semibold {{ $f->override_amount !== null ? 'text-amber-600' : 'text-gray-900' }}">
                                ₹{{ number_format($eff) }}
                                @if($f->override_amount !== null)<span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-amber-100 text-amber-700">overridden</span>@endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button @click="edit={{ $f->fee_category_id }}; amount='{{ $eff }}'; reason='{{ $f->override_reason }}'"
                                        class="text-blue-600 hover:text-blue-700 text-xs font-medium">Adjust</button>
                            </td>
                        </tr>
                        <tr x-show="edit==={{ $f->fee_category_id }}" x-cloak>
                            <td colspan="5" class="px-4 py-3 bg-blue-50/40">
                                <form method="POST" action="{{ panel_route('fees.student-override') }}" class="flex flex-wrap items-end gap-3">
                                    @csrf
                                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                                    <input type="hidden" name="fee_category_id" value="{{ $f->fee_category_id }}">
                                    <div>
                                        <label class="form-label">New Amount (₹)</label>
                                        <input name="amount" type="number" min="0" step="0.01" x-model="amount" required class="form-input w-40">
                                    </div>
                                    <div class="flex-1 min-w-[180px]">
                                        <label class="form-label">Reason</label>
                                        <input name="reason" x-model="reason" class="form-input" placeholder="e.g. Sibling discount">
                                    </div>
                                    <button class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm">Save</button>
                                    <button type="button" @click="edit=null" class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm">Cancel</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">No fee categories configured for this class yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p class="text-xs text-gray-400 mt-3">Note: adjustments apply to <strong>future</strong> generated invoices. Already-generated invoices are unchanged.</p>
    </div>
</div>
@endsection
