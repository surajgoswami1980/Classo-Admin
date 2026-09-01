<div x-data="{ showCreate: false, editing: null }" class="space-y-4">
    <div class="flex justify-end">
        <button @click="showCreate = true" class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Add Team Member
        </button>
    </div>

    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Name</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Email</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Access</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Status</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($users as $member)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $member->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $member->email }}</td>
                    <td class="px-4 py-3 text-gray-600">
                        @if($member->accessRole)
                            <span class="text-xs font-medium text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full">Role: {{ $member->accessRole->name }}</span>
                        @else
                            <span class="text-xs font-medium text-purple-700 bg-purple-50 px-2 py-0.5 rounded-full">{{ $member->directPolicies->count() }} direct polic{{ $member->directPolicies->count() === 1 ? 'y' : 'ies' }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <form method="POST" action="{{ panel_route('team.users.status', $member->id) }}" class="inline">@csrf @method('PATCH')
                            <button type="submit" class="text-xs font-medium px-2 py-0.5 rounded-full {{ $member->is_active ? 'text-green-700 bg-green-50' : 'text-gray-500 bg-gray-100' }}">
                                {{ $member->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <button @click="editing = {{ $member->id }}" class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg" title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                            </button>
                            <form method="POST" action="{{ panel_route('team.users.password.reset', $member->id) }}" class="inline" onsubmit="return confirm('Reset this team member\'s password?')">@csrf
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg" title="Reset Password">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm2.498-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5ZM8.25 6h7.5v2.25h-7.5V6ZM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5A2.25 2.25 0 0 0 6.75 21.75h10.5A2.25 2.25 0 0 0 19.5 19.5V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0 0 12 2.25Z"/></svg>
                                </button>
                            </form>
                            <form method="POST" action="{{ panel_route('team.users.destroy', $member->id) }}" class="inline" onsubmit="return confirm('Remove this team member?')">@csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg" title="Remove">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                <template x-if="editing === {{ $member->id }}">
                    <tr>
                        <td colspan="5" class="p-0">
                            <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" @click.self="editing = null">
                                <div class="bg-white rounded-xl shadow-lg w-full max-w-lg max-h-[90vh] overflow-y-auto p-6" @click.stop>
                                    <h3 class="text-base font-semibold text-gray-900 mb-4">Edit Team Member</h3>
                                    <form method="POST" action="{{ panel_route('team.users.update', $member->id) }}">
                                        @csrf @method('PUT')
                                        @include('admin.team.partials.user-form', ['member' => $member])
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
                <tr><td colspan="5" class="px-4 py-12 text-center text-gray-400">No team members yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($users->hasPages())
        <div class="px-4 py-3 border-t">{{ $users->links() }}</div>
        @endif
    </div>

    <div x-show="showCreate" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" @click.self="showCreate = false">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-lg max-h-[90vh] overflow-y-auto p-6" @click.stop>
            <h3 class="text-base font-semibold text-gray-900 mb-4">Add Team Member</h3>
            <form method="POST" action="{{ panel_route('team.users.store') }}">
                @csrf
                @include('admin.team.partials.user-form')
                <div class="flex justify-end gap-2 mt-5">
                    <button type="button" @click="showCreate = false" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>
