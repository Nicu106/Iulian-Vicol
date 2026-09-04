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

  {{-- ---- the hero ---------------------------------------------------------
       The words sit where the photograph can carry them. Found by sliding a
       text-sized block across the whole frame and scoring each position by its
       LIGHTEST pixel — that is what decides legibility, not the average. For this
       picture the answer is lower-right, 13.62:1, against 1.43:1 lower-left where
       they were. No text-shadow: a line that needs one is in the wrong place. --}}
  <section class="ct-hero">
    <img class="ct-hero__img" src="{{ $hero }}" width="2400" height="3200"
         alt="Un Porsche Cayman con matrícula alemana" fetchpriority="high" decoding="async">
    <div class="ct-hero__in cat-wrap">
      <p class="ct-hero__kicker">Málaga · {{ $sold }} coches entregados</p>
      <h1 class="ct-h1">Escríbeme.<br>Contesto yo.</h1>
      <p class="ct-lead">No hay centralita ni formulario esperando a que alguien lo mire.</p>
    </div>
  </section>

  {{-- ==================================================================
       A 720 km de aquí — the one bold thing on this page

       It exists only because this dealer's data allows it. He has no showroom;
       the page says so. A pin on a map answers a question nobody asked. What
       somebody looking at a car six hundred kilometres away actually wants to
       know is whether they can trust it unseen — and three people already did,
       in their own words.

       The map is information, not decoration: real coordinates, projected
       linearly over one country, and real road distances. Only cities the review
       TEXTS name are used. The author_location column says Santander for 19 of
       25 and contradicts its own quotes, so it is not trusted here.
       ================================================================== --}}
  <section class="ct-far" id="far" aria-labelledby="far-h">
    <div class="cat-wrap ct-far__in">
      <div class="ct-far__copy">
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

      <figure class="ct-map" aria-hidden="true">
        <svg class="ct-map__svg" viewBox="26 21 58 76" preserveAspectRatio="xMidYMid meet">
          @foreach($from as $i => $f)
            <line class="ct-map__road" data-i="{{ $i }}"
                  x1="{{ $home['x'] }}" y1="{{ $home['y'] }}" x2="{{ $f['x'] }}" y2="{{ $f['y'] }}"></line>
          @endforeach
          @foreach($from as $i => $f)
            <circle class="ct-map__city" data-i="{{ $i }}" cx="{{ $f['x'] }}" cy="{{ $f['y'] }}" r="1.1"></circle>
            <text class="ct-map__label" data-i="{{ $i }}"
                  x="{{ $f['x'] + ($f['x'] > 50 ? -3.2 : 3.2) }}" y="{{ $f['y'] - 2.4 }}"
                  text-anchor="{{ $f['x'] > 50 ? 'end' : 'start' }}">{{ $f['city'] }}</text>
          @endforeach
          <circle class="ct-map__home" cx="{{ $home['x'] }}" cy="{{ $home['y'] }}" r="1.9"></circle>
          <text class="ct-map__label ct-map__label--home"
                x="{{ $home['x'] + 3.6 }}" y="{{ $home['y'] + 1.2 }}">Málaga</text>
        </svg>
        <figcaption class="ct-map__cap">Distancias por carretera. Las ciudades las nombran
          ellos, en sus propias reseñas.</figcaption>
      </figure>
    </div>
  </section>

  {{-- ---- how you reach him ------------------------------------------------ --}}
  <section class="ct-ways-sec cat-wrap" aria-label="Cómo contactar">
    <div class="ct-ways">
      <a class="ct-way ct-way--wa" href="https://wa.me/{{ $phoneRaw }}">
        <span class="ct-way__k">WhatsApp</span>
        <span class="ct-way__v">{{ $phone }}</span>
        <span class="ct-way__note">Lo leo en minutos. Te mando vídeo del coche, incluido lo que no está perfecto.</span>
      </a>
      <a class="ct-way" href="tel:+{{ $phoneRaw }}">
        <span class="ct-way__k">Llamar</span>
        <span class="ct-way__v">{{ $phone }}</span>
        <span class="ct-way__note">Si estoy con un cliente, insiste o escríbeme.</span>
      </a>
      <a class="ct-way" href="mailto:{{ $email }}">
        <span class="ct-way__k">Email</span>
        <span class="ct-way__v">{{ $email }}</span>
        <span class="ct-way__note">Para documentación y facturas.</span>
      </a>
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

  /* ---- the roads draw themselves --------------------------------------
     Triggered by arrival, not tied to scroll position. Tying it to scroll meant
     the whole thing had played out before the section was properly on screen —
     measured: every road was already complete while the section's top was still
     100px below the fold. An observer fires once, when a third of the section is
     in view, and the roads then draw in their own time, furthest first, because
     that is the one that makes the point.

     The drawing is stroke-dashoffset and opacity, both paint-only. Nothing is
     pinned and no scroll is intercepted.

     Without JavaScript, with reduced motion, or if the observer never fires, the
     `is-armed` class is never added and every road, dot and name is simply there.
     The map is information; it must not depend on the effect. */
  var far = document.getElementById('far');
  if (far && 'IntersectionObserver' in window &&
      !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    var roads = far.querySelectorAll('.ct-map__road');
    Array.prototype.forEach.call(roads, function (r, i) {
      var len = r.getTotalLength ? r.getTotalLength() : 100;
      r.style.strokeDasharray = len;
      r.style.strokeDashoffset = len;
      r.style.transition = 'stroke-dashoffset 1100ms cubic-bezier(.22,.61,.36,1) ' + (i * 260) + 'ms';
    });
    far.classList.add('is-armed');

    new IntersectionObserver(function (entries, obs) {
      entries.forEach(function (e) {
        if (!e.isIntersecting) return;
        obs.disconnect();
        far.classList.add('is-drawn');
        Array.prototype.forEach.call(roads, function (r) { r.style.strokeDashoffset = '0'; });
        // each name and each row arrives as its own road lands
        far.querySelectorAll('.ct-far__item').forEach(function (el, i) {
          window.setTimeout(function () { el.classList.add('is-on'); }, 120 + i * 260);
        });
        far.querySelectorAll('.ct-map__city, .ct-map__label[data-i]').forEach(function (el) {
          var i = +el.getAttribute('data-i');
          window.setTimeout(function () { el.classList.add('is-on'); }, 900 + i * 260);
        });
      });
    }, { threshold: 0.34 }).observe(far);
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
