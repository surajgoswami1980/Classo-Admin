@extends('layouts.app')

@section('title', 'Add Student')
@section('page-title', 'Add Student')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Add New Student</h2>
            <p class="text-sm text-gray-500">Fill in the details to register a new student</p>
        </div>
        <a href="{{ panel_route('students.index') }}" class="btn btn-secondary">
            ← Back to List
        </a>
    </div>

    <!-- Form -->
    <form action="{{ panel_route('students.store') }}" method="POST" class="space-y-6">
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

        <!-- Personal Information -->
        <div class="mc-card p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Personal Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required
                        class="form-input">
                    @error('first_name') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" required
                        class="form-input">
                    @error('last_name') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="form-input">
                    @error('email') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                        class="form-input">
                    @error('phone') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="dob" value="{{ old('dob') }}"
                        class="form-input">
                    @error('dob') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-input">
                        <option value="">Select Gender</option>
                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('gender') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Blood Group</label>
                    <select name="blood_group" class="form-input">
                        <option value="">Select Blood Group</option>
                        @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                            <option value="{{ $bg }}" {{ old('blood_group') == $bg ? 'selected' : '' }}>{{ $bg }}</option>
                        @endforeach
                    </select>
                    @error('blood_group') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Academic Information -->
        <div class="mc-card p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Academic Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="form-label">Class <span class="text-red-500">*</span></label>
                    <select name="class_id" required class="form-input">
                        <option value="">Select Class</option>
                        @foreach($classes ?? [] as $id => $name)
                            <option value="{{ $id }}" {{ old('class_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('class_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Section <span class="text-red-500">*</span></label>
                    <select name="section_id" required class="form-input">
                        <option value="">Select Section</option>
                        @foreach($sections ?? [] as $section)
                            <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>{{ $section->name }} (Class {{ $section->class_id }})</option>
                        @endforeach
                    </select>
                    @error('section_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Roll No</label>
                    <input type="text" name="roll_number" value="{{ old('roll_number') }}"
                        class="form-input">
                    @error('roll_number') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Admission No <span class="text-red-500">*</span></label>
                    <input type="text" name="admission_no" value="{{ old('admission_no') }}" required
                        class="form-input">
                    @error('admission_no') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Parent/Guardian Information -->
        <div class="mc-card p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Parent/Guardian Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Father's Name</label>
                    <input type="text" name="father_name" value="{{ old('father_name') }}"
                        class="form-input">
                    @error('father_name') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Father's Phone</label>
                    <input type="text" name="father_phone" value="{{ old('father_phone') }}"
                        class="form-input">
                    @error('father_phone') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Mother's Name</label>
                    <input type="text" name="mother_name" value="{{ old('mother_name') }}"
                        class="form-input">
                    @error('mother_name') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Address Information -->
        <div class="mc-card p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Address Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="lg:col-span-2">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" value="{{ old('address') }}"
                        class="form-input">
                    @error('address') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">City</label>
                    <input type="text" name="city" value="{{ old('city') }}"
                        class="form-input">
                    @error('city') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">State</label>
                    <input type="text" name="state" value="{{ old('state') }}"
                        class="form-input">
                    @error('state') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Pincode</label>
                    <input type="text" name="pincode" value="{{ old('pincode') }}"
                        class="form-input">
                    @error('pincode') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ panel_route('students.index') }}" class="btn btn-secondary">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                Add Student
            </button>
        </div>
    </form>
</div>
@endsection
