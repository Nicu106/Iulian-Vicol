@extends('muestras.layout')
@section('title', 'Opiniones · ' . $variant[0])
@section('theme', '#E8EDF5')
@section('body', 'ms-on-band')
@section('content')
{{-- 4 · Pares — the client's own sketch (2026-10-08).
     Each review is a pair: the photograph in front, the words on a card
     behind it, set half a photograph lower and half a photograph to the
     right, so the two lie on a diagonal. The pairs stand in a row that the
     thumb (or the arrows under it, or a mouse drag) moves, snapping pair by
     pair: three in view on a computer, two on a tablet, one and the edge of
     the next on a phone.
     The photograph keeps its whole picture: a 3:4 card (the shape of most of
     them), the picture `contain` on the card's dark ground.
     A touch on the words brings their card to the front with the whole
     review, scrolling inside if it is long; another touch (or a touch on the
     photograph) sends it back. A touch on the photograph lifts it out of the
     row and opens it whole, over the dimmed page; × (or Esc, or Back) puts it
     back in its place.
     Without script: the same row, scrolled natively, every card carrying the
     whole review. Geometry from the pair's own width (container units), so
     any number of pairs and any length of words keep the diagonal. --}}
@php $C = \App\Http\Controllers\ReviewShowroomController::class; @endphp
<main class="ms-sec ms-v4" aria-labelledby="h-op">
  @include('muestras._head')
  <div class="pr" data-pairs>
    <ul class="pr__row" role="list" data-pr-row id="pr-row">
      @foreach($reviews as $i => $r)
        @php $c = $C::sentences($r->text, 120); @endphp
        <li class="pr__i" data-id="{{ $r->id }}">
          <div class="pr__pair">
            <button class="pr__ph" type="button" aria-haspopup="dialog" data-pr-zoom>
              <span class="ms-sr">Ver la foto entera: {{ $r->caption }}</span>
              <span class="pr__mat" style="--r:{{ $r->box }}">
                <x-img :src="$r->img" alt="" :sizes="'(min-width:1264px) 240px, (min-width:1000px) calc((100vw - 160px) * .2067), (min-width:700px) calc((100vw - 96px) * .31), 216px'" :max="720" class="pr__img" />
              </span>
            </button>
            <div class="pr__txt{{ $c['cut'] ? ' is-cut' : '' }}">
              <button class="pr__swap" type="button" aria-pressed="false" aria-controls="pr-f{{ $r->id }}">
                <span class="ms-sr">Leer entera la opinión de {{ $r->name }}</span>
              </button>
              <div class="pr__rest">
                <p class="pr__by"><span><b>{{ $r->name }},</b> con su coche</span></p>
                <p class="pr__q">{{ $c['short'] }}</p>
                @if($c['cut'])<p class="pr__more" aria-hidden="true">Leer más</p>@endif
              </div>
              <div class="pr__full" id="pr-f{{ $r->id }}" tabindex="-1">
                <blockquote class="pr__fq">@foreach($r->paras as $p)<p>{{ $p }}</p>@endforeach</blockquote>
                <p class="pr__fby">{{ $r->caption }}</p>
              </div>
            </div>
          </div>
        </li>
      @endforeach
    </ul>
    <div class="pr__nav">
      <button class="ms-rnd pr__arw" type="button" data-pr-step="-1" aria-label="Anteriores" aria-controls="pr-row">
        <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path d="M19 12H5M11 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
      </button>
      <button class="ms-rnd pr__arw" type="button" data-pr-step="1" aria-label="Siguientes" aria-controls="pr-row">
        <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
      </button>
    </div>
  </div>
</main>
@endsection
@push('after')
{{-- the photograph, lifted out of its pair: whole, over the dimmed page --}}
<dialog class="zm" id="pr-zoom" aria-label="Foto">
  <img class="zm__img" alt="" decoding="async">
  <p class="zm__by"></p>
  <button class="ms-rnd ms-rnd--glass zm__x" type="button" aria-label="Cerrar">
    <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
  </button>
</dialog>
<script type="application/json" id="ms-data">{!! $json !!}</script>
@endpush
