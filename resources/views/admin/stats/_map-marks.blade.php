{{-- A map's towns (StatsInsights::spainMap / wide): the dots, biggest first, then the names. --}}
<g class="ad-st-map__dots">
  @foreach($marks['dots'] as $d)
    <circle cx="{{ $d['x'] }}" cy="{{ $d['y'] }}" r="{{ $d['r'] }}"><title>{{ $d['title'] }}</title></circle>
  @endforeach
</g>
<g class="ad-st-map__names" aria-hidden="true">
  @foreach($marks['names'] as $p)
    <text x="{{ $p['x'] }}" y="{{ $p['y'] }}" text-anchor="{{ $p['end'] ? 'end' : 'start' }}">{{ $p['name'] }}</text>
  @endforeach
</g>
