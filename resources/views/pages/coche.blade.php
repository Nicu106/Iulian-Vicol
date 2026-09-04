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
<link rel="stylesheet" href="{{ asset('css/car.css') }}">
</head>
<body class="bb cat">

<header class="mc-head">
  <div class="mc-head__in cat-wrap" style="padding-block:0">
    <a class="mc-logo" href="/brandbook">IV&nbsp;MOTORCLASS</a>
    <nav class="mc-nav" style="margin-right:var(--s-4)">
      <a class="mc-nav__i" href="/catalogo">Coches</a>
      <a class="mc-nav__i" href="/brandbook">Quién soy</a>
    </nav>
    <a class="mc-btn mc-btn--cta" href="https://wa.me/34614753187">WhatsApp</a>
  </div>
</header>

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
        <span class="mc-price">{{ $euros($car->price) }}</span>
        @if($car->mileage)<span class="mc-km">{{ number_format($car->mileage, 0, ',', '.') }} km</span>@endif
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
    <section class="car-text">
      <h2 class="car-h2">Lo que hay que saber</h2>
      <p>{{ \Illuminate\Support\Str::of($car->description)->stripTags()->limit(700) }}</p>
    </section>
  @endif

  @if(is_array($car->features) && count($car->features))
    <section class="car-text">
      <h2 class="car-h2">Equipamiento</h2>
      <ul class="car-feats">
        @foreach(array_slice($car->features, 0, 24) as $f)<li>{{ $f }}</li>@endforeach
      </ul>
      @if(count($car->features) > 24)
        <p class="car-more">y {{ count($car->features) - 24 }} más — pregúntame por cualquiera.</p>
      @endif
    </section>
  @endif
</main>

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

  // arrow keys walk the strip, because a gallery that needs a mouse is not a gallery
  thumbs.addEventListener('keydown', function (e) {
    if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
    var shown = Array.prototype.filter.call(thumbs.children, function (a) { return !a.hidden; });
    var i = shown.indexOf(document.activeElement.closest('.car-thumb'));
    if (i < 0) return;
    var next = shown[i + (e.key === 'ArrowRight' ? 1 : -1)];
    if (!next) return;
    e.preventDefault(); next.focus(); pick(next);
  });

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
