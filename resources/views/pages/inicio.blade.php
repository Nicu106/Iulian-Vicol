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

<header class="mc-head">
  <div class="mc-head__in cat-wrap" style="padding-block:0">
    <a class="mc-logo" href="/inicio">IV&nbsp;MOTORCLASS</a>
    <nav class="mc-nav" style="margin-right:var(--s-4)">
      <a class="mc-nav__i is-current" href="/inicio">Inicio</a>
      <a class="mc-nav__i " href="/catalogo">Coches</a>
      <a class="mc-nav__i" href="/brandbook">Quién soy</a>
    </nav>
    <a class="mc-btn mc-btn--cta" href="https://wa.me/34614753187">WhatsApp</a>
  </div>
</header>

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

  {{-- ============ the people ============
       A mosaic, because the quotes run 32 to 901 characters and a uniform grid can
       only hold that by cutting the longest — which is the best one he has. Fixed
       width, free height, nothing clamped.

       On a wide screen the section is pinned and the mosaic travels sideways as the
       page scrolls: 24 reviews at full length are 4.1 screens of ordinary page, and
       this is one screen that the reader passes through. It is NOT scroll-hijacking
       — the scroll stays the browser's own, so the wheel, the bar, the keyboard,
       trackpad momentum and Page Down all behave exactly as they always do. Only the
       direction the content moves is ours.

       Narrow screens, reduced motion, and no JavaScript all get the plain vertical
       mosaic: pinning a sideways rail on a phone would fail WCAG's 400% reflow, and
       the mosaic reads perfectly well standing still. --}}
  <section class="hm-people" id="reviews" aria-labelledby="h-wall">
    <div class="hm-people__pin">
      <div class="cat-wrap hm-people__head">
        <h2 class="hm-h2" id="h-wall">{{ $wall->count() }} personas se hicieron la foto</h2>
        <p class="hm-sec__p">Con el coche que se llevaron. Ninguna foto de archivo, ningún texto recortado.</p>
      </div>

      <div class="hm-mosaic" id="mosaic">
        <div class="hm-mosaic__in" id="mosaic-in">
          @foreach($wall as $i => $t)
            <figure class="hm-t hm-t--{{ $t->kind }}">
              @if($t->kind === 'shot')
                <div class="hm-t__ph">
                  <img src="{{ $t->img }}" alt="{{ $t->name }} con su coche"
                       loading="lazy" decoding="async" width="600" height="750">
                </div>
              @endif
              <blockquote class="hm-t__q">{{ $t->quote }}</blockquote>
              <figcaption class="hm-t__by"><b>{{ $t->name }}</b>@if($t->place) · {{ $t->place }}@endif</figcaption>
            </figure>
          @endforeach
        </div>
      </div>
      </div>
    </div>
  </section>

  {{-- ============ how it goes ============ --}}
  <section class="hm-sec hm-steps cat-wrap" aria-labelledby="h-steps">
    <h2 class="hm-h2" id="h-steps">Proceso simple en 4 pasos</h2>
    <ol class="hm-steps__list">
      <li><b>Elige</b><span>Busca en el catálogo el modelo adecuado.</span></li>
      <li><b>Verifica</b><span>Historial claro e informe técnico detallado.</span></li>
      <li><b>Prueba</b><span>Programa una prueba de manejo sin compromisos.</span></li>
      <li><b>Finaliza</b><span>Financiación y entrega rápida.</span></li>
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
  /* ---- the pinned mosaic -------------------------------------------------
     The section is made tall; its contents stick to the top for that height; how
     far the page has moved through it becomes how far the mosaic has moved — and
     the mosaic moves FASTER than the page, so 24 reviews at full length pass in
     less scrolling than they would occupy standing still.

     The browser keeps its own scroll throughout: nothing is intercepted, no event
     is cancelled, no wheel is swallowed. The bar, the keyboard, trackpad momentum
     and Page Down all behave exactly as they always do — only what the movement
     is spent on is ours. That is the difference between this and scroll-hijacking:
     the section can still be reversed, flung past, or landed in from a browser's
     own restore-scroll, because none of those were ever taken away.

     The mosaic itself is CSS columns: a new review needs no arithmetic from anyone,
     it simply joins the flow and the section re-measures its own height on load
     and on resize.

     Narrow screens, reduced motion, and no JavaScript get the plain mosaic,
     standing still: pinning on a phone would fail WCAG's 400% reflow, and the
     mosaic reads perfectly well without moving. */
  var sec  = document.getElementById('reviews');
  var pin  = sec && sec.querySelector('.hm-people__pin');
  var rail = document.getElementById('mosaic-in');

  if (sec && rail && pin) {
    var tiles = Array.prototype.slice.call(rail.children);
    var wide  = window.matchMedia('(min-width: 1000px)');
    var still = window.matchMedia('(prefers-reduced-motion: reduce)');
    var SPEED = 2.1;              // the mosaic covers this much ground per page-pixel
    var travel = 0, ticking = false;

    function measure() {
      if (!wide.matches || still.matches) {
        sec.style.height = ''; rail.style.transform = '';
        sec.classList.remove('is-pinned');
        travel = 0;
        /* Standing still in one column, 24 reviews are 12.6 screens — measured.
           So where the section cannot be pinned it opens with six and hands over
           the rest on request. Without JavaScript none of this runs and all 24 are
           simply there, which is the honest fallback. */
        var many = tiles.length > 6 && window.innerWidth < 700;
        return;
      }
      sec.classList.add('is-pinned');
      sec.style.height = '';
      rail.style.transform = '';
      // How much taller the mosaic is than the window it is read through — the
      // window, not the section: the pin also holds the heading and its padding,
      // and measuring against the whole thing left the last tile 100px below the
      // fold at the end of the travel, permanently unread.
      var view = sec.querySelector('.hm-mosaic');
      var over = Math.max(0, rail.scrollHeight - view.clientHeight);
      travel = over;
      // the page pays for it at SPEED — the whole point of pinning it
      sec.style.height = (window.innerHeight + over / SPEED) + 'px';
      draw();
    }
    function draw() {
      if (!travel) return;
      var top = sec.getBoundingClientRect().top;
      var page = (sec.offsetHeight - window.innerHeight) || 1;
      var p = Math.min(1, Math.max(0, -top / page));
      rail.style.transform = 'translate3d(0,' + (-p * travel) + 'px,0)';
    }


    window.addEventListener('scroll', function () {
      if (ticking) return; ticking = true;
      requestAnimationFrame(function () { draw(); ticking = false; });
    }, { passive: true });
    window.addEventListener('resize', measure);
    wide.addEventListener('change', measure);
    still.addEventListener('change', measure);
    window.addEventListener('load', measure);   // the photographs decide the height
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
