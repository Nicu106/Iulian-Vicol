{{-- The Panel's popup: the same content as /admin/estadisticas, loaded into a
     native <dialog> (top layer, Escape and the phone's back gesture close it,
     focus stays inside). A sheet from the bottom on a phone, a wide panel from
     700px — the review window's pattern (.ad-cnet-dlg). The period switch and a
     car's own numbers load in place; "Abrir como página" leaves for the page.
     If the window cannot open or the content cannot load, the button is a link
     to the page. --}}
<dialog class="ad-st-dlg" id="ad-st-dlg" aria-labelledby="ad-st-dlg-h">
  <div class="ad-st-dlg__head">
    <div class="ad-st-dlg__top">
      <h2 class="ad-st-dlg__h" id="ad-st-dlg-h" tabindex="-1">Estadísticas</h2>
      <a class="ad-st-dlg__page" href="{{ route('admin.stats') }}" data-st-page>Abrir como página</a>
      <button class="ad-btn ad-btn--q ad-btn--s" type="button" data-st-close>Cerrar</button>
    </div>
    @include('admin.stats._switch', ['days' => 30, 'car' => null])
  </div>
  <div class="ad-st-dlg__body" data-st-body></div>
</dialog>

@push('js')
@include('admin.stats._script')
<script>
(function () {
  var dlg = document.getElementById('ad-st-dlg');
  var open = document.querySelector('[data-stats]');
  if (!dlg || !open || typeof dlg.showModal !== 'function' || !window.fetch) return;
  var body = dlg.querySelector('[data-st-body]'), page = dlg.querySelector('[data-st-page]');
  var base = open.getAttribute('href'), state = { d: 30, coche: null }, seq = 0;

  function url(frag) {
    var u = new URL(base, location.href);
    u.searchParams.set('d', state.d);
    if (state.coche) u.searchParams.set('coche', state.coche);
    if (frag) u.searchParams.set('frag', '1');
    return u.toString();
  }
  function load() {
    var mine = ++seq, target = url(false);
    page.href = target;
    dlg.querySelectorAll('[data-st-d]').forEach(function (a) {
      var on = +a.dataset.stD === state.d;
      a.classList.toggle('is-on', on);
      if (on) a.setAttribute('aria-current', 'true'); else a.removeAttribute('aria-current');
    });
    body.setAttribute('aria-busy', 'true');
    body.classList.add('is-busy');
    fetch(url(true), { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) {
        if (!r.ok || r.redirected) throw new Error('no');
        return r.text();
      })
      .then(function (html) {
        if (mine !== seq) return;
        body.innerHTML = html;
        body.scrollTop = 0;
        body.removeAttribute('aria-busy');
        body.classList.remove('is-busy');
        window.mcStats.wire(body);
      })
      .catch(function () { location.href = target; });
  }
  function parse(href) {
    var u = new URL(href, location.href);
    state.d = +(u.searchParams.get('d') || state.d) || 30;
    state.coche = u.searchParams.get('coche');
  }

  open.addEventListener('click', function (e) {
    if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button > 0) return;
    e.preventDefault();
    state = { d: 30, coche: null };
    body.innerHTML = '<p class="ad-st-quiet">Cargando…</p>';
    dlg.showModal();
    dlg.querySelector('#ad-st-dlg-h').focus();
    load();
  });
  dlg.addEventListener('click', function (e) {
    if (e.target === dlg) { dlg.close(); return; }
    if (e.target.closest('[data-st-close]')) { dlg.close(); return; }
    var a = e.target.closest('a[data-st-d], a[data-st-car], a[data-st-all]');
    if (!a || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    e.preventDefault();
    if (a.hasAttribute('data-st-d')) { state.d = +a.dataset.stD; }
    else { parse(a.href); }
    load();
  });
})();
</script>
@endpush
