@extends('muestras.layout')
@section('title', 'Opiniones · ' . $variant[0])
@section('content')
{{-- 3 · Carril. Cards in a row the thumb moves, as a phone's own apps do:
     native scrolling, snapping card by card, the next one showing at the
     edge so the row says it continues. The photograph sits in a box of the
     library's most common shape, or 4:5 if that is taller, so a phone shows
     the photograph and the words together; the rest sit whole on the band. Long words open in the viewer. --}}
<main class="ms-sec ms-v3" aria-labelledby="h-op">
  @include('muestras._head')
  <div class="ms-rail" data-rail>
    <ul class="ms-rail__list" role="list" tabindex="0" aria-label="Opiniones de clientes">
      @foreach($reviews as $r)
        <li class="ms-rail__i">
          <figure class="ms-card">
            @include('muestras._ph', ['r' => $r, 'box' => max($boxRatio, 0.8), 'sizes' => '(min-width:1000px) 340px, min(78vw, 340px)', 'max' => 1080])
            <figcaption class="ms-card__txt">
              @include('muestras._q', ['r' => $r, 'limit' => 120, 'key' => 'r', 'mode' => 'dialog'])
              <p class="ms-by">{{ $r->caption }}</p>
            </figcaption>
          </figure>
        </li>
      @endforeach
    </ul>
    <div class="ms-wrap ms-rail__nav">
      <button class="ms-ctl" type="button" data-step="-1" aria-label="Anteriores">
        <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M15 4 7 12l8 8" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/></svg>
      </button>
      <button class="ms-ctl" type="button" data-step="1" aria-label="Siguientes">
        <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M9 4l8 8-8 8" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/></svg>
      </button>
    </div>
  </div>
</main>
@endsection
@push('after') @include('muestras._viewer') @endpush
