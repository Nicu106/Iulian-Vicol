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
@php $gone = ($car->status ?? '') === 'sold'; @endphp
<body class="bb cat {{ $gone ? 'car--sold' : '' }}">

@include('partials.head', ['current' => ''])

<main class="cat-wrap car">

  <a class="car-back mc-link" href="/catalogo">← Todos los coches</a>

  <h1 class="car-h">
    {{ $car->brand }} {{ $car->model }} <span>{{ $car->year }}</span>
  </h1>

  @if($gone)
    {{-- The state, in the reading order, for everyone. The fixed tab below is
         aria-hidden precisely so this is not announced twice. --}}
    <p class="car-gone">Vendido. Esta ficha se queda como registro: las fotos son
      las que se hicieron entonces, sin retocar.</p>
  @endif

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

      @include('partials.car-specs')

      <div class="car-act">
        {{-- "Me interesa" on a car that is already gone is the page telling a
             lie about itself. What is actually useful is the question the
             visitor really has. --}}
        <a class="mc-btn mc-btn--cta" href="https://wa.me/34614753187?text={{ urlencode($gone
            ? 'Hola, he visto el '.$car->brand.' '.$car->model.' '.$car->year.' que ya vendiste. ¿Tienes algo parecido?'
            : 'Hola, me interesa el '.$car->brand.' '.$car->model.' '.$car->year) }}">{{ $gone ? '¿Tienes algo parecido?' : 'WhatsApp' }}</a>
        <a class="mc-btn mc-btn--ghost" href="tel:+34614753187">Llamar</a>
      </div>

      {{-- Everything that comes with the car sits in this column, under the price
           and the buttons — the client: "trebuiau toate sa fie sub pret pe
           varianta pe pc, adica in partea dreapta sa fie toate". One place in the
           markup, not two: below 1000px .car-grid is a single column, so this
           simply falls under the buttons on a phone, which is where it reads
           anyway. --}}
      <section class="car-with" aria-labelledby="with-h">
        <h2 class="car-with__h" id="with-h">Lo que va con el coche</h2>

      <div class="car-with__grid">

        <article class="car-off">
          <h3 class="car-off__h">Garantía</h3>
          <p class="car-off__say">Un año va incluido con cada coche que vendo.
            Si quieres más tiempo, se amplía.</p>
          <ul class="car-off__steps">
            <li class="car-off__step car-off__step--inc">
              <span class="car-off__t">1 año</span>
              <span class="car-off__p">Incluido</span>
            </li>
            <li class="car-off__step">
              <span class="car-off__t">2 años</span>
              <span class="car-off__p">600 €</span>
            </li>
            <li class="car-off__step">
              <span class="car-off__t">3 años</span>
              <span class="car-off__p">900 €</span>
            </li>
          </ul>
          <p class="car-off__note">Es una garantía nacional: vale en toda España, no
            sólo en Málaga. Si el coche te falla lejos de aquí, te lo atienden allí.</p>
        </article>

        <article class="car-off">
          <h3 class="car-off__h">Mantenimiento</h3>
          <p class="car-off__say">Aceite, filtros y lo que toque, a precio cerrado.
            Este es opcional.</p>
          <ul class="car-off__steps">
            <li class="car-off__step">
              <span class="car-off__t">1 año</span>
              <span class="car-off__p">200 €</span>
            </li>
            <li class="car-off__step">
              <span class="car-off__t">2 años</span>
              <span class="car-off__p">400 €</span>
            </li>
          </ul>
          <p class="car-off__note">Sale más a cuenta que ir suelto al taller cada vez,
            y no tienes que acordarte de nada: te aviso yo cuando toca.</p>
        </article>

      </div>
      </section>
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

</main>

{{-- Full screen. Built empty; the script fills and opens it. --}}
<div class="car-view" id="view" hidden role="dialog" aria-modal="true" aria-label="Fotografía a pantalla completa">
  <button class="car-view__x" type="button" id="view-x" aria-label="Cerrar">&times;</button>
  <button class="car-view__nav car-view__nav--prev" type="button" id="view-prev" aria-label="Anterior"></button>
  <figure class="car-view__fig">
    <img class="car-view__img" id="view-img" src="" alt="">
    @if($gone)<span class="car-view__sold" aria-hidden="true">Vendido</span>@endif
  </figure>
  <button class="car-view__nav car-view__nav--next" type="button" id="view-next" aria-label="Siguiente"></button>
  <span class="car-view__count" id="view-count"></span>
</div>

@if($gone)<p class="car-tab-sold" aria-hidden="true">Vendido</p>@endif

@include('partials.foot')

<div class="cat-dock" role="complementary" aria-label="Contacto">
  <div class="mc-bar">
    @if($gone)
      {{-- Not a price and two buttons: this one is not for sale. On a phone this
           replaces the square stamp entirely — see car.css — so it is the only
           place the word appears down here, and it is not aria-hidden. --}}
      <span class="cat-dock__sold">Vendido</span>
      {{-- .mc-btn--cta IS the WhatsApp button: it carries the mark in a ::before as
           well as the green. A link to the catalogue wearing it says "this opens
           WhatsApp", which it does not. The base .mc-btn is the same solid button
           without the mark. --}}
      <a class="mc-btn cat-dock__back" href="/catalogo">Catálogo</a>
    @else
    <span class="mc-bar__pair">
      <span class="mc-bar__price">{{ $euros($car->price) }}</span>
      @if($car->mileage)<span class="mc-bar__km">{{ number_format($car->mileage, 0, ',', '.') }} km</span>@endif
    </span>
    <span class="mc-bar__act">
      {{-- The same question the side CTA asks, so the two do not disagree about
           what this page is for. --}}
      <a class="mc-btn mc-btn--cta" href="https://wa.me/34614753187{{ $gone
         ? '?text='.urlencode('Hola, he visto el '.$car->brand.' '.$car->model.' '.$car->year.' que ya vendiste. ¿Tienes algo parecido?')
         : '' }}">WhatsApp</a>
      <a class="mc-btn mc-btn--ghost" href="tel:+34614753187" aria-label="Llamar">Tel</a>
    </span>
    @endif
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
