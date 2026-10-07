@extends('layouts.site')

@section('title', '¿Por qué IV MOTORCLASS? — Coches alemanes en Málaga')
@section('description', 'Seleccionados, revisados y preparados. Te contesto yo, te lo enseño antes de que vengas y te lo llevas a tu nombre en 30 minutos.')
@section('og_image', url(\App\Support\Img::url('/storage/why/welcome.jpg', 1600) ?? '/storage/why/welcome.jpg'))
@section('current', 'porque')

@push('css')
<link rel="stylesheet" href="{{ asset('css/why2.css') }}">
@endpush

@push('head')
{{-- the opening film's first frame, so the screen is never empty while it loads --}}
<link rel="preload" as="image" href="/storage/why/a-phone.webp" media="(max-aspect-ratio: 1/1)" fetchpriority="high">
<link rel="preload" as="image" href="/storage/why/a-desk.webp" media="(min-aspect-ratio: 1/1)" fetchpriority="high">
@endpush

@php
  $wa = fn ($t) => 'https://wa.me/34614753187?text=' . urlencode($t);
  $n  = fn ($v) => number_format((int) $v, 0, ',', '.');
@endphp

{{-- =============================================================================
     DIRECTION (2026-10-07). A story told by scrolling, in four scenes, with his
     own footage. Words are few and large; every line is one he has already
     written on the site. No labels, no numbering — the client: small texts and
     enumerations look cheap.

     1  FILM A   the row of cars from the front. One word per car:
                 Seleccionados → Revisados → Preparados → "para que disfrutes…"
     2  PORTRAIT him at sunset between two Mercedes. The photograph settles as
                 you scroll; who answers, line by line; WhatsApp.
     3  FILM B   walking round the same cars from behind: what you will be shown.
     4  WELCOME  arms open in front of the four cars. The photograph opens from
                 the centre to the whole screen: the keys are yours.

     The scroll drives each film (currentTime follows the page). Files encoded
     for it: 24 fps, a keyframe every half second, no B-frames. Phones get their
     own upright crop (540×960): one car at a time, full screen.
     Without JavaScript, or with reduced motion: a normal page — still frames,
     the words beneath them (why.css, html:not(.wy-on)).
     ========================================================================== --}}

@section('content')
<main class="wy">

  {{-- ---- 1 · film A ------------------------------------------------------- --}}
  <section class="wy-film wy-film--a" data-scene="film" aria-label="Seleccionados, revisados y preparados">
    <div class="wy-stage">
      <video class="wy-video" muted playsinline preload="metadata" aria-hidden="true"
             data-phone="/storage/why/a-phone.mp4" data-desk="/storage/why/a-desk.mp4"
             data-poster-phone="/storage/why/a-phone.webp" data-poster-desk="/storage/why/a-desk.webp"></video>
      <div class="wy-copy">
        <h1 class="wy-beat wy-beat--open" data-in="0" data-out=".2">
          <span class="wy-l">Un coche bien elegido.</span>
          <span class="wy-l">Y alguien que responde.</span>
        </h1>
        <p class="wy-beat wy-beat--word" data-in=".24" data-out=".44"><span class="wy-l">Seleccionados.</span></p>
        <p class="wy-beat wy-beat--word" data-in=".47" data-out=".66"><span class="wy-l">Revisados.</span></p>
        <p class="wy-beat wy-beat--word" data-in=".69" data-out=".86"><span class="wy-l">Preparados.</span></p>
        <p class="wy-beat wy-beat--line" data-in=".89" data-out="2">
          <span class="wy-l">Para que disfrutes</span>
          <span class="wy-l">de algo especial.</span>
        </p>
      </div>
    </div>
  </section>

  {{-- ---- 2 · portrait ------------------------------------------------------ --}}
  <section class="wy-still wy-still--portrait" data-scene="still" aria-labelledby="wy-me">
    <div class="wy-stage">
      <div class="wy-photo">
        <x-img src="/storage/why/portrait.jpg" alt="El director de IV Motorclass con dos Mercedes-Benz descapotables"
               sizes="(min-aspect-ratio: 1/1) 62vw, 100vw" :max="2000" :fallback="1080" />
      </div>
      <div class="wy-copy wy-copy--ink">
        <h2 class="wy-beat wy-beat--line" id="wy-me" data-in=".1" data-out="2">
          <span class="wy-l">Soy el director</span>
          <span class="wy-l">de IV Motorclass.</span>
        </h2>
        <p class="wy-beat wy-beat--sub" data-in=".3" data-out="2">
          <span class="wy-l">Te contesto yo y te acompaño durante toda la compra,</span>
          <span class="wy-l">sin pasarte de una persona a otra.</span>
        </p>
        <p class="wy-beat wy-beat--act" data-in=".45" data-out="2">
          <a class="mc-btn mc-btn--cta" href="{{ $wa('Hola, me interesa un coche. ¿Hablamos?') }}">Hablemos por WhatsApp</a>
        </p>
      </div>
    </div>
  </section>

  {{-- ---- 3 · film B ------------------------------------------------------- --}}
  <section class="wy-film wy-film--b" data-scene="film" aria-label="Te lo enseño antes de que vengas">
    <div class="wy-stage">
      <video class="wy-video" muted playsinline preload="none" aria-hidden="true"
             data-phone="/storage/why/b-phone.mp4" data-desk="/storage/why/b-desk.mp4"
             data-poster-phone="/storage/why/b-phone.webp" data-poster-desk="/storage/why/b-desk.webp"></video>
      <div class="wy-copy">
        <h2 class="wy-beat wy-beat--line" data-in="-1" data-out=".24">
          <span class="wy-l">Te lo enseño</span>
          <span class="wy-l">antes de que vengas.</span>
        </h2>
        <p class="wy-beat wy-beat--word" data-in=".28" data-out=".42"><span class="wy-l">El exterior.</span></p>
        <p class="wy-beat wy-beat--word" data-in=".45" data-out=".58"><span class="wy-l">El interior.</span></p>
        <p class="wy-beat wy-beat--word" data-in=".61" data-out=".74"><span class="wy-l">Y sus desperfectos.</span></p>
        <p class="wy-beat wy-beat--line" data-in=".78" data-out="2">
          <span class="wy-l">Para que sepas</span>
          <span class="wy-l">qué te vas a encontrar.</span>
        </p>
      </div>
    </div>
  </section>

  {{-- ---- 4 · welcome ------------------------------------------------------- --}}
  <section class="wy-still wy-still--welcome" data-scene="still" aria-labelledby="wy-keys">
    <div class="wy-stage">
      <div class="wy-photo">
        <x-img src="/storage/why/welcome.jpg" alt="El director de IV Motorclass delante de cuatro coches preparados para entregar"
               sizes="100vw" :max="2000" :fallback="1080" />
      </div>
      <div class="wy-copy wy-copy--wall">
        <h2 class="wy-beat wy-beat--line" id="wy-keys" data-in=".38" data-out="2">
          <span class="wy-l">Las llaves son tuyas.</span>
          <span class="wy-l">Mi teléfono sigue disponible.</span>
        </h2>
        <p class="wy-beat wy-beat--sub" data-in=".55" data-out="2">
          <span class="wy-l">En 30 minutos, a tu nombre y con el seguro en vigor.</span>
          <span class="wy-l">Y después de la compra, me sigues teniendo al teléfono.</span>
        </p>
      </div>
    </div>
  </section>

  {{-- ---- the record ------------------------------------------------------- --}}
  <section class="wy-proof" aria-label="En cifras">
    <ul class="wy-proof__l cat-wrap">
      <li><b data-count="{{ $sold }}">{{ $n($sold) }}</b><span>coches entregados</span></li>
      <li><b data-count="{{ $reviewCount }}">{{ $n($reviewCount) }}</b><span>clientes en la foto de la entrega</span></li>
      <li><b data-count="{{ $plays }}">{{ $n($plays) }}</b><span>reproducciones de nuestros vídeos</span></li>
      <li><b data-count="{{ $farthest }}">{{ $n($farthest) }}</b><span>kilómetros hizo un cliente para comprar aquí</span></li>
    </ul>
  </section>

  {{-- ---- in their words --------------------------------------------------- --}}
  @if($reviews->count())
  <section class="wy-say cat-wrap" aria-labelledby="wy-say-h">
    <h2 class="wy-say__h" id="wy-say-h">Lo cuentan ellos</h2>
    <ul class="wy-say__l">
      @foreach($reviews->take(3) as $t)
        <li class="wy-q">
          <figure>
            <div class="wy-q__ph">
              <x-img :src="$t->image_path" :alt="$t->author_name.', el día de la entrega'"
                     sizes="(min-width:900px) 360px, 82vw" :max="1080" :fallback="720" />
            </div>
            <blockquote class="wy-q__t">{{ trim($t->quote) }}</blockquote>
            <figcaption class="wy-q__by">{{ $t->author_name }}</figcaption>
          </figure>
        </li>
      @endforeach
    </ul>
    <a class="mc-link wy-say__all" href="/#reviews">Todas las opiniones</a>
  </section>
  @endif

  {{-- ---- the way in -------------------------------------------------------- --}}
  <section class="wy-end">
    <div class="cat-wrap wy-end__in">
      <h2 class="wy-end__h">¿Hablamos de tu próximo coche?</h2>
      <p class="wy-end__p">Cuéntame qué buscas. Si no lo tengo ahora, te lo busco.</p>
      <div class="wy-end__act">
        <a class="mc-btn mc-btn--cta" href="{{ $wa('Hola, quiero hablar de mi próximo coche.') }}">Escríbeme por WhatsApp</a>
        <a class="mc-btn wy-end__ghost" href="/catalogo">Ver los coches</a>
      </div>
    </div>
  </section>

</main>
@endsection

@push('js')
<script src="{{ asset('js/why.js') }}" defer></script>
@endpush
