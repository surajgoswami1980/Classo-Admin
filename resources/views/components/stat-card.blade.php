@props(['label', 'value', 'icon' => 'chart-bar', 'tone' => 'primary', 'hint' => null, 'hintTone' => 'muted', 'href' => null])

@php
    $tones = [
        'primary' => 'bg-primary-50 text-primary-600',
        'success' => 'bg-green-50 text-green-600',
        'warning' => 'bg-amber-50 text-amber-600',
        'danger'  => 'bg-red-50 text-red-600',
        'info'    => 'bg-cyan-50 text-cyan-600',
        'navy'    => 'bg-navy-800/5 text-navy-800',
    ];
    $hintTones = ['muted' => 'text-gray-500', 'success' => 'text-green-600', 'danger' => 'text-red-600', 'warning' => 'text-amber-600'];
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'mc-card group block p-5 ' . ($href ? 'card-hover focus-visible:ring-2 focus-visible:ring-primary-500' : '')]) }}>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-[13px] font-medium text-gray-500">{{ $label }}</p>
            <p class="mt-1.5 truncate text-[26px] font-bold leading-none tracking-tight text-gray-900">{{ $value }}</p>
        </div>
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $tones[$tone] ?? $tones['primary'] }}">
            @include('components.icons.' . $icon, ['class' => 'w-[22px] h-[22px]'])
        </span>
    </div>
    @if($hint)
        <p class="mt-3 text-xs font-medium {{ $hintTones[$hintTone] ?? $hintTones['muted'] }}">{{ $hint }}</p>
    @endif
</{{ $tag }}>
