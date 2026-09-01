@extends('layouts.app')
@section('title', 'Settings')
@section('page-title', 'Settings')
@section('content')
<div class="max-w-2xl space-y-6">

    @if($school)
    <!-- School Settings (only for school-admin) -->
    <div class="bg-white rounded-xl border p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-4">School Information</h3>
        <form method="POST" action="{{ panel_route('settings.update') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">School Name</label>
                    <input type="text" name="school_name" value="{{ old('school_name', $school->name) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">School Code</label>
                    <input type="text" value="{{ $school->code }}" disabled class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-gray-50 text-gray-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
                    <input type="email" name="school_email" value="{{ old('school_email', $school->email) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Phone</label>
                    <input type="text" name="school_phone" value="{{ old('school_phone', $school->phone) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">Save Settings</button>
            </div>
        </form>
    </div>
    @endif

    <!-- Subscription Info -->
    <div class="bg-white rounded-xl border p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-3">Subscription</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-500">Plan:</span> <span class="font-medium">{{ ucfirst($school->subscription_plan ?? 'N/A') }}</span></div>
            <div><span class="text-gray-500">Status:</span>
                @if($school?->is_active)
                    <span class="text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded-full">Active</span>
                @else
                    <span class="text-xs font-medium text-red-700 bg-red-50 px-2 py-0.5 rounded-full">Inactive</span>
                @endif
            </div>
            <div><span class="text-gray-500">Starts:</span> <span class="font-medium">{{ $school?->subscription_start?->format('d M Y') ?? '—' }}</span></div>
            <div><span class="text-gray-500">Expires:</span> <span class="font-medium">{{ $school?->subscription_end?->format('d M Y') ?? '—' }}</span></div>
            <div><span class="text-gray-500">Max Students:</span> <span class="font-medium">{{ $school?->max_students ?? '—' }}</span></div>
            <div><span class="text-gray-500">Board:</span> <span class="font-medium">{{ $school?->board_affiliation ?? '—' }}</span></div>
        </div>
    </div>
</div>
@endsection
