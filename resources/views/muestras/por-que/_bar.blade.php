{{-- Review bar for /muestras/por-que/{v}: English, outside the page's own design.
     Switch between the versions of the same approach, back to the list, or hide it
     (remembered for the tab) to look at the page clean. --}}
@php
  $approach = $variants[$current][2];
  $siblings = array_filter($variants, fn ($x) => $x[2] === $approach);
  ksort($siblings, SORT_NATURAL);
@endphp
<nav class="rvb" id="rvb" aria-label="Review versions">
  <a class="rvb__all" href="/muestras/por-que">All versions</a>
  @if(count($siblings) > 1)
    @foreach($siblings as $k => $x)
      <a class="rvb__v{{ (string) $k === (string) $current ? ' is-on' : '' }}" href="/muestras/por-que/{{ $k }}"
         @if((string) $k === (string) $current) aria-current="page" @endif>{{ str_contains((string) $k, '.') ? $k : $k . '.0' }}</a>
    @endforeach
  @else
    <span class="rvb__v is-on">{{ $variants[$current][0] }}</span>
  @endif
  <button class="rvb__x" type="button" aria-label="Hide the review bar">&times;</button>
</nav>
<style>
  .rvb{position:fixed;left:12px;bottom:12px;z-index:2147483000;display:flex;align-items:center;gap:2px;padding:4px;
    border-radius:999px;background:rgba(5,8,15,.86);-webkit-backdrop-filter:blur(8px);backdrop-filter:blur(8px);
    font:600 13px/1 "DM Sans",system-ui,sans-serif;box-shadow:0 4px 18px rgba(0,0,0,.18)}
  .rvb a,.rvb span,.rvb button{display:inline-flex;align-items:center;min-height:36px;padding:0 12px;border-radius:999px;
    color:#C9D2DE;text-decoration:none;border:0;background:none;font:inherit;cursor:pointer}
  .rvb a:hover,.rvb button:hover{color:#fff}
  .rvb .is-on{background:#fff;color:#05080F}
  .rvb__all{border-right:1px solid rgba(255,255,255,.14)!important;border-radius:999px 0 0 999px!important}
  .rvb__x{font-size:18px;padding:0 10px}
  .rvb a:focus-visible,.rvb button:focus-visible{outline:2px solid #fff;outline-offset:1px}
  .rvb[hidden]{display:none}
  @media print{.rvb{display:none}}
</style>
<script>
(function () {
  var b = document.getElementById('rvb'), k = 'rvb-hidden';
  try { if (sessionStorage.getItem(k) === '1' || /[?&]clean\b/.test(location.search)) b.hidden = true; } catch (e) {}
  b.querySelector('.rvb__x').addEventListener('click', function () {
    b.hidden = true; try { sessionStorage.setItem(k, '1'); } catch (e) {}
  });
})();
</script>
