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
       The opening: one car, then the picture parts and what was behind it
       is where you reach him.

       Twelve vertical slices carry one photograph between them. As the page
       scrolls they collapse outwards from the centre — the left six toward the
       left edge, where the map is, the right six toward the right, where the
       card is — each one a little later than the one before it, so the picture
       opens rather than blinking off.

       Nothing is intercepted: the section is tall, its contents stick, and how
       far the page has moved is how far the slices have gone. Without
       JavaScript, on a narrow screen, or with reduced motion, the slices are
       never built and the map and the card are simply there.
       ================================================================== --}}
  <section class="ct-open" id="open">
    <div class="ct-open__pin">

      <div class="ct-open__under">
        <div class="ct-open__map">
          <iframe class="ct-map__it" title="Málaga en el mapa" loading="lazy"
                  referrerpolicy="no-referrer-when-downgrade" tabindex="-1" aria-hidden="true"
                  src="https://maps.google.com/maps?q={{ $mapQuery }}&z=11&output=embed"></iframe>
          <span class="ct-open__pinlabel">{{ $place }}</span>
        </div>

        <div class="ct-open__card">
          <span class="ct-hero__eyebrow">Málaga · {{ $sold }} coches entregados</span>
          <h1 class="ct-h1">Escríbeme.<br>Contesto yo.</h1>
          <p class="ct-lead">No hay centralita. El teléfono es el mío y WhatsApp lo leo en minutos.</p>

          <div class="ct-ways">
            <a class="ct-way ct-way--wa" href="https://wa.me/{{ $phoneRaw }}">
              <span class="ct-way__k">WhatsApp</span>
              <span class="ct-way__v">{{ $phone }}</span>
            </a>
            <a class="ct-way" href="tel:+{{ $phoneRaw }}">
              <span class="ct-way__k">Llamar</span>
              <span class="ct-way__v">{{ $phone }}</span>
            </a>
            <a class="ct-way" href="mailto:{{ $email }}">
              <span class="ct-way__k">Email</span>
              <span class="ct-way__v">{{ $email }}</span>
            </a>
          </div>
        </div>
      </div>

      {{-- the picture, on top, built by the script --}}
      <div class="ct-open__over" id="slices" aria-hidden="true"
           style="--hero:url('{{ $hero }}')"></div>

      {{-- The first words, on the car, before anything moves. aria-hidden: the
           same sentence is the card's heading underneath. --}}
      <div class="ct-open__title" id="open-title" aria-hidden="true">
        <span class="ct-open__kicker">Málaga · {{ $sold }} coches entregados</span>
        <p class="ct-open__big">Escríbeme.<br>Contesto yo.</p>
        <span class="ct-open__scroll">Baja para verlo ↓</span>
      </div>

      {{-- and the picture as one image, for everyone who never sees the slices --}}
      <img class="ct-open__plain" src="{{ $hero }}" width="1600" height="822"
           alt="Un Mercedes E350d que vendí, en Málaga" fetchpriority="high" decoding="async">
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

  /* ================================================================
     The opening, frame by frame

     0.00  One car, whole, filling the screen, with the page's first words on
           it. Nothing has moved.
     0.00-0.18  The picture separates into twelve columns: gaps open between
           them and the words fade, so what you are looking at stops being a
           photograph and becomes twelve pieces of one.
     0.18-0.30  Every other column loses its picture — it goes to the page's own
           navy — so the frame is columns WITH the car and columns without.
     0.24-0.62  The empty ones fall. They accelerate (t squared, the way a thing
           falls) and they leave in order, left to right, so it reads as a
           collapse rather than a switch.
     0.55-1.00  The six that kept the picture divide: three gather left, three
           gather right, each compressing toward its own edge — and behind them,
           where they were, is the map on the left and everything you need to
           reach him on the right.

     Two things the research settles. Movement is transform and opacity only, so
     it stays on the compositor. And the easing is LINEAR: a scroll-driven
     animation is already eased by the hand doing the scrolling, and a curve on
     top of that double-eases it — the previous version smoothstepped here, and
     that is gone.

     The scroll itself is never touched: the section is tall, its contents stick,
     and page distance becomes animation distance. ==================== */
  var open = document.getElementById('open');
  var host = document.getElementById('slices');
  var title = document.getElementById('open-title');
  if (open && host) {
    var N = 12;
    var wide = window.matchMedia('(min-width: 900px)');
    var still = window.matchMedia('(prefers-reduced-motion: reduce)');
    var kids = [], ticking = false, IMG = { w: 0, h: 0 };

    // The picture's own proportions, so the twelve pieces are a photograph and
    // not a stretched one. A percentage pair on background-size forces BOTH axes:
    // the previous version painted a 0.75 portrait into a 1.57 box.
    var probe = new Image();
    probe.onload = function () { IMG.w = probe.naturalWidth; IMG.h = probe.naturalHeight; measure(); };
    probe.src = @json($hero);

    function build() {
      host.innerHTML = ''; kids = [];
      for (var i = 0; i < N; i++) {
        var s = document.createElement('span');
        s.className = 'ct-slice' + (i % 2 ? ' is-empty' : ' is-photo');
        host.appendChild(s); kids.push(s);
      }
    }

    function frame() {
      // cover, computed rather than declared: the picture is scaled to fill the
      // screen at its own aspect, then each column shows its own strip of it
      if (!IMG.w || !kids.length) return;
      var vw = window.innerWidth, vh = host.clientHeight || window.innerHeight;
      var scale = Math.max(vw / IMG.w, vh / IMG.h);
      var dw = IMG.w * scale, dh = IMG.h * scale;
      var ox = (vw - dw) / 2, oy = (vh - dh) * 0.52;   // 52% down, where the car sits
      var col = vw / N;
      for (var i = 0; i < N; i++) {
        kids[i].style.backgroundSize = dw + 'px ' + dh + 'px';
        kids[i].style.backgroundPosition = (ox - i * col) + 'px ' + oy + 'px';
      }
    }

    function measure() {
      if (!wide.matches || still.matches || !IMG.w) {
        open.classList.remove('is-live'); open.style.height = ''; return;
      }
      if (!kids.length) build();
      open.classList.add('is-live');
      open.style.height = (window.innerHeight * 2.6) + 'px';
      frame(); draw();
    }

    var clamp = function (v) { return v < 0 ? 0 : v > 1 ? 1 : v; };
    var span  = function (p, a, b) { return clamp((p - a) / (b - a)); };

    function draw() {
      if (!open.classList.contains('is-live')) return;
      var top = open.getBoundingClientRect().top;
      var run = open.offsetHeight - window.innerHeight || 1;
      var p = clamp(-top / run);
      var vw = window.innerWidth, vh = window.innerHeight;

      if (title) title.style.opacity = String(1 - span(p, 0.04, 0.16));

      // What is behind stays hidden until the columns are actually leaving. Without
      // this the map and the card showed through the falling gaps and the middle of
      // the sequence was a jumble of tarmac, road names and half a phone number.
      var under = open.querySelector('.ct-open__under');
      if (under) under.style.opacity = String(span(p, 0.58, 0.86));

      var part  = span(p, 0.00, 0.18);
      var drop  = span(p, 0.24, 0.62);
      var split = span(p, 0.55, 1.00);

      for (var i = 0; i < N; i++) {
        var k = kids[i], x = (i - (N - 1) / 2) * (part * 10), y = 0, sx = 1, op = 1;

        if (k.classList.contains('is-empty')) {
          k.style.setProperty('--photo', String(1 - span(p, 0.18, 0.30)));
          var lag = (i / N) * 0.30;
          var t = clamp((drop - lag) / (1 - 0.30));
          y = t * t * (vh * 1.35);
          op = 1 - span(t, 0.75, 1);
        } else {
          var left = i < N / 2;
          var order = left ? (N / 2 - 1 - i) : (i - N / 2);
          var t2 = clamp((split - order * 0.10) / (1 - 0.25));
          var edge = left ? -(i + 1) * (vw / N) : (N - i) * (vw / N);
          x += t2 * edge;
          sx = 1 - t2 * 0.92;
          op = 1 - span(t2, 0.82, 1);
        }
        k.style.transform = 'translate3d(' + x + 'px,' + y + 'px,0) scaleX(' + sx + ')';
        k.style.opacity = String(op);
      }
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
