<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, interactive-widget=resizes-content">
<meta name="robots" content="noindex, nofollow">
<title>IV MOTORCLASS — Coches alemanes premium en Málaga</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&display=swap">
<link rel="stylesheet" href="{{ asset('css/mc-tokens.css') }}">
<link rel="stylesheet" href="{{ asset('css/brandbook.css') }}">
<link rel="stylesheet" href="{{ asset('css/catalog.css') }}">
<link rel="stylesheet" href="{{ asset('css/foot.css') }}">
<link rel="stylesheet" href="{{ asset('css/home.css') }}">
</head>
<body class="bb cat home">

@include('partials.head', ['current' => 'inicio'])

<main>

  {{-- ============ the hero ============ --}}
  <section class="hm-hero">
    <img class="hm-hero__img" src="{{ asset('img/banner/home.jpg') }}" alt=""
         width="2400" height="3600" fetchpriority="high" decoding="async">
    <div class="hm-hero__in cat-wrap">
      <div class="hm-hero__copy">
        <span class="hm-hero__eyebrow">Málaga · {{ $sold }} coches entregados</span>
        <h1 class="hm-hero__h">Tu coche perfecto<br>te espera</h1>
        <p class="hm-hero__p">Meticulosamente escogido y llevado a los estándares más altos para ti.</p>
        <span class="hm-hero__credit">Foto de archivo · Unsplash</span>
      </div>

      {{-- The search. Every figure on it is counted from the stock. --}}
      <form class="hm-search" id="search" action="/catalogo" method="get" aria-label="Buscar coche">
        <div class="hm-search__tabs" role="tablist">
          <span class="hm-search__tab is-on" role="tab" aria-selected="true">Comprar</span>
          <a class="hm-search__tab hm-search__tab--link"
             href="https://wa.me/34614753187?text={{ urlencode('Hola, quiero vender mi coche.') }}">Vende tu coche →</a>
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

        <div class="hm-f">
          <span>Pago</span>
          <div class="hm-seg" role="radiogroup" aria-label="Forma de pago">
            <label class="hm-seg__o"><input type="radio" name="pago" value="contado" checked><span>Al contado</span></label>
            <label class="hm-seg__o"><input type="radio" name="pago" value="mes"><span>Al mes</span></label>
          </div>
        </div>

        <label class="hm-f">
          <span id="s-max-label">Precio máximo</span>
          <select class="mc-input" name="max" id="s-max" aria-labelledby="s-max-label">
            <option value="">Sin límite</option>
          </select>
          <small class="hm-f__note" id="s-note" hidden>Al mes: precio entre {{ $months }} meses, <b>sin intereses ni comisiones</b>.</small>
        </label>

        <button class="mc-btn mc-btn--primary hm-search__go" type="submit" id="s-go">
          Buscar <b id="s-n">{{ $total }}</b> <span id="s-word">coches</span>
        </button>
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
       One row, 9,469px long, drifting on its own for ever. No arrows, no dots,
       no pause button: the movement is the invitation, and a wall of controls
       under a testimonial rail is the thing that makes it look like a widget.

       The cell is unchanged, because the cell was right. Each review is a column
       as wide as it needs to be — floored so it stays readable, capped at a
       64-character measure — and inside it the text takes what it needs while
       the photograph takes what is left: the height when the review is short and
       the picture sits above it, the width when the review is long and the
       picture stands beside it. 24 reviews, 6,900 characters, nothing truncated.

       The row is printed twice. The second copy is the first copy's continuation,
       so when the drift has travelled one row's width the scroll position is put
       back by exactly that width and the picture on screen does not change. It is
       carried by the browser's own scroller, which is why a trackpad, a swipe and
       a shift-wheel all work on it without a line of code, and why a flick has
       the platform's own momentum instead of an imitation of it.
       ================================================================== --}}
  <section class="hm-fb" id="reviews" aria-labelledby="h-fb">
    <div class="cat-wrap hm-fb__head">
      <h2 class="hm-h2" id="h-fb">{{ $reviewCount }} personas se hicieron la foto</h2>
      <p class="hm-sec__p">Con el coche que se llevaron. Ninguna foto de archivo,
        y ningún texto recortado: lo que escribieron está aquí entero.</p>
    </div>

    {{-- The one control there is. It is not visible until a keyboard reaches it,
         because moving content has to be stoppable and a permanent button would
         be the widget chrome this section is trying not to be. --}}
    <button class="hm-fb__halt" id="fb-halt" type="button" hidden>Detener el movimiento</button>

    <div class="hm-fb__rail" id="fb-rail" tabindex="0"
         role="group" aria-roledescription="carrusel"
         aria-label="Lo que escribieron los clientes">
      @foreach([0, 1] as $pass)
        <div class="hm-fb__row" @if($pass) aria-hidden="true" @endif>
          @foreach($reviews as $t)
            @php $src = fn ($w) => route('img.resize', ['w' => $w]) . '?p=' . urlencode($t->img); @endphp
            <figure class="fb {{ $t->side ? 'fb--side' : '' }} {{ $t->xl ? 'fb--xl' : '' }}"
                    style="--w:{{ $t->w }}px; --cell:{{ $t->cell }}px">
              <div class="fb__ph">
                <img src="{{ $src(600) }}"
                     srcset="{{ $src(400) }} 400w, {{ $src(600) }} 600w, {{ $src(900) }} 900w"
                     sizes="(min-width:1000px) 320px, 70vw"
                     alt="{{ $pass ? '' : $t->name . ', con su coche' }}"
                     loading="lazy" decoding="async">
              </div>
              <div class="fb__t">
                <blockquote class="fb__q">{{ $t->quote }}</blockquote>
                <figcaption class="fb__by">{{ $t->name }}</figcaption>
              </div>
            </figure>
          @endforeach
        </div>
      @endforeach
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
          <h3>Escribes</h3>
          <p>Al WhatsApp que hay en toda la web. Contesto yo, y normalmente en minutos.
            No hay nadie más al otro lado.</p>
          <a class="mc-link" href="/contacto">Ver cómo contactar</a>
        </div>
      </li>
      <li class="hm-how__step">
        <span class="hm-how__n" aria-hidden="true">02</span>
        <div class="hm-how__t">
          <h3>Te mando vídeo</h3>
          <p>Del coche entero, y de lo que no está perfecto también. Prefiero que lo
            veas en el móvil antes de coger el coche para venir.</p>
          <a class="mc-link" href="/catalogo">Ver los coches</a>
        </div>
      </li>
      <li class="hm-how__step">
        <span class="hm-how__n" aria-hidden="true">03</span>
        <div class="hm-how__t">
          <h3>Lo ves. O no lo ves</h3>
          <p>Quedamos en Málaga, en el punto exacto donde esté el coche. O no vienes:
            hay quien ha conducido 720 km para verlo y quien no se movió de casa.</p>
          <a class="mc-link" href="/contacto#far">Lo que hicieron ellos</a>
        </div>
      </li>
      <li class="hm-how__step">
        <span class="hm-how__n" aria-hidden="true">04</span>
        <div class="hm-how__t">
          <h3>Te lo llevas</h3>
          <p>Papeles, transferencia y entrega. Si no puedes venir a por él, te lo llevo.</p>
          <a class="mc-link" href="https://wa.me/34614753187">Preguntar por uno</a>
        </div>
      </li>
    </ol>
  </section>

  {{-- ============ questions ============ --}}
  <section class="hm-sec hm-faq cat-wrap" aria-labelledby="h-faq">
    <h2 class="hm-h2" id="h-faq">Preguntas frecuentes</h2>
    <details class="hm-q"><summary>¿Ofrecen garantía para vehículos?</summary><p>Sí, ofrecemos garantía extendida hasta 24 meses, dependiendo del modelo.</p></details>
    <details class="hm-q"><summary>¿Puedo comprar en leasing o con financiación?</summary><p>Sí, colaboramos con socios financieros para ofertas rápidas y ventajosas.</p></details>
    <details class="hm-q"><summary>¿Puedo programar una prueba de manejo?</summary><p>Por supuesto. Escríbeme por WhatsApp o llámame y la programamos.</p></details>
  </section>

  {{-- ============ the ask ============ --}}
  <section class="hm-cta">
    <div class="cat-wrap hm-cta__in">
      <h2 class="hm-cta__h">Encuentra tu coche perfecto hoy</h2>
      <p class="hm-cta__p">Stock actualizado, verificaciones completas y ofertas flexibles de financiación.</p>
      <a class="mc-btn mc-btn--cta" href="/catalogo">Entrar al catálogo</a>
    </div>
  </section>
</main>

@include('partials.foot')

<div class="cat-dock" role="complementary" aria-label="Contacto">
  <div class="mc-bar">
    <span class="cat-dock__t">¿Buscas algo concreto?<b>Te lo busco yo</b></span>
    <span class="mc-bar__act">
      <a class="mc-btn mc-btn--cta" href="https://wa.me/34614753187">WhatsApp</a>
      <a class="mc-btn mc-btn--ghost" href="tel:+34614753187" aria-label="Llamar">Tel</a>
    </span>
  </div>
</div>

<script>
(function () {
  /* ---- the drift --------------------------------------------------------
     The row moves by itself, for ever, and there is nothing to press. It is the
     browser's own scroller underneath, so a trackpad, a swipe, shift+wheel and
     the arrow keys all work on it for free, and a flick keeps the platform's own
     momentum rather than an imitation of it. All this adds is a constant
     velocity on top and a wrap at the seam.

     Velocity is never set, only aimed at: `v` chases `want` with a time
     constant, so hovering does not stop the row dead — it takes it down to a
     stop over about a quarter of a second, and lets it back up the same way.
     A hard stop is the single thing that makes one of these feel like a widget.

     dt-normalised, so a 120Hz screen does not run it at twice the speed.

     It yields to the reader completely: any wheel, drag, touch or key inside the
     rail suspends the drift, and it only creeps back once they have been still
     for a moment. And it never starts at all under prefers-reduced-motion. */
  var rail = document.getElementById('fb-rail');
  if (rail) {
    var SPEED = 34;        // px per second
    var TAU   = 260;       // ms for the velocity to close most of a change
    var YIELD = 1400;      // ms of stillness before the drift comes back
    var still = window.matchMedia('(prefers-reduced-motion: reduce)');
    var halt  = document.getElementById('fb-halt');

    var row   = rail.firstElementChild;
    var rowW  = 0, v = 0, over = false, stopped = false, busy = 0, last = 0, raf = 0;
    // The drift's own position, in floating point. scrollLeft rounds, so adding
    // 0.57px to it every frame did not move it 0.57px — it rounded up to a whole
    // one, and the row ran at exactly one pixel per frame whatever SPEED said.
    // Measured before the fix: 181 frames, 181px, 60.3px/s against a constant of
    // 34. We keep the position and hand the scroller a number; when the reader
    // takes over, we take theirs back.
    var pos = 0, mine = 0;

    function measure() { rowW = row.scrollWidth; }

    function frame(now) {
      raf = requestAnimationFrame(frame);
      var dt = last ? Math.min(64, now - last) : 16;
      last = now;
      if (!rowW) { measure(); return; }

      var want = (still.matches || stopped || over || now < busy) ? 0 : SPEED;
      v += (want - v) * (1 - Math.exp(-dt / TAU));

      if (v <= 0.02) { pos = rail.scrollLeft; return; }   // theirs, not ours
      if (Math.abs(pos - rail.scrollLeft) > 2) { pos = rail.scrollLeft; }

      pos += v * dt / 1000;
      // the seam: one row on, one row back, and the picture does not change
      if (pos >= rowW) { pos -= rowW; }
      else if (pos < 0) { pos += rowW; }

      rail.scrollLeft = pos;
      mine = now;
    }

    function yieldNow() { busy = performance.now() + YIELD; }
    ['wheel', 'pointerdown', 'touchstart', 'keydown'].forEach(function (e) {
      rail.addEventListener(e, yieldNow, { passive: true });
    });
    rail.addEventListener('scroll', function () {
      // A scroll we did not cause is the reader's, and the drift steps aside for
      // it. Ours fire this too, so the test is when, not whether.
      if (performance.now() - mine > 120) { yieldNow(); }
    }, { passive: true });

    var sec = document.getElementById('reviews');
    sec.addEventListener('pointerenter', function () { over = true; });
    sec.addEventListener('pointerleave', function () { over = false; });
    sec.addEventListener('focusin',  function () { over = true; });
    sec.addEventListener('focusout', function () {
      if (!sec.contains(document.activeElement)) { over = false; }
    });

    if (halt) {
      halt.hidden = false;
      halt.addEventListener('click', function () {
        stopped = !stopped;
        halt.textContent = stopped ? 'Reanudar el movimiento' : 'Detener el movimiento';
        halt.setAttribute('aria-pressed', stopped ? 'true' : 'false');
      });
      halt.setAttribute('aria-pressed', 'false');
    }

    window.addEventListener('resize', measure);
    document.addEventListener('visibilitychange', function () {
      if (document.hidden) { cancelAnimationFrame(raf); raf = 0; last = 0; }
      else if (!raf) { raf = requestAnimationFrame(frame); }
    });
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (es) {
        if (es[0].isIntersecting) { if (!raf) { last = 0; raf = requestAnimationFrame(frame); } }
        else { cancelAnimationFrame(raf); raf = 0; }
      }, { threshold: 0 }).observe(sec);
    } else {
      raf = requestAnimationFrame(frame);
    }
    window.addEventListener('load', measure);
    measure();
  }

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
    note.hidden = !monthly;
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
    if (k === 0) { n.textContent = ''; word.textContent = 'Ningún coche así — cambia algo'; }
  }
  marca.addEventListener('change', function () { fillModels(); count(); });
  modelo.addEventListener('change', count);
  max.addEventListener('change', count);
  document.querySelectorAll('input[name=pago]').forEach(function (r) { r.addEventListener('change', function () { fillMax(); count(); }); });
  fillMax(); fillModels(); count();
})();
</script>
</body>
</html>
