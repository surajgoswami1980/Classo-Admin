@props(['title' => 'Nothing here yet', 'message' => null, 'icon' => 'inbox'])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center px-5 py-12 text-center sm:px-8 sm:py-14']) }}>
    <span class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-primary-50 to-white text-primary-600 shadow-sm ring-1 ring-primary-100" aria-hidden="true">
        @include('components.icons.' . $icon, ['class' => 'w-6 h-6'])
    </span>
    <p class="text-sm font-bold tracking-[-.01em] text-gray-900">{{ $title }}</p>
    @if($message)
        <p class="mt-1.5 max-w-sm text-sm leading-6 text-gray-500">{{ $message }}</p>
    @endif
    @if(trim($slot) !== '')
        <div class="mt-4">{{ $slot }}</div>
    @endif
</div>
