@extends('muestras.layout')
@section('title', 'Opiniones · ' . $variant[0])
@section('content')
{{-- 9 · Las palabras. The words first. Type size follows length: a single
     line is set at display size, a short paragraph a step down, a letter at
     reading size and cut with "Leer más" — so the shortest review is the
     loudest instead of the emptiest. The photograph is a small companion
     beside the name; pressed, it opens whole in the viewer. A phone shows
     four, cut shorter, and the rest on a press: never a wall of text. --}}
<main class="ms-sec ms-v9" aria-labelledby="h-op">
  @include('muestras._head')
  <div class="ms-wrap">
    <ul class="ms-words" role="list" id="words-list">
      @foreach($reviews as $i => $r)
        <li class="ms-words__i {{ $i >= 4 ? 'is-later-m' : '' }} {{ $i >= 8 ? 'is-later' : '' }}" tabindex="-1">
          <figure class="ms-words__f">
            @include('muestras._q', ['r' => $r, 'limit' => 300, 'limitM' => 150, 'key' => 'p', 'cls' => 'ms-words__q'])
            <figcaption class="ms-words__by">
              <button class="ms-words__ph" type="button" data-open="{{ $r->id }}" aria-haspopup="dialog" aria-label="Ver la foto: {{ $r->caption }}" style="--r:{{ $r->box }}">
                <x-img :src="$r->img" alt="" sizes="160px" max="480" />
              </button>
              <span class="ms-by">{{ $r->caption }}</span>
            </figcaption>
          </figure>
        </li>
      @endforeach
    </ul>
    @if($reviews->count() > 4)
      <p class="ms-reveal"><button class="mc-btn mc-btn--outline" type="button" data-reveal="words-list">Ver todas las opiniones</button></p>
    @endif
  </div>
</main>
@endsection
@push('after') @include('muestras._viewer') @endpush
