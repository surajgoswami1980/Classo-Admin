@extends('layouts.app')

@section('title', 'Select a school')
@section('page-title', 'Select a school')

@section('content')
@php
    $isSuper = auth()->user()->hasRole('super-admin');
    $schools = $isSuper
        ? \App\Models\School::active()->orderBy('name')->limit(200)->get(['id', 'name', 'code', 'city'])
        : collect();
    // Turn the controller message ("… before managing the store.") into a short sentence.
    $reason = trim(preg_replace('/^Select a school \(use "Switch School"\)\s*/', '', $message ?? ''), ' .');
@endphp

<div class="mx-auto max-w-2xl" x-data="{ q: '' }">
    <div class="mc-card overflow-hidden">
        <div class="flex flex-col items-center px-6 pb-6 pt-8 text-center">
            <span class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-50 text-primary-600">
                @include('components.icons.building', ['class' => 'w-7 h-7'])
            </span>
            <h2 class="text-lg font-bold text-gray-900">Choose a school to continue</h2>
            <p class="mt-1.5 max-w-md text-sm text-gray-500">
                This page works with one school's data{{ $reason ? ' — select the school ' . $reason . '.' : '.' }}
                @if($isSuper) You'll come straight back here after choosing. @endif
            </p>
        </div>

        @if($isSuper)
            <div class="border-t border-gray-100 px-6 py-4">
                <label for="school-filter" class="sr-only">Search schools</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">@include('components.icons.magnifying-glass', ['class' => 'w-4 h-4'])</span>
                    <input id="school-filter" x-model="q" type="search" autofocus placeholder="Search by name, code or city…" class="form-input pl-9">
                </div>
            </div>

            @if($schools->count())
                <ul class="mc-scroll max-h-[420px] divide-y divide-gray-100 overflow-y-auto border-t border-gray-100">
                    @foreach($schools as $s)
                        <li x-show="!q || @js(mb_strtolower($s->name . ' ' . $s->code . ' ' . $s->city)).includes(q.toLowerCase())">
                            <form method="POST" action="{{ route('admin.schools.switch', $s) }}">
                                @csrf
                                <input type="hidden" name="return_to" value="{{ $returnTo }}">
                                <button type="submit" class="group flex w-full items-center gap-3 px-6 py-3 text-left transition hover:bg-primary-50/50 focus-visible:bg-primary-50/50 focus-visible:outline-none">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-sm font-bold text-primary-700">{{ mb_strtoupper(mb_substr($s->name, 0, 1)) }}</span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-semibold text-gray-900">{{ $s->name }}</span>
                                        <span class="block truncate text-xs text-gray-500">{{ $s->code }}{{ $s->city ? ' · ' . $s->city : '' }}</span>
                                    </span>
                                    <span class="text-xs font-semibold text-primary-600 opacity-0 transition group-hover:opacity-100 group-focus-visible:opacity-100">Open →</span>
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @else
                <x-empty-state icon="building" title="No active schools yet" message="Onboard a school first, then come back to this page.">
                    <a href="{{ route('admin.schools.create') }}" class="btn btn-primary btn-sm">Onboard school</a>
                </x-empty-state>
            @endif
        @else
            <div class="border-t border-gray-100 px-6 py-5 text-center">
                <p class="text-sm text-gray-500">Your account isn't linked to a school. Please contact your administrator.</p>
            </div>
        @endif

        <div class="flex justify-center border-t border-gray-100 bg-gray-50/60 px-6 py-3">
            <a href="{{ panel_route('dashboard') }}" class="text-sm font-medium text-gray-600 hover:text-primary-700">← Back to dashboard</a>
        </div>
    </div>
</div>
@endsection
