@props(['size' => 40, 'showText' => false, 'textClass' => 'text-gray-900', 'dark' => false])

{{-- Quilo brand mark: gradient "Q" ring (blue→cyan) with a teal swoosh tail. --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-3']) }}>
    <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="shrink-0">
        <defs>
            <linearGradient id="quiloRing" x1="15" y1="10" x2="85" y2="90" gradientUnits="userSpaceOnUse">
                <stop stop-color="#2EA7FF"/>
                <stop offset="0.55" stop-color="#1E6FE0"/>
                <stop offset="1" stop-color="#1746B0"/>
            </linearGradient>
            <linearGradient id="quiloTail" x1="45" y1="55" x2="80" y2="95" gradientUnits="userSpaceOnUse">
                <stop stop-color="#2BD4C4"/>
                <stop offset="1" stop-color="#15A58E"/>
            </linearGradient>
        </defs>
        {{-- Ring --}}
        <circle cx="46" cy="42" r="30" stroke="url(#quiloRing)" stroke-width="14"/>
        {{-- Tail / swoosh --}}
        <path d="M50 54 L84 92 L64 92 L40 64 Z" fill="url(#quiloTail)"/>
    </svg>
    @if($showText)
        <span class="text-xl font-extrabold tracking-tight {{ $textClass }}">Quilo</span>
    @endif
</span>
