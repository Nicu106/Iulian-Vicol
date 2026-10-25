{{-- Visits, newest first: the device as the browser announces it, where it came
     from, and the pages in order with the time on each and the presses. Nobody
     is named — there is nothing to name them with. --}}
@php
  $now = \App\Support\Stats::clock();
  $when = function ($t) use ($now) {
      $min = intdiv($now->getTimestamp() - $t->getTimestamp(), 60);
      if ($min < 1) return 'ahora';
      if ($min < 60) return 'hace ' . $min . ' min';
      if ($t->isSameDay($now)) return 'hoy, ' . $t->format('H:i');
      if ($t->isSameDay($now->subDay())) return 'ayer, ' . $t->format('H:i');
      return $t->locale('es')->translatedFormat('D j M') . ', ' . $t->format('H:i');
  };
@endphp
@if($feed)
  <ol class="ad-st-feed">
    @foreach($feed as $v)
      <li class="{{ $v['wa'] ? 'is-wa' : '' }}">
        <p class="ad-st-feed__h">
          <b>{{ $v['who'] }}</b>@if(! empty($v['place'])) · {{ $v['place'] }}@endif · {{ $v['source'] === 'Directo' ? 'entró directo' : 'llegó desde ' . $v['source'] }}
          <time datetime="{{ $v['at']->toIso8601String() }}">{{ $when($v['at']) }}</time>
        </p>
        <p class="ad-st-feed__p">
          @foreach($v['steps'] as $i => $s)
            @if($i)<span class="ad-st-feed__to" aria-hidden="true">→</span>@endif
            <span class="ad-st-feed__s">{{ $s['label'] }}@php
              $bits = array_filter([
                $s['d'] ? \App\Support\Stats::dur($s['d']) : null,
                $s['ph'] ? $s['ph'] . ' ' . ($s['ph'] === 1 ? 'foto' : 'fotos') : null,
              ]);
            @endphp @if($bits)<em>({{ implode(', ', $bits) }})</em>@endif</span>
            @foreach($s['events'] as $e)
              <span class="ad-st-feed__to" aria-hidden="true">→</span>
              <span class="ad-st-feed__e {{ $e === 'WhatsApp' ? 'is-wa' : '' }}">{{ $e === 'WhatsApp' ? 'pulsó WhatsApp' : $e }}</span>
            @endforeach
          @endforeach
        </p>
      </li>
    @endforeach
  </ol>
@else
  <p class="ad-st-quiet">Todavía no hay visitas en estos días.</p>
@endif
