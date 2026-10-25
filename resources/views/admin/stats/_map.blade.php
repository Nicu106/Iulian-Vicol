{{-- Where the visitors are (StatsInsights::places): Spain by region, a dot per
     town sized by people; as soon as someone from another country is in the
     period, a second map framed on wherever they all are (StatsInsights::wide);
     the towns and countries as lists underneath — the lists carry the numbers,
     the maps carry the picture. Drawn here, from resources/data/map-es.json and
     map-world.json: no map service, no request leaves the panel. Towns come from
     a GeoIP lookup on the server (App\Support\Geo). --}}
@php
  use App\Support\Stats;
  $n = fn ($v) => Stats::num($v);
  $pct = fn ($v) => $g['total'] ? (int) round(100 * $v / $g['total']) : 0;
  $es = $g['es'];
  $sp = $g['spainMap'] ?? null;
  $wide = $g['wide'] ?? null;
  $hidden = collect($g['countries'])->firstWhere('cc', 'ZZ')['n'] ?? 0;
  $abroad = $g['abroad'] - $hidden;
  $rest = $g['spain'] - $g['near'] - $g['andalucia'];
  $towns = array_values(array_filter($g['cities'], fn ($c) => $c['cc'] !== 'ZZ'));
  $countries = array_values(array_filter($g['countries'], fn ($c) => $c['cc'] !== 'ZZ'));
  $out = array_values(array_filter($countries, fn ($c) => $c['cc'] !== 'ES'));
  $outNames = count($out) > 2
      ? $out[0]['name'] . ', ' . $out[1]['name'] . ' y ' . (count($out) - 2) . ' ' . (count($out) === 3 ? 'país' : 'países') . ' más'
      : collect($out)->pluck('name')->implode(' y ');
  $lead = collect([
      $g['spain'] ? $pct($g['near']) . ' % a menos de 60 km de Málaga' : null,
      $g['andalucia'] ? $pct($g['andalucia']) . ' % del resto de Andalucía' : null,
      $rest > 0 ? $pct($rest) . ' % del resto de España' : null,
      $abroad > 0 ? $pct($abroad) . ' % de fuera' . ($outNames !== '' ? ' (' . $outNames . ')' : '') : null,
  ])->filter()->implode(', ');
@endphp
<section class="ad-st-sec">
  <h3 class="ad-st-h">De dónde son</h3>
  @if(! $g['total'])
    <p class="ad-st-quiet">Todavía nada.</p>
  @else
    @if($lead !== '')
      <p class="ad-st-say ad-st-say--lead">{{ $lead }}.</p>
    @endif

    <div class="ad-st-maps">
    @if($es && $sp)
      <figure class="ad-st-map">
        <svg viewBox="0 0 {{ $es['w'] }} {{ $es['h'] }}" role="img" aria-label="Mapa de España: de dónde son las personas que ven la web">
          @php $maxReg = max(1, max($g['regions'] ?: [0])); @endphp
          <g class="ad-st-map__land">
            @foreach($es['regions'] as $r)
              @php $v = $g['regions'][$r['id']] ?? 0; @endphp
              <path d="{{ $r['d'] }}" @if($v) class="is-on" style="fill-opacity: {{ round(.14 + .5 * $v / $maxReg, 2) }}" @endif><title>{{ $r['name'] }}: {{ $v }}</title></path>
            @endforeach
          </g>
          <path class="ad-st-map__frame" d="{{ $es['canary']['frame'] }}" />
          @include('admin.stats._map-marks', ['marks' => $sp])
        </svg>
      </figure>
    @endif

    @if($wide)
      <figure class="ad-st-map ad-st-map--world">
        <svg viewBox="0 0 {{ $wide['w'] }} {{ $wide['h'] }}" role="img" aria-label="Mapa: de dónde son las personas de fuera de España">
          <g class="ad-st-map__land" transform="matrix({{ $wide['m'] }})">
            @foreach($wide['lands'] as $c)
              <path d="{{ $c['d'] }}" @if($c['o']) class="is-on" style="fill-opacity: {{ $c['o'] }}" @endif><title>{{ $c['name'] }}@if($c['n']): {{ $c['n'] }} @endif</title></path>
            @endforeach
          </g>
          @include('admin.stats._map-marks', ['marks' => $wide])
        </svg>
      </figure>
    @endif
    </div>

    <ul class="ad-st-facts">
      <li><span class="ad-st-facts__k">Cerca de Málaga</span><span class="ad-st-facts__v">{{ $n($g['near']) }}</span></li>
      <li><span class="ad-st-facts__k">Resto de Andalucía</span><span class="ad-st-facts__v">{{ $n($g['andalucia']) }}</span></li>
      <li><span class="ad-st-facts__k">Resto de España</span><span class="ad-st-facts__v">{{ $n(max(0, $rest)) }}</span></li>
      <li><span class="ad-st-facts__k">Fuera de España</span><span class="ad-st-facts__v">{{ $n($abroad) }}</span></li>
    </ul>

    <div class="ad-st-two">
      <div>
        <p class="ad-st-search__h">Ciudades</p>
        @if($towns)
          @include('admin.stats._rank', ['rows' => array_map(fn ($c) => [
              'label' => $c['city'] . ($c['cc'] !== 'ES' && $c['city'] !== $c['country'] ? ' (' . $c['country'] . ')' : ''),
              'n' => $c['n'], 'share' => $pct($c['n']),
            ], array_slice($towns, 0, 12))])
        @endif
        @if($hidden)
          <p class="ad-st-quiet">Y {{ $n($hidden) }} con la ubicación oculta: un iPhone con «Relay privado» de iCloud
            no deja ver de dónde es.</p>
        @endif
      </div>
      @if($out)
        <div>
          <p class="ad-st-search__h">Países</p>
          @include('admin.stats._rank', ['rows' => array_map(fn ($c) => ['label' => $c['name'], 'n' => $c['n'], 'share' => $pct($c['n'])], $countries)])
        </div>
      @endif
    </div>
    <p class="ad-st-quiet">La ciudad sale de la conexión y es aproximada: con datos móviles a veces aparece la ciudad
      de la operadora (Madrid, Sevilla) en vez de la del móvil. La IP no se guarda. Ciudades:
      <a href="https://db-ip.com" rel="noopener" target="_blank">IP Geolocation by DB-IP</a>.</p>
  @endif
</section>
