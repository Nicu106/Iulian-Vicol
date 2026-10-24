{{-- This car on the site (App\Support\Stats::car): one line for the last 30 days,
     and behind "Ver más" the full table — 7 days, 30 days, since counting began.
     Not part of the form: nothing here is saved with the car. --}}
@php
  use App\Support\Stats;
  $model = \App\Models\Vehicle::where('slug', $vehicle['slug'] ?? '')->first();
  $c = $model ? Stats::car($model) : null;
@endphp
@if($c)
  <section class="ad-box ad-st-box" aria-labelledby="ad-st-box-h">
    <div class="ad-st-box__top">
      <h2 class="ad-fs__h" id="ad-st-box-h">En la web</h2>
      <a class="ad-st-box__a" href="{{ route('admin.stats', ['coche' => $model->slug]) }}">Estadísticas</a>
    </div>
    @if(! $c['any'])
      <p class="ad-st-quiet">Desde hoy se cuentan las visitas de este coche.</p>
    @else
      @php
        $w = $c['w30'];
        $since = $c['first']->locale('es')->translatedFormat('j \d\e F');
        $rows = [
          'Vistas'                      => fn ($x) => Stats::num($x['views']),
          'Personas'                    => fn ($x) => Stats::num($x['people']),
          'Pulsaron WhatsApp'           => fn ($x) => Stats::num($x['wa']),
          'Pulsaron llamar'             => fn ($x) => Stats::num($x['tel']),
          'Fotos por persona'           => fn ($x) => $x['photos'] !== null ? Stats::num($x['photos']) : '—',
          'Pusieron el vídeo'           => fn ($x) => Stats::num($x['video']),
          'Tiempo en la ficha'          => fn ($x) => Stats::dur($x['time']),
          'Llegan al final'             => fn ($x) => $x['end'] !== null ? $x['end'] . ' %' : '—',
          'Escanearon el cartel (QR)'   => fn ($x) => Stats::num($x['qr']),
        ];
      @endphp
      <p class="ad-st-box__p">
        Últimos 30 días: <b>{{ Stats::num($w['views']) }}</b> {{ $w['views'] === 1 ? 'vista' : 'vistas' }},
        <b>{{ Stats::num($w['people']) }}</b> {{ $w['people'] === 1 ? 'persona' : 'personas' }},
        <b>{{ Stats::num($w['wa']) }}</b> WhatsApp.
        @if($w['rate'] !== null) {{ $w['rate'] }} de cada 100 que lo ven te escriben o te llaman.@endif
      </p>
      <details class="ad-st-box__more">
        <summary>Ver más</summary>
        <table class="ad-st-box__t">
          <thead><tr><th scope="col"><span class="ad-st-sr">Dato</span></th><th scope="col">7 días</th><th scope="col">30 días</th><th scope="col">Total</th></tr></thead>
          <tbody>
            @foreach($rows as $label => $f)
              <tr><th scope="row">{{ $label }}</th><td>{{ $f($c['w7']) }}</td><td>{{ $f($c['w30']) }}</td><td>{{ $f($c['all']) }}</td></tr>
            @endforeach
          </tbody>
        </table>
        <p class="ad-st-quiet">Total: desde el {{ $since }}, cuando empezó la cuenta.</p>
      </details>
    @endif
  </section>
@endif
