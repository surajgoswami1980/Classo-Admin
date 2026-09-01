@extends('layouts.app')
@section('title', 'Student Details')
@section('page-title', 'Student Details')
@section('content')
<div class="max-w-4xl">
    <div class="flex items-center justify-between mb-6">
        <a href="{{ panel_route('students.index') }}" class="text-sm text-gray-500 hover:text-blue-600">← Back to list</a>
        <a href="{{ panel_route('students.edit', $student->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Edit Student</a>
    </div>

    <div class="bg-white rounded-xl border p-6 mb-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center text-xl font-bold text-blue-700">
                {{ strtoupper(substr($student->user_name ?? 'S', 0, 1)) }}
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $student->user_name ?? '—' }}</h2>
                <p class="text-sm text-gray-500">{{ $student->class_name ?? '' }} - {{ $student->section_name ?? '' }} | Roll: {{ $student->roll_number ?? '—' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div><p class="text-xs text-gray-500">Admission No</p><p class="text-sm font-medium">{{ $student->admission_number ?? '—' }}</p></div>
            <div><p class="text-xs text-gray-500">Roll Number</p><p class="text-sm font-medium">{{ $student->roll_number ?? '—' }}</p></div>
            <div><p class="text-xs text-gray-500">Email</p><p class="text-sm">{{ $student->email ?? '—' }}</p></div>
            <div><p class="text-xs text-gray-500">Phone</p><p class="text-sm">{{ $student->phone ?? '—' }}</p></div>
            <div><p class="text-xs text-gray-500">Gender</p><p class="text-sm">{{ ucfirst($student->gender ?? '—') }}</p></div>
            <div><p class="text-xs text-gray-500">DOB</p><p class="text-sm">{{ $student->date_of_birth ?? '—' }}</p></div>
            <div><p class="text-xs text-gray-500">Blood Group</p><p class="text-sm">{{ $student->blood_group ?? '—' }}</p></div>
            <div><p class="text-xs text-gray-500">Status</p><p class="text-sm font-medium {{ $student->status === 'active' ? 'text-green-600' : 'text-red-600' }}">{{ ucfirst($student->status ?? '') }}</p></div>
            <div><p class="text-xs text-gray-500">Admission Date</p><p class="text-sm">{{ $student->admission_date ?? '—' }}</p></div>
            <div><p class="text-xs text-gray-500">Father</p><p class="text-sm">{{ $student->father_name ?? '—' }} {{ $student->father_phone ? '('.$student->father_phone.')' : '' }}</p></div>
            <div><p class="text-xs text-gray-500">Mother</p><p class="text-sm">{{ $student->mother_name ?? '—' }}</p></div>
            <div><p class="text-xs text-gray-500">Address</p><p class="text-sm">{{ $student->address ?? '—' }}, {{ $student->city ?? '' }} {{ $student->state ?? '' }}</p></div>
        </div>
    </div>
</div>
@endsection
