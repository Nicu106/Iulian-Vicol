{{-- One responsive <img>, so no page has to remember the rules again.

     $src      a /storage/... or /img/banner/... path
     $sizes    what the slot MEASURES, not what it might be. Every value in the
               templates was taken from the rendered page at 320, 390, 768, 1000,
               1200, 1440 and 1800 — a `sizes` that lies is bandwidth spent with
               nothing on screen to show for it, and it is invisible in a review.
     $priority the LCP candidate: eager, and asked for first. Everything else is
               lazy, which is what stops a car page with 42 thumbnails from
               opening 42 connections before the main photograph has arrived.
     $max      do not build derivatives wider than the slot can ever be. --}}
@props([
    'src'      => null,
    'alt'      => '',
    'sizes'    => '100vw',
    'max'      => 2000,
    'priority' => false,
    'fallback' => 1080,
])
@php
    use App\Support\Img;
    $set = Img::srcset($src, (int) $max);
    $dim = Img::size($src);
    $url = Img::url($src, (int) $fallback) ?? $src;
@endphp
<img src="{{ $url }}"
     @if($set) srcset="{{ $set }}" sizes="{{ $sizes }}" @endif
     alt="{{ $alt }}"
     @if($dim) width="{{ $dim[0] }}" height="{{ $dim[1] }}" @endif
     @if($priority) fetchpriority="high" loading="eager"
     @else loading="lazy" decoding="async" @endif
     {{ $attributes }}>
