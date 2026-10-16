{{-- 4.6 · Pares in a row (2026-10-16). Its own document, css and js (op46.css,
     op46.js); nothing here is shared with 4.0, 4.4 or 4.5.
     The client: "like the old one, photos and reviews in a row, but more of them
     in view, a carousel, as inspired as 4.5: it has to show that these people are
     MANY, not one".
     - every review a Pares pair: the photograph as a white print, its words on a
       navy card lying under the print's corner on the diagonal. A tap on the card
       behind brings it forward (it lifts, swings out and lands on top); a tap on
       the card in front, or on "Ver más", opens the whole review with the whole
       photograph;
     - all of them in one row, newest first, that the thumb, a mouse drag, the
       arrows or the keyboard move pair by pair. Always more than fits: 1.3 pairs
       on a phone, 2.3 on a tablet, 3.45 on a laptop, 4.3 on a large screen; past
       1920 the window stops growing and stays centred;
     - a fixed structure: the heading and the arrows share a row that never
       depends on a review; every pair is the same size (whole pixels, from the
       screen only); every quote holds the same number of lines;
     - the brand's colours only (mc-tokens): the --mc-deep ground, --mc-navy
       cards, white prints and words, one accent (--mc-blue-light, on navy only).
       Depth is value, not hue: deep < navy < white. Each card carries a 12 %
       whisper of its photograph's own hue mixed into the navy ($look, cached per
       photo path + mtime in storage/app/public/op46), so neighbours are not
       stamped from one template, and no new hue ever appears;
     - every photograph fills one 3:4 window: covering it when that trims 10 % or
       less (the trim placed where the people and the car are), otherwise whole,
       at even margins, over a blurred copy of itself under a navy veil. --}}
@php
  $C = \App\Http\Controllers\ReviewShowroomController::class;
  $asset = fn ($p) => asset($p) . '?v=' . (@filemtime(public_path($p)) ?: 1);
  // for review the texts are English unless ?lang=es (ReviewShowroomController::EN)
  $t = fn ($es, $en_) => $en ? $en_ : $es;

  // newest first, as the owner adds them
  $born = \App\Models\Testimonial::whereIn('id', $reviews->pluck('id'))->pluck('created_at', 'id');
  $at = fn ($r) => optional($born[$r->id] ?? null)->timestamp ?? 0;
  $rows = $reviews->sort(fn ($a, $b) => [$at($b), $b->id] <=> [$at($a), $a->id])->values();

  /* ---- each review's colour and how its photograph sits in the 3:4 window ----
     Read once from the 320px derivative (already upright), cached as JSON per
     photo path + mtime: a new or replaced photograph is measured on its first
     view and never again; nobody runs anything. */
  $FRAME = 0.75; $COVER = 0.10;
  $NAVY = '#0E2E57'; $ON2 = '#A6B6C8';   // --mc-navy, --mc-on-navy-2 (mc-tokens.css)
  $MIX = 0.12;                           // how much of the photo's hue the navy takes
  $oklab = function ($r, $g, $b) {
    $f = fn ($v) => ($v /= 255) <= 0.04045 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
    [$r, $g, $b] = [$f($r), $f($g), $f($b)];
    $l = (0.4122214708 * $r + 0.5363325363 * $g + 0.0514459929 * $b) ** (1 / 3);
    $m = (0.2119034982 * $r + 0.6806995451 * $g + 0.1073969566 * $b) ** (1 / 3);
    $s = (0.0883024619 * $r + 0.2817188376 * $g + 0.6299787005 * $b) ** (1 / 3);
    return [0.2104542553 * $l + 0.7936177850 * $m - 0.0040720468 * $s,
            1.9779984951 * $l - 2.4285922050 * $m + 0.4505937099 * $s,
            0.0259040371 * $l + 0.7827717662 * $m - 0.8086757660 * $s];
  };
  // OKLab to #RRGGBB (clamped into sRGB)
  $hex = function ($L, $a, $b) {
    $l = ($L + 0.3963377774 * $a + 0.2158037573 * $b) ** 3;
    $m = ($L - 0.1055613458 * $a - 0.0638541728 * $b) ** 3;
    $s = ($L - 0.0894841775 * $a - 1.2914855480 * $b) ** 3;
    $rgb = [4.0767416621 * $l - 3.3077115913 * $m + 0.2309699292 * $s,
            -1.2684380046 * $l + 2.6097574011 * $m - 0.3413193965 * $s,
            -0.0041960863 * $l - 0.7034186147 * $m + 1.7076147010 * $s];
    return '#' . implode('', array_map(function ($v) {
      $v = max(0, min(1, $v));
      $v = $v <= 0.0031308 ? 12.92 * $v : 1.055 * $v ** (1 / 2.4) - 0.055;
      return sprintf('%02X', (int) round($v * 255));
    }, $rgb));
  };
  $look = function (string $img, float $ratio) use ($FRAME, $COVER, $NAVY, $ON2, $MIX, $oklab, $hex, $C) {
    $mt = (int) @filemtime(public_path(ltrim($img, '/')));
    $file = storage_path('app/public/op46/' . substr(md5('op46b|' . $img . '|' . $mt . '|' . $ratio), 0, 16) . '.json');
    if (is_file($file) && ($k = json_decode((string) @file_get_contents($file), true))) {
      return $k;
    }
    $h = 255.0; $c = 0.0; $lum = .52; $fx = 50.0; $fy = 50.0;
    $crop = $ratio < $FRAME ? 1 - $ratio / $FRAME : 1 - $FRAME / $ratio;
    $fit = $crop <= $COVER ? 'cover' : ($ratio < $FRAME ? 'tall' : 'wide');
    $small = \App\Support\Img::url($img, 320);
    $path = $small ? storage_path('app/public/cache/' . basename((string) parse_url($small, PHP_URL_PATH))) : null;
    if ($path && is_file($path) && ($im = @imagecreatefromwebp($path))) {
      $w = imagesx($im); $hh = imagesy($im);
      $tw = 64; $th = max(3, (int) round($hh * $tw / max(1, $w)));
      $sm = imagecreatetruecolor($tw, $th);
      imagecopyresampled($sm, $im, 0, 0, 0, 0, $tw, $th, $w, $hh);
      $sa = $sb = $sw = $sl = 0.0; $px = [];
      for ($y = 0; $y < $th; $y++) {
        for ($x = 0; $x < $tw; $x++) {
          $rgb = imagecolorat($sm, $x, $y);
          $px[$y][$x] = [($rgb >> 16) & 255, ($rgb >> 8) & 255, $rgb & 255];
          [$L, $A, $B] = $oklab(...$px[$y][$x]);
          $k = sqrt($A * $A + $B * $B);
          $sa += $k * $A; $sb += $k * $B; $sw += $k; $sl += $L;
        }
      }
      imagedestroy($im); imagedestroy($sm);
      $lum = $sl / ($tw * $th);
      if ($sw > 0) {
        $A = $sa / $sw; $B = $sb / $sw; $c = sqrt($A * $A + $B * $B);
        if ($c >= 0.008) { $h = fmod(rad2deg(atan2($B, $A)) + 360, 360); }
      }
      // the trim of a covering picture goes where it is busiest: edges, and skin x3
      if ($fit === 'cover' && $crop > 0.002) {
        $rows = array_fill(0, $th, 0.0); $cols = array_fill(0, $tw, 0.0);
        $l = fn ($q) => 0.3 * $q[0] + 0.59 * $q[1] + 0.11 * $q[2];
        for ($y = 1; $y < $th - 1; $y++) {
          for ($x = 1; $x < $tw - 1; $x++) {
            [$r, $g, $b] = $px[$y][$x];
            $e = abs($l($px[$y][$x + 1]) - $l($px[$y][$x - 1])) + abs($l($px[$y + 1][$x]) - $l($px[$y - 1][$x]));
            $skin = $r > 95 && $g > 40 && $b > 20 && max($r, $g, $b) - min($r, $g, $b) > 15 && abs($r - $g) > 15 && $r > $g && $r > $b;
            $v = $e + ($skin ? 120 : 0);
            $rows[$y] += $v; $cols[$x] += $v;
          }
        }
        $best = function (array $line, float $keep) {
          $n = count($line); $win = max(1, (int) round($n * $keep)); $span = $n - $win;
          if ($span <= 0) return 50.0;
          $sum = array_sum(array_slice($line, 0, $win)); $bs = $sum; $bo = 0;
          for ($o = 1; $o <= $span; $o++) { $sum += $line[$o + $win - 1] - $line[$o - 1]; if ($sum > $bs) { $bs = $sum; $bo = $o; } }
          return round($bo / $span * 100, 1);
        };
        if ($ratio < $FRAME) { $fy = $best($rows, 1 - $crop); } else { $fx = $best($cols, 1 - $crop); }
      }
    }
    // the card: --mc-navy with 12 % of the photograph's hue (at a middle tone) mixed
    // in, in OKLab; a picture with almost no colour leaves the navy as it is
    $n = $oklab(hexdec(substr($NAVY, 1, 2)), hexdec(substr($NAVY, 3, 2)), hexdec(substr($NAVY, 5, 2)));
    $card = $NAVY;
    if ($c >= 0.008) {
      $p = [.55, .12 * cos(deg2rad($h)), .12 * sin(deg2rad($h))];
      $card = $hex($n[0] + ($p[0] - $n[0]) * $MIX, $n[1] + ($p[1] - $n[1]) * $MIX, $n[2] + ($p[2] - $n[2]) * $MIX);
    }
    $out = ['card' => $card, 'hue' => round($h), 'chroma' => round($c, 4),
            'crWhite' => round($C::contrast('#FFFFFF', $card), 2), 'crOn2' => round($C::contrast($ON2, $card), 2),
            'fit' => $fit, 'crop' => round($crop, 3), 'fx' => $fx, 'fy' => $fy];
    @file_put_contents($file, json_encode($out));
    return $out;
  };
@endphp
<!DOCTYPE html>
<html lang="{{ $en ? 'en' : 'es' }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<title>Opiniones · {{ $variant[0] }}</title>
<meta name="theme-color" content="#040B1F">
<link rel="icon" href="/favicon.ico" sizes="32x32">
<link rel="preload" href="/fonts/dm-sans/dm-sans-latin-normal.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
<link rel="stylesheet" href="{{ asset('css/mc-tokens.css') }}">
<link rel="stylesheet" href="{{ asset('css/brandbook.css') }}">
<link rel="stylesheet" href="{{ $asset('css/op46.css') }}">
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
      if (!e.filename || /op46/.test(e.filename) || e.filename === location.href) d.classList.remove('js');
    });
  })(document.documentElement);
</script>
</head>
<body class="bb ms-body op46">
<main class="o6" aria-labelledby="h-op">
  <div class="o6__win">
    {{-- the heading and the arrows: one row, outside the carousel, never moved by it --}}
    <div class="o6__head">
      <h2 class="o6__h" id="h-op">{{ $t('Lo cuentan ellos', 'In their own words') }}</h2>
      <div class="o6__nav">
        <button class="o6__arw o6__arw--prev" type="button" data-o6-step="-1" aria-label="{{ $t('Anteriores', 'Previous') }}" aria-controls="o6-row" aria-disabled="true"></button>
        <button class="o6__arw o6__arw--next" type="button" data-o6-step="1" aria-label="{{ $t('Siguientes', 'Next') }}" aria-controls="o6-row"></button>
      </div>
    </div>
    <div class="o6__row" id="o6-row" tabindex="0" role="region" aria-label="{{ $t('Opiniones de clientes', 'Customer reviews') }}" data-o6-row>
      <ul class="o6__list" role="list">
        @foreach($rows as $i => $r)
          @php
            $k = $look($r->img, round($r->h ? $r->w / $r->h : .75, 4));
            $amb = $k['fit'] !== 'cover' ? $C::ambient($r->img) : null;
            $ratio = $r->h ? $r->w / $r->h : .75;
          @endphp
          <li class="o6__i" data-id="{{ $r->id }}"
              aria-label="{{ $r->name }}"
              style="--card:{{ $k['card'] }}">
            <div class="o6__pair">
              <button class="o6__ph" type="button" aria-haspopup="dialog"
                      data-front="{{ $t('Ver entera la foto y la opinión de', 'See the whole photo and review by') }} {{ $r->name }}"
                      data-back="{{ $t('Traer delante la foto de', 'Bring forward the photo of') }} {{ $r->name }}"
                      aria-label="{{ $t('Ver entera la foto y la opinión de', 'See the whole photo and review by') }} {{ $r->name }}">
                <span class="o6__mat o6__mat--{{ $k['fit'] }}" style="--r:{{ round($ratio, 4) }}@if($amb);--amb:url('{{ $amb }}')@endif">
                  <x-img :src="$r->img" alt="" :sizes="'(min-width:1000px) 232px, (min-width:700px) 196px, 168px'" :max="720" class="o6__img"
                         style=";object-position:{{ $k['fx'] }}% {{ $k['fy'] }}%" />
                </span>
              </button>
              <div class="o6__q">
                <button class="o6__swap" type="button" aria-pressed="false"
                        data-front="{{ $t('Leer entera la opinión de', 'Read the whole review by') }} {{ $r->name }}"
                        data-back="{{ $t('Traer delante la opinión de', 'Bring forward the review by') }} {{ $r->name }}"
                        aria-label="{{ $t('Traer delante la opinión de', 'Bring forward the review by') }} {{ $r->name }}"></button>
                <div class="o6__body">
                  <p class="o6__qt">{{ $r->text }}</p>
                  <p class="o6__by">{{ $r->name }}</p>
                  <button class="o6__more" type="button" aria-haspopup="dialog" tabindex="-1">{{ $t('Ver más', 'See more') }}<span class="ms-sr">: {{ $r->name }}</span></button>
                </div>
                {{-- without the script: the whole review, scrolling inside the card --}}
                <div class="o6__all">@foreach($r->paras as $p)<p>{{ $p }}</p>@endforeach</div>
              </div>
            </div>
          </li>
        @endforeach
      </ul>
    </div>
    {{-- where the row is: the part in view, along all of it --}}
    <div class="o6__bar" aria-hidden="true"><i></i></div>
  </div>
</main>
{{-- the whole review, with the whole photograph --}}
<dialog class="o6-ov" id="o6-ov" aria-labelledby="o6-ov-by">
  <div class="o6-ov__dim" aria-hidden="true"></div>
  <div class="o6-ov__panel">
    <div class="o6-ov__ph"><img alt="" decoding="async"></div>
    <div class="o6-ov__txt">
      <blockquote class="o6-ov__q"></blockquote>
      <p class="o6-ov__by" id="o6-ov-by"></p>
    </div>
  </div>
  <button class="o6-x" type="button" aria-label="{{ $t('Cerrar', 'Close') }}">&times;</button>
</dialog>
<script type="application/json" id="ms-data">{!! $json !!}</script>
<script src="{{ $asset('js/op46.js') }}" defer onerror="document.documentElement.classList.remove('js')"></script>
</body>
</html>
