@extends('layouts.app')

@section('title', 'Timetable')
@section('page-title', 'Timetable')

@section('content')
@php
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    $grouped = $timetable->groupBy('day_of_week');
    $selClass = $filters['class_id'] ?? '';
    $selSection = $filters['section_id'] ?? '';
    $storeRoute = route(request()->routeIs('admin.*') ? 'admin.timetable.store' : 'user.timetable.store');
    $openOnLoad = $errors->any();
@endphp

<div class="space-y-6" x-data="{ addOpen: {{ $openOnLoad ? 'true' : 'false' }}, cls: @js((string) old('class_id', $selClass)) }">
    <x-page-header title="Timetable" subtitle="Weekly periods by class and section">
        <button type="button" class="btn btn-primary" @click="addOpen = true">
            @include('components.icons.plus', ['class' => 'w-4 h-4']) Add period
        </button>
    </x-page-header>

    <!-- Filters -->
    <form method="GET" class="mc-card p-4" x-data="{ fc: @js((string) $selClass) }">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:items-end">
            <div>
                <label for="f-class" class="form-label">Class</label>
                <select id="f-class" name="class_id" class="form-input" x-model="fc" @change="$el.form.submit()">
                    <option value="">Select class</option>
                    @foreach($classes as $id => $name)
                        <option value="{{ $id }}" @selected($selClass == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="f-section" class="form-label">Section</label>
                <select id="f-section" name="section_id" class="form-input">
                    <option value="">Select section</option>
                    @foreach($sections as $sec)
                        @if($selClass == $sec->class_id)
                            <option value="{{ $sec->id }}" @selected($selSection == $sec->id)>{{ $sec->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-secondary w-full sm:w-auto">Load timetable</button>
            </div>
        </div>
    </form>

    @if($timetable->count() > 0)
        <!-- Week grid -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach($days as $day)
                @php $periods = $grouped->has($day) ? $grouped[$day]->sortBy('period_number') : collect(); @endphp
                <section class="mc-card overflow-hidden" aria-labelledby="day-{{ $day }}">
                    <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50/70 px-4 py-3">
                        <h3 id="day-{{ $day }}" class="text-sm font-semibold capitalize text-gray-900">{{ $day }}</h3>
                        <span class="badge {{ $periods->count() ? 'badge-primary' : 'badge-neutral' }}">{{ $periods->count() }} {{ \Illuminate\Support\Str::plural('period', $periods->count()) }}</span>
                    </div>
                    @if($periods->count())
                        <ul class="divide-y divide-gray-100">
                            @foreach($periods as $period)
                                <li class="group flex items-center gap-3 px-4 py-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-xs font-bold text-primary-700">P{{ $period->period_number }}</span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-gray-900">{{ $period->subject_name ?? '—' }}</p>
                                        <p class="truncate text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($period->start_time)->format('h:i A') }} – {{ \Carbon\Carbon::parse($period->end_time)->format('h:i A') }}
                                            · {{ $period->teacher_name ?? '—' }}
                                        </p>
                                    </div>
                                    <form method="POST" action="{{ route(request()->routeIs('admin.*') ? 'admin.timetable.destroy' : 'user.timetable.destroy', $period->id) }}"
                                          data-confirm="This action can't be undone." data-confirm-title="Remove this period?" data-confirm-ok="Remove" data-confirm-tone="danger">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-icon btn-icon-danger" title="Remove period" aria-label="Remove period {{ $period->period_number }} on {{ $day }}">
                                            @include('components.icons.trash', ['class' => 'w-4 h-4'])
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="px-4 py-6 text-center text-sm text-gray-400">No periods</p>
                    @endif
                </section>
            @endforeach
        </div>
    @elseif(!empty($selClass) && !empty($selSection))
        <div class="mc-card">
            <x-empty-state icon="clock" title="No periods for this class yet" message="Add the first period to start building the weekly timetable.">
                <button type="button" class="btn btn-primary btn-sm" @click="addOpen = true">Add period</button>
            </x-empty-state>
        </div>
    @else
        <div class="mc-card">
            <x-empty-state icon="clock" title="Choose a class and section" message="Select a class and section above to view its weekly timetable." />
        </div>
    @endif

    <!-- Add period modal -->
    <div x-show="addOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="add-period-title" @keydown.escape.window="addOpen = false">
        <div class="modal-backdrop" x-show="addOpen" x-transition.opacity @click="addOpen = false"></div>
        <form method="POST" action="{{ $storeRoute }}" class="modal-panel max-h-[90vh] max-w-lg overflow-y-auto"
              x-show="addOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
              x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 scale-95">
            @csrf
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h3 id="add-period-title" class="text-base font-semibold text-gray-900">Add timetable period</h3>
                <button type="button" class="btn-icon" @click="addOpen = false" aria-label="Close">@include('components.icons.x-mark', ['class' => 'w-5 h-5'])</button>
            </div>
            <div class="grid grid-cols-2 gap-4 px-6 py-5">
                <div>
                    <label for="p-class" class="form-label req">Class</label>
                    <select id="p-class" name="class_id" class="form-input" required x-model="cls">
                        <option value="">Select</option>
                        @foreach($classes as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="p-section" class="form-label req">Section</label>
                    <select id="p-section" name="section_id" class="form-input" required>
                        <option value="">Select</option>
                        @foreach($sections as $sec)
                            <option value="{{ $sec->id }}" data-class="{{ $sec->class_id }}" x-show="!cls || cls == '{{ $sec->class_id }}'"
                                    @selected(old('section_id', $selSection) == $sec->id)>{{ $sec->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2">
                    <label for="p-subject" class="form-label req">Subject</label>
                    <select id="p-subject" name="subject_id" class="form-input" required>
                        <option value="">Select subject</option>
                        @foreach($subjects as $id => $name)
                            <option value="{{ $id }}" @selected(old('subject_id') == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2">
                    <label for="p-teacher" class="form-label req">Teacher</label>
                    <select id="p-teacher" name="teacher_id" class="form-input" required>
                        <option value="">Select teacher</option>
                        @foreach($teachers as $id => $name)
                            <option value="{{ $id }}" @selected(old('teacher_id') == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="p-day" class="form-label req">Day</label>
                    <select id="p-day" name="day_of_week" class="form-input" required>
                        @foreach($days as $d)
                            <option value="{{ $d }}" @selected(old('day_of_week') === $d)>{{ ucfirst($d) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="p-num" class="form-label req">Period #</label>
                    <input id="p-num" type="number" name="period_number" class="form-input" min="1" max="12" required placeholder="1–12" value="{{ old('period_number') }}">
                </div>
                <div>
                    <label for="p-start" class="form-label req">Start time</label>
                    <input id="p-start" type="time" name="start_time" class="form-input" required value="{{ old('start_time') }}">
                </div>
                <div>
                    <label for="p-end" class="form-label req">End time</label>
                    <input id="p-end" type="time" name="end_time" class="form-input" required value="{{ old('end_time') }}">
                </div>
                <div class="col-span-2">
                    <label for="p-session" class="form-label req">Academic session</label>
                    <select id="p-session" name="academic_session_id" class="form-input" required>
                        @foreach($sessions as $id => $name)
                            <option value="{{ $id }}" @selected(old('academic_session_id') == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-2 border-t border-gray-100 bg-gray-50/60 px-6 py-4">
                <button type="button" class="btn btn-secondary" @click="addOpen = false">Cancel</button>
                <button type="submit" class="btn btn-primary">Add period</button>
            </div>
        </form>
    </div>
</div>
@endsection
