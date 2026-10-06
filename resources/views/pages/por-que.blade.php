@extends('layouts.site')

@section('title', '¿Por qué IV MOTORCLASS? — Coches alemanes en Málaga')
@section('description', 'Hablas con el director, ves el coche en vídeo antes de venir, solo cinco marcas alemanas, lo pruebas sin presión y te lo llevas a tu nombre en 30 minutos.')
@section('current', 'porque')

@push('css')
<link rel="stylesheet" href="{{ asset('css/why.css') }}">
@endpush

@php
  $wa = fn ($t) => 'https://wa.me/34614753187?text=' . urlencode($t);
  $n  = fn ($v) => number_format((int) $v, 0, ',', '.');
  // Six reasons, each one a promise already made on the site (home steps 01-04,
  // contact, catalogue). Colours: the site's own deep fields, in turn.
  $reasons = [
    ['Hablas con quien decide',
     'Soy el director de IV Motorclass. Te contesto yo, por WhatsApp y normalmente en minutos: sin comerciales, sin centralita, sin pasarte de una persona a otra.',
     'navy'],
    ['Lo ves antes de venir',
     'Te preparo un vídeo del coche y me detengo en lo que quieras comprobar: exterior, interior, equipamiento y también sus desperfectos. Si haces kilómetros para verlo, ya sabes lo que te vas a encontrar.',
     'blue'],
    ['Solo cinco marcas',
     'Volkswagen, Audi, BMW, Mercedes-Benz y Porsche. Trabajar solo con estas cinco me deja conocerlas a fondo y elegir mejor cada coche que entra.',
     'teal'],
    ['Lo pruebas sin presión',
     'Quedamos en Málaga, donde esté el coche, y salimos a probarlo. Tienes tiempo para preguntar y decidir. Si compras a distancia, te lo enseño y resuelvo tus dudas antes.',
     'navy'],
    ['A tu nombre en 30 minutos',
     'Con la documentación preparada, te lo llevas transferido a tu nombre y con el seguro en vigor. Los trámites los hago yo.',
     'blue'],
    ['Sigo al teléfono después',
     'Si después de la compra te surge una duda o un problema, me llamas. Mi atención continúa después de firmar.',
     'teal'],
  ];
@endphp

@section('content')
<main class="pq">

  {{-- ---- the opening ---------------------------------------------------- --}}
  <header class="pq-hero cat-wrap">
    @if($hero = $reviews->first())
      {{-- a real delivery, not a stock photograph: the page is about trust --}}
      <figure class="pq-hero__ph">
        <x-img :src="$hero->image_path" :alt="$hero->author_name.', el día de la entrega'"
               sizes="(min-width:900px) 520px, 1px" :max="1080" :fallback="720" />
        <figcaption>{{ $hero->author_name }}, el día de la entrega</figcaption>
      </figure>
    @endif
    <div class="pq-hero__txt">
    <p class="pq-hero__k">¿Por qué IV MOTORCLASS?</p>
    <h1 class="pq-hero__h">Un coche bien elegido.<br>Y alguien que responde.</h1>
    <p class="pq-hero__p">Cinco marcas alemanas, una sola persona al otro lado y todo a la vista antes de
      que vengas. Esto es lo que cambia cuando compras aquí.</p>
    <div class="pq-hero__act">
      <a class="mc-btn mc-btn--cta" href="{{ $wa('Hola, vengo de la web y busco un coche.') }}">WhatsApp</a>
      <a class="mc-btn mc-btn--ghost" href="/catalogo">Ver los coches</a>
    </div>
    </div>
  </header>

  {{-- ---- the proof: numbers from the database, not from copy ---------------- --}}
  <section class="pq-proof" aria-label="En cifras">
    <ul class="pq-proof__l cat-wrap">
      <li><b data-count="{{ $sold }}">{{ $n($sold) }}</b><span>coches entregados</span></li>
      <li><b data-count="{{ $reviewCount }}">{{ $n($reviewCount) }}</b><span>clientes en la foto de la entrega</span></li>
      <li><b data-count="{{ $plays }}">{{ $n($plays) }}</b><span>reproducciones de nuestros vídeos</span></li>
      <li><b data-count="{{ $farthest }}">{{ $n($farthest) }}</b><span>km recorrió un cliente para comprar aquí</span></li>
    </ul>
  </section>

  {{-- ---- six reasons: bands that fill with their colour as they arrive ----- --}}
  <section class="pq-why" aria-labelledby="pq-why-h">
    <h2 class="pq-why__h cat-wrap" id="pq-why-h">Seis razones, sin letra pequeña</h2>
    <ol class="pq-why__l">
      @foreach($reasons as $i => [$t, $p, $c])
        <li class="pq-r pq-r--{{ $c }} {{ $i % 2 ? 'pq-r--rtl' : '' }}">
          <span class="pq-r__fill" aria-hidden="true"></span>
          <div class="pq-r__in cat-wrap">
            <span class="pq-r__n" aria-hidden="true">{{ sprintf('%02d', $i + 1) }}</span>
            <div class="pq-r__t">
              <h3>{{ $t }}</h3>
              <p>{{ $p }}</p>
            </div>
          </div>
        </li>
      @endforeach
    </ol>
  </section>

  {{-- ---- in their words ----------------------------------------------------- --}}
  @if($reviews->count())
  <section class="pq-say cat-wrap" aria-labelledby="pq-say-h">
    <div class="pq-say__head">
      <h2 class="pq-say__h" id="pq-say-h">Lo cuentan ellos</h2>
      <a class="mc-link" href="/#reviews">Ver todas las opiniones →</a>
    </div>
    <ul class="pq-say__l">
      @foreach($reviews->count() > 3 ? $reviews->slice(1) : $reviews as $t)
        <li class="pq-q">
          <figure>
            <div class="pq-q__ph">
              <x-img :src="$t->image_path" :alt="$t->author_name.', el día de la entrega'"
                     sizes="(min-width:900px) 360px, 80vw" :max="1080" :fallback="720" />
            </div>
            <blockquote class="pq-q__t">{{ trim($t->quote) }}</blockquote>
            <figcaption class="pq-q__by">{{ $t->author_name }}</figcaption>
          </figure>
        </li>
      @endforeach
    </ul>
  </section>
  @endif

  {{-- ---- the way in ---------------------------------------------------------- --}}
  <section class="pq-end">
    <div class="cat-wrap pq-end__in">
      <h2 class="pq-end__h">¿Hablamos de tu próximo coche?</h2>
      <p class="pq-end__p">Cuéntame qué buscas. Si no lo tengo ahora, te lo busco.</p>
      <div class="pq-hero__act">
        <a class="mc-btn mc-btn--cta" href="{{ $wa('Hola, quiero hablar de mi próximo coche.') }}">Escríbeme por WhatsApp</a>
        <a class="mc-btn pq-end__ghost" href="tel:+34614753187">Llamar</a>
      </div>
    </div>
  </section>

</main>
@endsection

@push('js')
<script>
/* Bands fill with their colour as they come into view (once), and the numbers
   count up. Without IntersectionObserver, or with reduced motion, everything
   is simply there. */
(function () {
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  var bands = document.querySelectorAll('.pq-r');
  var nums = document.querySelectorAll('.pq-proof [data-count]');
  if (reduce || !('IntersectionObserver' in window)) {
    Array.prototype.forEach.call(bands, function (b) { b.classList.add('is-in'); });
    return;
  }
  document.documentElement.classList.add('pq-armed');
  var io = new IntersectionObserver(function (es) {
    es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); } });
  }, { threshold: .35 });
  Array.prototype.forEach.call(bands, function (b) { io.observe(b); });

  var fmt = function (v) { return Math.round(v).toLocaleString('es-ES'); };
  Array.prototype.forEach.call(nums, function (n) { n.textContent = '0'; });
  var count = new IntersectionObserver(function (es) {
    es.forEach(function (e) {
      if (!e.isIntersecting) return; count.unobserve(e.target);
      var el = e.target, to = +el.getAttribute('data-count'), t0 = null;
      var tick = function (t) {
        if (!t0) t0 = t; var k = Math.min(1, (t - t0) / 1400);
        el.textContent = fmt(to * (1 - Math.pow(1 - k, 3)));
        if (k < 1) requestAnimationFrame(tick);
      };
      requestAnimationFrame(tick);
    });
  }, { threshold: .6 });
  Array.prototype.forEach.call(nums, function (n) { count.observe(n); });
})();
</script>
@endpush
