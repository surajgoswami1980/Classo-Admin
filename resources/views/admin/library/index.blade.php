@extends('layouts.app')

@section('title', 'Library')
@section('page-title', 'Library Management')

@section('content')
<div x-data="{
        showBookModal: false,
        showAssignModal: false,
        assignBookId: '', assignBookTitle: '',
        editing: false,
        form: { id:'', title:'', author:'', isbn:'', category:'', publisher:'', total_copies:1, rack_location:'' },
        openCreate() { this.editing=false; this.form={ id:'', title:'', author:'', isbn:'', category:'', publisher:'', total_copies:1, rack_location:'' }; this.showBookModal=true; },
        openEdit(b) { this.editing=true; this.form={ ...b }; this.showBookModal=true; },
        openAssign(id, title) { this.assignBookId=id; this.assignBookTitle=title; this.showAssignModal=true; },
     }" class="space-y-6">

    {{-- Stat cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="mc-card p-4">
            <p class="text-xs text-gray-500">Total Copies</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_books']) }}</p>
        </div>
        <div class="mc-card p-4">
            <p class="text-xs text-gray-500">Issued</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($stats['issued']) }}</p>
        </div>
        <div class="mc-card p-4">
            <p class="text-xs text-gray-500">Overdue</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ number_format($stats['overdue']) }}</p>
        </div>
        <div class="mc-card p-4">
            <p class="text-xs text-gray-500">Fine Collected</p>
            <p class="text-2xl font-bold text-green-600 mt-1">₹{{ number_format($stats['fine_collected']) }}</p>
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search title, author, ISBN..." class="form-input w-64">
            <input type="text" name="category" value="{{ $filters['category'] ?? '' }}" placeholder="Category" class="form-input w-40">
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
        <div class="flex gap-2">
            <a href="{{ panel_route('library.issues') }}" class="btn btn-secondary">Issued Books</a>
            <button @click="openCreate()" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Add Book
            </button>
        </div>
    </div>

    <div class="mc-card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50/80 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Title</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Author</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Category</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Available</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Rack</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($books as $book)
                <tr class="hover:bg-primary-50/40 transition-colors">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $book->title }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $book->author ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $book->category ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $book->available_copies > 0 ? 'text-green-700 bg-green-50' : 'text-red-700 bg-red-50' }}">
                            {{ $book->available_copies }}/{{ $book->total_copies }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $book->rack_location ?? '—' }}</td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <button @click='openAssign({{ $book->id }}, @json($book->title))' @class(['text-xs font-medium text-cyan-600 hover:underline mr-3', 'opacity-40 pointer-events-none' => $book->available_copies <= 0])>Assign</button>
                        <button @click='openEdit(@json($book))' class="text-xs font-medium text-blue-600 hover:underline mr-3">Edit</button>
                        <form method="POST" action="{{ panel_route('library.books.destroy', $book->id) }}" class="inline" data-confirm="This action can't be undone." data-confirm-title="Delete this book?" data-confirm-ok="Delete" data-confirm-tone="danger">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs font-medium text-red-500 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">No books in the catalog yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($books->hasPages())
        <div class="px-4 py-3 border-t">{{ $books->links() }}</div>
        @endif
    </div>

    {{-- Add/Edit Book Modal --}}
    <div x-show="showBookModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-navy-950/50 backdrop-blur-[2px]" @click.self="showBookModal = false">
        <div class="modal-panel max-w-lg p-6" @click.stop>
            <h3 class="text-base font-semibold text-gray-900 mb-4" x-text="editing ? 'Edit Book' : 'Add Book'"></h3>
            <form method="POST" :action="editing ? '{{ url(panel_prefix().'/library/books') }}/' + form.id : '{{ panel_route('library.books.store') }}'" class="space-y-4">
                @csrf
                <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" x-model="form.title" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Author</label>
                        <input type="text" name="author" x-model="form.author" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">ISBN</label>
                        <input type="text" name="isbn" x-model="form.isbn" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Category</label>
                        <input type="text" name="category" x-model="form.category" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Publisher</label>
                        <input type="text" name="publisher" x-model="form.publisher" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Total Copies *</label>
                        <input type="number" name="total_copies" x-model="form.total_copies" min="1" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Rack Location</label>
                        <input type="text" name="rack_location" x-model="form.rack_location" class="form-input">
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showBookModal = false" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary" x-text="editing ? 'Update' : 'Add'"></button>
                </div>
            </form>
        </div>
    </div>

    {{-- Assign to student Modal --}}
    <div x-show="showAssignModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-navy-950/50 backdrop-blur-[2px]" @click.self="showAssignModal = false">
        <div class="modal-panel max-w-md p-6" @click.stop>
            <h3 class="text-base font-semibold text-gray-900 mb-1">Assign Book</h3>
            <p class="text-sm text-gray-500 mb-4" x-text="assignBookTitle"></p>
            <form method="POST" action="{{ panel_route('library.assign') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="book_id" :value="assignBookId">
                <div>
                    <label class="form-label">Student</label>
                    <select name="student_id" required class="form-input">
                        <option value="">Select student...</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->class_name }}-{{ $student->section_name }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" required min="{{ now()->toDateString() }}" value="{{ now()->addDays(14)->toDateString() }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showAssignModal = false" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-cyan-600 text-white rounded-lg text-sm font-medium hover:bg-cyan-700">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
