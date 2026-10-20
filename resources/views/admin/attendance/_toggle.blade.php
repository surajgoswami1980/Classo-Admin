{{-- Card toggle. Vars: $name, $on, $title, $hint, $model (optional Alpine model) --}}
<label class="flex cursor-pointer items-start justify-between gap-4 rounded-xl border border-gray-200 bg-white p-4 transition hover:border-gray-300 has-[:checked]:border-primary-300 has-[:checked]:bg-primary-50/40">
    <span>
        <span class="block text-sm font-semibold text-gray-900">{{ $title }}</span>
        <span class="mt-0.5 block text-xs text-gray-500">{{ $hint }}</span>
    </span>
    <span class="relative mt-0.5 inline-flex shrink-0 items-center">
        <input type="hidden" name="{{ $name }}" value="0">
        <input type="checkbox" name="{{ $name }}" value="1" @checked($on) @if($model) x-model="{{ $model }}" @endif class="peer sr-only">
        <span class="h-6 w-11 rounded-full bg-gray-200 transition-colors peer-checked:bg-primary-600 peer-focus-visible:ring-4 peer-focus-visible:ring-primary-500/20"></span>
        <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
        <span class="sr-only">{{ $title }}</span>
    </span>
</label>
