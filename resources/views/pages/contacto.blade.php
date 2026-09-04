<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, interactive-widget=resizes-content">
<meta name="robots" content="noindex, nofollow">
<title>Contacto — IV MOTORCLASS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&display=swap">
<link rel="stylesheet" href="{{ asset('css/mc-tokens.css') }}">
<link rel="stylesheet" href="{{ asset('css/brandbook.css') }}">
<link rel="stylesheet" href="{{ asset('css/catalog.css') }}">
<link rel="stylesheet" href="{{ asset('css/foot.css') }}">
<link rel="stylesheet" href="{{ asset('css/contact.css') }}">
</head>
<body class="bb cat">

@include('partials.head', ['current' => 'contacto'])

<main class="ct">

  {{-- ==================================================================
       The opening: the picture becomes the two panels below it

       One car filling the screen. Scrolling slices it into twelve columns; the
       photograph drains out of them left to right and leaves twelve WHITE
       strips standing on the pale band the next section is printed on. The
       strips then part, descend, and regroup — six on the left, six on the
       right — onto the exact rectangles of the two panels that carry the
       distance story. They are not animated towards a guess: every frame reads
       the real panels' position and aims at it, so the last frame of the
       animation and the first frame of the content are the same two shapes.

       The words sit lower-right, where a sliding-block scan of this photograph
       put the best position for white type: 13.62:1 at its lightest pixel,
       against 1.43:1 lower-left. No text-shadow — a line that needs one is in
       the wrong place.
       ================================================================== --}}
  <div class="ct-assemble" id="assemble">

    <div class="ct-open" id="open">
      <div class="ct-open__cut" id="cut" aria-hidden="true"></div>
      <div class="ct-open__cols" id="cols" style="--hero:url('{{ $hero }}')"></div>

      <img class="ct-open__plain" src="{{ $hero }}" width="2400" height="3200"
           alt="Un Porsche Cayman con matrícula alemana" fetchpriority="high" decoding="async">

      <div class="ct-open__words" id="open-words">
        <p class="ct-hero__kicker">Málaga · {{ $sold }} coches entregados</p>
        <h1 class="ct-h1">Escríbeme.<br>Contesto yo.</h1>
        <p class="ct-lead">No hay centralita ni formulario esperando a que alguien lo mire.</p>
      </div>
    </div>

    {{-- the scroll the strips fall through, before the panels arrive --}}
    <div class="ct-open__run" id="run" aria-hidden="true"></div>

    {{-- ==================================================================
         What the strips become: the two things somebody on a contact page
         actually came for. The details on the left, where he is on the right.

         Every item here is the one the live site carries — phone, WhatsApp,
         email, Málaga, "abierto cada día, consultar disponibilidad" — nothing
         invented and nothing dropped.

         The map is Google's, embedded, and it is covered by a shield until it is
         asked for. An embedded map that is live from the first frame eats the
         wheel the moment the pointer crosses it, and the pointer crosses it in
         the middle of a scroll animation. One click hands it over.
         ================================================================== --}}
    <section class="ct-reach" id="reach" aria-labelledby="reach-h">
      <div class="cat-wrap ct-reach__in">

        <div class="ct-panel ct-reach__card" id="panel-a">
          <h2 class="ct-h2" id="reach-h">Escríbeme</h2>
          <p class="ct-reach__lead">Contesto yo. No hay centralita, ni un formulario
            esperando a que alguien lo mire mañana.</p>

          <dl class="ct-reach__list">
            <div class="ct-reach__row">
              <dt>WhatsApp</dt>
              <dd><a href="https://wa.me/{{ $phoneRaw }}">{{ $phone }}</a>
                  <span>Lo leo en minutos. Te mando vídeo del coche, incluido lo que no está perfecto.</span></dd>
            </div>
            <div class="ct-reach__row">
              <dt>Teléfono</dt>
              <dd><a href="tel:+{{ $phoneRaw }}">{{ $phone }}</a>
                  <span>Si estoy con un cliente, insiste o escríbeme.</span></dd>
            </div>
            <div class="ct-reach__row">
              <dt>Email</dt>
              <dd><a href="mailto:{{ $email }}">{{ $email }}</a>
                  <span>Para documentación y facturas.</span></dd>
            </div>
            <div class="ct-reach__row">
              <dt>Dónde</dt>
              <dd><b>Málaga, España</b>
                  <span>Trabajo con cita: te digo el punto exacto cuando quedemos.</span></dd>
            </div>
            <div class="ct-reach__row">
              <dt>Horario</dt>
              <dd><b>Abierto cada día</b>
                  <span>Consultar disponibilidad. <a class="mc-link" href="#ct-hours-h">Ver el detalle</a></span></dd>
            </div>
          </dl>

          <a class="mc-btn mc-btn--cta ct-reach__cta"
             href="https://wa.me/{{ $phoneRaw }}?text={{ urlencode('Hola, te escribo desde la web.') }}">Abrir WhatsApp</a>
        </div>

        <figure class="ct-panel ct-gmap" id="panel-b">
          <div class="ct-gmap__frame">
            <iframe class="ct-gmap__f" title="Mapa de Málaga, España"
                    src="https://maps.google.com/maps?q={{ urlencode('Málaga, España') }}&z=11&hl=es&output=embed"
                    loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                    allowfullscreen></iframe>
            <button class="ct-gmap__shield" type="button" id="gmap-on">
              <span>Activar el mapa</span>
            </button>
          </div>
          <figcaption class="ct-gmap__bar">
            <span class="ct-gmap__where">Málaga, España</span>
            <a class="mc-link" href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode('Málaga, España') }}"
               target="_blank" rel="noopener">Abrir en Google Maps</a>
          </figcaption>
        </figure>

      </div>
    </section>
  </div>

  {{-- ==================================================================
       A 720 km de aquí

       He has no showroom, so a pin on a map answers a question nobody asked.
       What somebody looking at a car six hundred kilometres away actually wants
       to know is whether they can trust it unseen — and three people already did,
       in their own words. Only cities the review TEXTS name are used: the
       author_location column says Santander for 19 of 25 and contradicts its own
       quotes, so it is not trusted here.
       ================================================================== --}}
  <section class="ct-far" id="far" aria-labelledby="far-h">
    <div class="cat-wrap ct-far__in">
      <h2 class="ct-h2" id="far-h">A {{ number_format(max(array_column($from,'km')),0,',','.') }} km de aquí</h2>
      <p class="ct-far__lead">Nadie compra un coche a setecientos kilómetros por una web.
        Lo compran porque antes hablaron con alguien. Estos condujeron hasta Málaga:</p>

      <ol class="ct-far__list">
        @foreach($from as $i => $f)
          <li class="ct-far__item" data-i="{{ $i }}">
            <span class="ct-far__km">{{ number_format($f['km'],0,',','.') }}<i>km</i></span>
            <span class="ct-far__who"><b>{{ $f['who'] }}</b>, desde {{ $f['city'] }}</span>
            <span class="ct-far__said">{{ $f['said'] }}</span>
          </li>
        @endforeach
      </ol>

      <p class="ct-far__note">Y {{ $delivered['who'] }} no se movió de casa:
        «{{ $delivered['said'] }}»</p>

      <a class="mc-btn mc-btn--cta ct-far__cta"
         href="https://wa.me/{{ $phoneRaw }}?text={{ urlencode('Hola, estoy lejos de Málaga. ¿Cómo lo hacemos?') }}">Estoy lejos — escríbeme</a>
    </div>
  </section>

  {{-- ---- the hours, on their own ---------------------------------------- --}}
  <section class="ct-hours" aria-labelledby="ct-hours-h">
    <div class="cat-wrap ct-hours__in">
      <div>
        <h2 class="ct-h2" id="ct-hours-h">Cuándo</h2>
        <p class="ct-hours__big">Todos los días,<br><b>a convenir</b></p>
      </div>
      <ul class="ct-hours__list">
        <li><span>Lunes a viernes</span><b>Cuando te venga bien</b></li>
        <li><span>Sábado</span><b>Cuando te venga bien</b></li>
        <li><span>Domingo</span><b>Escríbeme y lo miramos</b></li>
      </ul>
      <p class="ct-hours__note">No tengo horario de oficina ni una tienda a la que
        presentarse: trabajo con cita, y te digo el punto exacto de Málaga cuando
        quedemos — depende de dónde tenga el coche que quieres ver.</p>
    </div>
  </section>

  {{-- ---- the faces, so the map is not the only picture ------------------- --}}
  <section class="ct-faces" aria-label="Clientes">
    <div class="cat-wrap">
      <p class="ct-faces__lead"><b>{{ $people }} personas</b> se hicieron la foto con el coche
        que se llevaron. <a class="mc-link" href="/inicio#reviews">Verlas todas →</a></p>
      <ul class="ct-faces__row">
        @foreach($faces as $f)
          <li><img src="{{ $f->image_path }}" alt="{{ $f->author_name }} con su coche"
                   loading="lazy" decoding="async" width="400" height="500"></li>
        @endforeach
      </ul>
    </div>
  </section>

  {{-- ---- the slow way, last ----------------------------------------------- --}}
  <section class="ct-formband" aria-labelledby="ct-form-h">
    <div class="cat-wrap ct-form-sec">
    <h2 class="ct-h2" id="ct-form-h">O déjalo escrito aquí</h2>
    <p class="ct-sec__p">Tres campos. Al enviar, eliges si te abro WhatsApp o el correo
      — en los dos casos el mensaje va ya redactado y lo puedes leer antes de mandarlo.</p>

    <form class="ct-form" id="ct-form">
      <label class="ct-f">
        <span>Tu nombre</span>
        <input class="mc-input" type="text" id="f-name" name="name" autocomplete="name" required>
      </label>
      <label class="ct-f">
        <span>Tu teléfono <em>(opcional)</em></span>
        <input class="mc-input" type="tel" id="f-tel" name="phone" autocomplete="tel" inputmode="tel">
      </label>
      <label class="ct-f ct-f--wide">
        <span>Qué necesitas</span>
        <textarea class="mc-input" id="f-msg" name="message" rows="4" required
                  placeholder="Un coche concreto, una prueba, financiación…"></textarea>
      </label>
      <div class="ct-form__go">
        <button class="mc-btn mc-btn--cta" type="submit" value="wa" name="via">Enviar por WhatsApp</button>
        <button class="mc-btn mc-btn--ghost" type="submit" value="mail" name="via">Enviar por email</button>
      </div>
      <p class="ct-form__note">No guardo nada en esta web: el mensaje se escribe en tu
        WhatsApp o en tu correo y lo envías tú. Así no hay datos míos que proteger ni
        casilla que marcar.</p>
    </form>
  </section>

  {{-- ---- instead of "¿por qué elegirnos?" ---------------------------------
       The old page answered that with four claims — verified vehicles, extended
       warranty, flexible finance, and 24/7 support. Counts he can stand behind
       answer it better, and the last of those four is not carried over: see the
       brandbook's sign-off table. --}}
  <section class="ct-sec ct-facts cat-wrap" aria-label="En números">
    <dl>
      <div><dt>Coches entregados</dt><dd>{{ $sold }}</dd></div>
      <div><dt>Personas fotografiadas con el suyo</dt><dd>{{ $people }}</dd></div>
      <div><dt>Disponibles ahora</dt><dd>{{ $available }}</dd></div>
      <div><dt>Marcas</dt><dd>5</dd></div>
    </dl>
    <p class="ct-sec__p">Las cinco alemanas, y sólo esas.
      <a class="mc-link" href="/catalogo">Ver el catálogo →</a></p>
  </section>
</main>

@include('partials.foot')

<div class="cat-dock" role="complementary" aria-label="Contacto">
  <div class="mc-bar">
    <span class="cat-dock__t">¿Hablamos?<b>Contesto yo</b></span>
    <span class="mc-bar__act">
      <a class="mc-btn mc-btn--cta" href="https://wa.me/{{ $phoneRaw }}">WhatsApp</a>
      <a class="mc-btn mc-btn--ghost" href="tel:+{{ $phoneRaw }}" aria-label="Llamar">Tel</a>
    </span>
  </div>
</div>

<script>
(function () {
  document.documentElement.className += ' js';

  /* ---- the picture becomes the two panels -------------------------------
     Twelve strips carry one photograph between them — size and position are
     computed from the picture's own proportions, because a percentage pair on
     background-size forces both axes and would stretch it.

     frame by frame
       0.00        the car, whole, with the words on it
       0.02-0.09   the cuts are drawn — before this the picture is untouched
       0.05-0.26   the strips part; the deep shows through the cuts; the words go
       0.10-0.40   the photograph drains out of them left to right — the picture
                   is being milled, not hidden
       0.40-0.88   each strip walks to its place in one of the two panels. Six
                   left, six right. It is not walking towards a guess: the two
                   panels are measured every frame and the strips aim at where
                   they actually are, so the target moves with the page and they
                   meet it exactly.
       0.72-0.88   the five seams inside each group fade; the two outer hairlines
                   stay and become the panel's own edges
       0.90-1.00   the stage is dismissed. The strips and the panels are the same
                   two rectangles by then, so what is revealed is what was there.

     The walk is LINEAR in scroll. That is not laziness: easing a scrubbed
     transform is the one thing that makes scroll position and visual position
     stop agreeing, and GreenSock states it as a rule for exactly this case. The
     easing lives where it belongs — in the per-strip stagger (0.035 of the
     window each, the scroll equivalent of the 50-80ms interval every studio
     that publishes its numbers uses) and in the opacity curves, which are paint
     and cannot desynchronise from anything.

     Transform and opacity only, so it stays on the compositor, and the scroll is
     never intercepted: the stage is sticky, the page below it is real, and page
     distance becomes animation distance.

     Below 900px, with reduced motion, or without JavaScript no strips are built
     at all — the photograph is simply there, at its own size, the panels are
     simply there, and the page carries on. The picture is content; the milling
     is decoration. ==================================================== */
  var assemble = document.getElementById('assemble');
  var open     = document.getElementById('open');
  var host     = document.getElementById('cols');
  var runEl    = document.getElementById('run');
  var words    = document.getElementById('open-words');
  var cut      = document.getElementById('cut');
  var panelA   = document.getElementById('panel-a');
  var panelB   = document.getElementById('panel-b');
  var live     = false;

  var clamp = function (v) { return v < 0 ? 0 : v > 1 ? 1 : v; };
  var span  = function (p, a, b) { return clamp((p - a) / (b - a)); };

  if (assemble && open && host && panelA && panelB) {
    var N = 12, M = 6, STEP = 0.035;
    var wide  = window.matchMedia('(min-width: 900px)');
    var still = window.matchMedia('(prefers-reduced-motion: reduce)');
    var kids = [], ticking = false, IMG = { w: 0, h: 0 }, RUN = 1;

    var probe = new Image();
    probe.onload = function () { IMG.w = probe.naturalWidth; IMG.h = probe.naturalHeight; measure(); };
    probe.src = @json($hero);

    function build() {
      host.innerHTML = ''; kids = [];
      for (var i = 0; i < N; i++) {
        var col = document.createElement('span'); col.className = 'ct-col';
        var pic = document.createElement('span'); pic.className = 'ct-col__pic';
        var l   = document.createElement('i');    l.className   = 'ct-col__edge ct-col__edge--l';
        var r   = document.createElement('i');    r.className   = 'ct-col__edge ct-col__edge--r';
        var lf  = document.createElement('i');    lf.className  = 'ct-col__lift';
        col.appendChild(lf); col.appendChild(pic); col.appendChild(l); col.appendChild(r);
        host.appendChild(col);
        kids.push({ col: col, pic: pic, l: l, r: r, lift: lf });
      }
    }

    /* everything that only changes when the window does */
    function frame() {
      if (!IMG.w || !kids.length) return;
      var vw = window.innerWidth, vh = open.clientHeight || window.innerHeight;
      var scale = Math.max(vw / IMG.w, vh / IMG.h);       // cover, computed
      var dw = IMG.w * scale, dh = IMG.h * scale;
      var ox = (vw - dw) / 2, oy = (vh - dh) * 0.52;
      var colw = vw / N;
      for (var i = 0; i < N; i++) {
        kids[i].col.style.left  = (i * colw) + 'px';
        kids[i].col.style.width = (colw + 0.6) + 'px';    // 0.6px of overlap, so six
        kids[i].pic.style.backgroundSize = dw + 'px ' + dh + 'px';   // strips close into one
        kids[i].pic.style.backgroundPosition = (ox - i * colw) + 'px ' + oy + 'px';
      }
    }

    function measure() {
      if (!wide.matches || still.matches || !IMG.w) {
        assemble.classList.remove('is-live'); live = false;
        open.style.opacity = ''; open.style.visibility = '';
        if (runEl) runEl.style.height = '';
        return;
      }
      if (!kids.length) build();
      assemble.classList.add('is-live'); live = true;
      var vh = window.innerHeight;
      // 0.9 of a screen for the strips to travel through before the panels are
      // where they need to be. Longer and the empty stage outstays its welcome;
      // shorter and twelve strips have to cross the screen in a flick.
      runEl.style.height = Math.round(vh * 0.9) + 'px';
      frame();
      // The scroll at which the panels sit 24% down the screen is the scroll at
      // which the strips must be home. Everything else is a fraction of it, so
      // the timing survives any content height, any viewport, any font.
      var top = panelA.getBoundingClientRect().top - assemble.getBoundingClientRect().top;
      RUN = Math.max(1, (top - vh * 0.24) / 0.88);
      draw();
    }

    function draw() {
      if (!live) return;
      var s  = -assemble.getBoundingClientRect().top;
      var p  = clamp(s / RUN);
      var vw = window.innerWidth, vh = open.clientHeight || window.innerHeight;
      var colw = vw / N;

      // read both targets before writing anything
      var o  = open.getBoundingClientRect();
      var rA = panelA.getBoundingClientRect(), rB = panelB.getBoundingClientRect();
      var T  = [
        { x: rA.left - o.left, y: rA.top - o.top, w: rA.width, h: rA.height },
        { x: rB.left - o.left, y: rB.top - o.top, w: rB.width, h: rB.height }
      ];

      if (words) {
        words.style.opacity = String(1 - span(p, 0.03, 0.15));
        // The stage is sticky at top:0, but the header above it is not: at rest
        // its last header-height of pixels are below the fold, and the words were
        // the thing in them. Anchored to the viewport rather than to the stage,
        // they are in the right place at rest, stuck, and everywhere between.
        var over = o.bottom - window.innerHeight;
        words.style.bottom = (over > 0 ? over : 0) + 'px';
      }
      // What the cuts open onto. Twelve white strips on a pale band read at
      // 1.08:1 — the milling was happening and almost nothing showed it. The
      // seams open onto the deep the rest of the site is built on, and it is
      // gone again before the strips have descended far enough for it to be a
      // dark screen rather than eleven dark lines. Cut in the dark, land in the
      // daylight.
      if (cut) cut.style.opacity = String(Math.min(span(p, 0.04, 0.16), 1 - span(p, 0.34, 0.52)));

      var part = span(p, 0.05, 0.26) * 13;
      // Before the cut there is no cut. The seams were drawn on the photograph
      // from the first frame, eleven grey lines down an untouched picture.
      var edge = span(p, 0.02, 0.09);
      for (var i = 0; i < N; i++) {
        var g = i < M ? 0 : 1, j = i % M, P = T[g];
        var Li = i * colw;

        // the picture drains, one strip at a time, left to right. With a small
        // stagger every strip bleached at once and the frame just looked washed
        // out; the milling has to be legible as an order.
        var white = clamp((span(p, 0.10, 0.40) - (i / N) * 0.55) / (1 - 0.55));
        kids[i].pic.style.opacity = String(1 - white);

        // the two ends of each group set off first, so the block closes inwards
        var lag = (i < M ? (M - 1 - j) : j) * STEP;
        var f   = clamp((span(p, 0.40, 0.88) - lag) / (1 - (M - 1) * STEP));

        // Horizontal is linear, because horizontal is the axis the hand is not
        // moving and any curve on it would visibly disagree with the scroll.
        // Vertical is f squared — a strip that lets go of the top of the screen
        // and gathers speed is the one thing here everybody has seen before, and
        // it keeps the strips high while the page below is still arriving instead
        // of leaving half a screen of empty band under them.
        var fy = f * f;         // gravity
        var fx = f * f * f;     // width closes last
        var tw = P.w / M;
        var kx = 1 + (tw / colw - 1) * fx;
        var ky = 1 + (P.h / vh - 1) * fy;
        // A group is laid out from its own left edge with the strips butted up
        // against each other, so the only gaps inside it are the ones the stagger
        // and the cut are opening. Interpolating each strip to its own final
        // rectangle instead made the group breathe apart as it narrowed — the six
        // pieces were converging on the panel and drifting away from each other
        // at the same time, which is not what closing up looks like.
        var gl = g * M * colw + (P.x - g * M * colw) * f;
        var x  = (gl + j * colw * kx - Li) + part * (i - (N - 1) / 2) * (1 - fx);
        var y  = P.y * fy;

        kids[i].col.style.transform =
          'translate3d(' + x + 'px,' + y + 'px,0) scale(' + kx + ',' + ky + ')';
        // Off the page while it travels, flat on the page when it arrives. Two
        // near-whites 1.08:1 apart cannot show a strip moving; a shadow can, and
        // it is gone by the time the strip is a panel, which has none.
        kids[i].lift.style.opacity = String(span(f, 0.02, 0.18) * (1 - span(f, 0.72, 0.99)));

        // the seams inside a group go; the outer two stay and become the panel's
        // own left and right edge. Counter-scaled, or a hairline stops being one.
        var seam = 1 - span(f, 0.60, 0.96);
        kids[i].l.style.opacity = String(edge * (j === 0     ? 1 : seam));
        kids[i].r.style.opacity = String(edge * (j === M - 1 ? 1 : seam));
        kids[i].l.style.transform = kids[i].r.style.transform = 'scaleX(' + (1 / kx) + ')';
      }

      // The strips are home at 0.88 and the stage goes over the next tenth, not
      // in a frame. Every studio that publishes its numbers spends the time on
      // the arrival rather than on the debris: the destination reveal is the
      // slowest thing in the sequence, and here it is the only thing left.
      var out = span(p, 0.90, 1);
      open.style.opacity    = String(1 - out);
      open.style.visibility = out >= 1 ? 'hidden' : 'visible';
    }

    window.addEventListener('scroll', function () {
      if (ticking) return; ticking = true;
      requestAnimationFrame(function () { draw(); ticking = false; });
    }, { passive: true });
    window.addEventListener('resize', measure);
    wide.addEventListener('change', measure);
    still.addEventListener('change', measure);
    measure();
  }

  /* ---- the map is handed over on request -------------------------------
     An embedded Google map is live from the moment it loads, and a live map eats
     the wheel as soon as the pointer crosses it — in the middle of a scroll
     animation that is the pointer's most likely position. The shield sits on top
     until somebody actually wants the map, which is also the click that starts
     Google's session rather than having one started for every visitor who scrolls
     past. Without JavaScript the shield is a button that does nothing and the map
     below it is still a map: readable, just not draggable. */
  var shield = document.getElementById('gmap-on');
  if (shield) {
    shield.addEventListener('click', function () {
      shield.parentNode.classList.add('is-on');
      shield.remove();
    });
  }

  /* ---- the journeys arrive as the section is read -----------------------
     Paint-only, arrival-triggered, and if the observer never fires the rows are
     simply there: `is-armed` is what dims them, and it is added by script. */
  var far = document.getElementById('far');
  if (far && 'IntersectionObserver' in window &&
      !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    far.classList.add('is-armed');
    new IntersectionObserver(function (entries, obs) {
      entries.forEach(function (e) {
        if (!e.isIntersecting) return;
        obs.disconnect();
        far.querySelectorAll('.ct-far__item').forEach(function (el, i) {
          window.setTimeout(function () { el.classList.add('is-on'); }, 60 + i * 90);
        });
      });
    }, { threshold: 0.25 }).observe(far);
  }

  var form = document.getElementById('ct-form');
  if (!form) return;
  var PHONE = @json($phoneRaw), MAIL = @json($email);

  // Which button was pressed decides where it goes. Submit is not cancelled until
  // there is somewhere to send it, so the browser's own required-field checks run
  // first and the page behaves like a form, because it is one.
  var via = 'wa';
  form.querySelectorAll('button[type=submit]').forEach(function (b) {
    b.addEventListener('click', function () { via = b.value; });
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var name = form.querySelector('#f-name').value.trim();
    var tel  = form.querySelector('#f-tel').value.trim();
    var msg  = form.querySelector('#f-msg').value.trim();
    var body = 'Hola' + (name ? ', soy ' + name : '') + '. ' + msg + (tel ? '\n\nMi teléfono: ' + tel : '');
    if (via === 'mail') {
      window.location.href = 'mailto:' + MAIL
        + '?subject=' + encodeURIComponent('Consulta de ' + (name || 'la web'))
        + '&body=' + encodeURIComponent(body);
    } else {
      window.open('https://wa.me/' + PHONE + '?text=' + encodeURIComponent(body), '_blank', 'noopener');
    }
  });
})();
</script>
</body>
</html>
