@extends('layouts.app')
@section('title', 'Assignments')
@section('page-title', 'Assignments')

@section('content')
<div x-data="assignmentPage()" class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Assignments</h2>
            <p class="text-sm text-gray-500">Create homework with a topic & attachment. Review submissions and award marks.</p>
        </div>
        <button @click="showCreate = true" class="px-4 py-2.5 bg-primary-600 text-white rounded-lg text-sm font-semibold hover:bg-primary-700 transition active:scale-95">+ New Assignment</button>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-5 py-3 text-left">Assignment</th>
                        <th class="px-5 py-3 text-left">Class</th>
                        <th class="px-5 py-3 text-left">Subject</th>
                        <th class="px-5 py-3 text-left">Due</th>
                        <th class="px-5 py-3 text-center">Progress</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($assignments as $a)
                        @php
                            $c = $counts[$a->id] ?? collect();
                            $submitted = (int) optional($c->firstWhere('status','submitted'))->c + (int) optional($c->firstWhere('status','graded'))->c;
                            $graded = (int) optional($c->firstWhere('status','graded'))->c;
                        @endphp
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-5 py-4">
                                <p class="font-semibold text-gray-900">{{ $a->title }}</p>
                                @if($a->topic)<p class="text-xs text-primary-600">{{ $a->topic }}</p>@endif
                            </td>
                            <td class="px-5 py-4 text-gray-700">{{ $a->class_name }} {{ $a->section_name }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $a->subject_name ?: '—' }}</td>
                            <td class="px-5 py-4 text-gray-500">{{ \Carbon\Carbon::parse($a->due_date)->format('d M Y') }}</td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700">{{ $submitted }} submitted</span>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700">{{ $graded }} graded</span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                {{-- 3-dot action menu --}}
                                <div class="relative inline-block" x-data="{ open: false }">
                                    <button @click="open = !open" class="p-2 rounded-lg hover:bg-gray-100 text-gray-500 transition">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/></svg>
                                    </button>
                                    <div x-show="open" @click.away="open = false" x-transition x-cloak
                                         class="absolute right-0 z-20 mt-1 w-44 bg-white rounded-xl shadow-lg ring-1 ring-gray-200 py-1">
                                        <a href="{{ panel_route('assignments.submissions', $a->id) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" /></svg>
                                            View & Grade
                                        </a>
                                        <form method="POST" action="{{ panel_route('assignments.destroy', $a->id) }}" onsubmit="return confirm('Delete this assignment and its submissions?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-16 text-center text-gray-400">No assignments yet. Create your first one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($assignments->hasPages())<div class="px-5 py-4 border-t border-gray-100">{{ $assignments->links() }}</div>@endif
    </div>

    {{-- Create modal --}}
    <div x-show="showCreate" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" @click.self="showCreate=false">
        <div x-show="showCreate" x-transition.scale class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">New Assignment</h3>
            <form method="POST" action="{{ panel_route('assignments.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Title *</label>
                    <input type="text" name="title" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Topic</label>
                    <input type="text" name="topic" placeholder="e.g. Chapter 5 — Fractions" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Class *</label>
                        <select name="class_id" x-model="classId" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                            <option value="">Select</option>
                            @foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Section *</label>
                        <select name="section_id" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                            <option value="">Select</option>
                            @foreach($sections as $s)<option value="{{ $s->id }}" x-show="String(classId)===String({{ $s->class_id }})">{{ $s->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Subject *</label>
                        <select name="subject_id" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                            <option value="">Select</option>
                            @foreach($subjects as $s)<option value="{{ $s->id }}" x-show="String(classId)===String({{ $s->class_id }})">{{ $s->name }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 outline-none"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Due Date *</label>
                        <input type="date" name="due_date" required class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Max Marks</label>
                        <input type="number" name="max_marks" value="100" min="1" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Attachment (image / PDF / doc)</label>
                    <input type="file" name="attachment" accept="image/*,.pdf,.doc,.docx" class="w-full text-xs">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showCreate=false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-semibold">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function assignmentPage() {
    return { showCreate: {{ $errors->any() ? 'true' : 'false' }}, classId: '' };
}
</script>
@endpush
@endsection
