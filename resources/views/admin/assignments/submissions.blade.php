@extends('layouts.app')
@section('title', 'Grade Assignment')
@section('page-title', 'Grade Assignment')

@section('content')
<div class="space-y-6">
    <a href="{{ panel_route('assignments.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-gray-900">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
        Back to Assignments
    </a>

    {{-- Header card --}}
    <div class="bg-gradient-to-r from-primary-600 to-cyan-500 rounded-2xl p-6 text-white shadow-lg">
        <h2 class="text-xl font-bold">{{ $assignment->title }}</h2>
        @if($assignment->topic)<p class="text-white/85 text-sm mt-0.5">{{ $assignment->topic }}</p>@endif
        <div class="mt-3 flex flex-wrap gap-4 text-sm text-white/90">
            <span>{{ $assignment->class_name }} {{ $assignment->section_name }}</span>
            <span>·</span>
            <span>{{ $assignment->subject_name }}</span>
            <span>·</span>
            <span>Due {{ \Carbon\Carbon::parse($assignment->due_date)->format('d M Y') }}</span>
            <span>·</span>
            <span>Max marks: {{ $assignment->max_marks }}</span>
        </div>
    </div>

    <form method="POST" action="{{ panel_route('assignments.grade', $assignment->id) }}">
        @csrf
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-5 py-3 text-left">Roll</th>
                        <th class="px-5 py-3 text-left">Student</th>
                        <th class="px-5 py-3 text-left">Submission</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-center">Marks / {{ $assignment->max_marks }}</th>
                        <th class="px-5 py-3 text-left">Feedback</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($rows as $r)
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-5 py-4 text-gray-500">{{ $r->roll_number ?: '—' }}</td>
                            <td class="px-5 py-4 font-medium text-gray-900">{{ $r->student_name }}</td>
                            <td class="px-5 py-4">
                                @if($r->description)
                                    <p class="text-gray-600 line-clamp-2 max-w-xs">{{ $r->description }}</p>
                                @endif
                                @if($r->attachment_url)
                                    <a href="{{ \Illuminate\Support\Str::startsWith($r->attachment_url,'http') ? $r->attachment_url : asset('storage/'.$r->attachment_url) }}" target="_blank"
                                       class="inline-flex items-center gap-1 mt-1 text-xs font-semibold text-primary-600 hover:underline">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" /></svg>
                                        View {{ strtoupper($r->attachment_type ?: 'file') }}
                                    </a>
                                @endif
                                @if(!$r->description && !$r->attachment_url)
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                @php
                                    $map = ['submitted'=>'bg-blue-100 text-blue-700','graded'=>'bg-emerald-100 text-emerald-700','missed'=>'bg-rose-100 text-rose-700','pending'=>'bg-gray-100 text-gray-500'];
                                    $st = $r->status ?: 'pending';
                                @endphp
                                <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full {{ $map[$st] ?? 'bg-gray-100 text-gray-500' }}">{{ ucfirst($st) }}</span>
                                @if($r->is_late)<span class="block mt-1 text-[10px] text-amber-600 font-semibold">Late</span>@endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                <input type="number" name="marks[{{ $r->student_id }}]" value="{{ $r->marks }}" min="0" max="{{ $assignment->max_marks }}" step="0.5"
                                       class="w-20 text-center border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                            </td>
                            <td class="px-5 py-4">
                                <input type="text" name="feedback[{{ $r->student_id }}]" value="{{ $r->feedback }}" placeholder="Optional"
                                       class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">No students in this class/section.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($rows->count())
                <div class="px-5 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white text-sm font-semibold rounded-lg hover:bg-primary-700 transition active:scale-95">Save Marks</button>
                </div>
            @endif
        </div>
    </form>
</div>
@endsection
