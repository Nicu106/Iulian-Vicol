@extends('muestras.layout')
@section('title', 'Reviews: proposals · IV MOTORCLASS')
@section('theme', '#F4F6FA')
@section('body', 'sh-body')
@section('content')
{{-- The showroom index (review chrome, in English; the proposals themselves are
     Spanish). Versions of the same approach are grouped, newest first, and every
     earlier version stays one click away. Each version opens on its own page,
     with a bar to step between the versions of its approach. On a computer two
     of them can be compared side by side, each in a phone-sized frame:
       - a section as tall as its content: /v?embed=1, the frame takes the
         height the proposal posts — no scroll in a scroll;
       - a pinned scroll sequence (1, 3): /v?embed=2, a frame of a screen's
         height that scrolls on its own. --}}
@php
  $groups = [];
  foreach ($variants as $k => $x) { $groups[$x[3]][$k] = $x; }
  foreach ($groups as &$g) { uksort($g, fn ($a, $b) => strnatcmp((string) $b, (string) $a)); } unset($g);
  $label = fn ($k) => str_contains((string) $k, '.') ? (string) $k : $k . '.0';
  $isPin = fn ($k) => in_array($k, $pinned, true);
  $src = fn ($k) => route('muestras.opiniones.show', $k) . '?embed=' . ($isPin($k) ? 2 : 1);
  $cmp = ['a' => '4.4', 'b' => 4];
@endphp
<main lang="en">
<header class="sh-top">
  <div class="ms-wrap">
    <p class="sh-brand">IV&nbsp;MOTORCLASS</p>
    <h1 class="sh-h1">Customer reviews: proposals</h1>
    <p class="sh-lede">Every proposal is the real section, with the {{ $count }} live reviews and their whole photos.
      Open each one on the phone and on the computer. Earlier versions stay here, so you can always go back.</p>
  </div>
</header>

<div class="ms-wrap" style="display:grid;gap:var(--s-8);padding-bottom:var(--s-8)">
  @foreach($groups as $approach => $list)
    <section style="border-top:1px solid var(--mc-hairline);padding-top:var(--s-6)" aria-labelledby="g-{{ $loop->index }}">
      <h2 class="sh-h2" id="g-{{ $loop->index }}" style="margin:0 0 var(--s-5)">{{ $approach }}</h2>
      <ul style="list-style:none;margin:0;padding:0;display:grid;gap:var(--s-6)">
        @foreach($list as $k => $x)
          @php $th = 'img/muestras/opiniones-' . $k . '.webp'; @endphp
          <li style="display:grid;grid-template-columns:7.5rem minmax(0,1fr);gap:var(--s-5);align-items:start">
            <a href="/muestras/opiniones/{{ $k }}" tabindex="-1" aria-hidden="true">
              @if(is_file(public_path($th)))
                <img src="{{ asset($th) }}?v={{ @filemtime(public_path($th)) ?: 1 }}" alt="" width="390" height="780" loading="lazy" decoding="async"
                     style="display:block;width:100%;height:auto;aspect-ratio:390/780;background:var(--mc-band);outline:1px solid var(--mc-hairline)">
              @endif
            </a>
            <div>
              <h3 style="margin:0 0 var(--s-2);font-size:var(--t-h3);line-height:1.2;color:var(--mc-ink)">
                @if(count($list) > 1){{ $label($k) }} · @endif{{ $x[1] }}@if(count($list) > 1 && $loop->first) <span style="color:var(--mc-ink-2);font-weight:500">· latest</span>@endif
              </h3>
              <p style="margin:0 0 var(--s-3);max-width:40rem;color:var(--mc-ink-2);font-size:var(--t-ui);line-height:1.55">{{ $x[2] }}</p>
              <a href="/muestras/opiniones/{{ $k }}" style="display:inline-flex;align-items:center;min-height:48px;padding:0 var(--s-5);border-radius:10px;font-weight:600;text-decoration:none;{{ $loop->first ? 'background:var(--mc-ink);color:#fff' : 'border:1px solid var(--mc-ink);color:var(--mc-ink)' }}">Open {{ count($list) > 1 ? $label($k) : $x[1] }}</a>
            </div>
          </li>
        @endforeach
      </ul>
    </section>
  @endforeach
</div>

{{-- Two side by side, each as a phone shows it. A computer only. --}}
<section class="sh-cmp" aria-labelledby="sh-cmp-h">
  <div class="ms-wrap">
    <h2 class="sh-h3" id="sh-cmp-h">Compare two, as a phone shows them</h2>
    <div class="sh-cmp__pick">
      @foreach($cmp as $s => $def)
        <label class="sh-cmp__sel"><span class="ms-sr">{{ $s === 'a' ? 'First proposal' : 'Second proposal' }}</span>
          <select data-cmp="{{ $s }}">
            @foreach($groups as $approach => $list)
              <optgroup label="{{ $approach }}">
                @foreach($list as $k => $x)
                  <option value="{{ $k }}" data-pinned="{{ $isPin($k) ? 1 : 0 }}" @selected((string) $k === (string) $def)>{{ count($list) > 1 ? $label($k) . ' · ' . $x[1] : $x[1] }}</option>
                @endforeach
              </optgroup>
            @endforeach
          </select>
        </label>
      @endforeach
    </div>
  </div>
  <div class="sh-cmp__stage">
    @foreach($cmp as $s => $def)
      <div class="sh-dev sh-dev--390">
        <iframe class="sh-frame" data-cmp-frame="{{ $s }}" src="{{ $src($def) }}"
                title="Compare: {{ $variants[$def][1] }}" height="{{ $isPin($def) ? 760 : 800 }}" loading="lazy" style="width:390px"></iframe>
      </div>
    @endforeach
  </div>
</section>
</main>
@endsection
