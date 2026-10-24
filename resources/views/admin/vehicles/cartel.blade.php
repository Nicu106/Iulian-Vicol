{{-- The A4 sheet that goes on the car's bonnet (Admin\SheetController::placard).

     Laid out like the coches.net "Imprimir vehículo" sheet Iulian prints today,
     on his instruction: his details beside the photograph, the name, the prices
     and the warranty, the spec grid (value above, word below), the extras with
     their prices beside the QR code, then the description and what it cost new.
     The first page is the one for the bonnet; the rest flows onto the next ones.

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
  html,body{ margin:0; }
  body{ font-family:"DM Sans",system-ui,-apple-system,"Segoe UI",Arial,sans-serif; color:var(--ink);
    -webkit-print-color-adjust:exact; print-color-adjust:exact; }
  .pc{ width:190mm; background:#fff; font-size:10pt; line-height:1.4; }
  /* page one is the bonnet's: exactly one A4 (297 - 10 - 14 mm), everything that
     matters on it, the QR code always. Extras that do not fit move to page two. */
  .p1{ height:273mm; display:flex; flex-direction:column; overflow:hidden; }
  .pc--rest{ break-before:page; }
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

  /* ---- extras beside the QR code; the list flows on to the next page ------ */
  .pc__h2{ margin:8mm 0 3mm; font-size:13pt; line-height:1.2; font-weight:700; break-after:avoid; }
  .pc--rest > .pc__h2:first-child, .pc--rest > section:first-child > .pc__h2:first-child{ margin-top:0; }
  .p1__eq{ flex:1 1 0; min-height:0; display:grid; grid-template-columns:minmax(0,1fr) 50mm; gap:8mm; margin-top:6mm; }
  .p1__list{ display:flex; flex-direction:column; min-height:0; }
  .p1__list .pc__h2{ margin-top:0; }
  .p1__list .pc__list{ flex:1 1 0; min-height:0; overflow:hidden; }
  .p1__more{ margin:2mm 0 0; font-size:9.5pt; font-weight:600; color:var(--ink2); }
  .p1__more[hidden]{ display:none; }
  .pc__qr{ align-self:start; padding:4.5mm 4mm 4mm; background:var(--panel); text-align:center; }
  .pc__qr b{ display:block; margin-bottom:3mm; font-size:12pt; line-height:1.2; }
  .pc__qr p{ margin:2.5mm 0 0; font-size:9.5pt; line-height:1.3; color:var(--ink2); }
  .pc__qr svg{ display:block; width:40mm; height:40mm; margin:0 auto; }
  .pc__qr svg path, .pc__qr svg rect{ fill:var(--ink); }
  .pc__list{ display:flow-root; list-style:none; margin:0; padding:0; }
  .pc__list li{ display:flex; justify-content:space-between; align-items:baseline; gap:4mm; padding:1.25mm 0;
    border-bottom:.5pt solid var(--hair); break-inside:avoid; }
  .pc__list li span:first-child::before{ content:"· "; color:var(--ink3); }
  .pc__list li span:last-child{ flex:0 0 auto; color:var(--ink2); }
  .pc__list--f{ columns:2; column-gap:7mm; }
  .pc__list--f li{ display:block; }

  /* ---- his words, and what it cost new ------------------------------------ */
  .pc__about{ columns:2; column-gap:8mm; }
  .pc__about p{ margin:0 0 2.5mm; font-size:9.5pt; line-height:1.5; orphans:3; widows:3; overflow-wrap:anywhere; }
  .pc__h2 + .pc__about{ margin-top:0; }
  .pc__deal{ max-width:150mm; break-inside:avoid; }
  .pc__deal p{ margin:0 0 1mm; color:var(--ink2); }
  .pc__row{ display:flex; justify-content:space-between; gap:6mm; padding:1.6mm 0; font-size:10.5pt; }
  .pc__row span:last-child{ font-weight:600; white-space:nowrap; }
  .pc__row--sum{ margin-top:2mm; border-top:1pt solid var(--ink); padding-top:2.5mm; }
  .pc__row--now{ font-size:13pt; font-weight:700; }
  .pc__row--now span:last-child{ font-weight:700; }
  .pc__row--save{ color:var(--ok); font-weight:700; }
  .pc__row--save span:last-child{ font-weight:700; }

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
    .pc + .pc{ margin-top:24px; min-height:297mm; }
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

  {{-- the list takes the room that is left; what does not fit is moved to page
       two by the script below (Chrome runs it before printing the PDF too) --}}
  <div class="p1__eq">
    <div class="p1__list">
      @if($extras)
        <h2 class="pc__h2">Equipamiento extra</h2>
        <ul class="pc__list num" data-fit>
          @foreach($extras as $x)
            <li><span>{{ $x['name'] }}</span>@if(($x['price'] ?? 0) > 0)<span>{{ $eurc($x['price']) }}</span>@endif</li>
          @endforeach
        </ul>
      @elseif($features)
        <h2 class="pc__h2">Equipamiento</h2>
        <ul class="pc__list" data-fit>
          @foreach($features as $x)<li><span>{{ \Illuminate\Support\Str::ucfirst($x) }}</span></li>@endforeach
        </ul>
      @endif
      <p class="p1__more" data-more hidden>y más en la página siguiente</p>
    </div>
    <aside class="pc__qr">
      <b>¿Te interesa?</b>
      {!! $qr !!}
      <p>Fotos, vídeo y todos los detalles, en ivmotorclass.com</p>
    </aside>
  </div>

</div>
</article>

{{-- the rest: what did not fit, what it cost new, and his description --}}
<article class="pc pc--rest" data-rest>
  <section data-rest-list hidden>
    <h2 class="pc__h2">{{ $extras ? 'Equipamiento extra' : 'Equipamiento' }}, continuación</h2>
    <ul class="pc__list {{ $extras ? 'num' : 'pc__list--f' }}"></ul>
  </section>

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

  @if($about)
    <h2 class="pc__h2">Descripción</h2>
    <div class="pc__about">
      @foreach($about as $p)<p>{!! nl2br(e($p)) !!}</p>@endforeach
    </div>
  @endif
</article>
</div>

<script>
  // Page one holds what fits of the list; the rest moves, in order, to page two,
  // and "y N más" says so. Measured after the fonts are in, because the line
  // heights depend on them; html[data-ready] tells the PDF printer it is done.
  (function () {
    var root = document.documentElement;
    var MM = 96 / 25.4;
    function fit() {
      // 1. the QR code must be whole on page one. When the page is fuller than
      //    usual (two-line name, a row of history, a fourth price) the photograph
      //    gives up the height, at its own proportions, down to 56 mm — it is
      //    made smaller, never cut.
      var p1 = document.querySelector('.p1'), qr = document.querySelector('.pc__qr'), fig = document.querySelector('[data-ratio]');
      if (p1 && qr && fig) {
        // on a phone the sheet is zoomed: measured boxes are in screen pixels,
        // the sizes set below are in the sheet's own
        var z = fig.currentCSSZoom || 1;
        var over = (qr.getBoundingClientRect().bottom - p1.getBoundingClientRect().bottom) / z + 1;
        if (over > 0) {
          var r = parseFloat(fig.getAttribute('data-ratio')) || 4 / 3;
          var h = Math.max(56 * MM, fig.getBoundingClientRect().height / z - over);
          fig.style.height = h + 'px'; fig.style.width = Math.min(122 * MM, h * r) + 'px';
          fig.parentNode.style.setProperty('--ph', h + 'px');
        }
      }
      // 2. the list: what fits stays, the rest moves on in order
      var ul = document.querySelector('[data-fit]'), more = document.querySelector('[data-more]');
      var rest = document.querySelector('[data-rest-list]'), page2 = document.querySelector('[data-rest]');
      if (ul && rest) {
        more.hidden = false;                       // its line is part of the room
        var limit = ul.getBoundingClientRect().bottom + 0.5, moved = [];
        while (ul.lastElementChild && ul.lastElementChild.getBoundingClientRect().bottom > limit) {
          moved.unshift(ul.removeChild(ul.lastElementChild));
        }
        var to = rest.querySelector('ul');
        moved.forEach(function (li) { to.appendChild(li); });
        if (moved.length) {
          more.textContent = 'y ' + moved.length + ' más en la página siguiente';
          rest.hidden = false;
        } else {
          more.hidden = true;
        }
      }
      // nothing for page two at all: no blank second sheet
      if (page2 && !page2.querySelector('section:not([hidden]), .pc__about')) page2.remove();
      root.setAttribute('data-ready', '1');
    }
    var go = function () { (document.fonts && document.fonts.ready ? document.fonts.ready : Promise.resolve()).then(fit); };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', go); else go();
@unless($inline)
    // a phone: the whole sheet scaled to the screen's width
    function zoom() {
      var k = Math.min(1, (window.innerWidth - 16) / (210 * 96 / 25.4));
      root.style.setProperty('--k', k.toFixed(4));
    }
    zoom(); window.addEventListener('resize', zoom);
@endunless
  })();
</script>
</body>
</html>
