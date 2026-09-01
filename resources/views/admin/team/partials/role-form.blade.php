{{-- Expects: $policies (active policies for this school), optional $role with ->policies loaded --}}
@php
    $checkedPolicyIds = isset($role) ? $role->policies->pluck('id')->all() : [];
@endphp
<div class="space-y-4">
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Role Name</label>
        <input type="text" name="name" required maxlength="100" value="{{ $role->name ?? '' }}"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-2">Policies granted to this role</label>
        <div class="border rounded-lg divide-y max-h-64 overflow-y-auto">
            @forelse($policies as $policy)
            <label class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700">
                <input type="checkbox" name="policy_ids[]" value="{{ $policy->id }}"
                    {{ in_array($policy->id, $checkedPolicyIds) ? 'checked' : '' }}
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                {{ $policy->name }}
            </label>
            @empty
            <p class="px-3 py-4 text-sm text-gray-400">No policies yet — create one first in the Policies tab.</p>
            @endforelse
        </div>
    </div>
</div>
