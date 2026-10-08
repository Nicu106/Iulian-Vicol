@extends('layouts.site')

@section('title', '¿Por qué IV MOTORCLASS? — Coches alemanes en Málaga')
@section('description', 'Seleccionados, revisados y preparados. Te contesto yo, te lo enseño antes de que vengas y te lo llevas a tu nombre en 30 minutos.')
@section('og_image', url(\App\Support\Img::url('/storage/why/keys.jpg', 1200) ?? '/storage/why/keys.jpg'))
@section('current', 'porque')

@php
  // the films and their posters carry their file's time: they are cached for 30
  // days, and a re-encode (new chapter times in data-ends) must never meet last
  // month's film in someone's cache
  $film = fn ($f) => '/storage/why/film/' . $f . '?v=' . @filemtime(storage_path('app/public/why/film/' . $f));
@endphp

@push('css')
<link rel="stylesheet" href="{{ asset('css/why2.css') }}">
<link rel="stylesheet" href="{{ asset('css/why-odo.css') }}">
<link rel="stylesheet" href="{{ asset('css/why-text.css') }}">
@endpush

@push('head')
{{-- the film layout is decided before the first paint, by the same test why.js
     makes: deferred, why.js could arrive after the page had already painted the
     plain version (stylesheets cached from another page, why.js not), and the
     screen jumped from a poster over a navy block of words to the film --}}
<script>(function(d){var m=window.matchMedia;if(!(m&&m('(prefers-reduced-motion: reduce)').matches)&&'IntersectionObserver' in window&&window.requestAnimationFrame)d.className+=' wy-on';})(document.documentElement)</script>
{{-- the opening film's first frame, so the screen is never empty while it loads --}}
<link rel="preload" as="image" href="{{ $film('a-p.webp') }}" media="(max-aspect-ratio: 1/1)" fetchpriority="high">
<link rel="preload" as="image" href="{{ $film('a-d.webp') }}" media="(min-aspect-ratio: 1/1)" fetchpriority="high">
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
     FILM A IS CUT AS A FILM (2026-10-08, tools/media/why-film-a.py). Shot list:
       open   the navy 4 Cabrio three-quarter, the row receding behind it —
              held under the title, in a 2.39 scope frame with the title in
              the black beneath; the frame opens as the camera starts to move
       1      along the Cabrio's nose: from rest, to rest on its kidney grille
              and headlight (the title's frame)
       2      past it to the C-Class: the star, the headlight, the spoked wheel
       3      the Octavia RS's black grille, the red calipers
       4      round to the 2 Series dead-on: symmetric, the IV plate centred
       5      back out to the whole row against the hills
     Each chapter is one move between two composed frames: stabilised (the
     walk's nod gone), a speed ramp (smoothstep: out of rest, ~1.5x its mean in
     the middle with a 1-2-1 shutter blur, settling onto the held frame), its own
     framing per held frame for phone and desk. Night grade: the white wall pulled
     down to slate where it is bright and high, a graduated ND from the top,
     deep blacks, lit paint. Film B keeps its daylight (it shows what you will
     find) in the same family: greens quieted, the same ND, lighter.
     CLIENT PASS (2026-10-08, evening). Film B re-encoded at 30 fps, capped at
     1.6 / 3 Mb/s (12 MB → 5.7 MB desk; it stalled on his connection) and
     fetched as soon as film A can play through; words arrive sharp (no blur).
     The 4th scene is now his photo with three BMWs (keys.jpg), set like the
     portrait mirrored — words in ink on white, never on the picture (on the
     old welcome photo they fell on his black jumper). The record: +9 años de
     experiencia, 600.000 reproducciones, 1.200 km — his figures.
     HIS WORDS (2026-10-08). Between the scenes, Iulian's own text, whole and
     unedited, set as reading (.wy-txt, why-text.css): the heading on the left
     and the paragraphs on the right from 900 px, stacked on phones; each
     closing line in ink, the rest in the body grey. The portrait carries his
     heading ("Una pasión personal…") and "Hablar con Iulian"; his closing
     ("Hay coches…", the signature, the two buttons) is the navy end.
     Without JavaScript, or with reduced motion: a normal page — the first
     frames as still pictures, the words beneath them (html:not(.wy-on)).
     ========================================================================== --}}

@section('content')
<main class="wy">

  {{-- ---- 1 · film A ------------------------------------------------------- --}}
  <section class="wy-film wy-film--a" data-scene="film" aria-label="Seleccionados, revisados y preparados">
    <div class="wy-stage">
      <picture class="wy-poster">
        <source media="(min-aspect-ratio: 1/1)" srcset="{{ $film('a-d.webp') }}" width="1600" height="900">
        <img src="{{ $film('a-p.webp') }}" alt="" width="608" height="1080" fetchpriority="high">
      </picture>
      <video class="wy-video" muted playsinline disableremoteplayback preload="none" aria-hidden="true"
             data-p="{{ $film('a-p.mp4') }}" data-d="{{ $film('a-d.mp4') }}" data-ends="2.2,4.8,7.6,10.6,13.8"></video>
      <canvas class="wy-canvas" aria-hidden="true"></canvas>
      <div class="wy-shade" aria-hidden="true"></div>
      <div class="wy-bar wy-bar--t" aria-hidden="true"></div>
      <div class="wy-bar wy-bar--b" aria-hidden="true"></div>
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

  {{-- ---- his words, I --------------------------------------------------------- --}}
  <section class="wy-txt" aria-labelledby="wy-t1">
    <div class="cat-wrap wy-txt__in">
      <h2 class="wy-txt__h" id="wy-t1"><span>Que te enamore el coche.</span> <span>Que te dé confianza su historia.</span></h2>
      <div class="wy-txt__b">
        <p>Hay algo especial en encontrar el coche que encaja contigo. La motorización que querías. El color que te hace volver a mirarlo. Ese equipamiento al que no quieres renunciar.</p>
        <p>Y, junto a esa ilusión, hay preguntas que merecen una respuesta clara: ¿cómo lo han cuidado?, ¿qué sabemos de sus kilómetros?, ¿ha tenido algún accidente?, ¿quién me atenderá después?</p>
        <p class="wy-txt__k">En IV Motorclass, nuestra forma de trabajar empieza precisamente ahí.</p>
      </div>
    </div>
  </section>

  {{-- ---- 2 · portrait ------------------------------------------------------ --}}
  <section class="wy-still wy-still--portrait" data-scene="still" aria-labelledby="wy-me">
    <div class="wy-stage">
      <div class="wy-photo">
        <x-img src="/storage/why/portrait.jpg" alt="Iulian, fundador de IV Motorclass, con dos Mercedes-Benz descapotables"
               sizes="(min-aspect-ratio: 1/1) 134vh, 86vh" :max="2000" :fallback="1080" />
      </div>
      <div class="wy-copy wy-copy--ink">
        <h2 class="wy-beat wy-beat--line" id="wy-me" data-in=".04" data-out="2">
          <span class="wy-l">Una pasión personal.</span>
          <span class="wy-l">Un compromiso contigo.</span>
        </h2>
        <p class="wy-beat wy-beat--act" data-in=".34" data-out="2">
          <a class="mc-btn mc-btn--cta" href="{{ $wa('Hola Iulian, me interesa un coche. ¿Hablamos?') }}">Hablar con Iulian</a>
        </p>
      </div>
    </div>
  </section>

  {{-- ---- his words, II -------------------------------------------------------- --}}
  <section class="wy-txt wy-txt--me" aria-label="Iulian, fundador de IV Motorclass">
    <div class="cat-wrap wy-txt__in">
      <div class="wy-txt__b">
        <p class="wy-txt__lead">Soy Iulian, fundador de IV Motorclass. Desde pequeño me han apasionado los coches alemanes. Podía pasar horas fijándome en sus formas, sus interiores y los detalles que hacían especial una versión.</p>
        <p>Esa misma curiosidad me lleva hoy a buscar unidades con personalidad: coches que apetece conducir, conservar y disfrutar. Pero convertir una pasión en un negocio implica algo más: asumir la responsabilidad de lo que eliges y de lo que vendes.</p>
        <p class="wy-txt__lead">Por eso me implico personalmente en la selección. Detrás de cada coche que ofrecemos está mi nombre y una relación de confianza que quiero mantener mucho después de la entrega.</p>
      </div>
    </div>
  </section>

  {{-- ---- 3 · film B ------------------------------------------------------- --}}
  <section class="wy-film wy-film--b" data-scene="film" aria-label="Te lo enseño antes de que vengas">
    <div class="wy-stage">
      <picture class="wy-poster">
        <source media="(min-aspect-ratio: 1/1)" srcset="{{ $film('b-d.webp') }}" width="1600" height="900">
        <img src="{{ $film('b-p.webp') }}" alt="" width="608" height="1080" loading="lazy">
      </picture>
      <video class="wy-video" muted playsinline disableremoteplayback preload="none" aria-hidden="true"
             data-p="{{ $film('b-p.mp4') }}" data-d="{{ $film('b-d.mp4') }}" data-ends="2.5,5.75,8.5,11.25,15"></video>
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

  {{-- ---- his words, III ------------------------------------------------------- --}}
  <section class="wy-txt" aria-labelledby="wy-t3">
    <div class="cat-wrap wy-txt__in">
      <h2 class="wy-txt__h" id="wy-t3">Lo que tú no ves a primera vista también importa.</h2>
      <div class="wy-txt__b">
        <p>Una buena configuración llama la atención. Un historial claro, un mantenimiento documentado y un estado cuidado son lo que nos da motivos para elegirla.</p>
        <p>Buscamos coches honestos: kilometraje respaldado por documentación, sin antecedentes de accidentes y con señales de haber recibido el cuidado que merecen. Priorizamos las unidades que conservan su pintura original y revisamos su estado más allá de las fotografías.</p>
        <p>Antes de entregarte el coche, lo revisamos, atendemos las necesidades detectadas y te explicamos su historial y condición. Si hay un detalle relevante para tu decisión, queremos que lo conozcas antes de tomarla.</p>
        <p class="wy-txt__k">Porque saber exactamente qué estás comprando también forma parte de disfrutarlo.</p>
      </div>
    </div>
  </section>

  {{-- ---- 4 · welcome ------------------------------------------------------- --}}
  <section class="wy-still wy-still--keys" data-scene="still" aria-labelledby="wy-keys">
    <div class="wy-stage">
      <div class="wy-photo">
        <x-img src="/storage/why/keys.jpg" alt="Iulian junto a tres BMW preparados para entregar, en Málaga"
               sizes="(min-aspect-ratio: 1/1) 56vw, 110vh" :max="1500" :fallback="1080" />
      </div>
      <div class="wy-copy wy-copy--ink">
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

  {{-- ---- his words, IV -------------------------------------------------------- --}}
  <section class="wy-txt" aria-labelledby="wy-t4">
    <div class="cat-wrap wy-txt__in">
      <h2 class="wy-txt__h" id="wy-t4">La confianza se demuestra después de la entrega.</h2>
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

  {{-- ---- the way in -------------------------------------------------------- --}}
  <section class="wy-end">
    <div class="cat-wrap wy-end__in">
      <h2 class="wy-end__h">Hay coches que llevas tiempo imaginando.</h2>
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
{{-- if it cannot load, the plain page (the class set in <head> comes off) --}}
<script src="{{ asset('js/why.js') }}" defer onerror="document.documentElement.classList.remove('wy-on')"></script>
<script src="{{ asset('js/why-odo.js') }}" defer></script>
<script src="{{ asset('js/why-text.js') }}" defer></script>
@endpush
