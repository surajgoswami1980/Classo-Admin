@extends('layouts.app')

@section('title', 'Exam Results')
@section('page-title', 'Exam Results')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Exam Results</h2>
            <p class="text-sm text-gray-500">View examination results and performance</p>
        </div>
        <a href="{{ panel_route('exams.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
            ← Back to Exams
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" action="{{ panel_route('exams.results', ['exam' => $exam->id ?? request('exam', 0)]) }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <select name="exam_id" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Select Exam</option>
                    @foreach($exams ?? [] as $examItem)
                        <option value="{{ $examItem->id }}" {{ request('exam_id') == $examItem->id ? 'selected' : '' }}>{{ $examItem->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="class" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">All Classes</option>
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ request('class') == $i ? 'selected' : '' }}>Class {{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <select name="section" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">All Sections</option>
                    @foreach(['A', 'B', 'C', 'D', 'E'] as $section)
                        <option value="{{ $section }}" {{ request('section') == $section ? 'selected' : '' }}>{{ $section }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search student..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <button type="submit" class="w-full px-4 py-2.5 bg-gray-800 text-white rounded-lg text-sm font-medium hover:bg-gray-900 transition">
                    View Results
                </button>
            </div>
        </form>
    </div>

    <!-- Results Table -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if(isset($exam))
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-sm font-semibold text-gray-900">{{ $exam->name ?? 'Exam Results' }}</h3>
                <p class="text-xs text-gray-500">Class {{ request('class', 'All') }} | Section {{ request('section', 'All') }}</p>
            </div>
        @endif
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Rank</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Roll No</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Student Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Marks</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Obtained</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Percentage</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Grade</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Result</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($results ?? [] as $index => $result)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $result->student->roll_no ?? '-' }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $result->student->first_name ?? '' }} {{ $result->student->last_name ?? '' }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $result->total_marks ?? 0 }}</td>
                            <td class="px-6 py-4 text-gray-900 font-semibold">{{ $result->obtained_marks ?? 0 }}</td>
                            <td class="px-6 py-4 text-gray-900 font-semibold">{{ $result->percentage ?? 0 }}%</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full
                                    @if(in_array($result->grade ?? '', ['A+', 'A'])) bg-green-100 text-green-700
                                    @elseif(in_array($result->grade ?? '', ['B+', 'B'])) bg-blue-100 text-blue-700
                                    @elseif(($result->grade ?? '') == 'C') bg-amber-100 text-amber-700
                                    @else bg-red-100 text-red-700
                                    @endif
                                ">{{ $result->grade ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if(($result->result ?? '') == 'pass')
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Pass</span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">Fail</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ panel_route('exams.report-card', ['exam' => request('exam_id'), 'student' => $result->student->id ?? 0]) }}" class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Report Card">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center text-gray-400">
                                    <svg class="w-12 h-12 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                    </svg>
                                    <p class="text-sm font-medium">No results found</p>
                                    <p class="text-xs mt-1">Select an exam and class to view results</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($results) && method_exists($results, 'hasPages') && $results->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $results->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
