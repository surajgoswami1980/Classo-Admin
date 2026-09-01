@extends('layouts.app')

@section('title', 'Manage Resources & Permissions')
@section('page-title', 'Manage Resources & Permissions')

@section('content')
<div class="space-y-6" x-data="{ showCreateResource: false, editingResource: null, addingActionTo: null, editingAction: null }">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Resources & Permissions</h2>
            <p class="text-sm text-gray-500">The catalog every school's Policies are built from. Most resources/actions are auto-derived from app routes — you can rename them, toggle availability, or add custom ones here.</p>
        </div>
        <button @click="showCreateResource = true" class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Resource
        </button>
    </div>

    <div class="space-y-4">
        @forelse($resources as $resource)
        <div class="bg-white rounded-xl border overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b bg-gray-50">
                <template x-if="editingResource !== {{ $resource->id }}">
                    <div class="flex items-center gap-3">
                        <h3 class="text-sm font-semibold text-gray-900">{{ $resource->name }}</h3>
                        <span class="font-mono text-xs text-gray-400">{{ $resource->slug }}</span>
                    </div>
                </template>
                <template x-if="editingResource === {{ $resource->id }}">
                    <form method="POST" action="{{ route('admin.resources.update', $resource) }}" class="flex items-center gap-2">
                        @csrf @method('PUT')
                        <input type="text" name="name" value="{{ $resource->name }}" class="border border-gray-300 rounded-lg px-2 py-1 text-sm">
                        <button type="submit" class="text-xs font-medium text-blue-600 hover:underline">Save</button>
                        <button type="button" @click="editingResource = null" class="text-xs text-gray-400 hover:underline">Cancel</button>
                    </form>
                </template>
                <div class="flex items-center gap-2">
                    <form method="POST" action="{{ route('admin.resources.status', $resource) }}">@csrf @method('PATCH')
                        <button type="submit" class="text-xs font-medium px-2 py-0.5 rounded-full {{ $resource->status ? 'text-green-700 bg-green-50' : 'text-gray-500 bg-gray-100' }}">
                            {{ $resource->status ? 'Active' : 'Inactive' }}
                        </button>
                    </form>
                    <button @click="editingResource = editingResource === {{ $resource->id }} ? null : {{ $resource->id }}" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                    </button>
                    <button @click="addingActionTo = addingActionTo === {{ $resource->id }} ? null : {{ $resource->id }}" class="text-xs font-medium text-blue-600 hover:underline">+ Action</button>
                </div>
            </div>

            <div x-show="addingActionTo === {{ $resource->id }}" x-cloak class="px-5 py-3 bg-blue-50/50 border-b">
                <form method="POST" action="{{ route('admin.resources.actions.store', $resource) }}" class="flex items-end gap-2">
                    @csrf
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Action key</label>
                        <input type="text" name="action" placeholder="e.g. export" required class="border border-gray-300 rounded-lg px-2 py-1.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Label</label>
                        <input type="text" name="label" placeholder="e.g. Export" required class="border border-gray-300 rounded-lg px-2 py-1.5 text-sm">
                    </div>
                    <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Add</button>
                </form>
            </div>

            <div class="divide-y">
                @forelse($resource->actions as $action)
                <div class="flex items-center justify-between px-5 py-2.5">
                    <template x-if="editingAction !== {{ $action->id }}">
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-700">{{ $action->label }}</span>
                            <span class="font-mono text-xs text-gray-400">{{ $resource->slug }}.{{ $action->action }}</span>
                        </div>
                    </template>
                    <template x-if="editingAction === {{ $action->id }}">
                        <form method="POST" action="{{ route('admin.resources.actions.update', $action) }}" class="flex items-center gap-2">
                            @csrf @method('PUT')
                            <input type="text" name="label" value="{{ $action->label }}" class="border border-gray-300 rounded-lg px-2 py-1 text-sm">
                            <button type="submit" class="text-xs font-medium text-blue-600 hover:underline">Save</button>
                            <button type="button" @click="editingAction = null" class="text-xs text-gray-400 hover:underline">Cancel</button>
                        </form>
                    </template>
                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('admin.resources.actions.status', $action) }}">@csrf @method('PATCH')
                            <button type="submit" class="text-xs font-medium px-2 py-0.5 rounded-full {{ $action->status ? 'text-green-700 bg-green-50' : 'text-gray-500 bg-gray-100' }}">
                                {{ $action->status ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                        <button @click="editingAction = editingAction === {{ $action->id }} ? null : {{ $action->id }}" class="p-1 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                        </button>
                    </div>
                </div>
                @empty
                <p class="px-5 py-4 text-sm text-gray-400">No actions defined yet.</p>
                @endforelse
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border p-12 text-center text-gray-400">
            No resources yet. Run the seeder or add one manually.
        </div>
        @endforelse
    </div>

    <!-- Create resource modal -->
    <div x-show="showCreateResource" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" @click.self="showCreateResource = false">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6" @click.stop>
            <h3 class="text-base font-semibold text-gray-900 mb-4">New Resource</h3>
            <form method="POST" action="{{ route('admin.resources.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Display Name</label>
                    <input type="text" name="name" required maxlength="100" placeholder="e.g. Hostel" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Slug (used in permission keys)</label>
                    <input type="text" name="slug" required maxlength="100" placeholder="e.g. hostel" pattern="[a-zA-Z0-9_-]+" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showCreateResource = false" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
