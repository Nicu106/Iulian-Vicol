/* /muestras/opiniones/4.4 — Pares, editorial. A copy of the parts of
   muestras-opiniones.js that 4 (Pares) runs, so that this version can change
   without touching 4.0 or the file 1–3 share. html.js is set in the <head>
   and removed by any error here, which drops the page to its static form. */
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

})();
