{{-- 7 / 30 / 90 días. Links, so the page works without JavaScript; in the popup
     the script loads the same address into the window instead. --}}
<nav class="ad-st-switch" aria-label="Periodo" data-st-switch>
  @foreach(\App\Support\Stats::PERIODS as $d)
    <a href="{{ route('admin.stats', array_filter(['d' => $d, 'coche' => $car?->slug])) }}" data-st-d="{{ $d }}"
       @if($d === $days) class="is-on" aria-current="true" @endif>{{ $d }} días</a>
  @endforeach
</nav>
