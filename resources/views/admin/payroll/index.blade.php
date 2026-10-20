@extends('layouts.app')

@section('title', 'Payroll')
@section('page-title', 'Payroll & Payslips')

@php
    $months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
@endphp

@section('content')
<div x-data="{
        showStructure:false, showPayslip:false,
        sForm:{ user_id:'', name:'', basic:0, hra:0, allowances:0, deductions:0 },
        pForm:{ user_id:'', name:'', month:{{ $month }}, year:{{ $year }}, lop_days:0, extra_deductions:0, remarks:'' },
        openStructure(s){ this.sForm={ user_id:s.user_id, name:s.name, basic:s.basic||0, hra:s.hra||0, allowances:s.allowances||0, deductions:s.deductions||0 }; this.showStructure=true; },
        openPayslip(s){ this.pForm={ user_id:s.user_id, name:s.name, month:{{ $month }}, year:{{ $year }}, lop_days:0, extra_deductions:0, remarks:'' }; this.showPayslip=true; },
        get sGross(){ return (+this.sForm.basic||0)+(+this.sForm.hra||0)+(+this.sForm.allowances||0); },
        get sNet(){ return this.sGross-(+this.sForm.deductions||0); },
     }" class="space-y-6">

    {{-- Summary --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="mc-card p-4"><p class="text-xs text-gray-500">Payslips ({{ $months[$month] }} {{ $year }})</p><p class="text-2xl font-bold text-gray-900 mt-1">{{ $summary->payslips ?? 0 }}</p></div>
        <div class="mc-card p-4"><p class="text-xs text-gray-500">Total Net</p><p class="text-2xl font-bold text-blue-600 mt-1">₹{{ number_format($summary->total_net ?? 0) }}</p></div>
        <div class="mc-card p-4"><p class="text-xs text-gray-500">Paid</p><p class="text-2xl font-bold text-green-600 mt-1">₹{{ number_format($summary->paid_net ?? 0) }}</p></div>
        <div class="mc-card p-4"><p class="text-xs text-gray-500">Pending</p><p class="text-2xl font-bold text-amber-600 mt-1">₹{{ number_format($summary->pending_net ?? 0) }}</p></div>
    </div>

    {{-- Period filter --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex gap-2">
            <select name="month" class="form-input">
                @foreach($months as $m => $label)<option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ $label }}</option>@endforeach
            </select>
            <select name="year" class="form-input">
                @for($y = now()->year; $y >= now()->year - 3; $y--)<option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>@endfor
            </select>
            <button type="submit" class="btn btn-primary">View</button>
        </form>
    </div>

    {{-- Staff & salary structures --}}
    <div class="mc-card overflow-hidden">
        <div class="px-4 py-3 border-b bg-gray-50"><h3 class="text-sm font-semibold text-gray-800">Staff & Salary Structures</h3></div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50/80 border-b border-gray-200"><tr>
                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Staff</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Basic</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">HRA</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Allowances</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Deductions</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Net</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($staff as $s)
                <tr class="hover:bg-primary-50/40 transition-colors">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900">{{ $s->name }}</p>
                        <p class="text-xs text-gray-400">{{ $s->designation ?? '—' }}</p>
                    </td>
                    <td class="px-4 py-3 text-right text-gray-600">{{ $s->basic !== null ? '₹'.number_format($s->basic) : '—' }}</td>
                    <td class="px-4 py-3 text-right text-gray-600">{{ $s->hra !== null ? '₹'.number_format($s->hra) : '—' }}</td>
                    <td class="px-4 py-3 text-right text-gray-600">{{ $s->allowances !== null ? '₹'.number_format($s->allowances) : '—' }}</td>
                    <td class="px-4 py-3 text-right text-gray-600">{{ $s->deductions !== null ? '₹'.number_format($s->deductions) : '—' }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ $s->net !== null ? '₹'.number_format($s->net) : '—' }}</td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <button @click='openStructure(@json($s))' class="text-xs font-medium text-blue-600 hover:underline">{{ $s->structure_id ? 'Edit Salary' : 'Set Salary' }}</button>
                        @if($s->structure_id)
                        <button @click='openPayslip(@json($s))' class="text-xs font-medium text-green-600 hover:underline ml-3">Generate Payslip</button>
                        @endif
                    </td>
                </tr>
                @empty<tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">No active staff found. Add teachers or team members first.</td></tr>@endforelse
            </tbody>
        </table>
    </div>

    {{-- Payslips for the period --}}
    <div class="mc-card overflow-hidden">
        <div class="px-4 py-3 border-b bg-gray-50"><h3 class="text-sm font-semibold text-gray-800">Payslips — {{ $months[$month] }} {{ $year }}</h3></div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50/80 border-b border-gray-200"><tr>
                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Staff</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Gross</th>
                <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">LOP Days</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Deductions</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Net Pay</th>
                <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Status</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($payslips as $p)
                <tr class="hover:bg-primary-50/40 transition-colors">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900">{{ $p->staff_name }}</p>
                        <p class="text-xs text-gray-400">{{ $p->designation ?? '—' }}</p>
                    </td>
                    <td class="px-4 py-3 text-right text-gray-600">₹{{ number_format($p->gross) }}</td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ $p->lop_days }}</td>
                    <td class="px-4 py-3 text-right text-gray-600">₹{{ number_format($p->deductions) }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-900">₹{{ number_format($p->net) }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $p->status === 'paid' ? 'text-green-700 bg-green-50' : 'text-amber-700 bg-amber-50' }}">{{ ucfirst($p->status) }}</span>
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <a href="{{ panel_route('payroll.payslip.document', $p->id) }}" target="_blank" class="text-xs font-medium text-cyan-600 hover:underline">Payslip</a>
                        @if($p->status !== 'paid')
                        <form method="POST" action="{{ panel_route('payroll.payslip.paid', $p->id) }}" class="inline">@csrf
                            <button type="submit" class="text-xs font-medium text-green-600 hover:underline ml-3">Mark Paid</button>
                        </form>
                        @else
                        <span class="text-xs text-gray-400 ml-3">Paid {{ $p->paid_on ? \Illuminate\Support\Carbon::parse($p->paid_on)->format('d M') : '' }}</span>
                        @endif
                    </td>
                </tr>
                @empty<tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">No payslips generated for this period.</td></tr>@endforelse
            </tbody>
        </table>
    </div>

    {{-- Salary Structure Modal --}}
    <div x-show="showStructure" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-navy-950/50 backdrop-blur-[2px]" @click.self="showStructure=false">
        <div class="modal-panel max-w-md p-6" @click.stop>
            <h3 class="text-base font-semibold text-gray-900 mb-1">Salary Structure</h3>
            <p class="text-sm text-gray-500 mb-4" x-text="sForm.name"></p>
            <form method="POST" action="{{ panel_route('payroll.structure.store') }}" class="space-y-4">@csrf
                <input type="hidden" name="user_id" :value="sForm.user_id">
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="form-label">Basic *</label><input type="number" name="basic" x-model="sForm.basic" min="0" step="0.01" required class="form-input"></div>
                    <div><label class="form-label">HRA</label><input type="number" name="hra" x-model="sForm.hra" min="0" step="0.01" class="form-input"></div>
                    <div><label class="form-label">Allowances</label><input type="number" name="allowances" x-model="sForm.allowances" min="0" step="0.01" class="form-input"></div>
                    <div><label class="form-label">Deductions</label><input type="number" name="deductions" x-model="sForm.deductions" min="0" step="0.01" class="form-input"></div>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 text-sm">
                    <span class="text-gray-500">Gross: <span class="font-semibold text-gray-800">₹<span x-text="sGross.toLocaleString()"></span></span></span>
                    <span class="text-gray-500">Net: <span class="font-semibold text-green-700">₹<span x-text="sNet.toLocaleString()"></span></span></span>
                </div>
                <div class="flex justify-end gap-2"><button type="button" @click="showStructure=false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancel</button><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Save</button></div>
            </form>
        </div>
    </div>

    {{-- Generate Payslip Modal --}}
    <div x-show="showPayslip" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-navy-950/50 backdrop-blur-[2px]" @click.self="showPayslip=false">
        <div class="modal-panel max-w-md p-6" @click.stop>
            <h3 class="text-base font-semibold text-gray-900 mb-1">Generate Payslip</h3>
            <p class="text-sm text-gray-500 mb-4" x-text="pForm.name"></p>
            <form method="POST" action="{{ panel_route('payroll.payslip.generate') }}" class="space-y-4">@csrf
                <input type="hidden" name="user_id" :value="pForm.user_id">
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="form-label">Month *</label>
                        <select name="month" x-model="pForm.month" class="form-input">
                            @foreach($months as $m => $label)<option value="{{ $m }}">{{ $label }}</option>@endforeach
                        </select></div>
                    <div><label class="form-label">Year *</label>
                        <select name="year" x-model="pForm.year" class="form-input">
                            @for($y = now()->year; $y >= now()->year - 3; $y--)<option value="{{ $y }}">{{ $y }}</option>@endfor
                        </select></div>
                    <div><label class="form-label">LOP Days</label><input type="number" name="lop_days" x-model="pForm.lop_days" min="0" max="31" class="form-input"></div>
                    <div><label class="form-label">Extra Deductions</label><input type="number" name="extra_deductions" x-model="pForm.extra_deductions" min="0" step="0.01" class="form-input"></div>
                </div>
                <div><label class="form-label">Remarks</label><textarea name="remarks" x-model="pForm.remarks" rows="2" class="form-input"></textarea></div>
                <div class="flex justify-end gap-2"><button type="button" @click="showPayslip=false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancel</button><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Generate</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
