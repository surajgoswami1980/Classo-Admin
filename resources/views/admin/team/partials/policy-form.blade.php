{{-- Expects: $resources (Resource::with('actions.permissions')), optional $policy with ->resourcePermissions loaded --}}
@php
    $checkedIds = isset($policy) ? $policy->resourcePermissions->pluck('id')->all() : [];
@endphp
<div class="space-y-4">
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Policy Name</label>
        <input type="text" name="name" required maxlength="150" value="{{ $policy->name ?? '' }}"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
        <textarea name="description" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">{{ $policy->description ?? '' }}</textarea>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-2">Permissions</label>
        <div class="border rounded-lg divide-y max-h-80 overflow-y-auto">
            @foreach($resources as $resource)
                @if($resource->actions->isNotEmpty())
                <div class="p-3">
                    <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide mb-2">{{ $resource->name }}</p>
                    <div class="flex flex-wrap gap-x-4 gap-y-2">
                        @foreach($resource->actions as $action)
                            @foreach($action->permissions as $permission)
                            <label class="flex items-center gap-1.5 text-sm text-gray-700">
                                <input type="checkbox" name="resource_permission_ids[]" value="{{ $permission->id }}"
                                    {{ in_array($permission->id, $checkedIds) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                {{ $action->label }}
                            </label>
                            @endforeach
                        @endforeach
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
