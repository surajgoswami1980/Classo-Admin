@extends('layouts.app')

@section('title', 'Transport')
@section('page-title', 'Transport Management')

@section('content')
<div x-data="{
        tab: 'routes', showRouteModal: false, showVehicleModal: false, stopsForRoute: null,
        showAssignModal: false, assignRouteId: '',
        stopsByRoute: {{ $routes->mapWithKeys(fn($r) => [$r->id => \Illuminate\Support\Facades\DB::table('transport_stops')->where('route_id', $r->id)->orderBy('sequence_order')->get(['id','name'])])->toJson() }},
     }" class="space-y-6">
    <div class="border-b border-gray-200">
        <nav class="flex gap-6 -mb-px">
            <button @click="tab = 'routes'" :class="tab === 'routes' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-3 px-1 border-b-2 text-sm font-medium transition">Routes</button>
            <button @click="tab = 'vehicles'" :class="tab === 'vehicles' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="py-3 px-1 border-b-2 text-sm font-medium transition">Vehicles</button>
        </nav>
    </div>

    <!-- ROUTES TAB -->
    <div x-show="tab === 'routes'" class="space-y-4">
        <div class="flex justify-end">
            <button @click="showRouteModal = true" class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                New Route
            </button>
        </div>

        <div class="bg-white rounded-xl border overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Route</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Vehicle</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Driver</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600">Stops</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600">Students</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($routes as $route)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $route->name }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            @if($route->vehicle_number)
                                {{ $route->vehicle_number }} <span class="text-xs text-gray-400">(cap. {{ $route->capacity }})</span>
                            @else
                                <span class="text-gray-400">Unassigned</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $route->driver_name ?? '—' }} @if($route->driver_phone) <span class="text-xs text-gray-400">{{ $route->driver_phone }}</span> @endif</td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $route->stop_count }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $route->capacity && $route->student_count >= $route->capacity ? 'text-red-700 bg-red-50' : 'text-gray-700 bg-gray-100' }}">
                                {{ $route->student_count }}{{ $route->capacity ? '/'.$route->capacity : '' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button @click="stopsForRoute = stopsForRoute === {{ $route->id }} ? null : {{ $route->id }}" class="text-xs font-medium text-blue-600 hover:underline mr-3">Stops</button>
                            <button @click="assignRouteId = '{{ $route->id }}'; showAssignModal = true" class="text-xs font-medium text-teal-600 hover:underline">Assign Student</button>
                        </td>
                    </tr>
                    <tr x-show="stopsForRoute === {{ $route->id }}" x-cloak>
                        <td colspan="6" class="px-6 py-4 bg-gray-50">
                            <div class="flex items-start justify-between">
                                <div class="space-y-1">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Stops</p>
                                    @forelse($route->stop_count ? \Illuminate\Support\Facades\DB::table('transport_stops')->where('route_id', $route->id)->orderBy('sequence_order')->get() : [] as $stop)
                                        <div class="flex items-center gap-3 text-sm text-gray-700">
                                            <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 text-xs flex items-center justify-center">{{ $stop->sequence_order }}</span>
                                            {{ $stop->name }}
                                            <span class="text-xs text-gray-400">{{ $stop->pickup_time }} / {{ $stop->drop_time }}</span>
                                            <form method="POST" action="{{ panel_route('transport.stops.destroy', $stop->id) }}" onsubmit="return confirm('Remove this stop?')">@csrf @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:underline text-xs">Remove</button>
                                            </form>
                                        </div>
                                    @empty
                                        <p class="text-sm text-gray-400">No stops yet.</p>
                                    @endforelse
                                </div>
                                <form method="POST" action="{{ panel_route('transport.stops.store') }}" class="flex items-end gap-2">
                                    @csrf
                                    <input type="hidden" name="route_id" value="{{ $route->id }}">
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Stop name</label>
                                        <input type="text" name="name" required class="border border-gray-300 rounded-lg px-2 py-1.5 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Order</label>
                                        <input type="number" name="sequence_order" min="1" required class="border border-gray-300 rounded-lg px-2 py-1.5 text-sm w-16">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Pickup</label>
                                        <input type="time" name="pickup_time" class="border border-gray-300 rounded-lg px-2 py-1.5 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Drop</label>
                                        <input type="time" name="drop_time" class="border border-gray-300 rounded-lg px-2 py-1.5 text-sm">
                                    </div>
                                    <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Add Stop</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">No routes yet — create one to get started.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- VEHICLES TAB -->
    <div x-show="tab === 'vehicles'" x-cloak class="space-y-4">
        <div class="flex justify-end">
            <button @click="showVehicleModal = true" class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Add Vehicle
            </button>
        </div>
        <div class="bg-white rounded-xl border overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Vehicle No.</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Type</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600">Capacity</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Driver</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Conductor</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Insurance Expiry</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Fitness Expiry</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($vehicles as $vehicle)
                    @php
                        $insuranceSoon = $vehicle->insurance_expiry && \Carbon\Carbon::parse($vehicle->insurance_expiry)->diffInDays(now(), false) > -30;
                        $fitnessSoon = $vehicle->fitness_expiry && \Carbon\Carbon::parse($vehicle->fitness_expiry)->diffInDays(now(), false) > -30;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $vehicle->vehicle_number }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ ucfirst($vehicle->vehicle_type) }}</td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $vehicle->capacity }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $vehicle->driver_name ?? '—' }} @if($vehicle->driver_phone) <span class="text-xs text-gray-400">{{ $vehicle->driver_phone }}</span> @endif</td>
                        <td class="px-4 py-3 text-gray-600">{{ $vehicle->conductor_name ?? '—' }} @if($vehicle->conductor_phone) <span class="text-xs text-gray-400">{{ $vehicle->conductor_phone }}</span> @endif</td>
                        <td class="px-4 py-3 {{ $insuranceSoon ? 'text-red-600 font-medium' : 'text-gray-600' }}">{{ $vehicle->insurance_expiry ?? '—' }} @if($insuranceSoon) <span class="text-xs">(expiring soon)</span> @endif</td>
                        <td class="px-4 py-3 {{ $fitnessSoon ? 'text-red-600 font-medium' : 'text-gray-600' }}">{{ $vehicle->fitness_expiry ?? '—' }} @if($fitnessSoon) <span class="text-xs">(expiring soon)</span> @endif</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">No vehicles yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- New Route Modal -->
    <div x-show="showRouteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" @click.self="showRouteModal = false">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6" @click.stop>
            <h3 class="text-base font-semibold text-gray-900 mb-4">New Route</h3>
            <form method="POST" action="{{ panel_route('transport.routes.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Route Name</label>
                    <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Vehicle</label>
                    <select name="vehicle_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">Unassigned</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}">{{ $vehicle->vehicle_number }} (cap. {{ $vehicle->capacity }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Driver Name</label>
                        <input type="text" name="driver_name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Driver Phone</label>
                        <input type="text" name="driver_phone" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showRouteModal = false" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Create</button>
                </div>
            </form>
        </div>
    </div>

    <!-- New Vehicle Modal -->
    <div x-show="showVehicleModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" @click.self="showVehicleModal = false">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6" @click.stop>
            <h3 class="text-base font-semibold text-gray-900 mb-4">Add Vehicle</h3>
            <form method="POST" action="{{ panel_route('transport.vehicles.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Vehicle Number</label>
                        <input type="text" name="vehicle_number" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Capacity</label>
                        <input type="number" name="capacity" min="1" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                    <select name="vehicle_type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="bus">Bus</option>
                        <option value="van">Van</option>
                        <option value="auto">Auto</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Driver Name</label>
                        <input type="text" name="driver_name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Driver Phone</label>
                        <input type="text" name="driver_phone" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Conductor Name</label>
                        <input type="text" name="conductor_name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Conductor Phone</label>
                        <input type="text" name="conductor_phone" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Insurance Expiry</label>
                        <input type="date" name="insurance_expiry" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Fitness Expiry</label>
                        <input type="date" name="fitness_expiry" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showVehicleModal = false" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Add</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Assign Student Modal -->
    <div x-show="showAssignModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" @click.self="showAssignModal = false">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6" @click.stop>
            <h3 class="text-base font-semibold text-gray-900 mb-4">Assign Student to Route</h3>
            <form method="POST" action="{{ panel_route('transport.assign') }}">
                @csrf
                <input type="hidden" name="route_id" :value="assignRouteId">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Student</label>
                        <select name="student_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="">Select student...</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->class_name }}-{{ $student->section_name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Stop</label>
                        <select name="stop_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="">Select stop...</option>
                            <template x-for="stop in (stopsByRoute[assignRouteId] || [])" :key="stop.id">
                                <option :value="stop.id" x-text="stop.name"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-5">
                    <button type="button" @click="showAssignModal = false" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
