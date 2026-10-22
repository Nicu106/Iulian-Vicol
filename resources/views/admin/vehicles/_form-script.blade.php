{{-- The car form's script (admin/vehicles/_form). Five jobs, each written so
     that failing at it loses nothing:

     1. PHOTOS, ONE AT A TIME. A photo chosen in a group is drawn into a canvas
        at most 2400 px on its long edge and sent as a ~0.5-1 MB JPEG to
        POST /admin/fotos — two at a time, each with its own progress, retried
        with growing pauses (1, 2, 4, 8, 16 s…) and paused while the phone is
        offline. One that fails says so on its own thumbnail and can be retried
        or dropped; the other twenty-nine are unaffected. The browser decodes
        through <img>, which applies the camera's EXIF rotation in every engine
        since 2020; a file it cannot read (HEIC on Android) goes up untouched
        and the server converts it.
     2. GROUPS. Each photo's group is a native <select>; changing it moves the
        photo into that group's list, in upload order.
     3. A COPY ON THE PHONE. Every change is kept in localStorage under
        mc-car:{slug|new} — fields, the photos already sent, the draft token —
        and offered back after a reload or a crash. The panel's layout removes it
        when the save succeeds (session draft_clear).
     4. THE SESSION. GET /admin/ping every four minutes while the page is
        visible, and when the phone wakes, keeps it alive and hands back the
        current CSRF token. A 401 means it is gone: the page says so, offers the
        sign-in in another tab and carries on by itself when it is back.
     5. SAVING. Spanish messages next to the field; the button waits for the
        photos still going up, refreshes the token, then posts.
     6. THE ORDER (2026-10-17). A photo is held and dragged to its place, in its
        group or into another; the keyboard does the same. The order goes with
        the form (photo_order) and into the phone's copy; the last moves can be
        undone. See "the order" below. --}}
@push('js')
<script>
(function () {
  'use strict';
  var form = document.getElementById('ad-car');
  if (!form || !window.XMLHttpRequest || !window.FormData) return;
  form.noValidate = true;               // our messages, in Spanish, beside the field

  function $(s, r) { return (r || document).querySelector(s); }
  function $$(s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); }
  var TAGS = @json($tagNames);
  var GROUPS = ['exterior', 'interior', 'flaw', 'none'];
  var tokenIn = $('#ad-token'), saveGo = $('#ad-save-go'), saveMsg = $('#ad-save-msg'), notice = $('#ad-notice');
  var meta = $('meta[name="csrf-token"]');
  var isNew = saveGo.getAttribute('data-label') === 'Publicar el coche';

  /* ---------------------------------------------------------------- csrf */
  function csrf() { return (meta && meta.content) || ((form.elements._token || {}).value) || ''; }
  function setCsrf(t) {
    if (!t) return;
    if (meta) meta.content = t;
    $$('input[name="_token"]').forEach(function (i) { i.value = t; });
  }

  /* --------------------------------------------------------- the status line */
  var lost = false, waiting = false, savedAt = 0, flash = '', flashT = 0, said = '';
  var undoText = '', undoT = 0, restoring = false;
  function say(text, kind, actions) {
    // the same line again is left alone: rebuilt, a focused button would lose focus
    var key = (kind || '') + '|' + (text || '') + '|' + (actions || []).map(function (a) { return a[0]; }).join();
    if (key === said) return;
    said = key;
    saveMsg.textContent = text || '';
    saveMsg.className = 'ad-save__msg' + (kind ? ' is-' + kind : '');
    (actions || []).forEach(function (a) {
      var b = document.createElement('button');
      b.type = 'button'; b.className = 'ad-save__act'; b.textContent = a[0];
      b.addEventListener('click', a[1]);
      saveMsg.appendChild(document.createTextNode(' ')); saveMsg.appendChild(b);
    });
  }
  function status() {
    if (lost) return say('Tu sesión ha caducado; nada se ha perdido.', 'bad',
      [['Entrar otra vez', function () { window.open(form.getAttribute('data-login'), '_blank', 'noopener'); }]]);
    if (undoText) return say(undoText, '', [['Deshacer', function () { Order.undo(); }]]);
    var p = Up.pending(), b = Up.failed();
    if (p) return say((p === 1 ? 'Subiendo 1 foto' : 'Subiendo ' + p + ' fotos') + (waiting ? '; se guarda al terminar.' : '…'), 'busy');
    if (b) return say(b === 1 ? '1 foto no se ha subido.' : b + ' fotos no se han subido.', 'bad',
      [['Reintentar', function () { Up.retryFailed(); }], ['Quitarlas', function () { Up.dropFailed(); }]]);
    if (flash) return say(flash);
    say(savedAt ? 'Copia guardada en este teléfono.' : '');
  }
  function tell(text) { flash = text; clearTimeout(flashT); flashT = setTimeout(function () { flash = ''; status(); }, 2600); status(); }
  /* A move, and the way back from it: six seconds under the thumb, longer while
     the finger or the focus is on "Deshacer". Ctrl/⌘+Z does the same. */
  function offerUndo(text) {
    undoText = text; clearTimeout(undoT);
    undoT = setTimeout(endUndo, 6000);
    status();
    var b = saveMsg.querySelector('.ad-save__act');
    if (b && !b._held) {
      b._held = true;
      var hold = function () { clearTimeout(undoT); }, go = function () { clearTimeout(undoT); undoT = setTimeout(endUndo, 3000); };
      b.addEventListener('pointerenter', hold); b.addEventListener('focus', hold);
      b.addEventListener('pointerleave', go); b.addEventListener('blur', go);
    }
  }
  function endUndo() { undoText = ''; clearTimeout(undoT); status(); }

  function showNotice(kind, parts) {
    notice.textContent = '';
    notice.className = 'ad-note-box' + (kind ? ' is-' + kind : '');
    parts.forEach(function (p) {
      if (typeof p === 'string') { notice.appendChild(document.createTextNode(p)); return; }
      var el = document.createElement(p.href ? 'a' : 'button');
      if (p.href) { el.href = p.href; el.target = '_blank'; el.rel = 'noopener'; } else { el.type = 'button'; el.addEventListener('click', p.go); }
      el.textContent = p.text; el.className = 'ad-note-box__a';
      notice.appendChild(el);
    });
    notice.hidden = false;
  }
  function hideNotice() { notice.hidden = true; notice.textContent = ''; }

  /* --------------------------------------------------------------- session */
  var lastPing = Date.now(), lostTimer = 0;
  function ping(ms) {
    lastPing = Date.now();
    var ctl = window.AbortController ? new AbortController() : null;
    var t = ctl ? setTimeout(function () { ctl.abort(); }, ms || 10000) : 0;
    return fetch(form.getAttribute('data-ping'), {
      credentials: 'same-origin', cache: 'no-store', signal: ctl ? ctl.signal : undefined,
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    }).then(function (r) {
      clearTimeout(t);
      if (r.status === 401 || r.status === 419) { sessionLost(); return 'lost'; }
      if (!r.ok) return 'error';
      return r.json().then(function (j) { setCsrf(j.csrf); if (lost) sessionBack(); return 'ok'; }, function () { return 'error'; });
    }, function () { clearTimeout(t); return 'offline'; });
  }
  setInterval(function () { if (document.visibilityState === 'visible') ping(); }, 4 * 60 * 1000);
  document.addEventListener('visibilitychange', function () {
    if (document.visibilityState === 'visible' && Date.now() - lastPing > 30000) ping();
  });
  function sessionLost() {
    if (lost) return;
    lost = true; Draft.now(); Up.pause();
    showNotice('bad', ['Tu sesión ha caducado. ', { href: form.getAttribute('data-login'), text: 'Entra otra vez' },
      ' (se abre en otra pestaña) y vuelve aquí: lo que has escrito y las fotos siguen en esta página.']);
    clearInterval(lostTimer);
    lostTimer = setInterval(function () { if (document.visibilityState === 'visible') ping(); }, 15000);
    status();
  }
  function sessionBack() {
    lost = false; clearInterval(lostTimer);
    showNotice('ok', ['Has vuelto a entrar. Sigue donde lo dejaste.']);
    setTimeout(hideNotice, 5000);
    Up.resume(); status();
  }

  /* ---------------------------------------------------------------- groups */
  function listOf(cat) { return $('.ad-cat[data-cat="' + cat + '"] [data-list]', form); }
  /* A new photo, or one given another group, goes to the END of that group: the
     order inside a group is his now (drag and drop), so slotting it in by
     upload time would put it somewhere he could not predict. */
  function place(li, cat) {
    listOf(cat).appendChild(li);
    var tag = $('.ad-tag', li);
    if (tag) tag.setAttribute('data-tag', cat);
    counts();
  }
  function counts() {
    $$('.ad-cat', form).forEach(function (c) {
      var all = $$('.ad-shot', c);
      $('[data-count]', c).textContent = all.filter(function (li) { return !li.classList.contains('is-gone'); }).length;
      // not while one is held: the page must not jump under the finger
      if (c.getAttribute('data-cat') === 'none' && !Order.busy()) c.hidden = !all.length;
    });
    coverHint();
  }
  /* With no photo chosen as the cover the server takes the first exterior one,
     then the first of all (App\Support\CarPhotos::apply). Mark that one, so
     what he sees is what the catalogue will show. */
  function coverHint() {
    $$('.ad-shot.is-auto', form).forEach(function (li) { li.classList.remove('is-auto'); });
    var live = function (li) { return !li.classList.contains('is-gone') && (!li.classList.contains('is-new') || li.getAttribute('data-state') === 'done'); };
    var chosen = $$('.ad-shot__cv', form).filter(function (r) { return r.checked && !r.disabled && live(r.closest('.ad-shot')); })[0];
    if (chosen) return;
    // the gallery's first, as the server orders it: Exterior, Interior, Desperfectos, then unsorted
    var pick = GROUPS.reduce(function (all, g) { return all.concat($$('.ad-shot', listOf(g))); }, []).filter(live)[0];
    if (pick) pick.classList.add('is-auto');
  }

  /* ------------------------------------------------------- photos, one by one */
  var Up = (function () {
    var URL_UP = form.getAttribute('data-photos');
    var EDGE = 2400, Q = 0.85, PARALLEL = 2, TRIES = 7, AHEAD = 3;
    var items = [], prepQ = [], sendQ = [], preparing = false, active = 0, paused = false;
    var seq = +form.getAttribute('data-staged') || 0;

    function options(cat) {
      return GROUPS.map(function (g) { return '<option value="' + g + '"' + (g === cat ? ' selected' : '') + '>' + TAGS[g] + '</option>'; }).join('');
    }
    function build(it) {
      var li = document.createElement('li');
      li.className = 'ad-shot is-new';
      li.setAttribute('data-i', 100000 + it.j);
      li.innerHTML =
        '<input type="hidden" name="staged[' + it.j + '][path]" value="" disabled>' +
        '<label class="ad-shot__pic"><input class="ad-shot__cv" type="radio" name="cover_from" value="" disabled>' +
        '<img class="ad-shot__i" alt="" draggable="false"><span class="ad-shot__flag">Portada</span><span class="ad-vh">Hacer esta foto la portada</span></label>' +
        '<span class="ad-shot__st"><span class="ad-shot__bar"><i></i></span><span class="ad-shot__say"></span></span>' +
        '<button class="ad-shot__mv" type="button" aria-describedby="ad-dnd-help"><span>Mover</span></button>' +
        '<button class="ad-shot__x" type="button" data-unstage><span class="ad-shot__xi" aria-hidden="true"></span><span class="ad-vh">Quitar esta foto</span></button>' +
        '<label class="ad-tag" data-tag="' + it.cat + '"><span class="ad-vh">Grupo de la foto</span>' +
        '<select class="ad-tag__s" name="staged[' + it.j + '][tag]">' + options(it.cat) + '</select></label>';
      li._it = it;
      return li;
    }
    function set(it, state, p) {
      it.state = state;
      it.li.setAttribute('data-state', state);
      var say = $('.ad-shot__say', it.li), bar = $('.ad-shot__bar i', it.li);
      if (!say) return;
      if (bar) bar.style.width = Math.round((p || 0) * 100) + '%';
      say.textContent = { queued: 'En cola', prep: 'Preparando', ready: 'En cola', up: Math.round((p || 0) * 100) + ' %',
                          wait: 'Reintentando…', bad: it.why || 'No se ha subido', done: '' }[state] || '';
    }

    function add(file, cat) {
      var it = { file: file, cat: cat, j: seq++, tries: 0 };
      it.li = build(it);
      items.push(it);
      place(it.li, cat);
      set(it, 'queued');
      prepQ.push(it);
      prep(); changed();
    }

    /* ---- shrinking it on the phone ---- */
    function decode(f) {
      return new Promise(function (ok, no) {
        var img = new Image(), url = null;
        var done = function () { if (url) { try { URL.revokeObjectURL(url); } catch (e) {} } };
        img.decoding = 'async';
        img.onload = function () { ok({ img: img, done: done }); };
        img.onerror = function () {
          done();
          if (url) {                          // blob: refused? read it as data: instead
            url = null;
            var r = new FileReader();
            r.onload = function () { img.onerror = function () { no(); }; img.src = r.result; };
            r.onerror = function () { no(); };
            r.readAsDataURL(f);
          } else { no(); }
        };
        try { url = URL.createObjectURL(f); img.src = url; } catch (e) { url = null; img.onerror(); }
      });
    }
    function thumb(it, img, w, h) {
      var k = Math.min(1, 320 / Math.max(w, h)), c = document.createElement('canvas');
      c.width = Math.max(1, Math.round(w * k)); c.height = Math.max(1, Math.round(h * k));
      c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
      try { $('.ad-shot__i', it.li).src = c.toDataURL('image/jpeg', 0.72); } catch (e) {}
    }
    function stem(f) { return String(f.name || 'foto').replace(/\.[^.]+$/, '').replace(/[^\w-]+/g, '_').slice(0, 60) || 'foto'; }
    function shrink(it) {
      var f = it.file;
      return decode(f).then(function (d) {
        var img = d.img, w = img.naturalWidth, h = img.naturalHeight;
        if (!w || !h) { d.done(); throw new Error('empty'); }
        thumb(it, img, w, h);
        var k = Math.min(1, EDGE / Math.max(w, h));
        // already small, and a JPEG: sent as it is, not compressed a second time
        if (k === 1 && f.size <= 1.5e6 && /^image\/jpe?g$/i.test(f.type)) { d.done(); return { blob: f, name: stem(f) + '.jpg' }; }
        var c = document.createElement('canvas');
        c.width = Math.round(w * k); c.height = Math.round(h * k);
        var x = c.getContext('2d');
        x.imageSmoothingEnabled = true; x.imageSmoothingQuality = 'high';
        x.drawImage(img, 0, 0, c.width, c.height);
        d.done();
        return new Promise(function (ok) {
          c.toBlob(function (b) {
            c.width = c.height = 0;           // give the memory back now, not at GC
            ok(b && b.size ? { blob: b, name: stem(f) + '.jpg' } : { blob: f, name: f.name || 'foto' });
          }, 'image/jpeg', Q);
        });
      }).catch(function () {
        $('.ad-shot__i', it.li).classList.add('is-blank');
        return { blob: f, name: f.name || 'foto' };   // the server converts HEIC, refuses the rest in words
      });
    }
    function prep() {
      if (preparing || sendQ.length >= AHEAD) return;
      var it = prepQ.shift();
      if (!it) return;
      if (it.state === 'gone') { prep(); return; }
      preparing = true; set(it, 'prep');
      shrink(it).then(function (r) {
        preparing = false;
        if (it.state !== 'gone') { it.blob = r.blob; it.name = r.name; it.file = null; set(it, 'ready'); sendQ.push(it); pump(); }
        prep(); changed();
      });
    }

    /* ---- sending it ---- */
    function pump() {
      while (!paused && active < PARALLEL && sendQ.length) {
        var it = sendQ.shift();
        if (it.state === 'ready') send(it);
      }
      prep();
    }
    function send(it) {
      active++; set(it, 'up', 0);
      var fd = new FormData();
      fd.append('token', tokenIn.value);
      fd.append('tag', it.cat === 'none' ? '' : it.cat);
      fd.append('photo', it.blob, it.name);
      var x = new XMLHttpRequest();
      it.xhr = x;
      x.open('POST', URL_UP, true);
      x.timeout = 180000;
      x.setRequestHeader('Accept', 'application/json');
      x.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      x.setRequestHeader('X-CSRF-TOKEN', csrf());
      x.upload.onprogress = function (e) { if (e.lengthComputable && it.state === 'up') set(it, 'up', e.loaded / e.total * 0.98); };
      x.onload = function () { end(it, x.status, x.responseText); };
      x.onerror = x.ontimeout = function () { end(it, 0, ''); };
      x.onabort = function () { active--; it.xhr = null; pump(); };
      x.send(fd);
    }
    function end(it, code, body) {
      active--; it.xhr = null;
      if (it.state === 'gone') { pump(); return; }
      var j = null;
      try { j = JSON.parse(body); } catch (e) {}
      if ((code === 201 || code === 200) && j && j.path) {
        done(it, j.path);
      } else if (code === 401 || (code === 419 && j && j.auth === false)) {
        set(it, 'ready'); sendQ.unshift(it); sessionLost();
      } else if (code === 419) {
        if (j && j.csrf) setCsrf(j.csrf);
        again(it, 300);
      } else if (code === 422 || code === 413 || code === 415) {
        it.why = (j && j.message) || 'Esta foto no se puede subir.';
        it.final = true; set(it, 'bad');
      } else {
        again(it);
      }
      pump(); changed();
    }
    function again(it, ms) {
      it.tries++;
      if (it.tries >= TRIES) { it.why = 'No se ha subido'; set(it, 'bad'); return; }
      set(it, 'wait');
      var go = function () { if (it.state === 'wait') { set(it, 'ready'); sendQ.push(it); pump(); } };
      if (navigator.onLine === false) {
        window.addEventListener('online', function once() { window.removeEventListener('online', once); go(); });
      } else {
        setTimeout(go, ms != null ? ms : Math.min(30000, 1000 * Math.pow(2, it.tries - 1)) * (0.75 + Math.random() * 0.5));
      }
    }
    function done(it, path) {
      it.path = path; it.blob = null;
      var hid = $('input[type="hidden"]', it.li), cv = $('.ad-shot__cv', it.li);
      hid.value = path; hid.disabled = false;
      cv.value = path; cv.disabled = false;
      it.li.setAttribute('data-path', path);
      set(it, 'done');
      coverHint();
      Order.sync();             // it has a path now, so it has a place in the order
      Draft.soon();
    }

    /* ---- what is already here: after a failed save, or from the phone's copy ---- */
    function adopt(li) {
      var it = { j: +li.getAttribute('data-j'), path: li.getAttribute('data-path'), li: li, state: 'done' };
      it.cat = $('.ad-tag__s', li).value;
      li._it = it; items.push(it);
      var img = $('.ad-shot__i', li);
      img.addEventListener('error', function () { forget(it); }, { once: true });   // the file is gone: so is the tile
      return it;
    }
    function restore(list) {
      list.forEach(function (s) {
        if (!s || !s.path || $$('.ad-shot', form).some(function (li) { return li.getAttribute('data-path') === s.path; })) return;
        var cat = GROUPS.indexOf(s.tag) >= 0 ? s.tag : 'none';
        var it = { j: seq++, cat: cat, path: s.path, tries: 0 };
        it.li = build(it);
        $('.ad-shot__i', it.li).src = s.path;
        items.push(it); place(it.li, cat);
        done(it, s.path);
        $('.ad-shot__i', it.li).addEventListener('error', function () { forget(it); }, { once: true });
      });
    }
    function forget(it) {
      it.state = 'gone';
      items = items.filter(function (x) { return x !== it; });
      if (it.li.parentNode) it.li.parentNode.removeChild(it.li);
      counts(); changed(); Draft.soon();
    }
    function cancel(it) {
      var was = it.state;
      it.state = 'gone';
      if (it.xhr) { try { it.xhr.abort(); } catch (e) {} }
      if (was === 'done' && it.path) {
        fetch(URL_UP, { method: 'DELETE', credentials: 'same-origin', keepalive: true,
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
          body: JSON.stringify({ token: tokenIn.value, path: it.path }) }).catch(function () {});
      }
      forget(it);
    }

    return {
      add: add, adopt: adopt, restore: restore, cancel: cancel,
      pending: function () { return items.filter(function (i) { return /^(queued|prep|ready|up|wait)$/.test(i.state); }).length; },
      failed: function () { return items.filter(function (i) { return i.state === 'bad'; }).length; },
      done: function () { return items.filter(function (i) { return i.state === 'done'; }).map(function (i) { return { path: i.path, tag: $('.ad-tag__s', i.li).value }; }); },
      retryFailed: function () {
        items.forEach(function (i) { if (i.state === 'bad' && !i.final) { i.tries = 0; set(i, 'ready'); sendQ.push(i); } });
        pump(); changed();
      },
      dropFailed: function () { items.filter(function (i) { return i.state === 'bad'; }).forEach(cancel); changed(); },
      pause: function () { paused = true; },
      resume: function () { paused = false; pump(); },
      setCat: function (li, cat) { if (li._it) li._it.cat = cat; }
    };
  })();

  /* Called whenever the queue moves: the status line, and the save that was
     waiting for the photos. */
  function changed() {
    status();
    if (waiting && !Up.pending()) {
      waiting = false; busy(false);
      if (Up.failed()) { status(); } else { trySave(); }
    }
  }

  /* -------------------------------------------------------------- the order
     2026-10-17. The client: "he uploads the photos, but he must be able to set
     their POSITION by drag and drop — make it as nice as possible."

     Picking one up. On a phone: a quarter of a second under the finger without
     moving (HOLD); a finger that moves first is scrolling, and the page
     scrolls as it always did. Touch EVENTS, not pointer events, for this:
     pointer events cannot stop a pan that has already been granted, a
     non-passive touchmove can (registered on the photos panel only, so the
     rest of the form still scrolls on the compositor). With a mouse: pressed
     and moved MSLOP px. A tap or a click is still "make this the cover".

     While it is held. A copy follows the finger in a layer above the page (the
     form's own tile stays in the list as the open slot, so its inputs never
     move out of the form). The slot goes where the finger is: onto a photo,
     before or after it; above a group's photos, first; below them, last; into
     an empty group, which opens while something is held. Everything that
     shifts glides there (FLIP, --m-move). The group the finger is over keeps
     still under it when the one above shrinks (the page scrolls by the
     difference), or the photos would slide out from under the finger. Near
     the top or the bottom of what is visible the page scrolls, faster the
     nearer the edge (px/s, damped like every speed on this site). Off the
     groups, the slot goes home: let go there, or press Esc, and nothing moved.

     The keyboard. Each photo's "Mover" (seen only when it has focus): Space or
     Enter picks it up, arrows move it (across the end of a group into the
     next), Home/End, Space puts it down, Esc puts it back. Spoken in Spanish.

     Every move can be undone ("Deshacer", Ctrl/⌘+Z). A photo still going up
     cannot be picked up; the others move around it. */
  var Order = (function () {
    var box = $('#ad-photos', form), orderIn = $('#ad-order', form), live = $('#ad-dnd-live', form);
    var root = document.documentElement, css = getComputedStyle(root);
    var REDUCED = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : { matches: false };
    var TR = !!(window.CSS && CSS.supports && CSS.supports('translate', '1px 1px') && CSS.supports('scale', '1'));
    var MOVE = parseFloat(css.getPropertyValue('--m-move')) || 280;              // ms, the token
    var EASE = css.getPropertyValue('--e-out').trim() || 'cubic-bezier(.22,.61,.36,1)';
    var HOLD = 250, SLOP = 10, MSLOP = 4, ARM = 24; // ms; px a finger may wander while held; px a mouse moves to start;
                                                   // px towards an edge before it scrolls
    var MAXV = 1400, TAU = 150, TAU_OFF = 60;     // auto-scroll px/s at the very edge; damping (DESIGN-GUIDE §4),
                                                   // quicker to stop than to start: out of the edge, it stops
    var undos = [], drag = null, press = null, kb = null, landing = false, layer = null;
    var swallowClick = 0, swallowKey = false, sayT = 0;
    if (!box) return { sync: function () {}, paths: function () { return []; }, apply: function () {}, undo: function () {}, regroup: place, busy: function () { return false; } };

    // what only works with this script is shown by it: the sentence that says so, each photo's "Mover"
    $$('[data-dnd], .ad-shot__mv', box).forEach(function (el) { el.hidden = false; });

    /* ---- what is where ---- */
    function lists() { return GROUPS.map(listOf); }               // the order the server keeps
    function shown() {                                           // top to bottom, as on the page
      return $$('.ad-cat', box).filter(function (c) { return !c.hidden; }).map(function (c) { return $('[data-list]', c); });
    }
    function tiles(list) { return Array.prototype.filter.call(list.children, function (el) { return el.classList.contains('ad-shot'); }); }
    function catOf(el) { var c = el.closest('.ad-cat'); return c ? c.getAttribute('data-cat') : 'none'; }
    function movable(li) { return !li.classList.contains('is-new') || li.getAttribute('data-state') === 'done'; }
    function paths() {
      var out = [];
      lists().forEach(function (l) { tiles(l).forEach(function (li) { var p = li.getAttribute('data-path'); if (p) out.push(p); }); });
      return out;
    }
    function sync() { if (orderIn) orderIn.value = JSON.stringify(paths()); }
    function says(li) {
      var l = li.parentNode, all = tiles(l);
      return 'Foto ' + (all.indexOf(li) + 1) + ' de ' + all.length + ', ' + TAGS[catOf(l)];
    }
    function announce(t) {
      if (!live) return;
      clearTimeout(sayT); live.textContent = '';
      sayT = setTimeout(function () { live.textContent = t; }, 40);   // emptied first, so the same words are said again
    }
    function setCat(li, cat) {
      var sel = $('.ad-tag__s', li), tag = $('.ad-tag', li);
      if (sel) sel.value = cat;
      if (tag) tag.setAttribute('data-tag', cat);
      Up.setCat(li, cat);
    }
    function buzz() { try { if (navigator.vibrate) navigator.vibrate(10); } catch (e) {} }

    /* Where layout puts an element, in page coordinates — transforms and running
       animations ignored, so a decision is never taken on a photo mid-glide. */
    function pageBox(el) {
      var x = 0, y = 0, e = el;
      while (e) { x += e.offsetLeft; y += e.offsetTop; e = e.offsetParent; }
      return { left: x, top: y, width: el.offsetWidth, height: el.offsetHeight };
    }

    /* ---- the others make room: FLIP ----
       Groups glide as blocks; a photo or an "Añadir fotos" inside one glides by
       its own movement minus its group's, so nested motion adds up exactly. */
    function movers() {
      var out = [];
      $$('.ad-cat', box).forEach(function (c) {
        if (c.hidden) return;
        out.push(c);
        $$('.ad-shot, .ad-up', c).forEach(function (el) { out.push(el); });
      });
      Array.prototype.forEach.call(box.children, function (el) { if (el.matches('.ad-more, .ad-err')) out.push(el); });
      return out;
    }
    function glide(el, dx, dy) {
      if (Math.abs(dx) < 0.5 && Math.abs(dy) < 0.5) return;
      var k = TR ? [{ translate: dx + 'px ' + dy + 'px' }, { translate: '0px 0px' }]
                 : [{ transform: 'translate(' + dx + 'px,' + dy + 'px)' }, { transform: 'none' }];
      try { el._mv = el.animate(k, { duration: MOVE, easing: EASE }); } catch (e) {}
    }
    function flip(mutate, anchor) {
      var a0 = anchor ? pageBox(anchor).top : 0;
      var fix = function () { if (anchor) { var d = pageBox(anchor).top - a0; if (d) window.scrollBy(0, d); } };
      if (REDUCED.matches || !Element.prototype.animate) { mutate(); fix(); return; }
      var first = new Map();
      movers().forEach(function (el) { first.set(el, el.getBoundingClientRect()); });
      first.forEach(function (r, el) { if (el._mv) { el._mv.cancel(); el._mv = null; } });
      mutate();
      fix();
      var now = movers(), last = new Map(), sec = new Map(), H = window.innerHeight;
      // off screen before and after: nothing to show, so no animation to run (a 51-photo car animates ~12, not 55)
      var off = function (f, l) { return (f.bottom < -80 || f.top > H + 80) && (l.bottom < -80 || l.top > H + 80); };
      now.forEach(function (el) { last.set(el, el.getBoundingClientRect()); });
      now.forEach(function (el) {
        var f = first.get(el), l = last.get(el);
        if (f && el.classList.contains('ad-cat')) sec.set(el, off(f, l) ? [0, 0] : [f.left - l.left, f.top - l.top]);
      });
      now.forEach(function (el) {
        var f = first.get(el), l = last.get(el);
        if (!f || off(f, l)) return;                      // just appeared (no history to glide from), or never seen
        var dx = f.left - l.left, dy = f.top - l.top;
        if (!el.classList.contains('ad-cat')) { var s = sec.get(el.closest('.ad-cat')); if (s) { dx -= s[0]; dy -= s[1]; } }
        glide(el, dx, dy);
      });
    }

    /* ---- where the finger is: which group, and where in it ---- */
    /* The groups, their lists and photos in page coordinates, read once and kept
       until something in the panel changes (a MutationObserver counts changes):
       reading offsets every frame forced a style and layout pass each time —
       on the 51-photo car, 4x slower CPU, ~5 ms a frame for nothing. */
    var ver = 0, seen = -1, geo = null;
    if (window.MutationObserver) new MutationObserver(function () { ver++; }).observe(box, { childList: true, subtree: true, attributes: true, attributeFilter: ['class', 'hidden', 'data-state'] });
    else ver = NaN;                                        // never equal: measured every time
    window.addEventListener('resize', function () { ver++; });
    function layout() {
      if (geo && seen === ver) return geo;
      var secs = $$('.ad-cat', box).filter(function (c) { return !c.hidden; });
      geo = { box: pageBox(box), secs: secs.map(function (c) {
        var list = $('[data-list]', c), lb = pageBox(list);
        return { top: pageBox(c).top, height: c.offsetHeight, list: list, lb: lb, tiles: tiles(list).map(function (li) {
          return { li: li, l: lb.left + li.offsetLeft, t: lb.top + li.offsetTop, w: li.offsetWidth, h: li.offsetHeight };
        }) };
      }) };
      seen = ver;
      return geo;
    }
    function hit(x, y) {
      var g = layout(), px = x + window.pageXOffset, py = y + window.pageYOffset, fb = g.box, sec = null;
      if (px < fb.left - 16 || px > fb.left + fb.width + 16) return null;
      for (var k = 0; k < g.secs.length; k++) {
        var top = g.secs[k].top, next = g.secs[k + 1] ? g.secs[k + 1].top : top + g.secs[k].height + 16;
        if (py >= (k ? top : top - 16) && py < next) { sec = g.secs[k]; break; }
      }
      if (!sec) return null;
      var list = sec.list, all = sec.tiles;
      if (!all.length || py < sec.lb.top) return { list: list, before: all.length ? all[0].li : null };
      for (var i = 0; i < all.length; i++) {
        var r = all[i];
        if (px >= r.l && px < r.l + r.w && py >= r.t && py < r.t + r.h) return { list: list, over: r.li };
      }
      var e = all[all.length - 1];
      if (py >= e.t + e.h || (py >= e.t && px >= e.l + e.w)) return { list: list, before: null };
      return { list: list, keep: true };                  // in the gap between two: stay
    }
    function homeOf(from, li) {      // where it came from, even if the photo after it has gone since
      if (from.before && from.before.parentNode === from.list && from.before !== li) return from.before;
      return tiles(from.list).filter(function (x) { return x !== li; })[from.i] || null;
    }
    function aim(t) {
      var li = drag.li, list, before;
      if (!t) { list = drag.from.list; before = homeOf(drag.from, li); }
      else if (t.keep) return;
      else if (t.over) {
        if (t.over === li) return;
        list = t.list;
        var all = tiles(list), si = all.indexOf(li), oi = all.indexOf(t.over);
        before = si >= 0 && si < oi ? t.over.nextElementSibling : t.over;
      } else { list = t.list; before = t.before; }
      if (before === li || (li.parentNode === list && li.nextElementSibling === before)) return;
      flip(function () { list.insertBefore(li, before); }, list);
      tone(catOf(list));
    }
    function tone(cat) {             // the copy in the hand wears the group it is over
      if (!drag || drag.tone === cat) return;
      drag.tone = cat;
      var sel = $('.ad-tag__s', drag.ghost), tag = $('.ad-tag', drag.ghost);
      if (sel) sel.value = cat;
      if (tag) tag.setAttribute('data-tag', cat);
    }

    /* ---- the visible band, for scrolling at its edges ---- */
    var head = $('.ad-rail__head'), nav = $('.ad-rail__nav'), bar = $('#ad-save');
    function fixed(el) { return el && getComputedStyle(el).position === 'fixed'; }
    function band() {                 // the fixed strips once per drag; the sticky bar when the page has moved
      var y = window.pageYOffset;
      if (drag.barAt !== y || drag.barVer !== ver) {
        var r = bar && bar.getBoundingClientRect();
        drag.barTop = r && r.top < drag.bottom && r.bottom > drag.top ? r.top : Infinity;
        drag.barAt = y; drag.barVer = ver;
      }
      return [drag.top, Math.min(drag.bottom, drag.barTop)];
    }
    function sq(t) { t = t < 0 ? 0 : t > 1 ? 1 : t; return t * t; }

    /* ---- held by a finger or a mouse ---- */
    function begin(li, x, y, how, id) {
      if (drag || kb || landing || !li.isConnected || !movable(li) || !li.parentNode.hasAttribute('data-list')) return;
      var r = li.getBoundingClientRect();
      if (!layer) {
        layer = document.createElement('div');
        layer.className = 'ad-car-form ad-dnd-layer';
        layer.setAttribute('aria-hidden', 'true');
        document.body.appendChild(layer);
      }
      var g = li.cloneNode(true);
      // nameless before it is anywhere: a checked "cover_from" copy would uncheck the real one
      $$('[name], [id]', g).forEach(function (el) { el.removeAttribute('name'); el.removeAttribute('id'); });
      g.removeAttribute('data-path');
      g.classList.remove('is-slot', 'is-lifted');
      g.classList.add('is-ghost');
      g.setAttribute('inert', '');
      g.style.left = r.left + 'px'; g.style.top = r.top + 'px';
      g.style.width = r.width + 'px'; g.style.height = r.height + 'px';
      layer.appendChild(g);
      drag = { li: li, ghost: g, how: how, id: id, x: x, y: y, sx: x, sy: y, gx: r.left, gy: r.top,
               v: 0, acc: 0, t: 0, tone: null,
               top: fixed(head) ? Math.max(0, head.getBoundingClientRect().bottom) : 0,
               bottom: fixed(nav) ? Math.min(window.innerHeight, nav.getBoundingClientRect().top) : window.innerHeight,
               from: { list: li.parentNode, before: li.nextElementSibling, i: tiles(li.parentNode).indexOf(li), cat: catOf(li) } };
      tone(drag.from.cat);
      root.classList.add('is-ordering');
      if (how === 'mouse') root.classList.add('is-grabbing');
      flip(function () { li.classList.add('is-slot'); form.classList.add('is-dnd'); }, li.parentNode);
      ver++;                          // the form's class is outside what the observer watches
      requestAnimationFrame(function () { if (drag && drag.ghost === g) g.classList.add('is-up'); });   // a frame later, so it lifts
      if (how === 'touch') buzz();
      drag.raf = requestAnimationFrame(tick);
    }
    function tick(now) {
      if (!drag) return;
      var dt = drag.t ? Math.min(100, now - drag.t) : 16;   // px/s even on a slow frame; capped after a stall
      drag.t = now;
      // reads first (the layout is clean from the last frame), writes after
      // near an edge of what is visible the page scrolls under it, faster nearer the edge
      // An edge only counts once the photo has been taken towards it: one picked up
      // near the top of the screen must not set the page moving by itself.
      var b = band(), zone = Math.min(96, (b[1] - b[0]) * 0.16), want = 0;
      if (drag.y > b[0] + zone || drag.sy - drag.y > ARM) drag.armUp = true;
      if (drag.y < b[1] - zone || drag.y - drag.sy > ARM) drag.armDown = true;
      if (drag.armUp && drag.y < b[0] + zone) want = -MAXV * sq((b[0] + zone - drag.y) / zone);
      else if (drag.armDown && drag.y > b[1] - zone) want = MAXV * sq((drag.y - b[1] + zone) / zone);
      // never past the photos: the panel's own ends are where it stops, at once
      var g = layout().box, frTop = g.top - window.pageYOffset;
      var down = Math.max(0, frTop + g.height - b[1]), up = Math.max(0, b[0] - frTop);
      if ((want > 0 && !down) || (want < 0 && !up)) want = 0;
      drag.v += (want - drag.v) * (1 - Math.exp(-dt / (Math.abs(want) >= Math.abs(drag.v) ? TAU : TAU_OFF)));
      if (!want && Math.abs(drag.v) < 4) { drag.v = 0; drag.acc = 0; }
      var step = 0;
      if (drag.v) {
        drag.acc += drag.v * dt / 1000;                     // the float is ours; the page only takes whole pixels
        step = drag.acc < 0 ? Math.ceil(drag.acc) : Math.floor(drag.acc);
        if (step > down || step < -up) { step = Math.max(-up, Math.min(down, step)); drag.v = 0; drag.acc = 0; }
        else drag.acc -= step;
      }
      aim(hit(drag.x, drag.y));                           // reads a clean layout; moves the slot (FLIP)
      var dx = drag.x - drag.sx, dy = drag.y - drag.sy;
      if (TR) drag.ghost.style.translate = dx + 'px ' + dy + 'px';
      else drag.ghost.style.transform = 'translate(' + dx + 'px,' + dy + 'px)';
      if (step) window.scrollBy(0, step);                 // the next frame's hit sees it
      drag.raf = requestAnimationFrame(tick);
    }
    function end(cancel) {
      if (!drag) return;
      var d = drag;
      drag = null;
      cancelAnimationFrame(d.raf);
      root.classList.remove('is-grabbing');
      var li = d.li;
      if (!li.isConnected) {                               // taken away meanwhile (its upload withdrawn)
        if (d.ghost.parentNode) d.ghost.parentNode.removeChild(d.ghost);
        form.classList.remove('is-dnd'); root.classList.remove('is-ordering'); counts(); sync();
        return;
      }
      if (cancel) flip(function () { d.from.list.insertBefore(li, homeOf(d.from, li)); }, d.from.list);
      var to = li.parentNode, moved = to !== d.from.list || tiles(to).indexOf(li) !== d.from.i;
      if (d.how === 'touch' && !cancel) buzz();
      landing = true;
      land(d, function () {
        landing = false;
        flip(function () {
          li.classList.remove('is-slot');
          form.classList.remove('is-dnd');
          if (moved && catOf(to) !== d.from.cat) setCat(li, catOf(to));
          counts();
        }, to);
        root.classList.remove('is-ordering');
        if (moved) {
          remember(li, d.from);
          offerUndo(catOf(to) !== d.from.cat ? 'Foto movida a ' + TAGS[catOf(to)] + '.' : 'Foto movida.');
        }
        sync(); Draft.soon();
      });
    }
    function land(d, then) {
      var g = d.ghost, b = pageBox(d.li);
      var tx = b.left - window.pageXOffset - d.gx, ty = b.top - window.pageYOffset - d.gy;
      var done = false, fin = function () {
        if (done) return;
        done = true;
        if (g.parentNode) g.parentNode.removeChild(g);
        then();
      };
      if (REDUCED.matches) { fin(); return; }
      g.classList.add('is-land');
      g.classList.remove('is-up');
      if (TR) g.style.translate = tx + 'px ' + ty + 'px';
      else g.style.transform = 'translate(' + tx + 'px,' + ty + 'px)';
      g.addEventListener('transitionend', function (e) { if (e.propertyName === 'translate' || e.propertyName === 'transform') fin(); });
      setTimeout(fin, MOVE + 80);
    }
    function unpress() { if (press) { clearTimeout(press.timer); press = null; } }
    function pic(e) {
      var p = e.target && e.target.closest && e.target.closest('.ad-shot__pic'), li = p && p.closest('.ad-shot');
      return li && !li.classList.contains('is-ghost') && li.parentNode && li.parentNode.hasAttribute('data-list') && movable(li) ? li : null;
    }
    function touch(list, id) {
      for (var i = 0; list && i < list.length; i++) if (list[i].identifier === id) return list[i];
      return null;
    }

    // a finger
    box.addEventListener('touchstart', function (e) {
      if (drag || kb || landing) return;
      unpress();
      if (e.touches.length !== 1) return;
      var li = pic(e);
      if (!li) return;
      var t = e.touches[0];
      press = { li: li, x: t.clientX, y: t.clientY, id: t.identifier, how: 'touch' };
      press.timer = setTimeout(function () {
        var p = press;
        press = null;
        if (p) begin(p.li, p.x, p.y, 'touch', p.id);
      }, HOLD);
    }, { passive: true });
    box.addEventListener('touchmove', function (e) {
      if (drag && drag.how === 'touch') {
        if (e.cancelable) e.preventDefault();               // the page must not scroll under a held photo
        var t = touch(e.touches, drag.id);
        if (t) { drag.x = t.clientX; drag.y = t.clientY; }
        return;
      }
      if (press && press.how === 'touch') {
        var p = touch(e.touches, press.id);
        if (!p || e.touches.length > 1 || Math.abs(p.clientX - press.x) + Math.abs(p.clientY - press.y) > SLOP) unpress();
      }
    }, { passive: false });
    box.addEventListener('touchend', function (e) {
      if (drag && drag.how === 'touch' && !touch(e.touches, drag.id)) {
        if (e.cancelable) e.preventDefault();               // no click: letting go is not "make it the cover"
        end(false);
        return;
      }
      if (press && press.how === 'touch') unpress();
    }, { passive: false });
    box.addEventListener('touchcancel', function () { if (drag && drag.how === 'touch') end(true); unpress(); });
    // Android's long-press menu ("Descargar imagen") and the selection it would start
    box.addEventListener('contextmenu', function (e) { if (press || drag) e.preventDefault(); });
    box.addEventListener('dragstart', function (e) { if (e.target.closest && e.target.closest('.ad-shot')) e.preventDefault(); });

    // a mouse
    box.addEventListener('pointerdown', function (e) {
      if (e.pointerType === 'touch' || e.button !== 0 || drag || kb || landing) return;
      var li = pic(e);
      if (!li) return;
      press = { li: li, x: e.clientX, y: e.clientY, id: e.pointerId, how: 'mouse' };
      e.preventDefault();             // no text selection; the click that follows still chooses the cover
    });
    document.addEventListener('pointermove', function (e) {
      if (drag) { if (drag.how === 'mouse' && e.pointerId === drag.id) { drag.x = e.clientX; drag.y = e.clientY; } return; }
      if (press && press.how === 'mouse' && e.pointerId === press.id
          && Math.abs(e.clientX - press.x) + Math.abs(e.clientY - press.y) > MSLOP) {
        var p = press;
        press = null;
        begin(p.li, p.x, p.y, 'mouse', p.id);
        if (drag) { drag.x = e.clientX; drag.y = e.clientY; }
      }
    });
    document.addEventListener('pointerup', function (e) {
      if (drag && drag.how === 'mouse' && e.pointerId === drag.id) { swallowClick = Date.now(); end(false); }
      if (press && press.how === 'mouse') press = null;
    });
    document.addEventListener('pointercancel', function () {
      if (drag && drag.how === 'mouse') end(true);
      if (press && press.how === 'mouse') press = null;
    });
    // letting go over the photo it came from must not make it the cover
    window.addEventListener('click', function (e) {
      if (swallowClick && Date.now() - swallowClick < 400) { swallowClick = 0; e.preventDefault(); e.stopPropagation(); }
    }, true);

    // away: put it back
    window.addEventListener('blur', function () { if (drag) end(true); });
    document.addEventListener('visibilitychange', function () { if (document.hidden) { if (drag) end(true); if (kb) put(true); } });
    window.addEventListener('orientationchange', function () { if (drag) end(true); });

    /* ---- the keyboard ---- */
    function handle(li) { return $('.ad-shot__mv', li); }
    function chip(li, text) { var s = $('.ad-shot__mv span', li); if (s) s.textContent = text; }
    function pick(li) {
      if (!movable(li)) { announce('Esta foto todavía se está subiendo; podrás moverla cuando termine.'); return; }
      kb = { li: li, from: { list: li.parentNode, before: li.nextElementSibling, i: tiles(li.parentNode).indexOf(li), cat: catOf(li) } };
      root.classList.add('is-ordering');
      flip(function () { li.classList.add('is-lifted'); form.classList.add('is-dnd'); }, li.parentNode);
      chip(li, 'Flechas · Espacio');
      announce(says(li) + '. Cogida: muévela con las flechas, Espacio para dejarla, Escape para cancelar.');
    }
    function to(list, t) {           // to place t of a list, counted without the photo itself
      var li = kb.li, others = tiles(list).filter(function (x) { return x !== li; });
      t = Math.max(0, Math.min(others.length, t));
      var before = others[t] || null;
      if (li.parentNode === list && li.nextElementSibling === before) { announce(says(li) + '.'); return; }
      kb.holding = true;
      flip(function () {
        list.insertBefore(li, before);
        if (catOf(list) !== $('.ad-tag', li).getAttribute('data-tag')) setCat(li, catOf(list));
      });
      var h = handle(li);
      try { h.focus({ preventScroll: true }); } catch (e) { h.focus(); }
      kb.holding = false;
      li.scrollIntoView({ block: 'nearest', inline: 'nearest', behavior: REDUCED.matches ? 'auto' : 'smooth' });
      announce(says(li) + '.');
    }
    function cols(list) {
      var c = getComputedStyle(list).gridTemplateColumns;
      return c && c !== 'none' ? Math.max(1, c.trim().split(/\s+/).length) : 1;
    }
    function step(dir) {             // ←/→ one place, over the end of a group into the next
      var li = kb.li, list = li.parentNode, all = tiles(list), i = all.indexOf(li), vis = shown(), g = vis.indexOf(list);
      if (dir > 0) { if (i < all.length - 1) return to(list, i + 1); if (vis[g + 1]) return to(vis[g + 1], 0); }
      else { if (i > 0) return to(list, i - 1); if (vis[g - 1]) return to(vis[g - 1], 1e6); }
      announce(says(li) + '.');
    }
    function row(dir) {              // ↑/↓ one row, into the group above or below at the edges
      var li = kb.li, list = li.parentNode, all = tiles(list), i = all.indexOf(li), c = cols(list), col = i % c;
      var vis = shown(), g = vis.indexOf(list), n;
      if (dir > 0) {
        if (i + c < all.length) return to(list, i + c);
        if (Math.floor(i / c) < Math.floor((all.length - 1) / c)) return to(list, all.length - 1);
        if (vis[g + 1]) return to(vis[g + 1], col);
      } else {
        if (i - c >= 0) return to(list, i - c);
        if (vis[g - 1]) { n = tiles(vis[g - 1]).length; return to(vis[g - 1], (n ? Math.floor((n - 1) / cols(vis[g - 1])) * cols(vis[g - 1]) : 0) + col); }
      }
      announce(says(li) + '.');
    }
    function put(cancel) {
      var k = kb;
      if (!k) return;
      kb = null;
      var li = k.li, h = handle(li);
      if (cancel) {
        flip(function () { k.from.list.insertBefore(li, homeOf(k.from, li)); setCat(li, k.from.cat); });
        if (document.activeElement !== h) { try { h.focus({ preventScroll: true }); } catch (e) {} }
      }
      var moved = li.parentNode !== k.from.list || tiles(li.parentNode).indexOf(li) !== k.from.i;
      flip(function () { li.classList.remove('is-lifted'); form.classList.remove('is-dnd'); counts(); });
      root.classList.remove('is-ordering');
      chip(li, 'Mover');
      if (moved) {
        remember(li, k.from);
        offerUndo(catOf(li.parentNode) !== k.from.cat ? 'Foto movida a ' + TAGS[catOf(li.parentNode)] + '.' : 'Foto movida.');
      }
      announce(cancel ? 'Cancelado. ' + says(li) + '.' : 'Foto dejada. ' + says(li) + '.');
      sync(); Draft.soon();
    }
    form.addEventListener('keydown', function (e) {
      var h = e.target.closest && e.target.closest('.ad-shot__mv');
      if (!h) return;
      var k = e.key;
      if (kb && h === handle(kb.li)) {
        if (k === 'ArrowRight' || k === 'ArrowLeft') { e.preventDefault(); step(k === 'ArrowRight' ? 1 : -1); }
        else if (k === 'ArrowDown' || k === 'ArrowUp') { e.preventDefault(); row(k === 'ArrowDown' ? 1 : -1); }
        else if (k === 'Home' || k === 'End') { e.preventDefault(); to(kb.li.parentNode, k === 'Home' ? 0 : 1e6); }
        else if (k === ' ' || k === 'Enter') { e.preventDefault(); swallowKey = true; put(false); }
        else if (k === 'Escape') { e.preventDefault(); put(true); }
        return;
      }
      if (!kb && !drag && (k === ' ' || k === 'Enter')) { e.preventDefault(); swallowKey = true; pick(h.closest('.ad-shot')); }
    });
    form.addEventListener('keyup', function (e) { if (e.key === ' ' || e.key === 'Enter') setTimeout(function () { swallowKey = false; }, 0); });
    form.addEventListener('click', function (e) {
      var h = e.target.closest && e.target.closest('.ad-shot__mv');
      if (!h) return;
      e.preventDefault();
      if (swallowKey) { swallowKey = false; return; }
      // a press that did not come through our keys (a screen reader's): pick up, or put down
      if (kb) put(false); else pick(h.closest('.ad-shot'));
    });
    // focus leaving the held photo puts it down where it is
    form.addEventListener('focusout', function (e) {
      if (!kb || kb.holding || e.target !== handle(kb.li)) return;
      setTimeout(function () { if (kb && !kb.holding && document.activeElement !== handle(kb.li)) put(false); }, 0);
    });
    document.addEventListener('keydown', function (e) {
      if (drag && e.key === 'Escape') { e.preventDefault(); end(true); return; }
      if ((e.ctrlKey || e.metaKey) && !e.shiftKey && !e.altKey && (e.key === 'z' || e.key === 'Z') && undos.length && !drag && !kb) {
        var t = e.target;
        if (t && (t.isContentEditable || (/^(INPUT|TEXTAREA|SELECT)$/.test(t.tagName) && !/^(radio|checkbox|button|submit|file)$/.test(t.type || '')))) return;   // the text's own undo
        e.preventDefault();
        undo();
      }
    });

    /* ---- undo ---- */
    function remember(li, from) {
      undos.push({ li: li, list: from.list, before: from.before, i: from.i, cat: from.cat });
      if (undos.length > 50) undos.shift();
    }
    function undo() {
      var u;
      do { u = undos.pop(); } while (u && !u.li.isConnected);
      endUndo();
      if (!u || drag || kb) return;
      flip(function () {
        u.list.insertBefore(u.li, homeOf(u, u.li));
        if (catOf(u.list) !== $('.ad-tag', u.li).getAttribute('data-tag')) setCat(u.li, u.cat);
        counts();
      });
      u.li.scrollIntoView({ block: 'nearest', behavior: REDUCED.matches ? 'auto' : 'smooth' });
      tell('La foto ha vuelto a su sitio.');
      announce('Deshecho. ' + says(u.li) + '.');
      sync(); Draft.soon();
    }

    /* ---- the chip: a move like any other ---- */
    function regroup(li, cat) {
      var from = { list: li.parentNode, before: li.nextElementSibling, i: tiles(li.parentNode).indexOf(li), cat: catOf(li) };
      flip(function () { Up.setCat(li, cat); place(li, cat); });
      remember(li, from);
      offerUndo('Foto movida al final de ' + TAGS[cat] + '.');
      sync();
    }

    /* ---- the phone's copy, put back: each group in the order it had ---- */
    function apply(order) {
      var at = {};
      order.forEach(function (p, i) { if (typeof p === 'string' && !at.hasOwnProperty(p)) at[p] = i; });
      lists().forEach(function (l) {
        tiles(l).map(function (li, i) { var p = li.getAttribute('data-path'); return [li, p && at.hasOwnProperty(p) ? at[p] : 1e9 + i]; })
          .sort(function (a, b) { return a[1] - b[1]; })
          .forEach(function (x) { l.appendChild(x[0]); });
      });
      counts(); sync();
    }

    return { sync: sync, paths: paths, apply: apply, undo: undo, regroup: regroup,
             busy: function () { return !!(drag || kb || landing); } };
  })();

  /* -------------------------------------------------------- the phone's copy */
  var Draft = (function () {
    var KEY = form.getAttribute('data-draft'), BASE = form.getAttribute('data-base'), t = 0, first = null, firstOrder = null, off = false;
    function ls() { try { return window.localStorage; } catch (e) { return null; } }
    function fields() {
      var out = {};
      $$('input, select, textarea', form).forEach(function (el) {
        var n = el.name;
        if (!n || n === '_token' || n === 'photo_token' || el.type === 'file' || el.type === 'hidden' || /^staged\[/.test(n)) return;
        if (el.type === 'radio') { if (el.checked) out[n] = el.value; return; }
        if (el.type === 'checkbox') { out[n] = el.checked ? el.value : ''; return; }
        out[n] = el.value;
      });
      return out;
    }
    function now() {
      var s = ls();
      if (!s || off) return;
      var f = fields(), staged = Up.done(), order = Order.paths();
      try {
        if (JSON.stringify(f) === first && !staged.length && JSON.stringify(order) === firstOrder) { s.removeItem(KEY); savedAt = 0; }
        else { s.setItem(KEY, JSON.stringify({ v: 1, at: Date.now(), base: BASE, token: tokenIn.value, fields: f, staged: staged, order: order })); savedAt = Date.now(); }
      } catch (e) { /* storage full or private browsing: the form still works */ }
      status();
    }
    function soon() { clearTimeout(t); t = setTimeout(now, 400); }
    function ago(ms) {
      var m = Math.round(ms / 60000);
      return m < 1 ? 'hace un momento' : m < 60 ? 'hace ' + m + ' min' : m < 1440 ? 'hace ' + Math.round(m / 60) + ' h' : 'hace ' + Math.round(m / 1440) + ' días';
    }
    function restore() {
      first = JSON.stringify(fields());
      firstOrder = JSON.stringify(Order.paths());
      var s = ls(), d = null;
      if (!s) return;
      try { d = JSON.parse(s.getItem(KEY) || 'null'); } catch (e) { d = null; }
      if (!d) return;
      // After a failed save the server printed the form back itself: that is newer.
      if (form.getAttribute('data-old') === '1') return;
      if (d.v !== 1 || d.base !== BASE || Date.now() - d.at > 14 * 864e5) { try { s.removeItem(KEY); } catch (e) {} return; }

      var n = 0, touched = [];
      restoring = true;           // what comes back is not a move to offer undoing
      if (d.staged && d.staged.length) {
        // the photos it names were sent under that token; it has to come back with them
        if (/^[A-Za-z0-9]{32,64}$/.test(d.token || '')) tokenIn.value = d.token;
        Up.restore(d.staged); n += d.staged.length;
      }
      Object.keys(d.fields || {}).forEach(function (name) {
        var v = d.fields[name];
        var els = $$('input, select, textarea', form).filter(function (i) { return i.name === name && i.type !== 'hidden' && i.type !== 'file'; });
        if (!els.length) return;
        var el = els[0];
        if (el.type === 'radio') {
          var r = els.filter(function (i) { return i.value === v; })[0];
          if (r && !r.checked) { r.checked = true; r.dispatchEvent(new Event('change', { bubbles: true })); n++; touched.push(r); }
        } else if (el.type === 'checkbox') {
          if (el.checked !== (v !== '')) { el.checked = v !== ''; el.dispatchEvent(new Event('change', { bubbles: true })); n++; touched.push(el); }
        } else if (el.value !== v) {
          el.value = v; n++; touched.push(el);
          el.dispatchEvent(new Event(el.tagName === 'SELECT' ? 'change' : 'input', { bubbles: true }));
        }
      });
      // last, once every photo is back in its group: where he had put each one
      if (Array.isArray(d.order) && d.order.length) {
        var was = JSON.stringify(Order.paths());
        Order.apply(d.order);
        if (JSON.stringify(Order.paths()) !== was) n++;
      }
      restoring = false;
      if (n) {
        savedAt = d.at;
        showNotice('', ['Lo que no llegaste a guardar (' + ago(Date.now() - d.at) + ') está otra vez aquí. ',
          { text: 'Descartarlo', go: function () { off = true; try { s.removeItem(KEY); } catch (e) {} location.reload(); } }]);
        touched.forEach(function (el) { var dt = el.closest('details'); if (dt && (el.value || el.checked)) dt.open = true; });
      }
    }
    return { soon: soon, now: now, restore: restore };
  })();

  /* ---------------------------------------------------------------- video */
  var YT_HOSTS = ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'music.youtube.com', 'youtu.be', 'www.youtu.be', 'youtube-nocookie.com', 'www.youtube-nocookie.com'];
  function ytId(u) {                      // the same reading as App\Support\YouTube::parse
    u = String(u || '').trim();
    if (!u) return null;
    if (!/^[a-z][a-z0-9+.-]*:\/\//i.test(u)) u = 'https://' + u.replace(/^\/+/, '');
    var p;
    try { p = new URL(u); } catch (e) { return null; }
    if (!/^https?:$/.test(p.protocol) || YT_HOSTS.indexOf(p.hostname.toLowerCase()) < 0) return null;
    var seg = p.pathname.split('/').filter(Boolean), id = null;
    if (/youtu\.be$/i.test(p.hostname)) id = seg[0];
    else if (p.searchParams.get('v')) id = p.searchParams.get('v');
    else if (seg[1] && ['embed', 'shorts', 'live', 'v', 'e'].indexOf(seg[0]) >= 0) id = seg[1];
    return id && /^[A-Za-z0-9_-]{11}$/.test(id) ? id : null;
  }
  var vIn = $('#ad-video'), vBox = $('#ad-yt'), vImg = $('#ad-yt-i'), vA = $('#ad-yt-a'), vX = $('#ad-yt-x'), vNow = null;
  function preview() {
    var id = ytId(vIn.value);
    if (id === vNow) return;
    vNow = id;
    if (!id) { vBox.hidden = true; return; }
    vImg.src = 'https://i.ytimg.com/vi/' + id + '/mqdefault.jpg';
    vA.href = 'https://www.youtube.com/watch?v=' + id;
    vBox.hidden = false; vBox.classList.remove('is-missing');
  }
  if (vIn) {
    // a video that does not exist, or is private, answers with a 120x90 grey placeholder
    vImg.addEventListener('load', function () { vBox.classList.toggle('is-missing', vImg.naturalWidth <= 120); });
    vIn.addEventListener('input', preview);
    vX.addEventListener('click', function () { vIn.value = ''; preview(); check(vIn); Draft.soon(); vIn.focus(); });
    preview();
  }

  /* ------------------------------------------------------------ the checks */
  function number(v) {                    // the same reading as VehicleController::number
    var s = String(v || '').trim().replace(/\s|€|eur(?:os)?|kms?/gi, '');
    if (/^\d{1,3}(\.\d{3})+(,\d{1,2})?$/.test(s)) return s.replace(/\./g, '').replace(',', '.');
    if (/^\d{1,3}(,\d{3})+(\.\d{1,2})?$/.test(s)) return s.replace(/,/g, '');
    if (/^\d+,\d{1,2}$/.test(s)) return s.replace(',', '.');
    return s;
  }
  var YEAR_MAX = new Date().getFullYear() + 1;
  var RULES = {
    brand: function (v) { return v.trim() ? '' : 'Escribe la marca.'; },
    model: function (v) { return v.trim() ? '' : 'Escribe el modelo.'; },
    year: function (v) {
      v = v.trim();
      if (!v) return 'Pon el año.';
      return /^\d{4}$/.test(v) && +v >= 1900 && +v <= YEAR_MAX ? '' : (+v > YEAR_MAX ? 'Ese año todavía no ha llegado.' : 'El año, con cuatro cifras: 2018.');
    },
    price: function (v) { v = number(v); if (!v) return 'Pon el precio.'; return /^\d+(\.\d{1,2})?$/.test(v) ? '' : 'El precio, solo el número: 23700.'; },
    mileage: function (v) { v = number(v); return !v || /^\d+$/.test(v) ? '' : 'Los kilómetros, solo el número: 137000.'; },
    original_price: function (v) { v = number(v); return !v || /^\d+(\.\d{1,2})?$/.test(v) ? '' : 'Solo el número: 25900.'; },
    offer_price: function (v) { v = number(v); return !v || /^\d+(\.\d{1,2})?$/.test(v) ? '' : 'Solo el número: 22900.'; },
    priority: function (v) { v = number(v); return !v || (/^\d+$/.test(v) && +v <= 999) ? '' : 'Un número entre 0 y 999.'; },
    video_url: function (v) { return !v.trim() || ytId(v) ? '' : 'Este enlace no es de un vídeo de YouTube. Cópialo con el botón «Compartir» del vídeo.'; }
  };
  function mark(el, msg) {
    var box = el.closest('.ad-field'), err = box && $('.ad-err', box);
    if (msg) {
      if (!err) { err = document.createElement('span'); err.className = 'ad-err'; box.appendChild(err); }
      err.textContent = msg; err.id = 'ad-err-' + el.name;
      el.classList.add('is-bad'); el.setAttribute('aria-invalid', 'true'); el.setAttribute('aria-describedby', err.id);
    } else {
      if (err) err.parentNode.removeChild(err);
      el.classList.remove('is-bad'); el.removeAttribute('aria-invalid'); el.removeAttribute('aria-describedby');
    }
  }
  function check(el) {
    var rule = RULES[el.name];
    if (!rule) return true;
    var msg = rule(el.value);
    mark(el, msg);
    return !msg;
  }
  function checkAll() {
    var bad = Object.keys(RULES).map(function (n) { return form.elements.namedItem(n); })
      .filter(function (el) { return el && !check(el); });
    if (bad.length) {
      var el = bad[0], dt = el.closest('details');
      if (dt) dt.open = true;
      el.scrollIntoView({ block: 'center', behavior: 'smooth' });
      try { el.focus({ preventScroll: true }); } catch (e) { el.focus(); }
    }
    return !bad.length;
  }
  form.addEventListener('focusout', function (e) {
    var el = e.target;
    if (RULES[el.name] && (el.value || el.classList.contains('is-bad'))) check(el);
  });

  /* --------------------------------------------------------- what he does */
  form.addEventListener('input', function (e) {
    if (e.target.classList.contains('is-bad')) check(e.target);   // the message goes as he fixes it
    Draft.soon();
  });
  form.addEventListener('change', function (e) {
    var t = e.target, li = t.closest && t.closest('.ad-shot');
    if (t.matches('[data-up]')) {
      var cat = t.getAttribute('data-up');
      Array.prototype.slice.call(t.files || []).forEach(function (f) { Up.add(f, cat); });
      t.value = '';                         // sent one by one; never again with the form
      return;
    }
    if (li && t.classList.contains('ad-tag__s')) {
      if (restoring) { Up.setCat(li, t.value); place(li, t.value); }
      else Order.regroup(li, t.value);
    }
    if (li && t.type === 'checkbox') { li.classList.toggle('is-gone', t.checked); counts(); }
    if (t.classList.contains('ad-shot__cv')) coverHint();
    if (t.name === 'status' && isNew) saveGo.textContent = t.value === 'draft' ? 'Guardar el borrador' : 'Publicar el coche';
    Draft.soon();
  });
  form.addEventListener('click', function (e) {
    var b = e.target.closest && e.target.closest('[data-unstage]');
    if (!b) return;
    var li = b.closest('.ad-shot');
    if (li && li._it) Up.cancel(li._it);
  });
  // a drop onto a group lands in that group
  $$('.ad-cat', form).forEach(function (c) {
    var cat = c.getAttribute('data-cat');
    if (cat === 'none') return;
    ['dragenter', 'dragover'].forEach(function (n) { c.addEventListener(n, function (e) { e.preventDefault(); c.classList.add('is-over'); }); });
    ['dragleave', 'drop'].forEach(function (n) { c.addEventListener(n, function () { c.classList.remove('is-over'); }); });
    c.addEventListener('drop', function (e) {
      e.preventDefault();
      Array.prototype.slice.call((e.dataTransfer && e.dataTransfer.files) || []).forEach(function (f) {
        if (/^image\//.test(f.type) || /\.(heic|heif|avif)$/i.test(f.name)) Up.add(f, cat);
      });
    });
  });
  // "Siguiente" on the phone's keyboard moves on; it does not post a half-filled form
  form.addEventListener('keydown', function (e) {
    if (e.key !== 'Enter' || e.isComposing) return;
    var t = e.target;
    if (t.tagName !== 'INPUT' || /^(submit|button|checkbox|radio|file)$/.test(t.type)) return;
    e.preventDefault();
    var all = $$('input[type="text"], input[type="url"], input[type="date"], textarea, select.ad-in', form)
      .filter(function (el) { return !el.disabled && el.offsetParent !== null; });
    var nx = all[all.indexOf(t) + 1];
    if (nx) nx.focus(); else t.blur();
  });

  /* ---------------------------------------------------------------- saving */
  var sending = false;
  function busy(on, text) {
    saveGo.disabled = !!on;
    saveGo.setAttribute('aria-busy', on ? 'true' : 'false');
    saveGo.textContent = on ? text : (isNew ? (($('input[name="status"]:checked', form) || {}).value === 'draft' ? 'Guardar el borrador' : 'Publicar el coche') : saveGo.getAttribute('data-label'));
  }
  function trySave() {
    if (sending) return;
    if (!checkAll()) { say('Falta algo: está marcado en rojo.', 'bad'); return; }
    if (Up.pending()) { waiting = true; busy(true, 'Esperando a las fotos…'); status(); return; }
    if (Up.failed()) { status(); return; }
    if (navigator.onLine === false) {
      say('Sin conexión: se guarda en cuanto vuelva.', 'bad');
      window.addEventListener('online', function once() { window.removeEventListener('online', once); trySave(); });
      return;
    }
    sending = true; busy(true, 'Guardando…');
    ping(8000).then(function (r) {
      if (r === 'lost') { sending = false; busy(false); return; }
      Order.sync();
      Draft.now();
      $$('input[type="file"][data-up]', form).forEach(function (i) { i.disabled = true; });
      window.removeEventListener('beforeunload', guard);
      HTMLFormElement.prototype.submit.call(form);
    });
  }
  form.addEventListener('submit', function (e) { e.preventDefault(); trySave(); });
  // Back from the next page (bfcache) must not find the button stuck on "Guardando…"
  window.addEventListener('pageshow', function (e) {
    if (e.persisted) { sending = false; busy(false); $$('input[type="file"][data-up]', form).forEach(function (i) { i.disabled = false; }); window.addEventListener('beforeunload', guard); }
  });
  function guard(e) {
    if (Up.pending()) { e.preventDefault(); e.returnValue = ''; return ''; }
  }
  window.addEventListener('beforeunload', guard);

  /* What the coches.net sheet reader (_sheet-script) needs from this form: the
     draft token its photograph is staged under, how many photos there are, and a
     way to put that photograph in like one sent from the phone. */
  window.mcCarForm = {
    token: function () { return tokenIn.value; },
    photos: function () { return $$('.ad-shot', form).filter(function (li) { return !li.classList.contains('is-gone'); }).length; },
    addPhotos: function (list) { Up.restore(list); counts(); Order.sync(); Draft.soon(); },
    csrf: csrf
  };

  /* ------------------------------------------------------------------ start */
  $$('.ad-shot.is-new[data-state="done"]', form).forEach(Up.adopt);
  Draft.restore();
  counts();
  Order.sync();
  status();
})();
</script>
@endpush
