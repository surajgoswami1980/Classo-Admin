@extends('layouts.app')

@section('title', 'Platform Dashboard')
@section('page-title', 'Platform Overview')

@section('content')
@php $greeting = now()->hour < 12 ? 'Good morning' : (now()->hour < 17 ? 'Good afternoon' : 'Good evening'); @endphp
<div class="space-y-6">
    <!-- Welcome -->
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-navy-800 to-navy-900 px-6 py-6 text-white shadow-card sm:px-8">
        <div class="pointer-events-none absolute -right-16 -top-24 h-64 w-64 rounded-full bg-primary-500/20 blur-3xl" aria-hidden="true"></div>
        <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-white/60">{{ $greeting }}, {{ explode(' ', auth()->user()->name)[0] }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight">MyClasses platform</h2>
                <p class="mt-1 text-sm text-white/60">{{ now()->format('l, d F Y') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.schools.create') }}" class="btn bg-white text-navy-900 hover:bg-primary-50">
                    @include('components.icons.plus', ['class' => 'w-4 h-4']) Onboard school
                </a>
                <a href="{{ route('admin.payment-links.index') }}" class="btn border border-white/15 bg-white/5 text-white hover:bg-white/10">
                    @include('components.icons.link', ['class' => 'w-4 h-4']) Payment link
                </a>
            </div>
        </div>
    </section>

    <!-- KPIs -->
    <section aria-label="Platform figures" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 stagger">
        <x-stat-card label="Total Schools" :value="number_format($stats['total_schools'] ?? 0)" icon="building" tone="primary"
                     :hint="'+' . ($stats['new_schools_this_month'] ?? 0) . ' this month'" hint-tone="success" :href="route('admin.schools.index')" />
        <x-stat-card label="Active Schools" :value="number_format($stats['active_subscriptions'] ?? 0)" icon="check-circle" tone="success"
                     :hint="($stats['expiring_soon'] ?? 0) ? ($stats['expiring_soon'] . ' expiring in 30 days') : 'No renewals due soon'"
                     :hint-tone="($stats['expiring_soon'] ?? 0) ? 'warning' : 'muted'" :href="route('admin.subscriptions.index')" />
        <x-stat-card label="Active Students" :value="number_format($stats['total_students_platform'] ?? 0)" icon="users" tone="info" hint="Across all schools" />
        <x-stat-card label="Collected This Month" :value="'₹' . number_format($stats['monthly_revenue'] ?? 0)" icon="currency-rupee" tone="warning"
                     hint="Successful payments" :href="route('admin.revenue')" />
    </section>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <!-- Recent schools -->
        <section class="mc-card overflow-hidden xl:col-span-2" aria-labelledby="rs-title">
            <div class="mc-card-header">
                <h3 id="rs-title" class="mc-card-title">Recently onboarded schools</h3>
                <a href="{{ route('admin.schools.index') }}" class="btn btn-secondary btn-sm">View all</a>
            </div>
            @if(isset($recentSchools) && count($recentSchools) > 0)
                <div class="table-wrap">
                    <table class="table-mc">
                        <thead>
                            <tr>
                                <th scope="col">School</th>
                                <th scope="col">Code</th>
                                <th scope="col">Plan</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="!text-right">Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentSchools as $school)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-sm font-bold text-primary-700">{{ strtoupper(substr($school->name, 0, 1)) }}</span>
                                            <div class="min-w-0">
                                                <p class="truncate font-medium text-gray-900">{{ $school->name }}</p>
                                                <p class="truncate text-xs text-gray-500">{{ collect([$school->city, $school->state])->filter()->implode(', ') ?: '—' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="rounded-md bg-gray-100 px-2 py-1 font-mono text-xs text-gray-700">{{ $school->code }}</span></td>
                                    <td class="capitalize">{{ $school->subscription_plan ?? '—' }}</td>
                                    <td><x-status-badge :status="$school->subscription_status" /></td>
                                    <td class="whitespace-nowrap text-right text-gray-500">{{ $school->created_at?->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-empty-state icon="building" title="No schools onboarded yet" message="Onboard your first school to get started.">
                    <a href="{{ route('admin.schools.create') }}" class="btn btn-primary btn-sm">Onboard school</a>
                </x-empty-state>
            @endif
        </section>

        <!-- Quick actions + attention -->
        <div class="space-y-6">
            <section class="mc-card" aria-labelledby="att-title">
                <div class="mc-card-header"><h3 id="att-title" class="mc-card-title">Needs attention</h3></div>
                <div class="space-y-2 p-5">
                    <a href="{{ route('admin.enquiries.index') }}" class="flex items-center gap-3 rounded-xl border border-gray-100 px-3 py-3 transition hover:border-primary-200 hover:bg-primary-50/40">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-cyan-50 text-cyan-600">@include('components.icons.inbox', ['class' => 'w-[18px] h-[18px]'])</span>
                        <span class="flex-1 text-sm font-medium text-gray-700">New enquiries</span>
                        <span class="badge {{ ($stats['new_enquiries'] ?? 0) ? 'badge-info' : 'badge-neutral' }}">{{ $stats['new_enquiries'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('admin.subscriptions.index') }}" class="flex items-center gap-3 rounded-xl border border-gray-100 px-3 py-3 transition hover:border-primary-200 hover:bg-primary-50/40">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600">@include('components.icons.clock', ['class' => 'w-[18px] h-[18px]'])</span>
                        <span class="flex-1 text-sm font-medium text-gray-700">Renewals in 30 days</span>
                        <span class="badge {{ ($stats['expiring_soon'] ?? 0) ? 'badge-warning' : 'badge-neutral' }}">{{ $stats['expiring_soon'] ?? 0 }}</span>
                    </a>
                </div>
            </section>

            <section class="mc-card" aria-labelledby="qa-title">
                <div class="mc-card-header"><h3 id="qa-title" class="mc-card-title">Quick actions</h3></div>
                @php
                    $qa = [
                        ['label' => 'Manage schools', 'route' => 'admin.schools.index', 'icon' => 'building'],
                        ['label' => 'Subscription plans', 'route' => 'admin.subscriptions.index', 'icon' => 'briefcase'],
                        ['label' => 'Revenue', 'route' => 'admin.revenue', 'icon' => 'chart-bar'],
                        ['label' => 'Integrations', 'route' => 'admin.integrations.index', 'icon' => 'puzzle'],
                    ];
                @endphp
                <div class="grid grid-cols-2 gap-3 p-5">
                    @foreach($qa as $l)
                        <a href="{{ route($l['route']) }}" class="group flex flex-col gap-2 rounded-xl border border-gray-100 p-3 transition hover:border-primary-200 hover:bg-primary-50/40">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-50 text-primary-600 transition-transform group-hover:scale-105">@include('components.icons.' . $l['icon'], ['class' => 'w-[18px] h-[18px]'])</span>
                            <span class="text-[13px] font-medium text-gray-700 group-hover:text-primary-700">{{ $l['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
