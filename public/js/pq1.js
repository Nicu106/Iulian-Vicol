/* /muestras/por-que/1 · "Cine" — the scroll is the camera.

   Each chapter ([data-pq]) is a tall section with a sticky stage; its frames
   (.pq-shot, data-len screens each) are shown one at a time as the scroll runs
   through it. Everything follows one smoothed scroll position (two exponential
   stages, time-based, as on the live page), so the picture and the finger never
   disagree and a flick still lands softly.

   Between frames (data-cut on the frame that comes in), all of it calm:
     match  a 1.1 s dissolve with the badge pinned: the two fronts sit on the
            same point, so the grille stays where it was and the car changes
     cut    a 0.8 s dissolve, the new frame over the old one, which stays whole
            underneath until it is covered (no dip in the light halfway); the
            client reads quick photo changes as cheap, so there are none
     hard   an instant cut (kept for the record; no frame uses it now)
   A frame whose picture has not arrived waits for it (at most 1.5 s) while the
   outgoing frame holds the screen: a blur is never dissolved in.
   Between chapters: a dip to 60% night, scrubbed by the scroll, so the next
   picture is seen arriving, never a black slab.

   Inside a frame (t = 0..1 across its data-len), scrubbed, linear:
     hero    wall x1.00 -> 1.03, words x1.00 -> 1.06, car x1.00 -> 1.10, all
             about the car's badge: a dolly in, the words between wall and car
     anchor  the frame x1.00 -> 1.04 about its anchor
     cover   settles from 106% to 100%; data-push: x1.00 -> 1.16 into its focal
             point, dissolving into the close-up; data-drift: a slow pan
   The words move on time, not on the scroll: they arrive 0.32 s after their
   frame, line by line (1.1 s on a long exponential ease, 0.09 s apart, rising
   0.2 em; the opening's rise from behind the car's roof), and go with the
   frame. A frame with beats (.pq-beat data-at / data-to, in t) changes its
   words inside the frame. Only a lit frame is its own compositor layer.

   Fitted headlines ([data-fit]): sized so the longest line fills that share of
   the screen's width, never taller than the room above the car. Measured, not
   estimated (fonts.ready, resize).

   Only transform and opacity change per frame. The loop runs only while
   something moves. Without IntersectionObserver or with reduced motion nothing
   runs: the frames stand one under another (html:not(.pq-on), pq1.css). */
(function () {
  'use strict';
  var root = document.documentElement;
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  var chEls = Array.prototype.slice.call(document.querySelectorAll('[data-pq]'));
  if (!chEls.length || reduce || !('IntersectionObserver' in window) || !window.requestAnimationFrame) { root.classList.remove('pq-on'); return; }
  root.classList.add('pq-on');

  var TAU = 0.11, LAG = 0.35;                     // the follow: s per stage; screens before it tightens
  var T_IN = 1.1, T_OUT = 0.45, STAG = 0.09, WAIT = 0.32;
  var RISE = 0.2, RISE_HERO = 0.36;               // em: a line rises; the opening rises from behind the roof
  var XF = 0.8, XF_MATCH = 1.1;                   // s: a calm dissolve between frames; the match dissolve (badge pinned)
  var SETTLE = 0.06, PUSH = 0.16, DRIFT = 0.03;
  var HERO = [0.03, 0.06, 0.10], ANCHOR = 0.04;   // wall, words, car
  var DIP_IN = 0.75, DIP_OUT = 0.6, DIP = 0.6;    // screens; how dark: the picture is seen arriving, dimmed, never as a black slab
  var NIGHT = 0.9, FIRST_WORDS = 0.35;            // s: the opening fades up; its words start
  var WAIT_IMG = 1.5;                             // s: longest a frame waits for its picture

  // dissolves and dips: an even in-out (sine); words: a long exponential
  // arrival, most of the travel in the first third, then a slow settle
  var E = function (t) { t = t < 0 ? 0 : t > 1 ? 1 : t; return 0.5 - 0.5 * Math.cos(Math.PI * t); };
  var EX = function (t) { t = t < 0 ? 0 : t > 1 ? 1 : t; return (1 - Math.pow(2, -9 * t)) / (1 - Math.pow(2, -9)); };
  var clamp = function (v) { return v < 0 ? 0 : v > 1 ? 1 : v; };
  var now = performance.now(), vh = window.innerHeight;
  var started = false, t0 = now;

  function tween(v, ease) { return { from: v, to: v, at: 0, dur: 1, e: ease || E }; }
  function aim(tw, to, dur, delay) {
    if (tw.to === to) return;
    tw.from = val(tw); tw.to = to; tw.at = now + (delay || 0) * 1000; tw.dur = dur;
  }
  function val(tw) { return now < tw.at ? tw.from : tw.from + (tw.to - tw.from) * tw.e((now - tw.at) / 1000 / tw.dur); }
  function busy(tw) { return tw.from !== tw.to && (now - tw.at) / 1000 < tw.dur; }
  function snap(tw, v) { tw.from = tw.to = v; tw.at = 0; }

  // ---- the model -------------------------------------------------------------
  var C = chEls.map(function (el, ci) {
    var shots = Array.prototype.slice.call(el.querySelectorAll('.pq-shot'));
    var acc = 0;
    var ch = {
      el: el, stage: el.querySelector('.pq-stage'), veil: el.querySelector('.pq-veil'),
      first: ci === 0 && el.classList.contains('pq-ch--first'),
      top: 0, run: 1, near: false, cur: -1, sv: {}
    };
    ch.shots = shots.map(function (s, si) {
      var len = parseFloat(s.getAttribute('data-len')) || 1;
      var beats = Array.prototype.slice.call(s.querySelectorAll('.pq-beat'));
      var words = Array.prototype.slice.call(s.querySelectorAll('.pq-w'));
      var groups = beats.length ? beats : words;
      var sh = {
        el: s, i: si, len: len, start: acc, cut: s.getAttribute('data-cut') || 'cut',
        hero: s.classList.contains('pq-hero'), anchor: s.classList.contains('pq-anchor'),
        push: s.hasAttribute('data-push'), drift: s.hasAttribute('data-drift'),
        wall: s.querySelector('.pq-wall'), car: s.querySelector('.pq-car'),
        pic: s.querySelector('.pq-pic img'), words: words,
        imgs: Array.prototype.slice.call(s.querySelectorAll('img')),
        op: tween(0), on: false, sty: {}, pending: null,
        beats: groups.map(function (b) {
          var lines = b.classList.contains('pq-act') ? [b] : Array.prototype.slice.call(b.querySelectorAll('.pq-l'));
          return { el: b, at: parseFloat(b.getAttribute('data-at')) || 0,
                   to: b.hasAttribute('data-to') ? parseFloat(b.getAttribute('data-to')) : 9,
                   act: b.classList.contains('pq-act') || !!b.querySelector('a'),
                   rise: b.hasAttribute('data-rise') ? RISE_HERO : RISE,
                   lines: lines, tin: lines.map(function () { return tween(0, EX); }), sty: [] };
        })
      };
      acc += len;
      return sh;
    });
    ch.len = acc;
    return ch;
  });

  // ---- fitted headlines --------------------------------------------------------
  function fit() {
    document.querySelectorAll('.pq-w[data-fit]').forEach(function (w) {
      var shot = w.closest('.pq-shot'), stage = w.closest('.pq-stage') || shot;
      var W = stage.clientWidth, H = shot.clientHeight || vh;
      var wide = window.matchMedia('(min-aspect-ratio: 1/1)').matches;
      var share = parseFloat(w.getAttribute(wide && w.hasAttribute('data-fit-d') ? 'data-fit-d' : 'data-fit')) || 0.9;
      w.style.fontSize = '100px';
      var widest = 0;
      w.querySelectorAll('.pq-l').forEach(function (l) {
        // a line broken into fragments on a phone: its fragments are the lines
        var fs = Array.prototype.slice.call(l.querySelectorAll('.pq-f')).filter(function (f) { return getComputedStyle(f).display === 'block'; });
        (fs.length ? fs : [l]).forEach(function (x) {
          var r = document.createRange(); r.selectNodeContents(x);
          widest = Math.max(widest, r.getBoundingClientRect().width);
        });
      });
      var tall = w.getBoundingClientRect().height;
      // the room above it: from the top of the frame (below a margin) to its foot
      var foot = H - (parseFloat(getComputedStyle(w).bottom) || 0);
      var isHero = w.classList.contains('pq-w--hero');
      var room = isHero ? foot - H * 0.09 : H * 0.36;
      // never wider than the frame less its margin on both sides
      var ws = getComputedStyle(w), side = Math.min(parseFloat(ws.left) || 1e9, parseFloat(ws.right) || 1e9);
      var gutter = isHero ? 0 : 2 * (side < 1e8 ? side : 20);
      var px = Math.min(W * share / widest * 100, room / tall * 100, 300);
      if (!isHero) px = Math.min(px, (W - gutter) / widest * 100);
      w.style.fontSize = Math.max(16, Math.floor(px * 10) / 10) + 'px';
    });
  }

  // ---- geometry ---------------------------------------------------------------------
  var lead = 0;
  function measure() {
    var sy = window.pageYOffset;
    C.forEach(function (ch) {
      var r = ch.el.getBoundingClientRect();
      ch.top = r.top + sy; ch.run = Math.max(1, r.height - vh);
    });
    // the first stage starts below the header: until the header has scrolled
    // away its frame is lifted by what is left of it, so the car sits on the
    // foot of the screen from the first paint
    lead = Math.max(0, C[0].top);
  }
  function words0rigin(sh) {
    // the words scale about the car's badge: the box's anchor, in the words' own box
    if (!sh.hero || !sh.words[0]) return;
    var box = sh.wall.getBoundingClientRect(), w = sh.words[0].getBoundingClientRect();
    var cs = getComputedStyle(sh.el);
    var ax = parseFloat(cs.getPropertyValue('--ax')), ay = parseFloat(cs.getPropertyValue('--ay'));
    var tr = sh.wall.style.transform; sh.wall.style.transform = 'none';
    box = sh.wall.getBoundingClientRect();
    sh.wall.style.transform = tr;
    var wt = sh.words[0].style.transform; sh.words[0].style.transform = 'none';
    w = sh.words[0].getBoundingClientRect();
    sh.words[0].style.transform = wt;
    sh.words[0].style.transformOrigin = (box.left + ax * box.width - w.left).toFixed(1) + 'px ' + (box.top + ay * box.height - w.top).toFixed(1) + 'px';
  }
  function relayout() {
    vh = window.innerHeight;
    fit();
    measure();
    C.forEach(function (ch) { ch.shots.forEach(function (sh) { sh.sty = {}; words0rigin(sh); }); ch.sv = {}; });
  }

  // ---- writing styles only when they change -----------------------------------------
  function set(o, key, el, prop, v) {
    if (o[key] === v) return; o[key] = v;
    el.style[prop] = v;
  }

  // ---- the cut ------------------------------------------------------------------------
  function show(ch, k) {
    var prev = ch.cur;
    if (k === prev) return;
    ch.cur = k;
    var inc = ch.shots[k], out = prev >= 0 ? ch.shots[prev] : null;
    // the boundary's kind belongs to the later of the two frames
    var kind = out ? ch.shots[Math.max(k, prev)].cut : 'hard';
    var xf = kind === 'match' ? XF_MATCH : XF;
    var soft = out && kind !== 'hard';
    // the incoming frame dissolves in OVER the outgoing one, which stays whole
    // underneath until it is covered (no dip in the light halfway through); a
    // dissolve already running is taken from where it is, so a flick never flashes
    var from = Math.min(1, val(inc.op));
    ch.shots.forEach(function (sh, i) {
      if (i === k) return;
      if (sh === out && soft) { sh.el.style.zIndex = '1'; aim(sh.op, 0, 0.01, xf); }   // goes once the new frame is in
      else { snap(sh.op, 0); sh.el.style.zIndex = ''; }
      sh.on = false; sh.el.classList.remove('is-on');
    });
    inc.on = true; inc.el.classList.add('is-on'); inc.el.style.zIndex = '2';
    if (soft && from < 0.999) {
      snap(inc.op, from);
      // a picture still on its way is never dissolved in as a blur: the frame
      // waits for it (at most WAIT_IMG), the outgoing one holds the screen
      if (ready(inc)) aim(inc.op, 1, xf * (1 - from));
      else { inc.pending = { since: now, xf: xf * (1 - from), out: out }; out.op.at = Infinity; }
    }
    else snap(inc.op, 1);
    ch.shots.forEach(function (sh) { if (sh !== inc) sh.pending = null; });
    // its words arrive after it; the other frames' words are reset for next time
    ch.shots.forEach(function (sh, i) {
      if (i === k) return;
      sh.beats.forEach(function (b) { b.tin.forEach(function (tw) { snap(tw, 0); }); });
    });
  }
  function ready(sh) {
    for (var i = 0; i < sh.imgs.length; i++) { var im = sh.imgs[i]; if (!(im.complete && im.naturalWidth)) return false; }
    return true;
  }
  function wordsFor(ch, sh, t, firstHold) {
    sh.beats.forEach(function (b) {
      var on = sh.on && !sh.pending && !firstHold && t >= b.at && t < b.to;
      if (b.act && b.el.contains(document.activeElement)) on = true;
      b.tin.forEach(function (tw, j) {
        if (on && tw.to !== 1) aim(tw, 1, T_IN, WAIT + j * STAG);
        else if (!on && tw.to !== 0) aim(tw, 0, T_OUT);
      });
    });
  }

  // ---- one frame of the loop ----------------------------------------------------------
  var x = window.pageYOffset, y = x, last = now, running = false, lastScroll = 0;
  function render() {
    var moving = false;
    var liftNow = Math.max(0, lead - window.pageYOffset);
    C.forEach(function (ch) {
      if (!ch.near) return;
      var p = clamp((x - ch.top) / ch.run);
      var s = p * ch.len, k = 0;
      for (var i = 0; i < ch.shots.length; i++) if (s >= ch.shots[i].start - 1e-6) k = i;
      show(ch, k);
      var intro = ch.first ? (started ? (now - t0) / 1000 : 0) : 99;
      // the dip: the stage rises out of the black and falls back into it
      var dipIn = ch.first ? 0 : DIP * (1 - E((x - (ch.top - vh)) / (DIP_IN * vh)));
      var dipOut = DIP * E((x - (ch.top + ch.run)) / (DIP_OUT * vh));
      var night = ch.first ? 1 - E(intro / NIGHT) : 0;
      var veil = Math.max(dipIn, dipOut, night);
      if (ch.veil) set(ch.sv, 'veil', ch.veil, 'opacity', veil.toFixed(3));
      if (night > 0.001) moving = true;

      ch.shots.forEach(function (sh, i) {
        if (sh.pending) {
          moving = true;
          if (ready(sh) || now - sh.pending.since > WAIT_IMG * 1000) {
            aim(sh.op, 1, sh.pending.xf);
            if (sh.pending.out) { sh.pending.out.op.at = 0; snap(sh.pending.out.op, 1); aim(sh.pending.out.op, 0, 0.01, sh.pending.xf); }
            sh.pending = null;
          }
        }
        var op = val(sh.op);
        if (busy(sh.op) || now < sh.op.at) moving = true;
        set(sh.sty, 'op', sh.el, 'opacity', op.toFixed(3));
        // only a frame that is lit is its own layer (a dissolve is then a
        // composite, not a repaint of two screens); the rest cost no memory
        var lit = op > 0.001 || sh.on;
        set(sh.sty, 'wc', sh.el, 'willChange', lit ? 'opacity' : 'auto');
        if (sh.pic) set(sh.sty, 'wcp', sh.pic, 'willChange', lit ? 'transform' : 'auto');
        if (op <= 0.001 && !sh.on) return;
        var t = clamp((s - sh.start) / sh.len);
        if (i < k) t = 1; else if (i > k) t = 0;
        // the first frame of the first chapter: lifted by what is left of the header
        if (ch.first && i === 0) set(sh.sty, 'lift', sh.el, 'transform', liftNow > 0.5 ? 'translate3d(0,' + (-liftNow).toFixed(1) + 'px,0)' : 'none');
        if (sh.hero) {
          set(sh.sty, 'w', sh.wall, 'transform', 'scale(' + (1 + HERO[0] * t).toFixed(4) + ')');
          set(sh.sty, 'c', sh.car, 'transform', 'scale(' + (1 + HERO[2] * t).toFixed(4) + ')');
          if (sh.words[0]) set(sh.sty, 't', sh.words[0], 'transform', 'scale(' + (1 + HERO[1] * t).toFixed(4) + ')');
        } else if (sh.anchor) {
          set(sh.sty, 'w', sh.wall, 'transform', 'scale(' + (1 + ANCHOR * t).toFixed(4) + ')');
        } else if (sh.pic) {
          var tr;
          if (sh.push) tr = 'scale(' + (1 + PUSH * t).toFixed(4) + ')';            // scrubbed, so linear: the scroll is the dolly
          else if (sh.drift) tr = 'scale(' + (1 + SETTLE).toFixed(3) + ') translate3d(' + ((0.5 - t) * DRIFT * 100).toFixed(3) + '%,0,0)';
          else tr = 'scale(' + (1 + SETTLE * (1 - t)).toFixed(4) + ')';
          set(sh.sty, 'p', sh.pic, 'transform', tr);
        }
        // the words
        var hold = ch.first && i === 0 && intro < FIRST_WORDS;
        if (hold) moving = true;
        wordsFor(ch, sh, t, hold);
        sh.beats.forEach(function (b, bi) {
          var vis = 0;
          b.tin.forEach(function (tw, j) {
            var v = val(tw);
            if (busy(tw) || now < tw.at) moving = true;
            var ty = (1 - v) * b.rise;
            var key = bi + '|' + j, str = v.toFixed(3) + '|' + ty.toFixed(3);
            if (b.sty[j] !== str) {
              b.sty[j] = str;
              var el = b.lines[j];
              el.style.opacity = v.toFixed(3);
              el.style.transform = ty > 0.001 ? 'translate3d(0,' + ty.toFixed(3) + 'em,0)' : 'none';
            }
            vis = Math.max(vis, v);
            void key;
          });
          if (b.act) b.el.style.pointerEvents = vis > 0.6 ? 'auto' : 'none';
        });
      });
    });
    return moving;
  }
  function follow(cur, to, dt) {
    var d = to - cur, r = d / (LAG * vh);
    return cur + d * (1 - Math.exp(-dt * (1 + r * r) / TAU));
  }
  function tick(t) {
    now = t;
    var dt = Math.min(0.05, Math.max(0, (t - last) / 1000)); last = t;
    var T = window.pageYOffset;
    y = follow(y, T, dt); x = follow(x, y, dt);
    var settled = Math.abs(T - x) < 0.25 && Math.abs(T - y) < 0.25;
    if (settled) x = y = T;
    var moving = render();
    if (settled && !moving && t - lastScroll > 200 && started) { running = false; return; }
    requestAnimationFrame(tick);
  }
  function kick() {
    if (running) return;
    running = true; last = performance.now();
    requestAnimationFrame(tick);
  }

  // ---- loading: a chapter's pictures are fetched a screen and a half ahead -----
  var io = new IntersectionObserver(function (es) {
    es.forEach(function (e) {
      var ch = C[chEls.indexOf(e.target)];
      ch.near = e.isIntersecting;
      if (ch.near) Array.prototype.forEach.call(ch.el.querySelectorAll('img[loading="lazy"]'), function (im) { im.loading = 'eager'; });
    });
    kick();
  }, { rootMargin: '250% 0px 250% 0px' });
  chEls.forEach(function (el) { io.observe(el); });

  // the opening fades up from night when its wall and car can be painted (at most 1.4 s)
  (function () {
    var ims = Array.prototype.slice.call(C[0].el.querySelectorAll('.pq-shot:first-child img'));
    var left = ims.length, go = function () { if (started) return; started = true; t0 = performance.now(); kick(); };
    if (!C[0].first) return go();
    ims.forEach(function (im) {
      if (im.complete && im.naturalWidth) { if (--left === 0) requestAnimationFrame(go); return; }
      var done = function () { if (--left === 0) requestAnimationFrame(go); };
      im.addEventListener('load', done); im.addEventListener('error', done);
    });
    if (!left) requestAnimationFrame(go);
    setTimeout(go, 1400);
  })();

  // ---- keyboard: a link inside a frame brings its frame on screen ------------------
  document.addEventListener('focusin', function (e) {
    var shotEl = e.target.closest && e.target.closest('.pq-shot');
    if (!shotEl) return;
    C.forEach(function (ch) {
      ch.shots.forEach(function (sh) {
        if (sh.el !== shotEl) return;
        var b = sh.beats.filter(function (b) { return b.el.contains(e.target); })[0];
        var at = sh.start + sh.len * Math.min(0.98, (b ? b.at : 0) + 0.05);
        window.scrollTo(0, Math.round(ch.top + (at / ch.len) * ch.run));
      });
    });
    kick();
  });

  // ---- a turned phone lands on the same frame ------------------------------------
  var anchor = null, lastW = window.innerWidth;
  function place() {
    anchor = null;
    for (var i = 0; i < C.length; i++) {
      var r = C[i].el.getBoundingClientRect();
      if (r.top <= 0 && r.bottom > 0) { anchor = { ch: C[i], p: -r.top / Math.max(1, r.height - window.innerHeight) }; break; }
    }
  }
  window.addEventListener('scroll', function () { lastScroll = performance.now(); place(); kick(); }, { passive: true });
  var rz = 0;
  window.addEventListener('resize', function () {
    var turned = window.innerWidth !== lastW;
    lastW = window.innerWidth;
    var a = anchor;
    cancelAnimationFrame(rz);
    rz = requestAnimationFrame(function () {
      relayout();
      if (turned && a) {
        window.scrollTo(0, Math.round(a.ch.top + a.p * a.ch.run));
        x = y = window.pageYOffset;
      }
      kick();
    });
  });
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { relayout(); kick(); });

  relayout(); place();
  C.forEach(function (ch) { var r = ch.el.getBoundingClientRect(); ch.near = r.bottom > -1.5 * vh && r.top < 2.5 * vh; });
  kick();
})();
