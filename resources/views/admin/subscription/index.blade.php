@extends('layouts.app')
@section('title', 'My Subscription')
@section('page-title', 'Subscription & Billing')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-gradient-to-r from-navy-800 to-primary-600 rounded-2xl p-6 text-white shadow-lg">
        <h2 class="text-xl font-bold">Your MyClasses Subscription</h2>
        <p class="text-white/85 text-sm mt-1">Pay your platform subscription invoices securely online.</p>
    </div>

    <div class="mc-card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50/80 border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                <tr>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Invoice</th>
                    <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Plan / Period</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Amount</th>
                    <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Status</th>
                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($invoices as $inv)
                    <tr class="hover:bg-primary-50/40 transition-colors">
                        <td class="px-5 py-4 font-mono text-xs text-gray-700">{{ $inv->invoice_number }}</td>
                        <td class="px-5 py-4 text-gray-500">{{ $inv->plan ?: '—' }} · {{ $inv->period ?: '—' }}</td>
                        <td class="px-5 py-4 text-right font-semibold text-gray-900">₹{{ number_format($inv->amount) }}</td>
                        <td class="px-5 py-4 text-center">
                            @php $m = ['paid'=>'bg-green-100 text-green-700','unpaid'=>'bg-amber-100 text-amber-700','cancelled'=>'bg-gray-100 text-gray-500']; @endphp
                            <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full {{ $m[$inv->status] }}">{{ ucfirst($inv->status) }}</span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            @if($inv->status === 'unpaid')
                                <button onclick="paySubscription({{ $inv->id }}, '{{ $inv->invoice_number }}')" class="px-4 py-2 bg-primary-600 text-white text-xs font-semibold rounded-lg hover:bg-primary-700 transition active:scale-95">Pay Now</button>
                            @elseif($inv->status === 'paid')
                                <span class="text-xs text-green-600 font-semibold">Paid {{ $inv->paid_at ? \Carbon\Carbon::parse($inv->paid_at)->format('d M Y') : '' }}</span>
                            @else
                                <span class="text-xs text-gray-300">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-12 text-center text-gray-400">No subscription invoices yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

async function paySubscription(id, number) {
    try {
        const res = await fetch(`{{ url(panel_prefix().'/subscription') }}/${id}/pay`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (!data.success) { alert(data.message || 'Could not start payment'); return; }

        const rzp = new Razorpay({
            key: data.key,
            amount: Math.round(data.amount * 100),
            currency: 'INR',
            name: 'MyClasses',
            description: 'Subscription ' + number,
            order_id: data.order_id,
            theme: { color: '#2563eb' },
            handler: async function (resp) {
                const vr = await fetch(`{{ url(panel_prefix().'/subscription') }}/${id}/verify`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: JSON.stringify(resp),
                });
                const vd = await vr.json();
                alert(vd.message || (vd.success ? 'Paid!' : 'Verification failed'));
                if (vd.success) location.reload();
            },
        });
        rzp.open();
    } catch (e) { alert('Payment error. Try again.'); }
}
</script>
@endpush
@endsection
