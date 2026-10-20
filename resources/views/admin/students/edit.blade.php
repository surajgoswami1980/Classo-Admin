@extends('layouts.app')
@section('title', 'Edit Student')
@section('page-title', 'Edit Student')
@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ panel_route('students.update', $student->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        @if($errors->any())
            <div class="p-4 rounded-lg bg-red-50 border border-red-200">
                <ul class="list-disc list-inside text-sm text-red-700">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="mc-card p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Personal Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">First Name *</label>
                    <input type="text" name="first_name" value="{{ old('first_name', explode(' ', $student->user_name ?? '')[0] ?? '') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name', implode(' ', array_slice(explode(' ', $student->user_name ?? ''), 1)) ?: '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $student->email ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $student->phone ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-input">
                        <option value="">Select</option>
                        <option value="male" {{ ($student->gender ?? '') === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ ($student->gender ?? '') === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ ($student->gender ?? '') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="mc-card p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Academic</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Class *</label>
                    <select name="class_id" required class="form-input">
                        @foreach($classes as $id => $name)
                            <option value="{{ $id }}" {{ $student->class_id == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Section *</label>
                    <select name="section_id" required class="form-input">
                        @foreach($sections as $sec)
                            <option value="{{ $sec->id }}" {{ $student->section_id == $sec->id ? 'selected' : '' }}>{{ $sec->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Roll Number</label>
                    <input type="text" name="roll_number" value="{{ old('roll_number', $student->roll_number ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="form-label">Admission Number</label>
                    <input type="text" name="admission_number" value="{{ old('admission_number', $student->admission_number ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-input">
                        <option value="active" {{ ($student->status ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ ($student->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="transferred" {{ ($student->status ?? '') === 'transferred' ? 'selected' : '' }}>Transferred</option>
                        <option value="graduated" {{ ($student->status ?? '') === 'graduated' ? 'selected' : '' }}>Graduated</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="mc-card p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Parent / Guardian</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Father's Name</label>
                    <input type="text" name="father_name" value="{{ old('father_name', $student->father_name ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="form-label">Father's Phone</label>
                    <input type="text" name="father_phone" value="{{ old('father_phone', $student->father_phone ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="form-label">Mother's Name</label>
                    <input type="text" name="mother_name" value="{{ old('mother_name', $student->mother_name ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="form-label">Address</label>
                    <input type="text" name="address" value="{{ old('address', $student->address ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ panel_route('students.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Student</button>
        </div>
    </form>
</div>
@endsection
