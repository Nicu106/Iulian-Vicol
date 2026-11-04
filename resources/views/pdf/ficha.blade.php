{{-- The car's PDF (App\Support\CarPdf): the car page on A4, for a buyer to
     keep, print or send on. Printed by Chrome (tools/cartel/print.mjs), so
     everything is inline: the fonts, the photographs, the marks.

     Page one is laid out like the page on a desk: the marque's colour across
     the top with the name on it, the photograph standing in front of it, the
     price, the facts, what comes with the car, and the way back to the page.
     The rest flows: the photographs, his description, the equipment, the
     instalment and the lenders. The tokens are public/css/mc-tokens.css's
     values, written out, because Chrome reads this file on its own. --}}
@php
  use App\Support\Finance;
  $eur = fn ($n) => number_format((int) $n, 0, ',', '.') . ' €';
  // page one: the eight facts a buyer asks first (two rows); every one of them
  // further on, as the page has its long list under the short one
  $order = ['Año', 'Kilómetros', 'Combustible', 'Cambio', 'Potencia', 'Tipología', 'Color', 'Etiqueta', 'Plazas', 'Puertas', 'Cubicaje', 'Motor', 'Maletero', 'Emisiones'];
  $all = array_filter(array_replace(array_fill_keys($order, null), $specs));
  $facts = array_slice($all, 0, 8, true);
  $name = $brand . ' ' . $car->model;
@endphp
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>{{ $name }} {{ $car->year }} — IV MOTORCLASS</title>
<style>
{!! $fonts !!}
@page { size: A4; margin: 14mm 0 16mm; }
@page :first { margin: 0; }
* { box-sizing: border-box; }
html, body { margin: 0; }
body {
  font-family: 'DM Sans', 'Helvetica Neue', Arial, sans-serif; color: #111C2E;
  font-size: 9.5pt; line-height: 1.45;
  -webkit-print-color-adjust: exact; print-color-adjust: exact;
}
b, strong { font-weight: 700; }

/* ---- page one ---------------------------------------------------------- */
/* A column of fixed parts with the photograph taking what they leave: a name
   on two lines makes the photograph shorter, never pushes the facts into the
   panels. */
.p1 { position: relative; height: 297mm; overflow: hidden; break-after: page; display: flex; flex-direction: column; }
.wall { position: absolute; top: 0; left: 0; right: 0; height: 84mm; background: {{ $colour }}; overflow: hidden; }
.wall i {
  position: absolute; right: -26mm; top: 50%; translate: 0 -50%; width: 118mm; height: 118mm;
  background: rgba(255,255,255,.07);
  -webkit-mask: url("{{ $mark }}") center / contain no-repeat; mask: url("{{ $mark }}") center / contain no-repeat;
}
.p1-in { position: relative; flex: 1 1 auto; min-height: 0; display: flex; flex-direction: column; padding: 11mm 14mm 0; }
.top { display: flex; justify-content: space-between; align-items: baseline; color: #FFFFFF; font-size: 8pt; }
.top b { font-size: 9pt; letter-spacing: .02em; }
.top span { color: rgba(255,255,255,.84); }
h1 {
  margin: 7mm 0 0; color: #FFFFFF; font-size: 26pt; line-height: 1.06; letter-spacing: -.022em; font-weight: 700;
  max-width: 170mm;
}
h1 span { color: rgba(255,255,255,.84); font-weight: 500; }
.cover { display: block; width: 182mm; flex: 1 1 0; min-height: 80mm; max-height: 121.3mm; margin-top: 7mm; object-fit: cover; background: #E8EDF5; }

.buy { display: flex; justify-content: space-between; align-items: flex-end; gap: 8mm; margin-top: 6mm; }
.price { display: flex; align-items: baseline; flex-wrap: wrap; gap: 1mm 4mm; }
.price b { color: #C6352A; font-size: 28pt; line-height: 1; letter-spacing: -.02em; }
.price .km { font-size: 15pt; color: #475467; letter-spacing: -.01em; }
.price .was { width: 100%; font-size: 8.5pt; color: #5D6B80; }
.price .was s { text-decoration-thickness: 1px; }
.price .was em { font-style: normal; font-weight: 700; color: #017B37; }
.month { text-align: right; white-space: nowrap; }
.month b { display: block; font-size: 15pt; line-height: 1.1; letter-spacing: -.01em; }
.month span { font-size: 8pt; color: #475467; }

.facts { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1.6mm; margin-top: 6mm; }
.facts div { padding: 2.2mm 3mm; background: #E8EDF5; min-width: 0; }
.facts dt { font-size: 7.5pt; color: #475467; }
.facts dd { margin: .4mm 0 0; font-size: 10.5pt; font-weight: 700; line-height: 1.2; overflow-wrap: anywhere; }
/* the DGT label in its own colours, as on the bonnet placard */
.label { display: inline-block; min-width: 5.6mm; padding: 0 1.4mm; border-radius: 50%; text-align: center; color: #FFFFFF; font-size: 8.5pt; }
.label--C { background: #3FAE49; } .label--B { background: #F7D117; color: #111111; }
.label--0 { background: #1F6FB8; } .label--ECO { background: linear-gradient(90deg, #1F6FB8 50%, #3FAE49 50%); border-radius: 2.8mm; font-size: 7pt; }

.offers { position: relative; margin: 6mm 14mm 0; display: grid; grid-template-columns: 1fr 1fr 46mm; gap: 4mm; }
.off { padding: 4mm 4.5mm; border: .25mm solid #E3E8F0; }
.off h2 { margin: 0 0 2.4mm; font-size: 10.5pt; letter-spacing: -.01em; }
.off ul { margin: 0; padding: 0; list-style: none; display: grid; gap: 1.2mm; }
.off li { display: flex; justify-content: space-between; align-items: baseline; padding: 1.8mm 3mm; background: #E8EDF5; }
.off li span { font-size: 8pt; color: #475467; }
.off li b { font-size: 11pt; letter-spacing: -.02em; }
.off li.inc { background: #0E2E57; }
.off li.inc span { color: #A6B6C8; }
.off li.inc b { color: #FFFFFF; }
.off p { margin: 2.4mm 0 0; font-size: 7.5pt; line-height: 1.45; color: #475467; }
.qr { text-align: center; background: #E8EDF5; border: 0; }
.qr svg { display: block; width: 27mm; height: 27mm; margin: 0 auto 2.4mm; }
.qr svg path, .qr svg rect { fill: #111C2E; }
.qr b { display: block; font-size: 9.5pt; }
.qr span { display: block; font-size: 7.5pt; color: #475467; line-height: 1.4; }

.foot {
  position: relative; margin: 6mm 14mm 9mm; padding-top: 2.6mm; border-top: .25mm solid #C9D2DF;
  display: flex; justify-content: space-between; font-size: 7.5pt; color: #475467;
}
.foot b { color: #111C2E; }

/* ---- the pages after --------------------------------------------------- */
.rest { padding: 0 14mm; }
.head { display: flex; justify-content: space-between; align-items: baseline; padding-bottom: 2.6mm;
  border-bottom: .8mm solid {{ $colour }}; font-size: 8.5pt; color: #475467; }
.head b { color: #111C2E; font-size: 10pt; }
.photos { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 2mm; margin-top: 5mm; }
.photos img { display: block; width: 100%; aspect-ratio: 3 / 2; object-fit: cover; background: #E8EDF5; }
h2.sec { margin: 8mm 0 3mm; font-size: 15pt; line-height: 1.1; letter-spacing: -.02em; break-after: avoid; }
.about { columns: 2; column-gap: 8mm; font-size: 9pt; color: #111C2E; }
.about p { margin: 0 0 2.4mm; white-space: pre-line; }
.about ul, .equip { margin: 0 0 2.4mm; padding: 0; list-style: none; }
.about li, .equip li { position: relative; padding-left: 3.6mm; margin-bottom: .9mm; break-inside: avoid; }
.about li::before, .equip li::before {
  content: ""; position: absolute; left: 0; top: 1.55mm; width: 1.4mm; height: 1.4mm; background: #0E2E57;
}
.equip { columns: 3; column-gap: 6mm; font-size: 8.5pt; }
.tech { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 8mm; margin: 0; break-inside: avoid; }
.tech div { display: flex; justify-content: space-between; gap: 4mm; padding: 1.8mm 0; border-bottom: .25mm solid #E3E8F0; font-size: 9pt; }
.tech dt { color: #475467; }
.tech dd { margin: 0; font-weight: 700; text-align: right; }
.past { display: flex; gap: 8mm; align-items: baseline; padding: 4mm 5mm; background: #E8EDF5; break-inside: avoid; }
.past b { font-size: 15pt; letter-spacing: -.02em; }
.past span { font-size: 8pt; color: #475467; display: block; }
.past p { margin: 0; font-size: 9pt; }
.fin { display: grid; grid-template-columns: 52mm 1fr; gap: 6mm; padding: 4.5mm 5mm; border: .25mm solid #E3E8F0; break-inside: avoid; }
.fin .q b { display: block; font-size: 22pt; line-height: 1; letter-spacing: -.02em; }
.fin .q span { font-size: 8.5pt; color: #475467; }
.fin p { margin: 0 0 1.6mm; font-size: 8.5pt; color: #475467; }
.fin p.ex { color: #111C2E; }
.banks { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 4mm; margin-top: 4mm; break-inside: avoid; }
.banks img { display: block; height: auto; }
.banks-say { margin: 3mm 0 0; font-size: 8.5pt; color: #475467; max-width: 150mm; }
.end { break-inside: avoid; }
.legal { margin-top: 8mm; padding-top: 2.6mm; border-top: .25mm solid #C9D2DF; font-size: 7.5pt; color: #475467; break-inside: avoid; }
.legal p { margin: 0 0 1mm; }
</style>
</head>
<body>

<section class="p1">
  <div class="wall">@if($mark)<i></i>@endif</div>
  <div class="p1-in">
    <div class="top"><b>IV MOTORCLASS</b><span>Coches de ocasión en Málaga · {{ $host }}</span></div>
    <h1>{{ $name }} <span>{{ $car->year }}</span></h1>
    @if($cover)<img class="cover" src="{{ $cover['src'] }}" alt="">@endif

    <div class="buy">
      <div class="price">
        <b>{{ $eur($price) }}</b>
        @if($car->mileage)<span class="km">{{ number_format($car->mileage, 0, ',', '.') }} km</span>@endif
        @if($before)<span class="was">antes <s>{{ $eur($before) }}</s> <em>−{{ $eur($before - $price) }}</em></span>@endif
      </div>
      <div class="month">
        <b>o {{ Finance::money($plan['monthly'], false) }} al mes</b>
        <span>en {{ $plan['label'] }} · TAE {{ Finance::pct($tae) }}</span>
      </div>
    </div>

    <dl class="facts">
      @foreach($facts as $k => $v)
        <div><dt>{{ $k }}</dt><dd>@if($k === 'Etiqueta')<span class="label label--{{ $v }}">{{ $v }}</span>@else{{ $v }}@endif</dd></div>
      @endforeach
    </dl>
  </div>

  <div class="offers">
    <div class="off">
      <h2>Garantía</h2>
      <ul>
        @foreach($warranty as [$y, $cost, $inc])
          <li class="{{ $inc ? 'inc' : '' }}"><span>{{ $y === 1 ? '1 año' : $y . ' años' }}</span><b>{{ $cost }}</b></li>
        @endforeach
      </ul>
      <p>Nacional: vale en toda España, no sólo en Málaga.</p>
    </div>
    <div class="off">
      <h2>Mantenimiento</h2>
      <ul>
        @foreach($upkeep as [$y, $cost])
          <li><span>{{ $y === 1 ? '1 año' : $y . ' años' }}</span><b>{{ $cost }}</b></li>
        @endforeach
      </ul>
      <p>Aceite, filtros y lo que toque, a precio cerrado. Opcional.</p>
    </div>
    <div class="off qr">
      {!! $qr !!}
      <b>Míralo en la web</b>
      <span>Todas las fotos, el vídeo y los detalles</span>
    </div>
  </div>

  <div class="foot">
    <span><b>614 75 31 87</b> · también WhatsApp · ventas@ivmotorclass.com</span>
    <span>Con cita previa · Málaga</span>
  </div>
</section>

<section class="rest">
  <div class="head"><b>{{ $name }} {{ $car->year }}</b><span>{{ $eur($price) }} · IV MOTORCLASS</span></div>

  @if($photos)
    <div class="photos">
      @foreach($photos as $p)<img src="{{ $p['src'] }}" alt="">@endforeach
    </div>
  @endif

  @if($about)
    <h2 class="sec">Lo que hay que saber</h2>
    <div class="about">
      @foreach($about as [$kind, $body])
        @if($kind === 'ul')
          <ul>@foreach($body as $li)<li>{{ $li }}</li>@endforeach</ul>
        @else
          <p>{{ $body }}</p>
        @endif
      @endforeach
    </div>
  @endif

  @if($features)
    <h2 class="sec">Equipamiento</h2>
    <ul class="equip">@foreach($features as $f)<li>{{ $f }}</li>@endforeach</ul>
  @endif

  @if(count($all) > count($facts))
    <h2 class="sec">Especificaciones técnicas</h2>
    <dl class="tech">
      @foreach($all as $k => $v)<div><dt>{{ $k }}</dt><dd>{{ $v === '0' && $k === 'Etiqueta' ? '0 (cero emisiones)' : $v }}</dd></div>@endforeach
    </dl>
  @endif

  @if($past && $past['total'])
    <h2 class="sec">Su historia</h2>
    <div class="past">
      <div><span>Nuevo{{ $past['year'] ? ', en ' . $past['year'] : '' }}</span><b>{{ $eur($past['total']) }}</b></div>
      <div><span>Hoy</span><b>{{ $eur($past['now']) }}</b></div>
      <p>{{ $eur($past['save']) }} menos que nuevo, un {{ $past['pct'] }}&nbsp;%.@if(($past['xtotal'] ?? 0) > 0) De lo que costaba, {{ $eur($past['xtotal']) }} eran extras de fábrica.@endif</p>
    </div>
  @endif

  {{-- one block from the heading to the last line: a page break inside it left
       the lenders' sentence and the small print alone on a fourth page --}}
  <div class="end">
  <h2 class="sec">Financiación</h2>
  <div class="fin">
    <div class="q"><b>{{ Finance::money($plan['monthly'], false) }}</b><span>al mes en {{ $plan['label'] }}, sin entrada</span></div>
    <div>
      <p class="ex">{{ $plan['months'] }} cuotas de {{ Finance::money($plan['monthly']) }}. TAE {{ Finance::pct($tae) }}, TIN {{ Finance::pct(Finance::TIN) }}.
        Importe financiado {{ Finance::money($plan['amount'], false) }}; total a pagar {{ Finance::money($plan['total']) }}.</p>
      <p>Cálculo orientativo, sin comisión de apertura. La cuota y las condiciones finales las fija la entidad financiera al estudiar tu solicitud.
        Con entrada o a otro plazo, calcúlalo en la web o pregúntame.</p>
    </div>
  </div>
  <div class="banks">
    @foreach($banks as $b)@if($b['src'])<img src="{{ $b['src'] }}" alt="{{ $b['name'] }}" style="width:{{ round($b['w'] * .17, 1) }}mm">@endif @endforeach
  </div>
  <p class="banks-say">Trabajo con entidades como estas. Las opciones dependen de tu perfil, del coche y de los criterios de cada
    financiera. Haces la solicitud conmigo y te acompaño en todo el proceso.</p>

  <div class="legal">
    <p>Precio final, con los impuestos incluidos. Los datos del coche se ponen con cuidado, pero puede haber errores: los que valen son
      los de la documentación del coche y los del contrato. Un coche puede venderse o reservarse antes de que la web lo diga.</p>
    <p>Ficha del {{ $date }} · {{ $url }} · 614 75 31 87 · ventas@ivmotorclass.com</p>
  </div>
  </div>
</section>

</body>
</html>
