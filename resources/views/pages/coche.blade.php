@extends('layouts.site')

@section('title', $car->brand.' '.$car->model.' '.$car->year.' — IV MOTORCLASS')
@section('current', '')
{{-- A car that is gone dresses the whole document, header and footer
     included — see the SOLD block in car.css. --}}
@section('body', ($car->status ?? '') === 'sold' ? 'car--sold' : '')

@push('css')
<link rel="stylesheet" href="{{ asset('css/car.css') }}">
@endpush

@php $gone = ($car->status ?? '') === 'sold'; @endphp

@section('content')
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

  @php
    // How many blocks end up in the left column. The sidebar spans exactly that
    // many rows, so it stops dictating the height of row 1 — which is what left a
    // ~790px hole under the thumbnails while the panels ran on beside it.
    $mainRows = 1
      + ($car->description ? 1 : 0)
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

    {{-- Inside the grid, and after the column in the DOM. On a phone the grid is
         one track, so the order read is gallery → price → facts → buttons → what
         comes with it → this, which is the order that matters there. On a desktop
         the rule in car.css puts everything that is not .car-side into column 1,
         so these fall UNDER the gallery and fill the left side against the panels.
         Before this they sat below the grid, and the left column ended at the
         thumbnails while the right ran on for another 950px — the hole the client
         photographed. --}}
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
      <a class="mc-btn mc-btn--cta" href="https://wa.me/34614753187{{ $gone
         ? '?text='.urlencode('Hola, he visto el '.$car->brand.' '.$car->model.' '.$car->year.' que ya vendiste. ¿Tienes algo parecido?')
         : '' }}">WhatsApp</a>
      <a class="mc-btn mc-btn--ghost" href="tel:+34614753187" aria-label="Llamar">Tel</a>
    </span>
    @endif
  </div>
</div>
@endsection

@push('js')
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

  function pick(a) {
    var was = thumbs.querySelector('.car-thumb.is-on');
    if (was) was.classList.remove('is-on');
    a.classList.add('is-on');
    swapStage(a);
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
  var viewToken = 0;

  function paint() {
    var list = shown(), i = indexNow(), a = list[i];
    if (!a) return;
    // Same reason as the stage: assigning to the visible element empties it for
    // the length of the download. Here the frame is black, so an empty one is a
    // black hole in the middle of the screen between two photographs.
    var url = a.getAttribute('href');
    var token = ++viewToken;
    var next = new Image();
    next.src = url;
    var show = function () { if (token === viewToken) { vImg.src = url; } };
    if (next.decode) { next.decode().then(show, show); } else { next.onload = show; next.onerror = show; }
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
@endpush
