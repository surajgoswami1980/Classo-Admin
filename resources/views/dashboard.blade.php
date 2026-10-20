@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('content')
@php
    $ins = $insights ?? [];
    $trend = $ins['trend'] ?? [];
    $pending = $ins['pending'] ?? null;
    $staffToday = $ins['staffToday'] ?? null;
    $marked = ($stats['today_attendance'] ?? 0) + ($stats['today_absent'] ?? 0);
    $todayPct = $marked > 0 ? round(($stats['today_attendance'] / $marked) * 100) : null;
    $hasTrend = collect($trend)->sum('marked') > 0;
@endphp

<div class="space-y-6">
    <x-school-banner :banner="$banner ?? null" />

    <!-- KPIs -->
    <section aria-label="Key figures" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 stagger">
        <x-stat-card label="Active Students" :value="number_format($stats['total_students'] ?? 0)" icon="users" tone="primary"
                     :hint="($stats['total_classes'] ?? 0) . ' classes'" :href="panel_route('students.index')" />
        <x-stat-card label="Active Teachers" :value="number_format($stats['total_teachers'] ?? 0)" icon="user-badge" tone="info"
                     :hint="$staffToday && $staffToday->marked ? ($staffToday->present . ' checked in today') : null" :href="panel_route('teachers.index')" />
        <x-stat-card label="Present Today" :value="number_format($stats['today_attendance'] ?? 0)" icon="clipboard-check" tone="success"
                     :hint="$marked ? (($stats['today_absent'] ?? 0) . ' absent · ' . $todayPct . '% attendance') : 'Attendance not marked yet'"
                     :hint-tone="$marked && ($stats['today_absent'] ?? 0) > 0 ? 'danger' : 'muted'" :href="panel_route('attendance.index')" />
        <x-stat-card label="Fees This Month" :value="'₹' . number_format($stats['fee_collected'] ?? 0)" icon="currency-rupee" tone="warning"
                     :hint="$pending && $pending->amount > 0 ? ('₹' . number_format($pending->amount) . ' pending') : null"
                     hint-tone="warning" :href="panel_route('fees.index')" />
    </section>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <!-- Attendance trend -->
        <section class="mc-card xl:col-span-2" aria-labelledby="trend-title">
            <div class="mc-card-header">
                <div>
                    <h3 id="trend-title" class="mc-card-title">Student attendance · last 7 days</h3>
                    <p class="text-xs text-gray-500">Present + late as a share of students marked each day</p>
                </div>
                <a href="{{ panel_route('attendance.report') }}" class="btn btn-secondary btn-sm">View report</a>
            </div>
            <div class="mc-card-body">
                @if($hasTrend)
                    <div class="flex h-56 items-end gap-2 sm:gap-4" role="img" aria-label="Attendance percentage for the last seven days">
                        @foreach($trend as $d)
                            <div class="group flex h-full flex-1 flex-col items-center justify-end gap-2">
                                <span class="text-xs font-semibold {{ $d['pct'] === null ? 'text-gray-300' : 'text-gray-700' }}">{{ $d['pct'] === null ? '—' : $d['pct'] . '%' }}</span>
                                <div class="relative flex w-full max-w-[56px] flex-1 items-end overflow-hidden rounded-xl bg-gray-100">
                                    @if($d['pct'] !== null)
                                        <div class="w-full rounded-xl bg-gradient-to-t from-primary-600 to-primary-400 transition-all duration-500 group-hover:from-primary-700"
                                             style="height: {{ max(4, $d['pct']) }}%"></div>
                                    @endif
                                </div>
                                <div class="text-center leading-tight">
                                    <p class="text-xs font-semibold text-gray-700">{{ $d['label'] }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $d['marked'] ? $d['marked'] . ' marked' : $d['date'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-empty-state icon="chart-bar" title="No attendance in the last 7 days" message="Once attendance is marked, the daily trend will appear here.">
                        <a href="{{ panel_route('attendance.students') }}" class="btn btn-primary btn-sm">Mark attendance</a>
                    </x-empty-state>
                @endif
            </div>
        </section>

        <!-- Fees snapshot + alerts -->
        <section class="mc-card flex flex-col" aria-labelledby="dues-title">
            <div class="mc-card-header">
                <h3 id="dues-title" class="mc-card-title">Fee dues</h3>
                <a href="{{ panel_route('fees.pending') }}" class="text-sm font-semibold text-primary-600 hover:text-primary-700">Details</a>
            </div>
            <div class="mc-card-body flex-1 space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-amber-50 p-4">
                        <p class="text-xs font-medium text-amber-700">Pending</p>
                        <p class="mt-1 text-lg font-bold text-amber-800">₹{{ number_format($pending->amount ?? 0) }}</p>
                        <p class="text-[11px] text-amber-700/80">{{ number_format($pending->invoices ?? 0) }} invoices</p>
                    </div>
                    <div class="rounded-xl bg-red-50 p-4">
                        <p class="text-xs font-medium text-red-700">Overdue</p>
                        <p class="mt-1 text-lg font-bold text-red-700">₹{{ number_format($pending->overdue ?? 0) }}</p>
                        <p class="text-[11px] text-red-700/80">past due date</p>
                    </div>
                </div>

                @php
                    $alerts = [];
                    if (($pending->overdue ?? 0) > 0) $alerts[] = ['tone' => 'danger', 'text' => '₹' . number_format($pending->overdue) . ' in overdue fees', 'href' => panel_route('fees.defaulters'), 'cta' => 'Defaulters'];
                    if ($marked === 0 && !now()->isSunday()) $alerts[] = ['tone' => 'warning', 'text' => "Today's student attendance hasn't been marked", 'href' => panel_route('attendance.students'), 'cta' => 'Mark now'];
                    if (!empty($banner) && !is_null($banner['days_to_expiry']) && $banner['days_to_expiry'] <= 30) $alerts[] = ['tone' => $banner['days_to_expiry'] < 0 ? 'danger' : 'warning', 'text' => $banner['days_to_expiry'] < 0 ? 'Subscription expired' : 'Subscription ends in ' . $banner['days_to_expiry'] . ' days', 'href' => \Illuminate\Support\Facades\Route::has('user.subscription') && !auth()->user()->hasRole('super-admin') ? route('user.subscription') : null, 'cta' => 'Renew'];
                @endphp
                <div>
                    <p class="mc-section-title mb-2">Alerts</p>
                    @forelse($alerts as $a)
                        <div class="mb-2 flex items-center gap-3 rounded-xl border px-3 py-2.5 {{ $a['tone'] === 'danger' ? 'border-red-100 bg-red-50/60' : 'border-amber-100 bg-amber-50/60' }}">
                            @include('components.icons.exclamation', ['class' => 'w-4 h-4 shrink-0 ' . ($a['tone'] === 'danger' ? 'text-red-600' : 'text-amber-600')])
                            <p class="flex-1 text-[13px] text-gray-700">{{ $a['text'] }}</p>
                            @if($a['href'])<a href="{{ $a['href'] }}" class="shrink-0 text-xs font-semibold text-primary-600 hover:text-primary-700">{{ $a['cta'] }}</a>@endif
                        </div>
                    @empty
                        <div class="flex items-center gap-2 rounded-xl bg-green-50 px-3 py-2.5 text-[13px] text-green-700">
                            @include('components.icons.check-circle', ['class' => 'w-4 h-4'])
                            All caught up
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-3">
        <!-- Recent payments -->
        <section class="mc-card xl:col-span-1" aria-labelledby="pay-title">
            <div class="mc-card-header">
                <h3 id="pay-title" class="mc-card-title">Recent payments</h3>
                <a href="{{ panel_route('fees.collection-report') }}" class="text-sm font-semibold text-primary-600 hover:text-primary-700">Report</a>
            </div>
            @if(($ins['recentPayments'] ?? collect())->count())
                <ul class="divide-y divide-gray-100">
                    @foreach($ins['recentPayments'] as $p)
                        <li class="flex items-center gap-3 px-5 py-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-green-50 text-green-600">@include('components.icons.currency-rupee', ['class' => 'w-[18px] h-[18px]'])</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-900">{{ $p->student_name ?? ucfirst(str_replace('_', ' ', $p->payable_type ?? 'Payment')) }}</p>
                                <p class="text-xs text-gray-500">{{ ucfirst($p->gateway ?? '—') }} · {{ \Illuminate\Support\Carbon::parse($p->created_at)->diffForHumans() }}</p>
                            </div>
                            <span class="text-sm font-semibold text-gray-900">₹{{ number_format($p->amount) }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <x-empty-state icon="receipt" title="No payments yet" message="Successful fee payments will show up here." />
            @endif
        </section>

        <!-- Upcoming -->
        <section class="mc-card" aria-labelledby="up-title">
            <div class="mc-card-header">
                <h3 id="up-title" class="mc-card-title">Coming up</h3>
                <a href="{{ panel_route('events.index') }}" class="text-sm font-semibold text-primary-600 hover:text-primary-700">Events</a>
            </div>
            @php
                $upcoming = collect($ins['holidays'] ?? [])->map(fn ($h) => ['kind' => 'holiday', 'title' => $h->title, 'at' => \Illuminate\Support\Carbon::parse($h->start_date), 'end' => \Illuminate\Support\Carbon::parse($h->end_date), 'meta' => ucfirst(str_replace('_', ' ', $h->type)) . ' · ' . ($h->applies_to === 'all' ? 'Everyone' : ucfirst($h->applies_to))])
                    ->merge(collect($ins['events'] ?? [])->map(fn ($e) => ['kind' => 'event', 'title' => $e->title, 'at' => \Illuminate\Support\Carbon::parse($e->start_at), 'end' => null, 'meta' => trim(($e->venue ?? '') . ' · ' . \Illuminate\Support\Carbon::parse($e->start_at)->format('h:i A'), ' ·')]))
                    ->sortBy('at')->take(6);
            @endphp
            @if($upcoming->count())
                <ul class="divide-y divide-gray-100">
                    @foreach($upcoming as $u)
                        <li class="flex items-center gap-3 px-5 py-3">
                            <div class="w-11 shrink-0 rounded-xl py-1 text-center {{ $u['kind'] === 'holiday' ? 'bg-rose-50' : 'bg-primary-50' }}">
                                <p class="text-[10px] font-semibold uppercase {{ $u['kind'] === 'holiday' ? 'text-rose-500' : 'text-primary-500' }}">{{ $u['at']->format('M') }}</p>
                                <p class="text-base font-bold leading-tight {{ $u['kind'] === 'holiday' ? 'text-rose-700' : 'text-primary-700' }}">{{ $u['at']->format('d') }}</p>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-900">{{ $u['title'] }}</p>
                                <p class="truncate text-xs text-gray-500">
                                    @if($u['end'] && !$u['end']->isSameDay($u['at'])) Until {{ $u['end']->format('d M') }} · @endif{{ $u['meta'] }}
                                </p>
                            </div>
                            <span class="badge {{ $u['kind'] === 'holiday' ? 'bg-rose-50 text-rose-700 ring-rose-600/20' : 'badge-primary' }}">{{ $u['kind'] === 'holiday' ? 'Holiday' : 'Event' }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <x-empty-state icon="calendar" title="Nothing scheduled" message="Upcoming holidays and published events appear here.">
                    <a href="{{ panel_route('holidays.index') }}" class="btn btn-secondary btn-sm">Add holiday</a>
                </x-empty-state>
            @endif
        </section>

        <!-- Quick actions -->
        <section class="mc-card lg:col-span-2 xl:col-span-1" aria-labelledby="qa-title">
            <div class="mc-card-header"><h3 id="qa-title" class="mc-card-title">Quick actions</h3></div>
            @php
                $links = [
                    ['label' => 'Mark attendance', 'route' => 'attendance.students', 'icon' => 'clipboard-check', 'tone' => 'bg-green-50 text-green-600'],
                    ['label' => 'Add student', 'route' => 'students.create', 'icon' => 'users', 'tone' => 'bg-primary-50 text-primary-600'],
                    ['label' => 'Fee invoices', 'route' => 'fees.invoices', 'icon' => 'receipt', 'tone' => 'bg-amber-50 text-amber-600'],
                    ['label' => 'Send notification', 'route' => 'notifications.index', 'icon' => 'bell', 'tone' => 'bg-cyan-50 text-cyan-600'],
                    ['label' => 'Timetable', 'route' => 'timetable.index', 'icon' => 'clock', 'tone' => 'bg-navy-800/5 text-navy-800'],
                    ['label' => 'Store', 'route' => 'store.index', 'icon' => 'shopping-bag', 'tone' => 'bg-rose-50 text-rose-600'],
                ];
            @endphp
            <div class="grid grid-cols-2 gap-3 p-5 sm:grid-cols-3 xl:grid-cols-2">
                @foreach($links as $l)
                    <a href="{{ panel_route($l['route']) }}" class="group flex items-center gap-3 rounded-xl border border-gray-100 p-3 transition hover:border-primary-200 hover:bg-primary-50/40 focus-visible:ring-2 focus-visible:ring-primary-500">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $l['tone'] }} transition-transform group-hover:scale-105">@include('components.icons.' . $l['icon'], ['class' => 'w-[18px] h-[18px]'])</span>
                        <span class="text-[13px] font-medium text-gray-700 group-hover:text-primary-700">{{ $l['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    </div>
</div>
@endsection
