@props(['title' => 'Nothing here yet', 'message' => null, 'icon' => 'inbox'])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center px-6 py-12 text-center']) }}>
    <span class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-primary-50 text-primary-500">
        @include('components.icons.' . $icon, ['class' => 'w-6 h-6'])
    </span>
    <p class="text-sm font-semibold text-gray-900">{{ $title }}</p>
    @if($message)
        <p class="mt-1 max-w-sm text-sm text-gray-500">{{ $message }}</p>
    @endif
    @if(trim($slot) !== '')
        <div class="mt-4">{{ $slot }}</div>
    @endif
</div>
