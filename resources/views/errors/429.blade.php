{{-- Rate limit reached on a form. --}}
@extends('layouts.site')
@section('title', 'Demasiados intentos — IV MOTORCLASS')
@section('current', '')
@section('robots', 'noindex, nofollow')
@section('content')
<main class="cat-wrap" style="padding-block:var(--s-8)">
  <p style="margin:0 0 var(--s-3);font-size:var(--t-label);color:var(--mc-ink-3)">Error 429</p>
  <h1 style="margin:0 0 var(--s-4);font-size:var(--t-display);line-height:1.04;letter-spacing:-.024em;max-width:16ch">Demasiados envíos seguidos.</h1>
  <p style="margin:0 0 var(--s-5);font-size:var(--t-sub);line-height:1.5;color:var(--mc-ink-2);max-width:44ch">Para frenar el spam, el formulario se bloquea un rato después de varios envíos. Inténtalo más tarde, o escríbeme ahora por WhatsApp: contesto yo.</p>
  <div style="display:flex;flex-wrap:wrap;gap:var(--s-2);max-width:30rem">
    <a class="mc-btn mc-btn--cta" style="flex:1 1 12rem;justify-content:center" href="https://wa.me/34614753187">WhatsApp</a>
    <a class="mc-btn mc-btn--ghost" style="flex:1 1 12rem;justify-content:center" href="{{ url()->previous() }}">Volver</a>
  </div>
</main>
@endsection
