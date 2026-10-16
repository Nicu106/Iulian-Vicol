{{-- SHOWROOM 2.5 (custom phone design), copied from the live page; the live header note follows.
     /por-que-nosotros — "Galería", live since 2026-10-16: proposal 2.4 from the
     showroom (/muestras/por-que/2.4, tag por-que-v2.4), with its own assets
     (css/pq25.css, js/pq25.js, copies of pq24.*) so later showroom work never
     reaches the live page. Photos in storage/app/public/why/{photos,pq2,pq21,pq22,pq23}
     — do not delete those folders. The earlier film page: tag por-que-live-films. --}}
@extends('layouts.site')

@section('title', '¿Por qué IV MOTORCLASS? — Coches alemanes en Málaga')
@section('description', 'Seleccionados, revisados y preparados. Te contesto yo, te lo enseño antes de que vengas y te lo llevas a tu nombre en 30 minutos.')
@section('og_image', url(\App\Support\Img::url('/storage/why/welcome-graded.jpg', 1200) ?? '/storage/why/welcome-graded.jpg'))
@section('robots', 'noindex, nofollow')
@section('current', 'porque')

@push('css')
<link rel="stylesheet" href="{{ asset('css/pq25.css') }}?v={{ @filemtime(public_path('css/pq25.css')) }}">
@endpush

@push('head')
{{-- the motion version is decided before the first paint (the same test pq2.js
     makes), so a reveal never hides a picture that was already painted; if the
     script never arrives the class comes off and the page is simply there --}}
<script>(function(d){var m=window.matchMedia;if(!(m&&m('(prefers-reduced-motion: reduce)').matches)&&'IntersectionObserver' in window&&window.requestAnimationFrame){d.className+=' pq-on';setTimeout(function(){if(!window.pq2)d.classList.remove('pq-on');},6000);}})(document.documentElement)</script>
@endpush

@php
  $wa = fn ($t) => 'https://wa.me/34614753187?text=' . urlencode($t);
  $n  = fn ($v) => number_format((int) $v, 0, ',', '.');
  // his photographs, graded as one set (storage/app/public/why/pq2/NN.jpg: the wall
  // of every picture made the same neutral grey, in linear light; recipe in the commit)
  $ph = fn ($k) => sprintf('/storage/why/pq2/%02d.jpg', $k);
  // a plate's photograph with its own wall carried on above it (pq2/tall-NN.jpg):
  // one picture, so there is no seam to hide and the plate may move like any other
  $tall = fn ($k) => sprintf('/storage/why/pq2/tall-%02d.jpg', $k);
  // the same plate for wide screens (pq2/dk-NN.jpg): exactly the window the plate
  // shows there, cut out of the tall picture, under 4 MP. WebKit draws bigger bitmaps
  // subsampled, and a 2000 x 3200 tall derivative read soft on a desktop.
  $dk = fn ($k) => sprintf('/storage/why/pq2/dk-%02d.jpg', $k);
  // frame mode: the media query pq2.css uses for it, word for word
  $frame = '(min-width: 1000px), (min-aspect-ratio: 1/1)';
  $src = fn ($k) => \App\Support\Img::srcset($dk($k), 2000);
  // 2.3 (2): the three plates as wide as the grid (show, after, end) draw 1296 css px at
  // 1440 x 900 @2 = 2592 device px; the 2000 ladder top was upscaled 1.3x. Their dk files are
  // 2400 wide: wideSrcset offers them whole (1600, 2000, then the file itself)
  $srcw = fn ($k) => \App\Support\Img::wideSrcset($dk($k));
  // 2.1, screens 2200 px and wider only: the two full-bleed pictures from files
  // wide enough for them (Img::wideSrcset, why/ files only, up to 3840 w). The
  // cover is pq21/dkw-22.jpg, dk-22 rebuilt from the original HEIC at 4153 px
  // (same grade, same window); a phone never matches these sources.
  $wide = '(min-width: 2200px)';
  // 2.5: an upright phone (the media query pq25.css scopes the phone design with). Its
  // stages draw the plates a whole screen tall: pq25/tall-NNx.jpg is pq2/tall-NN with his
  // wall carried on above (2400 x 5520, 2.3:1), so the wall reaches the top of any phone
  $phone = '(max-width: 599px) and (max-aspect-ratio: 1/1)';
  $tallx = fn ($k) => \App\Support\Img::srcset(sprintf('/storage/why/pq25/tall-%02dx.jpg', $k), 1600);
@endphp

{{-- =============================================================================
     PROPOSAL 2 · GALERÍA (2026-10-09). /por-que-nosotros as a car magazine,
     from the client's own photographs. Every text is the live page's, in the
     live page's order; the two films are gone.

     The page is daylight, like /inicio and /catalogo: white paper, generous
     margins, an editorial grid. The story is NOT on black here. His photographs
     are daylight catalogue shots on one pale wall; on black every one of them
     became a bright grey rectangle and the wall stopped being paper. On white
     the wall and the page are the same material, so the words can step from
     the page onto the wall and back — that is the whole idea.

     One rule: the big lines are set in ink on his wall (a "plate").
       Every plate is ONE picture: pq2/tall-NN.jpg, his photograph with its own
       wall carried on above it (per column the tone of the top band, so the panel
       joints run on straight; a grain matched to his; the last rows fade into a
       mirror of the photo's first rows). No seam exists, so the plate can move.
       phones        the plate is the words, then a 4:3 window: the picture is
                     anchored at its foot, the whole photograph shows under the
                     words and the car is never cropped
       wide screens  the plate is a window on the same picture, the words set on
                     the wall inside it in cqi; --cut trims the asphalt so the
                     wall, not the car, gets the room
     All his photographs are graded as one set (pq2/NN.jpg): the wall of each is
     the same neutral grey (sRGB 208) in linear light, close shots white balance
     only, a soft shoulder so the white leather never clips.
     Where a photograph has no wall (details, interiors) the words sit beside
     it on the page, like a caption, at reading size or larger — never small.

     Third pass (2026-10-10), a magazine's pacing: two picture scales on a phone
     (the whole width, or the quiet one stepped in to the 38% line / two side by
     side), type and air between them, four spacing steps (pq2.css). The cover is
     one screen: upright, the Cabrio from three quarters (tall-20) standing at the
     foot with the words just above its windscreen; wide, the same car in profile
     (dk-22) edge to edge. Wide plates are pq2/dk-NN.jpg, the window cut out under
     4 MP (WebKit subsamples bigger bitmaps). Reading text in ink, paragraphs
     indented; his story's last sentence set in place as the pull quote; the
     record as a numbers page; their words as a letters page; the end signed
     with his line, the buttons in the page's ink.

     Scenes: cover (2.3: 40 everywhere) · Seleccionados (2.3: 12) / Revisados (35) / Preparados (51)
     · Para que disfrutes (24) · his first text with three details (36, 21 in 2.3, 30)
     · the portrait and his own story · Te lo enseño (14) and the grey C-Class
     walked round (17, 18, 16): a swipe on phones, a spread on desks · what you
     don't see (46) · the welcome, edge to edge: the one bold moment · after
     the delivery (48) · the record · their words · the end (27).

     Motion is quiet and transform/opacity only: a picture arrives through its
     own frame (the frame clips, the picture rises into it and its scale settles
     slowly), and drifts a little inside the frame as you scroll (scrubbed,
     linear). On a plate the words come a beat after their wall. The welcome's
     frame opens from 90% to the whole width (scrubbed). No JS or reduced motion:
     the same page, still.
     ========================================================================== --}}

@section('content')
<main class="pq">

  {{-- 2.5: on an upright phone the cover and the three words are ONE stage (pq25.css,
       pq25.js): a screen that holds still while the photograph under the words changes.
       Everywhere else (and with reduced motion) the two wrappers are display: contents
       and the page is exactly the one above. --}}
  <div class="pq-run pq-run--a" data-stage="0.26 0.22 0.22 0.26"><div class="pq-run__v">
  {{-- ---- the cover ---------------------------------------------------------- --}}
  <section class="pq-cover" aria-labelledby="pq-h1" data-layer="base">
    <div class="pq-plate pq-plate--cover">
      <div class="pq-plate__in">
        <h1 class="pq-w" id="pq-h1"><span>Un coche bien elegido.</span> <span>Y alguien que responde.</span></h1>
        <div class="pq-win" aria-hidden="true"></div>
        {{-- 2.3: one photograph for every screen, photo 40 (IMG_1862) retouched from the
             HEIC: the black C-Class from three quarters. Upright: pq23/tall-40 (the car at
             115% of the width, its nose on the left edge, the wall carried on above); every
             other screen: pq23/dk-40, the same car with his wall and asphalt carried on
             either side, drawn from the screen's height (pq23.css) --}}
        @php $dk40 = '/storage/why/pq23/dk-40.jpg'; $lq40 = \App\Support\Img::lqip($dk40); @endphp
        <div class="pq-f"@if($lq40) style="--lq:url('{{ $lq40 }}')"@endif><div class="pq-px"><picture>
          {{-- drawn 2.54 x the cover's height wide (pq23.css: car at 55% of it) --}}
          <source media="{{ $frame }}" srcset="{{ \App\Support\Img::wideSrcset($dk40) }}" sizes="(max-aspect-ratio: 4/5) 182vw, calc(254vh - 183px)">
          {{-- 2.3 (2): upright, the WHOLE car: pq23/tall-40w, cut from dk-40 with the car at 90%
               of the width and centred, his wall carried on above (2000 x 4600) --}}
          <x-img src="/storage/why/pq23/tall-40w.jpg" alt="Mercedes-Benz Clase C negro con llantas AMG, de tres cuartos, ante una pared blanca"
                 sizes="100vw" :max="1600" :fallback="1080" :priority="true" />
        </picture></div></div>
      </div>
    </div>
  </section>

  {{-- ---- seleccionados, revisados, preparados ---------------------------------- --}}
  <section class="pq-three pq-wrap" aria-label="Seleccionados, revisados y preparados">
    <div class="pq-plate pq-plate--sel" data-r data-layer="base">
      <div class="pq-plate__in">
        <p class="pq-w">Seleccionados.</p>
        <div class="pq-win" aria-hidden="true"></div>
        <div class="pq-f pq-whole"><div class="pq-px"><picture>
          {{-- 2.3: photo 40 is the cover now; this plate is photo 12, the grey C-Class
               from three quarters (pq23/tall-12, dk-12: the set's grade and cut) --}}
          <source media="{{ $phone }}" srcset="{{ $tallx(12) }}" sizes="100vw">
          <source media="{{ $frame }}" srcset="{{ \App\Support\Img::srcset('/storage/why/pq23/dk-12.jpg', 2000) }}" sizes="(min-width: 1000px) min(62vw, 960px), 100vw">
          <x-img src="/storage/why/pq23/tall-12.jpg" alt="Mercedes-Benz Clase C gris, de tres cuartos"
                 sizes="100vw" :max="1600" />
        </picture></div></div>
      </div>
    </div>
    {{-- the word above its picture, like the two on the wall: each of the three
         words is the top-left corner of its own photograph --}}
    <figure class="pq-pic pq-pic--rev" data-layer="foot">
      <figcaption class="pq-word">Revisados.</figcaption>
      <div class="pq-f" data-r><div class="pq-f__in"><div class="pq-px">
        <x-img :src="$ph(35)" alt="Rueda del Škoda Octavia RS con la pinza de freno roja"
               sizes="(min-width: 1000px) 37vw, 88vw" :max="1600" />
      </div></div></div>
    </figure>
    {{-- 2.4: ONLY the two texts swapped (the client), every frame where it was: "Preparados."
         now stands under the leather, where its line was; "Para que disfrutes de algo
         especial." on the silver C-Class's wall, where "Preparados." was. The leather comes
         first in the source so "Preparados." is read first; on a phone pq24.css keeps the
         plate shown before it, as in 2.3 --}}
    <figure class="pq-pic pq-pic--joy" data-layer="foot">
      <div class="pq-f" data-r><div class="pq-f__in"><div class="pq-px">
        <x-img :src="$ph(24)" alt="El interior de cuero blanco del BMW Serie 4 Cabrio, visto desde arriba"
               sizes="(min-width: 1000px) 56vw, (min-width: 600px) 92vw, 167vw" :max="2000" />
      </div></div></div>
      <figcaption class="pq-word">Preparados.</figcaption>
    </figure>
    <div class="pq-plate pq-plate--pre" data-r data-layer="base">
      <div class="pq-plate__in">
        <p class="pq-w"><span>Para que disfrutes</span> <span>de algo especial.</span></p>
        <div class="pq-win" aria-hidden="true"></div>
        <div class="pq-f pq-whole"><div class="pq-px"><picture>
          <source media="{{ $phone }}" srcset="{{ $tallx(51) }}" sizes="100vw">
          <source media="{{ $frame }}" srcset="{{ $src(51) }}" sizes="(min-width: 1000px) min(62vw, 960px), 100vw">
          <x-img :src="$tall(51)" alt="Mercedes-Benz Clase C plata AMG, de tres cuartos"
                 sizes="100vw" :max="1600" />
        </picture></div></div>
      </div>
    </div>
  </section>
  </div></div>

  {{-- ---- his words, I: three details, his own sentences as their captions ------- --}}
  <section class="pq-love pq-wrap" aria-labelledby="pq-t1">
    <h2 class="pq-h" id="pq-t1"><span>Que te enamore el coche.</span> <span>Que te dé confianza su historia.</span></h2>
    <p class="pq-lede">Hay algo especial en encontrar el coche que encaja contigo.</p>
    <div class="pq-caps">
      <figure class="pq-cap pq-cap--a">
        <div class="pq-f" data-r><div class="pq-f__in"><div class="pq-px">
          <x-img :src="$ph(36)" alt="La parrilla negra y el faro del Škoda Octavia RS"
                 sizes="(min-width: 1000px) min(52vw, 830px), (min-width: 600px) 100vw, 167vw" :max="2000" />
        </div></div></div>
        <figcaption>La motorización que querías.</figcaption>
      </figure>
      <figure class="pq-cap pq-cap--b">
        <div class="pq-f pq-whole" data-r><div class="pq-f__in"><div class="pq-px">
          {{-- 2.3 (2): photo 27 from the HEIC (P3 -> sRGB, the c27 grade), the WHOLE car on every
               screen: the frame carried on 260 px before the front wheel and 160 px past the
               bumper, so wheels and tail have air: his wall mirrored row by row, his asphalt as
               2-D blocks moved 1:1 from the same rows (the drain grate runs on), never stretched. 3:2 on a phone, the
               page's whole width (pq23/27w); 16:9 on a desk (pq23/dk-27w). The car is the subject:
               it is never scaled past its frame (no settle overhang, no drift) --}}
          <picture>
            <source media="(min-width: 1000px)" srcset="{{ \App\Support\Img::srcset('/storage/why/pq23/dk-27w.jpg', 2000) }}" sizes="min(36vw, 560px)">
            <x-img src="/storage/why/pq23/27w.jpg" alt="El BMW Serie 4 Cabrio azul marino visto desde atrás, con su cuero blanco"
                   sizes="100vw" :max="1600" />
          </picture>
        </div></div></div>
        <figcaption>El color que te hace volver a mirarlo.</figcaption>
      </figure>
      <figure class="pq-cap pq-cap--c">
        <div class="pq-f" data-r><div class="pq-f__in"><div class="pq-px">
          <x-img :src="$ph(30)" alt="Puerta del BMW Serie 4 Cabrio: cuero blanco y altavoz harman/kardon"
                 sizes="(min-width: 1000px) min(36vw, 560px), 104vw" :max="2000" />
        </div></div></div>
        <figcaption>Ese equipamiento al que no quieres renunciar.</figcaption>
      </figure>
    </div>
    <div class="pq-read">
      <p>Y, junto a esa ilusión, hay preguntas que merecen una respuesta clara: ¿cómo lo han cuidado?, ¿qué sabemos de sus kilómetros?, ¿ha tenido algún accidente?, ¿quién me atenderá después?</p>
      <p class="pq-k">En IV&nbsp;Motorclass, nuestra forma de trabajar empieza precisamente ahí.</p>
    </div>
  </section>

  {{-- ---- the portrait, and his own story ------------------------------------- --}}
  <section class="pq-me pq-wrap" aria-labelledby="pq-me">
    <h2 class="pq-h pq-me__h" id="pq-me"><span>Una pasión personal.</span> <span>Un compromiso contigo.</span></h2>
    <figure class="pq-pic pq-me__ph">
      <div class="pq-f" data-r><div class="pq-f__in"><div class="pq-px">
        <x-img src="/storage/why/portrait.jpg" alt="Iulian, fundador de IV Motorclass, con dos Mercedes-Benz descapotables"
               sizes="(min-width: 1000px) min(73vw, calc((100vh - 120px) * 1.33)), (min-width: 600px) 92vw, 167vw" :max="2000" />
      </div></div></div>
    </figure>
    <div class="pq-read pq-me__t">
      <p class="pq-lead">Soy Iulian, fundador de IV&nbsp;Motorclass. Desde pequeño me han apasionado los coches alemanes. Podía pasar horas fijándome en sus formas, sus interiores y los detalles que hacían especial una versión.</p>
      <p>Esa misma curiosidad me lleva hoy a buscar unidades con personalidad: coches que apetece conducir, conservar y disfrutar. Pero convertir una pasión en un negocio implica algo más: asumir la responsabilidad de lo que eliges y de lo que vendes.</p>
      <p>Por eso me implico personalmente en la selección.</p>
      {{-- his paragraph's last sentence, where it stands, set as the page's pull quote --}}
      <p class="pq-pull">Detrás de cada coche que ofrecemos está mi nombre y una relación de confianza que quiero mantener mucho después de la entrega.</p>
      <p class="pq-act"><a class="mc-btn mc-btn--cta" href="{{ $wa('Hola Iulian, me interesa un coche. ¿Hablamos?') }}">Hablar con Iulian</a></p>
    </div>
  </section>

  {{-- ---- te lo enseño: one car, walked round ---------------------------------- --}}
  <section class="pq-show" aria-labelledby="pq-show">
    {{-- 2.5: on an upright phone the plate and the walk-round are one stage (as above) --}}
    <div class="pq-run pq-run--b" data-stage="0.22 0.22 0.22 0.16" data-lead="0.06"><div class="pq-run__v">
    <div class="pq-wrap">
      <div class="pq-plate pq-plate--show" data-r data-layer="base" data-keep>
        <div class="pq-plate__in">
          <h2 class="pq-w" id="pq-show"><span>Te lo enseño</span> <span>antes de que vengas.</span></h2>
          <div class="pq-win" aria-hidden="true"></div>
          <div class="pq-f pq-whole"><div class="pq-px"><picture>
            <source media="{{ $phone }}" srcset="{{ $tallx(14) }}" sizes="100vw">
            <source media="{{ $frame }}" srcset="{{ $srcw(14) }}" sizes="(min-width: 1000px) min(90vw, 1440px), 100vw">
            <x-img :src="$tall(14)" alt="Mercedes-Benz Clase C gris, de tres cuartos"
                   sizes="100vw" :max="1600" />
          </picture></div></div>
        </div>
      </div>
    </div>
    <ul class="pq-gal" tabindex="0" aria-label="El exterior, el interior y sus desperfectos">
      <li class="pq-slide pq-slide--ext" data-layer="foot">
        <figure>
          <div class="pq-f pq-whole" data-r><div class="pq-f__in"><div class="pq-px">
            <x-img :src="$ph(17)" alt="El mismo Clase C gris por detrás, de tres cuartos"
                   sizes="(min-width: 1000px) min(50vw, 800px), 86vw" :max="1600" />
          </div></div></div>
          <figcaption class="pq-word">El exterior.</figcaption>
        </figure>
      </li>
      <li class="pq-slide pq-slide--int" data-layer="foot">
        <figure>
          <div class="pq-f" data-r><div class="pq-f__in"><div class="pq-px">
            <x-img :src="$ph(18)" alt="El interior del mismo Clase C: volante, pantalla y consola"
                   sizes="(min-width: 1000px) min(50vw, 800px), 86vw" :max="1600" />
          </div></div></div>
          <figcaption class="pq-word">El interior.</figcaption>
        </figure>
      </li>
      <li class="pq-slide pq-slide--det" data-layer="foot">
        <figure>
          <div class="pq-f" data-r><div class="pq-f__in"><div class="pq-px">
            <x-img :src="$ph(16)" alt="El lateral del mismo Clase C de cerca: puertas, pintura y llanta"
                   sizes="(min-width: 1000px) min(50vw, 800px), 86vw" :max="1600" />
          </div></div></div>
          <figcaption class="pq-word">Y sus desperfectos.</figcaption>
        </figure>
      </li>
    </ul>
    <p class="pq-wrap pq-line pq-show__end" data-layer="line"><span>Para que sepas</span> <span>qué te vas a encontrar.</span></p>
    </div></div>
  </section>

  {{-- ---- his words, III: what you don't see -------------------------------- --}}
  <section class="pq-paint pq-wrap" aria-labelledby="pq-t3">
    <h2 class="pq-h pq-paint__h" id="pq-t3">Lo que tú no ves a primera vista también importa.</h2>
    <figure class="pq-pic pq-paint__ph">
      <div class="pq-f" data-r><div class="pq-f__in"><div class="pq-px">
        <x-img :src="$ph(46)" alt="Las puertas del Mercedes-Benz Clase C negro reflejan la pared como un espejo"
               sizes="(min-width: 1000px) 78vw, (min-width: 600px) 92vw, 167vw" :max="2000" />
      </div></div></div>
    </figure>
    <div class="pq-read pq-paint__t">
      <p>Una buena configuración llama la atención. Un historial claro, un mantenimiento documentado y un estado cuidado son lo que nos da motivos para elegirla.</p>
      <p>Buscamos coches honestos: kilometraje respaldado por documentación, sin antecedentes de accidentes y con señales de haber recibido el cuidado que merecen. Priorizamos las unidades que conservan su pintura original y revisamos su estado más allá de las fotografías.</p>
      <p>Antes de entregarte el coche, lo revisamos, atendemos las necesidades detectadas y te explicamos su historial y condición. Si hay un detalle relevante para tu decisión, queremos que lo conozcas antes de tomarla.</p>
      <p class="pq-k">Porque saber exactamente qué estás comprando también forma parte de disfrutarlo.</p>
    </div>
  </section>

  {{-- ---- the welcome: edge to edge, words on his wall ------------------------- --}}
  <section class="pq-welcome" aria-labelledby="pq-keys">
    <div class="pq-open">
      <div class="pq-open__in">
        {{-- the welcome exactly as the client approved it on the live page (a neutral
             copy, pq2/welcome.jpg, read colder; the warm wall is kept on purpose) --}}
        <picture>
          {{-- its width on these screens (pq21.css): min(100vw, 2560px, 2.4 x 100vh) --}}
          <source media="{{ $wide }}" srcset="{{ \App\Support\Img::wideSrcset('/storage/why/welcome-graded.jpg') }}"
                  sizes="(max-height: 1066px) 240vh, (min-width: 2560px) 2560px, 100vw">
          {{-- 2.3 (2): desks under 2200 px drew it 1.44x upscaled at 1440 x 900 @2 (2880 device px
               from the 2000 file), a phone on its side 1.27x; the same file, whole --}}
          <source media="(min-width: 1000px), (min-aspect-ratio: 1/1)" srcset="{{ \App\Support\Img::wideSrcset('/storage/why/welcome-graded.jpg') }}"
                  sizes="(max-aspect-ratio: 4/3) 134vh, 100vw">
          {{-- an upright phone shows only the middle of it (x .32-.67 at 390 x 844), drawn 1.7x
               upscaled from the 2000 file. pq23/welcome-ph.jpg is the same file's columns
               784-3215 (60.3%, the original's own JPEG tables: mean difference 0.1/255), cut so
               that the same object-position (49%) frames exactly the same window on every
               upright screen: (0.49 - 784/4031) / 0.6031 = 0.49 --}}
          <source media="(max-aspect-ratio: 4/5)" srcset="{{ \App\Support\Img::srcset('/storage/why/pq23/welcome-ph.jpg', 2000) }}" sizes="81vh">
          <x-img src="/storage/why/welcome-graded.jpg" alt="Iulian, con los brazos abiertos, delante de cuatro coches preparados para entregar"
                 sizes="(max-aspect-ratio: 4/3) 134vh, 100vw" :max="2000" />
        </picture>
        <h2 class="pq-w" id="pq-keys"><span>Las llaves son tuyas.</span> <span>Mi teléfono sigue disponible.</span></h2>
      </div>
    </div>
    <p class="pq-wrap pq-welcome__sub"><span>En 30 minutos, a tu nombre y con el seguro en vigor.</span> <span>Y después de la compra, me sigues teniendo al teléfono.</span></p>
  </section>

  {{-- ---- his words, IV: after the delivery ----------------------------------- --}}
  <section class="pq-after pq-wrap" aria-labelledby="pq-t4">
    <div class="pq-plate pq-plate--after" data-r>
      <div class="pq-plate__in">
        <h2 class="pq-w" id="pq-t4"><span>La confianza se demuestra</span> <span>después de la entrega.</span></h2>
        <div class="pq-win" aria-hidden="true"></div>
        <div class="pq-f pq-whole"><div class="pq-px"><picture>
          <source media="{{ $frame }}" srcset="{{ \App\Support\Img::wideSrcset('/storage/why/pq23/dk-48w.jpg') }}" sizes="(min-width: 1000px) min(90vw, 1440px), 100vw">
          {{-- 2.3 (2): upright, pq23/tall-48w: pq2/tall-48 with his wall and asphalt carried on before the front wheel, so the car has air --}}
          <x-img src="/storage/why/pq23/tall-48w.jpg" alt="Mercedes-Benz Clase C negro, de tres cuartos trasero"
                 sizes="100vw" :max="1600" />
        </picture></div></div>
      </div>
    </div>
    <div class="pq-after__t">
      <p class="pq-lede pq-after__lede">Cuando te llevas las llaves, nuestro compromiso continúa.</p>
      <div class="pq-read">
        <p>Trabajamos con empresas especializadas para ofrecer una garantía de cobertura nacional que proteja los principales componentes del vehículo, según las condiciones contratadas. Antes de decidir, te explicamos qué incluye y cómo utilizarla.</p>
        <p>Si surge una incidencia, tienes a quién llamar. Te escuchamos, revisamos contigo lo ocurrido y nos implicamos en su gestión, manteniéndote informado de los siguientes pasos.</p>
        <p class="pq-k">Así entendemos el trato personal: conocerte cuando buscas un coche y seguir respondiendo cuando ya es tuyo.</p>
      </div>
    </div>
  </section>

  {{-- ---- the record ---------------------------------------------------------- --}}
  <section class="pq-proof" aria-label="En cifras">
    <ul class="pq-proof__l pq-wrap">
      <li><b data-count="{{ $years }}" data-prefix="+">+{{ $years }}</b><span>años de experiencia</span></li>
      <li><b data-count="{{ $plays }}">{{ $n($plays) }}</b><span>reproducciones de nuestros vídeos</span></li>
      <li><b data-count="{{ $farthest }}">{{ $n($farthest) }}</b><span>kilómetros hizo un cliente para comprar aquí</span></li>
    </ul>
  </section>

  {{-- ---- in their words ------------------------------------------------------ --}}
  {{-- 2.4: the latest three letters (the client), built here so the controller that feeds the
       live page stays as it is: active, with a photograph, newest first. A long letter shows
       its first eight lines, fading, and the home page's "Ver más" opens it (pq24.js); without
       the script every letter is all there --}}
  @php
    $say = \App\Models\Testimonial::where('is_active', true)->whereNotNull('image_path')
      ->orderByDesc('created_at')->orderByDesc('id')->take(3)->get();
  @endphp
  @if($say->count())
  <section class="pq-say pq-wrap" aria-labelledby="pq-say-h">
    <h2 class="pq-h" id="pq-say-h">Lo cuentan ellos</h2>
    @php
      // each photograph's own ratio (clamped as before), and the widest of them: the
      // row takes one photo height, the one at which the widest fills its column
      $ratio = function ($t) { $d = \App\Support\Img::size($t->image_path);
        return $d && $d[1] ? round(max(.6, min(1.6, $d[0] / $d[1])), 4) : .75; };
      $rmax = $say->map($ratio)->max() ?: .75;
    @endphp
    <ul class="pq-say__l" style="--rmax: {{ $rmax }}">
      @foreach($say as $t)
        <li class="pq-q">
          <figure>
            {{-- a customer's photograph is never cropped: the box takes the file's own ratio --}}
            <div class="pq-q__ph" style="--r: {{ $ratio($t) }}">
              <x-img :src="$t->image_path" :alt="$t->author_name.', el día de la entrega'"
                     sizes="(min-width: 1000px) min(30vw, 480px), (min-width: 600px) 30vw, 82vw" :max="1080" :fallback="720" />
            </div>
            <blockquote class="pq-q__t">{{ trim($t->quote) }}</blockquote>
            <button type="button" class="pq-q__more mc-link" hidden data-more="Ver más" data-less="Ver menos">Ver más</button>
            <figcaption class="pq-q__by">{{ $t->author_name }}</figcaption>
          </figure>
        </li>
      @endforeach
    </ul>
    <a class="mc-link pq-say__all" href="/#reviews">Todas las opiniones</a>
  </section>
  @endif

  {{-- ---- the way in ---------------------------------------------------------- --}}
  <section class="pq-end pq-wrap" aria-labelledby="pq-end">
    <div class="pq-plate pq-plate--end" data-r>
      <div class="pq-plate__in">
        <h2 class="pq-w" id="pq-end">Hay coches que llevas tiempo imaginando.</h2>
        <div class="pq-win" aria-hidden="true"></div>
        <div class="pq-f pq-whole"><div class="pq-px"><picture>
          {{-- 2.3 (3): photo 27 is "El color…" now, so the way in is photo 20 (IMG_1273.HEIC, P3 ->
               sRGB, verticals from 2.2's upright, the c27 grade): the same navy Cabrio from the
               front three quarters, whole. His asphalt is his own, nothing re-laid: carried on 200 px
               before the tail and 260 px past the nose with blocks of it moved 1:1 from the same rows.
               Upright pq23/tall-20e (2400 x 3840, his wall carried on above); every other screen
               pq23/dk-20e (2400 x 1680, 10:7: the window cut from it) --}}
          <source media="{{ $frame }}" srcset="{{ \App\Support\Img::wideSrcset('/storage/why/pq23/dk-20e.jpg') }}" sizes="(min-width: 1000px) min(90vw, 1440px), 100vw">
          <x-img src="/storage/why/pq23/tall-20e.jpg" alt="BMW Serie 4 Cabrio azul con la capota abierta, de tres cuartos delantero"
                 sizes="100vw" :max="1600" />
        </picture></div></div>
      </div>
    </div>
    <div class="pq-end__t">
      <p class="pq-end__p">Puede ser tu primer BMW, un cabrio para disfrutar de la costa o esa versión concreta que rara vez aparece. Queremos ayudarte a encontrar una unidad que reúna la ilusión de tenerla y la tranquilidad de haber elegido bien.</p>
      <p class="pq-end__p">Descubre nuestra selección o cuéntanos qué coche tienes en mente.</p>
      <div class="pq-end__act">
        <a class="mc-btn mc-btn--outline" href="/catalogo">Ver coches disponibles</a>
        <a class="mc-btn mc-btn--cta" href="{{ $wa('Hola Iulian, quiero hablar de mi próximo coche.') }}">Hablar con Iulian</a>
      </div>
      <p class="pq-end__sign"><b>IV Motorclass</b> <span>Coches honestos. Personas que responden.</span></p>
    </div>
  </section>

</main>
@endsection

@push('js')
<script src="{{ asset('js/pq25.js') }}?v={{ @filemtime(public_path('js/pq25.js')) }}" defer onerror="document.documentElement.classList.remove('pq-on')"></script>
@endpush
