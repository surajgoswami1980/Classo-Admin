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
        <div class="mc-card p-4"><p class="text-xs text-gray-500">Total Events</p><p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p></div>
        <div class="mc-card p-4"><p class="text-xs text-gray-500">Published</p><p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['published'] }}</p></div>
        <div class="mc-card p-4"><p class="text-xs text-gray-500">Upcoming</p><p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['upcoming'] }}</p></div>
        <div class="mc-card p-4"><p class="text-xs text-gray-500">Registrations</p><p class="text-2xl font-bold text-cyan-600 mt-1">{{ $stats['registrations'] }}</p></div>
    </div>

    <div class="flex items-center justify-between">
        <form method="GET" class="flex gap-2">
            <select name="status" class="form-input">
                <option value="">All Status</option>
                @foreach(['draft','published','cancelled','completed'] as $st)
                    <option value="{{ $st }}" {{ ($filters['status'] ?? '') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
        <button @click="openCreate()" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Create Event
        </button>
    </div>

    <div class="mc-card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50/80 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Event</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">When</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Audience</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Fee</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Regs</th>
                    <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Status</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($events as $event)
                <tr class="hover:bg-primary-50/40 transition-colors">
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
                        <form method="POST" action="{{ panel_route('events.publish', $event->id) }}" class="inline" data-confirm="Please confirm to continue." data-confirm-title="Publish and notify students?" data-confirm-ok="Publish" data-confirm-tone="primary">@csrf
                            <button type="submit" class="text-xs font-medium text-green-600 hover:underline">Publish</button>
                        </form>
                        @elseif($event->status === 'published')
                        <form method="POST" action="{{ panel_route('events.cancel', $event->id) }}" class="inline" data-confirm="This action can't be undone." data-confirm-title="Cancel this event?" data-confirm-ok="Yes, cancel" data-confirm-tone="danger">@csrf
                            <button type="submit" class="text-xs font-medium text-red-500 hover:underline">Cancel</button>
                        </form>
                        @endif
                        <button @click='openEdit(@json($event))' class="text-xs font-medium text-blue-600 hover:underline ml-3">Edit</button>
                        <form method="POST" action="{{ panel_route('events.destroy', $event->id) }}" class="inline ml-3" data-confirm="This action can't be undone." data-confirm-title="Delete this event?" data-confirm-ok="Delete" data-confirm-tone="danger">@csrf @method('DELETE')
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
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto bg-navy-950/50 backdrop-blur-[2px]" @click.self="showModal = false">
        <div class="modal-panel max-w-2xl p-6 my-8" @click.stop>
            <h3 class="text-base font-semibold text-gray-900 mb-4" x-text="editing ? 'Edit Event' : 'Create Event'"></h3>
            <form method="POST" enctype="multipart/form-data" :action="editing ? '{{ url(panel_prefix().'/events') }}/' + form.id : '{{ panel_route('events.store') }}'" class="space-y-4">
                @csrf
                <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>

                <div>
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" x-model="form.title" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Description</label>
                    <textarea name="description" x-model="form.description" rows="2" class="form-input"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Event Image / Banner</label>
                        <input type="file" name="banner" accept="image/*" class="w-full text-xs">
                        <p class="text-[11px] text-gray-400 mt-1">Shown on the event; can also be used as an app banner.</p>
                    </div>
                    <div>
                        <label class="form-label">Thumbnail (optional)</label>
                        <input type="file" name="thumbnail" accept="image/*" class="w-full text-xs">
                    </div>
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="hidden" name="set_as_banner" value="0">
                    <input type="checkbox" name="set_as_banner" value="1" class="form-check">
                    Also show this event's image as an app banner (slider)
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Category</label>
                        <select name="category" x-model="form.category" class="form-input">
                            @foreach(['general','sports','cultural','academic','trip','competition'] as $cat)
                                <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Venue</label>
                        <input type="text" name="venue" x-model="form.venue" class="form-input">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="form-label">Starts *</label>
                        <input type="datetime-local" name="start_at" x-model="form.start_at" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Ends</label>
                        <input type="datetime-local" name="end_at" x-model="form.end_at" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Reg. Deadline</label>
                        <input type="datetime-local" name="registration_deadline" x-model="form.registration_deadline" class="form-input">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3 items-end">
                    <div>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="hidden" name="is_paid" value="0">
                            <input type="checkbox" name="is_paid" value="1" x-model="form.is_paid" class="form-check"> Paid Event
                        </label>
                    </div>
                    <div x-show="form.is_paid">
                        <label class="form-label">Fee (₹)</label>
                        <input type="number" name="fee" x-model="form.fee" min="0" step="0.01" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Capacity</label>
                        <input type="number" name="capacity" x-model="form.capacity" min="1" placeholder="Unlimited" class="form-input">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="form-label">Audience *</label>
                        <select name="audience_type" x-model="form.audience_type" required class="form-input">
                            <option value="all">Whole School</option>
                            <option value="class">Specific Class</option>
                            <option value="section">Specific Section</option>
                        </select>
                    </div>
                    <div x-show="form.audience_type !== 'all'">
                        <label class="form-label">Class</label>
                        <select name="class_id" x-model="form.class_id" class="form-input">
                            <option value="">Select class</option>
                            @foreach($classes as $cid => $cname)
                                <option value="{{ $cid }}">{{ $cname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div x-show="form.audience_type === 'section'">
                        <label class="form-label">Section</label>
                        <select name="section_id" x-model="form.section_id" class="form-input">
                            <option value="">Select section</option>
                            <template x-for="s in filteredSections" :key="s.id">
                                <option :value="s.id" x-text="s.name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showModal = false" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary" x-text="editing ? 'Update' : 'Create'"></button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
