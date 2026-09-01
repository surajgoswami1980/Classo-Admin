@extends('layouts.app')
@section('title', 'Subscriptions')
@section('page-title', 'Subscription Plans')
@section('content')
<div>
    <div class="bg-white rounded-xl border p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-4">Active Plans</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="border rounded-xl p-5 hover:border-blue-300 transition">
                <h4 class="font-semibold text-gray-900">Starter</h4>
                <p class="text-2xl font-bold text-gray-900 mt-2">₹4,999<span class="text-sm font-normal text-gray-500">/month</span></p>
                <p class="text-xs text-gray-500 mt-1">Up to 300 students</p>
            </div>
            <div class="border-2 border-blue-500 rounded-xl p-5 relative">
                <span class="absolute -top-2.5 left-4 bg-blue-600 text-white text-xs px-2 py-0.5 rounded-full">Popular</span>
                <h4 class="font-semibold text-gray-900">Growth</h4>
                <p class="text-2xl font-bold text-gray-900 mt-2">₹9,999<span class="text-sm font-normal text-gray-500">/month</span></p>
                <p class="text-xs text-gray-500 mt-1">Up to 1,000 students</p>
            </div>
            <div class="border rounded-xl p-5 hover:border-blue-300 transition">
                <h4 class="font-semibold text-gray-900">Enterprise</h4>
                <p class="text-2xl font-bold text-gray-900 mt-2">₹19,999<span class="text-sm font-normal text-gray-500">/month</span></p>
                <p class="text-xs text-gray-500 mt-1">Unlimited students</p>
            </div>
        </div>
    </div>
</div>
@endsection
