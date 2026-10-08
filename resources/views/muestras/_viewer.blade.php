{{-- One review whole: its photograph and every word. A native modal
     <dialog>: the page behind is inert, Tab stays inside, Esc closes it, and
     so does the phone's Back (the script gives it a history entry). Filled
     from #ms-data by whatever opened it ([data-open]). --}}
<dialog class="vw" id="ms-vw" aria-labelledby="ms-vw-by">
  <div class="vw__in">
    <div class="vw__ph"><img alt="" decoding="async"></div>
    <div class="vw__txt" tabindex="0">
      <blockquote class="vw__q"></blockquote>
      <p class="vw__by" id="ms-vw-by"></p>
    </div>
  </div>
  <button class="ms-rnd vw__x" type="button" aria-label="Cerrar">
    <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
  </button>
</dialog>
<script type="application/json" id="ms-data">{!! $json !!}</script>
