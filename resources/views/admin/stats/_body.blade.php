{{-- The whole site, over $s['days'] days (App\Support\Stats::summary). Used by
     the page and, as a fragment, by the Panel's popup.

     The order is what a dealer asks first: how many came, did they write, which
     cars they looked at, where they came from, what they did. One bold thing:
     the people-per-day columns. Everything else is words, numbers and hairlines. --}}
@php
  use App\Support\Stats;
  $n = fn ($v) => Stats::num($v);
  $since = $s['first'] ? $s['first']->locale('es')->translatedFormat('j \d\e F') : null;
  $span = $s['partial'] ? 'desde el ' . $since : 'en los últimos ' . $s['days'] . ' días';
  $plural = fn (int $v, string $one, string $many) => $v === 1 ? $one : $many;
  $db = $s['db'];
@endphp
<div class="ad-st" data-days="{{ $s['days'] }}">

@if(! $s['any'])
  <div class="ad-st-start">
    <p class="ad-st-start__t">Las visitas se cuentan desde hoy</p>
    <p class="ad-st-start__p">En cuanto entre alguien en la web, aquí verás cuántas personas la ven, qué coches
      miran, cuánto tiempo, de dónde llegan y quién pulsa WhatsApp. Sin cookies y sin guardar quién es.</p>
  </div>
@else

  {{-- How many --}}
  <section class="ad-st-hero" aria-label="Personas">
    <p class="ad-st-hero__n">{{ $n($s['people'][0]) }}</p>
    <p class="ad-st-hero__t">{{ $plural($s['people'][0], 'persona vio la web', 'personas vieron la web') }} {{ $span }}</p>
    @if($s['compare'] && ($d = Stats::delta($s['people'][0], $s['people'][1])))
      <p class="ad-st-hero__d">{{ $d }} que los {{ $s['days'] }} días anteriores ({{ $n($s['people'][1]) }})</p>
    @endif
    @include('admin.stats._bars', ['series' => $s['series'], 'first' => $s['first'], 'what' => 'personas'])
    <p class="ad-st-hero__f">
      Hoy, hasta ahora: <b>{{ $n($s['today']) }}</b>.
      @if($s['best'])
        El mejor día, {{ $s['best']['day']->locale('es')->translatedFormat('l j') }}: <b>{{ $n($s['best']['n']) }}</b>.
      @endif
    </p>
  </section>

  {{-- Right now --}}
  <p class="ad-st-now {{ $s['now']['n'] ? 'is-on' : '' }}">
    @if($s['now']['n'])
      <b>Ahora en la web: {{ $s['now']['n'] }} {{ $plural($s['now']['n'], 'persona', 'personas') }}</b>
      — {{ collect($s['now']['where'])->map(fn ($w) => $w['n'] . ' en ' . $w['label'])->implode(', ') }}
    @else
      Ahora mismo no hay nadie en la web.
    @endif
  </p>

  <ul class="ad-st-facts">
    <li><span class="ad-st-facts__k">Visitas</span><span class="ad-st-facts__v">{{ $n($s['visits'][0]) }}</span></li>
    <li><span class="ad-st-facts__k">Páginas vistas</span><span class="ad-st-facts__v">{{ $n($s['views'][0]) }}</span></li>
    <li><span class="ad-st-facts__k">Desde el móvil</span><span class="ad-st-facts__v">{{ $s['mobile'] !== null ? $s['mobile'] . ' %' : '—' }}</span></li>
    <li><span class="ad-st-facts__k">Tiempo por visita</span><span class="ad-st-facts__v">{{ $s['visitTime'] ? Stats::dur($s['visitTime']) : '—' }}</span></li>
  </ul>
@endif

  {{-- Did they write --}}
  <section class="ad-st-sec">
    <h3 class="ad-st-h">Te contactaron</h3>
    <ul class="ad-st-facts">
      @if($s['any'])
        <li><span class="ad-st-facts__k">Pulsaron WhatsApp</span><span class="ad-st-facts__v">{{ $n($s['wap']) }}</span></li>
        <li><span class="ad-st-facts__k">Pulsaron llamar</span><span class="ad-st-facts__v">{{ $n($s['tel'][0]) }}</span></li>
      @endif
      <li><span class="ad-st-facts__k">Mensajes del formulario</span><span class="ad-st-facts__v">{{ $n($db['messages'][0]) }}</span></li>
      <li><span class="ad-st-facts__k">Te ofrecieron su coche</span><span class="ad-st-facts__v">{{ $n($db['offers'][0]) }}</span></li>
    </ul>
    @if($s['any'] && $s['wap'] > 0 && $s['people'][0] >= 10)
      <p class="ad-st-say">1 de cada {{ max(1, (int) round($s['people'][0] / $s['wap'])) }} personas que entran pulsa WhatsApp.</p>
    @endif
    @if($s['any'] && $s['mail'][0] > 0)
      <p class="ad-st-quiet">Y {{ $n($s['mail'][0]) }} {{ $plural($s['mail'][0], 'pulsó', 'pulsaron') }} en tu e-mail.</p>
    @endif
  </section>

@if($s['any'])
  {{-- Which cars --}}
  <section class="ad-st-sec">
    <h3 class="ad-st-h">Los coches</h3>
    @php $cars = $s['cars']; @endphp
    @if($cars)
      <label class="ad-st-sort">Ordenar por
        <select data-st-sort>
          <option value="views">Vistas</option>
          <option value="people">Personas</option>
          <option value="wa">WhatsApp</option>
          <option value="photos">Fotos</option>
          <option value="time">Tiempo</option>
          <option value="qr">Cartel</option>
        </select>
      </label>
      <div class="ad-st-cars-wrap">
      <table class="ad-st-cars" data-st-cars>
        <thead>
          <tr>
            <th scope="col">Coche</th>
            @foreach(['views' => 'Vistas', 'people' => 'Personas', 'wa' => 'WhatsApp', 'photos' => 'Fotos', 'time' => 'Tiempo', 'end' => 'Al final', 'qr' => 'Cartel'] as $k => $h)
              <th scope="col" class="is-n" @if($k === 'views') aria-sort="descending" @endif>
                <button type="button" data-st-key="{{ $k }}">{{ $h }}</button>
              </th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @foreach($cars as $i => $c)
            <tr class="{{ $c['sold'] ? 'is-sold' : '' }} {{ $i >= 10 ? 'is-more' : '' }}"
                data-views="{{ $c['views'] }}" data-people="{{ $c['people'] }}" data-wa="{{ $c['wa'] }}"
                data-photos="{{ $c['photos'] ?? -1 }}" data-time="{{ $c['time'] ?? -1 }}" data-end="{{ $c['end'] ?? -1 }}" data-qr="{{ $c['qr'] }}">
              <th scope="row">
                <a class="ad-st-car" href="{{ route('admin.stats', ['coche' => $c['slug'], 'd' => $s['days']]) }}" data-st-car>
                  <span class="ad-st-car__img">@if($c['img'])<x-img :src="$c['img']" alt="" sizes="64px" :max="320" :fallback="320" />@endif</span>
                  <span class="ad-st-car__t">
                    <b>{{ $c['name'] }} {{ $c['year'] }}</b>
                    <em>{{ $c['sold'] ? 'Vendido' : ($c['price'] ? Stats::eur($c['price']) : '—') }}</em>
                  </span>
                </a>
              </th>
              <td class="is-n is-views">
                <span class="ad-st-cars__v">{{ $n($c['views']) }}</span><span class="ad-st-cars__k"> vistas</span>
                <span class="ad-st-cars__bar"><i style="width: {{ $c['pct'] }}%"></i></span>
              </td>
              <td class="is-n"><span class="ad-st-cars__v">{{ $n($c['people']) }}</span><span class="ad-st-cars__k"> personas</span></td>
              <td class="is-n {{ $c['wa'] ? 'is-wa' : '' }}"><span class="ad-st-cars__v">{{ $n($c['wa']) }}</span><span class="ad-st-cars__k"> WhatsApp</span></td>
              <td class="is-n"><span class="ad-st-cars__v">{{ $c['photos'] !== null ? $n($c['photos']) : '—' }}</span><span class="ad-st-cars__k"> fotos</span></td>
              <td class="is-n"><span class="ad-st-cars__v">{{ Stats::dur($c['time']) }}</span><span class="ad-st-cars__k"> en la ficha</span></td>
              <td class="is-n"><span class="ad-st-cars__v">{{ $c['end'] !== null ? $c['end'] . ' %' : '—' }}</span><span class="ad-st-cars__k"> al final</span></td>
              <td class="is-n"><span class="ad-st-cars__v">{{ $n($c['qr']) }}</span><span class="ad-st-cars__k"> QR</span></td>
            </tr>
          @endforeach
        </tbody>
      </table>
      </div>
      @if(count($cars) > 10)
        <button class="ad-st-more" type="button" data-st-more hidden>Ver los {{ count($cars) }} coches</button>
      @endif
      <p class="ad-st-quiet">Fotos: las que ve cada persona de media. Tiempo: de media en la ficha. Al final: cuántos
        bajan hasta abajo. Cartel: escaneos del QR del cartel impreso.</p>
    @else
      <p class="ad-st-quiet">No hay coches a la venta.</p>
    @endif
  </section>

  {{-- What to buy, where they are, the photographs --}}
  @include('admin.stats._brands', ['b' => $s['brands'], 'pr' => $s['prices']])
  @include('admin.stats._map', ['g' => $s['places']])
  @include('admin.stats._photos', ['ph' => $s['photos']])

  {{-- Where from, on what --}}
  <div class="ad-st-two">
    <section class="ad-st-sec">
      <h3 class="ad-st-h">De dónde llegan</h3>
      @if($s['sources'])
        @include('admin.stats._rank', ['rows' => array_map(fn ($r) => $r + ['share' => $r['pct']], $s['sources'])])
        <p class="ad-st-quiet">Un enlace abierto desde WhatsApp en un iPhone llega sin origen, así que cuenta como «Directo».</p>
      @else
        <p class="ad-st-quiet">Todavía nada.</p>
      @endif
    </section>
    <section class="ad-st-sec">
      <h3 class="ad-st-h">Con qué la ven</h3>
      @if($s['devices'])
        @include('admin.stats._rank', ['rows' => array_map(fn ($r) => $r + ['share' => $r['pct']], $s['devices'])])
      @else
        <p class="ad-st-quiet">Todavía nada.</p>
      @endif
    </section>
  </div>

  <div class="ad-st-two">
    <section class="ad-st-sec">
      <h3 class="ad-st-h">Qué páginas ven</h3>
      @if($s['pages'])
        @include('admin.stats._rank', ['rows' => array_map(fn ($r) => $r + ['sub' => collect([
            $r['time'] !== null ? Stats::dur($r['time']) . ' de media' : null,
            $r['end'] !== null ? $r['end'] . ' % llega al final' : null,
          ])->filter()->implode(' · ')], $s['pages'])])
      @else
        <p class="ad-st-quiet">Todavía nada.</p>
      @endif
    </section>
    <section class="ad-st-sec">
      <h3 class="ad-st-h">Qué hacen</h3>
      @if($s['events'])
        @php $evMax = max(1, $s['events'][0]['n']); @endphp
        @include('admin.stats._rank', ['rows' => array_map(fn ($r) => $r + ['pct' => (int) round(100 * $r['n'] / $evMax)], $s['events'])])
      @else
        <p class="ad-st-quiet">Todavía nadie ha abierto la galería, el vídeo ni un formulario.</p>
      @endif
    </section>
  </div>

  @if(! empty($s['search']['modelo']) || ! empty($s['search']['precio']))
    <section class="ad-st-sec">
      <h3 class="ad-st-h">Lo que buscan</h3>
      <div class="ad-st-search">
        @foreach(['modelo' => 'Modelo', 'precio' => 'Precio'] as $k => $h)
          @if(! empty($s['search'][$k]))
            <div>
              <p class="ad-st-search__h">{{ $h }}</p>
              <ul class="ad-st-list">
                @foreach(array_slice($s['search'][$k], 0, 8) as $r)
                  <li><span>{{ $r['label'] }}</span><span>{{ $n($r['n']) }}</span></li>
                @endforeach
              </ul>
            </div>
          @endif
        @endforeach
      </div>
    </section>
  @endif

  <section class="ad-st-sec">
    <h3 class="ad-st-h">Las últimas visitas</h3>
    @include('admin.stats._feed', ['feed' => $s['visitsFeed']])
  </section>
@endif

  @include('admin.stats._history', ['h' => $s['history']])

  {{-- The stock: from the panel's own records, so it is here from day one. --}}
  <section class="ad-st-sec">
    <h3 class="ad-st-h">Tu stock</h3>
    <ul class="ad-st-facts">
      <li><span class="ad-st-facts__k">A la venta</span><span class="ad-st-facts__v">{{ $n($db['live']) }}</span></li>
      <li><span class="ad-st-facts__k">Valor del stock</span><span class="ad-st-facts__v">{{ Stats::eur($db['value']) }}</span></li>
      <li><span class="ad-st-facts__k">Precio medio</span><span class="ad-st-facts__v">{{ $db['avg'] ? Stats::eur($db['avg']) : '—' }}</span></li>
      <li><span class="ad-st-facts__k">Días a la venta, de media</span><span class="ad-st-facts__v">{{ $db['age'] ?? '—' }}</span></li>
    </ul>
    <p class="ad-st-say">
      @if($db['oldest'])
        El que más lleva: <a href="{{ route('admin.vehicles.edit', $db['oldest']['slug']) }}">{{ $db['oldest']['name'] }}</a>,
        {{ $db['oldest']['days'] }} días.
      @endif
      Vendidos: {{ $n($db['sold']) }} en total{{ $db['soldNow'] ? ', ' . $n($db['soldNow']) . ' en estos ' . $s['days'] . ' días' : '' }}.
      @if($db['added']) Añadidos en estos {{ $s['days'] }} días: {{ $n($db['added']) }}.@endif
      Opiniones en la portada: {{ $n($db['reviews']) }}.
      @if($db['referrers']) Pidieron su enlace de recomendación: {{ $n($db['referrers']) }}.@endif
    </p>
  </section>

  <section class="ad-st-sec ad-st-me">
    <h3 class="ad-st-h">Tus visitas, aparte</h3>
    <p class="ad-st-say">Lo que miráis tú y tu equipo se apunta aquí al momento —páginas, segundos, fotos, WhatsApp—, para
      que veas que todo se registra, y no se suma a ninguna cifra de arriba.</p>
    @if($s['team'])
      @include('admin.stats._feed', ['feed' => $s['team']])
    @else
      <p class="ad-st-quiet">Nada desde ayer.</p>
    @endif
    <p class="ad-st-say">Cuentan como tuyos los móviles y ordenadores que han entrado en este panel, aunque luego miren la
      web sin entrar, y lo que se vea desde la misma conexión durante las 6 horas siguientes (el wifi de casa o de la
      tienda). Para un móvil que nunca entra en el panel —el de un compañero— ábrele este enlace en él:</p>
    <div class="ad-st-me__row">
      <span class="ad-st-me__qr" role="img" aria-label="Código QR del enlace «No contar este dispositivo»">{!! (new \chillerlan\QRCode\QRCode(new \chillerlan\QRCode\QROptions([
        'outputType' => 'svg', 'outputBase64' => false, 'eccLevel' => \chillerlan\QRCode\Common\EccLevel::M,
        'addQuietzone' => false, 'svgUseFillAttributes' => true, 'drawLightModules' => false,
        'connectPaths' => true, 'svgAddXmlHeader' => false,
      ])))->render($s['me']) !!}</span>
      <div>
        <input class="ad-in ad-st-me__url" type="text" readonly value="{{ $s['me'] }}" aria-label="Enlace «No contar este dispositivo»" data-st-copy>
        <p class="ad-st-quiet">Escanéalo con ese móvil, o envíaselo. En cada navegador que use (Safari, Chrome) una vez.</p>
      </div>
    </div>
  </section>

  <p class="ad-st-foot">
    @if($since)Se cuenta desde el {{ $since }}, @else Se cuenta @endif sin cookies y sin guardar la IP de nadie.
    Una persona que vuelve otro día cuenta otra vez. No cuentan los robots ni los servidores que se hacen pasar por
    navegadores, ni tus visitas.
  </p>
</div>
