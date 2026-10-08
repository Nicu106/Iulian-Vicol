@extends('muestras.layout')
@section('title', 'Opiniones · ' . $variant[0])
@section('content')
{{-- 4 · Muro de fotos. Only the photographs, every one whole: justified rows
     where each picture is as wide as its own shape asks at a shared height —
     pure CSS (flex-grow = the ratio), so it holds without script and for any
     photograph added later. A touch opens the review whole in the viewer,
     with the previous and next ones. A phone shows fourteen first. Without script each picture is a link
     to the review written out below the wall. --}}
<main class="ms-sec ms-v4" aria-labelledby="h-op">
  @include('muestras._head')
  <div class="ms-wrap">
    <ul class="ms-wall" role="list" id="wall-list">
      @foreach($reviews as $i => $r)
        <li class="ms-wall__i {{ $i >= 14 ? 'is-later-m' : '' }}" style="--r:{{ $r->box }}">
          <a class="ms-wall__t" href="#op-{{ $r->id }}" data-open="{{ $r->id }}" aria-label="{{ $r->label }}">
            @include('muestras._ph', ['r' => $r, 'tag' => 'span', 'alt' => $r->caption, 'sizes' => '(min-width:1000px) 340px, (min-width:600px) 30vw, 45vw', 'max' => 720])
          </a>
        </li>
      @endforeach
      <li class="ms-wall__end" aria-hidden="true"></li>
    </ul>
    @if($reviews->count() > 14)
      <p class="ms-reveal ms-reveal--m"><button class="mc-btn mc-btn--outline" type="button" data-reveal="wall-list">Ver todas las fotos</button></p>
    @endif
    <div class="ms-wall__all">
      @foreach($reviews as $r)
        <article class="ms-wall__a" id="op-{{ $r->id }}">
          @include('muestras._q', ['r' => $r, 'limit' => 100000, 'key' => 'w'])
          <p class="ms-by">{{ $r->caption }}</p>
        </article>
      @endforeach
    </div>
  </div>
</main>
@endsection
@push('after') @include('muestras._viewer') @endpush
