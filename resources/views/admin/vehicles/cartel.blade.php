{{-- The A4 placard that goes on the car's bonnet (Admin\SheetController::placard).
     Read standing at the car, a metre or two away: the name and the price first and
     big, then the six facts that decide a viewing, the rest of the specs, what it
     cost new against what it costs today, its factory extras, its past, and a QR
     code to its page (photos, video, everything). Always today's data.

     One page, always: the photograph and the extras list have fixed heights, the
     extras are the most expensive ones (the rest counted), the facts one line.
     Prints in black and white too: nothing is said by colour alone. On screen it is
     the page on a grey desk with Imprimir and Descargar PDF; in the PDF
     ($inline) the fonts and the photograph are inside the file. --}}
@php
  $eur = fn ($n) => number_format((float) $n, 0, ',', '.') . ' €';
  $name = trim($car->model);
  $top = array_slice($specs, 0, 5, true);           // Año, Kilómetros, Combustible, Cambio, Potencia
  $rest = array_slice($specs, 5, null, true);
  $shown = array_slice($extras, 0, 8);
  $more = count($extras) - count($shown);
  $phone = '614 75 31 87';
@endphp
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Cartel · {{ $brand }} {{ $name }} {{ $car->year }}</title>
@if($inline)
  <style>{!! $fonts !!}</style>
@else
  <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
@endif
<style>
  :root{
    --navy:#0E2E57; --deep:#040B1F; --ink:#111C2E; --ink2:#475467; --ink3:#5D6B80;
    --rule:#C9D2DF; --hair:#E3E8F0; --band:#E8EDF5; --blue:#1558D6; --ok:#017B37;
  }
  @page{ size:A4; margin:0; }
  *{ box-sizing:border-box; }
  html,body{ margin:0; }
  body{ font-family:"DM Sans",system-ui,-apple-system,"Segoe UI",Arial,sans-serif; color:var(--ink);
    -webkit-print-color-adjust:exact; print-color-adjust:exact; font-feature-settings:"tnum" 1; }

  /* ---- the page ---------------------------------------------------------- */
  .pc{ width:210mm; height:297mm; overflow:hidden; background:#fff; position:relative;
    display:flex; flex-direction:column; }
  .pc__band{ display:flex; align-items:center; justify-content:space-between; height:17mm; padding:0 12mm;
    background:var(--navy); color:#fff; flex:0 0 auto; }
  .pc__logo{ font-weight:700; font-size:15pt; letter-spacing:.14em; }
  .pc__where{ font-size:10.5pt; font-weight:500; color:#C9D6E6; letter-spacing:.02em; }
  .pc__photo{ height:80mm; flex:0 0 auto; background:var(--band); overflow:hidden; }
  .pc__photo img{ display:block; width:100%; height:100%; object-fit:cover; object-position:50% 50%; }
  .pc__body{ flex:1 1 auto; display:flex; flex-direction:column; padding:8mm 12mm 0; min-height:0; }

  /* name and price: the two things read from the pavement */
  .pc__head{ display:grid; grid-template-columns:minmax(0,1fr) auto; align-items:end; gap:6mm; }
  .pc__brand{ margin:0; font-size:13pt; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:var(--navy); }
  .pc__name{ margin:1mm 0 0; font-size:25pt; line-height:1.04; font-weight:700; letter-spacing:-.025em; }
  .pc__name span{ font-weight:500; color:var(--ink2); }
  .pc__price{ text-align:right; }
  .pc__now{ display:block; font-size:38pt; line-height:1; font-weight:700; letter-spacing:-.035em; white-space:nowrap; }
  .pc__was{ display:block; margin-top:1.5mm; font-size:11pt; color:var(--ink2); }
  .pc__was s{ text-decoration-thickness:1px; }

  /* the five facts, then the rest */
  .pc__top{ display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); margin:6mm 0 0;
    border-top:1.4pt solid var(--ink); border-bottom:.6pt solid var(--rule); }
  .pc__top div{ padding:3mm 2mm 3mm 0; }
  .pc__top dt{ font-size:8.5pt; color:var(--ink2); margin:1mm 0 0; }
  .pc__top dd{ margin:0; font-size:14pt; font-weight:700; letter-spacing:-.01em; white-space:nowrap; }
  .pc__rest{ display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:0 4mm; margin:2mm 0 0; }
  .pc__rest div{ display:flex; justify-content:space-between; gap:2mm; padding:1.7mm 0; border-bottom:.5pt solid var(--hair); font-size:9.5pt; }
  .pc__rest dt{ color:var(--ink2); }
  .pc__rest dd{ margin:0; font-weight:600; text-align:right; }
  .pc__top dl, .pc__rest dl{ margin:0; }

  /* new against today, the extras, the QR */
  .pc__mid{ display:grid; grid-template-columns:minmax(0,1fr) 52mm; gap:8mm; margin-top:6mm; flex:1 1 auto; min-height:0; }
  .pc__new h2{ margin:0; font-size:15pt; line-height:1.15; font-weight:700; letter-spacing:-.015em; }
  .pc__new h2 b{ white-space:nowrap; }
  .pc__save{ margin:2mm 0 0; font-size:11pt; color:var(--ink2); }
  .pc__save b{ color:var(--ink); }
  .pc__ex{ list-style:none; margin:4mm 0 0; padding:0; columns:2; column-gap:6mm; }
  .pc__ex li{ break-inside:avoid; display:flex; justify-content:space-between; gap:2mm; padding:1.3mm 0;
    border-bottom:.5pt solid var(--hair); font-size:8.8pt; line-height:1.25; }
  .pc__ex li span:first-child{ min-width:0; }
  .pc__ex li span:last-child{ flex:0 0 auto; color:var(--ink2); }
  .pc__exmore{ margin:2mm 0 0; font-size:8.8pt; color:var(--ink2); }
  .pc__facts{ margin:4mm 0 0; font-size:10.5pt; font-weight:600; line-height:1.5; }
  .pc__facts span{ white-space:nowrap; }
  .pc__facts span + span::before{ content:"·"; margin:0 2.2mm; color:var(--ink3); font-weight:400; }

  .pc__qr{ display:flex; flex-direction:column; align-items:center; text-align:center; }
  .pc__qr svg{ display:block; width:46mm; height:46mm; }
  .pc__qr svg path, .pc__qr svg rect{ fill:var(--ink); }
  .pc__qr p{ margin:3mm 0 0; font-size:10.5pt; font-weight:700; line-height:1.25; }
  .pc__qr small{ display:block; margin-top:1mm; font-size:9pt; font-weight:500; color:var(--ink2); }
  .pc__dgt{ display:flex; align-items:center; gap:2.5mm; margin-top:5mm; font-size:9pt; color:var(--ink2); text-align:left; }
  .pc__lbl{ display:inline-grid; place-items:center; width:12mm; height:12mm; border-radius:50%;
    font-weight:700; font-size:12pt; color:#fff; border:.8pt solid rgba(0,0,0,.25); flex:0 0 auto; }
  .pc__lbl--C{ background:#3FAE49; } .pc__lbl--B{ background:#F7D117; color:#111; }
  .pc__lbl--0{ background:#1F6FB8; } .pc__lbl--ECO{ background:linear-gradient(90deg,#1F6FB8 50%,#3FAE49 50%); font-size:8.5pt; }

  /* what comes with it, and how to reach him */
  .pc__foot{ flex:0 0 auto; display:flex; align-items:center; justify-content:space-between; gap:6mm;
    margin-top:5mm; padding:5mm 12mm 7mm; border-top:1.4pt solid var(--ink); }
  .pc__with{ font-size:10.5pt; line-height:1.4; }
  .pc__with b{ font-size:12.5pt; }
  .pc__call{ text-align:right; }
  .pc__call b{ display:block; font-size:19pt; letter-spacing:-.01em; white-space:nowrap; }
  .pc__call span{ font-size:9.5pt; color:var(--ink2); }

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
    .desk{ padding:24px 16px 48px; overflow-x:auto; }
    .pc{ margin:0 auto; box-shadow:0 6px 30px rgba(4,11,31,.18); }
    /* a phone: the sheet scaled to the screen, still the whole A4 */
    @media (max-width:820px){
      .desk{ padding:16px 0 32px; }
      .fit{ width:calc(210mm * var(--k)); height:calc(297mm * var(--k)); margin:0 auto; }
      .fit .pc{ transform:scale(var(--k)); transform-origin:0 0; }
    }
  }
  @media print{ .bar{ display:none !important; } .desk, .fit{ padding:0; margin:0; width:auto; height:auto; } .fit .pc{ transform:none; } }
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
<div class="desk"><div class="fit">
<article class="pc">
  <header class="pc__band">
    <span class="pc__logo">IV MOTORCLASS</span>
    <span class="pc__where">Málaga · ivmotorclass.com</span>
  </header>

  @if($photo)
    <div class="pc__photo"><img src="{{ $photo }}" alt=""></div>
  @endif

  <div class="pc__body">
    <div class="pc__head">
      <div>
        <p class="pc__brand">{{ $brand }}</p>
        <h1 class="pc__name">{{ $name }} <span>{{ $car->year }}</span></h1>
      </div>
      <p class="pc__price" style="margin:0">
        <span class="pc__now">{{ $eur($price) }}</span>
        @if($before)<span class="pc__was">antes <s>{{ $eur($before) }}</s></span>@endif
      </p>
    </div>

    <dl class="pc__top">
      @foreach($top as $k => $v)<div><dd>{{ $v }}</dd><dt>{{ $k }}</dt></div>@endforeach
    </dl>
    @if($rest)
      <dl class="pc__rest">
        @foreach($rest as $k => $v)<div><dt>{{ $k }}</dt><dd>{{ $v }}</dd></div>@endforeach
      </dl>
    @endif

    <div class="pc__mid">
      <div class="pc__new">
        @if($new)
          <h2>Nuevo{{ $new['year'] ? ', en ' . $new['year'] : '' }}, costaba <b>{{ $eur($new['total']) }}</b>.
            Hoy, <b>{{ $eur($price) }}</b>.</h2>
          <p class="pc__save">Un {{ $new['pct'] }} % menos: <b>{{ $eur($new['save']) }}</b> que no pagas.@if(!empty($new['extras']) && $new['extras'] > 0) {{ $eur($new['extras']) }} eran extras de fábrica.@endif</p>
        @endif
        @if($shown)
          <ul class="pc__ex">
            @foreach($shown as $x)<li><span>{{ $x['name'] }}</span><span>{{ $eur($x['price']) }}</span></li>@endforeach
          </ul>
          @if($more > 0)<p class="pc__exmore">y {{ $more }} {{ $more === 1 ? 'extra más' : 'extras más' }}: la lista entera, en el código.</p>@endif
        @endif
        @if(!$new && !$shown && $features)
          <h2>Equipamiento</h2>
          <ul class="pc__ex pc__ex--f">
            @foreach($features as $x)<li><span>{{ $x }}</span></li>@endforeach
          </ul>
        @endif
        @if($facts)
          <p class="pc__facts">@foreach($facts as $f)<span>{{ $f }}</span>@endforeach</p>
        @endif
      </div>

      <div class="pc__qr">
        {!! $qr !!}
        <p>Fotos, vídeo y todos los detalles</p>
        <small>ivmotorclass.com</small>
        @if($label)
          <div class="pc__dgt"><span class="pc__lbl pc__lbl--{{ $label }}">{{ $label }}</span><span>Etiqueta<br>{{ $label === '0' ? 'Cero emisiones' : ($label === 'ECO' ? 'ECO' : $label) }}</span></div>
        @endif
      </div>
    </div>
  </div>

  <footer class="pc__foot">
    <p class="pc__with" style="margin:0"><b>Garantía {{ $warranty >= 24 ? intdiv($warranty, 12) . ' años' : '1 año' }} incluida</b><br>Garantía nacional: vale en toda España</p>
    <p class="pc__call" style="margin:0"><b>WhatsApp {{ $phone }}</b><span>Llama o escribe: te lo enseño cuando quieras</span></p>
  </footer>
</article>
</div></div>
@unless($inline)
<script>
  // a phone: the whole A4 scaled to the screen's width
  (function () {
    function fit() {
      var k = Math.min(1, (window.innerWidth - 16) / (210 * 96 / 25.4));
      document.documentElement.style.setProperty('--k', k.toFixed(4));
    }
    fit(); window.addEventListener('resize', fit);
  })();
</script>
@endunless
</body>
</html>
