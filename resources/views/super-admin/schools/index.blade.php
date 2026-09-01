@extends('layouts.app')

@section('title', 'Manage Schools')
@section('page-title', 'Schools')

@section('content')
<div>
    <!-- Header Actions -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <input type="text" placeholder="Search schools..." class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none w-64">
            <select class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="trial">Trial</option>
                <option value="expired">Expired</option>
            </select>
        </div>
        <a href="{{ route('admin.schools.create') }}" class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Onboard School
        </a>
    </div>

    <!-- Schools Table -->
    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">School</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Code</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Board</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Status</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Students</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($schools as $school)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center text-sm font-bold text-blue-700">{{ strtoupper(substr($school->name, 0, 1)) }}</div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $school->name }}</p>
                                <p class="text-xs text-gray-500">{{ $school->city }}, {{ $school->state }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3"><span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">{{ $school->code }}</span></td>
                    <td class="px-4 py-3 text-gray-600">{{ $school->board_affiliation }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($school->is_active)
                            <span class="text-xs font-medium text-green-700 bg-green-50 px-2.5 py-1 rounded-full">Active</span>
                        @else
                            <span class="text-xs font-medium text-red-700 bg-red-50 px-2.5 py-1 rounded-full">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ $school->max_students }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @if($school->is_active)
                            <form method="POST" action="{{ route('admin.schools.switch', $school) }}" class="inline">@csrf
                                <button type="submit" class="px-2.5 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition" title="View as School Admin">
                                    Switch
                                </button>
                            </form>
                            @endif
                            <a href="{{ route('admin.schools.edit', $school) }}" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                            </a>
                            <a href="{{ route('admin.schools.show', $school) }}" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                        <p>No schools onboarded yet</p>
                        <a href="{{ route('admin.schools.create') }}" class="text-blue-600 text-sm font-medium mt-1 inline-block">+ Onboard first school</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($schools->hasPages())
        <div class="px-4 py-3 border-t">{{ $schools->links() }}</div>
        @endif
    </div>
</div>
@endsection
