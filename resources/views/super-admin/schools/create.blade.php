@extends('layouts.app')

@section('title', 'Onboard New School')
@section('page-title', 'Onboard New School')

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('admin.schools.store') }}" class="space-y-6">
        @csrf

        @if($errors->any())
            <div class="p-4 rounded-lg bg-red-50 border border-red-200">
                <p class="text-sm font-medium text-red-800 mb-1">Please fix the following errors:</p>
                <ul class="list-disc list-inside text-sm text-red-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- School Info -->
        <div class="mc-card p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">School Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="form-label">School Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input" placeholder="Delhi Public School">
                </div>
                <div>
                    <label class="form-label">School Code * <span class="text-xs text-gray-400">(unique)</span></label>
                    <input type="text" name="code" value="{{ old('code') }}" required class="form-input font-mono uppercase" placeholder="DPSRN01">
                </div>
                <div>
                    <label class="form-label">Board Affiliation *</label>
                    <select name="board_affiliation" required class="form-input">
                        <option value="CBSE">CBSE</option>
                        <option value="ICSE">ICSE</option>
                        <option value="STATE">State Board</option>
                        <option value="UNIVERSITY">University</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Contact Email</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Contact Phone</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" class="form-input">
                </div>
                <div class="md:col-span-2">
                    <label class="form-label">Address</label>
                    <textarea name="address" rows="2" class="form-input">{{ old('address') }}</textarea>
                </div>
                <div>
                    <label class="form-label">City</label>
                    <input type="text" name="city" value="{{ old('city') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">State</label>
                    <input type="text" name="state" value="{{ old('state') }}" class="form-input">
                </div>
            </div>
        </div>

        <!-- Subscription -->
        <div class="mc-card p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Subscription</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Plan</label>
                    <select name="subscription_status" class="form-input">
                        <option value="trial">Trial (30 days)</option>
                        <option value="active">Active</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Max Students</label>
                    <input type="number" name="max_students" value="300" class="form-input">
                </div>
            </div>
        </div>

        <!-- Admin Credentials -->
        <div class="mc-card p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">School Admin Account</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Admin Name *</label>
                    <input type="text" name="admin_name" value="{{ old('admin_name') }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Admin Email *</label>
                    <input type="email" name="admin_email" value="{{ old('admin_email') }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Admin Phone</label>
                    <input type="text" name="admin_phone" value="{{ old('admin_phone') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Password *</label>
                    <input type="password" name="admin_password" required class="form-input" placeholder="Min 8 characters">
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.schools.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Onboard School</button>
        </div>
    </form>
</div>
@endsection
