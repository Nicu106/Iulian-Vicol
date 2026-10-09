@extends('layouts.site')

@section('title', '¿Por qué IV MOTORCLASS? — Coches alemanes en Málaga')
@section('description', 'Seleccionados, revisados y preparados. Te contesto yo, te lo enseño antes de que vengas y te lo llevas a tu nombre en 30 minutos.')
@section('og_image', url(\App\Support\Img::url('/storage/why/welcome-graded.jpg', 1200) ?? '/storage/why/welcome-graded.jpg'))
@section('robots', 'noindex, nofollow')
@section('current', 'porque')

@push('css')
<link rel="stylesheet" href="{{ asset('css/pq2.css') }}?v={{ @filemtime(public_path('css/pq2.css')) }}">
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

     Scenes: cover (22) · Seleccionados (40) / Revisados (35) / Preparados (51)
     · Para que disfrutes (24) · his first text with three details (36, 19, 30)
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

  {{-- ---- the cover ---------------------------------------------------------- --}}
  <section class="pq-cover pq-wrap" aria-labelledby="pq-h1">
    <div class="pq-plate pq-plate--cover">
      <div class="pq-plate__in">
        <h1 class="pq-w" id="pq-h1"><span>Un coche bien elegido.</span> <span>Y alguien que responde.</span></h1>
        <div class="pq-win" aria-hidden="true"></div>
        <div class="pq-f"><div class="pq-px">
          <x-img :src="$tall(22)" alt="BMW Serie 4 Cabrio azul con la capota abierta, de perfil, ante una pared blanca"
                 sizes="(min-width: 1000px) min(92vw, 1600px), 100vw" :max="2000" :fallback="1080" :priority="true" />
        </div></div>
      </div>
    </div>
  </section>

  {{-- ---- seleccionados, revisados, preparados ---------------------------------- --}}
  <section class="pq-three pq-wrap" aria-label="Seleccionados, revisados y preparados">
    <div class="pq-plate pq-plate--sel" data-r>
      <div class="pq-plate__in">
        <p class="pq-w">Seleccionados.</p>
        <div class="pq-win" aria-hidden="true"></div>
        <div class="pq-f"><div class="pq-px">
          <x-img :src="$tall(40)" alt="Mercedes-Benz Clase C negro con llantas AMG, de tres cuartos"
                 sizes="(min-width: 1000px) 60vw, 100vw" :max="2000" />
        </div></div>
      </div>
    </div>
    {{-- the word above its picture, like the two on the wall: each of the three
         words is the top-left corner of its own photograph --}}
    <figure class="pq-pic pq-pic--rev">
      <figcaption class="pq-word">Revisados.</figcaption>
      <div class="pq-f" data-r><div class="pq-f__in"><div class="pq-px">
        <x-img :src="$ph(35)" alt="Rueda del Škoda Octavia RS con la pinza de freno roja"
               sizes="(min-width: 1000px) 28vw, 82vw" :max="1080" />
      </div></div></div>
    </figure>
    <div class="pq-plate pq-plate--pre" data-r>
      <div class="pq-plate__in">
        <p class="pq-w">Preparados.</p>
        <div class="pq-win" aria-hidden="true"></div>
        <div class="pq-f"><div class="pq-px">
          <x-img :src="$tall(51)" alt="Mercedes-Benz Clase C plata AMG, de tres cuartos"
                 sizes="(min-width: 1000px) 60vw, 100vw" :max="2000" />
        </div></div>
      </div>
    </div>
    <figure class="pq-pic pq-pic--joy">
      <div class="pq-f" data-r><div class="pq-f__in"><div class="pq-px">
        <x-img :src="$ph(24)" alt="El interior de cuero blanco del BMW Serie 4 Cabrio, visto desde arriba"
               sizes="(min-width: 1000px) 56vw, (min-width: 600px) 92vw, 167vw" :max="2000" />
      </div></div></div>
      <figcaption class="pq-line"><span>Para que disfrutes</span> <span>de algo especial.</span></figcaption>
    </figure>
  </section>

  {{-- ---- his words, I: three details, his own sentences as their captions ------- --}}
  <section class="pq-love pq-wrap" aria-labelledby="pq-t1">
    <h2 class="pq-h" id="pq-t1"><span>Que te enamore el coche.</span> <span>Que te dé confianza su historia.</span></h2>
    <p class="pq-lede">Hay algo especial en encontrar el coche que encaja contigo.</p>
    <div class="pq-caps">
      <figure class="pq-cap pq-cap--a">
        <div class="pq-f" data-r><div class="pq-f__in"><div class="pq-px">
          <x-img :src="$ph(36)" alt="La parrilla negra y el faro del Škoda Octavia RS"
                 sizes="(min-width: 600px) 56vw, 150vw" :max="1080" />
        </div></div></div>
        <figcaption>La motorización que querías.</figcaption>
      </figure>
      <figure class="pq-cap pq-cap--b">
        <div class="pq-f" data-r><div class="pq-f__in"><div class="pq-px">
          <x-img :src="$ph(19)" alt="El capó azul del BMW Serie 4 Cabrio, de frente"
                 sizes="(min-width: 600px) 64vw, 104vw" :max="1080" />
        </div></div></div>
        <figcaption>El color que te hace volver a mirarlo.</figcaption>
      </figure>
      <figure class="pq-cap pq-cap--c">
        <div class="pq-f" data-r><div class="pq-f__in"><div class="pq-px">
          <x-img :src="$ph(30)" alt="Puerta del BMW Serie 4 Cabrio: cuero blanco y altavoz harman/kardon"
                 sizes="(min-width: 600px) 56vw, 167vw" :max="1080" />
        </div></div></div>
        <figcaption>Ese equipamiento al que no quieres renunciar.</figcaption>
      </figure>
    </div>
    <div class="pq-read">
      <p>Y, junto a esa ilusión, hay preguntas que merecen una respuesta clara: ¿cómo lo han cuidado?, ¿qué sabemos de sus kilómetros?, ¿ha tenido algún accidente?, ¿quién me atenderá después?</p>
      <p class="pq-k">En IV Motorclass, nuestra forma de trabajar empieza precisamente ahí.</p>
    </div>
  </section>

  {{-- ---- the portrait, and his own story ------------------------------------- --}}
  <section class="pq-me pq-wrap" aria-labelledby="pq-me">
    <h2 class="pq-h pq-me__h" id="pq-me"><span>Una pasión personal.</span> <span>Un compromiso contigo.</span></h2>
    <figure class="pq-pic pq-me__ph">
      <div class="pq-f" data-r><div class="pq-f__in"><div class="pq-px">
        <x-img src="/storage/why/portrait.jpg" alt="Iulian, fundador de IV Motorclass, con dos Mercedes-Benz descapotables"
               sizes="(min-width: 1000px) 55vw, (min-width: 600px) 92vw, 167vw" :max="2000" />
      </div></div></div>
    </figure>
    <div class="pq-read pq-me__t">
      <p class="pq-lead">Soy Iulian, fundador de IV Motorclass. Desde pequeño me han apasionado los coches alemanes. Podía pasar horas fijándome en sus formas, sus interiores y los detalles que hacían especial una versión.</p>
      <p>Esa misma curiosidad me lleva hoy a buscar unidades con personalidad: coches que apetece conducir, conservar y disfrutar. Pero convertir una pasión en un negocio implica algo más: asumir la responsabilidad de lo que eliges y de lo que vendes.</p>
      <p class="pq-lead">Por eso me implico personalmente en la selección. Detrás de cada coche que ofrecemos está mi nombre y una relación de confianza que quiero mantener mucho después de la entrega.</p>
      <p class="pq-act"><a class="mc-btn mc-btn--cta" href="{{ $wa('Hola Iulian, me interesa un coche. ¿Hablamos?') }}">Hablar con Iulian</a></p>
    </div>
  </section>

  {{-- ---- te lo enseño: one car, walked round ---------------------------------- --}}
  <section class="pq-show" aria-labelledby="pq-show">
    <div class="pq-wrap">
      <div class="pq-plate pq-plate--show" data-r>
        <div class="pq-plate__in">
          <h2 class="pq-w" id="pq-show"><span>Te lo enseño</span> <span>antes de que vengas.</span></h2>
          <div class="pq-win" aria-hidden="true"></div>
          <div class="pq-f"><div class="pq-px">
            <x-img :src="$tall(14)" alt="Mercedes-Benz Clase C gris, de tres cuartos"
                   sizes="(min-width: 1000px) min(92vw, 1600px), 100vw" :max="2000" />
          </div></div>
        </div>
      </div>
    </div>
    <ul class="pq-gal" tabindex="0" aria-label="El exterior, el interior y sus desperfectos">
      <li class="pq-slide pq-slide--ext">
        <figure>
          <div class="pq-f" data-r><div class="pq-f__in"><div class="pq-px">
            <x-img :src="$ph(17)" alt="El mismo Clase C gris por detrás, de tres cuartos"
                   sizes="(min-width: 1000px) 30vw, (min-width: 600px) 77vw, 140vw" :max="1080" />
          </div></div></div>
          <figcaption class="pq-word">El exterior.</figcaption>
        </figure>
      </li>
      <li class="pq-slide pq-slide--int">
        <figure>
          <div class="pq-f" data-r><div class="pq-f__in"><div class="pq-px">
            <x-img :src="$ph(18)" alt="El interior del mismo Clase C: volante, pantalla y consola"
                   sizes="(min-width: 1000px) 30vw, (min-width: 600px) 77vw, 140vw" :max="1080" />
          </div></div></div>
          <figcaption class="pq-word">El interior.</figcaption>
        </figure>
      </li>
      <li class="pq-slide pq-slide--det">
        <figure>
          <div class="pq-f" data-r><div class="pq-f__in"><div class="pq-px">
            <x-img :src="$ph(16)" alt="El lateral del mismo Clase C de cerca: puertas, pintura y llanta"
                   sizes="(min-width: 1000px) 30vw, (min-width: 600px) 77vw, 140vw" :max="1080" />
          </div></div></div>
          <figcaption class="pq-word">Y sus desperfectos.</figcaption>
        </figure>
      </li>
    </ul>
    <p class="pq-wrap pq-line pq-show__end"><span>Para que sepas</span> <span>qué te vas a encontrar.</span></p>
  </section>

  {{-- ---- his words, III: what you don't see -------------------------------- --}}
  <section class="pq-paint pq-wrap" aria-labelledby="pq-t3">
    <h2 class="pq-h pq-paint__h" id="pq-t3">Lo que tú no ves a primera vista también importa.</h2>
    <figure class="pq-pic pq-paint__ph">
      <div class="pq-f" data-r><div class="pq-f__in"><div class="pq-px">
        <x-img :src="$ph(46)" alt="Las puertas del Mercedes-Benz Clase C negro reflejan la pared como un espejo"
               sizes="(min-width: 1000px) 78vw, (min-width: 600px) 92vw, 167vw" :max="1600" />
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
        {{-- welcome-graded.jpg, white-balanced with the set (pq2/welcome.jpg: its wall
             was the one warm-yellow wall on the page; 85% neutral, brightness kept) --}}
        <x-img src="/storage/why/pq2/welcome.jpg" alt="Iulian, con los brazos abiertos, delante de cuatro coches preparados para entregar"
               sizes="(max-aspect-ratio: 4/3) 134vh, 100vw" :max="2000" />
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
        <div class="pq-f"><div class="pq-px">
          <x-img :src="$tall(48)" alt="Mercedes-Benz Clase C negro, de tres cuartos trasero"
                 sizes="(min-width: 1000px) min(92vw, 1600px), 100vw" :max="2000" />
        </div></div>
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
  @if($reviews->count())
  <section class="pq-say pq-wrap" aria-labelledby="pq-say-h">
    <h2 class="pq-h" id="pq-say-h">Lo cuentan ellos</h2>
    <ul class="pq-say__l">
      @foreach($reviews->take(3) as $t)
        @php $d = \App\Support\Img::size($t->image_path); @endphp
        <li class="pq-q">
          <figure>
            {{-- a customer's photograph is never cropped: the box takes the file's own ratio --}}
            <div class="pq-q__ph" style="--r: {{ $d && $d[1] ? round(max(.6, min(1.6, $d[0] / $d[1])), 4) : .75 }}">
              <x-img :src="$t->image_path" :alt="$t->author_name.', el día de la entrega'"
                     sizes="(min-width: 1000px) 380px, (min-width: 600px) 30vw, 82vw" :max="1080" :fallback="720" />
            </div>
            <blockquote class="pq-q__t">{{ trim($t->quote) }}</blockquote>
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
        <div class="pq-f"><div class="pq-px">
          <x-img :src="$tall(27)" alt="BMW Serie 4 Cabrio azul con la capota abierta, de tres cuartos trasero"
                 sizes="(min-width: 1000px) min(92vw, 1600px), 100vw" :max="2000" />
        </div></div>
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
<script src="{{ asset('js/pq2.js') }}?v={{ @filemtime(public_path('js/pq2.js')) }}" defer onerror="document.documentElement.classList.remove('pq-on')"></script>
@endpush
