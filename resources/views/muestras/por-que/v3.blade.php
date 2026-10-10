@extends('layouts.site')

@section('title', '¿Por qué IV MOTORCLASS? — Coches alemanes en Málaga')
@section('robots', 'noindex, nofollow')
@section('description', 'Seleccionados, revisados y preparados. Te contesto yo, te lo enseño antes de que vengas y te lo llevas a tu nombre en 30 minutos.')
@section('og_image', url(\App\Support\Img::url('/storage/why/welcome-graded.jpg', 1200) ?? '/storage/why/welcome-graded.jpg'))
@section('current', 'porque')

{{-- =============================================================================
     PROPOSAL 3 · INMERSIVA (2026-10-09). /por-que-nosotros told by travelling
     THROUGH his photographs. Every text, the portrait, the welcome scene, the
     record, the reviews and the end are the live page's, unchanged; the two
     films are gone and two reels of photographs take their place.

     A reel is one sticky screen. The scroll is a camera: it pushes from the
     whole car into a detail, and at the detail the next photograph of the same
     car is already there at exactly the same scale, position and roll, so the
     camera seems to keep going (a "zoom-through"). The pairs were matched on the
     originals, on what each screen shape sees at the handover (tools/media/
     pq3-media.py; table in public/js/pq3.js). The two are locked together and
     the next one dissolves in whole, in time (a pause never freezes a double
     image); where the two camera positions disagree on screen, the same handover
     dips through black. Between cars, a cut through the story's black, also in
     time. A reel's first photograph rises from black, its last one goes down to
     black under its last words, and the reading after it rises on that black.
     Words: one type scale (single words W, every statement a major third below),
     two places (top or bottom, the same margins), on the calmest ground of their
     photograph: ink on his white wall, white on the darkest paint.

     The third pass (2026-10-10) also dresses his text, the record, their words and
     the end in the reels' night and scale (pq3.css), and lays the header over
     the first photograph. The welcome is untouched.

     reel A  14 grey C-Class whole  →  15 its front           (headlight, star)
             40 black C-Class 3/4   →  41 its front quarter   (headlight, wheel)
             43 black C-Class front →  44 closer               (the star)
             19 4 Cabrio, roof up   →  21 the same, roof down  (the grille)
     reel B  22 the Cabrio's profile · 27 its rear three-quarter · 26 its cabin
             · 29 at the wheel · 30 its door, close · 24 the white leather from above

     Every photograph is cut from the original for the screen that shows it: a
     phone gets the upright slice its camera path sees, wide screens the whole
     photograph, and both a small dense cut for the deepest zoom.
     Phone first: every frame has its own focus and zoom for an upright screen
     and for a wide one (pq3.js), never a centre crop. Without JavaScript, or
     with reduced motion: a calm photo essay, one photograph per line of text.
     ========================================================================== --}}

@php
  $wa  = fn ($t) => 'https://wa.me/34614753187?text=' . urlencode($t);
  $n   = fn ($v) => number_format((int) $v, 0, ',', '.');
  $ph  = fn ($id) => '/storage/why/photos/' . [
      '14' => '14_DJI_20260329_154326_944.jpg', '15' => '15_DJI_20260329_154341_391.jpg',
      '40' => '40_IMG_1862.jpg', '41' => '41_IMG_1863.jpg',
      '43' => '43_IMG_1869.jpg', '44' => '44_IMG_1871.jpg',
      '19' => '19_IMG_1252.jpg', '21' => '21_IMG_1274.jpg',
      '22' => '22_IMG_1276.jpg', '27' => '27_IMG_1313.jpg', '26' => '26_IMG_1310.jpg',
      '29' => '29_IMG_1325.jpg', '30' => '30_IMG_1350.jpg', '24' => '24_IMG_1280.jpg',
  ][$id];
  // the cuts the reels show (tools/media/pq3-media.py; pq3.js knows their rects):
  // <id>-p0 a phone's upright slice of the photograph, <id>-l0 the whole of it for
  // every other screen, <id>-p1 / -l1 the small, dense cut for the deepest zoom.
  // A cut that is not there is simply not offered (the photo above is used).
  $cut = function ($id, $k) {
      $f = storage_path('app/public/why/pq3/' . $id . '-' . $k . '.webp');
      return is_file($f) ? '/storage/why/pq3/' . $id . '-' . $k . '.webp?v=' . filemtime($f) : null;
  };
  // the essay (no JavaScript, reduced motion) shows each key photograph whole, at most 1280 wide
  $essay = '(min-aspect-ratio: 1/1) min(100vw, 1280px), 100vw';
  // the screens that are sent the phone's slices (pq3.js makes the same choice)
  $phone = '(max-width: 600px) and (orientation: portrait)';
  // [id, alt (key frames only: the one shown in the plain essay)]
  $reels = [
    'a' => ['label' => 'Seleccionados, revisados y preparados', 'beats' => [
      ['a1', [['14', 'Mercedes-Benz Clase C gris delante de la pared blanca']]],
      ['a2', [['15', 'El frontal del mismo Mercedes-Benz, de cerca']]],
      ['a3', [['40', ''], ['41', 'El faro y la llanta de un Mercedes-Benz Clase C negro, de cerca']]],
      ['a4', [['43', ''], ['44', 'La estrella del Mercedes-Benz Clase C negro, de frente']]],
      ['a5', [['19', ''], ['21', 'BMW Serie 4 Cabrio azul con la capota abierta y el interior blanco']]],
    ]],
    'b' => ['label' => 'Te lo enseño antes de que vengas', 'beats' => [
      ['b1', [['22', 'BMW Serie 4 Cabrio azul de perfil, delante de la pared blanca']]],
      ['b2', [['27', 'El mismo Cabrio desde atrás']]],
      ['b3', [['26', ''], ['29', 'El interior del Cabrio: volante y asientos de cuero blanco']]],
      ['b4', [['30', 'La puerta del Cabrio, de cerca: cuero blanco y altavoz harman/kardon']]],
      ['b5', [['24', 'Los asientos de cuero blanco del Cabrio, vistos desde arriba']]],
    ]],
  ];
  $words = [
    'a1' => '<h1 class="pq-w pq-w--h1 pq-w--ink" data-b="a1"><span class="pq-l">Un coche bien elegido.</span> <span class="pq-l">Y alguien que responde.</span></h1>',
    'a2' => '<p class="pq-w pq-w--word pq-w--ink" data-b="a2"><span class="pq-l">Seleccionados.</span></p>',
    'a3' => '<p class="pq-w pq-w--word" data-b="a3"><span class="pq-l">Revisados.</span></p>',
    'a4' => '<p class="pq-w pq-w--word" data-b="a4"><span class="pq-l">Preparados.</span></p>',
    'a5' => '<p class="pq-w pq-w--line" data-b="a5"><span class="pq-l">Para que disfrutes</span> <span class="pq-l">de algo especial.</span></p>',
    'b1' => '<h2 class="pq-w pq-w--line pq-w--ink" data-b="b1"><span class="pq-l">Te lo enseño</span> <span class="pq-l">antes de que vengas.</span></h2>',
    'b2' => '<p class="pq-w pq-w--word pq-w--ink" data-b="b2"><span class="pq-l">El exterior.</span></p>',
    'b3' => '<p class="pq-w pq-w--word" data-b="b3"><span class="pq-l">El interior.</span></p>',
    'b4' => '<p class="pq-w pq-w--word pq-w--wrap pq-w--ink" data-b="b4"><span class="pq-l">Y sus desperfectos.</span></p>',
    'b5' => '<p class="pq-w pq-w--line" data-b="b5"><span class="pq-l">Para que sepas</span> <span class="pq-l">qué te vas a encontrar.</span></p>',
  ];
@endphp

@push('css')
<link rel="stylesheet" href="{{ asset('css/why2.css') }}">
<link rel="stylesheet" href="{{ asset('css/why-odo.css') }}">
<link rel="stylesheet" href="{{ asset('css/why-text.css') }}">
<link rel="stylesheet" href="{{ asset('css/why-dark.css') }}">
<link rel="stylesheet" href="{{ asset('css/pq3.css') }}?v={{ @filemtime(public_path('css/pq3.css')) }}">
@endpush

@push('head')
{{-- decided before the first paint, by the same test pq3.js makes, so the page
     never paints the essay and then jumps to the reels --}}
<script>(function(d){var m=window.matchMedia;if(!(m&&m('(prefers-reduced-motion: reduce)').matches)&&'IntersectionObserver' in window&&window.requestAnimationFrame)d.className+=' pq-on wy-on';})(document.documentElement)</script>
{{-- the opening photograph, asked for before the script that shows it runs --}}
@if($cut('14', 'p0'))
<link rel="preload" as="image" href="{{ $cut('14', 'p0') }}" media="{{ $phone }} and (prefers-reduced-motion: no-preference)" fetchpriority="high">
<link rel="preload" as="image" href="{{ $cut('14', 'l0') }}" media="(min-width: 601px) and (prefers-reduced-motion: no-preference), (orientation: landscape) and (prefers-reduced-motion: no-preference)" fetchpriority="high">
@endif
@endpush

@section('content')
<main class="wy pq">

  @foreach($reels as $rid => $reel)
    @if($rid === 'b')
  {{-- ---- his words, I ------------------------------------------------------- --}}
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

  {{-- ---- the sunset portrait: as live ------------------------------------------ --}}
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
    @endif
    <section class="pq-reel pq-reel--{{ $rid }}" data-reel="{{ $rid }}" aria-label="{{ $reel['label'] }}">
      <div class="pq-stage">
        @foreach($reel['beats'] as [$bid, $shots])
          <div class="pq-beat" data-beat="{{ $bid }}">
            {!! $words[$bid] !!}
            @foreach($shots as [$id, $alt])
              <div class="pq-shot{{ $alt ? ' pq-shot--key' : '' }}" data-shot="{{ $id }}" @unless($alt) aria-hidden="true" @endunless
                   @foreach(['p0', 'p1', 'l0', 'l1'] as $k) @if($cut($id, $k)) data-{{ $k }}="{{ $cut($id, $k) }}" @endif @endforeach>
                {{-- no src: the reels give it its cut (pq3.js), the essay its photograph
                     (pq3.js, from data-*; without JavaScript, the copy in <noscript>) --}}
                <img class="pq-i" alt="{{ $alt }}" width="2400" height="1800" decoding="async"
                     data-src="{{ \App\Support\Img::url($ph($id), 1600) }}" data-srcset="{{ \App\Support\Img::srcset($ph($id), 2000) }}" data-sizes="{{ $essay }}">
                @if($alt)<noscript><x-img :src="$ph($id)" :alt="$alt" :sizes="$essay" :max="2000" :fallback="1600" /></noscript>@endif
              </div>
            @endforeach
          </div>
        @endforeach
      </div>
    </section>
  @endforeach

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

  {{-- ---- welcome: exactly as live ----------------------------------------------- --}}
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
{{-- if it cannot load, the essay (the classes set in <head> come off, the photographs go in) --}}
<script src="{{ asset('js/pq3.js') }}?v={{ @filemtime(public_path('js/pq3.js')) }}" defer onerror="document.documentElement.classList.remove('pq-on','wy-on');document.querySelectorAll('.pq-shot--key img[data-src]').forEach(function(i){i.sizes=i.dataset.sizes;i.srcset=i.dataset.srcset;i.src=i.dataset.src})"></script>
<script src="{{ asset('js/why-odo.js') }}" defer></script>
<script src="{{ asset('js/why-text.js') }}" defer></script>
@endpush
