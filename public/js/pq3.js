/* /muestras/por-que/3 · Inmersiva — travelling through the photographs.

   THE CAMERA. A reel is one sticky screen; u is how many screens of scroll have
   passed since its stage reached the top. Every photograph has a path of
   keyframes [u, fx, fy, qx, qy, z, roll°] for upright screens (P) and wide ones
   (L): the point (fx, fy) of the photograph (fractions of its width and height)
   sits at (qx, qy) of the screen, at z times the size that just covers the
   screen. Between keyframes z moves geometrically (a constant feel of speed),
   the rest linearly — scrubbed motion is linear on this site; the feel is in
   the smoothed scroll. A frame that would show an edge is slid (or, rolled,
   scaled about the centre) until it covers.

   THE ZOOM-THROUGH. Pairs of photographs of the same car were matched on the
   originals: SIFT points + RANSAC, one similarity per pair, B = k·R(φ)·A + t in
   units of the photo's width (tools: scratchpad/pq3/match/m3.py; the overlays
   were checked by eye around the detail). While the camera pushes into A, B is
   placed by that transform, so the detail stays exactly where it is on screen:
   B opens as a soft window around it (its masked twin, .pq-shot--f), widens,
   and the moment B covers the screen it takes over whole and A is gone. Then
   B's own path continues from where the match left it, unwinding any roll.
   Between cars, the picture dips through the story's black.

   THE WORDS arrive on time (0.7 s, line by line), not scrubbed, so a pause
   never leaves a line half there; while on, they drift with the camera by 12%
   of the movement of the point of the photograph under them.

   The portrait and the welcome scene are the live page's stills, driven as
   why.js drives them (it is not loaded here: it also drives the films).
   Reduced motion, no IntersectionObserver: nothing runs; the CSS shows the essay.
   Debug: #pq-debug exposes window.__pq (seek a reel, read the state). */
(function () {
  'use strict';

  // ---- data -------------------------------------------------------------------
  // pB = k·R(φ)·pA + t   (k, φ rad, tx, ty in photo widths)
  var PAIRS = {
    '14>15': [1.8079, -0.0102, -0.7686, -0.45205],
    '40>41': [1.8202, 0.03613, -0.39493, -0.42702],
    '41>42': [1.2254, 0.11003, 0.09898, -0.2068],
    '43>44': [1.315, 0, -0.16584, -0.24578],
    '19>21': [1.0663, 0, -0.02756, -0.04507],
    '26>29': [0.959, 0, 0.06001, -0.02035]
  };
  // per reel: its length U (screens), the beats' [in, out, anchor shot], the shots
  // in order. A shot: dip [in0, in1] / out [o0, o1] (opacity ramps), or from + zt
  // [start, handoff] for a zoom-through; P / L keyframes.
  var REELS = {
    a: {
      U: 8.3,
      beats: { a1: [-9, 0.5, '14'], a2: [1.42, 1.98, '15'], a3: [3.92, 4.42, '42'], a4: [5.62, 6.12, '44'], a5: [7.3, 99, '21'] },
      shots: [
        { id: '14', first: true,
          P: [[0, .80, .56, .5, .56, 1, 0], [0.42, .80, .565, .5, .56, 1.02, 0], [1.15, .80, .58, .5, .55, 1.95, 0]],
          L: [[0, .56, 0, .5, 0, 1, 0], [0.42, .58, 0, .5, 0, 1.02, 0], [1.15, .80, .58, .68, .56, 1.86, 0]] },
        { id: '15', from: '14', zt: [0.72, 1.15], out: [1.98, 2.24],
          P: [[1.5, .70, .40, .5, .42, 1.12, 0], [2.24, .62, .43, .5, .45, 1.36, 0]],
          L: [[1.5, .66, .38, .55, .42, 1.12, 0], [2.24, .60, .42, .55, .45, 1.32, 0]] },
        { id: '40', dip: [2.18, 2.44],
          P: [[2.18, .44, .55, .5, .55, 1, 0], [2.42, .45, .55, .5, .55, 1.02, 0], [3.0, .50, .58, .5, .58, 1.9, 0]],
          L: [[2.18, .52, .45, .5, .45, 1, 0], [2.42, .52, .46, .5, .46, 1.02, 0], [3.0, .50, .57, .5, .57, 1.9, 0]] },
        { id: '41', from: '40', zt: [2.62, 3.0],
          P: [[3.65, .55, .58, .5, .56, 1.36, 0]],
          L: [[3.65, .52, .55, .5, .55, 1.36, 0]] },
        { id: '42', from: '41', zt: [3.3, 3.65], out: [4.42, 4.68],
          P: [[4.0, .55, .42, .5, .45, 1.13, 0], [4.68, .58, .45, .5, .47, 1.3, 0]],
          L: [[4.0, .50, .45, .5, .50, 1.1, 0], [4.68, .52, .47, .5, .50, 1.24, 0]] },
        { id: '43', dip: [4.66, 4.92],
          P: [[4.66, .51, .55, .5, .55, 1, 0], [4.9, .51, .56, .5, .56, 1.02, 0], [5.45, .51, .62, .5, .60, 1.45, 0]],
          L: [[4.66, .50, .50, .5, .50, 1, 0], [4.9, .50, .51, .5, .51, 1.02, 0], [5.45, .51, .62, .5, .60, 1.45, 0]] },
        { id: '44', from: '43', zt: [5.08, 5.45], out: [6.12, 6.38],
          P: [[5.8, .51, .42, .5, .42, 1.12, 0], [6.38, .51, .45, .5, .47, 1.24, 0]],
          L: [[5.8, .51, .42, .5, .42, 1.1, 0], [6.38, .51, .45, .5, .47, 1.22, 0]] },
        { id: '19', dip: [6.36, 6.62],
          P: [[6.36, .50, .50, .5, .50, 1, 0], [6.6, .50, .51, .5, .51, 1.02, 0], [7.15, .50, .55, .5, .55, 1.3, 0]],
          L: [[6.36, .50, .48, .5, .48, 1, 0], [6.6, .50, .49, .5, .49, 1.02, 0], [7.15, .50, .55, .5, .55, 1.3, 0]] },
        { id: '21', from: '19', zt: [6.78, 7.15],
          P: [[7.6, .50, .42, .5, .33, 1.25, 0], [8.3, .50, .42, .5, .32, 1.32, 0]],
          L: [[7.6, .50, .42, .5, .40, 1.2, 0], [8.3, .50, .42, .5, .39, 1.28, 0]] }
      ]
    },
    b: {
      U: 6.3,
      beats: { b1: [-0.35, 0.86, '22'], b2: [1.4, 2.15, '27'], b3: [2.66, 3.24, '26'], b4: [4.44, 5.1, '30'], b5: [5.62, 99, '24'] },
      shots: [
        { id: '22', first: true, out: [0.9, 1.15],
          P: [[-0.6, .50, .55, .5, .55, 1, 0], [1.15, .62, .57, .5, .57, 1.18, 0]],
          L: [[-0.6, .50, .50, .5, .50, 1, 0], [1.15, .55, .55, .5, .55, 1.15, 0]] },
        { id: '27', dip: [1.1, 1.36], out: [2.2, 2.46],
          P: [[1.1, .62, .50, .5, .50, 1, 0], [2.46, .75, .48, .5, .50, 1.4, 0]],
          L: [[1.1, .55, .45, .5, .45, 1, 0], [2.46, .72, .48, .5, .50, 1.35, 0]] },
        { id: '26', dip: [2.42, 2.68],
          P: [[2.42, .40, .50, .5, .52, 1, 0], [2.95, .39, .52, .5, .53, 1.05, 0], [3.55, .376, .558, .5, .55, 1.25, 0]],
          L: [[2.42, .45, 1, .5, 1, 1, 0], [2.95, .43, 1, .48, 1, 1.05, 0], [3.55, .376, .558, .45, .55, 1.25, 0]] },
        { id: '29', from: '26', zt: [3.25, 3.55], out: [3.96, 4.2],
          P: [[4.2, .45, .50, .5, .50, 1.32, 0]],
          L: [[4.2, .45, .50, .5, .50, 1.3, 0]] },
        { id: '30', dip: [4.16, 4.42], out: [5.12, 5.38],
          P: [[4.16, .62, .40, .5, .40, 1, 0], [5.38, .66, .42, .5, .42, 1.22, 0]],
          L: [[4.16, .50, .40, .5, .40, 1, 0], [5.38, .55, .42, .5, .42, 1.2, 0]] },
        { id: '24', dip: [5.34, 5.6],
          P: [[5.34, .55, 1, .5, 1, 1.22, 0], [6.3, .56, 1, .5, 1, 1.34, 0]],
          L: [[5.34, .50, 1, .5, 1, 1.04, 0], [6.3, .52, 1, .5, 1, 1.14, 0]] }
      ]
    }
  };

  var AR = 0.75;                        // every photograph is 4:3
  var TWIN_IN = 0.09;                   // screens: the window fades up as it opens
  var HAND = 0.06;                      // screens: the whole photograph takes over from its window
  var ZTW = 0.36;                       // screens: the longest a window may stay open (a double exposure, kept short)
  var FOLLOW = 0.12;                    // words drift by this share of the camera's movement under them

  // ---- the camera, pure ---------------------------------------------------------
  // a matrix {s, r, ox, oy}: screen = o + s·R(r)·p, p in photo widths (y up to AR)
  function camM(c, v) {
    var s = c[5] * v.wc, r = c[6] * Math.PI / 180, cr = Math.cos(r), sr = Math.sin(r);
    var fx = c[1], fy = c[2] * AR;
    return { s: s, r: r, ox: c[3] * v.vw - s * (cr * fx - sr * fy), oy: c[4] * v.vh - s * (sr * fx + cr * fy) };
  }
  function covers(M, v) {
    var cr = Math.cos(-M.r), sr = Math.sin(-M.r), e = 0.75 / M.s;
    var pts = [[0, 0], [v.vw, 0], [0, v.vh], [v.vw, v.vh]];
    for (var i = 0; i < 4; i++) {
      var dx = pts[i][0] - M.ox, dy = pts[i][1] - M.oy;
      var px = (cr * dx - sr * dy) / M.s, py = (sr * dx + cr * dy) / M.s;
      if (px < -e || px > 1 + e || py < -e || py > AR + e) return false;
    }
    return true;
  }
  // slide (and if rolled, grow about the centre) until no edge shows
  // (one rule for every roll, so a roll unwinding to 0 never switches rules mid-move)
  function fit(M, v) {
    var ox = Math.min(0, Math.max(v.vw - M.s, M.ox)), oy = Math.min(0, Math.max(v.vh - M.s * AR, M.oy));
    var S = { s: M.s, r: M.r, ox: ox, oy: oy, slid: ox !== M.ox || oy !== M.oy };
    if (covers(S, v)) return S;
    var cx = v.vw / 2, cy = v.vh / 2, lo = 1, hi = 2.5, g = function (f) {
      return { s: S.s * f, r: S.r, ox: cx + (S.ox - cx) * f, oy: cy + (S.oy - cy) * f, slid: S.slid };
    };
    for (var i = 0; i < 18; i++) { var mid = (lo + hi) / 2; if (covers(g(mid), v)) hi = mid; else lo = mid; }
    var out = g(hi); out.grown = hi; return out;
  }
  var shift = function (o, min) { return o > 0 ? -o : o < min ? min - o : 0; };
  function solve(k, pr, v) {
    for (var n = 0; n < 30; n++) {
      var MA = camM(k, v), MB = mapM(MA, pr);
      MA.ox += shift(MB.ox, v.vw - MB.s); MA.oy += shift(MB.oy, v.vh - MB.s * AR);
      MA.ox += shift(MA.ox, v.vw - MA.s); MA.oy += shift(MA.oy, v.vh - MA.s * AR);
      MB = mapM(MA, pr);
      if (covers(MB, v) && covers(MA, v)) {
        var r = MA.r, x = k[1], y = k[2] * AR;
        k[3] = (MA.ox + MA.s * (Math.cos(r) * x - Math.sin(r) * y)) / v.vw;
        k[4] = (MA.oy + MA.s * (Math.sin(r) * x + Math.cos(r) * y)) / v.vh;
        return n;
      }
      k[5] *= 1.025;
    }
    return -1;
  }
  function mapM(MA, pr) {
    var s = MA.s / pr[0], r = MA.r - pr[1], cr = Math.cos(r), sr = Math.sin(r);
    return { s: s, r: r, ox: MA.ox - s * (cr * pr[2] - sr * pr[3]), oy: MA.oy - s * (sr * pr[2] + cr * pr[3]) };
  }
  // the camera of matrix M, expressed with focus (fx, fy) — for continuing a path from it
  function camOf(M, fx, fy, u, v) {
    var cr = Math.cos(M.r), sr = Math.sin(M.r), x = fx, y = fy * AR;
    return [u, fx, fy, (M.ox + M.s * (cr * x - sr * y)) / v.vw, (M.oy + M.s * (sr * x + cr * y)) / v.vh,
            M.s / v.wc, M.r * 180 / Math.PI];
  }
  function along(kf, u) {
    if (u <= kf[0][0]) return kf[0];
    var n = kf.length - 1;
    if (u >= kf[n][0]) return kf[n];
    for (var i = 0; i < n; i++) if (u < kf[i + 1][0]) break;
    var a = kf[i], b = kf[i + 1], t = (u - a[0]) / (b[0] - a[0]), o = [u];
    for (var j = 1; j < 7; j++) o.push(j === 5 ? a[5] * Math.pow(b[5] / a[5], t) : a[j] + (b[j] - a[j]) * t);
    return o;
  }
  // build a reel's geometry for one viewport: v = {vw, vh, wc, mode}
  function Reel(def) {
    this.def = def; this.U = def.U;
    this.by = {};
    var self = this;
    def.shots.forEach(function (sh) { self.by[sh.id] = sh; });
  }
  Reel.prototype.layout = function (v) {
    this.v = v; this.memo = {};
    var self = this;
    this.def.shots.forEach(function (sh) { sh.kv = sh[v.mode].map(function (k) { return k.slice(); }); });
    // where each zoom-through hands over: the first moment B covers by itself
    this.def.shots.forEach(function (sh) {
      if (!sh.from) return;
      sh.pair = PAIRS[sh.from + '>' + sh.id];
      // this screen's framing of A at the handoff: slid (or pushed a little
      // further) until B, placed by the match, covers the screen by itself
      var A = self.by[sh.from], h = sh.zt[1];
      A.kv.forEach(function (k) { if (Math.abs(k[0] - h) < 1e-6) solve(k, sh.pair, v); });
      self.memo = {};
      sh.uc = sh.zt[1];
      for (var u = sh.zt[0]; u <= sh.zt[1] + 1e-9; u += 0.005) {
        if (covers(mapM(self.M(sh.from, u), sh.pair), v)) { sh.uc = u; break; }
      }
      sh.short = !covers(mapM(self.M(sh.from, sh.zt[1]), sh.pair), v);
      // the window opens no later than the moment A would be enlarged past its own
      // pixels (v.file: the width of the file this screen loads), and is never open
      // for more than ZTW
      var un = sh.uc;
      for (var w = sh.zt[0]; w < sh.uc; w += 0.005) if (self.M(sh.from, w).s / v.file > 1.02) { un = w; break; }
      sh.zs = Math.max(sh.zt[0], sh.uc - ZTW, Math.min(sh.uc - 0.18, un));
      self.memo = {};
    });
  };
  // the matrix of a shot at u (memoised for the current u)
  Reel.prototype.M = function (id, u) {
    var key = id + '@' + u, m = this.memo[key];
    if (m) return m;
    var sh = this.by[id], v = this.v, kf = sh.kv;
    if (sh.from && u < sh.zt[1]) m = mapM(this.M(sh.from, u), PAIRS[sh.from + '>' + sh.id]);
    else {
      if (sh.from) {
        var h = sh.zt[1], f = kf[0];
        kf = [camOf(mapM(this.M(sh.from, h), PAIRS[sh.from + '>' + sh.id]), f[1], f[2], h, v)].concat(kf);
      }
      m = fit(camM(along(kf, u), v), v);
    }
    return (this.memo[key] = m);
  };
  // opacity of the shot, its twin; and whether it is worth keeping decoded
  var ramp = function (u, a, b) { return u <= a ? 0 : u >= b ? 1 : (u - a) / (b - a); };
  Reel.prototype.ops = function (sh, u) {
    var op = 1, tw = 0, next = this.nextOf(sh);
    if (sh.dip) op = ramp(u, sh.dip[0], sh.dip[1]);
    if (sh.from) {
      op = ramp(u, sh.uc, sh.uc + HAND);
      tw = u >= sh.zs && u < sh.uc + HAND ? ramp(u, sh.zs, sh.zs + TWIN_IN) : 0;
    }
    if (sh.out) op *= 1 - ramp(u, sh.out[0], sh.out[1]);
    if (next && u >= next.uc + HAND) op = 0;
    return [op, tw];
  };
  Reel.prototype.nextOf = function (sh) {
    var s = this.def.shots;
    for (var i = 0; i < s.length; i++) if (s[i].from === sh.id) return s[i];
    return null;
  };
  // the span of u in which a shot can be seen
  Reel.prototype.span = function (sh) {
    var a = sh.first ? -9 : sh.from ? sh.zs : sh.dip[0];
    var nx = this.nextOf(sh), b = sh.out ? sh.out[1] : nx ? nx.zt[1] + HAND : 99;
    return [a, b];
  };

  var MATH = { camM: camM, covers: covers, fit: fit, mapM: mapM, along: along, Reel: Reel, REELS: REELS, PAIRS: PAIRS };
  if (typeof document === 'undefined') { if (typeof module !== 'undefined') module.exports = MATH; return; }

  // ---- the page -----------------------------------------------------------------
  var root = document.documentElement;
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  var reelEls = Array.prototype.slice.call(document.querySelectorAll('[data-reel]'));
  if (!reelEls.length || reduce || !('IntersectionObserver' in window) || !window.requestAnimationFrame) {
    root.classList.remove('pq-on', 'wy-on'); return;
  }
  root.classList.add('pq-on', 'wy-on');

  var T_IN = 0.7, T_OUT = 0.42, T_STAG = 0.12, RISE = 0.3, LIFT = 0.18;
  var TAU = 0.11, LAG = 0.35;
  var E = function (t) { t = t < 0 ? 0 : t > 1 ? 1 : t; return t * t * (3 - 2 * t); };
  var clamp = function (v) { return v < 0 ? 0 : v > 1 ? 1 : v; };
  var now = performance.now();
  function tween(v) { return { from: v, to: v, at: 0, dur: 1 }; }
  function aim(tw, to, dur, delay) { if (tw.to === to) return; tw.from = val(tw); tw.to = to; tw.at = now + (delay || 0) * 1000; tw.dur = dur; }
  function val(tw) { return now < tw.at ? tw.from : tw.from + (tw.to - tw.from) * E((now - tw.at) / 1000 / tw.dur); }
  function busy(tw) { return tw.from !== tw.to && (now - tw.at) / 1000 < tw.dur; }

  var R = reelEls.map(function (el) {
    var id = el.getAttribute('data-reel'), def = REELS[id], g = new Reel(def);
    el.style.setProperty('--u', def.U);
    var st = el.querySelector('.pq-stage');
    var shots = def.shots.map(function (sh) {
      return { sh: sh, el: el.querySelector('[data-shot="' + sh.id + '"]'), tw: el.querySelector('[data-twin="' + sh.id + '"]'),
               live: false, sty: '' , tsty: '' };
    });
    var words = Array.prototype.slice.call(el.querySelectorAll('[data-b]')).map(function (w) {
      var b = def.beats[w.getAttribute('data-b')];
      var lines = Array.prototype.slice.call(w.querySelectorAll('.pq-l'));
      return { el: w, a: b[0], z: b[1], anchor: b[2], lines: lines, tin: lines.map(function () { return tween(0); }),
               sty: [], fit: w.classList.contains('pq-w--word'), cx: 0, cy: 0, ref: null, off: '' };
    });
    return { el: el, id: id, g: g, stage: st, shots: shots, words: words, top: 0, near: false, u: -9,
             intro: id === 'a' ? tween(0) : null };
  });
  // the opening fades up from night once its photograph is there (at most 1.2 s
  // after this script: night is a good enough ground to land the words on)
  (function () {
    var r = R[0], im = r.intro && r.shots[0].el.querySelector('img');
    if (!im) return;
    var go = function () { now = performance.now(); aim(r.intro, 1, 0.9); kick(); };
    if (im.complete && im.naturalWidth) requestAnimationFrame(go);
    else { im.addEventListener('load', go); im.addEventListener('error', go); setTimeout(go, 1200); }
  })();

  // ---- the stills (portrait, welcome): as why.js ---------------------------------
  var S = Array.prototype.slice.call(document.querySelectorAll('[data-scene="still"]')).map(function (el) {
    return {
      el: el, top: 0, run: 1, near: false, sv: {},
      welcome: el.classList.contains('wy-still--welcome'),
      pics: Array.prototype.slice.call(el.querySelectorAll('.wy-photo > img')),
      beats: Array.prototype.slice.call(el.querySelectorAll('.wy-beat')).map(function (b) {
        var whole = b.classList.contains('wy-beat--sub') || b.classList.contains('wy-beat--act');
        var lines = whole ? [b] : Array.prototype.slice.call(b.querySelectorAll('.wy-l'));
        return { el: b, a: parseFloat(b.getAttribute('data-in')), act: b.classList.contains('wy-beat--act'),
                 lines: lines, tin: lines.map(function () { return tween(0); }), sty: [],
                 // a beat that fades as a whole: its lines say so too while it is hidden, so a
                 // tool reading each line's own opacity never takes hidden words for shown ones
                 kids: whole ? Array.prototype.slice.call(b.querySelectorAll('.wy-l')) : [] };
      })
    };
  });

  // ---- measuring -------------------------------------------------------------------
  var vp = null;
  function measure() {
    var sy = window.pageYOffset;
    R.forEach(function (r) {
      var b = r.el.getBoundingClientRect();
      r.top = b.top + sy;
    });
    S.forEach(function (st) {
      var b = st.el.getBoundingClientRect();
      st.top = b.top + sy; st.run = Math.max(1, b.height - window.innerHeight);
    });
  }
  function layout() {
    var st = R[0].stage, vw = st.clientWidth, vh = st.clientHeight;
    if (!vw || !vh) return;
    var wc = Math.max(vw, vh / AR);
    vp = { vw: vw, vh: vh, wc: wc, mode: vw / vh < 1 ? 'P' : 'L',
           file: vw <= 600 && vw < vh ? 1600 : vw >= 1100 && vw >= vh ? 2400 : 2000 };
    R.forEach(function (r) {
      r.stage.style.setProperty('--wc', wc.toFixed(2) + 'px');
      r.stage.style.setProperty('--hc', (wc * AR).toFixed(2) + 'px');
      r.g.layout(vp);
      r.words.forEach(function (w) { if (w.fit) fitWord(w); });
      r.words.forEach(function (w) {
        w.el.style.transform = '';
        w.cx = w.el.offsetLeft + w.el.offsetWidth / 2; w.cy = w.el.offsetTop + w.el.offsetHeight / 2;
        w.ref = null; w.off = '';
      });
      r.shots.forEach(function (s) { s.sty = ''; s.tsty = ''; });
    });
    measure();
  }
  // a single word fills its measure: the box's width, never above --fit-max px
  function fitWord(w) {
    var el = w.el, span = w.lines[0];
    el.style.fontSize = '100px';
    var rg = document.createRange(); rg.selectNodeContents(span);
    var tw = rg.getBoundingClientRect().width, box = el.clientWidth;
    var cap = parseFloat(getComputedStyle(el).getPropertyValue('--fit-max')) || 160;
    var cap2 = vp.vh * (vp.mode === 'L' ? 0.2 : 0.14);
    if (tw > 0 && box > 0) el.style.fontSize = Math.min(cap, cap2, 100 * box / tw * 0.985).toFixed(2) + 'px';
    else el.style.fontSize = '';
  }

  // ---- drawing -----------------------------------------------------------------------
  function mstr(M) {
    var r = M.r, s = M.s / vp.wc;
    return 'translate3d(' + M.ox.toFixed(2) + 'px,' + M.oy.toFixed(2) + 'px,0)' +
      (Math.abs(r) > 1e-5 ? ' rotate(' + (r * 180 / Math.PI).toFixed(4) + 'deg)' : '') + ' scale(' + s.toFixed(5) + ')';
  }
  var moving = false;
  function drawReel(r) {
    var u = r.u, g = r.g;
    g.memo = {};
    r.shots.forEach(function (s) {
      var sp = g.span(s.sh), live = u > sp[0] - 1.2 && u < sp[1] + 0.6;
      if (live !== s.live) {
        s.live = live;
        s.el.classList.toggle('is-live', live); if (s.tw) s.tw.classList.toggle('is-live', live);
        // decoded before it is needed, off the main thread, so its first frame is not a stall
        if (live) { var im = s.el.querySelector('img'); if (im && im.decode) im.decode().catch(function () {}); }
        s.sty = ''; s.tsty = '';
        if (!live) { s.el.style.opacity = '0'; if (s.tw) s.tw.style.opacity = '0'; }
      }
      if (!live) return;
      var o = g.ops(s.sh, u), op = o[0], tw = o[1];
      if (r.intro && s.sh.first) { op *= val(r.intro); if (busy(r.intro)) moving = true; }
      var vis = op > 0.001 || tw > 0.001;
      var t = vis ? mstr(g.M(s.sh.id, u)) : null;
      var k = op.toFixed(3) + (op > 0.001 ? t : '');
      if (k !== s.sty) { s.sty = k; s.el.style.opacity = op.toFixed(3); if (op > 0.001) s.el.style.transform = t; }
      if (s.tw) {
        var k2 = tw.toFixed(3) + (tw > 0.001 ? t : '');
        if (k2 !== s.tsty) { s.tsty = k2; s.tw.style.opacity = tw.toFixed(3); if (tw > 0.001) s.tw.style.transform = t; }
      }
    });
    r.words.forEach(function (w) {
      var show = u >= w.a && u < w.z && !(r.intro && w.a < -1 && r.intro.to < 1);
      w.tin.forEach(function (tw, j) { aim(tw, show ? 1 : 0, show ? T_IN : T_OUT, show ? j * T_STAG : 0); });
      var vis = 0;
      for (var j = 0; j < w.lines.length; j++) {
        var tw = w.tin[j], i = val(tw);
        if (busy(tw) || now < tw.at) moving = true;
        var leaving = tw.to === 0;
        var ty = leaving ? -(1 - i) * LIFT : (1 - i) * RISE;
        var s = i.toFixed(3) + '|' + ty.toFixed(3);
        if (w.sty[j] !== s) {
          w.sty[j] = s;
          w.lines[j].style.opacity = i.toFixed(3);
          w.lines[j].style.transform = Math.abs(ty) > 1e-4 ? 'translate3d(0,' + ty.toFixed(3) + 'em,0)' : 'none';
        }
        vis = Math.max(vis, i);
      }
      // the drift: the point of the photograph under the words, from where it was
      if (vis > 0.001) {
        var ru = Math.min(Math.max(w.a, -0.2) + 0.25, w.z);
        if (!w.ref || w.refU !== ru) {
          var M0 = g.M(w.anchor, ru), cr = Math.cos(-M0.r), sr = Math.sin(-M0.r);
          var dx = w.cx - M0.ox, dy = w.cy - M0.oy;
          w.ref = [(cr * dx - sr * dy) / M0.s, (sr * dx + cr * dy) / M0.s]; w.refU = ru;
        }
        var M = g.M(w.anchor, u), c2 = Math.cos(M.r), s2 = Math.sin(M.r);
        var X = M.ox + M.s * (c2 * w.ref[0] - s2 * w.ref[1]), Y = M.oy + M.s * (s2 * w.ref[0] + c2 * w.ref[1]);
        var lim = 0.05 * vp.vh;
        var ox = Math.max(-lim, Math.min(lim, (X - w.cx) * FOLLOW)), oy = Math.max(-lim, Math.min(lim, (Y - w.cy) * FOLLOW));
        var os = 'translate3d(' + ox.toFixed(2) + 'px,' + oy.toFixed(2) + 'px,0)';
        if (os !== w.off) { w.off = os; w.el.style.transform = os; }
      }
    });
  }
  function setLine(b, j, op, i) {
    var ty = (1 - i) * RISE, s = op.toFixed(3) + '|' + ty.toFixed(3);
    if (b.sty[j] === s) return; b.sty[j] = s;
    var el = b.lines[j];
    el.style.opacity = op.toFixed(3);
    if (b.kids.length) b.kids.forEach(function (k) { k.style.opacity = op < 0.005 ? '0' : ''; });
    el.style.transform = ty ? 'translate3d(0,' + ty.toFixed(3) + 'em,0)' : 'none';
  }
  function drawStill(st) {
    var p = clamp((x - st.top) / st.run);
    st.beats.forEach(function (b) {
      var show = p >= b.a || b.el.contains(document.activeElement);
      b.tin.forEach(function (tw, j) { aim(tw, show ? 1 : 0, show ? T_IN : T_OUT, show ? j * T_STAG : 0); });
      var vis = 0;
      for (var j = 0; j < b.lines.length; j++) {
        var i = val(b.tin[j]);
        if (busy(b.tin[j]) || now < b.tin[j].at) moving = true;
        setLine(b, j, i, i); vis = Math.max(vis, i);
      }
      if (b.act) b.el.style.pointerEvents = vis > 0.6 ? 'auto' : 'none';
    });
    var sc = 'scale(' + (1 + 0.06 * (1 - E(st.welcome ? p / 0.6 : p))).toFixed(4) + ')';
    if (st.sv.sc !== sc) { st.sv.sc = sc; st.pics.forEach(function (el) { el.style.transform = sc; }); }
    if (st.welcome) {
      var o = E(p / 0.35).toFixed(4);
      if (st.sv.o !== o) { st.sv.o = o; st.el.style.setProperty('--o', o); }
    }
  }

  // ---- one smoothed scroll position --------------------------------------------------
  var x = window.pageYOffset, y = x, last = now, running = false, lastScroll = 0, snapped = false;
  function follow(cur, to, dt) {
    var d = to - cur, r = d / (LAG * window.innerHeight);
    return cur + d * (1 - Math.exp(-dt * (1 + r * r) / TAU));
  }
  function render() {
    moving = false;
    var vh = window.innerHeight;
    R.forEach(function (r) {
      var b = r.top - x;
      r.near = b < 2.5 * vh && b + (r.g.U + 1) * vp.vh > -1.5 * vh;
      if (!r.near) return;
      r.u = (x - r.top) / vp.vh;
      drawReel(r);
    });
    S.forEach(function (st) {
      var b = st.top - x;
      st.near = b < 2 * vh && b + st.run + vh > -vh;
      if (st.near) drawStill(st);
    });
  }
  function tick(t) {
    now = t;
    var dt = Math.min(0.05, Math.max(0, (t - last) / 1000)); last = t;
    var T = window.pageYOffset;
    if (snapped) { x = y = T; snapped = false; }
    y = follow(y, T, dt); x = follow(x, y, dt);
    var settled = Math.abs(T - x) < 0.25 && Math.abs(T - y) < 0.25;
    if (settled) x = y = T;
    render();
    if (settled && !moving && t - lastScroll > 200) { running = false; return; }
    requestAnimationFrame(tick);
  }
  function kick() {
    if (running) return;
    running = true; last = performance.now();
    requestAnimationFrame(tick);
  }
  window.addEventListener('scroll', function () { lastScroll = performance.now(); kick(); }, { passive: true });
  var lastW = window.innerWidth, lastH = window.innerHeight;
  window.addEventListener('resize', function () {
    // a phone's toolbar coming and going changes only the height by a little: the
    // stage is 100svh and does not move, so nothing is laid out again
    if (window.innerWidth === lastW && Math.abs(window.innerHeight - lastH) < 120 && R[0].stage.clientHeight === vp.vh) { kick(); return; }
    lastW = window.innerWidth; lastH = window.innerHeight;
    layout(); kick();
  });
  document.addEventListener('visibilitychange', function () { if (!document.hidden) kick(); });

  layout();
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { layout(); kick(); });
  window.addEventListener('load', function () { measure(); kick(); });
  kick();

  // ---- debug: #pq-debug ------------------------------------------------------------------
  if (/pq-debug/.test(location.hash)) {
    window.__pq = {
      MATH: MATH, R: R, vp: function () { return vp; },
      // put reel id at u, with the words settled in their final state
      seek: function (id, u, settle) {
        var r = R.filter(function (q) { return q.id === id; })[0];
        measure();
        window.scrollTo(0, Math.round(r.top + u * vp.vh));
        x = y = window.pageYOffset; snapped = true;
        now = performance.now();
        render();
        if (settle) {
          var done = function (tw) { tw.from = tw.to; tw.at = 0; };
          R.forEach(function (q) { q.words.forEach(function (w) { w.tin.forEach(done); }); if (q.intro) done(q.intro); });
          S.forEach(function (st) { st.beats.forEach(function (b) { b.tin.forEach(done); }); });
          render();
        }
        return { u: (x - r.top) / vp.vh };
      },
      state: function (id) {
        var r = R.filter(function (q) { return q.id === id; })[0], g = r.g;
        return r.shots.map(function (s) {
          var M = s.live ? g.M(s.sh.id, r.u) : null, img = s.el.querySelector('img');
          return { id: s.sh.id, live: s.live, op: +s.el.style.opacity, tw: s.tw ? +s.tw.style.opacity : null,
                   z: M ? M.s / vp.wc : null, r: M ? M.r * 180 / Math.PI : null, slid: M && M.slid, grown: M && M.grown,
                   uc: s.sh.uc, short: s.sh.short,
                   upscale: M && img && img.naturalWidth ? M.s / img.naturalWidth : null, src: img && img.currentSrc };
        });
      }
    };
  }
})();
