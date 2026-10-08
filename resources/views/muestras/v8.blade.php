@extends('muestras.layout')
@section('title', 'Opiniones · ' . $variant[0])
@section('content')
{{-- 8 · Álbum. A calm contact sheet: every photograph whole, standing on one
     line in its cell — the cells share a shape, the pictures keep theirs, and
     the cell's ground is the section's, so nothing is drawn around them — the
     name underneath. A touch opens the review right there, across the full
     width under that row, with a mark pointing back at the picture; another
     touch moves it, the same one closes it. Without script each cell simply
     carries its words. A phone shows eight first. --}}
<main class="ms-sec ms-v8" aria-labelledby="h-op">
  @include('muestras._head')
  <div class="ms-wrap">
    <ul class="ms-alb" role="list" data-album id="alb-list">
      @foreach($reviews as $i => $r)
        <li class="ms-alb__i {{ $i >= 8 ? 'is-later-m' : '' }}" data-id="{{ $r->id }}">
          <button class="ms-alb__t" type="button" aria-expanded="false" aria-controls="alb-panel">
            @include('muestras._ph', ['r' => $r, 'tag' => 'span', 'box' => $boxRatio, 'cls' => 'ms-alb__ph', 'sizes' => '(min-width:1264px) 210px, (min-width:1000px) 17vw, (min-width:600px) 30vw, 45vw', 'max' => 720])
            <span class="ms-alb__cap">{{ $r->caption }}</span>
          </button>
          <div class="ms-alb__txt">
            @include('muestras._q', ['r' => $r, 'limit' => 100000, 'key' => 'a'])
          </div>
        </li>
      @endforeach
    </ul>
    @if($reviews->count() > 8)
      <p class="ms-reveal ms-reveal--m"><button class="mc-btn mc-btn--outline" type="button" data-reveal="alb-list">Ver todas las fotos</button></p>
    @endif
  </div>
</main>
@endsection
@push('after') <script type="application/json" id="ms-data">{!! $json !!}</script> @endpush
