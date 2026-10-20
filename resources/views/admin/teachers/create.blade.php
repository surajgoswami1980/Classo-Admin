@extends('layouts.app')

@section('title', 'Add Teacher')
@section('page-title', 'Add Teacher')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Add New Teacher</h2>
            <p class="text-sm text-gray-500">Fill in the details to add a new teacher</p>
        </div>
        <a href="{{ panel_route('teachers.index') }}" class="btn btn-secondary">
            ← Back to List
        </a>
    </div>

    <!-- Form -->
    <form action="{{ panel_route('teachers.store') }}" method="POST" class="space-y-6">
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
                    <label class="form-label">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="form-input">
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="form-input">
                    @error('email') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Phone <span class="text-red-500">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required
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
                    <label class="form-label">Qualification</label>
                    <input type="text" name="qualification" value="{{ old('qualification') }}"
                        class="form-input">
                    @error('qualification') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Professional Information -->
        <div class="mc-card p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Professional Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Employee ID <span class="text-red-500">*</span></label>
                    <input type="text" name="employee_id" value="{{ old('employee_id') }}" required
                        class="form-input">
                    @error('employee_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Designation <span class="text-red-500">*</span></label>
                    <select name="designation" required class="form-input">
                        <option value="">Select Designation</option>
                        <option value="Principal" {{ old('designation') == 'Principal' ? 'selected' : '' }}>Principal</option>
                        <option value="Vice Principal" {{ old('designation') == 'Vice Principal' ? 'selected' : '' }}>Vice Principal</option>
                        <option value="Head of Department" {{ old('designation') == 'Head of Department' ? 'selected' : '' }}>Head of Department</option>
                        <option value="Senior Teacher" {{ old('designation') == 'Senior Teacher' ? 'selected' : '' }}>Senior Teacher</option>
                        <option value="Teacher" {{ old('designation') == 'Teacher' ? 'selected' : '' }}>Teacher</option>
                        <option value="Assistant Teacher" {{ old('designation') == 'Assistant Teacher' ? 'selected' : '' }}>Assistant Teacher</option>
                    </select>
                    @error('designation') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Department <span class="text-red-500">*</span></label>
                    <select name="department" required class="form-input">
                        <option value="">Select Department</option>
                        <option value="mathematics" {{ old('department') == 'mathematics' ? 'selected' : '' }}>Mathematics</option>
                        <option value="science" {{ old('department') == 'science' ? 'selected' : '' }}>Science</option>
                        <option value="english" {{ old('department') == 'english' ? 'selected' : '' }}>English</option>
                        <option value="hindi" {{ old('department') == 'hindi' ? 'selected' : '' }}>Hindi</option>
                        <option value="social_science" {{ old('department') == 'social_science' ? 'selected' : '' }}>Social Science</option>
                        <option value="computer" {{ old('department') == 'computer' ? 'selected' : '' }}>Computer Science</option>
                        <option value="physical_education" {{ old('department') == 'physical_education' ? 'selected' : '' }}>Physical Education</option>
                        <option value="arts" {{ old('department') == 'arts' ? 'selected' : '' }}>Arts</option>
                    </select>
                    @error('department') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Joining Date</label>
                    <input type="date" name="joining_date" value="{{ old('joining_date') }}"
                        class="form-input">
                    @error('joining_date') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Salary</label>
                    <input type="number" name="salary" value="{{ old('salary') }}" placeholder="₹"
                        class="form-input">
                    @error('salary') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Experience (Years)</label>
                    <input type="number" name="experience" value="{{ old('experience') }}"
                        class="form-input">
                    @error('experience') <p class="form-error">{{ $message }}</p> @enderror
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
            </div>
        </div>

        <!-- Class Assignment & Subjects -->
        <div x-data="teacherAssign()" class="mc-card p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-1">Class Assignment & Subjects</h3>
            <p class="text-sm text-gray-500 mb-4">Make this teacher a class incharge and assign the subjects they teach (across any level — Nursery, LKG, 1st, etc.).</p>

            {{-- Class Incharge --}}
            <div class="rounded-lg border border-gray-100 bg-gray-50/60 p-4 mb-5">
                <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-800 cursor-pointer">
                    <input type="checkbox" name="is_class_incharge" value="1" x-model="isIncharge" class="form-check">
                    Assign as Class Incharge
                </label>
                <div x-show="isIncharge" x-cloak x-transition class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="form-label">Incharge of Class</label>
                        <select name="incharge_class_id" x-model="inchargeClass" class="form-input">
                            <option value="">Select Class</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Section</label>
                        <select name="incharge_section_id" :disabled="!inchargeClass" class="form-input disabled:bg-gray-100">
                            <option value="">Select Section</option>
                            <template x-for="s in inchargeSections" :key="s.id">
                                <option :value="s.id" x-text="s.name"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Classes taught --}}
            <div class="mb-5">
                <label class="form-label">Classes this teacher handles</label>
                @if($classes->isEmpty())
                    <p class="text-sm text-gray-400">No classes defined yet. Add classes under Academics → Classes first.</p>
                @else
                    <div class="flex flex-wrap gap-2">
                        @foreach($classes as $c)
                            <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-700 cursor-pointer hover:bg-gray-50 has-[:checked]:bg-blue-50 has-[:checked]:border-blue-300 has-[:checked]:text-blue-700 transition">
                                <input type="checkbox" name="assigned_classes[]" value="{{ $c->id }}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                {{ $c->name }}
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Subjects (grouped by class) --}}
            <div>
                <label class="form-label">Subjects taught</label>
                @if($subjects->isEmpty())
                    <p class="text-sm text-gray-400">No subjects defined yet. Subjects are created per class; add them first to assign here.</p>
                @else
                    <div class="space-y-4 max-h-80 overflow-y-auto pr-1">
                        @foreach($subjects->groupBy('class_name') as $className => $classSubjects)
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-xs font-bold uppercase tracking-wide text-gray-500">{{ $className }}</span>
                                    <span class="h-px flex-1 bg-gray-100"></span>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($classSubjects as $subj)
                                        <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-700 cursor-pointer hover:bg-gray-50 has-[:checked]:bg-green-50 has-[:checked]:border-green-300 has-[:checked]:text-green-700 transition">
                                            <input type="checkbox" name="assigned_subjects[]" value="{{ $subj->id }}" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                            {{ $subj->name }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ panel_route('teachers.index') }}" class="btn btn-secondary">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                Add Teacher
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function teacherAssign() {
    return {
        isIncharge: {{ old('is_class_incharge') ? 'true' : 'false' }},
        inchargeClass: '{{ old('incharge_class_id') }}',
        allSections: @json($sections),
        get inchargeSections() {
            return this.allSections.filter(s => String(s.class_id) === String(this.inchargeClass));
        },
    };
}
</script>
@endpush
@endsection
