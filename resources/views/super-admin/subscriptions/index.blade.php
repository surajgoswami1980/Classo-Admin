@extends('layouts.app')
@section('title', 'Subscriptions')
@section('page-title', 'Subscription Management')
@section('content')
<div class="space-y-6">

    {{-- Plan reference cards --}}
    <div class="bg-white rounded-xl border p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-gray-900">Plans</h3>
            <div class="flex gap-2">
                <a href="{{ route('admin.revenue') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Revenue</a>
                <a href="{{ route('admin.subscriptions.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Assign Subscription</a>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
            @foreach($plans as $key => $plan)
                <div class="border rounded-xl p-4">
                    <h4 class="font-semibold text-gray-900">{{ $plan['label'] }}</h4>
                    <p class="text-xl font-bold text-gray-900 mt-1">₹{{ number_format($plan['price']) }}<span class="text-xs font-normal text-gray-500">/mo</span></p>
                    <p class="text-xs text-gray-500 mt-1">Up to {{ number_format($plan['max_students']) }} students</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border p-4 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="School name or code" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Plan</label>
            <select name="plan" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="">All</option>
                @foreach($plans as $key => $plan)
                    <option value="{{ $key }}" {{ ($filters['plan'] ?? '') === $key ? 'selected' : '' }}>{{ $plan['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="">All</option>
                <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="expired" {{ ($filters['status'] ?? '') === 'expired' ? 'selected' : '' }}>Expired</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800">Filter</button>
    </form>

    {{-- Subscriptions table --}}
    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="text-left px-4 py-3 font-medium">School</th>
                    <th class="text-left px-4 py-3 font-medium">Plan</th>
                    <th class="text-left px-4 py-3 font-medium">Period</th>
                    <th class="text-left px-4 py-3 font-medium">Limits</th>
                    <th class="text-left px-4 py-3 font-medium">Status</th>
                    <th class="text-right px-4 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($subscriptions as $school)
                    @php
                        $expired = $school->subscription_end && \Carbon\Carbon::parse($school->subscription_end)->isPast();
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">{{ $school->name }}</p>
                            <p class="text-xs text-gray-400 font-mono">{{ $school->code }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-blue-50 text-blue-700 capitalize">{{ $school->subscription_plan ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">
                            @if($school->subscription_start)
                                {{ \Carbon\Carbon::parse($school->subscription_start)->format('d M Y') }}
                                — {{ $school->subscription_end ? \Carbon\Carbon::parse($school->subscription_end)->format('d M Y') : '—' }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">
                            {{ number_format($school->max_students ?? 0) }} students / {{ number_format($school->max_staff ?? 0) }} staff
                        </td>
                        <td class="px-4 py-3">
                            @if(!$school->is_active)
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500">Inactive</span>
                            @elseif($expired)
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-red-50 text-red-600">Expired</span>
                            @else
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-green-50 text-green-600">Active</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.subscriptions.show', $school) }}" class="text-blue-600 hover:underline text-xs">View</a>
                            <a href="{{ route('admin.subscriptions.edit', $school) }}" class="text-gray-600 hover:underline text-xs ml-3">Edit</a>
                            <form action="{{ route('admin.subscriptions.destroy', $school) }}" method="POST" class="inline ml-3" onsubmit="return confirm('Cancel this subscription? The school will be deactivated.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:underline text-xs">Cancel</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">No schools found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $subscriptions->links() }}</div>
</div>
@endsection
