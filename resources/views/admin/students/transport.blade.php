@extends('layouts.app')

@section('title', 'Student Transport')
@section('page-title', 'Transport — ' . ($student->user_name ?? 'Student'))

@section('content')
<div class="space-y-5 max-w-2xl">
    <div class="bg-white rounded-xl border p-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $student->user_name }}</h2>
            <p class="text-sm text-gray-500">{{ $student->class_name }} - {{ $student->section_name }} · Roll {{ $student->roll_number ?? '—' }}</p>
        </div>
        <a href="{{ panel_route('students.index') }}" class="text-sm text-gray-600 hover:underline">&larr; Back to students</a>
    </div>

    @if($assignment)
    <div class="bg-white rounded-xl border overflow-hidden">
        <div class="bg-gradient-to-br from-teal-600 to-teal-700 p-5 text-white">
            <p class="text-teal-100 text-xs">Assigned Route</p>
            <h3 class="text-xl font-bold">{{ $assignment->route_name }}</h3>
            @if($assignment->session_name)<p class="text-teal-100 text-xs mt-1">Session: {{ $assignment->session_name }}</p>@endif
        </div>
        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-500">Stop</p>
                <p class="text-sm font-medium text-gray-900">{{ $assignment->stop_name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Pickup / Drop</p>
                <p class="text-sm font-medium text-gray-900">{{ $assignment->pickup_time ?? '—' }} / {{ $assignment->drop_time ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Vehicle</p>
                <p class="text-sm font-medium text-gray-900">{{ $assignment->vehicle_number ? $assignment->vehicle_number.' ('.$assignment->vehicle_type.')' : 'Not assigned' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Capacity</p>
                <p class="text-sm font-medium text-gray-900">{{ $assignment->capacity ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Driver</p>
                <p class="text-sm font-medium text-gray-900">{{ $assignment->driver_name ?? 'Not assigned' }} @if($assignment->driver_phone)<span class="text-xs text-gray-400">· {{ $assignment->driver_phone }}</span>@endif</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Conductor</p>
                <p class="text-sm font-medium text-gray-900">{{ $assignment->conductor_name ?? 'Not assigned' }} @if($assignment->conductor_phone)<span class="text-xs text-gray-400">· {{ $assignment->conductor_phone }}</span>@endif</p>
            </div>
        </div>
    </div>
    @else
    <div class="bg-white rounded-xl border p-10 text-center text-gray-400">
        This student is not assigned to any transport route.
        <div class="mt-3">
            <a href="{{ panel_route('transport.index') }}" class="text-blue-600 text-sm hover:underline">Go to Transport →</a>
        </div>
    </div>
    @endif
</div>
@endsection
