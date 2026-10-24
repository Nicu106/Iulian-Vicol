@extends('layouts.site')

@section('title', 'IV MOTORCLASS — Coches alemanes premium en Málaga')
@section('current', 'inicio')
@section('body', 'home')

@push('css')
<link rel="stylesheet" href="{{ asset('css/home.css') }}">
@endpush

@section('content')
<main>

  {{-- ============ the hero ============ --}}
  <section class="hm-hero">
    {{-- Was the 456 KB JPEG straight out of public/. Full-bleed, so 100vw is the
         truth; the ladder tops out at 2000 because nothing here is retina-4K. --}}
    <x-img class="hm-hero__img" src="/img/banner/home.jpg" alt=""
           sizes="100vw" :max="2000" :fallback="1080" :priority="true" />
    <div class="hm-hero__in cat-wrap">
      <div class="hm-hero__copy">
        <span class="hm-hero__eyebrow">Málaga</span>
        <h1 class="hm-hero__h">Tu coche perfecto<br>te espera</h1>
        <p class="hm-hero__p">Meticulosamente escogido y llevado a los estándares más altos para ti.</p>
      </div>

      {{-- The search. Every figure on it is counted from the stock. --}}
      <form class="hm-search" id="search" action="/catalogo" method="get" aria-label="Buscar coche">
        <div class="hm-search__tabs" role="tablist">
          <span class="hm-search__tab is-on" role="tab" aria-selected="true">Comprar</span>
          <a class="hm-search__tab hm-search__tab--link"
             href="/vende">Vende tu coche →</a>
        </div>
        <h2 class="hm-search__h">Encuentra tu próximo coche</h2>

        <label class="hm-f">
          <span>Marca</span>
          <select class="mc-input" name="marca" id="s-marca">
            <option value="">Todas ({{ $total }})</option>
            @foreach($marques as $m)
              <option value="{{ $m['key'] }}" {{ $m['n'] ? '' : 'disabled' }}>{{ $m['name'] }} ({{ $m['n'] }})</option>
            @endforeach
          </select>
        </label>

        <label class="hm-f">
          <span>Modelo</span>
          <select class="mc-input" name="modelo" id="s-modelo">
            <option value="">Todos</option>
          </select>
        </label>

        {{-- "Pago: al contado / al mes" removed 2026-10-20 at the client's request:
             the price ceiling is always the cash price (the script still reads a
             "pago" radio if one ever comes back) --}}
        <label class="hm-f">
          <span id="s-max-label">Precio máximo</span>
          <select class="mc-input" name="max" id="s-max" aria-labelledby="s-max-label">
            <option value="">Sin límite</option>
          </select>
        </label>

        <button class="mc-btn mc-btn--primary hm-search__go" type="submit" id="s-go">
          <span id="s-lead">Buscar</span> <b id="s-n">{{ $total }}</b> <span id="s-word">coches</span>
        </button>
        <a class="mc-link hm-search__ask" id="s-ask" hidden href="https://wa.me/34614753187">No lo tengo ahora: te lo busco por WhatsApp</a>
      </form>
    </div>
  </section>

  {{-- ============ the five marques ============ --}}
  <section class="hm-marques" aria-label="Marcas">
    <div class="cat-wrap">
      <ul class="hm-marques__list">
        @foreach($marques as $m)
          <li>
            <a class="hm-marque {{ $m['n'] ? '' : 'is-empty' }}" href="/catalogo#marque-{{ $m['key'] }}">
              <span class="hm-marque__logo" style="--logo:url('{{ asset('img/marques/'.$m['key'].'.svg') }}')"></span>
              <span class="hm-marque__name">{{ $m['name'] }}</span>
              <span class="hm-marque__n">{{ $m['n'] ? $m['n'].' '.($m['n'] === 1 ? 'disponible' : 'disponibles') : 'bajo pedido' }}</span>
            </a>
          </li>
        @endforeach
      </ul>
    </div>
  </section>

  {{-- ============ what is here now ============ --}}
  <section class="hm-sec cat-wrap" aria-labelledby="h-now">
    <div class="hm-sec__head">
      <h2 class="hm-h2" id="h-now">Disponibles ahora</h2>
      <a class="mc-link" href="/catalogo">Ver el catálogo →</a>
    </div>
    <div class="home-rail">
      <div class="home-rail__in">
        @foreach($available as $car)
          @include('partials.card', ['car' => $car, 'kind' => 'available'])
        @endforeach
      </div>
    </div>
  </section>

  {{-- ============ what they wrote ============
       partials/reviews.blade.php (2026-10-16: 4.6 "La fila" from the showroom
       replaced the drifting rail; the rail is in git at 5f12842). --}}
  @if($reviewCount > 0)
    @include('partials.reviews')
  @endif

  {{-- ============ where else he is ============
       Below the reviews on purpose: the photographs above are 25 people who
       bought. This is the 600,000 who watched. One is proof, the other is reach,
       and reach only means something after proof.

       NOT three equal tiles. TikTok is the one that works, so it takes the
       section and the other two take a line each. Three logos in a row would be
       stating a symmetry that is not true and would waste the only number here
       big enough to make anyone press anything.

       The number is the picture. It is set at display scale in TikTok's light
       mode (white ground, black ink; was their black until 2026-10-16, when the
       reviews above became the page's dark band), and it carries TikTok's own chromatic split — cyan #25F4EE and red
       #FE2C55, the two halves of their mark — offset and then resolving into
       register as it arrives. That device belongs to the platform being linked
       to; it is not an ornament borrowed from somewhere. Everything else here is
       the site's own furniture.
       ============================================================== --}}
  <section class="hm-soc" id="social" aria-labelledby="h-soc">
    <div class="cat-wrap hm-soc__in">

      <div class="hm-soc__say">
        <p class="hm-soc__kick">Fuera de esta web</p>
        <h2 class="hm-h2 hm-soc__h" id="h-soc">Los coches se ven mejor en movimiento.</h2>
        <p class="hm-soc__p">Los coches están en Málaga. Las miradas llegan de todas partes.</p>
        <p class="hm-soc__p">Nuestros vídeos nos conectan con personas de muchos lugares que comparten
          nuestra pasión por los coches. Muchos nos descubren en redes y nos escriben para encontrar
          su próximo vehículo.</p>
        <p class="hm-soc__p">Explora nuestros reels, conoce detalles y curiosidades, descubre nuestras
          últimas entregas. El coche que te interesa puede estar también en nuestros vídeos.</p>
        <p class="hm-soc__p"><a class="hm-soc__cta" href="{{ $social['tiktok']['url'] ?? 'https://www.tiktok.com/@_ivmotorclass' }}"
          target="_blank" rel="noopener">Descubrir IV Motorclass en redes</a></p>
      </div>

      <div class="hm-soc__num">
        {{-- aria-hidden on the animated copy; the real figure is in the text
             below it, so a screen reader is read a number and not a counter. --}}
        <p class="hm-soc__big" data-to="{{ $social['tiktok']['plays'] }}" aria-hidden="true">
          <span class="hm-soc__ghost hm-soc__ghost--c">0</span>
          <span class="hm-soc__ghost hm-soc__ghost--r">0</span>
          <span class="hm-soc__real" id="soc-n">0</span>
        </p>
        <p class="hm-soc__unit">
          <span class="mc-vh">{{ number_format($social['tiktok']['plays'], 0, ',', '.') }} </span>reproducciones en TikTok
        </p>
      </div>

      <ul class="hm-soc__list">
        <li class="hm-soc__row hm-soc__row--lead">
          <a class="hm-soc__link" href="{{ $social['tiktok']['url'] }}" target="_blank" rel="noopener">
            @include('partials.social-mark', ['k' => 'tiktok'])
            <span class="hm-soc__t">
              <b>Ver los coches en TikTok</b>
              <em>{{ $social['tiktok']['handle'] }}</em>
            </span>
            <span class="hm-soc__go" aria-hidden="true"></span>
          </a>
        </li>
        @foreach(['instagram', 'facebook'] as $k)
          <li class="hm-soc__row">
            <a class="hm-soc__link" href="{{ $social[$k]['url'] }}" target="_blank" rel="noopener">
              @include('partials.social-mark', ['k' => $k])
              <span class="hm-soc__t">
                <b>{{ $social[$k]['name'] }}</b>
                <em>{{ $social[$k]['handle'] }}</em>
              </span>
              <span class="hm-soc__go" aria-hidden="true"></span>
            </a>
          </li>
        @endforeach
      </ul>

    </div>
  </section>

  {{-- ============ how it actually goes ============
       This was "Proceso simple en 4 pasos": Elige, Verifica, Prueba, Finaliza —
       the stock four-step funnel, in four equal one-line columns. Three of the
       four were not true here. There is no technical report anywhere on this
       site; what he sends is video, with the parts that are not perfect in it.
       There is no showroom to book a test drive at — the contact page says so in
       his own words. And several buyers never came at all: one drove 720 km, one
       had the car delivered to his door.

       So it does not claim a process any more. Each step is a thing the visitor
       can go and check somewhere else on this site, which is why every one of
       them ends in a link. The route runs down the page rather than across it
       because the copy is now sentences, and four narrow columns would clamp
       them back into slogans — which is how it got here. --}}
  <section class="hm-sec hm-how cat-wrap" aria-labelledby="h-steps">
    <div class="hm-how__head">
      <h2 class="hm-h2" id="h-steps">Cómo va, de verdad</h2>
      <p class="hm-sec__p">No hay concesionario, ni centralita, ni un formulario esperando
        a que alguien lo mire mañana. Hay un teléfono, un coche y yo.</p>
    </div>

    <ol class="hm-how__list">
      <li class="hm-how__step">
        <span class="hm-how__n" aria-hidden="true">01</span>
        <div class="hm-how__t">
          <h3>Tu primer mensaje lo recibo yo</h3>
          <p>Soy el director de IV Motorclass y quien te acompañará durante toda la compra.
            Escríbeme por WhatsApp y cuéntame qué coche te interesa. Hablaremos de lo que buscas
            y resolveré tus dudas, sin pasarte de una persona a otra.</p>
          <a class="mc-link" href="https://wa.me/34614753187?text=Hola%2C+me+interesa+un+coche.+%C2%BFHablamos%3F">Hablemos por WhatsApp</a>
        </div>
      </li>
      <li class="hm-how__step">
        <span class="hm-how__n" aria-hidden="true">02</span>
        <div class="hm-how__t">
          <h3>Te lo enseño antes de que vengas</h3>
          <p>Te preparo un vídeo del coche y me detengo en los detalles que quieras comprobar.
            Verás el exterior, el interior, el equipamiento y también sus posibles desperfectos.
            Si vas a desplazarte para verlo, quiero que sepas de antemano qué te vas a encontrar.</p>
          <a class="mc-link" href="/catalogo">Elige el coche que quieres conocer</a>
        </div>
      </li>
      <li class="hm-how__step">
        <span class="hm-how__n" aria-hidden="true">03</span>
        <div class="hm-how__t">
          <h3>Lo pruebas y decides con calma</h3>
          <p>Quedamos en Málaga, vemos el coche juntos y salimos a probarlo. Tendrás tiempo para
            preguntar y decidir, sin presiones. Si compras a distancia, me encargo de enseñártelo
            y resolver tus dudas personalmente antes de que tomes la decisión.</p>
          <a class="mc-link" href="#reviews">Así lo vivieron otros clientes</a>
        </div>
      </li>
      <li class="hm-how__step">
        <span class="hm-how__n" aria-hidden="true">04</span>
        <div class="hm-how__t">
          <h3>Las llaves son tuyas. Mi teléfono sigue disponible.</h3>
          <p>Con la documentación preparada, en solo 30 minutos puedes llevártelo transferido a tu
            nombre y con el seguro en vigor. Yo me encargo de los trámites y de entregártelo. Y si
            después tienes una duda o surge algún problema, puedes seguir llamándome: mi atención
            continúa después de la compra.</p>
          <a class="mc-link" href="https://wa.me/34614753187?text=Hola%2C+quiero+hablar+de+mi+pr%C3%B3ximo+coche.">Hablemos de tu próximo coche</a>
        </div>
      </li>
    </ol>
    <p class="hm-how__more"><a class="mc-link" href="/por-que-nosotros">Por qué comprar aquí</a></p>
  </section>

  {{-- ============ questions ============ --}}
  <section class="hm-sec hm-faq cat-wrap" aria-labelledby="h-faq">
    <h2 class="hm-h2" id="h-faq">Preguntas frecuentes</h2>
    <details class="hm-q"><summary>¿Ofrecen garantía para vehículos?</summary><p>Sí, ofrecemos garantía extendida hasta 36 meses, dependiendo del modelo.</p></details>
    <details class="hm-q"><summary>¿Puedo comprar en leasing o con financiación?</summary><p>Sí, colaboramos con socios financieros para ofertas rápidas y ventajosas.</p></details>
    <details class="hm-q"><summary>¿Puedo hacer una prueba de conducción?</summary><p>Por supuesto. Escríbeme por WhatsApp o llámame y la programamos.</p></details>
  </section>

  {{-- ============ the ask ============ --}}
  <section class="hm-cta">
    <div class="cat-wrap hm-cta__in">
      <h2 class="hm-cta__h">Encuentra tu coche perfecto hoy</h2>
      <p class="hm-cta__p">Stock actualizado, verificaciones completas y ofertas flexibles de financiación.</p>
      {{-- base .mc-btn, not --cta: that modifier paints the WhatsApp mark, and this
           goes to the catalogue. --}}
      <p class="hm-cta__go">
        <a class="mc-btn" href="/catalogo">Entrar al catálogo</a>
        <a class="mc-btn mc-btn--outline" href="/vende">Vende tu coche</a>
      </p>
    </div>
  </section>
</main>
@endsection

@section('after')
<div class="cat-dock" role="complementary" aria-label="Contacto">
  <div class="mc-bar">
    <span class="cat-dock__t">¿Buscas algo concreto?<b>Te lo busco yo</b></span>
    <span class="mc-bar__act">
      <a class="mc-btn mc-btn--cta" href="https://wa.me/34614753187?text=Hola%2C+busco+algo+concreto%3A+">WhatsApp</a>
      <a class="mc-btn mc-btn--ghost" href="tel:+34614753187" aria-label="Llamar">Tel</a>
    </span>
  </div>
</div>
@endsection

@push('js')
<script>
/* "Disponibles ahora" moves by itself: one card every 3.5 s, for ever. The cards
   are followed by aria-hidden, unfocusable copies so the loop has no visible jump:
   once the first set has scrolled away, the strip is moved back by exactly one set
   width, which shows the same picture. It waits while a hand is on it (hover,
   touch, focus), while it is off screen, and does not move at all for
   prefers-reduced-motion. A swipe still works: the strip is a normal scroller. */
(function () {
  var rail = document.querySelector('.home-rail__in');
  if (!rail || rail.children.length < 2) return;
  if (window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  var originals = Array.prototype.slice.call(rail.children);
  originals.forEach(function (c) {
    var k = c.cloneNode(true); k.setAttribute('aria-hidden', 'true'); k.setAttribute('data-clone', '');
    k.querySelectorAll('a, button').forEach(function (a) { a.setAttribute('tabindex', '-1'); });
    rail.appendChild(k);
  });
  function setWidth() { return rail.children[originals.length].offsetLeft - rail.children[0].offsetLeft; }
  function step() { var a = rail.children[0], b = rail.children[1]; return b.offsetLeft - a.offsetLeft; }
  var held = false, visible = false, timer = null;
  function wrap() { var w = setWidth(); if (rail.scrollLeft >= w - 2) rail.scrollLeft -= w; }
  function tick() {
    if (held || !visible || document.hidden) return;
    wrap();
    rail.scrollBy({ left: step(), behavior: 'smooth' });
  }
  rail.addEventListener('scroll', function () { clearTimeout(rail._t); rail._t = setTimeout(wrap, 150); }, { passive: true });
  ['mouseenter', 'touchstart', 'focusin'].forEach(function (e) { rail.addEventListener(e, function () { held = true; }, { passive: true }); });
  rail.addEventListener('mouseleave', function () { held = false; });
  rail.addEventListener('focusout', function () { held = false; });
  // a hand that touched it has taken over: it stays where they left it (WCAG 2.2.2)
  if ('IntersectionObserver' in window) {
    new IntersectionObserver(function (es) { visible = es[0].isIntersecting; }, { threshold: .3 }).observe(rail);
  } else { visible = true; }
  timer = setInterval(tick, 3500);
})();
</script>
@endpush

@push('js')
<script>
/* ---- the number arrives once -----------------------------------------------
   It counts up to 600.000 and the two chromatic halves settle into register at
   the same time. Once, on first sight, and never again — a figure that re-runs
   every time it scrolls past is a widget, and it stops meaning anything by the
   third time.

   The counter is aria-hidden and the real figure is in the text below it, so a
   screen reader is read a number rather than a slot machine. Under
   prefers-reduced-motion the number is simply set and the halves do not travel:
   the CSS holds them in register and this skips straight to the end. */
(function () {
  var sec = document.getElementById('social');
  if (!sec) { return; }
  var big = sec.querySelector('.hm-soc__big');
  var out = sec.querySelectorAll('.hm-soc__real, .hm-soc__ghost');
  var to  = parseInt(big.getAttribute('data-to'), 10) || 0;
  var fmt = function (n) { return new Intl.NumberFormat('es-ES').format(n); };
  var still = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  var land = function () {
    for (var i = 0; i < out.length; i++) { out[i].textContent = fmt(to); }
    sec.classList.add('is-in');
  };
  if (still || !('IntersectionObserver' in window)) { sec.classList.add('is-armed'); land(); return; }

  sec.classList.add('is-armed');
  var io = new IntersectionObserver(function (es) {
    if (!es[0].isIntersecting) { return; }
    io.disconnect();
    sec.classList.add('is-in');            // the halves start travelling now
    var t0 = null, RUN = 1400;
    var step = function (t) {
      if (t0 === null) { t0 = t; }
      var p = Math.min(1, (t - t0) / RUN);
      // out-cubic: fast at the start, so the size of the number reads immediately,
      // then slow enough at the end that the last digits can be followed
      var e = 1 - Math.pow(1 - p, 3);
      var n = Math.round(to * e);
      for (var i = 0; i < out.length; i++) { out[i].textContent = fmt(n); }
      if (p < 1) { requestAnimationFrame(step); }
    };
    requestAnimationFrame(step);
  }, { threshold: 0.35 });
  io.observe(sec);
})();
</script>
@endpush

@push('js')
<script>
(function () {
  /* the reviews' row has its own file now: public/js/reviews.js */

  document.documentElement.className += ' js';
  var STOCK  = @json($stock);
  var MONTHS = {{ $months }};
  var marca = document.getElementById('s-marca'), modelo = document.getElementById('s-modelo');
  var max = document.getElementById('s-max'), n = document.getElementById('s-n'), word = document.getElementById('s-word');
  var note = document.getElementById('s-note'), maxLabel = document.getElementById('s-max-label');
  if (!marca) return;

  function pago() { var r = document.querySelector('input[name=pago]:checked'); return r ? r.value : 'contado'; }
  function money(v) { return new Intl.NumberFormat('es-ES').format(v) + ' €'; }

  // The price steps come from the stock itself, so the list never offers a ceiling
  // nothing sits under, and "al mes" is the same arithmetic as the car page's calculator.
  function fillMax() {
    var monthly = pago() === 'mes';
    var vals = STOCK.map(function (c) { return monthly ? c.month : c.price; });
    var top = Math.max.apply(null, vals), step = monthly ? 50 : 2500;
    var keep = max.value;
    max.innerHTML = '<option value="">Sin límite</option>';
    for (var v = Math.ceil(Math.min.apply(null, vals) / step) * step; v <= Math.ceil(top / step) * step; v += step) {
      var o = document.createElement('option'); o.value = v;
      o.textContent = monthly ? money(v) + ' / mes' : money(v);
      max.appendChild(o);
    }
    max.value = keep && max.querySelector('option[value="' + keep + '"]') ? keep : '';
    maxLabel.textContent = monthly ? 'Cuota máxima' : 'Precio máximo';
    if (note) note.hidden = !monthly;
  }
  function fillModels() {
    var keep = modelo.value;
    modelo.innerHTML = '<option value="">Todos</option>';
    var seen = {};
    STOCK.filter(function (c) { return !marca.value || c.marca === marca.value; })
      .map(function (c) { return c.model; }).sort()
      .forEach(function (m) { if (seen[m]) return; seen[m] = 1;
        var o = document.createElement('option'); o.value = m; o.textContent = m; modelo.appendChild(o); });
    modelo.value = keep && modelo.querySelector('option[value="' + CSS.escape(keep) + '"]') ? keep : '';
  }
  function count() {
    var monthly = pago() === 'mes', lim = parseInt(max.value, 10) || Infinity;
    var k = STOCK.filter(function (c) {
      return (!marca.value || c.marca === marca.value)
          && (!modelo.value || c.model === modelo.value)
          && ((monthly ? c.month : c.price) <= lim);
    }).length;
    n.textContent = k; word.textContent = k === 1 ? 'coche' : 'coches';
    // "Buscar 0 coches" is a dead end dressed as a button; say what is true instead
    var go = document.getElementById('s-go');
    go.disabled = k === 0;
    document.getElementById('s-lead').hidden = k === 0;
    if (k === 0) { n.textContent = ''; word.textContent = 'Ningún coche así'; }
    // nothing fits: the man who finds cars is one message away
    var ask = document.getElementById('s-ask');
    if (ask) {
      ask.hidden = k !== 0;
      var mo = modelo.value, mx = max.value, mk = marca.options[marca.selectedIndex];
      var want = [mk && marca.value ? mk.textContent.replace(/\s*\(.*\)\s*$/, '') : '', mo, mx ? 'hasta ' + Number(mx).toLocaleString('es-ES') + (document.querySelector('input[name=pago]:checked') && document.querySelector('input[name=pago]:checked').value === 'mes' ? ' €/mes' : ' €') : ''].filter(Boolean).join(' ');
      ask.href = 'https://wa.me/34614753187?text=' + encodeURIComponent('Hola, busco ' + (want || 'un coche') + '. ¿Me lo buscas?');
    }
  }
  marca.addEventListener('change', function () { fillModels(); count(); });
  modelo.addEventListener('change', count);
  max.addEventListener('change', count);
  document.querySelectorAll('input[name=pago]').forEach(function (r) { r.addEventListener('change', function () { fillMax(); count(); }); });
  fillMax(); fillModels(); count();
})();
</script>
@endpush
