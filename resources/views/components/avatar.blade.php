@props([
    'src' => null,        // kép elérési útvonala
    'size' => 48,         // pixelben: pl. 48 = 48px
    'alt' => 'Avatar',    // alternatív szöveg
    'fallback' => null,   // ha nincs kép, pl. 'AB' vagy placeholder útvonal
])

@php
    $dimension = is_numeric($size) ? $size . 'px' : $size;
@endphp

<div
    class="flex items-center justify-center rounded-full overflow-hidden bg-gray-200 text-gray-600 font-semibold shadow-sm"
    style="width: {{ $dimension }}; height: {{ $dimension }};"
>
    @if ($src)
        <img
            src="{{ Storage::disk('public')->url($src) }}"
            alt="{{ $alt }}"
            class="w-full h-full object-cover"
            onerror="this.style.display='none'; this.parentElement.textContent='{{ $fallback ?? '?' }}';"
        >
    @else
        <span class="text-sm select-none">{{ $fallback ?? '?' }}</span>
    @endif
</div>
