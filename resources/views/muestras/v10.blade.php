@extends('muestras.layout')
@section('title', 'Opiniones · ' . $variant[0])
@section('content')
{{-- 10 · Destacadas. The three most complete reviews — chosen by length, so
     it keeps choosing as reviews are added — set as letters: whole photograph
     on top, the photographs standing on one line whatever their shapes, the
     words on white below. Everyone else waits behind "Ver todas las
     opiniones", in a quieter list. A phone shows two letters first. --}}
@php
  $top  = $reviews->sortByDesc('len')->take(3)->sortBy(fn ($r) => $reviews->search($r))->values();
  $rest = $reviews->reject(fn ($r) => $top->contains('id', $r->id))->values();
  $shelf = $top->min('box') ?: 0.75;
@endphp
<main class="ms-sec ms-v10" aria-labelledby="h-op">
  @include('muestras._head')
  <div class="ms-wrap">
    <ul class="ms-feat" role="list" id="feat-top">
      @foreach($top as $i => $r)
        <li class="ms-feat__i {{ $i >= 2 ? 'is-later-m' : '' }}" tabindex="-1">
          <figure class="ms-feat__f">
            @include('muestras._ph', ['r' => $r, 'box' => $shelf, 'cls' => 'ms-feat__ph', 'sizes' => '(min-width:1264px) 373px, (min-width:900px) calc((100vw - 128px) / 3), calc(100vw - 32px)', 'max' => 1080])
            <figcaption class="ms-feat__txt">
              @include('muestras._q', ['r' => $r, 'limit' => 300, 'limitM' => 170, 'key' => 't'])
              <p class="ms-by">{{ $r->caption }}</p>
            </figcaption>
          </figure>
        </li>
      @endforeach
    </ul>
    @if($rest->isNotEmpty())
      <p class="ms-reveal ms-feat__open"><button class="mc-btn mc-btn--outline" type="button" data-reveal="feat-top feat-rest" aria-controls="feat-rest">Ver todas las opiniones</button></p>
      <ul class="ms-rest" role="list" id="feat-rest">
        @foreach($rest as $r)
          <li class="ms-rest__i is-later" tabindex="-1">
            <figure class="ms-rest__f">
              @include('muestras._ph', ['r' => $r, 'cls' => 'ms-rest__ph', 'sizes' => '120px', 'max' => 480])
              <figcaption>
                @include('muestras._q', ['r' => $r, 'limit' => 160, 'key' => 'o'])
                <p class="ms-by">{{ $r->caption }}</p>
              </figcaption>
            </figure>
          </li>
        @endforeach
      </ul>
    @endif
  </div>
</main>
@endsection
