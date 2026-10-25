{{-- The A4 sheet that goes on the car's bonnet (Admin\SheetController::placard).

     Laid out like the coches.net "Imprimir vehículo" sheet Iulian prints today,
     on his instruction: his details beside the photograph, the name, the prices
     and the warranty, the spec grid (value above, word below), the extras with
     their prices, the description and what it cost new, with the QR code.
     All of it on one A4 page, whatever the car carries: the lower part (the
     equipment, the description, the offer) is set at the largest type that
     fits, measured in the browser before printing (the script at the end).

     The photograph is never cut: its box takes the photo's own proportions
     (SheetController::placardPhoto), so the whole car is on the paper. The old
     placard drew it in a fixed 80 mm band with object-fit:cover and lost the
     roof and both ends of the car.

     Always today's data. Prints in black and white too: nothing is said by colour
     alone. On screen it is the sheet on a grey desk with Imprimir and Descargar
     PDF; in the PDF ($inline) fonts and images are inside the file. --}}
@php
  $eur = fn ($n) => number_format((float) $n, 0, ',', '.') . ' €';
  // extras keep their cents, as on the sheet he prints today
  $eurc = fn ($n) => number_format((float) $n, fmod((float) $n, 1) ? 2 : 0, ',', '.') . ' €';
  $title = trim($brand . ' ' . $car->model);
  $years = intdiv($warranty, 12);
  $phone = '614 75 31 87';
  $mail = 'jvmotorclass@gmail.com';
  $lblName = ['0' => 'Cero', 'ECO' => 'ECO', 'C' => 'C', 'B' => 'B'];
@endphp
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Cartel · {{ $title }} {{ $car->year }}</title>
@if($inline)
  <style>{!! $fonts !!}</style>
@else
  <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
@endif
<style>
  :root{
    --ink:#111C2E; --ink2:#475467; --ink3:#5D6B80;
    --rule:#C9D2DF; --hair:#E3E8F0; --panel:#EEF1F5; --ok:#017B37;
  }
  /* the margins are the page's; the footer is drawn in them, on every page */
  @page{
    size:A4; margin:10mm 10mm 14mm;
    @bottom-left{ content:"IV MOTORCLASS  ·  ivmotorclass.com  ·  WhatsApp {{ $phone }}";
      font:500 8pt "DM Sans",Arial,sans-serif; color:#5D6B80; vertical-align:middle; }
    @bottom-right{ content:counter(page) " / " counter(pages);
      font:500 8pt "DM Sans",Arial,sans-serif; color:#5D6B80; vertical-align:middle; }
  }
  *{ box-sizing:border-box; }
  /* phones enlarge the type of wide blocks on their own: not on a sheet of paper */
  html{ -webkit-text-size-adjust:100%; text-size-adjust:100%; }
  html,body{ margin:0; }
  body{ font-family:"DM Sans",system-ui,-apple-system,"Segoe UI",Arial,sans-serif; color:var(--ink);
    -webkit-print-color-adjust:exact; print-color-adjust:exact; }
  .pc{ width:190mm; background:#fff; font-size:10pt; line-height:1.4; }
  /* one A4 (297 - 10 - 14 mm), everything on it, never a second sheet */
  .p1{ height:273mm; display:flex; flex-direction:column; overflow:hidden; }
  .num{ font-variant-numeric:tabular-nums; }

  /* ---- his details, and the car whole ------------------------------------- */
  .pc__top{ display:flex; min-height:var(--ph); }
  .pc__dealer{ flex:1 1 auto; min-width:0; display:flex; flex-direction:column; justify-content:space-between;
    gap:4mm; padding:6mm 5mm 5.5mm 6.5mm; background:var(--panel); }
  .pc__logo{ display:block; width:31mm; height:auto; }
  .pc__who{ margin:0; font-size:9.5pt; line-height:1.5; color:var(--ink2); }
  .pc__who b{ font-weight:700; color:var(--ink); }
  .pc__photo{ flex:0 0 auto; margin:0; display:flex; align-items:center; justify-content:center; background:var(--panel); }
  .pc__photo img{ display:block; width:100%; height:100%; object-fit:contain; }
  .pc__top--bare .pc__dealer{ flex-direction:row; align-items:center; }

  /* ---- the name, the money, the warranty --------------------------------- */
  .pc__name{ margin:6.5mm 0 0; font-size:23pt; line-height:1.1; font-weight:700; letter-spacing:-.02em; }
  .pc__prices{ display:grid; grid-auto-flow:column; grid-auto-columns:minmax(0,1fr); gap:5mm; margin-top:5mm; }
  .pc__k{ display:block; font-size:10pt; color:var(--ink2); }
  .pc__v{ display:block; margin-top:.5mm; font-size:23pt; line-height:1.05; font-weight:700; letter-spacing:-.02em; white-space:nowrap; }
  .pc__n{ display:block; margin-top:1.5mm; font-size:10pt; color:var(--ink2); }
  .pc__n--ok{ color:var(--ok); font-weight:700; }
  .pc__n s{ text-decoration-thickness:1px; }
  .pc__v--was{ color:var(--ink2); }

  /* ---- the spec grid: value above, the word below ------------------------ */
  .pc__specs{ display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:4mm 4mm; margin:6.5mm 0 0; }
  .pc__specs div{ min-width:0; }
  .pc__specs dd{ margin:0; font-size:12.5pt; line-height:1.2; font-weight:700; overflow-wrap:anywhere; }
  .pc__specs dt{ margin-top:.6mm; font-size:9.5pt; color:var(--ink2); }
  .pc__lbl{ display:inline-grid; place-items:center; min-width:7.5mm; height:7.5mm; padding:0 1.2mm; border-radius:3.75mm;
    font-size:8.5pt; font-weight:700; color:#fff; border:.6pt solid rgba(0,0,0,.25); vertical-align:middle; }
  .pc__lbl--C{ background:#3FAE49; } .pc__lbl--B{ background:#F7D117; color:#111; }
  .pc__lbl--0{ background:#1F6FB8; } .pc__lbl--ECO{ background:linear-gradient(90deg,#1F6FB8 50%,#3FAE49 50%); font-size:7pt; }
  .pc__specs dd .pc__lbl + span{ margin-left:1.5mm; font-size:10pt; font-weight:600; }

  .pc__facts{ margin:5mm 0 0; padding:2.2mm 0; border-top:.6pt solid var(--rule); border-bottom:.6pt solid var(--rule);
    font-size:10pt; font-weight:600; line-height:1.6; }
  .pc__facts span{ white-space:nowrap; }
  .pc__facts span + span::before{ content:"·"; margin:0 2mm; color:var(--ink3); font-weight:400; }

  /* ---- the lower part: the equipment and his words on the left, the QR code
     and what it cost new on the right. Everything in it is sized in em from
     --b, which the script sets to the largest that fits (9.5pt down to 6.5pt). */
  /* with a great deal to say, the spaces between the top blocks close up */
  .p1--tight .pc__name{ margin-top:4mm; }
  .p1--tight .pc__prices{ margin-top:3mm; }
  .p1--tight .pc__specs{ margin-top:4mm; gap:2.5mm 3mm; grid-template-columns:repeat(6,minmax(0,1fr)); }
  .p1--tight .pc__facts{ margin-top:3mm; padding:1.5mm 0; }
  .p1--tight .p1__low{ margin-top:4mm; }
  .p1--tight .pc__dealer{ padding:4mm 4mm 4mm 5mm; gap:3mm; }
  .p1--tight .pc__logo{ width:24mm; }
  /* and, last, the big type in the top a size down */
  .p1--tighter .pc__name{ font-size:19pt; margin-top:3mm; }
  .p1--tighter .pc__v{ font-size:19pt; }
  .p1--tighter .pc__specs dd{ font-size:11pt; }
  .p1--tighter .pc__specs dt, .p1--tighter .pc__k, .p1--tighter .pc__n{ font-size:8.5pt; }
  .p1__low{ flex:1 1 0; min-height:0; display:grid; grid-template-columns:minmax(0,1fr) 50mm;
    grid-template-rows:minmax(0,1fr); gap:7mm; margin-top:6mm; padding-bottom:1.5mm;
    font-size:var(--b,9.5pt); line-height:1.4; }
  /* not clipped: the screen (where it is measured) and the printer round a
     little differently, and a hair more goes into the 1.5 mm below */
  .p1__main, .p1__side{ min-width:0; min-height:0; }
  .p1__side{ display:flex; flex-direction:column; gap:5mm; }
  .pc__h2{ margin:1.15em 0 .5em; font-size:1.3em; line-height:1.2; font-weight:700; }
  .p1__main > .pc__h2:first-child, .p1__side .pc__h2:first-child{ margin-top:0; }
  /* the equipment as one run of text: a list of fifty short lines would take the page */
  .pc__feats{ margin:0; }
  .pc__feats span{ white-space:nowrap; }
  /* the extras carry a price: two columns, the price on the right */
  .pc__list{ list-style:none; margin:0; padding:0; columns:2; column-gap:7mm; }
  .pc__list li{ display:flex; justify-content:space-between; align-items:baseline; gap:3mm; padding:.12em 0;
    border-bottom:.5pt solid var(--hair); break-inside:avoid; }
  .pc__list li span:first-child::before{ content:"· "; color:var(--ink3); }
  .pc__list li span:last-child{ flex:0 0 auto; color:var(--ink2); }
  .pc__about{ columns:3; column-gap:6mm; }
  .pc__about{ orphans:2; widows:2; overflow-wrap:anywhere; }
  .pc__nl, .pc__gap{ display:block; }
  .pc__gap{ height:.6em; }
  /* the description going on under the QR code: the fourth column, starting lower */
  .pc__about--on{ columns:auto; }
  .pc__about--on:empty{ display:none; }
  .pc__about--on > .pc__nl:first-child, .pc__about--on > .pc__gap:first-child{ display:none; }
  .pc__deal{ margin-top:auto; }
  /* the QR code goes with the type: 40 mm at 9.5pt, 27 mm at the smallest */
  .pc__qr{ flex:0 0 auto; padding:1.35em 1.2em 1.2em; background:var(--panel); text-align:center; line-height:1.3; }
  .pc__qr b{ display:block; margin-bottom:.9em; font-size:1.26em; line-height:1.2; }
  .pc__qr p{ margin:.75em 0 0; color:var(--ink2); }
  .pc__qr svg{ display:block; width:12em; height:12em; margin:0 auto; }
  .pc__qr svg path, .pc__qr svg rect{ fill:var(--ink); }
  .pc__deal p{ margin:0 0 .3em; color:var(--ink2); }
  .pc__row{ display:flex; justify-content:space-between; align-items:baseline; gap:2mm; padding:.3em 0; }
  .pc__row span:last-child{ font-weight:600; white-space:nowrap; }
  .pc__row--sum{ margin-top:.3em; border-top:1pt solid var(--ink); padding-top:.5em; }
  .pc__row--now{ font-size:1.15em; font-weight:700; }
  .pc__row--now span:last-child, .pc__row--save span:last-child{ font-weight:700; }
  .pc__row--save{ color:var(--ok); font-weight:700; flex-wrap:wrap; }
  .pc__cut{ color:var(--ink2); font-weight:600; }

  /* ---- on screen: the sheet on a desk, and the two buttons ----------------- */
  @media screen{
    body{ background:#D9DFE8; min-height:100vh; }
    .bar{ position:sticky; top:0; z-index:5; display:flex; flex-wrap:wrap; align-items:center; gap:8px;
      padding:10px 16px; background:#fff; border-bottom:1px solid var(--rule); }
    .bar a, .bar button{ display:inline-flex; align-items:center; min-height:44px; padding:0 18px; border-radius:10px;
      font:600 15px/1 "DM Sans",system-ui,sans-serif; text-decoration:none; cursor:pointer; border:1px solid var(--ink); }
    .bar__back{ color:var(--ink); background:#fff; border-color:transparent !important; padding-left:0 !important; margin-right:auto; }
    .bar__print{ background:var(--ink); color:#fff; }
    .bar__pdf{ background:#fff; color:var(--ink); }
    .bar__msg{ flex-basis:100%; margin:0; font:500 14px/1.4 "DM Sans",system-ui,sans-serif; color:#A8330F; }
    .desk{ padding:24px 16px 48px; }
    /* the paper: 210 mm with its 10 mm margins drawn in */
    .pc{ width:210mm; margin:0 auto; padding:10mm 10mm 14mm; box-shadow:0 6px 30px rgba(4,11,31,.18); }
    /* a phone: the whole sheet scaled to the screen's width */
    @media (max-width:820px){ .desk{ padding:12px 0 32px; } .pc{ zoom:var(--k,1); } }
  }
  @media print{ .bar{ display:none !important; } .desk{ padding:0; } }
</style>
</head>
<body>
@unless($inline)
  <nav class="bar" aria-label="Cartel">
    <a class="bar__back" href="{{ route('admin.vehicles.index') }}">← Coches</a>
    <button class="bar__print" type="button" onclick="window.print()">Imprimir</button>
    <a class="bar__pdf" href="{{ route('admin.vehicles.placard-pdf', $car->slug) }}">Descargar PDF</a>
    @if(session('status'))<p class="bar__msg">{{ session('status') }}</p>@endif
  </nav>
@endunless
<div class="desk">
<article class="pc">
<div class="p1">

  <header class="pc__top {{ $photo ? '' : 'pc__top--bare' }}" style="--ph:{{ $photo['h'] ?? 0 }}mm">
    <div class="pc__dealer">
      @if($logo)<img class="pc__logo" src="{{ $logo }}" alt="IV Motorclass">@endif
      <p class="pc__who"><b class="num">{{ $phone }}</b><br>{{ $mail }}<br>Con cita previa · Málaga</p>
    </div>
    @if($photo)
      <figure class="pc__photo" data-ratio="{{ $photo['ratio'] }}" style="width:{{ $photo['w'] }}mm;height:{{ $photo['h'] }}mm"><img src="{{ $photo['src'] }}" alt=""></figure>
    @endif
  </header>

  <h1 class="pc__name">{{ $title }}</h1>

  <div class="pc__prices num">
    <div>
      <span class="pc__k">Precio al contado</span>
      <b class="pc__v">{{ $eur($price) }}</b>
      <span class="pc__n pc__n--ok">Impuesto incluido</span>
      @if($before)<span class="pc__n">Antes <s>{{ $eur($before) }}</s></span>@endif
    </div>
    @if($financed)
      <div>
        <span class="pc__k">Precio financiado</span>
        <b class="pc__v">{{ $eur($financed) }}</b>
        <span class="pc__n">Pregunta las condiciones</span>
      </div>
    @endif
    <div>
      <span class="pc__k">Garantía</span>
      <b class="pc__v">{{ $years === 1 ? '1 año' : $years . ' años' }}</b>
      <span class="pc__n">{{ $warranty }} meses · en toda España</span>
    </div>
    @if($new)
      <div>
        <span class="pc__k">Nuevo{{ $new['year'] ? ', en ' . $new['year'] : '' }}</span>
        <b class="pc__v pc__v--was">{{ $eur($new['total']) }}</b>
        <span class="pc__n pc__n--ok">Hoy, un {{ $new['pct'] }} % menos</span>
      </div>
    @endif
  </div>

  <dl class="pc__specs num">
    @foreach($specs as $k => $v)
      <div>
        @if($k === 'Etiqueta')
          <dd><span class="pc__lbl pc__lbl--{{ $v }}">{{ $v }}</span>@if($v === '0')<span>Cero emisiones</span>@endif</dd>
        @else
          <dd>{{ $v }}</dd>
        @endif
        <dt>{{ $k }}</dt>
      </div>
    @endforeach
  </dl>

  @if($facts)
    <p class="pc__facts">@foreach($facts as $f)<span>{{ $f }}</span>@endforeach</p>
  @endif

  <div class="p1__low">
    <div class="p1__main" data-main>
      @if($extras)
        <h2 class="pc__h2">Equipamiento extra</h2>
        <ul class="pc__list num">
          @foreach($extras as $x)
            <li><span>{{ $x['name'] }}</span>@if(($x['price'] ?? 0) > 0)<span>{{ $eurc($x['price']) }}</span>@endif</li>
          @endforeach
        </ul>
      @elseif($features)
        <h2 class="pc__h2">Equipamiento</h2>
        <p class="pc__feats">@foreach($features as $x)<span>{{ \Illuminate\Support\Str::ucfirst($x) }}</span>@if(! $loop->last) · @endif @endforeach</p>
      @endif

      @if($about)
        <h2 class="pc__h2">Descripción</h2>
        {{-- a sentence each, so what does not fit beside the QR code can go on under
             it; between paragraphs a gap, between his lines a break (no whitespace
             between the pieces: they are moved one by one) --}}
        @php
          $bits = [];
          foreach ($about as $pi => $para) {
              foreach (array_values(array_filter(array_map('trim', explode("\n", $para)), 'strlen')) as $li => $line) {
                  if ($pi || $li) $bits[] = '<span class="' . ($li ? 'pc__nl' : 'pc__gap') . '"></span>';
                  $parts = preg_split('/(?<=[.!?…:])\s+/u', $line, -1, PREG_SPLIT_NO_EMPTY);
                  foreach ($parts as $k => $t) $bits[] = '<span class="pc__s">' . e($t) . ($k < count($parts) - 1 ? ' ' : '') . '</span>';
              }
          }
        @endphp
        <div class="pc__about" data-about>{!! implode('', $bits) !!}</div>
      @endif
    </div>

    <div class="p1__side" data-side>
      <aside class="pc__qr">
        <b>¿Te interesa?</b>
        {!! $qr !!}
        <p>Fotos, vídeo y todos los detalles, en ivmotorclass.com</p>
      </aside>
      <div class="pc__about pc__about--on" data-flow></div>
      @if($new)
        <section class="pc__deal num">
          <h2 class="pc__h2">Desglose de la oferta</h2>
          @if($new['base'] > 0)
            <p>PVP vehículo nuevo en España{{ $new['year'] ? ' (año ' . $new['year'] . ')' : '' }}</p>
            <div class="pc__row"><span>{{ $title }}</span><span>{{ $eurc($new['base']) }}</span></div>
            @if($new['extras'] > 0)
              <div class="pc__row"><span>Total equipamiento extra añadido</span><span>{{ $eurc($new['extras']) }}</span></div>
            @endif
          @endif
          <div class="pc__row pc__row--sum"><span>Total{{ $new['base'] > 0 ? '' : ', nuevo' . ($new['year'] ? ' en ' . $new['year'] : '') }}</span><span>{{ $eurc($new['total']) }}</span></div>
          <div class="pc__row pc__row--now"><span>Precio oferta</span><span>{{ $eur($price) }}</span></div>
          <div class="pc__row pc__row--save"><span>Te ahorras, frente a nuevo</span><span>{{ $eurc($new['save']) }} · {{ $new['pct'] }} %</span></div>
        </section>
      @endif
    </div>
  </div>

</div>
</article>
</div>

<script>
  // Everything on one page. The top (photo, name, prices, specs) stays as it is;
  // the lower part takes the largest type that fits, 9.5pt down to 8pt, and the
  // description lines that do not fit beside the QR code go on under it. When
  // even 8pt does not fit, the photograph gives up height first, at its own
  // proportions, down to 56 mm (smaller, never cut); then the spaces in the top
  // close up and the type goes on down to 6.5pt, the photo to 48 mm, the big
  // type in the top a size down. Past that the end gives way, and says so. Measured
  // after the fonts are in, because the line heights depend on them;
  // html[data-ready] tells the PDF printer it is done.
  (function () {
    var root = document.documentElement;
    var q = function (s) { return document.querySelector(s); };
    var zoom = function () {};
    function fit() {
      var p1 = q('.p1'), low = q('.p1__low'), main = q('[data-main]'), side = q('[data-side]');
      if (!(p1 && low && main && side)) return;
      root.style.setProperty('--k', '1');          // measured at the paper's own size, not the phone's
      var about = q('[data-about]'), flow = q('[data-flow]'), fig = q('[data-ratio]');
      var r = fig ? parseFloat(fig.getAttribute('data-ratio')) || 4 / 3 : 0;
      var h0 = fig ? parseFloat(fig.style.height) : 0;
      var over = function (el) { return el.scrollHeight > el.clientHeight + 1; };
      var bad = function () { return over(main) || over(side) || over(p1); };
      function lay(pt, h) {
        low.style.setProperty('--b', pt + 'pt');
        if (fig) {
          fig.style.height = h + 'mm'; fig.style.width = Math.min(122, h * r) + 'mm';
          fig.parentNode.style.setProperty('--ph', h + 'mm');
        }
        if (about && flow) {
          while (flow.firstElementChild) about.appendChild(flow.firstElementChild);
          var move = function () { flow.insertBefore(about.lastElementChild, flow.firstElementChild); };
          while (over(main) && about.children.length > 1) {
            move();
            while (about.lastElementChild && !about.lastElementChild.classList.contains('pc__s')) move();   // a break goes with what follows it
          }
        }
        return !bad();
      }
      // the largest size between lo and hi, in quarter points, that fits; 0 if none
      // (smaller type never fits worse, so halving the range finds it)
      function best(lo, hi, h) {
        if (!lay(lo, h)) return 0;
        var a = lo * 4, b = hi * 4;
        while (a < b) { var m = Math.ceil((a + b) / 2); if (lay(m / 4, h)) a = m; else b = m - 1; }
        lay(a / 4, h);
        return a / 4;
      }
      var size = best(8, 9.5, h0);
      for (var h = h0 - 4; !size && fig && h > 56; h -= 4) size = best(8, 9.5, h);
      if (!size) { p1.classList.add('p1--tight'); size = best(6.5, 8, fig ? 56 : 0); }
      if (!size && fig) size = best(6.5, 8, 48);
      if (!size) { p1.classList.add('p1--tighter'); size = best(6.5, 8, fig ? 48 : 0); }
      if (!size) { lay(6.5, fig ? 48 : 0); cut(); }
      root.setAttribute('data-size', (size || 6.5) + 'pt');

      // the last resort: the description's end, then the equipment's, each saying so
      function cut() {
        var last = function () { return (flow && flow.lastElementChild) || (about && about.lastElementChild); };
        var n = function () { return (about ? about.children.length : 0) + (flow ? flow.children.length : 0); };
        var b, gone = false;
        while (bad() && n() > 1) { last().remove(); gone = true; }
        while ((b = last()) && n() > 1 && !b.classList.contains('pc__s')) b.remove();
        if ((b = last()) && (gone || bad())) {
          var words = b.textContent.split(/\s+/);
          var put = function () {
            b.textContent = words.join(' ') + '… ';
            var more = document.createElement('span');
            more.className = 'pc__cut'; more.textContent = 'Sigue en ivmotorclass.com';
            b.appendChild(more);
          };
          put();
          while (bad() && words.length > 6) { words.splice(-4); put(); }
        }
        var list = main.querySelector('ul, .pc__feats');
        var items = list ? list.children : [], k = 0;
        while (list && bad() && items.length > 1) {
          var it = items[items.length - 1];
          if (it.previousSibling && it.previousSibling.nodeType === 3) it.previousSibling.remove();
          it.remove(); k++;
        }
        if (k) {
          var tail = document.createElement(list.tagName === 'UL' ? 'li' : 'span');
          tail.className = 'pc__cut'; tail.textContent = (list.tagName === 'UL' ? '' : ' · ') + 'y ' + k + ' más en ivmotorclass.com';
          list.appendChild(tail);
        }
      }
    }
    var go = function () {
      (document.fonts && document.fonts.ready ? document.fonts.ready : Promise.resolve()).then(function () {
        try { fit(); } finally { zoom(); root.setAttribute('data-ready', '1'); }
      });
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', go); else go();
@unless($inline)
    // a phone: the whole sheet scaled to the screen's width (the layout width:
    // innerWidth grows with a sheet wider than the screen and never shrinks it)
    zoom = function () {
      var k = Math.min(1, (document.documentElement.clientWidth - 16) / (210 * 96 / 25.4));
      root.style.setProperty('--k', k.toFixed(4));
    };
    zoom(); window.addEventListener('resize', function () { zoom(); });
@endunless
  })();
</script>
</body>
</html>
