/* /muestras/por-que/2.1 — GALERÍA (2.1; 2 stays in pq2.js). Quiet motion, transform and opacity only.

   1  arrival: each [data-r] gets .is-in once, as it comes on screen (CSS does
      the rest: the picture rises 12% into its frame and settles).
   2  drift: every .pq-px on screen moves ±2% of its frame's height as the
      frame crosses the viewport (inside the 2.5% its 105% scale overhangs; none
      on a phone plate, whose picture ends exactly at the window's foot). Scrubbed, so linear, and dt-free: it is a
      function of the scroll position, nothing accumulates.
   3  the welcome opens: its window scales from 90% to 100% as it rises into
      the screen, the picture inside by the inverse (it stands still).
   4  the record rolls in like an odometer (the live page's drums): only once
      60% of a figure is in view, a short roll (the last few digits of each drum,
      ≤ 1.2 s); until then the figure is not drawn, so no still frame reads "+0"
      or "0.000". A figure scrolled past before it rolled simply stands final.
   5  the gallery (a swipe on phones): only the slides wholly in view show their
      word, so the next one never peeks in cut in half.
   The loop runs only while something on screen can move. Reduced motion, or
   no IntersectionObserver: nothing runs and .pq-on comes off (a still page). */
(function () {
  'use strict';
  var root = document.documentElement;
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce || !('IntersectionObserver' in window) || !window.requestAnimationFrame) { root.classList.remove('pq-on'); return; }
  root.classList.add('pq-on');
  window.pq2 = true;

  /* 0 · the header's real height, for the cover's one screen (pq2.css carries the
     same measured numbers, 100 / 72 px, so this only corrects an unusual header) */
  var pqMain = document.querySelector('main.pq');
  function head() {
    if (!pqMain) return;
    var t = Math.round(pqMain.getBoundingClientRect().top + window.pageYOffset);
    if (t > 0 && t < 240) pqMain.style.setProperty('--pq-head', t + 'px');
  }
  head(); addEventListener('resize', head);

  /* 1 · arrival ------------------------------------------------------------- */
  var io = new IntersectionObserver(function (es) {
    es.forEach(function (e) {
      if (!e.isIntersecting) return;
      io.unobserve(e.target);
      // a swipe row arrives as one: the slides still off to the side come with it,
      // 70 ms apart, instead of rising in the middle of a swipe
      var kids = e.target.__pqKids;
      if (kids) kids.forEach(function (k, i) { setTimeout(function () { k.classList.add('is-in'); }, i * 70); });
      else e.target.classList.add('is-in');
    });
  }, { rootMargin: '0px 0px -6% 0px', threshold: 0.08 });
  Array.prototype.forEach.call(document.querySelectorAll('[data-r]'), function (el) {
    var row = el.closest('.pq-gal');
    if (!row) return io.observe(el);
    if (!row.__pqKids) { row.__pqKids = []; io.observe(row); }
    row.__pqKids.push(el);
  });

  /* 2 + 3 · drift and the opening ------------------------------------------- */
  // on a phone a plate's picture ends exactly at the foot of its window: no drift there
  var phonePlate = matchMedia('(max-width: 999px) and (max-aspect-ratio: 1/1)');
  // ±2%, inside the 2.5% the 105% scale overhangs each edge: at ±3% a 4 px band of
  // the frame's ground showed at the foot of a picture near the screen's edge (measured)
  var AMP = 0.02;
  var items = [];
  Array.prototype.forEach.call(document.querySelectorAll('.pq-px'), function (px) {
    items.push({ el: px, frame: px.parentNode.closest('.pq-f') || px.parentNode, plate: !!px.closest('.pq-plate'), on: false, last: null });
  });
  var open = document.querySelector('.pq-open'), openIn = open && open.querySelector('.pq-open__in');
  var openItem = open ? { el: open, on: false, last: null } : null;

  var seen = new IntersectionObserver(function (es) {
    es.forEach(function (e) {
      var it = e.target.__pq; if (!it) return;
      it.on = e.isIntersecting; kick();
    });
  }, { rootMargin: '10% 0px 10% 0px' });
  items.forEach(function (it) { it.frame.__pq = it; seen.observe(it.frame); });
  // no drift on a picture that holds still on purpose: a sticky one (it would swim
  // while pinned) and the cover
  var cover = document.querySelector('.pq-plate--cover');
  var stageMq = matchMedia('(max-width: 599px) and (max-aspect-ratio: 1/1)');
  function still() {
    items.forEach(function (it) {
      var f = it.frame.closest('figure');
      it.hold = !!(f && getComputedStyle(f).position === 'sticky') ||
        // 2.2: the cover never drifts — its picture is placed to the pixel under its words
        (!!cover && cover.contains(it.el)) ||
        // 2.3 (2): a whole car never drifts: it has no overhang to move in (pq23.css)
        it.frame.classList.contains('pq-whole') ||
        // 2.5: inside a phone stage the scroll itself moves the picture (the stage engine below)
        (stageMq.matches && !!it.el.closest('.pq-run'));
    });
  }
  still(); addEventListener('resize', function () { still(); kick(); });
  if (openItem) { open.__pq = openItem; seen.observe(open); }

  var raf = 0;
  function kick() { if (!raf) raf = requestAnimationFrame(frame); }
  function frame() {
    raf = 0;
    var vh = window.innerHeight, still = phonePlate.matches;
    items.forEach(function (it) {
      if (!it.on) return;
      if ((it.plate && still) || it.hold) { if (it.last !== 0) { it.el.style.transform = ''; it.last = 0; } return; }
      var r = it.frame.getBoundingClientRect();
      // -1 as the frame enters at the foot of the screen, +1 as it leaves at the top
      var t = ((vh + r.height) / 2 - (r.top + r.height / 2)) / ((vh + r.height) / 2);
      t = t < -1 ? -1 : t > 1 ? 1 : t;
      var y = Math.round(-t * AMP * r.height * 10) / 10;
      if (y !== it.last) { it.el.style.transform = 'translate3d(0,' + y + 'px,0)'; it.last = y; }
    });
    if (openItem && openItem.on) {
      var r = open.getBoundingClientRect();
      // closed (90%) when its top is at the foot of the screen, open by the time its top reaches 15%
      var p = (vh - r.top) / (vh * 0.85);
      p = p < 0 ? 0 : p > 1 ? 1 : p;
      var s = Math.round((0.9 + 0.1 * p) * 10000) / 10000;
      // 2.3 (2): an upright phone keeps the welcome edge to edge from the first pixel: its
      // frame opening from 90% read there as a box laid on the photograph
      if (still) s = 1;
      if (s !== openItem.last) {
        open.style.transform = s === 1 ? '' : 'scale(' + s + ')';
        openIn.style.transform = s === 1 ? '' : 'scale(' + (1 / s) + ')';
        openItem.last = s;
      }
    }
  }
  addEventListener('scroll', kick, { passive: true });
  addEventListener('resize', kick);
  kick();

  /* 5 · the gallery's current slide ---------------------------------------- */
  var gal = document.querySelector('.pq-gal');
  if (gal) {
    var slides = Array.prototype.slice.call(gal.children), gt = 0;
    var mark = function () {
      gt = 0;
      // a slide is "in place" when it is whole inside the row's visible box
      var g = gal.getBoundingClientRect();
      slides.forEach(function (li) { var r = li.getBoundingClientRect();
        li.classList.toggle('is-cur', r.left >= g.left - 2 && r.right <= g.right + 2); });
    };
    mark(); gal.classList.add('is-live');
    gal.addEventListener('scroll', function () { if (!gt) gt = requestAnimationFrame(mark); }, { passive: true });
    addEventListener('resize', mark);
  }

  /* 4 · the record ---------------------------------------------------------- */
  var nums = Array.prototype.slice.call(document.querySelectorAll('.pq-proof [data-count]'));
  if (!nums.length || !Element.prototype.animate) return;
  var EASE = 'cubic-bezier(.22,.61,.36,1)';   // --e-out
  root.classList.add('pq-odo-on');
  function build(old) {
    var b = old.cloneNode(false);
    var text = (old.getAttribute('data-prefix') || '') + String(+old.getAttribute('data-count') || 0).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    b.removeAttribute('data-count');
    var sr = document.createElement('span'); sr.className = 'pq-sr'; sr.textContent = text;
    var odo = document.createElement('span'); odo.className = 'pq-odo'; odo.setAttribute('aria-hidden', 'true');
    var drums = [], digits = text.replace(/\D/g, '').length, seenD = 0;
    text.split('').forEach(function (ch) {
      if (!/\d/.test(ch)) { var s = document.createElement('span'); s.className = 'pq-odo__sep'; s.textContent = ch; odo.appendChild(s); return; }
      // each drum rolls from 0 to its own digit, once: no extra turns. The figures
      // are set big now (86 px on a phone, 176 on a desk), and a strip of 40 cells
      // at DPR 3 is a 10,000 px layer while it moves: WebKit crashed the page on it
      // (measured, Playwright WebKit 390 DPR 3; 5rem passed, 22vw did not).
      // a short roll: the drum shows its digit's three predecessors and stops on it
      // (9 → 6 7 8 9, 1 → 8 9 0 1). A 0 that leads nothing in still rolls, so the
      // figure moves as one.
      var d = +ch, fromRight = digits - 1 - seenD++;
      var n = 3, html = '';
      for (var i = 0; i <= n; i++) html += '<i>' + ((d - n + i + 10) % 10) + '</i>';
      var c = document.createElement('span'); c.className = 'pq-odo__c' + (seenD === 1 && d === 1 ? ' is-one' : '');
      var strip = document.createElement('span'); strip.className = 'pq-odo__s'; strip.innerHTML = html;
      c.appendChild(strip); odo.appendChild(c);
      drums.push({ el: strip, to: n, fromRight: fromRight });
    });
    b.appendChild(sr); b.appendChild(odo);
    old.parentNode.replaceChild(b, old);
    // no measured width on the drums: the figures are tabular (pq2.css), so every
    // drum is one figure wide by itself. Setting a measured em width on them crashed
    // WebKit's page at DPR 2-3 with the figures this size (Playwright WebKit,
    // every phone width tried; bisected to this one line).
    drums.forEach(function (dr) { dr.el.style.transform = 'translateY(0)'; });
    odo.classList.add('is-wait');
    return { b: b, odo: odo, li: b.closest('li'), drums: drums, done: false };
  }
  var odos = nums.map(build);
  // the figure as it ends: every drum on its last cell, nothing moving
  function land(it) {
    if (it.done) return; it.done = true;
    it.drums.forEach(function (dr) { dr.el.style.transform = 'translateY(' + (-dr.to) + 'em)'; });
    it.odo.classList.remove('is-wait');
    if (it.li) it.li.classList.add('is-landed');
  }
  function roll(it, delay) {
    if (it.done) return; it.done = true;
    it.odo.classList.add('is-rolling');
    setTimeout(function () { it.odo.classList.remove('is-wait'); }, delay);
    var last = 0;
    it.drums.forEach(function (dr) {
      var dur = 900 + dr.fromRight * 75, end = -dr.to, give = .07;
      dr.el.animate([
        { transform: 'translateY(0)' },
        { transform: 'translateY(' + (end - give) + 'em)', offset: .9, easing: 'cubic-bezier(.3,.6,.4,1)' },
        { transform: 'translateY(' + end + 'em)' }
      ], { duration: dur, delay: delay, easing: EASE, fill: 'both' });
      last = Math.max(last, dur);
    });
    setTimeout(function () { it.odo.classList.remove('is-rolling'); if (it.li) it.li.classList.add('is-landed'); }, delay + last - 120);
  }
  var queue = [], timer = 0;
  var oio = new IntersectionObserver(function (es) {
    es.forEach(function (e) {
      var it = odos.filter(function (o) { return o.b === e.target; })[0];
      if (!it || it.done) return;
      if (e.isIntersecting && e.intersectionRatio >= 0.6) { oio.unobserve(e.target); queue.push(it); }
      // gone off the top of the screen before it rolled (a jump, a fast fling): final
      else if (!e.isIntersecting && e.boundingClientRect.top < 0) { oio.unobserve(e.target); land(it); }
    });
    if (queue.length && !timer) timer = setTimeout(function () {
      queue.sort(function (a, b) { return odos.indexOf(a) - odos.indexOf(b); });
      queue.forEach(function (it, i) { roll(it, i * 140); });
      queue = []; timer = 0;
    }, 60);
  }, { threshold: [0, 0.6] });
  odos.forEach(function (it) { oio.observe(it.b); });
})();

/* 2.4: a long letter in "Lo cuentan ellos" shows its first two lines, fading, and "Ver más"
   opens it (the home page's pattern and words). Clamped only when it overflows; measured
   again when the fonts arrive, and on resize while it is still closed. */
(function () {
  var qs = [].slice.call(document.querySelectorAll('.pq-q__t'));
  if (!qs.length) return;
  function fit(q) {
    var b = q.nextElementSibling;
    if (!b || !b.classList.contains('pq-q__more') || b.getAttribute('aria-expanded') === 'true') return;
    // the letter's own height against the clamp's (scrollHeight would count the hung
    // quote mark's glyph, which rises past its line box, as overflow on a 42-letter quote)
    q.classList.remove('is-clamped');
    var full = q.getBoundingClientRect().height;
    q.classList.add('is-clamped');
    var over = full > q.getBoundingClientRect().height + 2;
    q.classList.toggle('is-clamped', over);
    b.hidden = !over;
    if (over) b.setAttribute('aria-expanded', 'false');
  }
  qs.forEach(function (q) {
    var b = q.nextElementSibling;
    if (!b || !b.classList.contains('pq-q__more')) return;
    b.addEventListener('click', function () {
      var open = q.classList.toggle('is-clamped') === false;
      b.textContent = open ? b.dataset.less : b.dataset.more;
      b.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    fit(q);
  });
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { qs.forEach(fit); });
  var t = 0;
  addEventListener('resize', function () { clearTimeout(t); t = setTimeout(function () { qs.forEach(fit); }, 150); });
})();

/* 2.4.1: the letters' row on a phone is a swipe: only the letters wholly in view show their
   words (the walk-round's old rule), so the next one's photograph peeks in, never its words
   cut in half. Without the script, or with reduced motion, every word stays. */
(function () {
  var row = document.querySelector('.pq-say__l');
  if (!row || !document.documentElement.classList.contains('pq-on')) return;
  var qs = [].slice.call(row.children), t = 0;
  function mark() {
    t = 0;
    var g = row.getBoundingClientRect();
    qs.forEach(function (q) { var r = q.getBoundingClientRect();
      q.classList.toggle('is-cur', r.left >= g.left - 2 && r.right <= g.right + 2); });
  }
  mark(); row.classList.add('is-live');
  row.addEventListener('scroll', function () { if (!t) t = requestAnimationFrame(mark); }, { passive: true });
  addEventListener('resize', mark);
})();

/* 2.5: the phone's stages. A [data-stage] run is a tall track; its .pq-run__v holds the screen
   still (CSS sticky) while the scroll uncovers its layers one by one: layer k's box rises from
   below the screen's foot to its place and its contents are moved back by the same amount, so
   the picture itself stands still and only its top edge travels (transform only, two numbers
   per layer per frame, written only when they change). Scrubbed, so linear in the scroll: a
   step's last 18% is a hold, so each word stands a moment before the next picture comes. A
   "foot" layer (a detail rising where the car stands) fades the word it replaces, lifting it
   0.15em, before its own edge arrives there. data-stage: each step's length in screens (the
   CSS height of the run is the same sum). Upright phones only, motion allowed (pq-on). */
(function () {
  var root = document.documentElement;
  if (!root.classList.contains('pq-on') || !window.matchMedia) return;
  var mq = matchMedia('(max-width: 599px) and (max-aspect-ratio: 1/1)');
  var HOLD = 0.18;
  var main = document.querySelector('main.pq');
  var runs = [].slice.call(document.querySelectorAll('[data-stage]')).map(function (run) {
    var w = run.getAttribute('data-stage').trim().split(/\s+/).map(Number);
    var lead = +(run.getAttribute('data-lead') || 0);
    var layers = [].slice.call(run.querySelectorAll('[data-layer]')).map(function (el) {
      var type = el.getAttribute('data-layer');
      var inner = type === 'base' ? [el.querySelector('.pq-plate__in') || el.firstElementChild] : [].slice.call(el.children);
      var word = type === 'line' ? null : el.querySelector('.pq-w, .pq-word');
      // what fades: a heading's lines (the cover's h1 keeps its own rise animation, which
      // would hold an opacity set on it), or the word itself
      var fade = word ? (word.children.length ? [].slice.call(word.children) : [word]) : [];
      return { el: el, type: type, inner: inner, word: el.hasAttribute('data-keep') ? null : word, fade: fade, h: 0, wb: 0, last: null, wl: null };
    });
    var pre = run.classList.contains('pq-run--a') ? run.querySelector('.pq-cover > .pq-plate') : null;
    return { run: run, view: run.querySelector('.pq-run__v'), w: w, lead: lead, layers: layers, pre: pre, preLast: null, on: false, D: 0, vh: 0, imgs: [].slice.call(run.querySelectorAll('img')), warm: false };
  });
  if (!runs.length) return;

  var head = 100;
  function measure() {
    var s = main && getComputedStyle(main).getPropertyValue('--pq-head');
    head = parseFloat(s) || 100;
    runs.forEach(function (R) {
      R.vh = R.view.offsetHeight;
      R.D = R.run.offsetHeight - R.vh;
      R.layers.forEach(function (L) {
        L.h = L.el.offsetHeight; L.last = null; L.wl = null;
        // how high its word stands above the screen's foot (its transforms cancel out, but the
        // cover's is lifted by the header's height until the stage pins)
        // (layout offsets: transforms never change them; every word's offset parent ends on the
        // screen's foot: a plate's inner box, or a detail's own layer)
        if (L.word) { var op = L.word.offsetParent; L.wb = op ? op.offsetHeight - L.word.offsetTop - L.word.offsetHeight : 0; }
      });
      R.preLast = null;
      // stage B: the heading stands on the closing line's real height (it may wrap)
      var end = R.run.querySelector('[data-layer="line"]');
      if (end) R.run.style.setProperty('--endh', end.offsetHeight + 'px');
      R.run.classList.add('is-set');
    });
  }
  function clear() {
    runs.forEach(function (R) {
      R.layers.forEach(function (L) {
        L.el.style.transform = ''; L.inner.forEach(function (i) { i.style.transform = ''; });
        L.fade.forEach(function (w) { w.style.opacity = ''; w.style.transform = ''; });
        L.last = null; L.wl = null;
      });
      if (R.pre) R.pre.style.transform = '';
      R.preLast = null;
      R.run.style.removeProperty('--endh');
    });
  }
  // every picture of a stage is fetched and decoded before the stage reaches the screen,
  // so no layer ever rises empty (they are lazy, and a covered layer is never "in view")
  function warm(R) {
    if (R.warm) return; R.warm = true;
    R.imgs.forEach(function (im) {
      if (im.loading === 'lazy') im.loading = 'eager';
      if (im.decode) im.decode().catch(function () {});
    });
  }
  var c = function (v) { return v < 0 ? 0 : v > 1 ? 1 : v; };
  function frame() {
    raf = 0;
    if (!mq.matches) return;
    runs.forEach(function (R) {
      if (!R.on) return;
      var x = -R.run.getBoundingClientRect().top;      // px of the run scrolled past the screen's top
      if (R.pre) {
        var p = Math.round(Math.max(Math.min(x, 0), -head));
        if (p !== R.preLast) { R.pre.style.transform = 'translate3d(0,' + p + 'px,0)'; R.preLast = p; }
      }
      var sum = R.lead; R.w.forEach(function (v) { sum += v; });
      var k = R.D / sum, a = R.lead * k;
      for (var i = 1; i < R.layers.length; i++) {
        var L = R.layers[i], len = (R.w[i - 1] || 0) * k;
        var t = len > 0 ? c((x - a) / (len * (1 - HOLD))) : 1;
        a += len;
        var off = Math.round(L.h * (1 - t) * 2) / 2;
        if (off !== L.last) {
          var o = 'translate3d(0,' + off + 'px,0)', n = 'translate3d(0,' + (-off) + 'px,0)';
          L.el.style.transform = o;
          for (var j = 0; j < L.inner.length; j++) L.inner[j].style.transform = n;
          L.last = off;
        }
        // the word this layer replaces lifts and fades as the new picture rises, and is gone
        // just before the new edge reaches it (tw: the share of the travel at which it would)
        if (L.type !== 'line') {
          var P = R.layers[i - 1];
          if (P.word) {
            // ends just before the edge would reach it; never starts before the step does
            // (the cover's h1 stands low, so its window would otherwise open at rest)
            var e = (L.h ? P.wb / L.h : 1) - 0.03, b0 = Math.max(0.02, e - 0.26);
            var f = Math.round(c((t - b0) / Math.max(0.05, e - b0)) * 100) / 100;
            if (f !== P.wl) {
              for (var q = 0; q < P.fade.length; q++) {
                P.fade[q].style.opacity = f ? String(1 - f) : '';
                P.fade[q].style.transform = f ? 'translate3d(0,' + (-0.15 * f) + 'em,0)' : '';
              }
              P.wl = f;
            }
          }
        }
      }
    });
  }
  var raf = 0;
  function kick() { if (!raf) raf = requestAnimationFrame(frame); }
  // a run is live from a screen before it until a screen after it
  var io = new IntersectionObserver(function (es) {
    es.forEach(function (e) {
      var R = runs.filter(function (r) { return r.run === e.target; })[0]; if (!R) return;
      R.on = e.isIntersecting && mq.matches;
      R.run.classList.toggle('is-live', R.on);
      if (R.on) { warm(R); kick(); }
    });
  }, { rootMargin: '100% 0px 100% 0px' });
  runs.forEach(function (R) { io.observe(R.run); });
  function setup() {
    if (mq.matches) { measure(); runs.forEach(function (R) { var r = R.run.getBoundingClientRect(); R.on = r.bottom > -innerHeight && r.top < 2 * innerHeight; R.run.classList.toggle('is-live', R.on); if (R.on) warm(R); }); kick(); }
    else { clear(); runs.forEach(function (R) { R.on = false; R.run.classList.remove('is-live'); }); }
  }
  setup();
  addEventListener('scroll', kick, { passive: true });
  var rt = 0;
  addEventListener('resize', function () { clearTimeout(rt); rt = setTimeout(setup, 120); });
  if (mq.addEventListener) mq.addEventListener('change', setup);
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(setup);
  addEventListener('load', setup);
})();
