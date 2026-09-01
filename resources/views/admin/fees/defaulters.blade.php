@extends('layouts.app')

@section('title', 'Fee Defaulters')
@section('page-title', 'Fee Defaulters')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Fee Defaulters</h2>
            <p class="text-sm text-gray-500">Students with overdue fee payments</p>
        </div>
        <a href="{{ panel_route('fees.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
            ← Back to Fee Management
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" action="{{ panel_route('fees.defaulters') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by student name..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <select name="class" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">All Classes</option>
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ request('class') == $i ? 'selected' : '' }}>Class {{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <select name="overdue_days" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">All Overdue</option>
                    <option value="30" {{ request('overdue_days') == '30' ? 'selected' : '' }}>30+ Days</option>
                    <option value="60" {{ request('overdue_days') == '60' ? 'selected' : '' }}>60+ Days</option>
                    <option value="90" {{ request('overdue_days') == '90' ? 'selected' : '' }}>90+ Days</option>
                </select>
            </div>
            <div>
                <button type="submit" class="w-full px-4 py-2.5 bg-gray-800 text-white rounded-lg text-sm font-medium hover:bg-gray-900 transition">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Class</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Due</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Overdue Since</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Days Overdue</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Father's Phone</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($defaulters ?? [] as $defaulter)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center text-red-700 font-semibold text-xs">
                                        {{ strtoupper(substr($defaulter->student->first_name ?? '', 0, 1)) }}{{ strtoupper(substr($defaulter->student->last_name ?? '', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $defaulter->student->first_name ?? '' }} {{ $defaulter->student->last_name ?? '' }}</p>
                                        <p class="text-xs text-gray-500">{{ $defaulter->student->admission_no ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-700">{{ $defaulter->student->class ?? '-' }} - {{ $defaulter->student->section ?? '' }}</td>
                            <td class="px-6 py-4 text-red-600 font-semibold">₹{{ number_format($defaulter->total_due ?? 0) }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $defaulter->overdue_since ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">
                                    {{ $defaulter->days_overdue ?? 0 }} days
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-700">{{ $defaulter->student->father_phone ?? '-' }}</td>
                            <td class="px-6 py-4 text-right">
                                <button class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Send Reminder">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center text-gray-400">
                                    <svg class="w-12 h-12 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    <p class="text-sm font-medium">No defaulters found</p>
                                    <p class="text-xs mt-1">All fees are up to date</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($defaulters) && method_exists($defaulters, 'hasPages') && $defaulters->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $defaulters->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
