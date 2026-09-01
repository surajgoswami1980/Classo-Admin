@extends('layouts.app')

@section('title', 'Collection Report')
@section('page-title', 'Collection Report')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Fee Collection Report</h2>
            <p class="text-sm text-gray-500">Analytics and reporting of fee collections</p>
        </div>
        <a href="{{ panel_route('fees.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
            ← Back to Fee Management
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" action="{{ panel_route('fees.collection-report') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">From Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">To Date</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Class</label>
                <select name="class" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">All Classes</option>
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ request('class') == $i ? 'selected' : '' }}>Class {{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2.5 bg-gray-800 text-white rounded-lg text-sm font-medium hover:bg-gray-900 transition">
                    Generate Report
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm font-medium text-gray-500">Total Collected</p>
            <p class="mt-1 text-2xl font-bold text-green-600">₹{{ number_format($report['total_collected'] ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm font-medium text-gray-500">Total Transactions</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($report['total_transactions'] ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm font-medium text-gray-500">Average Per Student</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">₹{{ number_format($report['average_per_student'] ?? 0) }}</p>
        </div>
    </div>

    <!-- Collection Table -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900">Collection Details</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Receipt No</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Class</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fee Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Payment Mode</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($report['collections'] ?? [] as $collection)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-gray-700">{{ $collection->date ?? '-' }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $collection->receipt_no ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-900">{{ $collection->student_name ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $collection->class ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $collection->fee_type ?? '-' }}</td>
                            <td class="px-6 py-4 text-green-600 font-semibold">₹{{ number_format($collection->amount ?? 0) }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">
                                    {{ ucfirst($collection->payment_mode ?? 'cash') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center text-gray-400">
                                    <svg class="w-12 h-12 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                    </svg>
                                    <p class="text-sm font-medium">No collection data found</p>
                                    <p class="text-xs mt-1">Select a date range to generate the report</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
