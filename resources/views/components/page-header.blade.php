@props(['title', 'subtitle' => null])

{{-- Page heading with optional subtitle and right-aligned actions slot.
     Usage: <x-page-header title="Students" subtitle="120 total"><a class="btn btn-primary">Add</a></x-page-header> --}}
<div {{ $attributes->merge(['class' => 'flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="min-w-0">
        <h2 class="text-xl font-bold tracking-tight text-gray-900">{{ $title }}</h2>
        @if($subtitle)
            <p class="mt-0.5 text-sm text-gray-500">{{ $subtitle }}</p>
        @endif
    </div>
    @if(trim($slot) !== '')
        <div class="flex flex-wrap items-center gap-2">{{ $slot }}</div>
    @endif
</div>
