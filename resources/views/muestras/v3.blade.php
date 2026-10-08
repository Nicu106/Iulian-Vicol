@extends('muestras.layout')
@section('title', 'Opiniones · ' . $variant[0])
@section('theme', '#FFFFFF')
@section('body', 'ms-paper')
@section('content')
{{-- 3 · Sus palabras — the strongest phrase of a review, word for word
     (ReviewShowroomController::PHRASES, each checked to be a verbatim part of
     the review), set as large as the screen allows, lighting line by line
     as the scroll moves through it — the way Apple sets a quotation: the
     lines are there, pale, and the reading brings them up. Scrubbed, so
     linear. Under it, small and whole, the customer's photograph, the name,
     and the whole review a touch away.
     On paper, not on the night: 1 and 2 already live on the black, and
     type is the whole idea here — ink on white is how a quotation is set
     when it has to be believed (a printed page, not a film), and it keeps
     the three ideas apart at a glance.
     Reduced motion, no script: the phrases one under the other, fully set,
     each with its photograph and the review itself. --}}
<main class="ms-v3" aria-labelledby="h-op">
  @unless($embed)
    <div class="pl__head">@include('muestras._head')</div>
  @else
    <h2 class="ms-sr" id="h-op">La confianza se gana. Ellos te cuentan cómo.</h2>
  @endunless
  <section class="pl" data-palabras style="--n:{{ $beats->count() }}" aria-label="Sus palabras">
    <div class="pl__stage">
      @foreach($beats as $k => $r)
        <figure class="pl__b{{ $k === 0 ? ' is-on' : '' }}">
          <blockquote class="pl__q"><p class="pl__p">«{{ $r->phrase }}»</p></blockquote>
          <figcaption class="pl__by">
            <x-img :src="$r->img" :alt="$r->caption" :sizes="'(min-width:1000px) 220px, 140px'" :max="480" class="pl__img" />
            <span class="pl__who">
              <span class="pl__name">{{ $r->caption }}</span>
              <button class="ms-link pl__more" type="button" data-open="{{ $r->id }}" aria-haspopup="dialog">Leer la opinión completa<span class="ms-sr">: {{ $r->name }}</span></button>
              <details class="pl__all"><summary class="ms-link">Leer la opinión completa</summary>
                @foreach($r->paras as $p)<p>{{ $p }}</p>@endforeach
              </details>
            </span>
          </figcaption>
        </figure>
      @endforeach
    </div>
  </section>
</main>
@endsection
@push('after') @include('muestras._viewer') @endpush
