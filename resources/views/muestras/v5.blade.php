@extends('muestras.layout')
@section('title', 'Opiniones · ' . $variant[0])
@section('content')
{{-- 5 · Protagonista. One review, set large — the words lead, the
     photograph beside them — and under it every customer, by their own
     photograph, to choose who to read next. Type steps with length: a line
     is set at display size, a letter at reading size with the rest in the
     viewer, so a short review fills the stage instead of floating in it.
     The stage is held at the tallest review's height, so choosing never
     moves the row being chosen from. --}}
<main class="ms-sec ms-v5" aria-labelledby="h-op">
  @include('muestras._head')
  <div class="ms-wrap ms-spot" data-spot>
    <div class="ms-spot__stage ms-stack" data-stage aria-live="polite">
      @foreach($reviews as $i => $r)
        <figure class="ms-spot__s ms-stack__s {{ $i === 0 ? 'is-on' : '' }}" id="sp-{{ $r->id }}">
          <figcaption class="ms-spot__words">
            @include('muestras._q', ['r' => $r, 'limit' => 230, 'key' => 's', 'mode' => 'dialog', 'cls' => 'ms-spot__q'])
            <p class="ms-by">{{ $r->caption }}</p>
          </figcaption>
          <div class="ms-spot__ph">
            @include('muestras._ph', ['r' => $r, 'sizes' => '(min-width:1000px) 480px, calc(100vw - 32px)', 'max' => 1080])
          </div>
        </figure>
      @endforeach
    </div>
    <div class="ms-spot__pick" role="group" aria-label="Elige una opinión">
      @foreach($reviews as $i => $r)
        <button class="ms-spot__b" type="button" data-pick="{{ $i }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}"
                aria-controls="sp-{{ $r->id }}" aria-label="{{ $r->label }}" style="--r:{{ $r->box }}">
          <x-img :src="$r->img" alt="" sizes="96px" max="320" />
        </button>
      @endforeach
    </div>
  </div>
</main>
@endsection
@push('after') @include('muestras._viewer') @endpush
