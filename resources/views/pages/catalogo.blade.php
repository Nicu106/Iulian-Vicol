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
<link rel="stylesheet" href="{{ asset('css/foot.css') }}">
</head>
<body class="bb cat">

@php
  $euros = fn($n) => number_format($n, 0, ',', '.').' €';
  $km    = fn($n) => number_format($n, 0, ',', '.').' km';
  $words = ['','Un','Dos','Tres','Cuatro','Cinco','Seis','Siete','Ocho','Nueve','Diez','Once','Doce'];
@endphp

@include('partials.head', ['current' => 'catalogo'])

<main>
  {{-- The banner. The photograph is atmosphere, not an advert: the words on it are
       about him, not about this car, because he has never had a Panamera. Marked as
       a stock photograph in §10 of the brandbook, to be replaced by one of his own. --}}
  <header class="cat-hero">
    <img class="cat-hero__img" src="{{ asset('img/banner/panamera.jpg') }}"
         alt="" width="1800" height="1200" fetchpriority="high" decoding="async">
    <div class="cat-hero__in cat-wrap">
      <span class="cat-hero__eyebrow">Málaga · {{ $sold }} coches entregados</span>
      <h1 class="cat-hero__h"><span>{{ $words[$total] ?? $total }} coches.</span>
        <span>Cinco marcas alemanas.</span></h1>
      <p class="cat-hero__p">Sólo trabajo con estas cinco. Los he comprado y conducido yo,
         y si preguntas por uno te contesto yo.</p>
      <span class="cat-hero__credit">Foto de archivo · Unsplash</span>
    </div>
  </header>

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
            {{-- A rail's content scent is weak: nothing on screen says the row
                 continues past the edge. Shown once per visit, on the first rail
                 that has anything hidden, and only where the rail exists at all.
                 Written by the script, so it never appears without JavaScript and
                 never appears on a rail that fits. --}}
            <div class="cat-row__rail">
            <div class="cat-row__cars" id="cars-{{ $row['key'] }}"
                 style="--n:{{ $row['cars']->count() + $row['delivered']->count() + count($row['demo']) }}">
              @foreach($row['cars'] as $car)
                @include('partials.card', ['car' => $car, 'kind' => 'available'])
              @endforeach
              @foreach($row['delivered'] as $car)
                @include('partials.card', ['car' => $car, 'kind' => 'sold'])
              @endforeach
              @foreach($row['demo'] as $d)
                @include('partials.card', ['car' => $d, 'kind' => 'demo'])
              @endforeach
            </div>
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

{{-- Over 60% of car shopping happens on a phone, and the one action worth having
     always to hand belongs in the lower half of the screen, not in a header that
     scrolls away. Phone only: on a desktop the header's own button is always
     visible. --}}
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
      // Read the scroll position BEFORE the row shrinks. Closing it shortens the
      // document, and the browser clamps the scroll to the new maximum on the spot —
      // so a relative scrollBy afterwards is applied to an already-corrected position
      // and compensates twice. On a phone, where an open marque is ~3600px tall, that
      // sent the reader from 3749 to 0: back to the top of the page.
      // Anchor on what comes after the row, not on the row's own height: closing it
      // also turns its grid back into a rail, and the height difference that produces
      // is not the height difference the document sees. Measuring the next section on
      // both sides of the change gives the exact distance the page moved under the
      // reader — and reading it after the browser has clamped the scroll means the
      // clamp is already accounted for.
      var anchor = row.nextElementSibling || row.parentElement.nextElementSibling;
      var refBefore = anchor ? anchor.getBoundingClientRect().top : null;
      row.classList.remove('is-open');
      setLabel(false);
      if (compensate && refBefore !== null) {
        var refAfter = anchor.getBoundingClientRect().top;
        window.scrollTo(0, Math.max(0, window.pageYOffset + (refAfter - refBefore)));
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

  /* ---- the swipe hint ---------------------------------------------------
     Shown once per visit, on the first rail that actually has cars past the edge.
     It is built here rather than in the markup so it can never appear without
     JavaScript, and never on a rail that fits on the screen. */
  (function () {
    var SEEN = 'mc-rail-hint';
    try { if (sessionStorage.getItem(SEEN)) return; } catch (e) { /* private mode: just show it */ }

    var HAND = '<svg class="cat-hint__hand" viewBox="0 0 24 24" fill="none" aria-hidden="true">'
      + '<path d="M12 2.6a1.9 1.9 0 0 0-1.9 1.9v8.7L8.3 11.4a1.9 1.9 0 1 0-2.7 2.7l4.6 4.6c.6.6 1.4.9 2.2.9h4.4a2.9 2.9 0 0 0 2.9-2.5l.6-4.6a1.9 1.9 0 0 0-1.9-2.1h-4.5V4.5A1.9 1.9 0 0 0 12 2.6Z"'
      + ' fill="currentColor" fill-opacity=".92" stroke="rgba(0,0,0,.28)" stroke-width=".7"/></svg>';

    function railIsLive(grid) {
      return getComputedStyle(grid).display === 'flex'   // the rail, not the grid
          && grid.scrollWidth > grid.clientWidth + 24;   // and something is past the edge
    }

    var shown = false;
    function show(grid) {
      if (shown || !railIsLive(grid)) return;
      // It has to be somewhere a thumb can see. The rail can satisfy the observer
      // while its middle sits behind the dock, which is where the first attempt put
      // the hand: at y 782 on an 844 screen, under a 64px bar.
      var r = grid.getBoundingClientRect();
      var dock = document.querySelector('.cat-dock');
      var floor = window.innerHeight - (dock ? dock.getBoundingClientRect().height : 0) - 40;
      var at = r.top + r.height * 0.38;
      if (at < 80 || at > floor) return;   // not yet: the observer will offer it again
      shown = true;
      try { sessionStorage.setItem(SEEN, '1'); } catch (e) {}

      var hint = document.createElement('span');
      hint.className = 'cat-hint';
      hint.setAttribute('aria-hidden', 'true');
      hint.innerHTML = '<span class="cat-hint__disc"></span>' + HAND;
      grid.parentNode.appendChild(hint);
      // one frame later, so the animation starts from its own first keyframe
      requestAnimationFrame(function () { hint.classList.add('is-on'); });

      function done() {
        grid.removeEventListener('scroll', onScroll);
        if (hint.parentNode) hint.parentNode.removeChild(hint);
      }
      // A scroll-snap rail fires a scroll event as it settles its own layout, with
      // scrollLeft unchanged. Taking that for a gesture killed the hint on the frame
      // it was born. Only a real movement counts.
      var from = grid.scrollLeft;
      function onScroll() { if (Math.abs(grid.scrollLeft - from) > 4) done(); }
      grid.addEventListener('scroll', onScroll, { passive: true });
      grid.addEventListener('touchstart', done, { once: true, passive: true });
      hint.addEventListener('animationend', done);
      window.setTimeout(done, 4600);
    }

    var rails = document.querySelectorAll('.cat-row__cars');
    if (!('IntersectionObserver' in window)) return;
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        // wait until the row is properly on screen, and until its own colour has landed
        if (e.intersectionRatio < 0.45) return;
        window.setTimeout(function () {
          show(e.target);
          if (shown) io.disconnect();
        }, 900);
      });
    }, { threshold: [0.45, 0.75, 1] });
    Array.prototype.forEach.call(rails, function (g) { io.observe(g); });
  })();

  /* A scroller has to be reachable without a finger. Chrome only makes an
     overflow container focusable when it has no focusable children, and these
     are full of links, so say it explicitly while the rail is a rail. */
  (function () {
    function sync() {
      Array.prototype.forEach.call(document.querySelectorAll('.cat-row__cars'), function (g) {
        var rail = getComputedStyle(g).display === 'flex';
        if (rail && g.scrollWidth > g.clientWidth + 24) {
          g.setAttribute('tabindex', '0');
          g.setAttribute('role', 'group');
          g.setAttribute('aria-label', 'Coches de la marca, desplazable');
        } else {
          g.removeAttribute('tabindex');
          g.removeAttribute('role');
          g.removeAttribute('aria-label');
        }
      });
    }
    sync();
    var t; window.addEventListener('resize', function () {
      window.clearTimeout(t); t = window.setTimeout(sync, 180);
    });
  })();

  /* ---- arriving on a marque from its logo --------------------------------
     A hash lands the browser with the target flush against the top edge, which
     puts the marque's mark under the header line and the rest of the row below
     the eye. Land it in the middle instead: the whole row, with its colour and
     its cars, is what the click asked for. */
  (function () {
    var m = location.hash.match(/^#marque-([a-z-]+)$/);
    if (!m) return;
    var row = document.querySelector('.cat-row[aria-labelledby="marque-' + m[1] + '"]');
    if (!row) return;
    var land = function () { row.scrollIntoView({ block: 'center', behavior: 'auto' }); };
    // once now, and once after the browser's own jump and the images' first layout
    land(); requestAnimationFrame(land); window.setTimeout(land, 250);
  })();

  /* ---- arriving from the home page's search ------------------------------
     ?marca opens that marque to the full screen; ?modelo and ?max hide the cards
     that do not fit. Nothing is removed from the page — a car hidden here is one
     the reader can still reach by clearing the search. */
  (function () {
    var q = new URLSearchParams(location.search);
    var marca = q.get('marca'), modelo = q.get('modelo'), max = parseInt(q.get('max'), 10), pago = q.get('pago');
    if (!marca && !modelo && !max) return;
    var MONTHS = 48;
    var hid = 0;
    rows.forEach(function (row) {
      row.querySelectorAll('.mc-card').forEach(function (card) {
        var t = (card.querySelector('.mc-card__title') || {}).textContent || '';
        var p = parseInt(((card.querySelector('.mc-price') || {}).textContent || '').replace(/\D/g, ''), 10);
        var v = pago === 'mes' ? Math.round(p / MONTHS) : p;
        var out = (modelo && t.trim() !== modelo) || (max && v > max);
        if (out) { card.hidden = true; hid++; }
      });
    });
    if (marca) {
      var row = document.querySelector('.cat-row[aria-labelledby="marque-' + marca + '"]');
      var btn = row && row.querySelector('.cat-row__all');
      if (btn) window.setTimeout(function () { btn.click(); }, 700);
    }
  })();

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
