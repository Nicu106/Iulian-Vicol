/* /muestras/opiniones — the ten proposals and the showroom.
   One file, one behaviour per data-attribute; each part runs only where its
   markup is on the page. html.js is set in the <head> and removed by any
   error here, which drops every proposal to its static, complete form. */
(function () {
  'use strict';
  var d = document, root = d.documentElement;
  var RM = window.matchMedia('(prefers-reduced-motion: reduce)');
  var $ = function (s, c) { return (c || d).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || d).querySelectorAll(s)); };
  var on = function (el, ev, fn, o) { el.addEventListener(ev, fn, o || false); };
  var still = function () { return RM.matches; };
  var embed = root.classList.contains('is-embed');
  /* What part of this document is on screen. Normally the window; inside a
     showroom frame that is as tall as its content, the slice of it the page
     around it is showing (window.msView, from the <head>). */
  var vis = function () { return window.msView ? window.msView() : { top: 0, h: window.innerHeight }; };

  /* Inside the showroom: tell the frame how tall this is, whenever that
     changes (pictures arriving, "Leer más", "Ver todas", a panel opening). */
  if (embed && window.parent !== window) {
    var lastH = 0;
    var postH = function () {
      var h = Math.ceil(d.body.getBoundingClientRect().height);
      if (h === lastH) return;
      lastH = h;
      window.parent.postMessage({ type: 'ms:h', h: h }, location.origin);
    };
    if ('ResizeObserver' in window) new ResizeObserver(postH).observe(d.body);
    on(window, 'load', postH);
    postH();
  }

  /* ---------------------------------------------------------------- data */
  var DATA = {}, ORDER = [];
  var dataEl = $('#ms-data');
  if (dataEl) {
    JSON.parse(dataEl.textContent).forEach(function (r) { DATA[r.id] = r; ORDER.push(r.id); });
  }

  function words(text) { return (text.match(/\S+/g) || []).length; }

  /* A swipe: horizontal travel past 40px that beats the vertical. The page's
     own vertical scroll is left alone (touch-action: pan-y in the CSS). */
  function swipe(el, fn) {
    var x0 = null, y0 = 0, id = null;
    on(el, 'pointerdown', function (e) {
      if (e.pointerType === 'mouse' && e.button !== 0) return;
      x0 = e.clientX; y0 = e.clientY; id = e.pointerId;
    });
    on(el, 'pointerup', function (e) {
      if (x0 === null || e.pointerId !== id) return;
      var dx = e.clientX - x0, dy = e.clientY - y0;
      x0 = null;
      if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy) * 1.2) fn(dx < 0 ? 1 : -1);
    });
    on(el, 'pointercancel', function () { x0 = null; });
  }

  /* ------------------------------------------------------- "Leer más" */
  on(d, 'click', function (e) {
    var b = e.target.closest && e.target.closest('[data-more]');
    if (!b) return;
    var q = b.previousElementSibling;
    if (!q) return;
    var open = b.getAttribute('aria-expanded') !== 'true';
    q.classList.toggle('is-open', open);
    b.setAttribute('aria-expanded', open ? 'true' : 'false');
    b.textContent = open ? 'Leer menos' : 'Leer más';
    // closing a long letter would leave the reader far below it
    if (!open && b.getBoundingClientRect().top < vis().top) b.scrollIntoView({ block: 'center' });
  });

  /* --------------------------------------------------- "Ver todas…" */
  on(d, 'click', function (e) {
    var b = e.target.closest && e.target.closest('[data-reveal]');
    if (!b) return;
    var lists = b.getAttribute('data-reveal').split(' ').map(function (id) { return d.getElementById(id); }).filter(Boolean);
    if (!lists.length) return;
    var later = [];
    lists.forEach(function (list) {
      later = later.concat($$('.is-later, .is-later-m', list).filter(function (el) { return el.offsetParent === null; }));
      $$('.is-later, .is-later-m', list).forEach(function (el) { el.classList.remove('is-later', 'is-later-m'); });
      list.dispatchEvent(new CustomEvent('ms:revealed'));
    });
    var holder = b.closest('.ms-reveal');
    if (holder) holder.parentNode.removeChild(holder);
    // the first newly shown review takes the focus, so the keyboard carries on
    // where the eye does, and a screen reader starts reading it
    var first = later[0];
    if (first && !first.hasAttribute('tabindex')) first = first.querySelector('a[href], button') || first;
    if (first) first.focus({ preventScroll: true });
  });

  /* ================================================================
     The viewer: one review whole, previous / next, Esc and Back close.
     ================================================================ */
  var V = (function () {
    var dlg = $('#ms-dlg');
    if (!dlg || !ORDER.length) return null;
    var ph = $('.ms-dlg__ph', dlg), q = $('.ms-dlg__q', dlg), by = $('.ms-dlg__by', dlg),
        live = $('[data-dlg-live]', dlg), txt = $('.ms-dlg__txt', dlg);
    var i = 0, opener = null, pushed = false;

    function fill(k, announce) {
      i = (k + ORDER.length) % ORDER.length;
      var r = DATA[ORDER[i]];
      var img = d.createElement('img');
      img.alt = r.caption;
      img.width = r.w; img.height = r.h;
      img.decoding = 'async';
      if (r.srcset) { img.srcset = r.srcset; img.sizes = '(min-width:760px) 600px, 100vw'; }
      img.src = r.src;
      ph.textContent = ''; ph.appendChild(img);
      q.textContent = '';
      q.className = 'ms-dlg__q is-' + r.tier;
      r.paras.forEach(function (p) { var el = d.createElement('p'); el.textContent = p; q.appendChild(el); });
      by.textContent = r.caption;
      txt.scrollTop = 0;
      if (announce) live.textContent = r.caption;
      // the next picture, so the step is instant
      var n = DATA[ORDER[(i + 1) % ORDER.length]];
      if (n && n.srcset) { var pre = new Image(); pre.sizes = img.sizes; pre.srcset = n.srcset; }
    }
    function open(id, from) {
      var k = ORDER.indexOf(+id);
      if (k < 0) return;
      opener = from || d.activeElement;
      fill(k, false);
      live.textContent = '';
      if (embed) {
        // a frame as tall as its content: bring a screen's worth of it into view
        // and tell the page around it to hold still while the viewer is open
        var v = vis(), docH = d.body.getBoundingClientRect().height;
        var want = Math.max(0, Math.min(v.top, docH - v.h));
        if (want !== v.top) window.parent.scrollBy(0, want - v.top);
        window.parent.postMessage({ type: 'ms:modal', open: true }, location.origin);
      }
      if (typeof dlg.showModal === 'function') dlg.showModal(); else dlg.setAttribute('open', '');
      // a history entry, so the phone's Back closes the viewer, not the page
      try { history.pushState({ msViewer: 1 }, ''); pushed = true; } catch (e) { pushed = false; }
      var x = $('[data-dlg="x"]', dlg); if (x) x.focus({ preventScroll: true });
    }
    function close() { if (dlg.open) dlg.close(); }
    on(dlg, 'close', function () {
      if (embed) window.parent.postMessage({ type: 'ms:modal', open: false }, location.origin);
      if (pushed) { pushed = false; if (history.state && history.state.msViewer) history.back(); }
      if (opener && opener.focus) opener.focus({ preventScroll: true });
    });
    on(window, 'popstate', function () { if (dlg.open) { pushed = false; dlg.close(); } });
    on(dlg, 'click', function (e) {
      if (e.target === dlg) return close();   // the backdrop
      var b = e.target.closest('[data-dlg]');
      if (!b) return;
      var a = b.getAttribute('data-dlg');
      if (a === 'x') close(); else fill(i + (+a), true);
    });
    on(dlg, 'keydown', function (e) {
      if (e.key === 'ArrowRight') { fill(i + 1, true); e.preventDefault(); }
      else if (e.key === 'ArrowLeft') { fill(i - 1, true); e.preventDefault(); }
    });
    swipe(ph, function (dir) { fill(i + dir, true); });

    on(d, 'click', function (e) {
      var t = e.target.closest && e.target.closest('[data-open]');
      if (!t || dlg.contains(t)) return;
      e.preventDefault();
      open(t.getAttribute('data-open'), t);
    });
    // links that open it (the wall): they say what they do, and Space works
    $$('a[data-open]').forEach(function (a) {
      a.setAttribute('role', 'button');
      a.setAttribute('aria-haspopup', 'dialog');
      on(a, 'keydown', function (e) { if (e.key === ' ') { e.preventDefault(); a.click(); } });
    });
    return { open: open, close: close };
  })();

  /* ================================================================
     A stack: one thing shown, crossfaded to the next. The stage is held
     at its tallest member's height, so changing never moves the page.
     ================================================================ */
  function Stack(stage) {
    var slides = $$('.ms-stack__s', stage);
    var cur = Math.max(0, slides.findIndex(function (s) { return s.classList.contains('is-on'); }));
    var timer = null;

    function fit() {
      stage.style.minHeight = '';
      var max = 0;
      slides.forEach(function (s) {
        var shown = s.classList.contains('is-on');
        if (!shown) s.classList.add('is-measure');
        max = Math.max(max, s.offsetHeight);
        if (!shown) s.classList.remove('is-measure');
      });
      stage.style.minHeight = max + 'px';
    }
    function near(k) {
      slides.forEach(function (s) { s.classList.remove('is-near'); });
      var n = slides[(k + 1) % slides.length];
      if (n && n !== slides[k]) n.classList.add('is-near');
    }
    function show(k) {
      k = (k + slides.length) % slides.length;
      if (k === cur) return;
      var a = slides[cur], b = slides[k];
      clearTimeout(timer);
      slides.forEach(function (s) { if (s !== a) s.classList.remove('is-off'); });
      b.classList.remove('is-near');
      b.classList.add('is-near'); void b.offsetWidth; b.classList.remove('is-near');
      a.classList.remove('is-on'); a.classList.add('is-off');
      b.classList.add('is-on');
      cur = k;
      timer = setTimeout(function () { a.classList.remove('is-off'); near(cur); }, still() ? 0 : 900);
    }
    near(cur);
    fit();
    if (d.fonts && d.fonts.ready) d.fonts.ready.then(fit);
    var w = window.innerWidth, t;
    on(window, 'resize', function () {
      if (window.innerWidth === w) return;   // a phone's toolbar, not a new width
      w = window.innerWidth; clearTimeout(t); t = setTimeout(fit, 120);
    });
    return { show: show, get i() { return cur; }, get n() { return slides.length; }, slides: slides, fit: fit };
  }

  /* ================================================================ 1 · Mosaico */
  $$('[data-mosaic]').forEach(function (box) {
    var items = $$('.ms-mas__i', box), cols = 0;
    // two on a phone (small pictures, short words, the rest a touch away),
    // four on a computer so a screen holds a row of them, not one giant photograph
    function count() { var w = box.clientWidth; return w >= 1100 ? 4 : (w >= 640 ? 3 : 2); }
    function layout(force) {
      var n = count();
      if (n === cols && !force) return;
      cols = n;
      var pool = d.createDocumentFragment();
      items.forEach(function (it) { pool.appendChild(it); });
      box.textContent = '';
      var cs = [];
      for (var k = 0; k < n; k++) { var c = d.createElement('div'); c.className = 'ms-mas__col'; box.appendChild(c); cs.push(c); }
      box.classList.add('is-cols');
      // shortest column first, in order: the reading order stays across
      items.forEach(function (it) {
        var best = cs[0];
        cs.forEach(function (c) { if (c.offsetHeight < best.offsetHeight - 1) best = c; });
        best.appendChild(it);
      });
    }
    layout(true);
    if (d.fonts && d.fonts.ready) d.fonts.ready.then(function () { layout(true); });
    on(box, 'ms:revealed', function () { layout(true); });
    var t; on(window, 'resize', function () { clearTimeout(t); t = setTimeout(function () { layout(false); }, 120); });
  });

  /* ================================================================ 2 · Cine
     A computer: one review at a time, crossfaded in place. A phone: the same
     reviews as a row the thumb swipes, the next one showing at the edge; the
     platform's own scrolling does the swipe. Both move on by themselves,
     slowly, for as long as the words take to read, and hold under a touch,
     a resting pointer or the keyboard. Any control hands it to the visitor. */
  $$('[data-cine]').forEach(function (sec) {
    var stage = $('[data-stage]', sec);
    var slides = $$('.ms-cine__s', stage);
    var wideQ = window.matchMedia('(min-width:1000px)');
    var wide = wideQ.matches;
    var st = wide ? Stack(stage) : null;
    var bar = $('.ms-cine__time span', sec), live = $('[data-live]', sec), play = $('[data-play]', sec);
    var auto = !still(), held = 0, timer = null, visible = false, at = 0;

    // which one is on: the stack knows; on a phone, the slide nearest the start edge
    function cur() {
      if (st) return st.i;
      var pl = parseFloat(getComputedStyle(stage).scrollPaddingLeft) || 0, best = 0, bd = 1e9;
      slides.forEach(function (s, k) { var dd = Math.abs(s.offsetLeft - stage.offsetLeft - pl - stage.scrollLeft); if (dd < bd) { bd = dd; best = k; } });
      return best;
    }
    function show(k) {
      k = (k + slides.length) % slides.length;
      if (st) return st.show(k);
      var pl = parseFloat(getComputedStyle(stage).scrollPaddingLeft) || 0;
      stage.scrollTo({ left: slides[k].offsetLeft - stage.offsetLeft - pl, behavior: still() ? 'auto' : 'smooth' });
      at = k;
    }
    function dwell() {
      // as long as the words on screen take to read (238 wpm), plus a moment
      // for the photograph; never under 6s, never over 16s
      var s = slides[cur()], q = s && (s.querySelector('.ms-q__short--' + (wide ? 'd' : 'm')) || s.querySelector('.ms-q__short') || s.querySelector('.ms-q'));
      return Math.max(6000, Math.min(16000, 2500 + words(q ? q.textContent : '') / 238 * 60000));
    }
    function stop() {
      clearTimeout(timer); timer = null;
      bar.style.transition = 'none'; bar.style.transform = 'scaleX(0)';
    }
    function run() {
      stop();
      if (!auto || held || !visible || d.hidden) return;
      var ms = dwell();
      void bar.offsetWidth;
      bar.style.transition = 'transform ' + ms + 'ms linear';
      bar.style.transform = 'scaleX(1)';
      timer = setTimeout(function () { show(cur() + 1); run(); }, ms);
    }
    function setAuto(v) {
      auto = v;
      sec.classList.toggle('is-paused', !v);
      play.setAttribute('aria-label', v ? 'Pausar' : 'Reproducir');
      run();
    }
    function go(dir) {
      setAuto(false);                       // the visitor has taken over
      var k = (cur() + dir + slides.length) % slides.length;
      show(k);
      live.textContent = slides[k].getAttribute('aria-label');
    }
    if (still()) { sec.classList.add('is-still'); auto = false; }
    sec.classList.toggle('is-paused', !auto);
    if (!wide) stage.style.minHeight = '';

    $$('[data-go]', sec).forEach(function (b) { on(b, 'click', function () { go(+b.getAttribute('data-go')); }); });
    on(play, 'click', function () { setAuto(!auto); });
    if (wide) swipe(stage, go);
    else on(stage, 'pointerdown', function () { setAuto(false); });   // a thumb on the row: theirs now
    on(sec, 'keydown', function (e) {
      if (e.key === 'ArrowRight') { go(1); e.preventDefault(); }
      if (e.key === 'ArrowLeft') { go(-1); e.preventDefault(); }
    });
    on(sec, 'pointerenter', function (e) { if (e.pointerType === 'mouse') { held |= 1; run(); } });
    on(sec, 'pointerleave', function (e) { if (e.pointerType === 'mouse') { held &= ~1; run(); } });
    on(sec, 'focusin', function () { held |= 2; run(); });
    on(sec, 'focusout', function (e) { if (!sec.contains(e.relatedTarget)) { held &= ~2; run(); } });
    on(d, 'visibilitychange', run);
    // crossing 1000px changes the mechanism, not just the layout: start again
    wideQ.addEventListener && wideQ.addEventListener('change', function () { location.reload(); });
    RM.addEventListener && RM.addEventListener('change', function () {
      sec.classList.toggle('is-still', still()); if (still()) setAuto(false);
    });
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (es) { visible = es[0].isIntersecting; run(); }, { threshold: 0.35 }).observe(sec);
    } else { visible = true; run(); }
  });

  /* ================================================================ 3 · Carril */
  $$('[data-rail]').forEach(function (rail) {
    var list = $('.ms-rail__list', rail), btn = $$('[data-step]', rail);
    function state() {
      var max = list.scrollWidth - list.clientWidth;
      btn[0].setAttribute('aria-disabled', list.scrollLeft <= 2 ? 'true' : 'false');
      btn[1].setAttribute('aria-disabled', list.scrollLeft >= max - 2 ? 'true' : 'false');
    }
    btn.forEach(function (b) {
      on(b, 'click', function () {
        if (b.getAttribute('aria-disabled') === 'true') return;
        var card = $('.ms-rail__i', list), gap = parseFloat(getComputedStyle(list).columnGap) || 0;
        var step = card ? card.offsetWidth + gap : list.clientWidth * 0.8;
        // a whole number of cards per press: as many as are fully in view
        var n = Math.max(1, Math.floor((list.clientWidth - 64) / step));
        list.scrollBy({ left: +b.getAttribute('data-step') * n * step, behavior: still() ? 'auto' : 'smooth' });
      });
    });
    var raf = 0;
    on(list, 'scroll', function () { cancelAnimationFrame(raf); raf = requestAnimationFrame(state); }, { passive: true });
    on(window, 'resize', state);
    state();
  });

  /* ================================================================ 5 · Protagonista */
  $$('[data-spot]').forEach(function (box) {
    var stage = $('[data-stage]', box), st = Stack(stage), btns = $$('[data-pick]', box);
    btns.forEach(function (b) {
      on(b, 'click', function () {
        var k = +b.getAttribute('data-pick');
        btns.forEach(function (x) { x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
        st.show(k);
        b.scrollIntoView({ block: 'nearest', inline: 'nearest', behavior: still() ? 'auto' : 'smooth' });
        // on a phone the stage may be above the screen by now
        var top = stage.getBoundingClientRect().top;
        if (top < vis().top) stage.scrollIntoView({ block: 'start', behavior: still() ? 'auto' : 'smooth' });
      });
    });
    // arrows move along the row of customers
    on($('.ms-spot__pick', box), 'keydown', function (e) {
      var k = btns.indexOf(d.activeElement);
      if (k < 0) return;
      if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
        var n = btns[(k + (e.key === 'ArrowRight' ? 1 : -1) + btns.length) % btns.length];
        n.focus(); e.preventDefault();
      }
    });
  });

  /* ================================================================ 6 · Dos filas */
  $$('[data-drift]').forEach(function (box) {
    var SPEED = 34, TAU = 150;
    var rows = $$('.ms-drift__row', box).map(function (row) {
      return { row: row, belt: $('.ms-drift__belt', row), dir: +row.getAttribute('data-dir'),
               x: 0, v: 0, w: 0, hover: false, focus: false, touch: 0, drag: null, clones: [] };
    });
    var halted = false, visible = false, raf = 0, last = 0;

    function build(r) {
      r.clones.forEach(function (c) { c.remove(); }); r.clones = [];
      var track = $('.ms-drift__track', r.belt);
      var gap = parseFloat(getComputedStyle(r.belt).columnGap) || 0;
      r.w = track.offsetWidth + gap;
      // enough copies to cover the widest screen twice over
      var need = Math.max(1, Math.ceil((r.row.clientWidth * 2) / Math.max(1, r.w)));
      for (var k = 0; k < need; k++) {
        var c = track.cloneNode(true);
        c.setAttribute('aria-hidden', 'true'); c.setAttribute('inert', '');
        c.removeAttribute('aria-label');
        $$('[id]', c).forEach(function (el) { el.removeAttribute('id'); });
        $$('img', c).forEach(function (im) { im.alt = ''; });
        r.belt.appendChild(c); r.clones.push(c);
      }
      r.x = r.dir > 0 ? -r.w : 0;
      paint(r);
    }
    function paint(r) {
      while (r.x <= -r.w) r.x += r.w;
      while (r.x > 0) r.x -= r.w;
      r.belt.style.transform = 'translate3d(' + r.x.toFixed(2) + 'px,0,0)';
    }
    function tick(t) {
      raf = 0;
      var dt = last ? Math.min(64, t - last) : 16; last = t;
      rows.forEach(function (r) {
        var stop = r.hover || r.focus || r.touch || r.drag;
        var want = stop ? 0 : r.dir * SPEED * (window.innerWidth < 768 ? 0.8 : 1);
        r.v += (want - r.v) * (1 - Math.exp(-dt / TAU));
        if (!r.drag) { r.x += r.v * dt / 1000; paint(r); }
      });
      if (visible && !halted && !d.hidden) raf = requestAnimationFrame(tick);
    }
    function start() { if (!raf && visible && !halted && !d.hidden) { last = 0; raf = requestAnimationFrame(tick); } }
    function makeStatic() {
      halted = true; cancelAnimationFrame(raf); raf = 0;
      box.classList.add('is-static');
      rows.forEach(function (r) { r.clones.forEach(function (c) { c.remove(); }); r.clones = []; r.x = 0; r.belt.style.transform = ''; });
    }

    if (still()) { makeStatic(); return; }
    rows.forEach(build);

    rows.forEach(function (r) {
      on(r.row, 'pointerenter', function (e) { if (e.pointerType === 'mouse') r.hover = true; });
      on(r.row, 'pointerleave', function (e) { if (e.pointerType === 'mouse') r.hover = false; });
      on(r.row, 'focusin', function (e) {
        r.focus = true;
        if (halted) return;
        // focusing scrolls even an overflow:hidden box; the belt is ours to move
        r.row.scrollLeft = 0;
        // bring what the keyboard is on into view
        var card = e.target.closest('.ms-drift__i');
        if (!card) return;
        var a = card.getBoundingClientRect(), b = r.row.getBoundingClientRect(), pad = 24;
        if (a.left < b.left + pad) r.x += (b.left + pad) - a.left;
        else if (a.right > b.right - pad) r.x -= a.right - (b.right - pad);
        paint(r);
      });
      on(r.row, 'focusout', function (e) { if (!r.row.contains(e.relatedTarget)) r.focus = false; });
      // a finger or a mouse can take a row and move it
      on(r.row, 'pointerdown', function (e) {
        if (halted || (e.pointerType === 'mouse' && e.button !== 0)) return;
        if (e.pointerType !== 'mouse') { clearTimeout(r.touch); r.touch = 1; }
        r.drag = { x: e.clientX, x0: r.x, moved: false, id: e.pointerId };
      });
      on(r.row, 'pointermove', function (e) {
        if (!r.drag || e.pointerId !== r.drag.id) return;
        var dx = e.clientX - r.drag.x;
        if (!r.drag.moved && Math.abs(dx) > 6) {
          r.drag.moved = true; r.row.classList.add('is-drag');
          try { r.row.setPointerCapture(e.pointerId); } catch (er) {}
        }
        if (r.drag.moved) { r.x = r.drag.x0 + dx; paint(r); r.drag.x0 = r.x; r.drag.x = e.clientX; }
      });
      function end(e) {
        if (!r.drag || (e && e.pointerId !== r.drag.id)) return;
        var moved = r.drag.moved;
        r.drag = null; r.row.classList.remove('is-drag');
        if (r.touch) r.touch = setTimeout(function () { r.touch = 0; }, 2200);
        if (moved) {
          // a drag is not a press on what it ended over
          var stopClick = function (ev) { ev.stopPropagation(); ev.preventDefault(); };
          r.row.addEventListener('click', stopClick, true);
          setTimeout(function () { r.row.removeEventListener('click', stopClick, true); }, 0);
        }
      }
      on(r.row, 'pointerup', end);
      on(r.row, 'pointercancel', end);
    });

    var halt = $('[data-halt]', box);
    if (halt) on(halt, 'click', function () { makeStatic(); halt.remove(); var f = $('.ms-drift__track a, .ms-drift__track button', box); if (f) f.focus(); });
    RM.addEventListener && RM.addEventListener('change', function () { if (still()) makeStatic(); });
    on(d, 'visibilitychange', start);
    var w = window.innerWidth, t;
    on(window, 'resize', function () {
      if (halted || window.innerWidth === w) return;
      w = window.innerWidth; clearTimeout(t); t = setTimeout(function () { rows.forEach(build); }, 150);
    });
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (es) { visible = es[0].isIntersecting; start(); }).observe(box);
    } else { visible = true; start(); }
  });

  /* ================================================================ 7 · Foto fija */
  $$('[data-split]').forEach(function (box) {
    var pics = {}, cur = null, timer = null;
    $$('.ms-split__p', box).forEach(function (p) { pics[p.getAttribute('data-for')] = p; });
    var items = $$('.ms-split__i', box);
    var wide = window.matchMedia('(min-width:1000px)');

    function nearOf(id) {
      Object.keys(pics).forEach(function (k) { pics[k].classList.remove('is-near'); });
      var k = items.findIndex(function (it) { return it.getAttribute('data-id') === id; });
      [k - 1, k + 1].forEach(function (j) {
        var it = items[j];
        if (it && !it.classList.contains('is-later')) pics[it.getAttribute('data-id')].classList.add('is-near');
      });
    }
    function activate(id) {
      if (id === cur) return;
      var prev = cur ? pics[cur] : null, next = pics[id];
      items.forEach(function (it) { it.classList.toggle('is-active', it.getAttribute('data-id') === id); });
      clearTimeout(timer);
      if (prev) { prev.classList.remove('is-on'); prev.classList.add('is-off'); }
      if (next) { next.classList.remove('is-off'); next.classList.add('is-on'); }
      cur = id;
      timer = setTimeout(function () {
        Object.keys(pics).forEach(function (k) { if (k !== cur) pics[k].classList.remove('is-off'); });
        nearOf(cur);
      }, still() ? 0 : 900);
    }
    cur = items[0] ? items[0].getAttribute('data-id') : null;
    if (cur) nearOf(cur);

    // Inside a showroom frame there is no scrolling in here to stick to: the
    // page around it scrolls. The photo column follows that instead, with a
    // transform, inside the same bounds sticky would keep it in.
    if (embed) {
      var stick = $('.ms-split__stick', box);
      var follow = function () {
        if (!wide.matches) { stick.style.transform = ''; return; }
        var v = vis(), gap = 32;
        var y = Math.max(0, Math.min(v.top + gap - box.offsetTop, box.offsetHeight - stick.offsetHeight));
        stick.style.transform = 'translate3d(0,' + y + 'px,0)';
      };
      try { window.parent.addEventListener('scroll', follow, { passive: true }); window.parent.addEventListener('resize', follow); } catch (e) {}
      on(window, 'resize', follow);
      follow();
    }

    // the review crossing the middle of the screen is the one being read
    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (es) {
        if (!wide.matches) return;
        es.forEach(function (e) { if (e.isIntersecting) activate(e.target.getAttribute('data-id')); });
      }, { rootMargin: '-48% 0px -48% 0px' });
      items.forEach(function (it) { io.observe(it); });
    }
  });

  /* ================================================================ 8 · Álbum */
  $$('[data-album]').forEach(function (grid) {
    var panel = null, active = null;
    var tiles = $$('.ms-alb__i', grid);

    function rowEnd(li) {
      var top = li.offsetTop, end = li;
      tiles.forEach(function (t) { if (t.offsetParent && Math.abs(t.offsetTop - top) < 4 && t.offsetLeft > end.offsetLeft) end = t; });
      return end;
    }
    function build(li) {
      var r = DATA[li.getAttribute('data-id')];
      var p = d.createElement('li');
      p.className = 'ms-alb__panel'; p.id = 'alb-panel'; p.tabIndex = -1;
      p.setAttribute('role', 'region'); p.setAttribute('aria-label', r.caption);
      var ph = d.createElement('div'); ph.className = 'ms-ph';
      ph.style.setProperty('--r', r.box);
      var img = d.createElement('img');
      img.alt = r.caption; img.width = r.w; img.height = r.h; img.decoding = 'async';
      if (r.lqip) img.style.background = 'url("' + r.lqip + '") center/cover no-repeat';
      if (r.srcset) { img.srcset = r.srcset; img.sizes = '(min-width:900px) 440px, calc(100vw - 72px)'; }
      img.src = r.src;
      ph.appendChild(img);
      var words = d.createElement('div'); words.className = 'ms-alb__words';
      var q = $('.ms-q', li).cloneNode(true);
      $$('[id]', q).forEach(function (el) { el.removeAttribute('id'); });
      words.appendChild(q);
      var by = d.createElement('p'); by.className = 'ms-by'; by.textContent = r.caption;
      words.appendChild(by);
      var x = d.createElement('button');
      x.type = 'button'; x.className = 'ms-ctl'; x.setAttribute('aria-label', 'Cerrar');
      x.innerHTML = '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M5 5l14 14M19 5 5 19" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/></svg>';
      on(x, 'click', function () { close(true); });
      p.appendChild(ph); p.appendChild(words); p.appendChild(x);
      on(p, 'keydown', function (e) { if (e.key === 'Escape') { e.preventDefault(); close(true); } });
      return p;
    }
    function place() {
      if (!panel || !active) return;
      var end = rowEnd(active);
      if (end.nextSibling !== panel) end.parentNode.insertBefore(panel, end.nextSibling);
      var t = active.querySelector('.ms-alb__t');
      panel.style.setProperty('--nx', (active.offsetLeft - panel.offsetLeft + t.offsetWidth / 2) + 'px');
    }
    function close(refocus) {
      if (!panel) return;
      var was = active;
      panel.remove(); panel = null; active = null;
      tiles.forEach(function (t) { t.querySelector('.ms-alb__t').setAttribute('aria-expanded', 'false'); });
      if (refocus && was) was.querySelector('.ms-alb__t').focus();
    }
    tiles.forEach(function (li) {
      var t = li.querySelector('.ms-alb__t');
      on(t, 'click', function () {
        if (active === li) return close(false);
        if (panel) panel.remove();
        tiles.forEach(function (x) { x.querySelector('.ms-alb__t').setAttribute('aria-expanded', 'false'); });
        active = li; panel = build(li);
        t.setAttribute('aria-expanded', 'true');
        place();
        panel.focus({ preventScroll: true });
        var r = panel.getBoundingClientRect(), v = vis();
        if (r.bottom > v.top + v.h || r.top < v.top) {
          panel.scrollIntoView({ block: r.height > v.h ? 'start' : 'nearest', behavior: still() ? 'auto' : 'smooth' });
        }
      });
    });
    var w = window.innerWidth, tm;
    on(window, 'resize', function () {
      if (window.innerWidth === w) return;
      w = window.innerWidth; clearTimeout(tm);
      tm = setTimeout(place, 120);
    });
  });

  /* ================================================================
     The showroom: device frames, the width toggle, the index.
     ================================================================ */
  /* Each frame is as tall as what it holds: the proposal posts its height and
     the frame takes it, so nothing scrolls inside anything. Only messages from
     this origin, from one of these frames, carrying a number, are believed. */
  if ($('.sh-frame')) {
    on(window, 'message', function (e) {
      if (e.origin !== location.origin || !e.data || typeof e.data !== 'object') return;
      var f = $$('.sh-frame').filter(function (x) { return x.contentWindow === e.source; })[0];
      if (!f) return;
      if (e.data.type === 'ms:h') {
        var h = Math.round(+e.data.h);
        if (h > 0 && h < 60000) f.style.height = h + 'px';
      } else if (e.data.type === 'ms:modal') {
        root.classList.toggle('sh-lock', !!e.data.open);
      }
    });
  }
  // compare: choosing a proposal loads it into that frame
  $$('[data-cmp]').forEach(function (sel) {
    on(sel, 'change', function () {
      var f = $('[data-cmp-frame="' + sel.getAttribute('data-cmp') + '"]');
      f.src = '/muestras/opiniones/' + encodeURIComponent(sel.value) + '?embed=1';
      f.title = 'Comparar: ' + sel.options[sel.selectedIndex].text;
    });
  });
  // under 900px the shell is on a phone: the proposal is shown as the phone shows it
  var phoneQ = window.matchMedia('(max-width:899px)');
  $$('.sh-v').forEach(function (sec) {
    var dev = $('.sh-dev', sec), frame = $('.sh-frame', sec);
    var picked = $('input[type=radio]:checked', sec);
    var mode = picked ? picked.value : 'full';
    function layout() {
      var bare = phoneQ.matches || mode === 'full';
      dev.className = 'sh-dev' + (bare ? ' is-bare' : ' sh-dev--' + mode);
      sec.classList.toggle('is-bare', bare);
      frame.style.width = bare ? '100%' : mode + 'px';
    }
    $$('input[type=radio]', sec).forEach(function (r) {
      on(r, 'change', function () { if (r.checked) { mode = r.value; layout(); } });
    });
    phoneQ.addEventListener && phoneQ.addEventListener('change', layout);
    layout();
  });
  var idx = $$('.sh-index__a');
  if (idx.length && 'IntersectionObserver' in window) {
    var mark = function (id) {
      idx.forEach(function (a) {
        var me = a.getAttribute('href') === '#' + id;
        if (me) {
          a.setAttribute('aria-current', 'true');
          // keep the marked entry inside the index row, without touching the page's scroll
          var l = a.parentNode.parentNode, x = a.offsetLeft - l.offsetLeft;
          if (x < l.scrollLeft || x + a.offsetWidth > l.scrollLeft + l.clientWidth) l.scrollLeft = x - 16;
        }
        else a.removeAttribute('aria-current');
      });
    };
    var sio = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) mark(e.target.id); });
    }, { rootMargin: '-40% 0px -55% 0px' });
    $$('.sh-v').forEach(function (s) { sio.observe(s); });
  }
})();
