@extends('layouts.app')
@section('title', 'Holidays')
@section('page-title', 'Holidays & Weekly Offs')

@php
    $card = 'rounded-2xl border border-gray-200/80 bg-white shadow-[0_1px_2px_rgba(16,24,40,.04)]';
    $input = 'w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 transition hover:border-gray-300 focus:outline-none focus:ring-4 focus:ring-primary-500/15 focus:border-primary-500';
    $label = 'mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500';
    $typeColor = [
        'holiday' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        'vacation' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'exam_break' => 'bg-violet-50 text-violet-700 ring-violet-600/20',
        'other' => 'bg-gray-100 text-gray-700 ring-gray-500/20',
    ];
    $cellColor = ['holiday' => 'bg-rose-500', 'vacation' => 'bg-amber-500', 'exam_break' => 'bg-violet-500', 'other' => 'bg-gray-500'];
    $audience = ['all' => 'Everyone', 'students' => 'Students only', 'staff' => 'Staff only'];
    $dows = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    $range = fn ($h) => $h->start_date === $h->end_date
        ? \Carbon\Carbon::parse($h->start_date)->format('D, d M Y')
        : \Carbon\Carbon::parse($h->start_date)->format('d M') . ' – ' . \Carbon\Carbon::parse($h->end_date)->format('d M Y');
    $len = fn ($h) => \Carbon\Carbon::parse($h->start_date)->diffInDays(\Carbon\Carbon::parse($h->end_date)) + 1;
    $prefix = panel_prefix();
@endphp

@section('content')
<div class="mx-auto max-w-6xl space-y-6"
     x-data="{
        open: {{ $errors->any() ? 'true' : 'false' }},
        editing: null,
        form: { title: '{{ old('title') }}', description: '', start_date: '{{ old('start_date') }}', end_date: '{{ old('end_date') }}', type: '{{ old('type', 'holiday') }}', applies_to: '{{ old('applies_to', 'all') }}' },
        add(date) { this.editing = null; this.form = { title: '', description: '', start_date: date || '', end_date: '', type: 'holiday', applies_to: 'all' }; this.open = true; },
        edit(h) { this.editing = h.id; this.form = { title: h.title, description: h.description || '', start_date: h.start_date, end_date: h.end_date, type: h.type, applies_to: h.applies_to }; this.open = true; },
        get action() { return this.editing ? '{{ url($prefix . '/holidays') }}/' + this.editing : '{{ route($prefix . '.holidays.store') }}' },
     }">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Holidays</h2>
            <p class="mt-1 text-sm text-gray-500">Shown on every student, parent and staff calendar. Attendance can't be marked on these days and they don't lower anyone's percentage.</p>
        </div>
        <button type="button" @click="add()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary-600/20 transition hover:bg-primary-700 active:scale-[.98]">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Add holiday
        </button>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Calendar --}}
        <section class="{{ $card }} overflow-hidden lg:col-span-2">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                <a href="{{ route($prefix . '.holidays.index', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}" class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100" aria-label="Previous month">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                </a>
                <h3 class="text-base font-semibold text-gray-900">{{ $month->format('F Y') }}</h3>
                <a href="{{ route($prefix . '.holidays.index', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}" class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100" aria-label="Next month">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                </a>
            </div>
            <div class="p-4">
                <div class="mb-2 grid grid-cols-7 text-center text-[11px] font-semibold uppercase tracking-wide text-gray-400">
                    @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $d)<div class="py-1">{{ $d }}</div>@endforeach
                </div>
                <div class="grid grid-cols-7 gap-1.5">
                    @for($i = 1; $i < $days[0]['dow']; $i++)<div></div>@endfor
                    @foreach($days as $d)
                        @php $h = $d['holiday']; @endphp
                        <button type="button"
                                @if($h) @click='edit(@json($h))' @else @click="add('{{ $d['date'] }}')" @endif
                                title="{{ $h ? $h->title : ($d['weekly_off'] ? 'Weekly off' : 'Add holiday on this day') }}"
                                class="group relative flex aspect-square flex-col items-center justify-center rounded-xl border text-sm transition
                                       {{ $h ? 'border-transparent ' . $cellColor[$h->type] . ' text-white shadow-sm hover:brightness-95' : ($d['weekly_off'] ? 'border-dashed border-gray-200 bg-gray-50 text-gray-400' : 'border-gray-100 text-gray-700 hover:border-primary-300 hover:bg-primary-50') }}
                                       {{ $d['today'] && !$h ? 'ring-2 ring-primary-500' : '' }}">
                            <span class="font-semibold">{{ $d['day'] }}</span>
                            @if($h)
                                <span class="mt-0.5 hidden w-full truncate px-1 text-[10px] font-medium sm:block">{{ $h->title }}</span>
                            @elseif($d['weekly_off'])
                                <span class="mt-0.5 hidden text-[10px] sm:block">Off</span>
                            @endif
                        </button>
                    @endforeach
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-4 border-t border-gray-100 pt-3 text-xs text-gray-500">
                    @foreach($types as $k => $t)
                        <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full {{ $cellColor[$k] }}"></span>{{ $t }}</span>
                    @endforeach
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full border border-dashed border-gray-300 bg-gray-50"></span>Weekly off</span>
                    <span class="ml-auto text-gray-400">Tap a day to add · tap a holiday to edit</span>
                </div>
            </div>
        </section>

        {{-- Weekly offs + stats --}}
        <div class="space-y-6">
            <form method="POST" action="{{ route($prefix . '.holidays.weekly-off') }}" class="{{ $card }} p-5"
                  x-data="{ off: @js($rules['weekly_off']), sat: @js($rules['saturday_off_weeks']) }">
                @csrf
                <h3 class="font-semibold text-gray-900">Weekly off days</h3>
                <p class="mt-0.5 text-xs text-gray-500">Repeat every week. Students and staff see them as off days.</p>
                <div class="mt-4 grid grid-cols-7 gap-1.5">
                    @foreach($dows as $i => $d)
                        <label class="cursor-pointer">
                            <input type="checkbox" name="weekly_off[]" value="{{ $i }}" x-model.number="off" class="peer sr-only">
                            <span class="flex h-10 items-center justify-center rounded-lg border-2 border-gray-200 text-xs font-semibold text-gray-600 transition peer-checked:border-rose-400 peer-checked:bg-rose-50 peer-checked:text-rose-700 peer-focus-visible:ring-4 peer-focus-visible:ring-primary-500/20">{{ $d }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="mt-4" x-show="!off.includes(6)" x-cloak>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Also off on these Saturdays</p>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach([1 => '1st', 2 => '2nd', 3 => '3rd', 4 => '4th', 5 => '5th'] as $n => $l)
                            <label class="cursor-pointer">
                                <input type="checkbox" name="saturday_off_weeks[]" value="{{ $n }}" x-model.number="sat" class="peer sr-only">
                                <span class="block rounded-lg border-2 border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 transition peer-checked:border-rose-400 peer-checked:bg-rose-50 peer-checked:text-rose-700">{{ $l }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <button class="mt-5 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Save weekly offs</button>
            </form>

            <div class="grid grid-cols-2 gap-3">
                <div class="{{ $card }} p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">This year</p>
                    <p class="mt-1 text-xl font-bold text-gray-900">{{ $yearCount }} <span class="text-sm font-medium text-gray-400">days</span></p>
                </div>
                <div class="{{ $card }} p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Upcoming</p>
                    <p class="mt-1 text-xl font-bold text-gray-900">{{ $upcoming->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Lists --}}
    <section class="{{ $card }} overflow-hidden" x-data="{ show: 'upcoming' }">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3">
            <div class="flex gap-1 rounded-xl bg-gray-100 p-1 text-sm">
                <button type="button" @click="show = 'upcoming'" :class="show === 'upcoming' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500'" class="rounded-lg px-3 py-1.5 font-medium transition">Upcoming ({{ $upcoming->count() }})</button>
                <button type="button" @click="show = 'past'" :class="show === 'past' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500'" class="rounded-lg px-3 py-1.5 font-medium transition">Past ({{ $past->count() }})</button>
            </div>
        </div>
        @foreach(['upcoming' => $upcoming, 'past' => $past] as $key => $list)
            <div x-show="show === '{{ $key }}'" @if($key === 'past') x-cloak @endif>
                @forelse($list as $h)
                    <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 last:border-0 sm:flex-row sm:items-center">
                        <div class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-xl {{ $key === 'past' ? 'bg-gray-100 text-gray-500' : 'bg-rose-50 text-rose-700' }}">
                            <span class="text-[10px] font-semibold uppercase leading-none">{{ \Carbon\Carbon::parse($h->start_date)->format('M') }}</span>
                            <span class="text-lg font-bold leading-tight">{{ \Carbon\Carbon::parse($h->start_date)->format('d') }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-semibold text-gray-900">{{ $h->title }}</p>
                                <span class="rounded-full px-2 py-0.5 text-[11px] font-medium ring-1 ring-inset {{ $typeColor[$h->type] ?? $typeColor['other'] }}">{{ $types[$h->type] ?? ucfirst($h->type) }}</span>
                                @if($h->applies_to !== 'all')<span class="rounded-full bg-sky-50 px-2 py-0.5 text-[11px] font-medium text-sky-700 ring-1 ring-inset ring-sky-600/20">{{ $audience[$h->applies_to] }}</span>@endif
                            </div>
                            <p class="mt-0.5 text-sm text-gray-500">{{ $range($h) }} · {{ $len($h) }} {{ $len($h) === 1 ? 'day' : 'days' }}{{ $h->description ? ' · ' . $h->description : '' }}</p>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" @click='edit(@json($h))' class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">Edit</button>
                            <form method="POST" action="{{ route($prefix . '.holidays.destroy', $h->id) }}" onsubmit="return confirm('Delete {{ addslashes($h->title) }}?')">
                                @csrf @method('DELETE')
                                <button class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:border-red-200 hover:bg-red-50">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <p class="font-semibold text-gray-900">{{ $key === 'upcoming' ? 'No upcoming holidays' : 'No past holidays' }}</p>
                        @if($key === 'upcoming')<p class="mt-1 text-sm text-gray-500">Add Diwali, Holi, summer vacation… so everyone sees them on their calendar.</p>@endif
                    </div>
                @endforelse
            </div>
        @endforeach
    </section>

    {{-- Add / edit drawer --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-labelledby="hol-title" @keydown.escape.window="open = false">
        <div x-show="open" x-transition.opacity class="absolute inset-0 bg-gray-900/40 backdrop-blur-[2px]" @click="open = false"></div>
        <div x-show="open" x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
             x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
             class="absolute inset-y-0 right-0 flex w-full max-w-md flex-col bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h3 id="hol-title" class="text-lg font-bold text-gray-900" x-text="editing ? 'Edit holiday' : 'Add holiday'"></h3>
                <button type="button" @click="open = false" class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100" aria-label="Close">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" :action="action" class="flex min-h-0 flex-1 flex-col">
                @csrf
                <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>
                <div class="flex-1 space-y-5 overflow-y-auto px-6 py-5">
                    <div>
                        <label for="h-title" class="{{ $label }}">Name</label>
                        <input id="h-title" name="title" x-model="form.title" required maxlength="150" placeholder="Diwali" class="{{ $input }}">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="h-start" class="{{ $label }}">From</label>
                            <input id="h-start" type="date" name="start_date" x-model="form.start_date" required class="{{ $input }}">
                        </div>
                        <div>
                            <label for="h-end" class="{{ $label }}">To <span class="normal-case font-normal text-gray-400">(optional)</span></label>
                            <input id="h-end" type="date" name="end_date" x-model="form.end_date" :min="form.start_date" class="{{ $input }}">
                        </div>
                    </div>
                    <div>
                        <span class="{{ $label }}">Type</span>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($types as $k => $t)
                                <label class="cursor-pointer">
                                    <input type="radio" name="type" value="{{ $k }}" x-model="form.type" class="peer sr-only">
                                    <span class="block rounded-xl border-2 border-gray-200 px-3 py-2 text-center text-sm font-medium text-gray-700 transition peer-checked:border-primary-500 peer-checked:bg-primary-50 peer-checked:text-primary-700">{{ $t }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <span class="{{ $label }}">Who is off?</span>
                        <div class="space-y-2">
                            @foreach($audience as $k => $t)
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm transition has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50">
                                    <input type="radio" name="applies_to" value="{{ $k }}" x-model="form.applies_to" class="text-primary-600">
                                    <span class="font-medium text-gray-800">{{ $t }}</span>
                                    @if($k === 'students')<span class="text-xs text-gray-500">— staff still work (e.g. PTM day)</span>@endif
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label for="h-desc" class="{{ $label }}">Note <span class="normal-case font-normal text-gray-400">(optional)</span></label>
                        <textarea id="h-desc" name="description" x-model="form.description" rows="2" maxlength="500" placeholder="School reopens on Monday" class="{{ $input }}"></textarea>
                    </div>
                    <label x-show="!editing" class="flex items-start gap-3 rounded-xl bg-gray-50 p-3.5 text-sm">
                        <input type="checkbox" name="notify" value="1" checked class="mt-0.5 rounded border-gray-300 text-primary-600">
                        <span><span class="font-medium text-gray-900">Notify everyone</span><span class="block text-xs text-gray-500">Push + in-app message to the selected people</span></span>
                    </label>
                </div>
                <div class="flex gap-3 border-t border-gray-100 bg-gray-50/60 px-6 py-4">
                    <button type="button" @click="open = false" class="flex-1 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="flex-[2] rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-primary-600/20 hover:bg-primary-700" x-text="editing ? 'Save changes' : 'Add holiday'"></button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
