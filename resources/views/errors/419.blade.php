{{-- The form's security token expired: usually a page left open for hours on a
     phone. Nothing is lost by saying so plainly and offering the way back. --}}
@extends('layouts.site')
@section('title', 'Página caducada — IV MOTORCLASS')
@section('current', '')
@section('robots', 'noindex, nofollow')
@section('content')
<main class="cat-wrap" style="padding-block:var(--s-8)">
  <p style="margin:0 0 var(--s-3);font-size:var(--t-label);color:var(--mc-ink-3)">Error 419</p>
  <h1 style="margin:0 0 var(--s-4);font-size:var(--t-display);line-height:1.04;letter-spacing:-.024em;max-width:16ch">La página llevaba mucho tiempo abierta.</h1>
  <p style="margin:0 0 var(--s-5);font-size:var(--t-sub);line-height:1.5;color:var(--mc-ink-2);max-width:44ch">Por seguridad, el formulario caduca. Vuelve atrás, recarga la página y envíalo otra vez — o escríbeme directamente por WhatsApp.</p>
  <div style="display:flex;flex-wrap:wrap;gap:var(--s-2);max-width:30rem">
    <a class="mc-btn mc-btn--cta" style="flex:1 1 12rem;justify-content:center" href="https://wa.me/34614753187">WhatsApp</a>
    <a class="mc-btn mc-btn--ghost" style="flex:1 1 12rem;justify-content:center" href="{{ url()->previous() }}">Volver</a>
  </div>
</main>
@endsection
