@extends('layouts.app')

@section('title', 'Exams')
@section('page-title', 'Exams')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Examinations</h2>
            <p class="text-sm text-gray-500">Manage exams, schedules, and results</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="#exam-list" onclick="event.preventDefault(); mcToast('Pick an exam from the list below to enter marks.', 'info'); document.getElementById('exam-list')?.scrollIntoView({ behavior: 'smooth' })" class="btn btn-secondary">
                Enter Marks
            </a>
            <a href="#exam-list" onclick="event.preventDefault(); mcToast('Pick an exam from the list below to view results.', 'info'); document.getElementById('exam-list')?.scrollIntoView({ behavior: 'smooth' })" class="btn btn-secondary">
                View Results
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="mc-card p-4">
        <form method="GET" action="{{ panel_route('exams.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by exam name..."
                    class="form-input">
            </div>
            <div>
                <select name="class" class="form-input">
                    <option value="">All Classes</option>
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ request('class') == $i ? 'selected' : '' }}>Class {{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <select name="status" class="form-input">
                    <option value="">All Status</option>
                    <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                    <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary w-full">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div id="exam-list" class="mc-card overflow-hidden scroll-mt-24">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Exam Name</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Class</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Start Date</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">End Date</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Subjects</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Status</th>
                        <th class="px-6 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($exams ?? [] as $exam)
                        <tr class="hover:bg-primary-50/40 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $exam->name ?? '' }}</td>
                            <td class="px-6 py-4 text-gray-700">Class {{ $exam->class ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $exam->start_date ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $exam->end_date ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $exam->subjects_count ?? 0 }}</td>
                            <td class="px-6 py-4">
                                @if(($exam->status ?? '') == 'upcoming')
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700">Upcoming</span>
                                @elseif(($exam->status ?? '') == 'ongoing')
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-700">Ongoing</span>
                                @elseif(($exam->status ?? '') == 'completed')
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Completed</span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">{{ ucfirst($exam->status ?? 'N/A') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ panel_route('exams.marks', ['exam' => $exam->id]) }}" class="px-2 py-1 text-xs font-medium text-blue-700 bg-blue-50 rounded hover:bg-blue-100 transition">Marks</a>
                                    <a href="{{ panel_route('exams.results', ['exam' => $exam->id]) }}" class="px-2 py-1 text-xs font-medium text-green-700 bg-green-50 rounded hover:bg-green-100 transition">Results</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center text-gray-400">
                                    <svg class="w-12 h-12 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                    <p class="text-sm font-medium">No exams found</p>
                                    <p class="text-xs mt-1">Schedule an exam to get started</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($exams) && $exams->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $exams->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
