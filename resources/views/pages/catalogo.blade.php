<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, interactive-widget=resizes-content">
<meta name="robots" content="noindex, nofollow">
<title>Catálogo — IV MOTORCLASS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&display=swap">
<link rel="stylesheet" href="{{ asset('css/mc-tokens.css') }}">
<link rel="stylesheet" href="{{ asset('css/brandbook.css') }}">
<link rel="stylesheet" href="{{ asset('css/catalog.css') }}">
</head>
<body class="bb cat">

@php
  $euros = fn($n) => number_format($n, 0, ',', '.').' €';
  $km    = fn($n) => number_format($n, 0, ',', '.').' km';
  $words = ['','Un','Dos','Tres','Cuatro','Cinco','Seis','Siete','Ocho','Nueve','Diez','Once','Doce'];
@endphp

<header class="mc-head">
  <div class="mc-head__in cat-wrap" style="padding-block:0">
    <a class="mc-logo" href="/brandbook">IV&nbsp;MOTORCLASS</a>
    <nav class="mc-nav" style="margin-right:var(--s-4)">
      <a class="mc-nav__i is-current" href="/catalogo">Coches</a>
      <a class="mc-nav__i" href="/brandbook">Quién soy</a>
    </nav>
    <a class="mc-btn mc-btn--cta" href="https://wa.me/34614753187">WhatsApp</a>
  </div>
</header>

<main>
  <div class="cat-wrap cat-head">
    <span class="cat-head__count">Málaga · {{ $sold }} coches entregados</span>
    <h1>{{ $words[$total] ?? $total }} coches. Cinco marcas alemanas.</h1>
    <p>Sólo trabajo con estas cinco. Los he comprado y conducido yo, y si preguntas por
       uno te contesto yo.</p>
  </div>

  @foreach($rows as $i => $row)
      <section class="cat-row {{ $i % 2 ? 'cat-row--rtl' : '' }}"
               style="--brand:{{ $row['colour'] }}"
               aria-labelledby="marque-{{ $row['key'] }}">

        {{-- the colour is the row. It is painted behind everything and clipped from
             one edge; the inner grid is repeated inside it carrying a white wordmark, so
             the letters light up exactly as the colour reaches them. --}}
        <span class="cat-row__watermark" aria-hidden="true"
              style="--logo:url('{{ asset('img/marques/'.$row['key'].'.svg') }}')"></span>

        <div class="cat-row__fill" aria-hidden="true">
          <div class="cat-row__inner">
            <div class="cat-row__id">
              <span class="cat-row__logo" style="--logo:url('{{ asset('img/marques/'.$row['key'].'.svg') }}')"></span>
              <span class="cat-row__lead">&nbsp;</span>
            </div>
          </div>
        </div>

        <div class="cat-row__inner">
          <div class="cat-row__id">
            <h2 class="cat-row__name" id="marque-{{ $row['key'] }}">
              <span class="cat-row__logo" style="--logo:url('{{ asset('img/marques/'.$row['key'].'.svg') }}')"></span>
              <span class="mc-vh">{{ $row['name'] }}</span>
            </h2>
            <span class="cat-row__lead">
              @if($row['n'])
<span class="nw"><b>{{ $row['n'] }}</b> {{ $row['n'] === 1 ? 'disponible' : 'disponibles' }}</span> ·
                <span class="nw">desde <b>{{ $euros($row['from']) }}</b></span>@if($row['delivered']->count()) ·
                <span class="nw"><b>{{ $row['delivered']->count() }}</b> entregados</span>@endif
              @elseif($row['sold'])
                <span class="nw"><b>{{ $row['sold'] }}</b> entregados</span> · <span class="nw">ninguno ahora</span>
              @else
                Bajo pedido · ejemplo de ficha
              @endif
            </span>

            @if($row['n'] || $row['delivered']->count() || $row['demo'])
              {{-- Opens the marque to the full screen. Without JavaScript it is not
                   rendered at all, because there would be nothing for it to do. --}}
              <button class="cat-row__all" type="button" hidden
                      aria-expanded="false" aria-controls="cars-{{ $row['key'] }}"
                      data-open="{{ $row['total'] > 4 ? 'Ver los '.$row['total'] : 'Ver todos' }}"
                      data-close="Cerrar">{{ $row['total'] > 4 ? 'Ver los '.$row['total'] : 'Ver todos' }}</button>
            @endif
          </div>

          @if($row['n'] || $row['delivered']->count() || $row['demo'])
            <div class="cat-row__cars" id="cars-{{ $row['key'] }}"
                 style="--n:{{ $row['cars']->count() + $row['delivered']->count() + count($row['demo']) }}">
              @foreach($row['cars'] as $car)
                <article class="mc-card">
                  <a class="mc-card__link" href="#{{ $car->slug }}">
                    <div class="mc-frame mc-frame--card">
                      <img class="mc-img mc-img--vehicle" src="{{ $car->thumbUrl(800) }}"
                           alt="{{ $car->brand }} {{ $car->model }} {{ $car->year }}"
                           width="800" height="600" loading="lazy" decoding="async">
                    </div>
                    <div class="mc-card__body">
                      <h3 class="mc-card__title">{{ $car->model }} <span>{{ $car->year }}</span></h3>
                      <div class="mc-pair">
                        <span class="mc-price">{{ $euros($car->price) }}</span>
                        @if($car->mileage)<span class="mc-km">{{ $km($car->mileage) }}</span>
                        @else<span class="mc-km is-unknown">Km sin confirmar</span>@endif
                      </div>
                    </div>
                  </a>
                </article>
              @endforeach

              @foreach($row['delivered'] as $car)
                <article class="mc-card mc-card--sold">
                  <div class="mc-frame mc-frame--card">
                    <img class="mc-img mc-img--vehicle" src="{{ $car->thumbUrl(800) }}"
                         alt="{{ $car->brand }} {{ $car->model }} {{ $car->year }}"
                         width="800" height="600" loading="lazy" decoding="async">
                    <span class="mc-badge mc-badge--sold">Entregado</span>
                  </div>
                  <div class="mc-card__body">
                    <h3 class="mc-card__title">{{ $car->model }} <span>{{ $car->year }}</span></h3>
                    <div class="mc-pair">
                      <span class="mc-price">{{ $euros($car->price) }}</span>
                      @if($car->mileage)<span class="mc-km">{{ $km($car->mileage) }}</span>@endif
                    </div>
                  </div>
                </article>
              @endforeach

              @foreach($row['demo'] as $d)
                <article class="mc-card mc-card--demo">
                  <div class="mc-frame mc-frame--card">
                    <img class="mc-img mc-img--vehicle" src="{{ asset('storage/'.$d['img']) }}"
                         alt="Ejemplo de ficha — Porsche {{ $d['model'] }}"
                         width="800" height="600" loading="lazy" decoding="async">
                    <span class="mc-badge mc-badge--demo">Ejemplo</span>
                  </div>
                  <div class="mc-card__body">
                    <h3 class="mc-card__title">{{ $d['model'] }} <span>{{ $d['year'] }}</span></h3>
                    <div class="mc-pair">
                      <span class="mc-price">{{ $euros($d['price']) }}</span>
                      <span class="mc-km">{{ $km($d['km']) }}</span>
                    </div>
                  </div>
                </article>
              @endforeach
            </div>
          @else
            <div class="cat-row__none">
              <p>Todavía no he tenido ninguno aquí. Si buscas uno concreto, dímelo y lo busco.</p>
              <a class="mc-btn mc-btn--cta" href="https://wa.me/34614753187">Avísame</a>
            </div>
          @endif
        </div>

        @if(!$row['n'] && $row['delivered']->count())
          <p class="cat-row__note"><span>Ninguno disponible ahora mismo. Estos ya los entregué.
            <a class="mc-link" href="https://wa.me/34614753187">Avísame cuando entre uno</a>.</span></p>
        @elseif($row['demo'])
          <p class="cat-row__note"><span><b>Estas dos fichas son un ejemplo de maquetación</b>, no
            coches en venta: todavía no he tenido ningún Porsche. Fotografías de Unsplash.
            <a class="mc-link" href="https://wa.me/34614753187">Si buscas uno, dímelo</a>.</span></p>
        @endif
      </section>
    @endforeach
</main>

<script>
(function () {
  document.documentElement.className += ' js';

  var rows = Array.prototype.slice.call(document.querySelectorAll('.cat-row'));
  if (!rows.length) return;

  /* ---- opening a marque to the full screen ------------------------------
     The button exists only here: without JavaScript there is nothing for it to
     do, so it is never shown rather than shown and dead. */
  rows.forEach(function (row) {
    var btn = row.querySelector('.cat-row__all');
    if (!btn) return;
    btn.hidden = false;

    function setLabel(open) {
      btn.textContent = open ? btn.getAttribute('data-close') : btn.getAttribute('data-open');
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function close(compensate) {
      if (!row.classList.contains('is-open')) return;
      var before = row.getBoundingClientRect().height;
      row.classList.remove('is-open');
      setLabel(false);
      if (compensate) {
        // the section just lost height above the viewport, so everything below it
        // jumped up by exactly that much; take the same amount out of the scroll
        var after = row.getBoundingClientRect().height;
        window.scrollBy(0, after - before);
      }
    }

    btn.addEventListener('click', function () {
      var opening = !row.classList.contains('is-open');
      // only one marque open at a time: two full screens of colour is noise
      rows.forEach(function (r) {
        if (r !== row && r.classList.contains('is-open')) {
          r.classList.remove('is-open');
          var b = r.querySelector('.cat-row__all');
          if (b) { b.textContent = b.getAttribute('data-open'); b.setAttribute('aria-expanded', 'false'); }
        }
      });
      if (opening) {
        // Measure both ends, then animate between them. The upper edge is carried
        // to the top of the screen and stops there; the lower edge is the one that
        // travels, down to the bottom. Two edges converging on the two edges of the
        // screen is what "this is opening to fill the screen" looks like.
        var startH = row.getBoundingClientRect().height;
        row.classList.add('is-open');
        setLabel(true);
        var endH = row.getBoundingClientRect().height;

        var top = row.getBoundingClientRect().top + window.pageYOffset;
        window.scrollTo({ top: top, behavior: reduced() ? 'auto' : 'smooth' });

        if (!reduced() && endH > startH) {
          row.style.height = startH + 'px';
          row.classList.add('is-sizing');
          void row.offsetHeight;                       // commit the start height
          row.style.height = endH + 'px';
          var done = function (e) {
            if (e && e.propertyName !== 'height') return;
            row.style.height = '';                     // hand the height back to the content
            row.classList.remove('is-sizing');
            row.removeEventListener('transitionend', done);
          };
          row.addEventListener('transitionend', done);
          window.setTimeout(done, 900);                // in case the transition never fires
        }
      } else {
        close(false);
      }
    });

    // scrolling past the last car ends the marque: it returns to its band and the
    // next one follows. Only once the section is fully above the fold, so nothing
    // collapses under the reader's eyes.
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
          if (!e.isIntersecting && e.boundingClientRect.bottom < 0) close(true);
        });
      }, { threshold: 0 }).observe(row);
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    var open = document.querySelector('.cat-row.is-open');
    if (!open) return;
    var btn = open.querySelector('.cat-row__all');
    if (btn) btn.click();
  });

  function reduced() {
    return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  }

  // Each card waits for the stroke to land (640ms) plus its place in the row.
  rows.forEach(function (row) {
    var items = row.querySelectorAll('.cat-row__cars > *, .cat-row__none > *');
    Array.prototype.forEach.call(items, function (el, i) {
      el.style.setProperty('--d', (700 + i * 70) + 'ms');
      // the cards the compact band hides need their own index, counted from the
      // first hidden one, so they arrive 60ms apart when the row opens
      if (i >= 4) el.style.setProperty('--i', i - 4);
    });
  });

  function live(row, delay) {
    window.setTimeout(function () { row.classList.add('is-live'); }, delay || 0);
  }

  // A row already on screen joins the cascade; one below the fold waits until it is
  // reached, so the animation is never spent where nobody is looking.
  if (!('IntersectionObserver' in window)) {
    rows.forEach(function (r) { live(r, 0); });
    return;
  }

  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) {
      if (!e.isIntersecting) return;
      io.unobserve(e.target);
      live(e.target, 0);
    });
  }, { threshold: 0.2, rootMargin: '0px 0px -12% 0px' });

  var onScreen = 0, vh = window.innerHeight;
  rows.forEach(function (row) {
    if (row.getBoundingClientRect().top < vh * 0.9) { live(row, onScreen++ * 780); }
    else { io.observe(row); }
  });

  // Whatever happens, nothing stays hidden.
  window.setTimeout(function () {
    rows.forEach(function (r) { r.classList.add('is-live'); });
  }, 6000);
})();
</script>
</body>
</html>
