@extends('layouts.app')
@section('title', 'Attendance Settings')
@section('page-title', 'Attendance Settings')

@php
    $card = 'rounded-2xl border border-gray-200/80 bg-white shadow-[0_1px_2px_rgba(16,24,40,.04)]';
    $input = 'w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 transition hover:border-gray-300 focus:outline-none focus:ring-4 focus:ring-primary-500/15 focus:border-primary-500';
    $label = 'mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500';
    $p = panel_prefix();
    $r = $rules;
    $pendingFaces = $faces->where('status', 'pending')->count();
    $tabs = [
        'rules' => ['Rules & timing', null],
        'faces' => ['Face approvals', $pendingFaces ?: null],
        'devices' => ['Linked phones', $devices->count() ?: null],
        'biometric' => ['Fingerprint machines', $machines->count() ?: null],
    ];
    $methodLabel = ['manual' => 'Admin', 'app' => 'App', 'geo' => 'Location', 'face' => 'Face', 'face_geo' => 'Face + location', 'biometric' => 'Fingerprint'];
    $toggle = function (string $name, bool $on, string $title, string $hint, string $model = '') {
        return view('admin.attendance._toggle', compact('name', 'on', 'title', 'hint', 'model'))->render();
    };
@endphp

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
@endpush

@section('content')
<div class="mx-auto max-w-6xl space-y-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Attendance settings</h2>
            <p class="mt-1 text-sm text-gray-500">School timings, late rule and how teachers & staff mark their own attendance.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route($p . '.holidays.index') }}" class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Holidays</a>
            <a href="{{ route($p . '.attendance.staff') }}" class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Staff attendance</a>
        </div>
    </div>

    {{-- Today --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
        @foreach(['geo' => 'Location', 'face_geo' => 'Face + location', 'face' => 'Face', 'biometric' => 'Fingerprint', 'manual' => 'Marked by admin'] as $k => $l)
            <div class="{{ $card }} p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $l }}</p>
                <p class="mt-1 text-xl font-bold text-gray-900">{{ (int) ($todayStats[$k] ?? 0) }}</p>
                <p class="text-xs text-gray-400">today</p>
            </div>
        @endforeach
    </div>
    @if($rejectedToday > 0)
        <div class="flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <svg class="h-5 w-5 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
            {{ $rejectedToday }} check-in {{ $rejectedToday === 1 ? 'attempt was' : 'attempts were' }} rejected today (outside school area, face mismatch, wrong phone…). The full audit is on the Staff attendance page.
        </div>
    @endif

    {{-- Tabs --}}
    <div class="flex gap-1 overflow-x-auto rounded-xl border border-gray-200 bg-white p-1">
        @foreach($tabs as $k => [$l, $badge])
            <a href="{{ route($p . '.attendance.settings', ['tab' => $k]) }}"
               class="inline-flex items-center gap-2 whitespace-nowrap rounded-lg px-4 py-2 text-sm font-medium transition {{ $tab === $k ? 'bg-primary-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' }}">
                {{ $l }}
                @if($badge)<span class="rounded-full px-1.5 text-[11px] font-bold {{ $tab === $k ? 'bg-white/25' : 'bg-amber-100 text-amber-800' }}">{{ $badge }}</span>@endif
            </a>
        @endforeach
    </div>

    {{-- ═══════════ RULES ═══════════ --}}
    @if($tab === 'rules')
    <form method="POST" action="{{ route($p . '.attendance.settings.update') }}" class="space-y-6"
          x-data="{
            selfOn: {{ old('self_checkin.enabled', $r['self_checkin']['enabled']) ? 'true' : 'false' }},
            needLoc: {{ old('self_checkin.require_location', $r['self_checkin']['require_location']) ? 'true' : 'false' }},
            needFace: {{ old('self_checkin.require_face', $r['self_checkin']['require_face']) ? 'true' : 'false' }},
            start: '{{ old('timing.start', $r['timing']['start']) }}',
            grace: {{ (int) old('timing.grace_minutes', $r['timing']['grace_minutes']) }},
            opens: {{ (int) old('timing.checkin_opens_minutes', $r['timing']['checkin_opens_minutes']) }},
            add(t, m) { if (!t) return '—'; let [h, mm] = t.split(':').map(Number); let x = ((h * 60 + mm + m) % 1440 + 1440) % 1440; let H = Math.floor(x / 60), M = x % 60; return (H % 12 || 12) + ':' + String(M).padStart(2, '0') + (H < 12 ? ' AM' : ' PM'); },
          }">
        @csrf @method('PUT')

        {{-- Timing --}}
        <section class="{{ $card }} p-6">
            <div class="grid gap-6 lg:grid-cols-3">
                <div>
                    <h3 class="font-semibold text-gray-900">School timing</h3>
                    <p class="mt-1 text-sm text-gray-500">Used for every teacher and staff member until you change it.</p>
                    <div class="mt-4 rounded-xl bg-primary-50 p-4 text-sm text-primary-900">
                        <p>On time until <strong x-text="add(start, grace)"></strong></p>
                        <p class="mt-1">Check-in opens at <strong x-text="add(start, -opens)"></strong></p>
                    </div>
                </div>
                <div class="grid gap-5 sm:grid-cols-2 lg:col-span-2">
                    <div>
                        <label for="t-start" class="{{ $label }}">School starts</label>
                        <input id="t-start" type="time" name="timing[start]" x-model="start" required class="{{ $input }}">
                    </div>
                    <div>
                        <label for="t-end" class="{{ $label }}">School ends</label>
                        <input id="t-end" type="time" name="timing[end]" value="{{ old('timing.end', $r['timing']['end']) }}" required class="{{ $input }} @error('timing.end') border-red-400 @enderror">
                        @error('timing.end')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="t-grace" class="{{ $label }}">Late allowed (minutes)</label>
                        <input id="t-grace" type="number" min="0" max="180" name="timing[grace_minutes]" x-model.number="grace" required class="{{ $input }}">
                        <p class="mt-1.5 text-xs text-gray-500">After this, the day is marked <strong>Late</strong>.</p>
                    </div>
                    <div>
                        <label for="t-opens" class="{{ $label }}">Check-in opens (minutes before start)</label>
                        <input id="t-opens" type="number" min="0" max="240" name="timing[checkin_opens_minutes]" x-model.number="opens" required class="{{ $input }}">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="t-half" class="{{ $label }}">Half day if later than (minutes) <span class="normal-case font-normal text-gray-400">— 0 = off</span></label>
                        <input id="t-half" type="number" min="0" max="600" name="timing[half_day_after_minutes]" value="{{ old('timing.half_day_after_minutes', $r['timing']['half_day_after_minutes']) }}" class="{{ $input }} sm:max-w-xs">
                        <p class="mt-1.5 text-xs text-gray-500">E.g. 120 → anyone arriving 2+ hours after start gets a half day.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Self check-in --}}
        <section class="{{ $card }} overflow-hidden">
            <label class="flex cursor-pointer items-start justify-between gap-6 p-6">
                <span>
                    <span class="block text-base font-semibold text-gray-900">Teachers & staff mark their own attendance</span>
                    <span class="mt-0.5 block text-sm text-gray-500">From the mobile app. You can still mark or correct anyone's attendance yourself.</span>
                </span>
                <span class="relative mt-1 inline-flex shrink-0 items-center">
                    <input type="hidden" name="self_checkin[enabled]" value="0">
                    <input type="checkbox" name="self_checkin[enabled]" value="1" x-model="selfOn" class="peer sr-only">
                    <span class="h-6 w-11 rounded-full bg-gray-200 transition-colors peer-checked:bg-primary-600 peer-focus-visible:ring-4 peer-focus-visible:ring-primary-500/20"></span>
                    <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                    <span class="sr-only">Enable self check-in</span>
                </span>
            </label>

            <div class="space-y-5 border-t border-gray-100 p-6 transition" :class="selfOn ? '' : 'pointer-events-none opacity-50'">
                <div class="grid gap-3 sm:grid-cols-2">
                    {!! $toggle('self_checkin[require_location]', (bool) old('self_checkin.require_location', $r['self_checkin']['require_location']), 'Must be inside school', 'GPS check against the school location below. Fake-GPS apps are blocked.', 'needLoc') !!}
                    {!! $toggle('self_checkin[require_face]', (bool) old('self_checkin.require_face', $r['self_checkin']['require_face']), 'Face + liveness', 'Blink and turn head, matched with the approved face photo.', 'needFace') !!}
                    {!! $toggle('self_checkin[bind_device]', (bool) old('self_checkin.bind_device', $r['self_checkin']['bind_device']), 'One phone per person', "Stops marking from a friend's phone. Reset in Linked phones.") !!}
                    {!! $toggle('self_checkin[require_checkout]', (bool) old('self_checkin.require_checkout', $r['self_checkin']['require_checkout']), 'Ask to check out', 'Shows a Check-out button and flags early leaving.') !!}
                </div>
                @error('self_checkin.require_face')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                @unless($faceReady)
                    <p x-show="needFace" x-cloak class="rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-900">Face check uses AWS Rekognition. The platform admin must add AWS keys in <strong>Integrations → Email (Amazon SES)</strong>.</p>
                @endunless

                <div x-show="needFace" x-cloak class="sm:max-w-xs">
                    <label for="f-th" class="{{ $label }}">Face match strictness (%)</label>
                    <input id="f-th" type="number" min="80" max="99" name="face[threshold]" value="{{ old('face.threshold', $r['face']['threshold']) }}" class="{{ $input }}">
                    <p class="mt-1.5 text-xs text-gray-500">90 is a good balance. Higher = stricter.</p>
                </div>
            </div>
        </section>

        {{-- Location --}}
        <section class="{{ $card }} overflow-hidden"
                 x-data="geoPicker({{ json_encode(old('location.lat', $r['location']['lat'])) }}, {{ json_encode(old('location.lng', $r['location']['lng'])) }}, {{ (int) old('location.radius_m', $r['location']['radius_m']) }})"
                 x-init="init()">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4">
                <div>
                    <h3 class="font-semibold text-gray-900">School location</h3>
                    <p class="text-sm text-gray-500">Drag the pin to the school gate. Staff must be inside the circle.</p>
                </div>
                <button type="button" @click="locate()" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                    <span x-text="locating ? 'Locating…' : 'Use my current location'"></span>
                </button>
            </div>
            <div x-ref="map" class="h-80 w-full bg-gray-100" role="application" aria-label="Map to set school location"></div>
            <div class="grid gap-5 p-6 sm:grid-cols-4">
                <div>
                    <label for="l-lat" class="{{ $label }}">Latitude</label>
                    <input id="l-lat" name="location[lat]" x-model="lat" @change="move()" inputmode="decimal" placeholder="28.6139" class="{{ $input }} font-mono @error('location.lat') border-red-400 @enderror">
                </div>
                <div>
                    <label for="l-lng" class="{{ $label }}">Longitude</label>
                    <input id="l-lng" name="location[lng]" x-model="lng" @change="move()" inputmode="decimal" placeholder="77.2090" class="{{ $input }} font-mono">
                </div>
                <div>
                    <label for="l-rad" class="{{ $label }}">Allowed radius (m)</label>
                    <input id="l-rad" type="number" min="30" max="5000" name="location[radius_m]" x-model.number="radius" @input="circle && circle.setRadius(radius || 0)" class="{{ $input }}">
                </div>
                <div>
                    <label for="l-acc" class="{{ $label }}">Max GPS error (m)</label>
                    <input id="l-acc" type="number" min="10" max="500" name="location[max_accuracy_m]" value="{{ old('location.max_accuracy_m', $r['location']['max_accuracy_m']) }}" class="{{ $input }}">
                </div>
                @error('location.lat')<p class="text-sm text-red-600 sm:col-span-4">{{ $message }}</p>@enderror
                <p class="text-xs text-gray-500 sm:col-span-4">Tip: 100–200 m covers most campuses. Set the radius a little bigger than the campus so phones with weak GPS near the edge still pass.</p>
            </div>
        </section>

        <div class="sticky bottom-4 z-10 flex justify-end">
            <button type="submit" class="rounded-xl bg-primary-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-primary-600/25 transition hover:bg-primary-700 active:scale-[.98]">Save settings</button>
        </div>
    </form>
    @endif

    {{-- ═══════════ FACES ═══════════ --}}
    @if($tab === 'faces')
    <section class="{{ $card }} overflow-hidden">
        <div class="border-b border-gray-100 px-6 py-4">
            <h3 class="font-semibold text-gray-900">Face photos</h3>
            <p class="text-sm text-gray-500">Staff register a selfie once from the app. Approve only clear, front-facing photos of the right person.</p>
        </div>
        @forelse($faces as $f)
            <div class="flex flex-col gap-4 border-b border-gray-100 p-5 last:border-0 sm:flex-row sm:items-center">
                <img src="{{ route($p . '.attendance.settings.faces.photo', $f->id) }}" alt="Face photo of {{ $f->name }}" loading="lazy" class="h-20 w-20 shrink-0 rounded-2xl object-cover ring-1 ring-gray-200">
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-gray-900">{{ $f->name }} <span class="font-normal text-gray-400">{{ $f->employee_id ? '· ' . $f->employee_id : '' }}</span></p>
                    <p class="text-sm text-gray-500">Submitted {{ \Carbon\Carbon::parse($f->updated_at)->diffForHumans() }}</p>
                    @if($f->status === 'approved')
                        <span class="mt-1 inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20">Approved</span>
                    @elseif($f->status === 'rejected')
                        <span class="mt-1 inline-flex rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">Rejected — {{ $f->reject_reason }}</span>
                    @else
                        <span class="mt-1 inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20">Waiting for approval</span>
                    @endif
                </div>
                <div class="flex flex-wrap gap-2" x-data="{ rej: false }">
                    @if($f->status !== 'approved')
                        <form method="POST" action="{{ route($p . '.attendance.settings.faces.review', $f->id) }}">@csrf
                            <input type="hidden" name="decision" value="approve">
                            <button class="rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Approve</button>
                        </form>
                        <button type="button" @click="rej = !rej" class="rounded-lg border border-gray-200 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">Reject</button>
                    @endif
                    <form method="POST" action="{{ route($p . '.attendance.settings.faces.review', $f->id) }}" onsubmit="return confirm('Delete this face? They will need to register again.')">@csrf
                        <input type="hidden" name="decision" value="reset">
                        <button class="rounded-lg border border-gray-200 px-3.5 py-2 text-xs font-semibold text-red-600 hover:border-red-200 hover:bg-red-50">Delete</button>
                    </form>
                    <form x-show="rej" x-cloak method="POST" action="{{ route($p . '.attendance.settings.faces.review', $f->id) }}" class="flex w-full gap-2">@csrf
                        <input type="hidden" name="decision" value="reject">
                        <input name="reason" placeholder="Reason (e.g. blurry, wearing sunglasses)" maxlength="255" class="{{ $input }} py-2">
                        <button class="rounded-lg bg-red-600 px-3.5 py-2 text-xs font-semibold text-white">Send</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="px-6 py-14 text-center">
                <p class="font-semibold text-gray-900">No face photos yet</p>
                <p class="mt-1 text-sm text-gray-500">Turn on <strong>Face + liveness</strong> in Rules. Staff will be asked to register a selfie the first time they check in.</p>
            </div>
        @endforelse
    </section>
    @endif

    {{-- ═══════════ PHONES ═══════════ --}}
    @if($tab === 'devices')
    <section class="{{ $card }} overflow-hidden">
        <div class="border-b border-gray-100 px-6 py-4">
            <h3 class="font-semibold text-gray-900">Linked phones</h3>
            <p class="text-sm text-gray-500">Each person's first check-in links their phone. Unlink when someone changes phone.</p>
        </div>
        @forelse($devices as $d)
            <div class="flex items-center gap-4 border-b border-gray-100 px-6 py-4 last:border-0">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-50 text-gray-500">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-gray-900">{{ $d->name }} <span class="font-normal text-gray-400">{{ $d->employee_id ? '· ' . $d->employee_id : '' }}</span></p>
                    <p class="text-sm text-gray-500">{{ $d->device_name ?: 'Unknown phone' }}{{ $d->platform ? ' · ' . ucfirst($d->platform) : '' }} · last used {{ $d->last_used_at ? \Carbon\Carbon::parse($d->last_used_at)->diffForHumans() : 'never' }}</p>
                </div>
                <form method="POST" action="{{ route($p . '.attendance.settings.devices.reset', $d->id) }}" onsubmit="return confirm('Unlink this phone? Their next check-in will link the new phone.')">@csrf @method('DELETE')
                    <button class="rounded-lg border border-gray-200 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">Unlink</button>
                </form>
            </div>
        @empty
            <div class="px-6 py-14 text-center text-sm text-gray-500">No phones linked yet.</div>
        @endforelse
    </section>
    @endif

    {{-- ═══════════ FINGERPRINT ═══════════ --}}
    @if($tab === 'biometric')
    <div class="space-y-6">
        <form method="POST" action="{{ route($p . '.attendance.settings.update') }}" class="{{ $card }} flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
            @csrf @method('PUT')
            @foreach(['timing' => $r['timing'], 'location' => $r['location'], 'face' => $r['face']] as $g => $vals)
                @foreach($vals as $k => $val)<input type="hidden" name="{{ $g }}[{{ $k }}]" value="{{ $val }}">@endforeach
            @endforeach
            @foreach($r['self_checkin'] as $k => $val)<input type="hidden" name="self_checkin[{{ $k }}]" value="{{ $val ? 1 : 0 }}">@endforeach
            <div>
                <h3 class="font-semibold text-gray-900">Use fingerprint machine</h3>
                <p class="text-sm text-gray-500">First punch = check-in (late rule applies), last punch = check-out. Holidays are ignored.</p>
            </div>
            <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                <input type="hidden" name="biometric[enabled]" value="0">
                <input type="checkbox" name="biometric[enabled]" value="1" @checked($r['biometric']['enabled']) onchange="this.form.submit()" class="peer sr-only">
                <span class="h-6 w-11 rounded-full bg-gray-200 transition-colors peer-checked:bg-primary-600"></span>
                <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                <span class="sr-only">Enable fingerprint machine</span>
            </label>
        </form>

        <div class="grid gap-6 lg:grid-cols-5">
            <section class="{{ $card }} overflow-hidden lg:col-span-3">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="font-semibold text-gray-900">Machines</h3>
                    <p class="text-sm text-gray-500">ZKTeco, eSSL, Realtime and other ADMS / push-protocol machines.</p>
                </div>
                @forelse($machines as $m)
                    @php $online = $m->last_seen_at && \Carbon\Carbon::parse($m->last_seen_at)->gt(now()->subMinutes(10)); @endphp
                    <div class="flex items-center gap-4 border-b border-gray-100 px-6 py-4 last:border-0">
                        <span class="relative flex h-10 w-10 items-center justify-center rounded-xl bg-gray-50 text-gray-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.864 4.243A7.5 7.5 0 0 1 19.5 10.5c0 2.92-.556 5.709-1.568 8.268M5.742 6.364A7.465 7.465 0 0 0 4.5 10.5a7.464 7.464 0 0 1-1.15 3.993m1.989 3.559A11.209 11.209 0 0 0 8.25 10.5a3.75 3.75 0 1 1 7.5 0c0 .527-.021 1.049-.064 1.565M12 10.5a14.94 14.94 0 0 1-3.6 9.75m6.633-4.596a18.666 18.666 0 0 1-2.485 5.33"/></svg>
                            <span class="absolute -right-0.5 -top-0.5 h-3 w-3 rounded-full ring-2 ring-white {{ $online ? 'bg-emerald-500' : 'bg-gray-300' }}"></span>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-gray-900">{{ $m->name }} <span class="font-mono text-xs font-normal text-gray-400">{{ $m->serial_number }}</span></p>
                            <p class="text-sm text-gray-500">{{ $m->location ?: '—' }} · {{ $online ? 'Online' : ($m->last_seen_at ? 'Last seen ' . \Carbon\Carbon::parse($m->last_seen_at)->diffForHumans() : 'Never connected') }}</p>
                        </div>
                        <form method="POST" action="{{ route($p . '.attendance.settings.machines.destroy', $m->id) }}" onsubmit="return confirm('Remove this machine?')">@csrf @method('DELETE')
                            <button class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:border-red-200 hover:bg-red-50">Remove</button>
                        </form>
                    </div>
                @empty
                    <p class="px-6 py-10 text-center text-sm text-gray-500">No machines yet. Add one below.</p>
                @endforelse
                <form method="POST" action="{{ route($p . '.attendance.settings.machines.store') }}" class="grid gap-3 border-t border-gray-100 bg-gray-50/60 p-5 sm:grid-cols-4">
                    @csrf
                    <input name="serial_number" required placeholder="Serial no. (SN)" value="{{ old('serial_number') }}" class="{{ $input }} font-mono uppercase @error('serial_number') border-red-400 @enderror" aria-label="Machine serial number">
                    <input name="name" required placeholder="Name (Main gate)" value="{{ old('name') }}" class="{{ $input }}" aria-label="Machine name">
                    <input name="location" placeholder="Location (optional)" value="{{ old('location') }}" class="{{ $input }}" aria-label="Machine location">
                    <button class="rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-700">Add machine</button>
                    @error('serial_number')<p class="text-xs text-red-600 sm:col-span-4">{{ $message }}</p>@enderror
                </form>
            </section>

            <section class="{{ $card }} p-6 lg:col-span-2">
                <h3 class="font-semibold text-gray-900">Machine setup</h3>
                <ol class="mt-3 space-y-3 text-sm text-gray-600">
                    <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-50 text-xs font-bold text-primary-700">1</span>Connect the machine to the school Wi-Fi or LAN cable with internet.</li>
                    <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-50 text-xs font-bold text-primary-700">2</span><span>On the machine: <strong>Menu → Comm. → Cloud Server Setting (ADMS)</strong></span></li>
                    <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-50 text-xs font-bold text-primary-700">3</span>
                        <span>Server address <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs">{{ $apiHost }}</code>, port <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs">{{ $apiPort }}</code>, proxy off.</span></li>
                    <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-50 text-xs font-bold text-primary-700">4</span>Add the machine's serial number here (Menu → System Info → Device Info).</li>
                    <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-50 text-xs font-bold text-primary-700">5</span>Enrol each person's finger on the machine, then enter their machine ID below.</li>
                </ol>
                <p class="mt-4 rounded-xl bg-amber-50 p-3 text-xs text-amber-900">The machine must reach the API over the internet — use the public API domain, not localhost.</p>
            </section>
        </div>

        <form method="POST" action="{{ route($p . '.attendance.settings.machines.maps') }}" class="{{ $card }} overflow-hidden" x-data="{ q: '' }">
            @csrf
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4">
                <div>
                    <h3 class="font-semibold text-gray-900">Machine ID for each person</h3>
                    <p class="text-sm text-gray-500">The "User ID" you typed on the machine when enrolling their finger.</p>
                </div>
                <input x-model="q" type="search" placeholder="Search staff" class="{{ $input }} sm:w-56" aria-label="Search staff">
            </div>
            @if($unmapped->isNotEmpty())
                <div class="border-b border-amber-100 bg-amber-50 px-6 py-3 text-sm text-amber-900">
                    Unknown machine IDs punched recently:
                    @foreach($unmapped as $u)<span class="ml-1 rounded bg-white px-1.5 py-0.5 font-mono text-xs ring-1 ring-amber-200">{{ $u->device_pin }} ×{{ $u->punches }}</span>@endforeach
                    — assign them below.
                </div>
            @endif
            @error('pin')<p class="px-6 pt-3 text-sm text-red-600">{{ $message }}</p>@enderror
            <div class="max-h-[28rem] divide-y divide-gray-100 overflow-y-auto">
                @foreach($staff as $s)
                    <div class="flex items-center gap-4 px-6 py-2.5" x-show="!q || '{{ strtolower(addslashes($s->name . ' ' . $s->employee_id)) }}'.includes(q.toLowerCase())">
                        <p class="min-w-0 flex-1 truncate text-sm text-gray-900">{{ $s->name }} <span class="text-gray-400">{{ $s->employee_id ? '· ' . $s->employee_id : '' }}</span></p>
                        <input name="pin[{{ $s->id }}]" value="{{ old('pin.' . $s->id, $maps[$s->id] ?? '') }}" placeholder="—" inputmode="numeric" maxlength="30" aria-label="Machine ID for {{ $s->name }}"
                               class="w-28 rounded-lg border border-gray-200 px-3 py-1.5 text-center font-mono text-sm focus:outline-none focus:ring-4 focus:ring-primary-500/15 focus:border-primary-500">
                    </div>
                @endforeach
            </div>
            <div class="flex justify-end border-t border-gray-100 bg-gray-50/60 px-6 py-4">
                <button class="rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-700">Save machine IDs</button>
            </div>
        </form>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
window.geoPicker = function geoPicker(lat, lng, radius) {
    return {
        lat: lat ?? '', lng: lng ?? '', radius, locating: false, map: null, marker: null, circle: null,
        init() {
            if (!window.L) return;
            const has = this.lat !== '' && this.lng !== '';
            const center = has ? [Number(this.lat), Number(this.lng)] : [22.5, 79]; // India
            this.map = L.map(this.$refs.map, { scrollWheelZoom: false }).setView(center, has ? 17 : 5);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(this.map);
            this.marker = L.marker(center, { draggable: true }).addTo(this.map);
            this.circle = L.circle(center, { radius: this.radius, color: '#2563eb', fillOpacity: 0.12, weight: 2 }).addTo(this.map);
            if (!has) { this.marker.setOpacity(0.4); }
            this.marker.on('dragend', () => this.setFrom(this.marker.getLatLng()));
            this.map.on('click', (e) => this.setFrom(e.latlng));
        },
        setFrom(ll) {
            this.lat = ll.lat.toFixed(7); this.lng = ll.lng.toFixed(7);
            this.marker.setLatLng(ll).setOpacity(1); this.circle.setLatLng(ll);
        },
        move() {
            const a = Number(this.lat), b = Number(this.lng);
            if (!Number.isFinite(a) || !Number.isFinite(b) || !this.map) return;
            const ll = L.latLng(a, b); this.marker.setLatLng(ll).setOpacity(1); this.circle.setLatLng(ll); this.map.setView(ll, 17);
        },
        locate() {
            if (!navigator.geolocation) return alert('Your browser cannot share location.');
            this.locating = true;
            navigator.geolocation.getCurrentPosition(
                (p) => { this.locating = false; this.setFrom(L.latLng(p.coords.latitude, p.coords.longitude)); this.map.setView(this.marker.getLatLng(), 18); },
                () => { this.locating = false; alert('Could not get your location. Allow location access and try again.'); },
                { enableHighAccuracy: true, timeout: 15000 }
            );
        },
    };
}
</script>
@endpush
@endsection
