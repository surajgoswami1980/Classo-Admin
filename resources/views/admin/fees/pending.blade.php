@extends('layouts.app')

@section('title', 'Pending Fees')
@section('page-title', 'Pending Fees')

@section('content')
<div class="space-y-6" x-data="{ payId:null, payInvoice:'', showPay:false }">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Pending Fees (Class-wise)</h2>
            <p class="text-sm text-gray-500">See dues per class and record manual payments.</p>
        </div>
    </div>

    @if(session('success'))<div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">{{ $errors->first() }}</div>@endif

    <!-- Class summary -->
    <div class="mc-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Class</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Students with Dues</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Pending Invoices</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Pending Amount</th>
                        <th class="px-6 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($summary as $row)
                        <tr class="hover:bg-gray-50 {{ (int)$classId === (int)$row->class_id ? 'bg-blue-50/40' : '' }}">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $row->class_name }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $row->students_with_dues }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $row->pending_invoices }}</td>
                            <td class="px-6 py-4 font-semibold text-red-600">₹{{ number_format($row->pending_amount) }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ panel_route('fees.pending') }}?class_id={{ $row->class_id }}" class="text-blue-600 hover:text-blue-700 text-xs font-medium">View students →</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-10 text-center text-gray-400">No pending dues.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Student pending list for selected class -->
    @if($classId)
    <div class="mc-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">Pending — {{ $classes[$classId] ?? 'Class' }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Student</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Invoice</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Category</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Amount</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Due</th>
                        <th class="px-6 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rows as $r)
                        <tr class="hover:bg-primary-50/40 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $r->student_name }}</div>
                                <div class="text-xs text-gray-400">Roll {{ $r->roll_number ?? '—' }} · {{ $r->phone ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-600 font-mono text-xs">{{ $r->invoice_number }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $r->category_name ?? ($r->title ?? '—') }}</td>
                            <td class="px-6 py-4 font-semibold text-gray-900">₹{{ number_format($r->total_amount) }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ \Carbon\Carbon::parse($r->due_date)->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-right">
                                <button @click="payId={{ $r->id }}; payInvoice='{{ $r->invoice_number }}'; showPay=true"
                                        class="px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                                    Record Payment
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">No pending invoices for this class.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Manual pay modal -->
    <div x-show="showPay" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-navy-950/50 backdrop-blur-[2px]">
        <div class="bg-white rounded-xl w-full max-w-md p-6" @click.outside="showPay=false">
            <h3 class="text-lg font-semibold text-gray-900 mb-1">Record Manual Payment</h3>
            <p class="text-xs text-gray-400 mb-4" x-text="payInvoice"></p>
            <form method="POST" :action="`{{ url(panel_prefix().'/fees/invoices') }}/${payId}/pay-offline`" class="space-y-3">
                @csrf
                <div><label class="form-label">Method</label>
                    <select name="payment_method" class="form-input">
                        <option value="cash">Cash</option>
                        <option value="cheque">Cheque</option>
                    </select></div>
                <div><label class="form-label">Reference (optional)</label>
                    <input name="reference_number" class="form-input" placeholder="Cheque no / receipt no"></div>
                <div><label class="form-label">Remarks (optional)</label>
                    <input name="remarks" class="form-input"></div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showPay=false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancel</button>
                    <button class="btn btn-success">Mark Paid</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
