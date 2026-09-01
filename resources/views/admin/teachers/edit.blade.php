@extends('layouts.app')

@section('title', 'Edit Teacher')
@section('page-title', 'Edit Teacher')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Edit Teacher</h2>
            <p class="text-sm text-gray-500">Update information for {{ $teacher->user_name ?? '' }}</p>
        </div>
        <a href="{{ panel_route('teachers.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
            ← Back to List
        </a>
    </div>

    <!-- Form -->
    <form action="{{ panel_route('teachers.update', $teacher->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Personal Information -->
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Personal Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $teacher->user_name) }}" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $teacher->email) }}" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone <span class="text-red-500">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone', $teacher->phone) }}" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                    <input type="date" name="dob" value="{{ old('dob', $teacher->dob) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('dob') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                    <select name="gender" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">Select Gender</option>
                        <option value="male" {{ old('gender', $teacher->gender) == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $teacher->gender) == 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender', $teacher->gender) == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('gender') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Qualification</label>
                    <input type="text" name="qualification" value="{{ old('qualification', $teacher->qualification) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('qualification') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Professional Information -->
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Professional Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Employee ID <span class="text-red-500">*</span></label>
                    <input type="text" name="employee_id" value="{{ old('employee_id', $teacher->employee_id) }}" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('employee_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Designation <span class="text-red-500">*</span></label>
                    <select name="designation" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">Select Designation</option>
                        <option value="Principal" {{ old('designation', $teacher->designation) == 'Principal' ? 'selected' : '' }}>Principal</option>
                        <option value="Vice Principal" {{ old('designation', $teacher->designation) == 'Vice Principal' ? 'selected' : '' }}>Vice Principal</option>
                        <option value="Head of Department" {{ old('designation', $teacher->designation) == 'Head of Department' ? 'selected' : '' }}>Head of Department</option>
                        <option value="Senior Teacher" {{ old('designation', $teacher->designation) == 'Senior Teacher' ? 'selected' : '' }}>Senior Teacher</option>
                        <option value="Teacher" {{ old('designation', $teacher->designation) == 'Teacher' ? 'selected' : '' }}>Teacher</option>
                        <option value="Assistant Teacher" {{ old('designation', $teacher->designation) == 'Assistant Teacher' ? 'selected' : '' }}>Assistant Teacher</option>
                    </select>
                    @error('designation') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Department <span class="text-red-500">*</span></label>
                    <select name="department" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">Select Department</option>
                        <option value="mathematics" {{ old('department', $teacher->department) == 'mathematics' ? 'selected' : '' }}>Mathematics</option>
                        <option value="science" {{ old('department', $teacher->department) == 'science' ? 'selected' : '' }}>Science</option>
                        <option value="english" {{ old('department', $teacher->department) == 'english' ? 'selected' : '' }}>English</option>
                        <option value="hindi" {{ old('department', $teacher->department) == 'hindi' ? 'selected' : '' }}>Hindi</option>
                        <option value="social_science" {{ old('department', $teacher->department) == 'social_science' ? 'selected' : '' }}>Social Science</option>
                        <option value="computer" {{ old('department', $teacher->department) == 'computer' ? 'selected' : '' }}>Computer Science</option>
                        <option value="physical_education" {{ old('department', $teacher->department) == 'physical_education' ? 'selected' : '' }}>Physical Education</option>
                        <option value="arts" {{ old('department', $teacher->department) == 'arts' ? 'selected' : '' }}>Arts</option>
                    </select>
                    @error('department') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Joining Date</label>
                    <input type="date" name="joining_date" value="{{ old('joining_date', $teacher->joining_date) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('joining_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Salary</label>
                    <input type="number" name="salary" value="{{ old('salary', $teacher->salary) }}" placeholder="₹"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('salary') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Experience (Years)</label>
                    <input type="number" name="experience" value="{{ old('experience', $teacher->experience) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('experience') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Address Information -->
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Address Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <input type="text" name="address" value="{{ old('address', $teacher->address) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('address') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                    <input type="text" name="city" value="{{ old('city', $teacher->city) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('city') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                    <input type="text" name="state" value="{{ old('state', $teacher->state) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @error('state') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ panel_route('teachers.index') }}" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                Update Teacher
            </button>
        </div>
    </form>
</div>
@endsection
