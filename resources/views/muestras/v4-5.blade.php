{{-- 4.5 · Pares, in the site's own voice and in colour (2026-10-16). A copy of
     4.4 (frozen, tag opiniones-v4.4) with its own document, css and js
     (op45.css, op45.js).
     - the small things are the site's, as on /muestras/por-que/2.4 ("Lo cuentan
       ellos"): DM Sans only, 2.4's heading and pull-quote sizes, its hung
       quotation mark, "Ver más", the car page's viewer (scrim, round ×);
     - one pair at a time on a fixed stage. The heading and the arrows share a
       row outside it; the stage's size comes from the screen, never from a
       review, so nothing moves from one review to the next;
     - Pares' two cards: the photograph as a white print, the words on a card
       in the review's own colour, one lying under the other on a clear offset.
       A tap on the card behind brings it forward (it lifts, swings out on a
       short arc and lands on top while the other recedes); a tap on the
       photograph in front opens it whole;
     - every review has its own colour, taken from its photograph and snapped
       to one of seven deep tones (ReviewShowroomController::look); the
       section's ground, the light behind the pair and the stitching across it
       change with the review;
     - every photograph fills the same 3:4 frame: covering it when that crops
       13 % or less (the crop placed where the people and the car are), whole
       over a blurred copy of itself when not. --}}
@php
  $C = \App\Http\Controllers\ReviewShowroomController::class;
  $asset = fn ($p) => asset($p) . '?v=' . (@filemtime(public_path($p)) ?: 1);
  // for review the texts are English unless ?lang=es (ReviewShowroomController::EN)
  $t = fn ($es, $en_) => $en ? $en_ : $es;
  $looks = $reviews->mapWithKeys(fn ($r) => [$r->id => $C::look($r->img, $r->h ? $r->w / $r->h : .75)]);
  $first = $looks->first();
@endphp
<!DOCTYPE html>
<html lang="{{ $en ? 'en' : 'es' }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<title>Opiniones · {{ $variant[0] }}</title>
<meta name="theme-color" content="#05080F">
<link rel="icon" href="/favicon.ico" sizes="32x32">
<link rel="preload" href="/fonts/dm-sans/dm-sans-latin-normal.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
<link rel="stylesheet" href="{{ asset('css/mc-tokens.css') }}">
<link rel="stylesheet" href="{{ asset('css/brandbook.css') }}">
<link rel="stylesheet" href="{{ $asset('css/op45.css') }}">
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
      if (!e.filename || /op45/.test(e.filename) || e.filename === location.href) d.classList.remove('js');
    });
  })(document.documentElement);
</script>
</head>
<body class="bb ms-body op45">
<main class="op-sec" aria-labelledby="h-op">
  {{-- the ground: three layers in the reviews' colours (this one's, and either side's, painted ahead), crossfaded on opacity --}}
  <div class="op-bg is-on" aria-hidden="true" style="--field:{{ $first['field'] }};--glow:{{ $first['glow'] }};--tint:{{ $first['tint'] }}"><i class="op-bg__light"></i><i class="op-bg__stitch"></i></div>
  <div class="op-bg" aria-hidden="true"><i class="op-bg__light"></i><i class="op-bg__stitch"></i></div>
  <div class="op-bg" aria-hidden="true"><i class="op-bg__light"></i><i class="op-bg__stitch"></i></div>
  <div class="pr" data-pairs>
    {{-- the heading and the arrows share one row, outside the stage --}}
    <div class="pr__head op-wrap">
      <h2 class="pr__h" id="h-op">{{ $t('Lo cuentan ellos', 'In their own words') }}</h2>
      <div class="pr__nav">
        <button class="pr__arw pr__arw--prev" type="button" data-pr-step="-1" aria-label="{{ $t('Anterior', 'Previous') }}" aria-controls="pr-list"></button>
        <button class="pr__arw pr__arw--next" type="button" data-pr-step="1" aria-label="{{ $t('Siguiente', 'Next') }}" aria-controls="pr-list"></button>
      </div>
    </div>
    <div class="pr__stage op-wrap" data-pr-stage>
      <ul class="pr__list" role="list" id="pr-list">
        @foreach($reviews as $i => $r)
          @php $c = $C::pull($r->text, 150); $k = $looks[$r->id]; $amb = $k['fit'] === 'contain' ? $C::ambient($r->img) : null; @endphp
          <li class="pr__i{{ $i === 0 ? ' is-on' : '' }}{{ $c['cut'] ? ' is-cut' : '' }}" data-id="{{ $r->id }}"
              data-field="{{ $k['field'] }}" data-glow="{{ $k['glow'] }}" data-tint="{{ $k['tint'] }}"
              style="--card:{{ $k['card'] }};--tint:{{ $k['tint'] }};--field:{{ $k['field'] }}">
            <div class="pr__pair">
              <button class="pr__ph" type="button" aria-haspopup="dialog">
                <span class="ms-sr">{{ $t('Ver la foto entera', 'See the whole photo') }}: {{ $r->caption }}</span>
                <span class="pr__mat pr__mat--{{ $k['fit'] }}"@if($amb) style="--amb:url('{{ $amb }}')"@endif>
                  @if($k['fit'] === 'cover')
                    <x-img :src="$r->img" alt="" :sizes="'(min-width:700px) 440px, 72vw'" :max="1080" class="pr__img"
                           style=";object-position:{{ $k['fx'] }}% {{ $k['fy'] }}%" />
                  @else
                    @php $wn = $k['win']; @endphp
                    <span class="pr__win" style="--ww:{{ $wn['ww'] }}%;--wh:{{ $wn['wh'] }}%">
                      <x-img :src="$r->img" alt="" :sizes="'(min-width:700px) 440px, 72vw'" :max="1080" class="pr__img"
                             style=";--iw:{{ $wn['iw'] }}%;--ih:{{ $wn['ih'] }}%;--il:{{ $wn['il'] }}%;--it:{{ $wn['it'] }}%" />
                    </span>
                  @endif
                </span>
              </button>
              <div class="pr__txt">
                <button class="pr__swap" type="button" aria-pressed="false" aria-controls="pr-f{{ $r->id }}">
                  <span class="ms-sr">{{ $t('Leer entera la opinión de', 'Read the whole review by') }} {{ $r->name }}</span>
                </button>
                <div class="pr__rest">
                  <p class="pr__q"><span class="pr__qt">{{ $c['short'] }}</span></p>
                  <p class="pr__by">{{ $r->name }}</p>
                  <p class="pr__more" aria-hidden="true">{{ $t('Ver más', 'See more') }}</p>
                </div>
                <div class="pr__full" id="pr-f{{ $r->id }}" tabindex="-1">
                  <blockquote class="pr__fq">@foreach($r->paras as $p)<p>{{ $p }}</p>@endforeach</blockquote>
                  <p class="pr__fby">{{ $r->name }}</p>
                </div>
              </div>
            </div>
          </li>
        @endforeach
      </ul>
    </div>
    <p class="ms-sr" aria-live="polite" data-pr-live></p>
  </div>
</main>
{{-- the photograph, lifted out of its pair: whole, over the car page's scrim --}}
<dialog class="zm" id="pr-zoom" aria-label="{{ $t('Foto', 'Photo') }}">
  <img class="zm__img" alt="" decoding="async">
  <p class="zm__by"></p>
  <button class="op-x zm__x" type="button" aria-label="{{ $t('Cerrar', 'Close') }}">&times;</button>
</dialog>
<script type="application/json" id="ms-data">{!! $json !!}</script>
<script src="{{ $asset('js/op45.js') }}" defer onerror="document.documentElement.classList.remove('js')"></script>
</body>
</html>
