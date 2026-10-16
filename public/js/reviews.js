/* The home page's reviews (#reviews): 4.6 "La fila" from the showroom (tag
   opiniones-v4.6) as a site component (partials/reviews.blade.php,
   public/css/reviews.css, App\Support\ReviewRow). Without this file the row
   still scrolls natively and snaps pair by pair.
   Everything that moves is transform or opacity (Web Animations) or the
   browser's own scrolling, so it runs on the compositor. Nothing moves on its
   own. Under reduced motion every change is instant. */
(function () {
  'use strict';
  var d = document, root = d.documentElement;
  var RM = window.matchMedia('(prefers-reduced-motion: reduce)');
  var $ = function (s, c) { return (c || d).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || d).querySelectorAll(s)); };
  var on = function (el, ev, fn, o) { el.addEventListener(ev, fn, o || false); };
  var still = function () { return RM.matches; };
  var EASE = 'cubic-bezier(.22,.61,.36,1)';          // --e-out
  var SPRING = 'cubic-bezier(.2,.9,.25,1)';
  var anim = function (el, kf, ms, ease, delay) {
    if (still() || !el.animate) return null;
    return el.animate(kf, { duration: ms, easing: ease || EASE, delay: delay || 0, fill: 'both' });
  };
  /* every step that waits on a motion runs once, when the motion ends or, if the
     animation clock is held up (a background tab, a busy phone), shortly after
     it should have: a state never hangs on an animation's promise */
  var once = function (fn) { var did = false; return function () { if (!did) { did = true; fn(); } }; };
  var done = function (a, fn) {
    if (!a) return fn();
    var f = once(fn), t = 700;
    try { t = a.effect.getComputedTiming().endTime || t; } catch (e) {}
    a.finished.then(f, f); setTimeout(f, t + 150);
  };
  /* "in ms", on the animation clock, so a step that has to land at a point of
     a motion stays on it even when the motion is slowed */
  var later = function (el, ms, fn) {
    if (still() || !el.animate) return fn();
    var f = once(fn);
    el.animate([{ opacity: 1 }, { opacity: 1 }], { duration: ms, composite: 'add' }).finished.then(f, f);
    setTimeout(f, ms + 150);
  };

  var modal = function () {};

  var DATA = {};
  var dataEl = $('#rv-data');
  if (dataEl) JSON.parse(dataEl.textContent).forEach(function (r) { DATA[r.id] = r; });

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
     The whole review, with the whole photograph (.rv-ov). The panel takes
     the review's navy; the photograph keeps its own shape, sized in whole
     pixels to the room there is. */
  var Over = (function () {
    var dlg = $('#rv-ov');
    if (!dlg) return null;
    var dim = $('.rv-ov__dim', dlg), panel = $('.rv-ov__panel', dlg), box = $('.rv-ov__ph', dlg), img = $('img', box);
    var q = $('.rv-ov__q', dlg), by = $('.rv-ov__by', dlg), x = $('.rv-x', dlg), txt = $('.rv-ov__txt', dlg);
    var opener = null, busy = false, closing = false, rec = null;
    var hist = backCloses(function () { return dlg.open; }, function () { close(true); });

    function size() {
      if (!rec) return;
      var W = dlg.clientWidth, H = dlg.clientHeight, r = rec.w / rec.h, w, h;
      if (W >= 860) {
        var pw = Math.min(1080, W - 96);
        var mh = H - 96 - 64 - 16;
        h = Math.min(mh, (pw * .5 - 16) / r); w = h * r;
        panel.style.setProperty('--ph2', Math.round(h) + 16 + 64 + 'px');
      } else {
        w = Math.min(W - 40 - 16, (H * .46) * r); h = w / r;
        panel.style.removeProperty('--ph2');
      }
      box.style.setProperty('--ow', Math.round(w) + 'px');
      box.style.setProperty('--oh', Math.round(h) + 'px');
      if (rec.srcset) img.sizes = Math.ceil(w) + 'px';
    }
    function open(li, from) {
      if (busy || dlg.open) return;
      var r = DATA[li.getAttribute('data-id')];
      if (!r) return;
      rec = r; opener = from;
      dlg.style.setProperty('--card', getComputedStyle(li).getPropertyValue('--card'));
      q.textContent = '';
      r.paras.forEach(function (p) { var e = d.createElement('p'); e.textContent = p; q.appendChild(e); });
      by.textContent = r.name;
      var pic = $('.rv__img', li);
      img.removeAttribute('srcset');
      img.alt = r.caption;
      img.style.backgroundImage = pic && (pic.currentSrc || pic.src) ? 'url("' + (pic.currentSrc || pic.src) + '")' : '';
      modal(true);
      dlg.showModal();
      size();
      if (r.srcset) img.srcset = r.srcset;
      img.src = r.src;
      panel.scrollTop = 0; txt.scrollTop = 0;
      busy = true;
      anim(dim, [{ opacity: 0 }, { opacity: 1 }], 260);
      var a = anim(panel, [{ opacity: 0, transform: 'translate3d(0,18px,0) scale(.985)' }, { opacity: 1, transform: 'none' }], 460, SPRING);
      anim(x, [{ opacity: 0 }, { opacity: 1 }], 240, EASE, 160);
      done(a, function () { busy = false; });
      x.focus({ preventScroll: true });
      hist.push();
    }
    function close(fromBack) {
      if (!dlg.open || closing) return;
      // asked to close while still opening: land the opening at once, then close
      [dim, panel, x].forEach(function (el) { el.getAnimations && el.getAnimations().forEach(function (k) { k.finish(); }); });
      busy = closing = true;
      if (!fromBack) hist.pop();
      anim(dim, [{ opacity: 1 }, { opacity: 0 }], 220);
      anim(x, [{ opacity: 1 }, { opacity: 0 }], 120);
      var a = anim(panel, [{ opacity: 1, transform: 'none' }, { opacity: 0, transform: 'translate3d(0,12px,0) scale(.99)' }], 220, 'cubic-bezier(.4,0,1,1)');
      done(a, function () {
        dlg.close();
        [dim, panel, x].forEach(function (el) { el.getAnimations && el.getAnimations().forEach(function (k) { k.cancel(); }); });
        busy = closing = false; rec = null;
        modal(false);
        if (opener) opener.focus({ preventScroll: true });
      });
    }
    trap(dlg);
    on(x, 'click', function () { close(); });
    on(dlg, 'cancel', function (e) { e.preventDefault(); close(); });
    on(dlg, 'keydown', function (e) { if (e.key === 'Escape') { e.preventDefault(); close(); } });
    on(dlg, 'click', function (e) { if (e.target === dlg || e.target === dim) close(); });
    on(window, 'resize', function () { if (dlg.open) size(); });
    return { open: open, close: close };
  })();

  /* ================================================================ the row */
  var row = $('[data-rv-row]');
  if (!row) return;
  var sec = row.closest('.rv'), list = $('.rv__list', row), items = $$('.rv__i', list);
  var prev = $('[data-rv-step="-1"]', sec), next = $('[data-rv-step="1"]', sec);
  var bar = $('.rv__bar', sec), thumb = bar && $('i', bar);
  var G = { step: 1, side: 0, max: 0, page: 1, gap: 0, track: 0 };

  function measure() {
    var a = items[0], b = items[1];
    G.step = b ? b.offsetLeft - a.offsetLeft : a.offsetWidth;
    G.gap = G.step - a.offsetWidth;
    G.side = parseFloat(getComputedStyle(list).paddingLeft) || 0;
    G.max = Math.max(0, row.scrollWidth - row.clientWidth);
    // the pairs wholly in view at a snap point
    G.page = Math.max(1, Math.floor((row.clientWidth - G.side + G.gap) / G.step + .02));
    if (bar) {
      var tw = Math.max(24, Math.round(bar.clientWidth * row.clientWidth / Math.max(1, row.scrollWidth)));
      thumb.style.setProperty('--tw', tw + 'px');
      G.track = bar.clientWidth - tw;
    }
    paint();
  }
  /* the arrows' ends and the lit part of the hairline: read the position once
     per frame, write two properties, never lay anything out */
  var raf = 0;
  function paint() {
    raf = 0;
    var s = row.scrollLeft;
    prev.setAttribute('aria-disabled', s <= 2 ? 'true' : 'false');
    next.setAttribute('aria-disabled', s >= G.max - 2 ? 'true' : 'false');
    if (thumb) thumb.style.setProperty('--tx', (G.max ? Math.round(s / G.max * G.track) : 0) + 'px');
  }
  on(row, 'scroll', function () { if (!raf) raf = requestAnimationFrame(paint); }, { passive: true });

  /* ---- the arrows and the keys: a page at a time (the pairs wholly in view),
     to a snap point, by the browser's own smooth scroll ---- */
  var aim = null, aimT = 0;
  function to(left) {
    left = Math.max(0, Math.min(G.max, left));
    aim = left; clearTimeout(aimT); aimT = setTimeout(function () { aim = null; }, 900);
    row.scrollTo({ left: left, behavior: still() ? 'auto' : 'smooth' });
  }
  function go(dir) {
    var from = aim !== null ? aim : row.scrollLeft;
    var k = Math.round(from / G.step) + dir * G.page;
    var last = Math.ceil(G.max / G.step - .01);
    k = Math.max(0, Math.min(last, k));
    to(k >= last ? G.max : k * G.step);
  }
  on(prev, 'click', function () { go(-1); });
  on(next, 'click', function () { go(1); });
  on(sec, 'keydown', function (e) {
    if (e.target.closest && e.target.closest('dialog')) return;
    if (e.altKey || e.ctrlKey || e.metaKey) return;
    if (e.key === 'ArrowRight') { e.preventDefault(); go(1); }
    else if (e.key === 'ArrowLeft') { e.preventDefault(); go(-1); }
    else if (e.key === 'Home' && e.target === row) { e.preventDefault(); to(0); }
    else if (e.key === 'End' && e.target === row) { e.preventDefault(); to(G.max); }
  });

  /* ---- a mouse drags the row like a finger: it follows, and when let go it
     carries on by its speed to the nearest pair, smoothly ---- */
  var dr = null, dragged = false;
  function settle(fn) {
    var last = -1, same = 0, n = 0;
    (function tick() {
      var s = row.scrollLeft;
      same = Math.abs(s - last) < .5 ? same + 1 : 0; last = s;
      if (same >= 4 || ++n > 180) fn(); else requestAnimationFrame(tick);
    })();
  }
  on(row, 'pointerdown', function (e) {
    if (e.pointerType !== 'mouse' || e.button !== 0) return;
    dragged = false;
    dr = { x: e.clientX, s: row.scrollLeft, id: e.pointerId, on: false, pts: [] };
  });
  on(row, 'pointermove', function (e) {
    if (!dr || e.pointerId !== dr.id) return;
    var dx = e.clientX - dr.x;
    if (!dr.on) {
      if (Math.abs(dx) < 6) return;
      dr.on = true;
      row.classList.add('is-drag');
      try { row.setPointerCapture(e.pointerId); } catch (x) {}
    }
    dr.pts.push([e.timeStamp, e.clientX]);
    while (dr.pts.length > 2 && e.timeStamp - dr.pts[0][0] > 100) dr.pts.shift();
    row.scrollLeft = dr.s - dx;
  });
  function release(e) {
    if (!dr || (e && e.pointerId !== dr.id)) return;
    var d0 = dr; dr = null;
    if (!d0.on) return;
    dragged = true;
    var p = d0.pts, v = p.length > 1 ? (p[p.length - 1][1] - p[0][1]) / Math.max(1, p[p.length - 1][0] - p[0][0]) : 0;
    // the throw: where it would coast to (iOS's 0.8 x v over ~325 ms), to the nearest pair
    var proj = row.scrollLeft - v * 260;
    var k = Math.round(proj / G.step), k0 = Math.round(d0.s / G.step);
    k = Math.max(k0 - G.page * 2, Math.min(k0 + G.page * 2, k));
    var left = Math.max(0, Math.min(G.max, k * G.step));
    row.scrollTo({ left: left, behavior: still() ? 'auto' : 'smooth' });
    settle(function () { row.classList.remove('is-drag'); });
  }
  on(row, 'pointerup', release);
  on(row, 'pointercancel', release);
  on(row, 'lostpointercapture', release);
  on(row, 'click', function (e) { if (dragged) { e.stopPropagation(); e.preventDefault(); dragged = false; } }, true);
  on(row, 'dragstart', function (e) { e.preventDefault(); });

  /* ---- the photographs: asked for two screens ahead and decoded before they
     come into view, so a pair never arrives blank ---- */
  function warm(li) {
    var img = $('.rv__img', li);
    if (!img || img._w) return;
    img._w = 1;
    if (img.loading === 'lazy') img.loading = 'eager';
    if (img.decode) img.decode().catch(function () {});
  }
  // only once the section is within half a screen of the window (an observer
  // rooted on the row measures against the row alone, and would fetch them at
  // load); until then the browser's own lazy loading decides
  if ('IntersectionObserver' in window) {
    var near = new IntersectionObserver(function (es) {
      if (!es[0].isIntersecting) return;
      near.disconnect();
      sec.classList.add('is-near');
      var io = new IntersectionObserver(function (es2) {
        es2.forEach(function (en) { if (en.isIntersecting) { warm(en.target); io.unobserve(en.target); } });
      }, { root: row, rootMargin: '0px 100% 0px 100%' });
      items.forEach(function (li) { io.observe(li); });
    }, { rootMargin: '50% 0px 50% 0px' });
    near.observe(sec);
  } else sec.classList.add('is-near');

  /* ---- each quote: its box is always --L lines of the base size. A short
     review is set larger to fill it (L/3, L/4 ... of the base: the largest that
     holds it whole); a long one is clamped and shows "Ver más". The box never
     changes size. ---- */
  function fit() {
    var L = Math.round(parseFloat(getComputedStyle(items[0]).getPropertyValue('--L'))) || 5;
    items.forEach(function (li) {
      var q = $('.rv__qt', li); if (!q) return;
      var box = q.clientHeight, n = Math.ceil(L / 2), got = 0;
      q.classList.add('is-free');
      for (; n <= L && !got; n++) {
        q.style.setProperty('--s', L / n);
        if (q.scrollHeight <= box + 1) got = n;
      }
      q.classList.remove('is-free');
      q.style.setProperty('--s', got ? L / got : 1);
      q.style.setProperty('--ql', got || L);
      li.classList.toggle('is-long', !got);
    });
  }

  /* ---- the swap: the card behind lifts, swings out on a short arc and lands
     on top; the one in front sinks back to its place. 650 ms, transform only;
     the change of layer happens in mid-flight, where they are furthest apart */
  function label(pair, q) {
    var ph = $('.rv__ph', pair), sw = $('.rv__swap', pair);
    ph.setAttribute('aria-label', ph.getAttribute(q ? 'data-back' : 'data-front'));
    sw.setAttribute('aria-label', sw.getAttribute(q ? 'data-front' : 'data-back'));
    sw.setAttribute('aria-pressed', q ? 'true' : 'false');
    if (q) { sw.setAttribute('aria-haspopup', 'dialog'); ph.removeAttribute('aria-haspopup'); }
    else { sw.removeAttribute('aria-haspopup'); ph.setAttribute('aria-haspopup', 'dialog'); }
  }
  function swap(pair, toQuote) {
    if (pair.classList.contains('is-q') === toQuote || pair._busy) return;
    var ph = $('.rv__ph', pair), tx = $('.rv__q', pair);
    var rise = toQuote ? tx : ph, sink = toQuote ? ph : tx;
    var flip = function () { pair.classList.toggle('is-q', toQuote); };
    label(pair, toQuote);
    if (still() || !rise.animate) { flip(); return; }
    // out along the line from the other card's centre to this one's; 2D only
    // (translate and scale), so the cards stay flat layers on the compositor
    var a = rise.getBoundingClientRect(), b = sink.getBoundingClientRect();
    var vx = (a.left + a.width / 2) - (b.left + b.width / 2), vy = (a.top + a.height / 2) - (b.top + b.height / 2);
    var L = Math.hypot(vx, vy) || 1, D = Math.max(14, Math.min(28, pair.offsetWidth * .075));
    var ox = vx / L * D, oy = vy / L * D;
    var T = function (x, y, s) { return 'translate3d(' + x.toFixed(1) + 'px,' + y.toFixed(1) + 'px,0) scale(' + s + ')'; };
    var MS = 650;
    pair._busy = true;
    pair.classList.add('is-swap');
    rise.style.willChange = sink.style.willChange = 'transform';
    var ra = rise.animate([
      { transform: T(0, 0, 1), easing: 'cubic-bezier(.3,.7,.2,1)' },
      { transform: T(ox, oy, 1.035), offset: .42, easing: 'cubic-bezier(.45,0,.2,1)' },
      { transform: T(ox * .1, oy * .1, 1.01), offset: .74, easing: 'cubic-bezier(.4,0,.4,1)' },
      { transform: T(0, 0, .996), offset: .9, easing: 'ease-out' },
      { transform: T(0, 0, 1) }
    ], { duration: MS, fill: 'both' });
    sink.animate([
      { transform: T(0, 0, 1), easing: 'cubic-bezier(.3,.7,.2,1)' },
      { transform: T(-ox * .45, -oy * .45, .97), offset: .42, easing: SPRING },
      { transform: T(0, 0, 1) }
    ], { duration: MS, fill: 'both' });
    later(rise, MS * .44, flip);
    done(ra, function () {
      [rise, sink].forEach(function (el) { el.getAnimations().forEach(function (k) { k.cancel(); }); el.style.willChange = ''; });
      pair.classList.remove('is-swap');
      pair._busy = false;
    });
  }

  items.forEach(function (li) {
    var pair = $('.rv__pair', li);
    var more = $('.rv__more', li), sw = $('.rv__swap', li);
    more.removeAttribute('tabindex');
    on($('.rv__ph', li), 'click', function () {
      if (pair.classList.contains('is-q')) swap(pair, false);
      else if (Over) Over.open(li, this);
    });
    // a tap on the card (it lands on its words) or Enter on its button
    on($('.rv__q', li), 'click', function (e) {
      if (e.target.closest('.rv__more')) return;
      if (pair.classList.contains('is-q')) { if (Over) Over.open(li, sw); }
      else swap(pair, true);
    });
    on(more, 'click', function () { if (Over) Over.open(li, more); });
  });

  measure(); fit();
  var rt = 0;
  on(window, 'resize', function () { clearTimeout(rt); rt = setTimeout(function () { measure(); fit(); }, 120); });
  if (d.fonts && d.fonts.ready) d.fonts.ready.then(function () { measure(); fit(); });
})();
