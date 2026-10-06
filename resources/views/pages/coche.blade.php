@extends('layouts.site')

@section('title', (\App\Support\Marques::for($car->brand)['name'] ?? $car->brand).' '.$car->model.' '.$car->year.' — IV MOTORCLASS')
@section('current', '')
{{-- the link preview: price and the facts that decide a viewing, and the cover photo --}}
@section('description', trim(implode(' · ', array_filter([
    ($car->status ?? '') === 'sold' ? 'Vendido' : ($car->price ? number_format((int) $car->price, 0, ',', '.') . ' €' : null),
    $car->mileage ? number_format($car->mileage, 0, ',', '.') . ' km' : null,
    $car->fuel ?: $car->fuel_type,
    $car->transmission,
    'Málaga',
]))) . (($car->status ?? '') === 'sold' ? '. Ya vendido: pregúntame por uno parecido.' : '. Fotos reales, garantía incluida. Escríbeme por WhatsApp.'))
@section('og_image', route('og.car', $car->slug) . '?v=' . (@filemtime(\App\Http\Controllers\ShareImageController::fsPath($car->cover_image) ?? '') ?: '1'))
{{-- A car that is gone dresses the whole document, header and footer
     included — see the SOLD block in car.css. --}}
@section('body', trim((($car->status ?? '') === 'sold' ? 'car--sold ' : '') . 'car--wall'))

@push('css')
<link rel="stylesheet" href="{{ asset('css/car.css') }}">
@endpush

@php $gone = ($car->status ?? '') === 'sold'; @endphp

@section('content')
<main class="cat-wrap car">

  {{-- The wall. The marque's own colour — the one its row carries in the
       catalogue — painted behind the top of the page with the same stroke, so a
       click from the blue BMW row lands on a blue BMW page. It runs edge to edge
       and stops part-way down the photograph: the car stands in front of its
       colour instead of being framed by it. Everything placed on it is white;
       everything with a colour of its own (the spec rows, the buttons, the red
       price on a phone) sits below its edge. See car.css, THE WALL. --}}
  <div class="car-top">
    <div class="car-wall" aria-hidden="true"
         style="{{ $marque ? '--brand:'.$marque['colour'].';--logo:url('.asset('img/marques/'.$marque['key'].'.svg').')' : '' }}">
      @if($marque)<span class="car-wall__mark"></span>@endif
    </div>

    {{-- Back to this marque's row, not to the top of the catalogue: that row is
         where the reader came from, and the catalogue centres it on arrival. --}}
    <a class="car-back mc-link" href="/catalogo{{ $marque ? '#marque-'.$marque['key'] : '' }}">← Todos los coches</a>

    <h1 class="car-h">
      {{ $brand }} {{ $car->model }} <span>{{ $car->year }}</span>
    </h1>

    @if($gone)
      {{-- The state, in the reading order, for everyone. The fixed tab below is
           aria-hidden precisely so this is not announced twice. Inside the top so
           the wall ends below it, not over it: a panel with its own ground, on the
           grey of a sold car's wall. --}}
      <p class="car-gone">Vendido. Esta ficha se queda como registro: las fotos son
        las que se hicieron entonces, sin retocar.</p>
    @endif
  </div>


  @php
    // How many blocks end up in the left column. The sidebar spans exactly that
    // many rows, so it stops dictating the height of row 1 — which is what left a
    // ~790px hole under the thumbnails while the panels ran on beside it.
    $mainRows = 1
      + (mb_strlen(trim(strip_tags((string) $car->description))) >= 40 ? 1 : 0)
      + ((is_array($car->features) && count($car->features)) ? 1 : 0);
  @endphp
  <div class="car-grid" style="--main-rows:{{ $mainRows }}">

    {{-- ---------------- the photographs ---------------- --}}
    <section class="car-gallery" aria-label="Fotografías">

      {{-- The one being looked at. Without JavaScript this is simply the first
           photograph and every other one is reachable below as its own link. --}}
      <figure class="car-stage">
        {{-- Measured: 286px wide at 320, 356 at 390, 702 at 768, and it stops
             growing at 750 from 1000px up, because the gallery column stops
             growing. --}}
        <x-img class="car-stage__img" id="stage"
               :src="$photos[0]['path']"
               :alt="$car->brand.' '.$car->model.' '.$car->year"
               sizes="(min-width:1000px) 750px, calc(100vw - 2rem)"
               :max="1600" :fallback="1080" :priority="true" />
        {{-- Arrows and the full-screen open are written by the script: without it the
             stage is a photograph, which is honest, rather than dead furniture. --}}
        <figcaption class="car-stage__count"><b id="stage-n">1</b> / {{ count($photos) }}</figcaption>
      </figure>

      {{-- Under the photograph, what the buyer is asking. Three questions, in the
           order they get asked: what does it look like, what is it like inside,
           and what is wrong with it. Written by the script — without JavaScript
           there is nothing to filter, so an inert row of buttons would be a lie. --}}
      {{-- Only once he has sorted some photographs: a row of "Exterior 0 ·
           Interior 0" filters nothing. Then a group with none is left out — except
           Imperfecciones, whose empty state says so honestly. --}}
      @php $sorted = $counts['exterior'] + $counts['interior'] + $counts['flaw'] > 0; @endphp
      @if($sorted)
      <div class="car-tabs" role="tablist" aria-label="Tipo de fotografía" hidden>
        <button class="car-tab is-on" type="button" role="tab" aria-selected="true"
                data-group="all">Todas <span>{{ $counts['all'] }}</span></button>
        @foreach($groups as $key => $label)
          @continue($counts[$key] === 0 && $key !== 'flaw')
          <button class="car-tab" type="button" role="tab" aria-selected="false"
                  data-group="{{ $key }}">{{ $label }} <span>{{ $counts[$key] }}</span></button>
        @endforeach
      </div>
      @endif

      {{-- Small, and scrolled. 58 photographs will not fit any other way, and a
           grid of 58 thumbnails is a wall, not a gallery. --}}
      <div class="car-thumbs" id="thumbs">
        @foreach($photos as $i => $p)
          {{-- href is a derivative, not the original. The script reads it to swap
               the stage and to fill the full-screen viewer, so every thumbnail
               press used to pull the raw file — 3.59 MB for the heaviest in this
               car's set. 1600 is what the viewer can actually show on the largest
               screen this site is used on; the original stays reachable, it is
               just not what a tap costs. --}}
          <a class="car-thumb {{ $i === 0 ? 'is-on' : '' }}"
             href="{{ \App\Support\Img::url($p['path'], 1600) ?? $p['path'] }}"
             data-stage="{{ \App\Support\Img::url($p['path'], 1080) ?? $p['path'] }}"
             {{-- The stage carries a srcset, and srcset BEATS src: setting only
                  .src on it changed the attribute and left the picture alone,
                  which is exactly what "the photo does not really change" looks
                  like. Each thumbnail carries the candidates for the slot it is
                  about to fill. --}}
             data-set="{{ \App\Support\Img::srcset($p['path'], 1600) }}"
             {{-- full screen: up to 2000, so a retina laptop gets the pixels it shows --}}
             data-vset="{{ \App\Support\Img::srcset($p['path'], 2000) }}"
             data-group="{{ $p['group'] ?? 'none' }}"
             data-n="{{ $p['n'] }}"
             aria-label="Foto {{ $p['n'] }}{{ $p['group'] ? ' · '.($groups[$p['group']] ?? '') : '' }}">
            {{-- 102px at 320, 118px from 1000 up. 160 covers it at DPR 2 and the
                 ladder rounds that to the 320 step. --}}
            <x-img :src="$p['path']" alt="" sizes="120px" :max="320" :fallback="320" />
          </a>
        @endforeach
      </div>

      {{-- The one group that can be empty and still has to say something. --}}
      <p class="car-empty" id="car-empty" hidden></p>
    </section>

    {{-- ---------------- the facts ---------------- --}}
    <aside class="car-side">
      <div class="car-price">
        @if($gone)<span class="car-price__sold">Vendido por</span>@endif
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
            ? 'Hola, he visto el '.$brand.' '.$car->model.' '.$car->year.' que ya vendiste. ¿Tienes algo parecido?'
            : 'Hola, me interesa el '.$brand.' '.$car->model.' '.$car->year.' — '.url()->current()) }}">{{ $gone ? '¿Tienes algo parecido?' : 'WhatsApp' }}</a>
        <a class="mc-btn mc-btn--ghost" href="tel:+34614753187">Llamar</a>
      </div>
      {{-- Most cars are decided by two people. The phone's own share sheet
           (WhatsApp, Messages…); where there is none, the link is copied. Shown
           by the script, so without one there is no dead button. --}}
      <button class="car-share" type="button" id="car-share" hidden
              data-title="{{ $brand }} {{ $car->model }} {{ $car->year }}">
        <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 3v12M7 8l5-5 5 5M5 13v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6"/></svg>
        <span>Compartir este coche</span>
      </button>

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

    {{-- Inside the grid, and after the column in the DOM. On a phone the grid is
         one track, so the order read is gallery → price → facts → buttons → what
         comes with it → this, which is the order that matters there. On a desktop
         the rule in car.css puts everything that is not .car-side into column 1,
         so these fall UNDER the gallery and fill the left side against the panels.
         Before this they sat below the grid, and the left column ended at the
         thumbnails while the right ran on for another 950px — the hole the client
         photographed. --}}
  @if(mb_strlen(trim(strip_tags((string) $car->description))) >= 40)
      <section class="car-sec car-text">
        <h2 class="car-h2">Lo que hay que saber</h2>
        {{-- The whole description, never cut: long ones fold after a few lines with
             "Leer más" (script below; without it the text is simply all there). --}}
        <p class="car-text__p" id="car-desc">{{ \Illuminate\Support\Str::of($car->description)->stripTags() }}</p>
        <button class="car-more mc-link" type="button" id="car-more" hidden>Leer más</button>
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

  </div>

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

  {{-- Not a dead end: what else is here now, same marque first. --}}
  @if($others->count())
    <section class="car-sec car-others" aria-labelledby="h-others">
      <div class="car-others__head">
        <h2 class="car-h2" id="h-others">{{ $gone ? 'Disponibles ahora' : 'Otros coches que tengo ahora' }}</h2>
        <a class="mc-link" href="/catalogo">Ver el catálogo →</a>
      </div>
      <div class="car-others__rail">
        @foreach($others as $o)
          @include('partials.card', ['car' => $o, 'kind' => 'available'])
        @endforeach
      </div>
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
@endsection

{{-- Below the footer: the full-screen photograph viewer, and the phone dock. --}}
@section('after')
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
      <a class="mc-btn mc-btn--cta" href="https://wa.me/34614753187?text={{ urlencode('Hola, me interesa el '.$brand.' '.$car->model.' '.$car->year.' — '.url()->current()) }}">WhatsApp</a>
      <a class="mc-btn mc-btn--ghost" href="tel:+34614753187" aria-label="Llamar">Tel</a>
    </span>
    @endif
  </div>
</div>
@endsection

@push('js')
<script>
(function () {
  var b = document.getElementById('car-share');
  if (!b) return;
  var url = location.origin + location.pathname, title = b.getAttribute('data-title');
  var label = b.querySelector('span'), was = label.textContent;
  if (!navigator.share && !(navigator.clipboard && window.isSecureContext)) return;
  b.hidden = false;
  b.addEventListener('click', function () {
    if (navigator.share) {
      navigator.share({ title: title, text: title + ' — IV MOTORCLASS', url: url }).catch(function () {});
      return;
    }
    navigator.clipboard.writeText(url).then(function () {
      label.textContent = 'Enlace copiado';
      setTimeout(function () { label.textContent = was; }, 2200);
    });
  });
})();
</script>
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

  /* Swap the stage without a gap.

     srcset has to move with src or nothing changes on screen. And the new file is
     decoded BEFORE it is shown: assigning straight to the visible element blanks
     it for as long as the download takes — on a laptop the first press of a
     thumbnail meant waiting for a 1080px file with an empty frame in the meantime.
     The old photograph stays up until the new one can be painted in one go.

     decode() can reject if the swap is overtaken by a faster press; the guard on
     `token` means only the most recent one is allowed to land. */
  var swapToken = 0;
  function swapStage(a) {
    var url = a.getAttribute('data-stage') || a.getAttribute('href');
    var set = a.getAttribute('data-set') || '';
    var token = ++swapToken;
    var next = new Image();
    if (set) { next.srcset = set; next.sizes = stage.sizes || ''; }
    next.src = url;
    var show = function () {
      if (token !== swapToken) { return; }
      if (set) { stage.srcset = set; } else { stage.removeAttribute('srcset'); }
      stage.src = url;
    };
    if (next.decode) { next.decode().then(show, show); } else { next.onload = show; next.onerror = show; }
  }

  /* Neighbours fetched before they are asked for: the next and previous
     photograph, at the size their slot will pick, at idle time. A press or a
     swipe then finds the file already here (measured: 270-1000 ms per press on
     4G before, 3.4 s on slow 4G in the viewer). Bytes are spent only while
     someone is actually looking through the photographs. */
  var warmed = {};
  var idle = window.requestIdleCallback || function (f) { return setTimeout(f, 200); };
  function warm(a, big) {
    if (!a) return;
    var set = a.getAttribute(big ? 'data-vset' : 'data-set') || '';
    var key = (big ? 'v' : 's') + (a.getAttribute('data-n') || '');
    if (warmed[key]) return; warmed[key] = 1;
    idle(function () {
      var im = new Image(); im.decoding = 'async';
      if (set) { im.srcset = set; im.sizes = big ? '100vw' : (stage.sizes || '100vw'); }
      im.src = big ? a.getAttribute('href') : (a.getAttribute('data-stage') || a.getAttribute('href'));
    });
  }
  function warmAround(big) {
    var list = shown(), i = indexNow(), n = list.length;
    if (n < 2) return;
    warm(list[(i + 1) % n], big); warm(list[(i - 1 + n) % n], big);
    if (n > 2) warm(list[(i + 2) % n], big);
  }

  function pick(a) {
    var was = thumbs.querySelector('.car-thumb.is-on');
    if (was) was.classList.remove('is-on');
    a.classList.add('is-on');
    swapStage(a);
    stageN.textContent = a.getAttribute('data-n');
    warmAround(false);
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
  var viewToken = 0;

  function paint() {
    var list = shown(), i = indexNow(), a = list[i];
    if (!a) return;
    // Same reason as the stage: assigning to the visible element empties it for
    // the length of the download. Here the frame is black, so an empty one is a
    // black hole in the middle of the screen between two photographs.
    var url = a.getAttribute('href'), vset = a.getAttribute('data-vset') || '';
    var token = ++viewToken;
    var next = new Image();
    if (vset) { next.srcset = vset; next.sizes = '100vw'; }
    next.src = url;
    var show = function () {
      if (token !== viewToken) { return; }
      if (vset) { vImg.sizes = '100vw'; vImg.srcset = vset; } else { vImg.removeAttribute('srcset'); }
      vImg.src = url;
    };
    warmAround(true);
    if (next.decode) { next.decode().then(show, show); } else { next.onload = show; next.onerror = show; }
    vImg.alt = a.getAttribute('aria-label') || '';
    vCnt.textContent = (i + 1) + ' / ' + list.length;
  }
  // iOS ignores overflow:hidden on <body> for touch: the page behind the viewer still
  // moved under a swipe. Pin the body where it is, and put the scroll back on close.
  var lockY = 0;
  function lock() { lockY = window.pageYOffset; document.body.style.top = -lockY + 'px'; document.body.classList.add('is-locked'); }
  function unlock() { document.body.classList.remove('is-locked'); document.body.style.top = ''; window.scrollTo(0, lockY); }
  /* The phone's Back closes the viewer instead of leaving the page: opening it
     adds a history entry; Back (popstate) closes it; closing by × or Esc takes
     that entry away again so the history stays as it was. */
  var pushed = false, onClose = null;
  function pushView() { try { history.pushState({ mcView: 1 }, ''); pushed = true; } catch (e) { pushed = false; } }
  function open() {
    lastFocus = document.activeElement;
    paint();
    view.hidden = false;
    lock();                                      // the page must not move behind it
    pushView();
    document.getElementById('view-x').focus();
  }
  function close(fromBack) {
    if (view.hidden) return;
    view.hidden = true;
    unlock();
    if (onClose) onClose();
    if (lastFocus && lastFocus.focus) lastFocus.focus();
    if (pushed) { pushed = false; if (fromBack !== true) history.back(); }
  }
  window.addEventListener('popstate', function () { if (!view.hidden) close(true); });

  stage.addEventListener('click', open);
  stage.style.cursor = 'zoom-in';
  document.getElementById('view-x').addEventListener('click', function () { close(); });
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

  var PHONE = window.matchMedia('(max-width: 768px)');
  // a swipe across the full-screen photograph, which is how a phone expects to move
  var x0 = null;
  view.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
  view.addEventListener('touchend', function (e) {
    if (x0 === null || PHONE.matches) return;    // on a phone the viewer is a swipeable strip (below)
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

  /* ---- the description folds when it is long ---------------------------- */
  (function () {
    var d = document.getElementById('car-desc'), b = document.getElementById('car-more'); if (!d || !b) return;
    d.classList.add('is-folded');
    if (d.scrollHeight <= d.clientHeight + 4) { d.classList.remove('is-folded'); return; }
    b.hidden = false; b.setAttribute('aria-expanded', 'false'); b.setAttribute('aria-controls', 'car-desc');
    b.addEventListener('click', function () { var open = d.classList.toggle('is-folded') === false;
      b.textContent = open ? 'Leer menos' : 'Leer más'; b.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (!open && d.getBoundingClientRect().top < 0) d.scrollIntoView({ block: 'start' }); });
  })();

  /* ---- the phone: a strip you swipe, and dots ---------------------------
     2026-10-05. The client: the arrows go on a phone (on the page AND full
     screen), dots like Instagram's show there is more, and — the bug — "the
     number in the corner changes but the photograph does not". That was the
     swap: the counter moved at once while the new file waited on decode(),
     which iOS Safari can hold for seconds or drop. Here the photographs ARE the
     strip; the browser scrolls it natively, and the number and the dots are
     read off what is on screen, so they cannot disagree with the picture. */
  // a phone turned to landscape (or a window resized) across 768px: one gallery or the other, not both
  if (PHONE.addEventListener && matchMedia('(pointer: coarse)').matches) PHONE.addEventListener('change', function () { location.reload(); });
  if (!PHONE.matches) return;
  var cap = stageFig.querySelector('.car-stage__count');
  var track = document.createElement('div'); track.className = 'car-track';
  stageFig.insertBefore(track, cap); stageFig.classList.add('is-track');
  var dots = document.createElement('div'); dots.className = 'car-dots'; dots.setAttribute('aria-hidden', 'true');
  stageFig.parentNode.insertBefore(dots, stageFig.nextSibling);
  var list = [], cur = 0;

  function drawDots(box, n, i) {
    // up to 7 visible; the far ones shrink, as Instagram's do, so 59 photographs stay a short row
    var html = '', from = Math.max(0, Math.min(i - 3, n - 7)), to = Math.min(n, from + 7);
    for (var k = from; k < to; k++) {
      var d = Math.abs(k - i), edge = (k === from && from > 0) || (k === to - 1 && to < n);
      html += '<i class="' + (k === i ? 'on' : '') + (edge ? ' sm' : d >= 3 ? ' sm' : '') + '"></i>';
    }
    box.innerHTML = n > 1 ? html : '';
  }
  function setIdx(i) {
    cur = i; var a = list[i]; if (!a) return;
    // the next two and the previous one load now, not when they slide in
    for (var k = i - 1; k <= i + 2; k++) { var el = track.children[k]; if (el && el.loading !== 'eager') el.loading = 'eager'; }
    cap.innerHTML = '<b id="stage-n">' + (i + 1) + '</b> / ' + list.length;
    stageN = document.getElementById('stage-n');
    drawDots(dots, list.length, i);
    var was = thumbs.querySelector('.car-thumb.is-on'); if (was) was.classList.remove('is-on');
    a.classList.add('is-on');
    thumbs.scrollTo({ left: a.offsetLeft - (thumbs.clientWidth - a.clientWidth) / 2, behavior: 'smooth' });
  }
  function slide(a, i, big) {
    var im = new Image(); im.className = big ? 'car-view__slide' : 'car-track__img';
    im.alt = a.getAttribute('aria-label') || ''; im.decoding = 'async'; im.loading = i < 2 ? 'eager' : 'lazy';
    // the strip is the stage's width, not the screen's: the same `sizes` picks the
    // same file the stage already downloaded, so slide 0 costs nothing
    var set = a.getAttribute(big ? 'data-vset' : 'data-set') || a.getAttribute('data-set');
    if (set) { im.srcset = set; im.sizes = big ? '100vw' : (stage.getAttribute('sizes') || '100vw'); }
    if (i === 0 && !big) { im.fetchPriority = 'high'; }
    im.src = big ? a.getAttribute('href') : (a.getAttribute('data-stage') || a.getAttribute('href'));
    return im;
  }
  function build() {
    // a group with no photographs keeps the strip it had: the page says why underneath
    // Imperfecciones with none: no photograph pretending to be one, only the message
    var none = !shown().length;
    stageFig.classList.toggle('is-empty', none); dots.hidden = none;
    if (none) return;
    list = shown(); track.innerHTML = '';
    list.forEach(function (a, i) { track.appendChild(slide(a, i, false)); });
    track.scrollLeft = 0; setIdx(0);
  }
  var raf = 0;
  track.addEventListener('scroll', function () {
    if (raf) return; raf = requestAnimationFrame(function () { raf = 0;
      var i = Math.round(track.scrollLeft / track.clientWidth); if (i !== cur && list[i]) setIdx(i); });
  }, { passive: true });
  // a thumbnail scrolls the strip to its photograph
  thumbs.addEventListener('click', function (e) {
    var a = e.target.closest('.car-thumb'); if (!a) return;
    e.preventDefault(); e.stopImmediatePropagation();
    var i = list.indexOf(a); if (i >= 0) track.scrollTo({ left: i * track.clientWidth, behavior: 'smooth' });
  }, true);
  if (tabs) tabs.addEventListener('click', function () { setTimeout(build, 0); });

  // full screen: the same strip, larger, on black, with its own dots; no arrows
  var vtrack = document.createElement('div'); vtrack.className = 'car-view__track';
  var vdots = document.createElement('div'); vdots.className = 'car-dots car-dots--view'; vdots.setAttribute('aria-hidden', 'true');
  view.appendChild(vtrack); view.appendChild(vdots); view.classList.add('is-track');
  var vcur = 0, vraf = 0;
  function vset(i) { vcur = i;
    for (var k = i - 1; k <= i + 2; k++) { var el = vtrack.children[k]; if (el && el.loading !== 'eager') el.loading = 'eager'; } vCnt.textContent = (i + 1) + ' / ' + list.length; drawDots(vdots, list.length, i); }
  vtrack.addEventListener('scroll', function () {
    if (vraf) return; vraf = requestAnimationFrame(function () { vraf = 0;
      var i = Math.round(vtrack.scrollLeft / vtrack.clientWidth); if (i !== vcur && list[i]) vset(i); });
  }, { passive: true });
  function vopen() {
    lastFocus = document.activeElement;
    vtrack.innerHTML = ''; list.forEach(function (a, i) { var im = slide(a, i, true); im.loading = Math.abs(i - cur) < 2 ? 'eager' : 'lazy'; vtrack.appendChild(im); });
    view.hidden = false; lock(); pushView();
    vtrack.scrollLeft = cur * vtrack.clientWidth; vset(cur);
    document.getElementById('view-x').focus();
  }
  track.addEventListener('click', vopen);
  // closing — ×, Esc or Back — brings the page strip to the photograph you stopped on
  onClose = function () { if (!list.length) return; track.scrollLeft = vcur * track.clientWidth; setIdx(vcur); };
  build();
})();
</script>
@endpush
