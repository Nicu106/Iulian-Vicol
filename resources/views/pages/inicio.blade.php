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
       A carousel, running on its own, holding every review in full.

       The measurements that decide the shape: 24 reviews, 6,900 characters,
       7m25s of reading. 49% of all that text is in 5 of the 24 (494 to 901
       characters) while 15 are under 250. One slide shape and one interval
       cannot serve both — the first version's answer was to clamp the long ones
       behind "Ver más", which shows a truncated review as if it were the review.

       So the shape follows the text. Each review is a column as wide as it needs
       to be, floored so it stays readable and capped at a 64-character measure,
       and the columns are packed into slides. Inside a column the text takes what
       it needs and THE PHOTOGRAPH TAKES WHAT IS LEFT: height when the review is
       short, width when it is long. One idea on two axes, and every one of the
       24 photographs is used at a size it earns.

       The interval follows the text too — a slide's dwell is its own character
       count, 7 to 12 seconds, so a dense slide is not gone before a sparse one
       has been looked at.

       It stops when you touch it, when you tab into it, when the tab is hidden,
       when it scrolls out of view, and when you press pause — and it never
       starts at all under prefers-reduced-motion. Without JavaScript the rail is
       a plain horizontal scroller with all nine slides in it. --}}
  <section class="hm-fb" id="reviews" aria-labelledby="h-fb">
    <div class="cat-wrap hm-fb__head">
      <h2 class="hm-h2" id="h-fb">{{ $reviewCount }} personas se hicieron la foto</h2>
      <p class="hm-sec__p">Con el coche que se llevaron. Ninguna foto de archivo,
        y ningún texto recortado: lo que escribieron está aquí entero.</p>
    </div>

    <div class="cat-wrap hm-fb__stage">
      <div class="hm-fb__rail" id="fb-rail" tabindex="0"
           role="group" aria-roledescription="carrusel" aria-label="Lo que escribieron los clientes">
        @foreach($slides as $i => $row)
          <div class="hm-fb__slide" role="group" aria-roledescription="diapositiva"
               aria-label="{{ $i + 1 }} de {{ count($slides) }}"
               data-chars="{{ collect($row)->sum('len') }}">
            @foreach($row as $t)
              @php
                $src = fn ($w) => route('img.resize', ['w' => $w]) . '?p=' . urlencode($t->img);
              @endphp
              <figure class="fb {{ $t->side ? 'fb--side' : '' }}"
                      style="--w:{{ $t->w }}px; --cell:{{ $t->cell }}px">
                <div class="fb__ph">
                  <img src="{{ $src(600) }}"
                       srcset="{{ $src(400) }} 400w, {{ $src(600) }} 600w, {{ $src(900) }} 900w"
                       sizes="(min-width:1000px) 320px, 70vw"
                       alt="{{ $t->name }}, con su coche" loading="lazy" decoding="async">
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

      <div class="hm-fb__bar">
        <button class="hm-fb__nav" type="button" data-go="-1" aria-label="Anterior">
          <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false">
            <path d="M15 4 7 12l8 8" fill="none" stroke="currentColor" stroke-width="2"
                  stroke-linecap="square"/></svg>
        </button>

        <p class="hm-fb__count" id="fb-count" aria-hidden="true"></p>

        <ol class="hm-fb__ticks" id="fb-ticks">
          @foreach($slides as $i => $row)
            <li><button type="button" data-to="{{ $i }}"
                        aria-label="Ir a la diapositiva {{ $i + 1 }}"></button></li>
          @endforeach
        </ol>

        <button class="hm-fb__nav" type="button" data-go="1" aria-label="Siguiente">
          <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false">
            <path d="M9 4l8 8-8 8" fill="none" stroke="currentColor" stroke-width="2"
                  stroke-linecap="square"/></svg>
        </button>

        <button class="hm-fb__play" id="fb-play" type="button" hidden>Pausar</button>
      </div>
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
  /* ---- the carousel -----------------------------------------------------
     A scroll-snap rail, not a transform carousel: swipe, trackpad, keyboard and
     the scrollbar are the browser's own and cost nothing, and with the script
     absent the rail is still a horizontal scroller holding every slide. Autoplay
     is then only a scrollTo on a timer.

     The dwell is the slide's own character count — 7s to 12s — because the
     packing made the slides similar in reading time but not identical, and a
     fixed interval would hand the densest slide the same seconds as the sparsest.

     It does not run when: the reader prefers reduced motion (never), the pointer
     is over it, focus is inside it, the tab is hidden, the section is off screen,
     or pause has been pressed. That last one is WCAG 2.2.2 — anything that moves
     by itself for more than five seconds needs a way to stop it — and the button
     is only shown once we know the script is here to honour it. */
  var rail = document.getElementById('fb-rail');
  if (rail) {
    // On a wide screen a slide is a packed group; below that the grouping is
    // dissolved in CSS and every review is its own stop. The script asks the
    // layout which it is rather than deciding for it.
    var groups = Array.prototype.slice.call(rail.children);
    var cells  = Array.prototype.slice.call(rail.querySelectorAll('.fb'));
    var packed = window.matchMedia('(min-width: 1000px)');
    var slides = packed.matches ? groups : cells;
    var ticks  = Array.prototype.slice.call(document.querySelectorAll('#fb-ticks button'));
    var play   = document.getElementById('fb-play');
    var still  = window.matchMedia('(prefers-reduced-motion: reduce)');
    var at = 0, timer = null, paused = false, seen = true, over = false;

    function dwell(i) {
      var n = +(slides[i].getAttribute('data-chars') || 400);
      return Math.max(7000, Math.min(12000, n * 10.5));
    }
    var count = document.getElementById('fb-count');
    function mark() {
      var on = Math.round(at / Math.max(1, slides.length - 1) * (ticks.length - 1));
      ticks.forEach(function (t, k) {
        t.setAttribute('aria-current', k === on ? 'true' : 'false');
      });
      if (count) { count.textContent = (at + 1) + ' / ' + slides.length; }
    }
    function go(i, smooth) {
      at = (i + slides.length) % slides.length;
      rail.scrollTo({ left: slides[at].offsetLeft - slides[0].offsetLeft,
                      behavior: smooth && !still.matches ? 'smooth' : 'auto' });
      mark();
    }
    function stop() { if (timer) { clearTimeout(timer); timer = null; } }
    function tick() {
      stop();
      if (still.matches || paused || over || !seen || document.hidden) { return; }
      timer = window.setTimeout(function () { go(at + 1, true); tick(); }, dwell(at));
    }

    // the rail is the source of truth: a swipe moves it, and everything follows
    var settle = null;
    rail.addEventListener('scroll', function () {
      window.clearTimeout(settle);
      settle = window.setTimeout(function () {
        var x = rail.scrollLeft + slides[0].offsetLeft, best = 0, d = Infinity;
        slides.forEach(function (s, k) {
          var v = Math.abs(s.offsetLeft - x);
          if (v < d) { d = v; best = k; }
        });
        if (best !== at) { at = best; mark(); }
        tick();
      }, 120);
    }, { passive: true });

    document.querySelectorAll('.hm-fb__nav').forEach(function (b) {
      b.addEventListener('click', function () { go(at + (+b.getAttribute('data-go')), true); tick(); });
    });
    ticks.forEach(function (t, k) {
      t.addEventListener('click', function () {
        go(Math.round(k / Math.max(1, ticks.length - 1) * (slides.length - 1)), true); tick();
      });
    });

    if (play) {
      play.hidden = false;
      play.addEventListener('click', function () {
        paused = !paused;
        play.textContent = paused ? 'Reanudar' : 'Pausar';
        play.setAttribute('aria-pressed', paused ? 'true' : 'false');
        tick();
      });
      play.setAttribute('aria-pressed', 'false');
    }

    var sec = document.getElementById('reviews');
    ['pointerenter', 'focusin'].forEach(function (e) {
      sec.addEventListener(e, function () { over = true; stop(); });
    });
    ['pointerleave', 'focusout'].forEach(function (e) {
      sec.addEventListener(e, function () {
        if (e === 'focusout' && sec.contains(document.activeElement)) { return; }
        over = false; tick();
      });
    });
    document.addEventListener('visibilitychange', tick);
    still.addEventListener('change', function () { still.matches ? stop() : tick(); });

    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (es) {
        seen = es[0].isIntersecting; tick();
      }, { threshold: 0.25 }).observe(sec);
    }

    function relayout() {
      var next = packed.matches ? groups : cells;
      if (next !== slides) { slides = next; at = Math.min(at, slides.length - 1); }
      // the ticks count the packed groups; off the packed layout they still mark
      // progress through the same reviews, so map the stop onto them
      go(at, false);
    }
    packed.addEventListener('change', relayout);
    window.addEventListener('resize', relayout);
    mark(); tick();
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
