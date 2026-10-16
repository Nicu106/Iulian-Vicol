/* /muestras/opiniones/4.5 — Pares, in the site's voice and in colour. Started as
   a copy of the parts of muestras-opiniones.js that 4 (Pares) runs, so that
   this version can change without touching 4.0 or the file 1–3 share. html.js is set in the <head>
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
  /* when an animation is over; once. With a timer as well as the promise:
     headless WebKit did not always keep an animation's finished promise, and a
     viewer or a card must never stay locked */
  var done = function (a, fn) {
    if (!a) return fn();
    var once = false, go = function () { if (!once) { once = true; fn(); } };
    a.finished.then(go, go);
    var t = a.effect && a.effect.getComputedTiming ? a.effect.getComputedTiming() : null;
    setTimeout(go, (t && isFinite(t.endTime) ? t.endTime : 1000) + 400);
  };
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
    var src = null, rec = null, busy = false, opening = false, opener = null;
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
      opening = true;
      var opened = function () { if (opening) { opening = false; busy = false; } };
      done(a, opened);
      setTimeout(opened, MS + 400);   // a finished promise WebKit never kept must not lock the viewer
      x.focus({ preventScroll: true });
      hist.push();
    }
    function close(fromBack) {
      if (!dlg.open) return;
      // closed while still opening: the opening ends where it was going, at once
      if (opening) { [img, dim, by, x].forEach(function (el) { el.getAnimations && el.getAnimations().forEach(function (k) { try { k.finish(); } catch (e) {} }); }); opening = false; busy = false; }
      if (busy) return;
      busy = true;
      if (!fromBack) hist.pop();
      var t = target(), s = srcRect();
      var a = anim(img, [{ transform: 'none' }, { transform: from(s, t) }], MS * 0.85);
      anim(dim, [{ opacity: 1 }, { opacity: 0 }], MS * 0.85);
      anim(by, [{ opacity: 1 }, { opacity: 0 }], 160);
      anim(x, [{ opacity: 1 }, { opacity: 0 }], 160);
      var shut = false;
      var closed = function () {
        if (shut) return; shut = true;
        src.classList.remove('is-out');
        dlg.close();
        img.getAnimations && img.getAnimations().forEach(function (k) { k.cancel(); });
        [dim, by, x].forEach(function (el) { el.getAnimations && el.getAnimations().forEach(function (k) { k.cancel(); }); });
        busy = false;
        modal(false);
        if (opener) opener.focus({ preventScroll: true });
      };
      done(a, closed);
      setTimeout(closed, MS + 400);
    }
    trap(dlg);
    on(x, 'click', function () { close(); });
    on(dlg, 'cancel', function (e) { e.preventDefault(); close(); });
    on(dlg, 'click', function (e) { if (e.target === dlg || e.target === dim || e.target === img) close(); });
    return { open: open, close: close };
  })();

  /* ================================================================ 4.5 · Pares
     One pair at a time on a fixed stage. Everything that moves is transform
     and opacity, through the Web Animations API, so it runs on the compositor
     and every motion can be read, slowed and measured. Under reduced motion
     every change is instant. */
  $$('[data-pairs]').forEach(function (box) {
    var sec = box.closest('.op-sec');
    var stage = $('[data-pr-stage]', box), list = $('.pr__list', box);
    var items = $$('.pr__i', list), N = items.length;
    var prev = $('[data-pr-step="-1"]', box), next = $('[data-pr-step="1"]', box);
    var live = $('[data-pr-live]', box);
    var bgs = $$('.op-bg', sec), bgOn = 0;
    var cur = Math.max(0, items.findIndex(function (li) { return li.classList.contains('is-on'); }));
    var SPRING = 'cubic-bezier(.2,.9,.25,1)';
    var pending = null;            // the running slide change: its anims and its tidy-up

    var W = function () { return stage.clientWidth; };
    // the ground's slow drift holds while the cards move
    // (not merely paused: a paused compositor animation still cost a frame in two at
    // 1440, measured; so it is frozen where it is, taken off, and put back at that time)
    var moving = 0, frozen = null;
    var hold = function (on) {
      var was = moving > 0;
      moving = Math.max(0, moving + (on ? 1 : -1));
      if (moving > 0 === was) return;
      var lt = $('.op-bg.is-on .op-bg__light', sec);
      if (!lt || still()) return;
      if (moving > 0) {
        var a = lt.getAnimations()[0];
        frozen = { el: lt, t: a ? a.currentTime : 0 };
        lt.style.transform = getComputedStyle(lt).transform;
        lt.style.animation = 'none';
      } else if (frozen) {
        var f = frozen; frozen = null;
        f.el.style.animation = ''; f.el.style.transform = '';
        var b = f.el.getAnimations()[0]; if (b && f.t != null) b.currentTime = f.t;
      }
    };
    function ends() {
      prev.setAttribute('aria-disabled', cur === 0 ? 'true' : 'false');
      next.setAttribute('aria-disabled', cur === N - 1 ? 'true' : 'false');
    }
    /* the light sits behind the pair: its centre, in the ground layer's own
       coordinates. The stage never changes size with a review, so this is
       measured on load and on resize only */
    function light() {
      var li = items[cur], ph = $('.pr__ph', li), tx = $('.pr__txt', li);
      var a = ph.getBoundingClientRect(), b = tx.getBoundingClientRect(), r = sec.getBoundingClientRect();
      var cx = (Math.min(a.left, b.left) + Math.max(a.right, b.right)) / 2, cy = (Math.min(a.top, b.top) + Math.max(a.bottom, b.bottom)) / 2;
      sec.style.setProperty('--gx', Math.round(cx - r.left) + 'px');
      sec.style.setProperty('--gy', Math.round(cy - r.top) + 'px');
    }
    /* the grounds: the review's tone on the layer in front; the tones either side
       painted ahead on the other two (paint() runs when nothing moves) */
    var tone = function (g) { return g.style.getPropertyValue('--field'); };
    function dress(g, li) {
      g.style.setProperty('--field', li.getAttribute('data-field'));
      g.style.setProperty('--glow', li.getAttribute('data-glow'));
      g.style.setProperty('--tint', li.getAttribute('data-tint'));
    }
    function colours(li) {
      var f = li.getAttribute('data-field');
      if (tone(bgs[bgOn]) === f) return;   // the same tone: nothing changes
      var k = bgs.findIndex(function (g, i) { return i !== bgOn && tone(g) === f; });
      if (k < 0) { k = (bgOn + 1) % bgs.length; dress(bgs[k], li); }
      bgs[k].classList.add('is-on'); bgs[bgOn].classList.remove('is-on');
      bgOn = k;
    }
    function paint() {
      var want = [items[cur - 1], items[cur + 1]].filter(Boolean).map(function (li) { return li; });
      want.forEach(function (li) {
        var f = li.getAttribute('data-field');
        if (bgs.some(function (g) { return tone(g) === f; })) return;
        var keep = [cur - 1, cur + 1].map(function (i) { return items[i] && items[i].getAttribute('data-field'); });
        var g = bgs.find(function (g, i) { return i !== bgOn && keep.indexOf(tone(g)) < 0; });
        if (g) dress(g, li);
      });
    }
    var idle = window.requestIdleCallback || function (fn) { return setTimeout(fn, 200); };
    /* the next pictures, asked for before they are needed, and decoded */
    function warm(k) {
      [k - 1, k, k + 1].forEach(function (i) {
        var img = items[i] && $('.pr__img', items[i]);
        if (img && img.loading === 'lazy') { img.loading = 'eager'; if (img.decode) img.decode().catch(function () {}); }
      });
    }
    function say(li) {
      var by = $('.pr__by', li), q = $('.pr__qt', li);
      if (live) live.textContent = (by ? by.textContent + ': ' : '') + (q ? q.textContent : '');
    }
    /* each pull-quote: the largest of three sizes that holds it whole in its
       fixed box (x 2 in two lines, x 4/3 in three, x 1 in four); if even four
       lines cut it, it is clamped and "Ver más" shows (as it does when the
       pull-quote is only the opening of the review: .is-cut from the server).
       The box and the "Ver más" line never change size, so nothing moves. */
    function fit() {
      var L = Math.round(parseFloat(getComputedStyle(list).getPropertyValue('--lines'))) || 4;
      items.forEach(function (li) {
        var q = $('.pr__qt', li); if (!q) return;
        var n = Math.ceil(L / 2), got = 0, box;
        q.classList.add('is-free');
        for (; n <= L && !got; n++) {
          q.style.setProperty('--s', L / n);
          box = parseFloat(getComputedStyle(q.parentNode).height) - parseFloat(getComputedStyle(q.parentNode, '::before').height) - parseFloat(getComputedStyle(q.parentNode, '::before').marginBottom);
          // unscaled layout heights (the card behind is at .96 on screen)
          if (q.offsetHeight <= box + 1) got = n;
        }
        q.classList.remove('is-free');
        q.style.setProperty('--s', got ? L / got : 1);
        q.style.setProperty('--ql', got || L);
        li.classList.toggle('is-long', !got);
      });
    }

    /* ---- the swap: the card behind lifts, swings out on a short arc and lands
       on top; the one in front recedes to its place behind ---- */
    function swap(li, toQuote) {
      var pair = $('.pr__pair', li);
      // a swap asked for while one is in the air: that one lands at once, then this one runs
      if (pair._busy && pair._end) pair._end();
      if (pair.classList.contains('is-q') === toQuote) return;
      var ph = $('.pr__ph', pair), tx = $('.pr__txt', pair), btn = $('.pr__swap', pair), full = $('.pr__full', pair);
      var rise = toQuote ? tx : ph, sink = toQuote ? ph : tx;
      var more = function () { full.classList.toggle('is-more', full.scrollTop + full.clientHeight < full.scrollHeight - 4); };
      if (!full._m) { full._m = 1; on(full, 'scroll', more, { passive: true }); }
      var flip = function () {
        pair.classList.toggle('is-q', toQuote);
        full.scrollTop = 0; more();
      };
      btn.setAttribute('aria-pressed', toQuote ? 'true' : 'false');
      full.tabIndex = toQuote ? 0 : -1;
      if (still() || !rise.animate) { flip(); return; }
      // out along the line from the other card's centre to this one's
      var a = rise.getBoundingClientRect(), b = sink.getBoundingClientRect();
      var vx = (a.left + a.width / 2) - (b.left + b.width / 2), vy = (a.top + a.height / 2) - (b.top + b.height / 2);
      var L = Math.hypot(vx, vy) || 1, D = Math.max(16, Math.min(34, W() * .055));
      var ox = vx / L * D, oy = vy / L * D, ry = (vx >= 0 ? 1 : -1) * 5;
      var P = 'perspective(1200px) ', T = function (x, y, r, s) { return P + 'translate3d(' + x.toFixed(1) + 'px,' + y.toFixed(1) + 'px,0) rotateY(' + r + 'deg) scale(' + s + ')'; };
      var MS = 650;
      pair._busy = true; hold(true);
      rise.classList.add('is-fly');
      var ra = rise.animate([
        { transform: T(0, 0, 0, .96), easing: 'cubic-bezier(.3,.7,.2,1)' },
        { transform: T(ox, oy, ry, 1.03), offset: .42, easing: 'cubic-bezier(.45,0,.2,1)' },
        { transform: T(ox * .1, oy * .1, -ry * .25, 1.012), offset: .74, easing: 'cubic-bezier(.4,0,.4,1)' },
        { transform: T(0, 0, 0, .994), offset: .9, easing: 'ease-out' },
        { transform: T(0, 0, 0, 1) }
      ], { duration: MS, fill: 'both' });
      sink.animate([
        { transform: T(0, 0, 0, 1), easing: 'cubic-bezier(.3,.7,.2,1)' },
        { transform: T(-ox * .45, -oy * .45, -ry * .4, .975), offset: .42, easing: SPRING },
        { transform: T(0, 0, 0, .96) }
      ], { duration: MS, fill: 'both' });
      // the change of layer happens in mid-flight, where they are furthest apart
      var flipped = false;
      later(rise, MS * .44, function () { if (!flipped) { flipped = true; flip(); } });
      later(rise, MS * .7, function () { rise.classList.remove('is-fly'); });
      pair._end = function () {
        if (!pair._busy) return;
        if (!flipped) { flipped = true; flip(); }
        [rise, sink].forEach(function (el) { el.getAnimations().forEach(function (k) { k.cancel(); }); });
        rise.classList.remove('is-fly');
        pair._busy = false; hold(false);
      };
      done(ra, pair._end);
    }
    function reset(li) {
      var pair = $('.pr__pair', li);
      pair.classList.remove('is-q');
      $('.pr__swap', pair).setAttribute('aria-pressed', 'false');
      $('.pr__full', pair).tabIndex = -1;
    }

    /* ---- one review to the next: the pair in view drifts out and fades, the
       next comes in from the side it travels from, the photograph first and
       its words 80 ms after; the ground crossfades at the same time ---- */
    function settle() { if (pending) { var p = pending; pending = null; p.anims.forEach(function (a) { a.finish(); }); p.tidy(); } }
    function go(dir, fromX) {
      var k = cur + dir;
      if (k < 0 || k >= N) { if (fromX) home(fromX); return; }
      settle();
      var out = items[cur], inn = items[k];
      cur = k;
      warm(k + dir);
      inn.classList.add('is-on');
      colours(inn); say(inn); ends();
      idle(function () { if (!pending) paint(); }, { timeout: 1200 });
      var tidy = function () {
        out.classList.remove('is-on'); reset(out);
        [out, $('.pr__ph', inn), $('.pr__txt', inn)].forEach(function (el) { el.getAnimations().forEach(function (k) { k.cancel(); }); el.style.willChange = ''; });
        out.style.transform = out.style.opacity = '';
      };
      if (still() || !out.animate) { tidy(); return; }
      var w = W(), from = fromX || 0;
      var o0 = fromX ? Math.max(.2, 1 - Math.abs(fromX) / w * .9) : 1;
      out.style.transform = out.style.opacity = '';
      var ph = $('.pr__ph', inn), tx = $('.pr__txt', inn);
      var rest = function (el) { var t = getComputedStyle(el).transform; return t === 'none' ? '' : ' ' + t; };
      var phR = rest(ph), txR = rest(tx);
      [out, ph, tx].forEach(function (el) { el.style.willChange = 'transform, opacity'; });
      var anims = [
        out.animate([{ transform: 'translate3d(' + from + 'px,0,0)', opacity: o0 },
                     { transform: 'translate3d(' + (from - dir * Math.min(96, w * .16)) + 'px,0,0)', opacity: 0 }],
                    { duration: 420, easing: 'cubic-bezier(.4,0,.7,1)', fill: 'both' }),
        ph.animate([{ transform: 'translate3d(' + dir * Math.min(110, w * .2) + 'px,0,0)' + phR, opacity: 0 },
                    { transform: 'translate3d(0,0,0)' + phR, opacity: 1 }],
                   { duration: 640, delay: 60, easing: SPRING, fill: 'both' }),
        tx.animate([{ transform: 'translate3d(' + dir * Math.min(150, w * .27) + 'px,0,0)' + txR, opacity: 0 },
                    { transform: 'translate3d(0,0,0)' + txR, opacity: 1 }],
                   { duration: 640, delay: 140, easing: SPRING, fill: 'both' })
      ];
      hold(true);
      var tidy0 = tidy; tidy = function () { tidy0(); hold(false); };
      var mine = { anims: anims, tidy: tidy };
      pending = mine;
      done(anims[2], function () { if (pending === mine) { pending = null; tidy(); idle(paint, { timeout: 600 }); } });
    }
    /* a drag let go short of the next review: back home, on the spring */
    function home(x) {
      var li = items[cur];
      li.style.transform = li.style.opacity = '';
      if (!still() && x) li.animate([{ transform: 'translate3d(' + x + 'px,0,0)' }, { transform: 'translate3d(0,0,0)' }], { duration: 460, easing: SPRING });
    }

    on(prev, 'click', function () { go(-1); });
    on(next, 'click', function () { go(1); });
    on(sec, 'keydown', function (e) {
      if (e.target.closest && e.target.closest('dialog')) return;
      if (e.key === 'ArrowRight') { e.preventDefault(); go(1); }
      else if (e.key === 'ArrowLeft') { e.preventDefault(); go(-1); }
    });
    // Esc sends the words back from anywhere: Safari does not focus a tapped button,
    // so the key does not start inside the section
    on(d, 'keydown', function (e) {
      if (e.key !== 'Escape' || d.querySelector('dialog[open]')) return;
      var li = items[cur];
      if ($('.pr__pair', li).classList.contains('is-q')) { e.preventDefault(); swap(li, false); $('.pr__swap', li).focus({ preventScroll: true }); }
    });

    /* ---- the finger: the pair follows it; past a fifth of the stage, or let
       go fast, it carries on to the next; at either end it gives a little and
       comes back (rubber band). A mouse drags the same way. ---- */
    var dr = null, dragged = false;
    on(stage, 'pointerdown', function (e) {
      if (e.button > 0) return;
      if (e.pointerType === 'mouse') e.preventDefault();   // no native picture drag, no text selection (Firefox)
      dragged = false;
      dr = { x: e.clientX, y: e.clientY, id: e.pointerId, on: false, pts: [], eff: 0 };
    });
    on(stage, 'pointermove', function (e) {
      if (!dr || e.pointerId !== dr.id) return;
      var dx = e.clientX - dr.x, dy = e.clientY - dr.y;
      if (!dr.on) {
        if (Math.abs(dy) > 10 && Math.abs(dy) > Math.abs(dx)) { dr = null; return; }
        if (Math.abs(dx) < 8) return;
        dr.on = true; settle();
        try { stage.setPointerCapture(e.pointerId); } catch (x) {}
        items[cur].style.willChange = 'transform, opacity';
      }
      dr.pts.push([e.timeStamp, dx]);
      while (dr.pts.length > 2 && e.timeStamp - dr.pts[0][0] > 100) dr.pts.shift();
      var dir = dx < 0 ? 1 : -1, can = !!items[cur + dir], w = W();
      var eff = can ? dx : (dx < 0 ? -1 : 1) * Math.min(64, Math.abs(dx) * .28);
      dr.eff = eff;
      items[cur].style.transform = 'translate3d(' + eff.toFixed(1) + 'px,0,0)';
      items[cur].style.opacity = can ? String(Math.max(.35, 1 - Math.abs(eff) / w * .9)) : '';
    });
    var release = function (e) {
      if (!dr || (e && e.pointerId !== dr.id)) return;
      var d0 = dr; dr = null;
      if (!d0.on) return;
      dragged = true;
      items[cur].style.willChange = '';
      var p = d0.pts, v = p.length > 1 ? (p[p.length - 1][1] - p[0][1]) / Math.max(1, p[p.length - 1][0] - p[0][0]) : 0;
      var eff = d0.eff, dir = eff < 0 ? 1 : -1, w = W();
      if (items[cur + dir] && (Math.abs(eff) > w * .2 || (Math.abs(v) > .45 && (v < 0 ? 1 : -1) === dir))) go(dir, eff);
      else home(eff);
    };
    on(stage, 'pointerup', release);
    on(stage, 'pointercancel', release);
    // a drag is not a tap
    on(stage, 'click', function (e) { if (dragged) { e.stopPropagation(); e.preventDefault(); dragged = false; } }, true);
    on(stage, 'dragstart', function (e) { e.preventDefault(); });
    // Firefox starts its own drag of a picture before any of this is asked
    $$('img', stage).forEach(function (im) { im.draggable = false; });

    /* ---- the cards: the one behind comes forward; the photograph in front
       opens whole; the words in front go back ---- */
    items.forEach(function (li) {
      var pair = $('.pr__pair', li);
      on($('.pr__swap', li), 'click', function () { swap(li, true); });
      on($('.pr__full', li), 'click', function () { swap(li, false); });
      on($('.pr__ph', li), 'click', function () {
        if (pair.classList.contains('is-q')) return swap(li, false);
        var r = DATA[li.getAttribute('data-id')];
        if (Zoom && r) Zoom.open($('.pr__ph', li), r);
      });
    });

    ends(); warm(cur); fit(); say(items[cur]); paint();
    var lt = null;
    var relight = function () { clearTimeout(lt); lt = setTimeout(function () { light(); fit(); }, 120); };
    light();
    var fitted = function () { fit(); light(); list.classList.add('is-fit'); };
    if (d.fonts && d.fonts.ready) d.fonts.ready.then(fitted); else fitted();
    setTimeout(function () { list.classList.add('is-fit'); }, 2000);
    on(window, 'resize', relight);
    if (live) live.textContent = '';
  });

})();
