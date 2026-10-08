@extends('muestras.layout')
@section('title', 'Opiniones · ' . $variant[0])
@section('content')
{{-- 1 · Mosaico. Every photograph whole, at its own shape, in columns, the
     words under it on the section's own ground — no card, no frame. Two
     columns on a phone, four on a computer; the script lays them out
     shortest-first, so the order still reads across (without it, CSS columns
     hold the same picture). A phone gets a short cut of the words; the
     picture and "Leer más" open the review whole. Six first on a phone (two screens),
     twelve on a computer, the rest on one press. --}}
<main class="ms-sec ms-v1" aria-labelledby="h-op">
  @include('muestras._head')
  <div class="ms-wrap">
    <div class="ms-mas" data-mosaic id="mas-list">
      @foreach($reviews as $i => $r)
        <figure class="ms-mas__i {{ $i >= 6 ? 'is-later-m' : '' }} {{ $i >= 12 ? 'is-later' : '' }}" id="op-{{ $r->id }}" tabindex="-1">
          <button class="ms-tap" type="button" data-open="{{ $r->id }}" aria-haspopup="dialog" aria-label="Leer entera: {{ $r->label }}">
            @include('muestras._ph', ['r' => $r, 'tag' => 'span', 'sizes' => '(min-width:1264px) 276px, (min-width:1100px) calc((100vw - 160px) / 4), (min-width:700px) calc((100vw - 96px) / 2), calc(50vw - 24px)', 'max' => 720])
          </button>
          <figcaption class="ms-mas__txt">
            @include('muestras._q', ['r' => $r, 'limit' => 170, 'limitM' => 72, 'key' => 'm', 'mode' => 'dialog'])
            <p class="ms-by">{{ $r->caption }}</p>
          </figcaption>
        </figure>
      @endforeach
    </div>
    @if($reviews->count() > 6)
      <p class="ms-reveal"><button class="mc-btn mc-btn--outline" type="button" data-reveal="mas-list">Ver todas las opiniones</button></p>
    @endif
  </div>
</main>
@endsection
@push('after') @include('muestras._viewer') @endpush
