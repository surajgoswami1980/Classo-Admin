@extends('layouts.app')
@section('title', 'My Profile')
@section('page-title', 'My Profile')
@section('content')
<div class="max-w-2xl space-y-6">

    <!-- Profile Info -->
    <div class="mc-card p-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center text-2xl font-bold text-blue-700">
                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900">{{ $user->name }}</h2>
                <p class="text-sm text-gray-500">{{ $user->roles->first()?->name ?? 'User' }} · {{ $user->school?->name ?? 'Platform' }}</p>
            </div>
        </div>

        <form method="POST" action="{{ panel_route('profile.update') }}" class="space-y-4">
            @csrf @method('PUT')

            @if($errors->any() && !$errors->has('current_password'))
                <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="form-label">Role</label>
                    <input type="text" value="{{ $user->roles->first()?->name ?? '—' }}" disabled class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm bg-gray-50 text-gray-500">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>

    <!-- Change Password -->
    <div class="mc-card p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-4">Change Password</h3>
        <form method="POST" action="{{ panel_route('profile.password') }}" class="space-y-4">
            @csrf

            @if($errors->has('current_password'))
                <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">{{ $errors->first('current_password') }}</div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" required class="form-input">
                </div>
                <div>
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" required class="form-input">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary">Change Password</button>
            </div>
        </form>
    </div>

    <!-- Account Info -->
    <div class="mc-card p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-3">Account Info</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-500">User ID:</span> <span class="font-medium">{{ $user->id }}</span></div>
            <div><span class="text-gray-500">School ID:</span> <span class="font-medium">{{ $user->school_id ?? '—' }}</span></div>
            <div><span class="text-gray-500">Last Login:</span> <span class="font-medium">{{ $user->last_login_at?->format('d M Y, h:i A') ?? '—' }}</span></div>
            <div><span class="text-gray-500">Joined:</span> <span class="font-medium">{{ $user->created_at?->format('d M Y') ?? '—' }}</span></div>
        </div>
    </div>
</div>
@endsection
