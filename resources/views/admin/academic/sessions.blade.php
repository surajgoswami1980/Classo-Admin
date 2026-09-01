@extends('layouts.app')
@section('title', 'Academic Sessions')
@section('page-title', 'Academic Sessions')
@section('content')
<div class="max-w-4xl">
    <!-- Add Session Form -->
    <div class="bg-white rounded-xl border p-6 mb-6">
        <h3 class="text-base font-semibold text-gray-900 mb-4">Add New Session</h3>
        <form method="POST" action="{{ panel_route('academic.sessions.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @csrf
            @if($errors->any())
                <div class="md:col-span-4 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Session Name *</label>
                <input type="text" name="name" required placeholder="e.g. 2026-2027" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Start Date *</label>
                <input type="date" name="start_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">End Date *</label>
                <input type="date" name="end_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div class="flex items-end gap-2">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_current" value="1" class="rounded">
                    Current
                </label>
                <button type="submit" class="px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">Add</button>
            </div>
        </form>
    </div>

    <!-- Sessions List -->
    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Name</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Start</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">End</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($sessions as $session)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $session->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ \Carbon\Carbon::parse($session->start_date)->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ \Carbon\Carbon::parse($session->end_date)->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($session->is_current)
                            <span class="text-xs font-medium text-green-700 bg-green-50 px-2.5 py-1 rounded-full">Current</span>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">No sessions created yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
