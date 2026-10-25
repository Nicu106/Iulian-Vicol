{{-- Where the visitors are (StatsInsights::places): Spain by region, a dot per
     town sized by people, the towns and countries as lists underneath — the
     lists carry the numbers, the map carries the picture. Drawn here, from
     resources/data/map-es.json and map-world.json: no map service, no request
     leaves the panel. Towns come from a GeoIP lookup on the server (App\Support\Geo). --}}
@php
  use App\Support\Stats;
  use App\Support\StatsInsights as In;
  $n = fn ($v) => Stats::num($v);
  $pct = fn ($v) => $g['total'] ? (int) round(100 * $v / $g['total']) : 0;
  $es = $g['es'];
  $hidden = collect($g['countries'])->firstWhere('cc', 'ZZ')['n'] ?? 0;
  $abroad = $g['abroad'] - $hidden;
  $rest = $g['spain'] - $g['near'] - $g['andalucia'];
  $towns = array_values(array_filter($g['cities'], fn ($c) => $c['cc'] !== 'ZZ'));
  $inSpain = array_values(array_filter($towns, fn ($c) => $c['cc'] === 'ES'));
  $maxTown = max(1, $inSpain[0]['n'] ?? 1);
  $maxReg = max(1, max($g['regions'] ?: [0]));
@endphp
<section class="ad-st-sec">
  <h3 class="ad-st-h">De dónde son</h3>
  @if(! $g['total'])
    <p class="ad-st-quiet">Todavía nada.</p>
  @else
    @if($g['spain'])
      <p class="ad-st-say ad-st-say--lead">
        {{ collect([
          $pct($g['near']) . ' % a menos de 60 km de Málaga',
          $g['andalucia'] ? $pct($g['andalucia']) . ' % del resto de Andalucía' : null,
          $rest > 0 ? $pct($rest) . ' % del resto de España' : null,
          $abroad > 0 ? $pct($abroad) . ' % de fuera' : null,
        ])->filter()->implode(', ') }}.
      </p>
    @endif

    @if($es && $g['spain'])
      <figure class="ad-st-map">
        <svg viewBox="0 0 {{ $es['w'] }} {{ $es['h'] }}" role="img" aria-label="Mapa de España: de dónde son las personas que ven la web">
          <g class="ad-st-map__land">
            @foreach($es['regions'] as $r)
              @php $v = $g['regions'][$r['id']] ?? 0; @endphp
              <path d="{{ $r['d'] }}" @if($v) class="is-on" style="fill-opacity: {{ round(.14 + .5 * $v / $maxReg, 2) }}" @endif><title>{{ $r['name'] }}: {{ $v }}</title></path>
            @endforeach
          </g>
          <path class="ad-st-map__frame" d="{{ $es['canary']['frame'] }}" />
          <g class="ad-st-map__dots">
            @foreach(array_reverse($inSpain) as $c)
              @php [$x, $y] = In::esXy($c['lat'], $c['lon'], $es); @endphp
              <circle cx="{{ $x }}" cy="{{ $y }}" r="{{ round(4 + 11 * sqrt($c['n'] / $maxTown), 1) }}"><title>{{ $c['city'] }}: {{ $c['n'] }}</title></circle>
            @endforeach
          </g>
          <g class="ad-st-map__names" aria-hidden="true">
            @php
              // the three biggest towns get their name, unless it would sit on a name already placed
              $placed = [];
              foreach (array_slice($inSpain, 0, 6) as $c) {
                  if (count($placed) === 3) break;
                  [$x, $y] = In::esXy($c['lat'], $c['lon'], $es);
                  $r = 4 + 11 * sqrt($c['n'] / $maxTown);
                  $w = 9.5 * mb_strlen($c['city']);
                  $left = $x + $r + 5 + $w > $es['w'];        // near the right edge the name goes on the left
                  $x0 = $left ? $x - $r - 5 - $w : $x + $r + 5;
                  $box = [$x0, $y - 13, $x0 + $w, $y + 7];
                  foreach ($placed as $p) {
                      if ($box[0] < $p['box'][2] && $box[2] > $p['box'][0] && $box[1] < $p['box'][3] && $box[3] > $p['box'][1]) continue 2;
                  }
                  $placed[] = ['box' => $box, 'x' => $left ? $x - $r - 5 : $x + $r + 5, 'y' => $y + 6, 'end' => $left, 'name' => $c['city']];
              }
            @endphp
            @foreach($placed as $p)
              <text x="{{ round($p['x'], 1) }}" y="{{ round($p['y'], 1) }}" text-anchor="{{ $p['end'] ? 'end' : 'start' }}">{{ $p['name'] }}</text>
            @endforeach
          </g>
        </svg>
      </figure>
    @endif

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
              'label' => $c['city'] . ($c['cc'] !== 'ES' ? ' (' . $c['country'] . ')' : ''),
              'n' => $c['n'], 'share' => $pct($c['n']),
            ], array_slice($towns, 0, 12))])
        @endif
        @if($hidden)
          <p class="ad-st-quiet">Y {{ $n($hidden) }} con la ubicación oculta: un iPhone con «Relay privado» de iCloud
            no deja ver de dónde es.</p>
        @endif
      </div>
      @php $countries = array_values(array_filter($g['countries'], fn ($c) => $c['cc'] !== 'ZZ')); @endphp
      @if(count($countries) > 1 || ($countries && $countries[0]['cc'] !== 'ES'))
        <div>
          <p class="ad-st-search__h">Países</p>
          @include('admin.stats._rank', ['rows' => array_map(fn ($c) => ['label' => $c['name'], 'n' => $c['n'], 'share' => $pct($c['n'])], $countries)])
          @if($g['world'] && $abroad > 0)
            @php
              $on = array_flip(array_column($countries, 'cc')); $maxC = max(1, max(array_column($countries, 'n'))); $byCc = array_column($countries, 'n', 'cc');
              // Europe and the north of Africa when everyone is there; the world otherwise
              $europe = ['AD','AL','AT','BA','BE','BG','BY','CH','CY','CZ','DE','DK','DZ','EE','ES','FI','FR','GB','GR','HR','HU','IE','IS','IT','LT','LU','LV','MA','MD','ME','MK','MT','NL','NO','PL','PT','RO','RS','SE','SI','SK','TN','UA','XK','LY','EG','TR'];
              $wm = $g['world'];
              [$vx, $vy] = \App\Support\StatsInsights::worldXy(72, -25, $wm);
              [$vx2, $vy2] = \App\Support\StatsInsights::worldXy(27, 45, $wm);
              $box = array_diff(array_keys($on), $europe) ? '0 0 ' . $wm['w'] . ' ' . $wm['h'] : "$vx $vy " . round($vx2 - $vx, 1) . ' ' . round($vy2 - $vy, 1);
            @endphp
            <figure class="ad-st-map ad-st-map--world">
              <svg viewBox="{{ $box }}" role="img" aria-label="Mapa: países de las personas que ven la web">
                <g class="ad-st-map__land">
                  @foreach($g['world']['countries'] as $c)
                    <path d="{{ $c['d'] }}" @isset($on[$c['iso2']]) class="is-on" style="fill-opacity: {{ round(.25 + .6 * $byCc[$c['iso2']] / $maxC, 2) }}" @endisset><title>{{ $c['name'] }}@isset($byCc[$c['iso2']]): {{ $byCc[$c['iso2']] }}@endisset</title></path>
                  @endforeach
                </g>
              </svg>
            </figure>
          @endif
        </div>
      @endif
    </div>
    <p class="ad-st-quiet">La ciudad sale de la conexión y es aproximada: con datos móviles a veces aparece la ciudad
      de la operadora (Madrid, Sevilla) en vez de la del móvil. La IP no se guarda. Ciudades:
      <a href="https://db-ip.com" rel="noopener" target="_blank">IP Geolocation by DB-IP</a>.</p>
  @endif
</section>
