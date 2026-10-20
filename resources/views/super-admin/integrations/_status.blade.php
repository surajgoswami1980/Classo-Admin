{{-- Status pill. Var: $s = connected | not_configured | disabled | log --}}
@php
    $map = [
        'connected' => ['Connected', 'bg-green-50 text-green-700 ring-green-600/20', 'bg-green-500'],
        'not_configured' => ['Not configured', 'bg-amber-50 text-amber-700 ring-amber-600/20', 'bg-amber-500'],
        'disabled' => ['Disabled', 'bg-gray-100 text-gray-600 ring-gray-500/20', 'bg-gray-400'],
        'log' => ['Log only', 'bg-sky-50 text-sky-700 ring-sky-600/20', 'bg-sky-500'],
    ];
    [$label, $cls, $dot] = $map[$s] ?? $map['not_configured'];
@endphp
<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $cls }}">
    <span class="h-1.5 w-1.5 rounded-full {{ $dot }}"></span>{{ $label }}
</span>
