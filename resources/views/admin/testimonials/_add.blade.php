{{-- "Añadir opinión": two ways in, one window.

     Front: "La escribo yo" (the form, as before) or "Le mando un enlace". The
     second turns the card over: how long the link stays open and, if he wants,
     the customer's name (it is written for them in their form). "Crear el
     enlace" makes it (Admin\ReviewLinkController) and the same face shows what
     to do with it: the QR to scan on his phone, WhatsApp, or copy.
     "Ver el QR" on an open link opens the window on that face directly.

     A native <dialog>: top layer, Escape and the phone's back gesture close it,
     focus stays inside. Centred at every width. The face not on show is inert.
     Reduced motion: the faces swap without turning. Without <dialog> or fetch,
     "Añadir opinión" stays the link to the form it always was. --}}
<dialog class="ad-rv" id="ad-rv" aria-labelledby="ad-rv-h1">
  <div class="ad-rv__card">

    <section class="ad-rv__face ad-rv__front" data-rv-front>
      <h2 class="ad-rv__h" id="ad-rv-h1">Añadir una opinión</h2>
      <div class="ad-rv__pick">
        <a class="ad-rv__opt" href="{{ route('admin.testimonials.create') }}">
          <b class="ad-rv__opt-h">La escribo yo</b>
          <span class="ad-rv__opt-p">Su nombre, lo que te dijo y su foto.</span>
        </a>
        <button class="ad-rv__opt" type="button" data-rv-flip>
          <b class="ad-rv__opt-h">Le mando un enlace</b>
          <span class="ad-rv__opt-p">La escribe el cliente desde su móvil. Tú la lees y la publicas.</span>
        </button>
      </div>
      <button class="ad-rv__x" type="button" data-rv-close>Cerrar</button>
    </section>

    <section class="ad-rv__face ad-rv__back" data-rv-back-face inert>
      <form class="ad-rv__ask" data-rv-ask action="{{ route('admin.review-links.store') }}" method="post">
        @csrf
        <h2 class="ad-rv__h" tabindex="-1">Un enlace para tu cliente</h2>
        <div class="ad-field">
          <span>¿Cuánto tiempo está abierto?</span>
          <div class="ad-pills ad-rv__days">
            @foreach(\App\Models\ReviewLink::PERIODS as $d => $t)
              <label class="ad-pill"><input type="radio" name="days" value="{{ $d }}" @checked($d === \App\Models\ReviewLink::DEFAULT_DAYS)><span>{{ $t }}</span></label>
            @endforeach
          </div>
        </div>
        <label class="ad-field">
          <span>Su nombre <em>— si quieres, le sale ya escrito</em></span>
          <input class="ad-in" type="text" name="name" maxlength="80" autocomplete="off" autocapitalize="words" enterkeyhint="go">
        </label>
        <p class="ad-err" data-rv-err role="alert" hidden></p>
        <div class="ad-rv__go">
          <button class="ad-btn" type="submit">Crear el enlace</button>
          <button class="ad-btn ad-btn--q" type="button" data-rv-unflip>Volver</button>
        </div>
      </form>

      <div class="ad-rv__done" data-rv-done hidden>
        <h2 class="ad-rv__h" tabindex="-1" data-rv-done-h>Tu enlace</h2>
        <p class="ad-rv__p" data-rv-until></p>
        <div class="ad-rv__qr" role="img" aria-label="Código QR del enlace" data-rv-qr></div>
        <p class="ad-rv__url" data-rv-url></p>
        <div class="ad-rv__go">
          <a class="ad-btn ad-rv__wa" href="#" target="_blank" rel="noopener" data-rv-wa>Enviar por WhatsApp</a>
          <button class="ad-btn ad-btn--q" type="button" data-rv-copy>Copiar el enlace</button>
        </div>
        <button class="ad-rv__x" type="button" data-rv-close>Hecho</button>
      </div>
    </section>

  </div>
</dialog>

@push('js')
<script>
(function () {
  var dlg = document.getElementById('ad-rv');
  if (!dlg || typeof dlg.showModal !== 'function' || !window.fetch) return;
  var front = dlg.querySelector('[data-rv-front]'), back = dlg.querySelector('[data-rv-back-face]');
  var ask = dlg.querySelector('[data-rv-ask]'), done = dlg.querySelector('[data-rv-done]');
  var err = dlg.querySelector('[data-rv-err]'), copyB = dlg.querySelector('[data-rv-copy]');
  var url = '', made = false;

  function face(toBack, animate) {
    if (!animate) dlg.classList.add('is-still');
    dlg.classList.toggle('is-flipped', toBack);
    front.inert = toBack; back.inert = !toBack;
    if (!animate) { void dlg.offsetWidth; dlg.classList.remove('is-still'); }
  }
  function reset() {
    face(false, false);
    ask.hidden = false; done.hidden = true; err.hidden = true;
    ask.reset(); url = '';
  }
  function show(d) {
    url = d.url;
    dlg.querySelector('[data-rv-done-h]').textContent = d.name ? 'El enlace de ' + d.name : 'Tu enlace';
    dlg.querySelector('[data-rv-until]').textContent = 'Abierto hasta el ' + d.until + '. Que escanee el código con la cámara, o mándaselo.';
    dlg.querySelector('[data-rv-qr]').innerHTML = d.qr;
    dlg.querySelector('[data-rv-url]').textContent = d.url;
    dlg.querySelector('[data-rv-wa]').href = d.wa;
    copyB.textContent = 'Copiar el enlace';
    ask.hidden = true; done.hidden = false;
    back.scrollTop = 0;
    dlg.querySelector('[data-rv-done-h]').focus({ preventScroll: true });
  }

  // "Añadir opinión": the window instead of the page
  document.querySelectorAll('[data-rv-add]').forEach(function (a) {
    a.addEventListener('click', function (e) { e.preventDefault(); reset(); dlg.showModal(); });
  });
  // "Ver el QR" on a link that is still open
  document.querySelectorAll('[data-rv-show]').forEach(function (b) {
    b.addEventListener('click', function () {
      fetch(b.getAttribute('data-rv-show'), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
        .then(function (r) { if (!r.ok) throw r; return r.json(); })
        .then(function (d) { reset(); face(true, false); dlg.showModal(); show(d); })
        .catch(function () { location.reload(); });
    });
  });

  dlg.querySelector('[data-rv-flip]').addEventListener('click', function () {
    face(true, true);
    ask.querySelector('.ad-rv__h').focus({ preventScroll: true });
  });
  dlg.querySelector('[data-rv-unflip]').addEventListener('click', function () {
    face(false, true);
    dlg.querySelector('[data-rv-flip]').focus();
  });
  dlg.querySelectorAll('[data-rv-close]').forEach(function (b) {
    b.addEventListener('click', function () { dlg.close(); });
  });
  // a press on the dimmed page around the card closes it
  dlg.addEventListener('click', function (e) { if (e.target === dlg) dlg.close(); });
  // once closed, a link made in it belongs on the page's list
  dlg.addEventListener('close', function () { if (made) location.reload(); });

  ask.addEventListener('submit', function (e) {
    e.preventDefault();
    var btn = ask.querySelector('[type=submit]');
    btn.disabled = true; err.hidden = true;
    fetch(ask.action, { method: 'POST', body: new FormData(ask), credentials: 'same-origin',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { if (!r.ok) throw r; return r.json(); })
      .then(function (d) { made = true; show(d); })
      .catch(function () { err.textContent = 'No se ha podido crear. Prueba otra vez.'; err.hidden = false; })
      .then(function () { btn.disabled = false; });
  });

  copyB.addEventListener('click', function () {
    var ok = function () { copyB.textContent = 'Copiado'; };
    var pick = function () {
      var r = document.createRange(); r.selectNodeContents(dlg.querySelector('[data-rv-url]'));
      var s = getSelection(); s.removeAllRanges(); s.addRange(r);
      copyB.textContent = 'Seleccionado';
    };
    if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(url).then(ok, pick);
    else pick();
  });
})();
</script>
@endpush
