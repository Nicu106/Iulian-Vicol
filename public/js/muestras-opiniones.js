/* /muestras/opiniones — the four proposals and the showroom (second round).
   One file, one behaviour per data-attribute; each part runs only where its
   markup is on the page. html.js is set in the <head> and removed by any
   error here, which drops every proposal to its static, complete form.
   Motion is transform and opacity only, on --e-out, and none at all under
   prefers-reduced-motion. */
(function () {
  'use strict';
  var d = document, root = d.documentElement;
  var RM = window.matchMedia('(prefers-reduced-motion: reduce)');
  var $ = function (s, c) { return (c || d).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || d).querySelectorAll(s)); };
  var on = function (el, ev, fn, o) { el.addEventListener(ev, fn, o || false); };
  var still = function () { return RM.matches; };
  var EASE = 'cubic-bezier(.22,.61,.36,1)';          // --e-out
  var EASE_IO = 'cubic-bezier(.4,0,.2,1)';           // --e-inout
  var embed = root.classList.contains('is-embed');
  /* What part of this document is on screen. Normally the window; inside a
     content-height showroom frame, the slice of it the page around it shows. */
  var vis = function () { return window.msView ? window.msView() : { top: 0, h: window.innerHeight }; };
  var anim = function (el, kf, ms, ease, delay) {
    if (still() || !el.animate) return null;
    return el.animate(kf, { duration: ms, easing: ease || EASE, delay: delay || 0, fill: 'both' });
  };
  var done = function (a, fn) { if (a) a.finished.then(fn, fn); else fn(); };
  /* "in ms", on the animation clock (not setTimeout), so a step that has to
     land at a point of a motion stays on it even when the motion is slowed */
  var later = function (el, ms, fn) {
    if (still() || !el.animate) return fn();
    el.animate([{ opacity: 1 }, { opacity: 1 }], { duration: ms, composite: 'add' }).finished.then(fn, fn);
  };

  /* Inside the showroom (?embed=1): tell the frame how tall this is. */
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
  var modal = function (open) {
    if (embed) window.parent.postMessage({ type: 'ms:modal', open: !!open }, location.origin);
  };

  /* ---------------------------------------------------------------- data */
  var DATA = {}, ORDER = [];
  var dataEl = $('#ms-data');
  if (dataEl) JSON.parse(dataEl.textContent).forEach(function (r) { DATA[r.id] = r; ORDER.push(r.id); });

  /* A swipe: horizontal travel past 40px that beats the vertical. */
  function swipe(el, fn) {
    var x0 = null, y0 = 0, id = null;
    on(el, 'pointerdown', function (e) {
      if (e.pointerType === 'mouse') return;
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

  /* Where a picture of w×h lands, `contain`, inside a box. */
  function fit(box, w, h) {
    var s = Math.min(box.width / w, box.height / h);
    var fw = w * s, fh = h * s;
    return { left: box.left + (box.width - fw) / 2, top: box.top + (box.height - fh) / 2, width: fw, height: fh };
  }

  /* Tab stays inside an open dialog: a native modal lets it out to the
     browser's own toolbar, which on a phone is a dead end */
  function trap(dlg) {
    on(dlg, 'keydown', function (e) {
      if (e.key !== 'Tab') return;
      var f = $$('button, [href], [tabindex="0"], input, select, textarea', dlg).filter(function (el) {
        return !el.disabled && el.getClientRects().length && getComputedStyle(el).visibility !== 'hidden';
      });
      if (!f.length) return;
      var i = f.indexOf(d.activeElement);
      e.preventDefault();
      f[(i + (e.shiftKey ? -1 : 1) + f.length) % f.length].focus();
    });
  }

  /* A modal's history entry, so the phone's Back closes it, not the page. */
  function backCloses(isOpen, close) {
    var pushed = false;
    on(window, 'popstate', function () { if (isOpen()) { pushed = false; close(true); } });
    return {
      push: function () { try { history.pushState({ msModal: 1 }, ''); pushed = true; } catch (e) { pushed = false; } },
      pop: function () { if (pushed) { pushed = false; if (history.state && history.state.msModal) history.back(); } }
    };
  }

  /* ================================================================
     The photograph, lifted out of the page (.zm).
     It flies from its place to the largest whole size the window allows
     (FLIP: laid out at the end, drawn at the start by one transform, let go),
     and back to its exact place on close. The page's own picture is hidden
     meanwhile, so it is one photograph moving, not two.
     ================================================================ */
  var Zoom = (function () {
    var dlg = $('#pr-zoom');
    if (!dlg) return null;
    var img = $('.zm__img', dlg), by = $('.zm__by', dlg), x = $('.zm__x', dlg);
    var dim = d.createElement('div');
    dim.className = 'zm__dim'; dim.setAttribute('aria-hidden', 'true');
    dlg.insertBefore(dim, dlg.firstChild);
    var src = null, rec = null, busy = false, opener = null;
    var MS = 560;

    function srcRect() {
      var pic = src.querySelector('img');
      return fit(pic.getBoundingClientRect(), rec.w, rec.h);
    }
    function target() {
      var D = dlg.getBoundingClientRect(), wide = D.width >= 768;
      var side = wide ? 96 : 16, top = wide ? 96 : 84, bottom = wide ? 88 : 72;
      var f = fit({ left: side, top: top, width: D.width - side * 2, height: D.height - top - bottom }, rec.w, rec.h);
      return { f: f, D: D };
    }
    function from(s, t) {
      return 'translate(' + (s.left - t.D.left - t.f.left) + 'px,' + (s.top - t.D.top - t.f.top) + 'px) scale(' + (s.width / t.f.width) + ')';
    }
    var hist = backCloses(function () { return dlg.open; }, function () { close(); });

    function open(button, r) {
      if (busy || dlg.open) return;
      src = button; rec = r; opener = button;
      var pic = button.querySelector('img');
      by.textContent = r.caption;
      img.removeAttribute('srcset');
      img.alt = r.caption;
      img.style.backgroundImage = 'url("' + (pic.currentSrc || pic.src) + '")';
      modal(true);
      dlg.showModal();
      var t = target();
      img.style.left = t.f.left + 'px'; img.style.top = t.f.top + 'px';
      img.style.width = t.f.width + 'px'; img.style.height = t.f.height + 'px';
      if (r.srcset) { img.sizes = Math.ceil(t.f.width) + 'px'; img.srcset = r.srcset; }
      img.src = r.src;
      var s = srcRect();
      src.classList.add('is-out');
      busy = true;
      var a = anim(img, [{ transform: from(s, t) }, { transform: 'none' }], MS);
      anim(dim, [{ opacity: 0 }, { opacity: 1 }], MS);
      anim(by, [{ opacity: 0 }, { opacity: 1 }], 320, EASE, MS * 0.55);
      anim(x, [{ opacity: 0 }, { opacity: 1 }], 320, EASE, MS * 0.4);
      done(a, function () { busy = false; });
      x.focus({ preventScroll: true });
      hist.push();
    }
    function close(fromBack) {
      if (!dlg.open || busy) return;
      busy = true;
      if (!fromBack) hist.pop();
      var t = target(), s = srcRect();
      var a = anim(img, [{ transform: 'none' }, { transform: from(s, t) }], MS * 0.85);
      anim(dim, [{ opacity: 1 }, { opacity: 0 }], MS * 0.85);
      anim(by, [{ opacity: 1 }, { opacity: 0 }], 160);
      anim(x, [{ opacity: 1 }, { opacity: 0 }], 160);
      done(a, function () {
        src.classList.remove('is-out');
        dlg.close();
        img.getAnimations && img.getAnimations().forEach(function (k) { k.cancel(); });
        [dim, by, x].forEach(function (el) { el.getAnimations && el.getAnimations().forEach(function (k) { k.cancel(); }); });
        busy = false;
        modal(false);
        if (opener) opener.focus({ preventScroll: true });
      });
    }
    trap(dlg);
    on(x, 'click', function () { close(); });
    on(dlg, 'cancel', function (e) { e.preventDefault(); close(); });
    on(dlg, 'click', function (e) { if (e.target === dlg || e.target === dim || e.target === img) close(); });
    return { open: open, close: close };
  })();

  /* ================================================================ 4 · Pares */
  $$('[data-pairs]').forEach(function (box) {
    var row = $('[data-pr-row]', box);
    var items = $$('.pr__i', row);
    var prev = $('[data-pr-step="-1"]', box), next = $('[data-pr-step="1"]', box);
    var MS = 520;

    /* the arrows: as many pairs as are in view; quiet at either end */
    function step() { var a = items[0], b = items[1]; return b ? b.offsetLeft - a.offsetLeft : row.clientWidth; }
    function inView() { return Math.max(1, Math.round((row.clientWidth - 40) / step())); }
    function ends() {
      var max = row.scrollWidth - row.clientWidth;
      prev.setAttribute('aria-disabled', row.scrollLeft <= 2 ? 'true' : 'false');
      next.setAttribute('aria-disabled', row.scrollLeft >= max - 2 ? 'true' : 'false');
    }
    function go(dir) {
      var k = Math.round(row.scrollLeft / step()) + dir * inView();
      row.scrollTo({ left: Math.max(0, k * step()), behavior: still() ? 'auto' : 'smooth' });
    }
    on(prev, 'click', function () { go(-1); });
    on(next, 'click', function () { go(1); });
    on(row, 'scroll', ends, { passive: true });
    on(window, 'resize', ends);

    /* the row's height: the tallest pair in view, plus the row's own padding
       (room for the shadows). Measured on the pair's content, so a card that
       came to the front, or a new width, is followed. */
    var fitT = null;
    function fitH() {
      var R = row.getBoundingClientRect(), cs = getComputedStyle(row), max = 0;
      items.forEach(function (li) {
        var r = li.getBoundingClientRect();
        var seen = Math.min(r.right, R.right) - Math.max(r.left, R.left);
        if (seen > r.width * 0.5) max = Math.max(max, li.firstElementChild.getBoundingClientRect().height);
      });
      if (max) row.style.height = Math.ceil(max + parseFloat(cs.paddingTop) + parseFloat(cs.paddingBottom)) + 'px';
    }
    var fitSoon = function () { clearTimeout(fitT); fitT = setTimeout(fitH, 90); };
    on(row, 'scroll', fitSoon, { passive: true });
    on(window, 'resize', fitSoon);
    if ('ResizeObserver' in window) { var ro = new ResizeObserver(fitSoon); items.forEach(function (li) { ro.observe(li.firstElementChild); }); }
    fitH();
    ends();

    /* a mouse drags the row like a thumb does; let go, it settles on a pair */
    var drag = null, dragged = false;
    on(row, 'pointerdown', function (e) {
      if (e.pointerType !== 'mouse' || e.button !== 0) return;
      drag = { x: e.clientX, left: row.scrollLeft, id: e.pointerId }; dragged = false;
    });
    on(row, 'pointermove', function (e) {
      if (!drag || e.pointerId !== drag.id) return;
      var dx = e.clientX - drag.x;
      if (!dragged && Math.abs(dx) < 6) return;
      if (!dragged) { dragged = true; row.classList.add('is-drag'); try { row.setPointerCapture(drag.id); } catch (x) {} }
      row.scrollLeft = drag.left - dx;
    });
    var endDrag = function () {
      if (!drag) return;
      drag = null;
      if (!dragged) return;
      var k = Math.round(row.scrollLeft / step()), from = row.scrollLeft;
      row.classList.remove('is-drag');
      row.scrollLeft = from;   // switching snap back on must not jump
      row.scrollTo({ left: k * step(), behavior: still() ? 'auto' : 'smooth' });
    };
    on(row, 'pointerup', endDrag);
    on(row, 'pointercancel', endDrag);
    on(row, 'click', function (e) { if (dragged) { e.stopPropagation(); e.preventDefault(); dragged = false; } }, true);
    on(row, 'dragstart', function (e) { e.preventDefault(); });

    /* the words come to the front, and go back */
    function swap(txt, front) {
      if (txt.classList.contains('is-front') === front || txt._busy) return;
      var ph = txt.parentNode.querySelector('.pr__ph');
      var btn = $('.pr__swap', txt), full = $('.pr__full', txt);
      var more = function () { full.classList.toggle('is-more', full.scrollTop + full.clientHeight < full.scrollHeight - 4); };
      if (!full._m) { full._m = 1; on(full, 'scroll', more, { passive: true }); }
      txt._busy = true;
      var lifted = 'translate(0,-6px)';
      var a = anim(txt, front
        ? [{ transform: 'none' }, { transform: 'translate(12px,14px) scale(.985)', offset: .38 }, { transform: lifted }]
        : [{ transform: lifted }, { transform: 'translate(12px,14px) scale(.985)', offset: .38 }, { transform: 'none' }], MS);
      anim(ph, [{ transform: 'none' }, { transform: 'translate(-8px,-10px) scale(1.01)', offset: .38 }, { transform: 'none' }], MS);
      // the change of layer happens where the two are furthest apart
      later(txt, MS * 0.38, function () {
        txt.classList.toggle('is-front', front);
        full.scrollTop = 0;
        more();
      });
      btn.setAttribute('aria-pressed', front ? 'true' : 'false');
      full.tabIndex = front ? 0 : -1;
      done(a, function () {
        txt._busy = false;
        if (txt.getAnimations) txt.getAnimations().forEach(function (k) { k.cancel(); });
        if (ph.getAnimations) ph.getAnimations().forEach(function (k) { k.cancel(); });
      });
    }
    $$('.pr__txt', row).forEach(function (txt) {
      on(txt, 'click', function () {
        var front = !txt.classList.contains('is-front');
        if (front) $$('.pr__txt.is-front', row).forEach(function (o) { if (o !== txt) swap(o, false); });
        swap(txt, front);
      });
      on(txt, 'keydown', function (e) {
        if (e.key === 'Escape' && txt.classList.contains('is-front')) { e.preventDefault(); swap(txt, false); $('.pr__swap', txt).focus(); }
      });
    });
    $$('.pr__ph', row).forEach(function (ph) {
      on(ph, 'click', function () {
        var txt = ph.parentNode.querySelector('.pr__txt');
        if (txt.classList.contains('is-front')) return swap(txt, false);
        var r = DATA[ph.closest('.pr__i').getAttribute('data-id')];
        if (Zoom && r) Zoom.open(ph, r);
      });
    });
  });

  /* ================================================================
     One review whole (.vw): opened by any [data-open="id"].
     ================================================================ */
  var Viewer = (function () {
    var dlg = $('#ms-vw');
    if (!dlg || !ORDER.length) return null;
    var img = $('.vw__ph img', dlg), q = $('.vw__q', dlg), by = $('.vw__by', dlg), txt = $('.vw__txt', dlg);
    var opener = null;
    var hist = backCloses(function () { return dlg.open; }, function () { dlg.close(); });
    trap(dlg);
    function open(id, from) {
      var r = DATA[id];
      if (!r) return;
      opener = from || d.activeElement;
      img.alt = r.caption; img.width = r.w; img.height = r.h;
      img.removeAttribute('srcset');
      img.style.background = r.lqip ? 'url("' + r.lqip + '") center/contain no-repeat' : '';
      if (r.srcset) { img.sizes = '(min-width:900px) 560px, 100vw'; img.srcset = r.srcset; }
      img.src = r.src;
      q.textContent = '';
      r.paras.forEach(function (p) { var el = d.createElement('p'); el.textContent = p; q.appendChild(el); });
      by.textContent = r.caption;
      txt.scrollTop = 0;
      modal(true);
      dlg.showModal();
      $('.vw__x', dlg).focus({ preventScroll: true });
      hist.push();
    }
    on($('.vw__x', dlg), 'click', function () { dlg.close(); });
    on(dlg, 'click', function (e) { if (e.target === dlg) dlg.close(); });
    on(dlg, 'close', function () {
      hist.pop();
      modal(false);
      if (opener && opener.focus) opener.focus({ preventScroll: true });
    });
    on(d, 'click', function (e) {
      var t = e.target.closest && e.target.closest('[data-open]');
      if (!t || dlg.contains(t)) return;
      e.preventDefault();
      open(t.getAttribute('data-open'), t);
    });
    return { open: open };
  })();

  /* A stage held while the page scrolls past it: which beat is on, and how
     far through the whole sequence (0..1). The window is the scroller — in
     the showroom too: a pinned proposal gets a frame that scrolls itself. */
  function progress(sec) {
    var r = sec.getBoundingClientRect(), vh = window.innerHeight;
    var run = Math.max(1, r.height - vh);
    return Math.min(1, Math.max(0, -r.top / run));
  }

  /* ================================================================ 1 · La entrega */
  $$('[data-entrega]').forEach(function (sec) {
    var beats = $$('.en__b', sec), bar = $('.en__bar i', sec);
    var n = beats.length, cur = 0;
    var live = function () { return !still() && 'IntersectionObserver' in window; };

    /* the type steps down (and then the photograph) until a beat fits its screen */
    var STEPS = [1, .92, .85, .78, .72];
    function fitAll() {
      if (!sec.classList.contains('is-live')) return;
      beats.forEach(function (b) {
        var inn = $('.en__in', b), k = 0;
        b.classList.remove('is-tight');
        inn.style.setProperty('--s', 1);
        // centred in a flex column, an overflow spills both ways and
        // scrollHeight sees only half of it: measure the content itself
        var over = function () {
          var cs = getComputedStyle(inn), a = inn.firstElementChild.getBoundingClientRect(), z = inn.lastElementChild.getBoundingClientRect();
          return (z.bottom - a.top) - (inn.clientHeight - parseFloat(cs.paddingTop) - parseFloat(cs.paddingBottom));
        };
        while (over() > 0 && k < STEPS.length - 1) {
          k++; inn.style.setProperty('--s', STEPS[k]);
          if (k === 3) b.classList.add('is-tight');
        }
      });
    }
    function show(k) {
      if (k === cur) return;
      beats[cur].classList.remove('is-on');
      beats[k].classList.add('is-on');
      cur = k;
    }
    var ticking = false;
    function frame() {
      ticking = false;
      var p = progress(sec);
      if (bar) bar.style.transform = 'scaleX(' + p.toFixed(4) + ')';
      show(Math.min(n - 1, Math.floor(p * n * 0.9999)));
    }
    function tick() { if (!ticking) { ticking = true; requestAnimationFrame(frame); } }
    function setup() {
      sec.classList.toggle('is-live', live());
      if (!live()) { beats.forEach(function (b) { b.classList.add('is-on'); }); return; }
      beats.forEach(function (b, i) { b.classList.toggle('is-on', i === cur); });
      fitAll(); frame();
    }
    setup();
    if (d.fonts && d.fonts.ready) d.fonts.ready.then(fitAll);
    on(window, 'scroll', tick, { passive: true });
    var w = window.innerWidth, h = window.innerHeight, t;
    on(window, 'resize', function () {
      if (window.innerWidth === w && Math.abs(window.innerHeight - h) < 120) return;   // a phone's toolbar
      w = window.innerWidth; h = window.innerHeight; clearTimeout(t); t = setTimeout(function () { fitAll(); frame(); }, 120);
    });
    RM.addEventListener && RM.addEventListener('change', setup);
  });
  $$('[data-en-all]').forEach(function (b) {
    on(b, 'click', function () {
      var l = d.getElementById(b.getAttribute('aria-controls'));
      var open = b.getAttribute('aria-expanded') !== 'true';
      l.classList.toggle('is-open', open);
      b.setAttribute('aria-expanded', open ? 'true' : 'false');
      b.textContent = open ? 'Ver menos' : 'Ver todas las opiniones';
    });
  });

  /* ================================================================ 2 · El muro */
  $$('[data-muro]').forEach(function (box) {
    var items = $$('.mu__i', box), feats = $$('.mu__f', box);
    var ids = items.map(function (li) { return li.getAttribute('data-id'); });
    var lit = 0, timer = null, pausedUntil = 0, halted = false, inView = false;
    var wall = $('.mu__wall', box);

    /* ---- the rows: CSS alone justifies every row but the last, which it
       leaves short and ragged. Here the photographs are split into rows that
       all come out as close as possible to the target height, the last one
       included (least squares over every split, O(n²): 27 photographs is
       nothing, 300 would still be nothing). Widths are the ratio times the
       row's height, so nothing is cut and every row is flush both sides. */
    function rows() {
      var W = wall.clientWidth;
      if (!W) return;
      var cs = getComputedStyle(box);
      var rh = parseFloat(cs.getPropertyValue('--rh')) * (cs.getPropertyValue('--rh').indexOf('rem') > -1 ? parseFloat(getComputedStyle(root).fontSize) : 1);
      var gap = parseFloat(getComputedStyle(wall).columnGap) || 0;
      var r = items.map(function (li) { return parseFloat(li.style.getPropertyValue('--r')) || 0.75; });
      var n = r.length, best = [0], cut = [0];
      for (var j = 1; j <= n; j++) {
        best[j] = Infinity;
        var sum = 0;
        for (var i = j - 1; i >= 0; i--) {
          sum += r[i];
          var h = (W - gap * (j - i - 1)) / sum;
          if (h < rh * 0.55 && j - i > 1) break;
          var c = best[i] + Math.pow(h - rh, 2) * (h > rh * 1.6 ? 10 : 1);
          if (c < best[j]) { best[j] = c; cut[j] = i; }
        }
      }
      var spans = [], j2 = n;
      while (j2 > 0) { spans.unshift([cut[j2], j2]); j2 = cut[j2]; }
      // placed, not wrapped: a flex line breaks on a 1/64px rounding error
      var y = 0;
      spans.forEach(function (sp) {
        var sum = 0, k, x = 0;
        for (k = sp[0]; k < sp[1]; k++) sum += r[k];
        var h = (W - gap * (sp[1] - sp[0] - 1)) / sum;
        for (k = sp[0]; k < sp[1]; k++) {
          var st = items[k].style;
          st.left = x + 'px'; st.top = y + 'px';
          st.width = (k === sp[1] - 1 ? W - x : r[k] * h) + 'px';
          st.height = h + 'px';
          x += r[k] * h + gap;
        }
        y += h + gap;
      });
      wall.style.height = Math.max(0, y - gap) + 'px';
      wall.classList.add('is-rows');
    }
    rows();
    if ('ResizeObserver' in window) new ResizeObserver(function () { rows(); }).observe(wall);
    var start = ids.indexOf(box.getAttribute('data-start'));
    var IDLE = 6000, RESUME = 10000;

    /* ---- the idle light: the next photograph brightens, its words come forward */
    function light(k) {
      items[lit].classList.remove('is-lit');
      feats[lit].classList.remove('is-on');
      lit = (k + items.length) % items.length;
      items[lit].classList.add('is-lit');
      feats[lit].classList.add('is-on');
    }
    function tick() {
      timer = null;
      if (!halted && inView && !still() && Date.now() >= pausedUntil && !fo.open) light(lit + 1);
      schedule();
    }
    function schedule() { clearTimeout(timer); if (!halted) timer = setTimeout(tick, IDLE); }
    function pause() { pausedUntil = Date.now() + RESUME; }
    ['pointerdown', 'pointermove', 'focusin', 'keydown'].forEach(function (ev) { on(box, ev, pause, { passive: true }); });
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (es) { inView = es[0].isIntersecting; }, { threshold: 0.35 }).observe(box);
    }
    var halt = $('[data-mu-halt]', box);
    on(halt, 'click', function () {
      halted = !halted;
      halt.setAttribute('aria-pressed', halted ? 'true' : 'false');
      halt.textContent = halted ? 'Reanudar el movimiento' : 'Detener el movimiento';
      schedule();
    });
    if (start > 0) light(start);
    schedule();

    /* ---- one brought forward ---- */
    var fo = $('#mu-fo');
    var img = $('.fo__img', fo), area = $('.fo__area', fo), dim = $('.fo__dim', fo), txt = $('.fo__txt', fo),
        q = $('.fo__q', fo), by = $('.fo__by', fo), live = $('[data-fo-live]', fo);
    var chrome = [txt, $('.fo__nav', fo), $('.fo__x', fo)];
    var cur = -1, busy = false, opener = null, MS = 600;
    var hist = backCloses(function () { return fo.open; }, function () { close(true); });
    trap(fo);

    function thumb(k) { return items[k].querySelector('.mu__t'); }
    function spot() {                      // where the photograph stands, in the dialog's own frame
      var D = fo.getBoundingClientRect(), A = area.getBoundingClientRect(), r = DATA[ids[cur]];
      var f = fit({ left: A.left - D.left, top: A.top - D.top, width: A.width, height: A.height }, r.w, r.h);
      return { f: f, D: D };
    }
    function place(r) {
      var t = spot();
      img.style.left = t.f.left + 'px'; img.style.top = t.f.top + 'px';
      img.style.width = t.f.width + 'px'; img.style.height = t.f.height + 'px';
      return t;
    }
    function from(k, t) {
      var s = thumb(k).getBoundingClientRect();
      return 'translate(' + (s.left - t.D.left - t.f.left) + 'px,' + (s.top - t.D.top - t.f.top) + 'px) scale(' + (s.width / t.f.width) + ')';
    }
    function fill(k) {
      cur = (k + items.length) % items.length;
      var r = DATA[ids[cur]], th = thumb(cur).querySelector('img');
      img.alt = r.caption;
      img.removeAttribute('srcset');
      img.style.backgroundImage = 'url("' + (th.currentSrc || th.src) + '")';
      q.textContent = '';
      r.paras.forEach(function (p) { var el = d.createElement('p'); el.textContent = p; q.appendChild(el); });
      by.textContent = r.caption;
      txt.scrollTop = 0;
      var t = place(r);
      if (r.srcset) { img.sizes = Math.ceil(t.f.width) + 'px'; img.srcset = r.srcset; }
      img.src = r.src;
      items.forEach(function (li, i) { li.querySelector('.mu__t').classList.toggle('is-out', i === cur); });
      return t;
    }
    function open(k, from_) {
      if (busy || fo.open) return;
      opener = from_ || thumb(k);
      modal(true);
      fo.showModal();
      var t = fill(k);
      busy = true;
      var a = anim(img, [{ transform: from(k, t) }, { transform: 'none' }], MS);
      anim(dim, [{ opacity: 0 }, { opacity: 1 }], MS);
      chrome.forEach(function (el) { anim(el, [{ opacity: 0 }, { opacity: 1 }], 420, EASE, MS * 0.5); });
      done(a, function () { busy = false; });
      $('.fo__x', fo).focus({ preventScroll: true });
      hist.push();
      light(k);
    }
    function step(dir) {
      if (busy) return;
      busy = true;
      var a = anim(img, [{ opacity: 1 }, { opacity: 0 }], 200, EASE_IO);
      anim(txt, [{ opacity: 1 }, { opacity: 0 }], 200, EASE_IO);
      done(a, function () {
        fill(cur + dir);
        live.textContent = DATA[ids[cur]].caption;
        light(cur);
        var b = anim(img, [{ opacity: 0, transform: 'scale(1.02)' }, { opacity: 1, transform: 'none' }], 520);
        anim(txt, [{ opacity: 0 }, { opacity: 1 }], 520, EASE, 80);
        done(b, function () { busy = false; });
      });
    }
    function close(fromBack) {
      if (!fo.open || busy) return;
      busy = true;
      if (!fromBack) hist.pop();
      var t = spot();
      var a = anim(img, [{ transform: 'none', opacity: 1 }, { transform: from(cur, t), opacity: 1 }], MS * 0.85);
      anim(dim, [{ opacity: 1 }, { opacity: 0 }], MS * 0.85);
      chrome.forEach(function (el) { anim(el, [{ opacity: 1 }, { opacity: 0 }], 180); });
      done(a, function () {
        items.forEach(function (li) { li.querySelector('.mu__t').classList.remove('is-out'); });
        fo.close();
        [img, dim].concat(chrome).forEach(function (el) { el.getAnimations && el.getAnimations().forEach(function (x) { x.cancel(); }); });
        busy = false;
        modal(false);
        var back = thumb(cur);
        (opener && box.contains(opener) && opener.closest('.mu__i') === items[cur] ? opener : back).focus({ preventScroll: true });
        pause();
      });
    }
    on(box, 'click', function (e) {
      var t = e.target.closest('[data-mu-open]');
      if (!t) return;
      var k = ids.indexOf(t.getAttribute('data-mu-open'));
      if (k > -1) open(k, t);
    });
    on(fo, 'click', function (e) {
      var b = e.target.closest('[data-fo]');
      if (b) { var a = b.getAttribute('data-fo'); if (a === 'x') close(); else step(+a); return; }
      if (e.target === dim || e.target === fo) close();
    });
    on(fo, 'cancel', function (e) { e.preventDefault(); close(); });
    on(fo, 'keydown', function (e) {
      if (e.key === 'ArrowRight') { step(1); e.preventDefault(); }
      else if (e.key === 'ArrowLeft') { step(-1); e.preventDefault(); }
    });
    swipe(fo, function (dir) { step(dir); });
    on(window, 'resize', function () { if (fo.open && !busy) place(); });
  });

  /* ================================================================ 3 · Sus palabras */
  $$('[data-palabras]').forEach(function (sec) {
    var beats = $$('.pl__b', sec), n = beats.length, cur = 0;
    var texts = beats.map(function (b) { return $('.pl__p', b).textContent; });
    var lines = [];                        // per beat: its line spans
    var t0 = performance.now(), INTRO = 1400;   // the first phrase lights by itself on arrival
    var live = function () { return !still() && 'IntersectionObserver' in window; };

    /* the phrase as large as its space allows (no larger than the cap), then
       cut into the lines the browser chose, so each can be lit on its own */
    function setLines(b, k) {
      var p = $('.pl__p', b), q = $('.pl__q', b);
      p.textContent = texts[k];
      var wide = window.innerWidth >= 900;
      var cap = Math.min(wide ? 112 : 84, window.innerWidth * (wide ? 0.085 : 0.15));
      var lo = 28, hi = Math.round(cap), best = lo;
      var room = q.clientHeight, W = q.clientWidth;
      while (lo <= hi) {
        var mid = (lo + hi) >> 1;
        p.style.setProperty('--fs', mid + 'px');
        if (p.scrollHeight <= room && p.scrollWidth <= W + 1) { best = mid; lo = mid + 1; } else hi = mid - 1;
      }
      p.style.setProperty('--fs', best + 'px');
      // words → lines by where they land
      var words = texts[k].split(' ');
      p.textContent = '';
      var spans = words.map(function (w, i) {
        var sp = d.createElement('span'); sp.textContent = w + (i < words.length - 1 ? ' ' : '');
        p.appendChild(sp); return sp;
      });
      var groups = [], top = null;
      spans.forEach(function (sp) {
        if (top === null || Math.abs(sp.offsetTop - top) > 4) { groups.push([]); top = sp.offsetTop; }
        groups[groups.length - 1].push(sp.textContent);
      });
      p.textContent = '';
      lines[k] = groups.map(function (g) {
        var l = d.createElement('span'); l.className = 'pl__l'; l.textContent = g.join('');
        p.appendChild(l); return l;
      });
    }
    function layout() {
      if (!sec.classList.contains('is-live')) return;
      beats.forEach(function (b, k) {
        var was = b.style.visibility;
        setLines(b, k);
      });
      frame();
    }
    function show(k) {
      if (k === cur) return;
      beats[cur].classList.remove('is-on');
      beats[k].classList.add('is-on');
      cur = k;
    }
    var ticking = false;
    function frame() {
      ticking = false;
      var p = progress(sec), x = p * n, k = Math.min(n - 1, Math.floor(x * 0.9999)), u = Math.min(1, x - k);
      show(k);
      // lines light one after another over the first 45% of the phrase's stretch;
      // then it holds, whole, with its photograph, for the rest
      var L = lines[k] ? lines[k].length : 0, intro = k === 0 ? Math.min(1, (performance.now() - t0) / INTRO) : 0;
      var lit = Math.max(u / 0.45, intro);
      for (var i = 0; i < L; i++) {
        var a = Math.min(1, Math.max(0, lit * L - i));
        lines[k][i].style.opacity = (0.18 + 0.82 * a).toFixed(3);
      }
      beats[k].classList.toggle('is-done', lit >= 0.98);
      if (k === 0 && intro < 1) tick();
    }
    function tick() { if (!ticking) { ticking = true; requestAnimationFrame(frame); } }
    function setup() {
      var L = live();
      sec.classList.toggle('is-live', L);
      if (!L) {
        beats.forEach(function (b, k) { b.classList.add('is-on', 'is-done'); $('.pl__p', b).textContent = texts[k]; $('.pl__p', b).style.removeProperty('--fs'); });
        lines = [];
        return;
      }
      beats.forEach(function (b, k) { b.classList.toggle('is-on', k === cur); });
      layout();
    }
    setup();
    if (d.fonts && d.fonts.ready) d.fonts.ready.then(function () { t0 = performance.now(); layout(); tick(); });
    on(window, 'scroll', tick, { passive: true });
    var w = window.innerWidth, h = window.innerHeight, tm;
    on(window, 'resize', function () {
      if (window.innerWidth === w && Math.abs(window.innerHeight - h) < 120) return;
      w = window.innerWidth; h = window.innerHeight; clearTimeout(tm); tm = setTimeout(layout, 120);
    });
    RM.addEventListener && RM.addEventListener('change', setup);
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
        if (f.hasAttribute('data-pinned')) return;
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
      var opt = sel.options[sel.selectedIndex], pin = opt.getAttribute('data-pinned') === '1';
      f.src = '/muestras/opiniones/' + encodeURIComponent(sel.value) + '?embed=' + (pin ? 2 : 1);
      f.title = 'Comparar: ' + opt.text;
      // a pinned sequence scrolls inside a phone's screen; the others take their height
      f.style.height = pin ? Math.min(844, window.innerHeight - 112) + 'px' : '';
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
      // a pinned sequence: a real screen's height (the CSS gives the bare one)
      if (frame.hasAttribute('data-pinned')) {
        frame.style.height = bare ? '' : Math.min(mode === '390' ? 844 : 1024, window.innerHeight - 112) + 'px';
      }
    }
    on(window, 'resize', layout);
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
