@extends('layouts.site')
@section('title', 'Propuestas — ¿Por qué IV MOTORCLASS?')
@section('robots', 'noindex, nofollow')
@section('content')
<main class="cat-wrap" style="padding-block:var(--s-8)">
  <h1 style="margin:0 0 var(--s-3);font-size:clamp(2rem,7vw,3.4rem);line-height:1.02;letter-spacing:-.035em">¿Por qué IV MOTORCLASS?<br>Propuestas con tus fotos</h1>
  <p style="margin:0 0 var(--s-7);max-width:40rem;color:var(--mc-ink-2);font-size:var(--t-prose)">Cada propuesta es la página entera, con los mismos textos de ahora. Ábrela en el móvil y en el ordenador y elige una.</p>
  <ol style="list-style:none;margin:0;padding:0;display:grid;gap:var(--s-5)">
    @foreach($variants as $n => [$name, $what])
      <li style="border-top:1px solid var(--mc-hairline);padding-top:var(--s-5)">
        <h2 style="margin:0 0 var(--s-2);font-size:var(--t-h2);letter-spacing:-.02em">{{ $name }}</h2>
        <p style="margin:0 0 var(--s-4);max-width:40rem;color:var(--mc-ink-2)">{{ $what }}</p>
        <a class="mc-btn mc-btn--cta" href="/muestras/por-que/{{ $n }}" style="min-height:48px">Abrir la propuesta</a>
      </li>
    @endforeach
  </ol>
</main>
@endsection
