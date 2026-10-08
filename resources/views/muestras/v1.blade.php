@extends('muestras.layout')
@section('title', 'Opiniones · ' . $variant[0])
@section('theme', '#05080F')
@section('body', 'ms-night')
@section('content')
{{-- 1 · La entrega — in the language of /por-que-nosotros: the films' black,
     a stage that holds still while the scroll brings one customer at a time.
     Each customer is a screen: the delivery photograph whole (`contain`, its
     own shape, never cut), standing in its own light — the same photograph
     blurred far past recognition, darkened and enlarged behind it (a 2 KB
     pre-rendered file, ReviewShowroomController::ambient) — and the words
     beside it (computer) or under it (phone), large, with the name. A long
     review shows its opening, cut at a sentence end, and "Leer más" opens it
     whole. The change from one customer to the next is a crossfade on time,
     with the photograph settling from 104% (why.js does the same), never
     scrubbed. After the sequence, quietly, every review.
     Reduced motion, no script: the same customers as a plain list, one under
     the other, photograph above, words below. --}}
@php $C = \App\Http\Controllers\ReviewShowroomController::class; @endphp
<main class="ms-v1" aria-labelledby="h-op">
  @unless($embed)
    <div class="en__head">@include('muestras._head')</div>
  @else
    <h2 class="ms-sr" id="h-op">La confianza se gana. Ellos te cuentan cómo.</h2>
  @endunless
  <section class="en" data-entrega style="--n:{{ $beats->count() }}" aria-label="Clientes, uno a uno">
    <div class="en__stage">
      @foreach($beats as $k => $r)
        @php $c = $C::sentences($r->text, 190); $amb = $C::ambient($r->img); @endphp
        <article class="en__b{{ $k === 0 ? ' is-on' : '' }}" aria-label="{{ $r->caption }}">
          <div class="en__amb" aria-hidden="true" @if($amb) style="background-image:url('{{ $amb }}')" @endif></div>
          <div class="en__in">
            <figure class="en__ph">
              <x-img :src="$r->img" :alt="$r->caption" :sizes="'(min-aspect-ratio: 1/1) min(46vw, 60vh), min(92vw, 38vh)'" :max="1080" :priority="$k === 0" class="en__img" />
            </figure>
            <div class="en__w">
              <blockquote class="en__q"><p>«{{ $c['short'] }}»</p></blockquote>
              <p class="en__by">{{ $r->caption }}</p>
              @if($c['cut'])
                <button class="ms-link en__more" type="button" data-open="{{ $r->id }}" aria-haspopup="dialog">Leer más<span class="ms-sr">: {{ $r->name }}</span></button>
              @endif
            </div>
          </div>
        </article>
      @endforeach
      <div class="en__bar" aria-hidden="true"><i></i></div>
    </div>
  </section>
  <section class="en-all" aria-label="Todas las opiniones">
    <p class="en-all__go"><button class="ms-link" type="button" aria-expanded="false" aria-controls="en-list" data-en-all>Ver todas las opiniones</button></p>
    <ul class="en-all__l" id="en-list" role="list">
      @foreach($reviews as $r)
        <li class="en-all__i">
          <x-img :src="$r->img" :alt="$r->caption" :sizes="'(min-width:1100px) 340px, (min-width:700px) 45vw, 92vw'" :max="720" class="en-all__img" />
          <blockquote class="en-all__q">@foreach($r->paras as $p)<p>{{ $p }}</p>@endforeach</blockquote>
          <p class="en-all__by">{{ $r->caption }}</p>
        </li>
      @endforeach
    </ul>
  </section>
</main>
@endsection
@push('after') @include('muestras._viewer') @endpush
