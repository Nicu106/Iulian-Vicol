@extends('layouts.site')

@section('title', 'Contacto — IV MOTORCLASS')
@section('current', 'contacto')
{{-- this page has its own map; the footer's band would be the same place twice --}}
@section('foot-map', 'off')

@push('css')
<link rel="stylesheet" href="{{ asset('css/contact.css') }}">
@endpush

@push('head')
{{-- The preload has to name the same candidate the <img> will choose, srcset and
     sizes included. Preloading a bare href next to a responsive img is how a page
     downloads its hero twice. --}}
<link rel="preload" as="image" fetchpriority="high"
      href="{{ \App\Support\Img::url($hero, 1080) ?? $hero }}"
      imagesrcset="{{ \App\Support\Img::srcset($hero, 1600) }}"
      imagesizes="(min-width:2000px) 920px, (min-width:900px) 800px, 100vw">
@endpush

@section('content')
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
      <p class="ct-open__lede ct-rise">Tres formas de llegar a mí, y las tres las contesto yo.
        Lo normal es en minutos.</p>
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

  {{-- ==================================================================
       THE WAYS — the catalogue's device, turned to this page's job.
       On /catalogo each marque is a full-bleed row of its own colour that
       sweeps in with a nib; here each way to reach him is: WhatsApp green,
       the site's blue for the phone, navy for e-mail. Each band is ONE link,
       the whole width of the screen — on a phone the three are the first
       screen, three thumb-sized targets that cannot be missed. The app's own
       mark sits in the band at 7% white, where a marque's badge sits in its row.
       ================================================================== --}}
  <ul class="ct-bands" id="ct-ways">
    <li class="ct-band ct-band--wa">
      <a class="ct-band__a" href="https://wa.me/{{ $phoneRaw }}">
        <span class="ct-band__fill" aria-hidden="true"></span>
        <span class="ct-band__mark" aria-hidden="true"></span>
        <span class="ct-band__in cat-wrap">
          <span class="ct-band__k">WhatsApp</span>
          <span class="ct-band__v">Escríbeme</span>
          <span class="ct-band__n">Lo leo en minutos. Te mando vídeo del coche, incluido lo que no está perfecto.</span>
          <span class="ct-band__go">Abrir WhatsApp</span>
        </span>
      </a>
    </li>
    <li class="ct-band ct-band--tel ct-band--rtl">
      <a class="ct-band__a" href="tel:+{{ $phoneRaw }}">
        <span class="ct-band__fill" aria-hidden="true"></span>
        <span class="ct-band__mark" aria-hidden="true"></span>
        <span class="ct-band__in cat-wrap">
          <span class="ct-band__k">Teléfono</span>
          <span class="ct-band__v">{{ $phone }}</span>
          <span class="ct-band__n">Si estoy con un cliente y no lo cojo, insiste o escríbeme.</span>
          <span class="ct-band__go">Llamar</span>
        </span>
      </a>
    </li>
    <li class="ct-band ct-band--mail">
      <a class="ct-band__a" href="mailto:{{ $email }}">
        <span class="ct-band__fill" aria-hidden="true"></span>
        <span class="ct-band__mark" aria-hidden="true"></span>
        <span class="ct-band__in cat-wrap">
          <span class="ct-band__k">Email</span>
          <span class="ct-band__v ct-band__v--mail">{{ $email }}</span>
          <span class="ct-band__n">Para documentación y facturas.</span>
          <span class="ct-band__go">Escribir un correo</span>
        </span>
      </a>
    </li>
  </ul>

  {{-- ---- where and when, one answer -------------------------------- --}}
  <section class="ct-where ct-grid" aria-labelledby="where-h">
    {{-- The map, as a picture of the map. The live Google embed is built only
         when "Activar el mapa" is pressed (script below): until then this is one
         lazy vector drawing (~30 KB compressed) in the site's own
         palette, and not a single request to Google — no scripts, no fonts, no
         cookies. See App\Support\StaticMap; `php artisan map:render` redraws it. --}}
    <div class="ct-where__canvas" id="ct-map" data-map-draw
         data-src="/img/map/malaga-phone.svg" data-src-wide="/img/map/malaga-wide.svg" data-media="(min-width:760px)">
      <picture>
        <source media="(min-width:760px)" srcset="/img/map/malaga-wide.svg" width="2000" height="875">
        <img class="ct-where__img" src="/img/map/malaga-phone.svg" width="800" height="600"
             alt="Mapa de Málaga" loading="lazy" decoding="async">
      </picture>
      {{-- Names are ours, over the drawing, in the site's typeface. Placed from
           the same projection that drew the map (StaticMap::pos), per ratio. --}}
      @php
        [$ax, $ay] = \App\Support\StaticMap::pos('malaga-phone', ...\App\Support\StaticMap::AIRPORT);
        [$wx, $wy] = \App\Support\StaticMap::pos('malaga-wide', ...\App\Support\StaticMap::AIRPORT);
      @endphp
      <span class="ct-pin" aria-hidden="true"></span>
      <span class="ct-place ct-place--city" aria-hidden="true">Málaga</span>
      <span class="ct-place ct-place--air" aria-hidden="true"
            style="--x:{{ $ax }}%;--y:{{ $ay }}%;--wx:{{ $wx }}%;--wy:{{ $wy }}%">Aeropuerto</span>
      <button class="ct-where__shield" id="map-on" type="button"><span>Activar el mapa</span></button>
      <small class="ct-where__osm">Mapa © OpenStreetMap</small>
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
          <input class="mc-input" type="text" id="f-name" name="name" autocomplete="name" autocapitalize="words" enterkeyhint="next" required>
        </label>
        <label class="ct-f">
          <span>Tu teléfono <em>(opcional)</em></span>
          <input class="mc-input" type="tel" id="f-tel" name="phone" autocomplete="tel" inputmode="tel" enterkeyhint="next">
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
          <span class="ct-trip__km"><span data-km="{{ $f['km'] }}">{{ number_format($f['km'],0,',','.') }}</span><i>km</i></span>
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
@endsection

@section('after')
<div class="cat-dock" role="complementary" aria-label="Contacto">
  <div class="mc-bar">
    <span class="cat-dock__t">¿Hablamos?<b>Contesto yo</b></span>
    <span class="mc-bar__act">
      <a class="mc-btn mc-btn--cta" href="https://wa.me/{{ $phoneRaw }}">WhatsApp</a>
      <a class="mc-btn mc-btn--ghost" href="tel:+{{ $phoneRaw }}" aria-label="Llamar">Tel</a>
    </span>
  </div>
</div>
@endsection

@push('js')
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

  /* ---- the bands sweep in, one after another, like the catalogue's rows ----
     is-live starts each band's stroke; those already on screen at load follow
     each other 260ms apart, the rest wait until they are reached. */
  var bands = document.querySelectorAll('.ct-band');
  if (bands.length) {
    document.documentElement.classList.add('bands-armed');
    var liveBand = function (b, d) { window.setTimeout(function () { b.classList.add('is-live'); }, d); };
    if (!('IntersectionObserver' in window) || still.matches) {
      bands.forEach(function (b) { b.classList.add('is-live'); });
    } else {
      var k = 0;
      var bo = new IntersectionObserver(function (es) {
        es.forEach(function (e) {
          if (!e.isIntersecting) return;
          bo.unobserve(e.target); liveBand(e.target, (k++) * 260);
        });
      }, { threshold: 0.25 });
      bands.forEach(function (b) { bo.observe(b); });
      window.setTimeout(function () { bands.forEach(function (b) { b.classList.add('is-live'); }); }, 5000);
    }
  }

  /* ---- the distances count up to themselves, once --------------------------
     720, 630, 130 km arrive as a trip odometer would: fast, then settling. The
     final number is in the markup; without script or with reduced motion it is
     simply there. */
  var kms = document.querySelectorAll('.ct-trip__km [data-km]');
  if (kms.length && 'IntersectionObserver' in window && !still.matches) {
    var fmt = function (n) { return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, '.'); };
    var ko = new IntersectionObserver(function (es) {
      es.forEach(function (e) {
        if (!e.isIntersecting) return;
        ko.unobserve(e.target);
        var el = e.target, to = +el.getAttribute('data-km'), t0 = null, dur = 1400;
        var step = function (t) {
          if (t0 === null) t0 = t;
          var p = Math.min(1, (t - t0) / dur), q = 1 - Math.pow(1 - p, 3);
          el.textContent = fmt(Math.round(to * q));
          if (p < 1) requestAnimationFrame(step);
        };
        el.textContent = '0';
        requestAnimationFrame(step);
      });
    }, { threshold: 0.6 });
    kms.forEach(function (el) { ko.observe(el); });
  }

  /* ---- the live map, built on request ---------------------------------
     Nothing from Google exists in the page until this runs. The picture stays
     underneath while the frame loads, so there is never an empty box. */
  var shield = document.getElementById('map-on');
  if (shield) {
    shield.addEventListener('click', function () {
      var box = document.getElementById('ct-map');
      var f = document.createElement('iframe');
      f.className = 'ct-where__f';
      f.title = 'Mapa de Málaga, España';
      f.referrerPolicy = 'no-referrer-when-downgrade';
      f.allowFullscreen = true;
      f.src = 'https://maps.google.com/maps?q=' + encodeURIComponent('Málaga, España') + '&z=12&hl=es&output=embed';
      f.addEventListener('load', function () { box.classList.add('is-live'); });
      box.appendChild(f);
      shield.remove();
      f.focus();
    });
  }

  /* ---- the dock waits while the bands are on screen --------------------
     On a phone the first screen already IS WhatsApp and the phone, three
     screen-wide targets; the dock repeating them underneath was the same
     things twice in one view. It arrives once they have scrolled away, and leaves again if
     the reader comes back up to them. */
  var ways = document.getElementById('ct-ways');
  if (ways && 'IntersectionObserver' in window) {
    new IntersectionObserver(function (es) {
      document.body.classList.toggle('ct-dock-wait', es[0].isIntersecting);
    }, { threshold: 0 }).observe(ways);
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
    if (window.mcRefTrack) { window.mcRefTrack(via === 'mail' ? 'email' : 'whatsapp'); }
    if (via === 'mail') {
      window.location.href = 'mailto:' + MAIL
        + '?subject=' + encodeURIComponent('Consulta de ' + (name || 'la web'))
        + '&body=' + encodeURIComponent(body);
    } else {
      // + the recommendation code, if this visitor came through someone's link (layouts/site)
      window.open('https://wa.me/' + PHONE + '?text=' + encodeURIComponent(body + (window.mcRefNote || '')), '_blank', 'noopener');
    }
  });
})();
</script>
@endpush
