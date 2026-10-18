@extends('layouts.app')

@section('title', 'Enquiries')
@section('page-title', 'Enquiries')

@section('content')
<div class="space-y-6">

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 stagger">
        @php
            $cards = [
                ['label' => 'Total Leads', 'value' => $stats['total'], 'color' => 'from-blue-500 to-blue-600', 'icon' => 'inbox'],
                ['label' => 'New', 'value' => $stats['new'], 'color' => 'from-amber-500 to-orange-600', 'icon' => 'bell'],
                ['label' => 'Contacted', 'value' => $stats['contacted'], 'color' => 'from-violet-500 to-purple-600', 'icon' => 'clipboard-check'],
                ['label' => 'Converted', 'value' => $stats['converted'], 'color' => 'from-emerald-500 to-green-600', 'icon' => 'chart-bar'],
            ];
        @endphp
        @foreach($cards as $c)
            <div class="card-hover relative overflow-hidden bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-gradient-to-br {{ $c['color'] }} opacity-10"></div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">{{ $c['label'] }}</p>
                        <p class="mt-1 text-3xl font-bold text-gray-900">{{ $c['value'] }}</p>
                    </div>
                    <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br {{ $c['color'] }} text-white shadow-lg">
                        @include('components.icons.' . $c['icon'], ['class' => 'w-5 h-5'])
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap items-center gap-3 bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
        <div class="relative flex-1 min-w-[220px]">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search name, email, phone, school, city…"
                   class="w-full pl-10 pr-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
        </div>
        <select name="status" class="px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500 outline-none">
            <option value="">All statuses</option>
            @foreach($statuses as $s)
                <option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button class="px-5 py-2.5 text-sm font-semibold text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition-colors">Filter</button>
        @if(!empty($filters['search']) || !empty($filters['status']))
            <a href="{{ route('admin.enquiries.index') }}" class="px-4 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-900">Reset</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-100">
                        <th class="px-5 py-3">Lead</th>
                        <th class="px-5 py-3">Organization</th>
                        <th class="px-5 py-3">Size</th>
                        <th class="px-5 py-3">Source</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Received</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($enquiries as $e)
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-5 py-4">
                                <p class="font-semibold text-gray-900">{{ $e->name }}</p>
                                <p class="text-gray-500">{{ $e->email }}</p>
                                @if($e->phone)<p class="text-xs text-gray-400">{{ $e->phone }}</p>@endif
                            </td>
                            <td class="px-5 py-4 text-gray-700">
                                {{ $e->organization ?: '—' }}
                                @if($e->city)<p class="text-xs text-gray-400">{{ $e->city }}</p>@endif
                            </td>
                            <td class="px-5 py-4 text-gray-700">{{ $e->student_count ? number_format($e->student_count) : '—' }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-block px-2 py-0.5 text-xs font-medium text-gray-600 bg-gray-100 rounded">{{ $e->source }}</span>
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $statusColors = [
                                        'new' => 'bg-amber-100 text-amber-700',
                                        'contacted' => 'bg-blue-100 text-blue-700',
                                        'qualified' => 'bg-violet-100 text-violet-700',
                                        'converted' => 'bg-emerald-100 text-emerald-700',
                                        'closed' => 'bg-gray-100 text-gray-600',
                                    ];
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full {{ $statusColors[$e->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    @if($e->status === 'new')<span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span>@endif
                                    {{ ucfirst($e->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-gray-500">{{ $e->created_at->diffForHumans() }}</td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('admin.enquiries.show', $e) }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-primary-600 bg-primary-50 rounded-lg hover:bg-primary-100 transition-colors">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    @include('components.icons.inbox', ['class' => 'w-12 h-12 text-gray-300'])
                                    <p class="text-gray-500 font-medium">No enquiries yet</p>
                                    <p class="text-sm text-gray-400">Leads submitted from your website will appear here.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($enquiries->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">{{ $enquiries->links() }}</div>
        @endif
    </div>
</div>
@endsection
