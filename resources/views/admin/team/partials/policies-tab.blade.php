<div x-data="{ showCreate: false, editing: null }" class="space-y-4">
    <div class="flex justify-end">
        <button @click="showCreate = true" class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Policy
        </button>
    </div>

    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Name</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Permissions</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Status</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($policies as $policy)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900">{{ $policy->name }}</p>
                        @if($policy->description)<p class="text-xs text-gray-500">{{ $policy->description }}</p>@endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $policy->resource_permissions_count }} permission(s)</td>
                    <td class="px-4 py-3 text-center">
                        <form method="POST" action="{{ panel_route('team.policies.status', $policy->id) }}" class="inline">@csrf @method('PATCH')
                            <button type="submit" class="text-xs font-medium px-2 py-0.5 rounded-full {{ $policy->status ? 'text-green-700 bg-green-50' : 'text-gray-500 bg-gray-100' }}">
                                {{ $policy->status ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <button @click="editing = {{ $policy->id }}" class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg" title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                            </button>
                            <form method="POST" action="{{ panel_route('team.policies.destroy', $policy->id) }}" class="inline" onsubmit="return confirm('Delete this policy?')">@csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg" title="Delete">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                <!-- Edit modal for this policy -->
                <template x-if="editing === {{ $policy->id }}">
                    <tr>
                        <td colspan="4" class="p-0">
                            <div x-data x-init="$el.querySelector('dialog') && null" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" @click.self="editing = null">
                                <div class="bg-white rounded-xl shadow-lg w-full max-w-lg max-h-[90vh] overflow-y-auto p-6" @click.stop>
                                    <h3 class="text-base font-semibold text-gray-900 mb-4">Edit Policy</h3>
                                    <form method="POST" action="{{ panel_route('team.policies.update', $policy->id) }}">
                                        @csrf @method('PUT')
                                        @include('admin.team.partials.policy-form', ['policy' => $policy])
                                        <div class="flex justify-end gap-2 mt-5">
                                            <button type="button" @click="editing = null" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Cancel</button>
                                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Save</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                </template>
                @empty
                <tr><td colspan="4" class="px-4 py-12 text-center text-gray-400">No policies yet — create one to bundle permissions together.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Create modal -->
    <div x-show="showCreate" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" @click.self="showCreate = false">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-lg max-h-[90vh] overflow-y-auto p-6" @click.stop>
            <h3 class="text-base font-semibold text-gray-900 mb-4">New Policy</h3>
            <form method="POST" action="{{ panel_route('team.policies.store') }}">
                @csrf
                @include('admin.team.partials.policy-form')
                <div class="flex justify-end gap-2 mt-5">
                    <button type="button" @click="showCreate = false" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
