@extends('layouts.app')
@section('title', 'Edit Subscription: ' . $school->name)
@section('page-title', 'Edit Subscription')
@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.subscriptions.update', $school) }}" class="space-y-6">
        @csrf
        @method('PUT')

        @if($errors->any())
            <div class="p-4 rounded-lg bg-red-50 border border-red-200">
                <ul class="list-disc list-inside text-sm text-red-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-xl border p-6">
            <div class="mb-4">
                <p class="text-base font-semibold text-gray-900">{{ $school->name }}</p>
                <p class="text-xs text-gray-400 font-mono">{{ $school->code }}</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Plan *</label>
                    <select name="subscription_plan" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        @foreach($plans as $key => $plan)
                            <option value="{{ $key }}" {{ $school->subscription_plan === $key ? 'selected' : '' }}>{{ $plan['label'] }} (₹{{ number_format($plan['price']) }}/mo)</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ $school->is_active ? 'checked' : '' }} class="rounded border-gray-300">
                        Active
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                    <input type="date" name="subscription_start" value="{{ old('subscription_start', optional($school->subscription_start)->toDateString()) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date *</label>
                    <input type="date" name="subscription_end" value="{{ old('subscription_end', optional($school->subscription_end)->toDateString()) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Students</label>
                    <input type="number" name="max_students" value="{{ old('max_students', $school->max_students) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Staff</label>
                    <input type="number" name="max_staff" value="{{ old('max_staff', $school->max_staff) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.subscriptions.index') }}" class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">Update Subscription</button>
        </div>
    </form>
</div>
@endsection
