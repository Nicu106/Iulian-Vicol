{{-- People per day: the one bold thing on the page. Square navy columns from one
     baseline, capped at 24px and centred in their day, so seven days and ninety
     read the same. Today is lighter: it is not over. Days before counting began
     are left as an empty track, not as zero — nobody was counting.
     Hover (or a tap) shows the day; "Ver día por día" opens the same numbers
     as a table, for the keyboard and a screen reader.
     $series  list of ['day' => CarbonImmutable, 'n' => int]
     $first   ?CarbonImmutable — the first counted day
     $what    'personas' --}}
@php
  $max = max(1, collect($series)->max('n'));
  $today = \App\Support\Stats::today();
  $fmt = fn ($d) => \Illuminate\Support\Str::ucfirst($d->locale('es')->translatedFormat('D j M'));
@endphp
<figure class="ad-st-bars" style="--n: {{ count($series) }}">
  <div class="ad-st-bars__plot" aria-hidden="true">
    <span class="ad-st-bars__max">{{ \App\Support\Stats::num($max) }}</span>
    @foreach($series as $p)
      @php
        $off = $first === null || $p['day']->lt($first);
        $isToday = $p['day']->toDateString() === $today;
        $tip = $fmt($p['day']) . ' · ' . ($off ? 'sin contar' : \App\Support\Stats::num($p['n']) . ' ' . $what . ($isToday ? ', hasta ahora' : ''));
      @endphp
      <span class="ad-st-bar {{ $off ? 'is-off' : '' }} {{ $isToday ? 'is-today' : '' }}" data-tip="{{ $tip }}">
        <i style="height: {{ $off ? 0 : round(100 * $p['n'] / $max, 2) }}%"></i>
      </span>
    @endforeach
  </div>
  <figcaption class="ad-st-bars__x">
    <span>{{ $fmt($series[0]['day']) }}</span>
    <span>Hoy</span>
  </figcaption>
  <details class="ad-st-bars__data">
    <summary>Ver día por día</summary>
    <table>
      <caption class="ad-st-sr">{{ \Illuminate\Support\Str::ucfirst($what) }} por día</caption>
      <tbody>
        @foreach(array_reverse($series) as $p)
          <tr><th scope="row">{{ $fmt($p['day']) }}</th><td>{{ ($first === null || $p['day']->lt($first)) ? 'sin contar' : \App\Support\Stats::num($p['n']) }}</td></tr>
        @endforeach
      </tbody>
    </table>
  </details>
</figure>
