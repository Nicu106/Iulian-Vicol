@extends('muestras.layout')
@section('title', 'Opiniones · ' . $variant[0])
@section('theme', '#05080F')
@section('body', 'ms-night')
@section('content')
{{-- 2 · El muro — the scale of trust. Every delivery photograph at once,
     whole, in justified rows on the night (pure CSS: each picture's
     flex-grow is its own ratio, so a row shares one height and nothing is
     cut — any photograph added later just takes its place). The wall rests
     dimmed; one review stands in front, its photograph lit in the wall.
     Every six seconds the light moves on to the next and the words change —
     only while the wall is on screen, never under reduced motion, paused by
     any touch, hover or focus, and stopped for good by the keyboard's
     "Detener" (WCAG 2.2.2).
     A touch on any photograph brings it forward: it flies from its place in
     the wall to the front and grows, and its words arrive beside it
     (computer) or under it (phone, a full-screen sheet that swipes to the
     next and previous). Closing flies it back to its place.
     Without script: the wall, and the words of the first review in front;
     each photograph links to nothing it cannot keep. --}}
@php $C = \App\Http\Controllers\ReviewShowroomController::class; @endphp
<main class="ms-sec ms-v2" aria-labelledby="h-op">
  @include('muestras._head')
  <div class="mu ms-wrap" data-muro data-start="{{ $start }}">
    <div class="mu__feat" aria-live="polite">
      @foreach($reviews as $i => $r)
        @php $c = $C::sentences($r->text, 110); @endphp
        <figure class="mu__f{{ $i === 0 ? ' is-on' : '' }}" data-id="{{ $r->id }}">
          <blockquote class="mu__q"><p>«{{ $c['short'] }}»</p></blockquote>
          {{-- without script the wall opens nothing: every review is written out --}}
          <blockquote class="mu__full">@foreach($r->paras as $p)<p>{{ $p }}</p>@endforeach</blockquote>
          <figcaption class="mu__by">{{ $r->caption }}</figcaption>
          @if($c['cut'])
            <button class="ms-link mu__more" type="button" data-mu-open="{{ $r->id }}" aria-haspopup="dialog">Leer más<span class="ms-sr">: {{ $r->name }}</span></button>
          @endif
        </figure>
      @endforeach
    </div>
    <ul class="mu__wall" role="list">
      @foreach($reviews as $i => $r)
        <li class="mu__i{{ $i === 0 ? ' is-lit' : '' }}" style="--r:{{ $r->box }}" data-id="{{ $r->id }}">
          <button class="mu__t" type="button" data-mu-open="{{ $r->id }}" aria-haspopup="dialog">
            <span class="ms-sr">{{ $r->caption }}</span>
            <x-img :src="$r->img" alt="" :sizes="'(min-width:1000px) 200px, 120px'" :max="480" class="mu__img" />
          </button>
        </li>
      @endforeach
    </ul>
    <button class="mu__halt" type="button" aria-pressed="false" data-mu-halt>Detener el movimiento</button>
  </div>
</main>
@endsection
@push('after')
<dialog class="fo" id="mu-fo" aria-labelledby="mu-fo-by">
  <div class="fo__dim" aria-hidden="true"></div>
  <div class="fo__area" aria-hidden="true"></div>
  <img class="fo__img" alt="" decoding="async">
  <div class="fo__txt" tabindex="0">
    <blockquote class="fo__q"></blockquote>
    <p class="fo__by" id="mu-fo-by"></p>
  </div>
  <div class="fo__nav">
    <button class="ms-rnd ms-rnd--glass" type="button" data-fo="-1" aria-label="Opinión anterior">
      <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path d="M19 12H5M11 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
    </button>
    <button class="ms-rnd ms-rnd--glass" type="button" data-fo="1" aria-label="Opinión siguiente">
      <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
    </button>
  </div>
  <button class="ms-rnd ms-rnd--glass fo__x" type="button" data-fo="x" aria-label="Cerrar">
    <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
  </button>
  <p class="ms-sr" aria-live="polite" data-fo-live></p>
</dialog>
<script type="application/json" id="ms-data">{!! $json !!}</script>
@endpush
