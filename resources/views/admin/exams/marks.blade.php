@extends('layouts.app')

@section('title', 'Enter Marks')
@section('page-title', 'Enter Marks')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Marks Entry</h2>
            <p class="text-sm text-gray-500">Enter and manage student exam marks</p>
        </div>
        <a href="{{ panel_route('exams.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
            ← Back to Exams
        </a>
    </div>

    <!-- Selection Form -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-4">Select Exam & Subject</h3>
        <form method="GET" action="{{ panel_route('exams.marks', ['exam' => $exam->id ?? request('exam_id', 0)]) }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Exam</label>
                <select name="exam_id" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Select Exam</option>
                    @foreach($exams ?? [] as $examItem)
                        <option value="{{ $examItem->id }}" {{ request('exam_id') == $examItem->id ? 'selected' : '' }}>{{ $examItem->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Class</label>
                <select name="class" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Select Class</option>
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ request('class') == $i ? 'selected' : '' }}>Class {{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Section</label>
                <select name="section" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Select Section</option>
                    @foreach(['A', 'B', 'C', 'D', 'E'] as $section)
                        <option value="{{ $section }}" {{ request('section') == $section ? 'selected' : '' }}>{{ $section }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Subject</label>
                <select name="subject" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Select Subject</option>
                    <option value="mathematics" {{ request('subject') == 'mathematics' ? 'selected' : '' }}>Mathematics</option>
                    <option value="science" {{ request('subject') == 'science' ? 'selected' : '' }}>Science</option>
                    <option value="english" {{ request('subject') == 'english' ? 'selected' : '' }}>English</option>
                    <option value="hindi" {{ request('subject') == 'hindi' ? 'selected' : '' }}>Hindi</option>
                    <option value="social_science" {{ request('subject') == 'social_science' ? 'selected' : '' }}>Social Science</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2.5 bg-gray-800 text-white rounded-lg text-sm font-medium hover:bg-gray-900 transition">
                    Load Students
                </button>
            </div>
        </form>
    </div>

    <!-- Marks Entry Table -->
    @if(isset($exam) && isset($students))
        <form action="{{ panel_route('exams.marks.store', ['exam' => $exam->id]) }}" method="POST">
            @csrf
            <input type="hidden" name="exam_id" value="{{ $exam->id }}">
            <input type="hidden" name="subject" value="{{ request('subject') }}">

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">{{ $exam->name ?? 'Exam' }} - {{ ucfirst(request('subject', 'Subject')) }}</h3>
                        <p class="text-xs text-gray-500">Max Marks: {{ $exam->max_marks ?? 100 }}</p>
                    </div>
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                        Save Marks
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Roll No</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Student Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Marks Obtained</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Grade</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($students ?? [] as $student)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-gray-700">{{ $student->roll_no ?? '-' }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $student->first_name ?? '' }} {{ $student->last_name ?? '' }}</td>
                                    <td class="px-6 py-4">
                                        <input type="number" name="marks[{{ $student->id }}]" value="{{ $student->marks ?? '' }}"
                                            min="0" max="{{ $exam->max_marks ?? 100 }}" placeholder="0"
                                            class="w-24 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                    </td>
                                    <td class="px-6 py-4">
                                        <select name="grade[{{ $student->id }}]" class="w-20 border border-gray-300 rounded-lg px-2 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                            <option value="">-</option>
                                            <option value="A+" {{ ($student->grade ?? '') == 'A+' ? 'selected' : '' }}>A+</option>
                                            <option value="A" {{ ($student->grade ?? '') == 'A' ? 'selected' : '' }}>A</option>
                                            <option value="B+" {{ ($student->grade ?? '') == 'B+' ? 'selected' : '' }}>B+</option>
                                            <option value="B" {{ ($student->grade ?? '') == 'B' ? 'selected' : '' }}>B</option>
                                            <option value="C" {{ ($student->grade ?? '') == 'C' ? 'selected' : '' }}>C</option>
                                            <option value="D" {{ ($student->grade ?? '') == 'D' ? 'selected' : '' }}>D</option>
                                            <option value="F" {{ ($student->grade ?? '') == 'F' ? 'selected' : '' }}>F</option>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="text" name="remarks[{{ $student->id }}]" value="{{ $student->remarks ?? '' }}" placeholder="Optional"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center text-gray-400">
                                            <p class="text-sm font-medium">No students found</p>
                                            <p class="text-xs mt-1">Select exam, class, section, and subject to load students</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    @else
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="flex flex-col items-center text-gray-400">
                <svg class="w-16 h-16 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                <p class="text-sm font-medium text-gray-500">Select an exam and subject to start entering marks</p>
            </div>
        </div>
    @endif
</div>
@endsection
