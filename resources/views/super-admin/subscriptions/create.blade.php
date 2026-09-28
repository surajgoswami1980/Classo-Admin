@extends('layouts.app')
@section('title', 'Assign Subscription')
@section('page-title', 'Assign Subscription')
@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.subscriptions.store') }}" class="space-y-6">
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

        <div class="bg-white rounded-xl border p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Subscription Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">School *</label>
                    <select name="school_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">Select a school</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>
                                {{ $school->name }} ({{ $school->code }}) — current: {{ $school->subscription_plan ?? 'none' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Plan *</label>
                    <select name="subscription_plan" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        @foreach($plans as $key => $plan)
                            <option value="{{ $key }}" {{ old('subscription_plan') === $key ? 'selected' : '' }}>{{ $plan['label'] }} (₹{{ number_format($plan['price']) }}/mo)</option>
                        @endforeach
                    </select>
                </div>
                <div></div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                    <input type="date" name="subscription_start" value="{{ old('subscription_start', now()->toDateString()) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date *</label>
                    <input type="date" name="subscription_end" value="{{ old('subscription_end', now()->addYear()->toDateString()) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Students <span class="text-xs text-gray-400">(optional)</span></label>
                    <input type="number" name="max_students" value="{{ old('max_students') }}" placeholder="Uses plan default" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Staff <span class="text-xs text-gray-400">(optional)</span></label>
                    <input type="number" name="max_staff" value="{{ old('max_staff') }}" placeholder="Uses plan default" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.subscriptions.index') }}" class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">Assign Subscription</button>
        </div>
    </form>
</div>
@endsection
