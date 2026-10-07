@extends('layouts.site')

@section('title', '¿Por qué IV MOTORCLASS? — Coches alemanes en Málaga')
@section('description', 'Seleccionados, revisados y preparados. Te contesto yo, te lo enseño antes de que vengas y te lo llevas a tu nombre en 30 minutos.')
@section('og_image', url(\App\Support\Img::url('/storage/why/welcome.jpg', 1600) ?? '/storage/why/welcome.jpg'))
@section('current', 'porque')

@push('css')
<link rel="stylesheet" href="{{ asset('css/why2.css') }}">
<link rel="stylesheet" href="{{ asset('css/why-odo.css') }}">
@endpush

@push('head')
{{-- the film layout is decided before the first paint, by the same test why.js
     makes: deferred, why.js could arrive after the page had already painted the
     plain version (stylesheets cached from another page, why.js not), and the
     screen jumped from a poster over a navy block of words to the film --}}
<script>(function(d){var m=window.matchMedia;if(!(m&&m('(prefers-reduced-motion: reduce)').matches)&&'IntersectionObserver' in window&&window.requestAnimationFrame)d.className+=' wy-on';})(document.documentElement)</script>
{{-- the opening film's first frame, so the screen is never empty while it loads --}}
<link rel="preload" as="image" href="/storage/why/film/a-p.webp" media="(max-aspect-ratio: 1/1)" fetchpriority="high">
<link rel="preload" as="image" href="/storage/why/film/a-d.webp" media="(min-aspect-ratio: 1/1)" fetchpriority="high">
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

     The films PLAY, natively, in chapters (2026-10-07, fourth pass — the
     client found scrubbed frames choppy). Each film is cut at the cars: one
     chapter per line of text, its end on a composed frame. Scrolling into a
     line plays the film on to the end of its chapter and holds there; the words
     arrive with it. Back, or a long jump forward: a short dissolve to the
     chapter's held frame, never a reverse scrub. Encoded from his originals at
     twice the speed, 60 fps (every second source frame, an even cadence),
     graded once — film A darker and moodier with a vignette deepest where the
     words sit (white words at 7:1 or better), film B the lighter version of
     it — and a keyframe on every chapter end: /storage/why/film/<a|b>-p.mp4
     upright 608×1080 for phones, -d 1600×900 (tools/media/why-films.sh).
     One easing curve for everything else, driven by the smoothed scroll: films
     rise out of the page white and dissolve back into it, every picture settles
     from 106% to 100%, the shade sits only under the words. The opening fades
     up from night; the first words land, then the film moves. Details in
     public/js/why.js.
     Without JavaScript, or with reduced motion: a normal page — the first
     frames as still pictures, the words beneath them (html:not(.wy-on)).
     ========================================================================== --}}

@section('content')
<main class="wy">

  {{-- ---- 1 · film A ------------------------------------------------------- --}}
  <section class="wy-film wy-film--a" data-scene="film" aria-label="Seleccionados, revisados y preparados">
    <div class="wy-stage">
      <picture class="wy-poster">
        <source media="(min-aspect-ratio: 1/1)" srcset="/storage/why/film/a-d.webp" width="1600" height="900">
        <img src="/storage/why/film/a-p.webp" alt="" width="608" height="1080" fetchpriority="high">
      </picture>
      <video class="wy-video" muted playsinline disableremoteplayback preload="none" aria-hidden="true"
             data-p="/storage/why/film/a-p.mp4" data-d="/storage/why/film/a-d.mp4" data-ends="1.75,4,6.5,9.25,12.3"></video>
      <canvas class="wy-canvas" aria-hidden="true"></canvas>
      <div class="wy-shade" aria-hidden="true"></div>
      <div class="wy-veil" aria-hidden="true"></div>
      <div class="wy-copy">
        <h1 class="wy-beat wy-beat--open" data-in="-1" data-out=".18">
          <span class="wy-l">Un coche bien elegido.</span>
          <span class="wy-l">Y alguien que responde.</span>
        </h1>
        <p class="wy-beat wy-beat--word" data-in=".21" data-out=".41"><span class="wy-l">Seleccionados.</span></p>
        <p class="wy-beat wy-beat--word" data-in=".44" data-out=".63"><span class="wy-l">Revisados.</span></p>
        <p class="wy-beat wy-beat--word" data-in=".66" data-out=".84"><span class="wy-l">Preparados.</span></p>
        <p class="wy-beat wy-beat--line" data-in=".86" data-out="2">
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
               sizes="(min-aspect-ratio: 1/1) 134vh, 86vh" :max="2000" :fallback="1080" />
      </div>
      <div class="wy-copy wy-copy--ink">
        <h2 class="wy-beat wy-beat--line" id="wy-me" data-in=".04" data-out="2">
          <span class="wy-l">Soy el director</span>
          <span class="wy-l">de IV Motorclass.</span>
        </h2>
        <p class="wy-beat wy-beat--sub" data-in=".2" data-out="2">
          <span class="wy-l">Te contesto yo y te acompaño durante toda la compra,</span>
          <span class="wy-l">sin pasarte de una persona a otra.</span>
        </p>
        <p class="wy-beat wy-beat--act" data-in=".34" data-out="2">
          <a class="mc-btn mc-btn--cta" href="{{ $wa('Hola, me interesa un coche. ¿Hablamos?') }}">Hablemos por WhatsApp</a>
        </p>
      </div>
    </div>
  </section>

  {{-- ---- 3 · film B ------------------------------------------------------- --}}
  <section class="wy-film wy-film--b" data-scene="film" aria-label="Te lo enseño antes de que vengas">
    <div class="wy-stage">
      <picture class="wy-poster">
        <source media="(min-aspect-ratio: 1/1)" srcset="/storage/why/film/b-d.webp" width="1600" height="900">
        <img src="/storage/why/film/b-p.webp" alt="" width="608" height="1080" loading="lazy">
      </picture>
      <video class="wy-video" muted playsinline disableremoteplayback preload="none" aria-hidden="true"
             data-p="/storage/why/film/b-p.mp4" data-d="/storage/why/film/b-d.mp4" data-ends="2.5,5.75,8.5,11.25,15"></video>
      <canvas class="wy-canvas" aria-hidden="true"></canvas>
      <div class="wy-shade" aria-hidden="true"></div>
      <div class="wy-veil" aria-hidden="true"></div>
      <div class="wy-copy">
        <h2 class="wy-beat wy-beat--line" data-in="-1" data-out=".22">
          <span class="wy-l">Te lo enseño</span>
          <span class="wy-l">antes de que vengas.</span>
        </h2>
        <p class="wy-beat wy-beat--word" data-in=".25" data-out=".43"><span class="wy-l">El exterior.</span></p>
        <p class="wy-beat wy-beat--word" data-in=".46" data-out=".63"><span class="wy-l">El interior.</span></p>
        <p class="wy-beat wy-beat--word" data-in=".66" data-out=".83"><span class="wy-l">Y sus desperfectos.</span></p>
        <p class="wy-beat wy-beat--line" data-in=".86" data-out="2">
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
        <x-img src="/storage/why/welcome-graded.jpg" alt="El director de IV Motorclass delante de cuatro coches preparados para entregar"
               sizes="(max-aspect-ratio: 4/3) 134vh, 100vw" :max="2000" :fallback="1080" />
      </div>
      <div class="wy-copy wy-copy--wall">
        <h2 class="wy-beat wy-beat--line" id="wy-keys" data-in=".3" data-out="2">
          <span class="wy-l">Las llaves son tuyas.</span>
          <span class="wy-l">Mi teléfono sigue disponible.</span>
        </h2>
        <p class="wy-beat wy-beat--sub" data-in=".46" data-out="2">
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
        @php $d = \App\Support\Img::size($t->image_path); @endphp
        <li class="wy-q">
          <figure>
            <div class="wy-q__ph" style="--r: {{ $d && $d[1] ? round(max(.6, min(1.6, $d[0] / $d[1])), 4) : .75 }}">
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
{{-- if it cannot load, the plain page (the class set in <head> comes off) --}}
<script src="{{ asset('js/why.js') }}" defer onerror="document.documentElement.classList.remove('wy-on')"></script>
<script src="{{ asset('js/why-odo.js') }}" defer></script>
@endpush
