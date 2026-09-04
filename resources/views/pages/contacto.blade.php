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

  /* ---- the opening ------------------------------------------------------
     One photograph, carried by twelve vertical slices. As the page scrolls they
     collapse outwards from the centre — the left six toward the map, the right
     six toward the card — each a little later than the last, so the picture
     opens instead of blinking off.

     The scroll is never touched: the section is made tall, its contents stick,
     and how far the page has moved becomes how far the slices have gone. Every
     input device keeps behaving as it always did. */
  var open = document.getElementById('open');
  var host = document.getElementById('slices');
  if (open && host) {
    var N = 12;
    var wide = window.matchMedia('(min-width: 900px)');
    var still = window.matchMedia('(prefers-reduced-motion: reduce)');
    var built = false, ticking = false;

    function build() {
      if (built) return;
      host.innerHTML = '';
      for (var i = 0; i < N; i++) {
        var s = document.createElement('span');
        s.className = 'ct-slice';
        // the twelve together are one picture: each shows its own 1/12th of it
        s.style.backgroundSize = (N * 100) + '% 100%';
        s.style.backgroundPositionX = (i / (N - 1) * 100) + '%';
        // it collapses toward the edge it is nearest, which is where its half lands
        s.style.transformOrigin = i < N / 2 ? 'left center' : 'right center';
        host.appendChild(s);
      }
      built = true;
    }

    function measure() {
      if (!wide.matches || still.matches) {
        open.classList.remove('is-live'); open.style.height = ''; return;
      }
      build();
      open.classList.add('is-live');
      // one screen to look at it, one to open it
      open.style.height = (window.innerHeight * 2) + 'px';
      draw();
    }

    function draw() {
      if (!open.classList.contains('is-live')) return;
      var top = open.getBoundingClientRect().top;
      var run = open.offsetHeight - window.innerHeight || 1;
      var p = Math.min(1, Math.max(0, -top / run));
      var kids = host.children;
      for (var i = 0; i < kids.length; i++) {
        // slices nearer the centre go first; the outermost pair goes last
        var fromCentre = Math.abs((i + 0.5) - N / 2) / (N / 2);   // 0 centre … 1 edge
        var lag = fromCentre * 0.45;
        var q = Math.min(1, Math.max(0, (p - lag) / (1 - 0.45)));
        var e = q * q * (3 - 2 * q);                              // ease, so it settles
        kids[i].style.transform = 'scaleX(' + (1 - e) + ')';
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
