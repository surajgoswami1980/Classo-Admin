@extends('layouts.app')

@section('title', 'Event Registrations')
@section('page-title', 'Registrations — ' . $event->title)

@section('content')
<div class="space-y-5">
    <div class="bg-white rounded-xl border p-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $event->title }}</h2>
            <p class="text-sm text-gray-500">
                {{ \Carbon\Carbon::parse($event->start_at)->format('d M Y, h:i A') }}
                @if($event->venue) · {{ $event->venue }} @endif
                @if($event->is_paid) · Fee ₹{{ number_format($event->fee) }} @else · Free @endif
            </p>
        </div>
        <a href="{{ panel_route('events.index') }}" class="text-sm text-gray-600 hover:underline">&larr; Back to events</a>
    </div>

    <div class="bg-white rounded-xl border overflow-hidden">
        <div class="px-4 py-3 border-b text-sm font-medium text-gray-600">{{ $registrations->count() }} registration(s)</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Student</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Class</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Contact</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Payment</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-600">Status</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Registered</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($registrations as $r)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900">{{ $r->user_name }}</p>
                        @if($r->roll_number)<p class="text-xs text-gray-400">Roll {{ $r->roll_number }}</p>@endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $r->class_name ?? '—' }} {{ $r->section_name ? '- '.$r->section_name : '' }}</td>
                    <td class="px-4 py-3 text-gray-600 text-xs">{{ $r->phone ?? $r->email ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        @php $pb = ['not_required'=>'text-gray-600 bg-gray-100','pending'=>'text-amber-700 bg-amber-50','paid'=>'text-green-700 bg-green-50','failed'=>'text-red-700 bg-red-50'][$r->payment_status] ?? 'text-gray-600 bg-gray-100'; @endphp
                        <span class="text-xs font-medium {{ $pb }} px-2 py-0.5 rounded-full">
                            {{ $r->payment_status === 'not_required' ? 'Free' : ucfirst($r->payment_status) }}
                            @if($r->amount > 0) ₹{{ number_format($r->amount) }} @endif
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @php $sb = ['registered'=>'text-blue-700 bg-blue-50','cancelled'=>'text-red-700 bg-red-50','attended'=>'text-green-700 bg-green-50'][$r->status] ?? 'text-gray-600 bg-gray-100'; @endphp
                        <span class="text-xs font-medium {{ $sb }} px-2 py-0.5 rounded-full">{{ ucfirst($r->status) }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ \Carbon\Carbon::parse($r->created_at)->format('d M Y, h:i A') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">No registrations yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
