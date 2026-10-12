@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('content')
<div class="space-y-8">
    <!-- Welcome Banner (school sees its own details after login) -->
    <x-school-banner :banner="$banner ?? null" />

    <!-- Stats Cards -->
    @php
        $cards = [
            ['label' => 'Total Students', 'value' => number_format($stats['total_students'] ?? 0), 'from' => 'from-blue-500', 'to' => 'to-blue-600', 'ring' => 'bg-blue-100', 'text' => 'text-blue-600', 'icon' => 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z'],
            ['label' => 'Total Teachers', 'value' => number_format($stats['total_teachers'] ?? 0), 'from' => 'from-emerald-500', 'to' => 'to-green-600', 'ring' => 'bg-green-100', 'text' => 'text-green-600', 'icon' => 'M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342'],
            ['label' => "Today's Present", 'value' => $stats['today_attendance'] ?? 0, 'sub' => ($stats['today_absent'] ?? 0) . ' absent', 'from' => 'from-violet-500', 'to' => 'to-purple-600', 'ring' => 'bg-purple-100', 'text' => 'text-purple-600', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
            ['label' => 'Fee This Month', 'value' => '₹' . number_format($stats['fee_collected'] ?? 0), 'from' => 'from-amber-400', 'to' => 'to-orange-500', 'ring' => 'bg-amber-100', 'text' => 'text-amber-600', 'icon' => 'M15 8.25H9m6 3H9m3 6-3-3h1.5a3 3 0 1 0 0-6M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ];
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 stagger">
        @foreach($cards as $c)
        <div class="relative bg-white rounded-2xl border border-gray-100 p-5 card-hover overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r {{ $c['from'] }} {{ $c['to'] }}"></div>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500">{{ $c['label'] }}</p>
                    <p class="text-3xl font-extrabold text-gray-900 mt-1 tracking-tight">{{ $c['value'] }}</p>
                    @if(!empty($c['sub']))<p class="text-xs text-red-500 mt-0.5">{{ $c['sub'] }}</p>@endif
                </div>
                <div class="w-12 h-12 rounded-xl {{ $c['ring'] }} flex items-center justify-center">
                    <svg class="w-6 h-6 {{ $c['text'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $c['icon'] }}"/></svg>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Quick Links -->
    <div>
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Quick Actions</h3>
        @php
            $links = [
                ['label' => 'Mark Attendance', 'route' => 'attendance.students', 'ring' => 'bg-green-100', 'text' => 'text-green-600', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                ['label' => 'Add Student', 'route' => 'students.create', 'ring' => 'bg-blue-100', 'text' => 'text-blue-600', 'icon' => 'M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z'],
                ['label' => 'Fee Invoices', 'route' => 'fees.invoices', 'ring' => 'bg-amber-100', 'text' => 'text-amber-600', 'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z'],
                ['label' => 'Store', 'route' => 'store.index', 'ring' => 'bg-rose-100', 'text' => 'text-rose-600', 'icon' => 'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z'],
            ];
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 stagger">
            @foreach($links as $l)
            <a href="{{ panel_route($l['route']) }}" class="bg-white rounded-2xl border border-gray-100 p-5 text-center card-hover group">
                <div class="w-11 h-11 mx-auto mb-2.5 rounded-xl {{ $l['ring'] }} flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 {{ $l['text'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $l['icon'] }}"/></svg>
                </div>
                <p class="text-sm font-medium text-gray-700 group-hover:text-blue-700 transition-colors">{{ $l['label'] }}</p>
            </a>
            @endforeach
        </div>
    </div>
</div>
@endsection
