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
<link rel="preload" as="image" href="{{ $hero }}" fetchpriority="high">
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
       LA LLEGADA

       The page opens on the tail light, close enough that you cannot tell
       what it is: a bar of red in the dark. Then the camera pulls back —
       the light becomes a lamp, the lamp becomes a car, the car becomes a
       garage in Málaga — and when it has arrived the photograph hands the
       page over to the light and the words stay.

       This is the one place the boldness is spent. Everything below it is
       quiet.

       The numbers are the photograph's own, measured off the file rather
       than chosen: the tail light sits at 67.7% across and 50.8% down, it
       is rgb(240,56,51), and it is the only saturated thing in 2400x3200
       pixels of black car and grey concrete. The words sit bottom-right
       because that quadrant means 8.6:1 for white type, against 2.3:1
       top-left where the garage lamps are.
       ================================================================== --}}
  <div class="ct-approach" id="approach">
    <div class="ct-approach__stage" id="ap-stage">
      <img class="ct-approach__img" id="ap-img" src="{{ $hero }}" width="2400" height="3200"
           alt="Un Porsche Cayman con matrícula alemana, fotografiado en un garaje de Málaga"
           fetchpriority="high" decoding="async">

      {{-- the light the photograph is lit by, before you know it is a light --}}
      <div class="ct-approach__glow" id="ap-glow" aria-hidden="true"></div>
      {{-- and the page, rising through it at the end --}}
      <div class="ct-approach__veil" id="ap-veil" aria-hidden="true"></div>

      <div class="ct-approach__words" id="ap-words">
        <p class="ct-kicker">Málaga · {{ $sold }} coches entregados</p>
        <h1 class="ct-h1">Escríbeme.<br>Contesto yo.</h1>
        <p class="ct-lead">No hay centralita, ni un formulario esperando a que
          alguien lo mire mañana.</p>
      </div>
    </div>
  </div>

  {{-- ==================================================================
       ESCRÍBEME — the channels

       Not a grid of cards. A statement on the left that does not move, and
       the ways to reach him listed against it, because the point of the
       section is that they all end at the same person.
       ================================================================== --}}
  <section class="ct-write" id="write" aria-labelledby="write-h">
    <div class="cat-wrap ct-write__in">

      <div class="ct-write__say">
        <h2 class="ct-h2" id="write-h">Todos llegan a mí</h2>
        <p class="ct-write__p">Elige el que te resulte cómodo. Detrás de los cuatro
          hay una sola persona, y contesta él.</p>
        <a class="mc-btn mc-btn--cta ct-write__cta"
           href="https://wa.me/{{ $phoneRaw }}?text={{ urlencode('Hola, te escribo desde la web.') }}">Abrir WhatsApp</a>
      </div>

      <ul class="ct-chan">
        <li class="ct-chan__row">
          <span class="ct-chan__k">WhatsApp</span>
          <a class="ct-chan__v" href="https://wa.me/{{ $phoneRaw }}">{{ $phone }}</a>
          <span class="ct-chan__n">Lo leo en minutos. Te mando vídeo del coche, incluido lo que no está perfecto.</span>
        </li>
        <li class="ct-chan__row">
          <span class="ct-chan__k">Teléfono</span>
          <a class="ct-chan__v" href="tel:+{{ $phoneRaw }}">{{ $phone }}</a>
          <span class="ct-chan__n">Si estoy con un cliente, insiste o escríbeme.</span>
        </li>
        <li class="ct-chan__row">
          <span class="ct-chan__k">Email</span>
          <a class="ct-chan__v" href="mailto:{{ $email }}">{{ $email }}</a>
          <span class="ct-chan__n">Para documentación y facturas.</span>
        </li>
        <li class="ct-chan__row">
          <span class="ct-chan__k">Horario</span>
          <b class="ct-chan__v">Todos los días, a convenir</b>
          <span class="ct-chan__n">Trabajo con cita. <a class="mc-link" href="#hours-h">Ver el detalle</a></span>
        </li>
      </ul>

    </div>
  </section>

  {{-- ==================================================================
       DÓNDE — the map, full bleed, with the honest caption over it

       Every dealer's contact page pins a shop. He has none, and the page
       has said so twice already, so the map is the city and the caption is
       the truth rather than an address.
       ================================================================== --}}
  <section class="ct-where" aria-labelledby="where-h">
    <div class="ct-where__map">
      <div class="ct-where__canvas">
        <iframe class="ct-where__f" title="Mapa de Málaga, España"
                src="https://maps.google.com/maps?q={{ urlencode('Málaga, España') }}&z=11&hl=es&output=embed"
                loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
        {{-- An embedded map is live from the frame it loads and eats the wheel as
             soon as the pointer crosses it. One press hands it over. --}}
        <button class="ct-where__shield" id="map-on" type="button"><span>Activar el mapa</span></button>
      </div>
      <div class="ct-where__card">
        <h2 class="ct-h2" id="where-h">Málaga</h2>
        <p>No hay tienda a la que presentarse. Quedamos donde esté el coche que
          quieres ver, y te digo el punto exacto cuando quedemos.</p>
        <a class="mc-link" href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode('Málaga, España') }}"
           target="_blank" rel="noopener">Abrir en Google Maps</a>
      </div>
    </div>
  </section>

  {{-- A slice of the same photograph, at the flank rather than the light: the
       car is still there between the sections, and the page keeps its ground. --}}
  <div class="ct-seam" aria-hidden="true">
    <img src="{{ $hero }}" alt="" width="2400" height="3200" loading="lazy" decoding="async">
  </div>

  {{-- ==================================================================
       A 720 KM — why anyone trusts a man with no showroom
       Only cities the review TEXTS name are used: the author_location column
       says Santander for 19 of 25 and contradicts its own quotes.
       ================================================================== --}}
  <section class="ct-far" id="far" aria-labelledby="far-h">
    <div class="cat-wrap">
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
    </div>
  </section>

  {{-- ---- cuándo ------------------------------------------------------- --}}
  <section class="ct-hours" aria-labelledby="hours-h">
    <div class="cat-wrap ct-hours__in">
      <div>
        <h2 class="ct-h2" id="hours-h">Cuándo</h2>
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

  {{-- ---- the slow way, last ------------------------------------------- --}}
  <section class="ct-formband" aria-labelledby="form-h">
    <div class="cat-wrap ct-form-sec">
      <h2 class="ct-h2" id="form-h">O déjalo escrito aquí</h2>
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
    </div>
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

  /* ---- la llegada ------------------------------------------------------
     One photograph, pulled back from its own tail light.

       0.00        scale 2.6 on (67.7%, 50.8%) — the measured centre of the only
                   saturated thing in the frame. At this crop 1,000 source pixels
                   cover 1,440 of screen: 1.4x, soft the way a light is soft, not
                   the way a stretched JPEG is.
       0.00-0.45   the red glow that lights the opening fades out as the light
                   becomes a lamp on a car
       0.00-0.78   the pull-back
       0.34-0.58   the words arrive, bottom-right
       0.78-1.00   the photograph hands over: the page's own ground rises through it

     The scale ramp is GEOMETRIC, not linear — 2.6^(1-p). A camera's approach is
     geometric, and a linear one reads as though it slows down at the end. The
     rule that scrubbed motion must be linear is about a hand dragging something
     to a place; nothing here is being dragged to a place.

     Transform and opacity only: no filter, no blend on the image itself, so it
     stays on the compositor on a phone. dt is not used — this is a position
     mapping, not an animation — and everything is read once per frame.

     Under prefers-reduced-motion, or with no JavaScript, the stage is one screen
     tall, the photograph sits at its natural scale and the words are simply
     there. ==================================================================== */
  var wrap = document.getElementById('approach');
  var img  = document.getElementById('ap-img');
  var glow = document.getElementById('ap-glow');
  var veil = document.getElementById('ap-veil');
  var words= document.getElementById('ap-words');
  var stage= document.getElementById('ap-stage');

  if (wrap && img) {
    var FROM  = 2.6;      // the opening scale, on the tail light
    var still = window.matchMedia('(prefers-reduced-motion: reduce)');
    var run = 0, ticking = false;

    var clamp = function (v) { return v < 0 ? 0 : v > 1 ? 1 : v; };
    var span  = function (p, a, b) { return clamp((p - a) / (b - a)); };

    function measure() {
      if (still.matches) {
        wrap.style.height = '';
        wrap.classList.remove('is-live');
        img.style.transform = ''; glow.style.opacity = ''; veil.style.opacity = '';
        words.style.opacity = ''; words.style.transform = ''; words.style.bottom = '';
        return;
      }
      wrap.classList.add('is-live');
      // 2.2 screens: one to arrive, one and a bit to hand over. Shorter and the
      // pull-back is a jump; longer and the reader is scrolling at a photograph.
      wrap.style.height = Math.round(window.innerHeight * 2.2) + 'px';
      draw();
    }

    function draw() {
      if (!wrap.classList.contains('is-live')) { return; }
      var top = wrap.getBoundingClientRect().top;
      run = wrap.offsetHeight - window.innerHeight || 1;
      var p = clamp(-top / run);

      var s = Math.pow(FROM, 1 - clamp(span(p, 0, 0.78)));
      img.style.transform = 'scale(' + s.toFixed(4) + ')';
      glow.style.opacity  = String(1 - span(p, 0, 0.45));
      veil.style.opacity  = String(span(p, 0.78, 1));

      var w = span(p, 0.34, 0.58);
      words.style.opacity = String(w * (1 - span(p, 0.88, 1)));
      words.style.transform = 'translate3d(0,' + ((1 - w) * 14).toFixed(1) + 'px,0)';
      // The stage is sticky at top:0 but the header above it is not, so at rest
      // its last header-height of pixels are below the fold — and the words were
      // in them. Anchored to the viewport instead, they are right in both states.
      var over = stage.getBoundingClientRect().bottom - window.innerHeight;
      words.style.bottom = (over > 0 ? over : 0) + 'px';
    }

    window.addEventListener('scroll', function () {
      if (ticking) { return; }
      ticking = true;
      requestAnimationFrame(function () { draw(); ticking = false; });
    }, { passive: true });
    window.addEventListener('resize', measure);
    still.addEventListener('change', measure);
    measure();
  }

  /* ---- the map is handed over on request -------------------------------- */
  var shield = document.getElementById('map-on');
  if (shield) {
    shield.addEventListener('click', function () {
      shield.closest('.ct-where__canvas').classList.add('is-on');
      shield.remove();
    });
  }

  /* ---- the journeys arrive as the section is read ----------------------- */
  var far = document.getElementById('far');
  if (far && 'IntersectionObserver' in window &&
      !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    far.classList.add('is-armed');
    new IntersectionObserver(function (e, obs) {
      if (!e[0].isIntersecting) { return; }
      obs.disconnect();
      far.querySelectorAll('.ct-far__item').forEach(function (el, i) {
        window.setTimeout(function () { el.classList.add('is-on'); }, 60 + i * 90);
      });
    }, { threshold: 0.25 }).observe(far);
  }

  /* ---- the form ---------------------------------------------------------
     Which button was pressed decides where it goes. Submit is not cancelled
     until there is somewhere to send it, so the browser's own required-field
     checks run first and the page behaves like a form, because it is one. */
  var form = document.getElementById('ct-form');
  if (!form) { return; }
  var PHONE = @json($phoneRaw), MAIL = @json($email);
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
