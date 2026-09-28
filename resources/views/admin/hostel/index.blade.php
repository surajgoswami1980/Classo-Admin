@extends('layouts.app')

@section('title', 'Hostel')
@section('page-title', 'Hostel Management')

@section('content')
<div x-data="{
        tab: 'blocks',
        showBlock:false, showRoom:false, showAllocate:false,
        rooms: {{ $rooms->toJson() }},
     }" class="space-y-6">

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Blocks</p><p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['blocks'] }}</p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Rooms</p><p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['rooms'] }}</p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Capacity</p><p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['capacity'] }}</p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Occupied</p><p class="text-2xl font-bold text-teal-600 mt-1">{{ $stats['occupied'] }}</p></div>
    </div>

    <div class="border-b border-gray-200">
        <nav class="flex gap-6 -mb-px">
            <button @click="tab='blocks'" :class="tab==='blocks' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500'" class="py-3 px-1 border-b-2 text-sm font-medium">Blocks</button>
            <button @click="tab='rooms'" :class="tab==='rooms' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500'" class="py-3 px-1 border-b-2 text-sm font-medium">Rooms</button>
            <button @click="tab='allocations'" :class="tab==='allocations' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500'" class="py-3 px-1 border-b-2 text-sm font-medium">Allocations</button>
        </nav>
    </div>

    {{-- BLOCKS --}}
    <div x-show="tab==='blocks'" class="space-y-4">
        <div class="flex justify-end"><button @click="showBlock=true" class="px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">+ New Block</button></div>
        <div class="bg-white rounded-xl border overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b"><tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Block</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Type</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Warden</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Rooms</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Occupied</th>
                </tr></thead>
                <tbody class="divide-y">
                    @forelse($blocks as $b)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $b->name }}</td>
                        <td class="px-4 py-3 text-gray-600 capitalize">{{ $b->type }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $b->warden_name ?? '—' }} @if($b->warden_phone)<span class="text-xs text-gray-400">{{ $b->warden_phone }}</span>@endif</td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $b->room_count }}</td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $b->occupied }}</td>
                    </tr>
                    @empty<tr><td colspan="5" class="px-4 py-12 text-center text-gray-400">No blocks yet.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ROOMS --}}
    <div x-show="tab==='rooms'" x-cloak class="space-y-4">
        <div class="flex justify-end"><button @click="showRoom=true" class="px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">+ New Room</button></div>
        <div class="bg-white rounded-xl border overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b"><tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Room</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Block</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Type</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Occupancy</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Fee/mo</th>
                </tr></thead>
                <tbody class="divide-y">
                    @forelse($rooms as $r)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $r->room_number }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $r->block_name }}</td>
                        <td class="px-4 py-3 text-gray-600 capitalize">{{ $r->room_type }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $r->occupied >= $r->capacity ? 'text-red-700 bg-red-50' : 'text-green-700 bg-green-50' }}">{{ $r->occupied }}/{{ $r->capacity }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-gray-600">₹{{ number_format($r->fee_per_month) }}</td>
                    </tr>
                    @empty<tr><td colspan="5" class="px-4 py-12 text-center text-gray-400">No rooms yet.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ALLOCATIONS --}}
    <div x-show="tab==='allocations'" x-cloak class="space-y-4">
        <div class="flex justify-end"><button @click="showAllocate=true" class="px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">+ Allocate Student</button></div>
        <div class="bg-white rounded-xl border overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b"><tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Student</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Block / Room</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">From</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                </tr></thead>
                <tbody class="divide-y">
                    @forelse($allocations as $a)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $a->student_name }} @if($a->roll_number)<span class="text-xs text-gray-400">Roll {{ $a->roll_number }}</span>@endif</td>
                        <td class="px-4 py-3 text-gray-600">{{ $a->block_name }} · {{ $a->room_number }}</td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ \Carbon\Carbon::parse($a->allocated_from)->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ panel_route('hostel.vacate', $a->id) }}" class="inline" onsubmit="return confirm('Vacate this allocation?')">@csrf @method('DELETE')
                                <button type="submit" class="text-xs font-medium text-red-500 hover:underline">Vacate</button>
                            </form>
                        </td>
                    </tr>
                    @empty<tr><td colspan="4" class="px-4 py-12 text-center text-gray-400">No active allocations.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Block Modal --}}
    <div x-show="showBlock" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" @click.self="showBlock=false">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6" @click.stop>
            <h3 class="text-base font-semibold text-gray-900 mb-4">New Block</h3>
            <form method="POST" action="{{ panel_route('hostel.blocks.store') }}" class="space-y-4">@csrf
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Name *</label><input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                    <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"><option value="boys">Boys</option><option value="girls">Girls</option><option value="mixed">Mixed</option></select></div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Warden</label><input type="text" name="warden_name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Warden Phone</label><input type="text" name="warden_phone" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                </div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Address</label><textarea name="address" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></textarea></div>
                <div class="flex justify-end gap-2"><button type="button" @click="showBlock=false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancel</button><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Create</button></div>
            </form>
        </div>
    </div>

    {{-- Room Modal --}}
    <div x-show="showRoom" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" @click.self="showRoom=false">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6" @click.stop>
            <h3 class="text-base font-semibold text-gray-900 mb-4">New Room</h3>
            <form method="POST" action="{{ panel_route('hostel.rooms.store') }}" class="space-y-4">@csrf
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Block *</label>
                    <select name="block_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Select block</option>
                        @foreach($blocks as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
                    </select></div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Room No. *</label><input type="text" name="room_number" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Capacity *</label><input type="number" name="capacity" min="1" value="2" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                        <select name="room_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"><option value="single">Single</option><option value="double" selected>Double</option><option value="triple">Triple</option><option value="dormitory">Dormitory</option></select></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Fee / month</label><input type="number" name="fee_per_month" min="0" step="0.01" value="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                </div>
                <div class="flex justify-end gap-2"><button type="button" @click="showRoom=false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancel</button><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Add</button></div>
            </form>
        </div>
    </div>

    {{-- Allocate Modal --}}
    <div x-show="showAllocate" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" @click.self="showAllocate=false">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6" @click.stop>
            <h3 class="text-base font-semibold text-gray-900 mb-4">Allocate Student</h3>
            <form method="POST" action="{{ panel_route('hostel.allocate') }}" class="space-y-4">@csrf
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Student *</label>
                    <select name="student_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Select student</option>
                        @foreach($students as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->class_name }}-{{ $s->section_name }})</option>@endforeach
                    </select></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Room *</label>
                    <select name="room_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Select room</option>
                        <template x-for="r in rooms" :key="r.id">
                            <option :value="r.id" x-text="r.block_name + ' · ' + r.room_number + ' (' + r.occupied + '/' + r.capacity + ')'" :disabled="r.occupied >= r.capacity"></option>
                        </template>
                    </select></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Allocated From</label><input type="date" name="allocated_from" value="{{ now()->toDateString() }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                <div class="flex justify-end gap-2"><button type="button" @click="showAllocate=false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancel</button><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Allocate</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
