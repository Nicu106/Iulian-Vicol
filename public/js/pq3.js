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
   originals: SIFT + RANSAC first, then refined per screen shape on the part of
   the photo that is on screen at the handover (the similarity that best overlays
   the two photographs' edges, weighted to the middle of the screen; tools/media/
   pq3-media.py). The camera pushes into A until B, placed by that match, covers
   the screen by itself (A's framing there is solved per viewport). From that
   moment the two are locked together — B moves on its own path and A follows it
   through the match — and B dissolves in over A, whole, in T_X seconds (in time,
   not scrubbed: a pause never freezes a double image). No mask, no window: by
   then everything on screen is the detail both photographs share. Between cars,
   a cut: down to the story's black and up again, also in time.

   SHARPNESS. A phone sees an upright slice of each photograph, so it is sent
   that slice, cut from the original at the density its deepest zoom needs; and
   where the camera goes deeper than that density allows, a second, smaller cut
   (level 1) of just the region seen there takes over (same pixels of the same
   photograph: the switch cannot be seen, only the sharpness). Wide screens get
   the whole photograph (2400) and the same level-1 cuts. CROPS is written by
   tools/media/pq3-media.py; a cut that would not cover this screen's path is not
   used (the whole photograph is).

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
  // pB = k·R(φ)·pA + t   (k, φ rad, tx, ty in photo widths); per screen shape when
  // the fit differs (P upright, L wide)
  var PAIRS = /*PAIRS*/{
    '14>15': { P: [1.79631, 0.00804, -0.74554, -0.4725], L: [1.80535, 0.0008, -0.75735, -0.4664] },
    '40>41': { P: [1.85438, 0.04326, -0.40705, -0.44948], L: [1.79793, 0.04293, -0.37817, -0.42157] },
    '43>44': { P: [1.33392, -0.01318, -0.18418, -0.25348], L: [1.35449, 0.00296, -0.18465, -0.27182] },
    '19>21': { P: [1.04368, 0.01754, -0.00708, -0.04718], L: [1.05289, 0.00962, -0.01589, -0.04861] }
  }/*/PAIRS*/;
  // the cuts: per shot, per set (p: phones upright, l: everything else)
  // [level-0 rect, level-1 rect or 0, the zoom it was planned to take over at,
  //  level 0's and level 1's file pixels per photo width]; rects [x0, y0, x1, y1]
  // in fractions of the photograph
  var CROPS = /*CROPS*/{
    '14': { p: [[0.5038, 0.0, 1.0, 1.0], 0, 2.034, 3791, 0], l: [[0.0, 0.0, 1.0, 1.0], [0.2508, 0.1135, 1.0, 0.9493], 1.389, 2400, 3528] },
    '15': { p: [[0.423, 0.0, 0.9326, 0.9576], 0, 1.337, 2492, 0], l: [[0.0, 0.0, 1.0, 1.0], 0, 1.389, 2400, 0] },
    '19': { p: [[0.2419, 0.0, 0.7581, 1.0], 0, 1.307, 2437, 0], l: [[0.0, 0.0, 1.0, 1.0], 0, 1.389, 2400, 0] },
    '21': { p: [[0.2895, 0.0506, 0.7241, 0.9827], 0, 1.32, 2462, 0], l: [[0.0, 0.0, 1.0, 1.0], 0, 1.389, 2400, 0] },
    '22': { p: [[0.2419, 0.0, 0.8411, 1.0], 0, 1.179, 2198, 0], l: [[0.0, 0.0, 1.0, 1.0], 0, 1.389, 2400, 0] },
    '24': { p: [[0.3348, 0.1536, 0.7652, 1.0], 0, 1.34, 2498, 0], l: [[0.0, 0.0, 1.0, 1.0], 0, 1.389, 2400, 0] },
    '26': { p: [[0.1419, 0.0, 0.6581, 1.0], 0, 1.296, 2416, 0], l: [[0.0, 0.0, 1.0, 1.0], 0, 1.389, 2400, 0] },
    '27': { p: [[0.3619, 0.0, 0.9491, 1.0], 0, 1.395, 2600, 0], l: [[0.0, 0.0, 1.0, 1.0], 0, 1.389, 2400, 0] },
    '29': { p: [[0.2011, 0.0, 0.6989, 1.0], 0, 1.22, 2274, 0], l: [[0.0, 0.0, 1.0, 1.0], 0, 1.389, 2400, 0] },
    '30': { p: [[0.3619, 0.0, 0.882, 1.0], 0, 1.24, 2311, 0], l: [[0.0, 0.0, 1.0, 1.0], 0, 1.389, 2400, 0] },
    '40': { p: [[0.1819, 0.0, 0.7034, 1.0], [0.2944, 0.1722, 0.6609, 0.9111], 1.44, 2683, 4033], l: [[0.0, 0.0, 1.0, 1.0], [0.133, 0.1357, 0.8874, 0.9215], 1.389, 2400, 4032] },
    '41': { p: [[0.2232, 0.0, 0.7532, 1.0], 0, 1.46, 2723, 0], l: [[0.0, 0.0, 1.0, 1.0], 0, 1.389, 2400, 0] },
    '43': { p: [[0.2519, 0.0, 0.7681, 1.0], 0, 1.724, 3214, 0], l: [[0.0, 0.0, 1.0, 1.0], [0.1288, 0.1538, 0.8887, 0.9955], 1.389, 2400, 2825] },
    '44': { p: [[0.2774, 0.0, 0.7426, 0.9646], 0, 1.293, 2410, 0], l: [[0.0, 0.0, 1.0, 1.0], 0, 1.389, 2400, 0] }
  }/*/CROPS*/;

  // per reel: its length U (screens), the beats' [in, out, anchor shot], the shots
  // in order. A shot is the first, or comes in at a cut (in: u), or through a
  // zoom-through handing over at h (from, h); it may leave at a cut (out: u).
  // P / L keyframes. Cuts and dissolves happen at a point of the scroll and then
  // take their own time (T_*), so a pause never leaves two photographs half there.
  var REELS = {
    a: {
      U: 4.76,
      beats: { a1: [-9, 0.28, '14'], a2: [0.8, 1.32, '15'], a3: [2.1, 2.56, '41'], a4: [3.36, 3.74, '44'], a5: [4.36, 99, '21'] },
      shots: [
        { id: '14', first: true,
          P: [[0, .80, .56, .5, .56, 1, 0], [0.18, .80, .565, .5, .56, 1.02, 0], [0.75, .80, .58, .5, .55, 1.95, 0]],
          L: [[0, .56, 0, .5, 0, 1, 0], [0.18, .58, 0, .5, 0, 1.02, 0], [0.75, .80, .58, .68, .56, 1.86, 0]] },
        { id: '15', from: '14', h: 0.75, out: 1.38,
          P: [[1.0, .70, .40, .5, .42, 1.12, 0], [1.68, .62, .43, .5, .45, 1.34, 0]],
          L: [[1.0, .66, .38, .55, .42, 1.12, 0], [1.68, .60, .42, .55, .45, 1.32, 0]] },
        { id: '40', in: 1.38,
          P: [[1.38, .44, .55, .5, .55, 1, 0], [1.48, .45, .55, .5, .55, 1.02, 0], [1.95, .50, .58, .5, .58, 1.9, 0]],
          L: [[1.38, .52, .45, .5, .45, 1, 0], [1.48, .52, .46, .5, .46, 1.02, 0], [1.95, .50, .57, .5, .57, 1.9, 0]] },
        { id: '41', from: '40', h: 1.95, out: 2.62,
          P: [[2.2, .55, .58, .5, .56, 1.3, 0], [2.92, .52, .55, .5, .54, 1.46, 0]],
          L: [[2.2, .52, .55, .5, .55, 1.3, 0], [2.92, .50, .55, .5, .55, 1.44, 0]] },
        { id: '43', in: 2.62,
          P: [[2.62, .51, .55, .5, .55, 1, 0], [2.72, .51, .56, .5, .56, 1.02, 0], [3.13, .51, .62, .5, .60, 1.45, 0]],
          L: [[2.62, .50, .50, .5, .50, 1, 0], [2.72, .50, .51, .5, .51, 1.02, 0], [3.13, .51, .62, .5, .60, 1.45, 0]] },
        { id: '44', from: '43', h: 3.13, out: 3.8,
          P: [[3.38, .51, .42, .5, .42, 1.12, 0], [4.1, .51, .45, .5, .47, 1.25, 0]],
          L: [[3.38, .51, .42, .5, .42, 1.1, 0], [4.1, .51, .45, .5, .47, 1.23, 0]] },
        { id: '19', in: 3.8,
          P: [[3.8, .50, .50, .5, .50, 1, 0], [3.9, .50, .51, .5, .51, 1.02, 0], [4.28, .50, .55, .5, .55, 1.3, 0]],
          L: [[3.8, .50, .48, .5, .48, 1, 0], [3.9, .50, .49, .5, .49, 1.02, 0], [4.28, .50, .55, .5, .55, 1.3, 0]] },
        { id: '21', from: '19', h: 4.28,
          P: [[4.56, .50, .42, .5, .33, 1.25, 0], [5.26, .50, .42, .5, .32, 1.32, 0]],
          L: [[4.56, .50, .42, .5, .40, 1.2, 0], [5.26, .50, .42, .5, .39, 1.28, 0]] }
      ]
    },
    b: {
      U: 3.15,
      beats: { b1: [-0.35, 0.42, '22'], b2: [0.56, 1.02, '27'], b3: [1.16, 1.6, '26'], b4: [2.1, 2.58, '30'], b5: [2.74, 99, '24'] },
      shots: [
        { id: '22', first: true, out: 0.48,
          P: [[-0.6, .50, .55, .5, .55, 1, 0], [0.78, .62, .57, .5, .57, 1.18, 0]],
          L: [[-0.6, .50, .50, .5, .50, 1, 0], [0.78, .55, .55, .5, .55, 1.15, 0]] },
        { id: '27', in: 0.48, out: 1.08,
          P: [[0.48, .62, .50, .5, .50, 1, 0], [1.38, .76, .48, .5, .50, 1.4, 0]],
          L: [[0.48, .55, .45, .5, .45, 1, 0], [1.38, .73, .48, .5, .50, 1.35, 0]] },
        { id: '26', in: 1.08, out: 1.68,
          P: [[1.08, .40, .50, .5, .52, 1, 0], [1.28, .39, .52, .5, .53, 1.05, 0], [1.98, .376, .558, .5, .55, 1.3, 0]],
          L: [[1.08, .45, 1, .5, 1, 1, 0], [1.28, .43, 1, .48, 1, 1.05, 0], [1.98, .376, .558, .45, .55, 1.3, 0]] },
        { id: '29', in: 1.68, out: 2.02,
          P: [[1.68, .45, .50, .5, .50, 1.04, 0], [2.32, .45, .50, .5, .50, 1.22, 0]],
          L: [[1.68, .45, .50, .5, .50, 1.02, 0], [2.32, .45, .50, .5, .50, 1.18, 0]] },
        { id: '30', in: 2.02, out: 2.66,
          P: [[2.02, .62, .40, .5, .40, 1, 0], [2.96, .67, .42, .5, .42, 1.24, 0]],
          L: [[2.02, .50, .40, .5, .40, 1, 0], [2.96, .56, .42, .5, .42, 1.22, 0]] },
        { id: '24', in: 2.66,
          P: [[2.66, .55, 1, .5, 1, 1.22, 0], [3.6, .56, 1, .5, 1, 1.34, 0]],
          L: [[2.66, .50, 1, .5, 1, 1.04, 0], [3.6, .52, 1, .5, 1, 1.14, 0]] }
      ]
    }
  };

  var AR = 0.75;                        // every photograph is 4:3
  // seconds: a zoom-through's dissolve; a cut's fade down, and up after a breath of black
  var T_X = 0.42, T_DOWN = 0.3, T_UP = 0.55, T_GAP = 0.12;
  var LOCK = 0.3;                       // screens past h for which A is placed by B (tools check its cover there)
  var FOLLOW = 0.12;                    // words drift by this share of the camera's movement under them

  // ---- the camera, pure ---------------------------------------------------------
  // a matrix {s, r, ox, oy}: screen = o + s·R(r)·p, p in photo widths (y up to AR)
  function camM(c, v) {
    var s = c[5] * v.wc, r = c[6] * Math.PI / 180, cr = Math.cos(r), sr = Math.sin(r);
    var fx = c[1], fy = c[2] * AR;
    return { s: s, r: r, ox: c[3] * v.vw - s * (cr * fx - sr * fy), oy: c[4] * v.vh - s * (sr * fx + cr * fy) };
  }
  // the part of the photograph on screen: [x0, y0, x1, y1] in fractions
  function seen(M, v) {
    var cr = Math.cos(-M.r), sr = Math.sin(-M.r), q = [0, 0, v.vw, 0, 0, v.vh, v.vw, v.vh];
    var x0 = 9, y0 = 9, x1 = -9, y1 = -9;
    for (var i = 0; i < 8; i += 2) {
      var dx = q[i] - M.ox, dy = q[i + 1] - M.oy;
      var px = (cr * dx - sr * dy) / M.s, py = (sr * dx + cr * dy) / M.s / AR;
      if (px < x0) x0 = px; if (px > x1) x1 = px; if (py < y0) y0 = py; if (py > y1) y1 = py;
    }
    return [x0, y0, x1, y1];
  }
  function inside(a, c, e) { return a[0] >= c[0] - e && a[1] >= c[1] - e && a[2] <= c[2] + e && a[3] <= c[3] + e; }
  function covers(M, v) { return inside(seen(M, v), [0, 0, 1, 1], 0.75 / M.s); }
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
  // A's keyframe at the handover, moved (or pushed a little further) until B,
  // placed by the match, covers the screen by itself — and A too
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
  // B's matrix from A's (A placed, B where the match puts it), and back
  function mapM(MA, pr) {
    var s = MA.s / pr[0], r = MA.r - pr[1], cr = Math.cos(r), sr = Math.sin(r);
    return { s: s, r: r, ox: MA.ox - s * (cr * pr[2] - sr * pr[3]), oy: MA.oy - s * (sr * pr[2] + cr * pr[3]) };
  }
  function unmapM(MB, pr) {
    var cr = Math.cos(MB.r), sr = Math.sin(MB.r);
    return { s: MB.s * pr[0], r: MB.r + pr[1], ox: MB.ox + MB.s * (cr * pr[2] - sr * pr[3]), oy: MB.oy + MB.s * (sr * pr[2] + cr * pr[3]) };
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
  var ramp = function (u, a, b) { return u <= a ? 0 : u >= b ? 1 : (u - a) / (b - a); };

  // a reel's geometry for one viewport: v = {vw, vh, wc, mode}
  function Reel(def) {
    this.def = def; this.U = def.U; this.by = {};
    var self = this;
    def.shots.forEach(function (sh) { self.by[sh.id] = sh; });
    def.shots.forEach(function (sh) { if (sh.from) self.by[sh.from].to = sh; });
  }
  Reel.prototype.layout = function (v) {
    var self = this;
    this.v = v; this.memo = {};
    this.def.shots.forEach(function (sh) { sh.kv = sh[v.mode].map(function (k) { return k.slice(); }); });
    this.def.shots.forEach(function (sh) {
      if (!sh.from) return;
      var p = PAIRS[sh.from + '>' + sh.id];
      sh.pr = p[v.mode] || p;
      self.by[sh.from].kv.forEach(function (k) { if (Math.abs(k[0] - sh.h) < 1e-6) sh.solved = solve(k, sh.pr, v); });
      self.memo = {};
    });
  };
  // the matrix of a shot at u (memoised for the current u)
  Reel.prototype.M = function (id, u) {
    var key = id + '@' + u, m = this.memo[key];
    if (m) return m;
    var sh = this.by[id], v = this.v;
    if (sh.from && u < sh.h) m = mapM(this.M(sh.from, u), sh.pr);
    else if (sh.to && u > sh.to.h) m = unmapM(this.M(sh.to.id, u), sh.to.pr);
    else {
      var kf = sh.kv;
      if (sh.from) kf = [camOf(mapM(this.M(sh.from, sh.h), sh.pr), kf[0][1], kf[0][2], sh.h, v)].concat(kf);
      m = fit(camM(along(kf, u), v), v);
    }
    return (this.memo[key] = m);
  };
  // where a shot is on, as a function of the scroll alone (0/1; the page fades it in time)
  Reel.prototype.on = function (sh, u) {
    var sp = this.span(sh);
    return u >= sp[0] && u < sp[1] ? 1 : 0;
  };
  // the span of u in which a shot is on
  Reel.prototype.span = function (sh) {
    return [sh.first ? -99 : sh.from ? sh.h : sh['in'], sh.out != null ? sh.out : sh.to ? sh.to.h : 99];
  };

  var MATH = { camM: camM, covers: covers, seen: seen, inside: inside, fit: fit, mapM: mapM, unmapM: unmapM, along: along,
               Reel: Reel, REELS: REELS, PAIRS: PAIRS, CROPS: CROPS, AR: AR, LOCK: LOCK };
  if (typeof document === 'undefined') { if (typeof module !== 'undefined') module.exports = MATH; return; }

  // ---- the page -----------------------------------------------------------------
  var root = document.documentElement;
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  var reelEls = Array.prototype.slice.call(document.querySelectorAll('[data-reel]'));
  if (!reelEls.length || reduce || !('IntersectionObserver' in window) || !window.requestAnimationFrame) {
    // the essay: each key photograph, whole
    root.classList.remove('pq-on', 'wy-on');
    Array.prototype.forEach.call(document.querySelectorAll('.pq-shot--key img[data-src]'), function (i) {
      i.loading = 'lazy'; i.sizes = i.dataset.sizes; i.srcset = i.dataset.srcset; i.src = i.dataset.src;
    });
    return;
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
    var shots = def.shots.map(function (sh) {
      var box = el.querySelector('[data-shot="' + sh.id + '"]'), i0 = box.querySelector('img');
      var i1 = document.createElement('img');
      i1.alt = ''; i1.decoding = 'async'; i1.className = 'pq-i pq-i--1'; i1.setAttribute('aria-hidden', 'true');
      i0.classList.add('pq-i');
      box.appendChild(i1);
      // [img, its cut, the src it should have, last style key, shown]
      return { sh: sh, el: box, L: [{ im: i0, c: null, src: '', k: '', on: false }, { im: i1, c: null, src: '', k: '', on: false }],
               live: false, want: false, cut: null, set: '', vt: tween(0), init: false };
    });
    var words = Array.prototype.slice.call(el.querySelectorAll('[data-b]')).map(function (w) {
      var b = def.beats[w.getAttribute('data-b')];
      var lines = Array.prototype.slice.call(w.querySelectorAll('.pq-l'));
      return { el: w, a: b[0], z: b[1], anchor: b[2], lines: lines, tin: lines.map(function () { return tween(0); }),
               sty: [], fit: w.classList.contains('pq-w--word'), cx: 0, cy: 0, ref: null, off: '' };
    });
    return { el: el, id: id, g: g, stage: el.querySelector('.pq-stage'), shots: shots, words: words, top: 0, near: false, u: -9,
             intro: id === 'a' ? tween(0) : null };
  });

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
    R.forEach(function (r) { r.top = r.el.getBoundingClientRect().top + sy; });
    S.forEach(function (st) {
      var b = st.el.getBoundingClientRect();
      st.top = b.top + sy; st.run = Math.max(1, b.height - window.innerHeight);
    });
  }
  // the cut each shot uses on this screen: the phone's own if it covers the whole
  // path here (checked every 0.02 screens), else the wide set's
  function chooseCuts(r) {
    var g = r.g, phone = vp.vw <= 600 && vp.vw < vp.vh;
    r.shots.forEach(function (s) {
      var c = CROPS[s.sh.id], d = s.el.dataset, has = function (k) { return c && c[k] && d[k + '0']; };
      var set = phone && has('p') ? 'p' : 'l', cut = has(set) ? c[set] : null;
      if (cut && set === 'p' && !pathIn(g, s.sh, cut)) { set = 'l'; cut = has('l') ? c.l : null; }
      if (cut && cut[1] && !d[set + '1']) cut = [cut[0], 0, 9, cut[3], 0];
      s.cut = cut || null; s.set = cut ? set : '';
      s.L.forEach(function (l, i) {
        l.c = !cut ? (i ? null : [0, 0, 1, 1]) : (i ? cut[1] || null : cut[0]);
        l.k = '';
        if (!l.c) return;
        l.im.style.width = ((l.c[2] - l.c[0]) * vp.wc).toFixed(2) + 'px';
        l.im.style.height = ((l.c[3] - l.c[1]) * AR * vp.wc).toFixed(2) + 'px';
      });
    });
    g.memo = {};
  }
  // past level 0's density for this set (phones 1.5 file px per CSS px, the rest 0.9)
  function deep(cut, set, M) { return cut[1] && cut[3] / M.s < (set === 'p' ? 1.5 : 0.9) && cut[4] > cut[3]; }
  function pathIn(g, sh, cut) {
    var sp = g.span(sh);
    for (var u = Math.max(sp[0], -1); u <= Math.min(sp[1] + LOCK, g.U + 1); u += 0.02) {
      g.memo = {};
      var M = g.M(sh.id, u), a = seen(M, vp);
      if (!inside(a, cut[0], 1e-3) && !(deep(cut, 'p', M) && inside(a, cut[1], 1e-3))) return false;
    }
    return true;
  }
  function layout() {
    var st = R[0].stage, vw = st.clientWidth, vh = st.clientHeight;
    if (!vw || !vh) return;
    vp = { vw: vw, vh: vh, wc: Math.max(vw, vh / AR), mode: vw / vh < 1 ? 'P' : 'L' };
    R.forEach(function (r) {
      r.g.layout(vp);
      chooseCuts(r);
      r.shots.forEach(function (s) { if (s.want) load(s); });
      r.words.forEach(function (w) { if (w.fit) fitWord(w); });
      r.words.forEach(function (w) {
        w.el.style.transform = '';
        w.cx = w.el.offsetLeft + w.el.offsetWidth / 2; w.cy = w.el.offsetTop + w.el.offsetHeight / 2;
        w.ref = null; w.off = '';
      });
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
  // give a shot its files (only once it is near: a phone fetches what it will see)
  function load(s) {
    s.want = true;
    s.L.forEach(function (l, i) {
      if (!l.c) return;
      var d = l.im.dataset, src = s.cut ? s.el.dataset[s.set + i] : d.src;
      // no cut for this photograph: the whole one, at the size the cover box asks for
      if (!s.cut && !i && d.srcset) { l.im.sizes = Math.ceil(vp.wc) + 'px'; l.im.srcset = d.srcset; }
      if (l.src === src) return;
      l.src = src;
      var im = l.im;
      if (s.cut && !i) { im.removeAttribute('srcset'); im.removeAttribute('sizes'); }
      im.loading = 'eager';
      im.src = src;
      if (im.decode) im.decode().catch(function () {});
    });
  }

  // ---- drawing -----------------------------------------------------------------------
  // the transform of a cut c of a photograph placed by M
  function mstr(M, c) {
    var r = M.r, cr = Math.cos(r), sr = Math.sin(r), x = c[0], y = c[1] * AR;
    var ox = M.ox + M.s * (cr * x - sr * y), oy = M.oy + M.s * (sr * x + cr * y);
    return 'translate3d(' + ox.toFixed(2) + 'px,' + oy.toFixed(2) + 'px,0)' +
      (Math.abs(r) > 1e-5 ? ' rotate(' + (r * 180 / Math.PI).toFixed(4) + 'deg)' : '') + ' scale(' + (M.s / vp.wc).toFixed(5) + ')';
  }
  // a cut of a live shot is always placed (so it is rasterised, at the right scale, while
  // it is still at opacity 0: its first visible frame is not the one that pays for it)
  function show(l, op, M) {
    if (!l.c) return;
    var t = M ? mstr(M, l.c) : l.t, k = op.toFixed(3) + t;
    if (k === l.k) return;
    l.k = k; l.t = t; l.im.style.opacity = op.toFixed(3); if (t) l.im.style.transform = t;
  }
  var moving = false;
  function drawReel(r) {
    var u = r.u, g = r.g;
    g.memo = {};
    // first where each shot should be (cuts and dissolves run in time), then draw
    r.shots.forEach(function (s) {
      var sh = s.sh, on = g.on(sh, u);
      if (!s.init) { s.init = true; s.vt.from = s.vt.to = on; }
      if (sh.to && u >= sh.to.h) { s.vt.from = s.vt.to = 1; s.vt.at = 0; return; }   // under B: see below
      if (sh.from) aim(s.vt, on, T_X, 0);
      else aim(s.vt, on, on ? T_UP : T_DOWN, on ? T_GAP : 0);
    });
    r.shots.forEach(function (s, i) {
      var sh = s.sh, sp = g.span(sh), op;
      if (sh.to && u >= sh.to.h) {
        // under the photograph it dissolves into: whole until that one is whole
        var b = r.shots[i + 1].vt;
        op = b.to === 1 && val(b) < 0.999 ? 1 : 0;
      } else {
        op = val(s.vt);
        if (busy(s.vt) || now < s.vt.at) moving = true;
      }
      if (r.intro && sh.first) { op *= val(r.intro); if (busy(r.intro)) moving = true; }
      if (!s.want && u > sp[0] - 1.4 && u < sp[1] + 0.6) load(s);
      var live = op > 0.0005 || (u > sp[0] - 0.3 && u < sp[1] + 0.05);
      if (live !== s.live) { s.live = live; s.el.classList.toggle('is-live', live); if (!live) { s.L[0].k = s.L[1].k = ''; } }
      if (!live) return;
      var M = g.M(sh.id, u), L1 = s.L[1], hi = false;
      // level 1 once level 0 has fewer file pixels per CSS pixel than this set asks for
      // (phones 1.5, the rest 0.9), level 1 has more, and it covers the screen
      if (L1.c && L1.im.complete && L1.im.naturalWidth) hi = deep(s.cut, s.set, M) && inside(seen(M, vp), L1.c, 1e-3);
      show(s.L[0], hi ? 0 : op, M);
      show(L1, hi ? op : 0, M);
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
  // the opening photograph is asked for at once, and the page fades up from night
  // once it is there (at most 1.2 s after this script: night is a good enough
  // ground to land the words on)
  (function () {
    var r = R[0], s = r.shots[0];
    if (!r.intro) return;
    load(s);
    var im = s.L[0].im, done = false;
    var go = function () { if (done) return; done = true; now = performance.now(); aim(r.intro, 1, 0.9); kick(); };
    if (im.complete && im.naturalWidth) requestAnimationFrame(go);
    else { im.addEventListener('load', go); im.addEventListener('error', go); setTimeout(go, 1200); }
  })();
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
          R.forEach(function (q) { q.words.forEach(function (w) { w.tin.forEach(done); }); q.shots.forEach(function (s) { done(s.vt); }); if (q.intro) done(q.intro); });
          S.forEach(function (st) { st.beats.forEach(function (b) { b.tin.forEach(done); }); });
          render();
        }
        return { u: (x - r.top) / vp.vh };
      },
      // hold a shot's fade at v (for tools that look at a dissolve half-way)
      hold: function (id, shot, v) {
        var r = R.filter(function (q) { return q.id === id; })[0], s = r.shots.filter(function (q) { return q.sh.id === shot; })[0];
        s.vt.from = v; s.vt.to = 1; s.vt.at = 1e15; now = performance.now(); render();
      },
      // load every file of a reel now (for tools that seek)
      loadAll: function (id) { R.forEach(function (q) { if (!id || q.id === id) q.shots.forEach(load); }); },
      state: function (id) {
        var r = R.filter(function (q) { return q.id === id; })[0], g = r.g;
        g.memo = {};
        return r.shots.map(function (s) {
          var M = s.live ? g.M(s.sh.id, r.u) : null;
          return { id: s.sh.id, live: s.live, set: s.set, op0: +s.L[0].im.style.opacity || 0, op1: +s.L[1].im.style.opacity || 0,
                   z: M ? M.s / vp.wc : null, r: M ? M.r * 180 / Math.PI : null, slid: M && M.slid, grown: M && M.grown,
                   // file pixels per CSS pixel of the level on screen
                   dens: M ? s.L.map(function (l) { return l.c && l.im.naturalWidth ? l.im.naturalWidth / ((l.c[2] - l.c[0]) * M.s) : null; }) : null,
                   src: s.L.map(function (l) { return l.im.currentSrc; }) };
        });
      }
    };
  }
})();
