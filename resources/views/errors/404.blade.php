{{-- Not found. Most often a car that has been sold and removed, or an old link
     from Google. Not a dead end: say so plainly, show what is here now, and the
     two ways to ask. --}}
@extends('layouts.site')

@section('title', 'Página no encontrada — IV MOTORCLASS')
@section('current', '')
@section('robots', 'noindex, follow')

@php
  $others = \App\Models\Vehicle::where('status', 'available')->orderByDesc('updated_at')->take(6)->get();
  $chips  = \App\Http\Controllers\BrandCatalogController::chips();
  $sub    = \App\Http\Controllers\BrandCatalogController::sub();
  $euros  = fn ($n) => number_format((int) $n, 0, ',', '.') . ' €';
  $km     = fn ($n) => number_format((int) $n, 0, ',', '.') . ' km';
  $isCar  = str_starts_with(request()->path(), 'coche/');
@endphp

@push('css')
<style>
  .nf{ padding-block:var(--s-8) var(--s-6); }
  .nf__k{ margin:0 0 var(--s-3); font-size:var(--t-label); color:var(--mc-ink-3); }
  .nf__h{ margin:0 0 var(--s-4); font-size:var(--t-display); line-height:1.04; letter-spacing:-.024em; color:var(--mc-ink); max-width:16ch; }
  .nf__p{ margin:0 0 var(--s-5); font-size:var(--t-sub); line-height:1.5; color:var(--mc-ink-2); max-width:44ch; }
  .nf__act{ display:flex; flex-wrap:wrap; gap:var(--s-2); max-width:30rem; }
  .nf__act .mc-btn{ flex:1 1 12rem; justify-content:center; }
  .nf__more{ padding-bottom:var(--s-8); }
  .nf__head{ display:flex; align-items:baseline; justify-content:space-between; gap:var(--s-3); flex-wrap:wrap; }
  .nf__head .mc-link{ display:inline-flex; align-items:center; min-height:44px; }
  .nf__rail{ display:flex; gap:var(--s-4); overflow-x:auto; scroll-snap-type:x mandatory; overscroll-behavior-x:contain;
             scrollbar-width:none; padding:var(--s-2) 0 var(--s-3); margin-top:var(--s-3); }
  .nf__rail::-webkit-scrollbar{ display:none; }
  .nf__rail > *{ flex:0 0 15.5rem; scroll-snap-align:start; }
  @media (max-width:768px){
    .nf__rail{ margin-inline:calc(var(--mc-gutter-m) * -1); padding-inline:var(--mc-gutter-m); scroll-padding-inline:var(--mc-gutter-m); }
    .nf__rail > *{ flex:0 0 72vw; max-width:19rem; }
  }
</style>
@endpush

@section('content')
<main class="cat-wrap">
  <section class="nf">
    <p class="nf__k">Error 404</p>
    <h1 class="nf__h">{{ $isCar ? 'Este coche ya no está aquí.' : 'Esta página no existe.' }}</h1>
    <p class="nf__p">{{ $isCar
        ? 'Probablemente ya se vendió. Dime qué buscas y te digo si tengo algo parecido, o te lo busco.'
        : 'Puede que el enlace sea antiguo. Los coches están en el catálogo, y si buscas algo concreto, escríbeme.' }}</p>
    <div class="nf__act">
      <a class="mc-btn mc-btn--cta" href="https://wa.me/34614753187?text={{ urlencode('Hola, busco un coche. ¿Me ayudas?') }}">WhatsApp</a>
      <a class="mc-btn mc-btn--ghost" href="/catalogo">Ver el catálogo</a>
    </div>
  </section>

  @if($others->count())
  <section class="nf__more" aria-labelledby="h-nf">
    <div class="nf__head">
      <h2 class="car-h2 hm-h2" id="h-nf">Disponibles ahora</h2>
      <a class="mc-link" href="/catalogo">Ver todos →</a>
    </div>
    <div class="nf__rail home-rail">
      @foreach($others as $car)
        @include('partials.card', ['car' => $car, 'kind' => 'available'])
      @endforeach
    </div>
  </section>
  @endif
</main>
@endsection
