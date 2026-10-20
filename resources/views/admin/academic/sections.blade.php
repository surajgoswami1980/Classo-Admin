@extends('layouts.app')
@section('title', 'Sections')
@section('page-title', 'Sections')
@section('content')
<div class="max-w-4xl">
    <!-- Add Section Form -->
    <div class="mc-card p-6 mb-6">
        <h3 class="text-base font-semibold text-gray-900 mb-4">Add New Section</h3>
        <form method="POST" action="{{ panel_route('academic.sections.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @csrf
            @if($errors->any())
                <div class="md:col-span-4 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif
            <div>
                <label class="form-label">Section Name *</label>
                <input type="text" name="name" required placeholder="e.g. A, B, C" maxlength="10" class="form-input">
            </div>
            <div>
                <label class="form-label">Class *</label>
                <select name="class_id" required class="form-input">
                    <option value="">Select Class</option>
                    @foreach($classes as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Capacity</label>
                <input type="number" name="capacity" value="40" min="1" max="200" class="form-input">
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn btn-primary">Add Section</button>
            </div>
        </form>
    </div>

    <!-- Sections List -->
    <div class="mc-card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50/80 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Section</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Class</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Capacity</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($sections as $section)
                <tr class="hover:bg-primary-50/40 transition-colors">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $section->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $section->class_name }}</td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ $section->capacity }}</td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ panel_route('academic.sections.destroy', $section->id) }}" class="inline" data-confirm="This action can't be undone." data-confirm-title="Delete this section?" data-confirm-ok="Delete" data-confirm-tone="danger">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 text-xs">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">No sections created. Add classes first.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
