@extends('layouts.app')
@section('title', 'Classes')
@section('page-title', 'Classes')
@section('content')
<div class="max-w-4xl">
    <!-- Add Class Form -->
    <div class="bg-white rounded-xl border p-6 mb-6">
        <h3 class="text-base font-semibold text-gray-900 mb-4">Add New Class</h3>
        <form method="POST" action="{{ panel_route('academic.classes.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @csrf
            @if($errors->any())
                <div class="md:col-span-4 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Class Name *</label>
                <input type="text" name="name" required placeholder="e.g. Class 1, Nursery" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Session *</label>
                <select name="academic_session_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Select</option>
                    @foreach($sessions as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sort Order</label>
                <input type="number" name="numeric_order" value="0" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">Add Class</button>
            </div>
        </form>
    </div>

    <!-- Classes List -->
    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Class</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Session</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Order</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($classes as $class)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $class->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $class->session_name ?? '—' }}</td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ $class->numeric_order }}</td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ panel_route('academic.classes.destroy', $class->id) }}" class="inline" onsubmit="return confirm('Delete this class?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 text-xs">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">No classes created. Create an academic session first.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
