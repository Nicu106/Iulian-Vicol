{{-- The photographs, all cars together (StatsInsights::photos): how many a person
     sees, how long each holds them, how far into the set they get, and which
     kind holds them. For deciding how many to take, which go first, and whether
     the interior earns its share. --}}
@php
  use App\Support\Stats;
  $n = fn ($v) => Stats::num($v);
  $reach = array_values(array_filter($ph['reach'], fn ($r) => $r['of'] > 0));
  $cut = collect($reach)->first(fn ($r) => $r['pct'] < 50);
@endphp
<section class="ad-st-sec">
  <h3 class="ad-st-h">Las fotos</h3>
  @if(! $ph['views'])
    <p class="ad-st-quiet">Todavía nadie ha mirado las fotos de un coche en estos días.</p>
  @else
    <ul class="ad-st-facts">
      <li><span class="ad-st-facts__k">Fotos que ve cada persona</span><span class="ad-st-facts__v">{{ $ph['avg'] !== null ? $n($ph['avg']) : '—' }}</span></li>
      <li><span class="ad-st-facts__k">Tiempo en cada foto</span><span class="ad-st-facts__v">{{ $ph['secs'] !== null ? Stats::dur($ph['secs']) : '—' }}</span></li>
      <li><span class="ad-st-facts__k">Fichas vistas</span><span class="ad-st-facts__v">{{ $n($ph['views']) }}</span></li>
    </ul>
    @if($cut && $ph['views'] >= 20)
      <p class="ad-st-say ad-st-say--lead">La mitad de las personas deja de pasar fotos antes de la {{ $cut['pos'] }}.ª:
        lo que tiene que verse, en las {{ max(1, $cut['pos'] - 1) }} primeras.</p>
    @endif
    @if(count($reach) > 1)
      <div class="ad-st-reach" role="img" aria-label="Qué parte de las personas llega a cada foto">
        @foreach(array_slice($reach, 0, 24) as $r)
          <span class="ad-st-reach__c" title="Foto {{ $r['pos'] }}: {{ $r['pct'] }} %"><i style="height: {{ max(2, $r['pct']) }}%"></i></span>
        @endforeach
      </div>
      <p class="ad-st-bars__x"><span>foto 1 · {{ $reach[0]['pct'] }} %</span>
        <span>foto {{ min(24, count($reach)) }} · {{ $reach[min(24, count($reach)) - 1]['pct'] }} %</span></p>
    @endif
    @if(count($ph['groups']) > 1)
      <p class="ad-st-search__h" style="margin-top: var(--s-5)">Dónde se les va el tiempo</p>
      <ul class="ad-st-list">
        @foreach($ph['groups'] as $r)
          <li><span>{{ $r['label'] }} <em class="ad-st-list__q">{{ Stats::dur($r['n']) }} en total</em></span><span>{{ $r['share'] }} %</span></li>
        @endforeach
      </ul>
    @endif
    <p class="ad-st-quiet">Una foto cuenta como vista cuando está en pantalla al menos 0,7 segundos: pasar el dedo
      deprisa no cuenta. Cada coche tiene sus fotos una a una en su página de estadísticas.</p>
  @endif
</section>
