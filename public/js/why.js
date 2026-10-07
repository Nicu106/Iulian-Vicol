/* /por-que-nosotros — the scroll plays the films, frame by frame, like a video.

   The technique Apple uses on its product pages, not video seeking (seeking an
   H.264 file stepped at 10-17 new pictures a second, measured). Each film is a
   sequence of stills cut from his original footage and graded at extraction
   (/storage/why/seq/<film>/p = upright 608×1080 for phones, d = 1600×900 for
   anything wider), drawn on a <canvas> that covers the stage.

   - Loading: frame 0 is the poster under the canvas (preloaded by the page);
     then every 8th frame, so the whole film can be scrubbed at once; then the
     4th, 2nd and the rest. Film B starts loading a screen and a half before it
     arrives. Frames are fetched as bytes and only the ones around the current
     position are decoded (createImageBitmap, off the main thread), ahead in
     the direction of travel first; older browsers get <img> frames.
   - Between two frames the next is blended over the current one, so a slow
     glide, or a film still loading, never steps.
   - The follow: the page scroll is followed by a smoothed position — two
     identical exponential stages in series (critically damped: never
     overshoots, never runs backwards), time-based so it is the same at 60 or
     120 Hz. When the finger stops the film glides to rest in ~0.6 s and lands
     on a whole frame. On a fast flick the stages tighten as the gap grows, so
     the film stays within about a third of a screen of the finger.
   - One curve (smoothstep) for everything that moves, all read from that one
     position: the film (starting and ending at rest), each line's opacity,
     rise and blur-to-sharp (the same distance of scroll for every line in
     every scene), the shade under the words, every picture settling from
     106% to 100%, the dips: films rise out of the page white and dissolve
     back into it. The first film instead fades up from night on load, its
     words land, and only then may it move.
   - Idle: the loop runs only while something moves; nothing when settled.
   - Phones (taller than wide) get the upright frames; turning the phone swaps
     the set at the same moment of the film.
   Without IntersectionObserver / canvas, or with reduced motion, nothing runs
   and the CSS shows the plain page (html:not(.wy-on)) with the first frames. */
(function () {
  'use strict';
  var root = document.documentElement;
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  var scenes = Array.prototype.slice.call(document.querySelectorAll('[data-scene]'));
  var cv = document.createElement('canvas');
  if (!scenes.length || reduce || !('IntersectionObserver' in window) || !window.requestAnimationFrame || !cv.getContext) return;
  root.classList.add('wy-on');

  var TAU = 0.11;          // s, each of the two stages: rest in ~0.6 s
  var LAG = 0.35;          // screens: past this the follow tightens
  var SNAP_MS = 140;       // idle this long → settle on a whole frame
  var IN = 0.22, OUT = 0.16, STAGGER = 0.3;   // screens of scroll per reveal / exit; second line later
  var RISE = 0.32, LIFT = 0.22, BLUR = 6;      // em a line rises in / lifts out; px of blur while it arrives
  var PUSH = 0.06;         // every picture eases from 106% to 100% across its scene
  var DIP_IN = 0.8, DIP_OUT = 0.6;            // screens over which a film rises from / dissolves to the page
  var INTRO = [0.9, 0.45, 0.75, 0.18];        // s: picture fades up; first line starts, takes, next line after
  var CONC = 6;

  var upright = window.matchMedia('(max-aspect-ratio: 1/1)');
  var set = function () { return upright.matches ? 'p' : 'd'; };
  // ONE curve for everything that moves: film, pictures, words, dips
  var E = function (t) { t = t < 0 ? 0 : t > 1 ? 1 : t; return t * t * (3 - 2 * t); };
  var clamp = function (v) { return v < 0 ? 0 : v > 1 ? 1 : v; };
  var F = E;
  function Finv(f) { var a = 0, b = 1; for (var k = 0; k < 30; k++) { var c = (a + b) / 2; if (E(c) < f) a = c; else b = c; } return (a + b) / 2; }
  var t0 = performance.now(), intro = 0;   // seconds since the page started; the opening runs on time, not scroll
  var introEnd = INTRO[1] + INTRO[2] + INTRO[3];

  var dpr = 1, vh = window.innerHeight;
  var S = scenes.map(function (el) {
    var c = el.querySelector('canvas.wy-canvas');
    var st = {
      el: el, top: 0, run: 1, p: -1, near: false,
      shade: el.querySelector('.wy-shade'), veil: el.querySelector('.wy-veil'),
      pics: Array.prototype.slice.call(el.querySelectorAll('.wy-canvas, .wy-poster, .wy-photo > img')),
      first: el === scenes[0] && !!el.querySelector('canvas'), sv: {},
      beats: Array.prototype.slice.call(el.querySelectorAll('.wy-beat')).map(function (b) {
        var whole = b.classList.contains('wy-beat--sub') || b.classList.contains('wy-beat--act');
        return { el: b, a: parseFloat(b.getAttribute('data-in')), z: parseFloat(b.getAttribute('data-out')),
                 act: b.classList.contains('wy-beat--act'), sty: [],
                 lines: whole ? [b] : Array.prototype.slice.call(b.querySelectorAll('.wy-l')) };
      }),
      still: el.classList.contains('wy-still--welcome') ? 'o' : (el.classList.contains('wy-still') ? 'p' : null),
      cssv: ''
    };
    if (c) {
      st.c = c; st.ctx = c.getContext('2d');
      st.n = +c.getAttribute('data-n'); st.base = c.getAttribute('data-base');
      st.sets = {}; st.drawn = -1; st.started = false;
    }
    return st;
  });
  var films = S.filter(function (s) { return s.c; });

  // ---- loading ---------------------------------------------------------------
  // Where the browser can decode a picture off the main thread (createImageBitmap
  // from a Blob: Chrome, Firefox, Safari 15+), each frame is fetched as bytes
  // and only the frames around the current one are decoded, ahead in the
  // direction of travel first; the ones left behind are freed. Drawing a frame
  // decoded in advance costs ~2 ms on a phone; drawing an <img> the browser
  // has to decode first costs ~21 ms (measured) — a dropped frame. Older
  // browsers get plain <img> frames.
  var BM = !!(window.createImageBitmap && window.fetch && window.Blob);
  var queue = [], busy = 0;
  function frames(st, k) {
    var s = st.sets[k];
    if (!s) s = st.sets[k] = { k: k, src: new Array(st.n), ok: new Array(st.n), any: false, bm: {}, req: {}, busy: 0 };
    return s;
  }
  function order(n) {
    var seen = {}, out = [];
    [8, 4, 2, 1].forEach(function (d) {
      for (var i = 0; i < n; i += d) if (!seen[i]) { seen[i] = 1; out.push(i); }
      if (!seen[n - 1]) { seen[n - 1] = 1; out.push(n - 1); }
    });
    return out;
  }
  function start(st) {
    var k = set(), fs = frames(st, k);
    if (fs.queued) return; fs.queued = true; st.started = true;
    order(st.n).forEach(function (i) { queue.push([st, fs, i]); });
    pump();
  }
  function pump() {
    while (busy < CONC && queue.length) {
      var job = queue.shift(), st = job[0], fs = job[1], i = job[2];
      if (fs.src[i]) continue;
      busy++;
      (function (st, fs, i) {
        var url = st.base + fs.k + '/' + ('00' + i).slice(-3) + '.webp', done = false;
        var fin = function (what) {
          if (done) return; done = true; busy--;
          if (what) { fs.src[i] = what; fs.ok[i] = true; fs.any = true; st.drawn = -1; kick(); }
          pump();
        };
        fs.src[i] = 1;   // taken
        if (BM) {
          fetch(url).then(function (r) { if (!r.ok) throw r; return r.blob(); })
            .then(fin, function () { fs.src[i] = 0; fin(null); });
        } else {
          var im = new Image();
          im.decoding = 'async';
          im.onload = function () { if (im.decode) im.decode().then(function () { fin(im); }, function () { fin(im); }); else fin(im); };
          im.onerror = function () { fs.src[i] = 0; fin(null); };
          im.src = url;
        }
      })(st, fs, i);
    }
  }
  function release(fs, a, b) {
    for (var i in fs.bm) if (i < a || i > b) { if (fs.bm[i].close) fs.bm[i].close(); delete fs.bm[i]; }
    for (i in fs.req) if ((i < a || i > b) && !fs.bm[i]) delete fs.req[i];
  }
  // keep decoded: from where the film is to where the scroll is heading, plus
  // a margin (6 frames on a phone, 4 at 1600 px); at most ~75 MB / ~130 MB
  function keep(st, fs, pos, goal) {
    if (!BM) return;
    var w = fs.k === 'p' ? 6 : 4, dir = goal >= pos ? 1 : -1;
    var a = Math.max(0, Math.floor(Math.min(pos, goal)) - w), b = Math.min(st.n - 1, Math.ceil(Math.max(pos, goal)) + w);
    if (b - a > 40) { if (dir > 0) b = a + 40; else a = b - 40; }
    release(fs, a - 2, b + 2);
    // the frames the film is about to show first (towards the scroll), then behind
    var c = Math.round(pos), list = [], i;
    for (i = c; i >= a && i <= b; i += dir) list.push(i);
    for (i = c - dir; i >= a && i <= b; i -= dir) list.push(i);
    for (var k = 0; k < list.length && fs.busy < 4; k++) {
      i = list[k];
      if (!fs.ok[i] || fs.req[i]) continue;
      fs.req[i] = 1; fs.busy++;
      (function (i) {
        createImageBitmap(fs.src[i]).then(function (bmp) {
          fs.busy--;
          if (!fs.req[i]) { if (bmp.close) bmp.close(); }
          else { fs.bm[i] = bmp; st.drawn = -1; kick(); }
          keep(st, fs, st.pos, st.goal);
        }, function () { fs.busy--; delete fs.req[i]; });
      })(i);
    }
  }

  var io = new IntersectionObserver(function (es) {
    es.forEach(function (e) {
      var st = S[scenes.indexOf(e.target)];
      st.near = e.isIntersecting;
      if (e.isIntersecting && st.c) start(st);
      if (!e.isIntersecting && st.c) for (var k in st.sets) release(st.sets[k], 1, 0);
    });
    kick();
  }, { rootMargin: '150% 0px 150% 0px' });
  scenes.forEach(function (s) { io.observe(s); });

  // ---- drawing -----------------------------------------------------------------
  // the canvas has the screen's pixels (devicePixelRatio, at most 2) but never
  // more than the frame itself has on screen: beyond that the canvas would only
  // be enlarging the picture, which the compositor does as well at a fraction
  // of the cost (measured: 0.8 ms a frame instead of 8.7 on a phone).
  var DIM = { p: [608, 1080], d: [1600, 900] };
  function size() {
    dpr = Math.min(window.devicePixelRatio || 1, 2); vh = window.innerHeight;
    films.forEach(function (st) {
      var cw = st.c.clientWidth, ch = st.c.clientHeight, d = DIM[set()];
      var k = Math.min(dpr, cw && ch ? 1 / Math.max(cw / d[0], ch / d[1]) : dpr);
      var w = Math.round(cw * k), h = Math.round(ch * k);
      if (w && h && (st.c.width !== w || st.c.height !== h)) { st.c.width = w; st.c.height = h; }
      st.drawn = -1;
    });
  }
  function pic(fs, i) { return BM ? fs.bm[i] : (fs.ok[i] && fs.src[i]); }
  function nearest(fs, pos, dir) {
    var i = dir < 0 ? Math.floor(pos + 1e-6) : Math.ceil(pos - 1e-6);
    for (; i >= 0 && i < fs.ok.length; i += dir) if (pic(fs, i)) return i;
    return -1;
  }
  function blit(st, im, alpha) {
    var cw = st.c.width, ch = st.c.height, iw = im.naturalWidth || im.width, ih = im.naturalHeight || im.height;
    if (!iw || !cw) return;
    var sc = Math.max(cw / iw, ch / ih), sw = cw / sc, sh = ch / sc;
    st.ctx.globalAlpha = alpha;
    st.ctx.drawImage(im, (iw - sw) / 2, (ih - sh) / 2, sw, sh, 0, 0, cw, ch);
  }
  function draw(st, pos, goal) {
    var fs = st.sets[set()];
    if (!fs || !fs.any) { for (var k in st.sets) if (st.sets[k].any) { fs = st.sets[k]; break; } }
    if (!fs || !fs.any) return;
    st.pos = pos; st.goal = goal;
    keep(st, fs, pos, goal);
    if (Math.abs(pos - st.drawn) < 0.004) return;
    var lo = nearest(fs, pos, -1), hi = nearest(fs, pos, 1);
    if (lo < 0) lo = hi; if (hi < 0) hi = lo;
    if (lo < 0) return;
    var t = hi > lo ? (pos - lo) / (hi - lo) : 0;
    var ctx = st.ctx;
    ctx.imageSmoothingEnabled = true; ctx.imageSmoothingQuality = 'high';
    if (t > 0.985) blit(st, pic(fs, hi), 1);
    else { blit(st, pic(fs, lo), 1); if (t > 0.015) blit(st, pic(fs, hi), t); }
    ctx.globalAlpha = 1;
    st.drawn = pos;
  }

  // ---- the follow ----------------------------------------------------------------
  var x = window.pageYOffset, y = x, last = 0, running = false, lastScroll = 0;
  function measure() {
    var sy = window.pageYOffset;
    S.forEach(function (st) {
      var r = st.el.getBoundingClientRect();
      st.top = r.top + sy; st.run = Math.max(1, r.height - vh);
    });
    // the page opens with the first stage pushed down by the header: its words
    // are lifted by that much, so they are whole on the first screen
    var lead = Math.round(S[0].top) + 'px';
    if (lead !== S[0].lead) { S[0].lead = lead; S[0].el.style.setProperty('--wy-lead', lead); }
  }
  function target(now) {
    var T = window.pageYOffset;
    if (now - lastScroll < SNAP_MS) return T;
    // at rest inside a film: land on a whole frame, never between two
    for (var j = 0; j < films.length; j++) {
      var st = films[j], p = (T - st.top) / st.run;
      if (p > 0 && p < 1) {
        var f = Math.round(F(p) * (st.n - 1)) / (st.n - 1);
        return st.top + Finv(f) * st.run;
      }
    }
    return T;
  }
  function follow(cur, to, dt) {
    var d = to - cur, L = LAG * vh, r = d / L;
    return cur + d * (1 - Math.exp(-dt * (1 + r * r) / TAU));
  }
  function css(st, el, k, v) {
    var key = k + (el === st.el ? '' : st.pics.indexOf(el) + (el.className || ''));
    if (st.sv[key] === v) return; st.sv[key] = v;
    if (k.charAt(0) === '-') el.style.setProperty(k, v); else el.style[k] = v;
  }
  function setLine(b, j, op, i, o) {
    var ty = (1 - i) * RISE - o * LIFT, bl = (1 - i) * BLUR;
    var s = op.toFixed(3) + '|' + ty.toFixed(3) + '|' + bl.toFixed(1);
    if (b.sty[j] === s) return;
    b.sty[j] = s;
    var el = b.lines[j];
    el.style.opacity = op.toFixed(3);
    el.style.transform = ty ? 'translate3d(0,' + ty.toFixed(3) + 'em,0)' : 'none';
    el.style.filter = bl >= 0.1 ? 'blur(' + bl.toFixed(1) + 'px)' : 'none';
  }
  function render(T) {
    S.forEach(function (st) {
      if (!st.near) return;
      var q = (x - st.top) / st.run, p = clamp(q);
      // the opening: the first film fades up from the night of the stage, then
      // its words arrive; the film waits until the first line has landed
      var gate = st.first ? E((intro - INTRO[1] - INTRO[2]) / 0.5) : 1;
      if (st.c) draw(st, F(p) * (st.n - 1) * gate, F(clamp((T - st.top) / st.run)) * (st.n - 1) * gate);
      var key = q.toFixed(5) + (st.first ? '|' + intro.toFixed(3) : '');
      if (key === st.key) return;
      st.key = key;
      // films rise out of the page white and dissolve back into it: the same dip
      // on both sides of every film (the first one rises from night instead)
      var dipIn = st.first ? 0 : 1 - E((x - (st.top - vh)) / (DIP_IN * vh));
      var dipOut = E((x - (st.top + st.run)) / (DIP_OUT * vh));
      var night = st.first ? 1 - E(intro / INTRO[0]) : 0;
      var dip = Math.max(dipIn, dipOut);
      if (st.veil) {
        css(st, st.veil, 'opacity', Math.max(dip, night).toFixed(3));
        css(st, st.veil, 'backgroundColor', night > dip ? '#05080F' : '');
      }
      var rin = IN * vh / st.run, rout = OUT * vh / st.run, lit = 0;
      st.beats.forEach(function (b) {
        var o = b.z >= 1.5 ? 0 : E((p - (b.z - rout)) / rout), vis = 0;
        for (var j = 0; j < b.lines.length; j++) {
          var i = b.a < 0
            ? (st.first ? E((intro - INTRO[1] - j * INTRO[3]) / INTRO[2]) : 1)
            : E((p - b.a - j * STAGGER * rin) / rin);
          var op = i * (1 - o) * (1 - (st.c ? dip : 0));
          setLine(b, j, op, i, o);
          vis = Math.max(vis, op);
        }
        if (b.act) b.el.style.pointerEvents = vis > 0.6 ? 'auto' : 'none';
        lit = Math.max(lit, vis);
      });
      if (st.shade) css(st, st.shade, 'opacity', lit.toFixed(3));
      // the camera: every picture settles from 106% to 100% over its scene
      var sc = 'scale(' + (1 + PUSH * (1 - E(st.still === 'o' ? p / 0.6 : p))).toFixed(4) + ')';
      st.pics.forEach(function (el) { css(st, el, 'transform', sc); });
      if (st.still === 'o') css(st, st.el, '--o', E(p / 0.35).toFixed(4));
    });
  }
  function tick(now) {
    var dt = Math.min(0.05, Math.max(0, (now - last) / 1000)); last = now;
    intro = (now - t0) / 1000;
    measure();
    var T = target(now);
    y = follow(y, T, dt); x = follow(x, y, dt);
    var settled = Math.abs(T - x) < 0.25 && Math.abs(T - y) < 0.25;
    if (settled) { x = y = T; }
    render(T);
    if (settled && now - lastScroll > SNAP_MS + 50 && intro > introEnd + 0.6) { running = false; return; }
    requestAnimationFrame(tick);
  }
  function kick() {
    if (running) return;
    if (!S.some(function (st) { return st.near; }) && intro > introEnd + 0.6) { x = y = window.pageYOffset; return; }
    running = true; last = performance.now();
    requestAnimationFrame(tick);
  }
  window.addEventListener('scroll', function () { lastScroll = performance.now(); kick(); }, { passive: true });
  window.addEventListener('resize', function () { size(); S.forEach(function (st) { st.p = -1; }); kick(); });
  var swap = function () {
    films.forEach(function (st) { if (st.started) { st.started = false; start(st); } st.drawn = -1; });
    size(); kick();
  };
  if (upright.addEventListener) upright.addEventListener('change', swap); else if (upright.addListener) upright.addListener(swap);

  size(); measure();
  x = y = window.pageYOffset;
  S.forEach(function (st) { var r = st.el.getBoundingClientRect(); st.near = r.bottom > -vh && r.top < 2 * vh; });
  films.forEach(function (st) { if (st === films[0]) start(st); });
  render(x); running = false; kick();

  // the record counts up once, when it is seen
  var nums = document.querySelectorAll('.wy-proof [data-count]');
  var fmt = function (v) { return Math.round(v).toLocaleString('es-ES'); };
  Array.prototype.forEach.call(nums, function (n) { n.textContent = '0'; });
  var count = new IntersectionObserver(function (es) {
    es.forEach(function (e) {
      if (!e.isIntersecting) return; count.unobserve(e.target);
      var el = e.target, to = +el.getAttribute('data-count'), t0 = null;
      (function tick(t) {
        if (!t0) t0 = t; var k = Math.min(1, (t - t0) / 1400);
        el.textContent = fmt(to * (1 - Math.pow(1 - k, 3)));
        if (k < 1) requestAnimationFrame(tick);
      })(performance.now());
    });
  }, { threshold: .6 });
  Array.prototype.forEach.call(nums, function (n) { count.observe(n); });
})();
