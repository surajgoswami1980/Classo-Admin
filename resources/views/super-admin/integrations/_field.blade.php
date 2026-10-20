{{--
    One settings field. Vars: $group, $key, $def, $value, $source
    Secrets: the real value never reaches the browser — we show a masked hint,
    and leaving the input blank keeps the saved value.
--}}
@php
    // Rendered via view()->render(), so the shared $errors bag isn't injected automatically.
    $errors = session('errors') ?: new \Illuminate\Support\ViewErrorBag;
    $name = "{$group}[{$key}]";
    $id = "f-{$group}-{$key}";
    $err = "{$group}.{$key}";
    $isSecret = str_starts_with($def['type'], 'secret');
    $hasSaved = $isSecret && $value;
    $base = 'w-full rounded-xl border bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 transition focus:outline-none focus:ring-4 focus:ring-primary-500/15 focus:border-primary-500';
    $border = $errors->has($err) ? 'border-red-400' : 'border-gray-200 hover:border-gray-300';
    $sourceBadge = ['panel' => null, 'env' => ['.env', 'bg-amber-50 text-amber-700 ring-amber-600/20'], 'default' => ['default', 'bg-gray-50 text-gray-500 ring-gray-500/20'], 'none' => null][$source] ?? null;
@endphp

<div @if(!empty($def['show_when'])) x-show="driver === '{{ $def['show_when'] }}'" x-cloak @endif class="{{ $def['type'] === 'secret_textarea' ? 'sm:col-span-2' : '' }}">
    @if($def['type'] === 'toggle')
        <label for="{{ $id }}" class="flex items-start justify-between gap-4 rounded-xl border border-gray-200 bg-white p-4 cursor-pointer hover:border-gray-300 transition">
            <span>
                <span class="block text-sm font-semibold text-gray-900">{{ $def['label'] }}</span>
                @if(!empty($def['hint']))<span class="mt-0.5 block text-xs text-gray-500">{{ $def['hint'] }}</span>@endif
            </span>
            <span class="relative inline-flex shrink-0 items-center">
                <input type="hidden" name="{{ $name }}" value="0">
                <input id="{{ $id }}" type="checkbox" name="{{ $name }}" value="1" class="peer sr-only" @checked(old($err, $value) == '1')>
                <span class="h-6 w-11 rounded-full bg-gray-200 transition-colors peer-checked:bg-primary-600 peer-focus-visible:ring-4 peer-focus-visible:ring-primary-500/20"></span>
                <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
            </span>
        </label>
    @else
        <label for="{{ $id }}" class="mb-1.5 flex items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
            <span>{{ $def['label'] }}</span>
            @if($sourceBadge)
                <span class="rounded-md px-1.5 py-0.5 text-[10px] font-medium normal-case tracking-normal ring-1 ring-inset {{ $sourceBadge[1] }}" title="Currently read from {{ $sourceBadge[0] }}. Save here to manage it from the panel.">from {{ $sourceBadge[0] }}</span>
            @endif
        </label>

        @if($def['type'] === 'select')
            <select id="{{ $id }}" name="{{ $name }}" @if($key === 'driver' || $key === 'provider') x-model="driver" @endif class="{{ $base }} {{ $border }} appearance-none bg-[url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%239ca3af%22 stroke-width=%222%22><path d=%22M19 9l-7 7-7-7%22/></svg>')] bg-[length:16px] bg-[right_0.75rem_center] bg-no-repeat pr-9">
                @foreach($def['options'] as $optVal => $optLabel)
                    <option value="{{ $optVal }}" @selected(old($err, $value) == $optVal)>{{ $optLabel }}</option>
                @endforeach
            </select>
        @elseif($def['type'] === 'secret_textarea')
            <div x-data="{ show: false }">
                <textarea id="{{ $id }}" name="{{ $name }}" rows="6" spellcheck="false" autocomplete="off"
                          placeholder="{{ $hasSaved ? 'Saved ✓  Paste a new JSON file to replace it.' : 'Paste the full service account JSON here' }}"
                          class="{{ $base }} {{ $border }} font-mono text-xs leading-relaxed"></textarea>
                <label class="mt-2 inline-flex items-center gap-2 text-xs text-gray-500">
                    <input type="file" accept="application/json,.json" class="hidden" x-ref="file"
                           @change="const f = $event.target.files[0]; if (f) f.text().then(t => document.getElementById('{{ $id }}').value = t)">
                    <button type="button" @click="$refs.file.click()" class="rounded-lg border border-gray-200 bg-white px-2.5 py-1 font-medium text-gray-700 hover:bg-gray-50">Upload .json file</button>
                    @if($hasSaved)
                        <span class="text-gray-300">·</span>
                        <label class="inline-flex items-center gap-1.5 text-red-600"><input type="checkbox" name="{{ $group }}[{{ $key }}__clear]" value="1" class="rounded border-gray-300 text-red-600"> Remove saved key</label>
                    @endif
                </label>
            </div>
        @elseif($isSecret)
            <div x-data="{ show: false }" class="relative">
                <input id="{{ $id }}" :type="show ? 'text' : 'password'" name="{{ $name }}" autocomplete="new-password" spellcheck="false"
                       placeholder="{{ $hasSaved ? $value . '  (saved — leave blank to keep)' : ($def['placeholder'] ?? 'Enter secret') }}"
                       class="{{ $base }} {{ $border }} pr-10 font-mono">
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-gray-400 hover:text-gray-600" :aria-label="show ? 'Hide value' : 'Show value'">
                    <svg x-show="!show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                    <svg x-show="show" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                </button>
            </div>
        @else
            <input id="{{ $id }}" type="{{ $def['type'] === 'number' ? 'number' : 'text' }}" @if($def['type'] === 'number') step="0.01" min="0" @endif
                   name="{{ $name }}" value="{{ old($err, $value) }}" placeholder="{{ $def['placeholder'] ?? '' }}" autocomplete="off" spellcheck="false"
                   class="{{ $base }} {{ $border }} {{ in_array($key, ['key_id', 'app_id', 'api_url', 'ses_access_key'], true) ? 'font-mono' : '' }}">
        @endif

        @error($err)
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @else
            @if(!empty($def['hint']))<p class="mt-1.5 text-xs text-gray-500">{{ $def['hint'] }}</p>@endif
        @enderror
    @endif
</div>
