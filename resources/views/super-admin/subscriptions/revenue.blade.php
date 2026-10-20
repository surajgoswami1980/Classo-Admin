@extends('layouts.app')
@section('title', 'Revenue')
@section('page-title', 'Revenue Dashboard')
@section('content')
<div class="space-y-6">

    {{-- Filters --}}
    <form method="GET" class="mc-card p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="form-label">From</label>
            <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="form-input">
        </div>
        <div>
            <label class="form-label">To</label>
            <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="form-input">
        </div>
        <button type="submit" class="btn btn-primary">Apply</button>
        <a href="{{ route('admin.revenue.export', $filters) }}" class="btn btn-secondary">Export CSV</a>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="mc-card p-5">
            <p class="text-sm text-gray-500">Total Revenue</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">₹{{ number_format($stats['total_revenue'] ?? 0) }}</p>
        </div>
        <div class="mc-card p-5">
            <p class="text-sm text-gray-500">This Month</p>
            <p class="text-2xl font-bold text-green-600 mt-1">₹{{ number_format($stats['monthly_revenue'] ?? 0) }}</p>
        </div>
        <div class="mc-card p-5">
            <p class="text-sm text-gray-500">Commission Earned</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">₹{{ number_format($stats['commission'] ?? 0) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Revenue by plan --}}
        <div class="mc-card p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Revenue by Plan</h3>
            @if(!empty($revenueByPlan))
                <div class="space-y-3">
                    @php $maxPlan = max(array_map('floatval', $revenueByPlan)) ?: 1; @endphp
                    @foreach($revenueByPlan as $plan => $total)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="capitalize text-gray-700">{{ $plan ?? 'unassigned' }}</span>
                                <span class="font-medium text-gray-900">₹{{ number_format($total) }}</span>
                            </div>
                            <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-500 rounded-full" style="width: {{ round(($total / $maxPlan) * 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-400 text-sm text-center py-8">No revenue recorded for this period.</p>
            @endif
        </div>

        {{-- Monthly trend --}}
        <div class="mc-card p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Monthly Trend</h3>
            @if(!empty($monthlyTrend))
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-100">
                        @foreach($monthlyTrend as $month => $total)
                            <tr>
                                <td class="py-2 text-gray-600">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y') }}</td>
                                <td class="py-2 text-right font-medium text-gray-900">₹{{ number_format($total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-gray-400 text-sm text-center py-8">No trend data available.</p>
            @endif
        </div>
    </div>
</div>
@endsection
