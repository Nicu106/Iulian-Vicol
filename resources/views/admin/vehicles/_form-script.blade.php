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
        photos still going up, refreshes the token, then posts. --}}
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
  var lost = false, waiting = false, savedAt = 0, flash = '', flashT = 0;
  function say(text, kind, actions) {
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
    var p = Up.pending(), b = Up.failed();
    if (p) return say((p === 1 ? 'Subiendo 1 foto' : 'Subiendo ' + p + ' fotos') + (waiting ? '; se guarda al terminar.' : '…'), 'busy');
    if (b) return say(b === 1 ? '1 foto no se ha subido.' : b + ' fotos no se han subido.', 'bad',
      [['Reintentar', function () { Up.retryFailed(); }], ['Quitarlas', function () { Up.dropFailed(); }]]);
    if (flash) return say(flash);
    say(savedAt ? 'Copia guardada en este teléfono.' : '');
  }
  function tell(text) { flash = text; clearTimeout(flashT); flashT = setTimeout(function () { flash = ''; status(); }, 2600); status(); }

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
  function place(li, cat) {
    var list = listOf(cat), i = +li.getAttribute('data-i'), before = null;
    $$('.ad-shot', list).some(function (o) { if (o !== li && +o.getAttribute('data-i') > i) { before = o; return true; } return false; });
    list.insertBefore(li, before);
    var tag = $('.ad-tag', li);
    if (tag) tag.setAttribute('data-tag', cat);
    counts();
  }
  function counts() {
    $$('.ad-cat', form).forEach(function (c) {
      var all = $$('.ad-shot', c);
      $('[data-count]', c).textContent = all.filter(function (li) { return !li.classList.contains('is-gone'); }).length;
      if (c.getAttribute('data-cat') === 'none') c.hidden = !all.length;
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
    var pick = $$('.ad-cat[data-cat="exterior"] .ad-shot', form).filter(live)[0];
    if (!pick) {
      pick = $$('.ad-shot', form).filter(live).sort(function (a, b) { return a.getAttribute('data-i') - b.getAttribute('data-i'); })[0];
    }
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
        '<img class="ad-shot__i" alt=""><span class="ad-shot__flag">Portada</span><span class="ad-vh">Hacer esta foto la portada</span></label>' +
        '<span class="ad-shot__st"><span class="ad-shot__bar"><i></i></span><span class="ad-shot__say"></span></span>' +
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

  /* -------------------------------------------------------- the phone's copy */
  var Draft = (function () {
    var KEY = form.getAttribute('data-draft'), BASE = form.getAttribute('data-base'), t = 0, first = null, off = false;
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
      var f = fields(), staged = Up.done();
      try {
        if (JSON.stringify(f) === first && !staged.length) { s.removeItem(KEY); savedAt = 0; }
        else { s.setItem(KEY, JSON.stringify({ v: 1, at: Date.now(), base: BASE, token: tokenIn.value, fields: f, staged: staged })); savedAt = Date.now(); }
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
      var s = ls(), d = null;
      if (!s) return;
      try { d = JSON.parse(s.getItem(KEY) || 'null'); } catch (e) { d = null; }
      if (!d) return;
      // After a failed save the server printed the form back itself: that is newer.
      if (form.getAttribute('data-old') === '1') return;
      if (d.v !== 1 || d.base !== BASE || Date.now() - d.at > 14 * 864e5) { try { s.removeItem(KEY); } catch (e) {} return; }

      var n = 0, touched = [];
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
      Up.setCat(li, t.value);
      place(li, t.value);
      tell('Foto movida a ' + TAGS[t.value] + '.');
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

  /* ------------------------------------------------------------------ start */
  $$('.ad-shot.is-new[data-state="done"]', form).forEach(Up.adopt);
  Draft.restore();
  counts();
  status();
})();
</script>
@endpush
