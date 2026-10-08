{{-- The viewer: one review, whole — photograph and every word — with the
     previous and next ones. A native modal <dialog>: the rest of the page is
     inert while it is open, Esc closes it, and the phone's Back closes it too
     (the script gives it a history entry). Filled from #ms-data. --}}
<dialog class="ms-dlg" id="ms-dlg" aria-labelledby="ms-dlg-by">
  <div class="ms-dlg__in">
    <div class="ms-dlg__ph"></div>
    <div class="ms-dlg__txt">
      <blockquote class="ms-dlg__q"></blockquote>
      <p class="ms-dlg__by" id="ms-dlg-by"></p>
    </div>
    <div class="ms-dlg__nav">
      <button class="ms-ctl" type="button" data-dlg="-1" aria-label="Opinión anterior">
        <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M15 4 7 12l8 8" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/></svg>
      </button>
      <button class="ms-ctl" type="button" data-dlg="1" aria-label="Opinión siguiente">
        <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M9 4l8 8-8 8" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/></svg>
      </button>
    </div>
    <button class="ms-ctl ms-dlg__x" type="button" data-dlg="x" aria-label="Cerrar">
      <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M5 5l14 14M19 5 5 19" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/></svg>
    </button>
    <p class="ms-sr" aria-live="polite" data-dlg-live></p>
  </div>
</dialog>
<script type="application/json" id="ms-data">{!! $json !!}</script>
