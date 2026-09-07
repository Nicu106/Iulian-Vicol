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
{{-- The preload has to name the same candidate the <img> will choose, srcset and
     sizes included. Preloading a bare href next to a responsive img is how a page
     downloads its hero twice. --}}
<link rel="preload" as="image" fetchpriority="high"
      href="{{ \App\Support\Img::url($hero, 1080) ?? $hero }}"
      imagesrcset="{{ \App\Support\Img::srcset($hero, 1600) }}"
      imagesizes="(min-width:2000px) 920px, (min-width:900px) 800px, 100vw">
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
       The opening. Words on the page, photograph beside them.

       The file is 2400x3200 — portrait — so it is given a portrait-shaped
       field on the right instead of being squeezed into a landscape band and
       losing half of itself. Nothing is written over it: the frame was
       measured on a 12x16 grid first, and its calm dark region is the right
       half while its busiest and brightest regions are exactly where a
       bottom-left headline would land. See the head of contact.css.
       ================================================================== --}}
  <section class="ct-open ct-grid" aria-labelledby="open-h">

    <div class="ct-open__say">
      <p class="ct-open__kick ct-rise">Málaga · {{ $sold }} coches entregados</p>
      <h1 class="ct-open__h ct-rise" id="open-h">Escríbeme.<br>Contesto&nbsp;yo.</h1>
    </div>

    <div class="ct-open__ways-wrap">
      <ul class="ct-ways">
        <li class="ct-rise">
          <a class="ct-way" href="https://wa.me/{{ $phoneRaw }}">
            <span class="ct-way__k">WhatsApp</span>
            <span class="ct-way__v">{{ $phone }}</span>
            <span class="ct-way__n">Lo leo en minutos. Te mando vídeo del coche, incluido lo que no está perfecto.</span>
          </a>
        </li>
        <li class="ct-rise">
          <a class="ct-way" href="tel:+{{ $phoneRaw }}">
            <span class="ct-way__k">Teléfono</span>
            <span class="ct-way__v">{{ $phone }}</span>
            <span class="ct-way__n">Si estoy con un cliente, insiste o escríbeme.</span>
          </a>
        </li>
        <li class="ct-rise">
          <a class="ct-way" href="mailto:{{ $email }}">
            <span class="ct-way__k">Email</span>
            <span class="ct-way__v ct-way__v--mail">{{ $email }}</span>
            <span class="ct-way__n">Para documentación y facturas.</span>
          </a>
        </li>
      </ul>
    </div>

    <div class="ct-open__pic">
      <div class="ct-open__hold">
        {{-- `sizes` here is set by HEIGHT, not width. The column is 531px wide at 1440
             but 1049px tall, and object-fit:cover on a 3:4 photograph in a box that
             narrow has to satisfy the HEIGHT — so the file must supply
             1049 x 0.75 = 787 CSS px, not 531. Sized by width alone the browser
             chose the 720w file and scaled it up 1.09x to fill: an upscaled
             photograph, which is the one thing this page was rebuilt to stop.
             Measured across 390 → 2200: 390, 768, then 746-787 from 900 up, 911 at
             2200. --}}
        <x-img class="ct-open__img" id="hero-img" :src="$hero"
               alt="Un Porsche Cayman con matrícula alemana, fotografiado en un garaje de Málaga"
               sizes="(min-width:2000px) 920px, (min-width:900px) 800px, 100vw"
               :max="1600" :fallback="1080" :priority="true" />
      </div>
    </div>
  </section>

  {{-- ---- where and when, one answer -------------------------------- --}}
  <section class="ct-where ct-grid" aria-labelledby="where-h">
    <div class="ct-where__canvas">
      <iframe class="ct-where__f" title="Mapa de Málaga, España"
              src="https://maps.google.com/maps?q={{ urlencode('Málaga, España') }}&z=11&hl=es&output=embed"
              loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
      <button class="ct-where__shield" id="map-on" type="button"><span>Activar el mapa</span></button>
    </div>
    <div class="ct-where__say">
      <h2 class="ct-h2 ct-rise" id="where-h">Málaga</h2>
      <p class="ct-say ct-rise">No hay tienda a la que presentarse. Quedamos donde
        esté el coche que quieres ver.</p>
      <p class="ct-where__when ct-rise">Todos los días, a convenir. No tengo horario de
        oficina: trabajo con cita, y te digo el punto exacto cuando quedemos.</p>
      <p class="ct-rise"><a class="mc-link" href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode('Málaga, España') }}"
            target="_blank" rel="noopener">Abrir en Google Maps</a></p>
    </div>

    {{-- The form sits beside the caption, not in a section of its own below the
         proof band. As two separate sections — "Málaga" and "O déjalo escrito" —
         each was a left-aligned block with its right half empty; together they are
         one composition, 5 | 7, the mirror of the opening's 7 | 5. And the page now
         closes on the proof band, which is its strongest thing. --}}
    <div class="ct-write" aria-labelledby="write-h">
      <h2 class="ct-h2 ct-rise" id="write-h">O déjalo escrito</h2>
      <p class="ct-lede ct-rise">Tres campos. Al enviar eliges si te abro WhatsApp o el
        correo — en los dos casos el mensaje va redactado y lo lees antes de mandarlo.</p>

      <form class="ct-form ct-rise" id="ct-form">
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
        <p class="ct-note">No guardo nada en esta web: el mensaje se escribe en tu WhatsApp
          o en tu correo y lo envías tú.</p>
      </form>
    </div>
  </section>

  {{-- ---- why anyone trusts a man with no showroom ------------------- --}}
  {{-- Full bleed: the band is the point, so the colour has to reach both edges.
       The wrap moves inside, which keeps this section on the same spine as every
       other one. --}}
  <section class="ct-far" aria-labelledby="far-h">
    <div class="cat-wrap">
    <h2 class="ct-h2 ct-rise" id="far-h">A {{ number_format(max(array_column($from,'km')),0,',','.') }} km de aquí</h2>
    <p class="ct-lede ct-rise">Nadie compra un coche a setecientos kilómetros por una web.
      Lo compran porque antes hablaron con alguien. Estos condujeron hasta Málaga:</p>

    <ol class="ct-trips">
      @foreach($from as $f)
        <li class="ct-rise">
          <span class="ct-trip__km">{{ number_format($f['km'],0,',','.') }}<i>km</i></span>
          <span class="ct-trip__who">{{ $f['who'] }}, desde {{ $f['city'] }}</span>
          <span class="ct-trip__said">{{ $f['said'] }}</span>
        </li>
      @endforeach
    </ol>

    <p class="ct-note ct-rise">Y {{ $delivered['who'] }} no se movió de casa:
      «{{ $delivered['said'] }}»</p>
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

  /* ---- things arrive once ----------------------------------------------
     16px on --m-reveal / --e-out, 60ms apart. The system already owns the
     duration and the curve, so this invents neither. Once, on first sight:
     a page where everything re-animates on every pass will not sit still to
     be read. */
  var still = window.matchMedia('(prefers-reduced-motion: reduce)');
  var rise = document.querySelectorAll('.ct-rise');
  if (rise.length && 'IntersectionObserver' in window && !still.matches) {
    document.documentElement.classList.add('rise-armed');
    var io = new IntersectionObserver(function (es) {
      var n = 0;
      es.forEach(function (e) {
        if (!e.isIntersecting) { return; }
        var el = e.target;
        window.setTimeout(function () { el.classList.add('is-in'); }, (n++) * 60);
        io.unobserve(el);
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
    rise.forEach(function (el) { io.observe(el); });
  }

  /* ---- the map is handed over on request -------------------------------- */
  var shield = document.getElementById('map-on');
  if (shield) {
    shield.addEventListener('click', function () {
      shield.closest('.ct-where__canvas').classList.add('is-on');
      shield.remove();
    });
  }

  /* ---- the form --------------------------------------------------------
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
