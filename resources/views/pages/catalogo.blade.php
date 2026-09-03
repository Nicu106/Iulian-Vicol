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

  <div class="cat-wrap">
    @foreach($rows as $i => $row)
      <section class="cat-row {{ $i % 2 ? 'cat-row--rtl' : '' }}"
               style="--brand:{{ $row['colour'] }}"
               aria-labelledby="marque-{{ $row['key'] }}">

        {{-- the colour is the row. It is painted behind everything and clipped from
             one edge; the inner grid is repeated inside it carrying a white wordmark, so
             the letters light up exactly as the colour reaches them. --}}
        <div class="cat-row__fill" aria-hidden="true">
          <div class="cat-row__inner">
            <div class="cat-row__id">
              <span class="cat-row__mark">{{ $row['name'] }}</span>
              {{-- the same supporting line, hidden: it is what centres the wordmark, so
                   without it the copy sits 15px above the original --}}
              <span class="cat-row__lead">&nbsp;</span>
            </div>
          </div>
        </div>

        <div class="cat-row__inner">
          <div class="cat-row__id">
            <h2 class="cat-row__mark" id="marque-{{ $row['key'] }}">{{ $row['name'] }}</h2>
            <span class="cat-row__lead">
              @if($row['n'])
                <b>{{ $row['n'] }}</b> {{ $row['n'] === 1 ? 'coche' : 'coches' }} · desde <b>{{ $euros($row['from']) }}</b>
              @elseif($row['sold'])
                <b>{{ $row['sold'] }}</b> entregados · ninguno ahora
              @else
                Bajo pedido · ejemplo de ficha
              @endif
            </span>
          </div>

          @if($row['n'] || $row['delivered']->count() || $row['demo'])
            <div class="cat-row__cars">
              @foreach($row['cars'] as $car)
                <article class="mc-card">
                  <a class="mc-card__link" href="#{{ $car->slug }}">
                    <div class="mc-frame mc-frame--card">
                      <img class="mc-img mc-img--vehicle" src="{{ $car->thumbUrl(800) }}"
                           alt="{{ $car->brand }} {{ $car->model }} {{ $car->year }}"
                           width="800" height="600" loading="lazy" decoding="async">
                    </div>
                    <div class="mc-card__body">
                      <h3 class="mc-card__title">{{ $car->model }}</h3>
                      <div class="mc-pair">
                        <span class="mc-price">{{ $euros($car->price) }}</span>
                        @if($car->mileage)<span class="mc-km">{{ $km($car->mileage) }}</span>
                        @else<span class="mc-km is-unknown">Km sin confirmar</span>@endif
                      </div>
                      <ul class="mc-chips">
                        @foreach(array_filter([$car->year, $car->fuel_es, $car->transmission_es]) as $chip)
                          <li class="mc-chip">{{ $chip }}</li>
                        @endforeach
                      </ul>
                      <span class="mc-card__go">Ver el coche <span class="mc-card__arrow">&rarr;</span></span>
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
                    <h3 class="mc-card__title">{{ $car->model }}</h3>
                    <div class="mc-pair">
                      <span class="mc-price">{{ $euros($car->price) }}</span>
                      @if($car->mileage)<span class="mc-km">{{ $km($car->mileage) }}</span>@endif
                    </div>
                    <ul class="mc-chips">
                      @foreach(array_filter([$car->year, $car->fuel_es, $car->transmission_es]) as $chip)
                        <li class="mc-chip">{{ $chip }}</li>
                      @endforeach
                    </ul>
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
                    <h3 class="mc-card__title">{{ $d['model'] }}</h3>
                    <div class="mc-pair">
                      <span class="mc-price">{{ $euros($d['price']) }}</span>
                      <span class="mc-km">{{ $km($d['km']) }}</span>
                    </div>
                    <ul class="mc-chips">
                      <li class="mc-chip">{{ $d['year'] }}</li><li class="mc-chip">{{ $d['fuel'] }}</li><li class="mc-chip">{{ $d['gear'] }}</li>
                    </ul>
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

        @if($row['delivered']->count())
          <p class="cat-row__note">Ninguno disponible ahora mismo. Estos ya los entregué.
            <a class="mc-link" href="https://wa.me/34614753187">Avísame cuando entre uno</a>.</p>
        @elseif($row['demo'])
          <p class="cat-row__note"><b>Estas dos fichas son un ejemplo de maquetación</b>, no coches
            en venta: todavía no he tenido ningún Porsche. Fotografías de Unsplash.
            <a class="mc-link" href="https://wa.me/34614753187">Si buscas uno, dímelo</a>.</p>
        @endif
      </section>
    @endforeach
  </div>
</main>

<script>
(function () {
  document.documentElement.className += ' js';

  var rows = Array.prototype.slice.call(document.querySelectorAll('.cat-row'));
  if (!rows.length) return;

  // Each card waits for the stroke to land (640ms) plus its place in the row.
  rows.forEach(function (row) {
    var items = row.querySelectorAll('.cat-row__cars > *, .cat-row__none > *');
    Array.prototype.forEach.call(items, function (el, i) {
      el.style.setProperty('--d', (700 + i * 70) + 'ms');
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
