{{-- A ranked list: the word, the number, a thin bar under them. The bar is the
     share of the whole ($r['share']) or, for lists that are not parts of one
     whole, the size against the largest ($r['pct']). Navy on a hairline track. --}}
<ul class="ad-st-rank">
  @foreach($rows as $r)
    <li>
      <span class="ad-st-rank__l">{{ $r['label'] }}</span>
      <span class="ad-st-rank__v">{{ \App\Support\Stats::num($r['n']) }}@isset($r['share'])<em>{{ $r['share'] }} %</em>@endisset</span>
      <span class="ad-st-rank__bar"><i style="width: {{ max(1, $r['share'] ?? $r['pct'] ?? 0) }}%"></i></span>
      @if(! empty($r['sub']))<span class="ad-st-rank__s">{{ $r['sub'] }}</span>@endif
    </li>
  @endforeach
</ul>
