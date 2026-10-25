{{-- One car (App\Support\Stats::car): who looked at it, for how long, what they
     did, where they came from. Same order and the same one bold thing as the
     whole-site view. --}}
@php
  use App\Support\Stats;
  $n = fn ($v) => Stats::num($v);
  $w = $c['cur'];
  $name = trim($car->brand . ' ' . $car->model . ' ' . $car->year);
  $since = $c['first'] ? $c['first']->locale('es')->translatedFormat('j \d\e F') : null;
  $partial = $c['first'] && $c['first']->gt($c['from']);
  $span = $partial ? 'desde el ' . $since : 'en los últimos ' . $c['days'] . ' días';
@endphp
<div class="ad-st" data-days="{{ $c['days'] }}">

  <div class="ad-st-one">
    <span class="ad-st-car__img">@if($car->primary_image)<x-img :src="$car->primary_image" alt="" sizes="64px" :max="320" :fallback="320" />@endif</span>
    <div class="ad-st-one__t">
      <p class="ad-st-one__n">{{ $name }}</p>
      <p class="ad-st-one__p">
        {{ $car->status === 'sold' ? 'Vendido' : ($car->price ? Stats::eur($car->price) : '') }}
        · <a href="{{ route('admin.vehicles.edit', $car->slug) }}">Editar</a>
        · <a href="{{ url('/coche/' . $car->slug) }}">Ver en la web</a>
        · <a href="{{ route('admin.stats', ['d' => $c['days']]) }}" data-st-all>Todos los coches</a>
      </p>
    </div>
  </div>

@if(! $c['any'])
  <div class="ad-st-start">
    <p class="ad-st-start__t">Las visitas se cuentan desde hoy</p>
    <p class="ad-st-start__p">En cuanto alguien abra este coche, aquí verás cuántos lo ven, cuántas fotos miran,
      cuánto tiempo y quién pulsa WhatsApp.</p>
  </div>
@else
  <section class="ad-st-hero" aria-label="Personas">
    <p class="ad-st-hero__n">{{ $n($w['people']) }}</p>
    <p class="ad-st-hero__t">{{ $w['people'] === 1 ? 'persona vio este coche' : 'personas vieron este coche' }} {{ $span }}</p>
    @include('admin.stats._bars', ['series' => $c['series'], 'first' => $c['first'], 'what' => 'personas'])
    @if($w['rate'] !== null)
      <p class="ad-st-hero__f"><b>{{ $w['rate'] }} de cada 100</b> que lo ven te escriben o te llaman.</p>
    @endif
  </section>

  <ul class="ad-st-facts ad-st-facts--wide">
    <li><span class="ad-st-facts__k">Vistas</span><span class="ad-st-facts__v">{{ $n($w['views']) }}</span></li>
    <li><span class="ad-st-facts__k">Pulsaron WhatsApp</span><span class="ad-st-facts__v">{{ $n($w['wa']) }}</span></li>
    <li><span class="ad-st-facts__k">Pulsaron llamar</span><span class="ad-st-facts__v">{{ $n($w['tel']) }}</span></li>
    <li><span class="ad-st-facts__k">Fotos por persona</span><span class="ad-st-facts__v">{{ $w['photos'] !== null ? $n($w['photos']) : '—' }}</span></li>
    <li><span class="ad-st-facts__k">Tiempo en la ficha</span><span class="ad-st-facts__v">{{ Stats::dur($w['time']) }}</span></li>
    <li><span class="ad-st-facts__k">Llegan al final</span><span class="ad-st-facts__v">{{ $w['end'] !== null ? $w['end'] . ' %' : '—' }}</span></li>
    <li><span class="ad-st-facts__k">Pusieron el vídeo</span><span class="ad-st-facts__v">{{ $n($w['video']) }}</span></li>
    <li><span class="ad-st-facts__k">Escanearon el cartel</span><span class="ad-st-facts__v">{{ $n($w['qr']) }}</span></li>
  </ul>

  <div class="ad-st-two">
    <section class="ad-st-sec">
      <h3 class="ad-st-h">De dónde llegan</h3>
      @if($c['sources'])
        @include('admin.stats._rank', ['rows' => array_map(fn ($r) => $r + ['share' => $r['pct']], $c['sources'])])
      @else
        <p class="ad-st-quiet">Todavía nada.</p>
      @endif
    </section>
    <section class="ad-st-sec">
      <h3 class="ad-st-h">Qué hacen</h3>
      @if($c['events'])
        @php $evMax = max(1, $c['events'][0]['n']); @endphp
        @include('admin.stats._rank', ['rows' => array_map(fn ($r) => $r + ['pct' => (int) round(100 * $r['n'] / $evMax)], $c['events'])])
      @else
        <p class="ad-st-quiet">Todavía nada.</p>
      @endif
    </section>
  </div>

  {{-- Its photographs, one by one, in the page's order: who saw each, how long, who opened it big. --}}
  @php $pics = $c['photos']; $maxSec = max(1, max(array_map(fn ($p) => (int) $p['sec'], $pics['list']) ?: [0])); @endphp
  @if($pics['list'])
    <section class="ad-st-sec">
      <h3 class="ad-st-h">Sus fotos</h3>
      @if($pics['views'])
        @php
          $ranked = collect($pics['list'])->filter(fn ($p) => $p['seen'] >= 3)->sortByDesc('sec');
          $hold = $ranked->first();
        @endphp
        @if($hold && $pics['views'] >= 10)
          <p class="ad-st-say ad-st-say--lead">La que más retiene es la {{ $hold['n'] }}.ª ({{ Stats::dur($hold['sec']) }} de media).</p>
        @endif
        <ol class="ad-st-pics">
          @foreach($pics['list'] as $p)
            <li class="{{ $p['seen'] ? '' : 'is-none' }}">
              <span class="ad-st-pics__img"><x-img :src="$p['path']" alt="" sizes="96px" :max="320" :fallback="320" /></span>
              <span class="ad-st-pics__n">{{ $p['n'] }}</span>
              <span class="ad-st-pics__v"><b>{{ $p['pct'] !== null ? $p['pct'] . ' %' : '—' }}</b> la ven</span>
              <span class="ad-st-pics__v">{{ $p['sec'] !== null ? Stats::dur($p['sec']) : '—' }}@if($p['big']) · {{ $p['big'] }} en grande @endif</span>
              <span class="ad-st-pics__bar"><i style="width: {{ $p['pct'] ?? 0 }}%"></i></span>
            </li>
          @endforeach
        </ol>
        <p class="ad-st-quiet">De las {{ $n($pics['views']) }} veces que se abrió la ficha: qué parte vio cada foto
          (0,7 s o más en pantalla), cuánto tiempo de media y cuántas la abrieron en grande.</p>
      @else
        <p class="ad-st-quiet">Todavía nadie ha pasado sus fotos en estos días.</p>
      @endif
    </section>
  @endif

  @if($c['places'])
    <section class="ad-st-sec">
      <h3 class="ad-st-h">De dónde son</h3>
      @include('admin.stats._rank', ['rows' => array_map(fn ($r) => $r + ['share' => $r['pct']], array_slice($c['places'], 0, 10))])
    </section>
  @endif

  <section class="ad-st-sec">
    <h3 class="ad-st-h">Visitas que lo vieron</h3>
    @include('admin.stats._feed', ['feed' => $c['feed']])
  </section>

  <p class="ad-st-foot">Se cuenta desde el {{ $since }}, sin cookies y sin guardar la IP de nadie. Una persona que vuelve
    otro día cuenta otra vez. Tus visitas no cuentan.</p>
@endif

@if($c['before'])
  <p class="ad-st-quiet">En la web anterior, hasta el 6 de octubre, su página se abrió {{ $n($c['before']['n']) }} veces en
    {{ $c['before']['days'] }} días. Aquel contador sumaba también robots, recargas y tus propias visitas: sirve solo para
    comparar coches que estuvieron a la venta las mismas semanas.</p>
@endif
</div>
