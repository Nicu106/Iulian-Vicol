{{-- What to buy (App\Support\StatsInsights::brands / prices): per marque, the
     cars for sale, the people who looked at them, per car, WhatsApp, and what
     was asked for. The sentences come first — they are the point; the rows are
     the evidence. Under StatsInsights::ENOUGH people the page says so instead
     of naming a winner. --}}
@php
  use App\Support\Stats;
  $n = fn ($v) => Stats::num($v);
@endphp
<section class="ad-st-sec">
  <h3 class="ad-st-h">Qué marcas atraen</h3>
  @foreach($b['say'] as $line)
    <p class="ad-st-say ad-st-say--lead">{{ $line }}</p>
  @endforeach

  @if($b['rows'])
    <ul class="ad-st-brands">
      @foreach($b['rows'] as $r)
        <li>
          <p class="ad-st-brands__h">
            <b>{{ $r['name'] }}</b>
            <span>{{ $r['stock'] ? $r['stock'] . ' a la venta' . ($r['from'] ? ', desde ' . Stats::eur($r['from']) : '') : 'ninguno a la venta' }}</span>
          </p>
          <p class="ad-st-brands__p">
            <span><b>{{ $n($r['people']) }}</b> {{ $r['people'] === 1 ? 'persona' : 'personas' }}@if($r['people']) ({{ $r['share'] }} %)@endif</span>
            @if($r['perCar'] !== null)<span><b>{{ $n($r['perCar']) }}</b> por coche</span>@endif
            @if($r['wa'])<span class="is-wa"><b>{{ $n($r['wa']) }}</b> WhatsApp o llamada</span>@endif
            @if($r['time'])<span><b>{{ Stats::dur($r['time']) }}</b> por ficha</span>@endif
            @if($r['asked'])<span><b>{{ $n($r['asked']) }}</b> {{ $r['asked'] === 1 ? 'vez preguntada' : 'veces preguntada' }}</span>@endif
            @if($r['sold'])<span><b>{{ $n($r['sold']) }}</b> {{ $r['sold'] === 1 ? 'vendido' : 'vendidos' }} en total</span>@endif
          </p>
          @if($r['people'])<span class="ad-st-rank__bar"><i style="width: {{ max(1, $r['share']) }}%"></i></span>@endif
        </li>
      @endforeach
    </ul>
    <p class="ad-st-quiet">Personas: las que abrieron la ficha de un coche de esa marca. Preguntada: buscada desde la
      portada, su fila abierta entera en el catálogo o «Avísame cuando entre uno». Vendidos: desde que existe la web.</p>
  @endif
</section>

<section class="ad-st-sec">
  <h3 class="ad-st-h">Qué precio miran</h3>
  @foreach($pr['say'] as $line)
    <p class="ad-st-say ad-st-say--lead">{{ $line }}</p>
  @endforeach
  @if($pr['rows'])
    @include('admin.stats._rank', ['rows' => array_map(fn ($r) => [
        'label' => $r['label'],
        'n' => $r['people'],
        'share' => $r['share'],
        'sub' => collect([
          $r['stock'] ? $r['stock'] . ' a la venta' : 'ninguno a la venta',
          $r['perCar'] !== null && $r['people'] ? Stats::num($r['perCar']) . ' personas por coche' : null,
          $r['wa'] ? $r['wa'] . ' WhatsApp' : null,
        ])->filter()->implode(' · '),
      ], $pr['rows'])])
  @endif
  @if($pr['asked'])
    <p class="ad-st-quiet">Buscado como precio máximo desde la portada:
      {{ collect($pr['asked'])->map(fn ($a) => mb_strtolower($a['label']) . ' (' . $a['n'] . ')')->implode(', ') }}.</p>
  @endif
</section>
