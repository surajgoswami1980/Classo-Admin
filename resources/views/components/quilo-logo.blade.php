@props(['size' => 40, 'showText' => false, 'textClass' => 'text-gray-900', 'dark' => false])

{{-- MyClasses brand mark — the real logo from public/images/myclasses-logo.svg
     (falls back to the raster .jpg if the SVG can't load). Keeps the same
     { size, showText, dark } API used across the panel. --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <img src="{{ asset('images/myclasses-logo.svg') }}" alt="MyClasses"
         width="{{ $size }}" height="{{ $size }}"
         class="shrink-0 object-contain"
         onerror="this.onerror=null;this.src='{{ asset('images/myclasses-logo.jpg') }}'">
    @if($showText)
        <span class="text-xl font-extrabold tracking-tight {{ $textClass }}">
            <span class="text-primary-600">My</span><span class="{{ $dark ? 'text-white' : 'text-gray-900' }}">Classes</span>
        </span>
    @endif
</span>
