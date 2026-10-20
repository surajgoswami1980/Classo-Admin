@extends('layouts.app')

@section('title', 'Banners')
@section('page-title', 'Banners & Popups')

@section('content')
<div x-data="bannerManager()" class="space-y-6">

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div class="mc-card p-4"><p class="text-xs text-gray-500">Active Sliders</p><p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['sliders'] }}</p></div>
        <div class="mc-card p-4"><p class="text-xs text-gray-500">Active Popup</p><p class="text-2xl font-bold {{ $stats['popup_active'] ? 'text-green-600' : 'text-gray-400' }} mt-1">{{ $stats['popup_active'] }}/1</p></div>
        <div class="mc-card p-4"><p class="text-xs text-gray-500">Total Banners</p><p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p></div>
    </div>

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Banners</h2>
            <p class="text-sm text-gray-500">Sliders scroll at the top of the app home. Only one popup can be active at a time.</p>
        </div>
        <button @click="openCreate()" class="btn btn-primary">+ Add Banner</button>
    </div>

    {{-- Live preview of the slider --}}
    @php $sliders = $banners->where('placement','slider')->where('is_active', true); @endphp
    @if($sliders->count())
    <div>
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Slider preview</p>
        <div class="flex gap-3 overflow-x-auto pb-2">
            @foreach($sliders as $b)
            <div class="mc-card shrink-0 w-72 overflow-hidden">
                <img src="{{ \Illuminate\Support\Str::startsWith($b->image,'http') ? $b->image : asset('storage/'.$b->image) }}" class="w-full h-32 object-cover" alt="{{ $b->title }}">
                @if($b->title)<div class="px-3 py-2 text-sm font-medium text-gray-800">{{ $b->title }}</div>@endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Banner table --}}
    <div class="mc-card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50/80 border-b border-gray-200"><tr>
                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Preview</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Title / Link</th>
                <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Placement</th>
                <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Audience</th>
                <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Status</th>
                <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($banners as $b)
                <tr class="hover:bg-primary-50/40 transition-colors">
                    <td class="px-4 py-3">
                        <img src="{{ \Illuminate\Support\Str::startsWith($b->image,'http') ? $b->image : asset('storage/'.$b->image) }}" class="w-24 h-14 object-cover rounded-lg border" alt="">
                    </td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900">{{ $b->title ?: '—' }}</p>
                        <p class="text-xs text-gray-400">
                            @if($b->link_type==='event')🔗 Event #{{ $b->event_id }}
                            @elseif($b->link_type==='url')🔗 {{ \Illuminate\Support\Str::limit($b->link_url, 32) }}
                            @else No link @endif
                        </p>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $b->placement==='popup' ? 'text-primary-700 bg-primary-50' : 'text-blue-700 bg-blue-50' }}">{{ ucfirst($b->placement) }}</span>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-600 text-xs">
                        {{ $b->audience_type==='all' ? 'All' : ($b->audience_type==='class' ? 'Class #'.$b->class_id : 'Section #'.$b->section_id) }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <form method="POST" action="{{ panel_route('banners.toggle', $b->id) }}" class="inline">@csrf @method('PATCH')
                            <button type="submit" class="text-xs font-medium px-2 py-0.5 rounded-full {{ $b->is_active ? 'text-green-700 bg-green-50' : 'text-gray-500 bg-gray-100' }}">
                                {{ $b->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <button @click='openEdit(@json($b))' class="text-xs font-medium text-blue-600 hover:underline">Edit</button>
                        <form method="POST" action="{{ panel_route('banners.destroy', $b->id) }}" class="inline" data-confirm="This action can't be undone." data-confirm-title="Delete this banner?" data-confirm-ok="Delete" data-confirm-tone="danger">@csrf @method('DELETE')
                            <button type="submit" class="text-xs font-medium text-red-600 hover:underline ml-3">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty<tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">No banners yet. Add one to show it in the app.</td></tr>@endforelse
            </tbody>
        </table>
    </div>

    {{-- Create/Edit Modal --}}
    <div x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-navy-950/50 backdrop-blur-[2px]" @click.self="show=false">
        <div class="modal-panel max-w-lg max-h-[90vh] overflow-y-auto p-6" @click.stop>
            <h3 class="text-base font-semibold text-gray-900 mb-4" x-text="editing ? 'Edit Banner' : 'Add Banner'"></h3>
            <form :action="editing ? '{{ url(panel_prefix().'/banners') }}/' + form.id : '{{ panel_route('banners.store') }}'" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <template x-if="editing"><input type="hidden" name="_method" value="POST"></template>

                <div>
                    <label class="form-label">Title (optional)</label>
                    <input type="text" name="title" x-model="form.title" class="form-input">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Banner Image <span x-show="!editing" class="text-red-500">*</span></label>
                        <input type="file" name="image" accept="image/*" class="w-full text-xs">
                        <p class="text-[11px] text-gray-400 mt-1">JPG/PNG/WebP, max 4MB. ~16:9 recommended.</p>
                    </div>
                    <div>
                        <label class="form-label">Thumbnail (optional)</label>
                        <input type="file" name="thumbnail" accept="image/*" class="w-full text-xs">
                        <p class="text-[11px] text-gray-400 mt-1">Falls back to the main image.</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Placement</label>
                        <select name="placement" x-model="form.placement" class="form-input">
                            <option value="slider">Slider (carousel)</option>
                            <option value="popup">Popup (modal, max 1 active)</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" x-model="form.sort_order" min="0" class="form-input">
                    </div>
                </div>

                <div>
                    <label class="form-label">On Tap</label>
                    <select name="link_type" x-model="form.link_type" class="form-input">
                        <option value="none">Do nothing</option>
                        <option value="event">Open an event</option>
                        <option value="url">Open a URL</option>
                    </select>
                </div>
                <div x-show="form.link_type==='event'">
                    <label class="form-label">Event</label>
                    <select name="event_id" x-model="form.event_id" class="form-input">
                        <option value="">Select event</option>
                        @foreach($events as $e)<option value="{{ $e->id }}">{{ $e->title }} ({{ \Illuminate\Support\Carbon::parse($e->start_at)->format('d M') }})</option>@endforeach
                    </select>
                </div>
                <div x-show="form.link_type==='url'">
                    <label class="form-label">URL</label>
                    <input type="url" name="link_url" x-model="form.link_url" placeholder="https://..." class="form-input">
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="form-label">Audience</label>
                        <select name="audience_type" x-model="form.audience_type" class="form-input">
                            <option value="all">Everyone</option>
                            <option value="class">A class</option>
                            <option value="section">A section</option>
                        </select>
                    </div>
                    <div x-show="form.audience_type!=='all'">
                        <label class="form-label">Class</label>
                        <select name="class_id" x-model="form.class_id" class="form-input">
                            <option value="">Select</option>
                            @foreach($classes as $cid => $cname)<option value="{{ $cid }}">{{ $cname }}</option>@endforeach
                        </select>
                    </div>
                    <div x-show="form.audience_type==='section'">
                        <label class="form-label">Section</label>
                        <select name="section_id" x-model="form.section_id" class="form-input">
                            <option value="">Select</option>
                            @foreach($sections as $s)<option value="{{ $s->id }}" x-show="String(form.class_id)===String({{ $s->class_id }})">{{ $s->name }}</option>@endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div><label class="form-label">Start Date</label><input type="date" name="start_date" x-model="form.start_date" class="form-input"></div>
                    <div><label class="form-label">End Date</label><input type="date" name="end_date" x-model="form.end_date" class="form-input"></div>
                </div>

                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="form-check"> Active
                </label>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="show=false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm" x-text="editing ? 'Update' : 'Add'"></button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function bannerManager() {
        return {
            show: false,
            editing: false,
            form: this.blank(),
            blank() {
                return { id:'', title:'', placement:'slider', link_type:'none', event_id:'', link_url:'', audience_type:'all', class_id:'', section_id:'', sort_order:0, start_date:'', end_date:'', is_active:true };
            },
            openCreate() { this.editing=false; this.form=this.blank(); this.show=true; },
            openEdit(b) {
                this.editing=true;
                this.form={ id:b.id, title:b.title||'', placement:b.placement, link_type:b.link_type, event_id:b.event_id||'', link_url:b.link_url||'', audience_type:b.audience_type, class_id:b.class_id||'', section_id:b.section_id||'', sort_order:b.sort_order||0, start_date:b.start_date? b.start_date.substring(0,10):'', end_date:b.end_date? b.end_date.substring(0,10):'', is_active:!!b.is_active };
                this.show=true;
            },
        };
    }
</script>
@endpush
@endsection
