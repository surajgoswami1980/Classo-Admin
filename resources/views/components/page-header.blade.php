@props(['title', 'subtitle' => null])

{{-- Page heading with optional subtitle and right-aligned actions slot.
     Usage: <x-page-header title="Students" subtitle="120 total"><a class="btn btn-primary">Add</a></x-page-header> --}}
<div {{ $attributes->merge(['class' => 'flex flex-col gap-4 border-b border-gray-200/70 pb-5 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="min-w-0">
        <p class="mc-eyebrow mb-1">Workspace</p>
        <h2 class="text-xl font-bold tracking-[-.025em] text-gray-900 sm:text-2xl">{{ $title }}</h2>
        @if($subtitle)
            <p class="mt-1 text-sm leading-6 text-gray-500">{{ $subtitle }}</p>
        @endif
    </div>
    @if(trim($slot) !== '')
        <div class="flex flex-wrap items-center gap-2 sm:justify-end">{{ $slot }}</div>
    @endif
</div>
