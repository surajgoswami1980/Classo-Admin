@extends('layouts.app')
@section('title', 'Edit: ' . $school->name)
@section('page-title', 'Edit School')
@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('admin.schools.update', $school) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl border p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">School Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">School Name *</label>
                    <input type="text" name="name" value="{{ old('name', $school->name) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Board Affiliation *</label>
                    <select name="board_affiliation" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="CBSE" {{ $school->board_affiliation == 'CBSE' ? 'selected' : '' }}>CBSE</option>
                        <option value="ICSE" {{ $school->board_affiliation == 'ICSE' ? 'selected' : '' }}>ICSE</option>
                        <option value="STATE" {{ $school->board_affiliation == 'STATE' ? 'selected' : '' }}>State Board</option>
                        <option value="UNIVERSITY" {{ $school->board_affiliation == 'UNIVERSITY' ? 'selected' : '' }}>University</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Students</label>
                    <input type="number" name="max_students" value="{{ old('max_students', $school->max_students) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
                    <input type="email" name="email" value="{{ old('email', $school->email) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $school->phone) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea name="address" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">{{ old('address', $school->address) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                    <input type="text" name="city" value="{{ old('city', $school->city) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                    <input type="text" name="state" value="{{ old('state', $school->state) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subscription Plan</label>
                    <select name="subscription_plan" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="trial" {{ $school->subscription_plan == 'trial' ? 'selected' : '' }}>Trial</option>
                        <option value="basic" {{ $school->subscription_plan == 'basic' ? 'selected' : '' }}>Basic</option>
                        <option value="standard" {{ $school->subscription_plan == 'standard' ? 'selected' : '' }}>Standard</option>
                        <option value="premium" {{ $school->subscription_plan == 'premium' ? 'selected' : '' }}>Premium</option>
                        <option value="enterprise" {{ $school->subscription_plan == 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.schools.index') }}" class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">Update School</button>
        </div>
    </form>
</div>
@endsection
