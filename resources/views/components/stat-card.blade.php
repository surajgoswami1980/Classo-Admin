@props(['label', 'value', 'icon' => 'chart-bar', 'tone' => 'primary', 'hint' => null, 'hintTone' => 'muted', 'href' => null])

@php
    $tones = [
        'primary' => 'bg-primary-50 text-primary-600 ring-primary-600/10',
        'success' => 'bg-green-50 text-green-600 ring-green-600/10',
        'warning' => 'bg-amber-50 text-amber-600 ring-amber-600/10',
        'danger'  => 'bg-red-50 text-red-600 ring-red-600/10',
        'info'    => 'bg-cyan-50 text-cyan-600 ring-cyan-600/10',
        'navy'    => 'bg-navy-800/5 text-navy-800 ring-navy-800/10',
    ];
    $hintTones = ['muted' => 'text-gray-500', 'success' => 'text-green-600', 'danger' => 'text-red-600', 'warning' => 'text-amber-600'];
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'mc-card group relative block min-h-[148px] p-5 sm:p-6 ' . ($href ? 'card-hover focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2' : '')]) }}>
    <span class="pointer-events-none absolute inset-x-5 top-0 h-px bg-gradient-to-r from-transparent via-primary-300/60 to-transparent" aria-hidden="true"></span>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-[11px] font-bold uppercase tracking-[.1em] text-gray-500">{{ $label }}</p>
            <p class="mt-2.5 truncate text-[28px] font-bold leading-none tracking-[-.04em] text-gray-900 sm:text-[30px]">{{ $value }}</p>
        </div>
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ring-1 ring-inset transition-transform duration-200 group-hover:scale-[1.04] {{ $tones[$tone] ?? $tones['primary'] }}">
            @include('components.icons.' . $icon, ['class' => 'w-[22px] h-[22px]'])
        </span>
    </div>
    @if($hint)
        <p class="mt-3 border-t border-gray-100 pt-3 text-xs font-semibold {{ $hintTones[$hintTone] ?? $hintTones['muted'] }}">{{ $hint }}</p>
    @endif
</{{ $tag }}>
