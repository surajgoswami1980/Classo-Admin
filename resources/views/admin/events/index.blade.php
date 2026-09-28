@extends('layouts.app')

@section('title', 'Events')
@section('page-title', 'Events & Activities')

@section('content')
<div x-data="{
        showModal: false, editing: false,
        form: { id:'', title:'', description:'', category:'general', venue:'', start_at:'', end_at:'', registration_deadline:'', is_paid:false, fee:0, capacity:'', audience_type:'all', class_id:'', section_id:'' },
        openCreate() { this.editing=false; this.form={ id:'', title:'', description:'', category:'general', venue:'', start_at:'', end_at:'', registration_deadline:'', is_paid:false, fee:0, capacity:'', audience_type:'all', class_id:'', section_id:'' }; this.showModal=true; },
        openEdit(e) { this.editing=true; this.form={ ...e, is_paid: !!e.is_paid }; this.showModal=true; },
        sections: {{ $sections->toJson() }},
        get filteredSections() { return this.sections.filter(s => String(s.class_id) === String(this.form.class_id)); },
     }" class="space-y-6">

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Total Events</p><p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Published</p><p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['published'] }}</p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Upcoming</p><p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['upcoming'] }}</p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Registrations</p><p class="text-2xl font-bold text-teal-600 mt-1">{{ $stats['registrations'] }}</p></div>
    </div>

    <div class="flex items-center justify-between">
        <form method="GET" class="flex gap-2">
            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="">All Status</option>
                @foreach(['draft','published','cancelled','completed'] as $st)
                    <option value="{{ $st }}" {{ ($filters['status'] ?? '') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm font-medium hover:bg-gray-900">Filter</button>
        </form>
        <button @click="openCreate()" class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Create Event
        </button>
    </div>

    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Event</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">When</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Audience</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Fee</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Regs</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Status</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($events as $event)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900">{{ $event->title }}</p>
                        <p class="text-xs text-gray-400 capitalize">{{ $event->category }} @if($event->venue) · {{ $event->venue }} @endif</p>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">{{ \Carbon\Carbon::parse($event->start_at)->format('d M Y, h:i A') }}</td>
                    <td class="px-4 py-3 text-gray-600 text-xs capitalize">{{ $event->audience_type }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($event->is_paid)<span class="text-xs font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">₹{{ number_format($event->fee) }}</span>
                        @else<span class="text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded-full">Free</span>@endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ panel_route('events.registrations', $event->id) }}" class="text-blue-600 hover:underline">{{ $event->registrations }}</a>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @php $sb = ['draft'=>'text-gray-700 bg-gray-100','published'=>'text-green-700 bg-green-50','cancelled'=>'text-red-700 bg-red-50','completed'=>'text-blue-700 bg-blue-50'][$event->status] ?? 'text-gray-700 bg-gray-100'; @endphp
                        <span class="text-xs font-medium {{ $sb }} px-2 py-0.5 rounded-full">{{ ucfirst($event->status) }}</span>
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        @if($event->status === 'draft')
                        <form method="POST" action="{{ panel_route('events.publish', $event->id) }}" class="inline" onsubmit="return confirm('Publish and notify students?')">@csrf
                            <button type="submit" class="text-xs font-medium text-green-600 hover:underline">Publish</button>
                        </form>
                        @elseif($event->status === 'published')
                        <form method="POST" action="{{ panel_route('events.cancel', $event->id) }}" class="inline" onsubmit="return confirm('Cancel this event?')">@csrf
                            <button type="submit" class="text-xs font-medium text-red-500 hover:underline">Cancel</button>
                        </form>
                        @endif
                        <button @click='openEdit(@json($event))' class="text-xs font-medium text-blue-600 hover:underline ml-3">Edit</button>
                        <form method="POST" action="{{ panel_route('events.destroy', $event->id) }}" class="inline ml-3" onsubmit="return confirm('Delete this event?')">@csrf @method('DELETE')
                            <button type="submit" class="text-xs font-medium text-red-500 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">No events yet — create one to get started.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($events->hasPages())<div class="px-4 py-3 border-t">{{ $events->links() }}</div>@endif
    </div>

    {{-- Create / Edit Modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 overflow-y-auto" @click.self="showModal = false">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-2xl p-6 my-8" @click.stop>
            <h3 class="text-base font-semibold text-gray-900 mb-4" x-text="editing ? 'Edit Event' : 'Create Event'"></h3>
            <form method="POST" :action="editing ? '{{ url(panel_prefix().'/events') }}/' + form.id : '{{ panel_route('events.store') }}'" class="space-y-4">
                @csrf
                <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Title *</label>
                    <input type="text" name="title" x-model="form.title" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                    <textarea name="description" x-model="form.description" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
                        <select name="category" x-model="form.category" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            @foreach(['general','sports','cultural','academic','trip','competition'] as $cat)
                                <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Venue</label>
                        <input type="text" name="venue" x-model="form.venue" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Starts *</label>
                        <input type="datetime-local" name="start_at" x-model="form.start_at" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Ends</label>
                        <input type="datetime-local" name="end_at" x-model="form.end_at" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Reg. Deadline</label>
                        <input type="datetime-local" name="registration_deadline" x-model="form.registration_deadline" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3 items-end">
                    <div>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="hidden" name="is_paid" value="0">
                            <input type="checkbox" name="is_paid" value="1" x-model="form.is_paid" class="rounded border-gray-300"> Paid Event
                        </label>
                    </div>
                    <div x-show="form.is_paid">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Fee (₹)</label>
                        <input type="number" name="fee" x-model="form.fee" min="0" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Capacity</label>
                        <input type="number" name="capacity" x-model="form.capacity" min="1" placeholder="Unlimited" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Audience *</label>
                        <select name="audience_type" x-model="form.audience_type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="all">Whole School</option>
                            <option value="class">Specific Class</option>
                            <option value="section">Specific Section</option>
                        </select>
                    </div>
                    <div x-show="form.audience_type !== 'all'">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Class</label>
                        <select name="class_id" x-model="form.class_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="">Select class</option>
                            @foreach($classes as $cid => $cname)
                                <option value="{{ $cid }}">{{ $cname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div x-show="form.audience_type === 'section'">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Section</label>
                        <select name="section_id" x-model="form.section_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="">Select section</option>
                            <template x-for="s in filteredSections" :key="s.id">
                                <option :value="s.id" x-text="s.name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showModal = false" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700" x-text="editing ? 'Update' : 'Create'"></button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
