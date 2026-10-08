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

    @if($school && auth()->user()->hasRole('school-admin'))
    @php
        $otpEnabled = ($school->settings['otp_login_enabled'] ?? false) === true;
        $otpChannels = $school->settings['otp_channels'] ?? ['email', 'mobile'];
    @endphp
    <!-- Login & OTP Settings -->
    <div class="bg-white rounded-xl border p-6" x-data="{ otp: {{ $otpEnabled ? 'true' : 'false' }} }">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-base font-semibold text-gray-900">Login with OTP</h3>
                <p class="text-sm text-gray-500 mt-0.5">Let your staff and students sign in with a one-time password sent to their email or mobile.</p>
            </div>
        </div>

        <form method="POST" action="{{ panel_route('settings.otp') }}" class="mt-4 space-y-4">
            @csrf

            <!-- Toggle -->
            <label class="flex items-center justify-between cursor-pointer rounded-lg border border-gray-200 px-4 py-3">
                <span class="text-sm font-medium text-gray-700">Enable OTP login for this school</span>
                <span class="relative inline-flex items-center">
                    <input type="checkbox" name="otp_login_enabled" value="1" x-model="otp" class="sr-only peer">
                    <span class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 transition-colors"></span>
                    <span class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full transition-transform peer-checked:translate-x-5"></span>
                </span>
            </label>

            <!-- Channels -->
            <div x-show="otp" x-cloak class="rounded-lg border border-gray-200 px-4 py-3 space-y-2">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Allowed channels</p>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="otp_channels[]" value="email" {{ in_array('email', $otpChannels) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Email OTP
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="otp_channels[]" value="mobile" {{ in_array('mobile', $otpChannels) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Mobile (SMS) OTP
                </label>
                <p class="text-xs text-gray-400">If none selected, both are allowed by default.</p>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">Save Login Settings</button>
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
