<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, interactive-widget=resizes-content">
<meta name="robots" content="noindex, nofollow">
<title>{{ $car->brand }} {{ $car->model }} {{ $car->year }} — IV MOTORCLASS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&display=swap">
<link rel="stylesheet" href="{{ asset('css/mc-tokens.css') }}">
<link rel="stylesheet" href="{{ asset('css/brandbook.css') }}">
<link rel="stylesheet" href="{{ asset('css/catalog.css') }}">
<link rel="stylesheet" href="{{ asset('css/foot.css') }}">
<link rel="stylesheet" href="{{ asset('css/car.css') }}">
</head>
<body class="bb cat">

@include('partials.head', ['current' => ''])

<main class="cat-wrap car">

  <a class="car-back mc-link" href="/catalogo">← Todos los coches</a>

  <h1 class="car-h">
    {{ $car->brand }} {{ $car->model }} <span>{{ $car->year }}</span>
  </h1>

  <div class="car-grid">

    {{-- ---------------- the photographs ---------------- --}}
    <section class="car-gallery" aria-label="Fotografías">

      {{-- The one being looked at. Without JavaScript this is simply the first
           photograph and every other one is reachable below as its own link. --}}
      <figure class="car-stage">
        <img class="car-stage__img" id="stage"
             src="{{ $photos[0]['path'] }}"
             alt="{{ $car->brand }} {{ $car->model }} {{ $car->year }}"
             width="1600" height="1067" fetchpriority="high" decoding="async">
        {{-- Arrows and the full-screen open are written by the script: without it the
             stage is a photograph, which is honest, rather than dead furniture. --}}
        <figcaption class="car-stage__count"><b id="stage-n">1</b> / {{ count($photos) }}</figcaption>
      </figure>

      {{-- Under the photograph, what the buyer is asking. Three questions, in the
           order they get asked: what does it look like, what is it like inside,
           and what is wrong with it. Written by the script — without JavaScript
           there is nothing to filter, so an inert row of buttons would be a lie. --}}
      <div class="car-tabs" role="tablist" aria-label="Tipo de fotografía" hidden>
        <button class="car-tab is-on" type="button" role="tab" aria-selected="true"
                data-group="all">Todas <span>{{ $counts['all'] }}</span></button>
        @foreach($groups as $key => $label)
          <button class="car-tab" type="button" role="tab" aria-selected="false"
                  data-group="{{ $key }}">{{ $label }} <span>{{ $counts[$key] }}</span></button>
        @endforeach
      </div>

      {{-- Small, and scrolled. 58 photographs will not fit any other way, and a
           grid of 58 thumbnails is a wall, not a gallery. --}}
      <div class="car-thumbs" id="thumbs">
        @foreach($photos as $i => $p)
          <a class="car-thumb {{ $i === 0 ? 'is-on' : '' }}"
             href="{{ $p['path'] }}"
             data-group="{{ $p['group'] ?? 'none' }}"
             data-n="{{ $p['n'] }}"
             aria-label="Foto {{ $p['n'] }}{{ $p['group'] ? ' · '.($groups[$p['group']] ?? '') : '' }}">
            <img src="{{ $p['path'] }}" alt="" width="240" height="160" loading="lazy" decoding="async">
          </a>
        @endforeach
      </div>

      {{-- The one group that can be empty and still has to say something. --}}
      <p class="car-empty" id="car-empty" hidden></p>
    </section>

    {{-- ---------------- the facts ---------------- --}}
    <aside class="car-side">
      <div class="car-price">
        <span class="mc-price">{{ $euros($price['now']) }}</span>
        @if($car->mileage)<span class="mc-km">{{ number_format($car->mileage, 0, ',', '.') }} km</span>@endif
        @if($price['before'])
          <span class="car-price__was">
            antes <s>{{ $euros($price['before']) }}</s>
            <b>−{{ $euros($price['off']) }}</b>
          </span>
        @endif
      </div>

      <dl class="mc-specs car-specs">
        @foreach($specs as $k => $v)
          <div class="mc-specs__row"><dt>{{ $k }}</dt><dd>{{ $v }}</dd></div>
        @endforeach
      </dl>

      <div class="car-act">
        <a class="mc-btn mc-btn--cta" href="https://wa.me/34614753187?text={{ urlencode('Hola, me interesa el '.$car->brand.' '.$car->model.' '.$car->year) }}">WhatsApp</a>
        <a class="mc-btn mc-btn--ghost" href="tel:+34614753187">Llamar</a>
      </div>
    </aside>
  </div>

  @if($car->description)
    <section class="car-sec car-text">
      <h2 class="car-h2">Lo que hay que saber</h2>
      <p>{{ \Illuminate\Support\Str::of($car->description)->stripTags()->limit(700) }}</p>
    </section>
  @endif

  @if(is_array($car->features) && count($car->features))
    <section class="car-sec">
      <h2 class="car-h2">Equipamiento</h2>
      <ul class="car-feats">
        @foreach(array_slice($car->features, 0, 24) as $f)<li>{{ $f }}</li>@endforeach
      </ul>
      @if(count($car->features) > 24)
        <p class="car-more">y {{ count($car->features) - 24 }} más — pregúntame por cualquiera.</p>
      @endif
    </section>
  @endif

  @if(count($tech))
    <section class="car-sec">
      <h2 class="car-h2">Especificaciones técnicas</h2>
      <dl class="car-tech">
        @foreach($tech as $k => $v)
          <div class="car-tech__row"><dt>{{ $k }}</dt><dd>{{ $v }}</dd></div>
        @endforeach
      </dl>
    </section>
  @endif

  @if(count($tags))
    <section class="car-sec">
      <h2 class="car-h2">Etiquetas</h2>
      <ul class="car-tags">
        @foreach($tags as $t)<li>{{ $t }}</li>@endforeach
      </ul>
    </section>
  @endif

  {{-- Two things a buyer does before writing: works out the monthly figure, and asks
       to see it. Both here, both plain. The live site puts a "market average" beside
       the price, generated with random_int(1200,1800) — a different number on every
       load. It is not here, and the real reduction is, because that one is true. --}}
  <section class="car-sec car-ask">
    <div class="car-ask__col">
      <h2 class="car-h2">Calculadora</h2>
      <p class="car-ask__note">Orientativa. <b>No incluye intereses ni comisiones</b> —
        el número real depende de la financiera, y te lo digo antes de firmar nada.</p>
      <div class="car-calc">
        <label class="car-calc__f">
          <span>Entrada</span>
          <input class="mc-input" type="number" id="calc-down" value="5000" min="0"
                 max="{{ $price['now'] }}" step="500" inputmode="numeric">
        </label>
        <label class="car-calc__f">
          <span>Meses</span>
          <select class="mc-input" id="calc-months">
            <option>24</option><option selected>48</option><option>60</option><option>72</option>
          </select>
        </label>
        <p class="car-calc__out">
          <b id="calc-sum">—</b> <span>al mes, sin intereses</span>
        </p>
      </div>
    </div>

    <div class="car-ask__col">
      <h2 class="car-h2">¿Lo quieres ver?</h2>
      <p class="car-ask__note">Escríbeme y quedamos. Contesto yo, no un formulario.</p>
      <form class="car-form" id="car-form">
        <label class="car-calc__f">
          <span>Tu nombre</span>
          <input class="mc-input" type="text" id="f-name" autocomplete="name" placeholder="Cómo te llamas">
        </label>
        <label class="car-calc__f">
          <span>Cuándo te viene bien</span>
          <input class="mc-input" type="text" id="f-when" placeholder="Esta semana, fin de semana…">
        </label>
        <button class="mc-btn mc-btn--cta" type="submit">Enviar por WhatsApp</button>
      </form>
    </div>
  </section>
</main>

{{-- Full screen. Built empty; the script fills and opens it. --}}
<div class="car-view" id="view" hidden role="dialog" aria-modal="true" aria-label="Fotografía a pantalla completa">
  <button class="car-view__x" type="button" id="view-x" aria-label="Cerrar">&times;</button>
  <button class="car-view__nav car-view__nav--prev" type="button" id="view-prev" aria-label="Anterior"></button>
  <img class="car-view__img" id="view-img" src="" alt="">
  <button class="car-view__nav car-view__nav--next" type="button" id="view-next" aria-label="Siguiente"></button>
  <span class="car-view__count" id="view-count"></span>
</div>

@include('partials.foot')

<div class="cat-dock" role="complementary" aria-label="Contacto">
  <div class="mc-bar">
    <span class="mc-bar__pair">
      <span class="mc-bar__price">{{ $euros($car->price) }}</span>
      @if($car->mileage)<span class="mc-bar__km">{{ number_format($car->mileage, 0, ',', '.') }} km</span>@endif
    </span>
    <span class="mc-bar__act">
      <a class="mc-btn mc-btn--cta" href="https://wa.me/34614753187">WhatsApp</a>
      <a class="mc-btn mc-btn--ghost" href="tel:+34614753187" aria-label="Llamar">Tel</a>
    </span>
  </div>
</div>

<script>
(function () {
  document.documentElement.className += ' js';

  var stage  = document.getElementById('stage');
  var stageN = document.getElementById('stage-n');
  var thumbs = document.getElementById('thumbs');
  var tabs   = document.querySelector('.car-tabs');
  var empty  = document.getElementById('car-empty');
  if (!stage || !thumbs) return;

  var EMPTY = {
    flaw:     'Todavía no he subido fotos de las imperfecciones de este coche. ' +
              'Si hay algo, te lo enseño antes de que vengas: pregúntame.',
    interior: 'Todavía no he clasificado las fotos del interior de este coche.',
    exterior: 'Todavía no he clasificado las fotos del exterior de este coche.'
  };

  function pick(a) {
    var was = thumbs.querySelector('.car-thumb.is-on');
    if (was) was.classList.remove('is-on');
    a.classList.add('is-on');
    stage.src = a.getAttribute('href');
    stageN.textContent = a.getAttribute('data-n');
    // keep the chosen thumbnail in view without yanking the page
    if (a.scrollIntoView) a.scrollIntoView({ block: 'nearest', inline: 'nearest' });
  }

  thumbs.addEventListener('click', function (e) {
    var a = e.target.closest('.car-thumb');
    if (!a) return;
    e.preventDefault();          // without JS this link opens the photograph, which is right
    pick(a);
  });

  /* ---- moving through the photographs -----------------------------------
     One list, one index, used by the stage arrows and by the full-screen view,
     so the two can never disagree about which photograph you are on. */
  function shown() {
    return Array.prototype.filter.call(thumbs.children, function (a) { return !a.hidden; });
  }
  function indexNow() {
    var on = thumbs.querySelector('.car-thumb.is-on');
    return Math.max(0, shown().indexOf(on));
  }
  function step(d) {
    var list = shown();
    if (list.length < 2) return;
    var i = (indexNow() + d + list.length) % list.length;   // wraps, both ways
    pick(list[i]);
    if (!view.hidden) paint();
  }

  // arrows on the stage itself
  var stageFig = stage.parentNode;
  ['prev', 'next'].forEach(function (dir) {
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'car-stage__nav car-stage__nav--' + dir;
    btn.setAttribute('aria-label', dir === 'prev' ? 'Anterior' : 'Siguiente');
    btn.addEventListener('click', function (e) { e.stopPropagation(); step(dir === 'prev' ? -1 : 1); });
    stageFig.appendChild(btn);
  });
  stageFig.classList.add('is-live');

  /* ---- full screen ------------------------------------------------------- */
  var view  = document.getElementById('view');
  var vImg  = document.getElementById('view-img');
  var vCnt  = document.getElementById('view-count');
  var lastFocus = null;

  function paint() {
    var list = shown(), i = indexNow(), a = list[i];
    if (!a) return;
    vImg.src = a.getAttribute('href');
    vImg.alt = a.getAttribute('aria-label') || '';
    vCnt.textContent = (i + 1) + ' / ' + list.length;
  }
  function open() {
    lastFocus = document.activeElement;
    paint();
    view.hidden = false;
    document.body.style.overflow = 'hidden';     // the page must not scroll behind it
    document.getElementById('view-x').focus();
  }
  function close() {
    view.hidden = true;
    document.body.style.overflow = '';
    if (lastFocus && lastFocus.focus) lastFocus.focus();
  }

  stage.addEventListener('click', open);
  stage.style.cursor = 'zoom-in';
  document.getElementById('view-x').addEventListener('click', close);
  document.getElementById('view-prev').addEventListener('click', function () { step(-1); });
  document.getElementById('view-next').addEventListener('click', function () { step(1); });
  // the backdrop closes, the photograph and the buttons do not
  view.addEventListener('click', function (e) { if (e.target === view) close(); });

  document.addEventListener('keydown', function (e) {
    if (view.hidden) {
      // on the page, arrows only move the gallery when the gallery has focus
      if (!stageFig.contains(document.activeElement) && !thumbs.contains(document.activeElement)) return;
    }
    if (e.key === 'Escape' && !view.hidden) { e.preventDefault(); close(); }
    else if (e.key === 'ArrowRight') { e.preventDefault(); step(1); }
    else if (e.key === 'ArrowLeft')  { e.preventDefault(); step(-1); }
  });

  /* ---- the calculator ----------------------------------------------------
     Price minus deposit, divided by months. No interest, and it says so: a
     figure that pretends to include finance would be wrong the moment a real
     lender quoted it. */
  var down = document.getElementById('calc-down');
  var mons = document.getElementById('calc-months');
  var sum  = document.getElementById('calc-sum');
  if (down && mons && sum) {
    var PRICE = {{ (int) $price['now'] }};
    var money = function (n) {
      return new Intl.NumberFormat('es-ES', { maximumFractionDigits: 0 }).format(n) + ' €';
    };
    var run = function () {
      var d = Math.min(Math.max(parseInt(down.value, 10) || 0, 0), PRICE);
      var m = parseInt(mons.value, 10) || 48;
      sum.textContent = money(Math.round((PRICE - d) / m));
    };
    down.addEventListener('input', run);
    mons.addEventListener('change', run);
    run();
  }

  /* The form composes a WhatsApp message rather than posting to an inbox nobody
     reads. He answers WhatsApp; that is where the conversation actually happens. */
  var form = document.getElementById('car-form');
  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var name = (document.getElementById('f-name').value || '').trim();
      var when = (document.getElementById('f-when').value || '').trim();
      var text = 'Hola' + (name ? ', soy ' + name : '') + '. Me interesa el '
               + @json($car->brand . ' ' . $car->model . ' ' . $car->year)
               + (when ? '. ¿Podría verlo ' + when + '?' : '. ¿Cuándo puedo verlo?');
      window.open('https://wa.me/34614753187?text=' + encodeURIComponent(text), '_blank', 'noopener');
    });
  }

  // a swipe across the full-screen photograph, which is how a phone expects to move
  var x0 = null;
  view.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
  view.addEventListener('touchend', function (e) {
    if (x0 === null) return;
    var dx = e.changedTouches[0].clientX - x0;
    x0 = null;
    if (Math.abs(dx) > 45) step(dx < 0 ? 1 : -1);
  }, { passive: true });

  if (tabs) {
    tabs.hidden = false;
    tabs.addEventListener('click', function (e) {
      var b = e.target.closest('.car-tab');
      if (!b) return;
      var g = b.getAttribute('data-group');

      Array.prototype.forEach.call(tabs.children, function (x) {
        var on = x === b;
        x.classList.toggle('is-on', on);
        x.setAttribute('aria-selected', on ? 'true' : 'false');
      });

      var first = null, n = 0;
      Array.prototype.forEach.call(thumbs.children, function (a) {
        var show = g === 'all' || a.getAttribute('data-group') === g;
        a.hidden = !show;
        if (show) { n++; if (!first) first = a; }
      });

      thumbs.scrollLeft = 0;
      if (first) { pick(first); }

      // A group with nothing in it says why, rather than showing an empty strip.
      // "Imperfecciones" is the one that matters: an empty flaws tab is a promise
      // the dealer has not kept yet, not a page fault.
      if (!n && EMPTY[g]) { empty.textContent = EMPTY[g]; empty.hidden = false; }
      else { empty.hidden = true; }
      thumbs.hidden = !n;
    });
  }
})();
</script>
</body>
</html>
