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

  <section class="ad-st-sec">
    <h3 class="ad-st-h">Visitas que lo vieron</h3>
    @include('admin.stats._feed', ['feed' => $c['feed']])
  </section>

  <p class="ad-st-foot">Se cuenta desde el {{ $since }}, sin cookies y sin guardar la IP de nadie. Una persona que vuelve
    otro día cuenta otra vez. Tus visitas con el panel abierto no cuentan.</p>
@endif
</div>
