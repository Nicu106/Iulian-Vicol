@extends('muestras.layout')
@section('title', 'Opiniones · ' . $variant[0])
@section('content')
{{-- 2 · Cine. One review at a time: the photograph large at its own shape,
     the words beside it (under it on a phone). It moves on by itself, slowly —
     a crossfade, never a slide — and stays on each review as long as its
     words take to read. Any touch, hover or keyboard focus holds it; pressing
     a control hands it over to the visitor for good. Under reduced motion it
     never moves by itself. Without script: a row to swipe through. --}}
<main class="ms-sec ms-v2" aria-labelledby="h-op">
  @include('muestras._head')
  <div class="ms-wrap">
    <section class="ms-cine" data-cine aria-roledescription="carrusel" aria-label="Opiniones de clientes">
      <div class="ms-cine__stage ms-stack" data-stage>
        @foreach($reviews as $i => $r)
          <figure class="ms-cine__s ms-stack__s {{ $i === 0 ? 'is-on' : '' }}" id="cine-{{ $r->id }}"
                  aria-roledescription="opinión" aria-label="{{ $r->name }}">
            <div class="ms-cine__ph">
              @include('muestras._ph', ['r' => $r, 'sizes' => '(min-width:1000px) 640px, calc(100vw - 32px)', 'max' => 1600])
            </div>
            <figcaption class="ms-cine__txt">
              @include('muestras._q', ['r' => $r, 'limit' => 260, 'limitM' => 140, 'key' => 'c', 'mode' => 'dialog'])
              <p class="ms-by">{{ $r->caption }}</p>
            </figcaption>
          </figure>
        @endforeach
      </div>
      <div class="ms-cine__bar">
        <button class="ms-ctl" type="button" data-go="-1" aria-label="Opinión anterior">
          <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M15 4 7 12l8 8" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/></svg>
        </button>
        <button class="ms-ctl" type="button" data-play aria-label="Pausar">
          <svg class="ms-i-pause" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M8 5v14M16 5v14" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
          <svg class="ms-i-play" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M8 5l11 7-11 7z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="miter"/></svg>
        </button>
        <button class="ms-ctl" type="button" data-go="1" aria-label="Opinión siguiente">
          <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M9 4l8 8-8 8" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/></svg>
        </button>
        <span class="ms-cine__time" aria-hidden="true"><span></span></span>
      </div>
      <p class="ms-sr" aria-live="polite" data-live></p>
    </section>
  </div>
</main>
@endsection
@push('after') @include('muestras._viewer') @endpush
