@extends('layouts.app')

@section('title', 'Platform Dashboard')
@section('page-title', 'Platform Overview')

@section('content')
<div>
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <!-- Total Schools -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Total Schools</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total_schools'] ?? 0 }}</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-blue-100">
                    <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                    </svg>
                </div>
            </div>
            <p class="text-xs text-green-600 mt-2 font-medium">+{{ $stats['new_schools_this_month'] ?? 0 }} this month</p>
        </div>

        <!-- Active Subscriptions -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Active Subscriptions</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['active_subscriptions'] ?? 0 }}</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-green-100">
                    <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Students -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Total Students</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_students_platform'] ?? 0) }}</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-purple-100">
                    <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Monthly Revenue</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">₹{{ number_format($stats['monthly_revenue'] ?? 0) }}</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-amber-100">
                    <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 8.25H9m6 3H9m3 6-3-3h1.5a3 3 0 1 0 0-6M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions + Recent Schools -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Quick Actions -->
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="space-y-3">
                <a href="{{ route('admin.schools.create') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:bg-blue-50 hover:border-blue-200 transition group">
                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-blue-100 group-hover:bg-blue-200">
                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700">Onboard New School</span>
                </a>
                <a href="{{ route('admin.schools.index') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:bg-gray-50 transition group">
                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-gray-100">
                        <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Manage Schools</span>
                </a>
                <a href="{{ route('admin.subscriptions.index') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:bg-gray-50 transition group">
                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-gray-100">
                        <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Subscription Plans</span>
                </a>
                <a href="{{ route('admin.revenue') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:bg-gray-50 transition group">
                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-gray-100">
                        <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Revenue Dashboard</span>
                </a>
            </div>
        </div>

        <!-- Recent Schools -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-gray-900">Recent Schools</h3>
                <a href="{{ route('admin.schools.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View All →</a>
            </div>

            @if(isset($recentSchools) && count($recentSchools) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-xs text-gray-500 uppercase border-b">
                            <tr>
                                <th class="pb-3 text-left font-medium">School</th>
                                <th class="pb-3 text-left font-medium">Code</th>
                                <th class="pb-3 text-center font-medium">Status</th>
                                <th class="pb-3 text-right font-medium">Joined</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($recentSchools as $school)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-xs font-bold text-blue-700">
                                            {{ strtoupper(substr($school->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $school->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $school->city ?? '' }}, {{ $school->state ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">{{ $school->code }}</span>
                                </td>
                                <td class="py-3 text-center">
                                    @if($school->subscription_status === 'active')
                                        <span class="text-xs font-medium text-green-700 bg-green-50 px-2 py-1 rounded-full">Active</span>
                                    @elseif($school->subscription_status === 'trial')
                                        <span class="text-xs font-medium text-blue-700 bg-blue-50 px-2 py-1 rounded-full">Trial</span>
                                    @else
                                        <span class="text-xs font-medium text-red-700 bg-red-50 px-2 py-1 rounded-full">{{ ucfirst($school->subscription_status) }}</span>
                                    @endif
                                </td>
                                <td class="py-3 text-right text-gray-500">{{ $school->created_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-400">
                    <p>No schools onboarded yet</p>
                    <a href="{{ route('admin.schools.create') }}" class="text-sm text-blue-600 font-medium mt-2 inline-block">+ Onboard first school</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
