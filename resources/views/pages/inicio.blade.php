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
  @if($reviewCount > 0)
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
      @php
        // The loop needs each printed copy to be wider than any screen, or the
        // seam shows as a gap. Two copies cover it from about 8 reviews up;
        // fewer than that are printed more times. The copies after the first
        // are hidden from assistive tech so nothing is read twice.
        $copies = max(2, (int) ceil(3600 / max(1, $reviews->sum('w'))) + 1);
      @endphp
      @foreach(range(0, $copies - 1) as $pass)
        <div class="hm-fb__row" @if($pass) aria-hidden="true" @endif>
          @foreach($reviews as $t)
            @php $src = fn ($w) => route('img.resize', ['w' => $w]) . '?p=' . urlencode($t->img); @endphp
            <figure class="fb" style="--w:{{ $t->w }}px; --ratio:{{ $t->ratio }}; --scale:{{ $t->scale }}">
              <div class="fb__flip">
                <div class="fb__face fb__face--front">
                  <img src="{{ $src(600) }}"
                       srcset="{{ $src(400) }} 400w, {{ $src(600) }} 600w, {{ $src(900) }} 900w"
                       sizes="(min-width:1000px) 420px, 80vw"
                       alt="{{ $pass ? '' : $t->name . ', con su coche' }}"
                       loading="{{ !$pass && $loop->index < 8 ? 'eager' : 'lazy' }}" decoding="async">
                </div>
                <div class="fb__face fb__face--back">
                  <blockquote class="fb__q">{{ $t->quote }}</blockquote>
                  <figcaption class="fb__by">{{ $t->name }}</figcaption>
                </div>
              </div>
            </figure>
          @endforeach
        </div>
      @endforeach
    </div>

    {{-- Two, and nothing else. Each carries the next card across the middle,
         which is where a card turns over. --}}
    <div class="hm-fb__step">
      <button class="hm-fb__nav" type="button" data-go="-1" aria-label="Anterior">
        <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false">
          <path d="M15 4 7 12l8 8" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/></svg>
      </button>
      <button class="hm-fb__nav" type="button" data-go="1" aria-label="Siguiente">
        <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false">
          <path d="M9 4l8 8-8 8" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/></svg>
      </button>
    </div>
  </section>
  @endif

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
  /* ---- the row -----------------------------------------------------------
     It travels left to right on its own, for ever. Each card is a photograph
     through the middle of the row — that is what slows down there and what
     draws the eye — and turns over as it leaves, becoming what that person
     wrote. A press on a button carries the centred picture across, turns it,
     and brings the next one in.

     The speed is not constant, and that is the point. The row arrives fast, so
     that anyone scrolling past sees it moving; then it drops to a crawl
     whenever a card is sitting in the middle, so there is time to read the one
     that has just turned over. A detent, not a stop: a hard stop is the tell of
     a widget, and every ticker that publishes its numbers eases a multiplier
     instead.

     Two buttons carry the next card across the middle. Nothing else.

     It is the browser's own scroller underneath, so a trackpad, a swipe and the
     arrow keys all work for free. Positions are cached, so a frame reads no
     layout: 48 cards, and the only per-frame work is arithmetic and a custom
     property. dt-normalised, so 120Hz does not run it at double speed. Under
     prefers-reduced-motion it never moves and every card shows its words. */
  var rail = document.getElementById('fb-rail');
  if (rail) {
    var SPEED  = 110;    // px/s between cards
    var BOOST  = 2.2;    // ...and how much faster it arrives
    var CRAWL  = 0.22;   // what it slows to with a card in the middle — still visibly moving,
                         //   because at a tenth it read as stopped and the row looked dead
    var DETENT = 120;    // how near the middle a card has to be for that
    var FLIP   = 70;     // half the distance a card turns over in
    var LEAD   = 130;    // ...starting this far PAST the middle, once the slow zone is behind it
    var TAU    = 150;    // ms, the damping time constant
    var GLIDE  = 780;    // ms for a button press to land the next card

    var still  = window.matchMedia('(prefers-reduced-motion: reduce)');
    // On a phone one card fits, so a row that drifts and turns cards as they
    // LEAVE the middle turns them off screen. There the section runs a stepped
    // sequence instead: the photograph sits in the middle, turns over into its
    // words, holds long enough to read them, and the next photograph comes in.
    // "Next" advances that same sequence one step — turn, then advance, then
    // turn — and a swipe only chooses where the sequence carries on from.
    var narrow  = window.matchMedia('(max-width: 999px)');
    var reading = null;      // the card currently showing its words, on a phone
    var phase   = 'photo', since = 0, swipedAt = 0, held = false;
    var HOLD_PHOTO = 2200;   // ms to look at the picture
    var holdText = function (k) {           // ms to read: by length, within reason
      var n = k.el.querySelector('.fb__q').textContent.length;
      return Math.max(4000, Math.min(14000, n * 55));
    };

    var halt  = document.getElementById('fb-halt');
    var sec   = document.getElementById('reviews');
    var row   = rail.firstElementChild;

    var kids = [], rowW = 0, mid = 0;
    var pos = 0, v = 0, last = 0, raf = 0, mine = 0, warm = 0;
    var over = false, keyed = false, stopped = false, busy = 0, glide = null;

    function measure() {
      rowW = row.scrollWidth;
      mid  = rail.clientWidth / 2;
      kids = Array.prototype.map.call(rail.querySelectorAll('.fb'), function (el) {
        return { el: el, c: el.offsetLeft + el.offsetWidth / 2, flip: -1 };
      });
      pos = rail.scrollLeft;
    }

    var clamp01 = function (x) { return x < 0 ? 0 : x > 1 ? 1 : x; };
    var smooth  = function (x) { x = clamp01(x); return x * x * (3 - 2 * x); };

    function paint() {
      var near = Infinity;
      for (var i = 0; i < kids.length; i++) {
        var d = kids[i].c - pos - mid;
        if (d > -rowW / 2 - 1200 && d < rowW / 2 + 1200) {
          var a = d < 0 ? -d : d;
          if (a < near) { near = a; }
        }
        // Wide: the middle is for the PHOTOGRAPH — that is what slows down there
        // and what draws the eye — and the card turns over as it LEAVES, once
        // it is past the slow zone and picking up speed. Turning it exactly at
        // the middle put the 90-degree moment, a card of zero width, at the
        // slowest point of the row: a hole where a review should be.
        // Phone: nothing turns by position; only the card the sequence has
        // turned.
        var f = narrow.matches ? 0 : Math.round(smooth((d - LEAD) / (2 * FLIP)) * 180);
        if (kids[i] === reading) { f = 180; }
        if (f !== kids[i].flip) { kids[i].flip = f; kids[i].el.style.setProperty('--flip', f + 'deg'); }
      }
      return near;
    }

    function centredKid() {
      var best = null, bd = Infinity;
      for (var i = 0; i < kids.length; i++) {
        var a = Math.abs(kids[i].c - pos - mid);
        if (a < bd) { bd = a; best = kids[i]; }
      }
      return best;
    }
    function glideTo(kid) {
      // The card may be in the other printed copy, so normalise into one row's
      // worth and take the shorter way round — without this a step to the next
      // card once travelled 10,546px the wrong way.
      var to = (((kid.c - mid) % rowW) + rowW) % rowW;
      var d  = to - pos;
      if (d >  rowW / 2) { d -= rowW; }
      if (d < -rowW / 2) { d += rowW; }
      glide = { from: pos, to: pos + d, t0: performance.now() };
      v = 0;
    }
    function neighbour(kid, dir) {
      var i = kids.indexOf(kid);
      return kids[(i + dir + kids.length) % kids.length];
    }

    /* ---- the phone's sequence: photograph, turn, hold, next ---- */
    function turn(k)   { reading = k; phase = 'text';  since = performance.now(); paint(); }
    function unturn()  { reading = null; phase = 'photo'; since = performance.now(); paint(); }
    function advance(dir) { reading = null; phase = 'photo'; glideTo(neighbour(centredKid(), dir)); }
    function phoneFrame(now) {
      if (glide) {
        var t = clamp01((now - glide.t0) / GLIDE);
        var e = 1 - Math.pow(1 - t, 3);
        pos = glide.from + (glide.to - glide.from) * e;
        if (t >= 1) { glide = null; phase = reading ? 'text' : 'photo'; since = now; }
        if (pos < 0) { pos += rowW; } else if (pos >= rowW) { pos -= rowW; }
        rail.scrollLeft = pos; mine = now; paint();
        return;
      }
      // a swipe settles: seat the nearest card and carry on from it
      if (swipedAt && !held && now - swipedAt > 160) {
        swipedAt = 0; pos = rail.scrollLeft; reading = null; phase = 'photo';
        glideTo(centredKid());
        return;
      }
      // the sequence holds while a finger is down, while it is being read
      // with a keyboard, when stopped, and never runs under reduced motion
      if (still.matches || stopped || keyed || held || swipedAt) { since = now; return; }
      var k = centredKid();
      if (!k) { return; }
      if (phase === 'photo' && now - since > HOLD_PHOTO) { turn(k); return; }
      if (phase === 'text'  && now - since > holdText(k)) { advance(1); }
    }

    function frame(now) {
      raf = requestAnimationFrame(frame);
      var dt = last ? Math.min(64, now - last) : 16;
      last = now;
      if (!rowW) { measure(); return; }
      if (narrow.matches) { phoneFrame(now); return; }

      if (glide) {
        var t = clamp01((now - glide.t0) / GLIDE);
        var e = 1 - Math.pow(1 - t, 3);
        pos = glide.from + (glide.to - glide.from) * e;
        // land at rest. Left alone, the velocity from before the press kept
        // integrating for a few frames after the glide finished and the card
        // came to rest 14-16px off the middle it had just been carried to.
        if (t >= 1) { glide = null; v = 0; busy = now + 700; }
      } else {
        var near = paint();
        // fast on arrival, a crawl with a card in the middle, and hovering or
        // reading with a keyboard slows it further still
        var boost = warm ? 1 + (BOOST - 1) * clamp01((warm - now) / 1500) : 1;
        var want  = (still.matches || stopped || keyed || now < busy) ? 0
                  : SPEED * boost * (CRAWL + (1 - CRAWL) * smooth(near / DETENT))
                          * (over ? 0.35 : 1);
        v += (want - v) * (1 - Math.exp(-dt / TAU));
        if (v <= 0.02) { pos = rail.scrollLeft; paint(); return; }
        if (Math.abs(pos - rail.scrollLeft) > 2) { pos = rail.scrollLeft; }
        pos -= v * dt / 1000;                     // left to right
      }

      if (pos < 0) { pos += rowW; } else if (pos >= rowW) { pos -= rowW; }
      rail.scrollLeft = pos;
      mine = now;
      paint();
    }

    /* ---- the two buttons ---- */
    function step(dir) {
      if (!kids.length) { return; }
      if (narrow.matches) {
        // next: turn, then advance, then turn. previous: the same, backwards.
        // A turn reseats the card first, so the few pixels a finger dragged it
        // are given back while it turns.
        var k = centredKid();
        if (dir > 0) { if (phase === 'photo') { glideTo(k); turn(k); } else { advance(1); } }
        else         { if (phase === 'text')  { glideTo(k); unturn(); } else { advance(-1); } }
        return;
      }
      // measured from where a glide is GOING, so a second press during the first
      // steps two cards rather than re-targeting the one already on its way
      var at = glide ? glide.to : pos, best = 0, bd = Infinity;
      for (var i = 0; i < kids.length; i++) {
        var a = Math.abs(kids[i].c - at - mid);
        if (a < bd) { bd = a; best = i; }
      }
      glideTo(kids[(best + dir + kids.length) % kids.length]);
    }
    document.querySelectorAll('.hm-fb__step .hm-fb__nav').forEach(function (b) {
      b.addEventListener('click', function () { step(+b.getAttribute('data-go')); });
    });
    // A photograph you can see is a review you might want: click it and it
    // comes to the middle. On a phone the centred one turns instead. The press
    // has to travel less than 6px, so a drag is still a drag.
    var pressX = 0;
    rail.addEventListener('pointerdown', function (e) { pressX = e.clientX; });
    rail.addEventListener('click', function (e) {
      if (Math.abs(e.clientX - pressX) > 6) { return; }
      var card = e.target.closest ? e.target.closest('.fb') : null;
      if (!card || !kids.length) { return; }
      for (var i = 0; i < kids.length; i++) {
        if (kids[i].el !== card) { continue; }
        if (narrow.matches) { kids[i] === centredKid() ? step(1) : (reading = null, phase = 'photo', glideTo(kids[i])); }
        else { glideTo(kids[i]); }
        break;
      }
    });

    /* ---- and everything that is theirs ---- */
    function yieldNow() { busy = performance.now() + 1200; }
    ['wheel', 'keydown'].forEach(function (e) { rail.addEventListener(e, yieldNow, { passive: true }); });
    // On a phone a horizontal swipe is not a scroll, it is the gesture: a step
    // of the sequence, forwards or backwards. Left alone, a swipe scrolled the
    // row and landed on a photograph, which then waited its 2.2s to turn — so
    // anyone swiping through saw pictures and never a word. touch-action pan-y
    // on the rail keeps the browser from panning it; the card follows the
    // finger a little for feel, and the step happens when the finger lifts.
    var tx = 0, ty = 0, tPos = 0, tAxis = 0;
    rail.addEventListener('touchstart', function (e) {
      held = true; yieldNow();
      // A finger takes over from any glide still running — it jumps to where
      // the glide was going and the swipe starts from there. Left alone, a
      // swipe that began during the reseat after a press was ignored outright:
      // touchmove bailed on the glide, no axis was ever detected, and lifting
      // the finger did nothing.
      if (narrow.matches && glide) {
        pos = (((glide.to % rowW) + rowW) % rowW); glide = null;
        phase = reading ? 'text' : 'photo'; rail.scrollLeft = pos; mine = performance.now();
      }
      var t = e.touches[0]; tx = t.clientX; ty = t.clientY; tPos = pos; tAxis = 0;
    }, { passive: true });
    rail.addEventListener('touchmove', function (e) {
      if (!narrow.matches) { return; }
      var t = e.touches[0], dx = t.clientX - tx, dy = t.clientY - ty;
      if (!tAxis && (Math.abs(dx) > 8 || Math.abs(dy) > 8)) { tAxis = Math.abs(dx) > Math.abs(dy) ? 1 : 2; }
      if (tAxis !== 1) { return; }
      pos = tPos + Math.max(-40, Math.min(40, -dx * 0.25));
      rail.scrollLeft = pos; mine = performance.now();
    }, { passive: true });
    ['touchend', 'touchcancel'].forEach(function (n) {
      rail.addEventListener(n, function (e) {
        held = false;
        if (!narrow.matches) { if (!swipedAt) { swipedAt = performance.now(); } return; }
        var t = e.changedTouches && e.changedTouches[0];
        var dx = t ? t.clientX - tx : 0;
        if (tAxis === 1 && Math.abs(dx) > 40) { step(dx < 0 ? 1 : -1); }
        else if (Math.abs(pos - tPos) > 0.5) { glideTo(centredKid()); }   // gave up: seat it again
        tAxis = 0;
      }, { passive: true });
    });
    rail.addEventListener('scroll', function () {
      // A scroll we did not cause is the reader's. Wide, the drift steps aside
      // for it; on a phone it is a swipe, and the sequence resumes from wherever
      // it settles.
      if (!glide && performance.now() - mine > 120) {
        yieldNow(); pos = rail.scrollLeft;
        if (narrow.matches && !held) { swipedAt = performance.now(); reading = null; paint(); }
      }
    }, { passive: true });

    sec.addEventListener('pointerenter', function () { over = true; });
    sec.addEventListener('pointerleave', function () { over = false; });
    // Focus stops the row only when it is a KEYBOARD's focus. A mouse click on
    // one of the two buttons also focuses it, and that used to park the row for
    // good. The two buttons never count: pressing one is asking for motion.
    sec.addEventListener('focusin',  function (e) {
      if (e.target && e.target.closest && e.target.closest('.hm-fb__step')) { keyed = false; return; }
      keyed = !!(e.target && e.target.matches && e.target.matches(':focus-visible'));
    });
    sec.addEventListener('focusout', function () {
      if (!sec.contains(document.activeElement)) { keyed = false; }
    });

    if (halt) {
      halt.hidden = false;
      halt.setAttribute('aria-pressed', 'false');
      halt.addEventListener('click', function () {
        stopped = !stopped;
        halt.textContent = stopped ? 'Reanudar el movimiento' : 'Detener el movimiento';
        halt.setAttribute('aria-pressed', stopped ? 'true' : 'false');
      });
    }

    // Under prefers-reduced-motion nothing moves and nothing turns over — so
    // the words would be stuck on the back of every card. The section goes
    // static instead: photograph above, words below, all of it simply there.
    function calm() { sec.classList.toggle('is-static', still.matches); }
    still.addEventListener('change', calm); calm();
    // crossing the breakpoint: forget any turned card and, on a phone, seat one
    narrow.addEventListener('change', function () {
      reading = null; phase = 'photo'; measure();
      if (narrow.matches && kids.length) { glideTo(centredKid()); }
      paint();
    });

    window.addEventListener('resize', measure);
    window.addEventListener('load', function () { measure(); if (narrow.matches && kids.length && !glide) { glideTo(centredKid()); } });
    document.addEventListener('visibilitychange', function () {
      if (document.hidden) { cancelAnimationFrame(raf); raf = 0; last = 0; }
      else if (!raf) { raf = requestAnimationFrame(frame); }
    });
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (es) {
        if (es[0].isIntersecting) {
          if (!raf) { last = 0; warm = performance.now() + 1500; since = performance.now(); raf = requestAnimationFrame(frame); }
        } else { cancelAnimationFrame(raf); raf = 0; }
      }, { threshold: 0 }).observe(sec);
    } else {
      raf = requestAnimationFrame(frame);
    }
    measure();
    // a phone starts with the first photograph seated in the middle
    if (narrow.matches && kids.length) { glideTo(kids[0]); }
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
