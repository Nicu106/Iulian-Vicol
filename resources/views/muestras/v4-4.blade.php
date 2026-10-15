{{-- 4.4 · Pares, editorial (2026-10-16). A copy of 4 (frozen, tag
     opiniones-v4.0) with its own document, css and js (op44.css, op44.js).
     A magazine page. The heading at display size in Fraunces (OFL,
     self-hosted), its second sentence in the one accent (--mc-price). Each
     card leads with the review's first sentence as a pull-quote
     (ReviewShowroomController::pull) that wraps around the corner of the
     photograph and runs on beneath it; the whole review is a tap away, as in
     4.0. Structure from thin rules, not from boxes or labels. Two pairs in
     view on a computer, so the quotes can be large. --}}
@php
  $C = \App\Http\Controllers\ReviewShowroomController::class;
  $asset = fn ($p) => asset($p) . '?v=' . (@filemtime(public_path($p)) ?: 1);
  // for review the texts are English unless ?lang=es (ReviewShowroomController::EN)
  $t = fn ($es, $en_) => $en ? $en_ : $es;
@endphp
<!DOCTYPE html>
<html lang="{{ $en ? 'en' : 'es' }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<title>Opiniones · {{ $variant[0] }}</title>
<meta name="theme-color" content="#F6F4EF">
<link rel="icon" href="/favicon.ico" sizes="32x32">
<link rel="preload" href="/fonts/fraunces/fraunces-latin-opsz-normal.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
<link rel="stylesheet" href="{{ asset('css/mc-tokens.css') }}">
<link rel="stylesheet" href="{{ asset('css/brandbook.css') }}">
<link rel="stylesheet" href="{{ $asset('css/op44.css') }}">
{{-- "js" only while the script is healthy (see muestras/layout.blade.php) --}}
<script>
  (function (d) {
    d.classList.add('js');
    try {
      var m = /[?&]embed=(\d)/.exec(location.search);
      if (m && m[1] === '1' && window.frameElement && window.parent.location.origin === location.origin) {
        d.classList.add('is-embed');
        var P = window.parent, F = window.frameElement;
        var view = function () {
          d.style.setProperty('--vh', (P.innerHeight / 100) + 'px');
          d.style.setProperty('--vis-top', Math.max(0, -F.getBoundingClientRect().top) + 'px');
        };
        view();
        P.addEventListener('scroll', view, { passive: true });
        P.addEventListener('resize', view);
        window.msView = function () {
          var r = F.getBoundingClientRect();
          return { top: Math.max(0, -r.top), h: P.innerHeight, frameTop: r.top };
        };
      }
    } catch (e) {}
    window.addEventListener('error', function (e) {
      if (!e.filename || /op44/.test(e.filename) || e.filename === location.href) d.classList.remove('js');
    });
  })(document.documentElement);
</script>
</head>
<body class="bb ms-body op44">
<main class="ms-sec ms-v4" aria-labelledby="h-op">
  @unless($embed)
  <div class="ms-wrap ms-head">
    <h2 class="ms-h2" id="h-op">{{ $t('La confianza se gana.', 'Trust is earned.') }} <span>{{ $t('Ellos te cuentan cómo.', 'They tell you how.') }}</span></h2>
    <p class="ms-lede">{{ $t('Conoce a quienes ya han comprado su coche en IV Motorclass. Estas son sus fotos el día de la entrega y sus opiniones sobre la experiencia.',
      'Meet the people who have already bought their car at IV Motorclass. These are their photos on delivery day and what they say about the experience.') }}</p>
  </div>
  @else
  <h2 class="ms-sr" id="h-op">{{ $t('La confianza se gana. Ellos te cuentan cómo.', 'Trust is earned. They tell you how.') }}</h2>
  @endunless
  <div class="pr" data-pairs>
    <ul class="pr__row" role="list" data-pr-row id="pr-row">
      @foreach($reviews as $i => $r)
        @php $c = $C::pull($r->text, 150); @endphp
        <li class="pr__i" data-id="{{ $r->id }}">
          <div class="pr__pair">
            <button class="pr__ph" type="button" aria-haspopup="dialog" data-pr-zoom>
              <span class="ms-sr">{{ $t('Ver la foto entera', 'See the whole photo') }}: {{ $r->caption }}</span>
              <span class="pr__mat" style="--r:{{ $r->box }}">
                <x-img :src="$r->img" alt="" :sizes="'(min-width:1264px) 240px, (min-width:1000px) calc((100vw - 160px) * .2067), (min-width:700px) calc((100vw - 96px) * .31), 216px'" :max="720" class="pr__img" />
              </span>
            </button>
            <div class="pr__txt{{ $c['cut'] ? ' is-cut' : '' }}">
              <button class="pr__swap" type="button" aria-pressed="false" aria-controls="pr-f{{ $r->id }}">
                <span class="ms-sr">{{ $t('Leer entera la opinión de', 'Read the whole review by') }} {{ $r->name }}</span>
              </button>
              <div class="pr__rest">
                <span class="pr__wrap" aria-hidden="true"></span>
                <p class="pr__q">{{ $c['short'] }}</p>
                <p class="pr__by"><span><b>{{ $r->name }},</b> {{ $t('con su coche', 'with their car') }}</span></p>
                @if($c['cut'])<p class="pr__more" aria-hidden="true">{{ $t('Leer más', 'Read more') }}</p>@endif
              </div>
              <div class="pr__full" id="pr-f{{ $r->id }}" tabindex="-1">
                <blockquote class="pr__fq">@foreach($r->paras as $p)<p>{{ $p }}</p>@endforeach</blockquote>
                <p class="pr__fby">{{ $r->caption }}</p>
              </div>
            </div>
          </div>
        </li>
      @endforeach
    </ul>
    <div class="pr__nav">
      <button class="ms-rnd pr__arw" type="button" data-pr-step="-1" aria-label="{{ $t('Anteriores', 'Previous') }}" aria-controls="pr-row">
        <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path d="M19 12H5M11 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
      </button>
      <button class="ms-rnd pr__arw" type="button" data-pr-step="1" aria-label="{{ $t('Siguientes', 'Next') }}" aria-controls="pr-row">
        <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
      </button>
    </div>
  </div>
</main>
{{-- the photograph, lifted out of its pair: whole, over the dimmed page --}}
<dialog class="zm" id="pr-zoom" aria-label="{{ $t('Foto', 'Photo') }}">
  <img class="zm__img" alt="" decoding="async">
  <p class="zm__by"></p>
  <button class="ms-rnd ms-rnd--glass zm__x" type="button" aria-label="{{ $t('Cerrar', 'Close') }}">
    <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
  </button>
</dialog>
<script type="application/json" id="ms-data">{!! $json !!}</script>
<script src="{{ $asset('js/op44.js') }}" defer onerror="document.documentElement.classList.remove('js')"></script>
</body>
</html>
