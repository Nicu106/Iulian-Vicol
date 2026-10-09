@extends('layouts.site')

@section('title', '¿Por qué IV MOTORCLASS? — Coches alemanes en Málaga')
@section('description', 'Seleccionados, revisados y preparados. Te contesto yo, te lo enseño antes de que vengas y te lo llevas a tu nombre en 30 minutos.')
@section('og_image', url(\App\Support\Img::url('/storage/why/welcome-graded.jpg', 1200) ?? '/storage/why/welcome-graded.jpg'))
@section('robots', 'noindex, nofollow')
@section('current', 'porque')

{{-- =============================================================================
     PROPOSAL 1 · "CINE" (2026-10-09). /por-que-nosotros told with the client's
     own photographs, cut like a film on the story's black. Every text is the
     live page's, word for word; only the pictures, the layout and the motion are
     new. The films are gone.

     The grammar, in four rules:
     1  Words live on the white wall of each photograph, in ink. Where a frame
        has no wall (an interior, a close detail) they are white on its darkest,
        calmest part, measured — never on a scrim.
     2  Three frames put the words BEHIND the car: wall, then words, then the car
        cut out of the same photograph (tools/media/pq1-media.py). The opening
        (4 Series Cabrio, front), "Seleccionados." (C-Class, front) and the end
        (the Cabrio again, three-quarter). The wall is carried up — and across, on
        wide screens — past the photograph's edge from its own pixels
        (<k>-ext.jpg), so the words have the room the photo never had; on a
        phone the photograph sits at the foot of the screen and the wall goes on
        above it into the night.
     3  Scrolling is the camera. On those frames the layers scale about the car's
        badge at three rates (wall 3%, words 6%, car 10%): a dolly in. Every
        other frame settles from 106% to 100%. Transform and opacity only.
     4  The cut. A match cut between the two symmetric fronts (the badge never
        moves, the car changes), a push-in from the Octavia whole to its wheel,
        quick dissolves inside a chapter, a dip to black between chapters.

     Iulian's paragraph is set as he wrote it, but each of its sentences gets its
     own picture; his four questions end on his portrait. "Te lo enseño…" is one
     car, the grey C-Class, turning through its photographs. The welcome (arms
     open) is the live page's scene, untouched: its markup, why2/why-dark and
     why.js. Without JavaScript, or with reduced motion, every frame stands on
     its own, one screen each, words in place: a quiet photo essay.
     ========================================================================== --}}

@php
  use App\Support\Img;
  $P = '/storage/why/photos/';
  $Q = '/storage/why/pq1/';
  $wa = fn ($t) => 'https://wa.me/34614753187?text=' . urlencode($t);
  $n  = fn ($v) => number_format((int) $v, 0, ',', '.');
  $lq = function ($p) { $u = Img::lqip($p); return $u ? " style=\"background:url('" . $u . "') center/cover no-repeat\"" : ''; };

  // a frame that fills the screen: the phone gets its own upright crop
  // (<k>-p.jpg, 0.56:1 around the frame's focal point), wider screens the whole
  // photograph placed by its own object-position (pq1.css). `sizes` is what the
  // slot measures: the crop is the screen's height times 0.56 wide; the
  // landscape frame is the full width, or 4/3 of the height where that is wider.
  $cover = function ($key, $photo, $first = false) use ($P, $Q, $lq) {
      $p = $Q . $key . '-p.jpg'; $d = $P . $photo . '.jpg';
      $load = $first ? 'fetchpriority="high" loading="eager"' : 'loading="lazy" decoding="async"';
      return new \Illuminate\Support\HtmlString(
        '<picture class="pq-pic">'
        . '<source media="(max-aspect-ratio: 2/3)" srcset="' . e(Img::srcset($p, 1080)) . '" sizes="56vh" width="960" height="1714">'
        . '<img src="' . e(Img::url($d, 1080) ?? $d) . '" srcset="' . e(Img::srcset($d, 2000)) . '" sizes="(min-aspect-ratio: 4/3) 100vw, 134vh" width="2400" height="1800" alt="" ' . $load . $lq($d) . '>'
        . '</picture>');
  };
  // an anchored frame: the photograph at its own 4:3, the wall carried on
  // around it (<k>-ext.jpg, a quarter-resolution canvas three photos wide)
  $box = function ($key, $photoPath, $alt = '', $first = false, $cls = 'pq-wall') use ($Q, $lq) {
      $load = $first ? 'fetchpriority="high" loading="eager"' : 'loading="lazy" decoding="async"';
      $ext = $Q . $key . '-ext.jpg';
      return new \Illuminate\Support\HtmlString(
        '<div class="pq-box ' . $cls . '">'
        . '<img class="pq-ext" src="' . e(Img::url($ext, 1080) ?? $ext) . '" srcset="' . e(Img::srcset($ext, 2000)) . '" sizes="(min-aspect-ratio: 1/1) 300vh, 360vw" width="1800" height="1305" alt="" ' . $load . '>'
        . '<img class="pq-ph" src="' . e(Img::url($photoPath, 1080) ?? $photoPath) . '" srcset="' . e(Img::srcset($photoPath, 2000)) . '" sizes="(min-aspect-ratio: 1/1) 100vh, 122vw" width="2400" height="1800" alt="' . e($alt) . '" ' . $load . $lq($photoPath) . '>'
        . '</div>');
  };
  // the car, cut out: same frame as the photograph, so it sits exactly on it.
  // No placeholder behind it (it would fill the transparent wall).
  $car = function ($key, $first = false) use ($Q) {
      $c = $Q . $key . '-car.webp';
      $load = $first ? 'fetchpriority="high" loading="eager"' : 'loading="lazy" decoding="async"';
      return new \Illuminate\Support\HtmlString(
        '<div class="pq-box pq-car"><img src="' . e(Img::url($c, 1080) ?? $c) . '" srcset="' . e(Img::srcset($c, 2000)) . '" sizes="(min-aspect-ratio: 1/1) 100vh, 122vw" width="2400" height="1800" alt="" ' . $load . '></div>');
  };
@endphp

@push('css')
<link rel="stylesheet" href="{{ asset('css/why2.css') }}">
<link rel="stylesheet" href="{{ asset('css/why-odo.css') }}">
<link rel="stylesheet" href="{{ asset('css/why-text.css') }}">
<link rel="stylesheet" href="{{ asset('css/why-dark.css') }}">
<link rel="stylesheet" href="{{ asset('css/pq1.css') }}?v={{ @filemtime(public_path('css/pq1.css')) }}">
@endpush

@push('head')
{{-- the motion version is decided before the first paint (the same test the
     scripts make), so the screen never jumps from the essay to the film --}}
<script>(function(d){var m=window.matchMedia;if(!(m&&m('(prefers-reduced-motion: reduce)').matches)&&'IntersectionObserver' in window&&window.requestAnimationFrame)d.className+=' wy-on pq-on';})(document.documentElement)</script>
{{-- the opening frame: its wall, its car --}}
<link rel="preload" as="image" imagesrcset="{{ Img::srcset($Q.'open-wall.jpg', 2000) }}" imagesizes="(min-aspect-ratio: 1/1) 100vh, 122vw" fetchpriority="high">
<link rel="preload" as="image" imagesrcset="{{ Img::srcset($Q.'open-car.webp', 2000) }}" imagesizes="(min-aspect-ratio: 1/1) 100vh, 122vw" fetchpriority="high">
@endpush

@section('content')
<main class="wy pq">

  {{-- ---- I · the opening: four words, four cars ------------------------------ --}}
  <section class="pq-ch pq-ch--first" data-pq style="--len:6.35" aria-label="Seleccionados, revisados y preparados">
    <div class="pq-stage">
      <figure class="pq-shot pq-hero pq-k-open" data-len="1.3" data-cut="night">
        {{ $box('open', $Q.'open-wall.jpg', '', true) }}
        <h1 class="pq-w pq-w--hero" data-fit=".9" data-rise>
          <span class="pq-l"><span class="pq-f">Un coche</span> <span class="pq-f">bien elegido.</span></span>
          <span class="pq-l"><span class="pq-f">Y alguien</span> <span class="pq-f">que responde.</span></span>
        </h1>
        {{ $car('open', true) }}
      </figure>
      <figure class="pq-shot pq-hero pq-k-sel" data-len="1.1" data-cut="match">
        {{ $box('sel', $Q.'sel-wall.jpg') }}
        <p class="pq-w pq-w--hero pq-w--word" data-fit=".92"><span class="pq-l">Seleccionados.</span></p>
        {{ $car('sel') }}
      </figure>
      {{-- the word on the Octavia's wall; the camera pushes in to its front wheel
           and cuts to the wheel itself: the proof, without words --}}
      <figure class="pq-shot pq-k-revw" data-len="1.2" data-cut="cut" data-push>
        {{ $cover('revw', '33_IMG_1739') }}
        <p class="pq-w pq-w--word pq-at-tl pq-ink pq-beat" data-to=".62" data-fit=".86" data-fit-d=".5"><span class="pq-l">Revisados.</span></p>
      </figure>
      <figure class="pq-shot pq-k-rev" data-len=".55" data-cut="hard">
        {{ $cover('rev', '35_IMG_1750') }}
      </figure>
      <figure class="pq-shot pq-k-prep" data-len="1" data-cut="cut">
        {{ $cover('prep', '40_IMG_1862') }}
        <p class="pq-w pq-w--word pq-at-tl pq-ink" data-fit=".86" data-fit-d=".5"><span class="pq-l">Preparados.</span></p>
      </figure>
      <figure class="pq-shot pq-k-disf" data-len="1.2" data-cut="cut">
        {{ $cover('disf', '24_IMG_1280') }}
        <p class="pq-w pq-w--line pq-at-bl pq-white pq-nw">
          <span class="pq-l">Para que disfrutes</span>
          <span class="pq-l">de algo especial.</span>
        </p>
      </figure>
      <div class="pq-veil" aria-hidden="true"></div>
    </div>
  </section>

  {{-- ---- his words, I: each sentence its picture ----------------------------- --}}
  <section class="wy-txt" aria-labelledby="wy-t1">
    <div class="cat-wrap wy-txt__in">
      <h2 class="wy-txt__h" id="wy-t1"><span>Que te enamore el coche.</span> <span>Que te dé confianza su historia.</span></h2>
      <div class="wy-txt__b">
        <p>Hay algo especial en encontrar el coche que encaja contigo.</p>
      </div>
    </div>
  </section>

  <section class="pq-ch" data-pq style="--len:3.3" aria-label="La motorización, el color, el equipamiento">
    <div class="pq-stage">
      <figure class="pq-shot pq-k-mot" data-len="1.1" data-cut="cut">
        {{ $cover('mot', '36_IMG_1757') }}
        <p class="pq-w pq-w--line pq-at-bl pq-white"><span class="pq-l">La motorización que querías.</span></p>
      </figure>
      <figure class="pq-shot pq-k-color" data-len="1.1" data-cut="cut">
        {{ $cover('color', '22_IMG_1276') }}
        <p class="pq-w pq-w--line pq-at-tl pq-ink"><span class="pq-l">El color que te hace volver a mirarlo.</span></p>
      </figure>
      <figure class="pq-shot pq-k-equip" data-len="1.1" data-cut="cut">
        {{ $cover('equip', '30_IMG_1350') }}
        <p class="pq-w pq-w--line pq-at-tr pq-ink pq-nw"><span class="pq-l">Ese equipamiento</span> <span class="pq-l">al que no quieres</span> <span class="pq-l">renunciar.</span></p>
      </figure>
      <div class="pq-veil" aria-hidden="true"></div>
    </div>
  </section>

  <section class="wy-txt wy-txt--me pq-txt-q" aria-label="Las preguntas">
    <div class="cat-wrap wy-txt__in">
      <div class="wy-txt__b">
        <p>Y, junto a esa ilusión, hay preguntas que merecen una respuesta clara:</p>
      </div>
    </div>
  </section>

  {{-- ---- the four questions; the last is answered by him --------------------- --}}
  <section class="pq-ch" data-pq style="--len:5.2" aria-labelledby="wy-me">
    <div class="pq-stage">
      <figure class="pq-shot pq-k-cuid" data-len="1" data-cut="cut">
        {{ $cover('cuid', '05_DJI_20250318_103924_729') }}
        <p class="pq-w pq-w--line pq-at-bl pq-white"><span class="pq-l">¿cómo lo han cuidado?</span></p>
      </figure>
      <figure class="pq-shot pq-k-km" data-len="1" data-cut="cut">
        {{ $cover('km', '25_IMG_1283') }}
        <p class="pq-w pq-w--line pq-at-tr pq-ink"><span class="pq-l">¿qué sabemos</span> <span class="pq-l">de sus kilómetros?</span></p>
      </figure>
      <figure class="pq-shot pq-k-acc" data-len="1" data-cut="cut">
        {{ $cover('acc', '55_IMG_2853') }}
        <p class="pq-w pq-w--line pq-at-bl pq-white pq-nw"><span class="pq-l">¿ha tenido</span> <span class="pq-l">algún accidente?</span></p>
      </figure>
      <figure class="pq-shot pq-anchor pq-k-me" data-len="2.2" data-cut="cut">
        {{ $box('me', '/storage/why/portrait.jpg', 'Iulian, fundador de IV Motorclass, con dos Mercedes-Benz descapotables') }}
        <div class="pq-w pq-w--me">
          <p class="pq-beat pq-w--line" data-to=".42"><span class="pq-l">¿quién me atenderá después?</span></p>
          <h2 class="pq-beat pq-w--line" id="wy-me" data-at=".5">
            <span class="pq-l">Una pasión personal.</span>
            <span class="pq-l">Un compromiso contigo.</span>
          </h2>
          <p class="pq-beat pq-act" data-at=".62">
            <a class="mc-btn mc-btn--cta" href="{{ $wa('Hola Iulian, me interesa un coche. ¿Hablamos?') }}">Hablar con Iulian</a>
          </p>
        </div>
      </figure>
      <div class="pq-veil" aria-hidden="true"></div>
    </div>
  </section>

  {{-- ---- his words, II --------------------------------------------------------- --}}
  <section class="wy-txt wy-txt--me" aria-label="Iulian, fundador de IV Motorclass">
    <div class="cat-wrap wy-txt__in">
      <div class="wy-txt__b">
        <p class="wy-txt__k pq-k1">En IV Motorclass, nuestra forma de trabajar empieza precisamente ahí.</p>
        <p class="wy-txt__lead">Soy Iulian, fundador de IV Motorclass. Desde pequeño me han apasionado los coches alemanes. Podía pasar horas fijándome en sus formas, sus interiores y los detalles que hacían especial una versión.</p>
        <p>Esa misma curiosidad me lleva hoy a buscar unidades con personalidad: coches que apetece conducir, conservar y disfrutar. Pero convertir una pasión en un negocio implica algo más: asumir la responsabilidad de lo que eliges y de lo que vendes.</p>
        <p class="wy-txt__lead">Por eso me implico personalmente en la selección. Detrás de cada coche que ofrecemos está mi nombre y una relación de confianza que quiero mantener mucho después de la entrega.</p>
      </div>
    </div>
  </section>

  {{-- ---- II · one car, turning: what you will be shown ------------------------- --}}
  <section class="pq-ch" data-pq style="--len:5.6" aria-label="Te lo enseño antes de que vengas">
    <div class="pq-stage">
      <figure class="pq-shot pq-anchor pq-k-show" data-len="1.2" data-cut="cut">
        {{ $box('show', $P.'13_DJI_20260329_154306_816.jpg') }}
        <h2 class="pq-w pq-w--hero pq-w--line2" data-fit=".9">
          <span class="pq-l">Te lo enseño</span>
          <span class="pq-l"><span class="pq-f">antes de</span> <span class="pq-f">que vengas.</span></span>
        </h2>
      </figure>
      <figure class="pq-shot pq-k-ext" data-len="1" data-cut="cut">
        {{ $cover('ext', '14_DJI_20260329_154326_944') }}
        <p class="pq-w pq-w--word pq-at-tl pq-ink" data-fit=".8" data-fit-d=".34"><span class="pq-l">El exterior.</span></p>
      </figure>
      <figure class="pq-shot pq-k-int" data-len="1" data-cut="cut">
        {{ $cover('int', '18_DJI_20260329_155058_223') }}
        <p class="pq-w pq-w--word pq-at-bl pq-white" data-fit=".8" data-fit-d=".46"><span class="pq-l">El interior.</span></p>
      </figure>
      <figure class="pq-shot pq-k-desp" data-len="1" data-cut="cut">
        {{ $cover('desp', '16_DJI_20260329_154527_712') }}
        <p class="pq-w pq-w--line pq-at-tl pq-ink">
          <span class="pq-l">Y sus</span>
          <span class="pq-l">desperfectos.</span>
        </p>
      </figure>
      <figure class="pq-shot pq-k-enc" data-len="1.2" data-cut="cut">
        {{ $cover('enc', '17_DJI_20260329_154542_802') }}
        <p class="pq-w pq-w--line pq-at-tl pq-ink">
          <span class="pq-l">Para que sepas</span>
          <span class="pq-l">qué te vas a encontrar.</span>
        </p>
      </figure>
      <div class="pq-veil" aria-hidden="true"></div>
    </div>
  </section>

  {{-- ---- his words, III: the heading on the paint it is about ------------------ --}}
  <section class="pq-ch" data-pq style="--len:1.2" aria-labelledby="wy-t3">
    <div class="pq-stage">
      <figure class="pq-shot pq-k-noves" data-len="1.2" data-drift>
        {{ $cover('noves', '46_IMG_1877') }}
        <h2 class="pq-w pq-w--line pq-w--h pq-at-tl pq-ink" id="wy-t3"><span class="pq-l">Lo que tú no ves a primera vista también importa.</span></h2>
      </figure>
      <div class="pq-veil" aria-hidden="true"></div>
    </div>
  </section>
  <section class="wy-txt wy-txt--me" aria-label="Lo que tú no ves a primera vista también importa">
    <div class="cat-wrap wy-txt__in">
      <div class="wy-txt__b">
        <p>Una buena configuración llama la atención. Un historial claro, un mantenimiento documentado y un estado cuidado son lo que nos da motivos para elegirla.</p>
        <p>Buscamos coches honestos: kilometraje respaldado por documentación, sin antecedentes de accidentes y con señales de haber recibido el cuidado que merecen. Priorizamos las unidades que conservan su pintura original y revisamos su estado más allá de las fotografías.</p>
        <p>Antes de entregarte el coche, lo revisamos, atendemos las necesidades detectadas y te explicamos su historial y condición. Si hay un detalle relevante para tu decisión, queremos que lo conozcas antes de tomarla.</p>
        <p class="wy-txt__k">Porque saber exactamente qué estás comprando también forma parte de disfrutarlo.</p>
      </div>
    </div>
  </section>

  {{-- ---- the welcome: the live page's scene, as it is ------------------------- --}}
  <section class="wy-still wy-still--welcome" data-scene="still" aria-labelledby="wy-keys">
    <div class="wy-stage">
      <div class="wy-photo">
        <x-img src="/storage/why/welcome-graded.jpg" alt="Iulian delante de cuatro coches preparados para entregar"
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

  {{-- ---- his words, IV: the car leaving ----------------------------------------- --}}
  <section class="pq-ch" data-pq style="--len:1.2" aria-labelledby="wy-t4">
    <div class="pq-stage">
      <figure class="pq-shot pq-k-conf" data-len="1.2">
        {{ $cover('conf', '48_IMG_1880') }}
        <h2 class="pq-w pq-w--line pq-w--h pq-at-tl pq-ink" id="wy-t4"><span class="pq-l">La confianza se demuestra después de la entrega.</span></h2>
      </figure>
      <div class="pq-veil" aria-hidden="true"></div>
    </div>
  </section>
  <section class="wy-txt wy-txt--me" aria-label="La confianza se demuestra después de la entrega">
    <div class="cat-wrap wy-txt__in">
      <div class="wy-txt__b">
        <p class="wy-txt__lead">Cuando te llevas las llaves, nuestro compromiso continúa.</p>
        <p>Trabajamos con empresas especializadas para ofrecer una garantía de cobertura nacional que proteja los principales componentes del vehículo, según las condiciones contratadas. Antes de decidir, te explicamos qué incluye y cómo utilizarla.</p>
        <p>Si surge una incidencia, tienes a quién llamar. Te escuchamos, revisamos contigo lo ocurrido y nos implicamos en su gestión, manteniéndote informado de los siguientes pasos.</p>
        <p class="wy-txt__k">Así entendemos el trato personal: conocerte cuando buscas un coche y seguir respondiendo cuando ya es tuyo.</p>
      </div>
    </div>
  </section>

  {{-- ---- the record ------------------------------------------------------- --}}
  <section class="wy-proof" aria-label="En cifras">
    <ul class="wy-proof__l cat-wrap">
      <li><b data-count="{{ $years }}" data-prefix="+">+{{ $years }}</b><span>años de experiencia</span></li>
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

  {{-- ---- the way in: the opening car again, the words behind it ---------------- --}}
  <section class="pq-ch pq-ch--end" data-pq style="--len:1.3" aria-labelledby="pq-end-h">
    <div class="pq-stage">
      <figure class="pq-shot pq-hero pq-k-end" data-len="1.3">
        {{ $box('end', $Q.'end-wall.jpg') }}
        <h2 class="pq-w pq-w--hero pq-w--line2" id="pq-end-h" data-fit=".9">
          <span class="pq-l"><span class="pq-f">Hay coches</span> <span class="pq-f">que llevas</span></span>
          <span class="pq-l"><span class="pq-f">tiempo</span> <span class="pq-f">imaginando.</span></span>
        </h2>
        {{ $car('end') }}
      </figure>
      <div class="pq-veil" aria-hidden="true"></div>
    </div>
  </section>
  <section class="wy-end pq-end">
    <div class="cat-wrap wy-end__in">
      <p class="wy-end__p">Puede ser tu primer BMW, un cabrio para disfrutar de la costa o esa versión concreta que rara vez aparece. Queremos ayudarte a encontrar una unidad que reúna la ilusión de tenerla y la tranquilidad de haber elegido bien.</p>
      <p class="wy-end__p">Descubre nuestra selección o cuéntanos qué coche tienes en mente.</p>
      <div class="wy-end__act">
        <a class="mc-btn wy-end__ghost" href="/catalogo">Ver coches disponibles</a>
        <a class="mc-btn mc-btn--cta" href="{{ $wa('Hola Iulian, quiero hablar de mi próximo coche.') }}">Hablar con Iulian</a>
      </div>
      <p class="wy-end__sign"><b>IV Motorclass</b> <span>Coches honestos. Personas que responden.</span></p>
    </div>
  </section>

</main>
@endsection

@push('js')
<script src="{{ asset('js/pq1.js') }}?v={{ @filemtime(public_path('js/pq1.js')) }}" defer onerror="document.documentElement.classList.remove('pq-on')"></script>
{{-- the welcome is the live scene: its own engine, unchanged --}}
<script src="{{ asset('js/why.js') }}" defer onerror="document.documentElement.classList.remove('wy-on')"></script>
<script src="{{ asset('js/why-odo.js') }}" defer></script>
<script src="{{ asset('js/why-text.js') }}" defer></script>
@endpush
