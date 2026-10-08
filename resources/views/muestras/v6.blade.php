@extends('muestras.layout')
@section('title', 'Opiniones · ' . $variant[0])
@section('content')
{{-- 6 · Dos filas. Two rows drifting slowly in opposite directions, at the
     speed the contact strips use (34 px/s), the velocity damped rather than
     switched so nothing jolts. A row stops under the pointer, under a finger
     — and can be dragged — and stops when the keyboard is inside it. A
     keyboard-only control stops everything (WCAG 2.2.2); reduced motion and
     no script give two still rows to scroll by hand. --}}
<main class="ms-sec ms-v6" aria-labelledby="h-op">
  @include('muestras._head')
  <div class="ms-drift" data-drift>
    <button class="ms-halt" type="button" data-halt>Detener el movimiento</button>
    @foreach([0, 1] as $row)
      <div class="ms-drift__row" data-dir="{{ $row ? 1 : -1 }}">
        <div class="ms-drift__belt">
          <ul class="ms-drift__track" role="list" aria-label="Opiniones de clientes{{ $row ? ' (2)' : '' }}">
            @foreach($reviews as $i => $r)
              @continue($i % 2 !== $row)
              <li class="ms-drift__i">
                <figure class="ms-dcard">
                  @include('muestras._ph', ['r' => $r, 'sizes' => '(min-width:1000px) 380px, 300px', 'max' => 720])
                  <figcaption class="ms-dcard__txt">
                    @include('muestras._q', ['r' => $r, 'limit' => 84, 'key' => 'd', 'mode' => 'dialog'])
                    <p class="ms-by">{{ $r->caption }}</p>
                  </figcaption>
                </figure>
              </li>
            @endforeach
          </ul>
        </div>
      </div>
    @endforeach
  </div>
</main>
@endsection
@push('after') @include('muestras._viewer') @endpush
