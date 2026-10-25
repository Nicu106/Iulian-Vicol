{{-- Before the browser counted (StatsInsights::history): the fortnight estimated
     from the server's log (stats:history), kept apart from the real numbers and
     said to be an estimate; and every car sold, by marque. --}}
@php
  use App\Support\Stats;
  $n = fn ($v) => Stats::num($v);
  $d = fn ($day) => \Carbon\CarbonImmutable::parse($day)->locale('es')->translatedFormat('j \d\e F');
  $maxDay = max(1, max(array_column($h['days'], 'n') ?: [0]));
@endphp
@if($h['people'] || $h['sold'])
<section class="ad-st-sec ad-st-before">
  <h3 class="ad-st-h">Antes de empezar a contar</h3>

  @if($h['people'])
    <p class="ad-st-say ad-st-say--lead">Del {{ $d($h['from']) }} al {{ $d($h['to']) }}, unas <b>{{ $n($h['people']) }}</b>
      visitas de personas ({{ $n(round($h['people'] / max(1, count($h['days'])), 1)) }} al día) y {{ $n($h['views']) }} páginas vistas.</p>
    <div class="ad-st-reach ad-st-reach--days" role="img" aria-label="Personas por día, estimado">
      @foreach($h['days'] as $r)
        <span class="ad-st-reach__c" title="{{ $d($r['day']) }}: {{ $r['n'] }}"><i style="height: {{ max(2, round(100 * $r['n'] / $maxDay)) }}%"></i></span>
      @endforeach
    </div>

    <div class="ad-st-two">
      <div>
        <p class="ad-st-search__h">Los coches que más vieron</p>
        <ul class="ad-st-list">
          @foreach(array_slice($h['cars'], 0, 8) as $c)
            <li><span>{{ $c['name'] }}@if($c['sold']) <em class="ad-st-list__q">vendido</em>@endif</span><span>{{ $c['people'] }}</span></li>
          @endforeach
        </ul>
      </div>
      <div>
        <p class="ad-st-search__h">De dónde llegaron</p>
        <ul class="ad-st-list">
          @foreach(collect($h['sources'])->sortDesc()->take(6) as $k => $v)
            <li><span>{{ Stats::SOURCES[$k] ?? $k }}</span><span>{{ $v }}</span></li>
          @endforeach
        </ul>
        @if($h['places']['total'])
          <p class="ad-st-search__h" style="margin-top: var(--s-4)">De dónde son</p>
          <ul class="ad-st-list">
            @foreach(array_slice(array_values(array_filter($h['places']['cities'], fn ($c) => $c['cc'] !== 'ZZ')), 0, 6) as $c)
              <li><span>{{ $c['city'] }}{{ $c['cc'] !== 'ES' ? ' (' . $c['country'] . ')' : '' }}</span><span>{{ $c['n'] }}</span></li>
            @endforeach
          </ul>
        @endif
      </div>
    </div>
    <p class="ad-st-quiet">Estimado a partir del registro del servidor, que guarda 14 días: cuenta los navegadores que
      cargaron la página de verdad, sin robots ni servidores, y sin tus conexiones. No ve WhatsApp, tiempo ni fotos, y
      si el navegador ya tenía la página guardada puede no verlo: se queda algo corto, nunca largo. Estas cifras no se
      suman a las de arriba.</p>
  @endif

  @if($h['sold'])
    <p class="ad-st-search__h" style="margin-top: var(--s-5)">Lo que has vendido, por marca</p>
    <ul class="ad-st-list">
      @foreach($h['sold'] as $s)
        <li><span>{{ $s['name'] }} <em class="ad-st-list__q">precio medio {{ Stats::eur($s['avg']) }}</em></span><span>{{ $s['n'] }}</span></li>
      @endforeach
    </ul>
  @endif
</section>
@endif
